<?php
/**
 * The template for displaying the front page
 *
 * @package SafeQuote_Traditional
 */

get_header();
?>

<main id="primary" class="site-main">
    <!-- Hero Section -->
    <section class="hero bg-gradient-to-br from-blue-600 to-blue-800 text-white py-20">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto text-center">
                <h1 class="text-4xl md:text-5xl font-bold mb-6">
                    <?php esc_html_e('Compare Insurance Quotes for High-Performance Vehicles', 'safequote-traditional'); ?>
                </h1>
                <p class="text-xl mb-8 text-blue-100">
                    <?php esc_html_e('Find the perfect high-performance car with comprehensive safety ratings and insurance comparison', 'safequote-traditional'); ?>
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <button id="get-started-btn" class="px-8 py-4 bg-white text-blue-600 font-semibold rounded-lg hover:bg-gray-100 transition-colors">
                        <?php esc_html_e('Get Started', 'safequote-traditional'); ?>
                    </button>
                    <button id="learn-more-btn" class="px-8 py-4 bg-transparent border-2 border-white text-white font-semibold rounded-lg hover:bg-white hover:text-blue-600 transition-colors">
                        <?php esc_html_e('Learn More', 'safequote-traditional'); ?>
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- Vehicle Search Section -->
    <section class="vehicle-search py-16 bg-gray-50">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-12"><?php esc_html_e('Find Your Vehicle', 'safequote-traditional'); ?></h2>

            <!-- Search Filters -->
            <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-md p-6 mb-8">
                <form id="vehicle-search-form" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="year" class="block text-sm font-medium text-gray-700 mb-1">
                                <?php esc_html_e('Year', 'safequote-traditional'); ?>
                            </label>
                            <select id="year" name="year" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                <option value=""><?php esc_html_e('Select Year', 'safequote-traditional'); ?></option>
                                <?php
                                $current_year = date('Y');
                                for ($year = $current_year; $year >= 2010; $year--) {
                                    echo '<option value="' . $year . '">' . $year . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div>
                            <label for="make" class="block text-sm font-medium text-gray-700 mb-1">
                                <?php esc_html_e('Make', 'safequote-traditional'); ?>
                            </label>
                            <select id="make" name="make" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                <option value=""><?php esc_html_e('Select Make', 'safequote-traditional'); ?></option>
                            </select>
                        </div>
                        <div>
                            <label for="model" class="block text-sm font-medium text-gray-700 mb-1">
                                <?php esc_html_e('Model', 'safequote-traditional'); ?>
                            </label>
                            <select id="model" name="model" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                <option value=""><?php esc_html_e('Select Model', 'safequote-traditional'); ?></option>
                            </select>
                        </div>
                    </div>

                    <!-- Additional Filters -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="min-price" class="block text-sm font-medium text-gray-700 mb-1">
                                <?php esc_html_e('Min Price', 'safequote-traditional'); ?>
                            </label>
                            <input type="number" id="min-price" name="min_price" placeholder="0"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label for="max-price" class="block text-sm font-medium text-gray-700 mb-1">
                                <?php esc_html_e('Max Price', 'safequote-traditional'); ?>
                            </label>
                            <input type="number" id="max-price" name="max_price" placeholder="200000"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    <!-- Safety Rating Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <?php esc_html_e('Minimum Safety Rating', 'safequote-traditional'); ?>
                        </label>
                        <div class="flex space-x-1" id="safety-rating-filter">
                            <?php for ($i = 1; $i <= 5; $i++) : ?>
                                <button type="button" data-rating="<?php echo $i; ?>" class="safety-star p-1">
                                    <svg class="w-8 h-8 fill-gray-300 hover:fill-yellow-400 transition-colors" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                </button>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <div class="flex justify-center pt-4">
                        <button type="submit" class="px-8 py-3 bg-blue-600 text-white font-semibold rounded-md hover:bg-blue-700 transition-colors">
                            <?php esc_html_e('Search Vehicles', 'safequote-traditional'); ?>
                        </button>
                        <button type="reset" class="ml-4 px-8 py-3 bg-gray-200 text-gray-700 font-semibold rounded-md hover:bg-gray-300 transition-colors">
                            <?php esc_html_e('Clear Filters', 'safequote-traditional'); ?>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Vehicle Grid (populated by JavaScript) -->
            <div id="vehicle-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Vehicles will be loaded here dynamically -->
            </div>

            <!-- Loading Indicator -->
            <div id="loading-indicator" class="hidden text-center py-8">
                <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
                <p class="mt-4 text-gray-600"><?php esc_html_e('Loading vehicles...', 'safequote-traditional'); ?></p>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features py-16 bg-white">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-12"><?php esc_html_e('Why Choose SafeQuote?', 'safequote-traditional'); ?></h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-2"><?php esc_html_e('Safety First', 'safequote-traditional'); ?></h3>
                    <p class="text-gray-600"><?php esc_html_e('Access comprehensive NHTSA safety ratings for all vehicles', 'safequote-traditional'); ?></p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-2"><?php esc_html_e('Best Rates', 'safequote-traditional'); ?></h3>
                    <p class="text-gray-600"><?php esc_html_e('Compare insurance quotes from multiple providers instantly', 'safequote-traditional'); ?></p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-2"><?php esc_html_e('Fast & Easy', 'safequote-traditional'); ?></h3>
                    <p class="text-gray-600"><?php esc_html_e('Get personalized recommendations in minutes', 'safequote-traditional'); ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- Insurance Comparison Section -->
    <section class="insurance-comparison py-16 bg-gray-50">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-12"><?php esc_html_e('Compare Insurance Providers', 'safequote-traditional'); ?></h2>

            <div class="max-w-6xl mx-auto">
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-sm font-medium text-gray-900"><?php esc_html_e('Provider', 'safequote-traditional'); ?></th>
                                <th class="px-6 py-4 text-center text-sm font-medium text-gray-900"><?php esc_html_e('Monthly Premium', 'safequote-traditional'); ?></th>
                                <th class="px-6 py-4 text-center text-sm font-medium text-gray-900"><?php esc_html_e('Coverage', 'safequote-traditional'); ?></th>
                                <th class="px-6 py-4 text-center text-sm font-medium text-gray-900"><?php esc_html_e('Rating', 'safequote-traditional'); ?></th>
                                <th class="px-6 py-4 text-center text-sm font-medium text-gray-900"><?php esc_html_e('Action', 'safequote-traditional'); ?></th>
                            </tr>
                        </thead>
                        <tbody id="insurance-comparison-table" class="bg-white divide-y divide-gray-200">
                            <!-- Insurance providers will be loaded here dynamically -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta py-16 bg-gradient-to-r from-blue-600 to-blue-800 text-white">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-3xl font-bold mb-4"><?php esc_html_e('Ready to Find Your Perfect Vehicle?', 'safequote-traditional'); ?></h2>
            <p class="text-xl mb-8 text-blue-100"><?php esc_html_e('Start comparing safety ratings and insurance quotes today', 'safequote-traditional'); ?></p>
            <a href="#vehicle-search" class="inline-block px-8 py-4 bg-white text-blue-600 font-semibold rounded-lg hover:bg-gray-100 transition-colors">
                <?php esc_html_e('Start Your Search', 'safequote-traditional'); ?>
            </a>
        </div>
    </section>

    <!-- Selected Vehicles Modal Container -->
    <div id="selected-vehicles-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg max-w-4xl w-full p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-2xl font-bold"><?php esc_html_e('Selected Vehicles for Comparison', 'safequote-traditional'); ?></h3>
                    <button id="close-modal" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div id="selected-vehicles-list">
                    <!-- Selected vehicles will be displayed here -->
                </div>
                <div class="mt-6 flex justify-end space-x-4">
                    <button id="clear-selection" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition-colors">
                        <?php esc_html_e('Clear Selection', 'safequote-traditional'); ?>
                    </button>
                    <button id="compare-vehicles" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                        <?php esc_html_e('Compare Insurance Quotes', 'safequote-traditional'); ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
</main><!-- #primary -->

<?php
get_footer();