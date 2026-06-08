<?php

namespace WPForms\Integrations\ConstantContact\V3;

use Exception;
use RuntimeException;
use WPForms\Integrations\ConstantContact\V3\Api\Api;

/**
 * Class Auth.
 *
 * @since 1.9.3
 */
class Auth {

	/**
	 * Nonce.
	 *
	 * @since 1.9.3
	 */
	const NONCE = 'wpforms-constant-contact-v3';

	/**
	 * Add hooks.
	 *
	 * @since 1.9.3
	 */
	public function hooks() {

		add_action( 'wpforms_builder_enqueues', [ $this, 'enqueue_scripts' ] );
		add_action( 'wpforms_settings_enqueue', [ $this, 'enqueue_scripts' ] );

		add_action( 'wp_ajax_wpforms_constant_contact_popup_auth', [ $this, 'ajax_handle_auth' ] );
	}

	/**
	 * Load scripts.
	 *
	 * @since 1.9.3
	 */
	public function enqueue_scripts() {

		$min = wpforms_get_min_suffix();

		wp_enqueue_script(
			'wpforms-constant-contact-v3-auth',
			WPFORMS_PLUGIN_URL . "assets/js/integrations/constant-contact-v3/auth{$min}.js",
			[ 'jquery' ],
			WPFORMS_VERSION,
			true
		);

		wp_localize_script(
			'wpforms-constant-contact-v3-auth',
			'WPFormsConstantContactV3AuthVars',
			[
				'auth_url' => self::get_auth_url(),
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'page_url' => $this->get_page_url(),
				'nonce'    => wp_create_nonce( self::NONCE ),
				'strings'  => [
					'wait'  => esc_html__( 'Please wait a moment...', 'wpforms-lite' ),
					'error' => esc_html__( 'There was an error while processing your request. Please try again.', 'wpforms-lite' ),
				],
			]
		);
	}

	/**
	 * Handle Auth popup.
	 *
	 * @since 1.9.3
	 */
	public function ajax_handle_auth() {

		try {
			if ( ! wpforms_current_user_can() ) {
				wp_send_json_error( esc_html__( 'You do not have permission to perform this action.', 'wpforms-lite' ) );
			}

			$account = $this->create_account();

			$this->validate_account( $account );

			wpforms_update_providers_options( Core::SLUG, $account, $account['id'] );

			wp_send_json_success( $account['id'] );
		} catch ( Exception $e ) {
			wp_send_json_error( $e->getMessage() );
		}
	}

	/**
	 * Receive and validate access and refresh tokens.
	 *
	 * @since 1.9.3
	 *
	 * @return array
	 *
	 * @throws RuntimeException Invalid code.
	 */
	private function get_code(): array {

		check_ajax_referer( self::NONCE, 'nonce' );

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$response             = json_decode( wp_unslash( $_POST['data'] ?? '' ), true );
		$invalid_code_message = __( 'Invalid code.', 'wpforms-lite' );

		if ( empty( $response ) || empty( $response['code'] ) ) {
			throw new RuntimeException( esc_html( $invalid_code_message ) );
		}

		$code = json_decode( $response['code'], true );

		if ( empty( $code['access_token'] ) ) {
			throw new RuntimeException( esc_html( $invalid_code_message ) );
		}

		return $code;
	}

	/**
	 * Validate account.
	 *
	 * @since 1.9.3
	 *
	 * @param array $account Account data.
	 *
	 * @throws RuntimeException Invalid account.
	 */
	private function validate_account( array $account ) {

		if ( empty( $account['email'] ) ) {
			throw new RuntimeException( esc_html__( 'Invalid account.', 'wpforms-lite' ) );
		}

		$accounts = wpforms_get_providers_options( Core::SLUG );

		if ( empty( $accounts ) ) {
			return;
		}

		$emails = wp_list_pluck( $accounts, 'id', 'email' );

		if (
			isset( $emails[ $account['email'] ] )
			&& $emails[ $account['email'] ] !== $account['id']
		) {
			throw new RuntimeException( esc_html__( 'This email is already connected.', 'wpforms-lite' ) );
		}
	}

	/**
	 * Build an option array.
	 *
	 * @since 1.9.3
	 *
	 * @return array
	 * @noinspection NonSecureUniqidUsageInspection
	 */
	private function create_account(): array {

		$code = $this->get_code();
		$time = time();

		$account = [
			'id'            => uniqid(),
			'date'          => $time,
			'access_token'  => $code['access_token'],
			'refresh_token' => $code['refresh_token'] ?? '',
			'expires_in'    => $time + (int) ( $code['expires_in'] ?? 0 ),
		];

		$account_summary = ( new Api( $account ) )->get_account_summary();

		$account['email'] = $account_summary['contact_email'] ?? '';
		$account['label'] = $this->get_label( $account_summary );

		/**
		 * Filters the account data after it was created.
		 *
		 * @since 1.9.3
		 *
		 * @param array $account Account data.
		 */
		return (array) apply_filters( 'wpforms_integrations_constant_contact_v3_auth_create_account_data', $account );
	}

	/**
	 * Get APP data needed for auth in the sing-up popup.
	 *
	 * @since 1.9.3
	 *
	 * @return string
	 */
	public static function get_auth_url(): string {

		return add_query_arg(
			[
				'client_id'     => ConstantContact::get_api_key(),
				'scope'         => 'offline_access account_read contact_data',
				'redirect_uri'  => add_query_arg( 'api-version', 'v3', ConstantContact::get_middleware_url() ),
				'state'         => 'WPForms-' . wp_rand( 1000, 9999 ),
				'response_type' => 'code',
				'prompt'        => 'login',
			],
			ConstantContact::SIGN_UP
		);
	}

	/**
	 * Get label.
	 *
	 * @since 1.9.3
	 *
	 * @param array $account_summary Account summary.
	 *
	 * @return string
	 */
	private function get_label( array $account_summary ): string {

		$email_part = $account_summary['contact_email'] ?? '';
		$org_part   = $account_summary['organization_name'] ?? '';

		if ( empty( $email_part ) && empty( $org_part ) ) {
			return '';
		}

		if ( empty( $email_part ) ) {
			return $org_part;
		}

		if ( empty( $org_part ) ) {
			return $email_part;
		}

		return "$email_part / $org_part";
	}

	/**
	 * Get the URL to the providers' page with the focus on the CC v3 integration.
	 *
	 * @since 1.9.3
	 *
	 * @return string
	 */
	private function get_page_url(): string {

		return add_query_arg(
			[
				'page'                => 'wpforms-settings',
				'view'                => 'integrations',
				'wpforms-integration' => Core::SLUG,
			],
			admin_url( 'admin.php' )
		);
	}
}
