/*-----------------------------------------------------------------------------------------------------/
	@version		1.2.0
	@build			29th July, 2025
	@created		29th July, 2025
	@package		JoomlaHits
	@subpackage		utils.js
	@author			Hugo Dantas - Agence Agerix <https://www.agerix.fr>
	@copyright		Copyright (C) 2025. All Rights Reserved
	@license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html
/------------------------------------------------------------------------------------------------------*/

/**
 * Accept AI changes
 */
function acceptAIChanges() {
    if (isBulkAiProcessing) {
        acceptBulkAIChanges();
        return;
    }
    
    // Original single article logic
    document.getElementById('ai-preview-section').style.display = 'none';
    aiPreviewState = 'accepted';
    safeUpdateSaveButtonState();
    updateFieldCounters();
    
    // Afficher un message de confirmation
    var alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-success alert-dismissible fade show mt-3';
    alertDiv.innerHTML = '<i class="icon-checkmark me-2"></i>AI modifications accepted! You can now save.' +
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

/**
 * Accept bulk AI changes
 */
function acceptBulkAIChanges() {
    var articleId = currentArticleData.id;
    var storedChange = bulkAiChanges[articleId];
    
    // Store the accepted AI values (not current form values)
    storedChange.accepted = true;
    storedChange.finalValues = {};
    
    // Apply AI optimized values to both storage and form fields
    Object.keys(window.aiOptimizedValues).forEach(function(fieldType) {
        var aiValue = window.aiOptimizedValues[fieldType];
        var fieldElement = document.getElementById('seo-' + fieldType);
        
        if (aiValue !== undefined && fieldElement) {
            // Store the AI optimized value
            storedChange.finalValues[fieldType] = aiValue;
            // Apply to form field immediately
            fieldElement.value = aiValue;
        } else {
            // Keep original value if no AI optimization
            var originalValue = window.originalValues[fieldType] || '';
            storedChange.finalValues[fieldType] = originalValue;
            if (fieldElement) {
                fieldElement.value = originalValue;
            }
        }
    });
    
    // Hide preview
    document.getElementById('ai-preview-section').style.display = 'none';
    aiPreviewState = 'accepted';
    updateBulkSaveButtonState();
    updateFieldCounters();
    
    // Show confirmation
    showNotification('Modifications accepted for "' + currentArticleData.title + '"', 'success');
    
    // In editing phase, auto-navigate to next article immediately
    if (bulkProcessingPhase === 'editing') {
        if (currentBulkArticleIndex < bulkAiArticles.length - 1) {
            navigateBulkArticle(1);
        } else {
            // All articles processed, switch to review phase
            startBulkReviewPhase();
        }
    }
}

/**
 * Reject AI changes
 */
function rejectAIChanges() {
    if (isBulkAiProcessing) {
        rejectBulkAIChanges();
        return;
    }
    
    // Original single article logic
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
    safeUpdateSaveButtonState();
    updateFieldCounters();
    
    // Afficher un message de confirmation
    var alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-info alert-dismissible fade show mt-3';
    alertDiv.innerHTML = '<i class="icon-info me-2"></i>AI modifications cancelled. Original values have been restored.' +
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

/**
 * Reject bulk AI changes
 */
function rejectBulkAIChanges() {
    var articleId = currentArticleData.id;
    var storedChange = bulkAiChanges[articleId];
    
    // Restore original values to form fields
    Object.keys(window.originalValues).forEach(function(fieldType) {
        var fieldElement = document.getElementById('seo-' + fieldType);
        if (fieldElement) {
            fieldElement.value = window.originalValues[fieldType];
        }
    });
    
    // Store the rejected changes (keeping original values)
    storedChange.accepted = true; // Still mark as processed
    storedChange.finalValues = {
        title: window.originalValues.title || '',
        metadesc: window.originalValues.metadesc || '',
        metakey: window.originalValues.metakey || ''
    };
    
    // Hide preview
    document.getElementById('ai-preview-section').style.display = 'none';
    aiPreviewState = 'rejected';
    updateBulkSaveButtonState();
    updateFieldCounters();
    
    // Show confirmation
    showNotification('AI modifications rejected for "' + currentArticleData.title + '" - original values kept', 'info');
    
    // In editing phase, auto-navigate to next article immediately
    if (bulkProcessingPhase === 'editing') {
        if (currentBulkArticleIndex < bulkAiArticles.length - 1) {
            navigateBulkArticle(1);
        } else {
            // All articles processed, switch to review phase
            startBulkReviewPhase();
        }
    }
}


/**
 * Update save button state for bulk processing
 */
function updateBulkSaveButtonState() {
    var saveBtn = document.getElementById('saveSeoBtn');
    
    if (bulkProcessingPhase === 'editing') {
        // Completely hide the save button during editing phase
        saveBtn.style.display = 'none';
    } else if (bulkProcessingPhase === 'reviewing') {
        // In review phase, show final save button
        saveBtn.style.display = 'block';
        
        var processedCount = 0;
        Object.keys(bulkAiChanges).forEach(function(articleId) {
            if (bulkAiChanges[articleId].accepted) {
                processedCount++;
            }
        });
        
        saveBtn.disabled = false;
        saveBtn.innerHTML = '<i class="icon-checkmark me-2"></i>Save all articles (' + processedCount + ')';
        saveBtn.className = 'btn btn-success px-4';
        saveBtn.title = 'Save all modified articles permanently';
    }
}

/**
 * Save SEO fixes for single article
 */
function saveSeoFixes() {
    if (isBulkAiProcessing) {
        if (bulkProcessingPhase === 'editing') {
            handleBulkEditingNavigation();
        } else if (bulkProcessingPhase === 'reviewing') {
            saveBulkSeoFixes();
        }
    } else {
        saveSingleSeoFixes();
    }
}

/**
 * Handle bulk editing navigation
 */
function handleBulkEditingNavigation() {
    if (currentBulkArticleIndex < bulkAiArticles.length - 1) {
        // Go to next article
        navigateBulkArticle(1);
    } else {
        // All articles processed, switch to review phase
        startBulkReviewPhase();
    }
}

/**
 * Update sorting icons based on current sort column and direction
 */
function updateSortingIcons(column, direction) {
    // Remove all existing sort icons
    var sortLinks = document.querySelectorAll('.js-seo-sort');
    sortLinks.forEach(function(link) {
        var icon = link.querySelector('span[class*="icon-caret"]');
        if (icon) {
            icon.remove();
        }
    });
    
    // Add icon to the current sorted column
    var currentLink = document.querySelector('.js-seo-sort[data-column="' + column + '"]');
    if (currentLink) {
        var iconClass = direction === 'asc' ? 'icon-caret-up' : 'icon-caret-down';
        var icon = document.createElement('span');
        icon.className = 'ms-2 ' + iconClass;
        icon.setAttribute('aria-hidden', 'true');
        currentLink.appendChild(icon);
    }
}

/**
 * Get field label for display
 */
function getFieldLabel(fieldType) {
    var labels = {
        'title': 'Title',
        'metadesc': 'Meta desc',
        'metakey': 'Keywords'
    };
    return labels[fieldType] || fieldType;
}

/**
 * Check if a field has issues based on current article data
 */
function fieldHasIssues(fieldType, article) {
    if (!article || !article.issues) return false;
    
    var issueTypes = {
        'title': ['title_missing', 'title_too_short', 'title_too_long'],
        'metadesc': ['meta_desc_missing', 'meta_desc_too_short', 'meta_desc_too_long'],
        'metakey': ['meta_keywords_missing', 'meta_keywords_too_few']
    };
    
    var fieldIssues = issueTypes[fieldType] || [];
    
    // Check if any of the field's issue types are present in the article's issues
    for (var i = 0; i < article.issues.length; i++) {
        if (fieldIssues.includes(article.issues[i].type)) {
            return true;
        }
    }
    
    return false;
}

/**
 * Safe wrapper for updating save button state that always checks bulk mode
 */
function safeUpdateSaveButtonState() {
    // In bulk mode, never show the save button during editing phase
    if (isBulkAiProcessing && bulkProcessingPhase === 'editing') {
        var saveBtn = document.getElementById('saveSeoBtn');
        if (saveBtn) {
            saveBtn.style.display = 'none';
        }
        return;
    }
    
    // Otherwise, use the appropriate update function
    if (isBulkAiProcessing) {
        updateBulkSaveButtonState();
    } else {
        updateSaveButtonState();
    }
}
