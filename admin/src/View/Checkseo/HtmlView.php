<?php
/*-----------------------------------------------------------------------------------------------------/
	@version		1.1.0
	@build			28th July, 2025
	@created		28th July, 2025
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
namespace Joomla\Component\JoomlaHits\Administrator\View\CheckSeo;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\JoomlaHits\Administrator\Model\CheckSeoModel;

class HtmlView extends BaseHtmlView
{
    /**
     * SEO data for articles
     * @var array
     */
    protected $seoData;

    /**
     * Component parameters
     * @var \Joomla\Registry\Registry
     */
    protected $params;

    /**
     * Execute and display a template script.
     * Loads SEO check data from the model.
     *
     * @param   string  $tpl  The name of the template file to parse
     *
     * @return  void
     */
    public function display($tpl = null) : void
    {
        /** @var CheckSeoModel $model */
        $model = $this->getModel();
        
        // Get component parameters
        $this->params = ComponentHelper::getParams('com_joomlahits');
        
        // Get SEO data
        $this->seoData = $model->getSeoData();
        
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
        ToolbarHelper::title('COM_JOOMLAHITS_CHECKSEO_PAGE_TITLE');
        
        if (Factory::getApplication()->getIdentity()->authorise('core.admin', 'com_joomlahits')) {
            ToolbarHelper::preferences('com_joomlahits');
        }
    }
}