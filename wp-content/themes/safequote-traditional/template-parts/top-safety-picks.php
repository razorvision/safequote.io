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
			<circle cx="12" cy="8" r="6" stroke="currentColor" stroke-width="2" fill="none"></circle>
			<path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"></path>
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
	<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4" id="top-safety-picks-grid">
		<?php foreach ( $top_vehicles as $index => $vehicle ) : ?>
			<button
				class="stagger-item group w-full h-full p-4 flex flex-col items-center justify-center gap-2 text-center bg-white/70 hover:bg-white border border-gray-200 hover:border-primary rounded-lg transition-all duration-300 cursor-pointer relative top-safety-pick-vehicle"
				data-vehicle-id="<?php echo esc_attr( $vehicle['id'] ); ?>"
				data-vehicle-make="<?php echo esc_attr( $vehicle['make'] ); ?>"
				data-vehicle-model="<?php echo esc_attr( $vehicle['model'] ); ?>"
				data-vehicle-year="<?php echo esc_attr( $vehicle['year'] ); ?>"
				style="animation-delay: <?php echo esc_attr( $index * 0.1 ); ?>s;"
			>
				<!-- NHTSA Badge -->
				<div class="absolute top-2 right-2 nhtsa-badge-container" style="opacity: 0; transition: opacity 0.3s;">
					<span class="inline-flex items-center gap-1 px-2 py-1 bg-blue-50 text-blue-700 text-xs font-semibold rounded border border-blue-200 whitespace-nowrap">
						<svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24">
							<path d="M12 1L3 5v6c0 5.55 3.6 10.74 8 12.97 4.4-2.23 8-7.42 8-12.97V5l-9-4z"/>
						</svg>
						<span>NHTSA</span>
					</span>
				</div>
				<img
					src="<?php echo esc_url( $vehicle['image'] ); ?>"
					alt="<?php echo esc_attr( $vehicle['model'] ); ?>"
					class="w-full h-20 object-contain rounded-md mb-2"
					onerror="this.onerror=null; this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAwIiBoZWlnaHQ9IjMwMCIgdmlld0JveD0iMCAwIDQwMCAzMDAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CiAgPHJlY3Qgd2lkdGg9IjQwMCIgaGVpZ2h0PSIzMDAiIGZpbGw9IiNmM2Y0ZjYiLz4KICA8ZyB0cmFuc2Zvcm09InRyYW5zbGF0ZSgyMDAsIDE1MCkiPgogICAgPHBhdGggZD0iTSA2MCAxMGgyMGM2IDAgMTAtNCAxMC0xMHYtMzBjMC05LTctMTctMTUtMTlDNTcgLTU0IDMwIC02MCAzMCAtNjBzLTEzLTE0LTIyLTIzYy01LTQtMTEtNy0xOC03aC03MGMtNiAwLTExIDQtMTQgOWwtMTQgMjlBMzcgMzcgMCAwMC04MCAtNDB2NDBjMCA2IDQgMTAgMTAgMTBoMjAiIHN0cm9rZT0iIzljYTNhZiIgc3Ryb2tlLXdpZHRoPSI4IiBmaWxsPSJub25lIiBzdHJva2UtbGluZWNhcD0icm91bmQiIHN0cm9rZS1saW5lam9pbj0icm91bmQiLz4KICAgIDxjaXJjbGUgY3g9Ii00MCIgY3k9IjEwIiByPSIxNSIgc3Ryb2tlPSIjOWNhM2FmIiBzdHJva2Utd2lkdGg9IjgiIGZpbGw9Im5vbmUiLz4KICAgIDxwYXRoIGQ9Ik0gLTIwIDEwaDQwIiBzdHJva2U9IiM5Y2EzYWYiIHN0cm9rZS13aWR0aD0iOCIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIi8+CiAgICA8Y2lyY2xlIGN4PSI0MCIgY3k9IjEwIiByPSIxNSIgc3Ryb2tlPSIjOWNhM2FmIiBzdHJva2Utd2lkdGg9IjgiIGZpbGw9Im5vbmUiLz4KICA8L2c+Cjwvc3ZnPg==';"
				/>
				<!-- Star Rating -->
				<div class="nhtsa-rating-container" style="min-height: 20px; display: flex; align-items: center; justify-content: center;">
					<div class="text-sm text-gray-500 animate-pulse">Rating...</div>
				</div>

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
