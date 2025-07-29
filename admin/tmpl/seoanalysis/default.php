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
                            <div id="analysis-results-log" class="mt-2 small" style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; border-radius: 5px; background: #f8f9fa;"></div>
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
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title fw-bold" id="seoFixModalLabel">
                    <i class="icon-cog text-primary me-2"></i>
                    <?php echo Text::_('COM_JOOMLAHITS_SEO_FIX_MODAL_TITLE'); ?>
                </h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
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
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">
                    <i class="icon-times me-2"></i><?php echo Text::_('COM_JOOMLAHITS_CANCEL'); ?>
                </button>
                <button type="button" class="btn btn-primary px-4" onclick="saveSeoFixes()">
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

</style>

<script>
// Variables globales
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

// Fonction principale de démarrage
function startAnalysis() {
    console.log('Starting analysis...');
    
    var btn = document.getElementById('startAnalysisBtn');
    var loadingSection = document.getElementById('loading-section');
    var resultsSection = document.getElementById('results-section');
    var noIssuesSection = document.getElementById('no-issues-section');
    
    // Désactiver le bouton et afficher le chargement
    btn.disabled = true;
    btn.innerHTML = '<i class="icon-refresh icon-spin"></i> <span>Analyse en cours...</span>';
    
    loadingSection.style.display = 'block';
    resultsSection.style.display = 'none';
    noIssuesSection.style.display = 'none';
    
    // Réinitialiser l'état
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
    
    // Récupérer la liste des articles
    getArticlesList();
}

// Récupérer la liste des articles
function getArticlesList() {
    var xhr = new XMLHttpRequest();
    xhr.open('POST', '<?php echo Uri::root(); ?>administrator/components/com_joomlahits/direct_seo_analysis.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                try {
                    var data = JSON.parse(xhr.responseText);
                    if (data.success) {
                        articlesList = data.data;
                        currentAnalysisResults.total_articles = articlesList.length;
                        document.getElementById('current-analysis-status').textContent = 
                            'Début de l analyse - ' + articlesList.length + ' articles à traiter';
                        
                        // Démarrer l'analyse du premier article
                        if (articlesList.length > 0) {
                            analyzeNextArticle();
                        } else {
                            finishAnalysis();
                        }
                    } else {
                        alert('Erreur lors de la récupération des articles: ' + data.message);
                        resetAnalysisUI();
                    }
                } catch (e) {
                    console.error('Erreur parsing JSON:', e);
                    alert('Erreur lors du parsing de la réponse');
                    resetAnalysisUI();
                }
            } else {
                alert('Erreur de connexion');
                resetAnalysisUI();
            }
        }
    };
    
    xhr.send('get_articles_list=1');
}

// Analyser l'article suivant
function analyzeNextArticle() {
    if (isAnalysisCancelled || currentArticleIndex >= articlesList.length) {
        finishAnalysis();
        return;
    }
    
    var article = articlesList[currentArticleIndex];
    var progressBar = document.getElementById('analysis-progress-bar');
    var currentStatus = document.getElementById('current-analysis-status');
    var resultsLog = document.getElementById('analysis-results-log');
    
    // Mettre à jour la progression
    var progress = Math.round((currentArticleIndex / articlesList.length) * 100);
    progressBar.style.width = progress + '%';
    progressBar.setAttribute('aria-valuenow', progress);
    currentStatus.textContent = 'Analyse de "' + article.title + '" (' + (currentArticleIndex + 1) + '/' + articlesList.length + ')';
    
    // Analyser l'article
    var xhr = new XMLHttpRequest();
    xhr.open('POST', '<?php echo Uri::root(); ?>administrator/components/com_joomlahits/direct_seo_analysis.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                try {
                    var data = JSON.parse(xhr.responseText);
                    if (data.success && data.data.issues && data.data.issues.length > 0) {
                        // Ajouter l'article avec des problèmes
                        currentAnalysisResults.issues.push(data.data);
                        
                        // Mettre à jour les stats
                        for (var i = 0; i < data.data.issues.length; i++) {
                            var issue = data.data.issues[i];
                            var category = getIssueCategoryFromType(issue.type);
                            if (currentAnalysisResults.stats[category + '_issues'] !== undefined) {
                                currentAnalysisResults.stats[category + '_issues']++;
                            }
                        }
                        
                        // Logger le résultat
                        var severityClass = {
                            'critical': 'text-danger',
                            'warning': 'text-warning',
                            'info': 'text-info'
                        }[data.data.severity];
                        
                        resultsLog.innerHTML += '<div class="' + severityClass + '">' +
                            '<i class="icon-warning"></i> ' + data.data.issues.length + ' problème(s) trouvé(s) dans "' + article.title + '"' +
                        '</div>';
                    } else {
                        // Article sans problèmes
                        resultsLog.innerHTML += '<div class="text-success">' +
                            '<i class="icon-checkmark"></i> "' + article.title + '" - Aucun problème détecté' +
                        '</div>';
                    }
                    resultsLog.scrollTop = resultsLog.scrollHeight;
                } catch (e) {
                    console.error('Erreur parsing:', e);
                }
            }
            
            // Passer à l'article suivant
            currentArticleIndex++;
            setTimeout(analyzeNextArticle, 1);
        }
    };
    
    xhr.send('article_id=' + encodeURIComponent(article.id));
}

// Obtenir la catégorie d'un type de problème
function getIssueCategoryFromType(issueType) {
    if (issueType.indexOf('title') !== -1) return 'title';
    if (issueType.indexOf('meta_desc') !== -1) return 'meta_description';
    if (issueType.indexOf('content') !== -1 || issueType.indexOf('h1') !== -1) return 'content';
    if (issueType.indexOf('alt') !== -1 || issueType.indexOf('image') !== -1) return 'image';
    if (issueType.indexOf('url') !== -1) return 'url';
    return 'content';
}

// Terminer l'analyse
function finishAnalysis() {
    var progressBar = document.getElementById('analysis-progress-bar');
    var currentStatus = document.getElementById('current-analysis-status');
    var cancelBtn = document.getElementById('cancelAnalysis');
    
    progressBar.style.width = '100%';
    progressBar.setAttribute('aria-valuenow', '100');
    
    if (isAnalysisCancelled) {
        currentStatus.textContent = 'Analyse annulée';
    } else {
        currentStatus.textContent = 'Analyse terminée - ' + currentAnalysisResults.issues.length + ' articles avec problèmes sur ' + currentAnalysisResults.total_articles;
    }
    
    cancelBtn.disabled = true;
    
    // Trier les résultats par gravité
    currentAnalysisResults.issues.sort(function(a, b) {
        var severityOrder = {'critical': 0, 'warning': 1, 'info': 2};
        return severityOrder[a.severity] - severityOrder[b.severity];
    });
    
    // Afficher les résultats après un délai
    setTimeout(function() {
        document.getElementById('loading-section').style.display = 'none';
        analysisResults = currentAnalysisResults;
        displayResults(analysisResults);
        resetAnalysisUI();
    }, 2000);
}

// Réinitialiser l'interface
function resetAnalysisUI() {
    var btn = document.getElementById('startAnalysisBtn');
    btn.disabled = false;
    btn.innerHTML = '<i class="icon-search"></i> <span>Lancer l analyse complète</span>';
}

// Annuler l'analyse
function cancelAnalysis() {
    isAnalysisCancelled = true;
    document.getElementById('current-analysis-status').textContent = 'Annulation en cours...';
    document.getElementById('cancelAnalysis').disabled = true;
}

// Afficher les résultats
function displayResults(results) {
    if (!results.issues || results.issues.length === 0) {
        document.getElementById('no-issues-section').style.display = 'block';
        return;
    }
    
    // updateStatistics(results); // Fonction désactivée
    filteredResults = results.issues;
    document.getElementById('results-section').style.display = 'block';
    populateTable(filteredResults);
}

// Mettre à jour les statistiques
function updateStatistics(results) {
    var totalArticles = results.total_articles;
    var issuesCount = results.issues.length;
    var healthyArticles = totalArticles - issuesCount;
    var seoScore = Math.round((healthyArticles / totalArticles) * 100);
    
    var criticalCount = 0;
    var warningCount = 0;
    var infoCount = 0;
    
    for (var i = 0; i < results.issues.length; i++) {
        var article = results.issues[i];
        switch(article.severity) {
            case 'critical':
                criticalCount++;
                break;
            case 'warning':
                warningCount++;
                break;
            case 'info':
                infoCount++;
                break;
        }
    }
    
    document.getElementById('total-articles').textContent = totalArticles;
    document.getElementById('critical-issues').textContent = criticalCount;
    document.getElementById('warning-issues').textContent = warningCount;
    document.getElementById('info-issues').textContent = infoCount;
    document.getElementById('healthy-articles').textContent = healthyArticles;
    document.getElementById('seo-score').textContent = seoScore + '%';
}

// Remplir le tableau
function populateTable(articles) {
    var tbody = document.getElementById('results-tbody');
    tbody.innerHTML = '';
    
    for (var i = 0; i < articles.length; i++) {
        var row = createTableRow(articles[i]);
        tbody.appendChild(row);
    }
}

// Créer une ligne du tableau
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

// Appliquer les filtres
function applyFilters() {
    var severityFilter = document.getElementById('severity-filter').value;
    var issueTypeFilter = document.getElementById('issue-type-filter').value;
    var searchFilter = document.getElementById('search-filter').value.toLowerCase();
    
    filteredResults = [];
    
    for (var i = 0; i < analysisResults.issues.length; i++) {
        var article = analysisResults.issues[i];
        var shouldInclude = true;
        
        // Filtre de gravité
        if (severityFilter && article.severity !== severityFilter) {
            shouldInclude = false;
        }
        
        // Filtre de type de problème
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
        
        // Filtre de recherche
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


// Fonction de tri Joomla
if (typeof Joomla === 'undefined') {
    window.Joomla = {};
}

Joomla.tableOrdering = function(column, direction, task) {
    var form = document.getElementById('adminForm');
    if (!form) {
        // Créer un formulaire temporaire pour le tri
        form = document.createElement('form');
        form.id = 'adminForm';
        form.method = 'post';
        document.body.appendChild(form);
    }
    
    // Trier les résultats localement
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

// Variables pour le modal
var currentArticleData = null;
var seoModal = null;

// Ouvrir le modal SEO
function openSeoModal(articleId) {
    // Trouver l'article dans les résultats
    var article = null;
    for (var i = 0; i < filteredResults.length; i++) {
        if (filteredResults[i].id == articleId) {
            article = filteredResults[i];
            break;
        }
    }
    
    if (!article) return;
    
    currentArticleData = article;
    
    // Remplir le formulaire
    document.getElementById('seo-article-id').value = article.id;
    document.getElementById('seo-title').value = article.title;
    document.getElementById('seo-metadesc').value = article.metadesc || '';
    document.getElementById('seo-metakey').value = article.metakey || '';
    document.getElementById('seo-alias').value = article.alias || '';
    document.getElementById('seo-content').value = article.content || '';
    
    // Mettre à jour les compteurs
    updateFieldCounters();
    
    // Afficher les problèmes
    var issuesList = document.getElementById('issues-details');
    issuesList.innerHTML = '';
    for (var j = 0; j < article.issues.length; j++) {
        var li = document.createElement('li');
        li.textContent = article.issues[j].message;
        issuesList.appendChild(li);
    }
    
    // Ouvrir le modal
    if (!seoModal) {
        seoModal = new bootstrap.Modal(document.getElementById('seoFixModal'));
    }
    seoModal.show();
}

// Mettre à jour les compteurs et statuts
function updateFieldCounters() {
    // Titre
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
    
    // Méta description
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
    
    // Contenu
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
    
    // Mettre à jour la liste des problèmes
    updateIssuesList();
}

// Mettre à jour la liste des problèmes en temps réel
function updateIssuesList() {
    var issuesList = document.getElementById('issues-details');
    issuesList.innerHTML = '';
    
    var hasIssues = false;
    
    // Vérifier le titre
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
    
    // Vérifier la méta description
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
    
    // Vérifier l'alias
    var aliasLength = document.getElementById('seo-alias').value.length;
    if (aliasLength > 70) {
        addIssue(issuesList, '<?php echo Text::_('COM_JOOMLAHITS_SEOANALYSIS_URL_TOO_LONG'); ?> (' + aliasLength + ' <?php echo Text::_('COM_JOOMLAHITS_CHARACTERS'); ?>)', 'warning');
        hasIssues = true;
    }
    
    // Vérifier le contenu
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
    
    // Vérifier les mots-clés
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

// Ajouter un problème à la liste
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

// Sauvegarder les corrections SEO
function saveSeoFixes() {
    var form = document.getElementById('seoFixForm');
    var formData = new FormData(form);
    
    var xhr = new XMLHttpRequest();
    xhr.open('POST', '<?php echo Uri::root(); ?>administrator/components/com_joomlahits/direct_seo_fix.php', true);
    
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                try {
                    var data = JSON.parse(xhr.responseText);
                    if (data.success) {
                        alert('<?php echo Text::_('COM_JOOMLAHITS_SEO_FIX_SUCCESS'); ?>');
                        seoModal.hide();
                        // Relancer l'analyse pour cet article
                        updateSingleArticle(currentArticleData.id);
                    } else {
                        alert('Erreur: ' + data.message);
                    }
                } catch (e) {
                    alert('Erreur lors de la sauvegarde');
                }
            }
        }
    };
    
    xhr.send(formData);
}

// Mettre à jour un seul article après correction
function updateSingleArticle(articleId) {
    var xhr = new XMLHttpRequest();
    xhr.open('POST', '<?php echo Uri::root(); ?>administrator/components/com_joomlahits/direct_seo_analysis.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            try {
                var data = JSON.parse(xhr.responseText);
                if (data.success) {
                    // Mettre à jour l'article dans les résultats
                    for (var i = 0; i < filteredResults.length; i++) {
                        if (filteredResults[i].id == articleId) {
                            if (data.data.issues && data.data.issues.length > 0) {
                                filteredResults[i] = data.data;
                            } else {
                                // Plus de problèmes, retirer de la liste
                                filteredResults.splice(i, 1);
                            }
                            break;
                        }
                    }
                    
                    // Mettre à jour analysisResults aussi
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
                    
                    // Réafficher le tableau
                    populateTable(filteredResults);
                }
            } catch (e) {
                console.error('Erreur:', e);
            }
        }
    };
    
    xhr.send('article_id=' + encodeURIComponent(articleId));
}

// Initialisation au chargement
document.addEventListener('DOMContentLoaded', function() {
    console.log('SEO Analysis page loaded');
    
    // Ajouter l'event listener pour le bouton d'analyse
    var startBtn = document.getElementById('startAnalysisBtn');
    if (startBtn) {
        startBtn.addEventListener('click', startAnalysis);
    }
    
    // Les event listeners sont déjà ajoutés avec oninput dans le HTML
});
</script>