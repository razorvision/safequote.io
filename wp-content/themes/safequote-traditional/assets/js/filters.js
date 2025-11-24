/**
 * Vehicle Filters Module
 * Handles all filtering functionality for vehicle search
 */

(function() {
    'use strict';

    // State management
    const filterState = {
        year: '',
        make: '',
        model: '',
        minPrice: 0,
        maxPrice: 200000,
        minSafetyRating: 0,
        vehicleType: '',
        fuelType: ''
    };

    // Cache DOM elements
    let elements = {};

    /**
     * Initialize filters
     */
    function init() {
        cacheElements();
        bindEvents();
        loadInitialFilters();
    }

    /**
     * Cache DOM elements for better performance
     */
    function cacheElements() {
        elements = {
            yearSelect: document.getElementById('year'),
            makeSelect: document.getElementById('make'),
            modelSelect: document.getElementById('model'),
            minPriceInput: document.getElementById('min-price'),
            maxPriceInput: document.getElementById('max-price'),
            safetyRatingFilter: document.getElementById('safety-rating-filter'),
            searchForm: document.getElementById('vehicle-search-form'),
            vehicleGrid: document.getElementById('vehicle-grid'),
            loadingIndicator: document.getElementById('loading-indicator'),
            resultsCount: document.getElementById('results-count'),
            sortSelect: document.getElementById('sort-by')
        };
    }

    /**
     * Bind event listeners
     */
    function bindEvents() {
        // Year change triggers make population
        if (elements.yearSelect) {
            elements.yearSelect.addEventListener('change', handleYearChange);
        }

        // Make change triggers model population
        if (elements.makeSelect) {
            elements.makeSelect.addEventListener('change', handleMakeChange);
        }

        // Price range inputs
        if (elements.minPriceInput) {
            elements.minPriceInput.addEventListener('input', debounce(handlePriceChange, 500));
        }
        if (elements.maxPriceInput) {
            elements.maxPriceInput.addEventListener('input', debounce(handlePriceChange, 500));
        }

        // Safety rating filter
        if (elements.safetyRatingFilter) {
            const stars = elements.safetyRatingFilter.querySelectorAll('.safety-star');
            stars.forEach(star => {
                star.addEventListener('click', handleSafetyRatingClick);
            });
        }

        // Form submission
        if (elements.searchForm) {
            elements.searchForm.addEventListener('submit', handleFormSubmit);
            elements.searchForm.addEventListener('reset', handleFormReset);
        }

        // Sort functionality
        if (elements.sortSelect) {
            elements.sortSelect.addEventListener('change', handleSort);
        }
    }

    /**
     * Load initial filter data
     */
    function loadInitialFilters() {
        // Restore filters from URL parameters if present
        restoreFiltersFromURL();

        // Check if we have AJAX URL from localized script
        if (typeof safequote_ajax !== 'undefined') {
            // Load makes for the selected year if any
            const selectedYear = elements.yearSelect?.value;
            if (selectedYear) {
                loadMakes(selectedYear);
            }
        }
    }

    /**
     * Restore filters from URL parameters
     */
    function restoreFiltersFromURL() {
        const params = new URLSearchParams(window.location.search);

        // Restore year
        if (params.has('year')) {
            const year = params.get('year');
            filterState.year = year;
            if (elements.yearSelect) {
                elements.yearSelect.value = year;
            }
        }

        // Restore make
        if (params.has('make')) {
            filterState.make = params.get('make');
        }

        // Restore model
        if (params.has('model')) {
            filterState.model = params.get('model');
        }

        // Restore price range
        if (params.has('minPrice')) {
            filterState.minPrice = parseInt(params.get('minPrice'));
            if (elements.minPriceInput) {
                elements.minPriceInput.value = filterState.minPrice;
            }
        }

        if (params.has('maxPrice')) {
            filterState.maxPrice = parseInt(params.get('maxPrice'));
            if (elements.maxPriceInput) {
                elements.maxPriceInput.value = filterState.maxPrice;
            }
        }

        // Restore safety rating
        if (params.has('minSafetyRating')) {
            const rating = parseInt(params.get('minSafetyRating'));
            filterState.minSafetyRating = rating;

            // Update visual state for safety stars
            const stars = elements.safetyRatingFilter?.querySelectorAll('.safety-star svg');
            stars?.forEach((star, index) => {
                if (index < rating) {
                    star.classList.remove('fill-gray-300');
                    star.classList.add('fill-yellow-400');
                } else {
                    star.classList.remove('fill-yellow-400');
                    star.classList.add('fill-gray-300');
                }
            });
        }

        // If we have year and make from URL, load and apply filters
        if (filterState.year && filterState.make) {
            // Load makes and models, then auto-apply filters
            setTimeout(() => {
                loadMakes(filterState.year).then(() => {
                    if (filterState.make && elements.makeSelect) {
                        elements.makeSelect.value = filterState.make;
                        loadModels(filterState.year, filterState.make).then(() => {
                            if (filterState.model && elements.modelSelect) {
                                elements.modelSelect.value = filterState.model;
                            }
                            // Auto-apply the restored filters
                            applyFilters();
                        });
                    }
                });
            }, 100);
        }
    }

    /**
     * Update URL with current filter state
     */
    function updateURLWithFilters() {
        const params = new URLSearchParams();

        // Add non-empty filters to URL
        if (filterState.year) params.set('year', filterState.year);
        if (filterState.make) params.set('make', filterState.make);
        if (filterState.model) params.set('model', filterState.model);
        if (filterState.minPrice > 0) params.set('minPrice', filterState.minPrice);
        if (filterState.maxPrice < 200000) params.set('maxPrice', filterState.maxPrice);
        if (filterState.minSafetyRating > 0) params.set('minSafetyRating', filterState.minSafetyRating);

        // Update browser URL without reloading
        const queryString = params.toString();
        const newURL = queryString ? `${window.location.pathname}?${queryString}` : window.location.pathname;
        window.history.pushState({filters: filterState}, '', newURL);
    }

    /**
     * Handle year selection change
     */
    function handleYearChange(event) {
        const year = event.target.value;
        filterState.year = year;

        // Reset make and model
        if (elements.makeSelect) {
            elements.makeSelect.innerHTML = '<option value="">Select Make</option>';
            elements.makeSelect.disabled = !year;
        }
        if (elements.modelSelect) {
            elements.modelSelect.innerHTML = '<option value="">Select Model</option>';
            elements.modelSelect.disabled = true;
        }

        if (year) {
            loadMakes(year);
        }
    }

    /**
     * Handle make selection change
     */
    function handleMakeChange(event) {
        const make = event.target.value;
        filterState.make = make;

        // Reset model
        if (elements.modelSelect) {
            elements.modelSelect.innerHTML = '<option value="">Select Model</option>';
            elements.modelSelect.disabled = !make;
        }

        if (make && filterState.year) {
            loadModels(filterState.year, make);
        }
    }

    /**
     * Handle price range changes
     */
    function handlePriceChange() {
        const minPrice = parseInt(elements.minPriceInput?.value) || 0;
        const maxPrice = parseInt(elements.maxPriceInput?.value) || 200000;

        // Validate range
        if (minPrice > maxPrice) {
            showNotification('Minimum price cannot be greater than maximum price', 'error');
            return;
        }

        filterState.minPrice = minPrice;
        filterState.maxPrice = maxPrice;

        // Auto-search if other required fields are filled
        if (filterState.year && filterState.make) {
            applyFilters();
        }
    }

    /**
     * Handle safety rating filter clicks
     */
    function handleSafetyRatingClick(event) {
        event.preventDefault();
        const rating = parseInt(event.currentTarget.dataset.rating);

        // Update visual state
        const stars = elements.safetyRatingFilter.querySelectorAll('.safety-star svg');
        stars.forEach((star, index) => {
            if (index < rating) {
                star.classList.remove('fill-gray-300');
                star.classList.add('fill-yellow-400');
            } else {
                star.classList.remove('fill-yellow-400');
                star.classList.add('fill-gray-300');
            }
        });

        filterState.minSafetyRating = rating;
    }

    /**
     * Handle form submission
     */
    function handleFormSubmit(event) {
        event.preventDefault();

        // Validate required fields
        if (!filterState.year) {
            showNotification('Please select a year', 'error');
            return;
        }

        applyFilters();
    }

    /**
     * Handle form reset
     */
    function handleFormReset(event) {
        // Reset state
        filterState.year = '';
        filterState.make = '';
        filterState.model = '';
        filterState.minPrice = 0;
        filterState.maxPrice = 200000;
        filterState.minSafetyRating = 0;

        // Reset safety stars
        const stars = elements.safetyRatingFilter?.querySelectorAll('.safety-star svg');
        stars?.forEach(star => {
            star.classList.remove('fill-yellow-400');
            star.classList.add('fill-gray-300');
        });

        // Clear results
        if (elements.vehicleGrid) {
            elements.vehicleGrid.innerHTML = '';
        }
    }

    /**
     * Handle sorting
     */
    function handleSort(event) {
        const sortBy = event.target.value;
        sortVehicles(sortBy);
    }

    /**
     * Load makes from WordPress AJAX endpoint
     */
    async function loadMakes(year) {
        if (!safequote_ajax?.ajax_url || !safequote_ajax?.nonce) return;

        showLoading(true);

        try {
            const params = new URLSearchParams({
                action: 'get_makes',
                year: year,
                nonce: safequote_ajax.nonce
            });

            const response = await fetch(`${safequote_ajax.ajax_url}?${params.toString()}`);
            const data = await response.json();

            if (data.success && data.data && elements.makeSelect) {
                elements.makeSelect.innerHTML = '<option value="">Select Make</option>';

                data.data.forEach(make => {
                    const option = document.createElement('option');
                    option.value = make.name;
                    option.textContent = make.name;
                    elements.makeSelect.appendChild(option);
                });

                elements.makeSelect.disabled = false;
            } else {
                showNotification('Failed to load makes. Please try again.', 'error');
            }
        } catch (error) {
            console.error('Error loading makes:', error);
            showNotification('Failed to load makes. Please try again.', 'error');
        } finally {
            showLoading(false);
        }
    }

    /**
     * Load models from WordPress AJAX endpoint
     */
    async function loadModels(year, make) {
        if (!safequote_ajax?.ajax_url || !safequote_ajax?.nonce) return;

        showLoading(true);

        try {
            const params = new URLSearchParams({
                action: 'get_models',
                year: year,
                make: make,
                nonce: safequote_ajax.nonce
            });

            const response = await fetch(`${safequote_ajax.ajax_url}?${params.toString()}`);
            const data = await response.json();

            if (data.success && data.data && elements.modelSelect) {
                elements.modelSelect.innerHTML = '<option value="">Select Model</option>';

                data.data.forEach(model => {
                    const option = document.createElement('option');
                    option.value = model.name;
                    option.textContent = model.name;
                    elements.modelSelect.appendChild(option);
                });

                elements.modelSelect.disabled = false;
            } else {
                showNotification('Failed to load models. Please try again.', 'error');
            }
        } catch (error) {
            console.error('Error loading models:', error);
            showNotification('Failed to load models. Please try again.', 'error');
        } finally {
            showLoading(false);
        }
    }

    /**
     * Apply filters and search for vehicles
     */
    async function applyFilters() {
        showLoading(true);

        try {
            // Build query parameters
            const params = new URLSearchParams();
            Object.keys(filterState).forEach(key => {
                if (filterState[key]) {
                    params.append(key, filterState[key]);
                }
            });

            // Update browser URL with current filters
            updateURLWithFilters();

            // Make AJAX request to WordPress
            const response = await fetch(safequote_ajax.ajax_url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=search_vehicles&nonce=${safequote_ajax.nonce}&${params.toString()}`
            });

            const data = await response.json();

            if (data.success) {
                displayVehicles(data.data.vehicles);
                updateResultsCount(data.data.count);
            } else {
                showNotification('No vehicles found matching your criteria', 'info');
            }
        } catch (error) {
            console.error('Error searching vehicles:', error);
            showNotification('Failed to search vehicles. Please try again.', 'error');
        } finally {
            showLoading(false);
        }
    }

    /**
     * Display vehicles in the grid
     */
    function displayVehicles(vehicles) {
        if (!elements.vehicleGrid) return;

        elements.vehicleGrid.innerHTML = '';

        if (!vehicles || vehicles.length === 0) {
            elements.vehicleGrid.innerHTML = `
                <div class="col-span-full text-center py-8">
                    <p class="text-gray-600">No vehicles found. Try adjusting your filters.</p>
                </div>
            `;
            return;
        }

        vehicles.forEach(vehicle => {
            const vehicleCard = createVehicleCard(vehicle);
            elements.vehicleGrid.appendChild(vehicleCard);
        });

        // Add reveal animation
        setTimeout(() => {
            const cards = elements.vehicleGrid.querySelectorAll('.vehicle-card');
            cards.forEach((card, index) => {
                setTimeout(() => {
                    card.classList.add('fade-in');
                }, index * 50);
            });
        }, 100);
    }

    /**
     * Create a vehicle card element
     */
    function createVehicleCard(vehicle) {
        const card = document.createElement('div');
        card.className = 'vehicle-card';
        card.innerHTML = `
            <div class="vehicle-card-image-container">
                <img src="${vehicle.image || '/wp-content/themes/safequote-traditional/assets/images/placeholder-vehicle.jpg'}"
                     alt="${vehicle.year} ${vehicle.make} ${vehicle.model}"
                     class="vehicle-card-image" />
                ${vehicle.featured ? '<span class="badge-featured">Featured</span>' : ''}
            </div>
            <div class="vehicle-card-body">
                <h3 class="vehicle-card-title">${vehicle.year} ${vehicle.make} ${vehicle.model}</h3>
                <p class="vehicle-card-price">$${formatPrice(vehicle.price)}</p>
                <div class="safety-rating mb-2">
                    ${generateStarRating(vehicle.safetyRating)}
                </div>
                <div class="vehicle-card-features">
                    <span class="text-sm text-gray-600">${vehicle.vehicleType || 'Sedan'}</span>
                    <span class="text-sm text-gray-600 mx-2">•</span>
                    <span class="text-sm text-gray-600">${vehicle.fuelType || 'Gasoline'}</span>
                </div>
                <div class="mt-4 flex space-x-2">
                    <button class="btn btn-primary btn-sm flex-1" onclick="viewVehicleDetails('${vehicle.id}')">
                        View Details
                    </button>
                    <button class="btn btn-outline btn-sm" onclick="addToCompare('${vehicle.id}')">
                        Compare
                    </button>
                </div>
            </div>
        `;

        return card;
    }

    /**
     * Sort vehicles
     */
    function sortVehicles(sortBy) {
        const cards = Array.from(elements.vehicleGrid?.querySelectorAll('.vehicle-card') || []);

        cards.sort((a, b) => {
            switch(sortBy) {
                case 'price-low':
                    return getPriceFromCard(a) - getPriceFromCard(b);
                case 'price-high':
                    return getPriceFromCard(b) - getPriceFromCard(a);
                case 'rating':
                    return getRatingFromCard(b) - getRatingFromCard(a);
                case 'year':
                    return getYearFromCard(b) - getYearFromCard(a);
                default:
                    return 0;
            }
        });

        // Re-append sorted cards
        elements.vehicleGrid.innerHTML = '';
        cards.forEach(card => elements.vehicleGrid.appendChild(card));
    }

    /**
     * Helper functions
     */
    function showLoading(show) {
        if (elements.loadingIndicator) {
            elements.loadingIndicator.classList.toggle('hidden', !show);
        }
    }

    function updateResultsCount(count) {
        if (elements.resultsCount) {
            elements.resultsCount.textContent = `${count} vehicles found`;
        }
    }

    function formatPrice(price) {
        return new Intl.NumberFormat('en-US').format(price);
    }

    function generateStarRating(rating) {
        let stars = '';
        for (let i = 1; i <= 5; i++) {
            stars += `<svg class="safety-star ${i <= rating ? 'safety-star-filled' : 'safety-star-empty'}" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
            </svg>`;
        }
        return stars;
    }

    function showNotification(message, type) {
        // This will be handled by notifications.js
        if (window.SafeQuoteNotifications) {
            window.SafeQuoteNotifications.show(message, type);
        } else {
            alert(message);
        }
    }

    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    function getPriceFromCard(card) {
        const priceText = card.querySelector('.vehicle-card-price')?.textContent || '$0';
        return parseInt(priceText.replace(/[^0-9]/g, ''));
    }

    function getRatingFromCard(card) {
        return card.querySelectorAll('.safety-star-filled').length;
    }

    function getYearFromCard(card) {
        const title = card.querySelector('.vehicle-card-title')?.textContent || '';
        const year = title.match(/^\d{4}/);
        return year ? parseInt(year[0]) : 0;
    }

    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Handle browser back/forward buttons
    window.addEventListener('popstate', function(event) {
        if (event.state && event.state.filters) {
            // Restore filter state from history
            Object.assign(filterState, event.state.filters);

            // Update form fields
            if (elements.yearSelect) elements.yearSelect.value = filterState.year || '';
            if (elements.makeSelect) elements.makeSelect.value = filterState.make || '';
            if (elements.modelSelect) elements.modelSelect.value = filterState.model || '';
            if (elements.minPriceInput) elements.minPriceInput.value = filterState.minPrice || 0;
            if (elements.maxPriceInput) elements.maxPriceInput.value = filterState.maxPrice || 200000;

            // Update safety rating stars
            const stars = elements.safetyRatingFilter?.querySelectorAll('.safety-star svg');
            const rating = filterState.minSafetyRating || 0;
            stars?.forEach((star, index) => {
                if (index < rating) {
                    star.classList.remove('fill-gray-300');
                    star.classList.add('fill-yellow-400');
                } else {
                    star.classList.remove('fill-yellow-400');
                    star.classList.add('fill-gray-300');
                }
            });

            // Re-apply filters to show results
            if (filterState.year && filterState.make) {
                applyFilters();
            }
        }
    });

    // Expose API
    window.SafeQuoteFilters = {
        init: init,
        applyFilters: applyFilters,
        reset: handleFormReset,
        updateURL: updateURLWithFilters
    };

})();