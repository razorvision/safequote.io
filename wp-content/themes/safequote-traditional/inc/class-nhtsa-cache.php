<?php
/**
 * NHTSA API Cache & Wrapper
 *
 * Provides safe wrapper around NHTSA API calls with multi-tier caching:
 * L1: WordPress Transients (24 hours)
 * L2: Custom database table (7 days)
 * L3: Fallback to stale cache if available
 *
 * @package SafeQuote_Traditional
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class SafeQuote_NHTSA_Cache {

    const CACHE_TTL_TRANSIENT = 24 * HOUR_IN_SECONDS;
    const CACHE_TTL_DATABASE = 7 * DAY_IN_SECONDS;
    const API_BASE_URL = 'https://api.nhtsa.gov/SafetyRatings';
    const API_TIMEOUT = 15;
    const API_MAX_RETRIES = 3;

    /**
     * Get vehicle safety rating from NHTSA
     *
     * Attempts retrieval in this order:
     * 1. Transient cache (24 hours)
     * 2. Database cache with valid rating
     * 3. API fallback for database records with null rating (fills CSV gaps)
     * 4. Live API call for new vehicles
     * 5. Stale cache fallback
     *
     * @param int    $year  Vehicle model year.
     * @param string $make  Vehicle make/manufacturer.
     * @param string $model Vehicle model name.
     * @return array|false NHTSA safety data or false if unavailable.
     */
    public static function get_vehicle_rating($year, $make, $model) {
        $year = (int) $year;
        $make = sanitize_text_field($make);
        $model = sanitize_text_field($model);

        // L1: Check transient cache
        $cache_key = "nhtsa_rating_{$year}_{$make}_{$model}";
        $cached = get_transient($cache_key);

        if ($cached !== false && is_array($cached)) {
            error_log("[NHTSA] ✓ Transient HIT: {$year} {$make} {$model}");
            return $cached;
        }

        // L2: Check database cache
        $db_cache = SafeQuote_NHTSA_Database::get_vehicle_cache($year, $make, $model);

        if ($db_cache) {
            $data = json_decode($db_cache->nhtsa_data, true);

            // If we have a valid rating from CSV or API, use it
            if (isset($data['overall_rating']) && $data['overall_rating'] !== null) {
                // Repopulate transient for faster next request
                set_transient($cache_key, $data, self::CACHE_TTL_TRANSIENT);
                error_log("[NHTSA] ✓ Database HIT: {$year} {$make} {$model}");
                return $data;
            }

            // Database has vehicle but rating is null (from CSV import)
            // Try API to fill the gap
            error_log("[NHTSA] → Database has record but no rating, trying API: {$year} {$make} {$model}");

            $api_data = self::fetch_from_api($year, $make, $model);

            if ($api_data && isset($api_data['overall_rating']) && $api_data['overall_rating'] !== null) {
                // Store API result permanently with 'api' source flag
                SafeQuote_NHTSA_Database::update_vehicle_cache(
                    $year,
                    $make,
                    $model,
                    $api_data,
                    30 * 24, // 30 days expiry
                    'api' // Mark as API source - won't be overwritten by CSV syncs
                );

                set_transient($cache_key, $api_data, self::CACHE_TTL_TRANSIENT);
                error_log("[NHTSA] ✓ API filled gap: {$year} {$make} {$model}");
                return $api_data;
            }

            // No API data available, return database record with null rating
            set_transient($cache_key, $data, self::CACHE_TTL_TRANSIENT);
            error_log("[NHTSA] ⚠ Database has vehicle but no rating available: {$year} {$make} {$model}");
            return $data;
        }

        // L3: Fetch from live NHTSA API (for new vehicles not in CSV)
        error_log("[NHTSA] → Fetching live: {$year} {$make} {$model}");

        $data = self::fetch_from_api($year, $make, $model);

        if ($data) {
            // Cache the successful result with 'api' source
            set_transient($cache_key, $data, self::CACHE_TTL_TRANSIENT);
            SafeQuote_NHTSA_Database::update_vehicle_cache(
                $year,
                $make,
                $model,
                $data,
                30 * 24, // 30 days
                'api' // From API
            );

            error_log("[NHTSA] ✓ API SUCCESS: {$year} {$make} {$model}");
            return $data;
        }

        // L4: Stale cache fallback (no expiration check)
        error_log("[NHTSA] ⚠ Live fetch failed, checking stale cache");

        $stale = self::get_stale_cache($year, $make, $model);

        if ($stale) {
            error_log("[NHTSA] ⚠ Using stale cache: {$year} {$make} {$model}");
            return $stale;
        }

        error_log("[NHTSA] ✗ All cache layers failed: {$year} {$make} {$model}");
        return false;
    }

    /**
     * Fetch vehicle rating from NHTSA API with error handling
     *
     * @param int    $year  Vehicle model year.
     * @param string $make  Vehicle make.
     * @param string $model Vehicle model.
     * @return array|false Rating data or false on failure.
     */
    private static function fetch_from_api($year, $make, $model) {
        $endpoint = self::API_BASE_URL . "/modelyear/{$year}/make/{$make}/model/{$model}";

        try {
            $response = wp_remote_get($endpoint, array(
                'timeout' => self::API_TIMEOUT,
                'sslverify' => true,
                'headers' => array(
                    'Accept' => 'application/json',
                ),
            ));

            // Check for network errors
            if (is_wp_error($response)) {
                error_log("[NHTSA API Error] {$response->get_error_message()}");
                return false;
            }

            // Check HTTP status
            $status = wp_remote_retrieve_response_code($response);

            if ($status !== 200) {
                error_log("[NHTSA HTTP Error] Status: {$status}");
                return false;
            }

            // Parse response
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);

            if (!$data || !isset($data['Results']) || empty($data['Results'])) {
                error_log("[NHTSA] No data available for {$year} {$make} {$model}");
                return false;
            }

            // Extract and validate rating data
            $result = $data['Results'][0]; // Get first result

            return self::parse_nhtsa_response($result);

        } catch (Exception $e) {
            error_log("[NHTSA Exception] {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Parse NHTSA API response into consistent format
     *
     * Handles API field names and converts "Not Rated" strings to null.
     *
     * @param array $result NHTSA API result.
     * @return array Normalized rating data.
     */
    private static function parse_nhtsa_response($result) {
        // Helper to convert rating strings to float, handle "Not Rated"
        $get_rating = function($value) {
            if ($value === 'Not Rated' || $value === '' || $value === null) {
                return null;
            }
            if (is_numeric($value)) {
                return floatval($value);
            }
            return null;
        };

        $parsed = array(
            'vehicle_id' => isset($result['VehicleId']) ? intval($result['VehicleId']) : null,
            // Overall rating from OverallRating field
            'overall_rating' => $get_rating(isset($result['OverallRating']) ? $result['OverallRating'] : null),
            // Front crash from OverallFrontCrashRating field
            'front_crash' => $get_rating(isset($result['OverallFrontCrashRating']) ? $result['OverallFrontCrashRating'] : null),
            // Side crash from OverallSideCrashRating field
            'side_crash' => $get_rating(isset($result['OverallSideCrashRating']) ? $result['OverallSideCrashRating'] : null),
            // Rollover from RolloverRating field
            'rollover_crash' => $get_rating(isset($result['RolloverRating']) ? $result['RolloverRating'] : null),
            'description' => isset($result['VehicleDescription']) ? sanitize_text_field($result['VehicleDescription']) : null,
            'year' => isset($result['ModelYear']) ? intval($result['ModelYear']) : null,
            'source' => 'api',
        );

        return $parsed;
    }

    /**
     * Get stale cache (no expiration check)
     *
     * Used as final fallback when live API unavailable.
     *
     * @param int    $year  Vehicle year.
     * @param string $make  Vehicle make.
     * @param string $model Vehicle model.
     * @return array|false Cached data (potentially expired) or false.
     */
    private static function get_stale_cache($year, $make, $model) {
        global $wpdb;

        $table = $wpdb->prefix . 'nhtsa_vehicle_cache';

        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT nhtsa_data FROM $table WHERE year = %d AND make = %s AND model = %s",
            $year,
            $make,
            $model
        ));

        if ($row) {
            return json_decode($row->nhtsa_data, true);
        }

        return false;
    }

    /**
     * Get available model years from NHTSA
     *
     * @return array|false Array of available years or false on error.
     */
    public static function get_available_years() {
        $cache_key = 'nhtsa_available_years';
        $cached = get_transient($cache_key);

        if ($cached !== false) {
            return $cached;
        }

        $endpoint = self::API_BASE_URL . '/modelyear';

        try {
            $response = wp_remote_get($endpoint, array(
                'timeout' => self::API_TIMEOUT,
                'sslverify' => true,
            ));

            if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
                return false;
            }

            $data = json_decode(wp_remote_retrieve_body($response), true);

            if (!isset($data['Results'])) {
                return false;
            }

            $years = array_column($data['Results'], 'ModelYear');

            // Cache for 30 days (unlikely to change frequently)
            set_transient($cache_key, $years, 30 * DAY_IN_SECONDS);

            return $years;

        } catch (Exception $e) {
            error_log("[NHTSA] Error fetching years: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Get makes for a specific year
     *
     * @param int $year Vehicle model year.
     * @return array|false Array of makes or false on error.
     */
    public static function get_makes_for_year($year) {
        $year = (int) $year;
        $cache_key = "nhtsa_makes_{$year}";
        $cached = get_transient($cache_key);

        if ($cached !== false) {
            return $cached;
        }

        $endpoint = self::API_BASE_URL . "/modelyear/{$year}";

        try {
            $response = wp_remote_get($endpoint, array(
                'timeout' => self::API_TIMEOUT,
                'sslverify' => true,
            ));

            if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
                return false;
            }

            $data = json_decode(wp_remote_retrieve_body($response), true);

            if (!isset($data['Results'])) {
                return false;
            }

            $makes = array_column($data['Results'], 'Make');
            $makes = array_unique($makes);

            // Cache for 7 days
            set_transient($cache_key, $makes, 7 * DAY_IN_SECONDS);

            return array_values($makes);

        } catch (Exception $e) {
            error_log("[NHTSA] Error fetching makes: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Get models for a year and make
     *
     * @param int    $year Vehicle model year.
     * @param string $make Vehicle make.
     * @return array|false Array of models or false on error.
     */
    public static function get_models_for_year_make($year, $make) {
        $year = (int) $year;
        $make = sanitize_text_field($make);
        $cache_key = "nhtsa_models_{$year}_{$make}";
        $cached = get_transient($cache_key);

        if ($cached !== false) {
            return $cached;
        }

        $endpoint = self::API_BASE_URL . "/modelyear/{$year}/make/{$make}";

        try {
            $response = wp_remote_get($endpoint, array(
                'timeout' => self::API_TIMEOUT,
                'sslverify' => true,
            ));

            if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
                return false;
            }

            $data = json_decode(wp_remote_retrieve_body($response), true);

            if (!isset($data['Results'])) {
                return false;
            }

            $models = array_column($data['Results'], 'Model');
            $models = array_unique($models);

            // Cache for 7 days
            set_transient($cache_key, $models, 7 * DAY_IN_SECONDS);

            return array_values($models);

        } catch (Exception $e) {
            error_log("[NHTSA] Error fetching models: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Clear all NHTSA transient caches
     *
     * @return void
     */
    public static function clear_transients() {
        global $wpdb;

        // Clear all transients starting with 'nhtsa'
        $wpdb->query(
            "DELETE FROM {$wpdb->options}
             WHERE option_name LIKE '_transient_nhtsa%'
             OR option_name LIKE '_transient_timeout_nhtsa%'"
        );

        error_log("[NHTSA] All transient caches cleared");
    }

    /**
     * Clear database cache for specific vehicle
     *
     * @param int    $year  Vehicle year.
     * @param string $make  Vehicle make.
     * @param string $model Vehicle model.
     * @return void
     */
    public static function clear_vehicle_cache($year, $make, $model) {
        global $wpdb;

        $table = $wpdb->prefix . 'nhtsa_vehicle_cache';

        $wpdb->delete($table, array(
            'year' => $year,
            'make' => $make,
            'model' => $model,
        ));

        $cache_key = "nhtsa_rating_{$year}_{$make}_{$model}";
        delete_transient($cache_key);

        error_log("[NHTSA] Cache cleared for {$year} {$make} {$model}");
    }

    /**
     * Get cache statistics
     *
     * @return array Cache stats.
     */
    public static function get_cache_stats() {
        global $wpdb;

        $table = $wpdb->prefix . 'nhtsa_vehicle_cache';

        $total = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table");
        $expired = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE expires_at < NOW()");
        $valid = $total - $expired;
        $size = $wpdb->get_var("SELECT SUM(LENGTH(nhtsa_data)) FROM $table");

        return array(
            'total_entries' => $total,
            'valid_entries' => $valid,
            'expired_entries' => $expired,
            'total_size' => $size ? size_format($size, 2) : '0 B',
            'cache_hit_rate' => $total > 0 ? round(($valid / $total) * 100, 1) : 0,
        );
    }
}
