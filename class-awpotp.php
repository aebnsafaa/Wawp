<?php
/**
 * Automation Web Platform Plugin.
 *
 * @package AutomationWebPlatform.
 */

ob_start();
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WWO.
 */
class WWO {
	/**
	 * Data variable to hold the instance of the class.
	 *
	 * @var WWO
	 */
	private static $instance;

	/**
	 * Instance ID.
	 *
	 * @var string
	 */
	private $instance_id;

	/**
	 * Access token.
	 *
	 * @var string
	 */
	private $access_token;

	/**
	 * Phone number.
	 *
	 * @var string
	 */
	private $phone;

	/**
	 * Message.
	 *
	 * @var string
	 */
	private $message;

	/**
	 * Get the single instance of the class.
	 *
	 * @return WWO
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Class constructor. Initializes settings and includes necessary files based on settings.
	 */
	public function __construct() {
		$settings = get_option( 'wwo_settings' );
		$access_token = isset( $settings['general']['access_token'] ) ? $settings['general']['access_token'] : null;
		$instance_id  = isset( $settings['general']['instance_id'] ) ? $settings['general']['instance_id'] : null;
		$this->set_access_token( $access_token );
		$this->set_instance_id( $instance_id );

		if ( isset( $settings['general']['active_login'] ) && 'on' === $settings['general']['active_login'] ) {
			include_once 'class-login.php';
			new Login();
		}
		if ( isset( $settings['general']['active_register'] ) && 'on' === $settings['general']['active_register'] ) {
			include_once 'class-register.php';
			new Register();
		}

		add_action( 'admin_menu', array( $this, 'settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
	}

	/**
	 * Add settings page to the admin menu.
	 */
	public function settings() {
		$hook = add_menu_page(
			'Wawp OTP',
			'Wawp OTP',
			'manage_options',
			'awp-otp',
			array( $this, 'setting_page' ),
			WWO_URL . 'assets/img/menu.png',
			101
		);
		remove_menu_page( 'awp-otp' );
	}

	/**
	 * Display the settings page content.
	 */
	public function setting_page() {
		include 'admin/wc-setting-page.php';
	}

	/**
	 * Enqueue admin scripts and styles.
	 */
	public function enqueue() {
		global $pagenow;
		if ( is_admin() ) {
			add_action( 'admin_head', array( $this, 'wawp_consent_banner' ) );
			add_action( 'admin_footer', array( $this, 'wawp_add_banner_script' ) );
			add_action( 'admin_head', array( $this, 'wawp_consent_footer' ) );
			add_action( 'admin_footer', array( $this, 'wawp_add_footer_script' ) );
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( 'admin.php' === $pagenow && isset( $_GET['page'] ) && 'awp-otp' === $_GET['page'] ) :
			wp_enqueue_style( 'bootstrap-css', plugins_url( '/assets/css/resources/bootstrap.min.css', __FILE__ ), array(), '5.2.3' );
			wp_enqueue_style( 'bootstrap-icons-css', plugins_url( '/assets/css/resources/bootstrap-icons.css', __FILE__ ), array(), '1.8.1' );
			wp_enqueue_style( 'bootstrap-table-css', plugins_url( '/assets/css/resources/bootstrap-table.min.css', __FILE__ ), array(), '1.21.1' );
			wp_enqueue_style( 'sweetalert2-css', plugins_url( '/assets/css/resources/sweetalert2.min.css', __FILE__ ), array(), '11.4.35' );
			wp_enqueue_style( 'jquery-ui-css', plugins_url( '/assets/css/resources/jquery-ui.css', __FILE__ ), array(), '1.13.2' );
			wp_enqueue_style( 'lineicons-css', plugins_url( '/assets/css/resources/lineicons.css', __FILE__ ), array(), '3.0' );
			wp_enqueue_style( 'select2', plugins_url( '/assets/css/resources/select2.min.css', __FILE__ ), array(), '4.1.0' );
			wp_enqueue_style( 'admin', plugins_url( '/assets/css/admin.css', __FILE__ ), array(), '4.1.0' );
			wp_enqueue_script( 'jquery-js', plugins_url( 'assets/js/resources/jquery.min.js', __FILE__ ), array(), '3.6.0', true );
			wp_enqueue_script( 'jquery-ui-js', plugins_url( 'assets/js/resources/jquery-ui.js', __FILE__ ), array(), '1.13.2', true );
			wp_enqueue_script( 'bootstrap-js', plugins_url( 'assets/js/resources/bootstrap.bundle.min.js', __FILE__ ), array(), '5.2.3', true );
			wp_enqueue_script( 'bootstrap-table-js', plugins_url( 'assets/js/resources/bootstrap-table.min.js', __FILE__ ), array(), true, true, '1.21.1' );
			wp_enqueue_script( 'sweetalert2-js', plugins_url( 'assets/js/resources/sweetalert2.min.js', __FILE__ ), array(), true, true, '11.4.35' );
			wp_enqueue_script( 'select2', plugins_url( 'assets/js/resources/select2.js', __FILE__ ), array(), true, true, '4.1.0' );

			wp_enqueue_script( 'admin-script', WWO_URL . '/assets/js/admin.js', array( 'jquery' ), true, true );
			if ( is_rtl() ) {
				// Enqueue RTL versions of your CSS files here.
				wp_enqueue_style( 'otp-rtl-css', plugins_url( '/assets/css/otp-rtl.css', __FILE__ ), array(), '5.2.3' );
				// You can enqueue more RTL CSS files as needed.
			}
			// Localize the script with new data.
			$script_data_array = array(
				'ajaxurl'     => admin_url( 'admin-ajax.php' ),
				'admin_nonce' => wp_create_nonce( 'wwo_nonce' ),
			);
			wp_localize_script( 'ajax-script', 'wwo', $script_data_array );
			// Enqueued script with localized data.
			wp_enqueue_script( 'ajax-script' );
		endif;
	}

	/**
	 * Set instance ID method.
	 *
	 * @param string $data Instance ID.
	 */
	public function set_instance_id( $data ) {
		$this->instance_id = $data;
	}

	/**
	 * Set access token method.
	 *
	 * @param string $data Access token.
	 */
	public function set_access_token( $data ) {
		$this->access_token = $data;
	}

	/**
	 * Display the consent banner on specific admin pages.
	 */
	public function wawp_consent_banner() {
		$current_screen = get_current_screen();
		$allowed_pages = array(
			'toplevel_page_awp-settings', 
			'toplevel_page_awp-countrycode', 
			'toplevel_page_awp', 
			'toplevel_page_awp-otp', 
			'toplevel_page_awp-checkout-otp', 
			'awp_page_awp-message-log',
			'toplevel_page_awp-system-status-info'
		);

		if ( ! in_array( $current_screen->id, $allowed_pages ) && ! ( isset( $_GET['page'] ) && $_GET['page'] === 'awp-message-log' ) ) {
			return;
		}

		echo '<div id="wawp-consent-banner">' .
				'<div class="wawp-banner"><p class="promo">' . __('Upgrade Now to Wawp Pro. Use the code <strong>“Wawp50”</strong> to get 50% off.', 'awp') . '</p></div>' .
				'<div class="wawp-header">' .
					'<div class="wawp-topbar">' .
						'<div class="wawp-logo">' .
							'<a href="https://wawp.net" title="Wawp" target="_blank"><img style="height: 54px;" src="' . plugins_url('assets/img/wawp-logo.png', __FILE__) . '"></a>' .
							'<h1 class="title-text">' . get_admin_page_title() . '</h1>' .
						'</div>' .
						'<div class="wawp-links">' .
							'<div class="links-box">' .
								'<a href="https://wawp.net/whatsapp-text-formatter/" target="_blank" class="hint-btn">' . __('Text formatting', 'awp') . '</a>' .
								'<a href="https://www.facebook.com/groups/894016848870156" target="_blank" class="hint-btn">' . __('Support', 'awp') . '</a>' .
								'<a href="https://wawp.net/pricing/" target="_blank" class="hint-btn pro"><img style="margin-right: 6px;" src="' . plugins_url('assets/img/star.png', __FILE__) . '">' . __('Try Wawp Pro', 'awp') . '</a>' .
							'</div>' .
						'</div>' .
					'</div>' .
				'</div>' .
			'</div>';
	}

	/**
	 * Add script to move the consent banner to the top of the admin content.
	 */
	public function wawp_add_banner_script() {
		?>
		<script type="text/javascript">
			document.addEventListener('DOMContentLoaded', function() {
				var banner = document.getElementById('wawp-consent-banner');
				if (banner) {
					var wpcontent = document.getElementById('wpcontent');
					if (wpcontent) {
						wpcontent.insertBefore(banner, wpcontent.firstChild);
					}
				}
			});
		</script>
		<?php
	}

	/**
	 * Display the consent footer on specific admin pages.
	 */
	public function wawp_consent_footer() {
		$current_screen = get_current_screen();
		$allowed_pages = array(
			'toplevel_page_awp-settings', 
			'toplevel_page_awp-countrycode', 
			'toplevel_page_awp', 
			'toplevel_page_awp-otp', 
			'toplevel_page_awp-checkout-otp', 
			'awp_page_awp-message-log',
			'toplevel_page_awp-system-status-info'
		);

		if ( ! in_array( $current_screen->id, $allowed_pages ) && ! ( isset( $_GET['page'] ) && $_GET['page'] === 'awp-message-log' ) ) {
			return;
		}

		echo '<div id="wawp-consent-footer">' .
				'<div id="footer" role="contentinfo">' .
					'<div class="Wawp-footer-promotion">' .
						'<p>' . __('Made with ♥ by the Wawp Team', 'awp') . '</p>' .
						'<div class="Wawp-footer-social">' .
							'<a href="https://www.facebook.com/wawpapp" target="_blank" rel="noopener noreferrer">' .
								'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M12.001 2C6.47813 2 2.00098 6.47715 2.00098 12C2.00098 16.9913 5.65783 21.1283 10.4385 21.8785V14.8906H7.89941V12H10.4385V9.79688C10.4385 7.29063 11.9314 5.90625 14.2156 5.90625C15.3097 5.90625 16.4541 6.10156 16.4541 6.10156V8.5625H15.1931C13.9509 8.5625 13.5635 9.33334 13.5635 10.1242V12H16.3369L15.8936 14.8906H13.5635V21.8785C18.3441 21.1283 22.001 16.9913 22.001 12C22.001 6.47715 17.5238 2 12.001 2Z"></path></svg>' .
								'<span class="screen-reader-text">Facebook</span>' .
							'</a>' .
							'<a href="https://twitter.com/wawpapp" target="_blank" rel="noopener noreferrer">' .
								'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M22.2125 5.65605C21.4491 5.99375 20.6395 6.21555 19.8106 6.31411C20.6839 5.79132 21.3374 4.9689 21.6493 4.00005C20.8287 4.48761 19.9305 4.83077 18.9938 5.01461C18.2031 4.17106 17.098 3.69303 15.9418 3.69434C13.6326 3.69434 11.7597 5.56661 11.7597 7.87683C11.7597 8.20458 11.7973 8.52242 11.8676 8.82909C8.39047 8.65404 5.31007 6.99005 3.24678 4.45941C2.87529 5.09767 2.68005 5.82318 2.68104 6.56167C2.68104 8.01259 3.4196 9.29324 4.54149 10.043C3.87737 10.022 3.22788 9.84264 2.64718 9.51973C2.64654 9.5373 2.64654 9.55487 2.64654 9.57148C2.64654 11.5984 4.08819 13.2892 6.00199 13.6731C5.6428 13.7703 5.27232 13.8194 4.90022 13.8191C4.62997 13.8191 4.36771 13.7942 4.11279 13.7453C4.64531 15.4065 6.18886 16.6159 8.0196 16.6491C6.53813 17.8118 4.70869 18.4426 2.82543 18.4399C2.49212 18.4402 2.15909 18.4205 1.82812 18.3811C3.74004 19.6102 5.96552 20.2625 8.23842 20.2601C15.9316 20.2601 20.138 13.8875 20.138 8.36111C20.138 8.1803 20.1336 7.99886 20.1256 7.81997C20.9443 7.22845 21.651 6.49567 22.2125 5.65605Z"></path></svg>' .
								'<span class="screen-reader-text">Twitter</span>' .
							'</a>' .
							'<a href="https://www.youtube.com/@wawpapp" target="_blank" rel="noopener noreferrer">' .
								'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M12.2439 4C12.778 4.00294 14.1143 4.01586 15.5341 4.07273L16.0375 4.09468C17.467 4.16236 18.8953 4.27798 19.6037 4.4755C20.5486 4.74095 21.2913 5.5155 21.5423 6.49732C21.942 8.05641 21.992 11.0994 21.9982 11.8358L21.9991 11.9884L21.9991 11.9991C21.9991 11.9991 21.9991 12.0028 21.9991 12.0099L21.9982 12.1625C21.992 12.8989 21.942 15.9419 21.5423 17.501C21.2878 18.4864 20.5451 19.261 19.6037 19.5228C18.8953 19.7203 17.467 19.8359 16.0375 19.9036L15.5341 19.9255C14.1143 19.9824 12.778 19.9953 12.2439 19.9983L12.0095 19.9991L11.9991 19.9991C11.9991 19.9991 11.9956 19.9991 11.9887 19.9991L11.7545 19.9983C10.6241 19.9921 5.89772 19.941 4.39451 19.5228C3.4496 19.2573 2.70692 18.4828 2.45587 17.501C2.0562 15.9419 2.00624 12.8989 2 12.1625V11.8358C2.00624 11.0994 2.0562 8.05641 2.45587 6.49732C2.7104 5.51186 3.45308 4.73732 4.39451 4.4755C5.89772 4.05723 10.6241 4.00622 11.7545 4H12.2439ZM9.99911 8.49914V15.4991L15.9991 11.9991L9.99911 8.49914Z"></path></svg>' .
								'<span class="screen-reader-text">YouTube</span>' .
							'</a>' .
						'</div>' .
					'</div>' .
					'<p id="footer-left">' .
						sprintf(
							__('Please rate <strong>Wawp</strong> <a href="%s" target="_blank" rel="noopener noreferrer">★★★★★</a> on <a href="%s" target="_blank" rel="noopener noreferrer">WordPress.org</a> to help us spread the word.', 'awp'), 
							esc_url('https://wordpress.org/support/plugin/automation-web-platform/reviews/?filter=5#new-post'), 
							esc_url('https://wordpress.org/support/plugin/automation-web-platform/reviews/?filter=5#new-post')
						) .
					'</p>' .
				'</div>' .
			'</div>';
	}

	/**
	 * Add script to move the consent footer to the bottom of the admin content.
	 */
	public function wawp_add_footer_script() {
		?>
		<script type="text/javascript">
			document.addEventListener('DOMContentLoaded', function() {
				var footer = document.getElementById('wawp-consent-footer');
				if (footer) {
					var wpcontent = document.getElementById('wpcontent');
					if (wpcontent) {
						wpcontent.appendChild(footer);
					}
				}
			});
		</script>
		<?php
	}
}
?>
