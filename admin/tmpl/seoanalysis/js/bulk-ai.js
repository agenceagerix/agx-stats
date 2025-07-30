/*-----------------------------------------------------------------------------------------------------/
	@version		1.2.0
	@build			29th July, 2025
	@created		29th July, 2025
	@package		JoomlaHits
	@subpackage		bulk-ai.js
	@author			Hugo Dantas - Agence Agerix <https://www.agerix.fr>
	@copyright		Copyright (C) 2025. All Rights Reserved
	@license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html
/------------------------------------------------------------------------------------------------------*/

/**
 * Start bulk AI fix process
 */
function startBulkAiFix() {
    // Get selected checkboxes
    var checkboxes = document.querySelectorAll('input[name="cid[]"]:checked');
    if (checkboxes.length === 0) {
        showNotification('Please select at least one article', 'warning');
        return;
    }
    
    // Get article IDs and data
    bulkAiArticles = [];
    for (var i = 0; i < checkboxes.length; i++) {
        var articleId = checkboxes[i].value;
        // Find article data in filtered results
        for (var j = 0; j < filteredResults.length; j++) {
            if (filteredResults[j].id == articleId) {
                bulkAiArticles.push(filteredResults[j]);
                break;
            }
        }
    }
    
    if (bulkAiArticles.length === 0) {
        showNotification('No valid articles found for selected items', 'error');
        return;
    }
    
    // Initialize bulk processing
    currentBulkArticleIndex = 0;
    bulkAiResults = {};
    bulkAiChanges = {};
    isBulkAiProcessing = true;
    bulkProcessingPhase = 'editing';
    
    // Initialize change storage for each article
    for (var i = 0; i < bulkAiArticles.length; i++) {
        bulkAiChanges[bulkAiArticles[i].id] = {
            accepted: false,
            originalValues: {},
            aiValues: {},
            finalValues: {}
        };
    }
    
    showNotification('Starting bulk AI fix for ' + bulkAiArticles.length + ' articles...', 'info');
    
    // Open modal with first article
    openBulkSeoModal();
}

/**
 * Navigate between bulk articles
 */
function navigateBulkArticle(direction) {
    var newIndex = currentBulkArticleIndex + direction;
    if (newIndex < 0 || newIndex >= bulkAiArticles.length) {
        return;
    }
    currentBulkArticleIndex = newIndex;
    openBulkSeoModal();
}

/**
 * Restore accepted changes
 */
function restoreAcceptedChanges(storedChange) {
    // Fill form with final values
    document.getElementById('seo-title').value = storedChange.finalValues.title || storedChange.originalValues.title;
    document.getElementById('seo-metadesc').value = storedChange.finalValues.metadesc || storedChange.originalValues.metadesc;
    document.getElementById('seo-metakey').value = storedChange.finalValues.metakey || storedChange.originalValues.metakey;
    document.getElementById('seo-alias').value = storedChange.finalValues.alias || storedChange.originalValues.alias;
    
    updateFieldCounters();
    
    // Show preview if AI changes were accepted
    if (Object.keys(storedChange.aiValues).length > 0) {
        showAIPreview();
        // Set as accepted
        aiPreviewState = 'accepted';
        updateBulkSaveButtonState();
    }
}

/**
 * Start review phase
 */
function startBulkReviewPhase() {
    bulkProcessingPhase = 'reviewing';
    currentBulkArticleIndex = 0;
    
    showNotification('Phase d\'édition terminée. Vous pouvez maintenant réviser tous les articles avant la sauvegarde finale.', 'info');
    
    // Reopen first article in review mode
    openBulkSeoModal();
}

function finishBulkAiFix() {
    isBulkAiProcessing = false;
    
    // Close modal
    if (seoModal) {
        seoModal.hide();
    }
    
    // Show summary
    var processedCount = Object.keys(bulkAiResults).length;
    var successCount = 0;
    var errorCount = 0;
    
    Object.keys(bulkAiResults).forEach(function(articleId) {
        if (bulkAiResults[articleId].success) {
            successCount++;
        } else {
            errorCount++;
        }
    });
    
    var message = 'Bulk AI fix completed: ' + successCount + ' successful, ' + errorCount + ' errors out of ' + processedCount + ' articles processed';
    showNotification(message, successCount > 0 ? 'success' : 'warning');
    
    // Refresh the analysis to show updated results
    setTimeout(function() {
        startAnalysis();
    }, 2000);
}

/**
 * Finish bulk save process
 */
function finishBulkSave(successCount, errorCount) {
    var totalCount = successCount + errorCount;
    var message;
    
    if (errorCount === 0) {
        message = 'Correction IA groupée : ' + successCount + ' articles sauvegardés avec succès';
        showNotification(message, 'success');
    } else if (successCount === 0) {
        message = 'Correction IA groupée : Échec de la sauvegarde de tous les articles (' + errorCount + ' erreurs)';
        showNotification(message, 'error');
    } else {
        message = 'Correction IA groupée : ' + successCount + ' articles sauvegardés, ' + errorCount + ' erreurs sur ' + totalCount;
        showNotification(message, 'warning');
    }
    
    // Close modal and reset
    if (seoModal) {
        seoModal.hide();
    }
    
    isBulkAiProcessing = false;
    bulkProcessingPhase = 'editing';
    
    // Refresh analysis to show updated results
    setTimeout(function() {
        startAnalysis();
    }, 2000);
}
