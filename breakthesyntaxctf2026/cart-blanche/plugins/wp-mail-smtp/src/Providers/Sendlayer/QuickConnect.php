<?php

namespace WPMailSMTP\Providers\Sendlayer;

use WPMailSMTP\Admin\DebugEvents\DebugEvents;
use WPMailSMTP\ConnectionInterface;
use WPMailSMTP\Options;
use WPMailSMTP\WP;

/**
 * Class QuickConnect.
 *
 * Handles the quick connect flow for SendLayer:
 * - AJAX handler to initiate a connect session with the marketing site.
 * - Callback endpoint for ownership verification (HMAC-based).
 * - Exchange code handling when the user returns from signup.
 *
 * @since 4.8.0
 */
class QuickConnect {

	/**
	 * The SendLayer marketing site URL.
	 *
	 * @since 4.8.0
	 *
	 * @var string
	 */
	const SITE_URL = 'https://sendlayer.com';

	/**
	 * Register hooks.
	 *
	 * @since 4.8.0
	 */
	public function hooks() {

		add_action( 'wp_ajax_wp_mail_smtp_sendlayer_connect', [ $this, 'ajax_init_connect' ] );
		add_action( 'init', [ $this, 'handle_ownership_verification_callback' ] );
		add_action( 'admin_init', [ $this, 'handle_auth_complete' ] );
		add_action( 'admin_init', [ $this, 'display_notice' ] );
		add_action( 'admin_init', [ $this, 'handle_disconnect' ] );
		add_action( 'wp_ajax_wp_mail_smtp_sendlayer_disconnect', [ $this, 'ajax_disconnect' ] );
		add_action( 'wp_mail_smtp_admin_connection_settings_display_after_from_email_setting_row', [ $this, 'display_from_email_field' ], 10, 3 );
		add_filter( 'wp_mail_smtp_options_get', [ $this, 'filter_option' ], 10, 3 );
		add_action( 'wp_mail_smtp_admin_pages_before_content', [ $this, 'display_sendlayer_education_banner' ] );
		add_filter( 'wp_mail_smtp_admin_connection_settings_process_data', [ $this, 'process_settings_data' ], 10, 2 );
	}

	/**
	 * AJAX handler: Initiate the connect session with the marketing site.
	 *
	 * Called when the "Connect to SendLayer" button is clicked.
	 *
	 * @since 4.8.0
	 */
	public function ajax_init_connect() { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.MaxExceeded

		// Verify nonce.
		check_ajax_referer( 'wp-mail-smtp-sendlayer-connect', 'nonce' );

		// Check for permissions.
		if ( ! current_user_can( wp_mail_smtp()->get_capability_manage_options() ) ) {
			wp_send_json_error(
				[
					'message'    => esc_html__( 'You do not have permission to perform this action.', 'wp-mail-smtp' ),
					'error_code' => 'plugin.init_connect.permission_denied',
				]
			);
		}

		// Accept the return URL from the initiator page (settings, wizard, additional connection).
		// This is where the user ends up after the flow completes.
		if ( empty( $_POST['return_url'] ) ) {
			wp_send_json_error(
				[
					'message'    => $this->get_generic_error_message(),
					'error_code' => 'plugin.init_connect.missing_return_url',
				]
			);
		}

		// Validate it's a local URL to prevent open redirect.
		$return_url = wp_validate_redirect(
			esc_url_raw( wp_unslash( $_POST['return_url'] ) ),
			false
		);

		if ( ! $return_url ) {
			wp_send_json_error(
				[
					'message'    => $this->get_generic_error_message(),
					'error_code' => 'plugin.init_connect.invalid_return_url',
				]
			);
		}

		$connection_id = ! empty( $_POST['connection_id'] ) ? sanitize_key( $_POST['connection_id'] ) : '';

		// Build the redirect URL on the general settings page.
		// The auth handler always fires here; return_url is the final clean destination.
		$redirect_args = [
			'wp_mail_smtp_sendlayer_quick_connect_auth_complete' => 1,
			'nonce'      => wp_create_nonce( 'wp_mail_smtp_sendlayer_quick_connect' ),
			'return_url' => rawurlencode( $return_url ),
		];

		if ( ! empty( $connection_id ) ) {
			$redirect_args['connection_id'] = $connection_id;
		}

		$redirect_url = add_query_arg( $redirect_args, wp_mail_smtp()->get_admin()->get_admin_page_url() );

		// Generate a challenge secret for HMAC-based ownership verification.
		$challenge_secret = bin2hex( random_bytes( 16 ) );

		set_transient( 'wp_mail_smtp_sendlayer_quick_connect_challenge_secret', $challenge_secret, 300 );

		// POST to the marketing site start-session endpoint.
		$response = wp_remote_post(
			$this->get_marketing_site_url() . '/wp-json/smtp-plugin-connect/v1/start-session',
			[
				'timeout' => 30,
				'body'    => [
					'site_url'         => $this->get_site_url(),
					'return_url'       => $redirect_url,
					'challenge_secret' => $challenge_secret,
					'source'           => 'wpms',
				],
			]
		);

		if ( is_wp_error( $response ) ) {
			$error_code = 'plugin.init_connect.start_session_request_to_site.connection_failed';

			DebugEvents::add(
				'SendLayer Quick Connect: ' . $error_code . ' — ' . $response->get_error_message()
			);

			wp_send_json_error(
				[
					'message'    => $this->get_generic_error_message(),
					'error_code' => $error_code,
				]
			);
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		$response_body = json_decode( wp_remote_retrieve_body( $response ), true );

		// Non-200 response.
		if ( $response_code !== 200 ) {
			// Expected error — site returned a parseable error code.
			$error_code = ! empty( $response_body['code'] )
				? $this->sanitize_error_code( $response_body['code'] )
				: 'plugin.init_connect.start_session_request_to_site.unexpected_error_response';

			DebugEvents::add(
				'SendLayer Quick Connect: ' . $error_code . ' — HTTP ' . $response_code
			);

			wp_send_json_error(
				[
					'message'    => $this->get_generic_error_message(),
					'error_code' => $error_code,
				]
			);
		}

		// Unexpected success — 200 but missing expected data.
		if ( empty( $response_body['session_id'] ) ) {
			$error_code = 'plugin.init_connect.start_session_request_to_site.unexpected_success_response';

			DebugEvents::add(
				'SendLayer Quick Connect: ' . $error_code . ' — missing session_id'
			);

			wp_send_json_error(
				[
					'message'    => $this->get_generic_error_message(),
					'error_code' => $error_code,
				]
			);
		}

		$session_id = sanitize_text_field( $response_body['session_id'] );

		$redirect_url = add_query_arg( 'session', $session_id, $this->get_marketing_site_url() . '/smtp-plugin-connect' );

		// Add UTM parameters to the redirect URL for tracking which button initiated the flow.
		$utm_content = ! empty( $_POST['connect_args']['utm_content'] ) ? sanitize_text_field( wp_unslash( $_POST['connect_args']['utm_content'] ) ) : 'Quick Connect';

		// phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
		$redirect_url = wp_mail_smtp()->get_utm_url( $redirect_url, [ 'source' => 'wpmailsmtpplugin', 'medium' => 'WordPress', 'content' => $utm_content ] );

		wp_send_json_success(
			[
				'redirect_url' => $redirect_url,
			]
		);
	}

	/**
	 * Handle the ownership verification callback.
	 *
	 * The marketing site sends a server-to-server GET request with a `nonce`
	 * query parameter during the start-session process. We compute an
	 * HMAC-SHA256 signature using the stored challenge secret and return it
	 * for verification.
	 *
	 * Listens for the `wp_mail_smtp_sendlayer_quick_connect_verify` GET parameter.
	 *
	 * @since 4.8.0
	 */
	public function handle_ownership_verification_callback() {

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! isset( $_GET['wp_mail_smtp_sendlayer_quick_connect_verify'] ) ) {
			return;
		}

		// Read the nonce from the GET query parameter.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		if ( empty( $_GET['nonce'] ) ) {
			wp_send_json_error(
				[
					'message' => 'Missing nonce.',
					'code'    => 'missing_nonce',
				],
				400
			);
		}

		// Retrieve the stored challenge secret.
		$challenge_secret = get_transient( 'wp_mail_smtp_sendlayer_quick_connect_challenge_secret' );

		if ( empty( $challenge_secret ) ) {
			wp_send_json_error(
				[
					'message' => 'No active verification session.',
					'code'    => 'no_active_session',
				],
				400
			);
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$nonce     = sanitize_text_field( wp_unslash( $_GET['nonce'] ) );
		$signature = hash_hmac( 'sha256', $nonce, $challenge_secret );

		// Delete the transient after use (single-use).
		delete_transient( 'wp_mail_smtp_sendlayer_quick_connect_challenge_secret' );

		wp_send_json( [ 'signature' => $signature ] );
	}

	/**
	 * Handle the return from the marketing site signup flow.
	 *
	 * Handles both success (`_auth_complete`) and error (`_auth_error`) redirects.
	 * On success, exchanges the code for an API key, stores it, and redirects.
	 * On error, forwards the error code to the notice display via redirect.
	 *
	 * @since 4.8.0
	 */
	public function handle_auth_complete() { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.MaxExceeded, Generic.Metrics.CyclomaticComplexity.TooHigh

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if (
			empty( $_GET['wp_mail_smtp_sendlayer_quick_connect_auth_complete'] ) ||
			! wp_mail_smtp()->get_admin()->is_admin_page()
		) {
			return;
		}

		// Resolve the return URL early so every exit path redirects to it.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$return_url = ! empty( $_GET['return_url'] )
			? wp_validate_redirect( esc_url_raw( wp_unslash( $_GET['return_url'] ) ), wp_mail_smtp()->get_admin()->get_admin_page_url() )
			: wp_mail_smtp()->get_admin()->get_admin_page_url();

		if ( ! current_user_can( wp_mail_smtp()->get_capability_manage_options() ) ) {
			$this->redirect_with_result( $return_url, 'plugin.return.permission_denied' );
		}

		// Verify the nonce that was appended to the return URL during session init.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$nonce = isset( $_GET['nonce'] )
			? sanitize_text_field( wp_unslash( $_GET['nonce'] ) )
			: '';

		if ( ! wp_verify_nonce( $nonce, 'wp_mail_smtp_sendlayer_quick_connect' ) ) {
			$this->redirect_with_result( $return_url, 'plugin.return.invalid_nonce' );
		}

		if ( ! empty( $_GET['exit'] ) ) {
			wp_safe_redirect( $return_url );
			exit;
		}

		// If the marketing site redirected with an error, forward to the notice display.
		if ( ! empty( $_GET['error'] ) ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$error_code = $this->sanitize_error_code( wp_unslash( $_GET['error'] ) );

			$this->redirect_with_result( $return_url, $error_code );
		}

		$exchange_code = isset( $_GET['exchange_code'] )
			? sanitize_text_field( wp_unslash( $_GET['exchange_code'] ) )
			: '';

		if ( empty( $exchange_code ) ) {
			$this->redirect_with_result( $return_url, 'plugin.return.missing_exchange_code' );
		}

		// POST to the marketing site REST API to retrieve connection details.
		$response = wp_remote_post(
			$this->get_marketing_site_url() . '/wp-json/smtp-plugin-connect/v1/connection',
			[
				'timeout' => 30,
				'body'    => [
					'exchange_code' => $exchange_code,
					'source'        => 'wpms',
				],
			]
		);

		if ( is_wp_error( $response ) ) {
			$this->redirect_with_result( $return_url, 'plugin.return.exchange_request_to_site.connection_failed' );
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		$response_body = json_decode( wp_remote_retrieve_body( $response ), true );

		// Non-200 response.
		if ( $response_code !== 200 ) {
			// Expected error — site returned a parseable error code.
			$error_code = ! empty( $response_body['code'] )
				? $this->sanitize_error_code( $response_body['code'] )
				: 'plugin.return.exchange_request_to_site.unexpected_error_response';

			$this->redirect_with_result( $return_url, $error_code );
		}

		// Unexpected success — 200 but missing expected data.
		if ( empty( $response_body['api_key'] ) ) {
			$this->redirect_with_result( $return_url, 'plugin.return.exchange_request_to_site.unexpected_success_response' );
		}

		$api_key          = sanitize_text_field( $response_body['api_key'] );
		$sender_domain    = ! empty( $response_body['sender_domain'] ) ? sanitize_text_field( $response_body['sender_domain'] ) : '';
		$is_shared_domain = ! empty( $response_body['is_shared_domain'] );

		$connection = wp_mail_smtp()->get_connections_manager()->get_primary_connection();

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$connection_id = ! empty( $_GET['connection_id'] ) ? sanitize_key( $_GET['connection_id'] ) : '';

		if ( ! empty( $connection_id ) && wp_mail_smtp()->is_pro() ) {
			$connection = wp_mail_smtp()->get_connections_manager()->get_connection( $connection_id, false );
		}

		if ( $connection === false ) {
			$this->redirect_with_result( $return_url, 'plugin.return.invalid_connection' );
		}

		// Store the API key using the connection's Options.
		$options = $connection->get_options();
		$all_opt = $options->get_all_raw();

		$all_opt['mail']['mailer']                = 'sendlayer';
		$all_opt['sendlayer']['api_key']          = $api_key;
		$all_opt['sendlayer']['quick_connect']    = true;
		$all_opt['sendlayer']['is_shared_domain'] = $is_shared_domain;

		// Store the sender domain and configure From Email for shared domains.
		if ( ! empty( $sender_domain ) ) {
			$all_opt['sendlayer']['sender_domain'] = $sender_domain;

			$current_from = ! empty( $all_opt['mail']['from_email'] ) ? $all_opt['mail']['from_email'] : '';

			$all_opt['mail']['from_email']       = $this->build_from_email( $current_from, $sender_domain );
			$all_opt['mail']['from_email_force'] = true;
		}

		$options->set( $all_opt );

		$this->redirect_with_result( $return_url, 'success' );
	}

	/**
	 * Display admin notices for SendLayer Quick Connect and disconnect results.
	 *
	 * Reads result query params set by handle_auth_complete() and handle_disconnect()
	 * and renders the appropriate success or error notice.
	 *
	 * @since 4.8.0
	 */
	public function display_notice() { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		if ( ! wp_mail_smtp()->get_admin()->is_admin_page() ) {
			return;
		}

		// phpcs:disable WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$disconnect_result = isset( $_GET['sendlayer_quick_connect_disconnect_result'] )
			? $this->sanitize_error_code( wp_unslash( $_GET['sendlayer_quick_connect_disconnect_result'] ) )
			: '';
		// phpcs:enable WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		if ( $disconnect_result === 'success' ) {
			WP::add_admin_notice(
				esc_html__( 'SendLayer disconnected successfully.', 'wp-mail-smtp' ),
				WP::ADMIN_NOTICE_SUCCESS
			);

			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( empty( $_GET['sendlayer_quick_connect_result'] ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$result = $this->sanitize_error_code( wp_unslash( $_GET['sendlayer_quick_connect_result'] ) );

		// Success — show admin notice banner.
		if ( $result === 'success' ) {
			WP::add_admin_notice(
				esc_html__( 'SendLayer connected successfully! You can now send emails through SendLayer.', 'wp-mail-smtp' ),
				WP::ADMIN_NOTICE_SUCCESS
			);

			return;
		}

		// Errors — show admin notice with error code box.
		$actionable_messages = [
			'plugin.return.permission_denied' => esc_html__( 'SendLayer connection failed. You do not have permission to perform this action.', 'wp-mail-smtp' ),
			'plugin.return.invalid_nonce'     => esc_html__( 'SendLayer connection failed. Your session has expired. Please try again.', 'wp-mail-smtp' ),
			'site.exchange.rate_limit'        => esc_html__( 'SendLayer connection failed. Too many attempts. Please wait a few minutes and try again.', 'wp-mail-smtp' ),
			'site.exchange.code_expired'      => esc_html__( 'SendLayer connection failed. Your connection code has expired. Please try again.', 'wp-mail-smtp' ),
		];

		$message = isset( $actionable_messages[ $result ] )
			? $actionable_messages[ $result ]
			: $this->get_generic_error_message();

		WP::add_admin_notice( $message, WP::ADMIN_NOTICE_ERROR, true, '', $result );
	}

	/**
	 * Handle SendLayer disconnect action.
	 *
	 * Clears the API key, quick connect flag, and sender domain when the user
	 * clicks "Disconnect" via a nonce-protected URL, then redirects back to
	 * the settings page without the nonce query parameter.
	 *
	 * @since 4.8.0
	 */
	public function handle_disconnect() {

		if (
			! isset( $_GET['sendlayer_quick_connect_disconnect_nonce'] ) || // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			! wp_mail_smtp()->get_admin()->is_admin_page()
		) {
			return;
		}

		if ( ! current_user_can( wp_mail_smtp()->get_capability_manage_options() ) ) {
			WP::add_admin_notice(
				esc_html__( 'SendLayer disconnect failed. You do not have permission to perform this action.', 'wp-mail-smtp' ),
				WP::ADMIN_NOTICE_ERROR
			);

			return;
		}

		if ( ! wp_verify_nonce( sanitize_key( $_GET['sendlayer_quick_connect_disconnect_nonce'] ), 'sendlayer_quick_connect_disconnect' ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			WP::add_admin_notice(
				esc_html__( 'SendLayer disconnect failed. The nonce is invalid. Please try again.', 'wp-mail-smtp' ),
				WP::ADMIN_NOTICE_ERROR
			);

			return;
		}

		$connection = wp_mail_smtp()->get_connections_manager()->get_primary_connection();
		$options    = $connection->get_options();
		$all_opt    = $options->get_all_raw();

		$all_opt['sendlayer']['api_key'] = '';

		unset( $all_opt['sendlayer']['quick_connect'] );
		unset( $all_opt['sendlayer']['is_shared_domain'] );
		unset( $all_opt['sendlayer']['sender_domain'] );

		$options->set( $all_opt );

		wp_safe_redirect( add_query_arg( 'sendlayer_quick_connect_disconnect_result', 'success', remove_query_arg( 'sendlayer_quick_connect_disconnect_nonce' ) ) );
		exit;
	}

	/**
	 * AJAX handler: Disconnect SendLayer quick connect.
	 *
	 * Clears the API key, quick connect flag, shared domain flag, and sender domain.
	 *
	 * @since 4.8.0
	 */
	public function ajax_disconnect() {

		check_ajax_referer( 'wp-mail-smtp-sendlayer-connect', 'nonce' );

		if ( ! current_user_can( wp_mail_smtp()->get_capability_manage_options() ) ) {
			wp_send_json_error(
				[
					'message' => esc_html__( 'You do not have permission to perform this action.', 'wp-mail-smtp' ),
				]
			);
		}

		$old_opt = Options::init()->get_all_raw();

		$old_opt['sendlayer']['api_key'] = '';

		unset( $old_opt['sendlayer']['quick_connect'] );
		unset( $old_opt['sendlayer']['is_shared_domain'] );
		unset( $old_opt['sendlayer']['sender_domain'] );

		Options::init()->set( $old_opt );

		wp_send_json_success();
	}

	/**
	 * Display the SendLayer education banner when the default PHP mailer is active.
	 *
	 * Shown above the page content (below tabs) on the Settings page only.
	 *
	 * @since 4.8.0
	 */
	public function display_sendlayer_education_banner() {

		$mailer = wp_mail_smtp()->get_connections_manager()->get_primary_connection()->get_mailer_slug();

		if ( $mailer !== 'mail' ) {
			return;
		}

		if ( ! wp_mail_smtp()->get_admin()->is_admin_page() ) {
			return;
		}

		$is_dismissed = (bool) get_user_meta( get_current_user_id(), 'wp_mail_smtp_notice_sendlayer_education_dismissed', true );

		if ( $is_dismissed ) {
			return;
		}

		// phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
		$sendlayer_url = wp_mail_smtp()->get_utm_url( 'https://sendlayer.com/', [ 'source' => 'wpmailsmtpplugin', 'medium' => 'WordPress', 'content' => 'Default Mailer - SendLayer Education' ] );
		?>
		<div id="wp-mail-smtp-sendlayer-education-banner" class="wp-mail-smtp-sendlayer-education">
			<div class="wp-mail-smtp-sendlayer-education__content">
				<div class="wp-mail-smtp-sendlayer-education__text">
					<div class="wp-mail-smtp-sendlayer-education__heading">
						<span class="wp-mail-smtp-sendlayer-education__icon">
							<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M8 0C12.4062 0 16 3.59375 16 8C16 12.4375 12.4062 16 8 16C3.5625 16 0 12.4375 0 8C0 3.59375 3.5625 0 8 0ZM7.25 4.75V8.75C7.25 9.1875 7.5625 9.5 8 9.5C8.40625 9.5 8.75 9.1875 8.75 8.75V4.75C8.75 4.34375 8.40625 4 8 4C7.5625 4 7.25 4.34375 7.25 4.75ZM8 12.5C8.53125 12.5 8.96875 12.0625 8.96875 11.5312C8.96875 11 8.53125 10.5625 8 10.5625C7.4375 10.5625 7 11 7 11.5312C7 12.0625 7.4375 12.5 8 12.5Z" fill="#DF2A4A"/></svg>
						</span>
						<?php esc_html_e( 'Seems like you don\'t have a mailer setup yet!', 'wp-mail-smtp' ); ?>
					</div>
					<p class="wp-mail-smtp-sendlayer-education__desc">
						<?php
						printf(
							wp_kses(
								/* translators: %s - URL to SendLayer. */
								__( 'You\'re using the default WordPress mailer, which often lands in spam! Switch to <a href="%s" target="_blank" rel="noopener noreferrer">SendLayer</a>, our recommended mailer, and send your <strong>first 200 emails for free with just a few clicks!</strong>', 'wp-mail-smtp' ),
								[
									'a'      => [
										'href'   => [],
										'rel'    => [],
										'target' => [],
									],
									'strong' => [],
								]
							),
							esc_url( $sendlayer_url )
						);
						?>
					</p>
				</div>
				<div class="wp-mail-smtp-sendlayer-education__actions">
					<button type="button" id="wp-mail-smtp-sendlayer-education-connect-btn" class="wp-mail-smtp-btn wp-mail-smtp-btn-orange wp-mail-smtp-btn-md wp-mail-smtp-sendlayer-education__btn">
						<?php esc_html_e( 'Setup SendLayer', 'wp-mail-smtp' ); ?>
					</button>
					<span class="wp-mail-smtp-sendlayer-education__connect-badge">
						<svg width="9" height="11" viewBox="0 0 9 11" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6.67969 0.175781C6.91406 0.351562 6.99219 0.644531 6.89453 0.917969L5.35156 4.74609H8.18359C8.45703 4.74609 8.69141 4.90234 8.76953 5.15625C8.86719 5.41016 8.78906 5.68359 8.59375 5.85938L2.96875 10.5469C2.73438 10.7227 2.42188 10.7422 2.1875 10.5664C1.95312 10.3906 1.875 10.0977 1.97266 9.82422L3.51562 5.99609H0.683594C0.429688 5.99609 0.195312 5.83984 0.0976562 5.58594C0 5.33203 0.078125 5.05859 0.292969 4.88281L5.91797 0.195312C6.13281 0.0195312 6.44531 0 6.67969 0.175781Z" fill="#6F6F84"/></svg>
						<?php esc_html_e( 'Takes about 2 mins', 'wp-mail-smtp' ); ?>
					</span>
				</div>
			</div>
			<div class="wp-mail-smtp-sendlayer-education__illustration">
				<img src="<?php echo esc_url( wp_mail_smtp()->assets_url . '/images/education/sendlayer-banner.svg' ); ?>" alt="" />
			</div>
			<button type="button" class="wp-mail-smtp-sendlayer-education__dismiss js-wp-mail-smtp-sendlayer-education-dismiss" title="<?php esc_attr_e( 'Dismiss', 'wp-mail-smtp' ); ?>">
				<svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M0 7C0 3.14453 3.11719 0 7 0C10.8555 0 14 3.14453 14 7C14 10.8828 10.8555 14 7 14C3.11719 14 0 10.8828 0 7ZM4.78516 5.71484L6.07031 7L4.78516 8.28516C4.51172 8.55859 4.51172 8.96875 4.78516 9.21484C5.03125 9.48828 5.44141 9.48828 5.6875 9.21484L6.97266 7.92969L8.28516 9.21484C8.53125 9.48828 8.94141 9.48828 9.1875 9.21484C9.46094 8.96875 9.46094 8.55859 9.1875 8.28516L7.90234 7L9.1875 5.71484C9.46094 5.46875 9.46094 5.05859 9.1875 4.78516C8.94141 4.53906 8.53125 4.53906 8.28516 4.78516L6.97266 6.09766L5.6875 4.78516C5.44141 4.53906 5.03125 4.53906 4.78516 4.78516C4.51172 5.05859 4.51172 5.46875 4.78516 5.71484Z" fill="#BDBDC3"/></svg>
			</button>
			<p class="desc wp-mail-smtp-sendlayer-connect-error" style="margin-top: 10px;"></p>
		</div>
		<?php
	}

	/**
	 * Display the quick connect From Email Address field.
	 *
	 * Renders a split local-part + fixed domain field for shared domain
	 * connections. Hidden by default; JS toggles visibility on mailer change.
	 *
	 * @since 4.8.0
	 *
	 * @param ConnectionInterface $connection         The Connection object.
	 * @param Options             $connection_options The connection options instance.
	 * @param string              $mailer             The current mailer slug.
	 */
	public function display_from_email_field( $connection, $connection_options, $mailer ) {

		if ( ! $connection_options->get( 'sendlayer', 'quick_connect' ) || ! $connection_options->get( 'sendlayer', 'is_shared_domain' ) ) {
			return;
		}

		$sender_domain = $connection_options->get( 'sendlayer', 'sender_domain' );

		if ( empty( $sender_domain ) ) {
			return;
		}

		$from_email = $connection_options->get( 'mail', 'from_email' );
		// phpcs:ignore WordPress.WP.CapitalPDangit.Misspelled -- email local part, not a brand reference.
		$local_part = 'wordpress';

		if ( ! empty( $from_email ) && strpos( $from_email, '@' ) !== false ) {
			$parts      = explode( '@', $from_email );
			$local_part = $parts[0];
		}

		$is_active = $mailer === 'sendlayer';
		?>
		<div id="wp-mail-smtp-setting-row-sendlayer-quick-connect-from_email"
		     class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-text wp-mail-smtp-setting-row-sendlayer-quick-connect-from-email wp-mail-smtp-clear"
		     style="display: <?php echo $is_active ? 'block' : 'none'; ?>;">
			<div class="wp-mail-smtp-setting-label">
				<label for="wp-mail-smtp-setting-sendlayer-quick-connect-from_email">
					<?php esc_html_e( 'From Email Address', 'wp-mail-smtp' ); ?>
				</label>
			</div>
			<div class="wp-mail-smtp-setting-field">
				<div class="wp-mail-smtp-sendlayer-from-email-wrap">
					<input name="wp-mail-smtp[mail][from_email_local]" type="text"
					       value="<?php echo esc_attr( $local_part ); ?>"
					       id="wp-mail-smtp-setting-sendlayer-quick-connect-from_email" spellcheck="false"
					       <?php disabled( ! $is_active ); ?>
					/>
					<span class="wp-mail-smtp-sendlayer-from-email-domain">
						@<?php echo esc_html( $sender_domain ); ?>
					</span>
				</div>
				<input type="hidden" name="wp-mail-smtp[mail][from_email_domain]"
				       value="<?php echo esc_attr( $sender_domain ); ?>"
				       <?php disabled( ! $is_active ); ?> />
				<p class="desc">
					<?php esc_html_e( 'The email address that emails are sent from.', 'wp-mail-smtp' ); ?>
				</p>
				<p class="desc">
					<?php
					printf(
						wp_kses(
							/* translators: %1$s - URL to SendLayer app; %2$s - URL to documentation. */
							__( 'You can customize your From Email by adding your own domain first on the <a href="%1$s" target="_blank" rel="noopener noreferrer">SendLayer dashboard</a>. Check our <a href="%2$s" target="_blank" rel="noopener noreferrer">documentation</a> on how to add a custom domain.', 'wp-mail-smtp' ),
							[
								'a' => [
									'href'   => [],
									'rel'    => [],
									'target' => [],
								],
							]
						),
						// phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
						esc_url( wp_mail_smtp()->get_utm_url( 'https://app.sendlayer.com/', [ 'source' => 'wpmailsmtpplugin', 'medium' => 'WordPress', 'content' => 'Plugin Settings - From Email Domain Link' ] ) ),
						// phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
						esc_url( wp_mail_smtp()->get_utm_url( 'https://sendlayer.com/docs/authorizing-your-domain/', [ 'source' => 'wpmailsmtpplugin', 'medium' => 'WordPress', 'content' => 'Plugin Settings - Custom Domain Documentation' ] ) )
					);
					?>
				</p>
			</div>
		</div>
		<?php
	}

	/**
	 * Process quick connect settings data before save.
	 *
	 * Reconstructs from_email from split local + domain fields and forces
	 * from_email_force when a shared domain is active.
	 *
	 * @since 4.8.0
	 *
	 * @param array $data     Connection data.
	 * @param array $old_data Old connection data.
	 *
	 * @return array
	 */
	public function process_settings_data( $data, $old_data ) {

		// Reconstruct from_email from split local + domain fields.
		if ( ! empty( $data['mail']['from_email_local'] ) && ! empty( $data['mail']['from_email_domain'] ) ) {
			$data['mail']['from_email'] = sanitize_text_field( $data['mail']['from_email_local'] ) . '@' . sanitize_text_field( $data['mail']['from_email_domain'] );

			unset( $data['mail']['from_email_local'], $data['mail']['from_email_domain'] );
		}

		// When shared domain is active, force from_email_force since the toggle is hidden.
		if (
			! empty( $data['mail']['mailer'] ) &&
			$data['mail']['mailer'] === 'sendlayer' &&
			! empty( $data['sendlayer']['is_shared_domain'] )
		) {
			$data['mail']['from_email_force'] = true;
		}

		return $data;
	}

	/**
	 * Force quick_connect to false when the API key is defined via constant.
	 *
	 * Constant-based configuration takes precedence over quick connect state.
	 *
	 * @since 4.8.0
	 *
	 * @param mixed  $value The option value.
	 * @param string $group The options group.
	 * @param string $key   The options key.
	 *
	 * @return mixed
	 */
	public function filter_option( $value, $group, $key ) {

		if ( $group === 'sendlayer' && in_array( $key, [ 'quick_connect', 'is_shared_domain' ], true ) && Options::init()->is_const_defined( 'sendlayer', 'api_key' ) ) {
			return false;
		}

		return $value;
	}

	/**
	 * Get the marketing site URL.
	 *
	 * Allows overriding via the WP_MAIL_SMTP_SENDLAYER_SITE_URL constant.
	 *
	 * @since 4.8.0
	 *
	 * @return string
	 */
	private function get_marketing_site_url() {

		if ( defined( 'WP_MAIL_SMTP_SENDLAYER_SITE_URL' ) && WP_MAIL_SMTP_SENDLAYER_SITE_URL ) {
			return rtrim( WP_MAIL_SMTP_SENDLAYER_SITE_URL, '/' );
		}

		return self::SITE_URL;
	}

	/**
	 * Build a from email address for the shared sender domain.
	 *
	 * Takes the user part (local-part) from the current from email and
	 * combines it with the shared sender domain. Falls back to "WordPress"
	 * when no current from email is configured.
	 *
	 * @since 4.8.0
	 *
	 * @param string $current_from  The current from email address.
	 * @param string $sender_domain The shared sender domain.
	 *
	 * @return string
	 */
	private function build_from_email( $current_from, $sender_domain ) {

		// phpcs:ignore WordPress.WP.CapitalPDangit.Misspelled -- email local part, not a brand reference.
		$user_part = 'wordpress';

		if ( ! empty( $current_from ) && strpos( $current_from, '@' ) !== false ) {
			$parts     = explode( '@', $current_from );
			$user_part = $parts[0];
		}

		return $user_part . '@' . $sender_domain;
	}

	/**
	 * Redirect to a URL with a quick connect result code.
	 *
	 * @since 4.8.0
	 *
	 * @param string $url    The URL to redirect to.
	 * @param string $result The result code (e.g. 'success', 'plugin_invalid_session').
	 */
	private function redirect_with_result( $url, $result ) {

		if ( $result !== 'success' ) {
			DebugEvents::add( 'SendLayer Quick Connect: ' . $result );
		}

		wp_safe_redirect( add_query_arg( 'sendlayer_quick_connect_result', $result, $url ) );
		exit;
	}

	/**
	 * Build a generic error message for support reference.
	 *
	 * @since 4.8.0
	 *
	 * @return string
	 */
	private function get_generic_error_message() {

		return esc_html__( 'Something went wrong while connecting to SendLayer. Please try again, or contact support and provide the error code.', 'wp-mail-smtp' );
	}

	/**
	 * Get the home URL bypassing filters.
	 *
	 * Reads from WP_HOME constant or directly from the database
	 * to avoid any filter modifications to the URL.
	 *
	 * @since 4.8.0
	 *
	 * @return string
	 */
	private function get_site_url() {

		global $wpdb;

		if ( defined( 'WP_HOME' ) && WP_HOME ) {
			return WP_HOME;
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- intentional: bypass filters to get unmodified site URL.
		return $wpdb->get_var(
			$wpdb->prepare(
				"SELECT option_value FROM {$wpdb->options} WHERE option_name = %s",
				'home'
			)
		);
	}

	/**
	 * Sanitize an error code slug, preserving dots for dot-notation format.
	 *
	 * WordPress's `sanitize_key()` strips dots. This method allows dots,
	 * lowercase letters, digits, underscores, and hyphens.
	 *
	 * @since 4.8.0
	 *
	 * @param string $code The error code to sanitize.
	 *
	 * @return string
	 */
	private function sanitize_error_code( $code ) {

		$code = strtolower( trim( $code ) );

		return preg_replace( '/[^a-z0-9._-]/', '', $code );
	}
}
