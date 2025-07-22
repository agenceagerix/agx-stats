<?php
/*-----------------------------------------------------------------------------------------------------/
	@version		1.1.0
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

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
?>

<form action="<?php echo Route::_('index.php?option=com_joomlahits&view=cpanel'); ?>" method="post" name="adminForm" id="adminForm">

<div class="row">
    <div class="col-md-12">
        <h1><?php echo Text::_('Joomla Hits - Article Statistics'); ?></h1>
    </div>
</div>

<?php if ($this->statistics): ?>
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title">Total Articles</h5>
                <p class="card-text display-4 text-primary"><?php echo $this->statistics->total_articles; ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title">Total Hits</h5>
                <p class="card-text display-4 text-success"><?php echo number_format($this->statistics->total_hits); ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title">Average Hits</h5>
                <p class="card-text display-4 text-info"><?php echo number_format($this->statistics->average_hits, 1); ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title">Max Hits</h5>
                <p class="card-text display-4 text-warning"><?php echo number_format($this->statistics->max_hits); ?></p>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

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
                               placeholder="<?php echo Text::_('Search by title or category...'); ?>"
                               onchange="this.form.submit();">
                    </div>
                    <div class="col-md-3">
                        <label for="filter_category_id" class="form-label"><?php echo Text::_('Catégorie'); ?></label>
                        <select name="filter_category_id" id="filter_category_id" class="form-select" onchange="this.form.submit();">
                            <option value=""><?php echo Text::_('- All categories -'); ?></option>
                            <?php foreach ($this->categories as $category): ?>
                                <option value="<?php echo $category->value; ?>" 
                                        <?php echo $this->state->get('filter.category_id') == $category->value ? 'selected' : ''; ?>>
                                    <?php echo $this->escape($category->text); ?> (<?php echo $category->article_count; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="filter_published" class="form-label"><?php echo Text::_('État'); ?></label>
                        <select name="filter_published" id="filter_published" class="form-select" onchange="this.form.submit();">
                            <option value=""><?php echo Text::_('- All states -'); ?></option>
                            <option value="1" <?php echo $this->state->get('filter.published') === '1' ? 'selected' : ''; ?>>
                                <?php echo Text::_('Published'); ?>
                            </option>
                            <option value="0" <?php echo $this->state->get('filter.published') === '0' ? 'selected' : ''; ?>>
                                <?php echo Text::_('Unpublished'); ?>
                            </option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="filter_language" class="form-label"><?php echo Text::_('Langue'); ?></label>
                        <select name="filter_language" id="filter_language" class="form-select" onchange="this.form.submit();">
                            <option value=""><?php echo Text::_('- All languages -'); ?></option>
                            <?php foreach ($this->languages as $language): ?>
                                <option value="<?php echo $language->value; ?>" 
                                        <?php echo $this->state->get('filter.language') == $language->value ? 'selected' : ''; ?>>
                                    <?php echo $this->escape($language->text); ?> (<?php echo $language->article_count; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-grid">
                            <button type="button" class="btn btn-outline-secondary" onclick="document.getElementById('filter_search').value='';document.getElementById('filter_category_id').value='';document.getElementById('filter_published').value='';document.getElementById('filter_language').value='';this.form.submit();">
                                <?php echo Text::_('Clear'); ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <h2><?php echo Text::_('Articles with Hits'); ?> 
            <?php if ($this->pagination->total): ?>
                <span class="badge bg-info"><?php echo $this->pagination->total; ?> results</span>
            <?php endif; ?>
        </h2>
        
        <?php if (!empty($this->items)): ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover" id="articlesTable">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Language</th>
                        <th>Hits</th>
                        <th>State</th>
                        <th>Created Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($this->items as $article): ?>
                    <tr>
                        <td>
                            <span class="badge bg-primary">
                                <?php echo $article->id; ?>
                            </span>
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($article->title); ?></strong>
                            <br>
                            <small class="text-muted"><?php echo htmlspecialchars($article->alias); ?></small>
                        </td>
                        <td><?php echo htmlspecialchars($article->category_title ?: 'Uncategorized'); ?></td>
                        <td>
                            <?php 
                            $langClass = '';
                            $langText = '';
                            switch ($article->language) {
                                case 'fr-FR':
                                    $langClass = 'bg-primary';
                                    $langText = 'FR';
                                    break;
                                case 'en-GB':
                                    $langClass = 'bg-success';
                                    $langText = 'EN';
                                    break;
                                case '*':
                                    $langClass = 'bg-secondary';
                                    $langText = 'All';
                                    break;
                                default:
                                    $langClass = 'bg-warning';
                                    $langText = strtoupper(substr($article->language, 0, 2));
                            }
                            ?>
                            <span class="badge <?php echo $langClass; ?>">
                                <?php echo $langText; ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-success fs-6">
                                <?php echo number_format($article->hits); ?> views
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-<?php echo $article->state == 1 ? 'success' : 'danger'; ?>">
                                <?php echo $article->state == 1 ? 'Published' : 'Unpublished'; ?>
                            </span>
                        </td>
                        <td>
                            <?php echo date('d/m/Y H:i', strtotime($article->created)); ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php echo $this->pagination->getListFooter(); ?>
        
        <?php else: ?>
        <div class="alert alert-info">
            <p><?php echo Text::_('No items found.'); ?></p>
        </div>
        <?php endif; ?>
    </div>
</div>

<input type="hidden" name="task" value="">
<input type="hidden" name="boxchecked" value="0">
<?php echo HTMLHelper::_('form.token'); ?>
</form>