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
 * Wait for all scripts to load and then show confirmation dialog
 */
function waitForConfirmForceAiFix() {
    // Retry mechanism to ensure all scripts and variables are loaded
    var maxRetries = 50; // 5 seconds max wait time
    var retryCount = 0;
    
    function tryConfirm() {
        retryCount++;
        
        // Check if all required dependencies are loaded
        if (typeof showNotification !== 'undefined' && 
            typeof window.JOOMLA_LANG_FORCE_AI !== 'undefined' &&
            window.JOOMLA_LANG_FORCE_AI.warningTitle &&
            window.JOOMLA_LANG_FORCE_AI.warningMessage) {
            
            // All dependencies loaded, show confirmation
            confirmForceAiFix();
            return;
        }
        
        // If max retries reached, show error
        if (retryCount >= maxRetries) {
            alert(window.JOOMLA_LANG_FORCE_AI ? window.JOOMLA_LANG_FORCE_AI.scriptsNotLoaded : 'Error: Required scripts not loaded. Please refresh the page and try again.');
            return;
        }
        
        // Retry after 100ms
        setTimeout(tryConfirm, 100);
    }
    
    tryConfirm();
}

/**
 * Show confirmation dialog before starting Force AI processing
 */
function confirmForceAiFix() {
    // Get selected checkboxes to show count in confirmation
    var checkboxes = document.querySelectorAll('input[name="cid[]"]:checked');
    if (checkboxes.length === 0) {
        showNotification(window.JOOMLA_LANG_FORCE_AI ? window.JOOMLA_LANG_FORCE_AI.selectArticles : 'Please select at least one article', 'warning');
        return;
    }
    
    // Get language-specific warning message
    var warningTitle = window.JOOMLA_LANG_FORCE_AI ? window.JOOMLA_LANG_FORCE_AI.warningTitle : 'Token Consumption Warning';
    var warningMessage = window.JOOMLA_LANG_FORCE_AI ? window.JOOMLA_LANG_FORCE_AI.warningMessage : 
        'Starting Force AI processing will automatically consume AI tokens for each selected article. This process will generate optimized content for titles, meta descriptions, and keywords using artificial intelligence.\n\nTokens will be consumed immediately upon starting the process.\n\nDo you want to proceed with Force AI processing?';
    
    // Show enhanced warning notification first
    showTokenConsumptionWarning(warningTitle, warningMessage, checkboxes.length);
}

/**
 * Show enhanced token consumption warning with better UI
 */
function showTokenConsumptionWarning(title, message, articleCount) {
    // Create modal-style warning overlay
    var overlay = document.createElement('div');
    overlay.id = 'force-ai-warning-overlay';
    overlay.style.cssText = 'position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 10000; display: flex; align-items: center; justify-content: center;';
    
    var modal = document.createElement('div');
    modal.className = 'bg-secondary text-light';
    modal.style.cssText = 'padding: 30px; border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.3); max-width: 500px; width: 90%; max-height: 80vh; overflow-y: auto;';
    
    // Warning icon and title
    var header = document.createElement('div');
    header.style.cssText = 'display: flex; align-items: center; margin-bottom: 20px;';
    header.innerHTML = '<i class="icon-warning" style="font-size: 24px; margin-right: 10px; color: white;"></i><h3 style="margin: 0; color: white;">' + title + '</h3>';
    
    // Warning message
    var messageDiv = document.createElement('div');
    messageDiv.style.cssText = 'margin-bottom: 20px; line-height: 1.5;';
    var selectedArticlesText = window.JOOMLA_LANG_FORCE_AI ? window.JOOMLA_LANG_FORCE_AI.selectedArticles : 'Selected articles:';
    var warningUndoneText = window.JOOMLA_LANG_FORCE_AI ? window.JOOMLA_LANG_FORCE_AI.warningUndone : '⚠️ Warning:';
    var tokensConsumedText = window.JOOMLA_LANG_FORCE_AI ? window.JOOMLA_LANG_FORCE_AI.tokensConsumed : 'This action cannot be undone and will consume tokens immediately.';
    
    messageDiv.innerHTML = '<div class="bg-primary text-light" style="padding: 15px; border-radius: 4px; margin-bottom: 15px;">' + 
        message.replace(/\\n/g, '<br>') + '</div>' +
        '<p><strong>' + selectedArticlesText + ' ' + articleCount + '</strong></p>' +
        '<p><strong>' + warningUndoneText + '</strong> ' + tokensConsumedText + '</p>';
    
    // Buttons
    var buttonDiv = document.createElement('div');
    buttonDiv.style.cssText = 'display: flex; gap: 10px; justify-content: flex-end;';
    
    var cancelBtn = document.createElement('button');
    cancelBtn.textContent = window.JOOMLA_LANG ? window.JOOMLA_LANG.cancel || 'Cancel' : 'Cancel';
    cancelBtn.className = 'btn btn-secondary';
    cancelBtn.style.cssText = 'margin-right: 10px;';
    cancelBtn.onclick = function() {
        document.body.removeChild(overlay);
    };
    
    var proceedBtn = document.createElement('button');
    proceedBtn.textContent = window.JOOMLA_LANG_FORCE_AI ? window.JOOMLA_LANG_FORCE_AI.proceedButton : 'Proceed with Force AI';
    proceedBtn.className = 'btn btn-primary';
    proceedBtn.onclick = function() {
        document.body.removeChild(overlay);
        startForceAiFix();
    };
    
    buttonDiv.appendChild(cancelBtn);
    buttonDiv.appendChild(proceedBtn);
    
    modal.appendChild(header);
    modal.appendChild(messageDiv);
    modal.appendChild(buttonDiv);
    overlay.appendChild(modal);
    
    // Close on overlay click
    overlay.onclick = function(e) {
        if (e.target === overlay) {
            document.body.removeChild(overlay);
        }
    };
    
    // Close on Escape key
    document.addEventListener('keydown', function escapeHandler(e) {
        if (e.key === 'Escape') {
            if (document.getElementById('force-ai-warning-overlay')) {
                document.body.removeChild(overlay);
            }
            document.removeEventListener('keydown', escapeHandler);
        }
    });
    
    document.body.appendChild(overlay);
    
    // Focus on proceed button for better accessibility
    setTimeout(function() {
        proceedBtn.focus();
    }, 100);
}

/**
 * Start Force AI fix process
 */
function startForceAiFix() {
    // Get selected checkboxes
    var checkboxes = document.querySelectorAll('input[name="cid[]"]:checked');
    if (checkboxes.length === 0) {
        showNotification(window.JOOMLA_LANG_FORCE_AI ? window.JOOMLA_LANG_FORCE_AI.selectArticles : 'Please select at least one article', 'warning');
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
        showNotification(window.JOOMLA_LANG_FORCE_AI ? window.JOOMLA_LANG_FORCE_AI.noValidArticles : 'No valid articles found for selected items', 'error');
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
    
    var startingMessage = window.JOOMLA_LANG_FORCE_AI ? window.JOOMLA_LANG_FORCE_AI.startingProcessing.replace('%d', forceAiArticles.length) : 'Starting Force AI processing for ' + forceAiArticles.length + ' articles...';
    showNotification(startingMessage, 'info');
    
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
    currentStatus.textContent = window.JOOMLA_LANG.processingArticle + ' "' + article.title + '" (' + (currentForceAiIndex + 1) + '/' + forceAiArticles.length + ')';
    
    // Initialize storage for this article with loading placeholders
    forceAiChanges[article.id] = {
        title: article.title,
        originalValues: {
            title: window.JOOMLA_LANG_FORCE_AI ? window.JOOMLA_LANG_FORCE_AI.loading : 'Loading...',
            metadesc: window.JOOMLA_LANG_FORCE_AI ? window.JOOMLA_LANG_FORCE_AI.loading : 'Loading...',
            metakey: window.JOOMLA_LANG_FORCE_AI ? window.JOOMLA_LANG_FORCE_AI.loading : 'Loading...',
            content: window.JOOMLA_LANG_FORCE_AI ? window.JOOMLA_LANG_FORCE_AI.loading : 'Loading...'
        },
        aiValues: {},
        fieldsProcessed: 0,
        totalFields: 4
    };
    
    // First, fetch complete article data to get accurate original values
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
            
            // Update with complete original values
            forceAiChanges[article.id].originalValues = {
                title: fullArticle.title || '',
                metadesc: fullArticle.metadesc || '',
                metakey: fullArticle.metakey || '',
                content: (fullArticle.introtext || '') + ' ' + (fullArticle.fulltext || ''),
                introtext: fullArticle.introtext || '',
                fulltext: fullArticle.fulltext || ''
            };
            
            // Now process all fields for this article with complete data
            processAllFieldsForArticle(article);
        } else {
            // Fallback to basic data if fetch fails
            forceAiChanges[article.id].originalValues = {
                title: article.title || '',
                metadesc: article.metadesc || '',
                metakey: article.metakey || '',
                content: '', // We don't have content data in the fallback
                introtext: '',
                fulltext: ''
            };
            
            processAllFieldsForArticle(article);
        }
    })
    .catch(error => {
        console.error('Error loading complete article data for Force AI:', error);
        // Fallback to basic data if fetch fails
        forceAiChanges[article.id].originalValues = {
            title: article.title || '',
            metadesc: article.metadesc || '',
            metakey: article.metakey || '',
            content: '', // We don't have content data in the error fallback
            introtext: '',
            fulltext: ''
        };
        
        processAllFieldsForArticle(article);
    });
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
        currentStatus.textContent = window.JOOMLA_LANG.processingCancelled;
        setTimeout(function() {
            resetForceAiUI();
        }, 2000);
    } else {
        currentStatus.textContent = window.JOOMLA_LANG.processingCompleted + ' - ' + Object.keys(forceAiChanges).length + ' articles processed';
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
    var summaryTitle = window.JOOMLA_LANG_FORCE_AI ? window.JOOMLA_LANG_FORCE_AI.summaryTitle : 'AI modifications summary:';
    var summaryHtml = '<h5>' + summaryTitle + '</h5>';
    summaryHtml += '<div class="row">';
    
    Object.keys(forceAiChanges).forEach(function(articleId) {
        var articleData = forceAiChanges[articleId];
        var changesCount = Object.keys(articleData.aiValues).length;
        totalChanges += changesCount;
        
        summaryHtml += '<div class="col-md-6 mb-2">';
        summaryHtml += '<div class="card">';
        summaryHtml += '<div class="card-body py-2">';
        summaryHtml += '<h6 class="card-title mb-1">' + articleData.title + '</h6>';
        var fieldsModifiedText = window.JOOMLA_LANG_FORCE_AI ? window.JOOMLA_LANG_FORCE_AI.fieldsModified : 'fields modified';
        summaryHtml += '<small class="text-muted">' + changesCount + ' ' + fieldsModifiedText + '</small>';
        summaryHtml += '</div>';
        summaryHtml += '</div>';
        summaryHtml += '</div>';
    });
    
    summaryHtml += '</div>';
    summaryHtml += '<div class="alert alert-info mt-3">';
    var articlesProcessedText = window.JOOMLA_LANG_FORCE_AI ? window.JOOMLA_LANG_FORCE_AI.articlesProcessed : 'articles processed';
    var withTotalText = window.JOOMLA_LANG_FORCE_AI ? window.JOOMLA_LANG_FORCE_AI.withTotal : 'with a total of';
    var modificationsText = window.JOOMLA_LANG_FORCE_AI ? window.JOOMLA_LANG_FORCE_AI.modifications : 'modifications';
    
    summaryHtml += '<strong>' + totalArticles + ' ' + articlesProcessedText + '</strong> ' + withTotalText + ' <strong>' + totalChanges + ' ' + modificationsText + '</strong>';
    summaryHtml += '</div>';
    
    return summaryHtml;
}

/**
 * Cancel Force AI processing
 */
function cancelForceAi() {
    forceAiCancelled = true;
    document.getElementById('force-current-status').textContent = window.JOOMLA_LANG_FORCE_AI ? window.JOOMLA_LANG_FORCE_AI.cancelling : 'Cancelling...';
    document.getElementById('cancelForceAi').disabled = true;
}


function saveForceAiChanges() {
    var totalToSave = 0;
    Object.keys(forceAiChanges).forEach(function(articleId) {
        if (Object.keys(forceAiChanges[articleId].aiValues).length > 0) {
            totalToSave++;
        }
    });
    
    var savingMessage = window.JOOMLA_LANG_FORCE_AI ? window.JOOMLA_LANG_FORCE_AI.savingArticles.replace('%d', totalToSave) : 'Saving ' + totalToSave + ' articles...';
    showNotification(savingMessage, 'info');
    
    var articlesToSave = [];
    Object.keys(forceAiChanges).forEach(function(articleId) {
        var articleData = forceAiChanges[articleId];
        if (Object.keys(articleData.aiValues).length > 0) {
            var changes = {
                title: articleData.aiValues.title || articleData.originalValues.title,
                metadesc: articleData.aiValues.metadesc || articleData.originalValues.metadesc,
                metakey: articleData.aiValues.metakey || articleData.originalValues.metakey
            };
            
            // Handle introtext/fulltext properly
            if (articleData.originalValues.introtext !== undefined || articleData.originalValues.fulltext !== undefined) {
                // We have original structure - preserve it
                if (articleData.aiValues.content !== undefined && articleData.aiValues.content !== articleData.originalValues.content) {
                    // Content was modified by AI - need to handle splitting
                    var modifiedContent = articleData.aiValues.content;
                    var readmorePattern = /<hr\s+id\s*=\s*["']system-readmore["'][^>]*>/i;
                    if (readmorePattern.test(modifiedContent)) {
                        // Split the modified content
                        var parts = modifiedContent.split(readmorePattern);
                        changes.introtext = parts[0].trim();
                        changes.fulltext = parts[1] ? parts[1].trim() : '';
                    } else {
                        // No readmore separator - modified content goes to introtext, preserve original fulltext
                        changes.introtext = modifiedContent;
                        changes.fulltext = articleData.originalValues.fulltext || '';
                    }
                } else {
                    // Content not modified - use original structure
                    changes.introtext = articleData.originalValues.introtext || '';
                    changes.fulltext = articleData.originalValues.fulltext || '';
                }
            } else {
                // Fallback to old content field
                changes.content = articleData.aiValues.content || articleData.originalValues.content;
            }
            
            articlesToSave.push({
                articleId: articleId,
                title: articleData.title,
                changes: changes
            });
        }
    });
    
    if (articlesToSave.length === 0) {
        showNotification(window.JOOMLA_LANG_FORCE_AI ? window.JOOMLA_LANG_FORCE_AI.noChanges : 'No changes to save', 'warning');
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