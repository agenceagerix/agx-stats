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

use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
?>

<div class="row">
    <div class="col-md-12">
        <div id="j-main-container" class="j-main-container">
            
            <!-- Header -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">
                                <i class="icon-dashboard"></i> <?php echo Text::_('COM_JOOMLAHITS_CONTROL_PANEL'); ?>
                            </h2>
                        </div>
                        <div class="card-body">
                            <p class="card-text"><?php echo Text::_('COM_JOOMLAHITS_CONTROL_PANEL_DESC'); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Navigation Buttons -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="icon-list" style="font-size: 48px; color: #007cba;"></i>
                            </div>
                            <h4 class="card-title"><?php echo Text::_('COM_JOOMLAHITS_ARTICLES_LIST_TITLE'); ?></h4>
                            <p class="card-text"><?php echo Text::_('COM_JOOMLAHITS_ARTICLES_LIST_DESC'); ?></p>
                            <a href="<?php echo Route::_('index.php?option=com_joomlahits&view=articles'); ?>" class="btn btn-primary btn-lg">
                                <i class="icon-list"></i> <?php echo Text::_('COM_JOOMLAHITS_GO_TO_ARTICLES_LIST'); ?>
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="icon-dashboard" style="font-size: 48px; color: #28a745;"></i>
                            </div>
                            <h4 class="card-title"><?php echo Text::_('COM_JOOMLAHITS_DASHBOARD_TITLE'); ?></h4>
                            <p class="card-text"><?php echo Text::_('COM_JOOMLAHITS_DASHBOARD_DESC'); ?></p>
                            <a href="<?php echo Route::_('index.php?option=com_joomlahits&view=dashboard'); ?>" class="btn btn-success btn-lg">
                                <i class="icon-dashboard"></i> <?php echo Text::_('COM_JOOMLAHITS_GO_TO_DASHBOARD'); ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>