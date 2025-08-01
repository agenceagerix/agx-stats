<?php
/*-----------------------------------------------------------------------------------------------------/
	@version		1.2.0
	@build			29th July, 2025
	@created		29th July, 2025
	@package		JoomlaHits
	@subpackage		default.php
	@author			Hugo Dantas - Agence Agerix <https://www.agerix.fr>
	@copyright		Copyright (C) 2025. All Rights Reserved
	@license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html
/------------------------------------------------------------------------------------------------------*/
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('core')
    ->useScript('table.columns')
    ->useScript('multiselect')
    ->useScript('bootstrap.modal');

$app = Factory::getApplication();
$user = $this->getCurrentUser();
$userId = $user->id;
$listOrder = 'severity';
$listDirn = 'ASC';
?>

<!-- Include CSS file -->
<link rel="stylesheet" href="<?php echo Uri::root() . 'administrator/components/com_joomlahits/tmpl/seoanalysis/seoanalysis.css'; ?>">

<div class="row">
    <div class="col-md-12">
        <div id="j-main-container" class="j-main-container">

            <!-- Page Header -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h2 class="card-title mb-0">
                                <i class="icon-search"></i> <?php echo Text::_('COM_JOOMLAHITS_SEOANALYSIS_PAGE_TITLE'); ?>
                            </h2>
                        </div>
                        <div class="card-body">
                            <p class="card-text"><?php echo Text::_('COM_JOOMLAHITS_SEOANALYSIS_PAGE_DESCRIPTION'); ?></p>
                            
                            <?php 
                            // Get configuration info
                            $selectedCategories = $this->params->get('seo_categories', []);
                            $selectedIssues = $this->params->get('seo_critical_issues', ['title_missing', 'meta_desc_missing']);
                            
                            if (!empty($selectedCategories) && !in_array('', $selectedCategories)) : ?>
                            <div class="alert alert-info mb-3">
                                <i class="icon-info-circle"></i> 
                                <strong><?php echo Text::_('COM_JOOMLAHITS_CONFIG_SEO_CATEGORIES_LABEL'); ?>:</strong> 
                                <?php echo Text::_('COM_JOOMLAHITS_SEOANALYSIS_ANALYZING_SELECTED_CATEGORIES'); ?>
                            </div>
                            <?php endif; ?>
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <a href="<?php echo Route::_('index.php?option=com_joomlahits&view=checkseo'); ?>" class="btn btn-secondary">
                                    <i class="icon-arrow-left"></i> <?php echo Text::_('COM_JOOMLAHITS_BACK_TO_CHECKSEO'); ?>
                                </a>
                                <button type="button" class="btn btn-success btn-lg" id="startAnalysisBtn">
                                    <i class="icon-search"></i> <span id="analysisButtonText"><?php echo Text::_('COM_JOOMLAHITS_START_FULL_ANALYSIS'); ?></span>
                                </button>
                                <div></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Loading Section -->
            <div id="loading-section" class="row mb-4" style="display: none;">
                <div class="col-md-12">
                    <div class="card border-info">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <strong id="progress-title"><?php echo Text::_('COM_JOOMLAHITS_ANALYZING_ARTICLES'); ?></strong>
                                <button type="button" id="cancelAnalysis" class="btn btn-sm btn-outline-danger" onclick="cancelAnalysis()">
                                    <?php echo Text::_('COM_JOOMLAHITS_CANCEL'); ?>
                                </button>
                            </div>
                            <div class="progress mb-3">
                                <div id="analysis-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated" 
                                     role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <div id="current-analysis-status" class="text-center text-muted">
                                <?php echo Text::_('COM_JOOMLAHITS_ANALYZING_ARTICLES'); ?>...
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Results Section -->
            <div id="results-section" style="display: none;">
                <form action="<?php echo Route::_('index.php?option=com_joomlahits&view=seoanalysis'); ?>" method="post" name="adminForm" id="adminForm">

                <!-- Filters -->
                <div class="row mb-3" id="filters-section">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label for="severity-filter" class="form-label"><?php echo Text::_('COM_JOOMLAHITS_SEOANALYSIS_FILTER_SEVERITY'); ?></label>
                                        <select id="severity-filter" class="form-select" onchange="applyFilters()">
                                            <option value=""><?php echo Text::_('COM_JOOMLAHITS_SEOANALYSIS_ALL_LEVELS'); ?></option>
                                            <option value="critical"><?php echo Text::_('COM_JOOMLAHITS_SEOANALYSIS_CRITICAL'); ?></option>
                                            <option value="warning"><?php echo Text::_('COM_JOOMLAHITS_SEOANALYSIS_WARNING'); ?></option>
                                            <option value="info"><?php echo Text::_('COM_JOOMLAHITS_SEOANALYSIS_INFO'); ?></option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="issue-type-filter" class="form-label"><?php echo Text::_('COM_JOOMLAHITS_SEOANALYSIS_FILTER_ISSUE_TYPE'); ?></label>
                                        <select id="issue-type-filter" class="form-select" onchange="applyFilters()">
                                            <option value=""><?php echo Text::_('COM_JOOMLAHITS_SEOANALYSIS_ALL_TYPES'); ?></option>
                                            <option value="title_missing"><?php echo Text::_('COM_JOOMLAHITS_SEOANALYSIS_TITLE_MISSING'); ?></option>
                                            <option value="title_too_short"><?php echo Text::_('COM_JOOMLAHITS_SEOANALYSIS_TITLE_TOO_SHORT'); ?></option>
                                            <option value="title_too_long"><?php echo Text::_('COM_JOOMLAHITS_SEOANALYSIS_TITLE_TOO_LONG'); ?></option>
                                            <option value="meta_desc_missing"><?php echo Text::_('COM_JOOMLAHITS_SEOANALYSIS_META_DESC_MISSING'); ?></option>
                                            <option value="meta_desc_too_short"><?php echo Text::_('COM_JOOMLAHITS_SEOANALYSIS_META_DESC_TOO_SHORT'); ?></option>
                                            <option value="meta_desc_too_long"><?php echo Text::_('COM_JOOMLAHITS_SEOANALYSIS_META_DESC_TOO_LONG'); ?></option>
                                            <option value="content_too_short"><?php echo Text::_('COM_JOOMLAHITS_SEOANALYSIS_CONTENT_TOO_SHORT'); ?></option>
                                            <option value="missing_h1"><?php echo Text::_('COM_JOOMLAHITS_SEOANALYSIS_MISSING_H1'); ?></option>
                                            <option value="missing_alt_tags"><?php echo Text::_('COM_JOOMLAHITS_SEOANALYSIS_MISSING_ALT_TAGS'); ?></option>
                                            <option value="meta_keywords_too_few"><?php echo Text::_('COM_JOOMLAHITS_SEOANALYSIS_META_KEYWORDS_FEW'); ?></option>
                                            <option value="meta_keywords_missing"><?php echo Text::_('COM_JOOMLAHITS_SEOANALYSIS_META_KEYWORDS_MISSING'); ?></option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="search-filter" class="form-label"><?php echo Text::_('COM_JOOMLAHITS_SEARCH'); ?></label>
                                        <input type="text" id="search-filter" class="form-control" placeholder="<?php echo Text::_('COM_JOOMLAHITS_SEARCH_PLACEHOLDER'); ?>" onkeyup="applyFilters()">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Analysis Statistics -->
                <div id="analysis-stats" class="row mb-3" style="display: none;">
                    <div class="col-md-12">
                        <div class="alert alert-info">
                            <span class="icon-info" aria-hidden="true"></span>
                            <strong><?php echo Text::_('COM_JOOMLAHITS_SEOANALYSIS_RESULTS_SUMMARY'); ?>:</strong>
                            <span id="analysis-stats-text"></span>
                        </div>
                    </div>
                </div>

                <!-- Results Table -->
                <table class="table" id="seoResultsList">
                    <caption class="visually-hidden">
                        <?php echo Text::_('COM_JOOMLAHITS_SEOANALYSIS_TABLE_CAPTION'); ?>
                    </caption>
                    <thead>
                        <tr>
                            <th scope="col" class="w-1 text-center">
                                <?php echo HTMLHelper::_('grid.checkall'); ?>
                            </th>
                            <th scope="col" class="w-5 d-none d-lg-table-cell">
                                <a href="#" onclick="Joomla.tableOrdering('id','<?php echo $listDirn == 'ASC' ? 'DESC' : 'ASC'; ?>',''); return false;" class="js-seo-sort" data-column="id" data-direction="<?php echo $listDirn == 'ASC' ? 'DESC' : 'ASC'; ?>">
                                    <?php echo Text::_('JGRID_HEADING_ID'); ?>
                                    <?php if ($listOrder == 'id') : ?>
                                        <span class="ms-2 icon-<?php echo strtolower($listDirn) == 'asc' ? 'caret-up' : 'caret-down'; ?>" aria-hidden="true"></span>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th scope="col">
                                <a href="#" onclick="Joomla.tableOrdering('title','<?php echo $listDirn == 'ASC' ? 'DESC' : 'ASC'; ?>',''); return false;" class="js-seo-sort" data-column="title" data-direction="<?php echo $listDirn == 'ASC' ? 'DESC' : 'ASC'; ?>">
                                    <?php echo Text::_('COM_JOOMLAHITS_TITLE'); ?>
                                    <?php if ($listOrder == 'title') : ?>
                                        <span class="ms-2 icon-<?php echo strtolower($listDirn) == 'asc' ? 'caret-up' : 'caret-down'; ?>" aria-hidden="true"></span>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th scope="col" class="w-15 d-none d-md-table-cell">
                                <a href="#" onclick="Joomla.tableOrdering('category','<?php echo $listDirn == 'ASC' ? 'DESC' : 'ASC'; ?>',''); return false;" class="js-seo-sort" data-column="category" data-direction="<?php echo $listDirn == 'ASC' ? 'DESC' : 'ASC'; ?>">
                                    <?php echo Text::_('COM_JOOMLAHITS_CATEGORY'); ?>
                                    <?php if ($listOrder == 'category') : ?>
                                        <span class="ms-2 icon-<?php echo strtolower($listDirn) == 'asc' ? 'caret-up' : 'caret-down'; ?>" aria-hidden="true"></span>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th scope="col" class="w-10 d-none d-md-table-cell text-center">
                                <a href="#" onclick="Joomla.tableOrdering('severity','<?php echo $listDirn == 'ASC' ? 'DESC' : 'ASC'; ?>',''); return false;" class="js-seo-sort" data-column="severity" data-direction="<?php echo $listDirn == 'ASC' ? 'DESC' : 'ASC'; ?>">
                                    <?php echo Text::_('COM_JOOMLAHITS_SEVERITY'); ?>
                                    <?php if ($listOrder == 'severity') : ?>
                                        <span class="ms-2 icon-<?php echo strtolower($listDirn) == 'asc' ? 'caret-up' : 'caret-down'; ?>" aria-hidden="true"></span>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th scope="col" class="w-35 d-none d-md-table-cell">
                                <?php echo Text::_('COM_JOOMLAHITS_SEOANALYSIS_ISSUES'); ?>
                            </th>
                            <th scope="col" class="w-10">
                                <?php echo Text::_('COM_JOOMLAHITS_ACTIONS'); ?>
                            </th>
                        </tr>
                    </thead>
                    <tbody id="results-tbody">
                    </tbody>
                </table>
                
                <input type="hidden" name="task" value="">
                <input type="hidden" name="boxchecked" value="0">
                <?php echo HTMLHelper::_('form.token'); ?>
                </form>
            </div>

            <!-- No Issues Message -->
            <div id="no-issues-section" style="display: none;">
                <div class="alert alert-success">
                    <span class="icon-checkmark" aria-hidden="true"></span>
                    <strong><?php echo Text::_('COM_JOOMLAHITS_SEOANALYSIS_CONGRATULATIONS'); ?></strong> <?php echo Text::_('COM_JOOMLAHITS_SEOANALYSIS_NO_ISSUES_FOUND'); ?>
                </div>
            </div>

            <!-- Force AI Processing Section -->
            <div id="force-ai-section" style="display: none;">
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card border-info">
                            <div class="card-header bg-info text-white">
                                <h4 class="card-title mb-0">
                                    <i class="icon-lightning"></i> <?php echo Text::_('COM_JOOMLAHITS_FORCE_AI_PROCESSING'); ?>
                                </h4>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <strong id="force-progress-title"><?php echo Text::_('COM_JOOMLAHITS_FORCE_AI_PROCESSING'); ?></strong>
                                    <button type="button" id="cancelForceAi" class="btn btn-sm btn-outline-danger" onclick="cancelForceAi()">
                                        <?php echo Text::_('COM_JOOMLAHITS_CANCEL'); ?>
                                    </button>
                                </div>
                                <div class="progress mb-2">
                                    <div id="force-ai-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated" 
                                         role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <div id="force-current-status" class="small"></div>
                                <div id="force-results-log" class="mt-2 small" style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; border-radius: 5px;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Force AI Results Section -->
            <div id="force-ai-results-section" style="display: none;">
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card border-success">
                            <div class="card-header bg-success text-white">
                                <h4 class="card-title mb-0">
                                    <i class="icon-checkmark"></i> <?php echo Text::_('COM_JOOMLAHITS_FORCE_AI_COMPLETED'); ?>
                                </h4>
                            </div>
                            <div class="card-body">
                                <div id="force-ai-summary" class="mb-3"></div>
                                <div class="d-flex justify-content-center gap-3">
                                    <button type="button" class="btn btn-danger btn-lg" onclick="cancelForceAiChanges()">
                                        <i class="icon-times me-2"></i><?php echo Text::_('COM_JOOMLAHITS_FORCE_AI_CANCEL'); ?>
                                    </button>
                                    <button type="button" class="btn btn-success btn-lg" onclick="saveForceAiChanges()">
                                        <i class="icon-checkmark me-2"></i><?php echo Text::_('COM_JOOMLAHITS_FORCE_AI_SAVE'); ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- SEO Fix Modal -->
<div class="modal fade" id="seoFixModal" tabindex="-1" aria-labelledby="seoFixModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content" style="height: 90vh;">
            <div class="modal-header">
                <h4 class="modal-title fw-bold" id="seoFixModalLabel">
                    <i class="icon-cog text-primary me-2"></i>
                    <?php echo Text::_('COM_JOOMLAHITS_SEO_FIX_MODAL_TITLE'); ?>
                </h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="overflow-y: auto; max-height: calc(90vh - 120px);">
                <form id="seoFixForm">
                    <input type="hidden" id="seo-article-id" name="article_id">
                    
                    <!-- Title Section -->
                    <div class="card mb-3">
                        <div class="card-body">
                            <label for="seo-title" class="form-label"><i class="icon-pencil-2 text-primary me-2"></i><?php echo Text::_('COM_JOOMLAHITS_TITLE'); ?></label>
                            <input type="text" class="form-control" id="seo-title" name="title" oninput="updateFieldCounters()">
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <div class="form-text mb-0">
                                    <span class="badge bg-secondary me-2"><span id="title-counter">0</span> <?php echo Text::_('COM_JOOMLAHITS_CHARACTERS'); ?></span>
                                    <span class="text-muted"><?php echo Text::_('COM_JOOMLAHITS_SEO_TITLE_HELP'); ?></span>
                                </div>
                                <span id="title-status"></span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Meta Description Section -->
                    <div class="card mb-3">
                        <div class="card-body">
                            <label for="seo-metadesc" class="form-label"><i class="icon-quote text-primary me-2"></i><?php echo Text::_('COM_JOOMLAHITS_CHECKSEO_META_DESCRIPTION'); ?></label>
                            <textarea class="form-control" id="seo-metadesc" name="metadesc" rows="3" maxlength="185" oninput="updateFieldCounters()" style="resize: vertical;"></textarea>
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <div class="form-text mb-0">
                                    <span class="badge bg-secondary me-2"><span id="metadesc-counter">0</span>/185</span>
                                    <span class="text-muted"><?php echo Text::_('COM_JOOMLAHITS_SEO_METADESC_HELP_185'); ?></span>
                                </div>
                                <span id="metadesc-status"></span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Meta Keywords Section -->
                    <div class="card mb-3">
                        <div class="card-body">
                            <label for="seo-metakey" class="form-label"><i class="icon-tags-2 text-primary me-2"></i><?php echo Text::_('COM_JOOMLAHITS_CHECKSEO_META_KEYWORDS'); ?></label>
                            <input type="text" class="form-control" id="seo-metakey" name="metakey" placeholder="<?php echo Text::_('COM_JOOMLAHITS_SEO_METAKEY_HELP'); ?>" oninput="updateFieldCounters()">
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <div class="form-text mb-0">
                                    <small class="text-muted"><?php echo Text::_('COM_JOOMLAHITS_SEO_METAKEY_HELP'); ?></small>
                                </div>
                                <span id="metakey-status"></span>
                            </div>
                        </div>
                    </div>
                    
                    
                    <!-- Content Section -->
                    <div class="card mb-3">
                        <div class="card-body">
                            <label for="seo-content" class="form-label"><i class="icon-file-text text-primary me-2"></i><?php echo Text::_('COM_JOOMLAHITS_SEO_CONTENT'); ?></label>
                            <textarea class="form-control" id="seo-content" name="content" rows="10" oninput="updateFieldCounters()" style="resize: vertical;"></textarea>
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <div class="form-text mb-0">
                                    <span class="badge bg-secondary me-2"><span id="content-counter">0</span> <?php echo Text::_('COM_JOOMLAHITS_CHARACTERS'); ?></span>
                                    <span class="badge bg-secondary me-2"><span id="words-counter">0</span> <?php echo Text::_('COM_JOOMLAHITS_WORDS'); ?></span>
                                    <span class="text-muted"><?php echo Text::_('COM_JOOMLAHITS_SEO_CONTENT_HELP'); ?></span>
                                </div>
                                <span id="content-status"></span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Issues Summary -->
                    <div id="seo-issues-list" class="card">
                        <div class="card-body">
                            <h6 class="card-title"><i class="icon-warning-circle text-warning me-2"></i><?php echo Text::_('COM_JOOMLAHITS_SEO_ISSUES_DETECTED'); ?></h6>
                            <ul id="issues-details" class="mb-0 ps-4"></ul>
                        </div>
                    </div>
                    
                    <!-- AI Preview Section (initially hidden) -->
                    <div id="ai-preview-section" class="card mt-3 border-warning" style="display: none;">
                        <div class="card-header bg-warning text-dark">
                            <h6 class="card-title mb-0">
                                <i class="icon-eye me-2"></i><?php echo Text::_('COM_JOOMLAHITS_AI_PREVISUALISATION'); ?>
                            </h6>
                            <small class="d-block mt-1">
                                <i class="icon-warning me-1"></i><strong><?php echo Text::_('COM_JOOMLAHITS_AI_ACTION_REQUIRED'); ?></strong> <?php echo Text::_('COM_JOOMLAHITS_AI_ACTION_REQUIRED_DESC'); ?>
                            </small>
                        </div>
                        <div class="card-body">
                            <div id="ai-preview-content">
                                <!-- Content will be populated by JavaScript -->
                            </div>
                            <div class="mt-3 text-center">
                                <div class="alert alert-info mb-3">
                                    <i class="icon-info me-2"></i><?php echo Text::_('COM_JOOMLAHITS_AI_CHOOSE_ACTION'); ?>
                                </div>
                                <button type="button" class="btn btn-success me-2" onclick="acceptAIChanges()">
                                    <i class="icon-checkmark me-1"></i><?php echo Text::_('COM_JOOMLAHITS_AI_ACCEPT_CHANGES'); ?>
                                </button>
                                <button type="button" class="btn btn-danger" onclick="rejectAIChanges()">
                                    <i class="icon-times me-1"></i><?php echo Text::_('COM_JOOMLAHITS_AI_REJECT_CHANGES'); ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">
                    <i class="icon-times me-2"></i><?php echo Text::_('COM_JOOMLAHITS_CANCEL'); ?>
                </button>
                <button type="button" class="btn btn-warning px-4 me-2" onclick="fixWithAI()" id="aiFixBtn">
                    <i class="icon-wand me-2"></i><?php echo Text::_('COM_JOOMLAHITS_AI_FIX_WITH_AI'); ?>
                </button>
                <button type="button" class="btn btn-primary px-4" onclick="saveSeoFixes()" id="saveSeoBtn">
                    <i class="icon-checkmark me-2"></i><?php echo Text::_('COM_JOOMLAHITS_SAVE_CHANGES'); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Include JavaScript files -->
<script src="<?php echo Uri::root() . 'administrator/components/com_joomlahits/tmpl/seoanalysis/js/variables.js'; ?>"></script>
<script src="<?php echo Uri::root() . 'administrator/components/com_joomlahits/tmpl/seoanalysis/js/notifications.js'; ?>"></script>
<script src="<?php echo Uri::root() . 'administrator/components/com_joomlahits/tmpl/seoanalysis/js/analysis.js'; ?>"></script>
<script src="<?php echo Uri::root() . 'administrator/components/com_joomlahits/tmpl/seoanalysis/js/display.js'; ?>"></script>
<script src="<?php echo Uri::root() . 'administrator/components/com_joomlahits/tmpl/seoanalysis/js/modal.js'; ?>"></script>
<script src="<?php echo Uri::root() . 'administrator/components/com_joomlahits/tmpl/seoanalysis/js/utils.js'; ?>"></script>
<script src="<?php echo Uri::root() . 'administrator/components/com_joomlahits/tmpl/seoanalysis/js/bulk-ai.js'; ?>"></script>
<script src="<?php echo Uri::root() . 'administrator/components/com_joomlahits/tmpl/seoanalysis/js/force-ai.js'; ?>"></script>

<script>
// Set global variable for admin URL
window.JOOMLA_ADMIN_URL = '<?php echo Uri::root(); ?>administrator';

// Set global language variables for statistics
window.JOOMLA_LANG_STATS = {
    withIssues: <?php echo json_encode(Text::_('COM_JOOMLAHITS_SEOANALYSIS_STATS_WITH_ISSUES')); ?>,
    perfect: <?php echo json_encode(Text::_('COM_JOOMLAHITS_SEOANALYSIS_STATS_PERFECT')); ?>
};
    function createTableRow(article, index) {
        var tr = document.createElement('tr');
        tr.className = 'row' + (index % 2);
        
        var severityClass = {
            'critical': 'bg-danger',
            'warning': 'bg-warning',
            'info': 'bg-info'
        }[article.severity];
        
        var severityText = {
            'critical': 'Critique',
            'warning': 'Attention',
            'info': 'Info'
        }[article.severity];
        
        var issuesBadges = '';
        for (var i = 0; i < article.issues.length; i++) {
            var issue = article.issues[i];
            var iconClass = {
                'exclamation-triangle': 'text-danger',
                'warning': 'text-warning',
                'info': 'text-info',
                'image': 'text-primary',
                'link': 'text-secondary',
                'tag': 'text-secondary'
            }[issue.icon] || 'text-muted';
            
            issuesBadges += '<span class="badge bg-light text-dark me-1 mb-1" title="' + issue.message + '">' +
                '<i class="icon-' + issue.icon + ' ' + iconClass + '"></i> ' + issue.message +
            '</span>';
        }
        
        tr.innerHTML = '<td class="text-center">' +
                '<input type="checkbox" id="cb' + index + '" name="cid[]" value="' + article.id + '" onclick="Joomla.isChecked(this.checked);">' +
            '</td>' +
            '<td class="d-none d-lg-table-cell">' + article.id + '</td>' +
            '<th scope="row" class="has-context"><div><strong>' + article.title + '</strong></div></th>' +
            '<td class="d-none d-md-table-cell">' + article.category + '</td>' +
            '<td class="d-none d-md-table-cell text-center"><span class="badge ' + severityClass + '">' + severityText + '</span></td>' +
            '<td class="d-none d-md-table-cell">' + issuesBadges + '</td>' +
            '<td class="text-center">' +
                '<div class="btn-group" role="group">' +
                    '<a href="index.php?option=com_content&task=article.edit&id=' + article.id + '" class="btn btn-sm btn-outline-primary" title="<?php echo htmlspecialchars(Text::_('COM_JOOMLAHITS_EDIT_ARTICLE'), ENT_QUOTES, 'UTF-8'); ?>"><i class="icon-edit"></i></a>' +
                    '<button type="button" class="btn btn-sm btn-outline-success" onclick="openSeoModal(' + article.id + ')" title="<?php echo htmlspecialchars(Text::_('COM_JOOMLAHITS_FIX_SEO'), ENT_QUOTES, 'UTF-8'); ?>"><i class="icon-cog"></i></button>' +
                '</div>' +
            '</td>';
        
        return tr;
    }

    function getArticlesList() {
        document.getElementById('current-analysis-status').textContent = 'Starting analysis...';
        
        // Load all articles directly without progressive loading - empty body triggers bulk analysis
        fetch('<?php echo Uri::root(); ?>administrator/components/com_joomlahits/direct_seo_analysis.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: ''
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
                currentAnalysisResults = data.data;
                articlesList = []; // Not needed anymore since we have complete results
                
                document.getElementById('current-analysis-status').textContent = 
                    'Analysis completed - ' + currentAnalysisResults.issues.length + ' articles with issues out of ' + currentAnalysisResults.total_articles;
                
                // Directly finish analysis since all data is loaded
                finishAnalysis();
            } else {
                showNotification('Error during analysis: ' + data.message, 'error');
                resetAnalysisUI();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error during analysis: ' + error.message, 'error');
            resetAnalysisUI();
        });
    }
    /**
     * Reset analysis UI
     */
    function resetAnalysisUI() {
        var btn = document.getElementById('startAnalysisBtn');
        btn.disabled = false;
        btn.innerHTML = '<?php echo Text::_('COM_JOOMLAHITS_START_FULL_ANALYSIS'); ?>';
    }


    function saveBulkSeoFixes() {
        var totalToSave = 0;
        Object.keys(bulkAiChanges).forEach(function(articleId) {
            if (bulkAiChanges[articleId].accepted) {
                totalToSave++;
            }
        });
        
        showNotification('Saving ' + totalToSave + ' articles in progress...', 'info');
        
        var savePromises = [];
        var resultsToSave = [];
        
        // Prepare all articles to save
        Object.keys(bulkAiChanges).forEach(function(articleId) {
            var change = bulkAiChanges[articleId];
            if (change.accepted) {
                resultsToSave.push({
                    articleId: articleId,
                    changes: change.finalValues,
                    title: bulkAiArticles.find(function(article) { return article.id == articleId; }).title
                });
            }
        });
        
        if (resultsToSave.length === 0) {
            showNotification('No articles to save', 'warning');
            return;
        }
        
        var currentSaveIndex = 0;
        var successCount = 0;
        var errorCount = 0;
        
        function saveNextArticle() {
            if (currentSaveIndex >= resultsToSave.length) {
                // All articles processed
                finishBulkSave(successCount, errorCount);
                return;
            }
            
            var articleToSave = resultsToSave[currentSaveIndex];
            
            // Create FormData for this article
            var formData = new FormData();
            formData.append('article_id', articleToSave.articleId);
            formData.append('title', articleToSave.changes.title);
            formData.append('metadesc', articleToSave.changes.metadesc);
            formData.append('metakey', articleToSave.changes.metakey);
            
            fetch('<?php echo Uri::root(); ?>administrator/components/com_joomlahits/direct_seo_fix.php', {
                method: 'POST',
                body: formData
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
                    successCount++;
                } else {
                    errorCount++;
                    console.error('Erreur pour "' + articleToSave.title + '": ' + data.message);
                }
                
                currentSaveIndex++;
                setTimeout(saveNextArticle, 500); // Small delay between saves
            })
            .catch(error => {
                errorCount++;
                console.error('Save error for "' + articleToSave.title + '": ' + error.message);
                
                currentSaveIndex++;
                setTimeout(saveNextArticle, 500);
            });
        }
        
        // Start saving
        saveNextArticle();
    }
    function saveForceAiArticlesSequentially(articlesToSave) {
        var currentSaveIndex = 0;
        var successCount = 0;
        var errorCount = 0;
        
        function saveNextArticle() {
            if (currentSaveIndex >= articlesToSave.length) {
                // All articles saved
                var message;
                if (errorCount === 0) {
                    message = 'Force AI: ' + successCount + ' articles saved successfully';
                    showNotification(message, 'success');
                } else if (successCount === 0) {
                    message = 'Force AI: Failed to save all articles (' + errorCount + ' errors)';
                    showNotification(message, 'error');
                } else {
                    message = 'Force AI: ' + successCount + ' articles saved, ' + errorCount + ' errors';
                    showNotification(message, 'warning');
                }
                
                resetForceAiUI();
                
                // Refresh analysis
                setTimeout(function() {
                    startAnalysis();
                }, 2000);
                return;
            }
            
            var articleToSave = articlesToSave[currentSaveIndex];
            
            var formData = new FormData();
            formData.append('article_id', articleToSave.articleId);
            formData.append('title', articleToSave.changes.title);
            formData.append('metadesc', articleToSave.changes.metadesc);
            formData.append('metakey', articleToSave.changes.metakey);
            
            fetch('<?php echo Uri::root(); ?>administrator/components/com_joomlahits/direct_seo_fix.php', {
                method: 'POST',
                body: formData
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
                    successCount++;
                } else {
                    errorCount++;
                    console.error('Error saving "' + articleToSave.title + '": ' + data.message);
                }
                
                currentSaveIndex++;
                setTimeout(saveNextArticle, 300);
            })
            .catch(error => {
                errorCount++;
                console.error('Save error for "' + articleToSave.title + '": ' + error.message);
                
                currentSaveIndex++;
                setTimeout(saveNextArticle, 300);
            });
        }
        
        saveNextArticle();
    }
    function saveSingleSeoFixes() {
        // Check if we can save
        if (aiPreviewState === 'pending') {
            showNotification('You must first accept or cancel AI changes before saving.', 'warning');
            return;
        }
        
        var form = document.getElementById('seoFixForm');
        var formData = new FormData(form);
        
        fetch('<?php echo Uri::root(); ?>administrator/components/com_joomlahits/direct_seo_fix.php', {
            method: 'POST',
            body: formData
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
                showNotification('SEO fixes have been saved successfully', 'success');
                seoModal.hide();
                // Relancer l'analyse pour cet article
                updateSingleArticle(currentArticleData.id);
            } else {
                showNotification('Error: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            showNotification('Save error: ' + error.message, 'error');
        });
    }

    function updateSingleArticle(articleId) {
        fetch('<?php echo Uri::root(); ?>administrator/components/com_joomlahits/direct_seo_analysis.php', {
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
                // Update article in results
                for (var i = 0; i < filteredResults.length; i++) {
                    if (filteredResults[i].id == articleId) {
                        if (data.data.issues && data.data.issues.length > 0) {
                            filteredResults[i] = data.data;
                        } else {
                            // No more issues, remove from list
                            filteredResults.splice(i, 1);
                        }
                        break;
                    }
                }
                
                // Update analysisResults too
                for (var j = 0; j < analysisResults.issues.length; j++) {
                    if (analysisResults.issues[j].id == articleId) {
                        if (data.data.issues && data.data.issues.length > 0) {
                            analysisResults.issues[j] = data.data;
                        } else {
                            analysisResults.issues.splice(j, 1);
                        }
                        break;
                    }
                }
                
                // Redisplay table
                populateTable(filteredResults);
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
        });
    }
    
    function fixWithAI() {
        if (!currentArticleData) {
            showNotification('No article selected', 'warning');
            return;
        }
        
        var aiBtn = document.getElementById('aiFixBtn');
        var originalText = aiBtn.innerHTML;
        
        // Store original values for preview
        window.originalValues = {
            title: document.getElementById('seo-title').value,
            metadesc: document.getElementById('seo-metadesc').value,
            metakey: document.getElementById('seo-metakey').value
        };
        
        // Store in bulk changes if in bulk mode
        if (isBulkAiProcessing && currentArticleData) {
            var articleId = currentArticleData.id;
            if (bulkAiChanges[articleId]) {
                bulkAiChanges[articleId].originalValues = window.originalValues;
            }
        }
        
        window.aiOptimizedValues = {};
        
        // Disable button and show loading
        aiBtn.disabled = true;
        aiBtn.innerHTML = '<i class="icon-refresh icon-spin me-2"></i>IA en cours...';
        
        // List of fields to process (content disabled as AI too unpredictable, alias removed)
        var fields = ['title', 'metadesc', 'metakey'];
        var currentFieldIndex = 0;
        
        function processNextField() {
            if (currentFieldIndex >= fields.length) {
                // All fields have been processed
                aiBtn.disabled = false;
                aiBtn.innerHTML = originalText;
                updateFieldCounters();
                
                // Show preview only if we have optimized values
                if (Object.keys(window.aiOptimizedValues).length > 0) {
                    showAIPreview();
                    // Set state to "pending"
                    aiPreviewState = 'pending';
                    updateSaveButtonState();
                } else {
                    showNotification('Tous les champs sont déjà optimaux !', 'success');
                }
                return;
            }
            
            var fieldType = fields[currentFieldIndex];
            
            // Check if this field has issues
            if (!fieldHasIssues(fieldType, currentArticleData)) {
                currentFieldIndex++;
                processNextField();
                return;
            }
            aiBtn.innerHTML = '<i class="icon-refresh icon-spin me-2"></i>IA: ' + getFieldLabel(fieldType) + '...';
            
            fetch('<?php echo Uri::root(); ?>administrator/components/com_joomlahits/direct_ai_seo_fix.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'article_id=' + encodeURIComponent(currentArticleData.id) + '&field_type=' + encodeURIComponent(fieldType)
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
                    if (data.skipped) {
                        // Field was skipped (metakey already sufficient)
                        console.log('Field ' + fieldType + ' skipped: ' + data.message);
                    } else if (data.field_value) {
                        // Store optimized value for preview
                        window.aiOptimizedValues[fieldType] = data.field_value;
                        
                        // Store in bulk changes if in bulk mode
                        if (isBulkAiProcessing && currentArticleData) {
                            var articleId = currentArticleData.id;
                            if (bulkAiChanges[articleId]) {
                                bulkAiChanges[articleId].aiValues[fieldType] = data.field_value;
                            }
                        }
                        
                        // Remplir le champ correspondant (temporairement)
                        var fieldElement = document.getElementById('seo-' + fieldType);
                        if (fieldElement) {
                            fieldElement.value = data.field_value;
                        }
                    }
                } else {
                    console.error('Error for ' + fieldType + ':', data.message);
                }
                
                // Passer au champ suivant
                currentFieldIndex++;
                setTimeout(processNextField, 500); // Delay between calls
            })
            .catch(error => {
                console.error('Error for ' + fieldType + ':', error.message);
                
                // Move to next field even on error
                currentFieldIndex++;
                setTimeout(processNextField, 500);
            });
        }
        
        // Commencer le traitement
        processNextField();
    }

    function processAllFieldsForArticle(article) {
        var fields = ['title', 'metadesc', 'metakey'];
        var currentFieldIndex = 0;
        var articleData = forceAiChanges[article.id];
        
        function processNextField() {
            if (forceAiCancelled) {
                return;
            }
            
            if (currentFieldIndex >= fields.length) {
                // All fields processed for this article
                currentForceAiIndex++;
                setTimeout(processNextForceAiArticle, 300);
                return;
            }
            
            var fieldType = fields[currentFieldIndex];
            var resultsLog = document.getElementById('force-results-log');
            
            // Check if this field has issues
            if (!fieldHasIssues(fieldType, article)) {
                resultsLog.innerHTML += '<div class="text-info">' +
                    '<i class="icon-info"></i> ' + article.title + ' - ' + fieldType + ' already optimal' +
                '</div>';
                
                articleData.fieldsProcessed++;
                currentFieldIndex++;
                setTimeout(processNextField, 100);
                return;
            }
            
            fetch('<?php echo Uri::root(); ?>administrator/components/com_joomlahits/direct_ai_seo_fix.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'article_id=' + encodeURIComponent(article.id) + '&field_type=' + encodeURIComponent(fieldType)
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
                    if (data.skipped) {
                        // Field was skipped (metakey already sufficient)
                        resultsLog.innerHTML += '<div class="text-info">' +
                            '<i class="icon-info"></i> ' + article.title + ' - ' + fieldType + ' skipped: ' + (data.message || 'Already optimal') +
                        '</div>';
                    } else if (data.field_value) {
                        articleData.aiValues[fieldType] = data.field_value;
                        resultsLog.innerHTML += '<div class="text-success">' +
                            '<i class="icon-checkmark"></i> ' + article.title + ' - ' + fieldType + ' processed' +
                        '</div>';
                    }
                } else {
                    resultsLog.innerHTML += '<div class="text-warning">' +
                        '<i class="icon-warning"></i> ' + article.title + ' - ' + fieldType + ' failed: ' + (data.message || 'Unknown error') +
                    '</div>';
                }
                
                articleData.fieldsProcessed++;
                resultsLog.scrollTop = resultsLog.scrollHeight;
                
                currentFieldIndex++;
                setTimeout(processNextField, 200); // Short delay between field processing
            })
            .catch(error => {
                console.error('Error processing field:', error);
                resultsLog.innerHTML += '<div class="text-danger">' +
                    '<i class="icon-warning"></i> ' + article.title + ' - ' + fieldType + ' error: ' + error.message +
                '</div>';
                resultsLog.scrollTop = resultsLog.scrollHeight;
                
                articleData.fieldsProcessed++;
                currentFieldIndex++;
                setTimeout(processNextField, 200);
            });
        }
        
        processNextField();
    }

    function updateFieldCounters() {
    // Title
    var title = document.getElementById('seo-title');
    var titleLength = title.value.length;
    var titleCounter = document.getElementById('title-counter');
    var titleStatus = document.getElementById('title-status');
    
    titleCounter.textContent = titleLength;
    
    if (titleLength === 0) {
        titleStatus.innerHTML = '<span class="text-danger"><i class="icon-warning"></i> <?php echo Text::_('COM_JOOMLAHITS_SEOANALYSIS_TITLE_MISSING'); ?></span>';
    } else if (titleLength < 30) {
        titleStatus.innerHTML = '<span class="text-warning"><i class="icon-warning"></i> <?php echo Text::_('COM_JOOMLAHITS_SEOANALYSIS_TITLE_TOO_SHORT'); ?></span>';
    } else if (titleLength > 70) {
        titleStatus.innerHTML = '<span class="text-warning"><i class="icon-warning"></i> <?php echo Text::_('COM_JOOMLAHITS_SEOANALYSIS_TITLE_TOO_LONG'); ?></span>';
    } else {
        titleStatus.innerHTML = '<span class="text-success"><i class="icon-checkmark"></i> <?php echo Text::_('COM_JOOMLAHITS_SEO_OPTIMAL'); ?></span>';
    }
    
    // Meta description
    var metadesc = document.getElementById('seo-metadesc');
    var metadescLength = metadesc.value.length;
    var metadescCounter = document.getElementById('metadesc-counter');
    var metadescStatus = document.getElementById('metadesc-status');
    
    metadescCounter.textContent = metadescLength;
    
    if (metadescLength === 0) {
        metadescStatus.innerHTML = '<span class="text-danger"><i class="icon-warning"></i> <?php echo Text::_('COM_JOOMLAHITS_SEOANALYSIS_META_DESC_MISSING'); ?></span>';
    } else if (metadescLength < 120) {
        metadescStatus.innerHTML = '<span class="text-warning"><i class="icon-warning"></i> <?php echo Text::_('COM_JOOMLAHITS_SEOANALYSIS_META_DESC_TOO_SHORT'); ?></span>';
    } else if (metadescLength > 185) {
        metadescStatus.innerHTML = '<span class="text-warning"><i class="icon-warning"></i> <?php echo Text::_('COM_JOOMLAHITS_SEOANALYSIS_META_DESC_TOO_LONG'); ?></span>';
    } else {
        metadescStatus.innerHTML = '<span class="text-success"><i class="icon-checkmark"></i> <?php echo Text::_('COM_JOOMLAHITS_SEO_OPTIMAL'); ?></span>';
    }
    
    // Meta Keywords
    var metakey = document.getElementById('seo-metakey');
    var metakeyValue = metakey.value.trim();
    var metakeyStatus = document.getElementById('metakey-status');
    
    if (metakeyValue === '') {
        metakeyStatus.innerHTML = '<span class="text-warning"><i class="icon-warning"></i> <?php echo Text::_('COM_JOOMLAHITS_SEO_METAKEY_MISSING'); ?></span>';
    } else {
        var keywords = metakeyValue.split(',').filter(function(k) { return k.trim(); });
        if (keywords.length < 3) {
            metakeyStatus.innerHTML = '<span class="text-warning"><i class="icon-warning"></i> <?php echo Text::_('COM_JOOMLAHITS_SEO_METAKEY_TOO_FEW'); ?></span>';
        } else {
            metakeyStatus.innerHTML = '<span class="text-success"><i class="icon-checkmark"></i> <?php echo Text::_('COM_JOOMLAHITS_SEO_OPTIMAL'); ?></span>';
        }
    }
    
    // Content
    var content = document.getElementById('seo-content');
    var contentText = content.value;
    var contentLength = contentText.length;
    var wordsCount = contentText.trim() ? contentText.trim().split(/\s+/).length : 0;
    var contentCounter = document.getElementById('content-counter');
    var wordsCounter = document.getElementById('words-counter');
    var contentStatus = document.getElementById('content-status');
    
    contentCounter.textContent = contentLength;
    wordsCounter.textContent = wordsCount;
    
    var hasH1 = /<h1[^>]*>/i.test(contentText);
    
    if (wordsCount < 300) {
        contentStatus.innerHTML = '<span class="text-warning"><i class="icon-warning"></i> <?php echo Text::_('COM_JOOMLAHITS_SEOANALYSIS_CONTENT_TOO_SHORT'); ?></span>';
    } else if (!hasH1) {
        contentStatus.innerHTML = '<span class="text-warning"><i class="icon-warning"></i> <?php echo Text::_('COM_JOOMLAHITS_SEOANALYSIS_MISSING_H1'); ?></span>';
    } else {
        contentStatus.innerHTML = '<span class="text-success"><i class="icon-checkmark"></i> <?php echo Text::_('COM_JOOMLAHITS_SEO_OPTIMAL'); ?></span>';
    }
    
    // Update issues list
    updateIssuesList();
}
function updateIssuesList() {
    var issuesList = document.getElementById('issues-details');
    issuesList.innerHTML = '';
    
    var hasIssues = false;
    
    // Check title
    var titleLength = document.getElementById('seo-title').value.length;
    if (titleLength === 0) {
        addIssue(issuesList, '<?php echo Text::_('COM_JOOMLAHITS_SEOANALYSIS_TITLE_MISSING'); ?>', 'danger');
        hasIssues = true;
    } else if (titleLength < 30) {
        addIssue(issuesList, '<?php echo Text::_('COM_JOOMLAHITS_SEOANALYSIS_TITLE_TOO_SHORT'); ?> (' + titleLength + ' <?php echo Text::_('COM_JOOMLAHITS_CHARACTERS'); ?>)', 'warning');
        hasIssues = true;
    } else if (titleLength > 70) {
        addIssue(issuesList, '<?php echo Text::_('COM_JOOMLAHITS_SEOANALYSIS_TITLE_TOO_LONG'); ?> (' + titleLength + ' <?php echo Text::_('COM_JOOMLAHITS_CHARACTERS'); ?>)', 'warning');
        hasIssues = true;
    }
    
    // Check meta description
    var metadescLength = document.getElementById('seo-metadesc').value.length;
    if (metadescLength === 0) {
        addIssue(issuesList, '<?php echo Text::_('COM_JOOMLAHITS_SEOANALYSIS_META_DESC_MISSING'); ?>', 'danger');
        hasIssues = true;
    } else if (metadescLength < 120) {
        addIssue(issuesList, '<?php echo Text::_('COM_JOOMLAHITS_SEOANALYSIS_META_DESC_TOO_SHORT'); ?> (' + metadescLength + ' <?php echo Text::_('COM_JOOMLAHITS_CHARACTERS'); ?>)', 'warning');
        hasIssues = true;
    } else if (metadescLength > 185) {
        addIssue(issuesList, '<?php echo Text::_('COM_JOOMLAHITS_SEOANALYSIS_META_DESC_TOO_LONG'); ?> (' + metadescLength + ' <?php echo Text::_('COM_JOOMLAHITS_CHARACTERS'); ?>)', 'warning');
        hasIssues = true;
    }
    
    
    // Check content
    var contentText = document.getElementById('seo-content').value;
    var wordsCount = contentText.trim() ? contentText.trim().split(/\s+/).length : 0;
    var hasH1 = /<h1[^>]*>/i.test(contentText);
    
    if (wordsCount < 300) {
        addIssue(issuesList, '<?php echo Text::_('COM_JOOMLAHITS_SEOANALYSIS_CONTENT_TOO_SHORT'); ?> (' + wordsCount + ' <?php echo Text::_('COM_JOOMLAHITS_WORDS'); ?>)', 'warning');
        hasIssues = true;
    }
    
    if (!hasH1) {
        addIssue(issuesList, '<?php echo Text::_('COM_JOOMLAHITS_SEOANALYSIS_MISSING_H1'); ?>', 'warning');
        hasIssues = true;
    }
    
    // Check keywords
    var metakey = document.getElementById('seo-metakey').value;
    if (!metakey) {
        addIssue(issuesList, '<?php echo Text::_('COM_JOOMLAHITS_SEOANALYSIS_META_KEYWORDS_MISSING'); ?>', 'info');
        hasIssues = true;
    }
    
    if (!hasIssues) {
        var li = document.createElement('li');
        li.className = 'text-success';
        li.innerHTML = '<i class="icon-checkmark"></i> <?php echo Text::_('COM_JOOMLAHITS_SEO_ALL_GOOD'); ?>';
        issuesList.appendChild(li);
    }
}

/**
 * Update save button state for single article
 */
function updateSaveButtonState() {
    if (isBulkAiProcessing) {
        updateBulkSaveButtonState();
        return;
    }
    
    var saveBtn = document.getElementById('saveSeoBtn');
    
    if (aiPreviewState === 'pending') {
        // En attente d'acceptation/refus des modifications IA
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="icon-warning me-2"></i>' + '<?php echo Text::_('COM_JOOMLAHITS_AI_ACCEPT_OR_CANCEL'); ?>';
        saveBtn.className = 'btn btn-warning px-4';
        saveBtn.title = '<?php echo Text::_('COM_JOOMLAHITS_AI_ACCEPT_OR_CANCEL_BEFORE_SAVE'); ?>';
    } else {
        // Normal or after acceptance/rejection
        saveBtn.disabled = false;
        saveBtn.innerHTML = '<i class="icon-checkmark me-2"></i><?php echo Text::_('COM_JOOMLAHITS_SAVE_CHANGES'); ?>';
        saveBtn.className = 'btn btn-primary px-4';
        saveBtn.title = '';
    }
}

function openBulkSeoModal() {
    if (currentBulkArticleIndex >= bulkAiArticles.length) {
        // All articles processed, switch to review phase
        if (bulkProcessingPhase === 'editing') {
            startBulkReviewPhase();
        } else {
            finishBulkAiFix();
        }
        return;
    }
    
    var article = bulkAiArticles[currentBulkArticleIndex];
    currentArticleData = article;
    
    // Load stored changes if they exist
    var storedChange = bulkAiChanges[article.id];
    if (storedChange && storedChange.accepted) {
        // Restore previously accepted changes
        aiPreviewState = 'accepted';
        window.originalValues = storedChange.originalValues;
        window.aiOptimizedValues = storedChange.aiValues;
    } else {
        // Reset AI preview state
        aiPreviewState = null;
        window.originalValues = {};
        window.aiOptimizedValues = {};
    }
    
    document.getElementById('ai-preview-section').style.display = 'none';
    updateBulkSaveButtonState();
    
    // Update modal title to show progress and phase
    var modalTitle = document.getElementById('seoFixModalLabel');
    var phaseText = bulkProcessingPhase === 'editing' ? 'Editing' : 'Final review';
    modalTitle.innerHTML = '<i class="icon-cog text-primary me-2"></i>' +
        '<?php echo Text::_('COM_JOOMLAHITS_SEO_FIX_MODAL_TITLE'); ?> - ' + phaseText + ' - Article ' +
        (currentBulkArticleIndex + 1) + '/' + bulkAiArticles.length;
    
    // Show loading state first
    document.getElementById('seo-article-id').value = article.id;
    document.getElementById('seo-title').value = 'Loading...';
    document.getElementById('seo-metadesc').value = 'Loading...';
    document.getElementById('seo-metakey').value = 'Loading...';
    document.getElementById('seo-content').value = 'Loading...';
    
    // Open modal first
    if (!seoModal) {
        seoModal = new bootstrap.Modal(document.getElementById('seoFixModal'));
    }
    seoModal.show();
    
    // Fetch complete article data with all fields
    fetch(window.JOOMLA_ADMIN_URL + '/components/com_joomlahits/direct_seo_analysis.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'article_id=' + encodeURIComponent(article.id)
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
        if (data.success && data.data) {
            var fullArticle = data.data;
            
            // Update currentArticleData with complete data
            currentArticleData = {
                id: fullArticle.id,
                title: fullArticle.title,
                alias: fullArticle.alias,
                metadesc: fullArticle.metadesc,
                metakey: fullArticle.metakey,
                content: fullArticle.content,
                category: fullArticle.category,
                language: fullArticle.language,
                hits: fullArticle.hits,
                issues: article.issues // Keep original issues from analysis
            };
            
            // Fill form with complete data
            document.getElementById('seo-title').value = fullArticle.title || '';
            document.getElementById('seo-metadesc').value = fullArticle.metadesc || '';
            document.getElementById('seo-metakey').value = fullArticle.metakey || '';
            document.getElementById('seo-content').value = fullArticle.content || '';
            
            // Update counters
            updateFieldCounters();
            
            // Display issues and bulk info after data is loaded
            displayBulkModalContent();
        } else {
            showNotification('Error loading article details: ' + (data.message || 'Unknown error'), 'error');
            // Fallback to basic data
            document.getElementById('seo-title').value = article.title || '';
            document.getElementById('seo-metadesc').value = article.metadesc || '';
            document.getElementById('seo-metakey').value = article.metakey || '';
            document.getElementById('seo-content').value = article.content || '';
            updateFieldCounters();
            
            // Display issues and bulk info even with fallback data
            displayBulkModalContent();
        }
    })
    .catch(error => {
        console.error('Error loading article:', error);
        showNotification('Error loading article details: ' + error.message, 'error');
        // Fallback to basic data
        document.getElementById('seo-title').value = article.title || '';
        document.getElementById('seo-metadesc').value = article.metadesc || '';
        document.getElementById('seo-metakey').value = article.metakey || '';
        document.getElementById('seo-content').value = article.content || '';
        updateFieldCounters();
        
        // Display issues and bulk info even with error
        displayBulkModalContent();
    });
    
    function displayBulkModalContent() {
        // Display issues
        var issuesList = document.getElementById('issues-details');
        issuesList.innerHTML = '';
        for (var j = 0; j < article.issues.length; j++) {
            var li = document.createElement('li');
            li.textContent = article.issues[j].message;
            issuesList.appendChild(li);
        }
        
        // Add bulk progress info with navigation arrows
        var bulkInfo = document.createElement('div');
        bulkInfo.id = 'bulk-progress-info';
        bulkInfo.className = 'alert alert-info mb-3';
        
        var prevDisabled = currentBulkArticleIndex === 0 ? 'disabled' : '';
        var nextDisabled = currentBulkArticleIndex === bulkAiArticles.length - 1 ? 'disabled' : '';
        
        bulkInfo.innerHTML = '<div class="d-flex justify-content-between align-items-center">' +
            '<div>' +
                '<i class="icon-info me-2"></i><strong>Bulk fix (' + phaseText + '):</strong><br>' +
                'Article ' + (currentBulkArticleIndex + 1) + ' sur ' + bulkAiArticles.length + ' - "' + article.title + '"' +
            '</div>' +
            '<div class="btn-group" role="group">' +
                '<button type="button" class="btn btn-sm btn-outline-secondary" onclick="navigateBulkArticle(-1)" ' + prevDisabled + '>' +
                    '<i class="icon-arrow-left"></i> Previous' +
                '</button>' +
                '<button type="button" class="btn btn-sm btn-outline-secondary" onclick="navigateBulkArticle(1)" ' + nextDisabled + '>' +
                    'Next <i class="icon-arrow-right"></i>' +
                '</button>' +
            '</div>' +
        '</div>';
        
        var form = document.getElementById('seoFixForm');
        var existingInfo = document.getElementById('bulk-progress-info');
        if (existingInfo) {
            existingInfo.remove();
        }
        form.insertBefore(bulkInfo, form.firstChild);
    }
    
    // Automatically start AI fix for this article only if not already processed and in editing phase
    if (bulkProcessingPhase === 'editing' && (!storedChange || !storedChange.accepted)) {
        setTimeout(function() {
            fixWithAI();
        }, 500);
    } else if (storedChange && storedChange.accepted) {
        // Restore the preview if changes were already accepted
        restoreAcceptedChanges(storedChange);
    }
    
    // Open modal
    if (!seoModal) {
        seoModal = new bootstrap.Modal(document.getElementById('seoFixModal'));
    }
    seoModal.show();
}

function cancelForceAiChanges() {
    if (confirm('<?php echo Text::_('COM_JOOMLAHITS_FORCE_AI_CONFIRM_CANCEL'); ?>')) {
        resetForceAiUI();
        showNotification('Force AI changes cancelled', 'info');
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    var startBtn = document.getElementById('startAnalysisBtn');
    if (startBtn) {
        startBtn.addEventListener('click', startAnalysis);
    }
    
    // Initialize sorting icons for default sort (severity ASC)
    updateSortingIcons('severity', 'asc');
});
</script>