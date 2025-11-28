<?php
/**
 * Template part: Vehicle Card
 *
 * @package SafeQuote_Traditional
 * @since 1.0.0
 *
 * @param array $vehicle {
 *     @type int    $id              Vehicle ID
 *     @type string $image           Vehicle image URL
 *     @type int    $year            Vehicle year
 *     @type string $make            Vehicle make
 *     @type string $model           Vehicle model
 *     @type string $type            Vehicle type (SUV, Sedan, etc.)
 *     @type float  $safety_rating   Safety rating (0-5)
 *     @type float  $front_crash     Front crash rating
 *     @type float  $side_crash      Side crash rating
 *     @type float  $rollover_crash  Rollover crash rating
 * }
 * @param int $index The index of the vehicle in the grid (for stagger animation)
 */

if ( ! isset( $vehicle ) ) {
	return;
}

$delay = isset( $index ) ? $index * 0.1 : 0;
?>

<?php
$vehicle_image = ! empty( $vehicle['vehicle_picture'] ) ? $vehicle['vehicle_picture'] : ( ! empty( $vehicle['image'] ) ? $vehicle['image'] : '' );
$has_image     = ! empty( $vehicle_image );
?>
<div class="stagger-item bg-white rounded-xl shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden border border-gray-100 group" style="animation-delay: <?php echo esc_attr( $delay ); ?>s;">
	<div class="relative overflow-hidden h-48 <?php echo $has_image ? '' : 'bg-gray-200'; ?>">
		<?php if ( $has_image ) : ?>
		<img
			src="<?php echo esc_url( $vehicle_image ); ?>"
			alt="<?php echo esc_attr( $vehicle['year'] . ' ' . $vehicle['make'] . ' ' . $vehicle['model'] ); ?>"
			class="w-full h-48 object-cover transition-transform duration-300 group-hover:scale-110"
			onerror="this.style.display='none'; this.parentElement.classList.add('bg-gray-200');"
		/>
		<?php endif; ?>
	</div>

	<div class="p-5 space-y-4">
		<!-- Vehicle Name and Type -->
		<div>
			<h3 class="text-xl font-semibold text-gray-900 mb-2">
				<?php echo esc_html( $vehicle['year'] . ' ' . $vehicle['make'] . ' ' . $vehicle['model'] ); ?>
			</h3>
			<p class="text-gray-600 text-sm">
				<?php echo esc_html( $vehicle['type'] ); ?>
			</p>
		</div>

		<!-- Safety Rating -->
		<?php
		$has_rating = isset( $vehicle['safety_rating'] ) && $vehicle['safety_rating'] !== null && $vehicle['safety_rating'] > 0;
		?>
		<div class="flex items-center gap-1">
			<svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
				<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"></path>
			</svg>
			<?php if ( $has_rating ) : ?>
			<div class="flex ml-1">
				<?php for ( $i = 0; $i < 5; $i++ ) : ?>
					<svg class="w-4 h-4 <?php echo $i < $vehicle['safety_rating'] ? 'text-yellow-400 fill-yellow-400' : 'text-gray-300'; ?>" fill="currentColor" viewBox="0 0 20 20">
						<path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
					</svg>
				<?php endfor; ?>
			</div>
			<span class="font-semibold text-base ml-1">
				(<?php echo esc_html( $vehicle['safety_rating'] ); ?>/5)
			</span>
			<?php else : ?>
			<span class="text-gray-500 ml-1"><?php esc_html_e( 'No Rating', 'safequote-traditional' ); ?></span>
			<?php endif; ?>
		</div>

		<!-- Crash Ratings -->
		<?php if ( isset( $vehicle['front_crash'] ) || isset( $vehicle['side_crash'] ) || isset( $vehicle['rollover_crash'] ) ) : ?>
			<div class="pt-2 border-t">
				<p class="text-xs text-gray-500 mb-2">
					<?php esc_html_e( 'Crash Test Ratings:', 'safequote-traditional' ); ?>
				</p>
				<div class="space-y-1 text-xs text-gray-700">
					<?php if ( isset( $vehicle['front_crash'] ) ) : ?>
						<div class="flex justify-between">
							<span class="font-medium">Front Crash:</span>
							<span><?php echo esc_html( number_format( $vehicle['front_crash'], 1 ) ); ?></span>
						</div>
					<?php endif; ?>
					<?php if ( isset( $vehicle['side_crash'] ) ) : ?>
						<div class="flex justify-between">
							<span class="font-medium">Side Crash:</span>
							<span><?php echo esc_html( number_format( $vehicle['side_crash'], 1 ) ); ?></span>
						</div>
					<?php endif; ?>
					<?php if ( isset( $vehicle['rollover_crash'] ) ) : ?>
						<div class="flex justify-between">
							<span class="font-medium">Rollover:</span>
							<span><?php echo esc_html( number_format( $vehicle['rollover_crash'], 1 ) ); ?></span>
						</div>
					<?php endif; ?>
				</div>
			</div>
		<?php endif; ?>

		<!-- Get Insurance Quotes Button -->
		<button
			class="w-full mt-4 bg-gradient-to-r from-primary to-teal-500 hover:from-primary/90 hover:to-teal-500/90 text-white py-3 rounded-xl font-semibold transition-all duration-300 hover:shadow-lg hover:scale-[1.02]"
			data-vehicle-id="<?php echo esc_attr( $vehicle['id'] ); ?>"
			data-vehicle-make="<?php echo esc_attr( $vehicle['make'] ); ?>"
			data-vehicle-model="<?php echo esc_attr( $vehicle['model'] ); ?>"
			data-vehicle-year="<?php echo esc_attr( $vehicle['year'] ); ?>"
		>
			<?php esc_html_e( 'Get Insurance Quotes', 'safequote-traditional' ); ?>
		</button>
	</div>
</div>
