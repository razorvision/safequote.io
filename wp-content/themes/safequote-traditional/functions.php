<?php
/**
 * SafeQuote Traditional Theme Functions
 *
 * @package SafeQuote_Traditional
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Theme constants
 */
define('SAFEQUOTE_THEME_VERSION', '1.0.0');
define('SAFEQUOTE_THEME_DIR', get_template_directory());
define('SAFEQUOTE_THEME_URI', get_template_directory_uri());

/**
 * Theme setup
 */
function safequote_theme_setup() {
    // Add theme support for title tag
    add_theme_support('title-tag');

    // Add theme support for post thumbnails
    add_theme_support('post-thumbnails');

    // Add theme support for custom logo
    add_theme_support('custom-logo', array(
        'height'      => 100,
        'width'       => 400,
        'flex-height' => true,
        'flex-width'  => true,
        'header-text' => array('site-title', 'site-description'),
    ));

    // Add theme support for HTML5 markup
    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'script',
        'style',
        'navigation-widgets',
    ));

    // Add theme support for selective refresh widgets
    add_theme_support('customize-selective-refresh-widgets');

    // Add support for responsive embeds
    add_theme_support('responsive-embeds');

    // Add support for block styles
    add_theme_support('wp-block-styles');

    // Add support for align wide
    add_theme_support('align-wide');

    // Add support for editor styles
    add_theme_support('editor-styles');
    add_editor_style('assets/css/editor-style.css');

    // Register navigation menus
    register_nav_menus(array(
        'primary'   => __('Primary Menu', 'safequote-traditional'),
        'secondary' => __('Secondary Menu', 'safequote-traditional'),
        'footer'    => __('Footer Menu', 'safequote-traditional'),
    ));

    // Set content width
    if (!isset($content_width)) {
        $content_width = 1280;
    }
}
add_action('after_setup_theme', 'safequote_theme_setup');

/**
 * Enqueue styles
 */
function safequote_enqueue_styles() {
    // Google Fonts - Inter
    wp_enqueue_style(
        'safequote-google-fonts',
        'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap',
        array(),
        null
    );

    // Compiled Tailwind CSS (production-ready)
    wp_enqueue_style(
        'tailwind-compiled',
        SAFEQUOTE_THEME_URI . '/assets/css/tailwind.css',
        array(),
        filemtime(SAFEQUOTE_THEME_DIR . '/assets/css/tailwind.css')
    );

    // Main stylesheet with cache busting
    wp_enqueue_style(
        'safequote-main-style',
        SAFEQUOTE_THEME_URI . '/assets/css/main.css',
        array(),
        filemtime(SAFEQUOTE_THEME_DIR . '/assets/css/main.css')
    );

    // Components stylesheet - CRITICAL for visual parity
    wp_enqueue_style(
        'safequote-components',
        SAFEQUOTE_THEME_URI . '/assets/css/components.css',
        array('safequote-main-style'),
        filemtime(SAFEQUOTE_THEME_DIR . '/assets/css/components.css')
    );

    // Animations stylesheet - CRITICAL for visual parity
    wp_enqueue_style(
        'safequote-animations',
        SAFEQUOTE_THEME_URI . '/assets/css/animations.css',
        array('safequote-components'),
        filemtime(SAFEQUOTE_THEME_DIR . '/assets/css/animations.css')
    );

    // Theme style.css (required by WordPress, contains only metadata)
    wp_enqueue_style(
        'safequote-theme-style',
        get_stylesheet_uri(),
        array(),
        filemtime(get_stylesheet_directory() . '/style.css')
    );
}
add_action('wp_enqueue_scripts', 'safequote_enqueue_styles');

/**
 * Enqueue scripts
 */
function safequote_enqueue_scripts() {
    // Main JavaScript file
    wp_enqueue_script(
        'safequote-main',
        SAFEQUOTE_THEME_URI . '/assets/js/main.js',
        array(),
        SAFEQUOTE_THEME_VERSION,
        true
    );

    // Vehicle filters module
    wp_enqueue_script(
        'safequote-filters',
        SAFEQUOTE_THEME_URI . '/assets/js/filters.js',
        array('safequote-main'),
        SAFEQUOTE_THEME_VERSION,
        true
    );

    // Modal functionality
    wp_enqueue_script(
        'safequote-modals',
        SAFEQUOTE_THEME_URI . '/assets/js/modals.js',
        array('safequote-main'),
        SAFEQUOTE_THEME_VERSION,
        true
    );

    // Animation handler
    wp_enqueue_script(
        'safequote-animations',
        SAFEQUOTE_THEME_URI . '/assets/js/animations.js',
        array('safequote-main'),
        SAFEQUOTE_THEME_VERSION,
        true
    );

    // NHTSA API integration
    wp_enqueue_script(
        'safequote-nhtsa-api',
        SAFEQUOTE_THEME_URI . '/assets/js/nhtsa-api.js',
        array('safequote-main'),
        SAFEQUOTE_THEME_VERSION,
        true
    );

    // Insurance comparison functionality
    wp_enqueue_script(
        'safequote-insurance',
        SAFEQUOTE_THEME_URI . '/assets/js/insurance.js',
        array('safequote-main'),
        SAFEQUOTE_THEME_VERSION,
        true
    );

    // Notifications system
    wp_enqueue_script(
        'safequote-notifications',
        SAFEQUOTE_THEME_URI . '/assets/js/notifications.js',
        array('safequote-main'),
        SAFEQUOTE_THEME_VERSION,
        true
    );

    // Forms handler
    wp_enqueue_script(
        'safequote-forms',
        SAFEQUOTE_THEME_URI . '/assets/js/forms.js',
        array('safequote-main'),
        SAFEQUOTE_THEME_VERSION,
        true
    );

    // Localize script for AJAX
    wp_localize_script('safequote-main', 'safequote_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('safequote_ajax_nonce'),
        'theme_url' => SAFEQUOTE_THEME_URI,
        'api_endpoints' => array(
            'nhtsa' => 'https://api.nhtsa.gov/SafetyRatings/modelyear',
        ),
    ));

    // Comment reply script
    if (is_singular() && comments_open() && get_option('thread_comments')) {
        wp_enqueue_script('comment-reply');
    }
}
add_action('wp_enqueue_scripts', 'safequote_enqueue_scripts');

/**
 * Register widget areas
 */
function safequote_widgets_init() {
    register_sidebar(array(
        'name'          => __('Primary Sidebar', 'safequote-traditional'),
        'id'            => 'sidebar-1',
        'description'   => __('Add widgets here to appear in your sidebar.', 'safequote-traditional'),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h2 class="widget-title">',
        'after_title'   => '</h2>',
    ));

    register_sidebar(array(
        'name'          => __('Footer Widget Area 1', 'safequote-traditional'),
        'id'            => 'footer-1',
        'description'   => __('Add widgets here to appear in your footer.', 'safequote-traditional'),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ));

    register_sidebar(array(
        'name'          => __('Footer Widget Area 2', 'safequote-traditional'),
        'id'            => 'footer-2',
        'description'   => __('Add widgets here to appear in your footer.', 'safequote-traditional'),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ));

    register_sidebar(array(
        'name'          => __('Footer Widget Area 3', 'safequote-traditional'),
        'id'            => 'footer-3',
        'description'   => __('Add widgets here to appear in your footer.', 'safequote-traditional'),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ));

    register_sidebar(array(
        'name'          => __('Footer Widget Area 4', 'safequote-traditional'),
        'id'            => 'footer-4',
        'description'   => __('Add widgets here to appear in your footer.', 'safequote-traditional'),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ));
}
add_action('widgets_init', 'safequote_widgets_init');

/**
 * Custom body classes
 */
function safequote_body_classes($classes) {
    // Add class for singular pages
    if (is_singular()) {
        $classes[] = 'singular';
    }

    // Add class for home page
    if (is_front_page()) {
        $classes[] = 'front-page';
    }

    // Add class if sidebar is active
    if (is_active_sidebar('sidebar-1')) {
        $classes[] = 'has-sidebar';
    }

    return $classes;
}
add_filter('body_class', 'safequote_body_classes');

/**
 * Customize excerpt length
 */
function safequote_excerpt_length($length) {
    return 20;
}
add_filter('excerpt_length', 'safequote_excerpt_length');

/**
 * Customize excerpt more string
 */
function safequote_excerpt_more($more) {
    return '...';
}
add_filter('excerpt_more', 'safequote_excerpt_more');

/**
 * Get sample insurance quotes for display
 *
 * Returns sample insurance provider quotes (matches React insuranceData.js)
 */
function safequote_get_sample_insurance_quotes($vehicle = array()) {
    // Default vehicle if not provided
    if (empty($vehicle)) {
        $vehicle = array(
            'make' => 'your',
            'model' => 'car',
            'year' => 2024,
            'condition' => 'used',
            'safetyRating' => 4
        );
    }

    // Base monthly price calculation
    $base_price = 150; // Base price

    // Adjust for condition
    if (isset($vehicle['condition'])) {
        if ('new' === $vehicle['condition']) {
            $base_price = 165;
        } elseif ('preowned' === $vehicle['condition']) {
            $base_price = 155;
        }
    }

    // Adjust for safety rating
    if (isset($vehicle['safetyRating']) && $vehicle['safetyRating'] >= 5) {
        $base_price -= 20; // Teen driver discount
    }

    return array(
        array(
            'provider'     => 'SafeGuard Insurance',
            'url'          => 'https://www.progressive.com/',
            'monthlyPrice' => $base_price - 2,
            'rating'       => 4.8,
            'discount'     => isset($vehicle['safetyRating']) && $vehicle['safetyRating'] >= 5 ? 15 : 0,
            'recommended'  => true,
            'coverage'     => array(
                'Liability Coverage up to $100k',
                'Collision Coverage',
                'Comprehensive Coverage',
                '24/7 Roadside Assistance',
                'Teen Driver Discount'
            ),
        ),
        array(
            'provider'     => 'SecureRide Auto',
            'url'          => 'https://www.statefarm.com/',
            'monthlyPrice' => $base_price + 5,
            'rating'       => 4.6,
            'discount'     => isset($vehicle['safetyRating']) && $vehicle['safetyRating'] >= 5 ? 10 : 0,
            'recommended'  => false,
            'coverage'     => array(
                'Liability Coverage up to $100k',
                'Collision Coverage',
                'Uninsured Motorist Protection',
                'Online Account Management',
                'Teen Driver Monitoring'
            ),
        ),
        array(
            'provider'     => 'DriveGuard Protection',
            'url'          => 'https://www.allstate.com/',
            'monthlyPrice' => $base_price + 12,
            'rating'       => 4.5,
            'discount'     => isset($vehicle['safetyRating']) && $vehicle['safetyRating'] >= 5 ? 12 : 0,
            'recommended'  => false,
            'coverage'     => array(
                'Liability Coverage up to $100k',
                'Collision and Comprehensive',
                'Medical Payments Coverage',
                'Accident Forgiveness',
                'Young Driver Safety Course Discount'
            ),
        ),
    );
}

/**
 * Custom template tags
 */
require_once SAFEQUOTE_THEME_DIR . '/inc/template-tags.php';

/**
 * Custom functions that act independently of the theme templates
 */
require_once SAFEQUOTE_THEME_DIR . '/inc/extras.php';

/**
 * Customizer additions
 */
require_once SAFEQUOTE_THEME_DIR . '/inc/customizer.php';

/**
 * Load custom post types
 */
require_once SAFEQUOTE_THEME_DIR . '/inc/post-types.php';

/**
 * Load custom taxonomies
 */
require_once SAFEQUOTE_THEME_DIR . '/inc/taxonomies.php';

/**
 * Load AJAX handlers
 */
require_once SAFEQUOTE_THEME_DIR . '/inc/ajax-handlers.php';