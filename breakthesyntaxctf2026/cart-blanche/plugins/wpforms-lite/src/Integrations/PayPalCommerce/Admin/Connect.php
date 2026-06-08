<?php

namespace WPForms\Integrations\PayPalCommerce\Admin;

use RuntimeException;
use WPForms\Helpers\Transient;
use WPForms\Integrations\PayPalCommerce\Api\Api;
use WPForms\Integrations\PayPalCommerce\Api\WebhooksManager;
use WPForms\Integrations\PayPalCommerce\Connection;
use WPForms\Integrations\PayPalCommerce\Helpers;
use WPForms\Integrations\PayPalCommerce\PaymentMethods\ApplePay\DomainManager;
use WPForms\Integrations\PayPalCommerce\PayPalCommerce;

/**
 * PayPal Commerce Connect functionality.
 *
 * @since 1.10.0
 */
class Connect {

	/**
	 * WPForms website URL.
	 *
	 * @since 1.10.0
	 */
	public const WPFORMS_URL = 'https://wpformsapi.com/paypal/v1';

	/**
	 * Disconnect nonce.
	 *
	 * @since 1.10.0
	 */
	public const DISCONNECT_ACTION_NONCE = 'wpforms_paypal_commerce_disconnect';

	/**
	 * Merchant info name.
	 *
	 * @since 1.10.0
	 */
	public const MERCHANT_INFO_TRANSIENT_NAME = 'wpforms_paypal_commerce_merchant_info_';

	/**
	 * Signup transient name.
	 *
	 * @since 1.10.0
	 */
	private const SIGNUP_TRANSIENT_NAME = 'wpforms_paypal_commerce_signup_link_';

	/**
	 * Signup Site URL transient name.
	 *
	 * @since 1.10.0.3
	 */
	private const SIGNUP_SITE_URL_TRANSIENT_NAME = 'wpforms_paypal_commerce_signup_site_url';

	/**
	 * Lock Signup transient name.
	 *
	 * @since 1.10.0
	 */
	private const LOCK_SIGNUP_TRANSIENT_NAME = 'wpforms_paypal_commerce_lock_signup_link_';

	/**
	 * Lock Connect option name.
	 *
	 * @since 1.10.0
	 */
	private const LOCK_CONNECT_OPTION_NAME = 'wpforms_paypal_commerce_lock_connect_';

	/**
	 * Unlock Connect transient name.
	 *
	 * @since 1.10.0
	 */
	private const UNLOCK_CONNECT_TRANSIENT_NAME = 'wpforms_paypal_commerce_unlock_connect_';

	/**
	 * Secret Signup transient name.
	 *
	 * @since 1.10.0
	 */
	private const SECRET_SIGNUP_TRANSIENT_NAME = 'wpforms_paypal_commerce_secret_signup_link_';

	/**
	 * Refresh the signup key.
	 *
	 * @since 1.10.0
	 */
	private const REFRESH_SIGNUP_KEY = 'paypal_commerce_refresh_signup';

	/**
	 * Init class.
	 *
	 * @since 1.10.0
	 */
	public function init(): Connect {

		$this->hooks();

		return $this;
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.10.0
	 */
	public function hooks(): void {

		add_action( 'admin_init', [ $this, 'handle_actions' ] );
		add_action( 'update_option_wpforms_license', [ $this, 'update_license_option' ], 10, 3 );
		add_action( 'add_option_wpforms_license', [ $this, 'add_license_option' ], 10, 2 );
	}

	/**
	 * Handle actions.
	 *
	 * @since 1.10.0
	 */
	public function handle_actions(): void {

		if ( wp_doing_ajax() || ! wpforms_current_user_can() ) {
			return;
		}

		$this->validate_scopes();

		if ( ! wpforms_is_admin_page( 'settings', 'payments' ) ) {
			return;
		}

		if (
			isset( $_GET['merchantId'], $_GET['merchantIdInPayPal'] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		) {
			$this->handle_connect();

			return;
		}

		if (
			isset( $_GET['_wpnonce'] ) &&
			wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ), self::DISCONNECT_ACTION_NONCE )
		) {
			$this->handle_disconnect();
		}
	}

	/**
	 * Update license for the connected customer.
	 *
	 * @since 1.10.0
	 *
	 * @param mixed  $old_value Old license value.
	 * @param mixed  $value     New license value.
	 * @param string $option    Option name.
	 *
	 * @noinspection PhpUnusedParameterInspection
	 * @noinspection PhpMissingParamTypeInspection
	 */
	public function update_license_option( $old_value, $value, $option ): void { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed

		$this->update_license( $value['key'] ?? null );
	}

	/**
	 * Update license for the connected customer.
	 *
	 * @since 1.10.0
	 *
	 * @param string $option Option name.
	 * @param mixed  $value  License value.
	 *
	 * @noinspection PhpUnusedParameterInspection
	 * @noinspection PhpMissingParamTypeInspection
	 */
	public function add_license_option( $option, $value ): void { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed

		$this->update_license( $value['key'] ?? null );
	}

	/**
	 * Update license for the connected customer.
	 *
	 * @since 1.10.0
	 *
	 * @param string|null $license License key value.
	 */
	private function update_license( $license ): void {

		$connection = Connection::get();

		if ( ! $connection ) {
			return;
		}

		$api = PayPalCommerce::get_api( $connection );

		if ( ! $api || ! method_exists( $api, 'update_customer' ) ) {
			return;
		}

		$api->update_customer( [ 'license_key' => $license ] );
	}

	/**
	 * Handle connection.
	 *
	 * @since 1.10.0
	 *
	 * @throws RuntimeException Credentials request was failed.
	 */
	private function handle_connect(): void {

		$mode          = Helpers::get_mode();
		$settings_page = Helpers::get_settings_page_url();

		// If already processing, let the first request finish.
		if ( ! add_option( self::LOCK_CONNECT_OPTION_NAME . $mode, time() ) ) {
			$start = microtime( true );
			// Wait up to 5 seconds for the first request to completion.
			while ( microtime( true ) - $start < 5 ) {
				if ( Transient::get( self::UNLOCK_CONNECT_TRANSIENT_NAME . $mode ) ) {
					break;
				}
				usleep( 200000 ); // 200 ms
			}

			wp_safe_redirect( $settings_page );
			exit;
		}

		try {
			$connection_data = $this->get_credentials();

			if ( ! $connection_data ) {
				throw new RuntimeException( 'Missing or invalid connection credentials.' );
			}

			$connection    = new Connection( $connection_data );
			$api           = new Api( $connection );
			$connection    = self::refresh_access_token( $connection, $api );
			$merchant_info = $api->get_merchant_info();
			$status        = $connection->validate_permissions( $merchant_info );

			$connection->set_status( $status )->save();

			// Sync the settings mode with a connection mode.
			Helpers::set_mode( $mode );

			( new WebhooksManager() )->connect();

			/**
			 * Fires after successful PayPal Commerce connection.
			 *
			 * @since 1.10.0
			 *
			 * @param Connection $connection Connection instance.
			 * @param string     $mode       Connection mode.
			 */
			do_action( 'wpforms_integrations_paypal_commerce_admin_connect_after_handle', $connection, $mode ); // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName

			// Clear the transients.
			Transient::delete( self::SECRET_SIGNUP_TRANSIENT_NAME . $mode );
			Transient::delete( self::LOCK_SIGNUP_TRANSIENT_NAME . $mode );
			Transient::delete( self::SIGNUP_TRANSIENT_NAME . $mode );
		} catch ( \Exception $e ) {
			Helpers::log_errors(
				'PayPal Connect error.',
				'',
				$e->getMessage()
			);
		} finally {
			delete_option( self::LOCK_CONNECT_OPTION_NAME . $mode );
			Transient::set( self::UNLOCK_CONNECT_TRANSIENT_NAME . $mode, 1, 10 );
			wp_safe_redirect( $settings_page );
			exit;
		}
	}

	/**
	 * Get credentials.
	 *
	 * @since 1.10.0
	 *
	 * @return array
	 */
	private function get_credentials(): array {

		$mode = Helpers::get_mode();

		$merchant_id     = sanitize_text_field( wp_unslash( $_GET['merchantIdInPayPal'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotValidated
		$secret          = sanitize_text_field( wp_unslash( $_GET['merchantId'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotValidated
		$referral_link   = Transient::get( self::SIGNUP_TRANSIENT_NAME . $mode );
		$referral_params = [];

		if ( $secret !== Transient::get( self::SECRET_SIGNUP_TRANSIENT_NAME . $mode ) ) {
			Helpers::log_errors(
				'PayPal Secret mismatch detected.',
				'',
				$referral_link
			);

			return [];
		}

		if ( $referral_link ) {
			$referral_query = wp_parse_url( $referral_link, PHP_URL_QUERY );

			parse_str( $referral_query, $referral_params );
		}

		$credentials_response = wp_remote_post(
			self::get_server_url() . '/oauth/credentials',
			[
				'body'    => [
					'secret'         => $secret,
					'merchant_id'    => $merchant_id,
					'referral_token' => $referral_params['referralToken'] ?? '',
					'license_key'    => wpforms_get_license_key(),
					'webhooks_url'   => Helpers::get_webhook_url(),
					'site_url'       => site_url(),
					'live_mode'      => (int) ( $mode === Helpers::PRODUCTION ),
				],
				'timeout' => 15,
			]
		);

		if ( is_wp_error( $credentials_response ) ) {
			Helpers::log_errors(
				'PayPal Credentials Error.',
				'',
				$credentials_response
			);

			return [];
		}

		$body = wp_remote_retrieve_body( $credentials_response );

		$connection_data = json_decode( $body, true );

		$required_keys = [ 'partner_merchant_id', 'client_id', 'client_token', 'client_token_expires_in', 'sdk_client_token', 'sdk_client_token_expires_in' ];

		if ( empty( $connection_data ) || count( array_diff( $required_keys, array_keys( $connection_data ) ) ) !== 0 ) {
			Helpers::log_errors(
				'PayPal Connection data missed required keys.',
				'',
				$connection_data
			);

			return [];
		}

		$connection_data['merchant_id'] = $merchant_id;
		$connection_data['secret']      = $secret;
		$connection_data['type']        = Connection::TYPE_THIRD_PARTY;

		return $connection_data;
	}

	/**
	 * Handle disconnection.
	 *
	 * @since 1.10.0
	 */
	private function handle_disconnect(): void {

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$live_mode  = isset( $_GET['live_mode'] ) ? absint( $_GET['live_mode'] ) : 0;
		$mode       = $live_mode ? Helpers::PRODUCTION : Helpers::SANDBOX;
		$connection = Connection::get( $mode );

		if ( ! $connection ) {
			return;
		}

		if ( ! Helpers::is_legacy() ) {
			if ( Helpers::is_webhook_enabled() ) {
				// Disconnect webhooks.
				PayPalCommerce::get_webhooks_manager()->disconnect_webhook();
			}

			$api = PayPalCommerce::get_api( $connection );

			// Ensure we have a fresh access token before any API calls.
			self::refresh_access_token( $connection, $api );

			/**
			 * Fires before PayPal Commerce disconnection.
			 *
			 * @since 1.10.0
			 *
			 * @param Connection $connection Connection instance.
			 * @param string     $mode       Connection mode.
			 */
			do_action( 'wpforms_integrations_paypal_commerce_admin_disconnect_before_handle', $connection, $mode ); // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName

			$disconnected = $api->disconnect();

			if ( ! $disconnected ) {
				Helpers::log_errors(
					'PayPal Disconnect failed.',
					'',
					'Unauthorized or other API failure.'
				);

				wp_safe_redirect( add_query_arg( 'paypal_commerce_disconnect', 'failed', Helpers::get_settings_page_url() ) );
				exit;
			}
		}

		$connection->delete();

		Transient::delete( self::SECRET_SIGNUP_TRANSIENT_NAME . $mode );
		Transient::delete( self::LOCK_SIGNUP_TRANSIENT_NAME . $mode );
		Transient::delete( self::SIGNUP_TRANSIENT_NAME . $mode );
		Transient::delete( self::MERCHANT_INFO_TRANSIENT_NAME . $mode );
		Transient::delete( DomainManager::DOMAIN_REGISTERED_TRANSIENT_NAME . $mode );

		wp_safe_redirect( Helpers::get_settings_page_url() );
		exit;
	}

	/**
	 * Refresh access token.
	 *
	 * @since 1.10.0
	 *
	 * @param Connection|\WPFormsPaypalCommerce\Connection $connection Current Connection.
	 * @param Api|\WPFormsPaypalCommerce\Api\Api           $api        Current API.
	 *
	 * @return Connection|\WPFormsPaypalCommerce\Connection
	 */
	public static function refresh_access_token( $connection, $api = null ) {

		if ( is_null( $api ) ) {
			$api = PayPalCommerce::get_api( $connection );
		}

		if ( is_null( $api ) ) {
			return $connection;
		}

		$access_token = $api->generate_access_token();

		if ( ! empty( $access_token['access_token'] ) ) {
			$connection->set_access_token( $access_token['access_token'] )->set_access_token_expires_in( time() + $access_token['expires_in'] )->save();
		}

		return $connection;
	}

	/**
	 * Refresh client token.
	 *
	 * @since 1.10.0
	 *
	 * @param Connection|\WPFormsPaypalCommerce\Connection $connection Current Connection.
	 *
	 * @return Connection|\WPFormsPaypalCommerce\Connection
	 */
	public static function refresh_client_token( $connection ) {

		$api = PayPalCommerce::get_api( $connection );

		if ( is_null( $api ) ) {
			return $connection;
		}

		$client_token = $api->generate_client_token();

		if ( ! empty( $client_token['client_token'] ) ) {
			$connection->set_client_token( $client_token['client_token'] )->set_client_token_expires_in( time() + $client_token['expires_in'] )->save();
		}

		return $connection;
	}

	/**
	 * Refresh client token.
	 *
	 * @since 1.10.0
	 *
	 * @param Connection|\WPFormsPaypalCommerce\Connection $connection Current Connection.
	 *
	 * @return Connection|\WPFormsPaypalCommerce\Connection
	 */
	public static function refresh_sdk_client_token( $connection ) {

		$api = PayPalCommerce::get_api( $connection );

		if ( is_null( $api ) ) {
			return $connection;
		}

		$sdk_client_token = $api->generate_sdk_client_token();

		if ( ! empty( $sdk_client_token['sdk_client_token'] ) ) {
			$connection->set_sdk_client_token( $sdk_client_token['sdk_client_token'] )->set_sdk_client_token_expires_in( time() + $sdk_client_token['expires_in'] )->save();
		}

		return $connection;
	}

	/**
	 * Get Connect URL.
	 *
	 * @since 1.10.0
	 *
	 * @param string $mode Connection mode.
	 *
	 * @return string
	 */
	public function get_connect_url( string $mode ): string {

		$mode = Helpers::validate_mode( $mode );

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET[ self::REFRESH_SIGNUP_KEY ] ) ) {
			Transient::delete( self::LOCK_SIGNUP_TRANSIENT_NAME . $mode );
			Transient::delete( self::SIGNUP_TRANSIENT_NAME . $mode );
		}

		if ( Transient::get( self::LOCK_SIGNUP_TRANSIENT_NAME . $mode ) ) {
			return '';
		}

		$link     = Transient::get( self::SIGNUP_TRANSIENT_NAME . $mode );
		$site_url = remove_query_arg( self::REFRESH_SIGNUP_KEY, wpforms_current_url() );

		if ( ! empty( $link ) && $site_url === Transient::get( self::SIGNUP_SITE_URL_TRANSIENT_NAME ) ) {
			return (string) $link;
		}

		$secret = Transient::get( self::SECRET_SIGNUP_TRANSIENT_NAME . $mode );

		if ( empty( $secret ) ) {
			$secret = bin2hex( random_bytes( 16 ) );

			Transient::set( self::SECRET_SIGNUP_TRANSIENT_NAME . $mode, $secret );
		}

		$response = wp_remote_post(
			self::get_server_url() . '/oauth/partner-referral',
			[
				'body'    => [
					'secret'    => $secret,
					'site_url'  => $site_url,
					'live_mode' => (int) ( $mode === Helpers::PRODUCTION ),
				],
				'timeout' => 15,
			]
		);

		if ( ! is_wp_error( $response ) && wp_remote_retrieve_response_code( $response ) === 200 ) {
			$body = json_decode( wp_remote_retrieve_body( $response ), true );
			$link = isset( $body['url'] ) ? $body['url'] . '&displayMode=minibrowser' : '';

			if ( $link ) {
				Transient::set( self::SIGNUP_TRANSIENT_NAME . $mode, $link, $body['expires_in'] );
				Transient::set( self::SIGNUP_SITE_URL_TRANSIENT_NAME, $site_url );

				return $link;
			}
		}

		Transient::set( self::LOCK_SIGNUP_TRANSIENT_NAME . $mode, true, HOUR_IN_SECONDS );

		return '';
	}

	/**
	 * Validate connection scopes.
	 *
	 * @since 1.10.0
	 */
	private function validate_scopes(): void {

		$connection = Connection::get();

		if ( ! $connection || ! $connection->is_configured() || ! Helpers::is_legacy() ) {
			return;
		}

		$status = Helpers::is_license_ok() && Helpers::is_addon_active() ? 'valid' : 'invalid';

		$connection->update_connection_status( $status );
	}

	/**
	 * Retrieve connect server URL.
	 *
	 * @since 1.10.0
	 *
	 * @return string
	 */
	public static function get_server_url(): string {

		// Use a local server if constant set.
		if ( defined( 'WPFORMS_PAYPAL_COMMERCE_LOCAL_CONNECT_SERVER' ) && WPFORMS_PAYPAL_COMMERCE_LOCAL_CONNECT_SERVER ) {
			return home_url();
		}

		// Use a custom server if constant set.
		if ( defined( 'WPFORMS_PAYPAL_COMMERCE_CONNECT_SERVER' ) && WPFORMS_PAYPAL_COMMERCE_CONNECT_SERVER ) {
			return WPFORMS_PAYPAL_COMMERCE_CONNECT_SERVER;
		}

		/**
		 * Filter connect server URL.
		 *
		 * @since 1.10.0
		 *
		 * @param string $server_url Server URL.
		 */
		return (string) apply_filters( 'wpforms_paypal_commerce_connect_server_url', self::WPFORMS_URL ); // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName
	}
}
