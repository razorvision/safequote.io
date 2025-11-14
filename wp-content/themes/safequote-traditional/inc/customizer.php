<?php
/**
 * SafeQuote Traditional Theme Customizer
 *
 * @package SafeQuote_Traditional
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add postMessage support for site title and description for the Theme Customizer
 */
function safequote_customize_register($wp_customize) {
    $wp_customize->get_setting('blogname')->transport         = 'postMessage';
    $wp_customize->get_setting('blogdescription')->transport  = 'postMessage';
    $wp_customize->get_setting('header_textcolor')->transport = 'postMessage';

    // Add SafeQuote Panel
    $wp_customize->add_panel('safequote_panel', array(
        'priority'    => 30,
        'capability'  => 'edit_theme_options',
        'title'       => __('SafeQuote Settings', 'safequote-traditional'),
        'description' => __('Customize SafeQuote theme settings', 'safequote-traditional'),
    ));

    // Colors Section
    $wp_customize->add_section('safequote_colors', array(
        'title'    => __('Theme Colors', 'safequote-traditional'),
        'panel'    => 'safequote_panel',
        'priority' => 30,
    ));

    // Primary Color
    $wp_customize->add_setting('primary_color', array(
        'default'           => '#3B82F6',
        'sanitize_callback' => 'sanitize_hex_color',
        'transport'         => 'postMessage',
    ));

    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'primary_color', array(
        'label'    => __('Primary Color', 'safequote-traditional'),
        'section'  => 'safequote_colors',
        'settings' => 'primary_color',
    )));

    // Secondary Color
    $wp_customize->add_setting('secondary_color', array(
        'default'           => '#10B981',
        'sanitize_callback' => 'sanitize_hex_color',
        'transport'         => 'postMessage',
    ));

    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'secondary_color', array(
        'label'    => __('Secondary Color', 'safequote-traditional'),
        'section'  => 'safequote_colors',
        'settings' => 'secondary_color',
    )));

    // Header Settings
    $wp_customize->add_section('safequote_header', array(
        'title'    => __('Header Settings', 'safequote-traditional'),
        'panel'    => 'safequote_panel',
        'priority' => 35,
    ));

    // Sticky Header
    $wp_customize->add_setting('sticky_header', array(
        'default'           => true,
        'sanitize_callback' => 'safequote_sanitize_checkbox',
    ));

    $wp_customize->add_control('sticky_header', array(
        'type'     => 'checkbox',
        'label'    => __('Enable Sticky Header', 'safequote-traditional'),
        'section'  => 'safequote_header',
        'settings' => 'sticky_header',
    ));

    // Header Layout
    $wp_customize->add_setting('header_layout', array(
        'default'           => 'default',
        'sanitize_callback' => 'safequote_sanitize_select',
    ));

    $wp_customize->add_control('header_layout', array(
        'type'     => 'select',
        'label'    => __('Header Layout', 'safequote-traditional'),
        'section'  => 'safequote_header',
        'settings' => 'header_layout',
        'choices'  => array(
            'default'     => __('Default', 'safequote-traditional'),
            'centered'    => __('Centered', 'safequote-traditional'),
            'transparent' => __('Transparent', 'safequote-traditional'),
        ),
    ));

    // Footer Settings
    $wp_customize->add_section('safequote_footer', array(
        'title'    => __('Footer Settings', 'safequote-traditional'),
        'panel'    => 'safequote_panel',
        'priority' => 40,
    ));

    // Footer Copyright Text
    $wp_customize->add_setting('footer_copyright', array(
        'default'           => '',
        'sanitize_callback' => 'wp_kses_post',
        'transport'         => 'postMessage',
    ));

    $wp_customize->add_control('footer_copyright', array(
        'type'        => 'textarea',
        'label'       => __('Copyright Text', 'safequote-traditional'),
        'description' => __('Leave empty to use default copyright text', 'safequote-traditional'),
        'section'     => 'safequote_footer',
        'settings'    => 'footer_copyright',
    ));

    // Footer Columns
    $wp_customize->add_setting('footer_columns', array(
        'default'           => '4',
        'sanitize_callback' => 'safequote_sanitize_select',
    ));

    $wp_customize->add_control('footer_columns', array(
        'type'     => 'select',
        'label'    => __('Footer Widget Columns', 'safequote-traditional'),
        'section'  => 'safequote_footer',
        'settings' => 'footer_columns',
        'choices'  => array(
            '1' => __('1 Column', 'safequote-traditional'),
            '2' => __('2 Columns', 'safequote-traditional'),
            '3' => __('3 Columns', 'safequote-traditional'),
            '4' => __('4 Columns', 'safequote-traditional'),
        ),
    ));

    // Social Media Links
    $wp_customize->add_section('safequote_social', array(
        'title'    => __('Social Media Links', 'safequote-traditional'),
        'panel'    => 'safequote_panel',
        'priority' => 45,
    ));

    $social_networks = array(
        'facebook'  => __('Facebook URL', 'safequote-traditional'),
        'twitter'   => __('Twitter URL', 'safequote-traditional'),
        'linkedin'  => __('LinkedIn URL', 'safequote-traditional'),
        'instagram' => __('Instagram URL', 'safequote-traditional'),
        'youtube'   => __('YouTube URL', 'safequote-traditional'),
    );

    foreach ($social_networks as $network => $label) {
        $wp_customize->add_setting("social_$network", array(
            'default'           => '',
            'sanitize_callback' => 'esc_url_raw',
        ));

        $wp_customize->add_control("social_$network", array(
            'type'     => 'url',
            'label'    => $label,
            'section'  => 'safequote_social',
            'settings' => "social_$network",
        ));
    }

    // Layout Settings
    $wp_customize->add_section('safequote_layout', array(
        'title'    => __('Layout Settings', 'safequote-traditional'),
        'panel'    => 'safequote_panel',
        'priority' => 50,
    ));

    // Container Width
    $wp_customize->add_setting('container_width', array(
        'default'           => '1200',
        'sanitize_callback' => 'absint',
        'transport'         => 'postMessage',
    ));

    $wp_customize->add_control('container_width', array(
        'type'        => 'number',
        'label'       => __('Container Width (px)', 'safequote-traditional'),
        'section'     => 'safequote_layout',
        'settings'    => 'container_width',
        'input_attrs' => array(
            'min'  => 1000,
            'max'  => 1920,
            'step' => 10,
        ),
    ));

    // Sidebar Position
    $wp_customize->add_setting('sidebar_position', array(
        'default'           => 'right',
        'sanitize_callback' => 'safequote_sanitize_select',
    ));

    $wp_customize->add_control('sidebar_position', array(
        'type'     => 'select',
        'label'    => __('Sidebar Position', 'safequote-traditional'),
        'section'  => 'safequote_layout',
        'settings' => 'sidebar_position',
        'choices'  => array(
            'none'  => __('No Sidebar', 'safequote-traditional'),
            'left'  => __('Left Sidebar', 'safequote-traditional'),
            'right' => __('Right Sidebar', 'safequote-traditional'),
        ),
    ));

    // Typography Settings
    $wp_customize->add_section('safequote_typography', array(
        'title'    => __('Typography', 'safequote-traditional'),
        'panel'    => 'safequote_panel',
        'priority' => 55,
    ));

    // Body Font
    $wp_customize->add_setting('body_font', array(
        'default'           => 'Inter',
        'sanitize_callback' => 'safequote_sanitize_select',
        'transport'         => 'postMessage',
    ));

    $wp_customize->add_control('body_font', array(
        'type'     => 'select',
        'label'    => __('Body Font', 'safequote-traditional'),
        'section'  => 'safequote_typography',
        'settings' => 'body_font',
        'choices'  => array(
            'Inter'      => 'Inter',
            'Roboto'     => 'Roboto',
            'Open Sans'  => 'Open Sans',
            'Lato'       => 'Lato',
            'Montserrat' => 'Montserrat',
            'System'     => 'System Fonts',
        ),
    ));

    // Heading Font
    $wp_customize->add_setting('heading_font', array(
        'default'           => 'Inter',
        'sanitize_callback' => 'safequote_sanitize_select',
        'transport'         => 'postMessage',
    ));

    $wp_customize->add_control('heading_font', array(
        'type'     => 'select',
        'label'    => __('Heading Font', 'safequote-traditional'),
        'section'  => 'safequote_typography',
        'settings' => 'heading_font',
        'choices'  => array(
            'Inter'      => 'Inter',
            'Roboto'     => 'Roboto',
            'Open Sans'  => 'Open Sans',
            'Lato'       => 'Lato',
            'Montserrat' => 'Montserrat',
            'Playfair Display' => 'Playfair Display',
            'System'     => 'System Fonts',
        ),
    ));

    // Vehicle Settings
    $wp_customize->add_section('safequote_vehicles', array(
        'title'       => __('Vehicle Display Settings', 'safequote-traditional'),
        'panel'       => 'safequote_panel',
        'priority'    => 60,
        'description' => __('Configure how vehicles are displayed on your site', 'safequote-traditional'),
    ));

    // Vehicles per page
    $wp_customize->add_setting('vehicles_per_page', array(
        'default'           => 12,
        'sanitize_callback' => 'absint',
    ));

    $wp_customize->add_control('vehicles_per_page', array(
        'type'        => 'number',
        'label'       => __('Vehicles Per Page', 'safequote-traditional'),
        'section'     => 'safequote_vehicles',
        'settings'    => 'vehicles_per_page',
        'input_attrs' => array(
            'min'  => 6,
            'max'  => 30,
            'step' => 3,
        ),
    ));

    // Show safety ratings
    $wp_customize->add_setting('show_safety_ratings', array(
        'default'           => true,
        'sanitize_callback' => 'safequote_sanitize_checkbox',
    ));

    $wp_customize->add_control('show_safety_ratings', array(
        'type'     => 'checkbox',
        'label'    => __('Show Safety Ratings', 'safequote-traditional'),
        'section'  => 'safequote_vehicles',
        'settings' => 'show_safety_ratings',
    ));

    // Show compare button
    $wp_customize->add_setting('show_compare_button', array(
        'default'           => true,
        'sanitize_callback' => 'safequote_sanitize_checkbox',
    ));

    $wp_customize->add_control('show_compare_button', array(
        'type'     => 'checkbox',
        'label'    => __('Show Compare Button', 'safequote-traditional'),
        'section'  => 'safequote_vehicles',
        'settings' => 'show_compare_button',
    ));
}
add_action('customize_register', 'safequote_customize_register');

/**
 * Sanitize checkbox
 */
function safequote_sanitize_checkbox($checked) {
    return ((isset($checked) && true == $checked) ? true : false);
}

/**
 * Sanitize select
 */
function safequote_sanitize_select($input, $setting) {
    $input = sanitize_key($input);
    $choices = $setting->manager->get_control($setting->id)->choices;
    return (array_key_exists($input, $choices) ? $input : $setting->default);
}

/**
 * Bind JS handlers to make Theme Customizer preview reload changes asynchronously
 */
function safequote_customize_preview_js() {
    wp_enqueue_script('safequote-customizer', get_template_directory_uri() . '/assets/js/customizer.js', array('customize-preview'), SAFEQUOTE_THEME_VERSION, true);
}
add_action('customize_preview_init', 'safequote_customize_preview_js');

/**
 * Output Customizer CSS in header
 * DISABLED - Using compiled Tailwind CSS for all colors and styling
 * The customizer function is kept for reference but not hooked to wp_head
 */
function safequote_customizer_css() {
    // Disabled to allow compiled Tailwind CSS to control all styling
    // Previously output custom colors and container width
}