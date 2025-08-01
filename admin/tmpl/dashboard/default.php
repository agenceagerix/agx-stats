<?php
/*-----------------------------------------------------------------------------------------------------/
	@version		1.2.0
	@build			22nd July, 2025
	@created		21st July, 2025
	@package		JoomlaHits
	@subpackage		default.php
	@author			Hugo Dantas - Agence Agerix <https://www.agerix.fr>
	@copyright		Copyright (C) 2025. All Rights Reserved
	@license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html
	  __    ___  ____  __ _   ___  ____     __    ___  ____  ____  __  _  _
	 / _\  / __)(  __)(  ( \ / __)(  __)   / _\  / __)(  __)(  _ \(  )( \/ )
	/    \( (_ \ ) _) /    /( (__  ) _)   /    \( (_ \ ) _)  )   / )(  )  (
	\_/\_/ \___/(____)\_)__) \___)(____)  \_/\_/ \___/(____)(__\_)(__)(_/\_)
/------------------------------------------------------------------------------------------------------*/
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;

$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('bootstrap.collapse');
?>

<div class="row">
    <div class="col-12">
        <div id="j-main-container" class="j-main-container">

            <!-- Page Header -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">
                                <i class="icon-dashboard"></i> Dashboard
                            </h2>
                        </div>
                        <div class="card-body">
                            <p class="card-text"><?php echo Text::_('COM_JOOMLAHITS_DASHBOARD_SUBTITLE'); ?></p>
                            <a href="<?php echo Route::_('index.php?option=com_joomlahits&view=controlpanel'); ?>" class="btn btn-secondary">
                                <i class="icon-arrow-left"></i> <?php echo Text::_('COM_JOOMLAHITS_BACK_TO_CONTROL_PANEL'); ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dashboard Content -->
            <div class="tab-content" id="nav-tabContent">
                <div class="tab-pane fade show active" id="nav-dashboard" role="tabpanel" aria-labelledby="nav-dashboard-tab">

                    <!-- Overview Statistics -->
                    <?php if ($this->dashboardStats): ?>
                    <div class="row mb-4">
                        <div class="col-6 col-sm-6 col-md-4 col-xxl-2">
                            <div class="card text-center stats-card h-100">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo Text::_('COM_JOOMLAHITS_TOTAL_ARTICLES'); ?></h5>
                                    <p class="card-text display-4 text-primary"><?php echo $this->dashboardStats->total_articles; ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-sm-6 col-md-4 col-xxl-2">
                            <div class="card text-center stats-card h-100">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo Text::_('COM_JOOMLAHITS_PUBLISHED'); ?></h5>
                                    <p class="card-text display-4 text-success"><?php echo $this->dashboardStats->published_articles; ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-sm-6 col-md-4 col-xxl-2">
                            <div class="card text-center stats-card h-100">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo Text::_('COM_JOOMLAHITS_TOTAL_VIEWS'); ?></h5>
                                    <p class="card-text display-4 text-info"><?php echo number_format($this->dashboardStats->total_hits ?? 0); ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-sm-6 col-md-4 col-xxl-2">
                            <div class="card text-center stats-card h-100">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo Text::_('COM_JOOMLAHITS_AVERAGE_VIEWS'); ?></h5>
                                    <p class="card-text display-4 text-warning"><?php echo number_format($this->dashboardStats->average_hits ?? 0, 1); ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-sm-6 col-md-4 col-xxl-2">
                            <div class="card text-center stats-card h-100">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo Text::_('COM_JOOMLAHITS_MAX_VIEWS'); ?></h5>
                                    <p class="card-text display-4 text-danger"><?php echo number_format($this->dashboardStats->max_hits ?? 0); ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-sm-6 col-md-4 col-xxl-2">
                            <div class="card text-center stats-card h-100">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo Text::_('COM_JOOMLAHITS_WITH_VIEWS'); ?></h5>
                                    <p class="card-text display-4 text-secondary"><?php echo $this->dashboardStats->articles_with_hits; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Articles without clicks rate -->
                    <?php if ($this->articlesWithoutClicks): ?>
                    <div class="row mb-4">
                        <div class="col-12 col-lg-4 mb-3 mb-lg-0">
                            <div class="card stats-card h-100">
                                <div class="card-header">
                                    <h5>📊 <?php echo Text::_('COM_JOOMLAHITS_ARTICLES_WITHOUT_CLICKS_TITLE'); ?></h5>
                                </div>
                                <div class="card-body">
                                    <h3 class="text-danger"><?php echo $this->articlesWithoutClicks->no_clicks_percentage; ?>%</h3>
                                    <p class="mb-2"><?php echo $this->articlesWithoutClicks->articles_without_clicks; ?> <?php echo Text::_('COM_JOOMLAHITS_ARTICLES_OUT_OF'); ?> <?php echo $this->articlesWithoutClicks->total_articles; ?></p>
                                    <div class="progress">
                                        <div class="progress-bar bg-danger" role="progressbar" style="width: <?php echo $this->articlesWithoutClicks->no_clicks_percentage; ?>%" aria-valuenow="<?php echo $this->articlesWithoutClicks->no_clicks_percentage; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-lg-8">
                            <!-- Period comparison -->
                            <?php if ($this->periodComparison): ?>
                            <div class="card stats-card h-100">
                                <div class="card-header">
                                    <h5>📈 <?php echo Text::_('COM_JOOMLAHITS_MONTHLY_COMPARISON_TITLE'); ?></h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12 col-md-4 mb-3 mb-md-0">
                                            <h6><?php echo Text::_('COM_JOOMLAHITS_ARTICLES_CREATED'); ?></h6>
                                            <h4><?php echo $this->periodComparison->current_period->articles_created; ?></h4>
                                            <small class="<?php echo $this->periodComparison->articles_change >= 0 ? 'text-success' : 'text-danger'; ?>">
                                                <?php echo $this->periodComparison->articles_change >= 0 ? '+' : ''; ?><?php echo $this->periodComparison->articles_change; ?> 
                                                (<?php echo $this->periodComparison->articles_change_percent; ?>%)
                                            </small>
                                        </div>
                                        <div class="col-12 col-md-4 mb-3 mb-md-0">
                                            <h6><?php echo Text::_('COM_JOOMLAHITS_TOTAL_VIEWS'); ?></h6>
                                            <h4><?php echo number_format($this->periodComparison->current_period->total_hits ?? 0); ?></h4>
                                            <small class="<?php echo $this->periodComparison->hits_change >= 0 ? 'text-success' : 'text-danger'; ?>">
                                                <?php echo $this->periodComparison->hits_change >= 0 ? '+' : ''; ?><?php echo number_format($this->periodComparison->hits_change ?? 0); ?> 
                                                (<?php echo $this->periodComparison->hits_change_percent; ?>%)
                                            </small>
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <h6><?php echo Text::_('COM_JOOMLAHITS_PREVIOUS_MONTH'); ?></h6>
                                            <p><?php echo Text::_('COM_JOOMLAHITS_ARTICLES_CREATED'); ?>: <?php echo $this->periodComparison->previous_period->articles_created; ?></p>
                                            <p><?php echo Text::_('COM_JOOMLAHITS_VIEWS'); ?>: <?php echo number_format($this->periodComparison->previous_period->total_hits ?? 0); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Dynamic Top Articles by Language - ENHANCED VERSION -->
                    <?php if ($this->params->get('show_language_stats', 1) && !empty($this->availableLanguages) && !empty($this->topArticlesByLanguage)): ?>
                    <div class="row mb-4">
                        <?php 
                        $displayedLanguages = 0;
                        $maxLanguages = $this->params->get('max_languages_display', 4);
                        $languageCount = min(count($this->availableLanguages), $maxLanguages);
                        
                        // Define responsive classes based on the number of languages
                        $colClasses = '';
                        switch($languageCount) {
                            case 1:
                                $colClasses = 'col-12';
                                break;
                            case 2:
                                $colClasses = 'col-12 col-lg-6';
                                break;
                            case 3:
                                $colClasses = 'col-12 col-md-6 col-xl-4';
                                break;
                            case 4:
                            default:
                                $colClasses = 'col-12 col-md-6 col-xl-3';
                                break;
                        }
                        
                        foreach ($this->availableLanguages as $language): 
                            if ($displayedLanguages >= $maxLanguages) break;
                            if (!empty($this->topArticlesByLanguage[$language->language])):
                                // Get language flag/icon
                                $languageIcon = '';
                                $languageTitle = $language->language_name;
                                switch($language->language) {
                                    case 'fr-FR':
                                        $languageIcon = '🇫🇷';
                                        break;
                                    case 'en-GB':
                                        $languageIcon = '🇬🇧';
                                        break;
                                    case 'es-ES':
                                        $languageIcon = '🇪🇸';
                                        break;
                                    case 'de-DE':
                                        $languageIcon = '🇩🇪';
                                        break;
                                    case 'it-IT':
                                        $languageIcon = '🇮🇹';
                                        break;
                                    default:
                                        $languageIcon = '🌍';
                                }
                        ?>
                        <div class="<?php echo $colClasses; ?> mb-4">
                            <div class="card stats-card h-100">
                                <div class="card-header d-flex align-items-center justify-content-between">
                                    <h5 class="mb-0 text-truncate">
                                        <span class="me-2"><?php echo $languageIcon; ?></span>
                                        <span class="d-none d-sm-inline"><?php echo sprintf(Text::_('COM_JOOMLAHITS_TOP_ARTICLES_TITLE'), $this->escape($languageTitle)); ?></span>
                                        <span class="d-inline d-sm-none"><?php echo sprintf(Text::_('COM_JOOMLAHITS_TOP_ARTICLES_TITLE'), $this->escape($languageTitle)); ?></span>
                                    </h5>
                                    <small class="text-muted d-none d-md-block">
                                        <?php echo count($this->topArticlesByLanguage[$language->language]); ?> <?php echo Text::_('COM_JOOMLAHITS_ARTICLES_COUNT'); ?>
                                    </small>
                                </div>
                                <div class="card-body p-2 p-sm-3">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-sm mb-0">
                                            <thead class="d-none d-md-table-header-group">
                                                <tr>
                                                    <th class="border-top-0"><?php echo Text::_('COM_JOOMLAHITS_TITLE'); ?></th>
                                                    <th class="border-top-0 d-none d-lg-table-cell"><?php echo Text::_('COM_JOOMLAHITS_CATEGORY'); ?></th>
                                                    <th class="border-top-0 text-center" style="width: 80px;"><?php echo Text::_('COM_JOOMLAHITS_VIEWS'); ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                $articleIndex = 0;
                                                foreach ($this->topArticlesByLanguage[$language->language] as $article): 
                                                    $articleIndex++;
                                                ?>
                                                    <tr class="<?php echo $articleIndex > 5 ? 'd-none d-lg-table-row' : ''; ?>" data-language="<?php echo $language->language; ?>">
                                                        <td class="align-middle">
                                                            <!-- Mobile version with ranking -->
                                                            <div class="d-md-none">
                                                                <div class="d-flex align-items-start">
                                                                    <span class="badge bg-<?php echo $articleIndex <= 3 ? 'warning' : 'secondary'; ?> me-2 mt-1 flex-shrink-0" style="min-width: 24px;">
                                                                        <?php echo $articleIndex; ?>
                                                                    </span>
                                                                    <div class="flex-grow-1 min-width-0">
                                                                        <a href="<?php echo Route::_('index.php?option=com_content&task=article.edit&id=' . $article->id); ?>" 
                                                                           class="text-decoration-none fw-medium d-block text-truncate" 
                                                                           title="<?php echo $this->escape($article->title); ?>">
                                                                            <?php echo $this->escape($article->title); ?>
                                                                        </a>
                                                                        <div class="d-flex justify-content-between align-items-center mt-1">
                                                                            <small class="text-muted text-truncate me-2">
                                                                                <?php echo $this->escape($article->category_title); ?>
                                                                            </small>
                                                                            <span class="badge bg-info flex-shrink-0">
                                                                                <i class="icon-eye"></i> <?php echo number_format($article->hits ?? 0); ?>
                                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            
                                                            <!-- Desktop version -->
                                                            <div class="d-none d-md-block">
                                                                <a href="<?php echo Route::_('index.php?option=com_content&task=article.edit&id=' . $article->id); ?>" 
                                                                   class="text-decoration-none" 
                                                                   title="<?php echo $this->escape($article->title); ?>">
                                                                    <?php echo $this->escape(strlen($article->title) > 40 ? substr($article->title, 0, 40) . '...' : $article->title); ?>
                                                                </a>
                                                            </div>
                                                        </td>
                                                        <td class="d-none d-lg-table-cell align-middle">
                                                            <small><?php echo $this->escape($article->category_title); ?></small>
                                                        </td>
                                                        <td class="text-center align-middle d-none d-md-table-cell">
                                                            <span class="badge bg-info"><?php echo number_format($article->hits ?? 0); ?></span>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    
                                    <!-- "Show more" button for mobile if more than 5 articles -->
                                    <?php if (count($this->topArticlesByLanguage[$language->language]) > 5): ?>
                                    <div class="text-center mt-2 d-lg-none">
                                        <button class="btn btn-sm btn-outline-secondary toggle-more-articles" 
                                                data-language="<?php echo $language->language; ?>">
                                            <span class="show-more"><?php echo sprintf(Text::_('COM_JOOMLAHITS_SHOW_MORE'), count($this->topArticlesByLanguage[$language->language]) - 5); ?></span>
                                            <span class="show-less d-none"><?php echo Text::_('COM_JOOMLAHITS_SHOW_LESS'); ?></span>
                                        </button>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php 
                            endif;
                            $displayedLanguages++;
                        endforeach; 
                        ?>
                    </div>
                    <?php elseif ($this->params->get('show_language_stats', 1)): ?>
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <span class="icon-info-circle" aria-hidden="true"></span>
                                <span><?php echo Text::_('COM_JOOMLAHITS_NO_LANGUAGE_ARTICLES'); ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Enhanced Category Statistics -->
                    <?php if ($this->params->get('show_category_stats', 1)): ?>
                    <div class="row mb-4 justify-content-center">
                        <div class="col-12 col-xl-8">
                            <div class="card stats-card h-100">
                                <div class="card-header">
                                    <h5>📊 <?php echo Text::_('COM_JOOMLAHITS_CATEGORY_RANKING_TITLE'); ?></h5>
                                </div>
                                <div class="card-body">
                                    <?php if (!empty($this->enhancedCategoryStats)): ?>
                                        <!-- Line Chart -->
                                        <div class="mb-4">
                                            <div class="chart-container">
                                                <canvas id="categoryStatsChart"></canvas>
                                            </div>
                                        </div>
                                        
                                        <!-- Data Table -->
                                        <div class="table-responsive">
                                            <table class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th><?php echo Text::_('COM_JOOMLAHITS_RANK'); ?></th>
                                                        <th><?php echo Text::_('COM_JOOMLAHITS_CATEGORY'); ?></th>
                                                        <th class="text-center"><?php echo Text::_('COM_JOOMLAHITS_ARTICLES'); ?></th>
                                                        <th class="text-center"><?php echo Text::_('COM_JOOMLAHITS_TOTAL_VIEWS'); ?></th>
                                                        <th class="text-center"><?php echo Text::_('COM_JOOMLAHITS_AVG_VIEWS'); ?></th>
                                                        <th class="text-center"><?php echo Text::_('COM_JOOMLAHITS_PERCENT_OF_TOTAL'); ?></th>
                                                        <th class="text-center"><?php echo Text::_('COM_JOOMLAHITS_PERFORMANCE'); ?></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($this->enhancedCategoryStats as $index => $category): ?>
                                                        <tr>
                                                            <td>
                                                                <?php 
                                                                $rank = $index + 1;
                                                                $badge_class = $rank <= 3 ? 'bg-success' : ($rank <= 5 ? 'bg-warning' : 'bg-secondary');
                                                                ?>
                                                                <span class="badge <?php echo $badge_class; ?>">#<?php echo $rank; ?></span>
                                                            </td>
                                                            <td><?php echo $this->escape($category->category_name); ?></td>
                                                            <td class="text-center"><?php echo $category->article_count; ?></td>
                                                            <td class="text-center">
                                                                <span class="badge bg-info"><?php echo number_format($category->total_hits ?? 0); ?></span>
                                                            </td>
                                                            <td class="text-center"><?php echo number_format($category->average_hits ?? 0, 1); ?></td>
                                                            <td class="text-center">
                                                                <span class="badge bg-primary"><?php echo $category->hits_percentage; ?>%</span>
                                                            </td>
                                                            <td class="text-center">
                                                                <div class="progress" style="height: 8px;">
                                                                    <div class="progress-bar bg-gradient" role="progressbar" style="width: <?php echo min($category->hits_percentage * 2, 100); ?>%"></div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-muted"><?php echo Text::_('COM_JOOMLAHITS_NO_CATEGORY_STATS'); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Top Articles by Category -->
                    <?php if (!empty($this->topArticlesByCategory)): ?>
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card stats-card">
                                <div class="card-header">
                                    <h5>🏆 <?php echo Text::_('COM_JOOMLAHITS_TOP_ARTICLES_BY_CATEGORY'); ?></h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <?php 
                                        $categoryCount = 0;
                                        foreach ($this->enhancedCategoryStats as $category): 
                                            if ($categoryCount >= 4) break; // Limit to 4 categories for display
                                            if (!empty($this->topArticlesByCategory[$category->category_id])):
                                        ?>
                                            <div class="col-12 col-md-6 col-xl-3 mb-4">
                                                <div class="h-100">
                                                    <div class="border-bottom pb-2 mb-3">
                                                        <h6 class="mb-0 text-primary fw-bold">
                                                            <i class="icon-folder"></i> <?php echo $this->escape($category->category_name); ?>
                                                        </h6>
                                                    </div>
                                                    <div class="list-group list-group-flush">
                                                        <?php 
                                                        $articleRank = 1;
                                                        foreach ($this->topArticlesByCategory[$category->category_id] as $article): 
                                                        ?>
                                                            <div class="list-group-item px-0 py-2 border-0 bg-transparent">
                                                                <div class="d-flex align-items-start">
                                                                    <span class="badge bg-<?php echo $articleRank <= 3 ? 'warning' : 'secondary'; ?> me-2 mt-1" style="min-width: 24px;">
                                                                        <?php echo $articleRank; ?>
                                                                    </span>
                                                                    <div class="flex-grow-1">
                                                                        <a href="<?php echo Route::_('index.php?option=com_content&task=article.edit&id=' . $article->id); ?>" 
                                                                           class="text-decoration-none fw-medium" 
                                                                           title="<?php echo $this->escape($article->title); ?>">
                                                                            <?php echo $this->escape(strlen($article->title) > 35 ? substr($article->title, 0, 35) . '...' : $article->title); ?>
                                                                        </a>
                                                                        <div class="mt-1">
                                                                            <span class="badge bg-info">
                                                                                <i class="icon-eye"></i> <?php echo number_format($article->hits ?? 0); ?>
                                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        <?php 
                                                        $articleRank++;
                                                        endforeach; 
                                                        ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php 
                                            endif;
                                            $categoryCount++;
                                        endforeach; 
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <!-- Monthly Statistics by Year -->
                    <?php if ($this->params->get('show_monthly_stats', 1) && !empty($this->availableYears)): ?>
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card stats-card">
                                <div class="card-header">
                                    <div class="d-flex align-items-center">
                                        <h5 class="mb-0">📅 <?php echo Text::_('COM_JOOMLAHITS_MONTHLY_STATS_TITLE'); ?></h5>
                                        <div class="ms-auto">
                                            <form method="get" class="d-inline" id="yearSelectorForm">
                                                <input type="hidden" name="option" value="com_joomlahits">
                                                <input type="hidden" name="view" value="dashboard">
                                                <select name="year" class="form-select form-select-sm" id="yearSelector" style="width: auto;">
                                                    <?php foreach ($this->availableYears as $year): ?>
                                                        <option value="<?php echo $year->year; ?>" <?php echo ($year->year == $this->selectedYear) ? 'selected' : ''; ?>>
                                                            <?php echo $year->year; ?> (<?php echo $year->article_count; ?> articles)
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <?php if (!empty($this->monthlyStats)): ?>
                                        <?php 
                                        $totalArticles = array_sum(array_column($this->monthlyStats, 'articles_created'));
                                        $totalViews = array_sum(array_column($this->monthlyStats, 'total_views'));
                                        $avgViews = $totalArticles > 0 ? round($totalViews / $totalArticles, 1) : 0;
                                        ?>
                                        
                                        <!-- Summary Stats - Horizontal Layout -->
                                        <div class="row mb-4">
                                            <div class="col-12 col-sm-6 col-lg-4 mb-2 mb-lg-0">
                                                <div class="d-flex align-items-center justify-content-center justify-content-sm-start">
                                                    <span class="text-nowrap"><?php echo Text::_('COM_JOOMLAHITS_MONTHLY_STATS_ARTICLES_CREATED'); ?> <?php echo $this->selectedYear; ?> : </span>
                                                    <strong class="text-primary ms-2"><?php echo $totalArticles; ?></strong>
                                                </div>
                                            </div>
                                            <div class="col-12 col-sm-6 col-lg-4 mb-2 mb-lg-0">
                                                <div class="d-flex align-items-center justify-content-center justify-content-sm-start">
                                                    <span class="text-nowrap"><?php echo Text::_('COM_JOOMLAHITS_MONTHLY_STATS_TOTAL_VIEWS'); ?> : </span>
                                                    <strong class="text-success ms-2"><?php echo number_format($totalViews ?? 0); ?></strong>
                                                </div>
                                            </div>
                                            <div class="col-12 col-sm-12 col-lg-4">
                                                <div class="d-flex align-items-center justify-content-center justify-content-lg-start">
                                                    <span class="text-nowrap"><?php echo Text::_('COM_JOOMLAHITS_MONTHLY_STATS_AVERAGE_VIEWS'); ?> : </span>
                                                    <strong class="text-info ms-2"><?php echo number_format($avgViews ?? 0, 1); ?></strong>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Monthly Breakdown -->
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="table-responsive">
                                                    <table class="table table-striped table-sm">
                                                        <thead>
                                                            <tr>
                                                                <th><?php echo Text::_('COM_JOOMLAHITS_MONTHLY_STATS_MONTH'); ?></th>
                                                                <th class="text-center"><?php echo Text::_('COM_JOOMLAHITS_MONTHLY_STATS_ARTICLES_CREATED_COL'); ?></th>
                                                                <th class="text-center"><?php echo Text::_('COM_JOOMLAHITS_MONTHLY_STATS_TOTAL_VIEWS_COL'); ?></th>
                                                                <th class="text-center"><?php echo Text::_('COM_JOOMLAHITS_MONTHLY_STATS_AVERAGE_VIEWS_COL'); ?></th>
                                                                <th class="text-center"><?php echo Text::_('COM_JOOMLAHITS_MONTHLY_STATS_PERFORMANCE'); ?></th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php 
                                                            $monthNames = [
                                                                1 => Text::_('JANUARY'),
                                                                2 => Text::_('FEBRUARY'),
                                                                3 => Text::_('MARCH'),
                                                                4 => Text::_('APRIL'),
                                                                5 => Text::_('MAY'),
                                                                6 => Text::_('JUNE'),
                                                                7 => Text::_('JULY'),
                                                                8 => Text::_('AUGUST'),
                                                                9 => Text::_('SEPTEMBER'),
                                                                10 => Text::_('OCTOBER'),
                                                                11 => Text::_('NOVEMBER'),
                                                                12 => Text::_('DECEMBER')
                                                            ];
                                                            foreach ($this->monthlyStats as $monthStat): 
                                                            ?>
                                                                <tr>
                                                                    <td>
                                                                        <strong><?php echo $monthNames[$monthStat->month]; ?></strong>
                                                                    </td>
                                                                    <td class="text-center">
                                                                        <?php if ($monthStat->articles_created > 0): ?>
                                                                            <span class="badge bg-primary"><?php echo $monthStat->articles_created; ?></span>
                                                                        <?php else: ?>
                                                                            <span class="text-muted">-</span>
                                                                        <?php endif; ?>
                                                                    </td>
                                                                    <td class="text-center">
                                                                        <?php if ($monthStat->total_views > 0): ?>
                                                                            <span class="badge bg-info"><?php echo number_format($monthStat->total_views ?? 0); ?></span>
                                                                        <?php else: ?>
                                                                            <span class="text-muted">-</span>
                                                                        <?php endif; ?>
                                                                    </td>
                                                                    <td class="text-center">
                                                                        <?php if ($monthStat->average_views > 0): ?>
                                                                            <?php echo number_format($monthStat->average_views ?? 0, 1); ?>
                                                                        <?php else: ?>
                                                                            <span class="text-muted">-</span>
                                                                        <?php endif; ?>
                                                                    </td>
                                                                    <td class="text-center">
                                                                        <?php if ($monthStat->articles_created > 0): ?>
                                                                            <?php 
                                                                            $maxArticles = max(array_column($this->monthlyStats, 'articles_created'));
                                                                            $performance = $maxArticles > 0 ? ($monthStat->articles_created / $maxArticles) * 100 : 0;
                                                                            ?>
                                                                            <div class="progress" style="height: 8px; width: 60px; margin: 0 auto;">
                                                                                <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $performance; ?>%"></div>
                                                                            </div>
                                                                        <?php else: ?>
                                                                            <span class="text-muted">-</span>
                                                                        <?php endif; ?>
                                                                    </td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-muted text-center"><?php echo Text::_('COM_JOOMLAHITS_MONTHLY_STATS_NO_DATA'); ?> <?php echo $this->selectedYear; ?>.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="row">
                        <!-- Recent Activity -->
                        <div class="col-12 col-xl-6 mb-4 mb-xl-0">
                            <div class="card stats-card h-100">
                                <div class="card-header">
                                    <h5>🕒 <?php echo Text::_('COM_JOOMLAHITS_RECENT_ACTIVITY_TITLE'); ?></h5>
                                </div>
                                <div class="card-body">
                                    <?php if (!empty($this->recentActivity)): ?>
                                        <div class="table-responsive">
                                            <table class="table table-striped table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Title</th>
                                                        <th><?php echo Text::_('COM_JOOMLAHITS_CREATED_ON'); ?></th>
                                                        <th class="text-center">Views</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($this->recentActivity as $article): ?>
                                                        <tr>
                                                            <td>
                                                                <a href="<?php echo Route::_('index.php?option=com_content&task=article.edit&id=' . $article->id); ?>" title="Edit Article">
                                                                    <?php echo $this->escape($article->title); ?>
                                                                </a>
                                                            </td>
                                                            <td><?php echo HTMLHelper::_('date', $article->created, 'M d, Y'); ?></td>
                                                            <td class="text-center">
                                                                <span class="badge bg-secondary"><?php echo number_format($article->hits ?? 0); ?></span>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-muted"><?php echo Text::_('COM_JOOMLAHITS_NO_RECENT_ARTICLES'); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Language Statistics -->
                        <?php if ($this->params->get('show_language_stats', 1)): ?>
                        <div class="col-12 col-xl-6">
                            <div class="card stats-card h-100">
                                <div class="card-header">
                                    <h5>🌍 <?php echo Text::_('COM_JOOMLAHITS_LANGUAGE_STATS_TITLE'); ?></h5>
                                </div>
                                <div class="card-body">
                                    <?php if (!empty($this->languageStats)): ?>
                                        <div class="row">
                                            <!-- Pie Chart -->
                                            <div class="col-md-6">
                                                <div class="chart-container">
                                                    <canvas id="languageStatsChart" width="300" height="300"></canvas>
                                                </div>
                                            </div>
                                            <!-- Data Table -->
                                            <div class="col-md-6">
                                                <div class="table-responsive">
                                                    <table class="table table-striped table-sm">
                                                        <thead>
                                                            <tr>
                                                                <th><?php echo Text::_('COM_JOOMLAHITS_LANGUAGE'); ?></th>
                                                                <th class="text-center"><?php echo Text::_('COM_JOOMLAHITS_ARTICLES'); ?></th>
                                                                <th class="text-center"><?php echo Text::_('COM_JOOMLAHITS_TOTAL_VIEWS'); ?></th>
                                                                <th class="text-center"><?php echo Text::_('COM_JOOMLAHITS_AVG_VIEWS'); ?></th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($this->languageStats as $language): ?>
                                                                <tr>
                                                                    <td><?php echo $this->escape($language->language_name); ?></td>
                                                                    <td class="text-center"><?php echo $language->article_count; ?></td>
                                                                    <td class="text-center">
                                                                        <span class="badge bg-secondary"><?php echo number_format($language->total_hits ?? 0); ?></span>
                                                                    </td>
                                                                    <td class="text-center"><?php echo number_format($language->average_hits ?? 0, 1); ?></td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-muted"><?php echo Text::_('COM_JOOMLAHITS_NO_LANGUAGE_STATS'); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
/* CSS to improve responsive design for language containers */

/* Optimization of language statistics cards */

.chart-container {
    position: relative;
    margin: 20px 0;
    padding: 10px;
    min-height: 350px;
}

#languageStatsChart {
    max-width: 300px;
    max-height: 300px;
    margin: 0 auto;
}

#categoryStatsChart {
    height: 350px !important;
}

.chart-container canvas {
    border-radius: 8px;
}

/* Improvement of mobile display for articles by language */
@media (max-width: 767.98px) {
    .stats-card .card-header h5 {
        font-size: 0.95rem;
    }
    
    .stats-card .card-body {
        padding: 0.75rem !important;
    }
    
    /* Optimization of list display for mobile */
    .stats-card .table td {
        border: none;
        padding: 0.5rem 0;
    }
    
    .stats-card .table tbody tr {
        border-bottom: 1px solid rgba(255,255,255,0.1);
    }
    
    .stats-card .table tbody tr:last-child {
        border-bottom: none;
    }
    
    /* Style for ranking badges on mobile */
    .badge {
        font-size: 0.7rem;
    }
    
    /* Improvement of title truncation */
    .text-truncate {
        max-width: 100%;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    /* Style for "Show more" buttons */
    .toggle-more-articles {
        font-size: 0.8rem;
        padding: 0.25rem 0.75rem;
        border-radius: 15px;
    }
}

/* Optimization for tablets */
@media (min-width: 768px) and (max-width: 991.98px) {
    .stats-card .card-header h5 {
        font-size: 1rem;
    }
    
    /* Reducing the width of view columns */
    .stats-card .table th:last-child,
    .stats-card .table td:last-child {
        width: 80px;
        text-align: center;
    }
}

/* Optimization for large screens */
@media (min-width: 1200px) {
    /* When there are 4 columns, optimize spacing */
    .col-xl-3 .stats-card .card-header h5 {
        font-size: 0.9rem;
    }
    
    .col-xl-3 .stats-card .table {
        font-size: 0.85rem;
    }
    
    /* Improvement of truncation for small columns */
    .col-xl-3 .table td:first-child {
        max-width: 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
}

/* Optimization for very large screens */
@media (min-width: 1400px) {
    .col-xl-3 .stats-card .card-header h5 {
        font-size: 1rem;
    }
    
    .col-xl-3 .stats-card .table {
        font-size: 0.875rem;
    }
}

/* Hover effect for stats cards */
.stats-card {
    transition: all 0.3s ease;
}

.stats-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    border-color: rgba(13, 110, 253, 0.5);
}

/* Style for article links */
.stats-card .table a {
    color: inherit;
    text-decoration: none;
    transition: color 0.2s ease;
}

.stats-card .table a:hover {
    color: #0d6efd;
    text-decoration: underline;
}

/* Accessibility improvement */
.stats-card .table th,
.stats-card .table td {
    vertical-align: middle;
}

/* Style for language indicators */
.stats-card .card-header span:first-child {
    font-size: 1.2em;
    display: inline-block;
    margin-right: 0.5rem;
}

/* Custom responsive breakpoints for this section */
@media (max-width: 575.98px) {
    /* Very small screens - 1 column */
    .stats-card .card-header {
        padding: 0.75rem 1rem;
    }
    
    .stats-card .card-header h5 {
        font-size: 0.9rem;
        line-height: 1.3;
    }
}

@media (min-width: 576px) and (max-width: 767.98px) {
    /* Small screens - always 1 column but more space */
    .stats-card .card-header h5 {
        font-size: 1rem;
    }
}

/* Contrast improvement for accessibility */
@media (prefers-contrast: high) {
    .stats-card {
        border: 2px solid #333;
    }
    
    .badge {
        border: 1px solid currentColor;
    }
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
    
    .stats-card .table tbody tr {
        border-bottom-color: rgba(255,255,255,0.1);
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // JavaScript for the "Show more/less" toggle
    document.querySelectorAll('.toggle-more-articles').forEach(function(button) {
        button.addEventListener('click', function() {
            const card = this.closest('.card');
            const language = this.getAttribute('data-language');
            const allRows = card.querySelectorAll('tr[data-language="' + language + '"]');
            const showMoreSpan = this.querySelector('.show-more');
            const showLessSpan = this.querySelector('.show-less');
            
            if (showMoreSpan.classList.contains('d-none')) {
                // Hide additional rows (show only the first 5)
                allRows.forEach((row, index) => {
                    if (index >= 5) {
                        row.classList.add('d-none');
                        row.classList.remove('d-lg-table-row');
                    }
                });
                showMoreSpan.classList.remove('d-none');
                showLessSpan.classList.add('d-none');
            } else {
                // Show all rows
                allRows.forEach((row, index) => {
                    if (index >= 5) {
                        row.classList.remove('d-none');
                        row.classList.add('d-lg-table-row');
                    }
                });
                showMoreSpan.classList.add('d-none');
                showLessSpan.classList.remove('d-none');
            }
        });
    });
    
    // Pie chart for language statistics
    <?php if (!empty($this->languageStats)): ?>
    const languageCtx = document.getElementById('languageStatsChart');
    if (languageCtx) {
        const languageData = {
            labels: [
                <?php foreach ($this->languageStats as $language): ?>
                    '<?php echo addslashes($language->language_name); ?>',
                <?php endforeach; ?>
            ],
            datasets: [{
                data: [
                    <?php foreach ($this->languageStats as $language): ?>
                        <?php echo $language->total_hits; ?>,
                    <?php endforeach; ?>
                ],
                backgroundColor: [
                    '#FF6384',
                    '#36A2EB',
                    '#FFCE56',
                    '#4BC0C0',
                    '#9966FF',
                    '#FF9F40'
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        };

        new Chart(languageCtx, {
            type: 'pie',
            data: languageData,
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((value / total) * 100).toFixed(1);
                                return label + ': ' + value.toLocaleString() + ' views (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
    }
    <?php endif; ?>

    // Line chart for categories
    <?php if (!empty($this->enhancedCategoryStats)): ?>
    const categoryCtx = document.getElementById('categoryStatsChart');
    if (categoryCtx) {
        const categoryData = {
            labels: [
                <?php foreach ($this->enhancedCategoryStats as $category): ?>
                    '<?php echo addslashes($category->category_name); ?>',
                <?php endforeach; ?>
            ],
            datasets: [{
                label: 'Total Views',
                data: [
                    <?php foreach ($this->enhancedCategoryStats as $category): ?>
                        <?php echo $category->total_hits; ?>,
                    <?php endforeach; ?>
                ],
                borderColor: '#36A2EB',
                backgroundColor: 'rgba(54, 162, 235, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#36A2EB',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 6
            }, {
                label: 'Articles Count',
                data: [
                    <?php foreach ($this->enhancedCategoryStats as $category): ?>
                        <?php echo $category->article_count; ?>,
                    <?php endforeach; ?>
                ],
                borderColor: '#FF6384',
                backgroundColor: 'rgba(255, 99, 132, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#FF6384',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 6,
                yAxisID: 'y1'
            }]
        };

        new Chart(categoryCtx, {
            type: 'line',
            data: categoryData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 20
                        }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                    }
                },
                scales: {
                    x: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Categories'
                        },
                        ticks: {
                            maxRotation: 45,
                            minRotation: 0
                        }
                    },
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Number of Views'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Number of Articles'
                        },
                        grid: {
                            drawOnChartArea: false,
                        }
                    }
                }
            }
        });
    }
    <?php endif; ?>
    
    // Selector Year Change Event
    const yearSelector = document.getElementById('yearSelector');
    const yearForm = document.getElementById('yearSelectorForm');
    
    if (yearSelector && yearForm) {
        yearSelector.addEventListener('change', function(event) {
            event.preventDefault();
            const urlParams = new URLSearchParams(window.location.search);
            urlParams.set('year', this.value);
            const newUrl = window.location.pathname + '?' + urlParams.toString();
            window.history.pushState({}, '', newUrl);          
            window.location.reload();
        });
    }
});
</script>