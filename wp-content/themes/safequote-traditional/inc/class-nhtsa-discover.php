<?php
/**
 * NHTSA Vehicle Discovery
 *
 * Discovers which vehicles need NHTSA data syncing based on
 * the vehicles in vehicle-data.php.
 *
 * @package SafeQuote_Traditional
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class SafeQuote_NHTSA_Discover {

    /**
     * Discover and catalog all vehicles to sync
     *
     * Analyzes vehicle-data.php and creates sync log entries
     * for each vehicle that needs NHTSA rating data.
     *
     * @return array Result with 'created', 'skipped', 'errors' counts.
     */
    public static function discover_vehicles() {
        // Get our vehicles from vehicle-data.php
        $our_vehicles = safequote_get_vehicles();

        if (empty($our_vehicles)) {
            error_log("[NHTSA Discovery] No vehicles found in vehicle-data.php");
            return array(
                'created' => 0,
                'skipped' => 0,
                'errors' => 0,
            );
        }

        $result = array(
            'created' => 0,
            'skipped' => 0,
            'errors' => 0,
        );

        // Group vehicles by year/make for efficient NHTSA queries
        $grouped = self::group_vehicles_by_year_make($our_vehicles);

        error_log("[NHTSA Discovery] Found " . count($our_vehicles) . " vehicles to discover");

        // For each year/make combination, enumerate models from NHTSA
        foreach ($grouped as $year => $makes) {
            foreach ($makes as $make => $models) {
                // Fetch available models from NHTSA for this year/make
                $nhtsa_models = SafeQuote_NHTSA_Cache::get_models_for_year_make($year, $make);

                if (!$nhtsa_models) {
                    error_log("[NHTSA Discovery] No NHTSA data for {$year} {$make}");
                    $result['errors']++;
                    continue;
                }

                // For each model in our database
                foreach ($models as $our_model) {
                    // Try to find exact match in NHTSA
                    $nhtsa_match = self::find_model_match($our_model, $nhtsa_models);

                    if ($nhtsa_match) {
                        // Create sync log entry for this vehicle
                        $created = self::create_sync_entry($year, $make, $nhtsa_match, $our_model);

                        if ($created) {
                            $result['created']++;
                            error_log("[NHTSA Discovery] ✓ Created sync entry: {$year} {$make} {$nhtsa_match}");
                        } else {
                            $result['errors']++;
                        }
                    } else {
                        error_log("[NHTSA Discovery] ⚠ No match: {$year} {$make} {$our_model}");
                        $result['skipped']++;
                    }
                }
            }
        }

        update_option('safequote_nhtsa_last_discovery', current_time('mysql'));

        error_log("[NHTSA Discovery] Complete - Created: {$result['created']}, Skipped: {$result['skipped']}, Errors: {$result['errors']}");

        return $result;
    }

    /**
     * Group vehicles by year and make
     *
     * @param array $vehicles Array of vehicle data.
     * @return array Grouped as ['year']['make']['model'] => true.
     */
    private static function group_vehicles_by_year_make($vehicles) {
        $grouped = array();

        foreach ($vehicles as $vehicle) {
            $year = $vehicle['year'];
            $make = sanitize_text_field($vehicle['make']);
            $model = sanitize_text_field($vehicle['model']);

            if (!isset($grouped[$year])) {
                $grouped[$year] = array();
            }

            if (!isset($grouped[$year][$make])) {
                $grouped[$year][$make] = array();
            }

            if (!in_array($model, $grouped[$year][$make], true)) {
                $grouped[$year][$make][] = $model;
            }
        }

        return $grouped;
    }

    /**
     * Find best match for our model in NHTSA models list
     *
     * Uses fuzzy matching to handle differences like:
     * - "CX-5" vs "CX5"
     * - "CR-V" vs "CR-V"
     * - Case differences
     *
     * @param string $our_model  Our vehicle model name.
     * @param array  $nhtsa_models NHTSA available models for year/make.
     * @return string|false Best matching model name or false.
     */
    private static function find_model_match($our_model, $nhtsa_models) {
        $our_normalized = self::normalize_model_name($our_model);

        // First try: exact match (after normalization)
        foreach ($nhtsa_models as $nhtsa_model) {
            $nhtsa_normalized = self::normalize_model_name($nhtsa_model);

            if ($nhtsa_normalized === $our_normalized) {
                return $nhtsa_model; // Return original NHTSA name
            }
        }

        // Second try: fuzzy match (levenshtein distance)
        $best_match = null;
        $best_distance = 999;

        foreach ($nhtsa_models as $nhtsa_model) {
            $nhtsa_normalized = self::normalize_model_name($nhtsa_model);

            // Calculate Levenshtein distance (0 = exact match, higher = more different)
            $distance = levenshtein($our_normalized, $nhtsa_normalized);

            // Accept if distance is small and better than current best
            if ($distance < $best_distance && $distance <= 2) {
                $best_distance = $distance;
                $best_match = $nhtsa_model;
            }
        }

        if ($best_match) {
            error_log("[NHTSA Discovery] Fuzzy match: '{$our_model}' => '{$best_match}' (distance: {$best_distance})");
            return $best_match;
        }

        return false;
    }

    /**
     * Normalize model name for comparison
     *
     * @param string $model Model name.
     * @return string Normalized model name.
     */
    private static function normalize_model_name($model) {
        // Convert to lowercase
        $model = strtolower($model);

        // Remove spaces and hyphens
        $model = str_replace(array('-', ' '), '', $model);

        // Remove numbers and other special chars for fuzzy matching
        // but keep letters and digits together

        return $model;
    }

    /**
     * Create sync log entry for a vehicle
     *
     * @param int    $year   Vehicle year.
     * @param string $make   Vehicle make.
     * @param string $model  Vehicle model (NHTSA name).
     * @param string $our_model Our internal model name.
     * @return bool True on success, false on failure.
     */
    private static function create_sync_entry($year, $make, $model, $our_model) {
        // Check if already exists
        $existing = SafeQuote_NHTSA_Database::get_sync_log($year, $make, $model);

        if ($existing) {
            return false; // Already exists, skip
        }

        // Create new sync entry
        $result = SafeQuote_NHTSA_Database::update_sync_log(
            $year,
            $make,
            $model,
            'pending',
            array(
                'has_data' => false,
                'sync_attempt' => 0,
                'next_attempt' => current_time('mysql'),
            )
        );

        return $result !== false;
    }

    /**
     * Get discovery statistics
     *
     * @return array Discovery stats.
     */
    public static function get_discovery_stats() {
        global $wpdb;

        $table = $wpdb->prefix . 'nhtsa_sync_log';

        $total = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table");
        $pending = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE status = 'pending'");
        $successful = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE status = 'success'");
        $no_data = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE status = 'no_data'");
        $failed = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE status = 'failed'");

        $coverage = 0;
        if (($successful + $no_data) > 0) {
            $coverage = round(($successful / ($successful + $no_data)) * 100, 1);
        }

        return array(
            'total' => $total,
            'pending' => $pending,
            'successful' => $successful,
            'no_data' => $no_data,
            'failed' => $failed,
            'coverage' => $coverage,
            'last_discovery' => get_option('safequote_nhtsa_last_discovery', 'Never'),
        );
    }

    /**
     * Clear all discovery logs (for re-discovery)
     *
     * @return void
     */
    public static function reset_discovery() {
        global $wpdb;

        $table = $wpdb->prefix . 'nhtsa_sync_log';

        $wpdb->query("TRUNCATE TABLE $table");

        delete_option('safequote_nhtsa_last_discovery');

        error_log("[NHTSA Discovery] Reset - All discovery logs cleared");
    }
}
