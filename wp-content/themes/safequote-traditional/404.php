<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @package SafeQuote_Traditional
 */

get_header();
?>

<main id="primary" class="site-main">
    <div class="container mx-auto px-4 py-16">
        <section class="error-404 not-found text-center">
            <div class="max-w-2xl mx-auto">
                <!-- 404 Illustration -->
                <div class="mb-8">
                    <svg class="w-64 h-64 mx-auto text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="0.5"
                              d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>

                <header class="page-header mb-8">
                    <h1 class="page-title text-6xl font-bold text-gray-900 mb-4">404</h1>
                    <h2 class="text-3xl font-semibold text-gray-700">
                        <?php esc_html_e('Oops! Page Not Found', 'safequote-traditional'); ?>
                    </h2>
                </header>

                <div class="page-content">
                    <p class="text-lg text-gray-600 mb-8">
                        <?php esc_html_e('It looks like the page you\'re looking for doesn\'t exist. This might be because:', 'safequote-traditional'); ?>
                    </p>

                    <ul class="text-left max-w-md mx-auto mb-8 space-y-2 text-gray-600">
                        <li class="flex items-start">
                            <svg class="w-5 h-5 mr-2 mt-0.5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <?php esc_html_e('The page has been moved or deleted', 'safequote-traditional'); ?>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 mr-2 mt-0.5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <?php esc_html_e('You typed the URL incorrectly', 'safequote-traditional'); ?>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 mr-2 mt-0.5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <?php esc_html_e('You followed an outdated link', 'safequote-traditional'); ?>
                        </li>
                    </ul>

                    <p class="text-lg text-gray-600 mb-8">
                        <?php esc_html_e('Here are some helpful options:', 'safequote-traditional'); ?>
                    </p>

                    <div class="flex flex-col sm:flex-row gap-4 justify-center mb-12">
                        <a href="<?php echo esc_url(home_url('/')); ?>"
                           class="inline-block px-6 py-3 bg-gradient-to-r from-primary to-teal-500 text-white font-semibold rounded-xl hover:shadow-lg hover:scale-105 transition-all">
                            <?php esc_html_e('Go to Homepage', 'safequote-traditional'); ?>
                        </a>
                        <button onclick="history.back()"
                                class="inline-block px-6 py-3 bg-gray-200 text-gray-700 font-semibold rounded-md hover:bg-gray-300 transition-colors">
                            <?php esc_html_e('Go Back', 'safequote-traditional'); ?>
                        </button>
                    </div>

                    <div class="bg-gray-50 rounded-lg p-6">
                        <h3 class="text-lg font-semibold mb-4"><?php esc_html_e('Search Our Site', 'safequote-traditional'); ?></h3>
                        <?php get_search_form(); ?>
                    </div>

                    <div class="mt-12">
                        <h3 class="text-lg font-semibold mb-4"><?php esc_html_e('Popular Pages', 'safequote-traditional'); ?></h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-left max-w-md mx-auto">
                            <a href="<?php echo esc_url(home_url('/vehicle-search')); ?>"
                               class="block p-4 bg-white rounded-lg shadow hover:shadow-md transition-shadow">
                                <h4 class="font-semibold text-primary mb-1"><?php esc_html_e('Vehicle Search', 'safequote-traditional'); ?></h4>
                                <p class="text-sm text-gray-600"><?php esc_html_e('Find your perfect vehicle', 'safequote-traditional'); ?></p>
                            </a>
                            <a href="<?php echo esc_url(home_url('/insurance-quotes')); ?>"
                               class="block p-4 bg-white rounded-lg shadow hover:shadow-md transition-shadow">
                                <h4 class="font-semibold text-primary mb-1"><?php esc_html_e('Insurance Quotes', 'safequote-traditional'); ?></h4>
                                <p class="text-sm text-gray-600"><?php esc_html_e('Compare insurance rates', 'safequote-traditional'); ?></p>
                            </a>
                            <a href="<?php echo esc_url(home_url('/safety-ratings')); ?>"
                               class="block p-4 bg-white rounded-lg shadow hover:shadow-md transition-shadow">
                                <h4 class="font-semibold text-primary mb-1"><?php esc_html_e('Safety Ratings', 'safequote-traditional'); ?></h4>
                                <p class="text-sm text-gray-600"><?php esc_html_e('View NHTSA safety scores', 'safequote-traditional'); ?></p>
                            </a>
                            <a href="<?php echo esc_url(home_url('/contact')); ?>"
                               class="block p-4 bg-white rounded-lg shadow hover:shadow-md transition-shadow">
                                <h4 class="font-semibold text-primary mb-1"><?php esc_html_e('Contact Us', 'safequote-traditional'); ?></h4>
                                <p class="text-sm text-gray-600"><?php esc_html_e('Get in touch with our team', 'safequote-traditional'); ?></p>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</main><!-- #primary -->

<?php
get_footer();