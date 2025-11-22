<?php
/**
 * NHTSA Database Schema & Migration
 *
 * Handles creation and management of custom database tables
 * for caching NHTSA vehicle safety data and sync logs.
 *
 * @package SafeQuote_Traditional
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class SafeQuote_NHTSA_Database {

    /**
     * Create database tables for NHTSA caching
     *
     * @return void
     */
    public static function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        // Table 1: Sync Log - Tracks which vehicles have been synced
        $table_sync_log = $wpdb->prefix . 'nhtsa_sync_log';
        $sql_sync_log = "CREATE TABLE IF NOT EXISTS $table_sync_log (
            id BIGINT AUTO_INCREMENT PRIMARY KEY,
            year YEAR NOT NULL,
            make VARCHAR(100) NOT NULL,
            model VARCHAR(100) NOT NULL,
            vehicle_id INT,
            nhtsa_rating DECIMAL(3,1),
            has_data BOOLEAN DEFAULT FALSE,
            sync_attempt INT DEFAULT 0,
            last_attempt DATETIME,
            next_attempt DATETIME,
            error_message TEXT,
            status ENUM('pending', 'syncing', 'success', 'no_data', 'failed') DEFAULT 'pending',
            sync_hash VARCHAR(64),
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY idx_status (status),
            KEY idx_year_make_model (year, make, model),
            KEY idx_next_attempt (next_attempt),
            UNIQUE KEY unique_vehicle (year, make, model)
        ) $charset_collate;";

        // Table 2: Vehicle Cache - Stores actual NHTSA vehicle data
        $table_vehicle_cache = $wpdb->prefix . 'nhtsa_vehicle_cache';
        $sql_vehicle_cache = "CREATE TABLE IF NOT EXISTS $table_vehicle_cache (
            id BIGINT AUTO_INCREMENT PRIMARY KEY,
            year YEAR NOT NULL,
            make VARCHAR(100) NOT NULL,
            model VARCHAR(100) NOT NULL,
            vehicle_id INT,
            nhtsa_overall_rating DECIMAL(3,1),
            front_crash DECIMAL(3,1),
            side_crash DECIMAL(3,1),
            rollover_crash DECIMAL(3,1),
            vehicle_picture VARCHAR(500),
            nhtsa_data LONGTEXT NOT NULL,
            rating_source ENUM('csv', 'api', 'manual') DEFAULT 'csv' NOT NULL,
            cached_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            expires_at DATETIME,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY idx_year_make_model (year, make, model),
            KEY idx_vehicle_id (vehicle_id),
            KEY idx_expires (expires_at),
            KEY idx_rating_source (rating_source),
            UNIQUE KEY unique_vehicle (year, make, model)
        ) $charset_collate;";

        // Execute table creation
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        dbDelta($sql_sync_log);
        dbDelta($sql_vehicle_cache);

        // Migrate existing tables: add rating_source column if missing
        self::add_missing_columns();

        // Log success
        update_option('safequote_nhtsa_tables_created', current_time('mysql'));
    }

    /**
     * Add missing columns to existing tables (for migration)
     *
     * @return void
     */
    private static function add_missing_columns() {
        global $wpdb;

        $table_vehicle_cache = $wpdb->prefix . 'nhtsa_vehicle_cache';

        // Check if rating_source column exists
        $rating_source_exists = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = %s AND COLUMN_NAME = %s",
                $table_vehicle_cache,
                'rating_source'
            )
        );

        if (empty($rating_source_exists)) {
            // Column doesn't exist, add it
            $wpdb->query("ALTER TABLE $table_vehicle_cache ADD COLUMN rating_source ENUM('csv', 'api', 'manual') DEFAULT 'csv' NOT NULL");
            error_log('[NHTSA DB] Added missing rating_source column to ' . $table_vehicle_cache);
        }

        // Check if vehicle_picture column exists
        $vehicle_picture_exists = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = %s AND COLUMN_NAME = %s",
                $table_vehicle_cache,
                'vehicle_picture'
            )
        );

        if (empty($vehicle_picture_exists)) {
            // Column doesn't exist, add it
            $wpdb->query("ALTER TABLE $table_vehicle_cache ADD COLUMN vehicle_picture VARCHAR(500) AFTER rollover_crash");
            error_log('[NHTSA DB] Added missing vehicle_picture column to ' . $table_vehicle_cache);
        }
    }

    /**
     * Drop tables (for uninstall/reset)
     *
     * @return void
     */
    public static function drop_tables() {
        global $wpdb;

        $table_sync_log = $wpdb->prefix . 'nhtsa_sync_log';
        $table_vehicle_cache = $wpdb->prefix . 'nhtsa_vehicle_cache';

        // Use no priv to prevent SQL errors if tables don't exist
        $wpdb->query("DROP TABLE IF EXISTS $table_sync_log");
        $wpdb->query("DROP TABLE IF EXISTS $table_vehicle_cache");

        delete_option('safequote_nhtsa_tables_created');
    }

    /**
     * Get sync log entry for vehicle
     *
     * @param int    $year  Vehicle year.
     * @param string $make  Vehicle make.
     * @param string $model Vehicle model.
     * @return object|null Sync log entry or null if not found.
     */
    public static function get_sync_log($year, $make, $model) {
        global $wpdb;

        $table = $wpdb->prefix . 'nhtsa_sync_log';

        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE year = %d AND make = %s AND model = %s",
            $year,
            sanitize_text_field($make),
            sanitize_text_field($model)
        ));
    }

    /**
     * Insert or update sync log entry
     *
     * @param int    $year   Vehicle year.
     * @param string $make   Vehicle make.
     * @param string $model  Vehicle model.
     * @param string $status Sync status.
     * @param array  $data   Additional data to update.
     * @return int|bool Row ID on success, false on failure.
     */
    public static function update_sync_log($year, $make, $model, $status, $data = array()) {
        global $wpdb;

        $table = $wpdb->prefix . 'nhtsa_sync_log';
        $existing = self::get_sync_log($year, $make, $model);

        $update_data = array(
            'year' => $year,
            'make' => sanitize_text_field($make),
            'model' => sanitize_text_field($model),
            'status' => sanitize_text_field($status),
            'updated_at' => current_time('mysql'),
        );

        // Merge additional data
        if (!empty($data)) {
            $update_data = array_merge($update_data, $data);
        }

        if ($existing) {
            // Update existing entry
            return $wpdb->update($table, $update_data, array(
                'year' => $year,
                'make' => $make,
                'model' => $model,
            ), null, array('%d', '%s', '%s'));
        } else {
            // Insert new entry
            $update_data['created_at'] = current_time('mysql');
            return $wpdb->insert($table, $update_data);
        }
    }

    /**
     * Get vehicle cache entry
     *
     * @param int    $year  Vehicle year.
     * @param string $make  Vehicle make.
     * @param string $model Vehicle model.
     * @return object|null Vehicle cache entry or null if not found.
     */
    public static function get_vehicle_cache($year, $make, $model) {
        global $wpdb;

        $table = $wpdb->prefix . 'nhtsa_vehicle_cache';

        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE year = %d AND make = %s AND model = %s AND (expires_at IS NULL OR expires_at > NOW())",
            $year,
            sanitize_text_field($make),
            sanitize_text_field($model)
        ));
    }

    /**
     * Insert or update vehicle cache with smart merge logic
     *
     * @param int    $year         Vehicle year.
     * @param string $make         Vehicle make.
     * @param string $model        Vehicle model.
     * @param array  $nhtsa_data   NHTSA API response data.
     * @param int    $expires_in   Hours until cache expires (NULL or 0 = permanent storage, never expires).
     * @param string $source       Data source: 'csv', 'api', 'manual'.
     * @return int|bool Row ID on success, false on failure.
     */
    public static function update_vehicle_cache($year, $make, $model, $nhtsa_data, $expires_in = null, $source = 'csv') {
        global $wpdb;

        $table = $wpdb->prefix . 'nhtsa_vehicle_cache';
        $existing = self::get_vehicle_cache($year, $make, $model);

        // Extract rating from nhtsa_data using PascalCase field names
        $new_rating = isset($nhtsa_data['OverallRating']) ? floatval($nhtsa_data['OverallRating']) : null;

        // Smart merge logic: Don't overwrite API data with CSV null/empty
        if ($existing && $source === 'csv' && $new_rating === null && $existing->rating_source === 'api') {
            error_log("[NHTSA DB] Skipping CSV update for $year $make $model - protecting API data");
            return true; // Don't overwrite
        }

        // Set expiration: NULL or 0 means permanent storage (never expires)
        $expires_at = ($expires_in && $expires_in > 0) ? date('Y-m-d H:i:s', strtotime("+{$expires_in} hours")) : null;

        $cache_data = array(
            'year' => $year,
            'make' => sanitize_text_field($make),
            'model' => sanitize_text_field($model),
            'nhtsa_overall_rating' => $new_rating,
            'front_crash' => isset($nhtsa_data['OverallFrontCrashRating']) ? floatval($nhtsa_data['OverallFrontCrashRating']) : null,
            'side_crash' => isset($nhtsa_data['OverallSideCrashRating']) ? floatval($nhtsa_data['OverallSideCrashRating']) : null,
            'rollover_crash' => isset($nhtsa_data['RolloverRating']) ? floatval($nhtsa_data['RolloverRating']) : null,
            'vehicle_picture' => isset($nhtsa_data['VehiclePicture']) ? sanitize_url($nhtsa_data['VehiclePicture']) : null,
            'nhtsa_data' => json_encode($nhtsa_data),
            'rating_source' => sanitize_text_field($source),
            'expires_at' => $expires_at,
            'updated_at' => current_time('mysql'),
        );

        if ($existing) {
            // Update existing
            return $wpdb->update($table, $cache_data, array(
                'year' => $year,
                'make' => $make,
                'model' => $model,
            ), null, array('%d', '%s', '%s', '%f', '%f', '%f', '%f', '%s', '%s', '%s', '%s', '%s'));
        } else {
            // Insert new
            $cache_data['created_at'] = current_time('mysql');
            return $wpdb->insert($table, $cache_data, array('%d', '%s', '%s', '%f', '%f', '%f', '%f', '%s', '%s', '%s', '%s', '%s', '%s'));
        }
    }

    /**
     * Get sync statistics
     *
     * @return array Sync statistics.
     */
    public static function get_sync_stats() {
        global $wpdb;

        $table = $wpdb->prefix . 'nhtsa_sync_log';

        $stats = $wpdb->get_results(
            "SELECT status, COUNT(*) as count FROM $table GROUP BY status"
        );

        $summary = array(
            'pending' => 0,
            'syncing' => 0,
            'success' => 0,
            'no_data' => 0,
            'failed' => 0,
            'total' => 0,
        );

        foreach ($stats as $stat) {
            if (isset($summary[$stat->status])) {
                $summary[$stat->status] = (int) $stat->count;
            }
        }

        $summary['total'] = array_sum($summary);
        $summary['coverage'] = $summary['total'] > 0 ? round(($summary['success'] / $summary['total']) * 100, 1) : 0;
        $summary['last_run'] = get_option('safequote_nhtsa_last_sync', 'Never');

        return $summary;
    }

    /**
     * Clear expired cache entries
     *
     * @return int Number of rows deleted.
     */
    public static function clear_expired_cache() {
        // ⚠️ DISABLED: Database records are now permanent storage and NEVER deleted.
        // This function is kept for backward compatibility but no longer performs deletions.
        // Records are only updated with new data, never removed.
        error_log("[NHTSA DB] Cleanup check: Database records are permanent storage - no deletions performed");
        return 0; // Return 0 records deleted (no deletion happens)
    }

    /**
     * Truncate all NHTSA tables (for complete reset)
     *
     * @return void
     */
    public static function truncate_all() {
        global $wpdb;

        $table_sync_log = $wpdb->prefix . 'nhtsa_sync_log';
        $table_vehicle_cache = $wpdb->prefix . 'nhtsa_vehicle_cache';

        $wpdb->query("TRUNCATE TABLE $table_sync_log");
        $wpdb->query("TRUNCATE TABLE $table_vehicle_cache");

        delete_option('safequote_nhtsa_sync_stats');
        delete_option('safequote_nhtsa_last_sync');
    }
}
