<?php
/**
 * Template part: Search Filters
 *
 * Displays vehicle search filters with condition, type, safety rating, and price range
 *
 * @package SafeQuote_Traditional
 * @since 1.0.0
 *
 * @param array $filters {
 *     @type string $condition         Current condition filter (all|new|preowned)
 *     @type string $vehicleType       Current vehicle type filter
 *     @type int    $minSafetyRating   Minimum safety rating (0-5)
 *     @type int    $maxPrice          Maximum price filter
 * }
 */

$filters = isset( $filters ) ? $filters : array(
	'condition'        => 'all',
	'vehicleType'      => 'all',
	'minSafetyRating'  => 0,
	'maxPrice'         => 50000,
);
?>

<div id="search-filters" class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100 fade-in">
	<!-- Header -->
	<div class="flex items-center gap-2 mb-6">
		<svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
			<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
		</svg>
		<h3 class="text-xl font-semibold text-gray-900">
			<?php esc_html_e( 'Refine Your Search', 'safequote-traditional' ); ?>
		</h3>
	</div>

	<!-- Filters Grid -->
	<form id="vehicle-filters-form" class="grid md:grid-cols-4 gap-6">
		<!-- Condition Filter -->
		<div class="space-y-2">
			<label for="filter-condition" class="form-label">
				<?php esc_html_e( 'Condition', 'safequote-traditional' ); ?>
			</label>
			<select id="filter-condition" name="condition" class="form-select" data-filter="condition">
				<option value="all" <?php selected( $filters['condition'], 'all' ); ?>>
					<?php esc_html_e( 'All Vehicles', 'safequote-traditional' ); ?>
				</option>
				<option value="new" <?php selected( $filters['condition'], 'new' ); ?>>
					<?php esc_html_e( 'New Only', 'safequote-traditional' ); ?>
				</option>
				<option value="preowned" <?php selected( $filters['condition'], 'preowned' ); ?>>
					<?php esc_html_e( 'Preowned Only', 'safequote-traditional' ); ?>
				</option>
			</select>
		</div>

		<!-- Vehicle Type Filter -->
		<div class="space-y-2">
			<label for="filter-type" class="form-label">
				<?php esc_html_e( 'Vehicle Type', 'safequote-traditional' ); ?>
			</label>
			<select id="filter-type" name="vehicleType" class="form-select" data-filter="vehicleType">
				<option value="all" <?php selected( $filters['vehicleType'], 'all' ); ?>>
					<?php esc_html_e( 'All Types', 'safequote-traditional' ); ?>
				</option>
				<option value="sedan" <?php selected( $filters['vehicleType'], 'sedan' ); ?>>
					<?php esc_html_e( 'Sedan', 'safequote-traditional' ); ?>
				</option>
				<option value="suv" <?php selected( $filters['vehicleType'], 'suv' ); ?>>
					<?php esc_html_e( 'SUV', 'safequote-traditional' ); ?>
				</option>
				<option value="hatchback" <?php selected( $filters['vehicleType'], 'hatchback' ); ?>>
					<?php esc_html_e( 'Hatchback', 'safequote-traditional' ); ?>
				</option>
				<option value="truck" <?php selected( $filters['vehicleType'], 'truck' ); ?>>
					<?php esc_html_e( 'Truck', 'safequote-traditional' ); ?>
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

		<!-- Maximum Price Slider -->
		<div class="space-y-3">
			<label for="filter-max-price" class="form-label">
				<?php esc_html_e( 'Max Price:', 'safequote-traditional' ); ?>
				<span id="max-price-value" class="font-semibold">
					$<?php echo esc_html( number_format( $filters['maxPrice'] ) ); ?>
				</span>
			</label>
			<input
				id="filter-max-price"
				type="range"
				name="maxPrice"
				min="10000"
				max="50000"
				step="1000"
				value="<?php echo esc_attr( $filters['maxPrice'] ); ?>"
				class="w-full h-2 bg-gray-300 rounded-lg appearance-none cursor-pointer accent-primary"
				data-filter="maxPrice"
				data-display="max-price-value"
			/>
		</div>
	</form>

	<!-- Reset Filters Button -->
	<div class="mt-6 pt-6 border-t border-gray-200">
		<button id="reset-filters" class="btn btn-outline">
			<?php esc_html_e( 'Reset Filters', 'safequote-traditional' ); ?>
		</button>
	</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
	const filterForm = document.getElementById('vehicle-filters-form');
	const safetyRatingInput = document.getElementById('filter-safety-rating');
	const maxPriceInput = document.getElementById('filter-max-price');
	const resetButton = document.getElementById('reset-filters');

	// Update display values for sliders
	safetyRatingInput?.addEventListener('input', function() {
		const display = document.getElementById('safety-rating-value');
		if (display) display.textContent = this.value + '/5';
		triggerFilter();
	});

	maxPriceInput?.addEventListener('input', function() {
		const display = document.getElementById('max-price-value');
		if (display) display.textContent = '$' + parseInt(this.value).toLocaleString();
		triggerFilter();
	});

	// Filter on select change
	filterForm?.querySelectorAll('select').forEach(select => {
		select.addEventListener('change', triggerFilter);
	});

	// Reset filters
	resetButton?.addEventListener('click', function(e) {
		e.preventDefault();
		filterForm?.reset();
		if (safetyRatingInput) {
			safetyRatingInput.value = 0;
			document.getElementById('safety-rating-value').textContent = '0/5';
		}
		if (maxPriceInput) {
			maxPriceInput.value = 50000;
			document.getElementById('max-price-value').textContent = '$50,000';
		}
		triggerFilter();
	});

	function triggerFilter() {
		// Dispatch custom event for filter changes
		const filterChangeEvent = new CustomEvent('vehicleFiltersChanged', {
			detail: {
				condition: document.getElementById('filter-condition').value,
				vehicleType: document.getElementById('filter-type').value,
				minSafetyRating: parseInt(safetyRatingInput.value),
				maxPrice: parseInt(maxPriceInput.value),
			}
		});
		document.dispatchEvent(filterChangeEvent);
	}
});
</script>
