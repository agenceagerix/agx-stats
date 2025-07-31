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
    
    // Load AI provider configuration
    require_once __DIR__ . '/ai_provider.php';
    
    // Retrieve component configuration
    $extensionQuery = $db->prepare("
        SELECT params 
        FROM " . $config->dbprefix . "extensions 
        WHERE element = 'com_joomlahits' AND type = 'component' AND client_id = 1
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
    
    // Clean and prepare article content for AI processing
    $cleanTitle = trim(html_entity_decode($article->title, ENT_QUOTES, 'UTF-8'));
    $cleanIntrotext = strip_tags($article->introtext);
    $cleanIntrotext = html_entity_decode($cleanIntrotext, ENT_QUOTES, 'UTF-8');
    $cleanIntrotext = preg_replace('/\s+/', ' ', trim($cleanIntrotext));
    
    // Remove any remaining special characters that might cause issues
    $cleanIntrotext = preg_replace('/[^\p{L}\p{N}\s\-.,!?()]/u', '', $cleanIntrotext);
    
    // Limit introtext length to avoid API token limits
    if (mb_strlen($cleanIntrotext, 'UTF-8') > 500) {
        $cleanIntrotext = mb_substr($cleanIntrotext, 0, 500, 'UTF-8') . '...';
    }
    
    // Ensure we have valid content
    if (empty($cleanIntrotext)) {
        $cleanIntrotext = $cleanTitle;
    }
    
    // Get custom prompt from configuration or use default
    $defaultPrompt = "Generate a compelling SEO meta description of exactly 185 characters or less for this article. " .
                     "The meta description should be engaging, include relevant keywords, and encourage clicks. " .
                     "Do not include quotes or special formatting. Just return the meta description text. " .
                     "Write the meta description in the same language as the article (language code: {language}).\n\n" .
                     "Article Title: {title}\n" .
                     "Article Introduction: {introtext}";
    
    $customPrompt = $params['mistral_prompt'] ?? $defaultPrompt;
    
    // Replace placeholders with actual content
    $prompt = str_replace(['{title}', '{introtext}', '{language}'], [$cleanTitle, $cleanIntrotext, $article->language ?: 'en-GB'], $customPrompt);
    
    // Validate prompt content before sending to API
    if (empty($prompt) || mb_strlen($prompt, 'UTF-8') < 10) {
        echo json_encode(['success' => false, 'message' => 'Invalid prompt content for article "' . $article->title . '"']);
        exit;
    }
    
    // Generate content using AI provider
    try {
        $generatedMetaDesc = $aiProvider->generateContent($prompt);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'AI generation error: ' . $e->getMessage(),
            'debug_info' => [
                'article_id' => $articleId,
                'title' => $article->title,
                'clean_introtext_length' => mb_strlen($cleanIntrotext, 'UTF-8'),
                'ai_provider' => $aiProvider->getProvider()
            ]
        ]);
        exit;
    }
    
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
        'message' => 'Meta description generated successfully by ' . $aiProvider->getProvider() . ' for "' . $article->title . '"',
        'article_id' => $articleId,
        'title' => $article->title,
        'metadesc' => $generatedMetaDesc,
        'ai_provider' => $aiProvider->getProvider()
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    // Handle general exceptions
    echo json_encode(['success' => false, 'message' => 'Unexpected error: ' . $e->getMessage()]);
} catch (Error $e) {
    // Handle fatal errors
    echo json_encode(['success' => false, 'message' => 'Fatal error: ' . $e->getMessage()]);
}