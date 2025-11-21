<?php
/**
 * NHTSA CSV Importer
 *
 * Downloads and imports NHTSA safety ratings data from official CSV.
 * Checks for updates on NHTSA datasets page before downloading.
 *
 * @package SafeQuote_Traditional
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class SafeQuote_NHTSA_CSV_Import {

    const CSV_URL = 'https://static.nhtsa.gov/nhtsa/downloads/Safercar/Safercar_data.csv';
    const UPDATE_CHECK_URL = 'https://www.nhtsa.gov/nhtsa-datasets-and-apis';
    const CACHE_DIR = WP_CONTENT_DIR . '/cache/nhtsa/';
    const CSV_FILENAME = 'Safercar_data.csv';
    const REMOTE_HEADERS_CACHE = 'nhtsa_csv_headers';
    const LAST_IMPORT_OPTION = 'safequote_nhtsa_last_csv_import';
    const IMPORT_STATUS_OPTION = 'safequote_nhtsa_import_status';

    /**
     * Check if CSV needs to be updated and import if needed
     *
     * Called by WP-Cron job daily to check for updates.
     *
     * @return array Result with import status.
     */
    public static function sync_csv_data() {
        // Check if remote file was updated
        $remote_updated = self::get_remote_update_time();

        if (!$remote_updated) {
            error_log('[NHTSA CSV] Could not check remote update time');
            $error = array('status' => 'failed', 'reason' => 'could_not_check_remote', 'error' => 'Could not retrieve remote file headers');
            self::store_csv_error($error);
            return $error;
        }

        // Get last import time
        $last_import = (int) get_option(self::LAST_IMPORT_OPTION, 0);

        if ($remote_updated <= $last_import) {
            error_log('[NHTSA CSV] CSV is current, no update needed');
            return array(
                'status' => 'current',
                'last_import' => $last_import,
                'remote_updated' => $remote_updated,
            );
        }

        // CSV has been updated, download and import
        error_log('[NHTSA CSV] CSV updated detected, downloading...');

        $result = self::download_and_import_csv();

        // Always update timestamp on attempt (not just success) to prevent infinite retry loops
        update_option(self::LAST_IMPORT_OPTION, $remote_updated);

        if ($result['status'] === 'success') {
            error_log('[NHTSA CSV] ✓ CSV imported successfully');
            delete_option('safequote_nhtsa_csv_last_error'); // Clear error on success
        } else {
            error_log('[NHTSA CSV] ✗ CSV import failed: ' . $result['reason']);
            // Store detailed error for UI display
            self::store_csv_error($result);
        }

        return $result;
    }

    /**
     * Get last modified time of remote CSV file
     *
     * Uses HTTP HEAD request to check Last-Modified header.
     *
     * @return int|false Last modified timestamp or false.
     */
    private static function get_remote_update_time() {
        $cached = get_transient(self::REMOTE_HEADERS_CACHE);

        if ($cached !== false) {
            return $cached;
        }

        try {
            $response = wp_remote_head(self::CSV_URL, array(
                'timeout' => 10,
                'sslverify' => true,
            ));

            if (is_wp_error($response)) {
                error_log('[NHTSA CSV] Error checking remote headers: ' . $response->get_error_message());
                return false;
            }

            $headers = wp_remote_retrieve_headers($response);
            $last_modified = $headers['last-modified'] ?? null;

            if (!$last_modified) {
                error_log('[NHTSA CSV] No Last-Modified header found');
                return false;
            }

            // Convert to timestamp
            $timestamp = strtotime($last_modified);

            if (!$timestamp) {
                error_log('[NHTSA CSV] Could not parse Last-Modified: ' . $last_modified);
                return false;
            }

            // Cache header check for 24 hours
            set_transient(self::REMOTE_HEADERS_CACHE, $timestamp, 24 * HOUR_IN_SECONDS);

            return $timestamp;

        } catch (Exception $e) {
            error_log('[NHTSA CSV] Exception checking remote: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Download and import CSV data
     *
     * @return array Result with status and details.
     */
    private static function download_and_import_csv() {
        // Create cache directory if needed
        if (!file_exists(self::CACHE_DIR)) {
            wp_mkdir_p(self::CACHE_DIR);
        }

        $csv_path = self::CACHE_DIR . self::CSV_FILENAME;

        // Download CSV
        try {
            $response = wp_remote_get(self::CSV_URL, array(
                'timeout' => 30,
                'sslverify' => true,
                'stream' => true,
                'filename' => $csv_path,
            ));

            if (is_wp_error($response)) {
                $error_msg = $response->get_error_message();
                error_log('[NHTSA CSV] Download error: ' . $error_msg);
                return array(
                    'status' => 'failed',
                    'reason' => 'download_failed',
                    'error' => 'Failed to download CSV: ' . $error_msg,
                );
            }

            if (!file_exists($csv_path)) {
                return array(
                    'status' => 'failed',
                    'reason' => 'file_not_saved',
                );
            }

            error_log('[NHTSA CSV] ✓ Downloaded to ' . $csv_path);

        } catch (Exception $e) {
            return array(
                'status' => 'failed',
                'reason' => 'download_exception',
                'error' => $e->getMessage(),
            );
        }

        // Import CSV data
        $import_result = self::import_csv_file($csv_path);

        return $import_result;
    }

    /**
     * Parse and import CSV file into database
     *
     * @param string $csv_path Path to CSV file.
     * @return array Result with status and counts.
     */
    private static function import_csv_file($csv_path) {
        if (!file_exists($csv_path)) {
            return array(
                'status' => 'failed',
                'reason' => 'file_not_found',
            );
        }

        global $wpdb;

        $table = $wpdb->prefix . 'nhtsa_vehicle_cache';
        $imported = 0;
        $skipped = 0;
        $errors = 0;

        try {
            $handle = fopen($csv_path, 'r');

            if (!$handle) {
                return array(
                    'status' => 'failed',
                    'reason' => 'cannot_open_file',
                );
            }

            // Skip header row
            $headers = fgetcsv($handle);

            if (!$headers) {
                fclose($handle);
                return array(
                    'status' => 'failed',
                    'reason' => 'invalid_csv_format',
                );
            }

            // Map CSV columns to database fields
            $column_map = self::map_csv_columns($headers);

            if (!$column_map) {
                fclose($handle);
                return array(
                    'status' => 'failed',
                    'reason' => 'missing_required_columns',
                );
            }

            // Import rows
            while (($row = fgetcsv($handle)) !== false) {
                $data = self::parse_csv_row($row, $column_map);

                if (!$data) {
                    $skipped++;
                    continue;
                }

                // Insert or update in database with 'csv' source
                // Smart merge: won't overwrite API data with empty CSV rating
                $result = SafeQuote_NHTSA_Database::update_vehicle_cache(
                    $data['year'],
                    $data['make'],
                    $data['model'],
                    $data['nhtsa_data'],
                    null, // Permanent storage - never expires or deletes
                    'csv' // Mark as CSV source
                );

                if ($result) {
                    $imported++;
                    // Clear transient for this vehicle
                    $cache_key = "nhtsa_rating_{$data['year']}_{$data['make']}_{$data['model']}";
                    delete_transient($cache_key);
                } else {
                    $errors++;
                }
            }

            fclose($handle);

            error_log(
                "[NHTSA CSV] Import complete - Imported: $imported, Skipped: $skipped, Errors: $errors"
            );

            return array(
                'status' => 'success',
                'imported' => $imported,
                'skipped' => $skipped,
                'errors' => $errors,
            );

        } catch (Exception $e) {
            return array(
                'status' => 'failed',
                'reason' => 'import_exception',
                'error' => $e->getMessage(),
            );
        }
    }

    /**
     * Map CSV column headers to expected fields
     *
     * @param array $headers CSV header row.
     * @return array|false Column mapping or false if missing required fields.
     */
    private static function map_csv_columns($headers) {
        $headers_lower = array_map('strtolower', $headers);

        // Find required columns (flexible naming)
        $map = array();

        foreach ($headers_lower as $index => $header) {
            // Year: MODEL_YR, year, model year
            if (stripos($header, 'model_yr') !== false || stripos($header, 'year') !== false || stripos($header, 'model year') !== false) {
                $map['year'] = $index;
            }
            // Make: MAKE (but not part of model)
            elseif (stripos($header, 'make') !== false && stripos($header, 'model') === false) {
                $map['make'] = $index;
            }
            // Model: MODEL (but not BODY_STYLE)
            elseif (stripos($header, 'model') !== false && stripos($header, 'year') === false && stripos($header, 'body') === false) {
                $map['model'] = $index;
            }
            // Overall rating: OVERALL_STARS or overall rating
            elseif ((stripos($header, 'overall') !== false && stripos($header, 'stars') !== false) ||
                    (stripos($header, 'overall') !== false && stripos($header, 'rating') !== false)) {
                $map['overall_rating'] = $index;
            }
            // Front crash: FRNT_DRIV_STARS or front crash
            elseif ((stripos($header, 'frnt') !== false && stripos($header, 'star') !== false) ||
                    (stripos($header, 'front') !== false && stripos($header, 'crash') !== false)) {
                $map['front_crash'] = $index;
            }
            // Side crash: SIDE_DRIV_STARS or side crash
            elseif ((stripos($header, 'side') !== false && stripos($header, 'star') !== false && stripos($header, 'barrier') === false) ||
                    (stripos($header, 'side') !== false && stripos($header, 'crash') !== false)) {
                $map['side_crash'] = $index;
            }
            // Rollover: ROLLOVER_STARS or rollover crash
            elseif ((stripos($header, 'rollover') !== false && stripos($header, 'star') !== false) ||
                    (stripos($header, 'rollover') !== false && stripos($header, 'crash') !== false)) {
                $map['rollover_crash'] = $index;
            }
        }

        // Check required fields exist
        $required = array('year', 'make', 'model', 'overall_rating');

        foreach ($required as $field) {
            if (!isset($map[$field])) {
                error_log("[NHTSA CSV] Missing required column: $field. Headers found: " . implode(', ', $headers_lower));
                return false;
            }
        }

        error_log('[NHTSA CSV] Column mapping successful: ' . json_encode($map));

        return $map;
    }

    /**
     * Parse single CSV row into database format
     *
     * Accepts vehicles even without ratings - API will fill gaps later.
     *
     * @param array $row        CSV row data.
     * @param array $column_map Column mapping.
     * @return array|false Parsed data or false if invalid.
     */
    private static function parse_csv_row($row, $column_map) {
        // Extract fields using column map
        $year = isset($row[$column_map['year']]) ? intval($row[$column_map['year']]) : null;
        $make = isset($row[$column_map['make']]) ? sanitize_text_field($row[$column_map['make']]) : null;
        $model = isset($row[$column_map['model']]) ? sanitize_text_field($row[$column_map['model']]) : null;

        // Validate vehicle identity - year, make, model are required
        if (!$year || !$make || !$model) {
            return false;
        }

        // Parse rating - only numeric 1-5 is valid, skip invalid values like "Standard", "Optional", empty
        $rating_value = isset($row[$column_map['overall_rating']]) ? trim($row[$column_map['overall_rating']]) : '';
        $overall_rating = null;

        // Only accept numeric ratings 1-5
        if ($rating_value && preg_match('/^[1-5](?:\.\d+)?$/', $rating_value)) {
            $overall_rating = floatval($rating_value);
        }

        // Build NHTSA data object (rating can be null - API will fill)
        $nhtsa_data = array(
            'overall_rating' => $overall_rating,
            'front_crash' => isset($row[$column_map['front_crash']]) && preg_match('/^[1-5](?:\.\d+)?$/', trim($row[$column_map['front_crash']])) ? floatval($row[$column_map['front_crash']]) : null,
            'side_crash' => isset($row[$column_map['side_crash']]) && preg_match('/^[1-5](?:\.\d+)?$/', trim($row[$column_map['side_crash']])) ? floatval($row[$column_map['side_crash']]) : null,
            'rollover_crash' => isset($row[$column_map['rollover_crash']]) && preg_match('/^[1-5](?:\.\d+)?$/', trim($row[$column_map['rollover_crash']])) ? floatval($row[$column_map['rollover_crash']]) : null,
            'source' => 'csv_import',
        );

        return array(
            'year' => $year,
            'make' => $make,
            'model' => $model,
            'nhtsa_data' => $nhtsa_data,
        );
    }

    /**
     * Get import statistics
     *
     * @return array Import stats.
     */
    public static function get_import_stats() {
        global $wpdb;

        $table = $wpdb->prefix . 'nhtsa_vehicle_cache';

        $total = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE rating_source = 'csv'");
        $expired = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE rating_source = 'csv' AND expires_at < NOW()");
        $valid = $total - $expired;

        return array(
            'total_imported' => $total,
            'valid_entries' => $valid,
            'expired_entries' => $expired,
            'last_import' => get_option(self::LAST_IMPORT_OPTION, 'Never'),
        );
    }

    /**
     * Force reimport of CSV
     *
     * @return array Result.
     */
    public static function force_reimport() {
        delete_transient(self::REMOTE_HEADERS_CACHE);
        return self::sync_csv_data();
    }

    /**
     * Cleanup old CSV cache file
     *
     * @return void
     */
    public static function cleanup() {
        $csv_path = self::CACHE_DIR . self::CSV_FILENAME;

        if (file_exists($csv_path)) {
            wp_delete_file($csv_path);
        }

        if (file_exists(self::CACHE_DIR) && is_dir(self::CACHE_DIR)) {
            @rmdir(self::CACHE_DIR);
        }

        delete_transient(self::REMOTE_HEADERS_CACHE);
    }

    /**
     * Store CSV import error for UI display
     *
     * @param array $error Error details with status, reason, error message.
     * @return void
     */
    private static function store_csv_error($error) {
        $errors = get_option('safequote_nhtsa_csv_errors', array());

        // Keep last 5 errors
        if (!is_array($errors)) {
            $errors = array();
        }
        if (count($errors) >= 5) {
            array_shift($errors);
        }

        // Add new error with timestamp
        $errors[] = array(
            'timestamp' => current_time('mysql'),
            'reason' => isset($error['reason']) ? $error['reason'] : 'unknown',
            'error' => isset($error['error']) ? $error['error'] : '',
        );

        update_option('safequote_nhtsa_csv_errors', $errors);
        update_option('safequote_nhtsa_csv_last_error', array(
            'timestamp' => current_time('mysql'),
            'reason' => isset($error['reason']) ? $error['reason'] : 'unknown',
            'error' => isset($error['error']) ? $error['error'] : '',
        ));
    }
}
