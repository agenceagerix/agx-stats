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
     * Top performing articles
     * @var array
     */
    protected $topArticles;

    /**
     * Statistics by category
     * @var array
     */
    protected $categoryStats;

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
        
        $this->dashboardStats = $model->getDashboardStats();
        $this->topArticles = $model->getTopArticles(10);
        $this->categoryStats = $model->getCategoryStats();
        $this->languageStats = $model->getLanguageStats();
        $this->recentActivity = $model->getRecentActivity(30);
        
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
    }
}