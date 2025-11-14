/**
 * Main JavaScript File
 * Initializes all theme functionality
 */

(function() {
    'use strict';

    // Wait for DOM to be ready
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize mobile menu
        initMobileMenu();

        // Initialize smooth scrolling
        initSmoothScroll();

        // Initialize scroll reveal animations
        initScrollReveal();

        // Initialize all interactive components
        initializeComponents();

        console.log('SafeQuote Theme initialized');
    });

    /**
     * Initialize mobile menu functionality
     */
    function initMobileMenu() {
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');

        if (mobileMenuButton && mobileMenu) {
            mobileMenuButton.addEventListener('click', function() {
                const isExpanded = this.getAttribute('aria-expanded') === 'true';

                // Toggle menu visibility
                mobileMenu.classList.toggle('hidden');

                // Update aria-expanded
                this.setAttribute('aria-expanded', !isExpanded);

                // Toggle icon
                const icons = this.querySelectorAll('svg');
                icons.forEach(icon => icon.classList.toggle('hidden'));
            });

            // Close menu when clicking outside
            document.addEventListener('click', function(event) {
                if (!mobileMenuButton.contains(event.target) && !mobileMenu.contains(event.target)) {
                    mobileMenu.classList.add('hidden');
                    mobileMenuButton.setAttribute('aria-expanded', 'false');
                    const icons = mobileMenuButton.querySelectorAll('svg');
                    icons[0].classList.remove('hidden');
                    icons[1].classList.add('hidden');
                }
            });
        }
    }

    /**
     * Initialize smooth scrolling for anchor links
     */
    function initSmoothScroll() {
        const links = document.querySelectorAll('a[href^="#"]');

        links.forEach(link => {
            link.addEventListener('click', function(e) {
                const href = this.getAttribute('href');

                // Skip if it's just "#"
                if (href === '#') return;

                const target = document.querySelector(href);

                if (target) {
                    e.preventDefault();

                    const offsetTop = target.getBoundingClientRect().top + window.scrollY;
                    const adminBarHeight = document.getElementById('wpadminbar') ? 32 : 0;

                    window.scrollTo({
                        top: offsetTop - adminBarHeight - 80, // Account for fixed header
                        behavior: 'smooth'
                    });
                }
            });
        });
    }

    /**
     * Initialize scroll reveal animations
     */
    function initScrollReveal() {
        const reveals = document.querySelectorAll('.reveal');

        if (reveals.length === 0) return;

        const revealOnScroll = function() {
            reveals.forEach(element => {
                const windowHeight = window.innerHeight;
                const elementTop = element.getBoundingClientRect().top;
                const elementVisible = 150;

                if (elementTop < windowHeight - elementVisible) {
                    element.classList.add('active');
                }
            });
        };

        window.addEventListener('scroll', revealOnScroll);

        // Initial check
        revealOnScroll();
    }

    /**
     * Initialize all interactive components
     */
    function initializeComponents() {
        // Initialize tooltips
        initTooltips();

        // Initialize accordions
        initAccordions();

        // Initialize tabs
        initTabs();

        // Initialize back to top button
        initBackToTop();

        // Initialize form validation
        initFormValidation();
    }

    /**
     * Initialize tooltips
     */
    function initTooltips() {
        const tooltips = document.querySelectorAll('[data-tooltip]');

        tooltips.forEach(element => {
            const tooltipText = element.getAttribute('data-tooltip');

            element.addEventListener('mouseenter', function() {
                const tooltip = document.createElement('div');
                tooltip.className = 'tooltip';
                tooltip.textContent = tooltipText;
                tooltip.style.cssText = `
                    position: absolute;
                    background: #333;
                    color: white;
                    padding: 5px 10px;
                    border-radius: 4px;
                    font-size: 14px;
                    z-index: 1000;
                    pointer-events: none;
                `;

                document.body.appendChild(tooltip);

                const rect = element.getBoundingClientRect();
                tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
                tooltip.style.top = rect.top - tooltip.offsetHeight - 5 + 'px';

                element._tooltip = tooltip;
            });

            element.addEventListener('mouseleave', function() {
                if (element._tooltip) {
                    element._tooltip.remove();
                    delete element._tooltip;
                }
            });
        });
    }

    /**
     * Initialize accordions
     */
    function initAccordions() {
        const accordions = document.querySelectorAll('.accordion');

        accordions.forEach(accordion => {
            const triggers = accordion.querySelectorAll('.accordion-trigger');

            triggers.forEach(trigger => {
                trigger.addEventListener('click', function() {
                    const panel = this.nextElementSibling;
                    const isOpen = panel.style.maxHeight;

                    // Close all panels in this accordion
                    accordion.querySelectorAll('.accordion-panel').forEach(p => {
                        p.style.maxHeight = null;
                    });

                    // Toggle current panel
                    if (!isOpen) {
                        panel.style.maxHeight = panel.scrollHeight + 'px';
                    }

                    // Update aria-expanded
                    triggers.forEach(t => t.setAttribute('aria-expanded', 'false'));
                    if (!isOpen) {
                        this.setAttribute('aria-expanded', 'true');
                    }
                });
            });
        });
    }

    /**
     * Initialize tabs
     */
    function initTabs() {
        const tabContainers = document.querySelectorAll('.tabs');

        tabContainers.forEach(container => {
            const tabs = container.querySelectorAll('.tab-button');
            const panels = container.querySelectorAll('.tab-panel');

            tabs.forEach((tab, index) => {
                tab.addEventListener('click', function() {
                    // Remove active class from all tabs and panels
                    tabs.forEach(t => t.classList.remove('active'));
                    panels.forEach(p => p.classList.remove('active'));

                    // Add active class to clicked tab and corresponding panel
                    this.classList.add('active');
                    panels[index].classList.add('active');

                    // Update aria attributes
                    tabs.forEach(t => t.setAttribute('aria-selected', 'false'));
                    this.setAttribute('aria-selected', 'true');
                });
            });
        });
    }

    /**
     * Initialize back to top button
     */
    function initBackToTop() {
        // Create button if it doesn't exist
        let backToTop = document.getElementById('back-to-top');

        if (!backToTop) {
            backToTop = document.createElement('button');
            backToTop.id = 'back-to-top';
            backToTop.innerHTML = '↑';
            backToTop.style.cssText = `
                position: fixed;
                bottom: 20px;
                right: 20px;
                width: 50px;
                height: 50px;
                background: #3b82f6;
                color: white;
                border: none;
                border-radius: 50%;
                font-size: 20px;
                cursor: pointer;
                display: none;
                z-index: 999;
                transition: all 0.3s ease;
            `;
            document.body.appendChild(backToTop);
        }

        // Show/hide based on scroll position
        window.addEventListener('scroll', function() {
            if (window.scrollY > 300) {
                backToTop.style.display = 'block';
            } else {
                backToTop.style.display = 'none';
            }
        });

        // Scroll to top on click
        backToTop.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }

    /**
     * Initialize form validation
     */
    function initFormValidation() {
        const forms = document.querySelectorAll('.needs-validation');

        forms.forEach(form => {
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }

                form.classList.add('was-validated');
            });

            // Real-time validation
            const inputs = form.querySelectorAll('input, textarea, select');
            inputs.forEach(input => {
                input.addEventListener('blur', function() {
                    if (this.checkValidity()) {
                        this.classList.remove('is-invalid');
                        this.classList.add('is-valid');
                    } else {
                        this.classList.remove('is-valid');
                        this.classList.add('is-invalid');
                    }
                });
            });
        });
    }

    // Expose some functions globally if needed
    window.SafeQuoteTheme = {
        initMobileMenu: initMobileMenu,
        initSmoothScroll: initSmoothScroll,
        initScrollReveal: initScrollReveal,
        initializeComponents: initializeComponents
    };

})();