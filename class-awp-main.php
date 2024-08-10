<?php
/**
 * Automation Web Platform Plugin.
 *
 * @package AutomationWebPlatform.
 */

// Define a constant for the function name
define('awp_FUNCTION', 'awp_connection');

class awp_Main {

    protected static $instance = null;

    // Singleton pattern to ensure only one instance
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public $ui;

    // Constructor to initialize the plugin
    public function __construct() {
        $this->ui  = new awp_UI();
        $this->log = new awp_logger();
        
        // Adding various hooks and actions
        add_action('init', array($this, 'awp_textdomain'));
        add_action('admin_init', array($this, 'awp_register_settings'));
        add_filter('manage_edit-shop_order_columns', array($this, 'awp_wa_manual_new_columns'));
        add_action('manage_shop_order_posts_custom_column', array($this, 'awp_wa_manual_manage_columns'), 10, 2);
        add_action('admin_menu', array($this, 'awp_admin_menu'));
        add_action('admin_notices', array($this, 'awp_admin_notices'));
        add_action( 'woocommerce_order_status_pending', array( $this, 'awp_wa_process_states_pending' ), 10 );
		add_action( 'woocommerce_order_status_failed', array( $this, 'awp_wa_process_states_failed' ), 10 );
		add_action( 'woocommerce_order_status_on-hold', array( $this, 'awp_wa_process_states_onhold' ), 10 );
		add_action( 'woocommerce_order_status_completed', array( $this, 'awp_wa_process_states_completed' ), 10 );
		add_action( 'woocommerce_order_status_processing', array( $this, 'awp_wa_process_states_processing' ), 10 );
		add_action( 'woocommerce_order_status_refunded', array( $this, 'awp_wa_process_states_refunded' ), 10 );
		add_action( 'woocommerce_order_status_cancelled', array( $this, 'awp_wa_process_states_cancelled' ), 10 );
		add_action( 'woocommerce_new_customer_note', array( $this, 'awp_wa_process_note' ), 10 );
        add_action('admin_init', array($this, 'awp_custom_order_status'));
        add_action('woocommerce_save_account_details', array($this, 'save_billing_phone_on_edit_account'));
        add_action('woocommerce_edit_account_form', array($this, 'add_billing_phone_to_edit_account_form'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_select2'));
        add_action('followup_cron_hook', array($this, 'followup_order'));
        add_action('followup_cron_hook_2', array($this, 'followup_order_2'));
        add_action('followup_cron_hook_3', array($this, 'followup_order_3'));
        add_action('followup_cron_hook_4', array($this, 'followup_order_4'));
        add_action('aftersales_cron_hook', array($this, 'aftersales_order'));
        add_action('aftersales_cron_hook_2', array($this, 'aftersales_order_2'));
        add_action('aftersales_cron_hook_3', array($this, 'aftersales_order_3'));
        add_action('aftersales_cron_hook_4', array($this, 'aftersales_order_4'));
        add_action('abandoned_cron_hook', array($this, 'abandoned_order'));
        add_filter('cron_schedules', array($this, 'followup_cron_schedule'));
        add_filter('manage_users_custom_column', array($this, 'display_billing_phone_content'), 10, 3);
        add_action('admin_bar_menu', array($this, 'status_on_admin_bar'), 100);

        // Schedule cron events if not already scheduled
        $this->schedule_cron_events();
    }

    // Check if a plugin is active
    public function is_plugin_active($plugin) {
        return in_array($plugin, (array) get_option('active_plugins', array()));
    }

    // Load text domain for translations
    public function awp_textdomain() {
        load_plugin_textdomain('awp-send', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    // Register settings
    public function awp_register_settings() {
        register_setting('awp_storage_notifications', 'awp_notifications');
        register_setting('awp_storage_instances', 'awp_instances');
    }

    // Add menu and submenu pages
    	public function awp_admin_menu() {
		$config    = get_option( 'awp_notifications' );
			add_submenu_page(
			'awp',
			__( 'Sender Settings', 'awp' ),
			__( 'Sender Settings', 'awp' ),
			'manage_options',
			'awp-settings'
		);
				add_submenu_page(
			'awp',
			__('Country code settings', 'awp'),
			__('Country Code', 'awp'),
			'manage_options',
			'awp-countrycode'
		);
		
		
		$my_page_1 = add_menu_page(
			__( 'Notifications', 'awp' ),
			__( 'Wawp', 'awp' ),
			'manage_options',
			'awp',
			array(
				$this->ui,
				'admin_page',
			),
			plugin_dir_url( __FILE__ ) . 'assets/img/menu.png'
		);
		add_action( 'load-' . $my_page_1, array( $this, 'awp_load_admin_js' ) );
	
		
		add_submenu_page(
			'awp',
			__( 'WhatsApp Notifications', 'awp' ),
			__( 'Wa Notifications', 'awp' ),
			'manage_options',
			'awp',
			array( $this->ui, 'admin_page' )
		);
		
		   add_submenu_page(
			'awp',
			__( 'Login & Signup OTP', 'awp' ),
			__( 'OTP Logins', 'awp' ),
			'administrator',
			'awp-otp'
		);
		
		add_submenu_page(
			'awp',
			 __('Checkout OTP Verification', 'awp'), 
            __('Checkout OTP', 'awp'), 
			'manage_options', 
            'awp-checkout-otp'
		);
		
     $my_page_2 = add_submenu_page(
			'awp',
			__( 'Notification Logs', 'awp' ),
			__( 'Notification Logs', 'awp' ),
			'manage_options',
			'awp-message-log',
			array( $this->ui, 'logs_page' )
		);

		add_action( 'load-' . $my_page_2, array( $this, 'awp_load_admin_js' ) );
		
		add_submenu_page(
			'awp',
			 __('System Status', 'awp'), 
                __('System Status', 'awp'), 
            'manage_options', 
                'awp-system-status-info'
		);
		
		
		if ( isset( $_GET['post_type'] ) && $_GET['post_type'] == 'shop_order' ) {
			if ( isset( $_GET['id'] ) ) {
				// $post_id = sanitize_text_field($_GET['id']);
				$post_id = isset( $_GET['id'] ) ? absint( sanitize_text_field( $_GET['id'] ) ) : 0;
				$result  = $this->awp_wa_process_states( $post_id );
				?>
					<div class="notice notice-success is-dismissible">
	<p><?php printf( __( 'Resend Message %s', 'awp-send' ), esc_html( $result ) ); ?></p>

</div>

				<?php
			}
		}
	}

    // Load admin JS
    public function awp_load_admin_js() {
        add_action('admin_enqueue_scripts', array($this, 'awp_admin_assets'));
    }
    // Enqueue admin assets
    public function awp_admin_assets() {
         global $pagenow;
        if ('admin.php' === $pagenow && isset($_GET['page']) && ('awp' === $_GET['page'] || 'awp-message-log' === $_GET['page'])) {
        wp_enqueue_style('awp-admin-style', plugins_url('assets/css/awp-admin-style.css', __FILE__), array(), '1.1.4');
        wp_enqueue_style('awp-admin-emojicss', plugins_url('assets/css/resources/emojionearea.min.css', __FILE__));
        wp_enqueue_style('awp-admin-telcss', plugins_url('assets/css/intlTelInput.css', __FILE__));
         wp_enqueue_script('awp-admin-teljs', plugins_url('assets/js/resources/intlTelInput.js', __FILE__), array('jquery'), '23.0.10', true);
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-accordion');
        wp_enqueue_script('jquery-ui-sortable');
       
        wp_enqueue_script('awp-jquery-modal', plugins_url('assets/js/resources/jquery.modal.min.js', __FILE__));
        wp_enqueue_script('awp-admin-utils', plugins_url('assets/js/resources/utils.js', __FILE__), array('jquery'), '1.0.0', true);
        wp_enqueue_script('awp-plugin-textcomplete', plugins_url('assets/js/resources/jquery.textcomplete.js', __FILE__), array('jquery'), '1.0', true);
        wp_enqueue_script('awp-admin-js', plugins_url('assets/js/awp-admin-js.js', __FILE__), array(), true, true, '1.1.4');
        wp_enqueue_script('awp-admin-emojijs', plugins_url('assets/js/resources/emojionearea.min.js', __FILE__), array('jquery'), '3.4.0', true);
        
        // Check if WordPress is in RTL mode and load RTL CSS if necessary
        if (is_rtl()) {
            wp_enqueue_style('awp-admin-rtl-style', plugins_url('assets/css/awp-admin-rtl-style.css', __FILE__), array(), '1.1.4');
        }
        wp_enqueue_media();
        remove_action('admin_print_styles', 'print_emoji_styles');
        remove_action('wp_head', 'print_emoji_detection_script', 7);
        remove_action('admin_print_scripts', 'print_emoji_detection_script');
        remove_action('wp_print_styles', 'print_emoji_styles');
    }
}
    // Display admin notices
    public function awp_admin_notices() {
        $screen = get_current_screen();
        if (isset($_GET['settings-updated']) && $screen->id == 'toplevel_page_awp') {
            ?>
            <div class="notice notice-success is-dismissible">
                <p><?php esc_html_e('All changes have been saved!', 'awp-send'); ?></p>
            </div>
            <?php
        }
        if ($screen->id == 'awp-send-new_page_awp-message-log') {
            if (isset($_GET['clear'])) {
                $this->log->clear('awp-send', 'awp_logger');
                ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php esc_html_e('Message logs have been cleared!', 'awp-send'); ?></p>
                </div>
                <?php
            }
            if (isset($_POST['awp_resend_wa'])) {
                $resend_phone   = isset($_POST['awp_resend_phone']) ? sanitize_text_field($_POST['awp_resend_phone']) : '';
                $resend_message = isset($_POST['awp_resend_message']) ? sanitize_textarea_field($_POST['awp_resend_message']) : '';
                $resend_image   = isset($_POST['awp_resend_image']) ? esc_url_raw($_POST['awp_resend_image']) : '';
                if (!$resend_phone || !$resend_message) {
                    // handle the error here
                } else {
                    $result = $this->awp_wa_send_msg('', $resend_phone, $resend_message, $resend_image, '');
                }
                ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php echo esc_html(sprintf(__('Resend Message %s', 'awp-send'), $result)); ?></p>
                </div>
                <?php
            }
        }
        if (isset($_POST['awp_send_test'])) {
            if (!empty($_POST['awp_test_number'])) {
                $test_number  = isset($_POST['awp_test_number']) ? sanitize_text_field($_POST['awp_test_number']) : '';
                $test_message = isset($_POST['awp_test_message']) ? sanitize_textarea_field($_POST['awp_test_message']) : '';
                $test_image   = isset($_POST['awp_test_image']) ? esc_url_raw($_POST['awp_test_image']) : '';
                if (!$test_number || !$test_message) {
                    // handle the error here
                } else {
                    $result = $this->awp_wa_send_msg('', $test_number, $test_message, $test_image, '');
                }
                ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php echo esc_html(sprintf(__('Send Message %s', 'awp-send'), $result)); ?></p>
                </div>
                <?php
            }
        }
    }

    // Add new columns to the shop order table
    public function awp_wa_manual_new_columns($columns) {
        $columns['notification'] = __('Notification');
        return $columns;
    }

    // Manage the custom columns in the shop order table
    public function awp_wa_manual_manage_columns($column_name, $id) {
        global $wpdb, $post;
        if ('notification' == $column_name) {
            echo '<a href="' . admin_url('edit.php?post_type=shop_order&id=' . $post->ID) . '" class="button wc-action-buttonv">Resend WhatsApp</a>';
        }
    }

    // Process the status change of an order and send the appropriate WhatsApp message
 	public function awp_wa_process_states_onhold( $order ) {
		global $woocommerce, $custom_status_list_temp;
		$order       = new WC_Order( $order );
		$status      = $order->get_status();
		$status_list = array(
			'on-hold' => __( 'Receive', 'awp-send' ),
		);
		foreach ( $status_list as $status_lists => $translations ) {
			if ( $status == $status_lists ) {
				$status = $translations;
			}
		}
		$config = get_option( 'awp_notifications' );
		$phone  = $order->get_billing_phone();
		// Get the user's locale
		$user_locale = get_user_locale( get_current_user_id() );
		// Use different messages based on the user's locale
		if ( $status == __( 'Receive', 'awp-send' ) ) {
			if ( $user_locale == 'ar' ) {
				$msg = $this->awp_wa_process_variables( $config['order_onhold_arabic'], $order, '' );
				$img = $config['order_onhold_img_arabic'];
			} else {
				$msg = $this->awp_wa_process_variables( $config['order_onhold'], $order, '' );
				$img = $config['order_onhold_img'];
			}
		}
		/* Admin Receive Notification */
		if ( $status == 'Receive' ) {
			$msg_admin   = $this->awp_wa_process_variables( $config['admin_onhold'], $order, '' );
			$img_admin   = $config['admin_onhold_img'];
			$phone_admin = preg_replace( '/[^0-9]/', '', $config['admin_number'] );
			if ( ! empty( $msg_admin ) ) {
				$this->awp_wa_send_msg( $config, $phone_admin, $msg_admin, $img_admin, '' );
			}
		}
		if ( ! empty( $msg ) ) {
			$result = $this->awp_wa_send_msg( $config, $phone, $msg, $img, '' );
			return $result;
		}
	}
	public function awp_wa_process_states_pending( $order ) {
		global $woocommerce, $custom_status_list_temp;
		$order       = new WC_Order( $order );
		$status      = $order->get_status();
		$status_list = array(
			'pending' => __( 'Pending', 'awp-send' ),
		);
		foreach ( $status_list as $status_lists => $translations ) {
			if ( $status == $status_lists ) {
				$status = $translations;
			}
		}
		$config = get_option( 'awp_notifications' );
		$phone  = $order->get_billing_phone();
		// Get the user's locale
		$user_locale = get_user_locale( get_current_user_id() );
		// Use different messages based on the user's locale
		if ( $status == __( 'Pending', 'awp-send' ) ) {
			if ( $user_locale == 'ar' ) {
				$msg = $this->awp_wa_process_variables( $config['order_pending_arabic'], $order, '' );
				$img = $config['order_pending_img_arabic'];
			} else {
				$msg = $this->awp_wa_process_variables( $config['order_pending'], $order, '' );
				$img = $config['order_pending_img'];
			}
		}
		/* Admin Pending Notification */
		if ( $status == 'Pending' ) {
			$msg_admin   = $this->awp_wa_process_variables( $config['admin_pending'], $order, '' );
			$img_admin   = $config['admin_pending_img'];
			$phone_admin = preg_replace( '/[^0-9]/', '', $config['admin_number'] );
			if ( ! empty( $msg_admin ) ) {
				$this->awp_wa_send_msg( $config, $phone_admin, $msg_admin, $img_admin, '' );
			}
		}
		if ( ! empty( $msg ) ) {
			$result = $this->awp_wa_send_msg( $config, $phone, $msg, $img, '' );
			return $result;
		}
	}
	public function awp_wa_process_states_processing( $order ) {
		global $woocommerce, $custom_status_list_temp;
		$order       = new WC_Order( $order );
		$status      = $order->get_status();
		$status_list = array(
			'processing' => __( 'Processing', 'awp-send' ),
		);
		foreach ( $status_list as $status_lists => $translations ) {
			if ( $status == $status_lists ) {
				$status = $translations;
			}
		}
		$config = get_option( 'awp_notifications' );
		$phone  = $order->get_billing_phone();
		// Get the user's locale
		$user_locale = get_user_locale( get_current_user_id() );
		// Use different messages based on the user's locale
		if ( $status == __( 'Processing', 'awp-send' ) ) {
			if ( $user_locale == 'ar' ) {
				$msg = $this->awp_wa_process_variables( $config['order_processing_arabic'], $order, '' );
				$img = $config['order_processing_img_arabic'];
			} else {
				$msg = $this->awp_wa_process_variables( $config['order_processing'], $order, '' );
				$img = $config['order_processing_img'];
			}
		}

		/* Admin Processing Notification */
		if ( $status == 'Processing' ) {
			$msg_admin   = $this->awp_wa_process_variables( $config['admin_processing'], $order, '' );
			$img_admin   = $config['admin_processing_img'];
			$phone_admin = preg_replace( '/[^0-9]/', '', $config['admin_number'] );
			if ( ! empty( $msg_admin ) ) {
				$this->awp_wa_send_msg( $config, $phone_admin, $msg_admin, $img_admin, '' );
			}
		}

		if ( ! empty( $msg ) ) {
			$result = $this->awp_wa_send_msg( $config, $phone, $msg, $img, '' );
			return $result;
		}
	}
	public function awp_wa_process_states_completed( $order ) {
		global $woocommerce, $custom_status_list_temp;
		$order       = new WC_Order( $order );
		$status      = $order->get_status();
		$status_list = array(
			'completed' => __( 'Completed', 'awp-send' ),
		);
		foreach ( $status_list as $status_lists => $translations ) {
			if ( $status == $status_lists ) {
				$status = $translations;
			}
		}
		$config = get_option( 'awp_notifications' );
		$phone  = $order->get_billing_phone();
		// Get the user's locale
		$user_locale = get_user_locale( get_current_user_id() );
		// Use different messages based on the user's locale
		if ( $status == __( 'Completed', 'awp-send' ) ) {
			if ( $user_locale == 'ar' ) {
				$msg = $this->awp_wa_process_variables( $config['order_completed_arabic'], $order, '' );
				$img = $config['order_completed_img_arabic'];
			} else {
				$msg = $this->awp_wa_process_variables( $config['order_completed'], $order, '' );
				$img = $config['order_completed_img'];
			}
		}
		/* Admin Completed Notification */
		if ( $status == 'Completed' ) {
			$msg_admin   = $this->awp_wa_process_variables( $config['admin_completed'], $order, '' );
			$img_admin   = $config['admin_completed_img'];
			$phone_admin = preg_replace( '/[^0-9]/', '', $config['admin_number'] );
			if ( ! empty( $msg_admin ) ) {
				$this->awp_wa_send_msg( $config, $phone_admin, $msg_admin, $img_admin, '' );
			}
		}
		if ( ! empty( $msg ) ) {
			$result = $this->awp_wa_send_msg( $config, $phone, $msg, $img, '' );
			return $result;
		}
	}
	public function awp_wa_process_states_failed( $order ) {
		global $woocommerce, $custom_status_list_temp;
		$order       = new WC_Order( $order );
		$status      = $order->get_status();
		$status_list = array(
			'failed' => __( 'Failed', 'awp-send' ),
		);
		foreach ( $status_list as $status_lists => $translations ) {
			if ( $status == $status_lists ) {
				$status = $translations;
			}
		}
		$config = get_option( 'awp_notifications' );
		$phone  = $order->get_billing_phone();
		// Get the user's locale
		$user_locale = get_user_locale( get_current_user_id() );
		// Use different messages based on the user's locale
		if ( $status == __( 'Failed', 'awp-send' ) ) {
			if ( $user_locale == 'ar' ) {
				$msg = $this->awp_wa_process_variables( $config['order_failed_arabic'], $order, '' );
				$img = $config['order_failed_img_arabic'];
			} else {
				$msg = $this->awp_wa_process_variables( $config['order_failed'], $order, '' );
				$img = $config['order_failed_img'];
			}
		}
		/* Admin Failed Notification */
		if ( $status == 'Failed' ) {
			$msg_admin   = $this->awp_wa_process_variables( $config['admin_failed'], $order, '' );
			$img_admin   = $config['admin_failed_img'];
			$phone_admin = preg_replace( '/[^0-9]/', '', $config['admin_number'] );
			if ( ! empty( $msg_admin ) ) {
				$this->awp_wa_send_msg( $config, $phone_admin, $msg_admin, $img_admin, '' );
			}
		}
		if ( ! empty( $msg ) ) {
			$result = $this->awp_wa_send_msg( $config, $phone, $msg, $img, '' );
			return $result;
		}
	}
	public function awp_wa_process_states_refunded( $order ) {
		global $woocommerce, $custom_status_list_temp;
		$order       = new WC_Order( $order );
		$status      = $order->get_status();
		$status_list = array(
			'refunded' => __( 'Refunded', 'awp-send' ),
		);
		foreach ( $status_list as $status_lists => $translations ) {
			if ( $status == $status_lists ) {
				$status = $translations;
			}
		}
		$config = get_option( 'awp_notifications' );
		$phone  = $order->get_billing_phone();
		// Get the user's locale
		$user_locale = get_user_locale( get_current_user_id() );
		// Use different messages based on the user's locale
		if ( $status == __( 'Refunded', 'awp-send' ) ) {
			if ( $user_locale == 'ar' ) {
				$msg = $this->awp_wa_process_variables( $config['order_refunded_arabic'], $order, '' );
				$img = $config['order_refunded_img_arabic'];
			} else {
				$msg = $this->awp_wa_process_variables( $config['order_refunded'], $order, '' );
				$img = $config['order_refunded_img'];
			}
		}
		/* Admin Refunded Notification */
		if ( $status == 'Refunded' ) {
			$msg_admin   = $this->awp_wa_process_variables( $config['admin_refunded'], $order, '' );
			$img_admin   = $config['admin_refunded_img'];
			$phone_admin = preg_replace( '/[^0-9]/', '', $config['admin_number'] );
			if ( ! empty( $msg_admin ) ) {
				$this->awp_wa_send_msg( $config, $phone_admin, $msg_admin, $img_admin, '' );
			}
		}
		if ( ! empty( $msg ) ) {
			$result = $this->awp_wa_send_msg( $config, $phone, $msg, $img, '' );
			return $result;
		}
	}
	public function awp_wa_process_states_cancelled( $order ) {
		global $woocommerce, $custom_status_list_temp;
		$order       = new WC_Order( $order );
		$status      = $order->get_status();
		$status_list = array(
			'cancelled' => __( 'Cancelled', 'awp-send' ),
		);
		foreach ( $status_list as $status_lists => $translations ) {
			if ( $status == $status_lists ) {
				$status = $translations;
			}
		}
		$config = get_option( 'awp_notifications' );
		$phone  = $order->get_billing_phone();
		// Get the user's locale
		$user_locale = get_user_locale( get_current_user_id() );
		// Use different messages based on the user's locale
		if ( $status == __( 'Cancelled', 'awp-send' ) ) {
			if ( $user_locale == 'ar' ) {
				$msg = $this->awp_wa_process_variables( $config['order_cancelled_arabic'], $order, '' );
				$img = $config['order_cancelled_img_arabic'];
			} else {
				$msg = $this->awp_wa_process_variables( $config['order_cancelled'], $order, '' );
				$img = $config['order_cancelled_img'];
			}
		}
		/* Admin Cancelled Notification */
		if ( $status == 'Cancelled' ) {
			$msg_admin   = $this->awp_wa_process_variables( $config['admin_cancelled'], $order, '' );
			$img_admin   = $config['admin_cancelled_img'];
			$phone_admin = preg_replace( '/[^0-9]/', '', $config['admin_number'] );
			if ( ! empty( $msg_admin ) ) {
				$this->awp_wa_send_msg( $config, $phone_admin, $msg_admin, $img_admin, '' );
			}
		}
		if ( ! empty( $msg ) ) {
			$result = $this->awp_wa_send_msg( $config, $phone, $msg, $img, '' );
			return $result;
		}
	}
	
	public function awp_custom_order_status() {
		if ( $this->is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			global $custom_status_list_temp;
			$custom_status_list      = wc_get_order_statuses();
			$custom_status_list_temp = array();
			$original_status         = array(
				'pending',
				'failed',
				'on-hold',
				'processing',
				'completed',
				'refunded',
				'cancelled',
			);
			foreach ( $custom_status_list as $key => $status ) {
				$status_name = str_replace( 'wc-', '', $key );
				if ( ! in_array( $status_name, $original_status ) ) {
					$custom_status_list_temp[ $status ] = $status_name;
					add_action( 'woocommerce_order_status_' . $status_name, array( $this, 'awp_wa_process_states' ), 10 );
				}
			}
		}
	}
	
	public function awp_wa_process_note( $data ) {
		global $woocommerce;
		$order  = new WC_Order( $data['order_id'] );
		$config = get_option( 'awp_notifications' );
		$phone  = $order->get_billing_phone();
		$this->awp_wa_send_msg( $config, $phone, $this->awp_wa_process_variables( $config['order_note'], $order, '', wptexturize( $data['customer_note'] ) ), $config['order_note_img'], '' );
	}
    public function awp_wa_process_states( $order ) {
		global $woocommerce, $custom_status_list_temp;
		$order       = new WC_Order( $order );
		$status      = $order->get_status();
		$status_list = array();
		foreach ( $status_list as $status_lists => $translations ) {
			if ( $status == $status_lists ) {
				$status = $translations;
			}
		}
		$config             = get_option( 'awp_notifications' );
		$phone              = $order->get_billing_phone();
		$custom_status_list = $custom_status_list_temp;
		$msg                = '';
		$img                = '';
		if ( ! empty( $custom_status_list ) ) {
			foreach ( $custom_status_list as $status_name => $custom_status ) {
				if ( strtolower( $status ) == $custom_status ) {
					// Use different messages based on the user's locale
					$user_locale = get_user_locale( get_current_user_id() );

					if ( $user_locale == 'ar' ) {
						$msg = $this->awp_wa_process_variables( $config[ 'order_' . $custom_status . '_arabic' ], $order, '' );
						$img = $config[ 'order_' . $custom_status . '_img_arabic' ];
					} else {
						$msg = $this->awp_wa_process_variables( $config[ 'order_' . $custom_status ], $order, '' );
						$img = $config[ 'order_' . $custom_status . '_img' ];
					}
				}
			}
		}
		if ( ! empty( $msg ) ) {
			$result = $this->awp_wa_send_msg( $config, $phone, $msg, $img, '' );
			return $result;
		}
	}

    // Send WhatsApp message
    public function awp_wa_send_msg($config, $phone, $msg, $img, $resend) {
        global $result;
        $config = get_option('awp_notifications');
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (substr($phone, 0, 2) === '52') {
            if (substr($phone, 0, 3) !== '521') {
                $phone = '521' . substr($phone, 2);
            }
        }
        $instances = get_option('awp_instances');
        if (isset($instances['dashboard_prefix']) && isset($instances['access_token']) && isset($instances['instance_id'])) {
            $dashboard_prefix = $instances['dashboard_prefix'];
            $access_token = $instances['access_token'];
            $instance_id = $instances['instance_id'];
            $msg = $this->spintax($msg);
        } else {
            // handle error or provide default values
        }
        $msg = $this->spintax($msg);
        $instances = get_option('awp_instances');
        $dashboard_prefix = isset($instances['dashboard_prefix']) ? $instances['dashboard_prefix'] : '';
        $access_token = $instances['access_token'];
        $instance_id = $instances['instance_id'];
        if (empty($img)) {
            $url = 'https://app.wawp.net/api/send?number=' . $phone . '&type=text&message=' . urlencode($msg) . '&instance_id=' . $instance_id . '&access_token=' . $access_token;
            $rest_response = wp_remote_retrieve_body(
                wp_remote_get(
                    $url,
                    array(
                        'sslverify' => true,
                        'timeout' => 60,
                    )
                )
            );
        } else {
            $url = 'https://app.wawp.net/api/send?number=' . $phone . '&type=media&message=' . urlencode($msg) . '&media_url=' . $img . '&instance_id=' . $instance_id . '&access_token=' . $access_token;
            $rest_response = wp_remote_retrieve_body(
                wp_remote_get(
                    $url,
                    array(
                        'sslverify' => true,
                        'timeout' => 60,
                    )
                )
            );
        }
        $current_datetime = date(get_option('date_format') . ' ' . get_option('time_format'));
        $result = json_decode($rest_response, true);
        $this->log->add(
            'awpsend',
            '<tr><td>' . $current_datetime .
            '</td><td class="log-phone">' . $phone .
            '</td><td class="log-msg"><div>' . $msg . '</div></td><td class="log-img">' . $img . '</td>
            <td>' . $result["status"] . '</td> 
            <td style="max-height: 50px; overflow-y: auto;">' . (is_array($result['message']) ? json_encode($result['message']) : $result['message']) .
            '</td>
            <td><button type="button" class="button log-resend" data-instance-id="' . $instance_id . '" data-access-token="' . $access_token . '" data-phone="' . $phone . '" data-message="' . $msg . '" data-img="' . $img . '">Resend WhatsApp</button></td>
            </tr>'
        );
        if (empty($result['status'])) {
            $url = 'https://app.wawp.net/api/reconnect?instance_id=' . $instance_id . '&access_token=' . $access_token;
            $rest_response = wp_remote_retrieve_body(
                wp_remote_get(
                    $url,
                    array(
                        'sslverify' => true,
                        'timeout' => 60,
                    )
                )
            );
        }
        return $result['status'];
    }

    // Encode message
    public function awp_wa_encoding($msg) {
        return htmlentities($msg, ENT_QUOTES, 'UTF-8');
    }

    // Process message variables
    public function awp_wa_process_variables($msg, $order, $variables, $note = '') {
        global $wpdb, $woocommerce;
        $awp_wa = array('id', 'order_key', 'billing_first_name', 'billing_last_name', 'billing_company', 'billing_address_1', 'billing_address_2', 'billing_city', 'billing_postcode', 'billing_country', 'billing_state', 'billing_email', 'billing_phone', 'shipping_first_name', 'shipping_last_name', 'shipping_company', 'shipping_address_1', 'shipping_address_2', 'shipping_city', 'shipping_postcode', 'shipping_country', 'shipping_state', 'shipping_method', 'shipping_method_title', 'bacs_account', 'payment_method', 'payment_method_title', 'order_subtotal', 'order_discount', 'cart_discount', 'order_tax', 'order_shipping', 'order_shipping_tax', 'order_total', 'status', 'shop_name', 'currency', 'cust_note', 'note', 'product', 'product_name', 'dpd', 'unique_transfer_code', 'order_date', 'order_link');
        $variables = str_replace(array("\r\n", "\r"), "\n", $variables);
        $variables = explode("\n", $variables);
        preg_match_all('/{{(.*?)}}/', $msg, $search);
        $currency = get_woocommerce_currency_symbol();
        foreach ($search[1] as $variable) {
            $variable = strtolower($variable);
            // if (!in_array($variable, $awp_wa) && !in_array($variable, $variables)) continue;
            if ($variable != 'id' && $variable != 'shop_name' && $variable != 'currency' && $variable != 'shipping_method' && $variable != 'cust_note' && $variable != 'note' && $variable != 'bacs_account' && $variable != 'order_subtotal' && $variable != 'order_shipping' && $variable != 'product' && $variable != 'product_name' && $variable != 'dpd' && $variable != 'unique_transfer_code' && $variable != 'order_date' && $variable != 'order_link') {
                if (in_array($variable, $awp_wa)) {
                    $msg = str_replace('{{' . $variable . '}}', get_post_meta($order->get_id(), '_' . $variable, true), $msg);
                } elseif (strlen($order->order_custom_fields[$variable][0]) == 0) {
                    $msg = str_replace('{{' . $variable . '}}', get_post_meta($order->get_id(), $variable, true), $msg);
                } else {
                    $msg = str_replace('{{' . $variable . '}}', $order->order_custom_fields[$variable][0], $msg);
                }
            } elseif ($variable == 'id') {
                $msg = str_replace('{{' . $variable . '}}', $order->get_id(), $msg);
            } elseif ($variable == 'shop_name') {
                $msg = str_replace('{{' . $variable . '}}', get_bloginfo('name'), $msg);
            } elseif ($variable == 'currency') {
                $msg = str_replace('{{' . $variable . '}}', html_entity_decode($currency), $msg);
            } elseif ($variable == 'cust_note') {
                $msg = str_replace('{{' . $variable . '}}', $order->get_customer_note(), $msg);
            } elseif ($variable == 'shipping_method') {
                $msg = str_replace('{{' . $variable . '}}', $order->get_shipping_method(), $msg);
            } elseif ($variable == 'note') {
                $msg = str_replace('{{' . $variable . '}}', $note, $msg);
            } elseif ($variable == 'order_subtotal') {
                $msg = str_replace('{{' . $variable . '}}', number_format($order->get_subtotal(), wc_get_price_decimals()), $msg);
            } elseif ($variable == 'order_shipping') {
                $msg = str_replace('{{' . $variable . '}}', number_format(get_post_meta($order->get_id(), '_order_shipping', true), wc_get_price_decimals()), $msg);
            } elseif ($variable == 'dpd') {
                $order_id = $order->get_id();
                $table_name = $wpdb->prefix . 'dpd_orders';
                $parcels = $wpdb->get_results("SELECT id, parcel_number, date FROM $table_name WHERE order_id = $order_id AND (order_type != 'amazon_prime' OR order_type IS NULL ) AND status !='trash'");
                if (count($parcels) > 0) {
                    foreach ($parcels as $parcel) {
                        $dpd = $parcel->parcel_number;
                    }
                }
                $msg = str_replace('{{' . $variable . '}}', $dpd, $msg);
            } elseif ($variable == 'product') {
                $product_items = '';
                $order = wc_get_order($order->get_id());
                $i = 0;
                foreach ($order->get_items() as $item_id => $item_data) {
                    ++$i;
                    $new_line = ($i > 1) ? '\n' : '';
                    $product = $item_data->get_product();
                    $product_name = $product->get_name();
                    $item_quantity = $item_data->get_quantity();
                    $item_total = $item_data->get_total();
                    $product_items .= $new_line . $i . '. ' . $product_name . ' x ' . $item_quantity . ' = ' . $currency . ' ' . number_format($item_total, wc_get_price_decimals());
                }
                $msg = str_replace('{{' . $variable . '}}', html_entity_decode($product_items), $msg);
            } elseif ($variable == 'product_name') {
                $product_items = '';
                $order = wc_get_order($order->get_id());
                $i = 0;
                foreach ($order->get_items() as $item_id => $item_data) {
                    ++$i;
                    $new_line = ($i > 1) ? '\n' : '';
                    $product = $item_data->get_product();
                    $product_name = $product->get_name();
                    $product_items .= $new_line . $i . '. ' . $product_name;
                }
                $msg = str_replace('{{' . $variable . '}}', html_entity_decode($product_items), $msg);
            } elseif ($variable == 'unique_transfer_code') {
                $mtotal = get_post_meta($order->get_id(), '_order_total', true);
                $mongkir = get_post_meta($order->get_id(), '_order_shipping', true);
                $kode_unik = $mtotal - $mongkir;
                $msg = str_replace('{{' . $variable . '}}', $kode_unik, $msg);
            } elseif ($variable == 'order_date') {
                $order = wc_get_order($order->get_id());
                $date = $order->get_date_created();
                $date_format = get_option('date_format');
                $time_format = get_option('time_format');
                $msg = str_replace('{{' . $variable . '}}', date($date_format . ' ' . $time_format, strtotime($date)), $msg);
            } elseif ($variable == 'order_link') {
                $order_received_url = wc_get_endpoint_url('order-received', $order->get_id(), wc_get_checkout_url());
                $order_received_url = add_query_arg('key', $order->get_order_key(), $order_received_url);
                $msg = str_replace('{{' . $variable . '}}', $order_received_url, $msg);
            } elseif ($variable == 'bacs_account') {
                $gateway = new WC_Gateway_BACS();
                $country = WC()->countries->get_base_country();
                $locale = $gateway->get_country_locale();
                $bacs_info = get_option('woocommerce_bacs_accounts');
                $sort_code_label = isset($locale[$country]['sortcode']['label']) ? $locale[$country]['sortcode']['label'] : __('Sort code', 'woocommerce');
                $i = -1;
                $bacs_items = '';
                if ($bacs_info) {
                    foreach ($bacs_info as $account) {
                        ++$i;
                        $new_line = ($i > 0) ? '\n' : '';
                        $account_name = esc_attr(wp_unslash($account['account_name']));
                        $bank_name = esc_attr(wp_unslash($account['bank_name']));
                        $account_number = esc_attr($account['account_number']);
                        $sort_code = esc_attr($account['sort_code']);
                        $iban_code = esc_attr($account['iban']);
                        $bic_code = esc_attr($account['bic']);
                        $bacs_items .= $new_line . '🏦 ' . $bank_name . '\n' . '👤 ' . $account_name . '\n' . '🔢 ' . $account_number;
                    }
                }
                $msg = str_replace('{{' . $variable . '}}', $bacs_items, $msg);
            }
        }
        return $msg;
    }

    // Spintax for randomizing message content
    public function spintax($str) {
        return preg_replace_callback(
            '/{(.*?)}/',
            function($match) {
                $words = explode('|', $match[1]);
                return $words[array_rand($words)];
            },
            $str
        );
    }

    // Followup order function
    public function followup_order() {
        global $woocommerce;
        $config = get_option('awp_notifications');
        $customer_orders = wc_get_orders(
            array(
                'limit' => -1,
                'date_after' => date('Y-m-d', strtotime('-14 days')),
                'status' => 'on-hold',
            )
        );
        if (isset($customer_orders)) {
            $followup_send = array();
            foreach ($customer_orders as $order => $single_order) {
                $today = date_create(date('Y-m-d H:i:s'));
                $purchase_date = date_create($single_order->date_created->date('Y-m-d H:i:s'));
                $ts1 = strtotime($today->format('Y-m-d H:i:s'));
                $ts2 = strtotime($purchase_date->format('Y-m-d H:i:s'));
                $day_range = abs($ts1 - $ts2) / 3600;
                $followup_day = $config['followup_onhold_day'];

                if (empty($followup_day)) {
                    $followup_day = 24;
                }
                if ($day_range >= $followup_day) {
                    $sent = get_post_meta($single_order->ID, 'followup', true);
                    if (empty($sent) || $sent == null) {
                        update_post_meta($single_order->ID, 'followup', '0');
                    }
                    if ($sent == '0') {
                        echo esc_attr($single_order->ID) . ' = ' . esc_attr($sent) . '<br>';
                        $followup_send[] = $single_order->ID;
                    }
                }
            }
            if (count($followup_send) != 0) {
                foreach ($followup_send as $flw => $foll_id) {
                    $order = new WC_Order($foll_id);
                    $msg = $this->awp_wa_process_variables($config['followup_onhold'], $order, '');
                    $img = $config['followup_onhold_img'];
                    $phone = $order->get_billing_phone();
                    if (!empty($msg)) {
                        $this->awp_wa_send_msg($config, $phone, $msg, $img, '');
                    }
                    update_post_meta($foll_id, 'followup', '1');
                }
            }
        }
    }

    // Followup order 2 function
    public function followup_order_2() {
        global $woocommerce;
        $config = get_option('awp_notifications');
        $customer_orders = wc_get_orders(
            array(
                'limit' => -1,
                'date_after' => date('Y-m-d', strtotime('-14 days')),
                'status' => 'on-hold',
            )
        );
        if (isset($customer_orders)) {
            $followup_send_2 = array();
            foreach ($customer_orders as $order => $single_order) {
                $today = date_create(date('Y-m-d H:i:s'));
                $purchase_date = date_create($single_order->date_created->date('Y-m-d H:i:s'));
                $ts1 = strtotime($today->format('Y-m-d H:i:s'));
                $ts2 = strtotime($purchase_date->format('Y-m-d H:i:s'));
                $day_range = abs($ts1 - $ts2) / 3600;
                $followup_day = $config['followup_onhold_day_2'];

                if (empty($followup_day)) {
                    $followup_day = 48;
                }
                if ($day_range >= $followup_day) {
                    $sent = get_post_meta($single_order->ID, 'followup_2', true);
                    if (empty($sent) || $sent == null) {
                        update_post_meta($single_order->ID, 'followup_2', '0');
                    }
                    if ($sent == '0') {
                        echo esc_attr($single_order->ID) . ' = ' . esc_attr($sent) . '<br>';
                        $followup_send_2[] = $single_order->ID;
                    }
                }
            }
            if (count($followup_send_2) != 0) {
                foreach ($followup_send_2 as $flw => $foll_id) {
                    $order = new WC_Order($foll_id);
                    $msg = $this->awp_wa_process_variables($config['followup_onhold_2'], $order, '');
                    $img = $config['followup_onhold_img_2'];
                    $phone = $order->get_billing_phone();
                    if (!empty($msg)) {
                        $this->awp_wa_send_msg($config, $phone, $msg, $img, '');
                    }
                    update_post_meta($foll_id, 'followup_2', '1');
                }
            }
        }
    }

    // Followup order 3 function
    public function followup_order_3() {
        global $woocommerce;
        $config = get_option('awp_notifications');
        $customer_orders = wc_get_orders(
            array(
                'limit' => -1,
                'date_after' => date('Y-m-d', strtotime('-14 days')),
                'status' => 'on-hold',
            )
        );
        if (isset($customer_orders)) {
            $followup_send_3 = array();
            foreach ($customer_orders as $order => $single_order) {
                $today = date_create(date('Y-m-d H:i:s'));
                $purchase_date = date_create($single_order->date_created->date('Y-m-d H:i:s'));
                $ts1 = strtotime($today->format('Y-m-d H:i:s'));
                $ts2 = strtotime($purchase_date->format('Y-m-d H:i:s'));
                $day_range = abs($ts1 - $ts2) / 3600;
                $followup_day = $config['followup_onhold_day_3'];

                if (empty($followup_day)) {
                    $followup_day = 72;
                }
                if ($day_range >= $followup_day) {
                    $sent = get_post_meta($single_order->ID, 'followup_3', true);
                    if (empty($sent) || $sent == null) {
                        update_post_meta($single_order->ID, 'followup_3', '0');
                    }
                    if ($sent == '0') {
                        echo esc_attr($single_order->ID) . ' = ' . esc_attr($sent) . '<br>';
                        $followup_send_3[] = $single_order->ID;
                    }
                }
            }
            if (count($followup_send_3) != 0) {
                foreach ($followup_send_3 as $flw => $foll_id) {
                    $order = new WC_Order($foll_id);
                    $msg = $this->awp_wa_process_variables($config['followup_onhold_3'], $order, '');
                    $img = $config['followup_onhold_img_3'];
                    $phone = $order->get_billing_phone();
                    if (!empty($msg)) {
                        $this->awp_wa_send_msg($config, $phone, $msg, $img, '');
                    }
                    update_post_meta($foll_id, 'followup_3', '1');
                }
            }
        }
    }

    // Followup order 4 function
    public function followup_order_4() {
        global $woocommerce;
        $config = get_option('awp_notifications');
        $customer_orders = wc_get_orders(
            array(
                'limit' => -1,
                'date_after' => date('Y-m-d', strtotime('-14 days')),
                'status' => 'on-hold',
            )
        );
        if (isset($customer_orders)) {
            $followup_send_4 = array();
            foreach ($customer_orders as $order => $single_order) {
                $today = date_create(date('Y-m-d H:i:s'));
                $purchase_date = date_create($single_order->date_created->date('Y-m-d H:i:s'));
                $ts1 = strtotime($today->format('Y-m-d H:i:s'));
                $ts2 = strtotime($purchase_date->format('Y-m-d H:i:s'));
                $day_range = abs($ts1 - $ts2) / 3600;
                $followup_day = $config['followup_onhold_day_4'];

                if (empty($followup_day)) {
                    $followup_day = 96;
                }
                if ($day_range >= $followup_day) {
                    $sent = get_post_meta($single_order->ID, 'followup_4', true);
                    if (empty($sent) || $sent == null) {
                        update_post_meta($single_order->ID, 'followup_4', '0');
                    }
                    if ($sent == '0') {
                        echo esc_attr($single_order->ID) . ' = ' . esc_attr($sent) . '<br>';
                        $followup_send_4[] = $single_order->ID;
                    }
                }
            }
            if (count($followup_send_4) != 0) {
                foreach ($followup_send_4 as $flw => $foll_id) {
                    $order = new WC_Order($foll_id);
                    $msg = $this->awp_wa_process_variables($config['followup_onhold_4'], $order, '');
                    $img = $config['followup_onhold_img_4'];
                    $phone = $order->get_billing_phone();
                    if (!empty($msg)) {
                        $this->awp_wa_send_msg($config, $phone, $msg, $img, '');
                    }
                    update_post_meta($foll_id, 'followup_4', '1');
                }
            }
        }
    }

    // Aftersales order function
    public function aftersales_order() {
        global $woocommerce;
        $config = get_option('awp_notifications');
        $customer_orders = wc_get_orders(
            array(
                'limit' => -1,
                'date_after' => date('Y-m-d', strtotime('-14 days')),
                'status' => 'completed',
            )
        );
        if (isset($customer_orders)) {
            $aftersales_send = array();
            foreach ($customer_orders as $order => $single_order) {
                $today = date_create(date('Y-m-d H:i:s'));
                $purchase_date = date_create($single_order->date_created->date('Y-m-d H:i:s'));
                $paid_date_raw = date_format(date_create(get_post_meta($single_order->ID, '_completed_date', true)), 'Y-m-d H:i:s');
                $paid_date_obj = new DateTime();
                $paid_date = $paid_date_obj->createFromFormat('Y-m-d H:i:s', $paid_date_raw);
                $ts1 = strtotime($today->format('Y-m-d H:i:s'));
                $ts2 = strtotime($paid_date->format('Y-m-d H:i:s'));
                $day_range = abs($ts1 - $ts2) / 3600;

                $aftersales_day = $config['followup_aftersales_day'];
                if (empty($aftersales_day)) {
                    $aftersales_day = 48;
                }
                if ($day_range >= $aftersales_day) {
                    $sent = get_post_meta($single_order->ID, 'aftersales', true);
                    if (empty($sent) || $sent == null) {
                        update_post_meta($single_order->ID, 'aftersales', '0');
                    }
                    if ($sent == '0') {
                        $aftersales_send[] = $single_order->ID;
                    }
                }
            }
            if (count($aftersales_send) != 0) {
                foreach ($aftersales_send as $flw => $foll_id) {
                    $order = new WC_Order($foll_id);
                    $msg = $this->awp_wa_process_variables($config['followup_aftersales'], $order, '');
                    $img = $config['followup_aftersales_img'];
                    $phone = $order->get_billing_phone();
                    if (!empty($msg)) {
                        $this->awp_wa_send_msg($config, $phone, $msg, $img, '');
                    }
                    update_post_meta($foll_id, 'aftersales', '1');
                }
            }
        }
    }

    // Aftersales order 2 function
    public function aftersales_order_2() {
        global $woocommerce;
        $config = get_option('awp_notifications');
        $customer_orders = wc_get_orders(
            array(
                'limit' => -1,
                'date_after' => date('Y-m-d', strtotime('-14 days')),
                'status' => 'completed',
            )
        );
        if (isset($customer_orders)) {
            $aftersales_send_2 = array();
            foreach ($customer_orders as $order => $single_order) {
                $today = date_create(date('Y-m-d H:i:s'));
                $purchase_date = date_create($single_order->date_created->date('Y-m-d H:i:s'));
                $paid_date_raw = date_format(date_create(get_post_meta($single_order->ID, '_completed_date', true)), 'Y-m-d H:i:s');
                $paid_date_obj = new DateTime();
                $paid_date = $paid_date_obj->createFromFormat('Y-m-d H:i:s', $paid_date_raw);
                $ts1 = strtotime($today->format('Y-m-d H:i:s'));
                $ts2 = strtotime($paid_date->format('Y-m-d H:i:s'));
                $day_range = abs($ts1 - $ts2) / 3600;

                $aftersales_day_2 = $config['followup_aftersales_day_2'];
                if (empty($aftersales_day_2)) {
                    $aftersales_day_2 = 72;
                }
                if ($day_range >= $aftersales_day_2) {
                    $sent = get_post_meta($single_order->ID, 'aftersales_2', true);
                    if (empty($sent) || $sent == null) {
                        update_post_meta($single_order->ID, 'aftersales_2', '0');
                    }
                    if ($sent == '0') {
                        $aftersales_send_2[] = $single_order->ID;
                    }
                }
            }
            if (count($aftersales_send_2) != 0) {
                foreach ($aftersales_send_2 as $flw => $foll_id) {
                    $order = new WC_Order($foll_id);
                    $msg = $this->awp_wa_process_variables($config['followup_aftersales_2'], $order, '');
                    $img = $config['followup_aftersales_img_2'];
                    $phone = $order->get_billing_phone();
                    if (!empty($msg)) {
                        $this->awp_wa_send_msg($config, $phone, $msg, $img, '');
                    }
                    update_post_meta($foll_id, 'aftersales_2', '1');
                }
            }
        }
    }

    // Aftersales order 3 function
    public function aftersales_order_3() {
        global $woocommerce;
        $config = get_option('awp_notifications');
        $customer_orders = wc_get_orders(
            array(
                'limit' => -1,
                'date_after' => date('Y-m-d', strtotime('-14 days')),
                'status' => 'completed',
            )
        );
        if (isset($customer_orders)) {
            $aftersales_send_3 = array();
            foreach ($customer_orders as $order => $single_order) {
                $today = date_create(date('Y-m-d H:i:s'));
                $purchase_date = date_create($single_order->date_created->date('Y-m-d H:i:s'));
                $paid_date_raw = date_format(date_create(get_post_meta($single_order->ID, '_completed_date', true)), 'Y-m-d H:i:s');
                $paid_date_obj = new DateTime();
                $paid_date = $paid_date_obj->createFromFormat('Y-m-d H:i:s', $paid_date_raw);
                $ts1 = strtotime($today->format('Y-m-d H:i:s'));
                $ts2 = strtotime($paid_date->format('Y-m-d H:i:s'));
                $day_range = abs($ts1 - $ts2) / 3600;

                $aftersales_day_3 = $config['followup_aftersales_day_3'];
                if (empty($aftersales_day_3)) {
                    $aftersales_day_3 = 96;
                }
                if ($day_range >= $aftersales_day_3) {
                    $sent = get_post_meta($single_order->ID, 'aftersales_3', true);
                    if (empty($sent) || $sent == null) {
                        update_post_meta($single_order->ID, 'aftersales_3', '0');
                    }
                    if ($sent == '0') {
                        $aftersales_send_3[] = $single_order->ID;
                    }
                }
            }
            if (count($aftersales_send_3) != 0) {
                foreach ($aftersales_send_3 as $flw => $foll_id) {
                    $order = new WC_Order($foll_id);
                    $msg = $this->awp_wa_process_variables($config['followup_aftersales_3'], $order, '');
                    $img = $config['followup_aftersales_img_3'];
                    $phone = $order->get_billing_phone();
                    if (!empty($msg)) {
                        $this->awp_wa_send_msg($config, $phone, $msg, $img, '');
                    }
                    update_post_meta($foll_id, 'aftersales_3', '1');
                }
            }
        }
    }

    // Aftersales order 4 function
    public function aftersales_order_4() {
        global $woocommerce;
        $config = get_option('awp_notifications');
        $customer_orders = wc_get_orders(
            array(
                'limit' => -1,
                'date_after' => date('Y-m-d', strtotime('-14 days')),
                'status' => 'completed',
            )
        );
        if (isset($customer_orders)) {
            $aftersales_send_4 = array();
            foreach ($customer_orders as $order => $single_order) {
                $today = date_create(date('Y-m-d H:i:s'));
                $purchase_date = date_create($single_order->date_created->date('Y-m-d H:i:s'));
                $paid_date_raw = date_format(date_create(get_post_meta($single_order->ID, '_completed_date', true)), 'Y-m-d H:i:s');
                $paid_date_obj = new DateTime();
                $paid_date = $paid_date_obj->createFromFormat('Y-m-d H:i:s', $paid_date_raw);
                $ts1 = strtotime($today->format('Y-m-d H:i:s'));
                $ts2 = strtotime($paid_date->format('Y-m-d H:i:s'));
                $day_range = abs($ts1 - $ts2) / 3600;

                $aftersales_day_4 = $config['followup_aftersales_day_4'];
                if (empty($aftersales_day_4)) {
                    $aftersales_day_4 = 96;
                }
                if ($day_range >= $aftersales_day_4) {
                    $sent = get_post_meta($single_order->ID, 'aftersales_4', true);
                    if (empty($sent) || $sent == null) {
                        update_post_meta($single_order->ID, 'aftersales_4', '0');
                    }
                    if ($sent == '0') {
                        $aftersales_send_4[] = $single_order->ID;
                    }
                }
            }
            if (count($aftersales_send_4) != 0) {
                foreach ($aftersales_send_4 as $flw => $foll_id) {
                    $order = new WC_Order($foll_id);
                    $msg = $this->awp_wa_process_variables($config['followup_aftersales_4'], $order, '');
                    $img = $config['followup_aftersales_img_4'];
                    $phone = $order->get_billing_phone();
                    if (!empty($msg)) {
                        $this->awp_wa_send_msg($config, $phone, $msg, $img, '');
                    }
                    update_post_meta($foll_id, 'aftersales_4', '1');
                }
            }
        }
    }

    // Abandoned order function
    public function abandoned_order() {
        if ($this->is_plugin_active('woo-save-abandoned-carts/cartbounty-abandoned-carts.php')) {
            global $wpdb;
            $config = get_option('awp_notifications');
            $table_name = $wpdb->prefix . 'cartbounty';
            $ab_carts = $wpdb->get_results("SELECT * FROM $table_name WHERE other_fields != '1'");
            if (isset($ab_carts)) {
                foreach ($ab_carts as $ab_cart => $cart) {
                    $id = $cart->id;
                    $name = $cart->name;
                    $surname = $cart->surname;
                    $email = $cart->email;
                    // Check for an existing WooCommerce order by email
                    $orders = wc_get_orders(array('billing_email' => $email));
                    if (count($orders) > 0) {
                        // An order exists for this email, skip sending message
                        continue;
                    }
                    $phone = $cart->phone;
                    $total = $cart->cart_total;
                    $currency = $cart->currency;
                    $today = date_create(date('Y-m-d H:i:s'));
                    $abandoned_date_raw = date_format(date_create($cart->time), 'Y-m-d H:i:s');
                    $abandoned_date_obj = new DateTime();
                    $abandoned_date = $abandoned_date_obj->createFromFormat('Y-m-d H:i:s', $abandoned_date_raw);
                    $ts1 = strtotime($today->format('Y-m-d H:i:s'));
                    $ts2 = strtotime($abandoned_date->format('Y-m-d H:i:s'));
                    $day_range = round(abs($ts1 - $ts2) / 3600);
                    $abandoned_day = $config['followup_abandoned_day'];
                    $product_array = @unserialize($cart->cart_contents);
                    if ($product_array) {
                        $product_items = '';
                        $i = 0;
                        foreach ($product_array as $product) {
                            ++$i;
                            $new_line = ($i > 1) ? '\n' : '';
                            $product_name = $product['product_title'];
                            $item_quantity = $product['quantity'];
                            $item_total = $product['product_variation_price'];
                            $product_items .= $new_line . $i . '. ' . $product_name . ' x ' . $item_quantity . ' = ' . $currency . ' ' . $item_total;
                        }
                    }
                    if (empty($abandoned_day)) {
                        $abandoned_day = 24;
                    }
                    if ($day_range >= $abandoned_day) {
                        $replace_in_message = array('{{billing_first_name}}', '{{billing_last_name}}', '{{billing_email}}', '{{billing_phone}}', '{{product}}', '{{order_total}}', '{{currency}}');
                        $replace_with_message = array($name, $surname, $email, $phone, $product_items, $total, $currency);
                        $msg = str_replace($replace_in_message, $replace_with_message, $config['followup_abandoned']);
                        $img = $config['followup_abandoned_img'];
                        // Follow Up Abandoned Cart when status not shopping
                        $type = $cart->type;
                        $time = $cart->time;
                        $status = $cart->status;
                        $cart_time = strtotime($time);
                        $date = date_create(current_time('mysql', false));
                        $current_time = strtotime(date_format($date, 'Y-m-d H:i:s'));
                        if ($cart_time > $current_time - 60 * 60 && $item['type'] != 1) {
                            // Status is shopping
                            // Do nothing
                            // Source: woo-save-abandoned-carts/admin/class-cartbounty-admin-table.php:320
                        } else {
                            if (!empty($phone)) {
                                $this->awp_wa_send_msg($config, $phone, $msg, $img, '');
                            }
                            $wpdb->update($table_name, array('other_fields' => '1'), array('id' => $id));
                        }
                    }
                }
            }
        }
    }

    // Add custom cron schedules
    public function followup_cron_schedule($schedules) {
        $schedules['every_six_hours'] = array(
            'interval' => 21600,
            'display' => __('Every 6 hours'),
        );
        $schedules['every_half_hours'] = array(
            'interval' => 1800,
            'display' => __('Every 30 minutes'),
        );
        return $schedules;
    }

    // Add custom status to the admin bar
    public function status_on_admin_bar($wp_admin_bar) {
        $args = array(
            'id' => 'awp-admin-link',
            'title' => 'Wawp',
            'href' => admin_url() . 'admin.php?page=awp',
            'meta' => array(
                'class' => 'awp-admin-link',
            ),
        );
        $wp_admin_bar->add_node($args);
        $args = array(
            'id' => 'awp-sub-link-2',
            'title' => 'Wawp Notification',
            'href' => admin_url() . 'admin.php?page=awp',
            'parent' => 'awp-admin-link',
            'meta' => array(
                'class' => 'awp-admin-link',
            ),
        );
        $wp_admin_bar->add_node($args);
        $args = array(
            'id' => 'awp-sub-link-3',
            'title' => 'Wawp Otp',
            'href' => admin_url() . 'admin.php?page=awp-otp',
            'parent' => 'awp-admin-link',
            'meta' => array(
                'class' => 'awp-admin-link',
            ),
        );
        $wp_admin_bar->add_node($args);
        $args = array(
            'id' => 'awp-sub-link-4',
            'title' => 'Visit Wawp Dashboard',
            'href' => 'https://app.wawp.net/',
            'parent' => 'awp-admin-link',
            'meta' => array(
                'class' => 'awp-sub-link',
                'title' => 'Go to Wawp.net',
                'target' => '_blank',
            ),
        );
        $wp_admin_bar->add_node($args);
    }

    // Add billing phone field to edit account form
    public function add_billing_phone_to_edit_account_form() {
        $user_id = get_current_user_id();
        $billing_phone = get_user_meta($user_id, 'billing_phone', true);
        ?>
        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="billing_phone"><?php esc_html_e('Phone', 'woocommerce'); ?> <span class="required">*</span></label>
            <input type="tel" class="woocommerce-Input woocommerce-Input--text input-text" name="billing_phone" id="billing_phone" value="<?php echo esc_attr($billing_phone); ?>" />
        </p>
        <?php
    }

    // Save billing phone number on edit account
    public function save_billing_phone_on_edit_account($user_id) {
        if (isset($_POST['billing_phone'])) {
            update_user_meta($user_id, 'billing_phone', sanitize_text_field($_POST['billing_phone']));
        }
    }

    // Enqueue select2 script
    public function enqueue_select2() {
        wp_enqueue_script('select2', plugins_url('assets/js/resources/select2.js', __FILE__), array(), '4.1.0');
    }

    // Display billing phone number in the custom column
    public function display_billing_phone_content($content, $column_name, $user_id) {
        if ('billing_phone' === $column_name) {
            $customer = new WC_Customer($user_id);
            $billing_phone = $customer->get_billing_phone();

            if ($billing_phone) {
                $content = esc_html($billing_phone);
            } else {
                $content = '-';
            }
        }
        return $content;
    }


    // Schedule cron events if not already scheduled
    private function schedule_cron_events() {
        if (!wp_next_scheduled('followup_cron_hook')) {
            wp_schedule_event(time(), 'every_half_hours', 'followup_cron_hook');
        }
        if (!wp_next_scheduled('followup_cron_hook_2')) {
            wp_schedule_event(time(), 'daily', 'followup_cron_hook_2');
        }
        if (!wp_next_scheduled('followup_cron_hook_3')) {
            wp_schedule_event(time(), '+2 days', 'followup_cron_hook_3');
        }
        if (!wp_next_scheduled('followup_cron_hook_4')) {
            wp_schedule_event(time(), '+3 days', 'followup_cron_hook_4');
        }
        if (!wp_next_scheduled('aftersales_cron_hook')) {
            wp_schedule_event(time(), 'every_half_hours', 'aftersales_cron_hook');
        }
        if (!wp_next_scheduled('aftersales_cron_hook_2')) {
            wp_schedule_event(time(), 'daily', 'aftersales_cron_hook_2');
        }
        if (!wp_next_scheduled('aftersales_cron_hook_3')) {
            wp_schedule_event(time(), '+2 days', 'aftersales_cron_hook_4');
        }
        if (!wp_next_scheduled('aftersales_cron_hook_3')) {
            wp_schedule_event(time(), '+3 days', 'aftersales_cron_hook_4');
        }
        if (!wp_next_scheduled('abandoned_cron_hook')) {
            wp_schedule_event(time(), 'every_half_hours', 'abandoned_cron_hook');
        }
    }
}
?>
