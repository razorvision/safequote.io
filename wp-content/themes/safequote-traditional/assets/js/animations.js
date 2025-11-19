/**
 * Animation Effects Module
 * Handles all animation-related functionality
 */

(function() {
    'use strict';

    const animationClasses = {
        fadeIn: 'fade-in',
        fadeOut: 'fade-out',
        slideInRight: 'slide-in-right',
        slideInLeft: 'slide-in-left',
        slideInUp: 'slide-in-up',
        slideInDown: 'slide-in-down',
        scaleIn: 'scale-in',
        pulse: 'pulse',
        spin: 'spin',
        bounce: 'bounce'
    };

    // Check if user prefers reduced motion
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /**
     * Check if animations should be disabled
     */
    function shouldReduceMotion() {
        return prefersReducedMotion;
    }

    /**
     * Initialize animations
     */
    function init() {
        // Initialize scroll-triggered animations
        initScrollAnimations();

        // Initialize hover effects
        initHoverEffects();

        // Initialize loading animations
        initLoadingAnimations();

        // Initialize counter animations
        initCounterAnimations();

        // Listen for changes to prefers-reduced-motion media query
        const mediaQueryList = window.matchMedia('(prefers-reduced-motion: reduce)');
        mediaQueryList.addEventListener('change', (event) => {
            // Handle motion preference changes
            console.log('Motion preference changed:', event.matches);
        });
    }

    /**
     * Initialize scroll-triggered animations
     */
    function initScrollAnimations() {
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -100px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const element = entry.target;
                    const animation = element.dataset.animation || 'fadeIn';
                    const delay = element.dataset.animationDelay || 0;

                    setTimeout(() => {
                        animate(element, animation);
                        observer.unobserve(element);
                    }, delay);
                }
            });
        }, observerOptions);

        // Observe all elements with data-animation attribute
        document.querySelectorAll('[data-animation]').forEach(element => {
            observer.observe(element);
        });

        // Stagger animations for lists
        document.querySelectorAll('[data-stagger]').forEach(container => {
            const items = container.children;
            Array.from(items).forEach((item, index) => {
                item.style.animationDelay = `${index * 0.1}s`;
                item.classList.add('stagger-item');
            });
        });
    }

    /**
     * Initialize hover effects
     */
    function initHoverEffects() {
        // Lift effect
        document.querySelectorAll('[data-hover="lift"]').forEach(element => {
            element.classList.add('hover-lift');
        });

        // Grow effect
        document.querySelectorAll('[data-hover="grow"]').forEach(element => {
            element.classList.add('hover-grow');
        });

        // Shrink effect
        document.querySelectorAll('[data-hover="shrink"]').forEach(element => {
            element.classList.add('hover-shrink');
        });

        // Card hover effect
        document.querySelectorAll('.vehicle-card, .insurance-card').forEach(card => {
            card.classList.add('card-hover');
        });

        // Button press effect
        document.querySelectorAll('.btn').forEach(button => {
            button.classList.add('btn-press');
        });
    }

    /**
     * Initialize loading animations
     */
    function initLoadingAnimations() {
        // Create reusable loading spinner
        window.createLoadingSpinner = function() {
            const spinner = document.createElement('div');
            spinner.className = 'spinner';
            return spinner;
        };

        // Create skeleton loader
        window.createSkeletonLoader = function(type = 'card') {
            const skeleton = document.createElement('div');

            switch(type) {
                case 'card':
                    skeleton.className = 'skeleton-card rounded-lg';
                    break;
                case 'text':
                    skeleton.className = 'skeleton-text';
                    break;
                case 'title':
                    skeleton.className = 'skeleton-title';
                    break;
                default:
                    skeleton.className = 'skeleton';
            }

            return skeleton;
        };
    }

    /**
     * Initialize counter animations
     */
    function initCounterAnimations() {
        const counters = document.querySelectorAll('[data-counter]');

        const observerOptions = {
            threshold: 0.5
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const counter = entry.target;
                    animateCounter(counter);
                    observer.unobserve(counter);
                }
            });
        }, observerOptions);

        counters.forEach(counter => {
            observer.observe(counter);
        });
    }

    /**
     * Animate a counter element
     */
    function animateCounter(element) {
        const target = parseInt(element.dataset.counter);
        const duration = parseInt(element.dataset.duration) || 2000;

        // If user prefers reduced motion, skip animation and show final value
        if (shouldReduceMotion()) {
            element.textContent = formatNumber(target);
            return;
        }

        const start = 0;
        const increment = target / (duration / 16);
        let current = start;

        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                element.textContent = formatNumber(target);
                clearInterval(timer);
            } else {
                element.textContent = formatNumber(Math.floor(current));
            }
        }, 16);
    }

    /**
     * Apply animation to element
     */
    function animate(element, animation, duration = 300) {
        if (!animationClasses[animation]) {
            console.warn(`Animation "${animation}" not found`);
            return;
        }

        // Skip animations if user prefers reduced motion
        if (shouldReduceMotion()) {
            // For reduced motion, just show the element without animation
            element.style.opacity = '1';
            element.style.visibility = 'visible';
            return;
        }

        element.classList.add(animationClasses[animation]);

        // Remove animation class after completion
        if (!['spin', 'pulse', 'bounce'].includes(animation)) {
            setTimeout(() => {
                element.classList.remove(animationClasses[animation]);
            }, duration);
        }
    }

    /**
     * Transition between two states
     */
    function transition(element, fromClass, toClass, duration = 300) {
        element.classList.add('transition-all-300');
        element.classList.remove(fromClass);
        element.classList.add(toClass);
    }

    /**
     * Fade element in
     */
    function fadeIn(element, duration = 300) {
        element.style.display = 'block';

        // If user prefers reduced motion, skip the transition
        if (shouldReduceMotion()) {
            element.style.opacity = '1';
            return;
        }

        element.style.opacity = '0';

        requestAnimationFrame(() => {
            element.style.transition = `opacity ${duration}ms`;
            element.style.opacity = '1';
        });
    }

    /**
     * Fade element out
     */
    function fadeOut(element, duration = 300) {
        // If user prefers reduced motion, hide immediately
        if (shouldReduceMotion()) {
            element.style.display = 'none';
            return;
        }

        element.style.transition = `opacity ${duration}ms`;
        element.style.opacity = '0';

        setTimeout(() => {
            element.style.display = 'none';
        }, duration);
    }

    /**
     * Slide element
     */
    function slide(element, direction = 'down', show = true, duration = 300) {
        // If user prefers reduced motion, skip animation
        if (shouldReduceMotion()) {
            element.style.display = show ? 'block' : 'none';
            element.style.opacity = show ? '1' : '0';
            return;
        }

        const animations = {
            down: show ? 'slideInDown' : 'slideOutUp',
            up: show ? 'slideInUp' : 'slideOutDown',
            left: show ? 'slideInLeft' : 'slideOutRight',
            right: show ? 'slideInRight' : 'slideOutLeft'
        };

        animate(element, animations[direction], duration);
    }

    /**
     * Parallax effect
     */
    function initParallax() {
        const parallaxElements = document.querySelectorAll('[data-parallax]');

        if (parallaxElements.length === 0) return;

        window.addEventListener('scroll', () => {
            const scrolled = window.scrollY;

            parallaxElements.forEach(element => {
                const speed = element.dataset.parallax || 0.5;
                const yPos = -(scrolled * speed);

                element.style.transform = `translateY(${yPos}px)`;
            });
        });
    }

    /**
     * Ripple effect for buttons
     */
    function addRippleEffect(button) {
        button.addEventListener('click', function(e) {
            const ripple = document.createElement('span');
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;

            ripple.style.cssText = `
                position: absolute;
                width: ${size}px;
                height: ${size}px;
                border-radius: 50%;
                background: rgba(255, 255, 255, 0.5);
                left: ${x}px;
                top: ${y}px;
                pointer-events: none;
                transform: scale(0);
                animation: ripple 0.6s ease-out;
            `;

            this.style.position = 'relative';
            this.style.overflow = 'hidden';
            this.appendChild(ripple);

            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
    }

    /**
     * Typewriter effect
     */
    function typewriter(element, text, speed = 100) {
        let i = 0;
        element.textContent = '';

        function type() {
            if (i < text.length) {
                element.textContent += text.charAt(i);
                i++;
                setTimeout(type, speed);
            }
        }

        type();
    }

    /**
     * Format number with commas
     */
    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }

    /**
     * Shake element (for errors)
     */
    function shake(element) {
        element.classList.add('animate-shake');
        setTimeout(() => {
            element.classList.remove('animate-shake');
        }, 500);
    }

    // Add shake animation CSS if not exists
    if (!document.querySelector('#shake-animation-style')) {
        const style = document.createElement('style');
        style.id = 'shake-animation-style';
        style.textContent = `
            @keyframes shake {
                0%, 100% { transform: translateX(0); }
                10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
                20%, 40%, 60%, 80% { transform: translateX(5px); }
            }
            .animate-shake {
                animation: shake 0.5s;
            }
        `;
        document.head.appendChild(style);
    }

    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Expose API
    window.SafeQuoteAnimations = {
        init: init,
        animate: animate,
        transition: transition,
        fadeIn: fadeIn,
        fadeOut: fadeOut,
        slide: slide,
        shake: shake,
        typewriter: typewriter,
        addRippleEffect: addRippleEffect
    };

})();