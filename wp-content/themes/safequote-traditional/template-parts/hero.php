<?php
/**
 * Template part for displaying the hero section
 *
 * @package SafeQuote_Traditional
 */
?>

<!-- Hero Section - EXACT MATCH to React Hero.jsx -->
<section class="hero relative text-white py-20 md:py-32 overflow-hidden">
    <!-- Background Image and Gradients -->
    <div class="absolute inset-0">
        <img
            alt="Mother and son examining a car together"
            class="w-full h-full object-cover"
            src="<?php echo SAFEQUOTE_THEME_URI; ?>/assets/images/hero-parent-teen-car.jpg" />
        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/40 to-transparent"></div>
        <div class="absolute inset-0 bg-gradient-to-r from-primary/30 to-transparent"></div>
    </div>

    <!-- Hero Content -->
    <div class="container mx-auto px-4 relative">
        <div class="text-center max-w-3xl mx-auto space-y-8 hero-animate">
            <h1 class="text-4xl md:text-6xl font-bold" style="text-shadow: 0 2px 10px rgba(0,0,0,0.5);">
                <?php esc_html_e('Find the Safest Car & Lowest Insurance Rate', 'safequote-traditional'); ?>
            </h1>
            <p class="text-xl md:text-2xl text-white/90" style="text-shadow: 0 1px 5px rgba(0,0,0,0.5);">
                <?php esc_html_e("Discover top-rated safe cars and compare insurance quotes instantly. Your teen's safety and your budget, perfectly aligned.", 'safequote-traditional'); ?>
            </p>
            <button id="hero-get-started" class="inline-flex items-center bg-white text-primary hover:bg-white/90 text-lg px-8 py-6 rounded-lg font-semibold transition-all">
                <?php esc_html_e('Get Started', 'safequote-traditional'); ?>
                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                </svg>
            </button>
        </div>
    </div>
</section>
