<?php
/**
 * Template part: Vehicle Grid
 *
 * Displays a grid of vehicles with filtering and pagination support
 *
 * @package SafeQuote_Traditional
 * @since 1.0.0
 *
 * @param array $vehicles Array of vehicle data
 * @param bool  $show_filters Whether to show filter controls
 */

$vehicles = isset( $vehicles ) ? $vehicles : array();
?>

<div id="vehicle-grid" class="vehicle-grid-container">
	<div class="flex items-center justify-between mb-6">
		<h3 class="text-2xl font-bold text-gray-900">
			<?php esc_html_e( 'Available Vehicles', 'safequote-traditional' ); ?>
		</h3>
		<span class="text-gray-600 text-lg">
			<?php echo esc_html( count( $vehicles ) ); ?>
			<?php esc_html_e( 'vehicles found', 'safequote-traditional' ); ?>
		</span>
	</div>

	<?php if ( ! empty( $vehicles ) ) : ?>
		<div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
			<?php
			foreach ( $vehicles as $index => $vehicle ) {
				// Set up data for template
				set_query_var( 'vehicle', $vehicle );
				set_query_var( 'index', $index );

				// Include the vehicle card template part
				get_template_part( 'template-parts/vehicle-card' );
			}
			?>
		</div>
	<?php else : ?>
		<div class="text-center py-12">
			<p class="text-gray-500 text-lg">
				<?php esc_html_e( 'No vehicles match your filters. Try adjusting your search criteria.', 'safequote-traditional' ); ?>
			</p>
		</div>
	<?php endif; ?>
</div>
