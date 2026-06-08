<?php

namespace WPForms\Integrations\WooCommerce;

use WPForms\Integrations\IntegrationInterface;

/**
 * Class Notifications for WooCommerce integration.
 *
 * @since 1.8.9
 */
class Notifications implements IntegrationInterface {

	/**
	 * Assets handle.
	 *
	 * @since 1.8.9
	 *
	 * @var string Handle.
	 */
	const HANDLE = 'wpforms-woocommerce-notifications';

	/**
	 * Option name to store the dismissed state.
	 *
	 * @since 1.8.9
	 *
	 * @var string Option name.
	 */
	const OPTION_NAME = 'wpforms_woocommerce_notifications_dismissed';

	/**
	 * Indicate if current integration is allowed to load.
	 *
	 * @since 1.8.9
	 *
	 * @return bool
	 */
	public function allow_load() {

		// Check if WooCommerce is not installed and active.
		if ( ! class_exists( 'woocommerce' ) ) {
			return false;
		}

		// Do not show the notification if it was dismissed before.
		if ( get_option( self::OPTION_NAME ) ) {
			return false;
		}

		// Allow to load when the notification is being dismissed via AJAX.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( wpforms_is_admin_ajax() && isset( $_POST['action'] ) && $_POST['action'] === 'wpforms_woocommerce_dismiss' ) {
			return true;
		}

		// Load only on an WooCommerce Settings > Emails page.
		if ( ! $this->is_woocommerce_email_settings_page() ) {
			return false;
		}

		// Do not show the notification if any SMTP plugin is active.
		return ! $this->has_smtp_plugin();
	}

	/**
	 * Load integration.
	 *
	 * @since 1.8.9
	 */
	public function load() {

		$this->hooks();
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.8.9
	 */
	private function hooks() {

		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ], 20 );
		add_action( 'woocommerce_admin_field_email_notification' , [ $this, 'add_notification' ] );
		add_action( 'wp_ajax_wpforms_woocommerce_dismiss', [ $this, 'dismiss' ] );
	}

	/**
	 * Enqueue assets.
	 *
	 * @since 1.8.9
	 */
	public function enqueue_assets() {

		$min = wpforms_get_min_suffix();

		wp_enqueue_style(
			self::HANDLE,
			WPFORMS_PLUGIN_URL . "/assets/css/integrations/woocommerce/notifications{$min}.css",
			[],
			WPFORMS_VERSION
		);

		wp_enqueue_script(
			self::HANDLE,
			WPFORMS_PLUGIN_URL . "/assets/js/integrations/woocommerce/notifications{$min}.js",
			[ 'jquery' ],
			WPFORMS_VERSION,
			true
		);

		wp_localize_script(
			self::HANDLE,
			'wpforms_woocommerce_notifications',
			[
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( self::HANDLE ),
			]
		);
	}

	/**
	 * Add notification.
	 *
	 * @since 1.8.9
	 */
	public function add_notification() {
		?>

		<div class='wpforms-woocommerce-notification'>
			<div class='wpforms-woocommerce-notification-content'>
				<h2>
					<?php esc_html_e( 'Make Sure Important Emails Reach Your Customers', 'wpforms-lite' ); ?>
				</h2>

				<p>
					<?php esc_html_e( 'Solve common email deliverability issues for good.', 'wpforms-lite' ); ?>
				</p>

				<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpforms-smtp&source=woocommerce' ) ); ?>" class='button button-primary'>
					<?php esc_html_e( 'Get WP Mail SMTP', 'wpforms-lite' ); ?>
				</a>
			</div>

			<div class='wpforms-woocommerce-notification-image'></div>

			<i class='dashicons dashicons-no-alt' id='wpforms-woocommerce-close' title="<?php esc_attr_e( 'Close the notification', 'wpforms-lite' ); ?>"></i>
		</div>

		<?php
	}

	/**
	 * Dismiss notification.
	 *
	 * @since 1.8.9
	 */
	public function dismiss() {

		// Run a security check.
		check_ajax_referer( self::HANDLE, 'nonce' );

		// Check for permissions.
		if ( ! current_user_can( 'install_plugins' ) ) {
			wp_send_json_error();
		}

		update_option( self::OPTION_NAME, true );

		wp_send_json_success();
	}

	/**
	 * Check if the current page is WooCommerce Settings > Emails page.
	 *
	 * @since 1.8.9
	 *
	 * @return bool
	 */
	private function is_woocommerce_email_settings_page(): bool {

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return isset( $_GET['page'], $_GET['tab'] ) && $_GET['page'] === 'wc-settings' && $_GET['tab'] === 'email';
	}

	/**
	 * Check if the site has any active SMTP plugins.
	 *
	 * @since 1.8.9
	 *
	 * @return bool
	 */
	private function has_smtp_plugin(): bool {

		$smtp_plugins = [
			'wp-mail-smtp-pro/wp_mail_smtp.php',
			'wp-mail-smtp/wp_mail_smtp.php',
			'easy-wp-smtp/easy-wp-smtp.php',
			'smtp-settings-for-gravity-forms/smtp-settings-gravity-forms.php',
			'post-smtp/postman-smtp.php',
			'fluent-smtp/fluent-smtp.php',
			'gosmtp/gosmtp.php',
			'smtp-mailer/main.php',
			'wp-smtp/wp-smtp.php',
			'gmail-smtp/main.php',
			'simple-smtp/wp-simple-smtp.php',
			'bws-smtp/bws-smtp.php',
			'wp-mail-smtp-mailer/wp-mail-smtp-mailer.php',
			'welcome-email-editor/sb_welcome_email_editor.php',
			'bit-smtp/bit_smtp.php',
			'sar-friendly-smtp/sar-friendly-smtp.php',
			'smtp-mailer/main.php',
			'yaysmtp/yay-smtp.php',
			'smtp2go/smtp2go-wordpress-plugin.php',
			'mailersend-official-smtp-integration/mailersend-wordpress.php',
			'cf7-smtp/cf7-smtp.php',
			'smtp-mail/index.php',
			'mailpoet/mailpoet.php',
		];

		foreach ( $smtp_plugins as $plugin ) {
			// Check if plugin is active or installed.
			if ( is_plugin_active( $plugin ) || file_exists( WP_PLUGIN_DIR . '/' . dirname( $plugin ) ) ) {
				return true;
			}
		}

		return false;
	}
}
