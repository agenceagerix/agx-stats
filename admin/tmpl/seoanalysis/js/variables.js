/*-----------------------------------------------------------------------------------------------------/
	@version		1.2.0
	@build			29th July, 2025
	@created		29th July, 2025
	@package		JoomlaHits
	@subpackage		variables.js
	@author			Hugo Dantas - Agence Agerix <https://www.agerix.fr>
	@copyright		Copyright (C) 2025. All Rights Reserved
	@license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html
/------------------------------------------------------------------------------------------------------*/

// Global variables for SEO Analysis
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

// Bulk AI Fix variables
var bulkAiArticles = [];
var currentBulkArticleIndex = 0;
var bulkAiResults = {};
var bulkAiChanges = {}; // Store all changes before final save
var isBulkAiProcessing = false;
var bulkProcessingPhase = 'editing'; // 'editing' or 'reviewing'

// Force AI variables
var forceAiArticles = [];
var currentForceAiIndex = 0;
var forceAiChanges = {};
var isForceAiProcessing = false;
var forceAiCancelled = false;

// Modal variables
var currentArticleData = null;
var seoModal = null;
var aiPreviewState = null; // null: no preview, 'pending': waiting for accept/reject, 'accepted': accepted, 'rejected': rejected