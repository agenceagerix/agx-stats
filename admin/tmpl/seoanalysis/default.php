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
$listOrder = 'severity';
$listDirn = 'ASC';
?>

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
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <strong id="progress-title"><?php echo Text::_('COM_JOOMLAHITS_ANALYZING_ARTICLES'); ?></strong>
                                <button type="button" id="cancelAnalysis" class="btn btn-sm btn-outline-danger" onclick="cancelAnalysis()">
                                    <?php echo Text::_('COM_JOOMLAHITS_CANCEL'); ?>
                                </button>
                            </div>
                            <div class="progress mb-2">
                                <div id="analysis-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated" 
                                     role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <div id="current-analysis-status" class="small"></div>
                            <div id="analysis-results-log" class="mt-2 small" style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; border-radius: 5px;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Results Section -->
            <div id="results-section" style="display: none;">
                

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
                                            <option value="url_too_long"><?php echo Text::_('COM_JOOMLAHITS_SEOANALYSIS_URL_TOO_LONG'); ?></option>
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

                <!-- Results Table -->
                <table class="table" id="seoResultsList">
                    <caption class="visually-hidden">
                        <?php echo Text::_('COM_JOOMLAHITS_SEOANALYSIS_TABLE_CAPTION'); ?>
                    </caption>
                    <thead>
                        <tr>
                            <th scope="col" class="w-5 d-none d-lg-table-cell">
                                <a href="#" onclick="Joomla.tableOrdering('id','<?php echo $listDirn == 'ASC' ? 'DESC' : 'ASC'; ?>','')">
                                    ID <?php if ($listOrder == 'id') echo $listDirn == 'ASC' ? '↑' : '↓'; ?>
                                </a>
                            </th>
                            <th scope="col">
                                <a href="#" onclick="Joomla.tableOrdering('title','<?php echo $listDirn == 'ASC' ? 'DESC' : 'ASC'; ?>','')">
                                    <?php echo Text::_('COM_JOOMLAHITS_TITLE'); ?> <?php if ($listOrder == 'title') echo $listDirn == 'ASC' ? '↑' : '↓'; ?>
                                </a>
                            </th>
                            <th scope="col" class="w-15 d-none d-md-table-cell">
                                <a href="#" onclick="Joomla.tableOrdering('category','<?php echo $listDirn == 'ASC' ? 'DESC' : 'ASC'; ?>','')">
                                    <?php echo Text::_('COM_JOOMLAHITS_CATEGORY'); ?> <?php if ($listOrder == 'category') echo $listDirn == 'ASC' ? '↑' : '↓'; ?>
                                </a>
                            </th>
                            <th scope="col" class="w-10 d-none d-md-table-cell text-center">
                                <a href="#" onclick="Joomla.tableOrdering('severity','<?php echo $listDirn == 'ASC' ? 'DESC' : 'ASC'; ?>','')">
                                    <?php echo Text::_('COM_JOOMLAHITS_SEVERITY'); ?> <?php if ($listOrder == 'severity') echo $listDirn == 'ASC' ? '↑' : '↓'; ?>
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
            </div>

            <!-- No Issues Message -->
            <div id="no-issues-section" style="display: none;">
                <div class="alert alert-success">
                    <span class="icon-checkmark" aria-hidden="true"></span>
                    <strong><?php echo Text::_('COM_JOOMLAHITS_SEOANALYSIS_CONGRATULATIONS'); ?></strong> <?php echo Text::_('COM_JOOMLAHITS_SEOANALYSIS_NO_ISSUES_FOUND'); ?>
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
                            <input type="text" class="form-control" id="seo-metakey" name="metakey" placeholder="<?php echo Text::_('COM_JOOMLAHITS_SEO_METAKEY_HELP'); ?>">
                            <div class="form-text mt-2">
                                <small class="text-muted"><?php echo Text::_('COM_JOOMLAHITS_SEO_METAKEY_HELP'); ?></small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- URL Alias Section -->
                    <div class="card mb-3">
                        <div class="card-body">
                            <label for="seo-alias" class="form-label"><i class="icon-link text-primary me-2"></i><?php echo Text::_('COM_JOOMLAHITS_ALIAS'); ?></label>
                            <input type="text" class="form-control" id="seo-alias" name="alias" oninput="updateFieldCounters()">
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <div class="form-text mb-0">
                                    <span class="badge bg-secondary me-2"><span id="alias-counter">0</span> <?php echo Text::_('COM_JOOMLAHITS_CHARACTERS'); ?></span>
                                    <span class="text-muted"><?php echo Text::_('COM_JOOMLAHITS_SEO_ALIAS_HELP'); ?></span>
                                </div>
                                <span id="alias-status"></span>
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

<style>
.badge {
    font-size: 0.75em;
}

.table td {
    vertical-align: middle;
}

.icon-spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

#ai-preview-section .card-body {
    word-wrap: break-word;
}

#ai-preview-section .border-danger {
    border-left: 4px solid #dc3545 !important;
}

#ai-preview-section .border-success {
    border-left: 4px solid #198754 !important;
}

#ai-preview-section .text-dark.fw-bold {
    background-color: #d1e7dd;
    padding: 2px 4px;
    border-radius: 3px;
}

/* Améliorer l'apparence du modal avec scroll */
#seoFixModal .modal-body {
    padding: 1.5rem;
}

#seoFixModal .modal-body::-webkit-scrollbar {
    width: 8px;
}

#seoFixModal .modal-body::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

#seoFixModal .modal-body::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;
}

#seoFixModal .modal-body::-webkit-scrollbar-thumb:hover {
    background: #555;
}

/* Simple notification animations */
@keyframes fadeInRight {
    from {
        opacity: 0;
        transform: translateX(300px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes fadeOutRight {
    from {
        opacity: 1;
        transform: translateX(0);
    }
    to {
        opacity: 0;
        transform: translateX(300px);
    }
}

#joomla-notification-container .alert {
    border-radius: 4px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

</style>

<script>
// Simple notification system using Joomla
function showNotification(message, type) {
    // Create notification container if it doesn't exist
    var container = document.getElementById('joomla-notification-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'joomla-notification-container';
        container.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; max-width: 400px;';
        document.body.appendChild(container);
    }
    
    // Create notification
    var notification = document.createElement('div');
    var alertClass = 'alert-info';
    var iconClass = 'icon-info';
    
    if (type === 'success') {
        alertClass = 'alert-success';
        iconClass = 'icon-checkmark';
    } else if (type === 'error') {
        alertClass = 'alert-danger';
        iconClass = 'icon-warning';
    } else if (type === 'warning') {
        alertClass = 'alert-warning';
        iconClass = 'icon-warning';
    }
    
    notification.className = 'alert ' + alertClass + ' alert-dismissible';
    notification.style.cssText = 'margin-bottom: 10px; animation: fadeInRight 0.5s ease;';
    notification.innerHTML = '<i class="' + iconClass + '"></i> ' + message + 
                           '<button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>';
    
    container.appendChild(notification);
    
    // Auto-remove after 5 seconds
    setTimeout(function() {
        if (notification.parentNode) {
            notification.style.animation = 'fadeOutRight 0.5s ease';
            setTimeout(function() {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 500);
        }
    }, 5000);
}

// Global variables
var analysisResults = [];
var filteredResults = [];
var isAnalysisCancelled = false;
var currentSort = { column: null, direction: 'asc' };
var currentAnalysisResults = {
    total_articles: 0,
    issues: [],
    stats: {
        title_issues: 0,
        meta_description_issues: 0,
        content_issues: 0,
        image_issues: 0,
        url_issues: 0
    }
};
var articlesList = [];
var currentArticleIndex = 0;

// Main startup function
function startAnalysis() {    
    var btn = document.getElementById('startAnalysisBtn');
    var loadingSection = document.getElementById('loading-section');
    var resultsSection = document.getElementById('results-section');
    var noIssuesSection = document.getElementById('no-issues-section');
    
    // Disable button and show loading
    btn.disabled = true;
    btn.innerHTML = '<i class="icon-refresh icon-spin"></i> <span>Analyse en cours...</span>';
    
    loadingSection.style.display = 'block';
    resultsSection.style.display = 'none';
    noIssuesSection.style.display = 'none';
    
    // Reset state
    isAnalysisCancelled = false;
    currentArticleIndex = 0;
    currentAnalysisResults = {
        total_articles: 0,
        issues: [],
        stats: {
            title_issues: 0,
            meta_description_issues: 0,
            content_issues: 0,
            image_issues: 0,
            url_issues: 0
        }
    };
    
    // Get articles list
    getArticlesList();
}

// Get articles list
function getArticlesList() {
    fetch('<?php echo Uri::root(); ?>administrator/components/com_joomlahits/direct_seo_analysis.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'get_articles_list=1'
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
            articlesList = data.data;
            currentAnalysisResults.total_articles = articlesList.length;
            document.getElementById('current-analysis-status').textContent = 
                'Analysis started - ' + articlesList.length + ' articles to process';
            
            // Start analyzing first article
            if (articlesList.length > 0) {
                analyzeNextArticle();
            } else {
                finishAnalysis();
            }
        } else {
            showNotification('Error retrieving articles: ' + data.message, 'error');
            resetAnalysisUI();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error retrieving articles: ' + error.message, 'error');
        resetAnalysisUI();
    });
}

// Analyze next article
function analyzeNextArticle() {
    if (isAnalysisCancelled || currentArticleIndex >= articlesList.length) {
        finishAnalysis();
        return;
    }
    
    var article = articlesList[currentArticleIndex];
    var progressBar = document.getElementById('analysis-progress-bar');
    var currentStatus = document.getElementById('current-analysis-status');
    var resultsLog = document.getElementById('analysis-results-log');
    
    // Update progress
    var progress = Math.round((currentArticleIndex / articlesList.length) * 100);
    progressBar.style.width = progress + '%';
    progressBar.setAttribute('aria-valuenow', progress);
    currentStatus.textContent = 'Analyzing "' + article.title + '" (' + (currentArticleIndex + 1) + '/' + articlesList.length + ')';
    
    // Analyze article
    fetch('<?php echo Uri::root(); ?>administrator/components/com_joomlahits/direct_seo_analysis.php', {
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
        if (data.success && data.data.issues && data.data.issues.length > 0) {
            // Add article with issues
            currentAnalysisResults.issues.push(data.data);
            
            // Update stats
            for (var i = 0; i < data.data.issues.length; i++) {
                var issue = data.data.issues[i];
                var category = getIssueCategoryFromType(issue.type);
                if (currentAnalysisResults.stats[category + '_issues'] !== undefined) {
                    currentAnalysisResults.stats[category + '_issues']++;
                }
            }
            
            // Log result
            var severityClass = {
                'critical': 'text-danger',
                'warning': 'text-warning',
                'info': 'text-info'
            }[data.data.severity];
            
            resultsLog.innerHTML += '<div class="' + severityClass + '">' +
                '<i class="icon-warning"></i> ' + data.data.issues.length + ' issue(s) found in "' + article.title + '"' +
            '</div>';
        } else {
            // Article without issues
            resultsLog.innerHTML += '<div class="text-success">' +
                '<i class="icon-checkmark"></i> "' + article.title + '" - No issues detected' +
            '</div>';
        }
        resultsLog.scrollTop = resultsLog.scrollHeight;
        
        // Move to next article
        currentArticleIndex++;
        setTimeout(analyzeNextArticle, 1);
    })
    .catch(error => {
        console.error('Error:', error);
        resultsLog.innerHTML += '<div class="text-danger">' +
            '<i class="icon-warning"></i> Error analyzing "' + article.title + '": ' + error.message +
        '</div>';
        resultsLog.scrollTop = resultsLog.scrollHeight;
        
        // Move to next article even on error
        currentArticleIndex++;
        setTimeout(analyzeNextArticle, 1);
    });
}

// Get category from issue type
function getIssueCategoryFromType(issueType) {
    if (issueType.indexOf('title') !== -1) return 'title';
    if (issueType.indexOf('meta_desc') !== -1) return 'meta_description';
    if (issueType.indexOf('content') !== -1 || issueType.indexOf('h1') !== -1) return 'content';
    if (issueType.indexOf('alt') !== -1 || issueType.indexOf('image') !== -1) return 'image';
    if (issueType.indexOf('url') !== -1) return 'url';
    return 'content';
}

// Finish analysis
function finishAnalysis() {
    var progressBar = document.getElementById('analysis-progress-bar');
    var currentStatus = document.getElementById('current-analysis-status');
    var cancelBtn = document.getElementById('cancelAnalysis');
    
    progressBar.style.width = '100%';
    progressBar.setAttribute('aria-valuenow', '100');
    
    if (isAnalysisCancelled) {
        currentStatus.textContent = 'Analysis cancelled';
    } else {
        currentStatus.textContent = 'Analysis completed - ' + currentAnalysisResults.issues.length + ' articles with issues out of ' + currentAnalysisResults.total_articles;
    }
    
    cancelBtn.disabled = true;
    
    // Sort results by severity
    currentAnalysisResults.issues.sort(function(a, b) {
        var severityOrder = {'critical': 0, 'warning': 1, 'info': 2};
        return severityOrder[a.severity] - severityOrder[b.severity];
    });
    
    // Display results after delay
    setTimeout(function() {
        document.getElementById('loading-section').style.display = 'none';
        analysisResults = currentAnalysisResults;
        displayResults(analysisResults);
        resetAnalysisUI();
    }, 2000);
}

// Reset UI
function resetAnalysisUI() {
    var btn = document.getElementById('startAnalysisBtn');
    btn.disabled = false;
    btn.innerHTML = '<i class="icon-search"></i> <span>Lancer l analyse complète</span>';
}

// Cancel analysis
function cancelAnalysis() {
    isAnalysisCancelled = true;
    document.getElementById('current-analysis-status').textContent = 'Cancelling...';
    document.getElementById('cancelAnalysis').disabled = true;
}

// Display results
function displayResults(results) {
    if (!results.issues || results.issues.length === 0) {
        document.getElementById('no-issues-section').style.display = 'block';
        return;
    }
    
    filteredResults = results.issues;
    document.getElementById('results-section').style.display = 'block';
    populateTable(filteredResults);
}

// Populate table
function populateTable(articles) {
    var tbody = document.getElementById('results-tbody');
    tbody.innerHTML = '';
    
    for (var i = 0; i < articles.length; i++) {
        var row = createTableRow(articles[i]);
        tbody.appendChild(row);
    }
}

// Create table row
function createTableRow(article) {
    var tr = document.createElement('tr');
    
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
    
    tr.innerHTML = '<td class="d-none d-lg-table-cell">' + article.id + '</td>' +
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

// Apply filters
function applyFilters() {
    var severityFilter = document.getElementById('severity-filter').value;
    var issueTypeFilter = document.getElementById('issue-type-filter').value;
    var searchFilter = document.getElementById('search-filter').value.toLowerCase();
    
    filteredResults = [];
    
    for (var i = 0; i < analysisResults.issues.length; i++) {
        var article = analysisResults.issues[i];
        var shouldInclude = true;
        
        // Severity filter
        if (severityFilter && article.severity !== severityFilter) {
            shouldInclude = false;
        }
        
        // Issue type filter
        if (shouldInclude && issueTypeFilter) {
            var hasIssueType = false;
            for (var j = 0; j < article.issues.length; j++) {
                if (article.issues[j].type === issueTypeFilter) {
                    hasIssueType = true;
                    break;
                }
            }
            if (!hasIssueType) {
                shouldInclude = false;
            }
        }
        
        // Search filter
        if (shouldInclude && searchFilter) {
            if (article.title.toLowerCase().indexOf(searchFilter) === -1) {
                shouldInclude = false;
            }
        }
        
        if (shouldInclude) {
            filteredResults.push(article);
        }
    }
    
    populateTable(filteredResults);
}


// Joomla sorting function
if (typeof Joomla === 'undefined') {
    window.Joomla = {};
}

Joomla.tableOrdering = function(column, direction, task) {
    var form = document.getElementById('adminForm');
    if (!form) {
        // Create temporary form for sorting
        form = document.createElement('form');
        form.id = 'adminForm';
        form.method = 'post';
        document.body.appendChild(form);
    }
    
    // Sort results locally
    if (currentSort.column === column) {
        currentSort.direction = currentSort.direction === 'asc' ? 'desc' : 'asc';
    } else {
        currentSort.column = column;
        currentSort.direction = direction.toLowerCase();
    }
    
    filteredResults.sort(function(a, b) {
        var aValue, bValue;
        
        switch(column) {
            case 'id':
                aValue = parseInt(a.id);
                bValue = parseInt(b.id);
                break;
            case 'title':
                aValue = a.title.toLowerCase();
                bValue = b.title.toLowerCase();
                break;
            case 'category':
                aValue = a.category.toLowerCase();
                bValue = b.category.toLowerCase();
                break;
            case 'severity':
                var severityOrder = {'critical': 0, 'warning': 1, 'info': 2};
                aValue = severityOrder[a.severity];
                bValue = severityOrder[b.severity];
                break;
        }
        
        if (currentSort.direction === 'asc') {
            if (aValue < bValue) return -1;
            if (aValue > bValue) return 1;
            return 0;
        } else {
            if (aValue < bValue) return 1;
            if (aValue > bValue) return -1;
            return 0;
        }
    });
    
    populateTable(filteredResults);
    return false;
};

// Modal variables
var currentArticleData = null;
var seoModal = null;
var aiPreviewState = null; // null: no preview, 'pending': waiting for accept/reject, 'accepted': accepted, 'rejected': rejected

// Open SEO modal
function openSeoModal(articleId) {
    // Find article in results
    var article = null;
    for (var i = 0; i < filteredResults.length; i++) {
        if (filteredResults[i].id == articleId) {
            article = filteredResults[i];
            break;
        }
    }
    
    if (!article) return;
    
    currentArticleData = article;
    
    // Reset AI preview state
    aiPreviewState = null;
    document.getElementById('ai-preview-section').style.display = 'none';
    updateSaveButtonState();
    
    // Fill form
    document.getElementById('seo-article-id').value = article.id;
    document.getElementById('seo-title').value = article.title;
    document.getElementById('seo-metadesc').value = article.metadesc || '';
    document.getElementById('seo-metakey').value = article.metakey || '';
    document.getElementById('seo-alias').value = article.alias || '';
    document.getElementById('seo-content').value = article.content || '';
    
    // Update counters
    updateFieldCounters();
    
    // Display issues
    var issuesList = document.getElementById('issues-details');
    issuesList.innerHTML = '';
    for (var j = 0; j < article.issues.length; j++) {
        var li = document.createElement('li');
        li.textContent = article.issues[j].message;
        issuesList.appendChild(li);
    }
    
    // Open modal
    if (!seoModal) {
        seoModal = new bootstrap.Modal(document.getElementById('seoFixModal'));
    }
    seoModal.show();
}

// Update field counters and status
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
    
    // Alias
    var alias = document.getElementById('seo-alias');
    var aliasLength = alias.value.length;
    var aliasCounter = document.getElementById('alias-counter');
    var aliasStatus = document.getElementById('alias-status');
    
    aliasCounter.textContent = aliasLength;
    
    if (aliasLength > 70) {
        aliasStatus.innerHTML = '<span class="text-warning"><i class="icon-warning"></i> <?php echo Text::_('COM_JOOMLAHITS_SEOANALYSIS_URL_TOO_LONG'); ?></span>';
    } else if (aliasLength > 0) {
        aliasStatus.innerHTML = '<span class="text-success"><i class="icon-checkmark"></i> <?php echo Text::_('COM_JOOMLAHITS_SEO_OPTIMAL'); ?></span>';
    } else {
        aliasStatus.innerHTML = '';
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

// Update issues list in real time
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
    
    // Check alias
    var aliasLength = document.getElementById('seo-alias').value.length;
    if (aliasLength > 70) {
        addIssue(issuesList, '<?php echo Text::_('COM_JOOMLAHITS_SEOANALYSIS_URL_TOO_LONG'); ?> (' + aliasLength + ' <?php echo Text::_('COM_JOOMLAHITS_CHARACTERS'); ?>)', 'warning');
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

// Add issue to list
function addIssue(list, message, severity) {
    var li = document.createElement('li');
    var className = '';
    var icon = '';
    
    switch(severity) {
        case 'danger':
            className = 'text-danger';
            icon = 'exclamation-triangle';
            break;
        case 'warning':
            className = 'text-warning';
            icon = 'warning';
            break;
        case 'info':
            className = 'text-info';
            icon = 'info';
            break;
    }
    
    li.className = className;
    li.innerHTML = '<i class="icon-' + icon + '"></i> ' + message;
    list.appendChild(li);
}

// Fix with AI
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
        metakey: document.getElementById('seo-metakey').value,
        alias: document.getElementById('seo-alias').value
    };
    
    window.aiOptimizedValues = {};
    
    // Disable button and show loading
    aiBtn.disabled = true;
    aiBtn.innerHTML = '<i class="icon-refresh icon-spin me-2"></i>IA en cours...';
    
    // List of fields to process (content disabled as AI too unpredictable)
    var fields = ['title', 'metadesc', 'metakey', 'alias'];
    var currentFieldIndex = 0;
    
    function processNextField() {
        if (currentFieldIndex >= fields.length) {
            // All fields have been processed
            aiBtn.disabled = false;
            aiBtn.innerHTML = originalText;
            updateFieldCounters();
            
            // Show preview
            showAIPreview();
            
            // Set state to "pending"
            aiPreviewState = 'pending';
            updateSaveButtonState();
            return;
        }
        
        var fieldType = fields[currentFieldIndex];
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
            if (data.success && data.field_value) {
                // Store optimized value for preview
                window.aiOptimizedValues[fieldType] = data.field_value;
                
                // Remplir le champ correspondant (temporairement)
                var fieldElement = document.getElementById('seo-' + fieldType);
                if (fieldElement) {
                    fieldElement.value = data.field_value;
                }
            } else {
                console.error('Erreur pour le champ ' + fieldType + ':', data.message);
            }
            
            // Passer au champ suivant
            currentFieldIndex++;
            setTimeout(processNextField, 500); // Delay between calls
        })
        .catch(error => {
            console.error('Erreur pour le champ ' + fieldType + ':', error.message);
            
            // Move to next field even on error
            currentFieldIndex++;
            setTimeout(processNextField, 500);
        });
    }
    
    // Commencer le traitement
    processNextField();
}

function getFieldLabel(fieldType) {
    var labels = {
        'title': 'Titre',
        'metadesc': 'Meta desc',
        'metakey': 'Mots-clés',
        'alias': 'URL'
    };
    return labels[fieldType] || fieldType;
}

// Show AI changes preview
function showAIPreview() {
    var previewSection = document.getElementById('ai-preview-section');
    var previewContent = document.getElementById('ai-preview-content');
    
    var html = '';
    var fieldLabels = {
        'title': 'Titre',
        'metadesc': 'Meta Description',
        'metakey': 'Mots-clés',
        'alias': 'Alias URL'
    };
    
    Object.keys(window.originalValues).forEach(function(fieldType) {
        var original = window.originalValues[fieldType];
        var optimized = window.aiOptimizedValues[fieldType];
        
        if (original !== optimized) {
            html += '<div class="row mb-3">';
            html += '<div class="col-12">';
            html += '<h6 class="fw-bold text-primary">' + fieldLabels[fieldType] + '</h6>';
            html += '</div>';
            html += '<div class="col-md-6">';
            html += '<div class="card border-danger">';
            html += '<div class="card-header py-2">';
            html += '<small class="text-danger fw-bold"><i class="icon-times me-1"></i>AVANT</small>';
            html += '</div>';
            html += '<div class="card-body py-2">';
            html += '<small class="text-muted">' + (original || '<em>Vide</em>') + '</small>';
            html += '</div>';
            html += '</div>';
            html += '</div>';
            html += '<div class="col-md-6">';
            html += '<div class="card border-success">';
            html += '<div class="card-header py-2">';
            html += '<small class="text-success fw-bold"><i class="icon-checkmark me-1"></i>APRÈS</small>';
            html += '</div>';
            html += '<div class="card-body py-2">';
            html += '<small class="text-dark fw-bold">' + optimized + '</small>';
            html += '</div>';
            html += '</div>';
            html += '</div>';
            html += '</div>';
        }
    });
    
    if (html === '') {
        html = '<div class="text-center text-muted py-3">';
        html += '<i class="icon-info me-2"></i>Aucune modification n\'a été apportée par l\'IA.';
        html += '</div>';
    }
    
    previewContent.innerHTML = html;
    previewSection.style.display = 'block';
    
    // Scroll to preview in modal
    setTimeout(function() {
        var modalBody = document.querySelector('#seoFixModal .modal-body');
        var previewPosition = previewSection.offsetTop - modalBody.offsetTop;
        modalBody.scrollTo({
            top: previewPosition - 50, // Some space above
            behavior: 'smooth'
        });
    }, 100);
}

// Accepter les modifications IA
function acceptAIChanges() {
    // Values are already in fields, just hide preview
    document.getElementById('ai-preview-section').style.display = 'none';
    aiPreviewState = 'accepted';
    updateSaveButtonState();
    updateFieldCounters();
    
    // Afficher un message de confirmation
    var alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-success alert-dismissible fade show mt-3';
    alertDiv.innerHTML = '<i class="icon-checkmark me-2"></i>Modifications IA acceptées ! Vous pouvez maintenant sauvegarder.' +
                        '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
    
    var form = document.getElementById('seoFixForm');
    form.insertBefore(alertDiv, form.firstChild);
    
    // Remove alert after 5 seconds
    setTimeout(function() {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}

// Rejeter les modifications IA
function rejectAIChanges() {
    // Restaurer les valeurs originales
    Object.keys(window.originalValues).forEach(function(fieldType) {
        var fieldElement = document.getElementById('seo-' + fieldType);
        if (fieldElement) {
            fieldElement.value = window.originalValues[fieldType];
        }
    });
    
    // Hide preview
    document.getElementById('ai-preview-section').style.display = 'none';
    aiPreviewState = 'rejected';
    updateSaveButtonState();
    updateFieldCounters();
    
    // Afficher un message de confirmation
    var alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-info alert-dismissible fade show mt-3';
    alertDiv.innerHTML = '<i class="icon-info me-2"></i>Modifications IA annulées. Les valeurs originales ont été restaurées.' +
                        '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
    
    var form = document.getElementById('seoFixForm');
    form.insertBefore(alertDiv, form.firstChild);
    
    // Remove alert after 5 seconds
    setTimeout(function() {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}

// Update Save button state
function updateSaveButtonState() {
    var saveBtn = document.getElementById('saveSeoBtn');
    
    if (aiPreviewState === 'pending') {
        // En attente d'acceptation/refus des modifications IA
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="icon-warning me-2"></i>Acceptez ou annulez les modifications IA';
        saveBtn.className = 'btn btn-warning px-4';
        saveBtn.title = 'Vous devez accepter ou annuler les modifications IA avant de pouvoir enregistrer';
    } else {
        // Normal or after acceptance/rejection
        saveBtn.disabled = false;
        saveBtn.innerHTML = '<i class="icon-checkmark me-2"></i><?php echo Text::_('COM_JOOMLAHITS_SAVE_CHANGES'); ?>';
        saveBtn.className = 'btn btn-primary px-4';
        saveBtn.title = '';
    }
}

// Sauvegarder les corrections SEO
function saveSeoFixes() {
    // Check if we can save
    if (aiPreviewState === 'pending') {
        showNotification('You must first accept or cancel AI changes before saving.', 'warning');
        return;
    }
    
    var form = document.getElementById('seoFixForm');
    var formData = new FormData(form);
    
    // Note: AI content optimization is disabled to avoid issues
    
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

// Update single article after correction
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

document.addEventListener('DOMContentLoaded', function() {
    
    var startBtn = document.getElementById('startAnalysisBtn');
    if (startBtn) {
        startBtn.addEventListener('click', startAnalysis);
    }
    
    // Event listeners are already added with oninput in HTML
});
</script>