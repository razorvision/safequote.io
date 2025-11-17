<?php
/**
 * Template part for displaying the safety ratings section
 *
 * @package SafeQuote_Traditional
 */
?>

<!-- Safety Ratings Section - EXACT MATCH to React SafetyRatings.jsx -->
<section class="safety-ratings py-16 bg-white rounded-2xl shadow-lg border border-gray-100" id="safety-ratings">
    <div class="container mx-auto px-4">
        <div class="text-center mb-10">
            <svg class="w-12 h-12 mx-auto text-primary mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"></path>
            </svg>
            <h2 class="text-3xl md:text-4xl font-bold mb-3 text-gray-900">
                <?php esc_html_e('Check Vehicle Safety Ratings', 'safequote-traditional'); ?>
            </h2>
            <p class="text-gray-600 max-w-2xl mx-auto">
                <?php esc_html_e('Get official 5-star safety ratings from the National Highway Traffic Safety Administration (NHTSA).', 'safequote-traditional'); ?>
            </p>
        </div>

        <!-- Safety Ratings Form - EXACT MATCH to React SafetyRatings.jsx -->
        <form id="safety-ratings-form" class="max-w-3xl mx-auto grid grid-cols-1 md:grid-cols-4 gap-4 items-end mb-8">
            <div class="md:col-span-1">
                <label for="year" class="block text-sm font-medium text-gray-700 mb-1"><?php esc_html_e('Year', 'safequote-traditional'); ?></label>
                <input id="year" type="text" value="2024" placeholder="e.g., 2024" required
                       class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50" />
            </div>
            <div class="md:col-span-1">
                <label for="make" class="block text-sm font-medium text-gray-700 mb-1"><?php esc_html_e('Make', 'safequote-traditional'); ?></label>
                <input id="make" type="text" value="Toyota" placeholder="e.g., Toyota" required
                       class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50" />
            </div>
            <div class="md:col-span-1">
                <label for="model" class="block text-sm font-medium text-gray-700 mb-1"><?php esc_html_e('Model', 'safequote-traditional'); ?></label>
                <input id="model" type="text" value="Camry" placeholder="e.g., Camry" required
                       class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50" />
            </div>
            <button type="submit" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2 w-full md:col-span-1">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <span id="button-text"><?php esc_html_e('Check Rating', 'safequote-traditional'); ?></span>
            </button>
        </form>

        <!-- Safety Ratings Results (will be populated via JavaScript) -->
        <div id="safety-ratings-results" class="hidden">
            <!-- Results will be dynamically inserted here -->
        </div>
    </div>
</section>
