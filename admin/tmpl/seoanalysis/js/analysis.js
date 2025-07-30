/*-----------------------------------------------------------------------------------------------------/
	@version		1.2.0
	@build			29th July, 2025
	@created		29th July, 2025
	@package		JoomlaHits
	@subpackage		analysis.js
	@author			Hugo Dantas - Agence Agerix <https://www.agerix.fr>
	@copyright		Copyright (C) 2025. All Rights Reserved
	@license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html
/------------------------------------------------------------------------------------------------------*/

/**
 * Main startup function for SEO analysis
 */
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

/**
 * Get issue category from type
 */
function getIssueCategoryFromType(issueType) {
    if (issueType.indexOf('title') !== -1) return 'title';
    if (issueType.indexOf('meta_desc') !== -1) return 'meta_description';
    if (issueType.indexOf('content') !== -1 || issueType.indexOf('h1') !== -1) return 'content';
    if (issueType.indexOf('alt') !== -1 || issueType.indexOf('image') !== -1) return 'image';
    if (issueType.indexOf('url') !== -1) return 'url';
    return 'content';
}

/**
 * Finish analysis
 */
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

/**
 * Reset analysis UI
 */
function resetAnalysisUI() {
    var btn = document.getElementById('startAnalysisBtn');
    btn.disabled = false;
    btn.innerHTML = '<i class="icon-search"></i> <span>Lancer l analyse complète</span>';
}

/**
 * Cancel analysis
 */
function cancelAnalysis() {
    isAnalysisCancelled = true;
    document.getElementById('current-analysis-status').textContent = 'Cancelling...';
    document.getElementById('cancelAnalysis').disabled = true;
}