<?php
/**
 * Automation Web Platform Plugin
 *
 * @package AutomationWebPlatform
 */

if ( ! isset( $_POST['action'] ) ) {
	echo '0';
	exit;
}

add_action( 'admin_init', 'awp_plugin_update_check' );

/**
 * Awp plugin update check.
 */
function awp_plugin_update_check() {
	$connection = json_decode( get_option( 'wnt_connection' ), true );

	if ( ! isset( $connection['data']['downloadable']['name'], $connection['data']['downloadable']['url'] ) ) {
		return;
	}

	$obj              = new stdClass();
	$obj->slug        = 'awp/awp.php';
	$obj->name        = 'awp';
	$obj->plugin_name = 'awp.php';
	$obj->new_version = substr( $connection['data']['downloadable']['name'], 1 );
	$obj->url         = 'https://wawp.net';
	$obj->package     = $connection['data']['downloadable']['url'];

	switch ( sanitize_text_field( $_POST['action'] ) ) {
		case 'version':
			echo serialize( $obj );
			break;
		case 'info':
			$obj->requires      = '3.0';
			$obj->tested        = '6.4.3';
			$obj->downloaded    = 10000;
			$obj->last_updated  = '2024-02-10';
			$obj->sections      = array(
				'description' => 'Automated Whatsapp Order Notifications & OTP Verification for WooCommerce',
				'changelog'   => 'View wawp site (https://wawp.net) for changelogs',
			);
			$obj->download_link = $obj->package;
			echo serialize( $obj );
			break;
		case 'license':
			echo serialize( $obj );
			break;
	}
	exit;
}
