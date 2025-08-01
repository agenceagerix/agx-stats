<?php
/*-----------------------------------------------------------------------------------------------------/
	@version		1.2.0
	@build			29th July, 2025
	@created		29th July, 2025
	@package		JoomlaHits
	@subpackage		direct_seo_fix.php
	@author			Hugo Dantas - Agence Agerix <https://www.agerix.fr>
	@copyright		Copyright (C) 2025. All Rights Reserved
	@license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html
/------------------------------------------------------------------------------------------------------*/

// Initialize Joomla framework
define('_JEXEC', 1);

// Define path constants
define('JPATH_BASE', realpath(dirname(__FILE__) . '/../../..'));

// Verify Joomla configuration file exists
if (!file_exists(JPATH_BASE . '/configuration.php')) {
    echo json_encode(['success' => false, 'message' => 'Joomla configuration.php not found']);
    exit;
}

// Load Joomla configuration
require_once JPATH_BASE . '/configuration.php';
$config = new JConfig();

// Set headers for JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Check for valid request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get article ID
$articleId = isset($_POST['article_id']) ? (int)$_POST['article_id'] : 0;

if (!$articleId) {
    echo json_encode(['success' => false, 'message' => 'Invalid article ID']);
    exit;
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
    
    // Prepare update data
    $fields = [];
    
    // Title
    if (isset($_POST['title']) && !empty($_POST['title'])) {
        $fields[] = '`title` = :title';
    }
    
    // Meta description
    if (isset($_POST['metadesc'])) {
        $fields[] = '`metadesc` = :metadesc';
    }
    
    // Meta keywords
    if (isset($_POST['metakey'])) {
        $fields[] = '`metakey` = :metakey';
    }
    
    // Note: Content fields (introtext/fulltext) are not modified to preserve original structure
    
    // Update article if there are fields to update
    if (!empty($fields)) {
        $query = "UPDATE " . $config->dbprefix . "content SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $db->prepare($query);
        
        // Bind parameters
        $stmt->bindParam(':id', $articleId, PDO::PARAM_INT);
        
        if (isset($_POST['title']) && !empty($_POST['title'])) {
            $stmt->bindParam(':title', $_POST['title'], PDO::PARAM_STR);
        }
        
        if (isset($_POST['metadesc'])) {
            $stmt->bindParam(':metadesc', $_POST['metadesc'], PDO::PARAM_STR);
        }
        
        if (isset($_POST['metakey'])) {
            $stmt->bindParam(':metakey', $_POST['metakey'], PDO::PARAM_STR);
        }
        
        // Note: Content binding removed to preserve introtext/fulltext separation
        
        $result = $stmt->execute();
        
        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'SEO fields updated successfully',
                'data' => [
                    'article_id' => $articleId,
                    'updated_fields' => array_keys($_POST)
                ]
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to update article'
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No fields to update'
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}