<?php
/**
 * SafeQuote Theme Functions and Definitions
 *
 * @package SafeQuote
 */

/**
 * Enqueue React application assets from Vite build
 *
 * This function reads the Vite manifest.json to get the built JavaScript and CSS files,
 * then enqueues them with proper WordPress functions.
 */
function safequote_enqueue_react_app() {
	$theme_dir = get_template_directory();
	$theme_uri = get_template_directory_uri();
	$manifest_path = $theme_dir . '/build/manifest.json';

	// Check if manifest file exists
	if ( ! file_exists( $manifest_path ) ) {
		// If manifest doesn't exist, the app hasn't been built yet
		// Output a notice for development
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'SafeQuote: build/manifest.json not found. Run "npm run build" in the react-app directory.' );
		}
		return;
	}

	// Read manifest file
	$manifest = json_decode( file_get_contents( $manifest_path ), true );

	if ( ! is_array( $manifest ) ) {
		error_log( 'SafeQuote: Failed to parse build/manifest.json' );
		return;
	}

	// Enqueue main React app JavaScript and CSS from Vite manifest
	// The manifest contains all built assets from Vite

	// Look for the main entry point (either src/main.jsx or index.html depending on config)
	$main_entry = isset( $manifest['src/main.jsx'] )
		? $manifest['src/main.jsx']
		: ( isset( $manifest['index.html'] ) ? $manifest['index.html'] : null );

	if ( $main_entry ) {
		// Enqueue the main JS file
		wp_enqueue_script(
			'safequote-app',
			$theme_uri . '/build/' . $main_entry['file'],
			array(),
			null,
			true // Load in footer
		);

		// Enqueue CSS files associated with the main entry
		if ( isset( $main_entry['css'] ) && is_array( $main_entry['css'] ) ) {
			foreach ( $main_entry['css'] as $index => $css_file ) {
				wp_enqueue_style(
					'safequote-app-css-' . $index,
					$theme_uri . '/build/' . $css_file
				);
			}
		}
	}

	// Enqueue CSS from other entries if they exist and weren't already enqueued
	if ( isset( $manifest['index.css'] ) && ! isset( $main_entry['css'] ) ) {
		$index_css = $manifest['index.css'];
		wp_enqueue_style(
			'safequote-index-css',
			$theme_uri . '/build/' . $index_css['file']
		);
	}
}

// Hook into wp_enqueue_scripts to load our assets
add_action( 'wp_enqueue_scripts', 'safequote_enqueue_react_app' );

/**
 * Remove unnecessary WordPress frontend items for cleaner React experience
 */
function safequote_remove_default_styles() {
	// Remove unnecessary styles that might conflict with React app
	wp_dequeue_style( 'dashicons' );
	wp_dequeue_style( 'global-styles' );
}

add_action( 'wp_enqueue_scripts', 'safequote_remove_default_styles', 100 );

/**
 * Customize document title
 */
function safequote_custom_title( $title, $sep ) {
	$title = 'SafeQuote - Vehicle Safety Ratings & Insurance Comparison';
	return $title;
}

add_filter( 'document_title_parts', 'safequote_custom_title', 10, 2 );

/**
 * Add custom meta tags
 */
function safequote_add_meta_tags() {
	?>
	<meta name="description" content="SafeQuote helps you find safe vehicles and compare insurance quotes with real-time NHTSA safety ratings.">
	<meta name="theme-color" content="#1e293b">
	<?php
}

add_action( 'wp_head', 'safequote_add_meta_tags' );

/**
 * Disable certain WordPress features that might interfere with React SPA
 */
function safequote_remove_admin_bar() {
	show_admin_bar( false );
}

add_action( 'wp_loaded', 'safequote_remove_admin_bar' );
