/**
 * Insurance Comparison Module
 * Handles insurance quote functionality and comparisons
 */

(function() {
    'use strict';

    // Insurance providers data
    const providers = [
        { id: 'geico', name: 'GEICO', logo: '/assets/images/geico-logo.png' },
        { id: 'progressive', name: 'Progressive', logo: '/assets/images/progressive-logo.png' },
        { id: 'statefarm', name: 'State Farm', logo: '/assets/images/statefarm-logo.png' },
        { id: 'allstate', name: 'Allstate', logo: '/assets/images/allstate-logo.png' },
        { id: 'nationwide', name: 'Nationwide', logo: '/assets/images/nationwide-logo.png' },
        { id: 'usaa', name: 'USAA', logo: '/assets/images/usaa-logo.png' }
    ];

    // State for comparison
    const comparisonState = {
        selectedVehicles: [],
        userProfile: {},
        quotes: []
    };

    /**
     * Initialize insurance module
     */
    function init() {
        bindEvents();
        loadSavedComparisons();
    }

    /**
     * Bind event listeners
     */
    function bindEvents() {
        // Compare button on front page
        const compareBtn = document.getElementById('compare-vehicles');
        if (compareBtn) {
            compareBtn.addEventListener('click', handleCompareClick);
        }

        // Insurance form submission
        const insuranceForm = document.getElementById('insurance-quote-form');
        if (insuranceForm) {
            insuranceForm.addEventListener('submit', handleQuoteFormSubmit);
        }
    }

    /**
     * Calculate insurance quote
     */
    function calculateQuote(vehicle, userProfile, provider) {
        // Base rate calculation
        let baseRate = getBaseRate(vehicle);

        // Apply user factors
        baseRate = applyUserFactors(baseRate, userProfile);

        // Apply provider-specific adjustments
        baseRate = applyProviderAdjustments(baseRate, provider);

        // Apply safety rating discount
        baseRate = applySafetyDiscount(baseRate, vehicle.safetyRating);

        // Apply coverage level
        baseRate = applyCoverageLevel(baseRate, userProfile.coverage);

        return {
            monthly: Math.round(baseRate / 12),
            annual: Math.round(baseRate),
            provider: provider,
            discounts: getAppliedDiscounts(vehicle, userProfile, provider),
            coverage: getCoverageDetails(userProfile.coverage)
        };
    }

    /**
     * Get base insurance rate for vehicle
     */
    function getBaseRate(vehicle) {
        const vehicleValue = vehicle.price || 30000;
        const vehicleAge = new Date().getFullYear() - vehicle.year;

        // Base rate is approximately 3-5% of vehicle value annually
        let baseRate = vehicleValue * 0.04;

        // Adjust for vehicle age
        if (vehicleAge < 1) {
            baseRate *= 1.2; // New cars cost more to insure
        } else if (vehicleAge > 5) {
            baseRate *= 0.9; // Older cars cost less
        }

        // Adjust for vehicle type
        const highRiskTypes = ['sports car', 'luxury', 'convertible'];
        if (vehicle.vehicleType && highRiskTypes.includes(vehicle.vehicleType.toLowerCase())) {
            baseRate *= 1.3;
        }

        return baseRate;
    }

    /**
     * Apply user-specific factors
     */
    function applyUserFactors(rate, profile) {
        let adjustedRate = rate;

        // Age factor
        if (profile.age) {
            if (profile.age < 25) {
                adjustedRate *= 1.5;
            } else if (profile.age > 65) {
                adjustedRate *= 1.1;
            } else if (profile.age >= 30 && profile.age <= 50) {
                adjustedRate *= 0.9;
            }
        }

        // Driving experience
        if (profile.experience) {
            if (profile.experience < 2) {
                adjustedRate *= 1.3;
            } else if (profile.experience > 10) {
                adjustedRate *= 0.85;
            }
        }

        // Location (simplified)
        if (profile.zipCode) {
            // Urban areas typically have higher rates
            adjustedRate *= getLocationMultiplier(profile.zipCode);
        }

        // Driving record
        if (profile.hasAccidents) {
            adjustedRate *= 1.4;
        }
        if (profile.hasTickets) {
            adjustedRate *= 1.2;
        }

        // Credit score (simplified)
        if (profile.creditScore) {
            if (profile.creditScore > 750) {
                adjustedRate *= 0.9;
            } else if (profile.creditScore < 600) {
                adjustedRate *= 1.2;
            }
        }

        return adjustedRate;
    }

    /**
     * Apply provider-specific adjustments
     */
    function applyProviderAdjustments(rate, provider) {
        const adjustments = {
            'geico': 0.95,
            'progressive': 0.97,
            'statefarm': 1.0,
            'allstate': 1.02,
            'nationwide': 0.98,
            'usaa': 0.92 // Military discount
        };

        return rate * (adjustments[provider.id] || 1.0);
    }

    /**
     * Apply safety rating discount
     */
    function applySafetyDiscount(rate, safetyRating) {
        if (!safetyRating) return rate;

        // 3% discount per safety star above 3
        if (safetyRating > 3) {
            const discount = (safetyRating - 3) * 0.03;
            return rate * (1 - discount);
        }

        // Surcharge for low safety ratings
        if (safetyRating < 3) {
            return rate * 1.1;
        }

        return rate;
    }

    /**
     * Apply coverage level multiplier
     */
    function applyCoverageLevel(rate, coverage) {
        const multipliers = {
            'liability': 0.6,
            'collision': 0.85,
            'comprehensive': 1.0,
            'full': 1.2
        };

        return rate * (multipliers[coverage] || 1.0);
    }

    /**
     * Get location multiplier based on zip code
     */
    function getLocationMultiplier(zipCode) {
        // Simplified urban/rural detection
        const urbanZips = ['10001', '90001', '60601', '77001', '85001'];
        const firstFive = zipCode.substring(0, 5);

        if (urbanZips.includes(firstFive)) {
            return 1.15;
        }

        return 1.0;
    }

    /**
     * Get applied discounts list
     */
    function getAppliedDiscounts(vehicle, profile, provider) {
        const discounts = [];

        if (vehicle.safetyRating >= 4) {
            discounts.push('Safe Vehicle Discount');
        }

        if (profile.multiPolicy) {
            discounts.push('Multi-Policy Discount');
        }

        if (profile.goodStudent) {
            discounts.push('Good Student Discount');
        }

        if (profile.militaryService && provider.id === 'usaa') {
            discounts.push('Military Service Discount');
        }

        if (profile.experience > 10) {
            discounts.push('Safe Driver Discount');
        }

        return discounts;
    }

    /**
     * Get coverage details
     */
    function getCoverageDetails(coverageType) {
        const details = {
            'liability': {
                name: 'Liability Only',
                includes: ['Bodily Injury Liability', 'Property Damage Liability'],
                deductible: '$1,000'
            },
            'collision': {
                name: 'Collision Coverage',
                includes: ['Liability', 'Collision'],
                deductible: '$500'
            },
            'comprehensive': {
                name: 'Comprehensive Coverage',
                includes: ['Liability', 'Collision', 'Comprehensive', 'Uninsured Motorist'],
                deductible: '$500'
            },
            'full': {
                name: 'Full Coverage',
                includes: ['Liability', 'Collision', 'Comprehensive', 'Uninsured Motorist', 'Medical Payments', 'Rental Car', 'Roadside Assistance'],
                deductible: '$250'
            }
        };

        return details[coverageType] || details['comprehensive'];
    }

    /**
     * Handle compare button click
     */
    function handleCompareClick(event) {
        event.preventDefault();

        if (comparisonState.selectedVehicles.length === 0) {
            showNotification('Please select at least one vehicle to compare', 'warning');
            return;
        }

        generateComparison();
    }

    /**
     * Generate insurance comparison
     */
    async function generateComparison() {
        const comparisonTable = document.getElementById('insurance-comparison-table');
        if (!comparisonTable) return;

        // Show loading
        comparisonTable.innerHTML = '<tr><td colspan="5" class="text-center py-8">Calculating quotes...</td></tr>';

        // Generate quotes for each provider
        const quotes = [];
        for (const vehicle of comparisonState.selectedVehicles) {
            for (const provider of providers) {
                const quote = calculateQuote(vehicle, comparisonState.userProfile, provider);
                quotes.push({
                    ...quote,
                    vehicle: vehicle
                });
            }
        }

        // Sort by monthly premium
        quotes.sort((a, b) => a.monthly - b.monthly);

        // Display comparison
        displayComparison(quotes);
    }

    /**
     * Display insurance comparison
     */
    function displayComparison(quotes) {
        const comparisonTable = document.getElementById('insurance-comparison-table');
        if (!comparisonTable) return;

        let html = '';

        quotes.forEach(quote => {
            html += `
                <tr>
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <div>
                                <div class="text-sm font-medium text-gray-900">${quote.provider.name}</div>
                                <div class="text-sm text-gray-500">${quote.vehicle.year} ${quote.vehicle.make} ${quote.vehicle.model}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <div class="text-lg font-semibold text-green-600">$${quote.monthly}/mo</div>
                        <div class="text-sm text-gray-500">$${quote.annual}/year</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm">${quote.coverage.name}</div>
                        <div class="text-xs text-gray-500">Deductible: ${quote.coverage.deductible}</div>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <div class="flex justify-center">
                            ${generateStarRating(4)}
                        </div>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <button onclick="selectQuote('${quote.provider.id}')" class="btn btn-primary btn-sm">
                            Get Quote
                        </button>
                    </td>
                </tr>
            `;
        });

        comparisonTable.innerHTML = html;

        // Save quotes to state
        comparisonState.quotes = quotes;
    }

    /**
     * Handle insurance quote form submission
     */
    async function handleQuoteFormSubmit(event) {
        event.preventDefault();

        const formData = new FormData(event.target);
        const profile = Object.fromEntries(formData);

        // Calculate age from date of birth
        if (profile.dob) {
            const dob = new Date(profile.dob);
            const ageDiff = Date.now() - dob.getTime();
            profile.age = Math.floor(ageDiff / (1000 * 60 * 60 * 24 * 365.25));
        }

        // Update user profile
        comparisonState.userProfile = profile;

        // Save to localStorage
        saveUserProfile(profile);

        // Generate new comparison with updated profile
        await generateComparison();

        // Show success message
        showNotification('Your profile has been updated. Quotes have been recalculated.', 'success');
    }

    /**
     * Add vehicle to comparison
     */
    function addVehicleToComparison(vehicle) {
        // Check if already added
        const exists = comparisonState.selectedVehicles.find(v => v.id === vehicle.id);
        if (exists) {
            showNotification('This vehicle is already in your comparison list', 'info');
            return;
        }

        // Add to list
        comparisonState.selectedVehicles.push(vehicle);

        // Update UI
        updateComparisonUI();

        // Save to localStorage
        saveComparison();

        showNotification('Vehicle added to comparison', 'success');
    }

    /**
     * Remove vehicle from comparison
     */
    function removeVehicleFromComparison(vehicleId) {
        comparisonState.selectedVehicles = comparisonState.selectedVehicles.filter(v => v.id !== vehicleId);

        // Update UI
        updateComparisonUI();

        // Save to localStorage
        saveComparison();

        showNotification('Vehicle removed from comparison', 'success');
    }

    /**
     * Clear comparison
     */
    function clearComparison() {
        comparisonState.selectedVehicles = [];
        comparisonState.quotes = [];

        // Update UI
        updateComparisonUI();

        // Clear localStorage
        localStorage.removeItem('safequote_comparison');

        showNotification('Comparison cleared', 'success');
    }

    /**
     * Update comparison UI
     */
    function updateComparisonUI() {
        const count = comparisonState.selectedVehicles.length;

        // Update badge
        const badge = document.getElementById('comparison-badge');
        if (badge) {
            badge.textContent = count;
            badge.style.display = count > 0 ? 'block' : 'none';
        }

        // Update comparison list
        const list = document.getElementById('selected-vehicles-list');
        if (list) {
            if (count === 0) {
                list.innerHTML = '<p class="text-gray-500">No vehicles selected for comparison</p>';
            } else {
                let html = '<div class="space-y-2">';
                comparisonState.selectedVehicles.forEach(vehicle => {
                    html += `
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded">
                            <span>${vehicle.year} ${vehicle.make} ${vehicle.model}</span>
                            <button onclick="removeVehicleFromComparison('${vehicle.id}')" class="text-red-600 hover:text-red-800">
                                Remove
                            </button>
                        </div>
                    `;
                });
                html += '</div>';
                list.innerHTML = html;
            }
        }
    }

    /**
     * Save comparison to localStorage
     */
    function saveComparison() {
        localStorage.setItem('safequote_comparison', JSON.stringify(comparisonState));
    }

    /**
     * Load saved comparisons
     */
    function loadSavedComparisons() {
        const saved = localStorage.getItem('safequote_comparison');
        if (saved) {
            try {
                const data = JSON.parse(saved);
                comparisonState.selectedVehicles = data.selectedVehicles || [];
                comparisonState.userProfile = data.userProfile || {};
                updateComparisonUI();
            } catch (error) {
                console.error('Error loading saved comparison:', error);
            }
        }
    }

    /**
     * Save user profile
     */
    function saveUserProfile(profile) {
        localStorage.setItem('safequote_user_profile', JSON.stringify(profile));
    }

    /**
     * Generate star rating HTML
     */
    function generateStarRating(rating) {
        let stars = '';
        for (let i = 1; i <= 5; i++) {
            stars += `<svg class="w-4 h-4 ${i <= rating ? 'text-yellow-400' : 'text-gray-300'} fill-current inline-block" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
            </svg>`;
        }
        return stars;
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
    window.SafeQuoteInsurance = {
        init: init,
        calculateQuote: calculateQuote,
        addVehicleToComparison: addVehicleToComparison,
        removeVehicleFromComparison: removeVehicleFromComparison,
        clearComparison: clearComparison,
        generateComparison: generateComparison
    };

    // Global helper function
    window.selectQuote = function(providerId) {
        const provider = providers.find(p => p.id === providerId);
        if (provider) {
            window.location.href = `/insurance-quote?provider=${providerId}`;
        }
    };

})();