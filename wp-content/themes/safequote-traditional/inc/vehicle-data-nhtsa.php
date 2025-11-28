<?php
/**
 * Vehicle Data with NHTSA Database Integration
 *
 * Queries NHTSA vehicle cache directly with transient caching.
 * Uses 24-hour transient cache for optimal performance.
 *
 * @package SafeQuote_Traditional
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get top safety picks directly from NHTSA database with transient caching
 *
 * Queries vehicles with nhtsa_overall_rating >= 4.5 (top picks).
 * Results are cached in WordPress transients for 24 hours for fast access.
 *
 * @param int   $limit      Number of vehicles to return (default 6)
 * @param float $min_rating Minimum rating threshold (default 4.5)
 * @return array Array of top-rated vehicles from NHTSA database
 */
function safequote_get_top_safety_picks_from_db($limit = 6, $min_rating = 4.5) {
    global $wpdb;

    $limit = (int) $limit;
    $min_rating = (float) $min_rating;

    // Check transient cache first
    $cache_key = "nhtsa_top_picks_{$limit}_{$min_rating}";
    $cached_vehicles = get_transient($cache_key);

    if ($cached_vehicles !== false) {
        error_log("[NHTSA] Top picks retrieved from transient cache");
        return $cached_vehicles;
    }

    $table = $wpdb->prefix . 'nhtsa_vehicle_cache';

    // Query top-rated vehicles from database
    $results = $wpdb->get_results($wpdb->prepare(
        "SELECT
            year, make, model, vehicle_id,
            nhtsa_overall_rating, front_crash, side_crash, rollover_crash,
            vehicle_picture, nhtsa_data, rating_source,
            created_at, updated_at
         FROM $table
         WHERE nhtsa_overall_rating >= %f
         AND nhtsa_overall_rating IS NOT NULL
         ORDER BY nhtsa_overall_rating DESC, year DESC
         LIMIT %d",
        $min_rating,
        $limit
    ));

    // If no results at higher threshold, try lower threshold
    if (empty($results) && $min_rating >= 4.0) {
        error_log("[NHTSA] No vehicles found at rating {$min_rating}, trying 4.0");
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT
                year, make, model, vehicle_id,
                nhtsa_overall_rating, front_crash, side_crash, rollover_crash,
                vehicle_picture, nhtsa_data, rating_source,
                created_at, updated_at
             FROM $table
             WHERE nhtsa_overall_rating >= 4.0
             AND nhtsa_overall_rating IS NOT NULL
             ORDER BY nhtsa_overall_rating DESC, year DESC
             LIMIT %d",
            $limit
        ));
    }

    // Transform database results to vehicle array format
    $vehicles = array();
    $index = 0;

    if ($results) {
        foreach ($results as $row) {
            $vehicles[] = array(
                'id'              => $index++,
                'year'            => (int) $row->year,
                'make'            => $row->make,
                'model'           => $row->model,
                'vehicle_id'      => $row->vehicle_id,
                'type'            => 'Vehicle',
                'condition'       => 'Used',
                'price'           => 0,
                'mileage'         => 0,
                'safety_rating'   => (float) $row->nhtsa_overall_rating,
                'image'           => esc_url($row->vehicle_picture),
                'vehicle_picture' => esc_url($row->vehicle_picture),
                'front_crash'     => (float) $row->front_crash,
                'side_crash'      => (float) $row->side_crash,
                'rollover_crash'  => (float) $row->rollover_crash,
                'rating_source'   => $row->rating_source,
                'safety_features' => array('NHTSA Rated Vehicle'),
                'nhtsa_data'      => json_decode($row->nhtsa_data, true),
            );
        }
    }

    // Cache results for 24 hours
    set_transient($cache_key, $vehicles, 24 * HOUR_IN_SECONDS);

    $count = count($vehicles);
    error_log("[NHTSA] Top picks queried from database: {$count} vehicles cached for 24 hours");

    return $vehicles;
}

/**
 * Get all vehicles from NHTSA database with optional filtering and transient caching
 *
 * Query NHTSA vehicle cache with 24-hour transient caching for performance.
 *
 * @param array $args {
 *     Optional query arguments
 *     @type int    $limit     Number of results (default -1 for all)
 *     @type float  $min_rating Minimum rating (default 0)
 *     @type int    $year      Filter by model year
 *     @type string $make      Filter by make
 *     @type string $model     Filter by model
 * }
 * @return array Array of vehicles from NHTSA database
 */
function safequote_get_vehicles_from_nhtsa($args = array()) {
    global $wpdb;

    $defaults = array(
        'limit'      => -1,
        'min_rating' => 0,
        'year'       => 0,
        'make'       => '',
        'model'      => '',
    );

    $args = wp_parse_args($args, $defaults);

    // Generate cache key based on query parameters
    $cache_key = 'nhtsa_vehicles_' . md5(wp_json_encode($args));
    $cached_vehicles = get_transient($cache_key);

    if ($cached_vehicles !== false) {
        error_log("[NHTSA] Vehicles retrieved from transient cache");
        return $cached_vehicles;
    }

    $table = $wpdb->prefix . 'nhtsa_vehicle_cache';

    // Build WHERE conditions (1=1 allows all vehicles, NULL ratings = "No Rating")
    $where_conditions = array('1=1');

    if (!empty($args['min_rating'])) {
        $where_conditions[] = $wpdb->prepare('nhtsa_overall_rating >= %f', $args['min_rating']);
    }

    if (!empty($args['year'])) {
        $where_conditions[] = $wpdb->prepare('year = %d', $args['year']);
    }

    if (!empty($args['make'])) {
        $where_conditions[] = $wpdb->prepare('make = %s', sanitize_text_field($args['make']));
    }

    if (!empty($args['model'])) {
        $where_conditions[] = $wpdb->prepare('model = %s', sanitize_text_field($args['model']));
    }

    $where_sql = implode(' AND ', $where_conditions);

    // Query
    $limit_sql = $args['limit'] === -1 ? '' : $wpdb->prepare('LIMIT %d', $args['limit']);

    $results = $wpdb->get_results(
        "SELECT * FROM $table
         WHERE $where_sql
         ORDER BY nhtsa_overall_rating DESC, year DESC
         $limit_sql"
    );

    // Transform results
    $vehicles = array();
    $index = 0;

    if ($results) {
        foreach ($results as $row) {
            $vehicles[] = array(
                'id'              => $index++,
                'year'            => (int) $row->year,
                'make'            => $row->make,
                'model'           => $row->model,
                'vehicle_id'      => $row->vehicle_id,
                'type'            => 'Vehicle',
                'condition'       => 'Used',
                'price'           => 0,
                'mileage'         => 0,
                'safety_rating'   => (float) $row->nhtsa_overall_rating,
                'image'           => esc_url($row->vehicle_picture),
                'vehicle_picture' => esc_url($row->vehicle_picture),
                'front_crash'     => (float) $row->front_crash,
                'side_crash'      => (float) $row->side_crash,
                'rollover_crash'  => (float) $row->rollover_crash,
                'rating_source'   => $row->rating_source,
                'safety_features' => array('NHTSA Rated Vehicle'),
                'nhtsa_data'      => json_decode($row->nhtsa_data, true),
            );
        }
    }

    // Cache results for 24 hours
    set_transient($cache_key, $vehicles, 24 * HOUR_IN_SECONDS);

    $count = count($vehicles);
    error_log("[NHTSA] Vehicles queried from database: {$count} vehicles cached for 24 hours");

    return $vehicles;
}

/**
 * Get NHTSA rating for a specific vehicle via transient cache
 *
 * Uses SafeQuote_NHTSA_Cache for multi-tier caching.
 *
 * @param int    $year  Vehicle year
 * @param string $make  Vehicle make
 * @param string $model Vehicle model
 * @return array|null NHTSA rating data or null
 */
function safequote_get_nhtsa_rating($year, $make, $model) {
    require_once SAFEQUOTE_THEME_DIR . '/inc/class-nhtsa-cache.php';

    $year = (int) $year;
    $make = sanitize_text_field($make);
    $model = sanitize_text_field($model);

    if (!$year || !$make || !$model) {
        return null;
    }

    return SafeQuote_NHTSA_Cache::get_vehicle_rating($year, $make, $model);
}
