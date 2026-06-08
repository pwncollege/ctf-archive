<?php

namespace WPForms\Integrations\Square\Admin;

use WPForms\Integrations\Square\Api\Api;
use WPForms\Vendor\Square\Models\Location;
use WPForms\Vendor\Square\Models\LocationCapability;
use WPForms\Vendor\Square\Models\LocationStatus;
use WPForms\Vendor\Square\Environment;
use WP_Error;
use WPForms\Admin\Notice;
use WPForms\Helpers\Transient;
use WPForms\Tasks\Tasks;
use WPForms\Integrations\Square\Connection;
use WPForms\Integrations\Square\Helpers;
use WPForms\Integrations\Square\Api\WebhooksManager;

/**
 * Square Connect functionality.
 *
 * @since 1.9.5
 */
class Connect {

	/**
	 * WPForms website URL.
	 *
	 * @since 1.9.5
	 */
	private const WPFORMS_URL = 'https://wpforms.com';

	/**
	 * Webhooks manager.
	 *
	 * @since 1.9.5
	 *
	 * @var WebhooksManager
	 */
	private $webhooks_manager;

	/**
	 * Initialize.
	 *
	 * @since 1.9.5
	 *
	 * @return Connect
	 */
	public function init() {

		$this->webhooks_manager = new WebhooksManager();

		$this->hooks();

		return $this;
	}

	/**
	 * Connect hooks.
	 *
	 * @since 1.9.5
	 */
	private function hooks() {

		add_action( 'admin_init',                                [ $this, 'handle_actions' ] );
		add_action( 'wpforms_square_refresh_connection',         [ $this, 'refresh_connection_schedule' ] );
		add_action( 'wp_ajax_wpforms_square_refresh_connection', [ $this, 'refresh_connection_manual' ] );
		add_action( 'wp_ajax_wpforms_square_create_webhook',     [ $this->webhooks_manager, 'connect' ] );
	}

	/**
	 * Handle actions.
	 *
	 * @since 1.9.5
	 */
	public function handle_actions() {

		if ( ! wpforms_current_user_can() || wp_doing_ajax() ) {
			return;
		}

		$this->validate_scopes();

		if (
			isset( $_GET['_wpnonce'] ) &&
			wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ), 'wpforms_square_disconnect' )
		) {
			$this->handle_disconnect();

			return;
		}

		$this->schedule_refresh();

		if ( $this->is_valid_connect_request() ) {
			$this->handle_connected();
		}
	}

	/**
	 * Validate if the current connect request is valid.
	 *
	 * @since 1.10.0.3
	 *
	 * @return bool
	 */
	private function is_valid_connect_request(): bool {

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! isset( $_GET['square_connect'] ) || sanitize_key( $_GET['square_connect'] ) !== 'complete' ) {
			return false;
		}

		$state = isset( $_GET['state'] ) ? sanitize_text_field( wp_unslash( $_GET['state'] ) ) : '';

		if ( empty( $state ) ) {
			return false;
		}

		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ), 'wpforms_square_connect' ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Validate connection scopes.
	 *
	 * @since 1.9.5
	 */
	private function validate_scopes() {

		if ( Helpers::is_license_ok() ) {
			return;
		}

		$connection = Connection::get();

		if ( ! $connection || ! $connection->is_configured() ) {
			return;
		}

		// Bail early if currency is not supported for applying a fee.
		if ( ! Helpers::is_application_fee_supported() ) {
			return;
		}

		if ( $connection->get_scopes_updated() ) {
			return;
		}

		// Revoke tokens if the license is not valid and scopes are missing.
		$connection->revoke_tokens();
	}

	/**
	 * Handle a successful connection.
	 *
	 * @since 1.9.5
	 */
	private function handle_connected() {

		$state = sanitize_text_field( wp_unslash( $_GET['state'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotValidated

		if ( empty( $state ) ) {
			return;
		}

		$connection_raw = $this->fetch_new_connection( $state );
		$connection     = $this->maybe_save_connection( $connection_raw );

		if ( ! $connection ) {
			return;
		}

		$mode = $connection->get_mode();

		// Sync the Square settings mode with a connection mode.
		Helpers::set_mode( $mode );

		$this->prepare_locations( $mode );

		$redirect_url = Helpers::get_settings_page_url();

		if ( ! $connection->is_usable() ) {
			$redirect_url .= '#wpforms-setting-row-square-heading';
		}

		wp_safe_redirect( $redirect_url );
		exit;
	}

	/**
	 * Handle disconnection.
	 *
	 * @since 1.9.5
	 */
	private function handle_disconnect() {

		$live_mode  = isset( $_GET['live_mode'] ) ? absint( $_GET['live_mode'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$mode       = $live_mode ? Environment::PRODUCTION : Environment::SANDBOX;
		$connection = Connection::get( $mode );

		if ( $connection ) {
			$connection->delete();
		}

		if ( Helpers::is_production_mode() ) {
			$this->unschedule_refresh();
		}

		if ( Helpers::is_webhook_enabled() ) {
			Helpers::reset_webhook_configuration( true );
		}

		Helpers::set_locataion_id( '', $mode );
		Helpers::detete_transients( $mode );

		wp_safe_redirect( Helpers::get_settings_page_url() );
		exit;
	}

	/**
	 * Handle refresh connection triggered by AS task.
	 *
	 * @since 1.9.5
	 */
	public function refresh_connection_schedule() {

		// Don't run refresh tokens for Sandbox connection.
		if ( Helpers::is_sandbox_mode() ) {
			return;
		}

		$connection = Connection::get();

		// Check connection and cancel AS task if connection is not exists, broken OR already invalid.
		if ( ! $connection || ! $connection->is_configured() || ! $connection->is_valid() ) {
			$this->unschedule_refresh();

			return;
		}

		// If connection is not expired, we'll just fetch active locations.
		if ( ! $connection->is_expired() ) {
			$this->prepare_locations( $connection->get_mode() );

			return;
		}

		// If connection is expired, try to refresh tokens.
		$connection = $this->try_refresh_connection( $connection );

		if ( is_wp_error( $connection ) ) {
			return;
		}

		// If connection is invalid, we'll cancel AS task.
		if ( $connection && ! $connection->is_valid() ) {
			$this->unschedule_refresh();

			return;
		}

		// Make sure and check connection tokens through fetching active locations.
		$this->prepare_locations( $connection->get_mode() );
	}

	/**
	 * Handle refresh connection triggered manually.
	 *
	 * @since 1.9.5
	 */
	public function refresh_connection_manual() {

		// Security and permissions check.
		if (
			! check_ajax_referer( 'wpforms-admin', 'nonce', false ) ||
			! wpforms_current_user_can()
		) {
			wp_send_json_error( esc_html__( 'You are not allowed to perform this action', 'wpforms-lite' ) );
		}

		$error_general = esc_html__( 'Something went wrong while performing a refresh tokens request', 'wpforms-lite' );

		// Required data check.
		if ( empty( $_POST['mode'] ) ) {
			wp_send_json_error( $error_general );
		}

		$mode       = sanitize_key( $_POST['mode'] );
		$connection = Connection::get( $mode );

		// Connection check.
		if ( ! $connection || ! $connection->is_configured() ) {
			wp_send_json_error( $error_general );
		}

		// Try to refresh connection.
		$connection = $this->try_refresh_connection( $connection );

		if ( is_wp_error( $connection ) ) {
			$error_specific = $connection->get_error_message();
			$error_message  = empty( $error_specific ) ? $error_general : $error_general . ': ' . $error_specific;

			wp_send_json_error( $error_message );
		}

		$this->prepare_locations( $mode );

		wp_send_json_success();
	}

	/**
	 * Try to refresh connection.
	 *
	 * @since 1.9.5
	 *
	 * @param Connection $connection Connection object.
	 *
	 * @return Connection|WP_Error
	 */
	private function try_refresh_connection( $connection ) {

		$response = $this->fetch_refresh_connection( $connection->get_refresh_token(), $connection->get_mode() );

		if ( is_wp_error( $response ) ) {

			if ( $response->get_error_code() === 'refresh_connection_fail' && $connection->is_valid() ) {
				$connection
					->set_status( Connection::STATUS_INVALID )
					->save();
			}

			return $response;
		}

		$refreshed_connection = $this->maybe_save_connection( $response, true );

		return $refreshed_connection ?? new WP_Error();
	}

	/**
	 * Schedule the connection refresh.
	 *
	 * @since 1.9.5
	 */
	private function schedule_refresh() {

		/**
		 * Allow modifying a condition check where the AS task will be registered.
		 *
		 * @since 1.9.5
		 *
		 * @param int $interval The refresh interval.
		 */
		if ( (bool) apply_filters( 'wpforms_square_admin_connect_schedule_refresh_prevent_task_registration', ! wpforms_is_admin_page() ) ) { // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName
			return;
		}

		$tasks = wpforms()->obj( 'tasks' );

		if ( is_null( $tasks ) ) {
			return;
		}

		if ( $tasks->is_scheduled( 'wpforms_square_refresh_connection' ) !== false ) {
			return;
		}

		// Register AS task only if a Production connection exists.
		if ( ! Connection::get( Environment::PRODUCTION ) ) {
			return;
		}

		/**
		 * Filter the frequency with which the OAuth connection should be refreshed.
		 *
		 * @since 1.9.5
		 *
		 * @param int $interval The refresh interval.
		 */
		$interval = (int) apply_filters( 'wpforms_square_admin_connect_schedule_refresh_interval', 12 * HOUR_IN_SECONDS ); // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName

		$tasks->create( 'wpforms_square_refresh_connection' )
			->recurring( time() + $interval, $interval )
			->register();
	}

	/**
	 * Unschedule the connection refresh.
	 *
	 * @since 1.9.5
	 */
	private function unschedule_refresh() {

		// Exit if AS function does not exist.
		if ( ! function_exists( 'as_unschedule_all_actions' ) ) {
			return;
		}

		as_unschedule_all_actions(
			'wpforms_square_refresh_connection',
			[ 'tasks_meta_id' => null ],
			Tasks::GROUP
		);
	}

	/**
	 * Check connection raw data and save it if everything is OK.
	 *
	 * @since 1.9.5
	 *
	 * @param array $raw    Connection raw data.
	 * @param bool  $silent Optional. Whether to prevent showing admin notices. Default false.
	 *
	 * @return Connection|null
	 */
	private function maybe_save_connection( array $raw, bool $silent = false ) {

		$connection = new Connection( $raw, false );

		// Bail if a connection doesn't have required data.
		if ( ! $connection->is_configured() ) {
			$silent ? wpforms_log(
				'Square error',
				'We could not connect to Square. No tokens were given.',
				[
					'type' => [ 'payment', 'error' ],
				]
			) : Notice::error( esc_html__( 'Square Error: We could not connect to Square. No tokens were given.', 'wpforms-lite' ) );

			return null;
		}

		// Prepare connection for save.
		$connection
			->set_renew_at()
			->set_scopes_updated()
			->encrypt_tokens();

		// Bail if a connection is not ready for save.
		if ( ! $connection->is_saveable() ) {
			$silent ? wpforms_log(
				'Square error',
				'We could not save an account connection safely. Please, try again later.',
				[
					'type' => [ 'payment', 'error' ],
				]
			) : Notice::error( esc_html__( 'Square Error: We could not save an account connection safely. Please, try again later.', 'wpforms-lite' ) );

			return null;
		}

		$connection->save();

		return $connection;
	}

	/**
	 * Prepare Square business locations.
	 *
	 * @since 1.9.5
	 *
	 * @param string $mode Square mode.
	 *
	 * @return array
	 */
	private function prepare_locations( string $mode ): array {

		$locations = $this->fetch_locations( $mode );

		if ( $locations === null ) {
			$this->reset_location( $mode );

			Transient::delete( 'wpforms_square_active_locations_' . $mode );

			return [];
		}

		$locations = $this->active_locations_filter( $locations );

		if ( empty( $locations ) ) {
			$this->reset_location( $mode );

			Transient::set( 'wpforms_square_active_locations_' . $mode, [] );

			return [];
		}

		$this->set_location( $locations, $mode );

		Transient::set( 'wpforms_square_active_locations_' . $mode, $locations );

		return $locations;
	}

	/**
	 * Fetch Square business locations.
	 *
	 * @since 1.9.5
	 *
	 * @param string $mode Square mode.
	 *
	 * @return array|null
	 */
	private function fetch_locations( string $mode ) {

		$connection = Connection::get( $mode );

		if ( ! $connection ) {
			return null;
		}

		$api       = new Api( $connection );
		$locations = $api->get_locations();

		if ( ! $locations ) {
			$connection
				->set_status( Connection::STATUS_INVALID )
				->save();

			return null;
		}

		return is_array( $locations ) ? $locations : [ $locations ];
	}

	/**
	 * Fetch Square seller account from Square.
	 *
	 * @since 1.9.5
	 *
	 * @param string $mode Square mode.
	 *
	 * @return array|null
	 */
	private function fetch_account( string $mode ) {

		$connection = Connection::get( $mode );

		if ( ! $connection ) {
			return null;
		}

		$api = new Api( $connection );

		$merchant = $api->get_merchant( $connection->get_merchant_id() );

		if ( ! $merchant ) {
			return null;
		}

		return $merchant->jsonSerialize();
	}

	/**
	 * Fetch new connection credentials.
	 *
	 * @since 1.9.5
	 *
	 * @param string $state Unique ID to safely fetch connection data.
	 *
	 * @return array
	 */
	private function fetch_new_connection( string $state ): array {

		$connection = [];
		$response   = wp_remote_post(
			$this->get_server_url() . '/oauth/square-connect',
			[
				'body'    => [
					'action' => 'credentials',
					'state'  => $state,
				],
				'timeout' => 30,
			]
		);

		if ( ! is_wp_error( $response ) && wp_remote_retrieve_response_code( $response ) === 200 ) {
			$body       = json_decode( wp_remote_retrieve_body( $response ), true );
			$connection = is_array( $body ) ? $body : [];
		}

		return $connection;
	}

	/**
	 * Fetch refresh connection credentials.
	 *
	 * @since 1.9.5
	 *
	 * @param string $token The refresh token.
	 * @param string $mode  Square mode.
	 *
	 * @return array|WP_Error
	 */
	private function fetch_refresh_connection( string $token, string $mode ) {

		$response = wp_remote_post(
			$this->get_server_url() . '/oauth/square-connect',
			[
				'body'    => [
					'action'    => 'refresh',
					'live_mode' => absint( $mode === Environment::PRODUCTION ),
					'token'     => $token,
				],
				'timeout' => 30,
			]
		);

		if ( wp_remote_retrieve_response_code( $response ) !== 200 ) {
			return new WP_Error();
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( ! is_array( $body ) ) {
			return new WP_Error();
		}

		if ( ! empty( $body['success'] ) ) {
			return $body;
		}

		$error_message = empty( $body['message'] ) ? '' : wp_kses_post( $body['message'] );

		return new WP_Error( 'refresh_connection_fail', $error_message );
	}

	/**
	 * Retrieve active business locations with processing capability.
	 *
	 * @since 1.9.5
	 *
	 * @param array $locations Locations.
	 *
	 * @return array
	 */
	private function active_locations_filter( array $locations ): array {

		$active_locations = [];

		if ( empty( $locations ) ) {
			return $active_locations;
		}

		foreach ( $locations as $location ) {
			if (
				! $location instanceof Location ||
				$location->getStatus() !== LocationStatus::ACTIVE ||
				! is_array( $location->getCapabilities() ) ||
				! in_array( LocationCapability::CREDIT_CARD_PROCESSING, $location->getCapabilities(), true )
			) {
				continue;
			}

			$location_id = $location->getId();

			$active_locations[ $location_id ] = [
				'id'       => $location_id,
				'name'     => $location->getName(),
				'currency' => $location->getCurrency(),
			];
		}

		return $active_locations;
	}

	/**
	 * Set/update location things: ID and currency.
	 *
	 * @since 1.9.5
	 *
	 * @param array  $locations Active locations.
	 * @param string $mode      Square mode.
	 */
	private function set_location( array $locations, string $mode ) {

		$connection         = Connection::get( $mode );
		$stored_location_id = Helpers::get_location_id( $mode );

		// Location ID was not set previously or saved ID is not available now.
		if ( empty( $stored_location_id ) || ! isset( $locations[ $stored_location_id ] ) ) {
			$stored_location_id = Helpers::array_key_first( $locations );

			// Set a new location ID.
			Helpers::set_locataion_id( $stored_location_id, $mode );
		}

		// Set location currency for connection.
		// In this case, we can make sure that location currency is matched with WPForms currency.
		if ( $connection !== null ) {
			$connection->set_currency( $locations[ $stored_location_id ]['currency'] )->save();
		}
	}

	/**
	 * Reset location ID and currency if no locations are received.
	 *
	 * @since 1.9.5
	 *
	 * @param string $mode Square mode.
	 */
	private function reset_location( string $mode ) {

		Helpers::set_locataion_id( '', $mode );

		$connection = Connection::get( $mode );

		if ( ! $connection ) {
			return;
		}

		$connection->set_currency( '' )->save();
	}

	/**
	 * Get cached business locations or fetch it from Square.
	 *
	 * @since 1.9.5
	 *
	 * @param string $mode Square mode.
	 *
	 * @return array
	 */
	public function get_connected_locations( string $mode ): array {

		$mode      = Helpers::validate_mode( $mode );
		$locations = Transient::get( 'wpforms_square_active_locations_' . $mode );

		if ( empty( $locations ) ) {
			$locations = $this->prepare_locations( $mode );
		}

		return $locations;
	}

	/**
	 * Get cached Square seller account or fetch it from Square.
	 *
	 * @since 1.9.5
	 *
	 * @param string $mode Square mode.
	 *
	 * @return array|null
	 */
	public function get_connected_account( string $mode ) {

		$mode    = Helpers::validate_mode( $mode );
		$account = Transient::get( 'wpforms_square_account_' . $mode );

		if ( empty( $account['id'] ) ) {

			$account_id = $this->get_connected_account_id( $mode );

			if ( ! $account_id ) {
				return null;
			}

			$account = $this->fetch_account( $mode );

			if ( empty( $account['id'] ) || $account['id'] !== $account_id ) {
				return null;
			}

			Transient::set( 'wpforms_square_account_' . $mode, $account );
		}

		return $account;
	}

	/**
	 * Retrieve saved Square seller account ID from DB.
	 *
	 * @since 1.9.5
	 *
	 * @param string $mode Square mode.
	 *
	 * @return string
	 */
	public function get_connected_account_id( string $mode ): string {

		$connection = Connection::get( $mode );
		$account_id = $connection ? $connection->get_merchant_id() : '';

		/**
		 * Filter the connected account ID.
		 *
		 * @since 1.9.5
		 *
		 * @param string $account_id Square account ID.
		 * @param string $mode       Square mode.
		 */
		return (string) apply_filters( 'wpforms_square_admin_connect_get_connected_account_id', $account_id, $mode ); // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName
	}

	/**
	 * Retrieve the connect URL.
	 *
	 * @since 1.9.5
	 *
	 * @param string $mode Square mode.
	 *
	 * @return string
	 */
	public function get_connect_url( string $mode ): string {

		$mode = Helpers::validate_mode( $mode );

		$settings_url = add_query_arg(
			'_wpnonce',
			wp_create_nonce( 'wpforms_square_connect' ),
			Helpers::get_settings_page_url()
		);

		return add_query_arg(
			[
				'action'    => 'init',
				'live_mode' => absint( $mode === Environment::PRODUCTION ),
				'state'     => uniqid( '', true ),
				'site_url'  => rawurlencode( $settings_url ),
				'scopes'    => implode( ' ', $this->get_scopes() ),
			],
			$this->get_server_url() . '/oauth/square-connect'
		);
	}

	/**
	 * Retrieve the disconnect URL.
	 *
	 * @since 1.9.5
	 *
	 * @param string $mode Square mode.
	 *
	 * @return string
	 */
	public function get_disconnect_url( string $mode ): string {

		$mode   = Helpers::validate_mode( $mode );
		$action = 'wpforms_square_disconnect';
		$url    = add_query_arg(
			[
				'action'    => $action,
				'live_mode' => absint( $mode === Environment::PRODUCTION ),
			],
			Helpers::get_settings_page_url()
		);

		return wp_nonce_url( $url, $action );
	}

	/**
	 * Retrieve a connect server URL.
	 *
	 * @since 1.9.5
	 *
	 * @return string
	 */
	public function get_server_url(): string {

		if ( defined( 'WPFORMS_SQUARE_LOCAL_CONNECT_SERVER' ) && WPFORMS_SQUARE_LOCAL_CONNECT_SERVER ) {
			return home_url();
		}

		return self::WPFORMS_URL;
	}

	/**
	 * Retrieve the connection scopes (permissions).
	 *
	 * @since 1.9.5
	 *
	 * @return array
	 */
	public function get_scopes(): array {

		/**
		 * Filter the connection scopes.
		 *
		 * @since 1.9.5
		 *
		 * @param array $scopes The connection scopes.
		 */
		return (array) apply_filters( // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName
			'wpforms_square_admin_connect_get_scopes',
			[
				'MERCHANT_PROFILE_READ',
				'PAYMENTS_READ',
				'PAYMENTS_WRITE',
				'ORDERS_READ',
				'ORDERS_WRITE',
				'CUSTOMERS_READ',
				'CUSTOMERS_WRITE',
				'SUBSCRIPTIONS_READ',
				'SUBSCRIPTIONS_WRITE',
				'ITEMS_READ',
				'ITEMS_WRITE',
				'INVOICES_WRITE',
				'INVOICES_READ',
				'PAYMENTS_WRITE_ADDITIONAL_RECIPIENTS',
			]
		);
	}
}
