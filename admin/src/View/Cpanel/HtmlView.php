<?php
namespace Piedpiper\Component\JoomlaHits\Administrator\View\Cpanel;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Piedpiper\Component\JoomlaHits\Administrator\Model\CpanelModel;

class HtmlView extends BaseHtmlView
{
    /**
     * The items to display
     * @var array
     */
    protected $items;

    /**
     * The pagination object
     * @var \Joomla\CMS\Pagination\Pagination
     */
    protected $pagination;

    /**
     * The model state
     * @var \Joomla\Registry\Registry
     */
    protected $state;

    /**
     * Statistical data about article hits
     * @var \stdClass
     */
    protected $statistics;

    /**
     * Available categories for filtering
     * @var array
     */
    protected $categories;

    /**
     * Available languages for filtering
     * @var array
     */
    protected $languages;

    /**
     * Execute and display a template script.
     * Loads data from the model and displays the cpanel view with article hit statistics,
     * filtering options, and paginated results.
     *
     * @param   string  $tpl  The name of the template file to parse
     *
     * @return  void
     */
    public function display($tpl = null) : void
    {
        /** @var CpanelModel $model */
        $model = $this->getModel();
        
        $this->items = $model->getItems();
        $this->pagination = $model->getPagination();
        $this->state = $model->getState();
        $this->statistics = $model->getHitsStatistics();
        $this->categories = $model->getCategories();
        $this->languages = $model->getLanguages();
        
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
        ToolbarHelper::title('Joomla Hits - Articles statistics');
    }
}