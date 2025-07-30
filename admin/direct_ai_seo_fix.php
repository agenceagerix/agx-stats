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

if (!$fieldType || !in_array($fieldType, ['title', 'metadesc', 'metakey', 'alias'])) {
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
    
    // Retrieve Mistral AI API key from component configuration
    $extensionQuery = $db->prepare("
        SELECT `params`
        FROM " . $config->dbprefix . "extensions 
        WHERE `element` = 'com_joomlahits' AND `type` = 'component' AND `client_id` = 1
    ");
    $extensionQuery->execute();
    $extension = $extensionQuery->fetch();
    
    // Parse component parameters and extract API key and SEO settings
    $apiKey = getenv('MISTRAL_API_KEY');
    $promptTemplates = [];
    
    if (!$apiKey && $extension && !empty($extension->params)) {
        $params = json_decode($extension->params, true);
        $apiKey = $params['mistral_api_key'] ?? '';
        
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
        $promptTemplates['alias'] = $params['ai_prompt_alias'] ?? '';
    }
    
    // Validate API key is configured
    if (empty($apiKey)) {
        echo json_encode([
            'success' => false,
            'message' => 'Mistral API key not configured (set MISTRAL_API_KEY env var or component parameter)'
        ]);
        exit;
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
    
    // Prepare Mistral AI API request
    $url = 'https://api.mistral.ai/v1/chat/completions';
    
    // Create JSON payload for API request
    $payload = json_encode([
        'model' => 'mistral-small-latest',
        'messages' => [
            ['role' => 'user', 'content' => $prompt]
        ],
        'max_tokens' => 1000,
        'temperature' => 0.7
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    
    // Initialize cURL for API communication
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json; charset=utf-8',
        'Authorization: Bearer ' . $apiKey
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 45);
    curl_setopt($ch, CURLOPT_ENCODING, '');
    
    // Execute API request and get response information
    $fullResponse = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    // Check for cURL connection errors
    if ($curlError) {
        echo json_encode(['success' => false, 'message' => 'Connection error: ' . $curlError]);
        exit;
    }
    
    // Separate HTTP headers from response body
    $response = substr($fullResponse, $headerSize);
    
    // Validate HTTP response code
    if ($httpCode !== 200) {
        $errorDetails = '';
        if ($httpCode === 422) {
            $errorDetails = ' - Invalid request data.';
        } elseif ($httpCode === 401) {
            $errorDetails = ' - Invalid API key.';
        } elseif ($httpCode === 429) {
            $errorDetails = ' - Rate limit exceeded. Please try again later.';
        }
        
        echo json_encode([
            'success' => false, 
            'message' => 'Mistral API error: HTTP ' . $httpCode . $errorDetails
        ]);
        exit;
    }
    
    // Clean response and remove BOM if present
    $response = trim($response);
    $response = preg_replace('/^\xEF\xBB\xBF/', '', $response);
    
    // Parse JSON response from API
    $result = json_decode($response, true);
    
    // Validate JSON parsing was successful
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(['success' => false, 'message' => 'Invalid API response']);
        exit;
    }
    
    // Check for API error responses
    if (isset($result['error'])) {
        echo json_encode(['success' => false, 'message' => 'Mistral error: ' . $result['error']['message']]);
        exit;
    }
    
    // Validate expected response structure
    if (!isset($result['choices'][0]['message']['content'])) {
        echo json_encode(['success' => false, 'message' => 'Unexpected response structure']);
        exit;
    }
    
    // Extract generated SEO data from API response
    $generatedContent = trim($result['choices'][0]['message']['content']);
    
    // Remove potential code block markers
    $generatedContent = preg_replace('/^```json\s*/', '', $generatedContent);
    $generatedContent = preg_replace('/\s*```$/', '', $generatedContent);
    
    // Handle all fields normally (content field disabled)
    $generatedValue = trim($generatedContent);
    // Remove quotes if present
    $generatedValue = trim($generatedValue, '"\'');
    
    // Clean the generated value based on field type
    $cleanedValue = cleanFieldValue($fieldType, $generatedValue, $maxTitleLength, $maxMetaLength, $maxUrlLength);
    
    echo json_encode([
        'success' => true,
        'message' => ucfirst($fieldType) . ' optimisé avec succès',
        'article_id' => $articleId,
        'field_type' => $fieldType,
        'field_value' => $cleanedValue
    ], JSON_UNESCAPED_UNICODE);
    
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
                     
        'alias' => "Tu es un expert SEO. Génère UNIQUEMENT un alias URL optimisé pour cet article. " .
                   "Règles strictes : entre {minUrlLength}-{maxUrlLength} caractères, lettres minuscules, tirets uniquement, SEO-friendly. " .
                   "Réponds UNIQUEMENT avec l'alias URL, sans guillemets ni explication.\n\n" .
                   "Titre : {title}\n" .
                   "Langue : {language}"
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
        '{minContentLength}' => $minContentLength
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
            
        case 'alias':
            $alias = strtolower(trim($value));
            $alias = preg_replace('/[^a-z0-9\-]/', '-', $alias);
            $alias = preg_replace('/-+/', '-', $alias);
            $alias = trim($alias, '-');
            return mb_substr($alias, 0, $maxUrlLength, 'UTF-8');
            
        default:
            return trim($value);
    }
}