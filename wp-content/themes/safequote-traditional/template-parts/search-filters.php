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
			<select id="filter-make" name="make" class="form-select" data-filter="make" disabled>
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
			<select id="filter-model" name="model" class="form-select" data-filter="model" disabled>
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
	<div class="mt-6 pt-6 border-t border-gray-200 flex gap-3">
		<button id="search-vehicles-btn" class="btn btn-primary flex-1">
			<?php esc_html_e( 'Search Vehicles', 'safequote-traditional' ); ?>
		</button>
		<button id="reset-filters" class="btn btn-outline">
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

	// Load available years on page load
	if (yearSelect && window.safequote_ajax?.nonce) {
		loadYears();
	}

	// Year change handler
	yearSelect?.addEventListener('change', function() {
		// Reset make and model when year changes
		makeSelect.innerHTML = '<option value=""><?php esc_html_e( 'Select Make', 'safequote-traditional' ); ?></option>';
		makeSelect.disabled = !this.value;
		modelSelect.innerHTML = '<option value=""><?php esc_html_e( 'Select Model', 'safequote-traditional' ); ?></option>';
		modelSelect.disabled = true;

		if (this.value) {
			loadMakes(this.value);
		}
		// Don't auto-search, wait for Search button click
	});

	// Make change handler
	makeSelect?.addEventListener('change', function() {
		const year = yearSelect.value;
		// Reset model when make changes
		modelSelect.innerHTML = '<option value=""><?php esc_html_e( 'Select Model', 'safequote-traditional' ); ?></option>';
		modelSelect.disabled = !this.value;

		if (this.value && year) {
			loadModels(year, this.value);
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
		makeSelect.innerHTML = '<option value=""><?php esc_html_e( 'Select Make', 'safequote-traditional' ); ?></option>';
		makeSelect.disabled = true;
		modelSelect.innerHTML = '<option value=""><?php esc_html_e( 'Select Model', 'safequote-traditional' ); ?></option>';
		modelSelect.disabled = true;
		if (safetyRatingInput) {
			safetyRatingInput.value = 0;
			document.getElementById('safety-rating-value').textContent = '0/5';
		}
		triggerFilter();
	});

	function loadYears() {
		const params = new URLSearchParams({
			action: 'get_years',
			nonce: window.safequote_ajax.nonce
		});

		fetch(`${window.safequote_ajax.ajax_url}?${params.toString()}`)
			.then(res => res.json())
			.then(data => {
				if (data.success && data.data) {
					data.data.forEach(year => {
						const option = document.createElement('option');
						option.value = year.name;
						option.textContent = year.name;
						yearSelect.appendChild(option);
					});
				}
			})
			.catch(err => console.error('Error loading years:', err));
	}

	function loadMakes(year) {
		const params = new URLSearchParams({
			action: 'get_makes',
			year: year,
			nonce: window.safequote_ajax.nonce
		});

		fetch(`${window.safequote_ajax.ajax_url}?${params.toString()}`)
			.then(res => res.json())
			.then(data => {
				if (data.success && data.data) {
					makeSelect.innerHTML = '<option value=""><?php esc_html_e( 'Select Make', 'safequote-traditional' ); ?></option>';
					data.data.forEach(make => {
						const option = document.createElement('option');
						option.value = make.name;
						option.textContent = make.name;
						makeSelect.appendChild(option);
					});
					makeSelect.disabled = false;
				}
			})
			.catch(err => console.error('Error loading makes:', err));
	}

	function loadModels(year, make) {
		const params = new URLSearchParams({
			action: 'get_models',
			year: year,
			make: make,
			nonce: window.safequote_ajax.nonce
		});

		fetch(`${window.safequote_ajax.ajax_url}?${params.toString()}`)
			.then(res => res.json())
			.then(data => {
				if (data.success && data.data) {
					modelSelect.innerHTML = '<option value=""><?php esc_html_e( 'Select Model', 'safequote-traditional' ); ?></option>';
					data.data.forEach(model => {
						const option = document.createElement('option');
						option.value = model.name;
						option.textContent = model.name;
						modelSelect.appendChild(option);
					});
					modelSelect.disabled = false;
				}
			})
			.catch(err => console.error('Error loading models:', err));
	}

	function triggerFilter() {
		const year = yearSelect.value;
		const make = makeSelect.value;
		const model = modelSelect.value;
		const minSafetyRating = parseInt(safetyRatingInput.value);

		// Require year and make to perform search
		if (!year || !make) {
			return;
		}

		// Build search parameters
		const params = new URLSearchParams({
			action: 'search_vehicles',
			year: year,
			make: make,
			model: model,
			minSafetyRating: minSafetyRating,
			nonce: window.safequote_ajax.nonce
		});

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

				// Display vehicles if display function exists
				if (window.SafeQuoteFilters?.displayVehicles) {
					window.SafeQuoteFilters.displayVehicles(data.data.vehicles);
				}
			}
		})
		.catch(err => console.error('Error searching vehicles:', err));
	}
});
</script>
