<?php
/*-----------------------------------------------------------------------------------------------------/
	@version		1.2.0
	@build			29th July, 2025
	@created		29th July, 2025
	@package		JoomlaHits
	@subpackage		direct_ai_seo_fix.php
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
$fieldType = $_POST['field_type'] ?? '';

if (!$articleId) {
    echo json_encode(['success' => false, 'message' => 'Invalid article ID']);
    exit;
}

if (!$fieldType || !in_array($fieldType, ['title', 'metadesc', 'metakey', 'content'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid field type']);
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

// Initialize default configuration values
$minTitleLength = 30;
$maxTitleLength = 60;
$minMetaLength = 120;
$maxMetaLength = 160;
$minUrlLength = 5;
$maxUrlLength = 50;
$minContentLength = 300;

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
    $promptTemplates = [];
    
    if ($extension && !empty($extension->params)) {
        $params = json_decode($extension->params, true);
        
        // Load SEO parameters
        $minTitleLength = isset($params['seo_min_title_length']) ? intval($params['seo_min_title_length']) : 30;
        $maxTitleLength = isset($params['seo_max_title_length']) ? intval($params['seo_max_title_length']) : 60;
        $minMetaLength = isset($params['seo_min_meta_length']) ? intval($params['seo_min_meta_length']) : 120;
        $maxMetaLength = isset($params['seo_max_meta_length']) ? intval($params['seo_max_meta_length']) : 160;
        $minUrlLength = isset($params['seo_min_url_length']) ? intval($params['seo_min_url_length']) : 5;
        $maxUrlLength = isset($params['seo_max_url_length']) ? intval($params['seo_max_url_length']) : 50;
        $minContentLength = isset($params['seo_min_content_length']) ? intval($params['seo_min_content_length']) : 300;
        
        // Load custom AI prompts
        $promptTemplates['title'] = $params['ai_prompt_title'] ?? '';
        $promptTemplates['metadesc'] = $params['ai_prompt_metadesc'] ?? '';
        $promptTemplates['metakey'] = $params['ai_prompt_metakey'] ?? '';
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
    
    // Check if we should skip metakey field based on existing content
    if ($fieldType === 'metakey' && !empty($article->metakey)) {
        // Count the number of keywords (separated by commas)
        $keywords = array_filter(array_map('trim', explode(',', $article->metakey)));
        $keywordCount = count($keywords);
        
        // If we have 3 or more keywords, consider it optimal and skip
        if ($keywordCount >= 3) {
            echo json_encode([
                'success' => true,
                'message' => 'Meta keywords already optimal (' . $keywordCount . ' keywords)',
                'article_id' => $articleId,
                'field_type' => $fieldType,
                'field_value' => $article->metakey,
                'skipped' => true
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
    
    // Clean and prepare article content for AI processing
    $cleanTitle = trim(html_entity_decode($article->title, ENT_QUOTES, 'UTF-8'));
    $cleanContent = strip_tags($article->introtext . ' ' . $article->fulltext);
    $cleanContent = html_entity_decode($cleanContent, ENT_QUOTES, 'UTF-8');
    $cleanContent = preg_replace('/\s+/', ' ', trim($cleanContent));
    
    // Remove any remaining special characters that might cause issues
    $cleanContent = preg_replace('/[^\p{L}\p{N}\s\-.,!?()]/u', '', $cleanContent);
    
    // Limit content length to avoid API token limits
    if (mb_strlen($cleanContent, 'UTF-8') > 800) {
        $cleanContent = mb_substr($cleanContent, 0, 800, 'UTF-8') . '...';
    }
    
    // Ensure we have valid content
    if (empty($cleanContent)) {
        $cleanContent = $cleanTitle;
    }
    
    // Create field-specific prompt with SEO parameters
    $prompt = generateFieldPrompt($fieldType, $cleanTitle, $cleanContent, $article, $minTitleLength, $maxTitleLength, $minMetaLength, $maxMetaLength, $minUrlLength, $maxUrlLength, $minContentLength, $promptTemplates);
    
    // Generate content using AI provider
    try {
        $generatedContent = $aiProvider->generateContent($prompt);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'AI generation error: ' . $e->getMessage()
        ]);
        exit;
    }
    
    // Remove potential code block markers
    $generatedContent = preg_replace('/^```json\s*/', '', $generatedContent);
    $generatedContent = preg_replace('/^```html\s*/', '', $generatedContent);
    $generatedContent = preg_replace('/^```\s*/', '', $generatedContent);
    $generatedContent = preg_replace('/\s*```$/', '', $generatedContent);
    
    // Handle content field differently from other fields
    if ($fieldType === 'content') {
        $cleanedValue = trim($generatedContent);
        
        echo json_encode([
            'success' => true,
            'message' => 'Contenu modifié avec succès par ' . $aiProvider->getProvider(),
            'article_id' => $articleId,
            'field_type' => $fieldType,
            'modified_content' => $cleanedValue,
            'ai_provider' => $aiProvider->getProvider()
        ], JSON_UNESCAPED_UNICODE);
    } else {
        // Handle other fields normally (title, metadesc, metakey)
        $generatedValue = trim($generatedContent);
        // Remove quotes if present
        $generatedValue = trim($generatedValue, '"\'');
        
        // Clean the generated value based on field type
        $cleanedValue = cleanFieldValue($fieldType, $generatedValue, $maxTitleLength, $maxMetaLength, $maxUrlLength);
        
        echo json_encode([
            'success' => true,
            'message' => ucfirst($fieldType) . ' optimisé avec succès par ' . $aiProvider->getProvider(),
            'article_id' => $articleId,
            'field_type' => $fieldType,
            'field_value' => $cleanedValue,
            'ai_provider' => $aiProvider->getProvider()
        ], JSON_UNESCAPED_UNICODE);
    }
    
} catch (Exception $e) {
    // Handle general exceptions
    echo json_encode(['success' => false, 'message' => 'Unexpected error: ' . $e->getMessage()]);
} catch (Error $e) {
    // Handle fatal errors
    echo json_encode(['success' => false, 'message' => 'Fatal error: ' . $e->getMessage()]);
}

/**
 * Generate field-specific prompts for AI
 */
function generateFieldPrompt($fieldType, $title, $content, $article, $minTitleLength, $maxTitleLength, $minMetaLength, $maxMetaLength, $minUrlLength, $maxUrlLength, $minContentLength, $promptTemplates) {
    $language = $article->language ?: 'fr-FR';
    $contentSnippet = mb_substr($content, 0, 400, 'UTF-8');
    
    // Default prompts if not configured
    $defaultPrompts = [
        'title' => "Tu es un expert SEO. Génère UNIQUEMENT un titre optimisé SEO pour cet article. " .
                   "Règles strictes : entre {minTitleLength}-{maxTitleLength} caractères, accrocheur, avec mots-clés principaux. " .
                   "Réponds UNIQUEMENT avec le titre, sans guillemets ni explication.\n\n" .
                   "Titre actuel : {title}\n" .
                   "Contenu : {content}\n" .
                   "Langue : {language}",
                   
        'metadesc' => "Tu es un expert SEO. Génère UNIQUEMENT une meta description optimisée pour cet article. " .
                      "Règles strictes : entre {minMetaLength}-{maxMetaLength} caractères, incitative au clic, résume le contenu. " .
                      "Réponds UNIQUEMENT avec la meta description, sans guillemets ni explication.\n\n" .
                      "Titre : {title}\n" .
                      "Contenu : {content}\n" .
                      "Langue : {language}",
                      
        'metakey' => "Tu es un expert SEO. Génère UNIQUEMENT des mots-clés meta pour cet article. " .
                     "Règles strictes : 5-8 mots-clés pertinents, séparés par des virgules. " .
                     "Réponds UNIQUEMENT avec la liste de mots-clés, sans guillemets ni explication.\n\n" .
                     "Titre : {title}\n" .
                     "Contenu : {content}\n" .
                     "Langue : {language}",
                     
        'content' => "recopie ce texte à l'identique en ajoutant l'attribut alt à la / aux balises img que tu trouve et en la remplissant si la balise est déjà présente remplie la simplement Et au début du texte ajoute une balise H1 avec un titre en te basant sur le contenu uniquement si tu ne trouve pas d'autre H1 dans le texte Je veux uniquement que tu réponde par le texte corrigé ajoute aucun message de ta part dans la réponse et ajoute aussi cette balise entre l'intro text (qui le premier paragraphe / balise avec une ligne vide) et le full texte <hr id='system-readmore'>"
    ];
    
    // Get the prompt template (use default if not configured)
    $promptTemplate = (!empty($promptTemplates[$fieldType])) ? $promptTemplates[$fieldType] : $defaultPrompts[$fieldType];
    
    // Replace variables in the prompt template
    $replacements = [
        '{title}' => $title,
        '{content}' => $contentSnippet,
        '{language}' => $language,
        '{minTitleLength}' => $minTitleLength,
        '{maxTitleLength}' => $maxTitleLength,
        '{minMetaLength}' => $minMetaLength,
        '{maxMetaLength}' => $maxMetaLength,
        '{minUrlLength}' => $minUrlLength,
        '{maxUrlLength}' => $maxUrlLength,
        '{minContentLength}' => $minContentLength,
        '{metadesc}' => trim(html_entity_decode($article->metadesc, ENT_QUOTES, 'UTF-8')),
        '{fullContent}' => $article->introtext . ' ' . $article->fulltext
    ];
    
    $prompt = str_replace(array_keys($replacements), array_values($replacements), $promptTemplate);
    
    return $prompt;
}

/**
 * Clean field values based on type
 */
function cleanFieldValue($fieldType, $value, $maxTitleLength, $maxMetaLength, $maxUrlLength) {
    switch ($fieldType) {
        case 'title':
            return mb_substr(trim($value), 0, $maxTitleLength, 'UTF-8');
            
        case 'metadesc':
            return mb_substr(trim($value), 0, $maxMetaLength, 'UTF-8');
            
        case 'metakey':
            return trim($value);
            
        default:
            return trim($value);
    }
}