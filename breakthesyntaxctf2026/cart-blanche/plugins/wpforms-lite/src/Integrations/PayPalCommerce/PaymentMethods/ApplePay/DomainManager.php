<?php

namespace WPForms\Integrations\PayPalCommerce\PaymentMethods\ApplePay;

use WPForms\Admin\Notice;
use WPForms\Helpers\File;
use WPForms\Helpers\Transient;
use WPForms\Integrations\PayPalCommerce\Api\Api;
use WPForms\Integrations\PayPalCommerce\Connection;
use WPForms\Integrations\PayPalCommerce\Helpers;
use WPForms\Integrations\PayPalCommerce\PayPalCommerce;

/**
 * Domain Health Check class for PayPal Commerce Apple Pay verification.
 *
 * @since 1.10.0
 */
class DomainManager {

	/**
	 * Admin notice ID.
	 *
	 * @since 1.10.0
	 */
	public const NOTICE_ID = 'wpforms_paypal_commerce_domain_site_health';

	/**
	 * Is domain registered option flag.
	 *
	 * @since 1.10.0
	 */
	public const DOMAIN_REGISTERED_TRANSIENT_NAME = 'wpforms_paypal_commerce_domain_registered_';

	/**
	 * Initialization.
	 *
	 * @since 1.10.0
	 */
	public function init(): void {

		$this->hooks();
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.10.0
	 */
	private function hooks(): void {

		add_action( 'admin_notices', [ $this, 'admin_notice' ] );
		add_action( 'wp_ajax_wpforms_paypal_commerce_dismiss_domain_notice', [ $this, 'dismiss_notice' ] );
		add_action( 'wpforms_integrations_paypal_commerce_admin_connect_after_handle', [ $this, 'after_admin_connect' ], 10, 2 );
		add_action( 'wpforms_integrations_paypal_commerce_admin_disconnect_before_handle', [ $this, 'before_admin_disconnect' ], 10, 2 );
	}

	/**
	 * After an admin connect.
	 *
	 * @since 1.10.0
	 *
	 * @param Connection|null $connection Connection instance.
	 * @param string          $mode       Connection mode.
	 */
	public function after_admin_connect( $connection, string $mode ): void {

		// The domain association file needs to be available before the API call.
		$this->maybe_create_domain_association_file();
		$this->maybe_register_domain( $connection, $mode );
	}

	/**
	 * Before an admin disconnect.
	 *
	 * @since 1.10.0
	 *
	 * @param Connection|null $connection Connection instance.
	 * @param string          $mode       Connection mode.
	 */
	public function before_admin_disconnect( $connection, string $mode ): void {

		$this->maybe_deregister_domain( $connection, $mode );
	}

	/**
	 * Get the domain verification notice message.
	 *
	 * @since 1.10.0
	 *
	 * @return string
	 */
	public function get_notice_message(): string {

		return sprintf(
			wp_kses( /* translators: %1$s - PayPal Developer documentation URL for domain registration. */
				__( 'If you haven\'t registered your domain yet, Apple Pay may not work properly. If this notice persists, please <a href="%1$s" rel="nofollow noopener" target="_blank">verify your domain registration.</a>', 'wpforms-lite' ),
				[
					'a' => [
						'href'   => [],
						'target' => [],
						'rel'    => [],
					],
				]
			),
			wpforms_utm_link( 'https://wpforms.com/docs/paypal-commerce-addon/#apple-pay', 'Payment Settings', 'PayPal Commerce Apple Pay Documentation' )
		);
	}

	/**
	 * Display notice about issues with the domain.
	 *
	 * @since 1.10.0
	 *
	 * @return void
	 */
	public function admin_notice(): void {

		$connection = Connection::get();

		// Bail out if conditions are not met.
		if ( ! wpforms_is_admin_page( 'settings' ) || ! $connection ) {
			return;
		}

		$api = PayPalCommerce::get_api( $connection );

		if ( $this->is_domain_registered( $api ) ) {
			return;
		}

		Notice::warning(
			$this->get_notice_message(),
			[
				'dismiss' => Notice::DISMISS_USER,
				'slug'    => self::NOTICE_ID,
			]
		);
	}

	/**
	 * Handle AJAX request to dismiss the domain notice.
	 *
	 * @since 1.10.0
	 *
	 * @return void
	 */
	public function dismiss_notice(): void {

		// Verify nonce.
		if ( ! check_ajax_referer( 'wpforms-builder', 'nonce', false ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Security check failed.', 'wpforms-lite' ) ] );
		}

		// Update user meta to mark the notice as dismissed.
		$user_id                    = get_current_user_id();
		$notices                    = get_user_meta( $user_id, 'wpforms_admin_notices', true );
		$notices                    = ! is_array( $notices ) ? [] : $notices;
		$notices[ self::NOTICE_ID ] = [
			'time'      => time(),
			'dismissed' => true,
		];

		update_user_meta( $user_id, 'wpforms_admin_notices', $notices );
		wp_send_json_success();
	}

	/**
	 * Check if the domain is registered.
	 *
	 * @since 1.10.0
	 *
	 * @param mixed  $api    Api class.
	 * @param string $mode   Connection mode.
	 * @param string $domain Domain.
	 *
	 * @return bool
	 */
	public function is_domain_registered( $api, string $mode = '', string $domain = '' ): bool {

		if ( ! $api instanceof Api || Helpers::is_legacy() ) {
			return $this->is_dismissed_notice();
		}

		if ( ! $mode ) {
			$mode = Helpers::get_mode();
		}

		if ( Transient::get( self::DOMAIN_REGISTERED_TRANSIENT_NAME . $mode ) ) {
			return true;
		}

		if ( ! $domain ) {
			$domain = $this->get_domain_name();
		}

		$registered_domains = [];
		$page_size          = 50;
		$page               = 1;

		do {
			$domains_response = $api->list_domains( $page_size, $page );

			if ( empty( $domains_response['wallet_domains'] ) ) {
				return false;
			}

			foreach ( $domains_response['wallet_domains'] as $domain_item ) {
				$registered_domains[] = $domain_item['domain']['name'];
			}

			$total_pages = $domains_response['total_pages'] ?? 1;

			++$page;

		} while ( $page <= $total_pages );

		$is_registered = in_array( $domain, $registered_domains, true );

		Transient::set( self::DOMAIN_REGISTERED_TRANSIENT_NAME . $mode, $is_registered, DAY_IN_SECONDS );

		return $is_registered;
	}

	/**
	 * Check if the notice is marked as available.
	 *
	 * @since 1.10.0
	 *
	 * @return bool
	 */
	private function is_dismissed_notice(): bool {

		$user_id = get_current_user_id();
		$notices = get_user_meta( $user_id, 'wpforms_admin_notices', true );

		return ! empty( $notices[ self::NOTICE_ID ]['dismissed'] );
	}

	/**
	 * Maybe register a domain.
	 *
	 * @since 1.10.0
	 *
	 * @param Connection|null $connection Connection instance.
	 * @param string          $mode       Connection mode.
	 */
	private function maybe_register_domain( $connection, string $mode ): void {

		if ( ! $connection ) {
			return;
		}

		$api    = PayPalCommerce::get_api( $connection );
		$domain = $this->get_domain_name();

		if ( ! $api instanceof Api || $this->is_domain_registered( $api, $mode, $domain ) ) {
			return;
		}

		$api->register_domain( $domain );
	}

	/**
	 * Maybe de-register a domain.
	 *
	 * @since 1.10.0
	 *
	 * @param Connection|null $connection Connection instance.
	 * @param string          $mode       Connection mode.
	 */
	private function maybe_deregister_domain( $connection, string $mode ): void {

		if ( ! $connection ) {
			return;
		}

		$api    = PayPalCommerce::get_api( $connection );
		$domain = $this->get_domain_name();

		if ( ! $api instanceof Api || ! $this->is_domain_registered( $api, $mode, $domain ) ) {
			return;
		}

		$api->deregister_domain( $domain, 'Account Disconnected' );
	}

	/**
	 * Maybe create a domain association file.
	 *
	 * @since 1.10.0
	 */
	private function maybe_create_domain_association_file(): void {

		$wp_filesystem = File::get_filesystem();

		if ( is_null( $wp_filesystem ) ) {
			return;
		}

		$association_dir = $wp_filesystem->abspath() . '.well-known';

		// Ensure the directory exists. Only try to create it if it doesn't.
		if ( ! $wp_filesystem->is_dir( $association_dir ) && ! $wp_filesystem->mkdir( $association_dir, 0755 ) ) {
			$this->log_errors( 'Unable to create domain association folder in site root.' );
			return;
		}

		$base_file_name   = 'apple-developer-merchantid-domain-association';
		$source_file_name = Helpers::get_mode() . '-' . $base_file_name;
		$association_file = trailingslashit( $association_dir ) . $base_file_name;
		$source           = WPFORMS_PLUGIN_DIR . 'src/Integrations/PayPalCommerce/PaymentMethods/ApplePay/' . $source_file_name;

		// Copy/overwrite the association file into the directory.
		if ( ! $wp_filesystem->copy( $source, $association_file, true ) ) {
			$this->log_errors( 'Unable to copy domain association file to domain .well-known directory.' );
		}
	}

	/**
	 * Get the domain name.
	 *
	 * @since 1.10.0
	 *
	 * @return string
	 */
	private function get_domain_name(): string {

		$hostname = wp_parse_url( home_url(), PHP_URL_HOST );

		if ( ! $hostname ) {
			return '';
		}

		return (string) $hostname;
	}

	/**
	 * Log errors for the PayPal Commerce Domain Manager.
	 *
	 * @since 1.10.0
	 *
	 * @param string $message The error message to be logged.
	 */
	private function log_errors( string $message ): void {

		Helpers::log_errors( 'PayPal Commerce: Domain Manager',0, $message );
	}
}
