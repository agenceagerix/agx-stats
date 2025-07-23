<?php
/*-----------------------------------------------------------------------------------------------------/
	@version		1.1.0
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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('bootstrap.collapse');
?>

<div class="row">
    <div class="col-md-12">
        <div id="j-main-container" class="j-main-container">

            <!-- Navigation Tabs -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <nav>
                        <div class="nav nav-tabs" id="nav-tab" role="tablist">
                            <button class="nav-link active" id="nav-dashboard-tab" data-bs-toggle="tab" data-bs-target="#nav-dashboard" type="button" role="tab" aria-controls="nav-dashboard" aria-selected="true">
                                <i class="icon-dashboard"></i> Dashboard
                            </button>
                            <button class="nav-link" id="nav-articles-tab" data-bs-toggle="tab" data-bs-target="#nav-articles" type="button" role="tab" aria-controls="nav-articles" aria-selected="false" onclick="window.location.href='<?php echo Route::_('index.php?option=com_joomlahits&view=cpanel'); ?>'">
                                <i class="icon-list"></i> Articles List
                            </button>
                        </div>
                    </nav>
                </div>
            </div>

            <!-- Dashboard Content -->
            <div class="tab-content" id="nav-tabContent">
                <div class="tab-pane fade show active" id="nav-dashboard" role="tabpanel" aria-labelledby="nav-dashboard-tab">

                    <!-- Overview Statistics -->
                    <?php if ($this->dashboardStats): ?>
                    <div class="row mb-4">
                        <div class="col-md-2">
                            <div class="card text-center stats-card">
                                <div class="card-body">
                                    <h5 class="card-title">Total Articles</h5>
                                    <p class="card-text display-4 text-primary"><?php echo $this->dashboardStats->total_articles; ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card text-center stats-card">
                                <div class="card-body">
                                    <h5 class="card-title">Published</h5>
                                    <p class="card-text display-4 text-success"><?php echo $this->dashboardStats->published_articles; ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card text-center stats-card">
                                <div class="card-body">
                                    <h5 class="card-title">Total Views</h5>
                                    <p class="card-text display-4 text-info"><?php echo number_format($this->dashboardStats->total_hits); ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card text-center stats-card">
                                <div class="card-body">
                                    <h5 class="card-title">Average Views</h5>
                                    <p class="card-text display-4 text-warning"><?php echo number_format($this->dashboardStats->average_hits, 1); ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card text-center stats-card">
                                <div class="card-body">
                                    <h5 class="card-title">Max Views</h5>
                                    <p class="card-text display-4 text-danger"><?php echo number_format($this->dashboardStats->max_hits); ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card text-center stats-card">
                                <div class="card-body">
                                    <h5 class="card-title">With Views</h5>
                                    <p class="card-text display-4 text-secondary"><?php echo $this->dashboardStats->articles_with_hits; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Articles without clicks rate -->
                    <?php if ($this->articlesWithoutClicks): ?>
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card stats-card">
                                <div class="card-header">
                                    <h5>📊 Articles without clicks rate</h5>
                                </div>
                                <div class="card-body">
                                    <h3 class="text-danger"><?php echo $this->articlesWithoutClicks->no_clicks_percentage; ?>%</h3>
                                    <p class="mb-2"><?php echo $this->articlesWithoutClicks->articles_without_clicks; ?> articles out of <?php echo $this->articlesWithoutClicks->total_articles; ?></p>
                                    <div class="progress">
                                        <div class="progress-bar bg-danger" role="progressbar" style="width: <?php echo $this->articlesWithoutClicks->no_clicks_percentage; ?>%" aria-valuenow="<?php echo $this->articlesWithoutClicks->no_clicks_percentage; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <!-- Period comparison -->
                            <?php if ($this->periodComparison): ?>
                            <div class="card stats-card">
                                <div class="card-header">
                                    <h5>📈 Monthly comparison (this month vs last month)</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <h6>Articles created</h6>
                                            <h4><?php echo $this->periodComparison->current_period->articles_created; ?></h4>
                                            <small class="<?php echo $this->periodComparison->articles_change >= 0 ? 'text-success' : 'text-danger'; ?>">
                                                <?php echo $this->periodComparison->articles_change >= 0 ? '+' : ''; ?><?php echo $this->periodComparison->articles_change; ?> 
                                                (<?php echo $this->periodComparison->articles_change_percent; ?>%)
                                            </small>
                                        </div>
                                        <div class="col-md-4">
                                            <h6>Total views</h6>
                                            <h4><?php echo number_format($this->periodComparison->current_period->total_hits); ?></h4>
                                            <small class="<?php echo $this->periodComparison->hits_change >= 0 ? 'text-success' : 'text-danger'; ?>">
                                                <?php echo $this->periodComparison->hits_change >= 0 ? '+' : ''; ?><?php echo number_format($this->periodComparison->hits_change); ?> 
                                                (<?php echo $this->periodComparison->hits_change_percent; ?>%)
                                            </small>
                                        </div>
                                        <div class="col-md-4">
                                            <h6>Previous month</h6>
                                            <p>Articles: <?php echo $this->periodComparison->previous_period->articles_created; ?></p>
                                            <p>Views: <?php echo number_format($this->periodComparison->previous_period->total_hits); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Dynamic Top Articles by Language -->
                    <?php if ($this->params->get('show_language_stats', 1) && !empty($this->availableLanguages) && !empty($this->topArticlesByLanguage)): ?>
                    <div class="row mb-4">
                        <?php 
                        $displayedLanguages = 0;
                        foreach ($this->availableLanguages as $language): 
                            if ($displayedLanguages >= 4) break; // Limit to 4 languages
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
                        <div class="col-md-6">
                            <div class="card stats-card">
                                <div class="card-header">
                                    <h5><?php echo $languageIcon; ?> Top 10 <?php echo $this->escape($languageTitle); ?> Articles</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Title</th>
                                                    <th>Category</th>
                                                    <th class="text-center">Views</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($this->topArticlesByLanguage[$language->language] as $article): ?>
                                                    <tr>
                                                        <td>
                                                            <a href="<?php echo Route::_('index.php?option=com_content&task=article.edit&id=' . $article->id); ?>" title="Edit Article">
                                                                <?php echo $this->escape($article->title); ?>
                                                            </a>
                                                        </td>
                                                        <td><?php echo $this->escape($article->category_title); ?></td>
                                                        <td class="text-center">
                                                            <span class="badge bg-info"><?php echo number_format($article->hits); ?></span>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
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
                        <div class="col-md-12">
                            <div class="alert alert-info">
                                <span class="icon-info-circle" aria-hidden="true"></span>
                                <span>No language-specific articles available to display.</span>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Enhanced Category Statistics -->
                    <?php if ($this->params->get('show_category_stats', 1)): ?>
                    <div class="col mb-6">
                        <div class="col-md-8">
                            <div class="card stats-card">
                                <div class="card-header">
                                    <h5>📊 Category ranking by activity</h5>
                                </div>
                                <div class="card-body">
                                    <?php if (!empty($this->enhancedCategoryStats)): ?>
                                        <div class="table-responsive">
                                            <table class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Rank</th>
                                                        <th>Category</th>
                                                        <th class="text-center">Articles</th>
                                                        <th class="text-center">Total Views</th>
                                                        <th class="text-center">Avg Views</th>
                                                        <th class="text-center">% of Total</th>
                                                        <th class="text-center">Performance</th>
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
                                                                <span class="badge bg-info"><?php echo number_format($category->total_hits); ?></span>
                                                            </td>
                                                            <td class="text-center"><?php echo number_format($category->average_hits, 1); ?></td>
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
                                        <p class="text-muted">No category statistics available.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <!-- Top Articles by Category -->
                    <?php if (!empty($this->topArticlesByCategory)): ?>
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card stats-card">
                                <div class="card-header">
                                    <h5>🏆 Top articles by category</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <?php 
                                        $categoryCount = 0;
                                        foreach ($this->enhancedCategoryStats as $category): 
                                            if ($categoryCount >= 4) break; // Limit to 4 categories for display
                                            if (!empty($this->topArticlesByCategory[$category->category_id])):
                                        ?>
                                            <div class="col-md-3">
                                                <h6 class="border-bottom pb-2"><?php echo $this->escape($category->category_name); ?></h6>
                                                <?php foreach ($this->topArticlesByCategory[$category->category_id] as $article): ?>
                                                    <div class="mb-2">
                                                        <a href="<?php echo Route::_('index.php?option=com_content&task=article.edit&id=' . $article->id); ?>" class="text-decoration-none" title="Edit Article">
                                                            <small><?php echo $this->escape(strlen($article->title) > 30 ? substr($article->title, 0, 30) . '...' : $article->title); ?></small>
                                                        </a>
                                                        <br><span class="badge bg-secondary"><?php echo number_format($article->hits); ?> views</span>
                                                    </div>
                                                <?php endforeach; ?>
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
                    <?php endif; ?>

                    <div class="row">
                        <!-- Recent Activity -->
                        <div class="col-md-6">
                            <div class="card stats-card">
                                <div class="card-header">
                                    <h5>🕒 Recent activity (last 30 days)</h5>
                                </div>
                                <div class="card-body">
                                    <?php if (!empty($this->recentActivity)): ?>
                                        <div class="table-responsive">
                                            <table class="table table-striped table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Title</th>
                                                        <th>Created on</th>
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
                                                                <span class="badge bg-secondary"><?php echo number_format($article->hits); ?></span>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-muted">No recent articles found.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Language Statistics -->
                        <?php if ($this->params->get('show_language_stats', 1)): ?>
                        <div class="col-md-6">
                            <div class="card stats-card">
                                <div class="card-header">
                                    <h5>🌍 Statistics by language</h5>
                                </div>
                                <div class="card-body">
                                    <?php if (!empty($this->languageStats)): ?>
                                        <div class="table-responsive">
                                            <table class="table table-striped table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Language</th>
                                                        <th class="text-center">Articles</th>
                                                        <th class="text-center">Total Views</th>
                                                        <th class="text-center">Avg Views</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($this->languageStats as $language): ?>
                                                        <tr>
                                                            <td><?php echo $this->escape($language->language_name); ?></td>
                                                            <td class="text-center"><?php echo $language->article_count; ?></td>
                                                            <td class="text-center">
                                                                <span class="badge bg-secondary"><?php echo number_format($language->total_hits); ?></span>
                                                            </td>
                                                            <td class="text-center"><?php echo number_format($language->average_hits, 1); ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-muted">No language statistics available.</p>
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