<?php
/*-----------------------------------------------------------------------------------------------------/
	@version		1.2.0
	@build			28th July, 2025
	@created		28th July, 2025
	@package		JoomlaHits
	@subpackage		default.php
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
    // Using PDO instead of Joomla's database layer to avoid application context issues
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
    // Excludes trashed articles (state != -2)
    $stmt = $db->prepare("
        SELECT id, title, introtext, metadesc, language 
        FROM " . $config->dbprefix . "content 
        WHERE id = :article_id AND state != -2
    ");
    $stmt->bindParam(':article_id', $articleId, PDO::PARAM_INT);
    $stmt->execute();
    $article = $stmt->fetch();
    
    // Validate article exists
    if (!$article) {
        echo json_encode(['success' => false, 'message' => 'Article not found']);
        exit;
    }
    
    // Check if article already has a meta description
    if (!empty($article->metadesc)) {
        echo json_encode(['success' => false, 'message' => 'Article "' . $article->title . '" already has a meta description']);
        exit;
    }
    
    // Retrieve Mistral AI API key from component configuration
    // Access extension parameters directly from database
    $extensionQuery = $db->prepare("
        SELECT params 
        FROM " . $config->dbprefix . "extensions 
        WHERE element = 'com_joomlahits' AND type = 'component' AND client_id = 1
    ");
    $extensionQuery->execute();
    $extension = $extensionQuery->fetch();
    
    // Parse component parameters and extract API key
    $apiKey = getenv('MISTRAL_API_KEY');
    if (!$apiKey && $extension && !empty($extension->params)) {
        $params = json_decode($extension->params, true);
        $apiKey = $params['mistral_api_key'] ?? '';
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
    $cleanTitle = $article->title;
    $cleanIntrotext = strip_tags($article->introtext);
    $cleanIntrotext = preg_replace('/\s+/', ' ', trim($cleanIntrotext));
    
    // Limit introtext length to avoid API token limits
    if (strlen($cleanIntrotext) > 500) {
        $cleanIntrotext = substr($cleanIntrotext, 0, 500) . '...';
    }
    
    // Get custom prompt from configuration or use default
    $defaultPrompt = "Generate a compelling SEO meta description of exactly 185 characters or less for this article. " .
                     "The meta description should be engaging, include relevant keywords, and encourage clicks. " .
                     "Do not include quotes or special formatting. Just return the meta description text.\n\n" .
                     "Article Title: {title}\n" .
                     "Article Introduction: {introtext}";
    
    $customPrompt = $params['mistral_prompt'] ?? $defaultPrompt;
    
    // Replace placeholders with actual content
    $prompt = str_replace(['{title}', '{introtext}'], [$cleanTitle, $cleanIntrotext], $customPrompt);
    
    // Prepare Mistral AI API request
    $url = 'https://api.mistral.ai/v1/chat/completions';
    
    // Create JSON payload for API request
    $payload = json_encode([
        'model' => 'mistral-small-latest',
        'messages' => [
            ['role' => 'user', 'content' => $prompt]
        ],
        'max_tokens' => 200,
        'temperature' => 0.7
    ], JSON_UNESCAPED_UNICODE);
    
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
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
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
        echo json_encode(['success' => false, 'message' => 'Mistral API error: HTTP ' . $httpCode]);
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
    
    // Extract generated meta description from API response
    $generatedMetaDesc = trim($result['choices'][0]['message']['content']);
    
    // Ensure meta description doesn't exceed 185 character limit
    if (strlen($generatedMetaDesc) > 185) {
        $generatedMetaDesc = substr($generatedMetaDesc, 0, 182) . '...';
    }
    
    // Update article with generated meta description in database
    $updateStmt = $db->prepare("
        UPDATE " . $config->dbprefix . "content 
        SET metadesc = :metadesc 
        WHERE id = :article_id
    ");
    $updateStmt->bindParam(':metadesc', $generatedMetaDesc, PDO::PARAM_STR);
    $updateStmt->bindParam(':article_id', $articleId, PDO::PARAM_INT);
    $updateSuccess = $updateStmt->execute();
    
    // Validate database update was successful
    if (!$updateSuccess) {
        echo json_encode(['success' => false, 'message' => 'Database update failed']);
        exit;
    }
    
    // Return success response with generated meta description
    echo json_encode([
        'success' => true,
        'message' => 'Meta description generated successfully for "' . $article->title . '"',
        'article_id' => $articleId,
        'title' => $article->title,
        'metadesc' => $generatedMetaDesc
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    // Handle general exceptions
    echo json_encode(['success' => false, 'message' => 'Unexpected error: ' . $e->getMessage()]);
} catch (Error $e) {
    // Handle fatal errors
    echo json_encode(['success' => false, 'message' => 'Fatal error: ' . $e->getMessage()]);
}