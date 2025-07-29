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
            echo json_encode(['success' => false, 'message' => 'Article non trouvé']);
            exit;
        }
        
        $articleIssues = analyzeArticle($article);
        
        echo json_encode([
            'success' => true,
            'data' => [
                'id' => $article->id,
                'title' => $article->title,
                'category' => $article->category_title ?: 'Sans catégorie',
                'language' => $article->language ?: 'fr-FR',
                'hits' => $article->hits,
                'issues' => $articleIssues['issues'],
                'severity' => $articleIssues['severity']
            ],
            'message' => 'Article "' . $article->title . '" analysé'
        ], JSON_UNESCAPED_UNICODE);
        
    } elseif (isset($_POST['get_articles_list'])) {
        // Get list of all articles for progressive analysis
        $stmt = $db->prepare("
            SELECT a.id, a.title
            FROM " . $config->dbprefix . "content a
            WHERE a.state = 1
            ORDER BY a.title
        ");
        $stmt->execute();
        $articlesList = $stmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'data' => $articlesList,
            'message' => count($articlesList) . ' articles trouvés'
        ], JSON_UNESCAPED_UNICODE);
        
    } else {
        // Original: Analyze all articles at once (fallback)
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
            WHERE a.state = 1
            ORDER BY a.title
        ");
        $stmt->execute();
        $articles = $stmt->fetchAll();
        
        // Analyze all articles for SEO issues
        $results = analyzeArticles($articles);
        
        // Return success response
        echo json_encode([
            'success' => true,
            'data' => $results,
            'message' => 'Analyse SEO terminée avec succès'
        ], JSON_UNESCAPED_UNICODE);
    }
    
} catch (Exception $e) {
    // Handle general exceptions
    echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'analyse SEO: ' . $e->getMessage()]);
} catch (Error $e) {
    // Handle fatal errors
    echo json_encode(['success' => false, 'message' => 'Erreur fatale: ' . $e->getMessage()]);
}

/**
 * Analyze articles for SEO issues
 */
function analyzeArticles($articles)
{
    $results = [
        'total_articles' => count($articles),
        'issues' => [],
        'stats' => [
            'title_issues' => 0,
            'meta_description_issues' => 0,
            'content_issues' => 0,
            'image_issues' => 0,
            'url_issues' => 0
        ]
    ];

    foreach ($articles as $article) {
        $articleIssues = analyzeArticle($article);
        
        if (!empty($articleIssues['issues'])) {
            $results['issues'][] = [
                'id' => $article->id,
                'title' => $article->title,
                'category' => $article->category_title ?: 'Sans catégorie',
                'language' => $article->language ?: 'fr-FR',
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
function analyzeArticle($article)
{
    $issues = [];
    $categories = [];
    $maxSeverity = 'info';

    // 1. Title Analysis
    $titleLength = mb_strlen($article->title, 'UTF-8');
    if (empty($article->title)) {
        $issues[] = [
            'type' => 'title_missing',
            'message' => 'Titre manquant',
            'severity' => 'critical',
            'icon' => 'exclamation-triangle'
        ];
        $categories[] = 'title';
        $maxSeverity = 'critical';
    } elseif ($titleLength < 30) {
        $issues[] = [
            'type' => 'title_too_short',
            'message' => "Titre trop court ({$titleLength} caractères, recommandé: 30-60)",
            'severity' => 'warning',
            'icon' => 'warning'
        ];
        $categories[] = 'title';
        if ($maxSeverity !== 'critical') $maxSeverity = 'warning';
    } elseif ($titleLength > 60) {
        $issues[] = [
            'type' => 'title_too_long',
            'message' => "Titre trop long ({$titleLength} caractères, recommandé: 30-60)",
            'severity' => 'warning',
            'icon' => 'warning'
        ];
        $categories[] = 'title';
        if ($maxSeverity !== 'critical') $maxSeverity = 'warning';
    }

    // 2. Meta Description Analysis (same logic as direct_ai_metadesc.php)
    $metaDesc = trim($article->metadesc ?? '');
    $metaDescLength = mb_strlen($metaDesc, 'UTF-8');
    
    if (empty($metaDesc) || $metaDescLength === 0) {
        $issues[] = [
            'type' => 'meta_desc_missing',
            'message' => 'Méta-description manquante',
            'severity' => 'critical',
            'icon' => 'exclamation-triangle'
        ];
        $categories[] = 'meta_description';
        $maxSeverity = 'critical';
    } elseif ($metaDescLength < 120) {
        $issues[] = [
            'type' => 'meta_desc_too_short',
            'message' => "Méta-description trop courte ({$metaDescLength} caractères, recommandé: 120-160)",
            'severity' => 'warning',
            'icon' => 'warning'
        ];
        $categories[] = 'meta_description';
        if ($maxSeverity !== 'critical') $maxSeverity = 'warning';
    } elseif ($metaDescLength > 160) {
        $issues[] = [
            'type' => 'meta_desc_too_long',
            'message' => "Méta-description trop longue ({$metaDescLength} caractères, recommandé: 120-160)",
            'severity' => 'warning',
            'icon' => 'warning'
        ];
        $categories[] = 'meta_description';
        if ($maxSeverity !== 'critical') $maxSeverity = 'warning';
    }

    // 3. Content Analysis
    $content = $article->introtext . ' ' . $article->fulltext;
    $contentLength = mb_strlen(strip_tags($content), 'UTF-8');
    
    if ($contentLength < 300) {
        $issues[] = [
            'type' => 'content_too_short',
            'message' => "Contenu trop court ({$contentLength} caractères, recommandé: 300+)",
            'severity' => 'info',
            'icon' => 'info'
        ];
        $categories[] = 'content';
    }

    // Check for H1 tags
    if (stripos($content, '<h1') === false) {
        $issues[] = [
            'type' => 'missing_h1',
            'message' => 'Balise H1 manquante dans le contenu',
            'severity' => 'warning',
            'icon' => 'warning'
        ];
        $categories[] = 'content';
        if ($maxSeverity !== 'critical') $maxSeverity = 'warning';
    }

    // 4. Image Analysis
    $imageCount = substr_count(strtolower($content), '<img');
    $altCount = substr_count(strtolower($content), 'alt=');
    
    if ($imageCount > 0 && $altCount < $imageCount) {
        $missingAlt = $imageCount - $altCount;
        $issues[] = [
            'type' => 'missing_alt_tags',
            'message' => "{$missingAlt} image(s) sans attribut alt sur {$imageCount} total",
            'severity' => 'warning',
            'icon' => 'image'
        ];
        $categories[] = 'image';
        if ($maxSeverity !== 'critical') $maxSeverity = 'warning';
    }

    // 5. URL Analysis
    if (strlen($article->alias) > 50) {
        $issues[] = [
            'type' => 'url_too_long',
            'message' => 'Alias d\'URL trop long (' . strlen($article->alias) . ' caractères)',
            'severity' => 'info',
            'icon' => 'link'
        ];
        $categories[] = 'url';
    }

    // 6. Missing meta keywords (optional check)
    if (empty($article->metakey)) {
        $issues[] = [
            'type' => 'meta_keywords_missing',
            'message' => 'Mots-clés meta manquants',
            'severity' => 'info',
            'icon' => 'tag'
        ];
        $categories[] = 'meta_description';
    }

    return [
        'issues' => $issues,
        'severity' => $maxSeverity,
        'categories' => array_unique($categories)
    ];
}