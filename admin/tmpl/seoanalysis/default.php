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
    ->useScript('multiselect');

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
                                <button type="button" class="btn btn-success btn-lg" id="startAnalysisBtn" onclick="startAnalysis()">
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
                            'Début de l\'analyse - ' + articlesList.length + ' articles à traiter';
                        
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
    btn.innerHTML = '<i class="icon-search"></i> <span>Lancer l\'analyse complète</span>';
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
        '<td class="text-center"><a href="index.php?option=com_content&task=article.edit&id=' + article.id + '" class="btn btn-sm btn-outline-primary" title="Éditer l\'article"><i class="icon-edit"></i></a></td>';
    
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

// Initialisation au chargement
document.addEventListener('DOMContentLoaded', function() {
    console.log('SEO Analysis page loaded');
});
</script>