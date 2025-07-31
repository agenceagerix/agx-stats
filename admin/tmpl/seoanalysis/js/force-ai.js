/*-----------------------------------------------------------------------------------------------------/
	@version		1.2.0
	@build			29th July, 2025
	@created		29th July, 2025
	@package		JoomlaHits
	@subpackage		force-ai.js
	@author			Hugo Dantas - Agence Agerix <https://www.agerix.fr>
	@copyright		Copyright (C) 2025. All Rights Reserved
	@license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html
/------------------------------------------------------------------------------------------------------*/

/**
 * Start Force AI fix process
 */
function startForceAiFix() {
    // Get selected checkboxes
    var checkboxes = document.querySelectorAll('input[name="cid[]"]:checked');
    if (checkboxes.length === 0) {
        showNotification('Please select at least one article', 'warning');
        return;
    }
    
    // Get article IDs and data
    forceAiArticles = [];
    for (var i = 0; i < checkboxes.length; i++) {
        var articleId = checkboxes[i].value;
        // Find article data in filtered results
        for (var j = 0; j < filteredResults.length; j++) {
            if (filteredResults[j].id == articleId) {
                forceAiArticles.push(filteredResults[j]);
                break;
            }
        }
    }
    
    if (forceAiArticles.length === 0) {
        showNotification('No valid articles found for selected items', 'error');
        return;
    }
    
    // Initialize force processing
    currentForceAiIndex = 0;
    forceAiChanges = {};
    isForceAiProcessing = true;
    forceAiCancelled = false;
    
    // Hide other sections and show force AI section
    document.getElementById('results-section').style.display = 'none';
    document.getElementById('no-issues-section').style.display = 'none';
    document.getElementById('force-ai-section').style.display = 'block';
    document.getElementById('force-ai-results-section').style.display = 'none';
    
    showNotification('Starting Force AI processing for ' + forceAiArticles.length + ' articles...', 'info');
    
    // Start processing
    processNextForceAiArticle();
}

/**
 * Process next Force AI article
 */
function processNextForceAiArticle() {
    if (forceAiCancelled || currentForceAiIndex >= forceAiArticles.length) {
        finishForceAiProcessing();
        return;
    }
    
    var article = forceAiArticles[currentForceAiIndex];
    var progressBar = document.getElementById('force-ai-progress-bar');
    var currentStatus = document.getElementById('force-current-status');
    var resultsLog = document.getElementById('force-results-log');
    
    // Update progress
    var progress = Math.round((currentForceAiIndex / forceAiArticles.length) * 100);
    progressBar.style.width = progress + '%';
    progressBar.setAttribute('aria-valuenow', progress);
    currentStatus.textContent = 'Processing "' + article.title + '" (' + (currentForceAiIndex + 1) + '/' + forceAiArticles.length + ')';
    
    // Initialize storage for this article
    forceAiChanges[article.id] = {
        title: article.title,
        originalValues: {
            title: article.title,
            metadesc: article.metadesc || '',
            metakey: article.metakey || '',
            alias: article.alias || ''
        },
        aiValues: {},
        fieldsProcessed: 0,
        totalFields: 4
    };
    
    // Process all fields for this article
    processAllFieldsForArticle(article);
}

/**
 * Finish Force AI processing
 */
function finishForceAiProcessing() {
    var progressBar = document.getElementById('force-ai-progress-bar');
    var currentStatus = document.getElementById('force-current-status');
    var cancelBtn = document.getElementById('cancelForceAi');
    
    progressBar.style.width = '100%';
    progressBar.setAttribute('aria-valuenow', '100');
    
    if (forceAiCancelled) {
        currentStatus.textContent = 'Processing cancelled';
        setTimeout(function() {
            resetForceAiUI();
        }, 2000);
    } else {
        currentStatus.textContent = 'Processing completed - ' + Object.keys(forceAiChanges).length + ' articles processed';
        cancelBtn.disabled = true;
        
        // Show results section after delay
        setTimeout(function() {
            showForceAiResults();
        }, 1500);
    }
    
    isForceAiProcessing = false;
}

/**
 * Show Force AI results
 */
function showForceAiResults() {
    document.getElementById('force-ai-section').style.display = 'none';
    document.getElementById('force-ai-results-section').style.display = 'block';
    
    // Generate summary
    var summary = generateForceAiSummary();
    document.getElementById('force-ai-summary').innerHTML = summary;
}

/**
 * Generate Force AI summary
 */
function generateForceAiSummary() {
    var totalArticles = Object.keys(forceAiChanges).length;
    var totalChanges = 0;
    var summaryHtml = '<h5>AI modifications summary:</h5>';
    summaryHtml += '<div class="row">';
    
    Object.keys(forceAiChanges).forEach(function(articleId) {
        var articleData = forceAiChanges[articleId];
        var changesCount = Object.keys(articleData.aiValues).length;
        totalChanges += changesCount;
        
        summaryHtml += '<div class="col-md-6 mb-2">';
        summaryHtml += '<div class="card">';
        summaryHtml += '<div class="card-body py-2">';
        summaryHtml += '<h6 class="card-title mb-1">' + articleData.title + '</h6>';
        summaryHtml += '<small class="text-muted">' + changesCount + ' fields modified</small>';
        summaryHtml += '</div>';
        summaryHtml += '</div>';
        summaryHtml += '</div>';
    });
    
    summaryHtml += '</div>';
    summaryHtml += '<div class="alert alert-info mt-3">';
    summaryHtml += '<strong>' + totalArticles + ' articles processed</strong> with a total of <strong>' + totalChanges + ' modifications</strong>';
    summaryHtml += '</div>';
    
    return summaryHtml;
}

/**
 * Cancel Force AI processing
 */
function cancelForceAi() {
    forceAiCancelled = true;
    document.getElementById('force-current-status').textContent = 'Cancelling...';
    document.getElementById('cancelForceAi').disabled = true;
}


function saveForceAiChanges() {
    var totalToSave = 0;
    Object.keys(forceAiChanges).forEach(function(articleId) {
        if (Object.keys(forceAiChanges[articleId].aiValues).length > 0) {
            totalToSave++;
        }
    });
    
    showNotification('Saving ' + totalToSave + ' articles...', 'info');
    
    var articlesToSave = [];
    Object.keys(forceAiChanges).forEach(function(articleId) {
        var articleData = forceAiChanges[articleId];
        if (Object.keys(articleData.aiValues).length > 0) {
            articlesToSave.push({
                articleId: articleId,
                title: articleData.title,
                changes: {
                    title: articleData.aiValues.title || articleData.originalValues.title,
                    metadesc: articleData.aiValues.metadesc || articleData.originalValues.metadesc,
                    metakey: articleData.aiValues.metakey || articleData.originalValues.metakey,
                    alias: articleData.aiValues.alias || articleData.originalValues.alias
                }
            });
        }
    });
    
    if (articlesToSave.length === 0) {
        showNotification('No changes to save', 'warning');
        return;
    }
    
    saveForceAiArticlesSequentially(articlesToSave);
}


function resetForceAiUI() {
    document.getElementById('force-ai-section').style.display = 'none';
    document.getElementById('force-ai-results-section').style.display = 'none';
    document.getElementById('results-section').style.display = 'block';
    
    // Reset variables
    forceAiArticles = [];
    currentForceAiIndex = 0;
    forceAiChanges = {};
    isForceAiProcessing = false;
    forceAiCancelled = false;
}