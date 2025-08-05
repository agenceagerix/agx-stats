<?php
/*-----------------------------------------------------------------------------------------------------/
	@version		1.2.0
	@build			5th August, 2025
	@created		5th August, 2025
	@package		JoomlaHits
	@subpackage		direct_targeted_img_fix.php
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

// Extract and validate article ID from POST data
$articleId = intval($_POST['article_id'] ?? 0);

if (!$articleId) {
    echo json_encode(['success' => false, 'message' => 'Invalid article ID']);
    exit;
}

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
    
    // Fetch article information from database
    $stmt = $db->prepare("
        SELECT `id`, `title`, `introtext`, `fulltext`, `metadesc`, `metakey`, `alias`, `language` 
        FROM " . $config->dbprefix . "content 
        WHERE `id` = :article_id AND `state` != -2
    ");
    $stmt->bindParam(':article_id', $articleId, PDO::PARAM_INT);
    $stmt->execute();
    $article = $stmt->fetch();
    
    // Validate article exists
    if (!$article) {
        echo json_encode(['success' => false, 'message' => 'Article not found']);
        exit;
    }
    
    // Extract images with missing or empty alt attributes
    // Use custom content if provided, otherwise use article content from database
    if (isset($_POST['content']) && !empty($_POST['content'])) {
        $fullContent = $_POST['content'];
    } else {
        $fullContent = $article->introtext . ' ' . $article->fulltext;
    }
    $problematicImages = extractProblematicImages($fullContent);
    
    // If no problematic images found, return success with no changes
    if (empty($problematicImages)) {
        echo json_encode([
            'success' => true,
            'message' => 'No images with missing or empty alt attributes found',
            'article_id' => $articleId,
            'images_fixed' => 0,
            'modified_content' => $fullContent
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Load AI provider configuration
    require_once __DIR__ . '/ai_provider.php';
    
    // Retrieve component configuration
    $extensionQuery = $db->prepare("
        SELECT `params`
        FROM " . $config->dbprefix . "extensions 
        WHERE `element` = 'com_joomlahits' AND `type` = 'component' AND `client_id` = 1
    ");
    $extensionQuery->execute();
    $extension = $extensionQuery->fetch();
    
    // Parse component parameters
    $params = [];
    if ($extension && !empty($extension->params)) {
        $params = json_decode($extension->params, true);
    }
    
    // Check if AI is enabled for content/images processing
    $aiEnabled = isset($params['ai_enable_content_images']) ? (bool)$params['ai_enable_content_images'] : true;
    
    if (!$aiEnabled) {
        echo json_encode([
            'success' => false,
            'message' => 'AI is disabled for content/images processing in component configuration',
            'disabled' => true
        ]);
        exit;
    }
    
    // Initialize AI provider
    try {
        $aiProvider = new AIProvider($params);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'AI provider error: ' . $e->getMessage()
        ]);
        exit;
    }
    
    // Create targeted prompt for image alt fixes only
    $prompt = createTargetedImagePrompt($problematicImages, $article, $params);
    
    // Generate corrected img tags using AI provider
    try {
        $aiResponse = $aiProvider->generateContent($prompt);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'AI generation error: ' . $e->getMessage()
        ]);
        exit;
    }
    
    // Parse AI response to get corrected img tags
    $correctedImages = parseAIImageResponse($aiResponse, $problematicImages);
    
    // Replace the problematic img tags with corrected ones using enhanced replacement logic
    $modifiedContent = $fullContent;
    $imagesFixed = 0;
    $replacementLog = [];
    
    // Use indexed replacement to ensure 1:1 correspondence with enhanced fallback methods
    foreach ($correctedImages as $index => $correctedImg) {
        if (isset($problematicImages[$index])) {
            $originalImg = $problematicImages[$index]['tag'];
            $originalSrc = $problematicImages[$index]['src'];
            
            $replacementSuccess = false;
            $replacementMethod = '';
            
            // Method 1: Direct string replacement (most reliable)
            if (!$replacementSuccess) {
                $beforeReplace = $modifiedContent;
                $modifiedContent = str_replace($originalImg, $correctedImg, $modifiedContent);
                
                if ($beforeReplace !== $modifiedContent) {
                    $replacementSuccess = true;
                    $replacementMethod = 'direct_string';
                }
            }
            
            // Method 2: Regex replacement with escaped characters
            if (!$replacementSuccess) {
                $escapedOriginal = preg_quote($originalImg, '/');
                $beforeReplace = $modifiedContent;
                $modifiedContent = preg_replace('/' . $escapedOriginal . '/', $correctedImg, $modifiedContent, 1);
                
                if ($beforeReplace !== $modifiedContent) {
                    $replacementSuccess = true;
                    $replacementMethod = 'regex_escaped';
                }
            }
            
            // Method 3: Source-based replacement (find by src attribute and replace entire tag)
            if (!$replacementSuccess && !empty($originalSrc)) {
                $srcPattern = preg_quote($originalSrc, '/');
                $beforeReplace = $modifiedContent;
                
                // Find img tag with this src and replace it
                $pattern = '/<img[^>]*src\s*=\s*["\']' . $srcPattern . '["\'][^>]*>/i';
                $modifiedContent = preg_replace($pattern, $correctedImg, $modifiedContent, 1);
                
                if ($beforeReplace !== $modifiedContent) {
                    $replacementSuccess = true;
                    $replacementMethod = 'src_based';
                }
            }
            
            // Method 4: Flexible src matching (handle relative/absolute path variations)
            if (!$replacementSuccess && !empty($originalSrc)) {
                $srcBasename = basename($originalSrc);
                if (!empty($srcBasename)) {
                    $beforeReplace = $modifiedContent;
                    
                    // Find img tag with src ending with this basename
                    $pattern = '/<img[^>]*src\s*=\s*["\'][^"\']*' . preg_quote($srcBasename, '/') . '["\'][^>]*>/i';
                    $modifiedContent = preg_replace($pattern, $correctedImg, $modifiedContent, 1);
                    
                    if ($beforeReplace !== $modifiedContent) {
                        $replacementSuccess = true;
                        $replacementMethod = 'basename_match';
                    }
                }
            }
            
            // Log the replacement result
            if ($replacementSuccess) {
                $imagesFixed++;
                $replacementLog[] = [
                    'index' => $index,
                    'original_src' => $originalSrc,
                    'replaced' => true,
                    'method' => $replacementMethod
                ];
            } else {
                $replacementLog[] = [
                    'index' => $index,
                    'original_src' => $originalSrc,
                    'replaced' => false,
                    'reason' => 'All replacement methods failed',
                    'original_tag_length' => strlen($originalImg),
                    'corrected_tag_length' => strlen($correctedImg)
                ];
                
                // Log detailed info for debugging
                error_log('JoomlaHits Alt Fix: Failed to replace image - Original: ' . substr($originalImg, 0, 100) . '...');
                error_log('JoomlaHits Alt Fix: Failed to replace image - Corrected: ' . substr($correctedImg, 0, 100) . '...');
            }
        }
    }
    
    // ENHANCED ITERATIVE PROCESSING: Continue processing until all images are fixed or max attempts reached
    $maxPasses = 3;
    $currentPass = 1;
    $remainingProblematicImages = extractProblematicImages($modifiedContent);
    
    while (!empty($remainingProblematicImages) && 
           count($remainingProblematicImages) < count($problematicImages) && 
           $currentPass < $maxPasses) {
        
        $currentPass++;
        error_log('JoomlaHits Alt Fix: Pass ' . $currentPass . ' needed for article ' . $articleId . '. Remaining: ' . count($remainingProblematicImages));
        
        $passPrompt = createTargetedImagePrompt($remainingProblematicImages, $article, $params);
        
        try {
            $passAiResponse = $aiProvider->generateContent($passPrompt);
            $passCorrectedImages = parseAIImageResponse($passAiResponse, $remainingProblematicImages);
            
            $passImagesFixed = 0;
            
            // Use the same enhanced replacement logic for additional passes
            foreach ($passCorrectedImages as $index => $correctedImg) {
                if (isset($remainingProblematicImages[$index])) {
                    $originalImg = $remainingProblematicImages[$index]['tag'];
                    $originalSrc = $remainingProblematicImages[$index]['src'];
                    
                    $replacementSuccess = false;
                    $replacementMethod = '';
                    
                    // Method 1: Direct string replacement
                    if (!$replacementSuccess) {
                        $beforeReplace = $modifiedContent;
                        $modifiedContent = str_replace($originalImg, $correctedImg, $modifiedContent);
                        
                        if ($beforeReplace !== $modifiedContent) {
                            $replacementSuccess = true;
                            $replacementMethod = 'direct_string';
                        }
                    }
                    
                    // Method 2: Regex replacement
                    if (!$replacementSuccess) {
                        $escapedOriginal = preg_quote($originalImg, '/');
                        $beforeReplace = $modifiedContent;
                        $modifiedContent = preg_replace('/' . $escapedOriginal . '/', $correctedImg, $modifiedContent, 1);
                        
                        if ($beforeReplace !== $modifiedContent) {
                            $replacementSuccess = true;
                            $replacementMethod = 'regex_escaped';
                        }
                    }
                    
                    // Method 3: Source-based replacement
                    if (!$replacementSuccess && !empty($originalSrc)) {
                        $srcPattern = preg_quote($originalSrc, '/');
                        $beforeReplace = $modifiedContent;
                        $pattern = '/<img[^>]*src\s*=\s*["\']' . $srcPattern . '["\'][^>]*>/i';
                        $modifiedContent = preg_replace($pattern, $correctedImg, $modifiedContent, 1);
                        
                        if ($beforeReplace !== $modifiedContent) {
                            $replacementSuccess = true;
                            $replacementMethod = 'src_based';
                        }
                    }
                    
                    if ($replacementSuccess) {
                        $imagesFixed++;
                        $passImagesFixed++;
                        $replacementLog[] = [
                            'index' => 'pass_' . $currentPass . '_' . $index,
                            'original_src' => $originalSrc,
                            'replaced' => true,
                            'pass' => $currentPass,
                            'method' => $replacementMethod
                        ];
                    }
                }
            }
            
            // If no images were fixed in this pass, break to avoid infinite loop
            if ($passImagesFixed === 0) {
                error_log('JoomlaHits Alt Fix: Pass ' . $currentPass . ' fixed 0 images, stopping iteration');
                break;
            }
            
            // Update remaining problematic images for next iteration
            $previousCount = count($remainingProblematicImages);
            $remainingProblematicImages = extractProblematicImages($modifiedContent);
            
            // If count didn't decrease, break to avoid infinite loop
            if (count($remainingProblematicImages) >= $previousCount) {
                error_log('JoomlaHits Alt Fix: Pass ' . $currentPass . ' did not reduce problematic images count, stopping iteration');
                break;
            }
            
        } catch (Exception $e) {
            error_log('JoomlaHits Alt Fix: Pass ' . $currentPass . ' failed for article ' . $articleId . ': ' . $e->getMessage());
            break;
        }
    }
    
    // Log final iteration results
    if ($currentPass > 1) {
        error_log('JoomlaHits Alt Fix: Completed ' . $currentPass . ' passes for article ' . $articleId . '. Final remaining: ' . count($remainingProblematicImages));
    }
    
    // Final verification
    $finalProblematicImages = extractProblematicImages($modifiedContent);
    $finalImageCount = count($finalProblematicImages);
    $originalImageCount = count($problematicImages);
    
    echo json_encode([
        'success' => true,
        'message' => 'Image alt attributes fixed successfully by ' . $aiProvider->getProvider(),
        'article_id' => $articleId,
        'images_fixed' => $imagesFixed,
        'modified_content' => $modifiedContent,
        'ai_provider' => $aiProvider->getProvider(),
        'original_problematic_count' => $originalImageCount,
        'remaining_problematic_count' => $finalImageCount,
        'complete_success' => ($finalImageCount === 0),
        'replacement_log' => $replacementLog,
        'passes_completed' => $currentPass,
        'processing_details' => [
            'max_passes_allowed' => $maxPasses,
            'iterative_processing' => $currentPass > 1,
            'all_images_fixed' => ($finalImageCount === 0)
        ]
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    // Handle general exceptions
    echo json_encode(['success' => false, 'message' => 'Unexpected error: ' . $e->getMessage()]);
} catch (Error $e) {
    // Handle fatal errors
    echo json_encode(['success' => false, 'message' => 'Fatal error: ' . $e->getMessage()]);
}

/**
 * Extract img tags that have missing or empty alt attributes
 */
function extractProblematicImages($content) {
    $problematicImages = [];
    
    // Find all img tags using regex
    preg_match_all('/<img[^>]*>/i', $content, $matches);
    
    foreach ($matches[0] as $imgTag) {
        $hasAlt = preg_match('/alt\s*=\s*["\'][^"\']*["\']/', $imgTag);
        $hasEmptyAlt = preg_match('/alt\s*=\s*["\']["\']/', $imgTag);
        
        // Check if img tag has no alt attribute or has empty alt attribute
        if (!$hasAlt || $hasEmptyAlt) {
            // Extract src attribute for context
            $src = '';
            if (preg_match('/src\s*=\s*["\']([^"\']*)["\']/', $imgTag, $srcMatches)) {
                $src = $srcMatches[1];
            }
            
            $problematicImages[] = [
                'tag' => $imgTag,
                'src' => $src,
                'issue' => !$hasAlt ? 'missing_alt' : 'empty_alt'
            ];
        }
    }
    
    return $problematicImages;
}

/**
 * Create targeted prompt for fixing only image alt attributes
 */
function createTargetedImagePrompt($problematicImages, $article, $params = []) {
    $title = trim(html_entity_decode($article->title, ENT_QUOTES, 'UTF-8'));
    $metadesc = trim(html_entity_decode($article->metadesc, ENT_QUOTES, 'UTF-8'));
    $language = $article->language ?: 'fr-FR';
    
    // Use configurable prompt template if available
    if (!empty($params['ai_prompt_image'])) {
        $prompt = $params['ai_prompt_image'];
        
        // Prepare images list for the prompt
        $imagesList = '';
        foreach ($problematicImages as $index => $imgData) {
            $imagesList .= ($index + 1) . ". " . $imgData['tag'] . "\n";
        }
        
        // Replace placeholders in the configurable prompt
        $prompt = str_replace('{title}', $title, $prompt);
        $prompt = str_replace('{metadesc}', $metadesc, $prompt);
        $prompt = str_replace('{language}', $language, $prompt);
        $prompt = str_replace('{images}', $imagesList, $prompt);
        
        return $prompt;
    }
    
    // Fallback to default prompt if no custom prompt is configured
    $prompt = "SYSTÈME : Tu es un expert en accessibilité web et SEO. Ta mission est de corriger UNIQUEMENT les attributs alt manquants ou vides dans les balises img.\n\n";
    
    $prompt .= "INSTRUCTIONS STRICTES :\n";
    $prompt .= "1. Tu recevras une liste de balises <img> qui ont des attributs alt manquants ou vides\n";
    $prompt .= "2. Pour chaque balise, génère un attribut alt descriptif et pertinent\n";
    $prompt .= "3. Base-toi sur le titre de l'article et la description meta pour le contexte\n";
    $prompt .= "4. Retourne UNIQUEMENT les balises <img> corrigées, une par ligne\n";
    $prompt .= "5. Préserve tous les autres attributs existants (src, class, style, etc.)\n";
    $prompt .= "6. L'attribut alt doit être descriptif mais concis (50-125 caractères)\n\n";
    
    $prompt .= "CONTEXTE DE L'ARTICLE :\n";
    $prompt .= "Titre : " . $title . "\n";
    if (!empty($metadesc)) {
        $prompt .= "Description : " . $metadesc . "\n";
    }
    $prompt .= "Langue : " . $language . "\n\n";
    
    $prompt .= "BALISES IMG À CORRIGER :\n\n";
    
    foreach ($problematicImages as $index => $imgData) {
        $prompt .= ($index + 1) . ". " . $imgData['tag'] . "\n";
    }
    
    $prompt .= "\nRéponds avec les balises corrigées dans le même ordre, une par ligne :";
    
    return $prompt;
}

/**
 * Parse AI response to extract corrected img tags with enhanced reliability
 */
function parseAIImageResponse($aiResponse, $originalImages) {
    $correctedImages = [];
    
    // Remove potential code block markers more thoroughly
    $aiResponse = preg_replace('/^```[a-zA-Z]*\s*/m', '', $aiResponse);
    $aiResponse = preg_replace('/\s*```$/m', '', $aiResponse);
    $aiResponse = trim($aiResponse);
    
    // Enhanced Method 1: Try line-by-line parsing first with better numbering detection
    $lines = explode("\n", $aiResponse);
    
    foreach ($lines as $line) {
        $line = trim($line);
        
        // Skip empty lines
        if (empty($line)) continue;
        
        // Remove various numbering patterns more comprehensively
        $line = preg_replace('/^(\d+[\.\)\-\s]+|[•\-\*\+\>]+\s*|[a-zA-Z][\.\)]\s*)/i', '', $line);
        $line = trim($line);
        
        // Check if line contains an img tag with more flexible matching
        if (preg_match('/<img[^>]*>/i', $line, $matches)) {
            $imgTag = $matches[0];
            // Validate that this is actually a complete img tag
            if (substr_count($imgTag, '<') === 1 && substr_count($imgTag, '>') === 1) {
                $correctedImages[] = $imgTag;
            }
        }
    }
    
    // Method 2: If line-by-line didn't get enough images, try global extraction with validation
    if (count($correctedImages) < count($originalImages)) {
        preg_match_all('/<img[^>]*>/i', $aiResponse, $allMatches);
        foreach ($allMatches[0] as $imgTag) {
            // Validate that this is a complete img tag
            if (substr_count($imgTag, '<') === 1 && substr_count($imgTag, '>') === 1) {
                if (!in_array($imgTag, $correctedImages)) {
                    $correctedImages[] = $imgTag;
                }
            }
        }
    }
    
    // Method 3: Enhanced flexible parsing with better normalization
    if (count($correctedImages) < count($originalImages)) {
        // Normalize spacing and try to find img tags that might be split across lines
        $normalizedResponse = preg_replace('/\s+/', ' ', $aiResponse);
        $normalizedResponse = str_replace(array("\n", "\r"), ' ', $normalizedResponse);
        
        preg_match_all('/<img[^>]*>/i', $normalizedResponse, $flexibleMatches);
        
        foreach ($flexibleMatches[0] as $imgTag) {
            if (substr_count($imgTag, '<') === 1 && substr_count($imgTag, '>') === 1) {
                if (!in_array($imgTag, $correctedImages)) {
                    $correctedImages[] = $imgTag;
                }
            }
        }
    }
    
    // Method 4: Try to reconstruct img tags from partial matches
    if (count($correctedImages) < count($originalImages)) {
        // Look for src attributes that might indicate partial img tags
        preg_match_all('/src\s*=\s*["\'][^"\']*["\'][^>]*alt\s*=\s*["\'][^"\']*["\']|alt\s*=\s*["\'][^"\']*["\'][^>]*src\s*=\s*["\'][^"\']*["\']/', $aiResponse, $partialMatches);
        
        foreach ($partialMatches[0] as $partial) {
            // Try to construct a complete img tag from partial matches
            if (strpos($partial, '<img') === false) {
                $reconstructed = '<img ' . $partial . ' />';
                if (!in_array($reconstructed, $correctedImages)) {
                    $correctedImages[] = $reconstructed;
                }
            }
        }
    }
    
    // Log parsing results for debugging with more detail
    error_log('JoomlaHits Alt Fix: AI response parsing - Expected: ' . count($originalImages) . ', Found: ' . count($correctedImages) . ', Response length: ' . strlen($aiResponse));
    
    // Ensure we don't have more corrected images than original problematic ones
    if (count($correctedImages) > count($originalImages)) {
        $correctedImages = array_slice($correctedImages, 0, count($originalImages));
    }
    
    return $correctedImages;
}