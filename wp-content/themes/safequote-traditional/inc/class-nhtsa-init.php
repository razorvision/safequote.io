<?php
/**
 * NHTSA Initialization & WP-Cron Setup
 *
 * Initializes NHTSA integration, creates database tables,
 * and registers scheduled background tasks.
 *
 * @package SafeQuote_Traditional
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class SafeQuote_NHTSA_Init {

    /**
     * Initialize NHTSA integration
     *
     * Called on theme activation to set up database tables
     * and schedule cron jobs.
     *
     * @return void
     */
    public static function init() {
        // Guard: prevent multiple initializations (after_setup_theme can fire multiple times)
        static $initialized = false;
        if ($initialized) {
            return;
        }
        $initialized = true;

        // Schedule cron jobs
        self::schedule_crons();

        // Enqueue top-safety-picks JavaScript
        add_action('wp_enqueue_scripts', array(__CLASS__, 'enqueue_scripts'));

        // Add admin dashboard widgets
        if (is_admin()) {
            add_action('wp_dashboard_setup', array(__CLASS__, 'register_dashboard_widgets'));
        }
    }

    /**
     * Initialize NHTSA on theme activation
     *
     * Called once when theme is switched to, creates database tables
     * and schedules initial cron jobs.
     *
     * @return void
     */
    public static function activate() {
        // Create database tables on theme activation
        self::create_database_tables();
        error_log('[NHTSA Init] Theme activated - database tables initialized');
    }

    /**
     * Create or repair database tables
     *
     * Public method that can be called from admin page or theme activation.
     * Safe to call multiple times - checks if tables exist before creating.
     * If tables were dropped, this will recreate them.
     *
     * @return void
     */
    public static function create_database_tables() {
        global $wpdb;

        // Check if tables actually exist (more reliable than WordPress option)
        $table_vehicle_cache = $wpdb->prefix . 'nhtsa_vehicle_cache';
        $table_sync_log = $wpdb->prefix . 'nhtsa_sync_log';

        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_vehicle_cache'") === $table_vehicle_cache;

        // Only create if tables don't exist
        if (!$table_exists) {
            require_once SAFEQUOTE_THEME_DIR . '/inc/class-nhtsa-database.php';
            SafeQuote_NHTSA_Database::create_tables();
            error_log('[NHTSA Init] Database tables created/restored');
        } else {
            error_log('[NHTSA Init] Database tables already exist');
        }

        // Mark as initialized for reference
        update_option('safequote_nhtsa_db_initialized', current_time('mysql'));
    }

    /**
     * Schedule WP-Cron jobs
     *
     * @return void
     */
    private static function schedule_crons() {
        // Schedule CSV sync (daily at 2 AM)
        // Checks if NHTSA CSV was updated and imports if needed
        if (!wp_next_scheduled('safequote_nhtsa_csv_sync')) {
            wp_schedule_event(
                strtotime('02:00:00'),
                'daily',
                'safequote_nhtsa_csv_sync'
            );
            error_log('[NHTSA Init] Scheduled CSV sync cron');
        }

        // Schedule validation (daily at 3 AM)
        if (!wp_next_scheduled('safequote_nhtsa_validate')) {
            wp_schedule_event(
                strtotime('03:00:00'),
                'daily',
                'safequote_nhtsa_validate'
            );
            error_log('[NHTSA Init] Scheduled validation cron');
        }

        // Schedule cleanup (daily at 4 AM)
        if (!wp_next_scheduled('safequote_nhtsa_cleanup')) {
            wp_schedule_event(
                strtotime('04:00:00'),
                'daily',
                'safequote_nhtsa_cleanup'
            );
            error_log('[NHTSA Init] Scheduled cleanup cron');
        }

        // Schedule auto batch fill (every 3 minutes)
        // Automatically processes 50 vehicles at a time from NHTSA API
        if (!wp_next_scheduled('safequote_nhtsa_auto_batch_fill')) {
            wp_schedule_event(
                time(),
                'three_minutes',
                'safequote_nhtsa_auto_batch_fill'
            );
            error_log('[NHTSA Init] Scheduled auto batch fill cron (every 3 minutes)');
        }
    }

    /**
     * CSV sync cron job
     *
     * Checks if NHTSA CSV file has been updated on their server.
     * If updated, downloads and imports all vehicle safety ratings.
     *
     * @return void
     */
    public static function cron_csv_sync() {
        require_once SAFEQUOTE_THEME_DIR . '/inc/class-nhtsa-csv-import.php';

        $result = SafeQuote_NHTSA_CSV_Import::sync_csv_data();
        error_log('[NHTSA Cron] CSV Sync: ' . json_encode($result));
    }

    /**
     * Validation cron job
     *
     * @return void
     */
    public static function cron_validate() {
        require_once SAFEQUOTE_THEME_DIR . '/inc/class-nhtsa-validate.php';

        $report = SafeQuote_NHTSA_Validate::validate_sync();
        error_log('[NHTSA Cron] Validation complete: ' . json_encode($report['sync']));
    }

    /**
     * Cleanup cron job
     *
     * @return void
     */
    public static function cron_cleanup() {
        require_once SAFEQUOTE_THEME_DIR . '/inc/class-nhtsa-validate.php';
        require_once SAFEQUOTE_THEME_DIR . '/inc/class-nhtsa-database.php';

        $expired = SafeQuote_NHTSA_Validate::cleanup_expired_cache();
        SafeQuote_NHTSA_Database::get_sync_stats(); // Also updates transient

        error_log("[NHTSA Cron] Cleanup: Removed $expired expired entries");
    }

    /**
     * Auto batch fill cron job (every 5 minutes)
     *
     * Automatically processes 50 vehicles at a time from NHTSA API
     * Runs in background without requiring admin intervention
     *
     * @return void
     */
    public static function cron_auto_batch_fill() {
        require_once SAFEQUOTE_THEME_DIR . '/inc/class-nhtsa-admin-page.php';

        // Get or create batch session if doesn't exist
        $session = get_option('safequote_nhtsa_batch_session');

        // If no active session and there are vehicles to process, start a new one
        if (!$session) {
            global $wpdb;
            $table = $wpdb->prefix . 'nhtsa_vehicle_cache';
            $remaining = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE nhtsa_overall_rating IS NULL OR vehicle_picture IS NULL");

            if ($remaining > 0) {
                error_log("[NHTSA Cron] Auto batch: Starting new session for $remaining vehicles");
            } else {
                error_log("[NHTSA Cron] Auto batch: No vehicles to process");
                return;
            }
        }

        // Use reflection to call private method
        $reflection = new \ReflectionClass('SafeQuote_NHTSA_Admin_Page');
        $method = $reflection->getMethod('batch_fill_missing_ratings');
        $method->setAccessible(true);

        // Continue existing batch if session exists, otherwise start new
        $start_new = !$session;
        $result = $method->invoke(null, $start_new);

        if ($result['success']) {
            error_log("[NHTSA Cron] Auto batch: Processed {$result['processed']}, Updated {$result['updated']}, Status: {$result['status']}");
        } else {
            error_log("[NHTSA Cron] Auto batch failed: {$result['reason']}");
        }
    }

    /**
     * Enqueue NHTSA-related scripts
     *
     * @return void
     */
    public static function enqueue_scripts() {
        if (is_front_page()) {
            wp_enqueue_script(
                'safequote-top-safety-picks',
                SAFEQUOTE_THEME_URI . '/assets/js/top-safety-picks.js',
                array('safequote-main', 'safequote-nhtsa-api'),
                SAFEQUOTE_THEME_VERSION,
                true
            );

            // Localize script
            wp_localize_script('safequote-top-safety-picks', 'safequote_top_picks', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('safequote_top_picks_nonce'),
            ));
        }
    }

    /**
     * Register dashboard widgets
     *
     * @return void
     */
    public static function register_dashboard_widgets() {
        wp_add_dashboard_widget(
            'safequote-nhtsa-status',
            __('SafeQuote NHTSA Sync Status', 'safequote-traditional'),
            array(__CLASS__, 'dashboard_widget_callback')
        );
    }

    /**
     * Dashboard widget callback
     *
     * @return void
     */
    public static function dashboard_widget_callback() {
        require_once SAFEQUOTE_THEME_DIR . '/inc/class-nhtsa-validate.php';
        require_once SAFEQUOTE_THEME_DIR . '/inc/class-nhtsa-database.php';

        $health = SafeQuote_NHTSA_Validate::check_health();
        $stats = SafeQuote_NHTSA_Database::get_sync_stats();

        ?>
        <div style="padding: 0 20px;">
            <p>
                <strong><?php esc_html_e('Status:', 'safequote-traditional'); ?></strong>
                <span style="
                    display: inline-block;
                    padding: 4px 12px;
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

            <table style="width: 100%; margin: 10px 0;">
                <tr>
                    <td><?php esc_html_e('Total Vehicles:', 'safequote-traditional'); ?></td>
                    <td style="text-align: right;"><strong><?php echo esc_html($health['total_vehicles']); ?></strong></td>
                </tr>
                <tr>
                    <td><?php esc_html_e('Synchronized:', 'safequote-traditional'); ?></td>
                    <td style="text-align: right;"><strong><?php echo esc_html($health['synchronized']); ?></strong></td>
                </tr>
                <tr>
                    <td><?php esc_html_e('No Data:', 'safequote-traditional'); ?></td>
                    <td style="text-align: right;"><strong><?php echo esc_html($health['no_data']); ?></strong></td>
                </tr>
                <tr>
                    <td><?php esc_html_e('Pending:', 'safequote-traditional'); ?></td>
                    <td style="text-align: right;"><strong><?php echo esc_html($health['pending']); ?></strong></td>
                </tr>
                <tr style="border-top: 1px solid #ddd; margin-top: 10px; padding-top: 10px;">
                    <td><?php esc_html_e('Coverage:', 'safequote-traditional'); ?></td>
                    <td style="text-align: right;"><strong><?php echo esc_html($health['coverage']); ?>%</strong></td>
                </tr>
            </table>

            <p style="font-size: 12px; color: #666; margin-top: 15px;">
                <?php esc_html_e('Last Run:', 'safequote-traditional'); ?>
                <br />
                <?php echo esc_html($health['last_run']); ?>
            </p>
        </div>
        <?php
    }

    /**
     * Disable all NHTSA cron tasks
     *
     * Called from admin page to manually disable automated background tasks.
     * Does not delete scheduled events permanently, just unschedules them
     * so they won't run until enable_all_crons() is called.
     *
     * @return void
     */
    public static function disable_all_crons() {
        wp_clear_scheduled_hook('safequote_nhtsa_csv_sync');
        wp_clear_scheduled_hook('safequote_nhtsa_validate');
        wp_clear_scheduled_hook('safequote_nhtsa_cleanup');
        wp_clear_scheduled_hook('safequote_nhtsa_auto_batch_fill');

        error_log('[NHTSA Init] All cron tasks disabled via admin panel');
    }

    /**
     * Enable all NHTSA cron tasks
     *
     * Called from admin page to re-enable automated background tasks.
     * Re-schedules all cron jobs that were previously disabled.
     *
     * @return void
     */
    public static function enable_all_crons() {
        // Remove any existing schedules first (in case they're stuck)
        wp_clear_scheduled_hook('safequote_nhtsa_csv_sync');
        wp_clear_scheduled_hook('safequote_nhtsa_validate');
        wp_clear_scheduled_hook('safequote_nhtsa_cleanup');
        wp_clear_scheduled_hook('safequote_nhtsa_auto_batch_fill');

        // Re-schedule all crons
        self::schedule_crons();

        error_log('[NHTSA Init] All cron tasks enabled via admin panel');
    }

    /**
     * Cleanup on theme deactivation
     *
     * @return void
     */
    public static function cleanup() {
        // Unschedule all NHTSA crons
        wp_clear_scheduled_hook('safequote_nhtsa_csv_sync');
        wp_clear_scheduled_hook('safequote_nhtsa_validate');
        wp_clear_scheduled_hook('safequote_nhtsa_cleanup');
        wp_clear_scheduled_hook('safequote_nhtsa_auto_batch_fill');

        // Cleanup CSV import temporary files
        require_once SAFEQUOTE_THEME_DIR . '/inc/class-nhtsa-csv-import.php';
        SafeQuote_NHTSA_CSV_Import::cleanup();

        error_log('[NHTSA Init] Cleanup complete - crons unscheduled');
    }
}

// Register cron job callbacks
add_action('safequote_nhtsa_csv_sync', array('SafeQuote_NHTSA_Init', 'cron_csv_sync'));
add_action('safequote_nhtsa_validate', array('SafeQuote_NHTSA_Init', 'cron_validate'));
add_action('safequote_nhtsa_cleanup', array('SafeQuote_NHTSA_Init', 'cron_cleanup'));
add_action('safequote_nhtsa_auto_batch_fill', array('SafeQuote_NHTSA_Init', 'cron_auto_batch_fill'));

// Add custom 3-minute cron interval
add_filter('cron_schedules', function ($schedules) {
    if (!isset($schedules['three_minutes'])) {
        $schedules['three_minutes'] = array(
            'interval' => 180, // 3 minutes in seconds
            'display'  => __('Every 3 minutes', 'safequote-traditional'),
        );
    }
    return $schedules;
});

// Initialize on theme activation (setup database)
add_action('after_switch_theme', array('SafeQuote_NHTSA_Init', 'activate'));

// Initialize on theme setup (schedule crons and enqueue assets)
add_action('after_setup_theme', array('SafeQuote_NHTSA_Init', 'init'));
