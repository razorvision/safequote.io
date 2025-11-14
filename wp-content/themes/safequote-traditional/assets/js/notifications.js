/**
 * Notifications Module
 * Handles all notification and alert displays
 */

(function() {
    'use strict';

    const notificationQueue = [];
    let isProcessing = false;
    let container = null;

    const defaultOptions = {
        duration: 5000,
        position: 'top-right',
        animation: 'slide',
        closeable: true,
        progressBar: true
    };

    /**
     * Initialize notifications system
     */
    function init() {
        createContainer();
        setupStyles();
    }

    /**
     * Create notification container
     */
    function createContainer() {
        container = document.getElementById('notification-container');

        if (!container) {
            container = document.createElement('div');
            container.id = 'notification-container';
            container.className = 'fixed z-50 pointer-events-none';
            setPosition('top-right');
            document.body.appendChild(container);
        }
    }

    /**
     * Set container position
     */
    function setPosition(position) {
        if (!container) return;

        const positions = {
            'top-left': 'top-4 left-4',
            'top-center': 'top-4 left-1/2 transform -translate-x-1/2',
            'top-right': 'top-4 right-4',
            'bottom-left': 'bottom-4 left-4',
            'bottom-center': 'bottom-4 left-1/2 transform -translate-x-1/2',
            'bottom-right': 'bottom-4 right-4'
        };

        container.className = `fixed z-50 pointer-events-none ${positions[position] || positions['top-right']}`;
    }

    /**
     * Setup notification styles
     */
    function setupStyles() {
        if (document.getElementById('notification-styles')) return;

        const styles = `
            .notification {
                pointer-events: auto;
                min-width: 300px;
                max-width: 500px;
                margin-bottom: 1rem;
                border-radius: 0.5rem;
                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
                overflow: hidden;
                transition: all 0.3s ease;
            }

            .notification-content {
                padding: 1rem;
                display: flex;
                align-items: flex-start;
            }

            .notification-icon {
                flex-shrink: 0;
                width: 24px;
                height: 24px;
                margin-right: 0.75rem;
            }

            .notification-body {
                flex: 1;
            }

            .notification-title {
                font-weight: 600;
                margin-bottom: 0.25rem;
            }

            .notification-message {
                font-size: 0.875rem;
                line-height: 1.25rem;
            }

            .notification-close {
                flex-shrink: 0;
                margin-left: 0.75rem;
                cursor: pointer;
                opacity: 0.7;
                transition: opacity 0.2s;
            }

            .notification-close:hover {
                opacity: 1;
            }

            .notification-progress {
                height: 3px;
                background: rgba(0, 0, 0, 0.1);
                position: relative;
                overflow: hidden;
            }

            .notification-progress-bar {
                height: 100%;
                background: currentColor;
                transition: width linear;
            }

            /* Type-specific styles */
            .notification-success {
                background: #10b981;
                color: white;
            }

            .notification-error {
                background: #ef4444;
                color: white;
            }

            .notification-warning {
                background: #f59e0b;
                color: white;
            }

            .notification-info {
                background: #3b82f6;
                color: white;
            }

            /* Animation styles */
            @keyframes slideIn {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }

            @keyframes slideOut {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }
                to {
                    transform: translateX(100%);
                    opacity: 0;
                }
            }

            @keyframes fadeIn {
                from {
                    opacity: 0;
                }
                to {
                    opacity: 1;
                }
            }

            @keyframes fadeOut {
                from {
                    opacity: 1;
                }
                to {
                    opacity: 0;
                }
            }

            .notification.slide-in {
                animation: slideIn 0.3s ease-out;
            }

            .notification.slide-out {
                animation: slideOut 0.3s ease-out;
            }

            .notification.fade-in {
                animation: fadeIn 0.3s ease-out;
            }

            .notification.fade-out {
                animation: fadeOut 0.3s ease-out;
            }
        `;

        const styleElement = document.createElement('style');
        styleElement.id = 'notification-styles';
        styleElement.textContent = styles;
        document.head.appendChild(styleElement);
    }

    /**
     * Show notification
     */
    function show(message, type = 'info', options = {}) {
        const notification = {
            message: message,
            type: type,
            options: { ...defaultOptions, ...options },
            id: Date.now()
        };

        notificationQueue.push(notification);
        processQueue();

        return notification.id;
    }

    /**
     * Process notification queue
     */
    function processQueue() {
        if (isProcessing || notificationQueue.length === 0) return;

        isProcessing = true;
        const notification = notificationQueue.shift();
        displayNotification(notification);

        setTimeout(() => {
            isProcessing = false;
            processQueue();
        }, 100);
    }

    /**
     * Display a notification
     */
    function displayNotification(notification) {
        const element = createNotificationElement(notification);
        container.appendChild(element);

        // Trigger animation
        requestAnimationFrame(() => {
            element.classList.add(`${notification.options.animation}-in`);
        });

        // Auto-dismiss if duration is set
        if (notification.options.duration > 0) {
            const progressBar = element.querySelector('.notification-progress-bar');
            if (progressBar) {
                progressBar.style.transition = `width ${notification.options.duration}ms linear`;
                progressBar.style.width = '0%';
            }

            setTimeout(() => {
                dismiss(element);
            }, notification.options.duration);
        }

        return element;
    }

    /**
     * Create notification element
     */
    function createNotificationElement(notification) {
        const element = document.createElement('div');
        element.className = `notification notification-${notification.type}`;
        element.dataset.notificationId = notification.id;

        const icon = getIcon(notification.type);
        const title = getTitle(notification.type);

        let html = `
            <div class="notification-content">
                <div class="notification-icon">
                    ${icon}
                </div>
                <div class="notification-body">
                    ${title ? `<div class="notification-title">${title}</div>` : ''}
                    <div class="notification-message">${notification.message}</div>
                </div>
                ${notification.options.closeable ? `
                <div class="notification-close" onclick="window.SafeQuoteNotifications.close(${notification.id})">
                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </div>
                ` : ''}
            </div>
            ${notification.options.progressBar ? `
            <div class="notification-progress">
                <div class="notification-progress-bar" style="width: 100%;"></div>
            </div>
            ` : ''}
        `;

        element.innerHTML = html;

        return element;
    }

    /**
     * Get icon for notification type
     */
    function getIcon(type) {
        const icons = {
            success: `<svg fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>`,
            error: `<svg fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>`,
            warning: `<svg fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>`,
            info: `<svg fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
            </svg>`
        };

        return icons[type] || icons.info;
    }

    /**
     * Get title for notification type
     */
    function getTitle(type) {
        const titles = {
            success: 'Success',
            error: 'Error',
            warning: 'Warning',
            info: 'Information'
        };

        return titles[type] || '';
    }

    /**
     * Dismiss notification
     */
    function dismiss(element) {
        element.classList.remove(`${element.dataset.animation || 'slide'}-in`);
        element.classList.add(`${element.dataset.animation || 'slide'}-out`);

        setTimeout(() => {
            element.remove();
        }, 300);
    }

    /**
     * Close specific notification
     */
    function close(id) {
        const element = container.querySelector(`[data-notification-id="${id}"]`);
        if (element) {
            dismiss(element);
        }
    }

    /**
     * Clear all notifications
     */
    function clearAll() {
        const notifications = container.querySelectorAll('.notification');
        notifications.forEach(notification => dismiss(notification));
        notificationQueue.length = 0;
    }

    /**
     * Success notification shorthand
     */
    function success(message, options) {
        return show(message, 'success', options);
    }

    /**
     * Error notification shorthand
     */
    function error(message, options) {
        return show(message, 'error', options);
    }

    /**
     * Warning notification shorthand
     */
    function warning(message, options) {
        return show(message, 'warning', options);
    }

    /**
     * Info notification shorthand
     */
    function info(message, options) {
        return show(message, 'info', options);
    }

    /**
     * Show confirmation dialog
     */
    function confirm(message, onConfirm, onCancel) {
        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 z-50 flex items-center justify-center';
        modal.innerHTML = `
            <div class="fixed inset-0 bg-black bg-opacity-50"></div>
            <div class="relative bg-white rounded-lg p-6 max-w-md w-full mx-4">
                <h3 class="text-lg font-semibold mb-4">Confirm</h3>
                <p class="text-gray-600 mb-6">${message}</p>
                <div class="flex justify-end space-x-4">
                    <button id="confirm-cancel" class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">
                        Cancel
                    </button>
                    <button id="confirm-yes" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                        Confirm
                    </button>
                </div>
            </div>
        `;

        document.body.appendChild(modal);

        document.getElementById('confirm-cancel').addEventListener('click', () => {
            modal.remove();
            if (onCancel) onCancel();
        });

        document.getElementById('confirm-yes').addEventListener('click', () => {
            modal.remove();
            if (onConfirm) onConfirm();
        });
    }

    /**
     * Show prompt dialog
     */
    function prompt(message, defaultValue = '', onSubmit, onCancel) {
        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 z-50 flex items-center justify-center';
        modal.innerHTML = `
            <div class="fixed inset-0 bg-black bg-opacity-50"></div>
            <div class="relative bg-white rounded-lg p-6 max-w-md w-full mx-4">
                <h3 class="text-lg font-semibold mb-4">Input Required</h3>
                <p class="text-gray-600 mb-4">${message}</p>
                <input id="prompt-input" type="text" value="${defaultValue}"
                       class="w-full px-3 py-2 border rounded focus:ring-2 focus:ring-blue-500 mb-6">
                <div class="flex justify-end space-x-4">
                    <button id="prompt-cancel" class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">
                        Cancel
                    </button>
                    <button id="prompt-submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                        Submit
                    </button>
                </div>
            </div>
        `;

        document.body.appendChild(modal);

        const input = document.getElementById('prompt-input');
        input.focus();
        input.select();

        document.getElementById('prompt-cancel').addEventListener('click', () => {
            modal.remove();
            if (onCancel) onCancel();
        });

        document.getElementById('prompt-submit').addEventListener('click', () => {
            const value = input.value;
            modal.remove();
            if (onSubmit) onSubmit(value);
        });

        input.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                document.getElementById('prompt-submit').click();
            }
        });
    }

    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Expose API
    window.SafeQuoteNotifications = {
        init: init,
        show: show,
        close: close,
        clearAll: clearAll,
        success: success,
        error: error,
        warning: warning,
        info: info,
        confirm: confirm,
        prompt: prompt,
        setPosition: setPosition
    };

})();