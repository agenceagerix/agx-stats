/*-----------------------------------------------------------------------------------------------------/
	@version		1.2.0
	@build			29th July, 2025
	@created		29th July, 2025
	@package		JoomlaHits
	@subpackage		display.js
	@author			Hugo Dantas - Agence Agerix <https://www.agerix.fr>
	@copyright		Copyright (C) 2025. All Rights Reserved
	@license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html
/------------------------------------------------------------------------------------------------------*/

/**
 * Display analysis results
 */
function displayResults(results) {
    var totalArticles = results.total_articles || 0;
    var articlesWithIssues = results.issues ? results.issues.length : 0;
    
    if (!results.issues || results.issues.length === 0) {
        // Show stats for perfect results using language variable
        var statsText = window.JOOMLA_LANG_STATS.perfect.replace('%d', totalArticles);
        document.getElementById('analysis-stats-text').textContent = statsText;
        document.getElementById('analysis-stats').style.display = 'block';
        
        document.getElementById('no-issues-section').style.display = 'block';
        return;
    }
    
    filteredResults = results.issues;
    
    // Show analysis statistics using language variable
    var percentage = totalArticles > 0 ? Math.round((articlesWithIssues / totalArticles) * 100 * 10) / 10 : 0;
    
    // Replace placeholders in the correct order
    var statsText = window.JOOMLA_LANG_STATS.withIssues;
    var replacements = [articlesWithIssues, totalArticles, percentage];
    var index = 0;
    
    // Replace %d and %s placeholders in order
    statsText = statsText.replace(/%[ds]/g, function() {
        return replacements[index++];
    });
    
    document.getElementById('analysis-stats-text').textContent = statsText;
    document.getElementById('analysis-stats').style.display = 'block';
    
    document.getElementById('results-section').style.display = 'block';
    populateTable(filteredResults);
    
    // Initialize sorting icons for default sort (severity ASC)
    if (typeof updateSortingIcons === 'function') {
        updateSortingIcons('severity', 'asc');
    }
    
    // Initialize checkbox handlers after populating the table
    initializeCheckboxHandlers();
}

/**
 * Populate results table
 */
function populateTable(articles) {
    var tbody = document.getElementById('results-tbody');
    tbody.innerHTML = '';
    
    for (var i = 0; i < articles.length; i++) {
        var row = createTableRow(articles[i], i);
        tbody.appendChild(row);
    }
}

/**
 * Apply filters to results
 */
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
    
    // Re-initialize checkbox handlers after filtering
    initializeCheckboxHandlers();
}

/**
 * Initialize checkbox handlers for Joomla integration
 */
function initializeCheckboxHandlers() {
    // Joomla handles most of this automatically with the onclick="Joomla.isChecked(this.checked);"
    // We just need to make sure the check-all functionality works
    
    // Re-initialize Joomla's checkbox system if it exists
    if (typeof Joomla !== 'undefined' && Joomla.checkAll) {
        // Find the check-all checkbox and re-bind it
        var checkAllBox = document.querySelector('input[name="checkall-toggle"]');
        if (checkAllBox) {
            checkAllBox.onclick = function() {
                Joomla.checkAll(this);
            };
        }
    }
}