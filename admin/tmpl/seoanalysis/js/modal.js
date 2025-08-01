/*-----------------------------------------------------------------------------------------------------/
	@version		1.2.0
	@build			29th July, 2025
	@created		29th July, 2025
	@package		JoomlaHits
	@subpackage		modal.js
	@author			Hugo Dantas - Agence Agerix <https://www.agerix.fr>
	@copyright		Copyright (C) 2025. All Rights Reserved
	@license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html
/------------------------------------------------------------------------------------------------------*/

/**
 * Open SEO modal for single article
 */
function openSeoModal(articleId) {
    // Find article in results (for issues and basic info)
    var article = null;
    for (var i = 0; i < filteredResults.length; i++) {
        if (filteredResults[i].id == articleId) {
            article = filteredResults[i];
            break;
        }
    }
    
    if (!article) return;
    
    // Reset AI preview state
    aiPreviewState = null;
    document.getElementById('ai-preview-section').style.display = 'none';
    updateSaveButtonState();
    
    // Show loading state in modal
    document.getElementById('seo-article-id').value = articleId;
    document.getElementById('seo-title').value = 'Loading...';
    document.getElementById('seo-metadesc').value = 'Loading...';
    document.getElementById('seo-metakey').value = 'Loading...';
    document.getElementById('seo-content').value = 'Loading...';
    
    // Display issues from the analysis results
    var issuesList = document.getElementById('issues-details');
    issuesList.innerHTML = '';
    for (var j = 0; j < article.issues.length; j++) {
        var li = document.createElement('li');
        li.textContent = article.issues[j].message;
        issuesList.appendChild(li);
    }
    
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
        if (data.success && data.data) {
            var fullArticle = data.data;
            
            // Store complete article data
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
            
            // Fill form with real data
            document.getElementById('seo-title').value = fullArticle.title || '';
            document.getElementById('seo-metadesc').value = fullArticle.metadesc || '';
            document.getElementById('seo-metakey').value = fullArticle.metakey || '';
            document.getElementById('seo-content').value = fullArticle.content || '';
            
            // Update counters
            updateFieldCounters();
        } else {
            showNotification('Error loading article details: ' + (data.message || 'Unknown error'), 'error');
        }
    })
    .catch(error => {
        console.error('Error loading article:', error);
        showNotification('Error loading article details: ' + error.message, 'error');
    });
}

/**
 * Add issue to issues list
 */
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

/**
 * Get field label
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
 * Show AI preview
 */
function showAIPreview() {
    var previewSection = document.getElementById('ai-preview-section');
    var previewContent = document.getElementById('ai-preview-content');
    
    var html = '';
    var fieldLabels = {
        'title': 'Title',
        'metadesc': 'Meta Description',
        'metakey': 'Keywords'
    };
    
    Object.keys(window.aiOptimizedValues).forEach(function(fieldType) {
        var original = window.originalValues[fieldType];
        var optimized = window.aiOptimizedValues[fieldType];
        
        // Only show fields that were actually modified
        if (optimized !== undefined && original !== optimized) {
            html += '<div class="row mb-3">';
            html += '<div class="col-12">';
            html += '<h6 class="fw-bold text-primary">' + fieldLabels[fieldType] + '</h6>';
            html += '</div>';
            html += '<div class="col-md-6">';
            html += '<div class="card border-danger">';
            html += '<div class="card-header py-2">';
            html += '<small class="text-danger fw-bold"><i class="icon-times me-1"></i>BEFORE</small>';
            html += '</div>';
            html += '<div class="card-body py-2">';
            html += '<small class="text-muted">' + (original || '<em>Empty</em>') + '</small>';
            html += '</div>';
            html += '</div>';
            html += '</div>';
            html += '<div class="col-md-6">';
            html += '<div class="card border-success">';
            html += '<div class="card-header py-2">';
            html += '<small class="text-success fw-bold"><i class="icon-checkmark me-1"></i>AFTER</small>';
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
        html += '<i class="icon-info me-2"></i>No modifications were made by AI.';
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