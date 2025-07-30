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

}