<?php
/*-----------------------------------------------------------------------------------------------------/
	@version		1.1.0
	@build			28th July, 2025
	@created		28th July, 2025
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
                                <i class="icon-search"></i> <?php echo Text::_('COM_JOOMLAHITS_CHECKSEO_PAGE_TITLE'); ?>
                            </h2>
                        </div>
                        <div class="card-body">
                            <p class="card-text"><?php echo Text::_('COM_JOOMLAHITS_CHECKSEO_PAGE_DESCRIPTION'); ?></p>
                            <a href="<?php echo Route::_('index.php?option=com_joomlahits'); ?>" class="btn btn-secondary">
                                <i class="icon-arrow-left"></i> <?php echo Text::_('COM_JOOMLAHITS_BACK_TO_CONTROL_PANEL'); ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SEO Check Content -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">
                                <i class="icon-search"></i> <?php echo Text::_('COM_JOOMLAHITS_CHECKSEO_ANALYSIS_TITLE'); ?>
                            </h5>
                        </div>
                        <div class="card-body">
                            
                            <!-- SEO Check Button -->
                            <div class="text-center">
                                <button type="button" class="btn btn-primary btn-lg" id="seoCheckButton">
                                    <i class="icon-search"></i> <?php echo Text::_('COM_JOOMLAHITS_CHECKSEO_START_ANALYSIS'); ?>
                                </button>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const seoCheckButton = document.getElementById('seoCheckButton');
    
    if (seoCheckButton) {
        seoCheckButton.addEventListener('click', function() {
            // Simple button interaction for now
            this.innerHTML = '<i class="icon-loading"></i> <?php echo Text::_('COM_JOOMLAHITS_CHECKSEO_ANALYZING'); ?>...';
            this.disabled = true;
            
            // Simulate analysis
            setTimeout(() => {
                this.innerHTML = '<i class="icon-checkmark"></i> <?php echo Text::_('COM_JOOMLAHITS_CHECKSEO_ANALYSIS_COMPLETE'); ?>';
                this.classList.remove('btn-primary');
                this.classList.add('btn-success');
            }, 2000);
        });
    }
});
</script>