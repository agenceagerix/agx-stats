<?php
/*-----------------------------------------------------------------------------------------------------/
	@version		1.2.0
	@build			29th July, 2025
	@created		29th July, 2025
	@package		JoomlaHits
	@subpackage		direct_seo_analysis.php
	@author			Hugo Dantas - Agence Agerix <https://www.agerix.fr>
	@copyright		Copyright (C) 2025. All Rights Reserved
	@license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html
	  __    ___  ____  __ _   ___  ____     __    ___  ____  ____  __  _  _
	 / _\  / __)(  __)(  ( \ / __)(  __)   / _\  / __)(  __)(  _ \(  )( \/ )
	/    \( (_ \ ) _) /    /( (__  ) _)   /    \( (_ \ ) _)  )   / )(  )  (
	\_/\_/ \___/(____)\_)__) \___)(____)  \_/\_/ \___/(____)(__\_)(__)(_/\_)
/------------------------------------------------------------------------------------------------------*/
// Set response headers for JSON API endpoint
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Handle CORS preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Ensure only POST requests are allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Check if we're analyzing a single article or all articles
$singleArticleId = isset($_POST['article_id']) ? intval($_POST['article_id']) : null;

// Initialize Joomla environment for database configuration access
define('_JEXEC', 1);
define('JPATH_BASE', realpath(dirname(__FILE__) . '/../../..'));

// Verify Joomla configuration file exists
if (!file_exists(JPATH_BASE . '/configuration.php')) {
    echo json_encode(['success' => false, 'message' => 'Joomla configuration.php not found']);
    exit;
}

// Load Joomla database configuration
require_once JPATH_BASE . '/configuration.php';
$config = new JConfig();

// Initialize default configuration values
$selectedCategories = [];
$selectedIssues = [
    'title_missing',
    'title_too_short', 
    'title_too_long',
    'meta_desc_missing',
    'meta_desc_too_short',
    'meta_desc_too_long',
    'meta_keywords_missing',
    'missing_h1',
    'missing_alt_tags',
    'content_too_short'
];
$minTitleLength = 30;
$maxTitleLength = 60;
$minMetaLength = 120;
$maxMetaLength = 160;
$minContentLength = 300;
$minUrlLength = 5;
$maxUrlLength = 50;

// Try to load component parameters
try {
    // Establish direct PDO connection to read parameters
    $paramsDb = new PDO(
        'mysql:host=' . $config->host . ';dbname=' . $config->db . ';charset=utf8mb4',
        $config->user,
        $config->password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
        ]
    );
    
    // Get component parameters from database
    $stmt = $paramsDb->prepare("
        SELECT params 
        FROM " . $config->dbprefix . "extensions 
        WHERE element = 'com_joomlahits' AND type = 'component'
    ");
    $stmt->execute();
    $result = $stmt->fetch();
    
    if ($result && !empty($result->params)) {
        $paramsData = json_decode($result->params, true);
        if ($paramsData) {
            // Get configuration values
            $analyzeAllCategories = isset($paramsData['seo_analyze_all_categories']) ? $paramsData['seo_analyze_all_categories'] : '1';
            $selectedCategories = isset($paramsData['seo_categories']) ? $paramsData['seo_categories'] : [];
            
            // If analyze all categories is enabled, clear the selected categories
            if ($analyzeAllCategories == '1') {
                $selectedCategories = [];
            }
            
            $detectAllIssues = isset($paramsData['seo_detect_all_issues']) ? $paramsData['seo_detect_all_issues'] : '1';
            $selectedIssues = isset($paramsData['seo_critical_issues']) ? $paramsData['seo_critical_issues'] : ['title_missing', 'meta_desc_missing'];
            
            // If detect all issues is enabled, set all available issues
            if ($detectAllIssues == '1') {
                $selectedIssues = [
                    'title_missing',
                    'title_too_short', 
                    'title_too_long',
                    'meta_desc_missing',
                    'meta_desc_too_short',
                    'meta_desc_too_long',
                    'meta_keywords_missing',
                    'missing_h1',
                    'missing_alt_tags',
                    'content_too_short'
                ];
            }
            $minTitleLength = isset($paramsData['seo_min_title_length']) ? intval($paramsData['seo_min_title_length']) : 30;
            $maxTitleLength = isset($paramsData['seo_max_title_length']) ? intval($paramsData['seo_max_title_length']) : 60;
            $minMetaLength = isset($paramsData['seo_min_meta_length']) ? intval($paramsData['seo_min_meta_length']) : 120;
            $maxMetaLength = isset($paramsData['seo_max_meta_length']) ? intval($paramsData['seo_max_meta_length']) : 160;
            $minContentLength = isset($paramsData['seo_min_content_length']) ? intval($paramsData['seo_min_content_length']) : 300;
            $minUrlLength = isset($paramsData['seo_min_url_length']) ? intval($paramsData['seo_min_url_length']) : 5;
            $maxUrlLength = isset($paramsData['seo_max_url_length']) ? intval($paramsData['seo_max_url_length']) : 50;
        }
    }
} catch (Exception $e) {
    // If we can't load parameters, continue with defaults
}

try {
    // Establish direct PDO database connection
    $db = new PDO(
        'mysql:host=' . $config->host . ';dbname=' . $config->db . ';charset=utf8mb4',
        $config->user,
        $config->password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
        ]
    );
    
    if ($singleArticleId) {
        // Analyze single article
        $stmt = $db->prepare("
            SELECT 
                a.id,
                a.title,
                a.alias,
                a.introtext,
                a.fulltext,
                a.metadesc,
                a.metakey,
                a.images,
                a.hits,
                a.language,
                a.state,
                c.title AS category_title
            FROM " . $config->dbprefix . "content a
            LEFT JOIN " . $config->dbprefix . "categories c ON c.id = a.catid
            WHERE a.id = :article_id AND a.state = 1
        ");
        $stmt->bindParam(':article_id', $singleArticleId, PDO::PARAM_INT);
        $stmt->execute();
        $article = $stmt->fetch();
        
        if (!$article) {
            echo json_encode(['success' => false, 'message' => 'Article not found']);
            exit;
        }
        
        $articleIssues = analyzeArticle($article, $selectedIssues, $minTitleLength, $maxTitleLength, $minMetaLength, $maxMetaLength, $minContentLength, $minUrlLength, $maxUrlLength);
        
        echo json_encode([
            'success' => true,
            'data' => [
                'id' => $article->id,
                'title' => $article->title,
                'alias' => $article->alias,
                'metadesc' => $article->metadesc,
                'metakey' => $article->metakey,
                'content' => trim($article->introtext . ' ' . $article->fulltext),
                'category' => $article->category_title ?: 'No category',
                'language' => $article->language ?: 'en-GB',
                'hits' => $article->hits,
                'issues' => $articleIssues['issues'],
                'severity' => $articleIssues['severity']
            ],
            'message' => 'Article "' . $article->title . '" analyzed'
        ], JSON_UNESCAPED_UNICODE);
        
    } elseif (isset($_POST['get_articles_list'])) {
        // Get list of all articles for progressive analysis
        $query = "
            SELECT a.id, a.title
            FROM " . $config->dbprefix . "content a
            WHERE a.state = 1
        ";
        
        // Filter by selected categories if specified (including subcategories)
        if (!empty($selectedCategories) && !in_array('', $selectedCategories)) {
            $allCategoryIds = [];
            
            foreach ($selectedCategories as $catId) {
                $catId = intval($catId);
                if ($catId > 0) {
                    $allCategoryIds[] = $catId;
                    
                    // Find subcategories using simpler nested set query
                    $subCatStmt = $db->prepare("
                        SELECT c2.id 
                        FROM " . $config->dbprefix . "categories c1, " . $config->dbprefix . "categories c2
                        WHERE c2.lft > c1.lft AND c2.rgt < c1.rgt
                        AND c1.id = :parent_id
                        AND c2.extension = 'com_content'
                        AND c2.published = 1
                    ");
                    $subCatStmt->bindParam(':parent_id', $catId, PDO::PARAM_INT);
                    $subCatStmt->execute();
                    $subCategories = $subCatStmt->fetchAll();
                    
                    foreach ($subCategories as $subCat) {
                        $allCategoryIds[] = intval($subCat->id);
                    }
                }
            }
            
            if (!empty($allCategoryIds)) {
                $allCategoryIds = array_unique($allCategoryIds);
                $query .= " AND a.catid IN (" . implode(',', $allCategoryIds) . ")";
            }
        }
        
        $query .= " ORDER BY a.title";
        
        $stmt = $db->prepare($query);
        $stmt->execute();
        $articlesList = $stmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'data' => $articlesList,
            'message' => count($articlesList) . ' articles found'
        ], JSON_UNESCAPED_UNICODE);
        
    } else {
        // Original: Analyze all articles at once (fallback)
        $query = "
            SELECT 
                a.id,
                a.title,
                a.alias,
                a.introtext,
                a.fulltext,
                a.metadesc,
                a.metakey,
                a.images,
                a.hits,
                a.language,
                a.state,
                c.title AS category_title
            FROM " . $config->dbprefix . "content a
            LEFT JOIN " . $config->dbprefix . "categories c ON c.id = a.catid
            WHERE a.state = 1
        ";
        
        // Filter by selected categories if specified (including subcategories)
        if (!empty($selectedCategories) && !in_array('', $selectedCategories)) {
            $allCategoryIds = [];
            
            foreach ($selectedCategories as $catId) {
                $catId = intval($catId);
                if ($catId > 0) {
                    $allCategoryIds[] = $catId;
                    
                    // Find subcategories using simpler nested set query
                    $subCatStmt = $db->prepare("
                        SELECT c2.id 
                        FROM " . $config->dbprefix . "categories c1, " . $config->dbprefix . "categories c2
                        WHERE c2.lft > c1.lft AND c2.rgt < c1.rgt
                        AND c1.id = :parent_id
                        AND c2.extension = 'com_content'
                        AND c2.published = 1
                    ");
                    $subCatStmt->bindParam(':parent_id', $catId, PDO::PARAM_INT);
                    $subCatStmt->execute();
                    $subCategories = $subCatStmt->fetchAll();
                    
                    foreach ($subCategories as $subCat) {
                        $allCategoryIds[] = intval($subCat->id);
                    }
                }
            }
            
            if (!empty($allCategoryIds)) {
                $allCategoryIds = array_unique($allCategoryIds);
                $query .= " AND a.catid IN (" . implode(',', $allCategoryIds) . ")";
            }
        }
        
        $query .= " ORDER BY a.title";
        
        $stmt = $db->prepare($query);
        $stmt->execute();
        $articles = $stmt->fetchAll();
        
        // Analyze all articles for SEO issues
        $results = analyzeArticles($articles, $selectedIssues, $minTitleLength, $maxTitleLength, $minMetaLength, $maxMetaLength, $minContentLength, $minUrlLength, $maxUrlLength);
        
        // Return success response
        echo json_encode([
            'success' => true,
            'data' => $results,
            'message' => 'SEO analysis completed successfully'
        ], JSON_UNESCAPED_UNICODE);
    }
    
} catch (Exception $e) {
    // Handle general exceptions
    echo json_encode(['success' => false, 'message' => 'Error during SEO analysis: ' . $e->getMessage()]);
} catch (Error $e) {
    // Handle fatal errors
    echo json_encode(['success' => false, 'message' => 'Fatal error: ' . $e->getMessage()]);
}

/**
 * Analyze articles for SEO issues
 */
function analyzeArticles($articles, $selectedIssues, $minTitleLength, $maxTitleLength, $minMetaLength, $maxMetaLength, $minContentLength, $minUrlLength, $maxUrlLength)
{
    $results = [
        'total_articles' => count($articles),
        'issues' => [],
        'stats' => [
            'title_issues' => 0,
            'meta_description_issues' => 0,
            'content_issues' => 0,
            'image_issues' => 0
        ]
    ];

    foreach ($articles as $article) {
        $articleIssues = analyzeArticle($article, $selectedIssues, $minTitleLength, $maxTitleLength, $minMetaLength, $maxMetaLength, $minContentLength, $minUrlLength, $maxUrlLength);
        
        if (!empty($articleIssues['issues'])) {
            $results['issues'][] = [
                'id' => $article->id,
                'title' => $article->title,
                'category' => $article->category_title ?: 'No category',
                'language' => $article->language ?: 'en-GB',
                'hits' => $article->hits,
                'issues' => $articleIssues['issues'],
                'severity' => $articleIssues['severity']
            ];

            // Update stats
            foreach ($articleIssues['categories'] as $category) {
                if (isset($results['stats'][$category . '_issues'])) {
                    $results['stats'][$category . '_issues']++;
                }
            }
        }
    }

    // Sort by severity (critical first)
    usort($results['issues'], function($a, $b) {
        $severityOrder = ['critical' => 0, 'warning' => 1, 'info' => 2];
        return $severityOrder[$a['severity']] - $severityOrder[$b['severity']];
    });

    return $results;
}

/**
 * Analyze a single article for SEO issues
 */
function analyzeArticle($article, $selectedIssues = null, $minTitleLength = 30, $maxTitleLength = 60, $minMetaLength = 120, $maxMetaLength = 160, $minContentLength = 300, $minUrlLength = 5, $maxUrlLength = 50)
{
    // Default selected issues if not provided
    if ($selectedIssues === null) {
        $selectedIssues = ['title_missing', 'meta_desc_missing'];
    }
    
    $issues = [];
    $categories = [];
    $maxSeverity = 'info';

    // 1. Title Analysis
    $titleLength = mb_strlen($article->title, 'UTF-8');
    if (empty($article->title) && in_array('title_missing', $selectedIssues)) {
        $issues[] = [
            'type' => 'title_missing',
            'message' => 'Missing title',
            'severity' => 'critical',
            'icon' => 'exclamation-triangle'
        ];
        $categories[] = 'title';
        $maxSeverity = 'critical';
    } elseif ($titleLength < $minTitleLength && in_array('title_too_short', $selectedIssues)) {
        $issues[] = [
            'type' => 'title_too_short',
            'message' => "Title too short ({$titleLength} characters, recommended: {$minTitleLength}-{$maxTitleLength})",
            'severity' => 'warning',
            'icon' => 'warning'
        ];
        $categories[] = 'title';
        if ($maxSeverity !== 'critical') $maxSeverity = 'warning';
    } elseif ($titleLength > $maxTitleLength && in_array('title_too_long', $selectedIssues)) {
        $issues[] = [
            'type' => 'title_too_long',
            'message' => "Title too long ({$titleLength} characters, recommended: {$minTitleLength}-{$maxTitleLength})",
            'severity' => 'warning',
            'icon' => 'warning'
        ];
        $categories[] = 'title';
        if ($maxSeverity !== 'critical') $maxSeverity = 'warning';
    }

    // 2. Meta Description Analysis (same logic as direct_ai_metadesc.php)
    $metaDesc = trim($article->metadesc ?? '');
    $metaDescLength = mb_strlen($metaDesc, 'UTF-8');
    
    if ((empty($metaDesc) || $metaDescLength === 0) && in_array('meta_desc_missing', $selectedIssues)) {
        $issues[] = [
            'type' => 'meta_desc_missing',
            'message' => 'Missing meta description',
            'severity' => 'critical',
            'icon' => 'exclamation-triangle'
        ];
        $categories[] = 'meta_description';
        $maxSeverity = 'critical';
    } elseif ($metaDescLength < $minMetaLength && in_array('meta_desc_too_short', $selectedIssues)) {
        $issues[] = [
            'type' => 'meta_desc_too_short',
            'message' => "Meta description too short ({$metaDescLength} characters, recommended: {$minMetaLength}-{$maxMetaLength})",
            'severity' => 'warning',
            'icon' => 'warning'
        ];
        $categories[] = 'meta_description';
        if ($maxSeverity !== 'critical') $maxSeverity = 'warning';
    } elseif ($metaDescLength > $maxMetaLength && in_array('meta_desc_too_long', $selectedIssues)) {
        $issues[] = [
            'type' => 'meta_desc_too_long',
            'message' => "Meta description too long ({$metaDescLength} characters, recommended: {$minMetaLength}-{$maxMetaLength})",
            'severity' => 'warning',
            'icon' => 'warning'
        ];
        $categories[] = 'meta_description';
        if ($maxSeverity !== 'critical') $maxSeverity = 'warning';
    }

    // 3. Content Analysis
    $content = $article->introtext . ' ' . $article->fulltext;
    $contentLength = mb_strlen(strip_tags($content), 'UTF-8');
    
    if ($contentLength < $minContentLength && in_array('content_too_short', $selectedIssues)) {
        $issues[] = [
            'type' => 'content_too_short',
            'message' => "Content too short ({$contentLength} characters, recommended: {$minContentLength}+)",
            'severity' => 'info',
            'icon' => 'info'
        ];
        $categories[] = 'content';
    }

    // Check for H1 tags
    if (stripos($content, '<h1') === false && in_array('missing_h1', $selectedIssues)) {
        $issues[] = [
            'type' => 'missing_h1',
            'message' => 'Missing H1 tag in content',
            'severity' => 'warning',
            'icon' => 'warning'
        ];
        $categories[] = 'content';
        if ($maxSeverity !== 'critical') $maxSeverity = 'warning';
    }

    // 4. Image Analysis
    if (in_array('missing_alt_tags', $selectedIssues)) {
        $imageCount = substr_count(strtolower($content), '<img');
        $altCount = substr_count(strtolower($content), 'alt=');
        
        if ($imageCount > 0 && $altCount < $imageCount) {
            $missingAlt = $imageCount - $altCount;
            $issues[] = [
                'type' => 'missing_alt_tags',
                'message' => "{$missingAlt} image(s) without alt attribute out of {$imageCount} total",
                'severity' => 'warning',
                'icon' => 'image'
            ];
            $categories[] = 'image';
            if ($maxSeverity !== 'critical') $maxSeverity = 'warning';
        }
    }


    // 6. Missing or insufficient meta keywords (optional check)
    if (in_array('meta_keywords_missing', $selectedIssues)) {
        if (empty($article->metakey)) {
            $issues[] = [
                'type' => 'meta_keywords_missing',
                'message' => 'Missing meta keywords',
                'severity' => 'info',
                'icon' => 'tag'
            ];
            $categories[] = 'meta_description';
        } else {
            // Check if keywords are too few
            $keywords = array_filter(array_map('trim', explode(',', $article->metakey)));
            if (count($keywords) < 3) {
                $issues[] = [
                    'type' => 'meta_keywords_too_few',
                    'message' => 'Meta keywords are too few (less than 3)',
                    'severity' => 'info',
                    'icon' => 'tag'
                ];
                $categories[] = 'meta_description';
            }
        }
    }

    return [
        'issues' => $issues,
        'severity' => $maxSeverity,
        'categories' => array_unique($categories)
    ];
}