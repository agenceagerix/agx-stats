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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

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
            // Redirect to direct_seo_analysis.php
            $app = Factory::getApplication();
            $app->redirect('components/com_joomlahits/direct_seo_analysis.php');
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Erreur lors de l\'analyse SEO: ' . $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }

        Factory::getApplication()->close();
    }

    /**
     * Bulk AI fix for selected articles
     */
    public function bulkAiFix()
    {
        // Set JSON headers for AJAX response
        header('Content-Type: application/json; charset=utf-8');
        
        // Check for request forgeries
        try {
            $this->checkToken();
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => Text::_('JINVALID_TOKEN')
            ]);
            Factory::getApplication()->close();
            return;
        }
        
        $app = Factory::getApplication();
        $input = $app->input;
        
        // Get selected article IDs
        $cid = $input->get('cid', [], 'array');
        $cid = array_map('intval', $cid);
        
        if (empty($cid)) {
            echo json_encode([
                'success' => false,
                'message' => Text::_('COM_JOOMLAHITS_ERROR_NO_ITEMS_SELECTED')
            ]);
            Factory::getApplication()->close();
            return;
        }
        
        // Return article IDs for JavaScript processing
        echo json_encode([
            'success' => true,
            'message' => Text::sprintf('COM_JOOMLAHITS_BULK_AI_FIX_STARTED', count($cid)),
            'article_ids' => $cid,
            'total_count' => count($cid)
        ]);
        
        Factory::getApplication()->close();
    }
    
    /**
     * Bulk edit for selected articles
     */
    public function bulkEdit()
    {
        // Check for request forgeries
        $this->checkToken();
        
        $app = Factory::getApplication();
        $input = $app->input;
        
        // Get selected article IDs
        $cid = $input->get('cid', [], 'array');
        $cid = array_map('intval', $cid);
        
        if (empty($cid)) {
            $app->enqueueMessage(Text::_('COM_JOOMLAHITS_ERROR_NO_ITEMS_SELECTED'), 'warning');
            $this->setRedirect(Route::_('index.php?option=com_joomlahits&view=seoanalysis', false));
            return;
        }
        
        // Open first article for editing, others will be handled by JavaScript
        $firstId = $cid[0];
        $app->enqueueMessage(Text::sprintf('COM_JOOMLAHITS_BULK_EDIT_INFO', count($cid)), 'info');
        $this->setRedirect(Route::_('index.php?option=com_content&task=article.edit&id=' . $firstId, false));
    }
    
    /**
     * Export selected articles analysis results
     */
    public function export()
    {
        // Check for request forgeries
        $this->checkToken();
        
        $app = Factory::getApplication();
        $input = $app->input;
        
        // Get selected article IDs
        $cid = $input->get('cid', [], 'array');
        $cid = array_map('intval', $cid);
        
        if (empty($cid)) {
            $app->enqueueMessage(Text::_('COM_JOOMLAHITS_ERROR_NO_ITEMS_SELECTED'), 'warning');
            $this->setRedirect(Route::_('index.php?option=com_joomlahits&view=seoanalysis', false));
            return;
        }
        
        // Generate CSV export
        $csv = $this->generateCsvExport($cid);
        
        // Send CSV file
        $app->setHeader('Content-Type', 'text/csv; charset=utf-8');
        $app->setHeader('Content-Disposition', 'attachment; filename="seo_analysis_results.csv"');
        $app->setBody($csv);
        $app->close();
    }
    
    /**
     * Generate CSV export for selected articles
     */
    private function generateCsvExport($articleIds)
    {
        $csv = "ID,Title,Category,Issues\n";
        
        // For now, return basic CSV structure
        // In real implementation, you would fetch article data and issues
        foreach ($articleIds as $id) {
            $csv .= "\"$id\",\"Article Title\",\"Category\",\"Sample issues\"\n";
        }
        
        return $csv;
    }

}