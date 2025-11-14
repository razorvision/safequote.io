<?php
/**
 * Template part: Top Safety Picks
 *
 * Displays the highest-rated vehicles (5/5 safety rating)
 *
 * @package SafeQuote_Traditional
 * @since 1.0.0
 *
 * @param array $vehicles Array of top safety-rated vehicles
 */

// Get only top 4 vehicles
$top_vehicles = isset( $vehicles ) && ! empty( $vehicles ) ? array_slice( $vehicles, 0, 4 ) : array();
?>

<div id="top-safety-picks" class="top-safety-section p-8 border border-primary/20 stagger-item">
	<!-- Header -->
	<div class="flex items-center gap-3 mb-6">
		<svg class="w-8 h-8 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
			<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
		</svg>
		<h2 class="text-3xl font-bold text-gray-900">
			<?php esc_html_e( 'Top Safety Picks', 'safequote-traditional' ); ?>
		</h2>
	</div>

	<!-- Description -->
	<p class="text-gray-600 mb-6 max-w-3xl">
		<?php esc_html_e( 'These vehicles have received the highest possible safety rating (5/5). Click one to see estimated insurance quotes instantly.', 'safequote-traditional' ); ?>
	</p>

	<!-- Vehicles Grid -->
	<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
		<?php foreach ( $top_vehicles as $index => $vehicle ) : ?>
			<button
				class="stagger-item group w-full h-full p-4 flex flex-col items-center justify-center gap-2 text-center bg-white/70 hover:bg-white border border-gray-200 hover:border-primary rounded-lg transition-all duration-300 cursor-pointer"
				data-vehicle-id="<?php echo esc_attr( $vehicle['id'] ); ?>"
				data-vehicle-make="<?php echo esc_attr( $vehicle['make'] ); ?>"
				data-vehicle-model="<?php echo esc_attr( $vehicle['model'] ); ?>"
				data-vehicle-year="<?php echo esc_attr( $vehicle['year'] ); ?>"
				style="animation-delay: <?php echo esc_attr( $index * 0.1 ); ?>s;"
			>
				<img
					src="<?php echo esc_url( $vehicle['image'] ); ?>"
					alt="<?php echo esc_attr( $vehicle['model'] ); ?>"
					class="w-full h-20 object-contain rounded-md mb-2"
				/>
				<p class="font-semibold text-sm text-gray-800">
					<?php echo esc_html( $vehicle['make'] . ' ' . $vehicle['model'] ); ?>
				</p>
				<span class="inline-block px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold">
					<?php echo esc_html( $vehicle['year'] ); ?>
				</span>
			</button>
		<?php endforeach; ?>
	</div>
</div>
