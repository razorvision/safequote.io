<?php
/**
 * AJAX Handlers
 *
 * @package SafeQuote_Traditional
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Search vehicles AJAX handler - queries NHTSA database with filtering
 */
function safequote_ajax_search_vehicles() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'safequote_ajax_nonce')) {
        wp_die('Security check failed');
    }

    // Get search parameters (all optional)
    $year = isset($_POST['year']) && !empty($_POST['year']) ? intval($_POST['year']) : null;
    $make = isset($_POST['make']) && !empty($_POST['make']) ? sanitize_text_field($_POST['make']) : null;
    $model = isset($_POST['model']) && !empty($_POST['model']) ? sanitize_text_field($_POST['model']) : null;
    $min_rating = isset($_POST['minSafetyRating']) ? floatval($_POST['minSafetyRating']) : 0;

    // Query NHTSA database with filters
    require_once SAFEQUOTE_THEME_DIR . '/inc/vehicle-data-nhtsa.php';

    // Build search args - only include non-null values
    $search_args = array('limit' => 12);
    if ($year) $search_args['year'] = $year;
    if ($make) $search_args['make'] = $make;
    if ($model) $search_args['model'] = $model;
    if ($min_rating > 0) $search_args['min_rating'] = $min_rating;

    $vehicles = safequote_get_vehicles_from_nhtsa($search_args);

    wp_send_json_success(array(
        'vehicles' => $vehicles,
        'count' => count($vehicles),
    ));
}
add_action('wp_ajax_search_vehicles', 'safequote_ajax_search_vehicles');
add_action('wp_ajax_nopriv_search_vehicles', 'safequote_ajax_search_vehicles');

/**
 * Get vehicle details AJAX handler
 */
function safequote_ajax_get_vehicle_details() {
    // Verify nonce
    if (!isset($_GET['nonce']) || !wp_verify_nonce($_GET['nonce'], 'safequote_ajax_nonce')) {
        wp_die('Security check failed');
    }

    $vehicle_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    if (!$vehicle_id) {
        wp_send_json_error('Invalid vehicle ID');
    }

    $post = get_post($vehicle_id);

    if (!$post || $post->post_type !== 'vehicle') {
        wp_send_json_error('Vehicle not found');
    }

    // Get all vehicle data
    $vehicle_data = array(
        'id' => $vehicle_id,
        'title' => get_the_title($vehicle_id),
        'description' => apply_filters('the_content', $post->post_content),
        'year' => get_post_meta($vehicle_id, '_vehicle_year', true),
        'make' => get_post_meta($vehicle_id, '_vehicle_make', true),
        'model' => get_post_meta($vehicle_id, '_vehicle_model', true),
        'price' => get_post_meta($vehicle_id, '_vehicle_price', true),
        'vin' => get_post_meta($vehicle_id, '_vehicle_vin', true),
        'engine' => get_post_meta($vehicle_id, '_vehicle_engine', true),
        'transmission' => get_post_meta($vehicle_id, '_vehicle_transmission', true),
        'fuelType' => get_post_meta($vehicle_id, '_vehicle_fuel_type', true),
        'mpgCity' => get_post_meta($vehicle_id, '_vehicle_mpg_city', true),
        'mpgHighway' => get_post_meta($vehicle_id, '_vehicle_mpg_highway', true),
        'doors' => get_post_meta($vehicle_id, '_vehicle_doors', true),
        'seats' => get_post_meta($vehicle_id, '_vehicle_seats', true),
        'safetyRating' => get_post_meta($vehicle_id, '_vehicle_safety_overall', true),
        'frontCrash' => get_post_meta($vehicle_id, '_vehicle_safety_front_crash', true),
        'sideCrash' => get_post_meta($vehicle_id, '_vehicle_safety_side_crash', true),
        'rollover' => get_post_meta($vehicle_id, '_vehicle_safety_rollover', true),
        'image' => get_the_post_thumbnail_url($vehicle_id, 'full'),
        'gallery' => array(),
        'features' => wp_get_post_terms($vehicle_id, 'vehicle_feature', array('fields' => 'names')),
    );

    // Get gallery images
    $gallery_ids = get_post_meta($vehicle_id, '_vehicle_gallery', true);
    if (!empty($gallery_ids)) {
        $gallery_ids = explode(',', $gallery_ids);
        foreach ($gallery_ids as $image_id) {
            $vehicle_data['gallery'][] = wp_get_attachment_url($image_id);
        }
    }

    wp_send_json_success($vehicle_data);
}
add_action('wp_ajax_get_vehicle_details', 'safequote_ajax_get_vehicle_details');
add_action('wp_ajax_nopriv_get_vehicle_details', 'safequote_ajax_get_vehicle_details');

/**
 * Submit insurance quote AJAX handler
 */
function safequote_ajax_submit_insurance_quote() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'safequote_ajax_nonce')) {
        wp_die('Security check failed');
    }

    // Validate required fields
    $required_fields = array('firstName', 'lastName', 'email', 'phone', 'dob', 'coverage');
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            wp_send_json_error('Please fill in all required fields');
        }
    }

    // Sanitize input
    $quote_data = array(
        'first_name' => sanitize_text_field($_POST['firstName']),
        'last_name' => sanitize_text_field($_POST['lastName']),
        'email' => sanitize_email($_POST['email']),
        'phone' => sanitize_text_field($_POST['phone']),
        'dob' => sanitize_text_field($_POST['dob']),
        'experience' => isset($_POST['experience']) ? intval($_POST['experience']) : 0,
        'coverage' => sanitize_text_field($_POST['coverage']),
        'vehicle_id' => isset($_POST['vehicleId']) ? intval($_POST['vehicleId']) : 0,
    );

    // Save quote request (you might want to save to database or send email)
    $saved = safequote_save_quote_request($quote_data);

    if ($saved) {
        // Send email notification
        safequote_send_quote_email($quote_data);

        // Generate sample quotes (in real app, this would connect to insurance APIs)
        $quotes = safequote_generate_sample_quotes($quote_data);

        wp_send_json_success(array(
            'message' => 'Quote request submitted successfully!',
            'quotes' => $quotes,
        ));
    } else {
        wp_send_json_error('Failed to submit quote request');
    }
}
add_action('wp_ajax_submit_insurance_quote', 'safequote_ajax_submit_insurance_quote');
add_action('wp_ajax_nopriv_submit_insurance_quote', 'safequote_ajax_submit_insurance_quote');

/**
 * Submit contact form AJAX handler
 */
function safequote_ajax_submit_contact() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'safequote_ajax_nonce')) {
        wp_die('Security check failed');
    }

    // Validate required fields
    $required_fields = array('name', 'email', 'message');
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            wp_send_json_error('Please fill in all required fields');
        }
    }

    // Sanitize input
    $contact_data = array(
        'name' => sanitize_text_field($_POST['name']),
        'email' => sanitize_email($_POST['email']),
        'phone' => isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '',
        'subject' => isset($_POST['subject']) ? sanitize_text_field($_POST['subject']) : 'Contact Form Submission',
        'message' => sanitize_textarea_field($_POST['message']),
    );

    // Send email
    $to = get_option('admin_email');
    $subject = '[SafeQuote] ' . $contact_data['subject'];
    $message = sprintf(
        "Name: %s\nEmail: %s\nPhone: %s\n\nMessage:\n%s",
        $contact_data['name'],
        $contact_data['email'],
        $contact_data['phone'],
        $contact_data['message']
    );
    $headers = array(
        'From: ' . $contact_data['name'] . ' <' . $contact_data['email'] . '>',
        'Reply-To: ' . $contact_data['email'],
    );

    $sent = wp_mail($to, $subject, $message, $headers);

    if ($sent) {
        wp_send_json_success('Message sent successfully!');
    } else {
        wp_send_json_error('Failed to send message. Please try again.');
    }
}
add_action('wp_ajax_submit_contact', 'safequote_ajax_submit_contact');
add_action('wp_ajax_nopriv_submit_contact', 'safequote_ajax_submit_contact');

/**
 * Get makes from NHTSA database - with optional year filter
 */
function safequote_ajax_get_makes() {
    // Verify nonce
    if (!isset($_GET['nonce']) || !wp_verify_nonce($_GET['nonce'], 'safequote_ajax_nonce')) {
        wp_die('Security check failed');
    }

    $year = isset($_GET['year']) && !empty($_GET['year']) ? intval($_GET['year']) : null;

    global $wpdb;
    $table_name = $wpdb->prefix . 'nhtsa_vehicle_cache';

    // Cache key based on year filter
    $cache_key = 'safequote_makes' . ($year ? '_' . $year : '_all');
    $makes = get_transient($cache_key);

    if (false === $makes) {
        $sql = "SELECT DISTINCT make FROM {$table_name} WHERE make IS NOT NULL";
        if ($year) {
            $sql .= $wpdb->prepare(" AND year = %d", $year);
        }
        $sql .= " ORDER BY make ASC";

        $results = $wpdb->get_results($sql, ARRAY_A);
        $makes = array();
        foreach ($results as $row) {
            $makes[] = array('id' => sanitize_title($row['make']), 'name' => $row['make']);
        }
        set_transient($cache_key, $makes, DAY_IN_SECONDS);
    }

    wp_send_json_success($makes);
}
add_action('wp_ajax_get_makes', 'safequote_ajax_get_makes');
add_action('wp_ajax_nopriv_get_makes', 'safequote_ajax_get_makes');

/**
 * Get models from NHTSA database - with optional year filter
 */
function safequote_ajax_get_models() {
    // Verify nonce
    if (!isset($_GET['nonce']) || !wp_verify_nonce($_GET['nonce'], 'safequote_ajax_nonce')) {
        wp_die('Security check failed');
    }

    $year = isset($_GET['year']) && !empty($_GET['year']) ? intval($_GET['year']) : null;
    $make = isset($_GET['make']) && !empty($_GET['make']) ? sanitize_text_field($_GET['make']) : '';

    // Make is required to get models
    if (!$make) {
        wp_send_json_error('Make is required');
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'nhtsa_vehicle_cache';

    // Cache key based on make and optional year filter
    $cache_key = 'safequote_models_' . sanitize_title($make) . ($year ? '_' . $year : '_all');
    $models = get_transient($cache_key);

    if (false === $models) {
        $sql = $wpdb->prepare(
            "SELECT DISTINCT model FROM {$table_name} WHERE make = %s AND model IS NOT NULL",
            $make
        );
        if ($year) {
            $sql .= $wpdb->prepare(" AND year = %d", $year);
        }
        $sql .= " ORDER BY model ASC";

        $results = $wpdb->get_results($sql, ARRAY_A);
        $models = array();
        foreach ($results as $row) {
            $models[] = array(
                'id' => sanitize_title($row['model']),
                'name' => $row['model'],
            );
        }
        set_transient($cache_key, $models, DAY_IN_SECONDS);
    }

    wp_send_json_success($models);
}
add_action('wp_ajax_get_models', 'safequote_ajax_get_models');
add_action('wp_ajax_nopriv_get_models', 'safequote_ajax_get_models');

/**
 * Get available years from NHTSA database - with optional make filter
 */
function safequote_ajax_get_years() {
    // Verify nonce
    if (!isset($_GET['nonce']) || !wp_verify_nonce($_GET['nonce'], 'safequote_ajax_nonce')) {
        wp_die('Security check failed');
    }

    $make = isset($_GET['make']) && !empty($_GET['make']) ? sanitize_text_field($_GET['make']) : null;

    global $wpdb;
    $table_name = $wpdb->prefix . 'nhtsa_vehicle_cache';

    // Cache key based on make filter
    $cache_key = 'safequote_years' . ($make ? '_' . sanitize_title($make) : '_all');
    $years = get_transient($cache_key);

    if (false === $years) {
        $sql = "SELECT DISTINCT year FROM {$table_name} WHERE year IS NOT NULL";
        if ($make) {
            $sql .= $wpdb->prepare(" AND make = %s", $make);
        }
        $sql .= " ORDER BY year DESC";

        $results = $wpdb->get_results($sql, ARRAY_A);
        $years = array();
        foreach ($results as $row) {
            $years[] = array('id' => $row['year'], 'name' => $row['year']);
        }
        set_transient($cache_key, $years, DAY_IN_SECONDS);
    }

    wp_send_json_success($years);
}
add_action('wp_ajax_get_years', 'safequote_ajax_get_years');
add_action('wp_ajax_nopriv_get_years', 'safequote_ajax_get_years');

/**
 * Get NHTSA rating for vehicle AJAX handler
 *
 * Queries wp_nhtsa_vehicle_cache directly with LIKE matching on model
 * to return ALL matching variants (e.g., CAMRY and CAMRY HYBRID).
 */
function safequote_ajax_get_nhtsa_rating() {
    // Verify nonce (accepts both safequote_ajax_nonce and safequote_top_picks_nonce for backward compatibility)
    if (!isset($_GET['nonce'])) {
        wp_send_json_error('Security check failed');
    }

    $nonce_valid = wp_verify_nonce($_GET['nonce'], 'safequote_ajax_nonce') ||
                   wp_verify_nonce($_GET['nonce'], 'safequote_top_picks_nonce');

    if (!$nonce_valid) {
        wp_send_json_error('Security check failed');
    }

    $year = isset($_GET['year']) ? intval($_GET['year']) : 0;
    $make = isset($_GET['make']) ? sanitize_text_field($_GET['make']) : '';
    $model = isset($_GET['model']) ? sanitize_text_field($_GET['model']) : '';

    if (!$year || !$make || !$model) {
        wp_send_json_error('Year, make, and model are required');
    }

    global $wpdb;
    $table = $wpdb->prefix . 'nhtsa_vehicle_cache';

    // Query database with LIKE on model to get all variants (e.g., CAMRY, CAMRY HYBRID)
    $results = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table
         WHERE year = %d
         AND UPPER(make) = UPPER(%s)
         AND UPPER(model) LIKE UPPER(%s)
         ORDER BY model ASC",
        $year,
        $make,
        $model . '%'
    ));

    // Transform each row to rating format with nhtsa_data JSON merged
    $vehicles = array();
    if ($results) {
        foreach ($results as $row) {
            $nhtsa_data = json_decode($row->nhtsa_data, true) ?: array();
            // Merge nhtsa_data with row-level fields (row fields take precedence for key columns)
            $vehicle = array_merge($nhtsa_data, array(
                'vehicle_id'              => $row->vehicle_id,
                'ModelYear'               => (int) $row->year,
                'Make'                    => $row->make,
                'Model'                   => $row->model,
                'OverallRating'           => $row->nhtsa_overall_rating,
                'OverallFrontCrashRating' => $row->front_crash,
                'OverallSideCrashRating'  => $row->side_crash,
                'RolloverRating'          => $row->rollover_crash,
                'VehiclePicture'          => $row->vehicle_picture,
                'rating_source'           => $row->rating_source,
            ));
            $vehicles[] = $vehicle;
        }
    }

    // Return array of all matching vehicles
    wp_send_json_success($vehicles);
}
add_action('wp_ajax_get_nhtsa_rating', 'safequote_ajax_get_nhtsa_rating');
add_action('wp_ajax_nopriv_get_nhtsa_rating', 'safequote_ajax_get_nhtsa_rating');

/**
 * Helper function to save quote request
 */
function safequote_save_quote_request($data) {
    // In a real application, you would save this to a custom table or post type
    // For now, we'll save as a custom post type
    $post_data = array(
        'post_title' => $data['first_name'] . ' ' . $data['last_name'] . ' - ' . date('Y-m-d H:i:s'),
        'post_content' => json_encode($data),
        'post_type' => 'quote_request',
        'post_status' => 'private',
    );

    $post_id = wp_insert_post($post_data);

    if ($post_id) {
        // Save meta data
        foreach ($data as $key => $value) {
            update_post_meta($post_id, '_quote_' . $key, $value);
        }
        return true;
    }

    return false;
}

/**
 * Helper function to send quote email
 */
function safequote_send_quote_email($data) {
    $to = $data['email'];
    $subject = 'Your SafeQuote Insurance Quote Request';
    $message = sprintf(
        "Dear %s %s,\n\nThank you for requesting an insurance quote through SafeQuote.\n\nWe have received your request and our team will review it shortly. You will receive personalized quotes from our partner insurance providers within 24-48 hours.\n\nRequest Details:\nCoverage Type: %s\nDriving Experience: %d years\n\nIf you have any questions, please don't hesitate to contact us.\n\nBest regards,\nThe SafeQuote Team",
        $data['first_name'],
        $data['last_name'],
        ucfirst($data['coverage']),
        $data['experience']
    );

    $headers = array(
        'From: SafeQuote <noreply@safequote.io>',
        'Content-Type: text/plain; charset=UTF-8',
    );

    wp_mail($to, $subject, $message, $headers);

    // Also send to admin
    $admin_email = get_option('admin_email');
    $admin_subject = '[SafeQuote] New Quote Request';
    $admin_message = sprintf(
        "New quote request received:\n\nName: %s %s\nEmail: %s\nPhone: %s\nDOB: %s\nExperience: %d years\nCoverage: %s\nVehicle ID: %d",
        $data['first_name'],
        $data['last_name'],
        $data['email'],
        $data['phone'],
        $data['dob'],
        $data['experience'],
        $data['coverage'],
        $data['vehicle_id']
    );

    wp_mail($admin_email, $admin_subject, $admin_message, $headers);
}

/**
 * Helper function to generate sample quotes
 */
function safequote_generate_sample_quotes($data) {
    // In a real application, this would connect to insurance provider APIs
    // For demo purposes, we'll generate sample quotes
    $providers = array(
        array('name' => 'GEICO', 'monthly' => rand(80, 150), 'rating' => 4.5),
        array('name' => 'Progressive', 'monthly' => rand(85, 160), 'rating' => 4.3),
        array('name' => 'State Farm', 'monthly' => rand(90, 170), 'rating' => 4.6),
        array('name' => 'Allstate', 'monthly' => rand(95, 180), 'rating' => 4.2),
    );

    $quotes = array();
    foreach ($providers as $provider) {
        $quotes[] = array(
            'provider' => $provider['name'],
            'monthly' => $provider['monthly'],
            'annual' => $provider['monthly'] * 12,
            'rating' => $provider['rating'],
            'coverage' => ucfirst($data['coverage']),
        );
    }

    // Sort by monthly premium
    usort($quotes, function($a, $b) {
        return $a['monthly'] - $b['monthly'];
    });

    return $quotes;
}