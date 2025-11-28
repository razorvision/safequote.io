<?php
/**
 * Template part: Search Filters
 *
 * Displays vehicle search filters with year, make, model, condition, type, and safety rating
 *
 * @package SafeQuote_Traditional
 * @since 1.0.0
 *
 * @param array $filters {
 *     @type string $year              Vehicle year filter
 *     @type string $make              Vehicle make filter
 *     @type string $model             Vehicle model filter
 *     @type string $condition         Current condition filter (all|new|preowned)
 *     @type string $vehicleType       Current vehicle type filter
 *     @type int    $minSafetyRating   Minimum safety rating (0-5)
 * }
 */

$filters = isset( $filters ) ? $filters : array(
	'year'             => '',
	'make'             => '',
	'model'            => '',
	'minSafetyRating'  => 0,
);
?>

<div id="search-filters" class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100 fade-in">
	<!-- Header -->
	<div class="flex items-center gap-2 mb-6">
		<svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
			<polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"></polygon>
		</svg>
		<h3 class="text-xl font-semibold text-gray-900">
			<?php esc_html_e( 'Refine Your Search', 'safequote-traditional' ); ?>
		</h3>
	</div>

	<!-- Filters Grid -->
	<form id="vehicle-filters-form" class="grid md:grid-cols-2 lg:grid-cols-2 gap-6">
		<!-- Year Filter -->
		<div class="space-y-2">
			<label for="filter-year" class="form-label">
				<?php esc_html_e( 'Year', 'safequote-traditional' ); ?>
			</label>
			<select id="filter-year" name="year" class="form-select" data-filter="year">
				<option value="">
					<?php esc_html_e( 'All Years', 'safequote-traditional' ); ?>
				</option>
			</select>
		</div>

		<!-- Make Filter -->
		<div class="space-y-2">
			<label for="filter-make" class="form-label">
				<?php esc_html_e( 'Make', 'safequote-traditional' ); ?>
			</label>
			<select id="filter-make" name="make" class="form-select" data-filter="make">
				<option value="">
					<?php esc_html_e( 'Select Make', 'safequote-traditional' ); ?>
				</option>
			</select>
		</div>

		<!-- Model Filter -->
		<div class="space-y-2">
			<label for="filter-model" class="form-label">
				<?php esc_html_e( 'Model', 'safequote-traditional' ); ?>
			</label>
			<select id="filter-model" name="model" class="form-select" data-filter="model">
				<option value="">
					<?php esc_html_e( 'Select Model', 'safequote-traditional' ); ?>
				</option>
			</select>
		</div>

		<!-- Minimum Safety Rating Slider -->
		<div class="space-y-3">
			<label for="filter-safety-rating" class="form-label">
				<?php esc_html_e( 'Min Safety Rating:', 'safequote-traditional' ); ?>
				<span id="safety-rating-value" class="font-semibold">
					<?php echo esc_html( $filters['minSafetyRating'] ); ?>/5
				</span>
			</label>
			<input
				id="filter-safety-rating"
				type="range"
				name="minSafetyRating"
				min="0"
				max="5"
				step="1"
				value="<?php echo esc_attr( $filters['minSafetyRating'] ); ?>"
				class="w-full h-2 bg-gray-300 rounded-lg appearance-none cursor-pointer accent-primary"
				data-filter="minSafetyRating"
				data-display="safety-rating-value"
			/>
		</div>
	</form>

	<!-- Action Buttons -->
	<div class="mt-6 pt-6 border-t border-gray-200 flex justify-end gap-3">
		<button id="search-vehicles-btn" class="btn btn-primary bg-gradient-to-r from-primary to-teal-500 hover:from-primary/90 hover:to-teal-500/90 text-white px-8 py-2 rounded-lg font-semibold transition-all duration-300 hover:shadow-lg">
			<?php esc_html_e( 'Search Vehicles', 'safequote-traditional' ); ?>
		</button>
		<button id="reset-filters" class="btn btn-outline text-gray-700 border border-gray-300 px-6 py-2 rounded-lg font-medium transition-all duration-300 hover:bg-gray-100">
			<?php esc_html_e( 'Reset', 'safequote-traditional' ); ?>
		</button>
	</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
	const filterForm = document.getElementById('vehicle-filters-form');
	const yearSelect = document.getElementById('filter-year');
	const makeSelect = document.getElementById('filter-make');
	const modelSelect = document.getElementById('filter-model');
	const safetyRatingInput = document.getElementById('filter-safety-rating');
	const resetButton = document.getElementById('reset-filters');

	// Load available years and makes on page load
	if (window.safequote_ajax?.nonce) {
		if (yearSelect) loadYears();
		if (makeSelect) loadMakes(); // Load all makes
	}

	// Year change handler - filter makes by year if selected
	yearSelect?.addEventListener('change', function() {
		const make = makeSelect.value;
		// Reload makes filtered by year (or all if no year selected)
		loadMakes(this.value || null);
		// Reset model when year changes
		modelSelect.innerHTML = '<option value=""><?php esc_html_e( 'Select Model', 'safequote-traditional' ); ?></option>';
		// If make is selected, reload models filtered by year
		if (make) {
			loadModels(this.value || null, make);
		}
		// Don't auto-search, wait for Search button click
	});

	// Make change handler - load models for selected make and filter years
	makeSelect?.addEventListener('change', function() {
		const year = yearSelect.value;
		// Reload years filtered by make (bidirectional filtering)
		loadYears(this.value || null);
		// Reset model when make changes
		modelSelect.innerHTML = '<option value=""><?php esc_html_e( 'Select Model', 'safequote-traditional' ); ?></option>';
		// If make is selected, load models
		if (this.value) {
			loadModels(year || null, this.value);
		}
		// Don't auto-search, wait for Search button click
	});

	// Model change handler (just update state, don't auto-search)
	modelSelect?.addEventListener('change', function() {
		// Selection updated, ready for manual search button click
	});

	// Update display value for safety rating slider (no auto-search, wait for button)
	safetyRatingInput?.addEventListener('input', function() {
		const display = document.getElementById('safety-rating-value');
		if (display) display.textContent = this.value + '/5';
	});

	// Search button handler
	const searchBtn = document.getElementById('search-vehicles-btn');
	searchBtn?.addEventListener('click', function(e) {
		e.preventDefault();
		triggerFilter();
	});

	// Reset filters
	resetButton?.addEventListener('click', function(e) {
		e.preventDefault();
		filterForm?.reset();
		// Reload all years and makes (unfiltered)
		loadYears();
		loadMakes();
		modelSelect.innerHTML = '<option value=""><?php esc_html_e( 'Select Model', 'safequote-traditional' ); ?></option>';
		if (safetyRatingInput) {
			safetyRatingInput.value = 0;
			document.getElementById('safety-rating-value').textContent = '0/5';
		}
		triggerFilter();
	});

	function loadYears(make = null) {
		const params = new URLSearchParams({
			action: 'get_years',
			nonce: window.safequote_ajax.nonce
		});
		// Add make filter if provided (bidirectional filtering)
		if (make) {
			params.append('make', make);
		}

		fetch(`${window.safequote_ajax.ajax_url}?${params.toString()}`)
			.then(res => res.json())
			.then(data => {
				const currentYear = yearSelect.value;
				yearSelect.innerHTML = '<option value=""><?php esc_html_e( 'All Years', 'safequote-traditional' ); ?></option>';
				if (data.success && data.data) {
					data.data.forEach(year => {
						const option = document.createElement('option');
						option.value = year.name;
						option.textContent = year.name;
						// Preserve current selection if it exists in new list
						if (year.name == currentYear) option.selected = true;
						yearSelect.appendChild(option);
					});
				}
			})
			.catch(err => console.error('Error loading years:', err));
	}

	function loadMakes(year = null) {
		const params = new URLSearchParams({
			action: 'get_makes',
			nonce: window.safequote_ajax.nonce
		});
		// Only add year if provided
		if (year) {
			params.append('year', year);
		}

		fetch(`${window.safequote_ajax.ajax_url}?${params.toString()}`)
			.then(res => res.json())
			.then(data => {
				const currentMake = makeSelect.value;
				makeSelect.innerHTML = '<option value=""><?php esc_html_e( 'All Makes', 'safequote-traditional' ); ?></option>';
				if (data.success && data.data) {
					data.data.forEach(make => {
						const option = document.createElement('option');
						option.value = make.name;
						option.textContent = make.name;
						// Preserve current selection if it exists in new list
						if (make.name == currentMake) option.selected = true;
						makeSelect.appendChild(option);
					});
				}
			})
			.catch(err => console.error('Error loading makes:', err));
	}

	function loadModels(year = null, make) {
		const params = new URLSearchParams({
			action: 'get_models',
			make: make,
			nonce: window.safequote_ajax.nonce
		});
		// Only add year if provided
		if (year) {
			params.append('year', year);
		}

		fetch(`${window.safequote_ajax.ajax_url}?${params.toString()}`)
			.then(res => res.json())
			.then(data => {
				if (data.success && data.data) {
					modelSelect.innerHTML = '<option value=""><?php esc_html_e( 'All Models', 'safequote-traditional' ); ?></option>';
					data.data.forEach(model => {
						const option = document.createElement('option');
						option.value = model.name;
						option.textContent = model.name;
						modelSelect.appendChild(option);
					});
				}
			})
			.catch(err => console.error('Error loading models:', err));
	}

	function displaySearchResults(vehicles, year, make, model) {
		const vehicleGridContainer = document.getElementById('vehicle-grid');
		if (!vehicleGridContainer) return;

		// Find the inner grid div or create it
		let grid = vehicleGridContainer.querySelector('.grid');
		if (!grid) {
			grid = document.createElement('div');
			grid.className = 'grid md:grid-cols-2 lg:grid-cols-3 gap-6';
			vehicleGridContainer.appendChild(grid);
		}

		// Clear existing vehicle cards
		grid.innerHTML = '';

		if (!vehicles || vehicles.length === 0) {
			grid.innerHTML = `
				<div class="col-span-full text-center py-8">
					<p class="text-gray-600"><?php esc_html_e( 'No vehicles found. Try adjusting your filters.', 'safequote-traditional' ); ?></p>
				</div>
			`;
			// Update vehicle count
			const countSpan = vehicleGridContainer.querySelector('span');
			if (countSpan) {
				countSpan.textContent = `0 <?php esc_html_e( 'vehicles found', 'safequote-traditional' ); ?>`;
			}
			return;
		}

		// Add vehicles to grid
		vehicles.forEach((vehicle, index) => {
			const cardHTML = createVehicleCardHTML(vehicle, index);
			const tempDiv = document.createElement('div');
			tempDiv.innerHTML = cardHTML;
			grid.appendChild(tempDiv.firstElementChild);
		});

		// Update vehicle count in header
		const countSpan = vehicleGridContainer.querySelector('span');
		if (countSpan) {
			countSpan.textContent = `${vehicles.length} <?php esc_html_e( 'vehicles found', 'safequote-traditional' ); ?>`;
		}
	}

	function createVehicleCardHTML(vehicle, index) {
		const delay = index * 0.1;
		// Fallback to nhtsa_data.OverallRating if safety_rating is null/undefined
		// Keep null if no rating exists (will display "No Rating")
		const safetyRating = vehicle.safety_rating ?? vehicle.nhtsa_data?.OverallRating ?? null;
		const hasRating = safetyRating !== null && safetyRating > 0;
		const safetyStars = hasRating ? generateSafetyStars(safetyRating) : '';
		// Fallback for crash ratings from nhtsa_data
		const frontCrash = vehicle.front_crash ?? vehicle.nhtsa_data?.OverallFrontCrashRating ?? null;
		const sideCrash = vehicle.side_crash ?? vehicle.nhtsa_data?.OverallSideCrashRating ?? null;
		const rolloverCrash = vehicle.rollover_crash ?? vehicle.nhtsa_data?.RolloverRating ?? null;
		const crashRatingsHTML = frontCrash || sideCrash || rolloverCrash ? `
			<div class="pt-2 border-t">
				<p class="text-xs text-gray-500 mb-2"><?php esc_html_e( 'Crash Test Ratings:', 'safequote-traditional' ); ?></p>
				<div class="space-y-1 text-xs text-gray-700">
					${frontCrash ? `<div class="flex justify-between"><span class="font-medium"><?php esc_html_e( 'Front Crash:', 'safequote-traditional' ); ?></span><span>${parseFloat(frontCrash).toFixed(1)}</span></div>` : ''}
					${sideCrash ? `<div class="flex justify-between"><span class="font-medium"><?php esc_html_e( 'Side Crash:', 'safequote-traditional' ); ?></span><span>${parseFloat(sideCrash).toFixed(1)}</span></div>` : ''}
					${rolloverCrash ? `<div class="flex justify-between"><span class="font-medium"><?php esc_html_e( 'Rollover:', 'safequote-traditional' ); ?></span><span>${parseFloat(rolloverCrash).toFixed(1)}</span></div>` : ''}
				</div>
			</div>
		` : '';
		// Get image from vehicle_picture, image, or nhtsa_data.VehiclePicture - fallback to grey placeholder
		const vehicleImage = vehicle.vehicle_picture || vehicle.image || vehicle.nhtsa_data?.VehiclePicture || '';
		const hasImage = vehicleImage && vehicleImage.trim() !== '';

		return `
			<div class="stagger-item bg-white rounded-xl shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden border border-gray-100 group" style="animation-delay: ${delay}s;">
				<div class="relative overflow-hidden h-48 ${hasImage ? '' : 'bg-gray-200'}">
					${hasImage ? `
					<img
						src="${escapeHtml(vehicleImage)}"
						alt="${escapeHtml(vehicle.year + ' ' + vehicle.make + ' ' + vehicle.model)}"
						class="w-full h-48 object-cover transition-transform duration-300 group-hover:scale-110"
						onerror="this.style.display='none'; this.parentElement.classList.add('bg-gray-200');"
					/>
					` : ``}
				</div>

				<div class="p-5 space-y-4">
					<!-- Vehicle Name and Type -->
					<div>
						<h3 class="text-xl font-semibold text-gray-900 mb-2">
							${escapeHtml(vehicle.year + ' ' + vehicle.make + ' ' + vehicle.model)}
						</h3>
						<p class="text-gray-600 text-sm">
							${escapeHtml(vehicle.type || '')}
						</p>
					</div>

					<!-- Safety Rating -->
					<div class="flex items-center gap-1">
						<svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"></path>
						</svg>
						${hasRating ? `
						<div class="flex ml-1">
							${safetyStars}
						</div>
						<span class="font-semibold text-base ml-1">
							(${safetyRating}/5)
						</span>
						` : `
						<span class="text-gray-500 ml-1"><?php esc_html_e( 'No Rating', 'safequote-traditional' ); ?></span>
						`}
					</div>

					<!-- Crash Ratings -->
					${crashRatingsHTML}

					<!-- Get Insurance Quotes Button -->
					<button
						class="w-full mt-4 bg-gradient-to-r from-primary to-teal-500 hover:from-primary/90 hover:to-teal-500/90 text-white py-3 rounded-xl font-semibold transition-all duration-300 hover:shadow-lg hover:scale-[1.02]"
						data-vehicle-id="${escapeHtml(vehicle.id)}"
						data-vehicle-make="${escapeHtml(vehicle.make)}"
						data-vehicle-model="${escapeHtml(vehicle.model)}"
						data-vehicle-year="${escapeHtml(vehicle.year)}"
					>
						<?php esc_html_e( 'Get Insurance Quotes', 'safequote-traditional' ); ?>
					</button>
				</div>
			</div>
		`;
	}

	function generateSafetyStars(rating) {
		let stars = '';
		for (let i = 0; i < 5; i++) {
			const isFilled = i < rating;
			stars += `
				<svg class="w-4 h-4 ${isFilled ? 'text-yellow-400 fill-yellow-400' : 'text-gray-300'}" fill="currentColor" viewBox="0 0 20 20">
					<path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
				</svg>
			`;
		}
		return stars;
	}

	function escapeHtml(text) {
		if (text === null || text === undefined) return '';
		const str = String(text);
		const map = {
			'&': '&amp;',
			'<': '&lt;',
			'>': '&gt;',
			'"': '&quot;',
			"'": '&#039;'
		};
		return str.replace(/[&<>"']/g, m => map[m]);
	}

	function triggerFilter() {
		const year = yearSelect.value;
		const make = makeSelect.value;
		const model = modelSelect.value;
		const minSafetyRating = parseInt(safetyRatingInput.value);

		// Build search parameters - only include non-empty values
		const params = new URLSearchParams({
			action: 'search_vehicles',
			nonce: window.safequote_ajax.nonce
		});

		// Add filters only if they have values
		if (year) params.append('year', year);
		if (make) params.append('make', make);
		if (model) params.append('model', model);
		if (minSafetyRating > 0) params.append('minSafetyRating', minSafetyRating);

		// Make AJAX request to search vehicles
		fetch(window.safequote_ajax.ajax_url, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
			body: params.toString()
		})
		.then(res => res.json())
		.then(data => {
			if (data.success && data.data?.vehicles) {
				// Dispatch custom event for other listeners
				const filterChangeEvent = new CustomEvent('vehicleFiltersChanged', {
					detail: {
						year: year,
						make: make,
						model: model,
						minSafetyRating: minSafetyRating,
						vehicles: data.data.vehicles
					}
				});
				document.dispatchEvent(filterChangeEvent);

				// Display search results
				displaySearchResults(data.data.vehicles, year, make, model);
			}
		})
		.catch(err => console.error('Error searching vehicles:', err));
	}
});
</script>
