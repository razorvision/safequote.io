<?php
/**
 * Custom Post Types
 *
 * @package SafeQuote_Traditional
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register Vehicle Custom Post Type
 */
function safequote_register_vehicle_post_type() {
    $labels = array(
        'name'                  => _x('Vehicles', 'Post Type General Name', 'safequote-traditional'),
        'singular_name'         => _x('Vehicle', 'Post Type Singular Name', 'safequote-traditional'),
        'menu_name'             => __('Vehicles', 'safequote-traditional'),
        'name_admin_bar'        => __('Vehicle', 'safequote-traditional'),
        'archives'              => __('Vehicle Archives', 'safequote-traditional'),
        'attributes'            => __('Vehicle Attributes', 'safequote-traditional'),
        'parent_item_colon'     => __('Parent Vehicle:', 'safequote-traditional'),
        'all_items'             => __('All Vehicles', 'safequote-traditional'),
        'add_new_item'          => __('Add New Vehicle', 'safequote-traditional'),
        'add_new'               => __('Add New', 'safequote-traditional'),
        'new_item'              => __('New Vehicle', 'safequote-traditional'),
        'edit_item'             => __('Edit Vehicle', 'safequote-traditional'),
        'update_item'           => __('Update Vehicle', 'safequote-traditional'),
        'view_item'             => __('View Vehicle', 'safequote-traditional'),
        'view_items'            => __('View Vehicles', 'safequote-traditional'),
        'search_items'          => __('Search Vehicle', 'safequote-traditional'),
        'not_found'             => __('Not found', 'safequote-traditional'),
        'not_found_in_trash'    => __('Not found in Trash', 'safequote-traditional'),
        'featured_image'        => __('Vehicle Image', 'safequote-traditional'),
        'set_featured_image'    => __('Set vehicle image', 'safequote-traditional'),
        'remove_featured_image' => __('Remove vehicle image', 'safequote-traditional'),
        'use_featured_image'    => __('Use as vehicle image', 'safequote-traditional'),
        'insert_into_item'      => __('Insert into vehicle', 'safequote-traditional'),
        'uploaded_to_this_item' => __('Uploaded to this vehicle', 'safequote-traditional'),
        'items_list'            => __('Vehicles list', 'safequote-traditional'),
        'items_list_navigation' => __('Vehicles list navigation', 'safequote-traditional'),
        'filter_items_list'     => __('Filter vehicles list', 'safequote-traditional'),
    );

    $args = array(
        'label'                 => __('Vehicle', 'safequote-traditional'),
        'description'           => __('Vehicles for SafeQuote', 'safequote-traditional'),
        'labels'                => $labels,
        'supports'              => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields', 'revisions'),
        'taxonomies'            => array('vehicle_type', 'vehicle_make', 'vehicle_feature'),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 5,
        'menu_icon'             => 'dashicons-car',
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => 'vehicles',
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'post',
        'show_in_rest'          => true,
        'rewrite'               => array('slug' => 'vehicle', 'with_front' => false),
    );

    register_post_type('vehicle', $args);
}
add_action('init', 'safequote_register_vehicle_post_type', 0);

/**
 * Register Insurance Provider Custom Post Type
 */
function safequote_register_insurance_provider_post_type() {
    $labels = array(
        'name'                  => _x('Insurance Providers', 'Post Type General Name', 'safequote-traditional'),
        'singular_name'         => _x('Insurance Provider', 'Post Type Singular Name', 'safequote-traditional'),
        'menu_name'             => __('Insurance Providers', 'safequote-traditional'),
        'name_admin_bar'        => __('Insurance Provider', 'safequote-traditional'),
        'archives'              => __('Insurance Provider Archives', 'safequote-traditional'),
        'all_items'             => __('All Providers', 'safequote-traditional'),
        'add_new_item'          => __('Add New Provider', 'safequote-traditional'),
        'add_new'               => __('Add New', 'safequote-traditional'),
        'new_item'              => __('New Provider', 'safequote-traditional'),
        'edit_item'             => __('Edit Provider', 'safequote-traditional'),
        'update_item'           => __('Update Provider', 'safequote-traditional'),
        'view_item'             => __('View Provider', 'safequote-traditional'),
        'view_items'            => __('View Providers', 'safequote-traditional'),
        'search_items'          => __('Search Provider', 'safequote-traditional'),
        'not_found'             => __('Not found', 'safequote-traditional'),
        'not_found_in_trash'    => __('Not found in Trash', 'safequote-traditional'),
    );

    $args = array(
        'label'                 => __('Insurance Provider', 'safequote-traditional'),
        'description'           => __('Insurance Providers for SafeQuote', 'safequote-traditional'),
        'labels'                => $labels,
        'supports'              => array('title', 'editor', 'thumbnail', 'custom-fields'),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 6,
        'menu_icon'             => 'dashicons-shield',
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => 'insurance-providers',
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'post',
        'show_in_rest'          => true,
        'rewrite'               => array('slug' => 'insurance-provider', 'with_front' => false),
    );

    register_post_type('insurance_provider', $args);
}
add_action('init', 'safequote_register_insurance_provider_post_type', 0);

/**
 * Add Vehicle Meta Boxes
 */
function safequote_add_vehicle_meta_boxes() {
    add_meta_box(
        'vehicle_details',
        __('Vehicle Details', 'safequote-traditional'),
        'safequote_vehicle_details_callback',
        'vehicle',
        'normal',
        'high'
    );

    add_meta_box(
        'vehicle_specifications',
        __('Vehicle Specifications', 'safequote-traditional'),
        'safequote_vehicle_specifications_callback',
        'vehicle',
        'normal',
        'high'
    );

    add_meta_box(
        'vehicle_safety',
        __('Safety Ratings', 'safequote-traditional'),
        'safequote_vehicle_safety_callback',
        'vehicle',
        'side',
        'high'
    );
}
add_action('add_meta_boxes', 'safequote_add_vehicle_meta_boxes');

/**
 * Vehicle Details Meta Box Callback
 */
function safequote_vehicle_details_callback($post) {
    wp_nonce_field('safequote_vehicle_details', 'safequote_vehicle_details_nonce');

    $year = get_post_meta($post->ID, '_vehicle_year', true);
    $make = get_post_meta($post->ID, '_vehicle_make', true);
    $model = get_post_meta($post->ID, '_vehicle_model', true);
    $price = get_post_meta($post->ID, '_vehicle_price', true);
    $vin = get_post_meta($post->ID, '_vehicle_vin', true);
    ?>
    <table class="form-table">
        <tr>
            <th><label for="vehicle_year"><?php _e('Year', 'safequote-traditional'); ?></label></th>
            <td>
                <input type="number" id="vehicle_year" name="vehicle_year" value="<?php echo esc_attr($year); ?>" min="1990" max="<?php echo date('Y') + 1; ?>" class="regular-text" />
            </td>
        </tr>
        <tr>
            <th><label for="vehicle_make"><?php _e('Make', 'safequote-traditional'); ?></label></th>
            <td>
                <input type="text" id="vehicle_make" name="vehicle_make" value="<?php echo esc_attr($make); ?>" class="regular-text" />
            </td>
        </tr>
        <tr>
            <th><label for="vehicle_model"><?php _e('Model', 'safequote-traditional'); ?></label></th>
            <td>
                <input type="text" id="vehicle_model" name="vehicle_model" value="<?php echo esc_attr($model); ?>" class="regular-text" />
            </td>
        </tr>
        <tr>
            <th><label for="vehicle_price"><?php _e('Price', 'safequote-traditional'); ?></label></th>
            <td>
                <input type="number" id="vehicle_price" name="vehicle_price" value="<?php echo esc_attr($price); ?>" min="0" step="100" class="regular-text" />
            </td>
        </tr>
        <tr>
            <th><label for="vehicle_vin"><?php _e('VIN', 'safequote-traditional'); ?></label></th>
            <td>
                <input type="text" id="vehicle_vin" name="vehicle_vin" value="<?php echo esc_attr($vin); ?>" maxlength="17" class="regular-text" />
            </td>
        </tr>
    </table>
    <?php
}

/**
 * Vehicle Specifications Meta Box Callback
 */
function safequote_vehicle_specifications_callback($post) {
    wp_nonce_field('safequote_vehicle_specifications', 'safequote_vehicle_specifications_nonce');

    $engine = get_post_meta($post->ID, '_vehicle_engine', true);
    $transmission = get_post_meta($post->ID, '_vehicle_transmission', true);
    $fuel_type = get_post_meta($post->ID, '_vehicle_fuel_type', true);
    $mpg_city = get_post_meta($post->ID, '_vehicle_mpg_city', true);
    $mpg_highway = get_post_meta($post->ID, '_vehicle_mpg_highway', true);
    $doors = get_post_meta($post->ID, '_vehicle_doors', true);
    $seats = get_post_meta($post->ID, '_vehicle_seats', true);
    ?>
    <table class="form-table">
        <tr>
            <th><label for="vehicle_engine"><?php _e('Engine', 'safequote-traditional'); ?></label></th>
            <td>
                <input type="text" id="vehicle_engine" name="vehicle_engine" value="<?php echo esc_attr($engine); ?>" class="regular-text" />
            </td>
        </tr>
        <tr>
            <th><label for="vehicle_transmission"><?php _e('Transmission', 'safequote-traditional'); ?></label></th>
            <td>
                <select id="vehicle_transmission" name="vehicle_transmission">
                    <option value=""><?php _e('Select...', 'safequote-traditional'); ?></option>
                    <option value="manual" <?php selected($transmission, 'manual'); ?>><?php _e('Manual', 'safequote-traditional'); ?></option>
                    <option value="automatic" <?php selected($transmission, 'automatic'); ?>><?php _e('Automatic', 'safequote-traditional'); ?></option>
                    <option value="cvt" <?php selected($transmission, 'cvt'); ?>><?php _e('CVT', 'safequote-traditional'); ?></option>
                    <option value="dual-clutch" <?php selected($transmission, 'dual-clutch'); ?>><?php _e('Dual Clutch', 'safequote-traditional'); ?></option>
                </select>
            </td>
        </tr>
        <tr>
            <th><label for="vehicle_fuel_type"><?php _e('Fuel Type', 'safequote-traditional'); ?></label></th>
            <td>
                <select id="vehicle_fuel_type" name="vehicle_fuel_type">
                    <option value=""><?php _e('Select...', 'safequote-traditional'); ?></option>
                    <option value="gasoline" <?php selected($fuel_type, 'gasoline'); ?>><?php _e('Gasoline', 'safequote-traditional'); ?></option>
                    <option value="diesel" <?php selected($fuel_type, 'diesel'); ?>><?php _e('Diesel', 'safequote-traditional'); ?></option>
                    <option value="hybrid" <?php selected($fuel_type, 'hybrid'); ?>><?php _e('Hybrid', 'safequote-traditional'); ?></option>
                    <option value="electric" <?php selected($fuel_type, 'electric'); ?>><?php _e('Electric', 'safequote-traditional'); ?></option>
                    <option value="plug-in-hybrid" <?php selected($fuel_type, 'plug-in-hybrid'); ?>><?php _e('Plug-in Hybrid', 'safequote-traditional'); ?></option>
                </select>
            </td>
        </tr>
        <tr>
            <th><label for="vehicle_mpg"><?php _e('MPG', 'safequote-traditional'); ?></label></th>
            <td>
                <input type="number" id="vehicle_mpg_city" name="vehicle_mpg_city" value="<?php echo esc_attr($mpg_city); ?>" min="0" max="100" class="small-text" />
                <label for="vehicle_mpg_city"><?php _e('City', 'safequote-traditional'); ?></label>
                &nbsp;&nbsp;
                <input type="number" id="vehicle_mpg_highway" name="vehicle_mpg_highway" value="<?php echo esc_attr($mpg_highway); ?>" min="0" max="100" class="small-text" />
                <label for="vehicle_mpg_highway"><?php _e('Highway', 'safequote-traditional'); ?></label>
            </td>
        </tr>
        <tr>
            <th><label for="vehicle_doors"><?php _e('Doors', 'safequote-traditional'); ?></label></th>
            <td>
                <input type="number" id="vehicle_doors" name="vehicle_doors" value="<?php echo esc_attr($doors); ?>" min="2" max="5" class="small-text" />
            </td>
        </tr>
        <tr>
            <th><label for="vehicle_seats"><?php _e('Seats', 'safequote-traditional'); ?></label></th>
            <td>
                <input type="number" id="vehicle_seats" name="vehicle_seats" value="<?php echo esc_attr($seats); ?>" min="2" max="9" class="small-text" />
            </td>
        </tr>
    </table>
    <?php
}

/**
 * Vehicle Safety Meta Box Callback
 */
function safequote_vehicle_safety_callback($post) {
    wp_nonce_field('safequote_vehicle_safety', 'safequote_vehicle_safety_nonce');

    $overall_rating = get_post_meta($post->ID, '_vehicle_safety_overall', true);
    $front_crash = get_post_meta($post->ID, '_vehicle_safety_front_crash', true);
    $side_crash = get_post_meta($post->ID, '_vehicle_safety_side_crash', true);
    $rollover = get_post_meta($post->ID, '_vehicle_safety_rollover', true);
    ?>
    <p>
        <label for="vehicle_safety_overall"><?php _e('Overall Rating', 'safequote-traditional'); ?></label><br>
        <select id="vehicle_safety_overall" name="vehicle_safety_overall" style="width: 100%;">
            <option value=""><?php _e('Not Rated', 'safequote-traditional'); ?></option>
            <?php for ($i = 1; $i <= 5; $i++) : ?>
                <option value="<?php echo $i; ?>" <?php selected($overall_rating, $i); ?>><?php echo str_repeat('★', $i) . str_repeat('☆', 5 - $i); ?></option>
            <?php endfor; ?>
        </select>
    </p>
    <p>
        <label for="vehicle_safety_front_crash"><?php _e('Front Crash', 'safequote-traditional'); ?></label><br>
        <select id="vehicle_safety_front_crash" name="vehicle_safety_front_crash" style="width: 100%;">
            <option value=""><?php _e('Not Rated', 'safequote-traditional'); ?></option>
            <?php for ($i = 1; $i <= 5; $i++) : ?>
                <option value="<?php echo $i; ?>" <?php selected($front_crash, $i); ?>><?php echo str_repeat('★', $i) . str_repeat('☆', 5 - $i); ?></option>
            <?php endfor; ?>
        </select>
    </p>
    <p>
        <label for="vehicle_safety_side_crash"><?php _e('Side Crash', 'safequote-traditional'); ?></label><br>
        <select id="vehicle_safety_side_crash" name="vehicle_safety_side_crash" style="width: 100%;">
            <option value=""><?php _e('Not Rated', 'safequote-traditional'); ?></option>
            <?php for ($i = 1; $i <= 5; $i++) : ?>
                <option value="<?php echo $i; ?>" <?php selected($side_crash, $i); ?>><?php echo str_repeat('★', $i) . str_repeat('☆', 5 - $i); ?></option>
            <?php endfor; ?>
        </select>
    </p>
    <p>
        <label for="vehicle_safety_rollover"><?php _e('Rollover', 'safequote-traditional'); ?></label><br>
        <select id="vehicle_safety_rollover" name="vehicle_safety_rollover" style="width: 100%;">
            <option value=""><?php _e('Not Rated', 'safequote-traditional'); ?></option>
            <?php for ($i = 1; $i <= 5; $i++) : ?>
                <option value="<?php echo $i; ?>" <?php selected($rollover, $i); ?>><?php echo str_repeat('★', $i) . str_repeat('☆', 5 - $i); ?></option>
            <?php endfor; ?>
        </select>
    </p>
    <?php
}

/**
 * Save Vehicle Meta Box Data
 */
function safequote_save_vehicle_meta($post_id) {
    // Check if nonce is set
    if (!isset($_POST['safequote_vehicle_details_nonce'])) {
        return;
    }

    // Verify nonce
    if (!wp_verify_nonce($_POST['safequote_vehicle_details_nonce'], 'safequote_vehicle_details')) {
        return;
    }

    // Check autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Check permissions
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Save Vehicle Details
    $details_fields = array(
        'vehicle_year' => '_vehicle_year',
        'vehicle_make' => '_vehicle_make',
        'vehicle_model' => '_vehicle_model',
        'vehicle_price' => '_vehicle_price',
        'vehicle_vin' => '_vehicle_vin',
    );

    foreach ($details_fields as $field => $meta_key) {
        if (isset($_POST[$field])) {
            update_post_meta($post_id, $meta_key, sanitize_text_field($_POST[$field]));
        }
    }

    // Save Specifications
    if (isset($_POST['safequote_vehicle_specifications_nonce']) &&
        wp_verify_nonce($_POST['safequote_vehicle_specifications_nonce'], 'safequote_vehicle_specifications')) {

        $spec_fields = array(
            'vehicle_engine' => '_vehicle_engine',
            'vehicle_transmission' => '_vehicle_transmission',
            'vehicle_fuel_type' => '_vehicle_fuel_type',
            'vehicle_mpg_city' => '_vehicle_mpg_city',
            'vehicle_mpg_highway' => '_vehicle_mpg_highway',
            'vehicle_doors' => '_vehicle_doors',
            'vehicle_seats' => '_vehicle_seats',
        );

        foreach ($spec_fields as $field => $meta_key) {
            if (isset($_POST[$field])) {
                update_post_meta($post_id, $meta_key, sanitize_text_field($_POST[$field]));
            }
        }
    }

    // Save Safety Ratings
    if (isset($_POST['safequote_vehicle_safety_nonce']) &&
        wp_verify_nonce($_POST['safequote_vehicle_safety_nonce'], 'safequote_vehicle_safety')) {

        $safety_fields = array(
            'vehicle_safety_overall' => '_vehicle_safety_overall',
            'vehicle_safety_front_crash' => '_vehicle_safety_front_crash',
            'vehicle_safety_side_crash' => '_vehicle_safety_side_crash',
            'vehicle_safety_rollover' => '_vehicle_safety_rollover',
        );

        foreach ($safety_fields as $field => $meta_key) {
            if (isset($_POST[$field])) {
                update_post_meta($post_id, $meta_key, sanitize_text_field($_POST[$field]));
            }
        }
    }
}
add_action('save_post_vehicle', 'safequote_save_vehicle_meta');