<?php
/**
 * Vehicle Data
 *
 * Static vehicle data array with utility functions.
 * TODO: Replace with Custom Post Type "Vehicles" in future phase
 *
 * @package SafeQuote_Traditional
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get all vehicles with optional filtering
 *
 * @param array $args {
 *     Optional. Array of query arguments.
 *
 *     @type string $type          Vehicle type (sedan, suv, truck, etc.)
 *     @type string $condition     Vehicle condition (new, preowned)
 *     @type int    $safety_rating Minimum safety rating (1-5)
 *     @type int    $min_price     Minimum price
 *     @type int    $max_price     Maximum price
 *     @type string $make          Vehicle make
 *     @type string $model         Vehicle model
 *     @type int    $year          Vehicle year
 *     @type string $search        Search keyword
 * }
 * @return array Array of vehicle data
 */
function safequote_get_vehicles($args = array()) {
    // Static vehicle data
    // TODO: Replace with WP_Query when Vehicles CPT is created
    $vehicles = array(
        array(
            'id' => 1,
            'make' => 'Honda',
            'model' => 'Civic',
            'year' => 2024,
            'condition' => 'New',
            'type' => 'Sedan',
            'price' => 25000,
            'mileage' => 15,
            'safety_rating' => 5,
            'image' => 'https://imagedelivery.net/LqiWLm-3MGbYHtFuUbcBtA/11a56110-85f2-4543-78b1-2856f616e400/public',
            'safety_features' => array(
                'Forward Collision Warning',
                'Lane Departure Warning',
                'Automatic Emergency Braking',
                'Blind Spot Monitoring'
            )
        ),
        array(
            'id' => 2,
            'make' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2023,
            'condition' => 'Preowned',
            'type' => 'Sedan',
            'price' => 21000,
            'mileage' => 12000,
            'safety_rating' => 5,
            'image' => 'https://imagedelivery.net/LqiWLm-3MGbYHtFuUbcBtA/170b0965-3c12-421b-4399-6d635c05c700/public',
            'safety_features' => array(
                'Toyota Safety Sense',
                'Adaptive Cruise Control',
                'Lane Keeping Assist',
                'Pre-Collision System'
            )
        ),
        array(
            'id' => 3,
            'make' => 'Mazda',
            'model' => 'CX-5',
            'year' => 2024,
            'condition' => 'New',
            'type' => 'SUV',
            'price' => 32000,
            'mileage' => 8,
            'safety_rating' => 5,
            'image' => 'https://imagedelivery.net/LqiWLm-3MGbYHtFuUbcBtA/489b4f42-a8c9-4467-27b3-c15bdc0f4f00/public',
            'safety_features' => array(
                'Smart Brake Support',
                'Blind Spot Monitoring',
                'Rear Cross Traffic Alert',
                'Lane Keep Assist'
            )
        ),
        array(
            'id' => 4,
            'make' => 'Subaru',
            'model' => 'Outback',
            'year' => 2023,
            'condition' => 'Preowned',
            'type' => 'SUV',
            'price' => 28000,
            'mileage' => 18000,
            'safety_rating' => 5,
            'image' => 'https://imagedelivery.net/LqiWLm-3MGbYHtFuUbcBtA/f3563428-1110-4c31-7ab3-e29ed80a4a00/public',
            'safety_features' => array(
                'EyeSight Driver Assist',
                'Reverse Automatic Braking',
                'Blind Spot Detection',
                'Rear Cross Traffic Alert'
            )
        ),
        array(
            'id' => 5,
            'make' => 'Hyundai',
            'model' => 'Elantra',
            'year' => 2024,
            'condition' => 'New',
            'type' => 'Sedan',
            'price' => 23000,
            'mileage' => 5,
            'safety_rating' => 4,
            'image' => 'https://imagedelivery.net/LqiWLm-3MGbYHtFuUbcBtA/99e2b173-1991-4c07-b648-b3d952613d00/public',
            'safety_features' => array(
                'Forward Collision-Avoidance Assist',
                'Lane Keeping Assist',
                'Driver Attention Warning',
                'Blind-Spot Collision Warning'
            )
        ),
        array(
            'id' => 6,
            'make' => 'Volkswagen',
            'model' => 'Golf',
            'year' => 2022,
            'condition' => 'Preowned',
            'type' => 'Hatchback',
            'price' => 19000,
            'mileage' => 25000,
            'safety_rating' => 4,
            'image' => 'https://imagedelivery.net/LqiWLm-3MGbYHtFuUbcBtA/4c1b1836-9b6f-4029-798c-85949d873000/public',
            'safety_features' => array(
                'Automatic Post-Collision Braking',
                'Blind Spot Monitor',
                'Rear Traffic Alert',
                'Forward Collision Warning'
            )
        ),
        array(
            'id' => 7,
            'make' => 'Kia',
            'model' => 'Forte',
            'year' => 2024,
            'condition' => 'New',
            'type' => 'Sedan',
            'price' => 22000,
            'mileage' => 10,
            'safety_rating' => 4,
            'image' => 'https://imagedelivery.net/LqiWLm-3MGbYHtFuUbcBtA/7f12330a-1158-4503-4966-38600d350b00/public',
            'safety_features' => array(
                'Forward Collision Warning',
                'Lane Departure Warning',
                'Driver Attention Warning',
                'Rear Cross-Traffic Collision Warning'
            )
        ),
        array(
            'id' => 8,
            'make' => 'Nissan',
            'model' => 'Rogue',
            'year' => 2023,
            'condition' => 'Preowned',
            'type' => 'SUV',
            'price' => 27000,
            'mileage' => 15000,
            'safety_rating' => 4,
            'image' => 'https://imagedelivery.net/LqiWLm-3MGbYHtFuUbcBtA/15a13c9e-f4e9-4081-49e0-f2034033b000/public',
            'safety_features' => array(
                'Automatic Emergency Braking',
                'Blind Spot Warning',
                'Rear Cross Traffic Alert',
                'Lane Departure Warning'
            )
        ),
        array(
            'id' => 9,
            'make' => 'Honda',
            'model' => 'CR-V',
            'year' => 2024,
            'condition' => 'New',
            'type' => 'SUV',
            'price' => 33000,
            'mileage' => 12,
            'safety_rating' => 5,
            'image' => 'https://imagedelivery.net/LqiWLm-3MGbYHtFuUbcBtA/c2296b42-1271-4084-297c-3f9cb9076f00/public',
            'safety_features' => array(
                'Honda Sensing Suite',
                'Collision Mitigation Braking',
                'Road Departure Mitigation',
                'Adaptive Cruise Control'
            )
        ),
        array(
            'id' => 10,
            'make' => 'Ford',
            'model' => 'F-150',
            'year' => 2024,
            'condition' => 'New',
            'type' => 'Truck',
            'price' => 45000,
            'mileage' => 20,
            'safety_rating' => 5,
            'image' => 'https://imagedelivery.net/LqiWLm-3MGbYHtFuUbcBtA/48e55e51-f2f6-4999-7ef4-6f9a9446f200/public',
            'safety_features' => array(
                'Pre-Collision Assist',
                'Lane-Keeping System',
                '360-Degree Camera',
                'Blind Spot Information System'
            )
        )
    );

    // Apply filters
    $vehicles = safequote_filter_vehicles($vehicles, $args);

    return $vehicles;
}

/**
 * Filter vehicles by criteria
 *
 * @param array $vehicles Array of vehicle data
 * @param array $args     Filter arguments
 * @return array Filtered array of vehicle data
 */
function safequote_filter_vehicles($vehicles, $args = array()) {
    $defaults = array(
        'type' => '',
        'condition' => '',
        'safety_rating' => 0,
        'min_price' => 0,
        'max_price' => PHP_INT_MAX,
        'make' => '',
        'model' => '',
        'year' => '',
        'search' => '',
    );

    $args = wp_parse_args($args, $defaults);

    // Filter by type
    if (!empty($args['type'])) {
        $type_lower = strtolower($args['type']);
        $vehicles = array_filter($vehicles, function($vehicle) use ($type_lower) {
            return strtolower($vehicle['type']) === $type_lower;
        });
    }

    // Filter by condition
    if (!empty($args['condition'])) {
        $condition_lower = strtolower($args['condition']);
        $vehicles = array_filter($vehicles, function($vehicle) use ($condition_lower) {
            return strtolower($vehicle['condition']) === $condition_lower;
        });
    }

    // Filter by minimum safety rating
    if (!empty($args['safety_rating'])) {
        $safety_rating = (int) $args['safety_rating'];
        $vehicles = array_filter($vehicles, function($vehicle) use ($safety_rating) {
            return isset($vehicle['safety_rating']) && $vehicle['safety_rating'] >= $safety_rating;
        });
    }

    // Filter by minimum price
    if (!empty($args['min_price'])) {
        $min_price = (int) $args['min_price'];
        $vehicles = array_filter($vehicles, function($vehicle) use ($min_price) {
            return $vehicle['price'] >= $min_price;
        });
    }

    // Filter by maximum price
    if ($args['max_price'] !== PHP_INT_MAX && !empty($args['max_price'])) {
        $max_price = (int) $args['max_price'];
        $vehicles = array_filter($vehicles, function($vehicle) use ($max_price) {
            return $vehicle['price'] <= $max_price;
        });
    }

    // Filter by make
    if (!empty($args['make'])) {
        $make_lower = strtolower($args['make']);
        $vehicles = array_filter($vehicles, function($vehicle) use ($make_lower) {
            return strtolower($vehicle['make']) === $make_lower;
        });
    }

    // Filter by model
    if (!empty($args['model'])) {
        $model_lower = strtolower($args['model']);
        $vehicles = array_filter($vehicles, function($vehicle) use ($model_lower) {
            return strtolower($vehicle['model']) === $model_lower;
        });
    }

    // Filter by year
    if (!empty($args['year'])) {
        $year = (int) $args['year'];
        $vehicles = array_filter($vehicles, function($vehicle) use ($year) {
            return $vehicle['year'] === $year;
        });
    }

    // Search by keyword (searches make, model, type)
    if (!empty($args['search'])) {
        $search_lower = strtolower($args['search']);
        $vehicles = array_filter($vehicles, function($vehicle) use ($search_lower) {
            return strpos(strtolower($vehicle['make']), $search_lower) !== false ||
                   strpos(strtolower($vehicle['model']), $search_lower) !== false ||
                   strpos(strtolower($vehicle['type']), $search_lower) !== false;
        });
    }

    // Reset array keys
    return array_values($vehicles);
}

/**
 * Get a single vehicle by ID
 *
 * @param int $id Vehicle ID
 * @return array|null Vehicle data or null if not found
 */
function safequote_get_vehicle_by_id($id) {
    $id = (int) $id;
    $vehicles = safequote_get_vehicles();

    foreach ($vehicles as $vehicle) {
        if ($vehicle['id'] === $id) {
            return $vehicle;
        }
    }

    return null;
}

/**
 * Search vehicles by keyword
 *
 * @param string $search Search keyword
 * @param array  $args   Optional additional filter arguments
 * @return array Array of matching vehicles
 */
function safequote_search_vehicles($search, $args = array()) {
    $args['search'] = sanitize_text_field($search);
    return safequote_get_vehicles($args);
}

/**
 * Get top safety picks (vehicles with 5-star ratings)
 *
 * @param int $limit Number of vehicles to return
 * @return array Array of vehicle data
 */
function safequote_get_top_safety_picks($limit = 6) {
    $limit = (int) $limit;
    $vehicles = safequote_get_vehicles(array('safety_rating' => 5));
    return array_slice($vehicles, 0, $limit);
}

/**
 * Get unique vehicle types
 *
 * @return array Array of unique vehicle types
 */
function safequote_get_vehicle_types() {
    $vehicles = safequote_get_vehicles();
    $types = array();

    foreach ($vehicles as $vehicle) {
        if (!in_array($vehicle['type'], $types, true)) {
            $types[] = $vehicle['type'];
        }
    }

    return array_unique($types);
}

/**
 * Get unique vehicle years
 *
 * @return array Array of unique vehicle years sorted in descending order
 */
function safequote_get_vehicle_years() {
    $vehicles = safequote_get_vehicles();
    $years = array();

    foreach ($vehicles as $vehicle) {
        if (isset($vehicle['year']) && !in_array($vehicle['year'], $years, true)) {
            $years[] = $vehicle['year'];
        }
    }

    // Sort descending so newest years appear first
    rsort($years);
    return $years;
}

/**
 * Get unique vehicle makes
 *
 * @return array Array of unique vehicle makes
 */
function safequote_get_vehicle_makes() {
    $vehicles = safequote_get_vehicles();
    $makes = array();

    foreach ($vehicles as $vehicle) {
        if (!in_array($vehicle['make'], $makes, true)) {
            $makes[] = $vehicle['make'];
        }
    }

    return array_unique($makes);
}

/**
 * Future CPT Migration Checklist:
 *
 * When creating "Vehicles" Custom Post Type:
 *
 * Post Type: 'vehicle'
 * Fields (ACF or custom):
 *   - make (text)
 *   - model (text)
 *   - year (number)
 *   - type (taxonomy: vehicle_type)
 *   - condition (taxonomy: vehicle_condition)
 *   - price (number)
 *   - safety_rating (number 1-5)
 *   - safety_features (repeater or serialized array)
 *   - image (image)
 *   - mileage (number)
 *
 * Then replace safequote_get_vehicles() with:
 *
 * function safequote_get_vehicles($args = array()) {
 *     $defaults = array(
 *         'post_type' => 'vehicle',
 *         'posts_per_page' => -1,
 *     );
 *
 *     // Build tax_query for type and condition
 *     // Build meta_query for price, safety_rating, year
 *
 *     $query = new WP_Query(wp_parse_args($args, $defaults));
 *     $vehicles = array();
 *
 *     foreach ($query->posts as $post) {
 *         $vehicles[] = array(
 *             'id' => $post->ID,
 *             'make' => get_post_meta($post->ID, 'make', true),
 *             'model' => get_post_meta($post->ID, 'model', true),
 *             'year' => (int) get_post_meta($post->ID, 'year', true),
 *             'condition' => get_post_meta($post->ID, 'condition', true),
 *             'type' => get_post_meta($post->ID, 'type', true),
 *             'price' => (int) get_post_meta($post->ID, 'price', true),
 *             'mileage' => (int) get_post_meta($post->ID, 'mileage', true),
 *             'safety_rating' => (int) get_post_meta($post->ID, 'safety_rating', true),
 *             'image' => get_the_post_thumbnail_url($post->ID),
 *             'safety_features' => (array) get_post_meta($post->ID, 'safety_features', true),
 *         );
 *     }
 *
 *     return $vehicles;
 * }
 */
