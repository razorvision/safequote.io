/**
 * Forms Handler Module
 * Handles insurance quote form validation and submission
 */

(function() {
    'use strict';

    /**
     * Initialize forms module
     */
    function init() {
        initializeAllForms();
        bindGlobalEvents();
    }

    /**
     * Initialize all forms on the page
     */
    function initializeAllForms() {
        // Insurance quote form (rendered by modals.js)
        const insuranceForm = document.getElementById('insurance-quote-form');
        if (insuranceForm) {
            initInsuranceForm(insuranceForm);
        }
    }

    /**
     * Bind global form events
     */
    function bindGlobalEvents() {
        // Prevent double submissions
        document.addEventListener('submit', function(event) {
            const form = event.target;
            if (form.dataset.submitting === 'true') {
                event.preventDefault();
                return false;
            }
        });
    }

    /**
     * Initialize insurance quote form
     */
    function initInsuranceForm(form) {
        form.addEventListener('submit', async function(event) {
            event.preventDefault();

            if (!validateForm(form)) {
                return;
            }

            const formData = new FormData(form);
            await submitInsuranceQuote(formData, form);
        });

        // Calculate age from DOB
        const dobField = form.querySelector('[name="dob"]');
        if (dobField) {
            dobField.addEventListener('change', function() {
                const age = calculateAge(this.value);
                const ageDisplay = form.querySelector('#age-display');
                if (ageDisplay) {
                    ageDisplay.textContent = age ? `${age} years old` : '';
                }
            });
        }
    }

    /**
     * Validate entire form using HTML5 validation
     */
    function validateForm(form) {
        let isValid = true;
        const fields = form.querySelectorAll('input, textarea, select');

        fields.forEach(field => {
            if (!validateField(field)) {
                isValid = false;
            }
        });

        return isValid;
    }

    /**
     * Validate single field
     */
    function validateField(field) {
        // Clear previous errors
        clearFieldError(field);

        // HTML5 validation
        if (!field.checkValidity()) {
            showFieldError(field, field.validationMessage);
            return false;
        }

        // Mark as valid
        field.classList.add('is-valid');
        field.classList.remove('is-invalid');

        return true;
    }

    /**
     * Show field error
     */
    function showFieldError(field, message) {
        field.classList.add('is-invalid');
        field.classList.remove('is-valid');

        // Create or update error message
        let errorElement = field.parentElement.querySelector('.form-error');
        if (!errorElement) {
            errorElement = document.createElement('div');
            errorElement.className = 'form-error text-sm text-red-600 mt-1';
            field.parentElement.appendChild(errorElement);
        }
        errorElement.textContent = message;

        // Add shake animation
        if (window.SafeQuoteAnimations) {
            window.SafeQuoteAnimations.shake(field);
        }
    }

    /**
     * Clear field error
     */
    function clearFieldError(field) {
        field.classList.remove('is-invalid', 'is-valid');

        const errorElement = field.parentElement.querySelector('.form-error');
        if (errorElement) {
            errorElement.remove();
        }
    }

    /**
     * Submit insurance quote
     */
    async function submitInsuranceQuote(formData, form) {
        formData.append('action', 'submit_insurance_quote');
        formData.append('nonce', safequote_ajax.nonce);

        const submitBtn = form.querySelector('[type="submit"]');
        showButtonLoading(submitBtn, true);

        try {
            const response = await fetch(safequote_ajax.ajax_url, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                showNotification('Quote request submitted successfully!', 'success');

                // Show results
                if (data.data.quotes && window.SafeQuoteInsurance) {
                    window.SafeQuoteInsurance.displayComparison(data.data.quotes);
                }

                // Reset form
                form.reset();
            } else {
                showNotification(data.message || 'Failed to submit quote', 'error');
            }
        } catch (error) {
            console.error('Quote submission error:', error);
            showNotification('An error occurred while submitting your quote', 'error');
        } finally {
            showButtonLoading(submitBtn, false);
        }
    }

    /**
     * Calculate age from date of birth
     */
    function calculateAge(dob) {
        const birthDate = new Date(dob);
        const today = new Date();
        let age = today.getFullYear() - birthDate.getFullYear();
        const monthDiff = today.getMonth() - birthDate.getMonth();

        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }

        return age;
    }

    /**
     * Show button loading state
     */
    function showButtonLoading(button, isLoading) {
        if (!button) return;

        if (isLoading) {
            button.dataset.originalText = button.textContent;
            button.textContent = button.dataset.loadingText || 'Processing...';
            button.disabled = true;
            button.classList.add('opacity-75', 'cursor-not-allowed');
        } else {
            button.textContent = button.dataset.originalText || 'Submit';
            button.disabled = false;
            button.classList.remove('opacity-75', 'cursor-not-allowed');
        }
    }

    /**
     * Show notification
     */
    function showNotification(message, type) {
        if (window.SafeQuoteNotifications) {
            window.SafeQuoteNotifications.show(message, type);
        } else {
            alert(message);
        }
    }

    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Expose API
    window.SafeQuoteForms = {
        init: init,
        validateForm: validateForm,
        validateField: validateField,
        clearFieldError: clearFieldError,
        showFieldError: showFieldError
    };

})();
