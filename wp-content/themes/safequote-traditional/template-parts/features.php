<?php
/**
 * Template part for displaying the features/flow selector section
 *
 * @package SafeQuote_Traditional
 */
?>

<!-- Features Section - EXACT MATCH to React Features.jsx -->
<section class="features py-16 bg-white fade-in" id="features">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold mb-4 text-gray-900">
                <?php esc_html_e('Your Journey to a Safer Drive Starts Here', 'safequote-traditional'); ?>
            </h2>
            <p class="text-gray-600 max-w-3xl mx-auto">
                <?php esc_html_e("Find the ideal safe car, get insurance quotes for a vehicle you already own, or find the best driver's ed classes.", 'safequote-traditional'); ?>
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Find a Safe Car Button -->
            <div class="w-full transform hover:scale-105 transition-transform">
                <button id="flow-find-car" class="w-full h-full p-6 text-lg flex flex-col gap-2 bg-gradient-to-r from-primary to-teal-500 hover:from-primary/90 hover:to-teal-500/90 text-white shadow-lg rounded-lg transition-all">
                    <!-- Car Icon (matching React's lucide-react Car) -->
                    <svg class="w-8 h-8 mb-2 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 002 12v4c0 .6.4 1 1 1h2"></path>
                        <circle cx="7" cy="17" r="2" stroke-width="2"></circle>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17h6"></path>
                        <circle cx="17" cy="17" r="2" stroke-width="2"></circle>
                    </svg>
                    <span><?php esc_html_e('Find a Safe Car', 'safequote-traditional'); ?></span>
                    <span class="text-sm font-normal text-white/80"><?php esc_html_e('Compare safety & prices', 'safequote-traditional'); ?></span>
                </button>
            </div>

            <!-- Get Insurance Quotes Button -->
            <div class="w-full transform hover:scale-105 transition-transform">
                <button id="flow-get-insurance" class="w-full h-full p-6 text-lg border-2 border-primary text-primary hover:bg-primary/10 hover:text-primary/90 flex flex-col gap-2 shadow-lg rounded-lg transition-all">
                    <!-- Shield Icon -->
                    <svg class="w-8 h-8 mb-2 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"></path>
                    </svg>
                    <span><?php esc_html_e('Get Insurance Quotes', 'safequote-traditional'); ?></span>
                    <span class="text-sm font-normal text-primary/80"><?php esc_html_e('For a car you own', 'safequote-traditional'); ?></span>
                </button>
            </div>

            <!-- Find Driver's Ed Button -->
            <div class="w-full transform hover:scale-105 transition-transform">
                <button id="flow-drivers-ed" class="w-full h-full p-6 text-lg border-2 border-primary text-primary hover:bg-primary/10 hover:text-primary/90 flex flex-col gap-2 shadow-lg rounded-lg transition-all">
                    <!-- Book Open Icon -->
                    <svg class="w-8 h-8 mb-2 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                    <span><?php esc_html_e("Find Driver's Ed", 'safequote-traditional'); ?></span>
                    <span class="text-sm font-normal text-primary/80"><?php esc_html_e('Local & online classes', 'safequote-traditional'); ?></span>
                </button>
            </div>
        </div>
    </div>
</section>
