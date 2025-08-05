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
    safeUpdateSaveButtonState();
    
    // Store image issues immediately for persistent display during form updates
    window.currentImageIssues = article.issues.filter(function(issue) {
        return issue.type === 'missing_alt_tags' && issue.details;
    });
    
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
        var issue = article.issues[j];
        var li = document.createElement('li');
        
        // Check if this is an image alt attribute issue and has detailed information
        if (issue.type === 'missing_alt_tags' && issue.details) {
            li.innerHTML = createImageAltIssueDisplay(issue);
        } else {
            // Regular issue display
            li.textContent = issue.message;
        }
        
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
            
            // Store image issues globally for persistent display
            window.currentImageIssues = article.issues.filter(function(issue) {
                return issue.type === 'missing_alt_tags' && issue.details;
            });
            
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
 * Create detailed display for image alt attribute issues
 */
function createImageAltIssueDisplay(issue) {
    var details = issue.details;
    var totalProblematic = details.missing_alt + details.empty_alt;
    var uniqueId = 'image-details-' + Math.random().toString(36).substr(2, 9);
    
    var html = '<div class="text-warning">';
    html += '<i class="icon-image text-primary me-2"></i>';
    html += '<strong>' + issue.message + '</strong>';
    
    // Add expandable details section
    html += '<div class="mt-2">';
    html += '<button type="button" class="btn btn-sm btn-outline-info" onclick="toggleImageDetails(\'' + uniqueId + '\')">';
    html += '<i class="icon-eye me-1"></i>View Details';
    html += '</button>';
    html += '</div>';
    
    // Details section (initially hidden)
    html += '<div id="' + uniqueId + '" class="mt-3 p-3 border rounded" style="display: none;">';
    html += '<h6 class="text-primary"><i class="icon-info me-2"></i>Image Analysis Details</h6>';
    
    // Summary statistics
    html += '<div class="row mb-3">';
    html += '<div class="col-md-6">';
    html += '<div class="card border-warning">';
    html += '<div class="card-body p-2">';
    html += '<h6 class="card-title text-warning mb-1"><i class="icon-warning me-1"></i>Problems Found</h6>';
    html += '<ul class="list-unstyled mb-0 small">';
    html += '<li><strong>Missing alt attribute:</strong> ' + details.missing_alt + ' image(s)</li>';
    html += '<li><strong>Empty alt attribute:</strong> ' + details.empty_alt + ' image(s)</li>';
    html += '<li class="text-danger"><strong>Total problematic:</strong> ' + totalProblematic + ' image(s)</li>';
    html += '</ul>';
    html += '</div>';
    html += '</div>';
    html += '</div>';
    
    html += '<div class="col-md-6">';
    html += '<div class="card border-success">';
    html += '<div class="card-body p-2">';
    html += '<h6 class="card-title text-success mb-1"><i class="icon-checkmark me-1"></i>Good Images</h6>';
    html += '<ul class="list-unstyled mb-0 small">';
    html += '<li><strong>Proper alt attribute:</strong> ' + details.proper_alt + ' image(s)</li>';
    html += '<li><strong>Total images:</strong> ' + details.total_images + ' image(s)</li>';
    html += '</ul>';
    html += '</div>';
    html += '</div>';
    html += '</div>';
    html += '</div>';
    
    // Section breakdown
    if (details.introtext_analysis && details.introtext_analysis.image_count > 0) {
        html += '<div class="mb-3">';
        html += '<h6 class="text-info"><i class="icon-file-text me-2"></i>Intro Text Analysis</h6>';
        html += '<div class="small">';
        html += '<span class="badge bg-secondary me-2">Total: ' + details.introtext_analysis.image_count + '</span>';
        if (details.introtext_analysis.missing_alt > 0) {
            html += '<span class="badge bg-danger me-2">Missing alt: ' + details.introtext_analysis.missing_alt + '</span>';
        }
        if (details.introtext_analysis.empty_alt > 0) {
            html += '<span class="badge bg-warning me-2">Empty alt: ' + details.introtext_analysis.empty_alt + '</span>';
        }
        if (details.introtext_analysis.proper_alt > 0) {
            html += '<span class="badge bg-success me-2">Proper alt: ' + details.introtext_analysis.proper_alt + '</span>';
        }
        html += '</div>';
        html += '</div>';
    }
    
    if (details.fulltext_analysis && details.fulltext_analysis.image_count > 0) {
        html += '<div class="mb-3">';
        html += '<h6 class="text-info"><i class="icon-file-text me-2"></i>Full Text Analysis</h6>';
        html += '<div class="small">';
        html += '<span class="badge bg-secondary me-2">Total: ' + details.fulltext_analysis.image_count + '</span>';
        if (details.fulltext_analysis.missing_alt > 0) {
            html += '<span class="badge bg-danger me-2">Missing alt: ' + details.fulltext_analysis.missing_alt + '</span>';
        }
        if (details.fulltext_analysis.empty_alt > 0) {
            html += '<span class="badge bg-warning me-2">Empty alt: ' + details.fulltext_analysis.empty_alt + '</span>';
        }
        if (details.fulltext_analysis.proper_alt > 0) {
            html += '<span class="badge bg-success me-2">Proper alt: ' + details.fulltext_analysis.proper_alt + '</span>';
        }
        html += '</div>';
        html += '</div>';
    }
    
    // Problematic images list
    if (details.problematic_images && details.problematic_images.length > 0) {
        html += '<div class="mb-3">';
        html += '<h6 class="text-danger"><i class="icon-warning me-2"></i>Problematic Images</h6>';
        html += '<div class="table-responsive">';
        html += '<table class="table table-sm table-striped">';
        html += '<thead><tr><th>Location</th><th>Issue</th><th>Source</th></tr></thead>';
        html += '<tbody>';
        
        for (var i = 0; i < Math.min(details.problematic_images.length, 10); i++) {
            var img = details.problematic_images[i];
            html += '<tr>';
            html += '<td><span class="badge bg-info">' + img.content_type + '</span></td>';
            html += '<td><span class="badge bg-' + (img.issue.includes('Missing') ? 'danger' : 'warning') + '">' + img.issue + '</span></td>';
            html += '<td class="text-truncate" style="max-width: 200px;" title="' + (img.src || 'No source') + '">';
            html += '<small>' + (img.src ? img.src.substring(0, 50) + (img.src.length > 50 ? '...' : '') : 'No source') + '</small>';
            html += '</td>';
            html += '</tr>';
        }
        
        if (details.problematic_images.length > 10) {
            html += '<tr><td colspan="3" class="text-center text-muted"><small>... and ' + (details.problematic_images.length - 10) + ' more images</small></td></tr>';
        }
        
        html += '</tbody>';
        html += '</table>';
        html += '</div>';
        html += '</div>';
    }
    
    html += '<div class="text-muted small">';
    html += '<i class="icon-info me-1"></i>';
    html += '<strong>Recommendation:</strong> Add descriptive alt attributes to all images for better SEO and accessibility.';
    html += '</div>';
    
    html += '</div>'; // Close details section
    html += '</div>'; // Close main div
    
    return html;
}

/**
 * Toggle image details display
 */
function toggleImageDetails(detailsId) {
    var detailsElement = document.getElementById(detailsId);
    var button = document.querySelector('button[onclick*="' + detailsId + '"]');
    
    if (detailsElement.style.display === 'none') {
        detailsElement.style.display = 'block';
        if (button) {
            button.innerHTML = '<i class="icon-eye-blocked me-1"></i>Hide Details';
        }
    } else {
        detailsElement.style.display = 'none';
        if (button) {
            button.innerHTML = '<i class="icon-eye me-1"></i>View Details';
        }
    }
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
            html += '<small class="fw-bold">' + optimized + '</small>';
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