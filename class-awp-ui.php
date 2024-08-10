<?php
/**
 * Automation Web Platform Plugin.
 *
 * @package AutomationWebPlatform.
 */


class awp_UI {


	public function __construct() {
		$this->notif     = get_option( 'awp_notifications' );
		$this->instances = get_option( 'awp_instances' );
	}

	public function is_plugin_active( $plugin ) {
		return in_array( $plugin, (array) get_option( 'active_plugins', array() ) );
	}

	public function admin_page() {
		?>
		<div class="wrap" id="awp-wrap">
			<div class="form-wrapper">
				<div class="awp-tab-wrapper">
					<ul class="nav-tab-wrapper woo-nav-tab-wrapper">
						<li name="awp_notifications[notification-message]" class="nav-tab nav-tab-active"><a href="#notification"><?php esc_html_e( 'Customer notifications', 'awp' ); ?></a></li>
					
						<li name="awp_admin_notifications[admin_notification-message]" class="nav-tab"><a href="#admin-notification"><?php esc_html_e( 'Admin notifications', 'awp' ); ?></a></li>
																
						
						<li class="nav-tab"><a href="#followup"><?php esc_html_e( 'Follow up', 'awp' ); ?></a></li>
						
						<li class="nav-tab"><a href="#abandoned-cart"><?php esc_html_e( 'Abandoned cart', 'awp' ); ?></a></li>

						<li class="nav-tab"><a href="#help"><?php esc_html_e( 'Quick message', 'awp' ); ?></a></li>
						
					</ul>
					<form method="post" action="options.php">
						<div class="wp-tab-panels" id="notification">
								<?php
									$this->notification_settings();
								?>
						</div>
						<div class="wp-tab-panels" id="admin-notification" style="display: none;">
								<?php
									$this->admin_notification_settings();
								?>
						</div>
						<div class="wp-tab-panels" id="followup" style="display: none;">
								<?php
									$this->followup_settings();
								?>
						</div>
						<div class="wp-tab-panels" id="abandoned-cart" style="display: none;">
								<?php
									$this->abandoned_cart_settings();
								?>
						</div>
						<div class="wp-tab-panels" id="other" style="display: none;">
								<?php
									$this->other_settings();
								?>
						</div>
					<div class="wp-tab-panels" id="help" style="display: none;">
					</form>
			<?php
			$this->help_info();
			?>
					</div>
				</div>                
				<div class="info" style="display: none;">
			<?php
			$this->setup_info();
			?>
											</div>
			</div>
		</div>
        <?php
	}

	public function notification_settings() {
		if ( $this->is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			$status_list      = wc_get_order_statuses();
			$status_list_temp = array();
			$original_status  = array(
				'pending',
				'failed',
				'on-hold',
				'processing',
				'completed',
				'refunded',
				'cancelled',
			);
			foreach ( $status_list as $key => $status ) {
				$status_name = str_replace( 'wc-', '', $key );
				if ( ! in_array( $status_name, $original_status ) ) {
					$status_list_temp[ $status ] = $status_name;
				}
			}
			$status_list = $status_list_temp;
		}
		?>
		
		<?php settings_fields( 'awp_storage_notifications' ); ?>

			<div class="info-banner">
    			<p class="banner-text"><?php esc_html_e( 'Send real-time WhatsApp messages to your customers based on order status changes or new order creations.', 'awp' ); ?></p>
    			<input type="submit" class="button-primarywa saveit top" value="<?php esc_html_e( 'Save Changes', 'awp' ); ?>">
			</div>
			<hr class="line">

			
			
				<div class="notification-form" style="display: none;">
				<div class="heading-bar">
				<label for="awp_notifications[default_country]" class="notification-title"><?php esc_html_e( 'Default Country Code:', 'awp' ); ?></label>
				</div>
				<p class="deactive-hint"><em><?php echo esc_html__( 'Add your country code without any 00 or + ex: 2 for EG or 966 for SA  ', 'awp' ); ?></em></p>
				<br>
				<div class="notification">
						<div class="phone-field">
							<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" class="phone-icon"><path d="M12 2C6.486 2 2 6.486 2 12s4.486 10 10 10 10-4.486 10-10S17.514 2 12 2zm7.931 9h-2.764a14.67 14.67 0 0 0-1.792-6.243A8.013 8.013 0 0 1 19.931 11zM12.53 4.027c1.035 1.364 2.427 3.78 2.627 6.973H9.03c.139-2.596.994-5.028 2.451-6.974.172-.01.344-.026.519-.026.179 0 .354.016.53.027zm-3.842.7C7.704 6.618 7.136 8.762 7.03 11H4.069a8.013 8.013 0 0 1 4.619-6.273zM4.069 13h2.974c.136 2.379.665 4.478 1.556 6.23A8.01 8.01 0 0 1 4.069 13zm7.381 6.973C10.049 18.275 9.222 15.896 9.041 13h6.113c-.208 2.773-1.117 5.196-2.603 6.972-.182.012-.364.028-.551.028-.186 0-.367-.016-.55-.027zm4.011-.772c.955-1.794 1.538-3.901 1.691-6.201h2.778a8.005 8.005 0 0 1-4.469 6.201z"></path></svg>
							<input type="text" name="awp_notifications[default_country]" placeholder="<?php echo esc_attr__( 'Your country code', 'awp' ); ?>" class="admin_number regular-text admin_number upload-text" value="<?php echo esc_attr( isset( $this->notif['default_country'] ) ? $this->notif['default_country'] : '' ); ?>">
						</div>
				</div>
				<p class="deactive-hint"><em><?php echo esc_html__( 'Insert country code only if your customer is from a single country. This will remove the country detection library on the old checkout page. Leave blank if your customer is from many countries.', 'awp' ); ?></em></p>
			</div>



<!-- Add tabs for Arabic and English editors -->
<div class="editor-tabs">
    <h3 class="editor-title"><?php _e('Customer language:', 'awp'); ?></h3>
    <div class="editor-tab" data-lang="english"><?php _e('Default', 'awp'); ?></div>
    <div class="editor-tab" data-lang="arabic"><?php _e('Arabic', 'awp'); ?></div>
</div>


<div class="editor-content layout-en" data-lang="english">
<?php
$blank = __("leave blank to deactivate", "awp");
$txt_placeholder = __("Write your message...", "awp");
$img_format = __("Accepts .png, .jpg, .jpeg", "awp");
$upload_btn = __("Upload image", "awp");
?>

	
	<div class="notification-form english hint">
		<div class="hint-box">
			<label for="awp_notifications" class="hint-title"><?php esc_html_e( 'Order status notifications', 'awp' ); ?></label>
			<p class="hint-desc"><?php esc_html_e( 'Automatically send notification messages based on the primary language of the user’s WordPress account, including English, French, Italian, German, Spanish, Hindi, or any LTR language.', 'awp' ); ?></p>
		</div>
	</div>
	
	
	<div class="notification-form english">
		<div class="heading-bar">
			<label for="awp_notifications[order_onhold]" class="notification-title"><?php esc_html_e( 'Order on hold', 'awp' ); ?>
                <span class="tooltip-text"><?php esc_html_e( 'Sent when an order is awaiting payment confirmation.', 'awp' ); ?></span>
            </label>
			<p class="deactive-hint"><em><?php esc_html_e( $blank, 'awp' ); ?></em></p>
		</div>
		<hr class="line">
		<div class="notification">
			<div class="form">
			    <div class="dropdowns">
			        <!-- Placeholder Dropdown Container -->
                    <div class="placeholder-container"></div>
                    <!-- Placeholder Dropdown Container (Message Templates) -->
                    <div class="message-template-container"></div>
                </div>
				<!-- Add textareas for  English messages -->
				<textarea id="awp_notifications[order_onhold]" name="awp_notifications[order_onhold]" cols="50" rows="5" class="awp-emoji" placeholder="<?php esc_html_e( $txt_placeholder, 'awp' ); ?>"><?php echo isset( $this->notif['order_onhold'] ) ? esc_textarea( $this->notif['order_onhold'] ) : ''; ?></textarea>
				<div class="upload-field">
					<input type="text" name="awp_notifications[order_onhold_img]" placeholder="<?php esc_html_e( $img_format, 'awp' ); ?>" class="image_url regular-text order_onhold_img upload-text" value="<?php echo esc_attr( isset( $this->notif['order_onhold_img'] ) ? $this->notif['order_onhold_img'] : '' ); ?>">
					<input type="button" name="upload-btn" value="<?php esc_html_e( $upload_btn, 'awp' ); ?>" class="upload-btn" data-id="order_onhold_img">
				</div>
			</div>
		</div>
	</div>

	<div class="notification-form english">
		<div class="heading-bar">
			<label for="awp_notifications[order_processing]" class="notification-title"><?php esc_html_e( 'Order processing', 'awp' ); ?>
                <span class="tooltip-text"><?php esc_html_e( 'Sent when you mark an order as awaiting fulfillment. ', 'awp' ); ?></span>
            </label>
			<p class="deactive-hint"><em><?php esc_html_e( $blank, 'awp' ); ?></em></p>
		</div>
		<hr class="line">
		<div class="notification">
			<div class="form">
			    <div class="dropdowns">
			        <!-- Placeholder Dropdown Container -->
                    <div class="placeholder-container"></div>
                    <!-- Placeholder Dropdown Container (Message Templates) -->
                    <div class="message-template-container"></div>
                </div>
				<!-- Add textareas for  English messages -->
				<textarea id="awp_notifications[order_processing]" name="awp_notifications[order_processing]" cols="50" rows="5" class="awp-emoji" placeholder="<?php esc_html_e( $txt_placeholder, 'awp' ); ?>"><?php echo isset( $this->notif['order_processing'] ) ? esc_textarea( $this->notif['order_processing'] ) : ''; ?></textarea>
				<div class="upload-field">
					<input type="text" name="awp_notifications[order_processing_img]" placeholder="<?php esc_html_e( $img_format, 'awp' ); ?>" class="image_url regular-text order_processing_img upload-text" value="<?php echo esc_attr( isset( $this->notif['order_processing_img'] ) ? $this->notif['order_processing_img'] : '' ); ?>">
					<input type="button" name="upload-btn" value="<?php esc_html_e( $upload_btn, 'awp' ); ?>" class="upload-btn" data-id="order_processing_img">
				</div>
			</div>
		</div>
	</div>
	
	<div class="notification-form english">
		<div class="heading-bar">
			<label for="awp_notifications[order_completed]" class="notification-title"><?php esc_html_e( 'Order completed', 'awp' ); ?>
                <span class="tooltip-text"><?php esc_html_e( 'Sent when an order is delivered.', 'awp' ); ?></span>
            </label>
			<p class="deactive-hint"><em><?php esc_html_e( $blank, 'awp' ); ?></em></p>
		</div>
		<hr class="line">
		<div class="notification">
			<div class="form">
			    <div class="dropdowns">
			        <!-- Placeholder Dropdown Container -->
                    <div class="placeholder-container"></div>
                    <!-- Placeholder Dropdown Container (Message Templates) -->
                    <div class="message-template-container"></div>
                </div>
				<!-- Add textareas for  English messages -->
				<textarea id="awp_notifications[order_completed]" name="awp_notifications[order_completed]" cols="50" rows="5" class="awp-emoji" placeholder="<?php esc_html_e( $txt_placeholder, 'awp' ); ?>"><?php echo isset( $this->notif['order_completed'] ) ? esc_textarea( $this->notif['order_completed'] ) : ''; ?></textarea>
				<div class="upload-field">
					<input type="text" name="awp_notifications[order_completed_img]" placeholder="<?php esc_html_e( $img_format, 'awp' ); ?>" class="image_url regular-text order_completed_img upload-text" value="<?php echo esc_attr( isset( $this->notif['order_completed_img'] ) ? $this->notif['order_completed_img'] : '' ); ?>">
					<input type="button" name="upload-btn" value="<?php esc_html_e( $upload_btn, 'awp' ); ?>" class="upload-btn" data-id="order_completed_img">
				</div>
			</div>
		</div>
	</div>

	<div class="notification-form english">
		<div class="heading-bar">
			<label for="awp_notifications[order_pending]" class="notification-title"><?php esc_html_e( 'Order pending payment', 'awp' ); ?>
                <span class="tooltip-text"><?php esc_html_e( "Sent when a customer's pending payment can't be processed.", 'awp' ); ?></span>
            </label>
			<p class="deactive-hint"><em><?php esc_html_e( $blank, 'awp' ); ?></em></p>
		</div>
		<hr class="line">
		<div class="notification">
			<div class="form">
			    <div class="dropdowns">
			        <!-- Placeholder Dropdown Container -->
                    <div class="placeholder-container"></div>
                    <!-- Placeholder Dropdown Container (Message Templates) -->
                    <div class="message-template-container"></div>
                </div>
				<!-- Add textareas for  English messages -->
				<textarea id="awp_notifications[order_pending]" name="awp_notifications[order_pending]" cols="50" rows="5" class="awp-emoji" placeholder="<?php esc_html_e( $txt_placeholder, 'awp' ); ?>"><?php echo isset( $this->notif['order_pending'] ) ? esc_textarea( $this->notif['order_pending'] ) : ''; ?></textarea>
				<div class="upload-field">
					<input type="text" name="awp_notifications[order_pending_img]" placeholder="<?php esc_html_e( $img_format, 'awp' ); ?>" class="image_url regular-text order_pending_img upload-text" value="<?php echo esc_attr( isset( $this->notif['order_pending_img'] ) ? $this->notif['order_pending_img'] : '' ); ?>">
					<input type="button" name="upload-btn" value="<?php esc_html_e( $upload_btn, 'awp' ); ?>" class="upload-btn" data-id="order_pending_img">
				</div>
			</div>
		</div>
	</div>

	<div class="notification-form english">
		<div class="heading-bar">
			<label for="awp_notifications[order_failed]" class="notification-title"><?php esc_html_e( 'Order failed', 'awp' ); ?>
                <span class="tooltip-text"><?php esc_html_e( "Sent when a customer's payment can't be processed during checkout.", 'awp' ); ?></span>
            </label>
			<p class="deactive-hint"><em><?php esc_html_e( $blank, 'awp' ); ?></em></p>
		</div>
		<hr class="line">
		<div class="notification">
			<div class="form">
			    <div class="dropdowns">
			        <!-- Placeholder Dropdown Container -->
                    <div class="placeholder-container"></div>
                    <!-- Placeholder Dropdown Container (Message Templates) -->
                    <div class="message-template-container"></div>
                </div>
				<!-- Add textareas for  English messages -->
				<textarea id="awp_notifications[order_failed]" name="awp_notifications[order_failed]" cols="50" rows="5" class="awp-emoji" placeholder="<?php esc_html_e( $txt_placeholder, 'awp' ); ?>"><?php echo isset( $this->notif['order_failed'] ) ? esc_textarea( $this->notif['order_failed'] ) : ''; ?></textarea>
				<div class="upload-field">
					<input type="text" name="awp_notifications[order_failed_img]" placeholder="<?php esc_html_e( $img_format, 'awp' ); ?>" class="image_url regular-text order_failed_img upload-text" value="<?php echo esc_attr( isset( $this->notif['order_failed_img'] ) ? $this->notif['order_failed_img'] : '' ); ?>">
					<input type="button" name="upload-btn" value="<?php esc_html_e( $upload_btn, 'awp' ); ?>" class="upload-btn" data-id="order_failed_img">
				</div>
			</div>
		</div>
	</div>

	<div class="notification-form english">
		<div class="heading-bar">
			<label for="awp_notifications[order_refunded]" class="notification-title"><?php esc_html_e( 'Order refunded', 'awp' ); ?>
                <span class="tooltip-text"><?php esc_html_e( 'Sent when an order is refunded.', 'awp' ); ?></span>
            </label>
			<p class="deactive-hint"><em><?php esc_html_e( $blank, 'awp' ); ?></em></p>
		</div>
		<hr class="line">
		<div class="notification">
			<div class="form">
			    <div class="dropdowns">
			        <!-- Placeholder Dropdown Container -->
                    <div class="placeholder-container"></div>
                    <!-- Placeholder Dropdown Container (Message Templates) -->
                    <div class="message-template-container"></div>
                </div>
				<!-- Add textareas for  English messages -->
				<textarea id="awp_notifications[order_refunded]" name="awp_notifications[order_refunded]" cols="50" rows="5" class="awp-emoji" placeholder="<?php esc_html_e( $txt_placeholder, 'awp' ); ?>"><?php echo isset( $this->notif['order_refunded'] ) ? esc_textarea( $this->notif['order_refunded'] ) : ''; ?></textarea>
				<div class="upload-field">
					<input type="text" name="awp_notifications[order_refunded_img]" placeholder="<?php esc_html_e( $img_format, 'awp' ); ?>" class="image_url regular-text order_refunded_img upload-text" value="<?php echo esc_attr( isset( $this->notif['order_refunded_img'] ) ? $this->notif['order_refunded_img'] : '' ); ?>">
					<input type="button" name="upload-btn" value="<?php esc_html_e( $upload_btn, 'awp' ); ?>" class="upload-btn" data-id="order_refunded_img">
				</div>
			</div>
		</div>
	</div>

	<div class="notification-form english">
		<div class="heading-bar">
			<label for="awp_notifications[order_cancelled]" class="notification-title"><?php esc_html_e( 'Order cancelled', 'awp' ); ?>
                <span class="tooltip-text"><?php esc_html_e( 'Sent when a customer cancels their order.', 'awp' ); ?></span>
            </label>
			<p class="deactive-hint"><em><?php esc_html_e( $blank, 'awp' ); ?></em></p>
		</div>
		<hr class="line">
		<div class="notification">
			<div class="form">
			    <div class="dropdowns">
			        <!-- Placeholder Dropdown Container -->
                    <div class="placeholder-container"></div>
                    <!-- Placeholder Dropdown Container (Message Templates) -->
                    <div class="message-template-container"></div>
                </div>
				<!-- Add textareas for  English messages -->
				<textarea id="awp_notifications[order_cancelled]" name="awp_notifications[order_cancelled]" cols="50" rows="5" class="awp-emoji" placeholder="<?php esc_html_e( $txt_placeholder, 'awp' ); ?>"><?php echo isset( $this->notif['order_cancelled'] ) ? esc_textarea( $this->notif['order_cancelled'] ) : ''; ?></textarea>
				<div class="upload-field">
					<input type="text" name="awp_notifications[order_cancelled_img]" placeholder="<?php esc_html_e( $img_format, 'awp' ); ?>" class="image_url regular-text order_cancelled_img upload-text" value="<?php echo esc_attr( isset( $this->notif['order_cancelled_img'] ) ? $this->notif['order_cancelled_img'] : '' ); ?>">
					<input type="button" name="upload-btn" value="<?php esc_html_e( $upload_btn, 'awp' ); ?>" class="upload-btn" data-id="order_cancelled_img">
				</div>
			</div>
		</div>
	</div>

	<div class="notification-form english">
		<div class="heading-bar">
			<label for="awp_notifications[order_note]" class="notification-title"><?php esc_html_e( 'Order notes', 'awp' ); ?>
                <span class="tooltip-text"><?php esc_html_e( 'Sent when you add an order note.', 'awp' ); ?></span>
            </label>
			<p class="deactive-hint"><em><?php esc_html_e( $blank, 'awp' ); ?></em></p>
		</div>
		<hr class="line">
		<div class="notification">
			<div class="form">
			    <div class="dropdowns">
			        <!-- Placeholder Dropdown Container -->
                    <div class="placeholder-container"></div>
                    <!-- Placeholder Dropdown Container (Message Templates) -->
                    <div class="message-template-container"></div>
                </div>
				<!-- Add textareas for  English messages -->
				<textarea id="awp_notifications[order_note]" name="awp_notifications[order_note]" cols="50" rows="5" class="awp-emoji" placeholder="<?php esc_html_e( $txt_placeholder, 'awp' ); ?>"><?php echo isset( $this->notif['order_note'] ) ? esc_textarea( $this->notif['order_note'] ) : ''; ?></textarea>
				<div class="upload-field">
					<input type="text" name="awp_notifications[order_note_img]" placeholder="<?php esc_html_e( $img_format, 'awp' ); ?>" class="image_url regular-text order_note_img upload-text" value="<?php echo esc_attr( isset( $this->notif['order_note_img'] ) ? $this->notif['order_note_img'] : '' ); ?>">
					<input type="button" name="upload-btn" value="<?php esc_html_e( $upload_btn, 'awp' ); ?>" class="upload-btn" data-id="order_note_img">
				</div>
			</div>
		</div>
	</div>

	<?php if ( ! empty( $status_list ) ) : ?>
			<?php foreach ( $status_list as $status_name => $custom_status ) : ?>
	<div class="notification-form english">
	<div class="heading-bar">
	<label for="awp_notifications[order_<?php echo esc_attr( $custom_status ); ?>]" class="notification-title"><?php printf( __( 'Order - %s:', 'awp' ), esc_html( $status_name ) ); ?></label>
	</div>
	<div class="notification">
		<div class="form">
				<?php echo $message_icon; ?>
			<textarea id="awp_notifications[order_<?php echo esc_html( $custom_status ); ?>]" name="awp_notifications[order_<?php echo esc_html( $custom_status ); ?>]" cols="50" rows="5" class="awp-emoji" placeholder="<?php esc_html_e( 'Write your message here..', 'awp' ); ?>"><?php echo esc_textarea( isset( $this->notif[ 'order_' . esc_html( $custom_status ) ] ) ? $this->notif[ 'order_' . esc_html( $custom_status ) ] : '' ); ?></textarea>
			<div class="upload-field">
				<?php echo $link_icon; ?>
				<input type="text" name="awp_notifications[order_<?php echo esc_attr( $custom_status ); ?>_img]" placeholder="<?php esc_html_e( 'mage URL (Max 1 MB)...', 'awp' ); ?>" class="image_url regular-text order_<?php echo esc_attr( $custom_status ); ?>_img upload-text" value="<?php echo esc_attr( isset( $this->notif[ 'order_' . $custom_status . '_img' ] ) ? $this->notif[ 'order_' . $custom_status . '_img' ] : '' ); ?>">
				<input type="button" name="upload-btn" Value="<?php esc_html_e( 'Upload Image', 'awp' ); ?>" class="upload-btn" data-id="order_<?php echo $custom_status; ?>_img">
			</div>
		</div>
	</div>
	<p class="deactive-hint"><em><?php esc_html_e( 'Leave blank to deactivate.', 'awp' ); ?></em></p>
</div>
			<?php endforeach; ?>
		<?php endif; ?>    
</div>

<div class="editor-content layout-ar" data-lang="arabic">
    <?php
        $blank_ar = "اتركها فارغة لتعطيلها.";
        $txt_placeholder_ar = "اكتب رسالتك باللغة العربية هنا...";
        $img_format_ar = "الصيغ المتاحة .png, .jpg, .jpeg";
        $upload_btn_ar = "رفع الصورة";
    ?>

	<div class="notification-form arabic hint">
		<div class="hint-box">
			<label for="awp_notifications" class="hint-title"><?php esc_html_e( 'اشعارات حالة الطلب باللغة العربية', 'awp' ); ?></label>
			<p class="hint-desc"><?php esc_html_e( 'إرسل اشعارات حالة الطلب باللغة العربية تلقائياً، تعمل فقط في حالة اذا كانت اللغة العربية هي لغة الموقع الرئيسية أو اذا كانت لغة اضافية بموقع متعدد اللغات.', 'awp' ); ?></p>
		</div>
	</div>

	
	<div class="notification-form arabic">
		<div class="heading-bar">
			<label for="awp_notifications[order_onhold_arabic]" class="notification-title"><?php esc_html_e( 'قيد الانتظار', 'awp' ); ?>
                <span class="tooltip-text"><?php esc_html_e( 'ترسل عندما يكون الطلب في انتظار تأكيد الدفع.', 'awp' ); ?></span>
            </label>
			<p class="deactive-hint"><em><?php esc_html_e( $blank_ar, 'awp' ); ?></em></p>
		</div>
		<hr class="line">
		<div class="notification">
			<div class="form">
			    <div class="dropdowns">
			        <!-- Placeholder Dropdown Container -->
                    <div class="placeholder-container-ar"></div>
                    <!-- Placeholder Dropdown Container (Message Templates) -->
                     <div class="placeholder-messageTemplatesar"></div>
                </div>
				<!-- Add textareas for  English messages -->
				<textarea id="awp_notifications[order_onhold_arabic]" name="awp_notifications[order_onhold_arabic]" cols="50" rows="5" class="awp-emoji" placeholder="<?php esc_html_e( $txt_placeholder_ar, 'awp' ); ?>"><?php echo isset( $this->notif['order_onhold_arabic'] ) ? esc_textarea( $this->notif['order_onhold_arabic'] ) : ''; ?></textarea>
				<div class="upload-field">
					<input type="text" name="awp_notifications[order_onhold_img_arabic]" placeholder="<?php esc_html_e( $img_format_ar, 'awp' ); ?>" class="image_url regular-text order_onhold_img_arabic upload-text" value="<?php echo esc_attr( isset( $this->notif['order_onhold_img_arabic'] ) ? $this->notif['order_onhold_img_arabic'] : '' ); ?>">
					<input type="button" name="upload-btn" value="<?php esc_html_e( $upload_btn_ar, 'awp' ); ?>" class="upload-btn" data-id="order_onhold_img_arabic">
				</div>
			</div>
		</div>
	</div>

	<div class="notification-form arabic">
		<div class="heading-bar">
			<label for="awp_notifications[order_processing_arabic]" class="notification-title"><?php esc_html_e( 'قيد التنفيذ', 'awp' ); ?>
                <span class="tooltip-text"><?php esc_html_e( 'ترسل عن تحديد الطلب على أنه قيد التجهيز.', 'awp' ); ?></span>
            </label>
			<p class="deactive-hint"><em><?php esc_html_e( $blank_ar, 'awp' ); ?></em></p>
		</div>
		<hr class="line">
		<div class="notification">
			<div class="form">
			    <div class="dropdowns">
			        <!-- Placeholder Dropdown Container -->
                    <div class="placeholder-container-ar"></div>
                    <!-- Placeholder Dropdown Container (Message Templates) -->
                     <div class="placeholder-messageTemplatesar"></div>
                </div>
				<!-- Add textareas for  English messages -->
				<textarea id="awp_notifications[order_processing_arabic]" name="awp_notifications[order_processing_arabic]" cols="50" rows="5" class="awp-emoji" placeholder="<?php esc_html_e( $txt_placeholder_ar, 'awp' ); ?>"><?php echo isset( $this->notif['order_processing_arabic'] ) ? esc_textarea( $this->notif['order_processing_arabic'] ) : ''; ?></textarea>
				<div class="upload-field">
					<input type="text" name="awp_notifications[order_processing_img_arabic]" placeholder="<?php esc_html_e( $img_format_ar, 'awp' ); ?>" class="image_url regular-text order_processing_img_arabic upload-text" value="<?php echo esc_attr( isset( $this->notif['order_processing_img_arabic'] ) ? $this->notif['order_processing_img_arabic'] : '' ); ?>">
					<input type="button" name="upload-btn" value="<?php esc_html_e( $upload_btn_ar, 'awp' ); ?>" class="upload-btn" data-id="order_processing_img_arabic">
				</div>
			</div>
		</div>
	</div>
	
	<div class="notification-form arabic">
		<div class="heading-bar">
			<label for="awp_notifications[order_completed_arabic]" class="notification-title"><?php esc_html_e( 'الطلب مكتمل', 'awp' ); ?>
                <span class="tooltip-text"><?php esc_html_e( 'ترسل عند تسليم الطلب.', 'awp' ); ?></span>
            </label>
			<p class="deactive-hint"><em><?php esc_html_e( $blank_ar, 'awp' ); ?></em></p>
		</div>
		<hr class="line">
		<div class="notification">
			<div class="form">
			    <div class="dropdowns">
			        <!-- Placeholder Dropdown Container -->
                    <div class="placeholder-container-ar"></div>
                    <!-- Placeholder Dropdown Container (Message Templates) -->
                     <div class="placeholder-messageTemplatesar"></div>
                </div>
				<!-- Add textareas for  English messages -->
				<textarea id="awp_notifications[order_completed_arabic]" name="awp_notifications[order_completed_arabic]" cols="50" rows="5" class="awp-emoji" placeholder="<?php esc_html_e( $txt_placeholder_ar, 'awp' ); ?>"><?php echo isset( $this->notif['order_completed_arabic'] ) ? esc_textarea( $this->notif['order_completed_arabic'] ) : ''; ?></textarea>
				<div class="upload-field">
					<input type="text" name="awp_notifications[order_completed_img_arabic]" placeholder="<?php esc_html_e( $img_format_ar, 'awp' ); ?>" class="image_url regular-text order_completed_img_arabic upload-text" value="<?php echo esc_attr( isset( $this->notif['order_completed_img_arabic'] ) ? $this->notif['order_completed_img_arabic'] : '' ); ?>">
					<input type="button" name="upload-btn" value="<?php esc_html_e( $upload_btn_ar, 'awp' ); ?>" class="upload-btn" data-id="order_completed_img_arabic">
				</div>
			</div>
		</div>
	</div>

	<div class="notification-form arabic">
		<div class="heading-bar">
			<label for="awp_notifications[order_pending_arabic]" class="notification-title"><?php esc_html_e( 'بانتظار الدفع', 'awp' ); ?>
                <span class="tooltip-text"><?php esc_html_e( 'ترسل عندما لا يمكن معالجة الدفعة المعلقة للعميل.', 'awp' ); ?></span>
            </label>
			<p class="deactive-hint"><em><?php esc_html_e( $blank_ar, 'awp' ); ?></em></p>
		</div>
		<hr class="line">
		<div class="notification">
			<div class="form">
			    <div class="dropdowns">
			        <!-- Placeholder Dropdown Container -->
                    <div class="placeholder-container-ar"></div>
                    <!-- Placeholder Dropdown Container (Message Templates) -->
                    <div class="placeholder-messageTemplatesar"></div>
                </div>
				<!-- Add textareas for  English messages -->
				<textarea id="awp_notifications[order_pending_arabic]" name="awp_notifications[order_pending_arabic]" cols="50" rows="5" class="awp-emoji" placeholder="<?php esc_html_e( $txt_placeholder_ar, 'awp' ); ?>"><?php echo isset( $this->notif['order_pending_arabic'] ) ? esc_textarea( $this->notif['order_pending_arabic'] ) : ''; ?></textarea>
				<div class="upload-field">
					<input type="text" name="awp_notifications[order_pending_img_arabic]" placeholder="<?php esc_html_e( $img_format_ar, 'awp' ); ?>" class="image_url regular-text order_pending_img_arabic upload-text" value="<?php echo esc_attr( isset( $this->notif['order_pending_img_arabic'] ) ? $this->notif['order_pending_img_arabic'] : '' ); ?>">
					<input type="button" name="upload-btn" value="<?php esc_html_e( $upload_btn_ar, 'awp' ); ?>" class="upload-btn" data-id="order_pending_img_arabic">
				</div>
			</div>
		</div>
	</div>

	<div class="notification-form arabic">
		<div class="heading-bar">
			<label for="awp_notifications[order_failed_arabic]" class="notification-title"><?php esc_html_e( 'فشل الطلب', 'awp' ); ?>
                <span class="tooltip-text"><?php esc_html_e( 'ترسل عند فشل الدفع اثناء الطلب.', 'awp' ); ?></span>
            </label>
			<p class="deactive-hint"><em><?php esc_html_e( $blank_ar, 'awp' ); ?></em></p>
		</div>
		<hr class="line">
		<div class="notification">
			<div class="form">
			    <div class="dropdowns">
			        <!-- Placeholder Dropdown Container -->
                    <div class="placeholder-container-ar"></div>
                    <!-- Placeholder Dropdown Container (Message Templates) -->
                     <div class="placeholder-messageTemplatesar"></div>
                </div>
				<!-- Add textareas for  English messages -->
				<textarea id="awp_notifications[order_failed_arabic]" name="awp_notifications[order_failed_arabic]" cols="50" rows="5" class="awp-emoji" placeholder="<?php esc_html_e( $txt_placeholder_ar, 'awp' ); ?>"><?php echo isset( $this->notif['order_failed_arabic'] ) ? esc_textarea( $this->notif['order_failed_arabic'] ) : ''; ?></textarea>
				<div class="upload-field">
					<input type="text" name="awp_notifications[order_failed_img_arabic]" placeholder="<?php esc_html_e( $img_format_ar, 'awp' ); ?>" class="image_url regular-text order_failed_img_arabic upload-text" value="<?php echo esc_attr( isset( $this->notif['order_failed_img_arabic'] ) ? $this->notif['order_failed_img_arabic'] : '' ); ?>">
					<input type="button" name="upload-btn" value="<?php esc_html_e( $upload_btn_ar, 'awp' ); ?>" class="upload-btn" data-id="order_failed_img_arabic">
				</div>
			</div>
		</div>
	</div>

	<div class="notification-form arabic">
		<div class="heading-bar">
			<label for="awp_notifications[order_refunded_arabic]" class="notification-title"><?php esc_html_e( 'ارجاع الطلب', 'awp' ); ?>
                <span class="tooltip-text"><?php esc_html_e( 'ترسل عند استرداد الطلب.', 'awp' ); ?></span>
            </label>
			<p class="deactive-hint"><em><?php esc_html_e( $blank_ar, 'awp' ); ?></em></p>
		</div>
		<hr class="line">
		<div class="notification">
			<div class="form">
			    <div class="dropdowns">
			        <!-- Placeholder Dropdown Container -->
                    <div class="placeholder-container-ar"></div>
                    <!-- Placeholder Dropdown Container (Message Templates) -->
                    <div class="placeholder-messageTemplatesar"></div>
                </div>
				<!-- Add textareas for  English messages -->
				<textarea id="awp_notifications[order_refunded_arabic]" name="awp_notifications[order_refunded_arabic]" cols="50" rows="5" class="awp-emoji" placeholder="<?php esc_html_e( $txt_placeholder_ar, 'awp' ); ?>"><?php echo isset( $this->notif['order_refunded_arabic'] ) ? esc_textarea( $this->notif['order_refunded_arabic'] ) : ''; ?></textarea>
				<div class="upload-field">
					<input type="text" name="awp_notifications[order_refunded_img_arabic]" placeholder="<?php esc_html_e( $img_format_ar, 'awp' ); ?>" class="image_url regular-text order_refunded_img_arabic upload-text" value="<?php echo esc_attr( isset( $this->notif['order_refunded_img_arabic'] ) ? $this->notif['order_refunded_img_arabic'] : '' ); ?>">
					<input type="button" name="upload-btn" value="<?php esc_html_e( $upload_btn_ar, 'awp' ); ?>" class="upload-btn" data-id="order_refunded_img_arabic">
				</div>
			</div>
		</div>
	</div>

	<div class="notification-form arabic">
		<div class="heading-bar">
			<label for="awp_notifications[order_cancelled_arabic]" class="notification-title"><?php esc_html_e( 'الغاء الطلب', 'awp' ); ?>
                <span class="tooltip-text"><?php esc_html_e( 'ترسل عندما يقوم العميل بإلغاء طلبه.', 'awp' ); ?></span>
            </label>
			<p class="deactive-hint"><em><?php esc_html_e( $blank_ar, 'awp' ); ?></em></p>
		</div>
		<hr class="line">
		<div class="notification">
			<div class="form">
			    <div class="dropdowns">
			        <!-- Placeholder Dropdown Container -->
                    <div class="placeholder-container-ar"></div>
                    <!-- Placeholder Dropdown Container (Message Templates) -->
                    <div class="placeholder-messageTemplatesar"></div>
                </div>
				<!-- Add textareas for  English messages -->
				<textarea id="awp_notifications[order_cancelled_arabic]" name="awp_notifications[order_cancelled_arabic]" cols="50" rows="5" class="awp-emoji" placeholder="<?php esc_html_e( $txt_placeholder_ar, 'awp' ); ?>"><?php echo isset( $this->notif['order_cancelled_arabic'] ) ? esc_textarea( $this->notif['order_cancelled_arabic'] ) : ''; ?></textarea>
				<div class="upload-field">
					<input type="text" name="awp_notifications[order_cancelled_img_arabic]" placeholder="<?php esc_html_e( $img_format_ar, 'awp' ); ?>" class="image_url regular-text order_cancelled_img_arabic upload-text" value="<?php echo esc_attr( isset( $this->notif['order_cancelled_img_arabic'] ) ? $this->notif['order_cancelled_img_arabic'] : '' ); ?>">
					<input type="button" name="upload-btn" value="<?php esc_html_e( $upload_btn_ar, 'awp' ); ?>" class="upload-btn" data-id="order_cancelled_img_arabic">
				</div>
			</div>
		</div>
	</div>
	
	<div class="notification-form arabic">
		<div class="heading-bar">
			<label for="awp_notifications[order_note_arabic]" class="notification-title"><?php esc_html_e( 'ملاحظات الطلب ', 'awp' ); ?>
                <span class="tooltip-text"><?php esc_html_e( 'ترسل عند إضافتك ملاحظة للطلب.', 'awp' ); ?></span>
            </label>
			<p class="deactive-hint"><em><?php esc_html_e( $blank_ar, 'awp' ); ?></em></p>
		</div>
		<hr class="line">
		<div class="notification">
			<div class="form">
			    <div class="dropdowns">
			        <!-- Placeholder Dropdown Container -->
                    <div class="placeholder-container-ar"></div>
                    <!-- Placeholder Dropdown Container (Message Templates) -->
                     <div class="placeholder-messageTemplatesar"></div>
                </div>
				<!-- Add textareas for  English messages -->
				<textarea id="awp_notifications[order_note_arabic]" name="awp_notifications[order_note_arabic]" cols="50" rows="5" class="awp-emoji" placeholder="<?php esc_html_e( $txt_placeholder_ar, 'awp' ); ?>"><?php echo isset( $this->notif['order_note_arabic'] ) ? esc_textarea( $this->notif['order_note_arabic'] ) : ''; ?></textarea>
				<div class="upload-field">
					<input type="text" name="awp_notifications[order_note_img_arabic]" placeholder="<?php esc_html_e( $img_format_ar, 'awp' ); ?>" class="image_url regular-text order_note_img_arabic upload-text" value="<?php echo esc_attr( isset( $this->notif['order_note_img_arabic'] ) ? $this->notif['order_note_img_arabic'] : '' ); ?>">
					<input type="button" name="upload-btn" value="<?php esc_html_e( $upload_btn_ar, 'awp' ); ?>" class="upload-btn" data-id="order_note_img_arabic">
				</div>
			</div>
		</div>
	</div>


	<?php if ( ! empty( $status_list ) ) : ?>
		<?php foreach ( $status_list as $status_name => $custom_status ) : ?>
    	<div class="notification-form arabic">
    		<div class="heading-bar">
			    <label for="awp_notifications[order_<?php echo esc_attr( $custom_status ); ?>_arabic]" class="notification-title"><?php printf( __( 'Order - %s (العربية):', 'awp' ), esc_html( $status_name ) ); ?>
                </label>
    			<p class="deactive-hint"><em><?php esc_html_e( $blank_ar, 'awp' ); ?></em></p>
    		</div>
    		<hr class="line">
    		<div class="notification">
    			<div class="form">
    			    <div class="dropdowns">
    			        <!-- Placeholder Dropdown Container -->
                        <div class="placeholder-container-ar"></div>
                        <!-- Placeholder Dropdown Container (Message Templates) -->
                         <div class="placeholder-messageTemplatesar"></div>
                    </div>
    				<!-- Add textareas for  English messages -->
    				<textarea id="awp_notifications[order_<?php echo esc_html( $custom_status ); ?>_arabic]" name="awp_notifications[order_<?php echo esc_html( $custom_status ); ?>_arabic]" cols="50" rows="5" class="awp-emoji" placeholder="<?php esc_html_e( $txt_placeholder_ar, 'awp' ); ?>"><?php echo isset( $this->notif['order_' . esc_html( $custom_status ) . '_arabic'] ) ? esc_textarea( $this->notif['order_' . esc_html( $custom_status ) . '_arabic'] ) : ''; ?></textarea>
    				<div class="upload-field">
    					<input type="text" name="awp_notifications[order_<?php echo esc_attr( $custom_status ); ?>_img_arabic]" placeholder="<?php esc_html_e( $img_format_ar, 'awp' ); ?>" class="image_url regular-text order_<?php echo esc_attr( $custom_status ); ?>_img_arabic upload-text" value="<?php echo esc_attr( isset( $this->notif['order_' . $custom_status . '_img_arabic'] ) ? $this->notif['order_' . $custom_status . '_img_arabic'] : '' ); ?>">
    					<input type="button" name="upload-btn" value="<?php esc_html_e( $upload_btn_ar, 'awp' ); ?>" class="upload-btn" data-id="order_<?php echo $custom_status; ?>_img_arabic">
    				</div>
    			</div>
    		</div>
    	</div>
		<?php endforeach; ?>
	<?php endif; ?>

</div>

	<footer class="awp-panel-footer">
		<input type="submit" class="button-primarywa"
				value="<?php esc_html_e( 'Save Changes', 'awp' ); ?>">
	</footer>

	
		<?php
	}

	public function admin_notification_settings() {
		if ( $this->is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			$status_list      = wc_get_order_statuses();
			$status_list_temp = array();
			$original_status  = array(
				'pending',
				'failed',
				'on-hold',
				'processing',
				'completed',
				'refunded',
				'cancelled',
			);
			foreach ( $status_list as $key => $status ) {
				$status_name = str_replace( 'wc-', '', $key );
				if ( ! in_array( $status_name, $original_status ) ) {
					$status_list_temp[ $status ] = $status_name;
				}
			}
			$status_list = $status_list_temp;
		}
		?>
		<?php settings_fields( 'awp_storage_notifications' ); ?>
			
			
			
	<div class="info-banner">
    	<p class="banner-text"><?php esc_html_e( 'Receive WhatsApp messages about new orders or order status updates to stay informed in real-time.', 'awp' ); ?></p>
    	<input type="submit" class="button-primarywa saveit top" value="<?php esc_html_e( 'Save Changes', 'awp' ); ?>">
	</div>
	<hr class="line">
    <div class="notif-layout">
	<div class="notification-form english hint">
    <div class="hint-box">
        <label for="awp_notifications" class="hint-title"><?php esc_html_e('Admin order notifications', 'awp'); ?></label>
        <p class="hint-desc"><?php esc_html_e('Stay updated by receiving customer orders directly on your WhatsApp.', 'awp'); ?></p>
    </div>
    <div class="notification-form phone">
        <div class="heading-bar">
            <label for="awp_notifications[admin_number]" class="notification-title"><?php esc_html_e('Your Whatsapp number:', 'awp'); ?></label>
        </div>
        <div class="notification">
            <div class="phone-field">
                <input id="admin_number" type="text" name="awp_notifications[admin_number]" placeholder="<?php echo esc_attr__('010 01234567', 'awp'); ?>" class="admin_number regular-text admin_number upload-text" value="<?php echo esc_attr(isset($this->notif['admin_number']) ? $this->notif['admin_number'] : ''); ?>">
            </div>
        </div>
    </div>
</div>


	
<?php
$blank = __("leave blank to deactivate", "awp");
$txt_placeholder = __("Write your message...", "awp");
$img_format = __("Accepts .png, .jpg, .jpeg", "awp");
$upload_btn = __("Upload image", "awp");
?>

    
	<div class="notification-form">
		<div class="heading-bar">
			<label for="awp_notifications[admin_pending]" class="notification-title"><?php esc_html_e( 'Admin Notification (Pending Payment)', 'awp' ); ?>
                <span class="tooltip-text"><?php esc_html_e( "Receive when a customer's pending payment can't be processed.", 'awp' ); ?></span>
            </label>
			<p class="deactive-hint"><em><?php esc_html_e( $blank, 'awp' ); ?></em></p>
		</div>
		<hr class="line">
		<div class="notification">
			<div class="form">
			    <div class="dropdowns">
			        <!-- Placeholder Dropdown Container -->
                    <div class="placeholder-container"></div>
                </div>
				<!-- Add textareas for  English messages -->
				<textarea id="awp_notifications[admin_pending]" name="awp_notifications[admin_pending]" cols="50" rows="5" class="awp-emoji" placeholder="<?php esc_html_e( $txt_placeholder, 'awp' ); ?>"><?php echo isset( $this->notif['admin_pending'] ) ? esc_textarea( $this->notif['admin_pending'] ) : ''; ?></textarea>
				<div class="upload-field">
					<input type="text" name="awp_notifications[admin_pending_img]" placeholder="<?php esc_html_e( $img_format, 'awp' ); ?>" class="image_url regular-text admin_pending_img upload-text" value="<?php echo esc_attr( isset( $this->notif['admin_pending_img'] ) ? $this->notif['admin_pending_img'] : '' ); ?>">
					<input type="button" name="upload-btn" value="<?php esc_html_e( $upload_btn, 'awp' ); ?>" class="upload-btn" data-id="admin_pending_img">
				</div>
			</div>
		</div>
	</div>

	<div class="notification-form">
		<div class="heading-bar">
			<label for="awp_notifications[admin_failed]" class="notification-title"><?php esc_html_e( 'Admin Notification (failed)', 'awp' ); ?>
                <span class="tooltip-text"><?php esc_html_e( "Receive when a customer's payment can't be processed during checkout.", 'awp' ); ?></span>
            </label>
			<p class="deactive-hint"><em><?php esc_html_e( $blank, 'awp' ); ?></em></p>
		</div>
		<hr class="line">
		<div class="notification">
			<div class="form">
			    <div class="dropdowns">
			        <!-- Placeholder Dropdown Container -->
                    <div class="placeholder-container"></div>
                </div>
				<!-- Add textareas for  English messages -->
				<textarea id="awp_notifications[admin_failed]" name="awp_notifications[admin_failed]" cols="50" rows="5" class="awp-emoji" placeholder="<?php esc_html_e( $txt_placeholder, 'awp' ); ?>"><?php echo isset( $this->notif['admin_failed'] ) ? esc_textarea( $this->notif['admin_failed'] ) : ''; ?></textarea>
				<div class="upload-field">
					<input type="text" name="awp_notifications[admin_failed_img]" placeholder="<?php esc_html_e( $img_format, 'awp' ); ?>" class="image_url regular-text admin_failed_img upload-text" value="<?php echo esc_attr( isset( $this->notif['admin_failed_img'] ) ? $this->notif['admin_failed_img'] : '' ); ?>">
					<input type="button" name="upload-btn" value="<?php esc_html_e( $upload_btn, 'awp' ); ?>" class="upload-btn" data-id="admin_failed_img">
				</div>
			</div>
		</div>
	</div>

	<div class="notification-form">
		<div class="heading-bar">
			<label for="awp_notifications[admin_onhold]" class="notification-title"><?php esc_html_e( 'Admin Notification (On-Hold)', 'awp' ); ?>
                <span class="tooltip-text"><?php esc_html_e( 'Receive when an order is awaiting payment confirmation.', 'awp' ); ?></span>
            </label>
			<p class="deactive-hint"><em><?php esc_html_e( $blank, 'awp' ); ?></em></p>
		</div>
		<hr class="line">
		<div class="notification">
			<div class="form">
			    <div class="dropdowns">
			        <!-- Placeholder Dropdown Container -->
                    <div class="placeholder-container"></div>
                </div>
				<!-- Add textareas for  English messages -->
				<textarea id="awp_notifications[admin_onhold]" name="awp_notifications[admin_onhold]" cols="50" rows="5" class="awp-emoji" placeholder="<?php esc_html_e( $txt_placeholder, 'awp' ); ?>"><?php echo isset( $this->notif['admin_onhold'] ) ? esc_textarea( $this->notif['admin_onhold'] ) : ''; ?></textarea>
				<div class="upload-field">
					<input type="text" name="awp_notifications[admin_onhold_img]" placeholder="<?php esc_html_e( $img_format, 'awp' ); ?>" class="image_url regular-text admin_onhold_img upload-text" value="<?php echo esc_attr( isset( $this->notif['admin_onhold_img'] ) ? $this->notif['admin_onhold_img'] : '' ); ?>">
					<input type="button" name="upload-btn" value="<?php esc_html_e( $upload_btn, 'awp' ); ?>" class="upload-btn" data-id="admin_onhold_img">
				</div>
			</div>
		</div>
	</div>

	<div class="notification-form">
		<div class="heading-bar">
			<label for="awp_notifications[admin_processing]" class="notification-title"><?php esc_html_e( 'Admin Notification (processing)', 'awp' ); ?>
                <span class="tooltip-text"><?php esc_html_e( 'Receive when you mark an order as awaiting fulfillment.', 'awp' ); ?></span>
            </label>
			<p class="deactive-hint"><em><?php esc_html_e( $blank, 'awp' ); ?></em></p>
		</div>
		<hr class="line">
		<div class="notification">
			<div class="form">
			    <div class="dropdowns">
			        <!-- Placeholder Dropdown Container -->
                    <div class="placeholder-container"></div>
                </div>
				<!-- Add textareas for  English messages -->
				<textarea id="awp_notifications[admin_processing]" name="awp_notifications[admin_processing]" cols="50" rows="5" class="awp-emoji" placeholder="<?php esc_html_e( $txt_placeholder, 'awp' ); ?>"><?php echo isset( $this->notif['admin_processing'] ) ? esc_textarea( $this->notif['admin_processing'] ) : ''; ?></textarea>
				<div class="upload-field">
					<input type="text" name="awp_notifications[admin_processing_img]" placeholder="<?php esc_html_e( $img_format, 'awp' ); ?>" class="image_url regular-text admin_processing_img upload-text" value="<?php echo esc_attr( isset( $this->notif['admin_processing_img'] ) ? $this->notif['admin_processing_img'] : '' ); ?>">
					<input type="button" name="upload-btn" value="<?php esc_html_e( $upload_btn, 'awp' ); ?>" class="upload-btn" data-id="admin_processing_img">
				</div>
			</div>
		</div>
	</div>

	<div class="notification-form">
		<div class="heading-bar">
			<label for="awp_notifications[admin_completed]" class="notification-title"><?php esc_html_e( 'Admin Notification (completed)', 'awp' ); ?>
                <span class="tooltip-text"><?php esc_html_e( 'Receive when an order is delivered.', 'awp' ); ?></span>
            </label>
			<p class="deactive-hint"><em><?php esc_html_e( $blank, 'awp' ); ?></em></p>
		</div>
		<hr class="line">
		<div class="notification">
			<div class="form">
			    <div class="dropdowns">
			        <!-- Placeholder Dropdown Container -->
                    <div class="placeholder-container"></div>
                </div>
				<!-- Add textareas for  English messages -->
				<textarea id="awp_notifications[admin_completed]" name="awp_notifications[admin_completed]" cols="50" rows="5" class="awp-emoji" placeholder="<?php esc_html_e( $txt_placeholder, 'awp' ); ?>"><?php echo isset( $this->notif['admin_completed'] ) ? esc_textarea( $this->notif['admin_completed'] ) : ''; ?></textarea>
				<div class="upload-field">
					<input type="text" name="awp_notifications[admin_completed_img]" placeholder="<?php esc_html_e( $img_format, 'awp' ); ?>" class="image_url regular-text admin_completed_img upload-text" value="<?php echo esc_attr( isset( $this->notif['admin_completed_img'] ) ? $this->notif['admin_completed_img'] : '' ); ?>">
					<input type="button" name="upload-btn" value="<?php esc_html_e( $upload_btn, 'awp' ); ?>" class="upload-btn" data-id="admin_completed_img">
				</div>
			</div>
		</div>
	</div>

	<div class="notification-form">
		<div class="heading-bar">
			<label for="awp_notifications[admin_refunded]" class="notification-title"><?php esc_html_e( 'Admin Notification (refunded)', 'awp' ); ?>
                <span class="tooltip-text"><?php esc_html_e( 'Receive when an order is refunded.', 'awp' ); ?></span>
            </label>
			<p class="deactive-hint"><em><?php esc_html_e( $blank, 'awp' ); ?></em></p>
		</div>
		<hr class="line">
		<div class="notification">
			<div class="form">
			    <div class="dropdowns">
			        <!-- Placeholder Dropdown Container -->
                    <div class="placeholder-container"></div>
                </div>
				<!-- Add textareas for  English messages -->
				<textarea id="awp_notifications[admin_refunded]" name="awp_notifications[admin_refunded]" cols="50" rows="5" class="awp-emoji" placeholder="<?php esc_html_e( $txt_placeholder, 'awp' ); ?>"><?php echo isset( $this->notif['admin_refunded'] ) ? esc_textarea( $this->notif['admin_refunded'] ) : ''; ?></textarea>
				<div class="upload-field">
					<input type="text" name="awp_notifications[admin_refunded_img]" placeholder="<?php esc_html_e( $img_format, 'awp' ); ?>" class="image_url regular-text admin_refunded_img upload-text" value="<?php echo esc_attr( isset( $this->notif['admin_refunded_img'] ) ? $this->notif['admin_refunded_img'] : '' ); ?>">
					<input type="button" name="upload-btn" value="<?php esc_html_e( $upload_btn, 'awp' ); ?>" class="upload-btn" data-id="admin_refunded_img">
				</div>
			</div>
		</div>
	</div>

	<div class="notification-form">
		<div class="heading-bar">
			<label for="awp_notifications[admin_cancelled]" class="notification-title"><?php esc_html_e( 'Admin Notification (cancelled)', 'awp' ); ?>
                <span class="tooltip-text"><?php esc_html_e( 'Receive when a customer cancels their order.', 'awp' ); ?></span>
            </label>
			<p class="deactive-hint"><em><?php esc_html_e( $blank, 'awp' ); ?></em></p>
		</div>
		<hr class="line">
		<div class="notification">
			<div class="form">
			    <div class="dropdowns">
			        <!-- Placeholder Dropdown Container -->
                    <div class="placeholder-container"></div>
                </div>
				<!-- Add textareas for  English messages -->
				<textarea id="awp_notifications[admin_cancelled]" name="awp_notifications[admin_cancelled]" cols="50" rows="5" class="awp-emoji" placeholder="<?php esc_html_e( $txt_placeholder, 'awp' ); ?>"><?php echo isset( $this->notif['admin_cancelled'] ) ? esc_textarea( $this->notif['admin_cancelled'] ) : ''; ?></textarea>
				<div class="upload-field">
					<input type="text" name="awp_notifications[admin_cancelled_img]" placeholder="<?php esc_html_e( $img_format, 'awp' ); ?>" class="image_url regular-text admin_cancelled_img upload-text" value="<?php echo esc_attr( isset( $this->notif['admin_cancelled_img'] ) ? $this->notif['admin_cancelled_img'] : '' ); ?>">
					<input type="button" name="upload-btn" value="<?php esc_html_e( $upload_btn, 'awp' ); ?>" class="upload-btn" data-id="admin_cancelled_img">
				</div>
			</div>
		</div>
	</div>

</div>

<footer class="awp-panel-footer">
	<input type="submit" class="button-primarywa"
			value="<?php esc_html_e( 'Save Changes', 'awp' ); ?>">
</footer>
<?php
	}

	public function followup_settings() {
		?>
		<?php settings_fields( 'awp_storage_notifications' ); ?>

	<div class="info-banner">
		<p class="banner-text"><?php esc_html_e( 'Retarget customers with WhatsApp messages based on their order status and a timeframe you set.', 'awp' ); ?></p>
    	<input type="submit" class="button-primarywa saveit top" value="<?php esc_html_e( 'Save Changes', 'awp' ); ?>">
	</div>
	<hr class="line">
	<?php
$blank = __("leave blank to deactivate", "awp");
$txt_placeholder = __("Write your message...", "awp");
$img_format = __("Accepts .png, .jpg, .jpeg", "awp");
$upload_btn = __("Upload image", "awp");
$timer_icon = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" class="timer-icon"><path d="M12 2C6.486 2 2 6.486 2 12s4.486 10 10 10 10-4.486 10-10S17.514 2 12 2zm0 18c-4.411 0-8-3.589-8-8s3.589-8 8-8 8 3.589 8 8-3.589 8-8 8z"></path><path d="M13 7h-2v5.414l3.293 3.293 1.414-1.414L13 11.586z"></path></svg>';

?>

	<div class="notification-form english hint">
		<div class="hint-box">
			<label for="awp_notifications" class="hint-title"><?php esc_html_e( 'Order follow up notifications', 'awp' ); ?></label>
			<p class="hint-desc"><?php esc_html_e( 'Send timely follow-up messages to customers after their orders are placed, keeping them informed about their order status and encouraging engagement.', 'awp' ); ?></p>
		</div>
	</div>


	<div class="tabs">
		
	<input type="radio" name="tabs" id="tabone" checked="checked">
	<label for="tabone"><?php esc_html_e( 'On-Hold', 'awp' ); ?> </label>
    <div class="tab one">
        <div class="layout">
        	<div class="notification-form">
        		<div class="heading-bar">
        			<label for="awp_notifications[followup_onhold]" class="notification-title"><?php esc_html_e( '#1 - On-hold follow-up', 'awp' ); ?>
                        <span class="tooltip-text"><?php esc_html_e( 'Remind customers their order is awaiting fulfillment and reassure them.', 'awp' ); ?></span>
                    </label>
        			<p class="deactive-hint"><em><?php esc_html_e( $blank, 'awp' ); ?></em></p>
        		</div>
        		<hr class="line">
        		<div class="notification">
        			<div class="form">
        			    <div class="dropdowns">
        			        <!-- Placeholder Dropdown Container -->
                            <div class="placeholder-container"></div>
                        </div>
        				<!-- Add textareas for  English messages -->
        				<textarea id="awp_notifications[followup_onhold]" name="awp_notifications[followup_onhold]" cols="50" rows="5" class="awp-emoji" placeholder="<?php esc_html_e( $txt_placeholder, 'awp' ); ?>"><?php echo isset( $this->notif['followup_onhold'] ) ? esc_textarea( $this->notif['followup_onhold'] ) : ''; ?></textarea>
        				<div class="upload-field">
        					<input type="text" name="awp_notifications[followup_onhold_img]" placeholder="<?php esc_html_e( $img_format, 'awp' ); ?>" class="image_url regular-text followup_onhold_img upload-text" value="<?php echo esc_attr( isset( $this->notif['followup_onhold_img'] ) ? $this->notif['followup_onhold_img'] : '' ); ?>">
        					<input type="button" name="upload-btn" value="<?php esc_html_e( $upload_btn, 'awp' ); ?>" class="upload-btn" data-id="followup_onhold_img">
        				</div>
        			</div>
        		</div>
        		<hr class="sep">
        		<div class="timer">
        			<label for="awp_notifications[followup_onhold_day]"><?php esc_html_e( 'Send message after:', 'awp' ); ?></label>
        			<div class="input-with-hours">
            		    <?php echo $timer_icon; ?>
    				    <input id="awp_notifications[followup_onhold_day]" class="admin_number regular-text admin_number upload-text" name="awp_notifications[followup_onhold_day]" type="number" placeholder="<?php esc_html_e( '24', 'awp' ); ?>" value="<?php echo esc_attr( isset( $this->notif['followup_onhold_day'] ) ? $this->notif['followup_onhold_day'] : '' ); ?>">
                        <span class="hours-label">hours</span>
        			</div>
        		</div>
        	</div>
        	<div class="notification-form">
        		<div class="heading-bar">
        			<label for="awp_notifications[followup_onhold_2]" class="notification-title"><?php esc_html_e( '#2 - On-hold follow-up', 'awp' ); ?>
                        <span class="tooltip-text"><?php esc_html_e( 'Update customers on their on-hold order status and provide any additional information.', 'awp' ); ?></span>
                    </label>
        			<p class="deactive-hint"><em><?php esc_html_e( $blank, 'awp' ); ?></em></p>
        		</div>
        		<hr class="line">
        		<div class="notification">
        			<div class="form">
        			    <div class="dropdowns">
        			        <!-- Placeholder Dropdown Container -->
                            <div class="placeholder-container"></div>
                        </div>
        				<!-- Add textareas for  English messages -->
        				<textarea id="awp_notifications[followup_onhold_2]" name="awp_notifications[followup_onhold_2]" cols="50" rows="5" class="awp-emoji" placeholder="<?php esc_html_e( $txt_placeholder, 'awp' ); ?>"><?php echo isset( $this->notif['followup_onhold_2'] ) ? esc_textarea( $this->notif['followup_onhold_2'] ) : ''; ?></textarea>
        				<div class="upload-field">
        					<input type="text" name="awp_notifications[followup_onhold_img_2]" placeholder="<?php esc_html_e( $img_format, 'awp' ); ?>" class="image_url regular-text followup_onhold_img_2 upload-text" value="<?php echo esc_attr( isset( $this->notif['followup_onhold_img_2'] ) ? $this->notif['followup_onhold_img_2'] : '' ); ?>">
        					<input type="button" name="upload-btn" value="<?php esc_html_e( $upload_btn, 'awp' ); ?>" class="upload-btn" data-id="followup_onhold_img_2">
        				</div>
        			</div>
        		</div>
        		<hr class="sep">
        		<div class="timer">
        			<label for="awp_notifications[followup_onhold_day_2]"><?php esc_html_e( 'Send message after:', 'awp' ); ?></label>
        			<div class="input-with-hours">
            		    <?php echo $timer_icon; ?>
    				    <input id="awp_notifications[followup_onhold_day_2]" class="admin_number regular-text admin_number upload-text" name="awp_notifications[followup_onhold_day_2]" type="number" placeholder="<?php esc_html_e( '48', 'awp' ); ?>" value="<?php echo esc_attr( isset( $this->notif['followup_onhold_day_2'] ) ? $this->notif['followup_onhold_day_2'] : '' ); ?>">
                        <span class="hours-label">hours</span>
        			</div>
        		</div>
        	</div>
        	
        	<div class="notification-form">
        		<div class="heading-bar">
        			<label for="awp_notifications[followup_onhold_3]" class="notification-title"><?php esc_html_e( '#3 - On-hold follow-up', 'awp' ); ?>
                        <span class="tooltip-text"><?php esc_html_e( 'Remind customers of the pending status and offer assistance if needed.', 'awp' ); ?></span>
                    </label>
        			<p class="deactive-hint"><em><?php esc_html_e( $blank, 'awp' ); ?></em></p>
        		</div>
        		<hr class="line">
        		<div class="notification">
        			<div class="form">
        			    <div class="dropdowns">
        			        <!-- Placeholder Dropdown Container -->
                            <div class="placeholder-container"></div>
                        </div>
        				<!-- Add textareas for  English messages -->
        				<textarea id="awp_notifications[followup_onhold_3]" name="awp_notifications[followup_onhold_3]" cols="50" rows="5" class="awp-emoji" placeholder="<?php esc_html_e( $txt_placeholder, 'awp' ); ?>"><?php echo isset( $this->notif['followup_onhold_3'] ) ? esc_textarea( $this->notif['followup_onhold_3'] ) : ''; ?></textarea>
        				<div class="upload-field">
        					<input type="text" name="awp_notifications[followup_onhold_img_3]" placeholder="<?php esc_html_e( $img_format, 'awp' ); ?>" class="image_url regular-text followup_onhold_img_3 upload-text" value="<?php echo esc_attr( isset( $this->notif['followup_onhold_img_3'] ) ? $this->notif['followup_onhold_img_3'] : '' ); ?>">
        					<input type="button" name="upload-btn" value="<?php esc_html_e( $upload_btn, 'awp' ); ?>" class="upload-btn" data-id="followup_onhold_img_3">
        				</div>
        			</div>
        		</div>
        		<hr class="sep">
        		<div class="timer">
        			<label for="awp_notifications[followup_onhold_day_3]"><?php esc_html_e( 'Send message after:', 'awp' ); ?></label>
        			<div class="input-with-hours">
            		    <?php echo $timer_icon; ?>
    				    <input id="awp_notifications[followup_onhold_day_3]" class="admin_number regular-text admin_number upload-text" name="awp_notifications[followup_onhold_day_3]" type="number" placeholder="<?php esc_html_e( '72', 'awp' ); ?>" value="<?php echo esc_attr( isset( $this->notif['followup_onhold_day_3'] ) ? $this->notif['followup_onhold_day_3'] : '' ); ?>">
                        <span class="hours-label">hours</span>
        			</div>
        		</div>
        	</div>
        	
        	        	<div class="notification-form">
        		<div class="heading-bar">
        			<label for="awp_notifications[followup_onhold_4]" class="notification-title"><?php esc_html_e( '#4 - On-hold follow-up', 'awp' ); ?>
                        <span class="tooltip-text"><?php esc_html_e( 'Send a final reminder about the on-hold status, encouraging them to contact support if they have concerns.', 'awp' ); ?></span>
                    </label>
        			<p class="deactive-hint"><em><?php esc_html_e( $blank, 'awp' ); ?></em></p>
        		</div>
        		<hr class="line">
        		<div class="notification">
        			<div class="form">
        			    <div class="dropdowns">
        			        <!-- Placeholder Dropdown Container -->
                            <div class="placeholder-container"></div>
                        </div>
        				<textarea id="awp_notifications[followup_onhold_4]" name="awp_notifications[followup_onhold_4]" cols="50" rows="5" class="awp-emoji" placeholder="<?php esc_html_e( $txt_placeholder, 'awp' ); ?>"><?php echo isset( $this->notif['followup_onhold_4'] ) ? esc_textarea( $this->notif['followup_onhold_4'] ) : ''; ?></textarea>
        				<div class="upload-field">
        					<input type="text" name="awp_notifications[followup_onhold_img_4]" placeholder="<?php esc_html_e( $img_format, 'awp' ); ?>" class="image_url regular-text followup_onhold_img_4 upload-text" value="<?php echo esc_attr( isset( $this->notif['followup_onhold_img_4'] ) ? $this->notif['followup_onhold_img_4'] : '' ); ?>">
        					<input type="button" name="upload-btn" value="<?php esc_html_e( $upload_btn, 'awp' ); ?>" class="upload-btn" data-id="followup_onhold_img_4">
        				</div>
        			</div>
        		</div>
        		<hr class="sep">
        		<div class="timer">
        			<label for="awp_notifications[followup_onhold_day_4]"><?php esc_html_e( 'Send message after:', 'awp' ); ?></label>
        			<div class="input-with-hours">
            		    <?php echo $timer_icon; ?>
    				    <input id="awp_notifications[followup_onhold_day_4]" class="admin_number regular-text admin_number upload-text" name="awp_notifications[followup_onhold_day_4]" type="number" placeholder="<?php esc_html_e( '96', 'awp' ); ?>" value="<?php echo esc_attr( isset( $this->notif['followup_onhold_day_4'] ) ? $this->notif['followup_onhold_day_4'] : '' ); ?>">
                        <span class="hours-label">hours</span>
        			</div>
        		</div>
        	</div>

        	
        </div>
    </div>              
			  
	<input type="radio" name="tabs" id="tabtwo">
	<label for="tabtwo"><?php esc_html_e( 'Post Purchase', 'awp' ); ?></label>
	<div class="tab two">
        <div class="layout">
        	<div class="notification-form">
        		<div class="heading-bar">
        			<label for="awp_notifications[followup_aftersales]" class="notification-title"><?php esc_html_e( '#1 - Completed follow-up', 'awp' ); ?>
                        <span class="tooltip-text"><?php esc_html_e( 'Thank the customer for their purchase and confirm that the order has been completed.', 'awp' ); ?></span>
                    </label>
        			<p class="deactive-hint"><em><?php esc_html_e( $blank, 'awp' ); ?></em></p>
        		</div>
        		<hr class="line">
        		<div class="notification">
        			<div class="form">
        			    <div class="dropdowns">
        			        <!-- Placeholder Dropdown Container -->
                            <div class="placeholder-container"></div>
                        </div>
        				<!-- Add textareas for  English messages -->
        				<textarea id="awp_notifications[followup_aftersales]" name="awp_notifications[followup_aftersales]" cols="50" rows="5" class="awp-emoji" placeholder="<?php esc_html_e( $txt_placeholder, 'awp' ); ?>"><?php echo isset( $this->notif['followup_aftersales'] ) ? esc_textarea( $this->notif['followup_aftersales'] ) : ''; ?></textarea>
        				<div class="upload-field">
        					<input type="text" name="awp_notifications[followup_aftersales_img]" placeholder="<?php esc_html_e( $img_format, 'awp' ); ?>" class="image_url regular-text followup_aftersales_img upload-text" value="<?php echo esc_attr( isset( $this->notif['followup_aftersales_img'] ) ? $this->notif['followup_aftersales_img'] : '' ); ?>">
        					<input type="button" name="upload-btn" value="<?php esc_html_e( $upload_btn, 'awp' ); ?>" class="upload-btn" data-id="followup_aftersales_img">
        				</div>
        			</div>
        		</div>
        		<hr class="sep">
        		<div class="timer">
        			<label for="awp_notifications[followup_aftersales_day]"><?php esc_html_e( 'Send message after:', 'awp' ); ?></label>
        			<div class="input-with-hours">
            		    <?php echo $timer_icon; ?>
    				    <input id="awp_notifications[followup_aftersales_day]" class="admin_number regular-text admin_number upload-text" name="awp_notifications[followup_aftersales_day]" type="number" placeholder="<?php esc_html_e( '24', 'awp' ); ?>" value="<?php echo esc_attr( isset( $this->notif['followup_aftersales_day'] ) ? $this->notif['followup_aftersales_day'] : '' ); ?>">
                        <span class="hours-label">hours</span>
        			</div>
        		</div>
        	</div>
        	<div class="notification-form">
        		<div class="heading-bar">
        			<label for="awp_notifications[followup_aftersales_2]" class="notification-title"><?php esc_html_e( '#2 - Completed follow-up', 'awp' ); ?>
                        <span class="tooltip-text"><?php esc_html_e( 'Request feedback from the customer about their shopping experience.', 'awp' ); ?></span>
                    </label>
        			<p class="deactive-hint"><em><?php esc_html_e( $blank, 'awp' ); ?></em></p>
        		</div>
        		<hr class="line">
        		<div class="notification">
        			<div class="form">
        			    <div class="dropdowns">
        			        <!-- Placeholder Dropdown Container -->
                            <div class="placeholder-container"></div>
                        </div>
        				<!-- Add textareas for  English messages -->
        				<textarea id="awp_notifications[followup_aftersales_2]" name="awp_notifications[followup_aftersales_2]" cols="50" rows="5" class="awp-emoji" placeholder="<?php esc_html_e( $txt_placeholder, 'awp' ); ?>"><?php echo isset( $this->notif['followup_aftersales_2'] ) ? esc_textarea( $this->notif['followup_aftersales_2'] ) : ''; ?></textarea>
        				<div class="upload-field">
        					<input type="text" name="awp_notifications[followup_aftersales_img_2]" placeholder="<?php esc_html_e( $img_format, 'awp' ); ?>" class="image_url regular-text followup_aftersales_img_2 upload-text" value="<?php echo esc_attr( isset( $this->notif['followup_aftersales_img_2'] ) ? $this->notif['followup_aftersales_img_2'] : '' ); ?>">
        					<input type="button" name="upload-btn" value="<?php esc_html_e( $upload_btn, 'awp' ); ?>" class="upload-btn" data-id="followup_aftersales_img_2">
        				</div>
        			</div>
        		</div>
        		<hr class="sep">
        		<div class="timer">
        			<label for="awp_notifications[followup_aftersales_day_2]"><?php esc_html_e( 'Send message after:', 'awp' ); ?></label>
        			<div class="input-with-hours">
            		    <?php echo $timer_icon; ?>
    				    <input id="awp_notifications[followup_aftersales_day_2]" class="admin_number regular-text admin_number upload-text" name="awp_notifications[followup_aftersales_day_2]" type="number" placeholder="<?php esc_html_e( '48', 'awp' ); ?>" value="<?php echo esc_attr( isset( $this->notif['followup_aftersales_day_2'] ) ? $this->notif['followup_aftersales_day_2'] : '' ); ?>">
                        <span class="hours-label">hours</span>
        			</div>
        		</div>
        	</div>
        	<div class="notification-form">
        		<div class="heading-bar">
        			<label for="awp_notifications[followup_aftersales_3]" class="notification-title"><?php esc_html_e( '#3 - Completed follow-up', 'awp' ); ?>
                        <span class="tooltip-text"><?php esc_html_e( 'Encourage the customer to leave a review for the product they purchased.', 'awp' ); ?></span>
                    </label>
        			<p class="deactive-hint"><em><?php esc_html_e( $blank, 'awp' ); ?></em></p>
        		</div>
        		<hr class="line">
        		<div class="notification">
        			<div class="form">
        			    <div class="dropdowns">
        			        <!-- Placeholder Dropdown Container -->
                            <div class="placeholder-container"></div>
                        </div>
        				<!-- Add textareas for  English messages -->
        				<textarea id="awp_notifications[followup_aftersales_3]" name="awp_notifications[followup_aftersales_3]" cols="50" rows="5" class="awp-emoji" placeholder="<?php esc_html_e( $txt_placeholder, 'awp' ); ?>"><?php echo isset( $this->notif['followup_aftersales_3'] ) ? esc_textarea( $this->notif['followup_aftersales_3'] ) : ''; ?></textarea>
        				<div class="upload-field">
        					<input type="text" name="awp_notifications[followup_aftersales_img_3]" placeholder="<?php esc_html_e( $img_format, 'awp' ); ?>" class="image_url regular-text followup_aftersales_img_3 upload-text" value="<?php echo esc_attr( isset( $this->notif['followup_aftersales_img_3'] ) ? $this->notif['followup_aftersales_img_3'] : '' ); ?>">
        					<input type="button" name="upload-btn" value="<?php esc_html_e( $upload_btn, 'awp' ); ?>" class="upload-btn" data-id="followup_aftersales_img_3">
        				</div>
        			</div>
        		</div>
        		<hr class="sep">
        		<div class="timer">
        			<label for="awp_notifications[followup_aftersales_day_3]"><?php esc_html_e( 'Send message after:', 'awp' ); ?></label>
        			<div class="input-with-hours">
            		    <?php echo $timer_icon; ?>
    				    <input id="awp_notifications[followup_aftersales_day_3]" class="admin_number regular-text admin_number upload-text" name="awp_notifications[followup_aftersales_day_3]" type="number" placeholder="<?php esc_html_e( '72', 'awp' ); ?>" value="<?php echo esc_attr( isset( $this->notif['followup_aftersales_day_3'] ) ? $this->notif['followup_aftersales_day_3'] : '' ); ?>">
                        <span class="hours-label">hours</span>
        			</div>
        		</div>
        	</div>
        	<div class="notification-form">
        		<div class="heading-bar">
        			<label for="awp_notifications[followup_aftersales_4]" class="notification-title"><?php esc_html_e( '#4 - Completed follow-up', 'awp' ); ?>
                        <span class="tooltip-text"><?php esc_html_e( 'Invite the customer to join your loyalty program for exclusive benefits and discounts.', 'awp' ); ?></span>
                    </label>
        			<p class="deactive-hint"><em><?php esc_html_e( $blank, 'awp' ); ?></em></p>
        		</div>
        		<hr class="line">
        		<div class="notification">
        			<div class="form">
        			    <div class="dropdowns">
        			        <!-- Placeholder Dropdown Container -->
                            <div class="placeholder-container"></div>
                        </div>
        				<!-- Add textareas for  English messages -->
        				<textarea id="awp_notifications[followup_aftersales_4]" name="awp_notifications[followup_aftersales_4]" cols="50" rows="5" class="awp-emoji" placeholder="<?php esc_html_e( $txt_placeholder, 'awp' ); ?>"><?php echo isset( $this->notif['followup_aftersales_4'] ) ? esc_textarea( $this->notif['followup_aftersales_4'] ) : ''; ?></textarea>
        				<div class="upload-field">
        					<input type="text" name="awp_notifications[followup_aftersales_img_4]" placeholder="<?php esc_html_e( $img_format, 'awp' ); ?>" class="image_url regular-text followup_aftersales_img_4 upload-text" value="<?php echo esc_attr( isset( $this->notif['followup_aftersales_img_4'] ) ? $this->notif['followup_aftersales_img_4'] : '' ); ?>">
        					<input type="button" name="upload-btn" value="<?php esc_html_e( $upload_btn, 'awp' ); ?>" class="upload-btn" data-id="followup_aftersales_img_4">
        				</div>
        			</div>
        		</div>
        		<hr class="sep">
        		<div class="timer">
        			<label for="awp_notifications[followup_aftersales_day_4]"><?php esc_html_e( 'Send message after:', 'awp' ); ?></label>
        			<div class="input-with-hours">
            		    <?php echo $timer_icon; ?>
    				    <input id="awp_notifications[followup_aftersales_day_4]" class="admin_number regular-text admin_number upload-text" name="awp_notifications[followup_aftersales_day_4]" type="number" placeholder="<?php esc_html_e( '96', 'awp' ); ?>" value="<?php echo esc_attr( isset( $this->notif['followup_aftersales_day_4'] ) ? $this->notif['followup_aftersales_day_4'] : '' ); ?>">
                        <span class="hours-label">hours</span>
        			</div>
        		</div>
        	</div>
		</div>
	</div>              
</div>  
<footer class="awp-panel-footer">
	<input type="submit" class="button-primarywa" value="<?php esc_html_e( 'Save Changes', 'awp' ); ?>">
</footer>


<?php
	}

	public function abandoned_cart_settings() {
		?>
		<?php settings_fields( 'awp_storage_notifications' ); ?>

	<div class="info-banner">
		<p class="banner-text"><?php esc_html_e( 'Target visitors who abandoned their shopping carts after entering their details but did not complete the purchase.', 'awp' ); ?></p>
		 <?php
        if ( is_plugin_active( 'woo-save-abandoned-carts/cartbounty-abandoned-carts.php' ) ) {
        ?>
    	<input type="submit" class="button-primarywa saveit top" value="<?php esc_html_e( 'Save Changes', 'awp' ); ?>">
    	 <?php
        }
        ?>
	</div>
	<hr class="line">
	<div class="tab">
	  
        <div class="form-table awp-table">
         <?php
    if (!is_plugin_active('woo-save-abandoned-carts/cartbounty-abandoned-carts.php')) {
        printf(
            __(
                '<div class="hint-head"><span>Enable Abandoned Cart Notifications</span></div><div class="hint-info"><p class="desc">Install the <strong>Cartbounty plugin</strong> to activate notifications for abandoned carts.</p><a href="%s" class="banner-cta">Install Cartbounty</a></div>',
                'awp'
            ),
            admin_url('plugin-install.php?s=Cartbounty%20Abandoned%20Cart&tab=search&type=term')
        );
    }
    ?>
        </div>
        <?php
        if ( is_plugin_active( 'woo-save-abandoned-carts/cartbounty-abandoned-carts.php' ) ) {
        ?>
          
		  <?php
$blank = __("leave blank to deactivate", "awp");
$txt_placeholder = __("Write your message...", "awp");
$img_format = __("Accepts .png, .jpg, .jpeg", "awp");
$upload_btn = __("Upload image", "awp");
$timer_icon = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" class="timer-icon"><path d="M12 2C6.486 2 2 6.486 2 12s4.486 10 10 10 10-4.486 10-10S17.514 2 12 2zm0 18c-4.411 0-8-3.589-8-8s3.589-8 8-8 8 3.589 8 8-3.589 8-8 8z"></path><path d="M13 7h-2v5.414l3.293 3.293 1.414-1.414L13 11.586z"></path></svg>';

?>
        
	<div class="notification-form english hint">
		<div class="hint-box">
			<label for="awp_notifications" class="hint-title"><?php esc_html_e( 'Abandoned cart notifications', 'awp' ); ?></label>
			<p class="hint-desc"><?php esc_html_e( 'Remind customers about the items left in their cart and encourage them to complete their purchase with personalized WhatsApp messages.', 'awp' ); ?></p>
		</div>
	</div>

        <div class="notification-form">
        	<div class="heading-bar">
        		<label for="awp_notifications[followup_abandoned]" class="notification-title"><?php esc_html_e( '#1 - Abandoned cart follow-up', 'awp' ); ?>
                    <span class="tooltip-text"><?php esc_html_e( ' ', 'awp' ); ?></span>
                </label>
        		<p class="deactive-hint"><em><?php esc_html_e( $blank, 'awp' ); ?></em></p>
        	</div>
        	<hr class="line">
        	<div class="notification">
        		<div class="form">
        		    <div class="dropdowns">
        		        <!-- Placeholder Dropdown Container -->
                        <div class="placeholder-containerab"></div>
                    </div>
        			<!-- Add textareas for  English messages -->
        			<textarea id="awp_notifications[followup_abandoned]" name="awp_notifications[followup_abandoned]" cols="50" rows="5" class="awp-emoji" placeholder="<?php esc_html_e( $txt_placeholder, 'awp' ); ?>"><?php echo isset( $this->notif['followup_abandoned'] ) ? esc_textarea( $this->notif['followup_abandoned'] ) : ''; ?></textarea>
        			<div class="upload-field">
        				<input type="text" name="awp_notifications[followup_abandoned_img]" placeholder="<?php esc_html_e( $img_format, 'awp' ); ?>" class="image_url regular-text followup_abandoned_img upload-text" value="<?php echo esc_attr( isset( $this->notif['followup_abandoned_img'] ) ? $this->notif['followup_abandoned_img'] : '' ); ?>">
        				<input type="button" name="upload-btn" value="<?php esc_html_e( $upload_btn, 'awp' ); ?>" class="upload-btn" data-id="followup_abandoned_img">
        			</div>
        		</div>
        	</div>
        	<hr class="sep">
        	<div class="timer">
        		<label for="awp_notifications[followup_abandoned_day]"><?php esc_html_e( 'Send message after:', 'awp' ); ?></label>
        		<div class="input-with-hours">
        		    <?php echo $timer_icon; ?>
        		    <input id="awp_notifications[followup_abandoned_day]" class="admin_number regular-text admin_number upload-text" name="awp_notifications[followup_abandoned_day]" type="number" placeholder="<?php esc_html_e( '24', 'awp' ); ?>" value="<?php echo esc_attr( isset( $this->notif['followup_abandoned_day'] ) ? $this->notif['followup_abandoned_day'] : '' ); ?>">
                    <span class="hours-label">hours</span>
        		</div>
        	</div>
        </div>

        <?php
        }
        ?>

	</div>
	
			<?php
        if ( is_plugin_active( 'woo-save-abandoned-carts/cartbounty-abandoned-carts.php' ) ) {
        ?>  
		<footer class="awp-panel-footer">
				<input type="submit" class="button-primarywa"
						value="<?php esc_html_e( 'Save Changes', 'awp' ); ?>">
			</footer>
			<?php
        }
        ?>
		<?php
	}

	public function other_settings() {
		?>
		<?php settings_fields( 'awp_storage_notifications' ); ?>

	
		<?php
	}


	public function help_info() {
		?>
		<?php settings_fields( 'awp_storage_notifications' ); ?>

	<div class="info-banner">
		<p class="banner-text"><?php esc_html_e( 'Send messages to any WhatsApp number from this section.', 'awp' ); ?></p>
	</div>
	<hr class="line">

		
	<?php
$blank = __("leave blank to deactivate", "awp");
$txt_placeholder = __("Write your message...", "awp");
$img_format = __("Accepts .png, .jpg, .jpeg", "awp");
$upload_btn = __("Upload image", "awp");
?>


		<div class="awp-panel">


<form method="post">    
    <div class="notification-form msg">
    	<div class="heading">
    		<label for="awp_test-message" class="notification-title"><?php esc_html_e( 'Send WhatsApp message to:', 'awp' ); ?>
                <span class="tooltip-text"><?php esc_html_e( 'You can send a WhatsApp message to an individual customer directly from here.', 'awp' ); ?></span>
            </label>
            <input id="awp_test_number" class="admin_number regular-text admin_number upload-text" name="awp_test_number" type="text">
    	</div>
    	<hr class="divi">
    	<div class="notification">
    		<div class="form">
    			<!-- Add textareas for  English messages -->
    			<textarea id="awp_test_message" name="awp_test_message" cols="50" rows="5" class="awp-emoji" placeholder="<?php esc_html_e( $txt_placeholder, 'awp' ); ?>"></textarea>
    			<div class="upload-field">
    				<input type="text" name="awp_test_image" placeholder="<?php esc_html_e( $img_format, 'awp' ); ?>" class="image_url regular-text awp-test-image upload-text">
    				<input type="button" name="upload-btn" value="<?php esc_html_e( $upload_btn, 'awp' ); ?>" class="upload-btn" data-id="awp-test-image">
    			</div>
    		</div>
    	</div>
    	<hr class="divi">
		<input type="submit" name="awp_send_test" class="button-primarywa" value="<?php esc_html_e( 'Send Message', 'awp' ); ?>">
    </div>


</form>
		</div>
		<?php
	}

	public function setup_info() {
		?>
			<div class="info-body">
		<div>
			<form method="post" action="options.php" class="setting-form">
		<?php settings_fields( 'awp_storage_instances' ); ?>
			<div class="heading-bar credential">
				<div class="access-title">
					<span><?php esc_html_e( 'Notifications number', 'awp' ); ?></span>
				</div>
				<p><span><?php esc_html_e( 'WhatsApp number for order updates. Learn ', 'awp' ); ?></span>
				<a href="https://app.wawp.net/whatsapp_profile" class="" target="_blank"><?php esc_html_e( 'how to connect.', 'awp' ); ?></a>
				</p>
			</div>
			<label for="awp_instances[instance_id]" class="keys-label">
		<?php esc_html_e( 'Instance ID', 'awp' ); ?></label>
			<input type="text" id="instance_id" name="awp_instances[instance_id]" placeholder="Your instance ID" class="regular-text data" value="<?php echo esc_attr( isset( $this->instances['instance_id'] ) ? $this->instances['instance_id'] : '' ); ?>">
<label for="awp_instances[access_token]" class="keys-label">
		<?php esc_html_e( 'Access token', 'awp' ); ?>
					</label>
				<input type="text" id="access_token" name="awp_instances[access_token]" placeholder="Your access token" class="regular-text data" value="<?php echo esc_attr( isset( $this->instances['access_token'] ) ? $this->instances['access_token'] : '' ); ?>">
				<input type="submit" class="setting-button"
					value="<?php esc_html_e( 'Connect', 'awp' ); ?>" style="margin-top:10px;">  
				</form>

		<?php if ( isset( $this->instances['access_token'] ) && isset( $this->instances['instance_id'] ) ) : ?>
				<div class="instance-control">
					<p><strong><?php esc_html_e( 'Instance Control', 'awp' ); ?></strong></p>
				   
					<a href="#" class="button button-secondarywa ins-action" data-action="status"><?php esc_html_e( 'Connection status', 'awp' ); ?></a>
			 
			<a href="#" class="button button-secondarywa ins-action" data-action="connectionButtons"><?php esc_html_e( 'Connection test', 'awp' ); ?></a>
			 
					<div class="instance-desc">
						<br>
					<strong> <span>▼</span>  <?php esc_html_e( 'Control Description', 'awp' ); ?> </strong>
						<div>
							<strong><?php esc_html_e( 'Connection status', 'awp' ); ?>:  </strong><?php esc_html_e( 'A connection test is performed between the WhatsApp number and the Wawp system to inform you of the result whether it is connected or not', 'awp' ); ?>
							<br>
							<strong><?php esc_html_e( 'Connection test', 'awp' ); ?>:  </strong><?php esc_html_e( 'A WhatsApp message is sent from your number registered with Wawp and added to the WordPress plugin settings (account ID and access Token) to the Wawp Bot number to verify that the plugin is active and notifications are sent normally.', 'awp' ); ?>
					</div>      
				</div>
			  
				<div id="control-modal" class="modal"></div>
		<?php endif; ?>
			</div>

		</div>
		
		<div class="setting-banner"></div>


		<?php
	}

	public function logs_page() {
		$logger        = new awp_logger();
		$customer_logs = $logger->get_log_file( 'awpsend' );

		// Check if the "Clear Logs" button is clicked
		if ( isset( $_GET['clear_logs'] ) && $_GET['clear_logs'] == 1 ) {
			$handle_to_clear = 'awpsend'; // Specify the handle you want to clear
			$logger->clear( $handle_to_clear );

			// Display a success message
			echo '<div class="notice notice-success is-dismissible"><p>';
			echo __( 'Logs cleared successfully.', 'awp' );
			echo '</p></div>';
		}

		?>
	<div class="wrap" id="awp-wrap">
		<div class="form-wrapper">
			<div class="awp-tab-wrapper">
			    <div class="hint-inf mob">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" style="fill: rgba(0, 68, 68, 1);transform: ;msFilter:;" class="alert-icon"><path d="M12 2C6.486 2 2 6.486 2 12s4.486 10 10 10 10-4.486 10-10S17.514 2 12 2zm0 18c-4.411 0-8-3.589-8-8s3.589-8 8-8 8 3.589 8 8-3.589 8-8 8z"></path><path d="M11 11h2v6h-2zm0-4h2v2h-2z"></path></svg>
                    <p class="desc"><?php esc_html_e( "To view your wawp log, please browse from your computer.", 'awp' ); ?>
                    </p>
                </div>
			    <div class="hint-inf">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" style="fill: rgba(0, 68, 68, 1);transform: ;msFilter:;" class="alert-icon"><path d="M12 2C6.486 2 2 6.486 2 12s4.486 10 10 10 10-4.486 10-10S17.514 2 12 2zm0 18c-4.411 0-8-3.589-8-8s3.589-8 8-8 8 3.589 8 8-3.589 8-8 8z"></path><path d="M11 11h2v6h-2zm0-4h2v2h-2z"></path></svg>
                    <p class="desc"><?php esc_html_e( "Wawp Log only counts notification messages, we'll release a new update to count login and checkout confirmation messages.", 'awp' ); ?>
                    </p>
                </div>
				<div class="search-container">
					<label for="log-search"><?php esc_html_e( 'Search in log:', 'awp' ); ?></label>
					<div style="display: flex; flex-direction: row; gap: 16px; align-items: center;">
    					<input type="text" id="log-search" placeholder="<?php esc_html_e( 'Type to search...Date/WhatsApp Number/Message/Image Attachment/Status', 'awp' ); ?>">
            			<a href="<?php echo admin_url( 'admin.php?page=awp-message-log&clear_logs=1' ); ?>" class="button log-clear"><?php esc_html_e( "Clear Logs", 'awp' ); ?></a>
        			</div>
				</div>

				<table class="wp-list-table widefat fixed striped table-view-list posts table-message-logs" style="margin:10px 0;">
					<thead>
						<tr class="header-row">
							<th><?php esc_html_e( 'Date', 'awp' ); ?></th>
							<th><?php esc_html_e( 'WhatsApp Number', 'awp' ); ?></th>
							<th><?php esc_html_e( 'Message', 'awp' ); ?></th>
							<th><?php esc_html_e( 'Image Attachment', 'awp' ); ?></th>
							<th><?php esc_html_e( 'Plugin status', 'awp' ); ?></th>
							<th><?php esc_html_e( 'wawp.net status', 'awp' ); ?></th>
							<th><?php esc_html_e( 'Resend', 'awp' ); ?></th>
						</tr>
					</thead>
					<tbody>
		<?php echo $customer_logs; ?>
					</tbody>   
				</table>
			</div>
			<div class="info">
		<?php
		$this->setup_info();
		?>
							</div>
		</div>
	</div>
        <?php
	}
}