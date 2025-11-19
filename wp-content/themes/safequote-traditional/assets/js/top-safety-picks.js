/**
 * Top Safety Picks - NHTSA Rating Integration
 *
 * Fetches real NHTSA safety ratings for top-rated vehicles and displays them with badges.
 * Uses 3-tier caching: Transient → Database → Live API → Stale fallback
 *
 * @since 1.0.0
 */

(function() {
  'use strict';

  // Wait for DOM ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initTopSafetyPicks);
  } else {
    initTopSafetyPicks();
  }

  /**
   * Initialize top safety picks enhancement
   *
   * @return {void}
   */
  function initTopSafetyPicks() {
    const grid = document.getElementById('top-safety-picks-grid');

    if (!grid) {
      console.log('[Top Safety Picks] Grid not found, skipping');
      return;
    }

    console.log('[Top Safety Picks] Initializing');

    const vehicles = getVehiclesFromGrid();

    if (vehicles.length === 0) {
      console.warn('[Top Safety Picks] No vehicles found');
      return;
    }

    // Fetch NHTSA data for each vehicle
    vehicles.forEach((vehicle, index) => {
      fetchAndDisplayRating(vehicle, index);
    });
  }

  /**
   * Get vehicle data from grid buttons
   *
   * @return {Array} Array of vehicle objects
   */
  function getVehiclesFromGrid() {
    const buttons = document.querySelectorAll('.top-safety-pick-vehicle');
    const vehicles = [];

    buttons.forEach((button) => {
      vehicles.push({
        id: button.dataset.vehicleId,
        make: button.dataset.vehicleMake,
        model: button.dataset.vehicleModel,
        year: parseInt(button.dataset.vehicleYear, 10),
        element: button,
      });
    });

    return vehicles;
  }

  /**
   * Fetch and display NHTSA rating for a vehicle
   *
   * @param {Object} vehicle Vehicle data object
   * @param {number} index    Vehicle index
   * @return {void}
   */
  function fetchAndDisplayRating(vehicle, index) {
    const ratingContainer = vehicle.element.querySelector('.nhtsa-rating-container');
    const badgeContainer = vehicle.element.querySelector('.nhtsa-badge-container');

    if (!ratingContainer) {
      console.warn('[Top Safety Picks] Rating container not found for', vehicle.model);
      return;
    }

    // Fetch from server (which uses our multi-tier cache)
    fetchRating(vehicle.year, vehicle.make, vehicle.model)
      .then((rating) => {
        if (rating && rating.overall_rating) {
          // Display star rating
          displayStarRating(ratingContainer, rating.overall_rating);

          // Show NHTSA badge
          if (badgeContainer) {
            badgeContainer.style.opacity = '1';
          }

          console.log(
            `[Top Safety Picks] ✓ ${vehicle.year} ${vehicle.make} ${vehicle.model}: ${rating.overall_rating}/5.0`
          );
        } else {
          // No NHTSA rating available
          displayNoRating(ratingContainer);
          console.log(
            `[Top Safety Picks] ⚠ No NHTSA rating: ${vehicle.year} ${vehicle.make} ${vehicle.model}`
          );
        }
      })
      .catch((error) => {
        console.error('[Top Safety Picks] Error fetching rating:', error);
        displayErrorState(ratingContainer);
      });
  }

  /**
   * Fetch vehicle rating from server
   *
   * Uses the server-side multi-tier cache through WordPress transients,
   * database table, and live NHTSA API.
   *
   * @param {number} year  Vehicle year
   * @param {string} make  Vehicle make
   * @param {string} model Vehicle model
   * @return {Promise}     Promise resolving to rating data
   */
  function fetchRating(year, make, model) {
    // For now, return a promise that resolves to null
    // In Phase 3, we'll add an AJAX endpoint to fetch from server cache

    return new Promise((resolve) => {
      // TODO: Add AJAX call to /wp-admin/admin-ajax.php?action=safequote_get_nhtsa_rating
      // This will use the server-side multi-tier cache:
      // 1. Check Transients (24h)
      // 2. Check Database (7d)
      // 3. Fetch live from NHTSA
      // 4. Fall back to stale cache

      // For now, resolve with null to show "Not Rated"
      resolve(null);
    });
  }

  /**
   * Display star rating in container
   *
   * @param {Element} container Rating container element
   * @param {number}  rating    Safety rating (0-5)
   * @return {void}
   */
  function displayStarRating(container, rating) {
    const stars = Math.round(rating);
    const starHTML = createStarRating(stars);

    container.innerHTML = `
      <div class="flex items-center justify-center gap-1">
        ${starHTML}
        <span class="text-xs font-semibold text-gray-700 ml-1">${rating.toFixed(1)}</span>
      </div>
    `;
  }

  /**
   * Create star rating HTML
   *
   * @param {number} count Number of stars to display (1-5)
   * @return {string}      HTML markup for stars
   */
  function createStarRating(count) {
    let html = '';

    for (let i = 0; i < 5; i++) {
      if (i < count) {
        html += '<svg class="w-4 h-4 text-yellow-400 fill-current" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>';
      } else {
        html += '<svg class="w-4 h-4 text-gray-300 fill-current" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>';
      }
    }

    return html;
  }

  /**
   * Display "Not Rated" message
   *
   * @param {Element} container Rating container element
   * @return {void}
   */
  function displayNoRating(container) {
    container.innerHTML = '<div class="text-xs text-gray-500">Not rated</div>';
  }

  /**
   * Display error state
   *
   * @param {Element} container Rating container element
   * @return {void}
   */
  function displayErrorState(container) {
    container.innerHTML = '<div class="text-xs text-gray-400">—</div>';
  }
})();
