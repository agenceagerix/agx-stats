<?php
/*-----------------------------------------------------------------------------------------------------/
	@version		1.2.0
	@build			28th July, 2025
	@created		28th July, 2025
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
use Joomla\CMS\Uri\Uri;

$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('table.columns')
    ->useScript('multiselect');

$app = Factory::getApplication();
$user = $this->getCurrentUser();
$listOrder = $this->escape($app->input->getString('filter_order', 'a.title'));
$listDirn = $this->escape($app->input->getString('filter_order_Dir', 'ASC'));

// Get the 'show' parameter to determine if we should show results
$showResults = $app->input->getBool('show', false);
?>

<form action="<?php echo Route::_('index.php?option=com_joomlahits&view=checkseo' . ($showResults ? '&show=1' : '')); ?>" method="post" name="adminForm" id="adminForm">
    <div class="row">
        <div class="col-md-12">
            <div id="j-main-container" class="j-main-container">

                <!-- Page Header -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h2 class="card-title">
                                    <i class="icon-search"></i> <?php echo Text::_('COM_JOOMLAHITS_CHECKSEO_PAGE_TITLE'); ?>
                                </h2>
                            </div>
                            <div class="card-body">
                                <p class="card-text"><?php echo Text::_('COM_JOOMLAHITS_CHECKSEO_PAGE_DESCRIPTION'); ?></p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <a href="<?php echo Route::_('index.php?option=com_joomlahits'); ?>" class="btn btn-secondary">
                                        <i class="icon-arrow-left"></i> <?php echo Text::_('COM_JOOMLAHITS_BACK_TO_CONTROL_PANEL'); ?>
                                    </a>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-primary btn-lg" id="seoCheckButton">
                                            <i class="icon-search"></i> <span id="seoCheckButtonText"><?php echo Text::_('COM_JOOMLAHITS_CHECKSEO_START_ANALYSIS'); ?></span>
                                        </button>
                                        <a href="<?php echo Route::_('index.php?option=com_joomlahits&view=seoanalysis'); ?>" class="btn btn-success btn-lg" id="fullAnalysisButton">
                                            <i class="icon-cog"></i> <?php echo Text::_('COM_JOOMLAHITS_FULL_SEO_ANALYSIS'); ?>
                                        </a>
                                    </div>
                                    <div></div> <!-- Spacer for centering -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if ($showResults): ?>
                    <!-- Filters -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <label for="filter_search" class="form-label"><?php echo Text::_('Search'); ?></label>
                                            <input type="text" 
                                                   name="filter_search" 
                                                   id="filter_search" 
                                                   class="form-control" 
                                                   value="<?php echo $this->escape($app->input->getString('filter_search', '')); ?>" 
                                                   placeholder="<?php echo Text::_('COM_JOOMLAHITS_SEARCH_PLACEHOLDER'); ?>"
                                                   onchange="this.form.submit();">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="filter_category_id" class="form-label"><?php echo Text::_('COM_JOOMLAHITS_FILTER_CATEGORY'); ?></label>
                                            <select name="filter_category_id" id="filter_category_id" class="form-select" onchange="this.form.submit();">
                                                <option value=""><?php echo Text::_('COM_JOOMLAHITS_ALL_CATEGORIES'); ?></option>
                                                <?php if ($this->categories): foreach ($this->categories as $category): ?>
                                                    <option value="<?php echo $category->value; ?>" 
                                                            <?php echo $app->input->getString('filter_category_id', '') == $category->value ? 'selected' : ''; ?>>
                                                        <?php echo $this->escape($category->text); ?> (<?php echo $category->article_count; ?>)
                                                    </option>
                                                <?php endforeach; endif; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label for="filter_published" class="form-label"><?php echo Text::_('COM_JOOMLAHITS_FILTER_STATUS'); ?></label>
                                            <select name="filter_published" id="filter_published" class="form-select" onchange="this.form.submit();">
                                                <option value=""><?php echo Text::_('COM_JOOMLAHITS_ALL_STATES'); ?></option>
                                                <option value="1" <?php echo $app->input->getString('filter_published', '') === '1' ? 'selected' : ''; ?>>
                                                    <?php echo Text::_('Published'); ?>
                                                </option>
                                                <option value="0" <?php echo $app->input->getString('filter_published', '') === '0' ? 'selected' : ''; ?>>
                                                    <?php echo Text::_('Unpublished'); ?>
                                                </option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="filter_language" class="form-label"><?php echo Text::_('COM_JOOMLAHITS_FILTER_LANGUAGE'); ?></label>
                                            <select name="filter_language" id="filter_language" class="form-select" onchange="this.form.submit();">
                                                <option value=""><?php echo Text::_('COM_JOOMLAHITS_ALL_LANGUAGES'); ?></option>
                                                <?php if ($this->languages): foreach ($this->languages as $language): ?>
                                                    <option value="<?php echo $language->value; ?>" 
                                                            <?php echo $app->input->getString('filter_language', '') == $language->value ? 'selected' : ''; ?>>
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
                        <div class="alert alert-success">
                            <span class="icon-checkmark" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('SUCCESS'); ?></span>
                            <?php echo Text::_('COM_JOOMLAHITS_CHECKSEO_NO_METADESC_MISSING'); ?>
                        </div>
                    <?php else : 
                        $missingCount = count($this->items);
                        $totalCount = $this->totalAnalyzedCount ?? 0;
                        $percentage = $totalCount > 0 ? round(($missingCount / $totalCount) * 100, 1) : 0;
                    ?>
                        <div class="alert alert-warning mb-3">
                            <span class="icon-warning" aria-hidden="true"></span>
                            <?php echo Text::sprintf('COM_JOOMLAHITS_CHECKSEO_METADESC_MISSING_FOUND', $missingCount); ?>
                            <br><div class="mt-2" style="font-size: 0.95em; line-height: 1.4;">
                                <i class="icon-info me-1"></i> 
                                <strong>Analyse :</strong>
                                <?php 
                                // Fallback display if translation key is missing
                                $statsText = Text::_('COM_JOOMLAHITS_CHECKSEO_ANALYSIS_STATS');
                                if ($statsText === 'COM_JOOMLAHITS_CHECKSEO_ANALYSIS_STATS') {
                                    // Translation key not found, use hardcoded text
                                    echo $missingCount . ' anomalies détectées sur ' . $totalCount . ' articles analysés (' . $percentage . '%)';
                                } else {
                                    echo Text::sprintf('COM_JOOMLAHITS_CHECKSEO_ANALYSIS_STATS', $missingCount, $totalCount, $percentage);
                                }
                                ?>
                            </div>
                        </div>

                        <!-- AI Meta Description Generation -->
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="card border-primary">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="card-title mb-0">
                                            <i class="icon-wand"></i> <?php echo Text::_('COM_JOOMLAHITS_AI_METADESC_GENERATOR'); ?>
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <p class="card-text"><?php echo Text::_('COM_JOOMLAHITS_AI_METADESC_DESCRIPTION'); ?></p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <button type="button" id="generateAiMetaDescBtn" class="btn btn-success btn-lg">
                                                <i class="icon-magic"></i> <?php echo Text::_('COM_JOOMLAHITS_GENERATE_AI_METADESC'); ?>
                                            </button>
                                            <small class="text-muted"><?php echo Text::_('COM_JOOMLAHITS_AI_METADESC_HELP'); ?></small>
                                        </div>
                                        
                                        <!-- Progress section (hidden by default) -->
                                        <div id="ai-progress-section" class="mt-3" style="display: none;">
                                            <div class="alert alert-info">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <strong id="progress-title"><?php echo Text::_('COM_JOOMLAHITS_AI_PROCESSING'); ?></strong>
                                                    <button type="button" id="cancelAiProcess" class="btn btn-sm btn-outline-danger">
                                                        <?php echo Text::_('COM_JOOMLAHITS_CANCEL'); ?>
                                                    </button>
                                                </div>
                                                <div class="progress mb-2">
                                                    <div id="ai-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated" 
                                                         role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                                <div id="current-status" class="small"></div>
                                                <div id="results-log" class="mt-2 small"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <table class="table" id="seoCheckList">
                            <caption class="visually-hidden">
                                <?php echo Text::_('COM_JOOMLAHITS_CHECKSEO_CAPTION'); ?>
                            </caption>
                            <thead>
                                <tr>
                                    <td class="w-1 text-center">
                                        <?php echo HTMLHelper::_('grid.checkall'); ?>
                                    </td>
                                    <th scope="col" class="w-1 text-center d-none d-md-table-cell">
                                        <a href="#" onclick="Joomla.tableOrdering('a.state','<?php echo $listDirn == 'ASC' ? 'DESC' : 'ASC'; ?>','')">
                                            <?php echo Text::_('COM_JOOMLAHITS_STATUS'); ?> <?php if ($listOrder == 'a.state') echo $listDirn == 'ASC' ? '↑' : '↓'; ?>
                                        </a>
                                    </th>
                                    <th scope="col">
                                        <a href="#" onclick="Joomla.tableOrdering('a.title','<?php echo $listDirn == 'ASC' ? 'DESC' : 'ASC'; ?>','')">
                                            <?php echo Text::_('COM_JOOMLAHITS_TITLE'); ?> <?php if ($listOrder == 'a.title') echo $listDirn == 'ASC' ? '↑' : '↓'; ?>
                                        </a>
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
                                    <th scope="col" class="w-35 d-none d-md-table-cell">
                                        <?php echo Text::_('COM_JOOMLAHITS_CHECKSEO_META_DESCRIPTION'); ?>
                                    </th>
                                    <th scope="col" class="w-10 d-none d-lg-table-cell text-center">
                                        <a href="#" onclick="Joomla.tableOrdering('a.hits','<?php echo $listDirn == 'ASC' ? 'DESC' : 'ASC'; ?>','')">
                                            <?php echo Text::_('COM_JOOMLAHITS_VIEWS'); ?> <?php if ($listOrder == 'a.hits') echo $listDirn == 'ASC' ? '↑' : '↓'; ?>
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
                                    $canEdit = $user->authorise('core.edit', 'com_content.article.' . $item->id);
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
                                            </div>
                                        </th>
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
                                        <td class="d-none d-md-table-cell">
                                            <small class="text-muted">
                                                <?php echo !empty($item->metadesc) ? $this->escape($item->metadesc) : ''; ?>
                                            </small>
                                        </td>
                                        <td class="text-center d-none d-lg-table-cell">
                                            <span class="badge bg-info"><?php echo number_format($item->hits); ?></span>
                                        </td>
                                        <td class="d-none d-lg-table-cell">
                                            <?php echo (int) $item->id; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                <?php endif; ?>

            </div>
        </div>
    </div>

    <input type="hidden" name="task" value="">
    <input type="hidden" name="boxchecked" value="0">
    <input type="hidden" name="show" value="<?php echo $showResults ? '1' : '0'; ?>">
    <input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>">
    <input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>">
    <?php echo HTMLHelper::_('form.token'); ?>
    
    <script>
    Joomla.tableOrdering = function(order, dir) {
        document.adminForm.filter_order.value = order;
        document.adminForm.filter_order_Dir.value = dir;
        document.adminForm.submit();
    };

    // SEO Analysis Button Animation
    document.addEventListener('DOMContentLoaded', function() {
        const seoCheckButton = document.getElementById('seoCheckButton');
        const seoCheckButtonText = document.getElementById('seoCheckButtonText');
        
        if (seoCheckButton) {
            seoCheckButton.addEventListener('click', function() {
                // Start loading animation
                seoCheckButton.disabled = true;
                seoCheckButton.innerHTML = '<i class="icon-refresh icon-spin"></i> <span><?php echo Text::_('COM_JOOMLAHITS_CHECKSEO_ANALYZING'); ?></span>';
                
                // Set the show parameter and submit form
                document.querySelector('input[name="show"]').value = '1';
                
                // Navigate to analysis page after short delay
                setTimeout(() => {
                    document.adminForm.submit();
                }, 500);
            });
        }

        // AI Meta Description Generator
        const generateBtn = document.getElementById('generateAiMetaDescBtn');
        const progressSection = document.getElementById('ai-progress-section');
        const progressBar = document.getElementById('ai-progress-bar');
        const currentStatus = document.getElementById('current-status');
        const resultsLog = document.getElementById('results-log');
        const cancelBtn = document.getElementById('cancelAiProcess');
        let isProcessing = false;
        let cancelRequested = false;

        if (generateBtn) {
            generateBtn.addEventListener('click', function() {
                if (isProcessing) {
                    return;
                }
                
                // Get selected articles or all if none selected
                const checkboxes = document.querySelectorAll('input[name="cid[]"]:checked');
                const selectedIds = Array.from(checkboxes).map(cb => cb.value);
                
                // If no selection, get all visible article IDs
                if (selectedIds.length === 0) {
                    const allCheckboxes = document.querySelectorAll('input[name="cid[]"]');
                    selectedIds.push(...Array.from(allCheckboxes).map(cb => cb.value));
                }

                if (selectedIds.length === 0) {
                    alert(<?php echo json_encode(Text::_('COM_JOOMLAHITS_NO_ARTICLES_FOUND')); ?>);
                    return;
                }

                startAiGeneration(selectedIds);
            });
        }

        if (cancelBtn) {
            cancelBtn.addEventListener('click', function() {
                cancelRequested = true;
                currentStatus.textContent = <?php echo json_encode(Text::_('COM_JOOMLAHITS_AI_CANCELLING')); ?>;
                cancelBtn.disabled = true;
            });
        }

        function startAiGeneration(articleIds) {
            isProcessing = true;
            cancelRequested = false;
            
            // Show progress section
            progressSection.style.display = 'block';
            generateBtn.disabled = true;
            cancelBtn.disabled = false;
            
            // Reset progress
            progressBar.style.width = '0%';
            progressBar.setAttribute('aria-valuenow', '0');
            currentStatus.textContent = <?php echo json_encode(Text::_('COM_JOOMLAHITS_AI_INITIALIZING')); ?>;
            resultsLog.innerHTML = '';

            processArticles(articleIds, 0);
        }

        function processArticles(articleIds, currentIndex) {
            if (cancelRequested || currentIndex >= articleIds.length) {
                finishProcessing();
                return;
            }

            const articleId = articleIds[currentIndex];
            const progress = Math.round((currentIndex / articleIds.length) * 100);
            
            // Update progress
            progressBar.style.width = progress + '%';
            progressBar.setAttribute('aria-valuenow', progress);
            currentStatus.textContent = <?php echo json_encode(Text::_('COM_JOOMLAHITS_AI_PROCESSING_ARTICLE')); ?> + ' ' + (currentIndex + 1) + '/' + articleIds.length;

            // Make AJAX request
            fetch('<?php echo Uri::root(); ?>administrator/components/com_joomlahits/direct_ai_metadesc.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'article_id=' + encodeURIComponent(articleId)
            })
            .then(response => {
                return response.text().then(text => {
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        throw new Error('Server returned invalid JSON: ' + text);
                    }
                });
            })
            .then(data => {
                if (data.success) {
                    resultsLog.innerHTML += '<div class="text-success"><i class="icon-checkmark"></i> ' + data.message + '</div>';
                    // Update the table cell with new meta description
                    updateTableMetaDescription(articleId, data.metadesc);
                } else {
                    resultsLog.innerHTML += '<div class="text-danger"><i class="icon-warning"></i> ' + data.message + '</div>';
                }
                
                // Process next article
                setTimeout(() => {
                    processArticles(articleIds, currentIndex + 1);
                }, 500); // Small delay to avoid overwhelming the API
            })
            .catch(error => {
                resultsLog.innerHTML += '<div class="text-danger"><i class="icon-warning"></i> Erreur: ' + error.message + '</div>';
                setTimeout(() => {
                    processArticles(articleIds, currentIndex + 1);
                }, 500);
            });
        }

        function updateTableMetaDescription(articleId, metadesc) {
            // Find the row with this article ID and update the meta description cell
            const rows = document.querySelectorAll('#seoCheckList tbody tr');
            rows.forEach(row => {
                const checkbox = row.querySelector('input[name="cid[]"]');
                if (checkbox && checkbox.value === articleId) {
                    const metaCell = row.cells[5]; // Meta description column (index 5)
                    if (metaCell) {
                        metaCell.innerHTML = '<small class="text-success">' + metadesc.substring(0, 100) + '...</small>';
                    }
                }
            });
        }

        function finishProcessing() {
            isProcessing = false;
            generateBtn.disabled = false;
            cancelBtn.disabled = true;
            
            // Update progress to 100%
            progressBar.style.width = '100%';
            progressBar.setAttribute('aria-valuenow', '100');
            
            if (cancelRequested) {
                currentStatus.textContent = <?php echo json_encode(Text::_('COM_JOOMLAHITS_AI_CANCELLED')); ?>;
            } else {
                currentStatus.textContent = <?php echo json_encode(Text::_('COM_JOOMLAHITS_AI_COMPLETED')); ?>;
            }
            
            // Hide progress section after 3 seconds
            setTimeout(() => {
                progressSection.style.display = 'none';
            }, 3000);
        }
    });
    </script>
</form>