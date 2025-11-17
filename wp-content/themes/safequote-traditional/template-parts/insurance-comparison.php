<?php
/**
 * Template part: Insurance Comparison
 *
 * Displays insurance quotes comparison for a selected vehicle
 *
 * @package SafeQuote_Traditional
 * @since 1.0.0
 *
 * @param array $quotes Array of insurance quotes with provider details
 * @param array $vehicle Vehicle information for context
 */

// Retrieve query vars set by front-page.php
$vehicle = get_query_var( 'vehicle', array(
	'year'  => '',
	'make'  => 'your',
	'model' => 'car',
) );

$quotes = get_query_var( 'quotes', array() );

// Get unique section ID to avoid duplicate IDs when template is used multiple times
$section_id = get_query_var( 'section_id', 'insurance-comparison' );

// Generate sample quotes if not provided
if ( empty( $quotes ) ) {
	$quotes = safequote_get_sample_insurance_quotes( $vehicle );
}

$is_generic = 'your' === $vehicle['make'] && 'car' === $vehicle['model'];
?>

<div id="<?php echo esc_attr( $section_id ); ?>" class="bg-gradient-to-br from-primary/10 to-secondary/30 rounded-2xl p-8 border border-primary/20 fade-in">
	<!-- Header -->
	<div class="flex items-center gap-3 mb-6">
		<div class="bg-primary p-2 rounded-lg">
			<svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
				<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"></path>
			</svg>
		</div>
		<div>
			<h3 class="text-2xl font-bold text-gray-900">
				<?php esc_html_e( 'Insurance Quotes', 'safequote-traditional' ); ?>
			</h3>
			<p class="text-gray-600 text-sm">
				<?php
				if ( $is_generic ) {
					esc_html_e( 'Example quotes for a typical safe vehicle.', 'safequote-traditional' );
				} else {
					echo esc_html(
						sprintf(
							_x( 'For a %s %s %s', 'vehicle details', 'safequote-traditional' ),
							$vehicle['year'],
							$vehicle['make'],
							$vehicle['model']
						)
					);
				}
				?>
			</p>
		</div>
	</div>

	<!-- Info Alert for Generic Quotes -->
	<?php if ( $is_generic ) : ?>
		<div class="bg-primary/10 border-l-4 border-primary text-primary/80 p-4 rounded-md mb-6">
			<p class="font-semibold">
				<?php esc_html_e( 'Already have a car?', 'safequote-traditional' ); ?>
			</p>
			<p class="text-sm">
				<?php esc_html_e( 'Enter your vehicle details to get personalized quotes. These are sample rates.', 'safequote-traditional' ); ?>
			</p>
		</div>
	<?php endif; ?>

	<!-- Quotes Grid -->
	<div class="grid md:grid-cols-3 gap-6">
		<?php foreach ( $quotes as $index => $quote ) : ?>
			<div class="stagger-item bg-white rounded-xl p-6 shadow-md border-2 <?php echo $quote['recommended'] ? 'border-primary' : 'border-gray-200'; ?> relative flex flex-col" style="animation-delay: <?php echo esc_attr( $index * 0.1 ); ?>s;">
				<!-- Recommended Badge -->
				<?php if ( $quote['recommended'] ) : ?>
					<div class="absolute -top-4 left-1/2 transform -translate-x-1/2">
						<span class="inline-block px-2.5 py-0.5 bg-primary text-white border border-white/20 rounded-full text-xs font-semibold">
							<?php esc_html_e( 'Recommended', 'safequote-traditional' ); ?>
						</span>
					</div>
				<?php endif; ?>

				<!-- Provider Header -->
				<div class="text-center mb-4 pt-2">
					<h4 class="text-xl font-bold text-gray-900 mb-1">
						<?php echo esc_html( $quote['provider'] ); ?>
					</h4>
					<div class="flex items-center justify-center gap-1 text-gray-600 text-sm">
						<svg class="w-4 h-4 text-yellow-400 fill-yellow-400" viewBox="0 0 20 20">
							<path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
						</svg>
						<span><?php echo esc_html( $quote['rating'] ); ?>/5</span>
					</div>
				</div>

				<!-- Price Section -->
				<div class="text-center mb-6">
					<div class="text-4xl font-bold text-primary mb-1">
						$<?php echo esc_html( number_format( $quote['monthlyPrice'] ) ); ?>
					</div>
					<p class="text-gray-600 text-sm">
						<?php esc_html_e( 'per month', 'safequote-traditional' ); ?>
					</p>
					<?php if ( ! empty( $quote['discount'] ) && $quote['discount'] > 0 ) : ?>
						<div class="flex items-center justify-center gap-1 text-green-600 text-sm mt-2">
							<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7H7v6h6V7z"></path>
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 13l6-6"></path>
							</svg>
							<span><?php echo esc_html( $quote['discount'] ); ?>% teen driver discount</span>
						</div>
					<?php endif; ?>
				</div>

				<!-- Coverage Details -->
				<div class="space-y-2 mb-6 flex-grow">
					<?php foreach ( $quote['coverage'] as $coverage_item ) : ?>
						<div class="flex items-start gap-2 text-sm">
							<svg class="w-4 h-4 text-green-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
								<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
							</svg>
							<span class="text-gray-700"><?php echo esc_html( $coverage_item ); ?></span>
						</div>
					<?php endforeach; ?>
				</div>

				<!-- Get Quote Button -->
				<button
					class="w-full mt-auto py-3 px-4 rounded-lg font-semibold transition-all duration-300 flex items-center justify-center gap-2 <?php echo $quote['recommended'] ? 'bg-gradient-to-r from-primary to-teal-500 text-white hover:shadow-lg hover:scale-105' : 'bg-white border border-primary text-primary hover:bg-primary hover:text-white'; ?>"
					data-quote-url="<?php echo esc_attr( $quote['url'] ); ?>"
					class="get-quote-btn"
				>
					<?php esc_html_e( 'Get Quote', 'safequote-traditional' ); ?>
					<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
					</svg>
				</button>
			</div>
		<?php endforeach; ?>
	</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
	const quoteButtons = document.querySelectorAll('.get-quote-btn');
	quoteButtons.forEach(button => {
		button.addEventListener('click', function(e) {
			e.preventDefault();
			const url = this.getAttribute('data-quote-url');
			if (url) {
				window.open(url, '_blank', 'noopener,noreferrer');
				// Show notification
				if (typeof showNotification === 'function') {
					showNotification('success', 'Redirecting...', "You're being redirected to the provider's website to finalize your quote!");
				}
			}
		});
	});
});
</script>
