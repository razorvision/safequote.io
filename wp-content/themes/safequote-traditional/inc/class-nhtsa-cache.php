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
            if (isset($data['OverallRating']) && $data['OverallRating'] !== null) {
                // Repopulate transient for faster next request
                set_transient($cache_key, $data, self::CACHE_TTL_TRANSIENT);
                error_log("[NHTSA] ✓ Database HIT: {$year} {$make} {$model}");
                return $data;
            }

            // Database has vehicle but rating is null (from CSV import)
            // Try API to fill the gap
            error_log("[NHTSA] → Database has record but no rating, trying API: {$year} {$make} {$model}");

            $api_data = self::fetch_from_api($year, $make, $model);

            if ($api_data && isset($api_data['OverallRating']) && $api_data['OverallRating'] !== null) {
                // Store API result permanently (no expiration) with 'api' source flag
                SafeQuote_NHTSA_Database::update_vehicle_cache(
                    $year,
                    $make,
                    $model,
                    $api_data,
                    null, // Permanent storage - never expires or deletes
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
                null, // Permanent storage - never expires or deletes
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
     * Two-step process:
     * 1. Get ALL VehicleIds from modelyear/make/model endpoint (may return multiple variants)
     * 2. Get detailed ratings for EACH VehicleId and store ALL in database
     *
     * @param int    $year  Vehicle model year.
     * @param string $make  Vehicle make.
     * @param string $model Vehicle model.
     * @return array|false First rating data for backward compatibility, or false on failure.
     */
    public static function fetch_from_api($year, $make, $model) {
        try {
            // STEP 1: Get ALL VehicleIds from model/make/year endpoint
            $model_endpoint = self::API_BASE_URL . "/modelyear/{$year}/make/" . urlencode($make) . "/model/" . urlencode($model);

            $response = wp_remote_get($model_endpoint, array(
                'timeout' => self::API_TIMEOUT,
                'sslverify' => true,
                'headers' => array(
                    'Accept' => 'application/json',
                ),
            ));

            // Check for network errors
            if (is_wp_error($response)) {
                error_log("[NHTSA API Error] Step 1 model lookup failed: {$response->get_error_message()}");
                return false;
            }

            // Check HTTP status
            $status = wp_remote_retrieve_response_code($response);
            if ($status !== 200) {
                error_log("[NHTSA HTTP Error] Step 1 status {$status} for {$year} {$make} {$model}");
                return false;
            }

            // Parse response from Step 1
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);

            if (!$data || !isset($data['Results']) || empty($data['Results'])) {
                error_log("[NHTSA] No vehicle found for {$year} {$make} {$model}");
                return false;
            }

            $results_count = count($data['Results']);
            error_log("[NHTSA] Found {$results_count} variant(s) for {$year} {$make} {$model}");

            // STEP 2: Fetch detailed ratings for EACH VehicleId
            $all_parsed_results = array();

            foreach ($data['Results'] as $variant) {
                $vehicle_id = $variant['VehicleId'] ?? null;
                $vehicle_desc = $variant['VehicleDescription'] ?? '';

                if (!$vehicle_id) {
                    continue;
                }

                // Fetch detailed ratings for this variant
                $ratings_endpoint = self::API_BASE_URL . "/VehicleId/{$vehicle_id}?format=json";

                $ratings_response = wp_remote_get($ratings_endpoint, array(
                    'timeout' => self::API_TIMEOUT,
                    'sslverify' => true,
                    'headers' => array(
                        'Accept' => 'application/json',
                    ),
                ));

                // Check for network errors
                if (is_wp_error($ratings_response)) {
                    error_log("[NHTSA API Error] Step 2 failed for VehicleId {$vehicle_id}: {$ratings_response->get_error_message()}");
                    continue;
                }

                // Check HTTP status
                $ratings_status = wp_remote_retrieve_response_code($ratings_response);
                if ($ratings_status !== 200) {
                    error_log("[NHTSA HTTP Error] Step 2 status {$ratings_status} for VehicleId {$vehicle_id}");
                    continue;
                }

                // Parse ratings response
                $ratings_body = wp_remote_retrieve_body($ratings_response);
                $ratings_data = json_decode($ratings_body, true);

                if (!$ratings_data || !isset($ratings_data['Results']) || empty($ratings_data['Results'])) {
                    error_log("[NHTSA] No ratings data for VehicleId {$vehicle_id}");
                    continue;
                }

                // Parse and store this variant
                $result = $ratings_data['Results'][0];
                $parsed = self::parse_nhtsa_response($result);
                $overall = $parsed['OverallRating'] ?? null;

                error_log("[NHTSA Success] VehicleId {$vehicle_id} - {$vehicle_desc}: OverallRating={$overall}");

                // Store this variant in the database using VehicleDescription as model name
                $variant_model = $parsed['Model'] ?? $model;
                if (!empty($parsed['VehicleDescription'])) {
                    // Extract model name from description (e.g., "2015 Toyota Rav4 SUV AWD Later Release")
                    // Use the full description to differentiate variants
                    $variant_model = $parsed['VehicleDescription'];
                }

                // Store in database with variant-specific model name
                SafeQuote_NHTSA_Database::update_vehicle_cache(
                    $year,
                    $make,
                    $variant_model,
                    $parsed,
                    null, // Permanent storage
                    'api'
                );

                $all_parsed_results[] = $parsed;

                // Small delay between API calls to be respectful
                usleep(100000); // 100ms
            }

            // Return first result for backward compatibility
            if (!empty($all_parsed_results)) {
                error_log("[NHTSA] Stored " . count($all_parsed_results) . " variant(s) for {$year} {$make} {$model}");
                return $all_parsed_results[0];
            }

            return false;

        } catch (Exception $e) {
            error_log("[NHTSA Exception] {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Parse NHTSA API response into consistent format
     *
     * Returns complete NHTSA data to support both summary and detailed displays.
     * Includes all crash test details, safety features, complaints, recalls, and investigations.
     *
     * @param array $result NHTSA API result.
     * @return array Complete NHTSA rating data with all fields.
     */
    public static function parse_nhtsa_response($result) {
        // Return full NHTSA response with all fields intact
        // This supports detailed display in safety-ratings.js
        $description = isset($result['VehicleDescription']) ? sanitize_text_field($result['VehicleDescription']) : null;
        $year = isset($result['ModelYear']) ? intval($result['ModelYear']) : null;

        error_log("[NHTSA Parse] {$year} {$description}: VehicleId={$result['VehicleId']}");

        // Build complete response with all NHTSA fields
        $parsed = array(
            // Vehicle identification
            'VehicleId' => isset($result['VehicleId']) ? intval($result['VehicleId']) : null,
            'VehicleDescription' => $description,
            'ModelYear' => $year,
            'Make' => isset($result['Make']) ? sanitize_text_field($result['Make']) : null,
            'Model' => isset($result['Model']) ? sanitize_text_field($result['Model']) : null,
            'VehiclePicture' => isset($result['VehiclePicture']) ? sanitize_url($result['VehiclePicture']) : null,

            // Overall ratings
            'OverallRating' => isset($result['OverallRating']) ? $result['OverallRating'] : null,
            'OverallFrontCrashRating' => isset($result['OverallFrontCrashRating']) ? $result['OverallFrontCrashRating'] : null,
            'OverallSideCrashRating' => isset($result['OverallSideCrashRating']) ? $result['OverallSideCrashRating'] : null,
            'RolloverRating' => isset($result['RolloverRating']) ? $result['RolloverRating'] : null,

            // Detailed crash ratings - driver/passenger side
            'FrontCrashDriversideRating' => isset($result['FrontCrashDriversideRating']) ? $result['FrontCrashDriversideRating'] : null,
            'FrontCrashPassengersideRating' => isset($result['FrontCrashPassengersideRating']) ? $result['FrontCrashPassengersideRating'] : null,
            'SideCrashDriversideRating' => isset($result['SideCrashDriversideRating']) ? $result['SideCrashDriversideRating'] : null,
            'SideCrashPassengersideRating' => isset($result['SideCrashPassengersideRating']) ? $result['SideCrashPassengersideRating'] : null,
            'SidePoleCrashRating' => isset($result['SidePoleCrashRating']) ? $result['SidePoleCrashRating'] : null,

            // Rollover details
            'RolloverRating2' => isset($result['RolloverRating2']) ? $result['RolloverRating2'] : null,
            'RolloverPossibility' => isset($result['RolloverPossibility']) ? $result['RolloverPossibility'] : null,
            'RolloverPossibility2' => isset($result['RolloverPossibility2']) ? $result['RolloverPossibility2'] : null,
            'dynamicTipResult' => isset($result['dynamicTipResult']) ? $result['dynamicTipResult'] : null,

            // Side barrier ratings
            'combinedSideBarrierAndPoleRating-Front' => isset($result['combinedSideBarrierAndPoleRating-Front']) ? $result['combinedSideBarrierAndPoleRating-Front'] : null,
            'combinedSideBarrierAndPoleRating-Rear' => isset($result['combinedSideBarrierAndPoleRating-Rear']) ? $result['combinedSideBarrierAndPoleRating-Rear'] : null,

            // Safety features
            'NHTSAElectronicStabilityControl' => isset($result['NHTSAElectronicStabilityControl']) ? $result['NHTSAElectronicStabilityControl'] : null,
            'NHTSAForwardCollisionWarning' => isset($result['NHTSAForwardCollisionWarning']) ? $result['NHTSAForwardCollisionWarning'] : null,
            'NHTSALaneDepartureWarning' => isset($result['NHTSALaneDepartureWarning']) ? $result['NHTSALaneDepartureWarning'] : null,

            // Complaints, recalls, investigations
            'ComplaintsCount' => isset($result['ComplaintsCount']) ? intval($result['ComplaintsCount']) : 0,
            'RecallsCount' => isset($result['RecallsCount']) ? intval($result['RecallsCount']) : 0,
            'InvestigationCount' => isset($result['InvestigationCount']) ? intval($result['InvestigationCount']) : 0,

            // Metadata
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
