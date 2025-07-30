<?php
/*-----------------------------------------------------------------------------------------------------/
	@version		1.2.0
	@build			29th July, 2025
	@created		29th July, 2025
	@package		JoomlaHits
	@subpackage		HtmlView.php
	@author			Hugo Dantas - Agence Agerix <https://www.agerix.fr>
	@copyright		Copyright (C) 2025. All Rights Reserved
	@license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html
/------------------------------------------------------------------------------------------------------*/
namespace Joomla\Component\JoomlaHits\Administrator\View\Seoanalysis;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Toolbar\Button\DropdownButton;
use Joomla\CMS\Component\ComponentHelper;

/**
 * View class for a list of SEO analysis.
 */
class HtmlView extends BaseHtmlView
{
    /**
     * The search tools form
     *
     * @var    Form
     */
    public $filterForm;

    /**
     * The active search filters
     *
     * @var    array
     */
    public $activeFilters;

    /**
     * An array of items
     *
     * @var    array
     */
    protected $items;

    /**
     * The pagination object
     *
     * @var    Pagination
     */
    protected $pagination;

    /**
     * The model state
     *
     * @var    Registry
     */
    protected $state;

    /**
     * Form object for search filters
     *
     * @var    Form
     */
    public $form;

    /**
     * Component parameters
     *
     * @var    Registry
     */
    public $params;

    /**
     * Display the view.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     */
    public function display($tpl = null)
    {
        // Get component parameters
        $this->params = ComponentHelper::getParams('com_joomlahits');
        
        $this->addToolbar();

        parent::display($tpl);
    }

    /**
     * Add the page title and toolbar.
     *
     * @return  void
     */
    protected function addToolbar()
    {
        ToolbarHelper::title(Text::_('COM_JOOMLAHITS_SEOANALYSIS_PAGE_TITLE'), 'search');

        $user = Factory::getApplication()->getIdentity();
        $toolbar = $this->getDocument()->getToolbar();
        
        // Add Actions dropdown button
        /** @var DropdownButton $dropdown */
        $dropdown = $toolbar->dropdownButton('status-group')
            ->text('JTOOLBAR_CHANGE_STATUS')
            ->toggleSplit(false)
            ->icon('icon-ellipsis-h')
            ->buttonClass('btn btn-action')
            ->listCheck(true);

        $childBar = $dropdown->getChildToolbar();

        // Bulk AI Fix
        $childBar->standardButton('ai-fix', 'COM_JOOMLAHITS_BULK_AI_FIX')
            ->icon('icon-wand')
            ->listCheck(true)
            ->onclick('startBulkAiFix(); return false;');

        // Force AI Fix
        $childBar->standardButton('force-ai-fix', 'COM_JOOMLAHITS_FORCE_AI_FIX')
            ->icon('icon-lightning')
            ->listCheck(true)
            ->onclick('startForceAiFix(); return false;');

        // Add preferences button
        if ($user->authorise('core.admin', 'com_joomlahits')) {
            ToolbarHelper::preferences('com_joomlahits');
        }
    }
}