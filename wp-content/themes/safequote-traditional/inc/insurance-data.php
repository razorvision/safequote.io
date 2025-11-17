<?php
/**
 * Insurance Data
 *
 * Static insurance provider data and utility functions.
 * TODO: Replace with Custom Post Type "Insurance Providers" or API integration
 *
 * @package SafeQuote_Traditional
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get all insurance providers
 *
 * @return array Array of insurance provider data
 */
function safequote_get_insurance_providers() {
    // TODO: Replace with CPT or external API
    return array(
        array(
            'id' => 1,
            'provider' => 'SafeGuard Insurance',
            'url' => 'https://www.progressive.com/',
            'rating' => 4.8,
            'discount' => 15,
            'base_rate' => 180,
        ),
        array(
            'id' => 2,
            'provider' => 'DriveSecure',
            'url' => 'https://www.geico.com/',
            'rating' => 4.6,
            'discount' => 10,
            'base_rate' => 170,
        ),
        array(
            'id' => 3,
            'provider' => 'YouthShield Auto',
            'url' => 'https://www.statefarm.com/',
            'rating' => 4.5,
            'discount' => 12,
            'base_rate' => 175,
        ),
    );
}

/**
 * Get insurance quote for a vehicle
 *
 * Calculates insurance quotes based on vehicle safety rating and condition.
 * Mirrors logic from src/lib/insuranceData.js - getInsuranceQuotes()
 *
 * @param int   $vehicle_id Vehicle ID
 * @param array $driver_info Optional driver information (unused in current implementation)
 * @return array Array of quotes from different providers
 */
function safequote_get_insurance_quote($vehicle_id, $driver_info = array()) {
    // Load vehicle data if not provided
    if (!function_exists('safequote_get_vehicle_by_id')) {
        return array();
    }

    $vehicle = safequote_get_vehicle_by_id($vehicle_id);
    if (!$vehicle) {
        return array();
    }

    // Base price calculation - mirrors React logic:
    // const basePrice = vehicle.condition === 'New' ? 180 : 150;
    $base_price = 150;
    if (isset($vehicle['condition']) && 'New' === $vehicle['condition']) {
        $base_price = 180;
    }

    // Safety discount calculation - mirrors React logic:
    // const safetyDiscount = (vehicle.safetyRating || 5) * 2;
    $safety_rating = isset($vehicle['safety_rating']) ? $vehicle['safety_rating'] : 5;
    $safety_discount = $safety_rating * 2;

    // Provider-specific coverage (matching React getInsuranceQuotes)
    $provider_data = array(
        array(
            'provider' => 'SafeGuard Insurance',
            'url' => 'https://www.progressive.com/',
            'rating' => 4.8,
            'discount' => 15,
            'recommended' => true,
            'monthly_offset' => 0,
            'coverage' => array(
                'Liability Coverage',
                'Collision Coverage',
                'Comprehensive Coverage',
                'Teen Driver Discount',
                '24/7 Roadside Assistance'
            )
        ),
        array(
            'provider' => 'DriveSecure',
            'url' => 'https://www.geico.com/',
            'rating' => 4.6,
            'discount' => 10,
            'recommended' => false,
            'monthly_offset' => 20,
            'coverage' => array(
                'Full Coverage',
                'Accident Forgiveness',
                'New Driver Support',
                'Mobile App Tracking'
            )
        ),
        array(
            'provider' => 'YouthShield Auto',
            'url' => 'https://www.statefarm.com/',
            'rating' => 4.5,
            'discount' => 12,
            'recommended' => false,
            'monthly_offset' => 35,
            'coverage' => array(
                'Liability & Collision',
                'Teen Safety Program',
                'Defensive Driving Discount',
                'Parent Portal Access'
            )
        ),
    );

    $quotes = array();

    foreach ($provider_data as $provider) {
        // Mirror React calculation:
        // monthlyPrice: Math.round(basePrice - safetyDiscount + offset)
        $monthly_price = round($base_price - $safety_discount + $provider['monthly_offset']);

        $quotes[] = array(
            'provider' => $provider['provider'],
            'url' => $provider['url'],
            'monthly_price' => $monthly_price,
            'annual_price' => $monthly_price * 12,
            'rating' => $provider['rating'],
            'discount' => $provider['discount'],
            'recommended' => $provider['recommended'],
            'coverage' => $provider['coverage']
        );
    }

    // Sort by monthly price (lowest first)
    usort($quotes, function($a, $b) {
        return $a['monthly_price'] <=> $b['monthly_price'];
    });

    return $quotes;
}

/**
 * Compare insurance quotes for multiple vehicles
 *
 * @param array $vehicle_ids Array of vehicle IDs
 * @return array Comparison data with vehicles and their quotes
 */
function safequote_compare_insurance($vehicle_ids = array()) {
    if (empty($vehicle_ids)) {
        return array();
    }

    $vehicle_ids = array_map('intval', $vehicle_ids);
    $comparison = array();

    foreach ($vehicle_ids as $vehicle_id) {
        $quotes = safequote_get_insurance_quote($vehicle_id);
        $vehicle = safequote_get_vehicle_by_id($vehicle_id);

        if (!$vehicle) {
            continue;
        }

        $lowest_quote = 0;
        if (!empty($quotes)) {
            $lowest_quote = $quotes[0]['monthly_price'];
        }

        $comparison[] = array(
            'vehicle' => $vehicle,
            'quotes' => $quotes,
            'lowest_quote' => $lowest_quote,
        );
    }

    return $comparison;
}

/**
 * Get insurance quotes for all vehicles
 *
 * Useful for comparison pages or bulk operations.
 *
 * @param int $limit Maximum number of vehicles to get quotes for (0 = all)
 * @return array Array of all vehicles with their quotes
 */
function safequote_get_all_vehicle_quotes($limit = 0) {
    if (!function_exists('safequote_get_vehicles')) {
        return array();
    }

    $vehicles = safequote_get_vehicles();

    if ($limit > 0) {
        $vehicles = array_slice($vehicles, 0, $limit);
    }

    $result = array();

    foreach ($vehicles as $vehicle) {
        $quotes = safequote_get_insurance_quote($vehicle['id']);
        $result[] = array(
            'vehicle' => $vehicle,
            'quotes' => $quotes,
        );
    }

    return $result;
}

/**
 * Get the lowest insurance quote across all providers for a vehicle
 *
 * @param int $vehicle_id Vehicle ID
 * @return array|null Lowest quote data or null if no quotes found
 */
function safequote_get_lowest_insurance_quote($vehicle_id) {
    $quotes = safequote_get_insurance_quote($vehicle_id);

    if (empty($quotes)) {
        return null;
    }

    // Already sorted by safequote_get_insurance_quote, so first is lowest
    return $quotes[0];
}

/**
 * Get insurance quote statistics for a vehicle
 *
 * Returns min, max, and average insurance quotes
 *
 * @param int $vehicle_id Vehicle ID
 * @return array Statistics array with 'min', 'max', 'average' keys
 */
function safequote_get_insurance_statistics($vehicle_id) {
    $quotes = safequote_get_insurance_quote($vehicle_id);

    if (empty($quotes)) {
        return array(
            'min' => 0,
            'max' => 0,
            'average' => 0,
        );
    }

    $prices = array_map(function($quote) {
        return $quote['monthly_price'];
    }, $quotes);

    return array(
        'min' => min($prices),
        'max' => max($prices),
        'average' => round(array_sum($prices) / count($prices), 2),
    );
}

/**
 * Future CPT Migration Checklist:
 *
 * When creating "Insurance Providers" Custom Post Type or API integration:
 *
 * Post Type: 'insurance_provider'
 * Fields (ACF or custom):
 *   - provider_name (text)
 *   - provider_url (url)
 *   - provider_rating (number 0-5)
 *   - provider_discount (number)
 *   - base_rate (number)
 *   - coverage_areas (repeater)
 *
 * Or integrate with external APIs:
 *   - Progressive API
 *   - Geico API
 *   - State Farm API
 *
 * Then replace safequote_get_insurance_providers() with:
 *
 * function safequote_get_insurance_providers() {
 *     $providers = array();
 *
 *     // Option 1: CPT Query
 *     $args = array(
 *         'post_type' => 'insurance_provider',
 *         'posts_per_page' => -1,
 *     );
 *     $query = new WP_Query($args);
 *
 *     foreach ($query->posts as $post) {
 *         $providers[] = array(
 *             'id' => $post->ID,
 *             'provider' => get_post_meta($post->ID, 'provider_name', true),
 *             'url' => get_post_meta($post->ID, 'provider_url', true),
 *             'rating' => (float) get_post_meta($post->ID, 'provider_rating', true),
 *             'discount' => (int) get_post_meta($post->ID, 'provider_discount', true),
 *             'base_rate' => (int) get_post_meta($post->ID, 'base_rate', true),
 *         );
 *     }
 *
 *     return $providers;
 *
 *     // Option 2: External API Integration (async/cached)
 *     // $providers = wp_cache_get('insurance_providers');
 *     // if (false === $providers) {
 *     //     $providers = safequote_fetch_insurance_from_api();
 *     //     wp_cache_set('insurance_providers', $providers, '', 3600);
 *     // }
 *     // return $providers;
 * }
 *
 * And replace safequote_get_insurance_quote() with API calls:
 *
 * function safequote_get_insurance_quote($vehicle_id, $driver_info = array()) {
 *     $vehicle = safequote_get_vehicle_by_id($vehicle_id);
 *     if (!$vehicle) {
 *         return array();
 *     }
 *
 *     $driver = wp_parse_args($driver_info, array(
 *         'age' => 16,
 *         'experience' => 0,
 *     ));
 *
 *     $quotes = array();
 *     $providers = safequote_get_insurance_providers();
 *
 *     foreach ($providers as $provider) {
 *         // Call external API
 *         $quote = safequote_api_get_quote(
 *             $provider['url'],
 *             $vehicle,
 *             $driver
 *         );
 *         if ($quote) {
 *             $quotes[] = $quote;
 *         }
 *     }
 *
 *     usort($quotes, function($a, $b) {
 *         return $a['monthly_price'] <=> $b['monthly_price'];
 *     });
 *
 *     return $quotes;
 * }
 */
