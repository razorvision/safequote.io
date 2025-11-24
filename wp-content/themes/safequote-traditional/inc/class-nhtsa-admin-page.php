<?php
/**
 * NHTSA Admin Settings Page
 *
 * Provides admin interface for manual CSV import trigger,
 * database status, and import history.
 *
 * @package SafeQuote_Traditional
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class SafeQuote_NHTSA_Admin_Page {

    /**
     * Initialize admin page
     *
     * @return void
     */
    public static function init() {
        add_action('admin_menu', array(__CLASS__, 'register_menu'));
        add_action('admin_init', array(__CLASS__, 'handle_form_submission'));
    }

    /**
     * Register admin menu page
     *
     * @return void
     */
    public static function register_menu() {
        add_options_page(
            __('NHTSA Vehicle Data', 'safequote-traditional'),
            __('NHTSA Vehicle Data', 'safequote-traditional'),
            'manage_options',
            'safequote-nhtsa',
            array(__CLASS__, 'render_page')
        );
    }

    /**
     * Get system information for Information section
     *
     * @return array System info data.
     */
    private static function get_system_info() {
        return array(
            'wordpress_version' => get_bloginfo('version'),
            'php_version' => phpversion(),
            'theme_version' => SAFEQUOTE_THEME_VERSION,
            'wp_cron_enabled' => !defined('DISABLE_WP_CRON') || !DISABLE_WP_CRON,
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'db_initialized' => get_option('safequote_nhtsa_db_initialized'),
        );
    }

    /**
     * Get human-readable next cron run times
     *
     * @return array Next scheduled run times.
     */
    private static function get_next_cron_runs() {
        return array(
            'auto_batch' => array(
                'timestamp' => wp_next_scheduled('safequote_nhtsa_auto_batch_fill'),
                'label' => __('Auto Batch Fill', 'safequote-traditional'),
            ),
            'csv_sync' => array(
                'timestamp' => wp_next_scheduled('safequote_nhtsa_csv_sync'),
                'label' => __('CSV Sync', 'safequote-traditional'),
            ),
            'validation' => array(
                'timestamp' => wp_next_scheduled('safequote_nhtsa_validate'),
                'label' => __('Validation', 'safequote-traditional'),
            ),
            'cleanup' => array(
                'timestamp' => wp_next_scheduled('safequote_nhtsa_cleanup'),
                'label' => __('Cleanup', 'safequote-traditional'),
            ),
        );
    }

    /**
     * Format timestamp as human-readable time
     *
     * @param int $timestamp Unix timestamp.
     * @return string Human-readable time (e.g., "in 2 minutes" or "Tomorrow at 2:00 AM").
     */
    private static function format_next_run($timestamp) {
        if (!$timestamp) {
            return __('Not scheduled', 'safequote-traditional');
        }

        $now = time();
        $diff = $timestamp - $now;

        if ($diff < 0) {
            return __('Should run soon', 'safequote-traditional');
        }

        if ($diff < 60) {
            return sprintf(__('in %d second%s', 'safequote-traditional'), $diff, $diff !== 1 ? 's' : '');
        }

        if ($diff < 3600) {
            $minutes = round($diff / 60);
            return sprintf(__('in %d minute%s', 'safequote-traditional'), $minutes, $minutes !== 1 ? 's' : '');
        }

        if ($diff < 86400) {
            $hours = round($diff / 3600);
            return sprintf(__('in %d hour%s', 'safequote-traditional'), $hours, $hours !== 1 ? 's' : '');
        }

        // Tomorrow or later
        return wp_date('l @ g:i A', $timestamp);
    }

    /**
     * Handle form submissions (manual trigger)
     *
     * @return void
     */
    public static function handle_form_submission() {
        if (!isset($_POST['safequote_nhtsa_nonce'])) {
            return;
        }

        if (!wp_verify_nonce($_POST['safequote_nhtsa_nonce'], 'safequote_nhtsa_action')) {
            wp_die(__('Security check failed', 'safequote-traditional'));
        }

        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized', 'safequote-traditional'));
        }

        // Handle Force Reimport trigger (with database cleanup)
        if (isset($_POST['safequote_nhtsa_force_reimport'])) {
            require_once SAFEQUOTE_THEME_DIR . '/inc/class-nhtsa-csv-import.php';

            // Force reimport will automatically clean old CSV data and force fresh download
            $result = SafeQuote_NHTSA_CSV_Import::force_reimport();

            if ($result['status'] === 'success') {
                add_settings_error(
                    'safequote_nhtsa',
                    'force_reimport_success',
                    sprintf(
                        __('✓ Force reimport successful! Cleaned old records and imported: %d vehicles with new PascalCase format', 'safequote-traditional'),
                        $result['imported']
                    ),
                    'success'
                );
            } else {
                add_settings_error(
                    'safequote_nhtsa',
                    'force_reimport_error',
                    sprintf(
                        __('⚠ Force reimport failed: %s (reason: %s). Check WordPress debug log for details.', 'safequote-traditional'),
                        $result['error'] ?? 'Unknown error',
                        $result['reason'] ?? 'unknown'
                    ),
                    'error'
                );
                error_log('[NHTSA Admin] Force reimport result: ' . json_encode($result));
            }
        }

        // Handle CSV sync trigger
        if (isset($_POST['safequote_nhtsa_sync'])) {
            require_once SAFEQUOTE_THEME_DIR . '/inc/class-nhtsa-csv-import.php';

            $result = SafeQuote_NHTSA_CSV_Import::sync_csv_data();

            if ($result['status'] === 'success') {
                add_settings_error(
                    'safequote_nhtsa',
                    'sync_success',
                    sprintf(
                        __('CSV import successful! Imported: %d vehicles', 'safequote-traditional'),
                        $result['imported']
                    ),
                    'success'
                );
            } elseif ($result['status'] === 'current') {
                add_settings_error(
                    'safequote_nhtsa',
                    'sync_current',
                    __('CSV is already current, no import needed', 'safequote-traditional'),
                    'info'
                );
            } else {
                add_settings_error(
                    'safequote_nhtsa',
                    'sync_failed',
                    sprintf(
                        __('CSV import failed: %s', 'safequote-traditional'),
                        $result['reason']
                    ),
                    'error'
                );
            }
        }

        // Handle batch fill missing ratings (start new batch)
        if (isset($_POST['safequote_nhtsa_batch_fill'])) {
            // Delete old batch session to start fresh
            delete_option('safequote_nhtsa_batch_session');
            $result = self::batch_fill_missing_ratings(true); // true = start new batch

            if ($result['success']) {
                if ($result['status'] === 'in_progress') {
                    add_settings_error(
                        'safequote_nhtsa',
                        'batch_fill_progress',
                        sprintf(
                            __('Batch started! Processed: %d/%d vehicles, Updated: %d. Click "Continue Batch" to process more.', 'safequote-traditional'),
                            $result['processed'],
                            $result['total'],
                            $result['updated']
                        ),
                        'info'
                    );
                } else {
                    add_settings_error(
                        'safequote_nhtsa',
                        'batch_fill_success',
                        sprintf(
                            __('Batch complete! Processed: %d vehicles, Updated: %d with API data', 'safequote-traditional'),
                            $result['processed'],
                            $result['updated']
                        ),
                        'success'
                    );
                }
            } else {
                add_settings_error(
                    'safequote_nhtsa',
                    'batch_fill_failed',
                    sprintf(
                        __('Batch fill failed: %s', 'safequote-traditional'),
                        $result['reason']
                    ),
                    'error'
                );
            }
        }

        // Handle batch fill continue (resume existing batch)
        if (isset($_POST['safequote_nhtsa_batch_continue'])) {
            $result = self::batch_fill_missing_ratings(false); // false = continue existing batch

            if ($result['success']) {
                if ($result['status'] === 'in_progress') {
                    add_settings_error(
                        'safequote_nhtsa',
                        'batch_continue_progress',
                        sprintf(
                            __('Batch continuing! Processed: %d/%d vehicles, Updated: %d. Click "Continue Batch" to process more.', 'safequote-traditional'),
                            $result['processed'],
                            $result['total'],
                            $result['updated']
                        ),
                        'info'
                    );
                } else {
                    add_settings_error(
                        'safequote_nhtsa',
                        'batch_continue_complete',
                        sprintf(
                            __('Batch complete! Total processed: %d vehicles, Updated: %d with API data', 'safequote-traditional'),
                            $result['processed'],
                            $result['updated']
                        ),
                        'success'
                    );
                }
            } else {
                add_settings_error(
                    'safequote_nhtsa',
                    'batch_continue_failed',
                    sprintf(
                        __('Batch resume failed: %s', 'safequote-traditional'),
                        $result['reason']
                    ),
                    'error'
                );
            }
        }

        // Handle database initialization/repair
        if (isset($_POST['safequote_nhtsa_init_db'])) {
            require_once SAFEQUOTE_THEME_DIR . '/inc/class-nhtsa-init.php';

            try {
                SafeQuote_NHTSA_Init::create_database_tables();
                add_settings_error(
                    'safequote_nhtsa',
                    'db_init_success',
                    __('Database tables initialized/repaired successfully!', 'safequote-traditional'),
                    'success'
                );
            } catch (Exception $e) {
                add_settings_error(
                    'safequote_nhtsa',
                    'db_init_failed',
                    sprintf(
                        __('Database initialization failed: %s', 'safequote-traditional'),
                        $e->getMessage()
                    ),
                    'error'
                );
            }
        }

        // Handle year-based batch fill (start new)
        if (isset($_POST['safequote_nhtsa_year_batch_fill'])) {
            $year = isset($_POST['safequote_nhtsa_target_year']) ? (int) $_POST['safequote_nhtsa_target_year'] : 0;

            if ($year < 2015 || $year > 2026) {
                add_settings_error(
                    'safequote_nhtsa',
                    'year_batch_invalid_year',
                    __('Invalid year selected. Please choose a year between 2015 and 2026.', 'safequote-traditional'),
                    'error'
                );
            } else {
                // Delete old year batch session to start fresh
                $session_key = "safequote_nhtsa_year_batch_session_{$year}";
                delete_option($session_key);

                $result = self::batch_fill_missing_ratings_by_year($year, true); // true = start new

                if ($result['success']) {
                    if ($result['status'] === 'in_progress') {
                        add_settings_error(
                            'safequote_nhtsa',
                            'year_batch_fill_progress',
                            sprintf(
                                __('Year batch started for %d! Processed: %d/%d vehicles, Updated: %d. Click "Continue Year Batch" to process more.', 'safequote-traditional'),
                                $year,
                                $result['processed'],
                                $result['total'],
                                $result['updated']
                            ),
                            'info'
                        );
                    } else {
                        add_settings_error(
                            'safequote_nhtsa',
                            'year_batch_fill_success',
                            sprintf(
                                __('Year batch complete for %d! Processed: %d vehicles, Updated: %d with API data', 'safequote-traditional'),
                                $year,
                                $result['processed'],
                                $result['updated']
                            ),
                            'success'
                        );
                    }
                } else {
                    add_settings_error(
                        'safequote_nhtsa',
                        'year_batch_fill_failed',
                        sprintf(
                            __('Year batch fill failed for %d: %s', 'safequote-traditional'),
                            $year,
                            $result['reason']
                        ),
                        'error'
                    );
                }
            }
        }

        // Handle year-based batch fill continue (resume existing)
        if (isset($_POST['safequote_nhtsa_year_batch_continue'])) {
            $year = isset($_POST['safequote_nhtsa_target_year']) ? (int) $_POST['safequote_nhtsa_target_year'] : 0;

            if ($year < 2015 || $year > 2026) {
                add_settings_error(
                    'safequote_nhtsa',
                    'year_batch_invalid_year',
                    __('Invalid year selected. Please choose a year between 2015 and 2026.', 'safequote-traditional'),
                    'error'
                );
            } else {
                $result = self::batch_fill_missing_ratings_by_year($year, false); // false = continue

                if ($result['success']) {
                    if ($result['status'] === 'in_progress') {
                        add_settings_error(
                            'safequote_nhtsa',
                            'year_batch_continue_progress',
                            sprintf(
                                __('Year batch continuing for %d! Processed: %d/%d vehicles, Updated: %d. Click "Continue Year Batch" to process more.', 'safequote-traditional'),
                                $year,
                                $result['processed'],
                                $result['total'],
                                $result['updated']
                            ),
                            'info'
                        );
                    } else {
                        add_settings_error(
                            'safequote_nhtsa',
                            'year_batch_continue_complete',
                            sprintf(
                                __('Year batch complete for %d! Total processed: %d vehicles, Updated: %d with API data', 'safequote-traditional'),
                                $year,
                                $result['processed'],
                                $result['updated']
                            ),
                            'success'
                        );
                    }
                } else {
                    add_settings_error(
                        'safequote_nhtsa',
                        'year_batch_continue_failed',
                        sprintf(
                            __('Year batch resume failed for %d: %s', 'safequote-traditional'),
                            $year,
                            $result['reason']
                        ),
                        'error'
                    );
                }
            }
        }

        // Handle disable all cron tasks
        if (isset($_POST['safequote_nhtsa_disable_crons'])) {
            require_once SAFEQUOTE_THEME_DIR . '/inc/class-nhtsa-init.php';

            SafeQuote_NHTSA_Init::disable_all_crons();
            update_option('safequote_nhtsa_crons_disabled', true);

            add_settings_error(
                'safequote_nhtsa',
                'crons_disabled',
                __('All NHTSA cron tasks have been disabled. Automatic data updates will not run until you enable them again.', 'safequote-traditional'),
                'warning'
            );
        }

        // Handle enable all cron tasks
        if (isset($_POST['safequote_nhtsa_enable_crons'])) {
            require_once SAFEQUOTE_THEME_DIR . '/inc/class-nhtsa-init.php';

            SafeQuote_NHTSA_Init::enable_all_crons();
            delete_option('safequote_nhtsa_crons_disabled');

            add_settings_error(
                'safequote_nhtsa',
                'crons_enabled',
                __('All NHTSA cron tasks have been enabled. Automatic data updates will resume.', 'safequote-traditional'),
                'success'
            );
        }
    }

    /**
     * Render admin page
     *
     * @return void
     */
    public static function render_page() {
        require_once SAFEQUOTE_THEME_DIR . '/inc/class-nhtsa-database.php';
        require_once SAFEQUOTE_THEME_DIR . '/inc/class-nhtsa-csv-import.php';
        require_once SAFEQUOTE_THEME_DIR . '/inc/class-nhtsa-validate.php';
        require_once SAFEQUOTE_THEME_DIR . '/inc/class-nhtsa-cache.php';

        $db_stats = self::get_vehicle_cache_stats();
        $csv_stats = SafeQuote_NHTSA_CSV_Import::get_import_stats();
        $cache_stats = SafeQuote_NHTSA_Cache::get_cache_stats();
        $health = SafeQuote_NHTSA_Validate::check_health();
        $year_counts = self::get_vehicle_counts_by_year_and_source();
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('NHTSA Vehicle Safety Data', 'safequote-traditional'); ?></h1>
            <p class="description">
                <?php esc_html_e('Manage NHTSA vehicle safety ratings data and manual CSV import.', 'safequote-traditional'); ?>
            </p>

            <?php settings_errors('safequote_nhtsa'); ?>

            <style>
                /* Collapsible sections styling */
                details {
                    transition: all 0.3s ease;
                }

                details summary {
                    display: list-item;
                    outline: none;
                    transition: all 0.2s ease;
                }

                details summary:hover {
                    background-color: #f9f9f9;
                    border-radius: 4px;
                }

                details summary:focus {
                    outline: 2px solid #0073aa;
                    outline-offset: -2px;
                }

                details summary::marker {
                    color: #0073aa;
                    font-weight: bold;
                }

                details[open] summary {
                    border-bottom: 1px solid #e0e0e0;
                    margin-bottom: 12px;
                }

                /* Code blocks in details */
                details code {
                    background-color: #f5f5f5;
                    padding: 2px 6px;
                    border-radius: 3px;
                    font-family: 'Courier New', monospace;
                    font-size: 13px;
                }

                /* Tables in details */
                details table {
                    margin: 12px 0;
                }

                details table td,
                details table th {
                    vertical-align: middle;
                }

                /* Status indicators */
                details .status-good {
                    color: #28a745;
                    font-weight: bold;
                }

                details .status-warning {
                    color: #ff9800;
                    font-weight: bold;
                }

                details .status-error {
                    color: #d32f2f;
                    font-weight: bold;
                }
            </style>

            <!-- Status Cards -->
            <div class="nhtsa-status-cards" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin: 20px 0;">

                <!-- Overall Health -->
                <div class="postbox">
                    <div class="postbox-header">
                        <h2 class="hndle"><?php esc_html_e('Overall Status', 'safequote-traditional'); ?></h2>
                    </div>
                    <div class="inside">
                        <p>
                            <strong><?php esc_html_e('Health:', 'safequote-traditional'); ?></strong><br/>
                            <span style="
                                display: inline-block;
                                padding: 8px 16px;
                                border-radius: 4px;
                                font-weight: bold;
                                <?php
                                if ($health['status'] === 'healthy') {
                                    echo 'background-color: #d4edda; color: #155724;';
                                } elseif ($health['status'] === 'degraded' || $health['status'] === 'partial') {
                                    echo 'background-color: #fff3cd; color: #856404;';
                                } else {
                                    echo 'background-color: #f8d7da; color: #721c24;';
                                }
                                ?>
                            ">
                                <?php echo esc_html(ucfirst(str_replace('_', ' ', $health['status']))); ?>
                            </span>
                        </p>
                        <hr/>
                        <p>
                            <strong><?php esc_html_e('Coverage:', 'safequote-traditional'); ?></strong><br/>
                            <span style="font-size: 24px; font-weight: bold;"><?php echo esc_html($health['coverage']); ?>%</span>
                        </p>
                        <hr/>
                        <p>
                            <strong><?php esc_html_e('Last Run:', 'safequote-traditional'); ?></strong><br/>
                            <?php echo esc_html($health['last_run']); ?>
                        </p>
                    </div>
                </div>

                <!-- Database Stats -->
                <div class="postbox">
                    <div class="postbox-header">
                        <h2 class="hndle"><?php esc_html_e('Database Statistics', 'safequote-traditional'); ?></h2>
                    </div>
                    <div class="inside">
                        <p>
                            <strong><?php esc_html_e('Total Vehicles:', 'safequote-traditional'); ?></strong><br/>
                            <span style="font-size: 24px; font-weight: bold;"><?php echo esc_html($db_stats['total']); ?></span>
                        </p>
                        <hr/>
                        <table style="width: 100%;">
                            <tr>
                                <td><?php esc_html_e('With Ratings:', 'safequote-traditional'); ?></td>
                                <td style="text-align: right;"><strong style="color: #28a745;"><?php echo esc_html($db_stats['success']); ?></strong></td>
                            </tr>
                            <tr>
                                <td><?php esc_html_e('Without Ratings:', 'safequote-traditional'); ?></td>
                                <td style="text-align: right;"><strong style="color: #856404;"><?php echo esc_html($db_stats['no_data']); ?></strong></td>
                            </tr>
                            <tr style="border-top: 1px solid #ddd; padding-top: 8px;">
                                <td><?php esc_html_e('CSV Source:', 'safequote-traditional'); ?></td>
                                <td style="text-align: right;"><small><?php echo esc_html($db_stats['csv_source']); ?></small></td>
                            </tr>
                            <tr>
                                <td><?php esc_html_e('API Source:', 'safequote-traditional'); ?></td>
                                <td style="text-align: right;"><small><?php echo esc_html($db_stats['api_source']); ?></small></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- CSV Import Stats -->
                <div class="postbox">
                    <div class="postbox-header">
                        <h2 class="hndle"><?php esc_html_e('CSV Import Status', 'safequote-traditional'); ?></h2>
                    </div>
                    <div class="inside">
                        <p>
                            <strong><?php esc_html_e('Imported Vehicles:', 'safequote-traditional'); ?></strong><br/>
                            <span style="font-size: 24px; font-weight: bold;"><?php echo esc_html($csv_stats['total_imported']); ?></span>
                        </p>
                        <hr/>
                        <p>
                            <strong><?php esc_html_e('Valid Entries:', 'safequote-traditional'); ?></strong><br/>
                            <?php echo esc_html($csv_stats['valid_entries']); ?>
                        </p>
                        <hr/>
                        <p>
                            <strong><?php esc_html_e('Last Import:', 'safequote-traditional'); ?></strong><br/>
                            <small><?php echo esc_html($csv_stats['last_import']); ?></small>
                        </p>
                    </div>
                </div>

                <!-- CSV Import Errors -->
                <?php
                $last_error = get_option('safequote_nhtsa_csv_last_error', null);
                if (!empty($last_error)):
                ?>
                <div class="postbox" style="border-left: 4px solid #dc3545;">
                    <div class="postbox-header">
                        <h2 class="hndle" style="color: #dc3545;">
                            <?php esc_html_e('⚠️ Last Import Error', 'safequote-traditional'); ?>
                        </h2>
                    </div>
                    <div class="inside">
                        <p>
                            <strong><?php esc_html_e('Time:', 'safequote-traditional'); ?></strong><br/>
                            <small><?php echo esc_html($last_error['timestamp']); ?></small>
                        </p>
                        <hr/>
                        <p>
                            <strong><?php esc_html_e('Reason:', 'safequote-traditional'); ?></strong><br/>
                            <code style="background: #f5f5f5; padding: 8px; display: block; border-radius: 3px;">
                                <?php echo esc_html($last_error['reason']); ?>
                            </code>
                        </p>
                        <?php if (!empty($last_error['error'])): ?>
                        <hr/>
                        <p>
                            <strong><?php esc_html_e('Details:', 'safequote-traditional'); ?></strong><br/>
                            <small style="color: #666; display: block; word-break: break-word;">
                                <?php echo esc_html($last_error['error']); ?>
                            </small>
                        </p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Vehicles by Year & Source -->
                <div class="postbox">
                    <div class="postbox-header">
                        <h2 class="hndle"><?php esc_html_e('Vehicles by Year & Source', 'safequote-traditional'); ?></h2>
                    </div>
                    <div class="inside">
                        <?php if (empty($year_counts)): ?>
                            <p style="color: #666; font-style: italic;">
                                <?php esc_html_e('No vehicles in database yet.', 'safequote-traditional'); ?>
                            </p>
                        <?php else: ?>
                            <div id="nhtsa-year-table"></div>

                            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 15px; padding-top: 15px; border-top: 1px solid #ddd;">
                                <button id="nhtsa-prev-btn" onclick="prevYearPage()" class="button" style="padding: 6px 12px;">
                                    &larr; <?php esc_html_e('Previous', 'safequote-traditional'); ?>
                                </button>
                                <span id="nhtsa-page-info" style="font-weight: bold; color: #0073aa;"></span>
                                <button id="nhtsa-next-btn" onclick="nextYearPage()" class="button" style="padding: 6px 12px;">
                                    <?php esc_html_e('Next', 'safequote-traditional'); ?> &rarr;
                                </button>
                            </div>

                            <script>
                            (function() {
                                const yearData = <?php echo json_encode($year_counts); ?>;
                                const yearsPerPage = 5;
                                let currentPage = 1;
                                const totalPages = Math.ceil(Object.keys(yearData).length / yearsPerPage);

                                window.renderYearTable = function() {
                                    const years = Object.keys(yearData).sort((a, b) => parseInt(b) - parseInt(a));
                                    const startIdx = (currentPage - 1) * yearsPerPage;
                                    const endIdx = startIdx + yearsPerPage;
                                    const pageYears = years.slice(startIdx, endIdx);

                                    let html = '<table style="width: 100%; border-collapse: collapse;">';
                                    html += '<thead><tr style="background-color: #f5f5f5; border-bottom: 2px solid #ddd;">';
                                    html += '<th style="padding: 8px; text-align: left; font-weight: bold;">Year</th>';
                                    html += '<th style="padding: 8px; text-align: center; font-weight: bold;">Total</th>';
                                    html += '<th style="padding: 8px; text-align: center; font-weight: bold;">CSV</th>';
                                    html += '<th style="padding: 8px; text-align: center; font-weight: bold;">API</th>';
                                    html += '<th style="padding: 8px; text-align: center; font-weight: bold;">Manual</th>';
                                    html += '</tr></thead><tbody>';

                                    pageYears.forEach(year => {
                                        const counts = yearData[year];
                                        html += '<tr style="border-bottom: 1px solid #eee;">';
                                        html += '<td style="padding: 10px; font-weight: bold;">' + year + '</td>';
                                        html += '<td style="padding: 10px; text-align: center; background-color: #f0f7ff;"><strong>' + counts.total + '</strong></td>';
                                        html += '<td style="padding: 10px; text-align: center;"><span style="display: inline-block; background: #e8f4f8; padding: 4px 8px; border-radius: 3px; font-size: 12px;">' + counts.csv + '</span></td>';
                                        html += '<td style="padding: 10px; text-align: center;"><span style="display: inline-block; background: #f0e8ff; padding: 4px 8px; border-radius: 3px; font-size: 12px;">' + counts.api + '</span></td>';
                                        html += '<td style="padding: 10px; text-align: center;"><span style="display: inline-block; background: #fff0e8; padding: 4px 8px; border-radius: 3px; font-size: 12px;">' + counts.manual + '</span></td>';
                                        html += '</tr>';
                                    });

                                    html += '</tbody></table>';
                                    document.getElementById('nhtsa-year-table').innerHTML = html;

                                    // Update pagination info and buttons
                                    document.getElementById('nhtsa-page-info').textContent = 'Page ' + currentPage + ' of ' + totalPages;
                                    document.getElementById('nhtsa-prev-btn').disabled = currentPage === 1;
                                    document.getElementById('nhtsa-next-btn').disabled = currentPage === totalPages;
                                };

                                window.prevYearPage = function() {
                                    if (currentPage > 1) {
                                        currentPage--;
                                        renderYearTable();
                                    }
                                };

                                window.nextYearPage = function() {
                                    if (currentPage < totalPages) {
                                        currentPage++;
                                        renderYearTable();
                                    }
                                };

                                // Render immediately - DOM elements are now available
                                renderYearTable();
                            })();
                            </script>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Manual Actions -->
            <div class="postbox" style="margin: 20px 0;">
                <div class="postbox-header">
                    <h2 class="hndle"><?php esc_html_e('Manual Actions', 'safequote-traditional'); ?></h2>
                </div>
                <div class="inside">
                    <form method="post" action="">
                        <?php wp_nonce_field('safequote_nhtsa_action', 'safequote_nhtsa_nonce'); ?>

                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;">

                            <!-- Sync CSV -->
                            <div style="padding: 15px; border: 1px solid #ddd; border-radius: 4px;">
                                <h3 style="margin-top: 0;"><?php esc_html_e('Check & Sync CSV', 'safequote-traditional'); ?></h3>
                                <p class="description">
                                    <?php esc_html_e('Check NHTSA server for updates and download if CSV has changed.', 'safequote-traditional'); ?>
                                </p>
                                <button type="submit" name="safequote_nhtsa_sync" class="button button-primary">
                                    <?php esc_html_e('Sync Now', 'safequote-traditional'); ?>
                                </button>
                                <button type="submit" name="safequote_nhtsa_force_reimport" class="button button-secondary" style="margin-left: 10px;">
                                    <?php esc_html_e('Force Reimport (Clear & Reload)', 'safequote-traditional'); ?>
                                </button>
                                <p class="description" style="margin-top: 10px; font-size: 0.9em; color: #666;">
                                    <?php esc_html_e('Force Reimport will delete all existing CSV records and reimport with the latest format. Use this if you need to update the data structure.', 'safequote-traditional'); ?>
                                </p>
                            </div>

                            <!-- Batch Fill Missing Ratings -->
                            <div style="padding: 15px; border: 1px solid #ddd; border-radius: 4px;">
                                <h3 style="margin-top: 0;"><?php esc_html_e('Fill Missing Ratings', 'safequote-traditional'); ?></h3>
                                <p class="description">
                                    <?php esc_html_e('Query NHTSA API for vehicles without ratings (processes 50 at a time, resumable). Also runs automatically every 3 minutes.', 'safequote-traditional'); ?>
                                </p>
                                <button type="submit" name="safequote_nhtsa_batch_fill" class="button button-primary">
                                    <?php esc_html_e('Start Batch Fill', 'safequote-traditional'); ?>
                                </button>
                                <?php
                                $batch_session = get_option('safequote_nhtsa_batch_session');
                                if ($batch_session) :
                                ?>
                                    <button type="submit" name="safequote_nhtsa_batch_continue" class="button button-success" style="margin-left: 10px;">
                                        <?php esc_html_e('Continue Batch →', 'safequote-traditional'); ?>
                                    </button>
                                    <p style="color: #28a745; font-size: 12px; margin-top: 8px;">
                                        <?php esc_html_e('Batch in progress. Click Continue to process next 50 vehicles.', 'safequote-traditional'); ?>
                                    </p>
                                <?php endif; ?>

                                <!-- Auto Batch Status -->
                                <div style="margin-top: 12px; padding: 10px; background: #e7f3ff; border-left: 4px solid #2271b1; border-radius: 2px;">
                                    <p style="margin: 0; font-size: 12px;">
                                        <strong style="color: #2271b1;">🔄 Auto Batch Running:</strong> Every 3 minutes
                                    </p>
                                    <?php
                                    // Calculate estimated time
                                    $remaining_count = $db_stats['no_data'];
                                    if ($remaining_count > 0) {
                                        $sessions_needed = ceil($remaining_count / 50);
                                        $minutes_estimate = $sessions_needed * 3;
                                        $hours_estimate = ceil($minutes_estimate / 60);
                                    ?>
                                        <p style="margin: 5px 0 0 0; font-size: 12px; color: #666;">
                                            Estimated time to complete: <strong><?php echo esc_html($hours_estimate); ?> hour<?php echo $hours_estimate !== 1 ? 's' : ''; ?></strong>
                                            (<?php echo esc_html($sessions_needed); ?> sessions × 3 min)
                                        </p>
                                    <?php } else { ?>
                                        <p style="margin: 5px 0 0 0; font-size: 12px; color: #666;">
                                            ✓ All vehicles have ratings
                                        </p>
                                    <?php } ?>
                                </div>
                            </div>

                            <!-- Initialize/Repair Database -->
                            <div style="padding: 15px; border: 1px solid #ddd; border-radius: 4px;">
                                <h3 style="margin-top: 0;"><?php esc_html_e('Initialize Database', 'safequote-traditional'); ?></h3>
                                <p class="description">
                                    <?php esc_html_e('Create or repair NHTSA database tables. This is called automatically when the theme is activated.', 'safequote-traditional'); ?>
                                </p>
                                <button type="submit" name="safequote_nhtsa_init_db" class="button button-secondary">
                                    <?php esc_html_e('Initialize/Repair Database', 'safequote-traditional'); ?>
                                </button>
                            </div>

                            <!-- Fill Missing Ratings by Year -->
                            <div style="padding: 15px; border: 1px solid #ddd; border-radius: 4px;">
                                <h3 style="margin-top: 0;"><?php esc_html_e('Fill Missing Ratings by Year', 'safequote-traditional'); ?></h3>
                                <p class="description">
                                    <?php esc_html_e('Target specific model years to fill missing ratings. Recommended: Start with 2024-2025 (most likely to have NHTSA data).', 'safequote-traditional'); ?>
                                </p>
                                <div style="margin-bottom: 12px;">
                                    <label style="display: block; margin-bottom: 8px; font-weight: bold;">
                                        <?php esc_html_e('Select Year:', 'safequote-traditional'); ?>
                                    </label>
                                    <select name="safequote_nhtsa_target_year" style="padding: 8px; border: 1px solid #ddd; border-radius: 4px; width: 150px;">
                                        <option value="">-- Select Year --</option>
                                        <?php for ($y = date('Y') + 1; $y >= 2015; $y--) : ?>
                                            <option value="<?php echo esc_attr($y); ?>">
                                                <?php echo esc_html($y);
                                                if ($y == 2025 || $y == 2024) {
                                                    echo ' (Recommended)';
                                                }
                                                ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <button type="submit" name="safequote_nhtsa_year_batch_fill" class="button button-primary">
                                    <?php esc_html_e('Start Year Batch', 'safequote-traditional'); ?>
                                </button>
                                <?php
                                // Check for any active year batch sessions
                                $active_year_batch = null;
                                for ($y = 2015; $y <= 2026; $y++) {
                                    if (get_option("safequote_nhtsa_year_batch_session_{$y}")) {
                                        $active_year_batch = $y;
                                        break;
                                    }
                                }
                                if ($active_year_batch) :
                                ?>
                                    <button type="submit" name="safequote_nhtsa_year_batch_continue" class="button button-success" style="margin-left: 10px;">
                                        <?php echo esc_html(sprintf(__('Continue Year Batch (%d) →', 'safequote-traditional'), $active_year_batch)); ?>
                                    </button>
                                    <p style="color: #28a745; font-size: 12px; margin-top: 8px;">
                                        <?php echo esc_html(sprintf(__('Year %d batch in progress. Click Continue to process next 50 vehicles.', 'safequote-traditional'), $active_year_batch)); ?>
                                    </p>
                                <?php endif; ?>
                                <div style="margin-top: 12px; padding: 10px; background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 2px;">
                                    <p style="margin: 0; font-size: 12px;">
                                        <strong style="color: #856404;">💡 Tip:</strong> 2026 models won't have NHTSA data. Start with 2024-2025 for best results.
                                    </p>
                                </div>
                            </div>

                            <!-- Cron Task Management -->
                            <div style="padding: 15px; border: 1px solid #ddd; border-radius: 4px;">
                                <h3 style="margin-top: 0;"><?php esc_html_e('Cron Task Management', 'safequote-traditional'); ?></h3>
                                <?php
                                $crons_disabled = get_option('safequote_nhtsa_crons_disabled', false);
                                $cron_status = $crons_disabled ? 'Manually Disabled' : 'Enabled';
                                $status_color = $crons_disabled ? '#dc3545' : '#28a745';
                                $status_icon = $crons_disabled ? '❌' : '✓';
                                ?>
                                <p style="margin-bottom: 12px;">
                                    <strong><?php esc_html_e('Status:', 'safequote-traditional'); ?></strong>
                                    <span style="display: inline-block; padding: 4px 12px; border-radius: 4px; font-weight: bold; background-color: <?php echo esc_attr($crons_disabled ? '#f8d7da' : '#d4edda'); ?>; color: <?php echo esc_attr($status_color); ?>;">
                                        <?php echo esc_html("{$status_icon} {$cron_status}"); ?>
                                    </span>
                                </p>
                                <p class="description">
                                    <?php esc_html_e('Manage automated NHTSA background tasks. Disable to pause all automatic updates, enable to resume.', 'safequote-traditional'); ?>
                                </p>
                                <p style="font-size: 12px; color: #666; margin-bottom: 12px;">
                                    <strong><?php esc_html_e('Active Cron Jobs:', 'safequote-traditional'); ?></strong>
                                </p>
                                <ul style="margin: 0 0 12px 20px; font-size: 12px; color: #666;">
                                    <li><?php esc_html_e('CSV Sync (daily 2 AM)', 'safequote-traditional'); ?></li>
                                    <li><?php esc_html_e('Auto Batch Fill (every 3 minutes)', 'safequote-traditional'); ?></li>
                                    <li><?php esc_html_e('Validation (daily 3 AM)', 'safequote-traditional'); ?></li>
                                    <li><?php esc_html_e('Cleanup (daily 4 AM)', 'safequote-traditional'); ?></li>
                                </ul>
                                <?php if (!$crons_disabled) : ?>
                                    <button type="submit" name="safequote_nhtsa_disable_crons" class="button" style="background-color: #dc3545; color: white; border-color: #c82333;">
                                        <?php esc_html_e('🛑 Disable All Crons', 'safequote-traditional'); ?>
                                    </button>
                                <?php else : ?>
                                    <button type="submit" name="safequote_nhtsa_enable_crons" class="button button-success">
                                        <?php esc_html_e('✓ Enable All Crons', 'safequote-traditional'); ?>
                                    </button>
                                    <p style="color: #dc3545; font-size: 12px; margin-top: 8px;">
                                        <strong><?php esc_html_e('⚠️ Crons are currently disabled.', 'safequote-traditional'); ?></strong> <?php esc_html_e('Automatic data updates are not running.', 'safequote-traditional'); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Batch Fill Logs -->
            <?php self::render_batch_fill_logs(); ?>

            <!-- Database Query Table -->
            <div class="postbox" style="margin: 20px 0;">
                <div class="postbox-header">
                    <h2 class="hndle"><?php esc_html_e('Sample Vehicle Data', 'safequote-traditional'); ?></h2>
                </div>
                <div class="inside">
                    <?php self::render_sample_data(); ?>
                </div>
            </div>

            <!-- Information -->
            <div class="postbox" style="margin: 20px 0;">
                <div class="postbox-header">
                    <h2 class="hndle"><?php esc_html_e('Information & System Status', 'safequote-traditional'); ?></h2>
                </div>
                <div class="inside">
                    <!-- System Status -->
                    <details style="margin-bottom: 20px; padding: 12px; border: 1px solid #e0e0e0; border-radius: 4px; cursor: pointer;">
                        <summary style="font-weight: bold; color: #2c3338; user-select: none;">
                            <?php esc_html_e('💻 System Status', 'safequote-traditional'); ?>
                        </summary>
                        <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid #e0e0e0;">
                            <?php
                            $system_info = self::get_system_info();
                            ?>
                            <table style="width: 100%; border-collapse: collapse;">
                                <tr style="border-bottom: 1px solid #e0e0e0;">
                                    <td style="padding: 8px; font-weight: bold; width: 40%;"><?php esc_html_e('WordPress Version:', 'safequote-traditional'); ?></td>
                                    <td style="padding: 8px;"><?php echo esc_html($system_info['wordpress_version']); ?></td>
                                </tr>
                                <tr style="border-bottom: 1px solid #e0e0e0;">
                                    <td style="padding: 8px; font-weight: bold;"><?php esc_html_e('PHP Version:', 'safequote-traditional'); ?></td>
                                    <td style="padding: 8px;"><?php echo esc_html($system_info['php_version']); ?></td>
                                </tr>
                                <tr style="border-bottom: 1px solid #e0e0e0;">
                                    <td style="padding: 8px; font-weight: bold;"><?php esc_html_e('Theme Version:', 'safequote-traditional'); ?></td>
                                    <td style="padding: 8px;"><?php echo esc_html($system_info['theme_version']); ?></td>
                                </tr>
                                <tr style="border-bottom: 1px solid #e0e0e0;">
                                    <td style="padding: 8px; font-weight: bold;"><?php esc_html_e('WP-Cron Status:', 'safequote-traditional'); ?></td>
                                    <td style="padding: 8px;">
                                        <?php
                                        if ($system_info['wp_cron_enabled']) {
                                            echo '<span style="color: #28a745; font-weight: bold;">✓ Enabled</span>';
                                        } else {
                                            echo '<span style="color: #d32f2f; font-weight: bold;">✗ Disabled (WP_DISABLE_CRON)</span>';
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <tr style="border-bottom: 1px solid #e0e0e0;">
                                    <td style="padding: 8px; font-weight: bold;"><?php esc_html_e('Memory Limit:', 'safequote-traditional'); ?></td>
                                    <td style="padding: 8px;"><?php echo esc_html($system_info['memory_limit']); ?></td>
                                </tr>
                                <tr style="border-bottom: 1px solid #e0e0e0;">
                                    <td style="padding: 8px; font-weight: bold;"><?php esc_html_e('Max Execution Time:', 'safequote-traditional'); ?></td>
                                    <td style="padding: 8px;"><?php echo esc_html($system_info['max_execution_time']); ?>s</td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px; font-weight: bold;"><?php esc_html_e('Database Initialized:', 'safequote-traditional'); ?></td>
                                    <td style="padding: 8px;">
                                        <?php
                                        if ($system_info['db_initialized']) {
                                            echo '<span style="color: #28a745;">✓ ' . esc_html($system_info['db_initialized']) . '</span>';
                                        } else {
                                            echo '<span style="color: #d32f2f;">✗ Not initialized</span>';
                                        }
                                        ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </details>

                    <!-- Next Scheduled Runs -->
                    <details style="margin-bottom: 20px; padding: 12px; border: 1px solid #e0e0e0; border-radius: 4px; cursor: pointer;">
                        <summary style="font-weight: bold; color: #2c3338; user-select: none;">
                            <?php esc_html_e('⏱️ Next Scheduled Runs', 'safequote-traditional'); ?>
                        </summary>
                        <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid #e0e0e0;">
                            <?php
                            $cron_runs = self::get_next_cron_runs();
                            ?>
                            <table style="width: 100%; border-collapse: collapse;">
                                <?php foreach ($cron_runs as $cron_key => $cron_info) : ?>
                                    <tr style="border-bottom: 1px solid #e0e0e0;">
                                        <td style="padding: 8px; font-weight: bold; width: 40%;"><?php echo esc_html($cron_info['label']); ?>:</td>
                                        <td style="padding: 8px;">
                                            <?php echo esc_html(self::format_next_run($cron_info['timestamp'])); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </table>
                        </div>
                    </details>

                    <!-- Multi-Tier Cache System -->
                    <details style="margin-bottom: 20px; padding: 12px; border: 1px solid #e0e0e0; border-radius: 4px; cursor: pointer;">
                        <summary style="font-weight: bold; color: #2c3338; user-select: none;">
                            <?php esc_html_e('📦 Multi-Tier Cache System', 'safequote-traditional'); ?>
                        </summary>
                        <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid #e0e0e0;">
                            <p style="margin: 0 0 12px 0; color: #555;">
                                <?php esc_html_e('SafeQuote uses a 3-layer caching strategy for optimal performance and reliability:', 'safequote-traditional'); ?>
                            </p>

                            <table style="width: 100%; border-collapse: collapse; margin-bottom: 12px;">
                                <tr style="background: #f5f5f5; border: 1px solid #ddd;">
                                    <th style="padding: 10px; text-align: left; font-weight: bold; border: 1px solid #ddd;">Layer</th>
                                    <th style="padding: 10px; text-align: left; font-weight: bold; border: 1px solid #ddd;">Storage</th>
                                    <th style="padding: 10px; text-align: left; font-weight: bold; border: 1px solid #ddd;">Duration</th>
                                    <th style="padding: 10px; text-align: left; font-weight: bold; border: 1px solid #ddd;">Speed</th>
                                </tr>
                                <tr style="border: 1px solid #ddd;">
                                    <td style="padding: 10px; border: 1px solid #ddd; font-weight: bold; color: #28a745;">L1: Transient</td>
                                    <td style="padding: 10px; border: 1px solid #ddd;"><code>wp_options</code></td>
                                    <td style="padding: 10px; border: 1px solid #ddd;">24 hours (expires)</td>
                                    <td style="padding: 10px; border: 1px solid #ddd;">Fastest ⚡</td>
                                </tr>
                                <tr style="border: 1px solid #ddd;">
                                    <td style="padding: 10px; border: 1px solid #ddd; font-weight: bold; color: #28a745;">L2: Database</td>
                                    <td style="padding: 10px; border: 1px solid #ddd;"><code>wp_nhtsa_vehicle_cache</code></td>
                                    <td style="padding: 10px; border: 1px solid #ddd;">♾️ Permanent</td>
                                    <td style="padding: 10px; border: 1px solid #ddd;">Medium ⚙</td>
                                </tr>
                            </table>

                            <p style="margin: 0 0 12px 0; font-size: 12px; color: #28a745; background: #d4edda; padding: 10px; border-left: 4px solid #28a745; border-radius: 3px;">
                                <strong>✓ Permanent Storage:</strong> All vehicle data is stored permanently in the database and NEVER deleted. Only updated with new information.
                            </p>

                            <h4 style="margin: 12px 0 8px 0; color: #2c3338;"><?php esc_html_e('Data Update Triggers:', 'safequote-traditional'); ?></h4>
                            <ul style="margin: 0 0 12px 20px;">
                                <li style="margin-bottom: 6px;"><strong><?php esc_html_e('L1 Transient Expires:', 'safequote-traditional'); ?></strong> After 24 hours → falls back to L2 (temporary layer only)</li>
                                <li style="margin-bottom: 6px;"><strong><?php esc_html_e('L2 Database Updates:', 'safequote-traditional'); ?></strong> Only when new data is available; data NEVER expires or deletes</li>
                                <li style="margin-bottom: 6px;"><strong><?php esc_html_e('CSV Sync (Daily 2 AM):', 'safequote-traditional'); ?></strong> Checks NHTSA server → updates database if changes detected</li>
                                <li style="margin-bottom: 6px;"><strong><?php esc_html_e('Auto Batch (Every 3 min):', 'safequote-traditional'); ?></strong> Fills vehicles with missing ratings from API</li>
                                <li><strong><?php esc_html_e('Manual Updates:', 'safequote-traditional'); ?></strong> Use buttons above to manually trigger sync or batch fill</li>
                            </ul>

                            <h4 style="margin: 12px 0 8px 0; color: #2c3338;"><?php esc_html_e('Data Source Priority:', 'safequote-traditional'); ?></h4>
                            <ul style="margin: 0 0 0 20px;">
                                <li style="margin-bottom: 6px;"><strong><?php esc_html_e('API Data Protected:', 'safequote-traditional'); ?></strong> Once filled by API, CSV imports won\'t overwrite it (permanent priority)</li>
                                <li style="margin-bottom: 6px;"><strong><?php esc_html_e('CSV Fallback:', 'safequote-traditional'); ?></strong> Used when API unavailable or provides incomplete data</li>
                                <li><strong><?php esc_html_e('Result:', 'safequote-traditional'); ?></strong> Best available data always served; nothing ever deleted</li>
                            </ul>
                        </div>
                    </details>

                    <!-- Data Sources -->
                    <details style="margin-bottom: 20px; padding: 12px; border: 1px solid #e0e0e0; border-radius: 4px; cursor: pointer;">
                        <summary style="font-weight: bold; color: #2c3338; user-select: none;">
                            <?php esc_html_e('🔗 Data Sources', 'safequote-traditional'); ?>
                        </summary>
                        <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid #e0e0e0;">
                            <h4 style="margin: 0 0 8px 0; color: #2c3338;"><?php esc_html_e('CSV Source', 'safequote-traditional'); ?></h4>
                            <p style="margin: 0 0 12px 0; color: #666;">
                                <?php esc_html_e('Official NHTSA vehicle safety ratings data:', 'safequote-traditional'); ?><br/>
                                <code style="background: #f5f5f5; padding: 4px 8px; border-radius: 3px; font-size: 12px;">
                                    https://static.nhtsa.gov/nhtsa/downloads/Safercar/Safercar_data.csv
                                </code>
                            </p>

                            <h4 style="margin: 0 0 8px 0; color: #2c3338;"><?php esc_html_e('API Source', 'safequote-traditional'); ?></h4>
                            <p style="margin: 0 0 12px 0; color: #666;">
                                <?php esc_html_e('Real-time safety ratings for incomplete vehicles:', 'safequote-traditional'); ?><br/>
                                <code style="background: #f5f5f5; padding: 4px 8px; border-radius: 3px; font-size: 12px;">
                                    https://api.nhtsa.gov/SafetyRatings/modelyear/{year}/make/{make}/model/{model}
                                </code>
                            </p>

                            <h4 style="margin: 0 0 8px 0; color: #2c3338;"><?php esc_html_e('Database Tables', 'safequote-traditional'); ?></h4>
                            <ul style="margin: 0 0 0 20px;">
                                <li><code>wp_nhtsa_vehicle_cache</code> - <?php esc_html_e('Persistent vehicle ratings with source tracking', 'safequote-traditional'); ?></li>
                                <li><code>wp_options</code> - <?php esc_html_e('Transient cache, batch sessions, error logs', 'safequote-traditional'); ?></li>
                            </ul>
                        </div>
                    </details>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Batch fill missing ratings from NHTSA API with resumable sessions
     *
     * @param bool $start_new Whether to start a new batch or resume existing.
     * @return array Result with processed, updated counts, status, and logs.
     */
    private static function batch_fill_missing_ratings($start_new = true) {
        global $wpdb;

        require_once SAFEQUOTE_THEME_DIR . '/inc/class-nhtsa-cache.php';
        require_once SAFEQUOTE_THEME_DIR . '/inc/class-nhtsa-database.php';

        $table = $wpdb->prefix . 'nhtsa_vehicle_cache';
        $batch_size = 50; // Process 50 vehicles per session
        $session = get_option('safequote_nhtsa_batch_session', array());
        $batch_log = get_option('safequote_nhtsa_batch_fill_log', array(
            'logs' => array(),
            'total_processed' => 0,
            'total_updated' => 0,
            'total_errors' => 0,
        ));

        // Initialize new batch if requested
        if ($start_new) {
            $session = array(
                'batch_id' => uniqid('batch_'),
                'started_at' => current_time('mysql'),
                'processed_ids' => array(),
            );
            $batch_log = array(
                'logs' => array('Starting new batch session...'),
                'total_processed' => 0,
                'total_updated' => 0,
                'total_errors' => 0,
            );
        }

        if (empty($session)) {
            return array(
                'success' => false,
                'reason' => 'No active batch session. Start a new batch first.',
            );
        }

        $processed_ids = isset($session['processed_ids']) ? $session['processed_ids'] : array();
        $logs = isset($batch_log['logs']) ? $batch_log['logs'] : array();
        $total_processed = isset($batch_log['total_processed']) ? $batch_log['total_processed'] : 0;
        $total_updated = isset($batch_log['total_updated']) ? $batch_log['total_updated'] : 0;
        $total_errors = isset($batch_log['total_errors']) ? $batch_log['total_errors'] : 0;

        // Get total vehicles without ratings or images
        $total_remaining = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE nhtsa_overall_rating IS NULL OR vehicle_picture IS NULL");

        if ($total_remaining === 0) {
            $logs[] = '';
            $logs[] = '========== NO MORE VEHICLES ==========';
            $logs[] = sprintf('Batch complete! Total processed: %d', $total_processed);
            $logs[] = sprintf('Total updated: %d', $total_updated);
            $logs[] = sprintf('Total errors/no-data: %d', $total_errors);
            $logs[] = sprintf('Success rate: %d%%', $total_processed > 0 ? round(($total_updated / $total_processed) * 100) : 0);

            error_log("[NHTSA Batch] All vehicles processed!");

            update_option('safequote_nhtsa_batch_fill_log', array(
                'status' => 'complete',
                'total_processed' => $total_processed,
                'total_updated' => $total_updated,
                'total_errors' => $total_errors,
                'logs' => $logs,
                'timestamp' => current_time('mysql'),
            ));

            delete_option('safequote_nhtsa_batch_session');

            return array(
                'success' => true,
                'status' => 'complete',
                'processed' => $total_processed,
                'updated' => $total_updated,
                'total' => $total_processed,
            );
        }

        // Get next batch of vehicles (excluding already-processed IDs)
        // Order by year DESC to process newest vehicles first (2025, 2024, 2023...)
        $exclude_ids = !empty($processed_ids) ? implode(',', $processed_ids) : '0';
        $vehicles = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT id, year, make, model FROM $table WHERE (nhtsa_overall_rating IS NULL OR vehicle_picture IS NULL) AND id NOT IN ($exclude_ids) ORDER BY year DESC, make ASC, model ASC LIMIT %d",
                $batch_size
            )
        );

        if (empty($vehicles)) {
            $logs[] = 'No more vehicles to process.';
            update_option('safequote_nhtsa_batch_fill_log', array(
                'status' => 'complete',
                'total_processed' => $total_processed,
                'total_updated' => $total_updated,
                'total_errors' => $total_errors,
                'logs' => $logs,
                'timestamp' => current_time('mysql'),
            ));
            delete_option('safequote_nhtsa_batch_session');

            return array(
                'success' => true,
                'status' => 'complete',
                'processed' => $total_processed,
                'updated' => $total_updated,
                'total' => $total_processed,
            );
        }

        // Process this batch
        $batch_processed = 0;
        $batch_updated = 0;
        $batch_errors = 0;

        $logs[] = sprintf('Processing batch with %d vehicles...', count($vehicles));
        error_log("[NHTSA Batch] Processing session with " . count($vehicles) . " vehicles");

        try {
            foreach ($vehicles as $index => $vehicle) {
                $batch_processed++;
                $total_processed++;
                $processed_ids[] = $vehicle->id;

                $vehicle_label = "{$vehicle->year} {$vehicle->make} {$vehicle->model}";
                $progress = sprintf('[%d/%d]', $index + 1, count($vehicles));

                // Fetch from API
                $api_data = SafeQuote_NHTSA_Cache::fetch_from_api(
                    $vehicle->year,
                    $vehicle->make,
                    $vehicle->model
                );

                if (!$api_data) {
                    $logs[] = "{$progress} ✗ No data from API for {$vehicle_label}";
                    $batch_errors++;
                    $total_errors++;
                    error_log("[NHTSA Batch] {$progress} No API data for {$vehicle_label}");
                } else {
                    // Store API data regardless of whether it has ratings (even "Not Rated" is useful)
                    $rating = $api_data['OverallRating'] ?? 'Not Rated';
                    $result = SafeQuote_NHTSA_Database::update_vehicle_cache(
                        $vehicle->year,
                        $vehicle->make,
                        $vehicle->model,
                        $api_data,
                        null, // Permanent storage - never expires or deletes
                        'api'
                    );

                    if ($result) {
                        $batch_updated++;
                        $total_updated++;
                        $logs[] = "{$progress} ✓ Stored {$vehicle_label} - Rating: {$rating}";
                        error_log("[NHTSA Batch] {$progress} ✓ Stored {$vehicle_label} - Rating: {$rating}");

                        // Clear transient
                        $cache_key = "nhtsa_rating_{$vehicle->year}_{$vehicle->make}_{$vehicle->model}";
                        delete_transient($cache_key);
                    } else {
                        $logs[] = "{$progress} ✗ Database insert failed for {$vehicle_label}";
                        $batch_errors++;
                        $total_errors++;
                        error_log("[NHTSA Batch] {$progress} DB error for {$vehicle_label}");
                    }
                }

                // Rate limit: 1 request per second
                usleep(1000000);
            }

            // Determine if batch is complete or in-progress
            $remaining = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE (nhtsa_overall_rating IS NULL OR vehicle_picture IS NULL) AND id NOT IN ({$exclude_ids})");
            $batch_status = $remaining > 0 ? 'in_progress' : 'complete';

            $logs[] = '';
            $logs[] = sprintf('========== SESSION COMPLETE ==========');
            $logs[] = sprintf('This session - Processed: %d, Updated: %d, Errors: %d', $batch_processed, $batch_updated, $batch_errors);
            $logs[] = sprintf('Total so far - Processed: %d, Updated: %d, Errors: %d', $total_processed, $total_updated, $total_errors);
            $logs[] = sprintf('Remaining vehicles: %d', $remaining);

            error_log("[NHTSA Batch] Session complete: {$batch_processed} processed, {$batch_updated} updated, {$remaining} remaining");

            // Save session state
            $session['processed_ids'] = $processed_ids;
            update_option('safequote_nhtsa_batch_session', $session);

            // Save batch log
            update_option('safequote_nhtsa_batch_fill_log', array(
                'status' => $batch_status,
                'total_processed' => $total_processed,
                'total_updated' => $total_updated,
                'total_errors' => $total_errors,
                'logs' => $logs,
                'timestamp' => current_time('mysql'),
            ));

            // If all vehicles processed, clean up session
            if ($batch_status === 'complete') {
                delete_option('safequote_nhtsa_batch_session');
            }

            return array(
                'success' => true,
                'status' => $batch_status,
                'processed' => $total_processed,
                'updated' => $total_updated,
                'total' => $total_remaining,
            );

        } catch (Exception $e) {
            $error_msg = $e->getMessage();
            $logs[] = 'ERROR: ' . $error_msg;

            error_log("[NHTSA Batch] Exception: {$error_msg}");

            update_option('safequote_nhtsa_batch_fill_log', array(
                'status' => 'error',
                'total_processed' => $total_processed,
                'total_updated' => $total_updated,
                'total_errors' => $total_errors,
                'logs' => $logs,
                'timestamp' => current_time('mysql'),
            ));

            // Keep session for resume
            $session['processed_ids'] = $processed_ids;
            update_option('safequote_nhtsa_batch_session', $session);

            return array(
                'success' => false,
                'processed' => $total_processed,
                'updated' => $total_updated,
                'reason' => $error_msg,
            );
        }
    }

    /**
     * Batch fill missing ratings from NHTSA API for specific year
     *
     * @param int  $year      Vehicle model year to process.
     * @param bool $start_new Whether to start a new batch or resume existing.
     * @return array Result with processed, updated counts, status, and logs.
     */
    private static function batch_fill_missing_ratings_by_year($year, $start_new = true) {
        global $wpdb;

        require_once SAFEQUOTE_THEME_DIR . '/inc/class-nhtsa-cache.php';
        require_once SAFEQUOTE_THEME_DIR . '/inc/class-nhtsa-database.php';

        $table = $wpdb->prefix . 'nhtsa_vehicle_cache';
        $year = (int) $year;
        $batch_size = 100;
        $session_key = "safequote_nhtsa_year_batch_session_{$year}";
        $log_key = "safequote_nhtsa_year_batch_log_{$year}";

        $session = get_option($session_key, array());
        $batch_log = get_option($log_key, array(
            'logs' => array(),
            'total_processed' => 0,
            'total_updated' => 0,
            'total_errors' => 0,
        ));

        // Initialize new batch if requested
        if ($start_new) {
            $session = array(
                'batch_id' => uniqid('year_batch_'),
                'started_at' => current_time('mysql'),
                'year' => $year,
                'processed_ids' => array(),
            );
            $batch_log = array(
                'logs' => array("Starting new batch session for $year vehicles..."),
                'total_processed' => 0,
                'total_updated' => 0,
                'total_errors' => 0,
            );
        }

        if (empty($session)) {
            return array(
                'success' => false,
                'reason' => 'No active batch session. Start a new batch first.',
            );
        }

        $processed_ids = isset($session['processed_ids']) ? $session['processed_ids'] : array();
        $logs = isset($batch_log['logs']) ? $batch_log['logs'] : array();
        $total_processed = isset($batch_log['total_processed']) ? $batch_log['total_processed'] : 0;
        $total_updated = isset($batch_log['total_updated']) ? $batch_log['total_updated'] : 0;
        $total_errors = isset($batch_log['total_errors']) ? $batch_log['total_errors'] : 0;

        // Get total vehicles for this year without ratings or images
        $total_remaining = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE (nhtsa_overall_rating IS NULL OR vehicle_picture IS NULL) AND year = %d",
            $year
        ));

        if ($total_remaining === 0) {
            $logs[] = '';
            $logs[] = '========== NO MORE VEHICLES FOR THIS YEAR ==========';
            $logs[] = sprintf('Batch complete! Total processed: %d', $total_processed);
            $logs[] = sprintf('Total updated: %d', $total_updated);
            $logs[] = sprintf('Total errors/no-data: %d', $total_errors);
            $logs[] = sprintf('Success rate: %d%%', $total_processed > 0 ? round(($total_updated / $total_processed) * 100) : 0);

            error_log("[NHTSA Year Batch] $year: All vehicles processed!");

            update_option($log_key, array(
                'status' => 'complete',
                'total_processed' => $total_processed,
                'total_updated' => $total_updated,
                'total_errors' => $total_errors,
                'logs' => $logs,
                'timestamp' => current_time('mysql'),
            ));

            delete_option($session_key);

            return array(
                'success' => true,
                'status' => 'complete',
                'processed' => $total_processed,
                'updated' => $total_updated,
                'total' => $total_processed,
            );
        }

        // Get next batch of vehicles (excluding already-processed IDs)
        // Order alphabetically by make and model for consistent processing
        $exclude_ids = !empty($processed_ids) ? implode(',', $processed_ids) : '0';
        $vehicles = $wpdb->get_results($wpdb->prepare(
            "SELECT id, year, make, model FROM $table WHERE (nhtsa_overall_rating IS NULL OR vehicle_picture IS NULL) AND year = %d AND id NOT IN ($exclude_ids) ORDER BY make ASC, model ASC LIMIT %d",
            $year,
            $batch_size
        ));

        if (empty($vehicles)) {
            $logs[] = "No more vehicles to process for year $year.";
            update_option($log_key, array(
                'status' => 'complete',
                'total_processed' => $total_processed,
                'total_updated' => $total_updated,
                'total_errors' => $total_errors,
                'logs' => $logs,
                'timestamp' => current_time('mysql'),
            ));
            delete_option($session_key);

            return array(
                'success' => true,
                'status' => 'complete',
                'processed' => $total_processed,
                'updated' => $total_updated,
                'total' => $total_processed,
            );
        }

        // Process this batch
        $batch_processed = 0;
        $batch_updated = 0;
        $batch_errors = 0;

        $logs[] = sprintf('Processing batch with %d vehicles from %d...', count($vehicles), $year);
        error_log("[NHTSA Year Batch] $year: Processing session with " . count($vehicles) . " vehicles");

        try {
            foreach ($vehicles as $index => $vehicle) {
                $batch_processed++;
                $total_processed++;
                $processed_ids[] = $vehicle->id;

                $vehicle_label = "{$vehicle->year} {$vehicle->make} {$vehicle->model}";
                $progress = sprintf('[%d/%d]', $index + 1, count($vehicles));

                // Fetch from API
                $api_data = SafeQuote_NHTSA_Cache::fetch_from_api(
                    $vehicle->year,
                    $vehicle->make,
                    $vehicle->model
                );

                if (!$api_data) {
                    $logs[] = "{$progress} ✗ No data from API for {$vehicle_label}";
                    $batch_errors++;
                    $total_errors++;
                    error_log("[NHTSA Year Batch] $year: {$progress} No API data for {$vehicle_label}");
                } else {
                    // Store API data regardless of whether it has ratings (even "Not Rated" is useful)
                    $rating = $api_data['OverallRating'] ?? 'Not Rated';
                    $result = SafeQuote_NHTSA_Database::update_vehicle_cache(
                        $vehicle->year,
                        $vehicle->make,
                        $vehicle->model,
                        $api_data,
                        null, // Permanent storage - never expires or deletes
                        'api'
                    );

                    if ($result) {
                        $batch_updated++;
                        $total_updated++;
                        $logs[] = "{$progress} ✓ Stored {$vehicle_label} - Rating: {$rating}";
                        error_log("[NHTSA Year Batch] $year: {$progress} ✓ Stored {$vehicle_label} - Rating: {$rating}");

                        // Clear transient
                        $cache_key = "nhtsa_rating_{$vehicle->year}_{$vehicle->make}_{$vehicle->model}";
                        delete_transient($cache_key);
                    } else {
                        $logs[] = "{$progress} ✗ Database insert failed for {$vehicle_label}";
                        $batch_errors++;
                        $total_errors++;
                        error_log("[NHTSA Year Batch] $year: {$progress} DB error for {$vehicle_label}");
                    }
                }

                // Rate limit: 1 request per second
                usleep(1000000);
            }

            // Determine if batch is complete or in-progress
            $remaining = (int) $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $table WHERE (nhtsa_overall_rating IS NULL OR vehicle_picture IS NULL) AND year = %d AND id NOT IN ($exclude_ids)",
                $year
            ));
            $batch_status = $remaining > 0 ? 'in_progress' : 'complete';

            $logs[] = '';
            $logs[] = sprintf('========== SESSION COMPLETE FOR %d ==========', $year);
            $logs[] = sprintf('This session - Processed: %d, Updated: %d, Errors: %d', $batch_processed, $batch_updated, $batch_errors);
            $logs[] = sprintf('Total so far - Processed: %d, Updated: %d, Errors: %d', $total_processed, $total_updated, $total_errors);
            $logs[] = sprintf('Remaining vehicles for %d: %d', $year, $remaining);

            error_log("[NHTSA Year Batch] $year: Session complete: {$batch_processed} processed, {$batch_updated} updated, {$remaining} remaining");

            // Save session state
            $session['processed_ids'] = $processed_ids;
            update_option($session_key, $session);

            // Save batch log
            update_option($log_key, array(
                'status' => $batch_status,
                'total_processed' => $total_processed,
                'total_updated' => $total_updated,
                'total_errors' => $total_errors,
                'logs' => $logs,
                'timestamp' => current_time('mysql'),
            ));

            // If all vehicles processed, clean up session
            if ($batch_status === 'complete') {
                delete_option($session_key);
            }

            return array(
                'success' => true,
                'status' => $batch_status,
                'processed' => $total_processed,
                'updated' => $total_updated,
                'total' => $total_remaining,
            );

        } catch (Exception $e) {
            $error_msg = $e->getMessage();
            $logs[] = 'ERROR: ' . $error_msg;

            error_log("[NHTSA Year Batch] $year: Exception: {$error_msg}");

            update_option($log_key, array(
                'status' => 'error',
                'total_processed' => $total_processed,
                'total_updated' => $total_updated,
                'total_errors' => $total_errors,
                'logs' => $logs,
                'timestamp' => current_time('mysql'),
            ));

            // Keep session for resume
            $session['processed_ids'] = $processed_ids;
            update_option($session_key, $session);

            return array(
                'success' => false,
                'processed' => $total_processed,
                'updated' => $total_updated,
                'reason' => $error_msg,
            );
        }
    }

    /**
     * Get vehicle cache statistics
     *
     * @return array Vehicle cache stats.
     */
    private static function get_vehicle_cache_stats() {
        global $wpdb;

        $table = $wpdb->prefix . 'nhtsa_vehicle_cache';

        $total = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table");
        $with_ratings = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE nhtsa_overall_rating IS NOT NULL");
        $no_ratings = $total - $with_ratings;
        $csv_source = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE rating_source = 'csv'");
        $api_source = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE rating_source = 'api'");

        return array(
            'total' => $total,
            'success' => $with_ratings,
            'no_data' => $no_ratings,
            'failed' => 0,
            'csv_source' => $csv_source,
            'api_source' => $api_source,
        );
    }

    /**
     * Get vehicle counts by year and source
     *
     * @return array Vehicle counts organized by year and source.
     */
    private static function get_vehicle_counts_by_year_and_source() {
        global $wpdb;

        $table = $wpdb->prefix . 'nhtsa_vehicle_cache';

        // Get all years with vehicle counts
        $results = $wpdb->get_results(
            "SELECT year, rating_source, COUNT(*) as count
             FROM $table
             GROUP BY year, rating_source
             ORDER BY year DESC, rating_source ASC"
        );

        // Organize by year
        $year_data = array();
        foreach ($results as $row) {
            if (!isset($year_data[$row->year])) {
                $year_data[$row->year] = array(
                    'total' => 0,
                    'csv' => 0,
                    'api' => 0,
                    'manual' => 0,
                );
            }
            $year_data[$row->year][$row->rating_source] = (int) $row->count;
            $year_data[$row->year]['total'] += (int) $row->count;
        }

        return $year_data;
    }

    /**
     * Render batch fill logs
     *
     * @return void
     */
    private static function render_batch_fill_logs() {
        $log_data = get_option('safequote_nhtsa_batch_fill_log', null);

        if (!$log_data) {
            return;
        }

        $logs = isset($log_data['logs']) ? $log_data['logs'] : array();
        $status = isset($log_data['status']) ? $log_data['status'] : 'unknown';
        $timestamp = isset($log_data['timestamp']) ? $log_data['timestamp'] : 'N/A';

        // Get session data to show remaining vehicles
        global $wpdb;
        $remaining = (int) $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->prefix . "nhtsa_vehicle_cache WHERE nhtsa_overall_rating IS NULL OR vehicle_picture IS NULL");
        ?>

        <div class="postbox" style="margin: 20px 0;">
            <div class="postbox-header">
                <h2 class="hndle"><?php esc_html_e('Batch Fill Status & Logs', 'safequote-traditional'); ?></h2>
            </div>
            <div class="inside">
                <p>
                    <strong><?php esc_html_e('Status:', 'safequote-traditional'); ?></strong>
                    <span style="
                        display: inline-block;
                        padding: 4px 12px;
                        border-radius: 4px;
                        font-weight: bold;
                        <?php
                        if ($status === 'complete') {
                            echo 'background-color: #d4edda; color: #155724;';
                        } elseif ($status === 'error') {
                            echo 'background-color: #f8d7da; color: #721c24;';
                        } else {
                            echo 'background-color: #fff3cd; color: #856404;';
                        }
                        ?>
                    ">
                        <?php echo esc_html(ucfirst($status)); ?>
                    </span>
                    <br/>
                    <small style="color: #666;"><?php esc_html_e('Last run:', 'safequote-traditional'); ?> <?php echo esc_html($timestamp); ?></small>
                </p>

                <!-- Summary Stats -->
                <?php if (!empty($log_data)) : ?>
                    <div style="background: #f8f9f9; padding: 15px; border-radius: 4px; margin: 15px 0;">
                        <table style="width: 100%;">
                            <tr>
                                <td><?php esc_html_e('Total Processed:', 'safequote-traditional'); ?></td>
                                <td style="text-align: right;"><strong><?php echo esc_html(isset($log_data['total_processed']) ? $log_data['total_processed'] : 0); ?></strong></td>
                            </tr>
                            <tr>
                                <td><?php esc_html_e('Updated with Ratings:', 'safequote-traditional'); ?></td>
                                <td style="text-align: right;"><strong style="color: #28a745;"><?php echo esc_html(isset($log_data['total_updated']) ? $log_data['total_updated'] : 0); ?></strong></td>
                            </tr>
                            <tr>
                                <td><?php esc_html_e('Errors/No Data:', 'safequote-traditional'); ?></td>
                                <td style="text-align: right;"><strong style="color: #dc3545;"><?php echo esc_html(isset($log_data['total_errors']) ? $log_data['total_errors'] : 0); ?></strong></td>
                            </tr>
                            <tr style="border-top: 1px solid #ddd; padding-top: 8px;">
                                <td><?php esc_html_e('Remaining without Ratings:', 'safequote-traditional'); ?></td>
                                <td style="text-align: right;"><strong style="color: #856404;"><?php echo esc_html($remaining); ?></strong></td>
                            </tr>
                            <?php
                            $total_proc = isset($log_data['total_processed']) ? $log_data['total_processed'] : 0;
                            $total_upd = isset($log_data['total_updated']) ? $log_data['total_updated'] : 0;
                            $success_rate = $total_proc > 0 ? round(($total_upd / $total_proc) * 100) : 0;
                            ?>
                            <tr style="border-top: 2px solid #ddd; padding-top: 10px;">
                                <td><strong><?php esc_html_e('Success Rate:', 'safequote-traditional'); ?></strong></td>
                                <td style="text-align: right;"><strong style="font-size: 18px;"><?php echo esc_html($success_rate); ?>%</strong></td>
                            </tr>
                        </table>
                    </div>
                <?php endif; ?>

                <p style="margin-top: 15px; font-size: 12px; color: #666;">
                    <strong><?php esc_html_e('Full logs available in:', 'safequote-traditional'); ?></strong> WordPress error log (search for [NHTSA Batch Fill])
                </p>
            </div>
        </div>

        <?php
    }

    /**
     * Render sample vehicle data from database
     *
     * @return void
     */
    private static function render_sample_data() {
        global $wpdb;

        $table = $wpdb->prefix . 'nhtsa_vehicle_cache';

        // Get database statistics
        $total_vehicles = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table");

        if ($total_vehicles === 0) {
            echo '<p class="description">' . esc_html__('No vehicle data in database yet. Run CSV Import or Batch Fill to populate the database.', 'safequote-traditional') . '</p>';
            return;
        }

        // Data completeness: vehicles with full ratings vs partial vs none
        $complete_ratings = (int) $wpdb->get_var(
            "SELECT COUNT(*) FROM $table
             WHERE nhtsa_overall_rating IS NOT NULL
             AND front_crash IS NOT NULL
             AND side_crash IS NOT NULL
             AND rollover_crash IS NOT NULL"
        );
        $partial_ratings = (int) $wpdb->get_var(
            "SELECT COUNT(*) FROM $table
             WHERE nhtsa_overall_rating IS NOT NULL
             AND (front_crash IS NULL OR side_crash IS NULL OR rollover_crash IS NULL)"
        );
        $no_ratings = (int) $wpdb->get_var(
            "SELECT COUNT(*) FROM $table WHERE nhtsa_overall_rating IS NULL"
        );

        // Data source breakdown
        $csv_source = (int) $wpdb->get_var(
            "SELECT COUNT(*) FROM $table WHERE rating_source = 'csv'"
        );
        $api_source = (int) $wpdb->get_var(
            "SELECT COUNT(*) FROM $table WHERE rating_source = 'api'"
        );
        $manual_source = (int) $wpdb->get_var(
            "SELECT COUNT(*) FROM $table WHERE rating_source = 'manual'"
        );

        // Storage size
        $storage_size = $wpdb->get_var(
            "SELECT SUM(LENGTH(nhtsa_data)) FROM $table"
        );
        $storage_size_formatted = $storage_size ? size_format($storage_size, 2) : '0 B';

        // Year range
        $year_range = $wpdb->get_row(
            "SELECT MIN(year) as min_year, MAX(year) as max_year FROM $table"
        );

        // Most recent update
        $last_update = $wpdb->get_var(
            "SELECT MAX(updated_at) FROM $table"
        );
        $last_update_formatted = $last_update ? wp_date('M d, Y @ H:i', strtotime($last_update)) : __('Never', 'safequote-traditional');

        // Percentage calculations
        $complete_percent = $total_vehicles > 0 ? round(($complete_ratings / $total_vehicles) * 100, 1) : 0;
        $partial_percent = $total_vehicles > 0 ? round(($partial_ratings / $total_vehicles) * 100, 1) : 0;
        $no_rating_percent = $total_vehicles > 0 ? round(($no_ratings / $total_vehicles) * 100, 1) : 0;

        ?>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <!-- Total Vehicles -->
            <div style="padding: 15px; background: #f5f5f5; border-radius: 4px; border-left: 4px solid #0073aa;">
                <div style="font-size: 12px; color: #666; margin-bottom: 5px;"><?php esc_html_e('Total Vehicles Cached', 'safequote-traditional'); ?></div>
                <div style="font-size: 32px; font-weight: bold; color: #0073aa;"><?php echo number_format($total_vehicles); ?></div>
                <div style="font-size: 12px; color: #999; margin-top: 5px;">
                    <?php printf(
                        esc_html__('%d models (%d–%d)', 'safequote-traditional'),
                        count(array_unique($wpdb->get_col("SELECT model FROM $table"))),
                        $year_range->min_year,
                        $year_range->max_year
                    ); ?>
                </div>
            </div>

            <!-- Storage Size -->
            <div style="padding: 15px; background: #f5f5f5; border-radius: 4px; border-left: 4px solid #666;">
                <div style="font-size: 12px; color: #666; margin-bottom: 5px;"><?php esc_html_e('Database Storage', 'safequote-traditional'); ?></div>
                <div style="font-size: 32px; font-weight: bold; color: #666;"><?php echo esc_html($storage_size_formatted); ?></div>
                <div style="font-size: 12px; color: #999; margin-top: 5px;"><?php esc_html_e('Permanent storage - never deleted', 'safequote-traditional'); ?></div>
            </div>
        </div>

        <!-- Data Quality -->
        <h4 style="margin: 20px 0 12px 0; color: #2c3338;"><?php esc_html_e('Data Completeness', 'safequote-traditional'); ?></h4>
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
            <tr style="background: #f5f5f5; border: 1px solid #ddd;">
                <th style="padding: 10px; text-align: left; font-weight: bold; border: 1px solid #ddd;"><?php esc_html_e('Rating Status', 'safequote-traditional'); ?></th>
                <th style="padding: 10px; text-align: right; font-weight: bold; border: 1px solid #ddd;"><?php esc_html_e('Count', 'safequote-traditional'); ?></th>
                <th style="padding: 10px; text-align: right; font-weight: bold; border: 1px solid #ddd;"><?php esc_html_e('Percentage', 'safequote-traditional'); ?></th>
                <th style="padding: 10px; text-align: left; font-weight: bold; border: 1px solid #ddd;"><?php esc_html_e('Progress', 'safequote-traditional'); ?></th>
            </tr>
            <tr style="border: 1px solid #ddd;">
                <td style="padding: 10px; border: 1px solid #ddd; color: #28a745; font-weight: bold;">✓ <?php esc_html_e('Complete', 'safequote-traditional'); ?></td>
                <td style="padding: 10px; border: 1px solid #ddd; text-align: right;"><?php echo number_format($complete_ratings); ?></td>
                <td style="padding: 10px; border: 1px solid #ddd; text-align: right;"><?php echo esc_html($complete_percent); ?>%</td>
                <td style="padding: 10px; border: 1px solid #ddd;">
                    <div style="background: #e8f5e9; height: 20px; border-radius: 3px; overflow: hidden;">
                        <div style="background: #28a745; height: 100%; width: <?php echo esc_attr($complete_percent); ?>%; transition: width 0.3s;"></div>
                    </div>
                </td>
            </tr>
            <tr style="border: 1px solid #ddd;">
                <td style="padding: 10px; border: 1px solid #ddd; color: #ffc107; font-weight: bold;">⊘ <?php esc_html_e('Partial', 'safequote-traditional'); ?></td>
                <td style="padding: 10px; border: 1px solid #ddd; text-align: right;"><?php echo number_format($partial_ratings); ?></td>
                <td style="padding: 10px; border: 1px solid #ddd; text-align: right;"><?php echo esc_html($partial_percent); ?>%</td>
                <td style="padding: 10px; border: 1px solid #ddd;">
                    <div style="background: #fff3e0; height: 20px; border-radius: 3px; overflow: hidden;">
                        <div style="background: #ffc107; height: 100%; width: <?php echo esc_attr($partial_percent); ?>%; transition: width 0.3s;"></div>
                    </div>
                </td>
            </tr>
            <tr style="border: 1px solid #ddd;">
                <td style="padding: 10px; border: 1px solid #ddd; color: #dc3545; font-weight: bold;">✗ <?php esc_html_e('No Rating', 'safequote-traditional'); ?></td>
                <td style="padding: 10px; border: 1px solid #ddd; text-align: right;"><?php echo number_format($no_ratings); ?></td>
                <td style="padding: 10px; border: 1px solid #ddd; text-align: right;"><?php echo esc_html($no_rating_percent); ?>%</td>
                <td style="padding: 10px; border: 1px solid #ddd;">
                    <div style="background: #ffebee; height: 20px; border-radius: 3px; overflow: hidden;">
                        <div style="background: #dc3545; height: 100%; width: <?php echo esc_attr($no_rating_percent); ?>%; transition: width 0.3s;"></div>
                    </div>
                </td>
            </tr>
        </table>

        <!-- Data Sources -->
        <h4 style="margin: 20px 0 12px 0; color: #2c3338;"><?php esc_html_e('Data Sources', 'safequote-traditional'); ?></h4>
        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; margin-bottom: 20px;">
            <div style="padding: 12px; background: #e3f2fd; border-left: 4px solid #2196f3; border-radius: 3px;">
                <div style="font-size: 12px; color: #1976d2; margin-bottom: 5px;"><?php esc_html_e('CSV Import', 'safequote-traditional'); ?></div>
                <div style="font-size: 24px; font-weight: bold; color: #2196f3;"><?php echo number_format($csv_source); ?></div>
                <div style="font-size: 12px; color: #666; margin-top: 5px;">
                    <?php printf(
                        esc_html__('%d%%', 'safequote-traditional'),
                        $total_vehicles > 0 ? round(($csv_source / $total_vehicles) * 100) : 0
                    ); ?>
                </div>
            </div>
            <div style="padding: 12px; background: #f3e5f5; border-left: 4px solid #9c27b0; border-radius: 3px;">
                <div style="font-size: 12px; color: #7b1fa2; margin-bottom: 5px;"><?php esc_html_e('API Fetch', 'safequote-traditional'); ?></div>
                <div style="font-size: 24px; font-weight: bold; color: #9c27b0;"><?php echo number_format($api_source); ?></div>
                <div style="font-size: 12px; color: #666; margin-top: 5px;">
                    <?php printf(
                        esc_html__('%d%%', 'safequote-traditional'),
                        $total_vehicles > 0 ? round(($api_source / $total_vehicles) * 100) : 0
                    ); ?>
                </div>
            </div>
            <div style="padding: 12px; background: #f0f4c3; border-left: 4px solid #cddc39; border-radius: 3px;">
                <div style="font-size: 12px; color: #827717; margin-bottom: 5px;"><?php esc_html_e('Manual', 'safequote-traditional'); ?></div>
                <div style="font-size: 24px; font-weight: bold; color: #cddc39;"><?php echo number_format($manual_source); ?></div>
                <div style="font-size: 12px; color: #666; margin-top: 5px;">
                    <?php printf(
                        esc_html__('%d%%', 'safequote-traditional'),
                        $total_vehicles > 0 ? round(($manual_source / $total_vehicles) * 100) : 0
                    ); ?>
                </div>
            </div>
        </div>

        <!-- Last Update -->
        <div style="padding: 12px; background: #f5f5f5; border-radius: 4px; border-left: 4px solid #28a745;">
            <span style="font-weight: bold;"><?php esc_html_e('Last Database Update:', 'safequote-traditional'); ?></span>
            <span style="color: #666; margin-left: 10px;"><?php echo esc_html($last_update_formatted); ?></span>
        </div>
        <?php
    }
}
