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
      __    ___  ____  __ _   ___  ____     __    ___  ____  ____  __  _  _
	 / _\  / __)(  __)(  ( \ / __)(  __)   / _\  / __)(  __)(  _ \(  )( \/ )
	/    \( (_ \ ) _) /    /( (__  ) _)   /    \( (_ \ ) _)  )   / )(  )  (
	\_/\_/ \___/(____)\_)__) \___)(____)  \_/\_/ \___/(____)(__\_)(__)(_/\_)
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
    
    // Content field with introtext/fulltext splitting
    if (isset($_POST['content'])) {
        $content = $_POST['content'];
        $readmorePattern = '/<hr\s+id\s*=\s*["\']system-readmore["\'][^>]*>/i';
        
        if (preg_match($readmorePattern, $content)) {
            // Content has a readmore separator - split into introtext and fulltext
            $parts = preg_split($readmorePattern, $content, 2);
            $introtext = trim($parts[0]);
            $fulltext = isset($parts[1]) ? trim($parts[1]) : '';
            
            $fields[] = '`introtext` = :introtext';
            $fields[] = '`fulltext` = :fulltext';
        } else {
            // No readmore separator - put all content in introtext, clear fulltext
            $fields[] = '`introtext` = :introtext';
            $fields[] = '`fulltext` = :fulltext';
        }
    }
    
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
        
        // Content field binding
        if (isset($_POST['content'])) {
            $content = $_POST['content'];
            $readmorePattern = '/<hr\s+id\s*=\s*["\']system-readmore["\'][^>]*>/i';
            
            if (preg_match($readmorePattern, $content)) {
                // Split content for readmore
                $parts = preg_split($readmorePattern, $content, 2);
                $introtext = trim($parts[0]);
                $fulltext = isset($parts[1]) ? trim($parts[1]) : '';
                
                $stmt->bindParam(':introtext', $introtext, PDO::PARAM_STR);
                $stmt->bindParam(':fulltext', $fulltext, PDO::PARAM_STR);
            } else {
                // No readmore - all content goes to introtext
                $stmt->bindParam(':introtext', $content, PDO::PARAM_STR);
                $fulltext = '';
                $stmt->bindParam(':fulltext', $fulltext, PDO::PARAM_STR);
            }
        }
        
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