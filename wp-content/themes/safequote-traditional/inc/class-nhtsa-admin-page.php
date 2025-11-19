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

        // Handle clear cache
        if (isset($_POST['safequote_nhtsa_clear'])) {
            require_once SAFEQUOTE_THEME_DIR . '/inc/class-nhtsa-database.php';
            require_once SAFEQUOTE_THEME_DIR . '/inc/class-nhtsa-cache.php';

            SafeQuote_NHTSA_Database::truncate_all();
            SafeQuote_NHTSA_Cache::clear_transients();

            add_settings_error(
                'safequote_nhtsa',
                'cleared',
                __('All NHTSA caches cleared successfully', 'safequote-traditional'),
                'success'
            );
        }

        // Handle force reimport
        if (isset($_POST['safequote_nhtsa_force_reimport'])) {
            require_once SAFEQUOTE_THEME_DIR . '/inc/class-nhtsa-csv-import.php';
            require_once SAFEQUOTE_THEME_DIR . '/inc/class-nhtsa-database.php';

            // Clear all data first
            SafeQuote_NHTSA_Database::truncate_all();

            // Force reimport
            $result = SafeQuote_NHTSA_CSV_Import::force_reimport();

            if ($result['status'] === 'success') {
                add_settings_error(
                    'safequote_nhtsa',
                    'reimport_success',
                    sprintf(
                        __('Force reimport successful! Imported: %d vehicles', 'safequote-traditional'),
                        $result['imported']
                    ),
                    'success'
                );
            } else {
                add_settings_error(
                    'safequote_nhtsa',
                    'reimport_failed',
                    sprintf(
                        __('Force reimport failed: %s', 'safequote-traditional'),
                        $result['reason']
                    ),
                    'error'
                );
            }
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

        $db_stats = SafeQuote_NHTSA_Database::get_sync_stats();
        $csv_stats = SafeQuote_NHTSA_CSV_Import::get_import_stats();
        $cache_stats = SafeQuote_NHTSA_Cache::get_cache_stats();
        $health = SafeQuote_NHTSA_Validate::check_health();
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('NHTSA Vehicle Safety Data', 'safequote-traditional'); ?></h1>
            <p class="description">
                <?php esc_html_e('Manage NHTSA vehicle safety ratings data and manual CSV import.', 'safequote-traditional'); ?>
            </p>

            <?php settings_errors('safequote_nhtsa'); ?>

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
                                <td><?php esc_html_e('Synchronized:', 'safequote-traditional'); ?></td>
                                <td style="text-align: right;"><strong><?php echo esc_html($db_stats['success']); ?></strong></td>
                            </tr>
                            <tr>
                                <td><?php esc_html_e('No Data:', 'safequote-traditional'); ?></td>
                                <td style="text-align: right;"><strong><?php echo esc_html($db_stats['no_data']); ?></strong></td>
                            </tr>
                            <tr>
                                <td><?php esc_html_e('Failed:', 'safequote-traditional'); ?></td>
                                <td style="text-align: right;"><strong style="color: #721c24;"><?php echo esc_html($db_stats['failed']); ?></strong></td>
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

                <!-- Cache Stats -->
                <div class="postbox">
                    <div class="postbox-header">
                        <h2 class="hndle"><?php esc_html_e('Cache Performance', 'safequote-traditional'); ?></h2>
                    </div>
                    <div class="inside">
                        <p>
                            <strong><?php esc_html_e('Cache Size:', 'safequote-traditional'); ?></strong><br/>
                            <span style="font-size: 18px;"><?php echo esc_html($cache_stats['total_size']); ?></span>
                        </p>
                        <hr/>
                        <p>
                            <strong><?php esc_html_e('Valid Entries:', 'safequote-traditional'); ?></strong><br/>
                            <?php echo esc_html($cache_stats['valid_entries']); ?> / <?php echo esc_html($cache_stats['total_entries']); ?>
                        </p>
                        <hr/>
                        <p>
                            <strong><?php esc_html_e('Hit Rate:', 'safequote-traditional'); ?></strong><br/>
                            <span style="font-size: 18px; font-weight: bold;"><?php echo esc_html($cache_stats['cache_hit_rate']); ?>%</span>
                        </p>
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
                            </div>

                            <!-- Force Reimport -->
                            <div style="padding: 15px; border: 1px solid #ddd; border-radius: 4px;">
                                <h3 style="margin-top: 0;"><?php esc_html_e('Force Reimport', 'safequote-traditional'); ?></h3>
                                <p class="description">
                                    <?php esc_html_e('Clear all data and re-download CSV from scratch.', 'safequote-traditional'); ?>
                                </p>
                                <button type="submit" name="safequote_nhtsa_force_reimport" class="button button-secondary" onclick="return confirm('<?php esc_attr_e('This will clear all cached data and re-import. Continue?', 'safequote-traditional'); ?>');">
                                    <?php esc_html_e('Force Reimport', 'safequote-traditional'); ?>
                                </button>
                            </div>

                            <!-- Clear Cache -->
                            <div style="padding: 15px; border: 1px solid #ddd; border-radius: 4px;">
                                <h3 style="margin-top: 0;"><?php esc_html_e('Clear All Caches', 'safequote-traditional'); ?></h3>
                                <p class="description">
                                    <?php esc_html_e('Clear database and transient caches. Data will be re-fetched on next request.', 'safequote-traditional'); ?>
                                </p>
                                <button type="submit" name="safequote_nhtsa_clear" class="button button-link-delete" onclick="return confirm('<?php esc_attr_e('Clear all caches?', 'safequote-traditional'); ?>');">
                                    <?php esc_html_e('Clear Cache', 'safequote-traditional'); ?>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

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
                    <h2 class="hndle"><?php esc_html_e('Information', 'safequote-traditional'); ?></h2>
                </div>
                <div class="inside">
                    <h3><?php esc_html_e('CSV Source', 'safequote-traditional'); ?></h3>
                    <p>
                        <?php esc_html_e('Data is downloaded from the official NHTSA CSV:', 'safequote-traditional'); ?><br/>
                        <code>https://static.nhtsa.gov/nhtsa/downloads/Safercar/Safercar_data.csv</code>
                    </p>

                    <h3><?php esc_html_e('Automatic Schedule', 'safequote-traditional'); ?></h3>
                    <ul>
                        <li><strong>2:00 AM</strong> - <?php esc_html_e('Check for CSV updates and import if changed', 'safequote-traditional'); ?></li>
                        <li><strong>3:00 AM</strong> - <?php esc_html_e('Validate data completeness', 'safequote-traditional'); ?></li>
                        <li><strong>4:00 AM</strong> - <?php esc_html_e('Clean up expired cache entries', 'safequote-traditional'); ?></li>
                    </ul>

                    <h3><?php esc_html_e('Database Tables', 'safequote-traditional'); ?></h3>
                    <ul>
                        <li><code>wp_nhtsa_vehicle_cache</code> - <?php esc_html_e('Vehicle safety ratings (persistent)', 'safequote-traditional'); ?></li>
                        <li><code>wp_options</code> - <?php esc_html_e('Transient cache entries (temporary)', 'safequote-traditional'); ?></li>
                    </ul>
                </div>
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

        // Get sample data
        $results = $wpdb->get_results(
            "SELECT year, make, model, nhtsa_overall_rating
             FROM $table
             ORDER BY year DESC, make, model
             LIMIT 10"
        );

        if (empty($results)) {
            echo '<p class="description">' . esc_html__('No vehicle data in database yet.', 'safequote-traditional') . '</p>';
            return;
        }

        ?>
        <table class="widefat striped">
            <thead>
                <tr>
                    <th><?php esc_html_e('Year', 'safequote-traditional'); ?></th>
                    <th><?php esc_html_e('Make', 'safequote-traditional'); ?></th>
                    <th><?php esc_html_e('Model', 'safequote-traditional'); ?></th>
                    <th><?php esc_html_e('NHTSA Rating', 'safequote-traditional'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $row) : ?>
                    <tr>
                        <td><?php echo esc_html($row->year); ?></td>
                        <td><?php echo esc_html($row->make); ?></td>
                        <td><?php echo esc_html($row->model); ?></td>
                        <td>
                            <?php if ($row->nhtsa_overall_rating) : ?>
                                <strong><?php echo esc_html(number_format($row->nhtsa_overall_rating, 1)); ?>/5.0</strong>
                            <?php else : ?>
                                <em><?php esc_html_e('No rating', 'safequote-traditional'); ?></em>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <p class="description" style="margin-top: 10px;">
            <?php printf(
                esc_html__('Showing first 10 of %d vehicles in database.', 'safequote-traditional'),
                $wpdb->get_var("SELECT COUNT(*) FROM $table")
            ); ?>
        </p>
        <?php
    }
}
