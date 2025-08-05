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
    $fullContent = $article->introtext . ' ' . $article->fulltext;
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
    $prompt = createTargetedImagePrompt($problematicImages, $article);
    
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
    
    // Replace the problematic img tags with corrected ones in the content
    $modifiedContent = $fullContent;
    $imagesFixed = 0;
    
    foreach ($correctedImages as $index => $correctedImg) {
        if (isset($problematicImages[$index])) {
            $originalImg = $problematicImages[$index]['tag'];
            $modifiedContent = str_replace($originalImg, $correctedImg, $modifiedContent);
            $imagesFixed++;
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Image alt attributes fixed successfully by ' . $aiProvider->getProvider(),
        'article_id' => $articleId,
        'images_fixed' => $imagesFixed,
        'modified_content' => $modifiedContent,
        'ai_provider' => $aiProvider->getProvider()
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
function createTargetedImagePrompt($problematicImages, $article) {
    $title = trim(html_entity_decode($article->title, ENT_QUOTES, 'UTF-8'));
    $metadesc = trim(html_entity_decode($article->metadesc, ENT_QUOTES, 'UTF-8'));
    $language = $article->language ?: 'fr-FR';
    
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
 * Parse AI response to extract corrected img tags
 */
function parseAIImageResponse($aiResponse, $originalImages) {
    $correctedImages = [];
    
    // Remove potential code block markers
    $aiResponse = preg_replace('/^```html\s*/', '', $aiResponse);
    $aiResponse = preg_replace('/^```\s*/', '', $aiResponse);
    $aiResponse = preg_replace('/\s*```$/', '', $aiResponse);
    $aiResponse = trim($aiResponse);
    
    // Split response into lines and extract img tags
    $lines = explode("\n", $aiResponse);
    
    foreach ($lines as $line) {
        $line = trim($line);
        
        // Skip empty lines and numbered prefixes
        if (empty($line)) continue;
        
        // Remove numbering (1. 2. etc.)
        $line = preg_replace('/^\d+\.\s*/', '', $line);
        
        // Check if line contains an img tag
        if (preg_match('/<img[^>]*>/i', $line, $matches)) {
            $correctedImages[] = $matches[0];
        }
    }
    
    // If we don't have enough corrected images, try to extract from the whole response
    if (count($correctedImages) < count($originalImages)) {
        preg_match_all('/<img[^>]*>/i', $aiResponse, $allMatches);
        $correctedImages = $allMatches[0];
    }
    
    return $correctedImages;
}