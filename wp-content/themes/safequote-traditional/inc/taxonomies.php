<?php
/**
 * Custom Taxonomies
 *
 * @package SafeQuote_Traditional
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register Vehicle Type Taxonomy
 */
function safequote_register_vehicle_type_taxonomy() {
    $labels = array(
        'name'                       => _x('Vehicle Types', 'Taxonomy General Name', 'safequote-traditional'),
        'singular_name'              => _x('Vehicle Type', 'Taxonomy Singular Name', 'safequote-traditional'),
        'menu_name'                  => __('Vehicle Types', 'safequote-traditional'),
        'all_items'                  => __('All Vehicle Types', 'safequote-traditional'),
        'parent_item'                => __('Parent Vehicle Type', 'safequote-traditional'),
        'parent_item_colon'          => __('Parent Vehicle Type:', 'safequote-traditional'),
        'new_item_name'              => __('New Vehicle Type Name', 'safequote-traditional'),
        'add_new_item'               => __('Add New Vehicle Type', 'safequote-traditional'),
        'edit_item'                  => __('Edit Vehicle Type', 'safequote-traditional'),
        'update_item'                => __('Update Vehicle Type', 'safequote-traditional'),
        'view_item'                  => __('View Vehicle Type', 'safequote-traditional'),
        'separate_items_with_commas' => __('Separate types with commas', 'safequote-traditional'),
        'add_or_remove_items'        => __('Add or remove types', 'safequote-traditional'),
        'choose_from_most_used'      => __('Choose from the most used', 'safequote-traditional'),
        'popular_items'              => __('Popular Vehicle Types', 'safequote-traditional'),
        'search_items'               => __('Search Vehicle Types', 'safequote-traditional'),
        'not_found'                  => __('Not Found', 'safequote-traditional'),
        'no_terms'                   => __('No vehicle types', 'safequote-traditional'),
        'items_list'                 => __('Vehicle types list', 'safequote-traditional'),
        'items_list_navigation'      => __('Vehicle types list navigation', 'safequote-traditional'),
    );

    $args = array(
        'labels'                     => $labels,
        'hierarchical'               => true,
        'public'                     => true,
        'show_ui'                    => true,
        'show_admin_column'          => true,
        'show_in_nav_menus'          => true,
        'show_tagcloud'              => true,
        'show_in_rest'               => true,
        'rewrite'                    => array('slug' => 'vehicle-type', 'with_front' => false),
    );

    register_taxonomy('vehicle_type', array('vehicle'), $args);

    // Insert default terms
    $default_types = array(
        'Sedan',
        'SUV',
        'Truck',
        'Coupe',
        'Convertible',
        'Hatchback',
        'Wagon',
        'Minivan',
        'Sports Car',
        'Luxury',
        'Hybrid',
        'Electric',
    );

    foreach ($default_types as $type) {
        if (!term_exists($type, 'vehicle_type')) {
            wp_insert_term($type, 'vehicle_type');
        }
    }
}
add_action('init', 'safequote_register_vehicle_type_taxonomy', 0);

/**
 * Register Vehicle Make Taxonomy
 */
function safequote_register_vehicle_make_taxonomy() {
    $labels = array(
        'name'                       => _x('Vehicle Makes', 'Taxonomy General Name', 'safequote-traditional'),
        'singular_name'              => _x('Vehicle Make', 'Taxonomy Singular Name', 'safequote-traditional'),
        'menu_name'                  => __('Vehicle Makes', 'safequote-traditional'),
        'all_items'                  => __('All Makes', 'safequote-traditional'),
        'parent_item'                => __('Parent Make', 'safequote-traditional'),
        'parent_item_colon'          => __('Parent Make:', 'safequote-traditional'),
        'new_item_name'              => __('New Make Name', 'safequote-traditional'),
        'add_new_item'               => __('Add New Make', 'safequote-traditional'),
        'edit_item'                  => __('Edit Make', 'safequote-traditional'),
        'update_item'                => __('Update Make', 'safequote-traditional'),
        'view_item'                  => __('View Make', 'safequote-traditional'),
        'separate_items_with_commas' => __('Separate makes with commas', 'safequote-traditional'),
        'add_or_remove_items'        => __('Add or remove makes', 'safequote-traditional'),
        'choose_from_most_used'      => __('Choose from the most used', 'safequote-traditional'),
        'popular_items'              => __('Popular Makes', 'safequote-traditional'),
        'search_items'               => __('Search Makes', 'safequote-traditional'),
        'not_found'                  => __('Not Found', 'safequote-traditional'),
        'no_terms'                   => __('No makes', 'safequote-traditional'),
        'items_list'                 => __('Makes list', 'safequote-traditional'),
        'items_list_navigation'      => __('Makes list navigation', 'safequote-traditional'),
    );

    $args = array(
        'labels'                     => $labels,
        'hierarchical'               => true,
        'public'                     => true,
        'show_ui'                    => true,
        'show_admin_column'          => true,
        'show_in_nav_menus'          => true,
        'show_tagcloud'              => false,
        'show_in_rest'               => true,
        'rewrite'                    => array('slug' => 'make', 'with_front' => false),
    );

    register_taxonomy('vehicle_make', array('vehicle'), $args);

    // Insert default popular makes
    $default_makes = array(
        'Acura',
        'Audi',
        'BMW',
        'Buick',
        'Cadillac',
        'Chevrolet',
        'Chrysler',
        'Dodge',
        'Ford',
        'GMC',
        'Honda',
        'Hyundai',
        'Infiniti',
        'Jaguar',
        'Jeep',
        'Kia',
        'Land Rover',
        'Lexus',
        'Lincoln',
        'Mazda',
        'Mercedes-Benz',
        'Mitsubishi',
        'Nissan',
        'Porsche',
        'Ram',
        'Subaru',
        'Tesla',
        'Toyota',
        'Volkswagen',
        'Volvo',
    );

    foreach ($default_makes as $make) {
        if (!term_exists($make, 'vehicle_make')) {
            wp_insert_term($make, 'vehicle_make');
        }
    }
}
add_action('init', 'safequote_register_vehicle_make_taxonomy', 0);

/**
 * Register Vehicle Feature Taxonomy
 */
function safequote_register_vehicle_feature_taxonomy() {
    $labels = array(
        'name'                       => _x('Vehicle Features', 'Taxonomy General Name', 'safequote-traditional'),
        'singular_name'              => _x('Vehicle Feature', 'Taxonomy Singular Name', 'safequote-traditional'),
        'menu_name'                  => __('Vehicle Features', 'safequote-traditional'),
        'all_items'                  => __('All Features', 'safequote-traditional'),
        'parent_item'                => __('Parent Feature', 'safequote-traditional'),
        'parent_item_colon'          => __('Parent Feature:', 'safequote-traditional'),
        'new_item_name'              => __('New Feature Name', 'safequote-traditional'),
        'add_new_item'               => __('Add New Feature', 'safequote-traditional'),
        'edit_item'                  => __('Edit Feature', 'safequote-traditional'),
        'update_item'                => __('Update Feature', 'safequote-traditional'),
        'view_item'                  => __('View Feature', 'safequote-traditional'),
        'separate_items_with_commas' => __('Separate features with commas', 'safequote-traditional'),
        'add_or_remove_items'        => __('Add or remove features', 'safequote-traditional'),
        'choose_from_most_used'      => __('Choose from the most used', 'safequote-traditional'),
        'popular_items'              => __('Popular Features', 'safequote-traditional'),
        'search_items'               => __('Search Features', 'safequote-traditional'),
        'not_found'                  => __('Not Found', 'safequote-traditional'),
        'no_terms'                   => __('No features', 'safequote-traditional'),
        'items_list'                 => __('Features list', 'safequote-traditional'),
        'items_list_navigation'      => __('Features list navigation', 'safequote-traditional'),
    );

    $args = array(
        'labels'                     => $labels,
        'hierarchical'               => false,
        'public'                     => true,
        'show_ui'                    => true,
        'show_admin_column'          => true,
        'show_in_nav_menus'          => true,
        'show_tagcloud'              => true,
        'show_in_rest'               => true,
        'rewrite'                    => array('slug' => 'feature', 'with_front' => false),
    );

    register_taxonomy('vehicle_feature', array('vehicle'), $args);

    // Insert default features
    $default_features = array(
        // Safety Features
        'ABS Brakes',
        'Adaptive Cruise Control',
        'Blind Spot Monitoring',
        'Forward Collision Warning',
        'Lane Departure Warning',
        'Automatic Emergency Braking',
        'Backup Camera',
        'Parking Sensors',
        '360-Degree Camera',
        'Airbags (Front)',
        'Airbags (Side)',
        'Airbags (Curtain)',

        // Comfort Features
        'Leather Seats',
        'Heated Seats',
        'Ventilated Seats',
        'Power Seats',
        'Memory Seats',
        'Sunroof/Moonroof',
        'Panoramic Roof',
        'Dual-Zone Climate Control',
        'Tri-Zone Climate Control',
        'Remote Start',
        'Keyless Entry',
        'Push Button Start',

        // Technology Features
        'Navigation System',
        'Apple CarPlay',
        'Android Auto',
        'Bluetooth Connectivity',
        'USB Ports',
        'Wireless Charging',
        'Premium Audio System',
        'WiFi Hotspot',
        'Digital Dashboard',
        'Head-Up Display',

        // Performance Features
        'All-Wheel Drive',
        'Four-Wheel Drive',
        'Turbo Engine',
        'Sport Mode',
        'Performance Brakes',
        'Limited Slip Differential',
        'Adaptive Suspension',

        // Convenience Features
        'Power Liftgate',
        'Hands-Free Liftgate',
        'Roof Rails',
        'Towing Package',
        'Third Row Seating',
        'Folding Rear Seats',
    );

    foreach ($default_features as $feature) {
        if (!term_exists($feature, 'vehicle_feature')) {
            wp_insert_term($feature, 'vehicle_feature');
        }
    }
}
add_action('init', 'safequote_register_vehicle_feature_taxonomy', 0);

/**
 * Register Coverage Type Taxonomy for Insurance Providers
 */
function safequote_register_coverage_type_taxonomy() {
    $labels = array(
        'name'                       => _x('Coverage Types', 'Taxonomy General Name', 'safequote-traditional'),
        'singular_name'              => _x('Coverage Type', 'Taxonomy Singular Name', 'safequote-traditional'),
        'menu_name'                  => __('Coverage Types', 'safequote-traditional'),
        'all_items'                  => __('All Coverage Types', 'safequote-traditional'),
        'parent_item'                => __('Parent Coverage Type', 'safequote-traditional'),
        'parent_item_colon'          => __('Parent Coverage Type:', 'safequote-traditional'),
        'new_item_name'              => __('New Coverage Type Name', 'safequote-traditional'),
        'add_new_item'               => __('Add New Coverage Type', 'safequote-traditional'),
        'edit_item'                  => __('Edit Coverage Type', 'safequote-traditional'),
        'update_item'                => __('Update Coverage Type', 'safequote-traditional'),
        'view_item'                  => __('View Coverage Type', 'safequote-traditional'),
        'separate_items_with_commas' => __('Separate coverage types with commas', 'safequote-traditional'),
        'add_or_remove_items'        => __('Add or remove coverage types', 'safequote-traditional'),
        'choose_from_most_used'      => __('Choose from the most used', 'safequote-traditional'),
        'popular_items'              => __('Popular Coverage Types', 'safequote-traditional'),
        'search_items'               => __('Search Coverage Types', 'safequote-traditional'),
        'not_found'                  => __('Not Found', 'safequote-traditional'),
        'no_terms'                   => __('No coverage types', 'safequote-traditional'),
        'items_list'                 => __('Coverage types list', 'safequote-traditional'),
        'items_list_navigation'      => __('Coverage types list navigation', 'safequote-traditional'),
    );

    $args = array(
        'labels'                     => $labels,
        'hierarchical'               => true,
        'public'                     => true,
        'show_ui'                    => true,
        'show_admin_column'          => true,
        'show_in_nav_menus'          => true,
        'show_tagcloud'              => false,
        'show_in_rest'               => true,
        'rewrite'                    => array('slug' => 'coverage-type', 'with_front' => false),
    );

    register_taxonomy('coverage_type', array('insurance_provider'), $args);

    // Insert default coverage types
    $default_coverage = array(
        'Liability Coverage' => array(
            'Bodily Injury Liability',
            'Property Damage Liability',
        ),
        'Collision Coverage' => array(),
        'Comprehensive Coverage' => array(),
        'Personal Injury Protection' => array(),
        'Uninsured/Underinsured Motorist' => array(
            'Uninsured Motorist Bodily Injury',
            'Uninsured Motorist Property Damage',
            'Underinsured Motorist Coverage',
        ),
        'Medical Payments Coverage' => array(),
        'Additional Coverage' => array(
            'Rental Car Coverage',
            'Roadside Assistance',
            'Gap Insurance',
            'New Car Replacement',
            'Accident Forgiveness',
            'Vanishing Deductible',
        ),
    );

    foreach ($default_coverage as $parent => $children) {
        if (!term_exists($parent, 'coverage_type')) {
            $parent_term = wp_insert_term($parent, 'coverage_type');

            if (!is_wp_error($parent_term) && !empty($children)) {
                foreach ($children as $child) {
                    if (!term_exists($child, 'coverage_type')) {
                        wp_insert_term($child, 'coverage_type', array(
                            'parent' => $parent_term['term_id']
                        ));
                    }
                }
            }
        }
    }
}
add_action('init', 'safequote_register_coverage_type_taxonomy', 0);

/**
 * Add custom columns to vehicle admin list
 */
function safequote_add_vehicle_columns($columns) {
    $new_columns = array();

    foreach ($columns as $key => $value) {
        if ($key == 'title') {
            $new_columns[$key] = $value;
            $new_columns['vehicle_year'] = __('Year', 'safequote-traditional');
            $new_columns['vehicle_make'] = __('Make', 'safequote-traditional');
            $new_columns['vehicle_price'] = __('Price', 'safequote-traditional');
            $new_columns['vehicle_safety'] = __('Safety Rating', 'safequote-traditional');
        } else {
            $new_columns[$key] = $value;
        }
    }

    return $new_columns;
}
add_filter('manage_vehicle_posts_columns', 'safequote_add_vehicle_columns');

/**
 * Populate custom columns in vehicle admin list
 */
function safequote_vehicle_custom_columns($column, $post_id) {
    switch ($column) {
        case 'vehicle_year':
            echo esc_html(get_post_meta($post_id, '_vehicle_year', true));
            break;

        case 'vehicle_make':
            echo esc_html(get_post_meta($post_id, '_vehicle_make', true));
            break;

        case 'vehicle_price':
            $price = get_post_meta($post_id, '_vehicle_price', true);
            if ($price) {
                echo '$' . number_format($price);
            }
            break;

        case 'vehicle_safety':
            $rating = get_post_meta($post_id, '_vehicle_safety_overall', true);
            if ($rating) {
                echo str_repeat('★', $rating) . str_repeat('☆', 5 - $rating);
            } else {
                echo __('Not Rated', 'safequote-traditional');
            }
            break;
    }
}
add_action('manage_vehicle_posts_custom_column', 'safequote_vehicle_custom_columns', 10, 2);

/**
 * Make custom columns sortable
 */
function safequote_sortable_vehicle_columns($columns) {
    $columns['vehicle_year'] = 'vehicle_year';
    $columns['vehicle_price'] = 'vehicle_price';
    $columns['vehicle_safety'] = 'vehicle_safety';

    return $columns;
}
add_filter('manage_edit-vehicle_sortable_columns', 'safequote_sortable_vehicle_columns');

/**
 * Handle sorting of custom columns
 */
function safequote_vehicle_orderby($query) {
    if (!is_admin() || !$query->is_main_query()) {
        return;
    }

    $orderby = $query->get('orderby');

    switch ($orderby) {
        case 'vehicle_year':
            $query->set('meta_key', '_vehicle_year');
            $query->set('orderby', 'meta_value_num');
            break;

        case 'vehicle_price':
            $query->set('meta_key', '_vehicle_price');
            $query->set('orderby', 'meta_value_num');
            break;

        case 'vehicle_safety':
            $query->set('meta_key', '_vehicle_safety_overall');
            $query->set('orderby', 'meta_value_num');
            break;
    }
}
add_action('pre_get_posts', 'safequote_vehicle_orderby');