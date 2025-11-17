<?php
/**
 * Template part for displaying the call-to-action section
 *
 * @package SafeQuote_Traditional
 */
?>

<!-- Call-to-Action Section -->
<section class="cta py-16 md:py-24 bg-gradient-to-r from-primary to-teal-500">
    <div class="container mx-auto px-4 text-center text-white">
        <h2 class="text-3xl md:text-4xl font-bold mb-4">
            <?php esc_html_e('Ready to Find Your Perfect Safe Car?', 'safequote-traditional'); ?>
        </h2>
        <p class="text-xl md:text-2xl text-white/90 max-w-2xl mx-auto mb-8">
            <?php esc_html_e('Get started today and discover the safest vehicles with the best insurance rates for your teen driver.', 'safequote-traditional'); ?>
        </p>
        <button id="cta-get-started" class="inline-flex items-center bg-white text-primary hover:bg-white/90 text-lg px-8 py-3 rounded-lg font-semibold transition-all">
            <?php esc_html_e('Start Your Search', 'safequote-traditional'); ?>
            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
            </svg>
        </button>
    </div>
</section>
