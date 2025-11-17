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
 *     @type float  $price           Vehicle price
 *     @type int    $safety_rating   Safety rating (0-5)
 *     @type int    $mileage         Vehicle mileage
 *     @type array  $safety_features Array of safety features
 *     @type string $condition       Vehicle condition (New, Preowned)
 * }
 * @param int $index The index of the vehicle in the grid (for stagger animation)
 */

if ( ! isset( $vehicle ) ) {
	return;
}

$delay = isset( $index ) ? $index * 0.1 : 0;
?>

<div class="stagger-item bg-white rounded-xl shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden border border-gray-100 group" style="animation-delay: <?php echo esc_attr( $delay ); ?>s;">
	<div class="relative overflow-hidden">
		<img
			src="<?php echo esc_url( $vehicle['image'] ); ?>"
			alt="<?php echo esc_attr( $vehicle['year'] . ' ' . $vehicle['make'] . ' ' . $vehicle['model'] ); ?>"
			class="w-full h-48 object-cover transition-transform duration-300 group-hover:scale-110"
			onerror="this.onerror=null; this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAwIiBoZWlnaHQ9IjMwMCIgdmlld0JveD0iMCAwIDQwMCAzMDAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CiAgPHJlY3Qgd2lkdGg9IjQwMCIgaGVpZ2h0PSIzMDAiIGZpbGw9IiNmM2Y0ZjYiLz4KICA8ZyB0cmFuc2Zvcm09InRyYW5zbGF0ZSgyMDAsIDE1MCkiPgogICAgPHBhdGggZD0iTSA2MCAxMGgyMGM2IDAgMTAtNCAxMC0xMHYtMzBjMC05LTctMTctMTUtMTlDNTcgLTU0IDMwIC02MCAzMCAtNjBzLTEzLTE0LTIyLTIzYy01LTQtMTEtNy0xOC03aC03MGMtNiAwLTExIDQtMTQgOWwtMTQgMjlBMzcgMzcgMCAwMC04MCAtNDB2NDBjMCA2IDQgMTAgMTAgMTBoMjAiIHN0cm9rZT0iIzljYTNhZiIgc3Ryb2tlLXdpZHRoPSI4IiBmaWxsPSJub25lIiBzdHJva2UtbGluZWNhcD0icm91bmQiIHN0cm9rZS1saW5lam9pbj0icm91bmQiLz4KICAgIDxjaXJjbGUgY3g9Ii00MCIgY3k9IjEwIiByPSIxNSIgc3Ryb2tlPSIjOWNhM2FmIiBzdHJva2Utd2lkdGg9IjgiIGZpbGw9Im5vbmUiLz4KICAgIDxwYXRoIGQ9Ik0gLTIwIDEwaDQwIiBzdHJva2U9IiM5Y2EzYWYiIHN0cm9rZS13aWR0aD0iOCIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIi8+CiAgICA8Y2lyY2xlIGN4PSI0MCIgY3k9IjEwIiByPSIxNSIgc3Ryb2tlPSIjOWNhM2FmIiBzdHJva2Utd2lkdGg9IjgiIGZpbGw9Im5vbmUiLz4KICA8L2c+Cjwvc3ZnPg==';"
		/>
		<span class="absolute top-3 right-3 bg-white/90 text-gray-900 px-3 py-1 rounded-lg text-sm font-medium shadow backdrop-blur-sm">
			<?php echo esc_html( $vehicle['condition'] ); ?>
		</span>
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

		<!-- Safety Rating and Price -->
		<div class="flex items-center justify-between">
			<!-- Safety Rating -->
			<div class="flex items-center gap-1">
				<svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"></path>
				</svg>
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
			</div>

			<!-- Price -->
			<div class="flex items-center gap-1 text-green-700 font-bold text-xl">
				<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
				</svg>
				<?php echo esc_html( number_format( $vehicle['price'] ) ); ?>
			</div>
		</div>

		<!-- Year and Mileage -->
		<div class="flex items-center justify-between text-sm text-gray-600 pt-2 border-t">
			<div class="flex items-center gap-2">
				<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
				</svg>
				<span><?php echo esc_html( $vehicle['year'] ); ?></span>
			</div>
			<div class="flex items-center gap-2">
				<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path d="m12 14 4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
					<path d="M3.34 19a10 10 0 1 1 17.32 0" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
				</svg>
				<span><?php echo esc_html( number_format( $vehicle['mileage'] ) ); ?> miles</span>
			</div>
		</div>

		<!-- Safety Features Badges -->
		<div class="pt-2">
			<p class="text-xs text-gray-500 mb-2">
				<?php esc_html_e( 'Key Safety Features:', 'safequote-traditional' ); ?>
			</p>
			<div class="flex flex-wrap gap-1">
				<?php
				$featured_features = array_slice( $vehicle['safety_features'], 0, 3 );
				foreach ( $featured_features as $feature ) :
				?>
					<span class="inline-block px-2 py-1 bg-gray-100 text-gray-700 rounded-md text-xs border border-gray-200">
						<?php echo esc_html( $feature ); ?>
					</span>
				<?php endforeach; ?>
			</div>
		</div>

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
