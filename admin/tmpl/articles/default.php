<?php
/*-----------------------------------------------------------------------------------------------------/
	@version		1.2.0
	@build			22nd July, 2025
	@created		21st July, 2025
	@package		JoomlaHits
	@subpackage		default.php
	@author			Hugo Dantas - Agence Agerix <https://www.agerix.fr>
	@copyright		Copyright (C) 2025. All Rights Reserved
	@license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html
	  __    ___  ____  __ _   ___  ____     __    ___  ____  ____  __  _  _
	 / _\  / __)(  __)(  ( \ / __)(  __)   / _\  / __)(  __)(  _ \(  )( \/ )
	/    \( (_ \ ) _) /    /( (__  ) _)   /    \( (_ \ ) _)  )   / )(  )  (
	\_/\_/ \___/(____)\_)__) \___)(____)  \_/\_/ \___/(____)(__\_)(__)(_/\_)
/------------------------------------------------------------------------------------------------------*/
defined('_JEXEC') or die;

use Joomla\CMS\Button\PublishedButton;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('table.columns')
    ->useScript('multiselect');

$app       = Factory::getApplication();
$user      = $this->getCurrentUser();
$userId    = $user->id;
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
?>

<form action="<?php echo Route::_('index.php?option=com_joomlahits&view=articles'); ?>" method="post" name="adminForm" id="adminForm">
    <div class="row">
        <div class="col-md-12">
            <div id="j-main-container" class="j-main-container">

                <!-- Page Header -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h2 class="card-title">
                                    <i class="icon-list"></i> <?php echo Text::_('COM_JOOMLAHITS_ARTICLES_LIST_TITLE'); ?>
                                </h2>
                            </div>
                            <div class="card-body">
                                <p class="card-text"><?php echo Text::_('COM_JOOMLAHITS_ARTICLES_LIST_SUBTITLE'); ?></p>
                                <a href="<?php echo Route::_('index.php?option=com_joomlahits&view=controlpanel'); ?>" class="btn btn-secondary">
                                    <i class="icon-arrow-left"></i> <?php echo Text::_('COM_JOOMLAHITS_BACK_TO_CONTROL_PANEL'); ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label for="filter_search" class="form-label"><?php echo Text::_('Search'); ?></label>
                                        <input type="text" 
                                               name="filter_search" 
                                               id="filter_search" 
                                               class="form-control" 
                                               value="<?php echo $this->escape($this->state->get('filter.search')); ?>" 
                                               placeholder="<?php echo Text::_('COM_JOOMLAHITS_SEARCH_PLACEHOLDER'); ?>"
                                               onchange="this.form.submit();">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="filter_category_id" class="form-label"><?php echo Text::_('COM_JOOMLAHITS_FILTER_CATEGORY'); ?></label>
                                        <select name="filter_category_id" id="filter_category_id" class="form-select" onchange="this.form.submit();">
                                            <option value=""><?php echo Text::_('COM_JOOMLAHITS_ALL_CATEGORIES'); ?></option>
                                            <?php if ($this->categories): foreach ($this->categories as $category): ?>
                                                <option value="<?php echo $category->value; ?>" 
                                                        <?php echo $this->state->get('filter.category_id') == $category->value ? 'selected' : ''; ?>>
                                                    <?php echo $this->escape($category->text); ?> (<?php echo $category->article_count; ?>)
                                                </option>
                                            <?php endforeach; endif; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label for="filter_published" class="form-label"><?php echo Text::_('COM_JOOMLAHITS_FILTER_STATUS'); ?></label>
                                        <select name="filter_published" id="filter_published" class="form-select" onchange="this.form.submit();">
                                            <option value=""><?php echo Text::_('COM_JOOMLAHITS_ALL_STATES'); ?></option>
                                            <option value="1" <?php echo $this->state->get('filter.published') === '1' ? 'selected' : ''; ?>>
                                                <?php echo Text::_('Published'); ?>
                                            </option>
                                            <option value="0" <?php echo $this->state->get('filter.published') === '0' ? 'selected' : ''; ?>>
                                                <?php echo Text::_('Unpublished'); ?>
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="filter_language" class="form-label"><?php echo Text::_('COM_JOOMLAHITS_FILTER_LANGUAGE'); ?></label>
                                        <select name="filter_language" id="filter_language" class="form-select" onchange="this.form.submit();">
                                            <option value=""><?php echo Text::_('COM_JOOMLAHITS_ALL_LANGUAGES'); ?></option>
                                            <?php if ($this->languages): foreach ($this->languages as $language): ?>
                                                <option value="<?php echo $language->value; ?>" 
                                                        <?php echo $this->state->get('filter.language') == $language->value ? 'selected' : ''; ?>>
                                                    <?php echo $this->escape($language->text); ?> (<?php echo $language->article_count; ?>)
                                                </option>
                                            <?php endforeach; endif; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if (empty($this->items)) : ?>
                    <div class="alert alert-info">
                        <span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
                        <?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
                    </div>
                <?php else : ?>
                    <table class="table" id="articleList">
                        <caption class="visually-hidden">
                            <?php echo Text::_('COM_JOOMLAHITS_ARTICLES_CAPTION'); ?>
                        </caption>
                        <thead>
                            <tr>
                                <td class="w-1 text-center">
                                    <?php echo HTMLHelper::_('grid.checkall'); ?>
                                </td>
                                <th scope="col" class="w-1 text-center d-none d-md-table-cell">
                                    <a href="#" onclick="Joomla.tableOrdering('a.state','<?php echo $listDirn == 'ASC' ? 'DESC' : 'ASC'; ?>')">
                                        <?php echo Text::_('COM_JOOMLAHITS_STATUS'); ?> <?php if ($listOrder == 'a.state') echo $listDirn == 'ASC' ? '↑' : '↓'; ?>
                                    </a>
                                </th>
                                <th scope="col">
                                    <a href="#" onclick="Joomla.tableOrdering('a.title','<?php echo $listDirn == 'ASC' ? 'DESC' : 'ASC'; ?>','')">
                                        <?php echo Text::_('COM_JOOMLAHITS_TITLE'); ?> <?php if ($listOrder == 'a.title') echo $listDirn == 'ASC' ? '↑' : '↓'; ?>
                                    </a>
                                </th>
                                <th scope="col" class="w-20 d-none d-md-table-cell">
                                    <?php echo Text::_('COM_JOOMLAHITS_DESCRIPTION'); ?>
                                </th>
                                <th scope="col" class="w-15 d-none d-md-table-cell">
                                    <a href="#" onclick="Joomla.tableOrdering('category_title','<?php echo $listDirn == 'ASC' ? 'DESC' : 'ASC'; ?>','')">
                                        <?php echo Text::_('COM_JOOMLAHITS_CATEGORY'); ?> <?php if ($listOrder == 'category_title') echo $listDirn == 'ASC' ? '↑' : '↓'; ?>
                                    </a>
                                </th>
                                <th scope="col" class="w-10 d-none d-md-table-cell text-center">
                                    <a href="#" onclick="Joomla.tableOrdering('a.language','<?php echo $listDirn == 'ASC' ? 'DESC' : 'ASC'; ?>','')">
                                        <?php echo Text::_('COM_JOOMLAHITS_LANGUAGE'); ?> <?php if ($listOrder == 'a.language') echo $listDirn == 'ASC' ? '↑' : '↓'; ?>
                                    </a>
                                </th>
                                <th scope="col" class="w-10 d-none d-md-table-cell text-center">
                                    <a href="#" onclick="Joomla.tableOrdering('a.hits','<?php echo $listDirn == 'ASC' ? 'DESC' : 'ASC'; ?>','')">
                                        <?php echo Text::_('COM_JOOMLAHITS_VIEWS'); ?> <?php if ($listOrder == 'a.hits') echo $listDirn == 'ASC' ? '↑' : '↓'; ?>
                                    </a>
                                </th>
                                <th scope="col" class="w-10 d-none d-lg-table-cell">
                                    <a href="#" onclick="Joomla.tableOrdering('a.created','<?php echo $listDirn == 'ASC' ? 'DESC' : 'ASC'; ?>','')">
                                        <?php echo Text::_('COM_JOOMLAHITS_CREATED_DATE'); ?> <?php if ($listOrder == 'a.created') echo $listDirn == 'ASC' ? '↑' : '↓'; ?>
                                    </a>
                                </th>
                                <th scope="col" class="w-5 d-none d-lg-table-cell">
                                    <a href="#" onclick="Joomla.tableOrdering('a.id','<?php echo $listDirn == 'ASC' ? 'DESC' : 'ASC'; ?>','')">
                                        ID <?php if ($listOrder == 'a.id') echo $listDirn == 'ASC' ? '↑' : '↓'; ?>
                                    </a>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($this->items as $i => $item) :
                                $canEdit   = $user->authorise('core.edit', 'com_content.article.' . $item->id);
                                $canChange = $user->authorise('core.edit.state', 'com_content.article.' . $item->id);
                            ?>
                                <tr class="row<?php echo $i % 2; ?>" data-draggable-group="1">
                                    <td class="text-center">
                                        <?php echo HTMLHelper::_('grid.id', $i, $item->id, false, 'cid', 'cb', $item->title); ?>
                                    </td>
                                    <td class="text-center d-none d-md-table-cell">
                                        <?php
                                        $options = [
                                            'task_prefix' => 'articles.',
                                            'disabled' => !$canChange,
                                            'id' => 'state-' . $item->id
                                        ];

                                        echo (new PublishedButton)->render((int) $item->state, $i, $options);
                                        ?>
                                    </td>
                                    <th scope="row" class="has-context">
                                        <div>
                                            <?php if ($canEdit) : ?>
                                                <a class="hasTooltip" href="<?php echo Route::_('index.php?option=com_content&task=article.edit&id=' . $item->id); ?>" title="<?php echo Text::_('JACTION_EDIT'); ?>">
                                                    <?php echo $this->escape($item->title); ?>
                                                </a>
                                            <?php else : ?>
                                                <span title="<?php echo Text::sprintf('JFIELD_ALIAS_LABEL', $this->escape($item->alias)); ?>">
                                                    <?php echo $this->escape($item->title); ?>
                                                </span>
                                            <?php endif; ?>
                                            <span class="small break-word">
                                                <?php echo Text::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?>
                                            </span>
                                        </div>
                                    </th>
                                    <td class="d-none d-md-table-cell">
                                        <?php 
                                        $description = strip_tags($item->introtext);
                                        echo $this->escape(strlen($description) > 100 ? substr($description, 0, 100) . '...' : $description); 
                                        ?>
                                    </td>
                                    <td class="d-none d-md-table-cell">
                                        <?php echo $this->escape($item->category_title); ?>
                                    </td>
                                    <td class="d-none d-md-table-cell text-center">
                                        <?php 
                                        $languageDisplay = '';
                                        switch($item->language) {
                                            case 'fr-FR':
                                                $languageDisplay = Text::_('COM_JOOMLAHITS_LANGUAGE_FRENCH');
                                                break;
                                            case 'en-GB':
                                                $languageDisplay = Text::_('COM_JOOMLAHITS_LANGUAGE_ENGLISH');
                                                break;
                                            case '*':
                                                $languageDisplay = Text::_('COM_JOOMLAHITS_LANGUAGE_ALL');
                                                break;
                                            default:
                                                $languageDisplay = $item->language;
                                        }
                                        ?>
                                        <span class="badge bg-secondary">
                                            <?php echo $this->escape($languageDisplay); ?>
                                        </span>
                                    </td>
                                    <td class="d-none d-md-table-cell text-center">
                                        <span class="badge bg-info">
                                            <?php echo (int) $item->hits; ?>
                                        </span>
                                    </td>
                                    <td class="d-none d-lg-table-cell">
                                        <?php echo HTMLHelper::_('date', $item->created, Text::_('DATE_FORMAT_LC4')); ?>
                                    </td>
                                    <td class="d-none d-lg-table-cell">
                                        <?php echo (int) $item->id; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <?php echo $this->pagination->getListFooter(); ?>
                <?php endif; ?>

                <input type="hidden" name="task" value="">
                <input type="hidden" name="boxchecked" value="0">
                <input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>">
                <input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>">
                <?php echo HTMLHelper::_('form.token'); ?>
                
                <script>
                Joomla.tableOrdering = function(order, dir) {
                    document.adminForm.filter_order.value = order;
                    document.adminForm.filter_order_Dir.value = dir;
                    document.adminForm.submit();
                };
                </script>
            </div>
        </div>
    </div>
</form>