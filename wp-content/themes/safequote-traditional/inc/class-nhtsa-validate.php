<?php
/**
 * NHTSA Sync Validation
 *
 * Validates sync completeness and generates status reports.
 *
 * @package SafeQuote_Traditional
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class SafeQuote_NHTSA_Validate {

    /**
     * Validate sync completeness and generate report
     *
     * Called after batch fetch to generate status updates.
     *
     * @return array Validation report.
     */
    public static function validate_sync() {
        $sync_stats = SafeQuote_NHTSA_Database::get_sync_stats();
        $cache_stats = SafeQuote_NHTSA_Cache::get_cache_stats();

        $report = array(
            'timestamp' => current_time('mysql'),
            'status' => 'completed',
            'sync' => $sync_stats,
            'cache' => $cache_stats,
            'alerts' => array(),
        );

        // Generate alerts for unusual conditions
        $alerts = self::generate_alerts($report);
        $report['alerts'] = $alerts;

        // Save report
        update_option('safequote_nhtsa_validation_report', $report);

        // Log summary
        $message = sprintf(
            "[NHTSA Validate] Sync: %d vehicles, Success: %d, No Data: %d, Failed: %d, Coverage: %.1f%%",
            $sync_stats['total'],
            $sync_stats['success'],
            $sync_stats['no_data'],
            $sync_stats['failed'],
            $sync_stats['coverage']
        );

        error_log($message);

        // Send admin notification if needed
        if (!empty($alerts)) {
            self::notify_admin($report, $alerts);
        }

        return $report;
    }

    /**
     * Generate alerts based on validation results
     *
     * @param array $report Validation report.
     * @return array Array of alert messages.
     */
    private static function generate_alerts($report) {
        $alerts = array();

        $sync = $report['sync'];
        $fetch = $report['fetch'];

        // Alert if high failure rate
        if ($sync['failed'] > 0 && $sync['total'] > 0) {
            $failure_rate = ($sync['failed'] / $sync['total']) * 100;

            if ($failure_rate > 20) {
                $alerts[] = array(
                    'level' => 'warning',
                    'message' => sprintf(
                        'High NHTSA fetch failure rate: %.1f%% (%d/%d failed)',
                        $failure_rate,
                        $sync['failed'],
                        $sync['total']
                    ),
                );
            }
        }

        // Alert if sync incomplete
        if ($fetch['completion_percent'] < 100 && $fetch['pending'] > 0) {
            $alerts[] = array(
                'level' => 'info',
                'message' => sprintf(
                    'NHTSA sync in progress: %.1f%% complete (%d pending)',
                    $fetch['completion_percent'],
                    $fetch['pending']
                ),
            );
        }

        // Alert if all vehicles have no data
        if ($sync['success'] === 0 && $sync['no_data'] === $sync['total']) {
            $alerts[] = array(
                'level' => 'warning',
                'message' => 'No NHTSA data available for any vehicles',
            );
        }

        return $alerts;
    }

    /**
     * Notify admin of validation issues
     *
     * @param array $report Validation report.
     * @param array $alerts Array of alerts.
     * @return void
     */
    private static function notify_admin($report, $alerts) {
        $admin_email = get_option('admin_email');

        if (!$admin_email) {
            return;
        }

        $alert_text = '';

        foreach ($alerts as $alert) {
            $alert_text .= sprintf(
                "[%s] %s\n",
                strtoupper($alert['level']),
                $alert['message']
            );
        }

        $subject = __('SafeQuote: NHTSA Sync Status Alert', 'safequote-traditional');

        $message = sprintf(
            __("NHTSA sync validation completed.\n\nAlerts:\n%s\nTime: %s\n\nCheck the admin dashboard for details.", 'safequote-traditional'),
            $alert_text,
            $report['timestamp']
        );

        wp_mail($admin_email, $subject, $message);

        error_log("[NHTSA] Admin notification sent: " . count($alerts) . " alert(s)");
    }

    /**
     * Check sync health
     *
     * Returns overall health status of NHTSA sync.
     *
     * @return array Health status.
     */
    public static function check_health() {
        $stats = SafeQuote_NHTSA_Database::get_sync_stats();

        $health = array(
            'status' => 'healthy',
            'coverage' => $stats['coverage'],
            'total_vehicles' => $stats['total'],
            'synchronized' => $stats['success'],
            'no_data' => $stats['no_data'],
            'pending' => $stats['pending'],
            'failed' => $stats['failed'],
            'last_run' => $stats['last_run'],
        );

        // Determine health status
        if ($stats['total'] === 0) {
            $health['status'] = 'not_started';
        } elseif ($stats['failed'] > ($stats['success'] + $stats['no_data']) * 0.1) {
            // More than 10% failure rate
            $health['status'] = 'degraded';
        } elseif ($stats['pending'] > 0) {
            $health['status'] = 'in_progress';
        } elseif ($stats['coverage'] >= 80) {
            $health['status'] = 'healthy';
        } else {
            $health['status'] = 'partial';
        }

        return $health;
    }

    /**
     * Get latest validation report
     *
     * @return array|null Latest validation report or null if not found.
     */
    public static function get_latest_report() {
        return get_option('safequote_nhtsa_validation_report', null);
    }

    /**
     * Clear old cache entries
     *
     * Removes cache entries that have expired.
     * Called periodically as part of maintenance.
     *
     * @return int Number of expired entries removed.
     */
    public static function cleanup_expired_cache() {
        $count = SafeQuote_NHTSA_Database::clear_expired_cache();

        error_log("[NHTSA Cleanup] Removed {$count} expired cache entries");

        return $count;
    }

    /**
     * Generate HTML report for admin dashboard
     *
     * @return string HTML report markup.
     */
    public static function get_html_report() {
        $health = self::check_health();
        $stats = SafeQuote_NHTSA_Database::get_sync_stats();
        $cache_stats = SafeQuote_NHTSA_Cache::get_cache_stats();

        $status_class = 'status-' . $health['status'];
        $status_label = ucfirst(str_replace('_', ' ', $health['status']));

        $html = sprintf(
            '
            <div class="nhtsa-report %s">
                <h3>NHTSA Sync Status</h3>

                <div class="status-badge">
                    <span class="label">%s</span>
                    <span class="coverage">%.1f%% Coverage</span>
                </div>

                <div class="stats-grid">
                    <div class="stat-item">
                        <span class="label">Total Vehicles</span>
                        <span class="value">%d</span>
                    </div>
                    <div class="stat-item success">
                        <span class="label">Synchronized</span>
                        <span class="value">%d</span>
                    </div>
                    <div class="stat-item warning">
                        <span class="label">Pending</span>
                        <span class="value">%d</span>
                    </div>
                    <div class="stat-item">
                        <span class="label">No Data</span>
                        <span class="value">%d</span>
                    </div>
                    <div class="stat-item error">
                        <span class="label">Failed</span>
                        <span class="value">%d</span>
                    </div>
                </div>

                <div class="cache-info">
                    <p><strong>Cache Size:</strong> %s</p>
                    <p><strong>Valid Entries:</strong> %d / %d</p>
                    <p><strong>Last Sync:</strong> %s</p>
                </div>
            </div>
            ',
            $status_class,
            $status_label,
            $health['coverage'],
            $health['total_vehicles'],
            $health['synchronized'],
            $health['pending'],
            $health['no_data'],
            $health['failed'],
            $cache_stats['total_size'],
            $cache_stats['valid_entries'],
            $cache_stats['total_entries'],
            $health['last_run']
        );

        return $html;
    }
}
