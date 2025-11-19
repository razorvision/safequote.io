/**
 * Modal Management Module
 * Handles all modal dialogs in the theme
 */

(function() {
    'use strict';

    const modals = {};
    let activeModal = null;
    let modalStack = [];

    /**
     * Initialize modal system
     */
    function init() {
        // Register existing modals
        registerExistingModals();

        // Bind global events
        bindGlobalEvents();

        // Initialize specific modals
        initVehicleDetailsModal();
        initComparisonModal();
        initInsuranceQuoteModal();
    }

    /**
     * Register all existing modals in the DOM
     */
    function registerExistingModals() {
        const modalElements = document.querySelectorAll('[data-modal]');

        modalElements.forEach(element => {
            const modalId = element.dataset.modal;
            register(modalId, element);
        });
    }

    /**
     * Bind global events for modal management
     */
    function bindGlobalEvents() {
        // Close modals on ESC key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape' && activeModal) {
                close(activeModal.id);
            }
        });

        // Handle modal triggers
        document.addEventListener('click', function(event) {
            const trigger = event.target.closest('[data-modal-trigger]');
            if (trigger) {
                event.preventDefault();
                const modalId = trigger.dataset.modalTrigger;
                open(modalId);
            }

            // Handle close buttons
            const closeBtn = event.target.closest('[data-modal-close]');
            if (closeBtn) {
                event.preventDefault();
                const modal = closeBtn.closest('[data-modal]');
                if (modal) {
                    close(modal.dataset.modal);
                }
            }

            // Handle backdrop clicks
            if (event.target.classList.contains('modal-backdrop')) {
                const modal = document.querySelector('.modal-content');
                if (modal && !modal.contains(event.target)) {
                    close(activeModal.id);
                }
            }
        });
    }

    /**
     * Register a modal
     */
    function register(id, element) {
        modals[id] = {
            id: id,
            element: element,
            onOpen: null,
            onClose: null
        };

        return modals[id];
    }

    /**
     * Create a new modal dynamically
     */
    function create(options) {
        const modalId = options.id || 'modal-' + Date.now();
        const headerId = `${modalId}-header`;

        // Create modal HTML with ARIA attributes
        const modalHtml = `
            <div data-modal="${modalId}" class="modal hidden fixed inset-0 z-50" role="dialog" aria-modal="true" aria-labelledby="${headerId}">
                <div class="modal-backdrop fixed inset-0 bg-black bg-opacity-50"></div>
                <div class="modal-content fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white rounded-lg shadow-xl max-w-${options.size || '2xl'} w-full max-h-[90vh] overflow-hidden">
                    ${options.header !== false ? `
                    <div class="modal-header flex items-center justify-between p-6 border-b">
                        <h3 id="${headerId}" class="text-2xl font-bold">${options.title || ''}</h3>
                        <button data-modal-close class="text-gray-500 hover:text-gray-700" aria-label="Close dialog">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    ` : ''}
                    <div class="modal-body ${options.bodyClass || 'p-6'} overflow-y-auto" style="max-height: calc(90vh - 140px);">
                        ${options.content || ''}
                    </div>
                    ${options.footer !== false ? `
                    <div class="modal-footer flex items-center justify-end space-x-4 p-6 border-t">
                        ${options.footer || `
                            <button data-modal-close class="btn btn-secondary">Close</button>
                        `}
                    </div>
                    ` : ''}
                </div>
            </div>
        `;

        // Add to DOM
        const modalElement = document.createElement('div');
        modalElement.innerHTML = modalHtml;
        document.body.appendChild(modalElement.firstElementChild);

        // Register the modal
        const modal = register(modalId, document.querySelector(`[data-modal="${modalId}"]`));

        // Set callbacks if provided
        if (options.onOpen) modal.onOpen = options.onOpen;
        if (options.onClose) modal.onClose = options.onClose;

        return modal;
    }

    /**
     * Get all focusable elements within a modal
     */
    function getFocusableElements(modalElement) {
        const focusableSelectors = 'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])';
        return Array.from(modalElement.querySelectorAll(focusableSelectors));
    }

    /**
     * Announce to screen readers
     */
    function announceToScreenReader(message) {
        const announcement = document.createElement('div');
        announcement.setAttribute('role', 'status');
        announcement.setAttribute('aria-live', 'polite');
        announcement.setAttribute('aria-atomic', 'true');
        announcement.className = 'sr-only';
        announcement.textContent = message;
        document.body.appendChild(announcement);

        // Remove after announcement
        setTimeout(() => {
            document.body.removeChild(announcement);
        }, 1000);
    }

    /**
     * Handle focus trap for keyboard navigation
     */
    function handleModalKeydown(event, modalElement) {
        if (event.key !== 'Tab') return;

        const focusableElements = getFocusableElements(modalElement);
        if (focusableElements.length === 0) return;

        const firstElement = focusableElements[0];
        const lastElement = focusableElements[focusableElements.length - 1];
        const activeElement = document.activeElement;

        // Shift + Tab on first element - go to last
        if (event.shiftKey && activeElement === firstElement) {
            event.preventDefault();
            lastElement.focus();
        }
        // Tab on last element - go to first
        else if (!event.shiftKey && activeElement === lastElement) {
            event.preventDefault();
            firstElement.focus();
        }
    }

    /**
     * Open a modal
     */
    function open(id, data) {
        const modal = modals[id];

        if (!modal) {
            console.error(`Modal "${id}" not found`);
            return;
        }

        // Close active modal if exists (unless stacking)
        if (activeModal && activeModal.id !== id) {
            modalStack.push(activeModal);
        }

        // Show modal
        modal.element.classList.remove('hidden');
        document.body.style.overflow = 'hidden';

        // Add animation
        setTimeout(() => {
            modal.element.classList.add('show');
        }, 10);

        // Set as active
        activeModal = modal;

        // Call onOpen callback
        if (modal.onOpen) {
            modal.onOpen(data);
        }

        // Focus management - focus first focusable element
        const focusableElements = getFocusableElements(modal.element);
        if (focusableElements.length > 0) {
            focusableElements[0].focus();
        }

        // Add keyboard event listener for focus trap
        const handleKeydown = (event) => handleModalKeydown(event, modal.element);
        modal.element.addEventListener('keydown', handleKeydown);
        modal._keydownListener = handleKeydown;

        // Announce to screen readers
        const title = modal.element.querySelector('[id$="-header"]')?.textContent || 'Dialog opened';
        announceToScreenReader(`${title} dialog has opened`);

        return modal;
    }

    /**
     * Close a modal
     */
    function close(id) {
        const modal = id ? modals[id] : activeModal;

        if (!modal) return;

        // Remove animation
        modal.element.classList.remove('show');

        // Remove keyboard event listener
        if (modal._keydownListener) {
            modal.element.removeEventListener('keydown', modal._keydownListener);
            delete modal._keydownListener;
        }

        // Announce to screen readers
        const title = modal.element.querySelector('[id$="-header"]')?.textContent || 'Dialog';
        announceToScreenReader(`${title} dialog has closed`);

        // Hide after animation
        setTimeout(() => {
            modal.element.classList.add('hidden');

            // Restore body scroll if no other modals
            if (modalStack.length === 0) {
                document.body.style.overflow = '';
            }

            // Call onClose callback
            if (modal.onClose) {
                modal.onClose();
            }

            // Check for stacked modals
            if (modalStack.length > 0) {
                activeModal = modalStack.pop();
            } else {
                activeModal = null;
            }
        }, 300);
    }

    /**
     * Update modal content
     */
    function updateContent(id, content) {
        const modal = modals[id];
        if (!modal) return;

        const bodyElement = modal.element.querySelector('.modal-body');
        if (bodyElement) {
            bodyElement.innerHTML = content;
        }
    }

    /**
     * Initialize vehicle details modal
     */
    function initVehicleDetailsModal() {
        // Create modal if it doesn't exist
        if (!document.querySelector('[data-modal="vehicle-details"]')) {
            create({
                id: 'vehicle-details',
                title: 'Vehicle Details',
                size: '4xl',
                content: '<div class="text-center py-8">Loading vehicle details...</div>',
                footer: `
                    <button data-modal-close class="btn btn-secondary">Close</button>
                    <button id="add-to-compare-btn" class="btn btn-primary">Add to Compare</button>
                    <button id="get-quote-btn" class="btn btn-success">Get Insurance Quote</button>
                `,
                onOpen: function(vehicleId) {
                    loadVehicleDetails(vehicleId);
                }
            });
        }
    }

    /**
     * Initialize comparison modal
     */
    function initComparisonModal() {
        // Create modal if it doesn't exist
        const existingModal = document.getElementById('selected-vehicles-modal');
        if (existingModal) {
            register('comparison', existingModal);
        } else {
            create({
                id: 'comparison',
                title: 'Vehicle Comparison',
                size: '6xl',
                content: '<div id="comparison-content">Select vehicles to compare</div>',
                footer: `
                    <button id="clear-comparison" class="btn btn-secondary">Clear All</button>
                    <button data-modal-close class="btn btn-secondary">Close</button>
                    <button id="compare-insurance" class="btn btn-primary">Compare Insurance</button>
                `
            });
        }
    }

    /**
     * Initialize insurance quote modal
     */
    function initInsuranceQuoteModal() {
        create({
            id: 'insurance-quote',
            title: 'Get Insurance Quote',
            size: '3xl',
            content: `
                <form id="insurance-quote-form" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">First Name</label>
                            <input type="text" name="firstName" class="form-input" required>
                        </div>
                        <div>
                            <label class="form-label">Last Name</label>
                            <input type="text" name="lastName" class="form-input" required>
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-input" required>
                    </div>
                    <div>
                        <label class="form-label">Phone</label>
                        <input type="tel" name="phone" class="form-input" required>
                    </div>
                    <div>
                        <label class="form-label">Date of Birth</label>
                        <input type="date" name="dob" class="form-input" required>
                    </div>
                    <div>
                        <label class="form-label">Driving Experience (years)</label>
                        <input type="number" name="experience" class="form-input" min="0" required>
                    </div>
                    <div>
                        <label class="form-label">Coverage Type</label>
                        <select name="coverage" class="form-select" required>
                            <option value="">Select Coverage</option>
                            <option value="liability">Liability Only</option>
                            <option value="collision">Collision</option>
                            <option value="comprehensive">Comprehensive</option>
                            <option value="full">Full Coverage</option>
                        </select>
                    </div>
                </form>
            `,
            footer: `
                <button data-modal-close class="btn btn-secondary">Cancel</button>
                <button id="submit-quote-btn" class="btn btn-primary">Get Quote</button>
            `,
            onOpen: function(vehicleData) {
                // Pre-populate if vehicle data provided
                if (vehicleData) {
                    const form = document.getElementById('insurance-quote-form');
                    form.dataset.vehicleId = vehicleData.id;
                }
            }
        });

        // Handle form submission
        document.addEventListener('click', function(event) {
            if (event.target.id === 'submit-quote-btn') {
                submitInsuranceQuote();
            }
        });
    }

    /**
     * Load vehicle details via AJAX
     */
    async function loadVehicleDetails(vehicleId) {
        try {
            const response = await fetch(`${safequote_ajax.ajax_url}?action=get_vehicle_details&id=${vehicleId}&nonce=${safequote_ajax.nonce}`);
            const data = await response.json();

            if (data.success) {
                updateContent('vehicle-details', generateVehicleDetailsHTML(data.data));
            } else {
                updateContent('vehicle-details', '<div class="alert alert-error">Failed to load vehicle details</div>');
            }
        } catch (error) {
            console.error('Error loading vehicle details:', error);
            updateContent('vehicle-details', '<div class="alert alert-error">An error occurred while loading vehicle details</div>');
        }
    }

    /**
     * Generate vehicle details HTML
     */
    function generateVehicleDetailsHTML(vehicle) {
        return `
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div>
                    <img src="${vehicle.image}" alt="${vehicle.name}" class="w-full rounded-lg">
                    <div class="mt-4 grid grid-cols-4 gap-2">
                        ${vehicle.gallery ? vehicle.gallery.map(img => `
                            <img src="${img}" alt="" class="rounded cursor-pointer hover:opacity-75">
                        `).join('') : ''}
                    </div>
                </div>
                <div>
                    <h2 class="text-3xl font-bold mb-2">${vehicle.year} ${vehicle.make} ${vehicle.model}</h2>
                    <p class="text-3xl font-bold text-blue-600 mb-4">$${vehicle.price}</p>

                    <div class="mb-4">
                        <h3 class="font-semibold mb-2">Safety Rating</h3>
                        <div class="safety-rating">
                            ${generateStarRating(vehicle.safetyRating)}
                            <span class="ml-2 text-gray-600">(${vehicle.safetyRating}/5)</span>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h3 class="font-semibold mb-2">Key Features</h3>
                        <ul class="space-y-1">
                            ${vehicle.features ? vehicle.features.map(feature => `
                                <li class="flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    ${feature}
                                </li>
                            `).join('') : ''}
                        </ul>
                    </div>

                    <div class="mb-4">
                        <h3 class="font-semibold mb-2">Specifications</h3>
                        <dl class="grid grid-cols-2 gap-2 text-sm">
                            <dt class="font-medium">Engine:</dt>
                            <dd>${vehicle.engine || 'N/A'}</dd>
                            <dt class="font-medium">Transmission:</dt>
                            <dd>${vehicle.transmission || 'N/A'}</dd>
                            <dt class="font-medium">Fuel Type:</dt>
                            <dd>${vehicle.fuelType || 'N/A'}</dd>
                            <dt class="font-medium">MPG:</dt>
                            <dd>${vehicle.mpg || 'N/A'}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        `;
    }

    /**
     * Generate star rating HTML
     */
    function generateStarRating(rating) {
        let stars = '';
        for (let i = 1; i <= 5; i++) {
            stars += `<svg class="w-6 h-6 ${i <= rating ? 'text-yellow-400' : 'text-gray-300'} fill-current inline-block" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
            </svg>`;
        }
        return stars;
    }

    /**
     * Submit insurance quote form
     */
    async function submitInsuranceQuote() {
        const form = document.getElementById('insurance-quote-form');
        const formData = new FormData(form);

        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        // Add vehicle ID if present
        if (form.dataset.vehicleId) {
            formData.append('vehicleId', form.dataset.vehicleId);
        }

        // Show loading
        const submitBtn = document.getElementById('submit-quote-btn');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Processing...';
        submitBtn.disabled = true;

        try {
            const response = await fetch(safequote_ajax.ajax_url, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                // Show success message
                updateContent('insurance-quote', `
                    <div class="text-center py-8">
                        <svg class="w-16 h-16 text-green-500 mx-auto mb-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <h3 class="text-2xl font-bold mb-2">Quote Request Submitted!</h3>
                        <p class="text-gray-600">We'll send your personalized quotes to ${formData.get('email')}</p>
                    </div>
                `);

                // Close modal after delay
                setTimeout(() => {
                    close('insurance-quote');
                }, 3000);
            } else {
                throw new Error(data.message || 'Failed to submit quote request');
            }
        } catch (error) {
            console.error('Error submitting quote:', error);
            if (window.SafeQuoteNotifications) {
                window.SafeQuoteNotifications.show('Failed to submit quote request. Please try again.', 'error');
            }
        } finally {
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        }
    }

    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Expose API
    window.SafeQuoteModals = {
        init: init,
        register: register,
        create: create,
        open: open,
        close: close,
        updateContent: updateContent
    };

})();

// Global helper functions
window.viewVehicleDetails = function(vehicleId) {
    window.SafeQuoteModals.open('vehicle-details', vehicleId);
};

window.addToCompare = function(vehicleId) {
    // This will be handled by the comparison module
    if (window.SafeQuoteComparison) {
        window.SafeQuoteComparison.add(vehicleId);
    }
};