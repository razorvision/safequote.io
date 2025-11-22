<?php
/**
 * NHTSA Batch Fetch & Sync
 *
 * Handles fetching NHTSA rating data for vehicles in batches,
 * with error handling, rate limiting, and retry logic.
 *
 * @package SafeQuote_Traditional
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class SafeQuote_NHTSA_Fetch {

    const BATCH_SIZE = 10;
    const API_DELAY_MS = 200; // milliseconds between requests
    const RETRY_BACKOFF_BASE = 60; // seconds, multiplied by attempt number

    /**
     * Fetch pending NHTSA data in batches
     *
     * Runs in WP-Cron to prevent timeouts. Fetches a small batch
     * (default: 10) of pending vehicles and updates cache.
     *
     * @return array Result with 'processed', 'success', 'failed' counts.
     */
    public static function fetch_pending_batch() {
        global $wpdb;

        $table = $wpdb->prefix . 'nhtsa_sync_log';

        // Get batch of pending vehicles
        $pending = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table
             WHERE status = 'pending'
             OR (status = 'failed' AND next_attempt IS NOT NULL AND next_attempt <= NOW())
             ORDER BY sync_attempt ASC, year DESC, make ASC, model ASC
             LIMIT %d",
            self::BATCH_SIZE
        ));

        if (empty($pending)) {
            error_log("[NHTSA Fetch] No pending vehicles to fetch");
            return array(
                'processed' => 0,
                'success' => 0,
                'failed' => 0,
            );
        }

        $result = array(
            'processed' => 0,
            'success' => 0,
            'failed' => 0,
        );

        error_log("[NHTSA Fetch] Processing batch of " . count($pending) . " vehicles");

        // Process each vehicle in batch
        foreach ($pending as $vehicle) {
            // Mark as syncing
            SafeQuote_NHTSA_Database::update_sync_log(
                $vehicle->year,
                $vehicle->make,
                $vehicle->model,
                'syncing'
            );

            $result['processed']++;

            // Fetch from NHTSA
            $success = self::fetch_vehicle_rating(
                $vehicle->year,
                $vehicle->make,
                $vehicle->model
            );

            if ($success) {
                $result['success']++;
                error_log("[NHTSA Fetch] ✓ Success: {$vehicle->year} {$vehicle->make} {$vehicle->model}");
            } else {
                $result['failed']++;
                error_log("[NHTSA Fetch] ✗ Failed: {$vehicle->year} {$vehicle->make} {$vehicle->model}");
            }

            // Rate limiting - add small delay between API calls
            usleep(self::API_DELAY_MS * 1000);
        }

        update_option('safequote_nhtsa_last_fetch', current_time('mysql'));

        error_log("[NHTSA Fetch] Batch complete - Success: {$result['success']}, Failed: {$result['failed']}");

        return $result;
    }

    /**
     * Fetch rating for a single vehicle
     *
     * @param int    $year  Vehicle year.
     * @param string $make  Vehicle make.
     * @param string $model Vehicle model.
     * @return bool True if successful, false on error.
     */
    private static function fetch_vehicle_rating($year, $make, $model) {
        try {
            // Attempt to fetch from NHTSA API
            $rating_data = SafeQuote_NHTSA_Cache::get_vehicle_rating($year, $make, $model);

            if ($rating_data && isset($rating_data['OverallRating'])) {
                // Data found - update sync log as success
                SafeQuote_NHTSA_Database::update_sync_log(
                    $year,
                    $make,
                    $model,
                    'success',
                    array(
                        'nhtsa_rating' => $rating_data['OverallRating'],
                        'has_data' => true,
                        'vehicle_id' => $rating_data['VehicleId'] ?? null,
                        'sync_attempt' => 0,
                        'last_attempt' => current_time('mysql'),
                    )
                );

                return true;
            } else {
                // No data available from NHTSA
                SafeQuote_NHTSA_Database::update_sync_log(
                    $year,
                    $make,
                    $model,
                    'no_data',
                    array(
                        'has_data' => false,
                        'sync_attempt' => 0,
                        'last_attempt' => current_time('mysql'),
                    )
                );

                return true; // Treat "no data" as success (vehicle not tested by NHTSA)
            }
        } catch (Exception $e) {
            // Error occurred - mark for retry with backoff
            $log = SafeQuote_NHTSA_Database::get_sync_log($year, $make, $model);
            $attempt = ($log->sync_attempt ?? 0) + 1;
            $backoff_seconds = self::RETRY_BACKOFF_BASE * $attempt;
            $next_retry = date('Y-m-d H:i:s', strtotime("+{$backoff_seconds} seconds"));

            SafeQuote_NHTSA_Database::update_sync_log(
                $year,
                $make,
                $model,
                $attempt < 3 ? 'failed' : 'failed', // Keep as failed even after 3 attempts
                array(
                    'error_message' => substr($e->getMessage(), 0, 255),
                    'sync_attempt' => $attempt,
                    'last_attempt' => current_time('mysql'),
                    'next_attempt' => $attempt < 3 ? $next_retry : null, // Stop retrying after 3 attempts
                )
            );

            error_log("[NHTSA Fetch Error] {$year} {$make} {$model}: {$e->getMessage()} (Attempt {$attempt})");

            return false;
        }
    }

    /**
     * Get fetch statistics
     *
     * @return array Fetch stats.
     */
    public static function get_fetch_stats() {
        global $wpdb;

        $table = $wpdb->prefix . 'nhtsa_sync_log';

        $total = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table");
        $pending = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE status = 'pending'");
        $success = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE status = 'success'");
        $no_data = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE status = 'no_data'");
        $failed = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE status = 'failed'");
        $syncing = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE status = 'syncing'");

        $completion = 0;
        if ($total > 0) {
            $completed = $success + $no_data;
            $completion = round(($completed / $total) * 100, 1);
        }

        return array(
            'total' => $total,
            'pending' => $pending,
            'success' => $success,
            'no_data' => $no_data,
            'failed' => $failed,
            'syncing' => $syncing,
            'completion_percent' => $completion,
            'last_fetch' => get_option('safequote_nhtsa_last_fetch', 'Never'),
        );
    }

    /**
     * Retry failed fetch attempts
     *
     * Called periodically to retry vehicles that failed previously.
     *
     * @return array Result with retry statistics.
     */
    public static function retry_failed() {
        global $wpdb;

        $table = $wpdb->prefix . 'nhtsa_sync_log';

        // Get failed vehicles with retry backoff expiration
        $failed = $wpdb->get_results(
            "SELECT * FROM $table
             WHERE status = 'failed'
             AND next_attempt IS NOT NULL
             AND next_attempt <= NOW()
             AND sync_attempt < 3
             ORDER BY sync_attempt ASC, next_attempt ASC
             LIMIT 5"
        );

        if (empty($failed)) {
            return array('retried' => 0, 'success' => 0);
        }

        $result = array('retried' => 0, 'success' => 0);

        foreach ($failed as $vehicle) {
            $success = self::fetch_vehicle_rating(
                $vehicle->year,
                $vehicle->make,
                $vehicle->model
            );

            $result['retried']++;

            if ($success) {
                $result['success']++;
            }

            usleep(self::API_DELAY_MS * 1000);
        }

        error_log("[NHTSA Retry] Retried: {$result['retried']}, Success: {$result['success']}");

        return $result;
    }

    /**
     * Clear all failed attempts (dangerous - resets sync history)
     *
     * @return int Number of records reset.
     */
    public static function reset_failures() {
        global $wpdb;

        $table = $wpdb->prefix . 'nhtsa_sync_log';

        $count = $wpdb->query(
            "UPDATE $table SET status = 'pending', sync_attempt = 0, next_attempt = NULL
             WHERE status = 'failed'"
        );

        error_log("[NHTSA Fetch] Reset {$count} failed attempts to pending");

        return $count;
    }

    /**
     * Force re-fetch of all vehicles (clears cache, marks pending)
     *
     * @return int Number of vehicles reset.
     */
    public static function force_refetch_all() {
        global $wpdb;

        $sync_table = $wpdb->prefix . 'nhtsa_sync_log';
        $cache_table = $wpdb->prefix . 'nhtsa_vehicle_cache';

        // Clear cache table
        $wpdb->query("TRUNCATE TABLE $cache_table");

        // Reset sync log to pending
        $count = $wpdb->query(
            "UPDATE $sync_table SET status = 'pending', sync_attempt = 0, next_attempt = NULL"
        );

        // Clear transients
        SafeQuote_NHTSA_Cache::clear_transients();

        error_log("[NHTSA Fetch] Force re-fetch: Reset {$count} vehicles");

        return $count;
    }
}
