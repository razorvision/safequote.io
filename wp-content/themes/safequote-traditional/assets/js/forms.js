/**
 * Forms Handler Module
 * Handles form validation, submission, and AJAX interactions
 */

(function() {
    'use strict';

    const forms = new Map();

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
        // Vehicle search form
        const vehicleSearchForm = document.getElementById('vehicle-search-form');
        if (vehicleSearchForm) {
            initVehicleSearchForm(vehicleSearchForm);
        }

        // Insurance quote form
        const insuranceForm = document.getElementById('insurance-quote-form');
        if (insuranceForm) {
            initInsuranceForm(insuranceForm);
        }

        // Contact forms
        document.querySelectorAll('.contact-form').forEach(form => {
            initContactForm(form);
        });

        // Generic AJAX forms
        document.querySelectorAll('[data-ajax-form]').forEach(form => {
            initAjaxForm(form);
        });

        // Forms with validation
        document.querySelectorAll('[data-validate]').forEach(form => {
            initValidation(form);
        });
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

        // Real-time validation
        document.addEventListener('blur', function(event) {
            if (event.target.matches('input, textarea, select')) {
                const form = event.target.closest('form');
                if (form && form.dataset.validate) {
                    validateField(event.target);
                }
            }
        }, true);
    }

    /**
     * Initialize vehicle search form
     */
    function initVehicleSearchForm(form) {
        form.addEventListener('submit', async function(event) {
            event.preventDefault();

            if (!validateForm(form)) {
                return;
            }

            const formData = new FormData(form);
            await searchVehicles(formData);
        });

        // Dynamic field dependencies
        const yearField = form.querySelector('[name="year"]');
        const makeField = form.querySelector('[name="make"]');
        const modelField = form.querySelector('[name="model"]');

        if (yearField) {
            yearField.addEventListener('change', async function() {
                makeField.disabled = !this.value;
                modelField.disabled = true;

                if (this.value && window.SafeQuoteNHTSA) {
                    await populateMakes(this.value, makeField);
                }
            });
        }

        if (makeField) {
            makeField.addEventListener('change', async function() {
                modelField.disabled = !this.value;

                if (this.value && yearField.value && window.SafeQuoteNHTSA) {
                    await populateModels(yearField.value, this.value, modelField);
                }
            });
        }
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
            await submitInsuranceQuote(formData);
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
     * Initialize contact form
     */
    function initContactForm(form) {
        form.addEventListener('submit', async function(event) {
            event.preventDefault();

            if (!validateForm(form)) {
                return;
            }

            const formData = new FormData(form);
            await submitContactForm(formData);
        });
    }

    /**
     * Initialize generic AJAX form
     */
    function initAjaxForm(form) {
        const action = form.dataset.ajaxForm;

        form.addEventListener('submit', async function(event) {
            event.preventDefault();

            if (!validateForm(form)) {
                return;
            }

            const formData = new FormData(form);
            formData.append('action', action);
            formData.append('nonce', safequote_ajax.nonce);

            await submitAjaxForm(form, formData);
        });
    }

    /**
     * Initialize form validation
     */
    function initValidation(form) {
        const validator = {
            rules: {},
            messages: {}
        };

        // Parse validation rules from data attributes
        form.querySelectorAll('[data-validation]').forEach(field => {
            const rules = field.dataset.validation.split('|');
            validator.rules[field.name] = rules;

            if (field.dataset.validationMessages) {
                validator.messages[field.name] = JSON.parse(field.dataset.validationMessages);
            }
        });

        forms.set(form, validator);
    }

    /**
     * Validate entire form
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
        const form = field.closest('form');
        const validator = forms.get(form);

        // Clear previous errors
        clearFieldError(field);

        // HTML5 validation
        if (!field.checkValidity()) {
            showFieldError(field, field.validationMessage);
            return false;
        }

        // Custom validation rules
        if (validator && validator.rules[field.name]) {
            const rules = validator.rules[field.name];

            for (const rule of rules) {
                const error = applyValidationRule(field, rule);
                if (error) {
                    const customMessage = validator.messages[field.name]?.[rule];
                    showFieldError(field, customMessage || error);
                    return false;
                }
            }
        }

        // Mark as valid
        field.classList.add('is-valid');
        field.classList.remove('is-invalid');

        return true;
    }

    /**
     * Apply validation rule
     */
    function applyValidationRule(field, rule) {
        const value = field.value.trim();
        const [ruleName, ...params] = rule.split(':');

        switch(ruleName) {
            case 'required':
                if (!value) {
                    return 'This field is required.';
                }
                break;

            case 'email':
                if (value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                    return 'Please enter a valid email address.';
                }
                break;

            case 'phone':
                if (value && !/^[\d\s\-\+\(\)]+$/.test(value)) {
                    return 'Please enter a valid phone number.';
                }
                break;

            case 'min':
                if (value && value.length < parseInt(params[0])) {
                    return `Must be at least ${params[0]} characters.`;
                }
                break;

            case 'max':
                if (value && value.length > parseInt(params[0])) {
                    return `Must be no more than ${params[0]} characters.`;
                }
                break;

            case 'minValue':
                if (value && parseFloat(value) < parseFloat(params[0])) {
                    return `Must be at least ${params[0]}.`;
                }
                break;

            case 'maxValue':
                if (value && parseFloat(value) > parseFloat(params[0])) {
                    return `Must be no more than ${params[0]}.`;
                }
                break;

            case 'pattern':
                const pattern = new RegExp(params.join(':'));
                if (value && !pattern.test(value)) {
                    return 'Invalid format.';
                }
                break;

            case 'match':
                const matchField = field.form.querySelector(`[name="${params[0]}"]`);
                if (matchField && value !== matchField.value) {
                    return 'Fields do not match.';
                }
                break;

            case 'zipCode':
                if (value && !/^\d{5}(-\d{4})?$/.test(value)) {
                    return 'Please enter a valid ZIP code.';
                }
                break;

            case 'creditCard':
                if (value && !isValidCreditCard(value)) {
                    return 'Please enter a valid credit card number.';
                }
                break;
        }

        return null;
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
     * Submit vehicle search
     */
    async function searchVehicles(formData) {
        const loadingIndicator = document.getElementById('loading-indicator');
        const vehicleGrid = document.getElementById('vehicle-grid');

        if (loadingIndicator) {
            loadingIndicator.classList.remove('hidden');
        }

        try {
            formData.append('action', 'search_vehicles');
            formData.append('nonce', safequote_ajax.nonce);

            const response = await fetch(safequote_ajax.ajax_url, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                if (window.SafeQuoteFilters) {
                    // Use filters module to display results
                    window.SafeQuoteFilters.displayVehicles(data.data);
                } else if (vehicleGrid) {
                    // Fallback display
                    vehicleGrid.innerHTML = data.data.html;
                }

                showNotification('Search completed successfully', 'success');
            } else {
                showNotification(data.message || 'Search failed', 'error');
            }
        } catch (error) {
            console.error('Search error:', error);
            showNotification('An error occurred during search', 'error');
        } finally {
            if (loadingIndicator) {
                loadingIndicator.classList.add('hidden');
            }
        }
    }

    /**
     * Submit insurance quote
     */
    async function submitInsuranceQuote(formData) {
        formData.append('action', 'submit_insurance_quote');
        formData.append('nonce', safequote_ajax.nonce);

        showButtonLoading(event.target.querySelector('[type="submit"]'), true);

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
                event.target.reset();
            } else {
                showNotification(data.message || 'Failed to submit quote', 'error');
            }
        } catch (error) {
            console.error('Quote submission error:', error);
            showNotification('An error occurred while submitting your quote', 'error');
        } finally {
            showButtonLoading(event.target.querySelector('[type="submit"]'), false);
        }
    }

    /**
     * Submit contact form
     */
    async function submitContactForm(formData) {
        formData.append('action', 'submit_contact');
        formData.append('nonce', safequote_ajax.nonce);

        const form = event.target;
        showButtonLoading(form.querySelector('[type="submit"]'), true);

        try {
            const response = await fetch(safequote_ajax.ajax_url, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                showNotification('Message sent successfully!', 'success');
                form.reset();

                // Show success message in form
                const successMessage = document.createElement('div');
                successMessage.className = 'alert alert-success mb-4';
                successMessage.textContent = 'Thank you for contacting us. We will get back to you soon!';
                form.parentElement.insertBefore(successMessage, form);

                setTimeout(() => {
                    successMessage.remove();
                }, 5000);
            } else {
                showNotification(data.message || 'Failed to send message', 'error');
            }
        } catch (error) {
            console.error('Contact form error:', error);
            showNotification('An error occurred while sending your message', 'error');
        } finally {
            showButtonLoading(form.querySelector('[type="submit"]'), false);
        }
    }

    /**
     * Submit generic AJAX form
     */
    async function submitAjaxForm(form, formData) {
        showButtonLoading(form.querySelector('[type="submit"]'), true);

        try {
            const response = await fetch(safequote_ajax.ajax_url, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                showNotification(data.message || 'Form submitted successfully!', 'success');

                // Handle redirect if provided
                if (data.data?.redirect) {
                    window.location.href = data.data.redirect;
                }

                // Handle callback if provided
                const callback = form.dataset.successCallback;
                if (callback && window[callback]) {
                    window[callback](data.data);
                }

                // Reset form if specified
                if (form.dataset.reset === 'true') {
                    form.reset();
                }
            } else {
                showNotification(data.message || 'Form submission failed', 'error');
            }
        } catch (error) {
            console.error('Form submission error:', error);
            showNotification('An error occurred while submitting the form', 'error');
        } finally {
            showButtonLoading(form.querySelector('[type="submit"]'), false);
        }
    }

    /**
     * Populate makes dropdown
     */
    async function populateMakes(year, selectElement) {
        selectElement.innerHTML = '<option value="">Loading...</option>';

        try {
            const makes = await window.SafeQuoteNHTSA.getMakes(year);

            selectElement.innerHTML = '<option value="">Select Make</option>';
            makes.forEach(make => {
                const option = document.createElement('option');
                option.value = make.name;
                option.textContent = make.name;
                selectElement.appendChild(option);
            });

            selectElement.disabled = false;
        } catch (error) {
            console.error('Error loading makes:', error);
            selectElement.innerHTML = '<option value="">Error loading makes</option>';
        }
    }

    /**
     * Populate models dropdown
     */
    async function populateModels(year, make, selectElement) {
        selectElement.innerHTML = '<option value="">Loading...</option>';

        try {
            const models = await window.SafeQuoteNHTSA.getModels(year, make);

            selectElement.innerHTML = '<option value="">Select Model</option>';
            models.forEach(model => {
                const option = document.createElement('option');
                option.value = model.name;
                option.textContent = model.name;
                selectElement.appendChild(option);
            });

            selectElement.disabled = false;
        } catch (error) {
            console.error('Error loading models:', error);
            selectElement.innerHTML = '<option value="">Error loading models</option>';
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
     * Validate credit card number (Luhn algorithm)
     */
    function isValidCreditCard(number) {
        const digits = number.replace(/\D/g, '');
        if (digits.length < 13 || digits.length > 19) return false;

        let sum = 0;
        let isEven = false;

        for (let i = digits.length - 1; i >= 0; i--) {
            let digit = parseInt(digits[i]);

            if (isEven) {
                digit *= 2;
                if (digit > 9) {
                    digit -= 9;
                }
            }

            sum += digit;
            isEven = !isEven;
        }

        return sum % 10 === 0;
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