<?php
/*-----------------------------------------------------------------------------------------------------/
	@version		1.2.0
	@build			29th July, 2025
	@created		29th July, 2025
	@package		JoomlaHits
	@subpackage		SeoanalysisController.php
	@author			Hugo Dantas - Agence Agerix <https://www.agerix.fr>
	@copyright		Copyright (C) 2025. All Rights Reserved
	@license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html
/------------------------------------------------------------------------------------------------------*/
namespace Joomla\Component\JoomlaHits\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Response\JsonResponse;

/**
 * SEO Analysis controller.
 */
class SeoanalysisController extends BaseController
{
    /**
     * The prefix to use with controller messages.
     *
     * @var    string
     */
    protected $text_prefix = 'COM_JOOMLAHITS_SEOANALYSIS';

    /**
     * The default view.
     *
     * @var    string
     */
    protected $default_view = 'seoanalysis';

    /**
     * AJAX method to analyze all articles for SEO issues
     */
    public function analyze()
    {
        // Set JSON headers
        header('Content-Type: application/json; charset=utf-8');
        
        // Check for request forgeries
        try {
            $this->checkToken();
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Token de sécurité invalide'
            ]);
            Factory::getApplication()->close();
            return;
        }

        try {
            // Use the same logic as direct_ai_metadesc.php for database access
            $db = Factory::getDbo();
            $query = $db->getQuery(true);

            // Get all published articles with SEO issues
            $query->select([
                'a.id',
                'a.title',
                'a.alias',
                'a.introtext',
                'a.fulltext',
                'a.metadesc',
                'a.metakey',
                'a.images',
                'a.hits',
                'a.language',
                'a.state',
                'c.title AS category_title'
            ])
            ->from($db->quoteName('#__content', 'a'))
            ->join('LEFT', $db->quoteName('#__categories', 'c') . ' ON c.id = a.catid')
            ->where($db->quoteName('a.state') . ' = 1')
            ->order($db->quoteName('a.title'));

            $db->setQuery($query);
            $articles = $db->loadObjectList();

            $results = $this->analyzeArticles($articles);
            
            echo json_encode([
                'success' => true,
                'data' => $results,
                'message' => 'Analyse SEO terminée avec succès'
            ], JSON_UNESCAPED_UNICODE);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Erreur lors de l\'analyse SEO: ' . $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }

        Factory::getApplication()->close();
    }

    /**
     * Analyze articles for SEO issues
     */
    private function analyzeArticles($articles)
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
            $articleIssues = $this->analyzeArticle($article);
            
            if (!empty($articleIssues['issues'])) {
                $results['issues'][] = [
                    'id' => $article->id,
                    'title' => $article->title,
                    'category' => $article->category_title,
                    'language' => $article->language,
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
    private function analyzeArticle($article)
    {
        $issues = [];
        $categories = [];
        $maxSeverity = 'info';

        // 1. Title Analysis
        $titleLength = mb_strlen($article->title);
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
        $metaDescLength = mb_strlen($article->metadesc);
        if (empty($article->metadesc)) {
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
        $contentLength = mb_strlen(strip_tags($content));
        
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

        return [
            'issues' => $issues,
            'severity' => $maxSeverity,
            'categories' => array_unique($categories)
        ];
    }
}