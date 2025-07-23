<?php
/*-----------------------------------------------------------------------------------------------------/
	@version		1.1.0
	@build			22nd July, 2025
	@created		21st July, 2025
	@package		JoomlaHits
	@subpackage		HtmlView.php
	@author			Hugo Dantas - Agence Agerix <https://www.agerix.fr>
	@copyright		Copyright (C) 2025. All Rights Reserved
	@license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html
	  __    ___  ____  __ _   ___  ____     __    ___  ____  ____  __  _  _
	 / _\  / __)(  __)(  ( \ / __)(  __)   / _\  / __)(  __)(  _ \(  )( \/ )
	/    \( (_ \ ) _) /    /( (__  ) _)   /    \( (_ \ ) _)  )   / )(  )  (
	\_/\_/ \___/(____)\_)__) \___)(____)  \_/\_/ \___/(____)(__\_)(__)(_/\_)
/------------------------------------------------------------------------------------------------------*/
namespace Piedpiper\Component\JoomlaHits\Administrator\View\Dashboard;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Piedpiper\Component\JoomlaHits\Administrator\Model\DashboardModel;

class HtmlView extends BaseHtmlView
{
    /**
     * Dashboard statistics
     * @var \stdClass
     */
    protected $dashboardStats;


    /**
     * Statistics by language
     * @var array
     */
    protected $languageStats;

    /**
     * Recent articles activity
     * @var array
     */
    protected $recentActivity;

    /**
     * Top articles by available languages
     * @var array
     */
    protected $topArticlesByLanguage;

    /**
     * Available languages
     * @var array
     */
    protected $availableLanguages;

    /**
     * Articles without clicks statistics
     * @var \stdClass
     */
    protected $articlesWithoutClicks;

    /**
     * Enhanced category statistics
     * @var array
     */
    protected $enhancedCategoryStats;

    /**
     * Top articles by category
     * @var array
     */
    protected $topArticlesByCategory;

    /**
     * Period comparison
     * @var \stdClass
     */
    protected $periodComparison;

    /**
     * Component parameters
     * @var \Joomla\Registry\Registry
     */
    protected $params;

    /**
     * Execute and display a template script.
     * Loads dashboard data from the model and displays comprehensive statistics.
     *
     * @param   string  $tpl  The name of the template file to parse
     *
     * @return  void
     */
    public function display($tpl = null) : void
    {
        /** @var DashboardModel $model */
        $model = $this->getModel();
        
        // Get component parameters
        $this->params = ComponentHelper::getParams('com_joomlahits');
        
        // Basic stats
        $this->dashboardStats = $model->getDashboardStats();
        $this->recentActivity = $model->getRecentActivity(30);
        
        // Language stats - only if enabled in configuration
        if ($this->params->get('show_language_stats', 1)) {
            $this->languageStats = $model->getLanguageStats();
            
            // Advanced general stats - Dynamic language detection
            $this->availableLanguages = $model->getAvailableLanguages();
            $this->topArticlesByLanguage = [];
            
            // Get top articles for each available language (max 2 languages)
            $languageCount = 0;
            foreach ($this->availableLanguages as $language) {
                if ($languageCount >= 2) break; // Limit to 2 languages for display
                $this->topArticlesByLanguage[$language->language] = $model->getTopArticlesByLanguage($language->language, 10);
                $languageCount++;
            }
        }
        
        $this->articlesWithoutClicks = $model->getArticlesWithoutClicksRate();
        
        // Category stats - only if enabled in configuration
        if ($this->params->get('show_category_stats', 1)) {
            $this->enhancedCategoryStats = $model->getEnhancedCategoryStats();
            
            // Get top articles for each category
            $this->topArticlesByCategory = [];
            foreach ($this->enhancedCategoryStats as $category) {
                $this->topArticlesByCategory[$category->category_id] = $model->getTopArticlesByCategory($category->category_id, 3);
            }
        }
        
        // Temporal stats
        $this->periodComparison = $model->getPeriodComparison('month');
        
        $this->addToolbar();
        parent::display($tpl);
    }

    /**
     * Add the page title and toolbar.
     * Sets up the administration interface toolbar with the page title.
     *
     * @return  void
     */
    protected function addToolbar()
    {
        ToolbarHelper::title('Joomla Hits - Dashboard');
        
        if (Factory::getApplication()->getIdentity()->authorise('core.admin', 'com_joomlahits')) {
            ToolbarHelper::preferences('com_joomlahits');
        }
    }
}