<?php

namespace WPMailSMTP;

use Plugin_Upgrader;
use WP_Error;
use WPMailSMTP\Admin\PluginsInstallSkin;
use WPMailSMTP\Helpers\Helpers;

/**
 * WP Mail SMTP Connect.
 *
 * WP Mail SMTP Connect is our service that makes it easy for non-techy users to
 * upgrade to Pro version without having to manually install Pro plugin.
 *
 * @since 2.6.0
 */
class Connect {

	/**
	 * Hooks.
	 *
	 * @since 2.6.0
	 */
	public function hooks() {

		add_action( 'wp_mail_smtp_admin_area_enqueue_assets', [ $this, 'enqueue_scripts' ] );
		add_action( 'wp_ajax_wp_mail_smtp_connect_url', [ $this, 'ajax_generate_url' ] );
		add_action( 'wp_ajax_nopriv_wp_mail_smtp_connect_process', [ $this, 'process' ] );
	}

	/**
	 * Enqueue connect JS file to WP Mail SMTP admin area hook.
	 *
	 * @since 2.6.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script(
			'wp-mail-smtp-connect',
			wp_mail_smtp()->assets_url . '/js/connect' . WP::asset_min() . '.js',
			[ 'jquery' ],
			WPMS_PLUGIN_VER,
			true
		);

		wp_localize_script(
			'wp-mail-smtp-connect',
			'wp_mail_smtp_connect',
			[
				'ajax_url'   => admin_url( 'admin-ajax.php' ),
				'plugin_url' => wp_mail_smtp()->plugin_url,
				'nonce'      => wp_create_nonce( 'wp-mail-smtp-connect' ),
				'text'       => [
					'plugin_activate_btn' => esc_html__( 'Activate', 'wp-mail-smtp' ),
					'almost_done'         => esc_html__( 'Almost Done', 'wp-mail-smtp' ),
					'oops'                => esc_html__( 'Oops!', 'wp-mail-smtp' ),
					'ok'                  => esc_html__( 'OK', 'wp-mail-smtp' ),
					'server_error'        => esc_html__( 'Unfortunately there was a server connection error.', 'wp-mail-smtp' ),
				],
			]
		);
	}

	/**
	 * Generate and return WP Mail SMTP Connect URL.
	 *
	 * @since 2.6.0
	 *
	 * @param string $key        The license key.
	 * @param string $oth        The One-time hash.
	 * @param string $redirect   The redirect URL.
	 *
	 * @return bool|string
	 */
	public static function generate_url( $key, $oth = '', $redirect = '' ) {

		if ( empty( $key ) || wp_mail_smtp()->is_pro() ) {
			return false;
		}

		$oth        = ! empty( $oth ) ? $oth : hash( 'sha512', wp_rand() );
		$hashed_oth = hash_hmac( 'sha512', $oth, wp_salt() );

		$redirect = ! empty( $redirect ) ? $redirect : wp_mail_smtp()->get_admin()->get_admin_page_url();

		update_option( 'wp_mail_smtp_connect_token', $oth );
		update_option( 'wp_mail_smtp_connect', $key );

		return add_query_arg(
			[
				'key'      => $key,
				'oth'      => $hashed_oth,
				'endpoint' => admin_url( 'admin-ajax.php' ),
				'version'  => WPMS_PLUGIN_VER,
				'siteurl'  => admin_url(),
				'homeurl'  => site_url(),
				'redirect' => rawurldecode( base64_encode( $redirect ) ), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
				'v'        => 2,
			],
			'https://upgrade.wpmailsmtp.com'
		);
	}

	/**
	 * AJAX callback to generate and return the WP Mail SMTP Connect URL.
	 *
	 * @since 2.6.0
	 */
	public function ajax_generate_url() { //phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		// Run a security check.
		check_ajax_referer( 'wp-mail-smtp-connect', 'nonce' );

		// Check for permissions.
		if ( ! current_user_can( 'install_plugins' ) ) {
			wp_send_json_error(
				[
					'message' => esc_html__( 'You are not allowed to install plugins.', 'wp-mail-smtp' ),
				]
			);
		}

		$key = ! empty( $_POST['key'] ) ? sanitize_text_field( wp_unslash( $_POST['key'] ) ) : '';

		if ( empty( $key ) ) {
			wp_send_json_error(
				[
					'message' => esc_html__( 'Please enter your license key to connect.', 'wp-mail-smtp' ),
				]
			);
		}

		if ( wp_mail_smtp()->is_pro() ) {
			wp_send_json_error(
				[
					'message' => esc_html__( 'Only the Lite version can be upgraded.', 'wp-mail-smtp' ),
				]
			);
		}

		// Verify pro version is not installed.
		$active = activate_plugin( 'wp-mail-smtp-pro/wp_mail_smtp.php', false, false, true );

		if ( ! is_wp_error( $active ) ) {

			// Deactivate Lite.
			deactivate_plugins( plugin_basename( WPMS_PLUGIN_FILE ) );

			wp_send_json_success(
				[
					'message' => esc_html__( 'WP Mail SMTP Pro was already installed, but was not active. We activated it for you.', 'wp-mail-smtp' ),
					'reload'  => true,
				]
			);
		}

		$url = self::generate_url( $key );

		if ( empty( $url ) ) {
			wp_send_json_error(
				[
					'message' => esc_html__( 'There was an error while generating an upgrade URL. Please try again.', 'wp-mail-smtp' ),
				]
			);
		}

		wp_send_json_success( [ 'url' => $url ] );
	}

	/**
	 * AJAX callback to process WP Mail SMTP Connect.
	 *
	 * @since 2.6.0
	 */
	public function process() { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.MaxExceeded

		$error = esc_html__( 'There was an error while installing an upgrade. Please download the plugin from wpmailsmtp.com and install it manually.', 'wp-mail-smtp' );

		// Verify params present (oth & download link).
		$post_oth = ! empty( $_REQUEST['oth'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['oth'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		$post_url = ! empty( $_REQUEST['file'] ) ? esc_url_raw( wp_unslash( $_REQUEST['file'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

		if ( empty( $post_oth ) || empty( $post_url ) ) {
			wp_send_json_error( $error );
		}

		// Verify oth.
		$oth = get_option( 'wp_mail_smtp_connect_token' );

		if ( empty( $oth ) ) {
			wp_send_json_error( $error );
		}

		if ( hash_hmac( 'sha512', $oth, wp_salt() ) !== $post_oth ) {
			wp_send_json_error( $error );
		}

		// Delete so cannot replay.
		delete_option( 'wp_mail_smtp_connect_token' );

		// Set the current screen to avoid undefined notices.
		set_current_screen( 'toplevel_page_wp-mail-smtp' );

		// Prepare variables.
		$url = esc_url_raw( wp_mail_smtp()->get_admin()->get_admin_page_url() );

		// Verify pro not activated.
		if ( wp_mail_smtp()->is_pro() ) {
			wp_send_json_success( esc_html__( 'Plugin installed & activated.', 'wp-mail-smtp' ) );
		}

		// Verify pro not installed.
		$active = activate_plugin( 'wp-mail-smtp-pro/wp_mail_smtp.php', $url, false, true );

		if ( ! is_wp_error( $active ) ) {
			deactivate_plugins( plugin_basename( WPMS_PLUGIN_FILE ) );
			wp_send_json_success( esc_html__( 'Plugin installed & activated.', 'wp-mail-smtp' ) );
		}

		/*
		 * The `request_filesystem_credentials` function will output a credentials form in case of failure.
		 * We don't want that, since it will break AJAX response. So just hide output with a buffer.
		 */
		ob_start();
		// phpcs:ignore WPForms.Formatting.EmptyLineAfterAssigmentVariables.AddEmptyLine
		$creds = request_filesystem_credentials( $url, '', false, false, null );
		ob_end_clean();

		// Check for file system permissions.
		$perm_error = esc_html__( 'There was an error while installing an upgrade. Please check file system permissions and try again. Also, you can download the plugin from wpmailsmtp.com and install it manually.', 'wp-mail-smtp' );

		if ( false === $creds || ! WP_Filesystem( $creds ) ) {
			wp_send_json_error( $perm_error );
		}

		/*
		 * We do not need any extra credentials if we have gotten this far, so let's install the plugin.
		 */

		// Do not allow WordPress to search/download translations, as this will break JS output.
		remove_action( 'upgrader_process_complete', array( 'Language_Pack_Upgrader', 'async_upgrade' ), 20 );

		// Import the plugin upgrader.
		Helpers::include_plugin_upgrader();

		// Create the plugin upgrader with our custom skin.
		$installer = new Plugin_Upgrader( new PluginsInstallSkin() );

		// Error check.
		if ( ! method_exists( $installer, 'install' ) ) {
			wp_send_json_error( $error );
		}

		// Check license key.
		$key = get_option( 'wp_mail_smtp_connect', false );
		delete_option( 'wp_mail_smtp_connect' );

		if ( empty( $key ) ) {
			wp_send_json_error(
				new WP_Error(
					'403',
					esc_html__( 'There was an error while installing an upgrade. Please try again.', 'wp-mail-smtp' )
				)
			);
		}

		$installer->install( $post_url );

		// Flush the cache and return the newly installed plugin basename.
		wp_cache_flush();

		$plugin_basename = $installer->plugin_info();

		if ( $plugin_basename ) {

			// Deactivate the lite version first.
			deactivate_plugins( plugin_basename( WPMS_PLUGIN_FILE ) );

			// Activate the plugin silently.
			$activated = activate_plugin( $plugin_basename, '', false, true );

			if ( ! is_wp_error( $activated ) ) {

				// Save the license data, since it was verified on the connect page.
				$options = Options::init();
				$all_opt = $options->get_all_raw();

				$all_opt['license']['key']              = $key;
				$all_opt['license']['type']             = 'pro';
				$all_opt['license']['is_expired']       = false;
				$all_opt['license']['is_disabled']      = false;
				$all_opt['license']['is_invalid']       = false;
				$all_opt['license']['is_limit_reached'] = false;

				$options->set( $all_opt, false, true );

				wp_send_json_success( esc_html__( 'Plugin installed & activated.', 'wp-mail-smtp' ) );
			} else {
				// Reactivate the lite plugin if pro activation failed.
				activate_plugin( plugin_basename( WPMS_PLUGIN_FILE ), '', false, true );
				wp_send_json_error( esc_html__( 'Pro version installed but needs to be activated on the Plugins page.', 'wp-mail-smtp' ) );
			}
		}

		wp_send_json_error( $error );
	}
}
