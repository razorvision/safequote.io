/**
 * Safety Ratings Page Functionality
 * Handles NHTSA API integration and results display
 * Exact match to React SafetyRatings.jsx implementation
 */

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('safety-ratings-form');
    const yearInput = document.getElementById('safety-year');
    const makeInput = document.getElementById('safety-make');
    const modelInput = document.getElementById('safety-model');
    const submitBtn = form ? form.querySelector('button[type="submit"]') : null;
    const buttonText = document.getElementById('button-text');
    const resultsContainer = document.getElementById('safety-ratings-results');

    if (!form) return;

    // Form submission handler
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        await fetchSafetyRatings();
    });

    /**
     * Check if a value is properly rated (not "Not Rated", not empty, not 0)
     * Matches React isRated() function
     */
    function isRated(value) {
        return value && value !== 'Not Rated' && value !== '' && value !== 0;
    }

    /**
     * Fetch safety ratings from WordPress AJAX endpoint
     * Queries local database directly - returns ALL matching variants
     */
    async function fetchSafetyRatings() {
        const year = yearInput.value.trim();
        const make = makeInput.value.trim();
        const model = modelInput.value.trim();

        // Validation
        if (!year || !make || !model) {
            showError('Please fill in all fields');
            return;
        }

        // Show loading state
        setLoading(true);
        resultsContainer.classList.add('hidden');

        try {
            // Call WordPress AJAX endpoint - returns array of all matching vehicles
            const vehicles = await fetchRatingFromAjax(year, make, model);

            // Handle array of results (or empty array)
            if (!vehicles || !Array.isArray(vehicles) || vehicles.length === 0) {
                showError(`No safety ratings found for ${year} ${make} ${model}`);
                setLoading(false);
                return;
            }

            // Render all matching vehicles
            renderResults(vehicles);
            setLoading(false);
        } catch (error) {
            console.error('Error fetching safety ratings:', error);
            showError('Failed to fetch safety ratings. Please try again.');
            setLoading(false);
        }
    }

    /**
     * Fetch vehicle rating from WordPress AJAX endpoint
     *
     * Uses the server-side multi-tier cache through WordPress transients,
     * database table, and live NHTSA API.
     *
     * @param {number} year  Vehicle year
     * @param {string} make  Vehicle make
     * @param {string} model Vehicle model
     * @return {Promise}     Promise resolving to rating data
     */
    async function fetchRatingFromAjax(year, make, model) {
        try {
            // Build AJAX URL with parameters
            const url = new URL(safequote_ajax.ajax_url, window.location.origin);
            url.searchParams.set('action', 'get_nhtsa_rating');
            url.searchParams.set('year', year);
            url.searchParams.set('make', make);
            url.searchParams.set('model', model);
            url.searchParams.set('nonce', safequote_ajax.nonce);

            const response = await fetch(url.toString());
            if (!response.ok) throw new Error(`HTTP ${response.status}`);

            const data = await response.json();

            // WordPress AJAX returns { success: true/false, data: ... }
            if (data.success && data.data) {
                return data.data;
            }
            return null;
        } catch (error) {
            console.error(`AJAX error for ${year} ${make} ${model}:`, error);
            throw error;
        }
    }

    /**
     * Render rating row with automatic star detection
     * Matches React renderRatingRow() function
     */
    function renderRatingRow(label, value) {
        if (!isRated(value)) return '';

        // Check if it's a star rating (numeric 1-5)
        const isStarRating = /^[1-5]$/.test(value?.toString());

        if (isStarRating) {
            return `
                <div class="flex justify-between items-center">
                    <p class="text-sm md:text-base">${escapeHtml(label)}:</p>
                    <div class="flex gap-1">${renderStars(value)}</div>
                </div>
            `;
        } else {
            return `
                <div class="flex justify-between items-center">
                    <p class="text-sm md:text-base">${escapeHtml(label)}:</p>
                    <span class="text-sm font-medium text-green-600">${escapeHtml(value.toString())}</span>
                </div>
            `;
        }
    }

    /**
     * Render safety ratings results
     * Handles array of vehicles - renders each one
     */
    function renderResults(vehiclesArray) {
        // Ensure we have an array
        const vehicles = Array.isArray(vehiclesArray) ? vehiclesArray : [vehiclesArray];

        // Results count header
        let html = `
            <div class="max-w-3xl mx-auto mt-8 space-y-6">
                <p class="text-gray-600 text-center mb-4">${vehicles.length} vehicle${vehicles.length > 1 ? 's' : ''} found</p>
        `;

        // Render each vehicle
        vehicles.forEach(ratings => {
            html += renderSingleVehicle(ratings);
        });

        html += `</div>`;

        resultsContainer.innerHTML = html;
        resultsContainer.classList.remove('hidden');

        // Scroll to results
        setTimeout(() => {
            resultsContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }, 100);
    }

    /**
     * Render a single vehicle card
     */
    function renderSingleVehicle(ratings) {
        let html = `
            <div class="bg-gradient-to-br from-primary/5 to-secondary/20 rounded-xl shadow-md p-6 border border-primary/20 animate-fade-in">
                <!-- Vehicle Header -->
                <div class="flex flex-col md:flex-row gap-6 mb-8 pb-8 border-b border-primary/20">
        `;

        // Vehicle picture - ONLY show if exists (no placeholder)
        if (ratings.VehiclePicture) {
            html += `
                    <div class="flex-shrink-0">
                        <img src="${ratings.VehiclePicture}"
                             alt="${ratings.VehicleDescription || ''}"
                             class="w-32 h-32 object-cover rounded-lg" />
                    </div>
            `;
        }

        // Vehicle info
        html += `
                    <div class="flex-grow">
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">
                            ${ratings.VehicleDescription || `${ratings.ModelYear} ${ratings.Make} ${ratings.Model}`}
                        </h3>
                        <div class="flex items-center gap-2 mb-2">
                            <span class="text-lg font-semibold text-gray-700">Overall Rating:</span>
                            <div class="flex gap-1">
                                ${renderStars(ratings.OverallRating || 0)}
                            </div>
                            ${isRated(ratings.OverallRating) ? `<span class="text-lg font-bold text-primary">${ratings.OverallRating}</span>` : '<span class="text-lg font-bold text-gray-500">Not Rated</span>'}
                        </div>
                        <p class="text-gray-600">
                            ${ratings.OverallRating >= 4 ? '<svg class="w-4 h-4 text-green-600 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>Highly recommended for safety' : 'Review detailed ratings below'}
                        </p>
                    </div>
                </div>
        `;

        // Safety Ratings Section
        html += `
                <div class="mb-6">
                    <h4 class="text-lg font-bold text-gray-900 mb-3">Safety Ratings</h4>
                    <div class="space-y-3 bg-white rounded-lg p-4">
        `;

        html += renderRatingRow('Overall Rating', ratings.OverallRating);
        html += renderRatingRow('Front Crash Rating', ratings.OverallFrontCrashRating);
        html += renderRatingRow('Side Crash Rating', ratings.OverallSideCrashRating);
        html += renderRatingRow('Rollover Rating', ratings.RolloverRating);

        html += `
                    </div>
                </div>
        `;

        // Detailed Crash Ratings Section
        if (isRated(ratings.FrontCrashDriversideRating) ||
            isRated(ratings.FrontCrashPassengersideRating) ||
            isRated(ratings.SideCrashDriversideRating) ||
            isRated(ratings.SideCrashPassengersideRating) ||
            isRated(ratings.SidePoleCrashRating)) {

            html += `
                <div class="mb-6">
                    <h4 class="text-lg font-bold text-gray-900 mb-3">Detailed Crash Test Results</h4>
                    <div class="space-y-3 bg-white rounded-lg p-4">
            `;

            html += renderRatingRow('Front Crash - Driver Side', ratings.FrontCrashDriversideRating);
            html += renderRatingRow('Front Crash - Passenger Side', ratings.FrontCrashPassengersideRating);
            html += renderRatingRow('Side Crash - Driver Side', ratings.SideCrashDriversideRating);
            html += renderRatingRow('Side Crash - Passenger Side', ratings.SideCrashPassengersideRating);
            html += renderRatingRow('Side Pole Crash Rating', ratings.SidePoleCrashRating);

            if (isRated(ratings['combinedSideBarrierAndPoleRating-Front'])) {
                html += renderRatingRow('Side Barrier - Front', ratings['combinedSideBarrierAndPoleRating-Front']);
            }
            if (isRated(ratings['combinedSideBarrierAndPoleRating-Rear'])) {
                html += renderRatingRow('Side Barrier - Rear', ratings['combinedSideBarrierAndPoleRating-Rear']);
            }

            html += `
                    </div>
                </div>
            `;
        }

        // Rollover Details Section
        if (isRated(ratings.RolloverRating2) ||
            isRated(ratings.RolloverPossibility) ||
            isRated(ratings.RolloverPossibility2) ||
            isRated(ratings.dynamicTipResult)) {

            html += `
                <div class="mb-6">
                    <h4 class="text-lg font-bold text-gray-900 mb-3">Rollover Details</h4>
                    <div class="space-y-3 bg-white rounded-lg p-4">
            `;

            html += renderRatingRow('Rollover Rating (Alternative)', ratings.RolloverRating2);

            if (isRated(ratings.RolloverPossibility)) {
                const rolloverPercent = (ratings.RolloverPossibility * 100).toFixed(1);
                html += `
                    <div class="flex justify-between items-center">
                        <p class="text-sm md:text-base">Rollover Possibility:</p>
                        <span class="text-sm font-medium text-green-600">${rolloverPercent}%</span>
                    </div>
                `;
            }

            if (isRated(ratings.RolloverPossibility2)) {
                const rolloverPercent2 = (ratings.RolloverPossibility2 * 100).toFixed(1);
                html += `
                    <div class="flex justify-between items-center">
                        <p class="text-sm md:text-base">Rollover Possibility (Alt):</p>
                        <span class="text-sm font-medium text-green-600">${rolloverPercent2}%</span>
                    </div>
                `;
            }

            html += renderRatingRow('Dynamic Tip Result', ratings.dynamicTipResult);

            html += `
                    </div>
                </div>
            `;
        }

        // Safety Features Section
        if (isRated(ratings.NHTSAElectronicStabilityControl) ||
            isRated(ratings.NHTSAForwardCollisionWarning) ||
            isRated(ratings.NHTSALaneDepartureWarning)) {

            html += `
                <div class="mb-6">
                    <h4 class="text-lg font-bold text-gray-900 mb-3">Safety Features</h4>
                    <div class="space-y-2 bg-white rounded-lg p-4">
            `;

            if (isRated(ratings.NHTSAElectronicStabilityControl)) {
                html += `
                    <div class="flex justify-between items-center">
                        <p class="text-sm md:text-base">Electronic Stability Control:</p>
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                `;
            }

            if (isRated(ratings.NHTSAForwardCollisionWarning)) {
                const isStandard = ratings.NHTSAForwardCollisionWarning === 'Standard';
                const color = isStandard ? 'text-green-600' : 'text-gray-400';
                html += `
                    <div class="flex justify-between items-center">
                        <p class="text-sm md:text-base">Forward Collision Warning:</p>
                        <span class="${color} text-sm">${escapeHtml(ratings.NHTSAForwardCollisionWarning)}</span>
                    </div>
                `;
            }

            if (isRated(ratings.NHTSALaneDepartureWarning)) {
                const isStandard = ratings.NHTSALaneDepartureWarning === 'Standard';
                const color = isStandard ? 'text-green-600' : 'text-gray-400';
                html += `
                    <div class="flex justify-between items-center">
                        <p class="text-sm md:text-base">Lane Departure Warning:</p>
                        <span class="${color} text-sm">${escapeHtml(ratings.NHTSALaneDepartureWarning)}</span>
                    </div>
                `;
            }

            html += `
                    </div>
                </div>
            `;
        }

        // Complaints, Recalls, and Investigations Alerts (combined in single container)
        if ((ratings.ComplaintsCount && ratings.ComplaintsCount > 0) ||
            (ratings.RecallsCount && ratings.RecallsCount > 0) ||
            (ratings.InvestigationCount && ratings.InvestigationCount > 0)) {

            html += `
                <div class="mt-6 pt-4 border-t border-primary/20 space-y-2">
            `;

            if (ratings.ComplaintsCount && ratings.ComplaintsCount > 0) {
                html += `
                    <div class="flex items-start gap-2">
                        <svg class="w-4 h-4 text-orange-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4v.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-semibold text-gray-900">${ratings.ComplaintsCount} Complaints Found</p>
                            <p class="text-xs text-gray-600">Review complaints and safety issues reported by owners at NHTSA.gov</p>
                        </div>
                    </div>
                `;
            }

            if (ratings.RecallsCount && ratings.RecallsCount > 0) {
                html += `
                    <div class="flex items-start gap-2">
                        <svg class="w-4 h-4 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4v.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-semibold text-gray-900">${ratings.RecallsCount} Recalls Found</p>
                            <p class="text-xs text-gray-600">This vehicle has active recalls. Check NHTSA.gov for details.</p>
                        </div>
                    </div>
                `;
            }

            if (ratings.InvestigationCount && ratings.InvestigationCount > 0) {
                html += `
                    <div class="flex items-start gap-2">
                        <svg class="w-4 h-4 text-yellow-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4v.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-semibold text-gray-900">${ratings.InvestigationCount} Investigation(s) Ongoing</p>
                            <p class="text-xs text-gray-600">There are ongoing investigations for this vehicle. Check NHTSA.gov for more information.</p>
                        </div>
                    </div>
                `;
            }

            html += `
                </div>
            `;
        }

        html += `</div>`;
        return html;
    }

    /**
     * Render star rating - Yellow stars matching React using SVG icons
     */
    function renderStars(rating) {
        if (!isRated(rating)) {
            return '<span class="text-sm text-gray-500">Not Rated</span>';
        }

        let stars = '';
        const numStars = parseInt(rating, 10);
        const maxStars = 5;

        for (let i = 1; i <= maxStars; i++) {
            if (i <= numStars) {
                stars += '<svg class="w-5 h-5 text-yellow-400 fill-yellow-400 inline" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path></svg>';
            } else {
                stars += '<svg class="w-5 h-5 text-gray-300 inline" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path></svg>';
            }
        }

        return stars;
    }

    /**
     * Show error message
     */
    function showError(message) {
        resultsContainer.innerHTML = `
            <div class="bg-red-50 border border-red-200 rounded-lg p-6 text-center animate-fade-in">
                <p class="text-red-800 font-medium">${escapeHtml(message)}</p>
            </div>
        `;
        resultsContainer.classList.remove('hidden');
    }

    /**
     * Set loading state
     */
    function setLoading(isLoading) {
        if (isLoading) {
            submitBtn.disabled = true;
            submitBtn.classList.add('disabled:opacity-50');
            buttonText.innerHTML = '<svg class="inline-block w-4 h-4 animate-spin mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Checking...';
        } else {
            submitBtn.disabled = false;
            submitBtn.classList.remove('disabled:opacity-50');
            buttonText.textContent = 'Check Rating';
        }
    }

    /**
     * Escape HTML special characters
     */
    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    }
});
