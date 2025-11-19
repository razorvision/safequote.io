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
        // Create database tables
        self::create_tables();

        // Schedule cron jobs
        self::schedule_crons();

        // Enqueue top-safety-picks JavaScript
        add_action('wp_enqueue_scripts', array(__CLASS__, 'enqueue_scripts'));

        // Add admin dashboard widgets
        if (is_admin()) {
            add_action('wp_dashboard_setup', array(__CLASS__, 'register_dashboard_widgets'));
        }

        error_log('[NHTSA Init] Initialization complete');
    }

    /**
     * Create database tables
     *
     * @return void
     */
    private static function create_tables() {
        require_once SAFEQUOTE_THEME_DIR . '/inc/class-nhtsa-database.php';
        SafeQuote_NHTSA_Database::create_tables();
        error_log('[NHTSA Init] Database tables created');
    }

    /**
     * Schedule WP-Cron jobs
     *
     * @return void
     */
    private static function schedule_crons() {
        // Schedule discovery (daily at 2 AM)
        if (!wp_next_scheduled('safequote_nhtsa_discover')) {
            wp_schedule_event(
                strtotime('02:00:00'),
                'daily',
                'safequote_nhtsa_discover'
            );
            error_log('[NHTSA Init] Scheduled discovery cron');
        }

        // Schedule fetch (daily at 3 AM)
        if (!wp_next_scheduled('safequote_nhtsa_fetch')) {
            wp_schedule_event(
                strtotime('03:00:00'),
                'daily',
                'safequote_nhtsa_fetch'
            );
            error_log('[NHTSA Init] Scheduled fetch cron');
        }

        // Schedule validation (daily at 4 AM)
        if (!wp_next_scheduled('safequote_nhtsa_validate')) {
            wp_schedule_event(
                strtotime('04:00:00'),
                'daily',
                'safequote_nhtsa_validate'
            );
            error_log('[NHTSA Init] Scheduled validation cron');
        }

        // Schedule cleanup (daily at 5 AM)
        if (!wp_next_scheduled('safequote_nhtsa_cleanup')) {
            wp_schedule_event(
                strtotime('05:00:00'),
                'daily',
                'safequote_nhtsa_cleanup'
            );
            error_log('[NHTSA Init] Scheduled cleanup cron');
        }
    }

    /**
     * Discovery cron job
     *
     * @return void
     */
    public static function cron_discover() {
        require_once SAFEQUOTE_THEME_DIR . '/inc/class-nhtsa-discover.php';

        $result = SafeQuote_NHTSA_Discover::discover_vehicles();
        error_log('[NHTSA Cron] Discovery: ' . json_encode($result));
    }

    /**
     * Fetch cron job
     *
     * Runs multiple times to process batch in smaller chunks
     * to avoid timeout.
     *
     * @return void
     */
    public static function cron_fetch() {
        require_once SAFEQUOTE_THEME_DIR . '/inc/class-nhtsa-fetch.php';

        // Run fetch job 5 times with small delay between runs
        for ($i = 0; $i < 5; $i++) {
            $result = SafeQuote_NHTSA_Fetch::fetch_pending_batch();

            if ($result['processed'] === 0) {
                // No more pending items
                break;
            }

            // Small delay between batch runs
            sleep(1);
        }

        error_log('[NHTSA Cron] Fetch complete');
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
     * Cleanup on theme deactivation
     *
     * @return void
     */
    public static function cleanup() {
        // Unschedule all NHTSA crons
        wp_clear_scheduled_hook('safequote_nhtsa_discover');
        wp_clear_scheduled_hook('safequote_nhtsa_fetch');
        wp_clear_scheduled_hook('safequote_nhtsa_validate');
        wp_clear_scheduled_hook('safequote_nhtsa_cleanup');

        error_log('[NHTSA Init] Cleanup complete - crons unscheduled');
    }
}

// Register cron job callbacks
add_action('safequote_nhtsa_discover', array('SafeQuote_NHTSA_Init', 'cron_discover'));
add_action('safequote_nhtsa_fetch', array('SafeQuote_NHTSA_Init', 'cron_fetch'));
add_action('safequote_nhtsa_validate', array('SafeQuote_NHTSA_Init', 'cron_validate'));
add_action('safequote_nhtsa_cleanup', array('SafeQuote_NHTSA_Init', 'cron_cleanup'));

// Initialize on theme setup
add_action('after_setup_theme', array('SafeQuote_NHTSA_Init', 'init'));
