/*-----------------------------------------------------------------------------------------------------/
	@version		1.2.0
	@build			29th July, 2025
	@created		29th July, 2025
	@package		JoomlaHits
	@subpackage		notifications.js
	@author			Hugo Dantas - Agence Agerix <https://www.agerix.fr>
	@copyright		Copyright (C) 2025. All Rights Reserved
	@license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html
/------------------------------------------------------------------------------------------------------*/

/**
 * Simple notification system using Joomla
 * @param {string} message - The message to display
 * @param {string} type - The type of notification (info, success, error, warning)
 */
function showNotification(message, type) {
    // Create notification container if it doesn't exist
    var container = document.getElementById('joomla-notification-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'joomla-notification-container';
        container.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; max-width: 400px;';
        document.body.appendChild(container);
    }
    
    // Create notification
    var notification = document.createElement('div');
    var alertClass = 'alert-info';
    var iconClass = 'icon-info';
    
    if (type === 'success') {
        alertClass = 'alert-success';
        iconClass = 'icon-checkmark';
    } else if (type === 'error') {
        alertClass = 'alert-danger';
        iconClass = 'icon-warning';
    } else if (type === 'warning') {
        alertClass = 'alert-warning';
        iconClass = 'icon-warning';
    }
    
    notification.className = 'alert ' + alertClass + ' alert-dismissible';
    notification.style.cssText = 'margin-bottom: 10px; animation: fadeInRight 0.5s ease;';
    notification.innerHTML = '<i class="' + iconClass + '"></i> ' + message + 
                           '<button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>';
    
    container.appendChild(notification);
    
    // Auto-remove after 5 seconds
    setTimeout(function() {
        if (notification.parentNode) {
            notification.style.animation = 'fadeOutRight 0.5s ease';
            setTimeout(function() {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 500);
        }
    }, 5000);
}