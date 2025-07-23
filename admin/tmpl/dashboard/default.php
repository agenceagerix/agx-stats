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
                        <div class="col-md-12">
                            <h3>Overview Statistics</h3>
                        </div>
                        <div class="col-md-2">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h5 class="card-title">Total Articles</h5>
                                    <p class="card-text display-4 text-primary"><?php echo $this->dashboardStats->total_articles; ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h5 class="card-title">Published</h5>
                                    <p class="card-text display-4 text-success"><?php echo $this->dashboardStats->published_articles; ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h5 class="card-title">Total Views</h5>
                                    <p class="card-text display-4 text-info"><?php echo number_format($this->dashboardStats->total_hits); ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h5 class="card-title">Average Views</h5>
                                    <p class="card-text display-4 text-warning"><?php echo number_format($this->dashboardStats->average_hits, 1); ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h5 class="card-title">Max Views</h5>
                                    <p class="card-text display-4 text-danger"><?php echo number_format($this->dashboardStats->max_hits); ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h5 class="card-title">With Views</h5>
                                    <p class="card-text display-4 text-secondary"><?php echo $this->dashboardStats->articles_with_hits; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="row">
                        <!-- Top Articles -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h4>Top 10 Articles</h4>
                                </div>
                                <div class="card-body">
                                    <?php if (!empty($this->topArticles)): ?>
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
                                                    <?php foreach ($this->topArticles as $article): ?>
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
                                    <?php else: ?>
                                        <p class="text-muted">No articles found.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Activity -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h4>Recent Activity (Last 30 days)</h4>
                                </div>
                                <div class="card-body">
                                    <?php if (!empty($this->recentActivity)): ?>
                                        <div class="table-responsive">
                                            <table class="table table-striped table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Title</th>
                                                        <th>Created</th>
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
                    </div>

                    <div class="row mt-4">
                        <!-- Category Statistics -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h4>Statistics by Category</h4>
                                </div>
                                <div class="card-body">
                                    <?php if (!empty($this->categoryStats)): ?>
                                        <div class="table-responsive">
                                            <table class="table table-striped table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Category</th>
                                                        <th class="text-center">Articles</th>
                                                        <th class="text-center">Total Views</th>
                                                        <th class="text-center">Avg Views</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($this->categoryStats as $category): ?>
                                                        <tr>
                                                            <td><?php echo $this->escape($category->category_name); ?></td>
                                                            <td class="text-center"><?php echo $category->article_count; ?></td>
                                                            <td class="text-center">
                                                                <span class="badge bg-info"><?php echo number_format($category->total_hits); ?></span>
                                                            </td>
                                                            <td class="text-center"><?php echo number_format($category->average_hits, 1); ?></td>
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

                        <!-- Language Statistics -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h4>Statistics by Language</h4>
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
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>