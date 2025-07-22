<?php
namespace Piedpiper\Component\JoomlaHits\Administrator\View\Cpanel;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Piedpiper\Component\JoomlaHits\Administrator\Model\CpanelModel;

class HtmlView extends BaseHtmlView
{
    protected $items;
    protected $pagination;
    protected $state;
    protected $filterForm;
    protected $activeFilters;
    protected $statistics;
    protected $categories;
    protected $languages;

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

    protected function addToolbar()
    {
        ToolbarHelper::title('Joomla Hits - Articles statistics');
    }
}