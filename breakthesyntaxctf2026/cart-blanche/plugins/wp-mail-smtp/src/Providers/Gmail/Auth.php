<?php

namespace WPMailSMTP\Providers\Gmail;

use Exception;
use WPMailSMTP\Admin\Area;
use WPMailSMTP\Admin\ConnectionSettings;
use WPMailSMTP\Admin\DebugEvents\DebugEvents;
use WPMailSMTP\Admin\SetupWizard;
use WPMailSMTP\ConnectionInterface;
use WPMailSMTP\Debug;
use WPMailSMTP\Providers\AuthAbstract;
use WPMailSMTP\Providers\Gmail\Logger;
use WPMailSMTP\Vendor\Google_Client;
use WPMailSMTP\Vendor\Google\Service\Gmail;

/**
 * Class Auth to request access and refresh tokens.
 *
 * @since 1.0.0
 */
class Auth extends AuthAbstract {

	/**
	 * List of all possible "from email" email addresses (aliases).
	 *
	 * @since 2.2.0
	 *
	 * @var null|array
	 */
	private $aliases = null;

	/**
	 * Auth constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param ConnectionInterface $connection The Connection object.
	 */
	public function __construct( $connection = null ) {

		parent::__construct( $connection );

		if ( $this->mailer_slug !== Options::SLUG ) {
			return;
		}

		$this->options = $this->connection_options->get_group( $this->mailer_slug );

		if ( wp_mail_smtp()->is_pro() && ! empty( $this->options['one_click_setup_enabled'] ) ) {
			return;
		}

		if ( $this->is_clients_saved() ) {
			$this->include_vendor_lib();

			$this->client = $this->get_client();
		}
	}

	/**
	 * Get the url, that users will be redirected back to finish the OAuth process.
	 *
	 * @since 1.5.2 Returned to the old, pre-1.5, structure of the link to preserve BC.
	 *
	 * @param ConnectionInterface $connection The Connection object.
	 *
	 * @return string
	 */
	public static function get_plugin_auth_url( $connection = null ) {

		if ( is_null( $connection ) ) {
			$connection = wp_mail_smtp()->get_connections_manager()->get_primary_connection();
		}

		$auth_url = apply_filters(
			'wp_mail_smtp_gmail_get_plugin_auth_url',
			add_query_arg(
				[
					'page' => Area::SLUG,
					'tab'  => 'auth',
				],
				admin_url( 'options-general.php' )
			)
		);

		return add_query_arg( 'state', self::get_state_param( $connection ), $auth_url );
	}

	/**
	 * Init and get the Google Client object.
	 *
	 * @since 1.0.0
	 * @since 1.5.0 Add ability to apply custom options to the client via a filter.
	 *
	 * @param bool $force If the client should be forcefully reinitialized.
	 *
	 * @return Google_Client
	 */
	public function get_client( $force = false ) { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.MaxExceeded

		// Doesn't load client twice + gives ability to overwrite.
		if ( ! empty( $this->client ) && ! $force ) {
			return $this->client;
		}

		$this->include_vendor_lib();

		$client = new Google_Client(
			array(
				'client_id'     => $this->options['client_id'],
				'client_secret' => $this->options['client_secret'],
				'redirect_uris' => array(
					self::get_oauth_redirect_url(),
				),
			)
		);
		$client->setApplicationName( 'WP Mail SMTP v' . WPMS_PLUGIN_VER );
		$client->setAccessType( 'offline' );
		$client->setPrompt( 'consent' );
		$client->setIncludeGrantedScopes( false );
		// We request only the sending capability, as it's what we only need to do.
		$client->setScopes( array( Gmail::MAIL_GOOGLE_COM ) );
		$client->setRedirectUri( self::get_oauth_redirect_url() );

		// Set our custom logger to replace Monolog dependency.
		$client->setLogger( new Logger() );

		if ( self::use_self_oauth_redirect_url() ) {
			$client->setState( self::get_state_param( $this->connection ) );
		} else {
			$client->setState( self::get_plugin_auth_url( $this->connection ) );
		}

		// Apply custom options to the client.
		$client = apply_filters( 'wp_mail_smtp_providers_gmail_auth_get_client_custom_options', $client );

		if (
			$this->is_auth_required() &&
			! empty( $this->options['auth_code'] )
		) {
			try {
				$creds = $client->fetchAccessTokenWithAuthCode( $this->options['auth_code'] );
			} catch ( Exception $e ) {
				$creds['error'] = $e->getMessage();
			}

			// Bail if we have an error.
			if ( ! empty( $creds['error'] ) ) {
				if ( $creds['error'] === 'invalid_client' ) {
					$creds['error'] .= PHP_EOL . esc_html__( 'Please make sure your Google Client ID and Secret in the plugin settings are valid. Save the settings and try the Authorization again.' , 'wp-mail-smtp' );
				}

				Debug::set(
					'Mailer: Gmail' . "\r\n" .
					$creds['error']
				);

				return $client;
			} else {
				Debug::clear();
			}

			$this->update_access_token( $client->getAccessToken() );
			$this->update_refresh_token( $client->getRefreshToken() );
			$this->update_user_details( $client );

			// Update the "from email" to the connected user's email.
			if ( ! empty( $this->options['user_details']['email'] ) ) {
				$this->connection_options->set(
					[
						'mail' => [
							'from_email' => $this->options['user_details']['email'],
						],
					],
					false,
					false
				);
			}
		}

		if ( ! empty( $this->options['access_token'] ) ) {
			$client->setAccessToken( $this->options['access_token'] );
		}

		// Refresh the token if it's expired.
		if ( $client->isAccessTokenExpired() ) {
			$refresh = $client->getRefreshToken();
			if ( empty( $refresh ) && isset( $this->options['refresh_token'] ) ) {
				$refresh = $this->options['refresh_token'];
			}

			if ( ! empty( $refresh ) ) {
				try {
					$creds = $client->fetchAccessTokenWithRefreshToken( $refresh );
				} catch ( Exception $e ) {
					$creds['error'] = $e->getMessage();
					Debug::set(
						'Mailer: Gmail' . "\r\n" .
						$e->getMessage()
					);
				}

				// Bail if we have an error.
				if ( ! empty( $creds['error'] ) ) {
					return $client;
				}

				$this->update_access_token( $client->getAccessToken() );
				$this->update_refresh_token( $client->getRefreshToken() );
			}
		}

		return $client;
	}

	/**
	 * Get the auth code from the $_GET and save it.
	 * Redirect user back to settings with an error message, if failed.
	 *
	 * @since 1.0.0
	 */
	public function process() { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.MaxExceeded

		$redirect_url         = ( new ConnectionSettings( $this->connection ) )->get_admin_page_url();
		$is_setup_wizard_auth = ! empty( $this->options['is_setup_wizard_auth'] );

		if ( $is_setup_wizard_auth ) {
			$this->update_is_setup_wizard_auth( false );

			$redirect_url = SetupWizard::get_site_url() . '#/step/configure_mailer/gmail';
		}

		if ( ! ( isset( $_GET['tab'] ) && $_GET['tab'] === 'auth' ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			wp_safe_redirect( $redirect_url );
			exit;
		}

		$state = isset( $_GET['state'] ) ? sanitize_key( $_GET['state'] ) : false;

		if ( empty( $state ) ) {
			wp_safe_redirect(
				add_query_arg( 'error', 'oauth_invalid_state', $redirect_url )
			);
		}

		list( $nonce ) = array_pad( explode( '-', $state ), 1, false );

		// Verify the nonce that should be returned in the state parameter.
		if ( ! wp_verify_nonce( $nonce, $this->state_key ) ) {
			wp_safe_redirect(
				add_query_arg(
					'error',
					'google_invalid_nonce',
					$redirect_url
				)
			);
			exit;
		}

		// We can't process without saved client_id/secret.
		if ( ! $this->is_clients_saved() ) {
			Debug::set(
				esc_html__( 'There was an error while processing the Google authentication request. Please make sure that you have Client ID and Client Secret both valid and saved.', 'wp-mail-smtp' )
			);
			wp_safe_redirect(
				add_query_arg(
					'error',
					'google_no_clients',
					$redirect_url
				)
			);
			exit;
		}

		$this->include_vendor_lib();

		$code  = '';
		$scope = '';
		$error = '';

		if ( isset( $_GET['error'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$error = sanitize_key( $_GET['error'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		// In case of any error: display a message to a user.
		if ( ! empty( $error ) ) {
			DebugEvents::add_debug(
				sprintf( /* Translators: %s the error code passed from Google. */
					esc_html__( 'There was an error while processing Google authorization: %s', 'wp-mail-smtp' ),
					esc_html( $error )
				)
			);

			wp_safe_redirect(
				add_query_arg(
					'error',
					'google_' . $error,
					$redirect_url
				)
			);
			exit;
		}

		if ( isset( $_GET['code'] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$code = urldecode( $_GET['code'] );
		}

		if ( isset( $_GET['scope'] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$scope = $_GET['scope'];

			if ( self::use_self_oauth_redirect_url() ) {
				$scope = urldecode( $scope );
			} else {
				// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
				$scope = urldecode( base64_decode( $scope ) );
			}
		}

		// Let's try to get the access token.
		if (
			! empty( $code ) &&
			(
				$scope === Gmail::MAIL_GOOGLE_COM . ' ' . Gmail::GMAIL_SEND ||
				$scope === Gmail::GMAIL_SEND . ' ' . Gmail::MAIL_GOOGLE_COM ||
				$scope === Gmail::GMAIL_SEND ||
				$scope === Gmail::MAIL_GOOGLE_COM
			)
		) {
			// Save the auth code. So Google_Client can reuse it to retrieve the access token.
			$this->update_auth_code( $code );
		} else {
			DebugEvents::add_debug(
				esc_html__( 'There was an error while processing Google authorization: missing code or scope parameter.', 'wp-mail-smtp' )
			);

			wp_safe_redirect(
				add_query_arg(
					'error',
					'google_no_code_scope',
					$redirect_url
				)
			);
			exit;
		}

		Debug::clear();

		$this->get_client( true );

		$error = Debug::get_last();

		if ( ! empty( $error ) ) {
			wp_safe_redirect(
				add_query_arg(
					'error',
					'google_unsuccessful_oauth',
					$redirect_url
				)
			);
			exit;
		}

		wp_safe_redirect(
			add_query_arg(
				'success',
				'google_site_linked',
				$redirect_url
			)
		);
		exit;
	}

	/**
	 * Get the auth URL used to proceed to Provider to request access to send emails.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_auth_url() {

		if (
			! empty( $this->client ) &&
			class_exists( 'WPMailSMTP\Vendor\Google_Client', false ) &&
			$this->client instanceof Google_Client
		) {
			return filter_var( $this->client->createAuthUrl(), FILTER_SANITIZE_URL );
		}

		return '#';
	}

	/**
	 * Get and update user-related details (currently only email).
	 *
	 * @since 3.11.0
	 *
	 * @param Google_Client $client The Google Client object (optional).
	 */
	private function update_user_details( $client = false ) {

		if ( $client === false ) {
			$client = $this->get_client();
		}

		$gmail = new Gmail( $client );

		try {
			$email = $gmail->users->getProfile( 'me' )->getEmailAddress();

			$user_details = [
				'email' => $email,
			];

			// To save in DB.
			$updated_settings = [
				$this->mailer_slug => [
					'user_details' => $user_details,
				],
			];

			// To save in currently retrieved options array.
			$this->options['user_details'] = $user_details;

			$this->connection_options->set( $updated_settings, false, false );
		} catch ( Exception $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
			// Do nothing.
		}
	}

	/**
	 * Get user information (currently only email) that is associated with the current OAuth connection.
	 *
	 * @since 1.5.0
	 * @since 3.11.0 Switched to DB stored value instead of API call.
	 *
	 * @return array
	 */
	public function get_user_info() {

		/*
		 * We need to populate user data on the fly for old users who already performed
		 * authorization before we switched to DB stored value.
		 */
		if ( ! isset( $this->options['user_details'] ) && ! $this->is_auth_required() ) {
			$this->update_user_details();
		}

		return $this->connection_options->get( $this->mailer_slug, 'user_details' );
	}

	/**
	 * Get the registered email addresses that the user can use as the "from email".
	 *
	 * @since 2.2.0
	 *
	 * @return array The list of possible from email addresses.
	 */
	public function get_user_possible_send_from_addresses() {

		if ( isset( $this->aliases ) ) {
			return $this->aliases;
		}

		$gmail = new Gmail( $this->get_client() );

		try {

			// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			$response = $gmail->users_settings_sendAs->listUsersSettingsSendAs( 'me' );

			// phpcs:disable
			$this->aliases = array_map(
				function( $sendAsObject ) {
					return $sendAsObject['sendAsEmail'];
				},
				(array) $response->getSendAs()
			);
			// phpcs:enable

		} catch ( Exception $exception ) {
			DebugEvents::add_debug(
				sprintf( /* Translators: %s the error message. */
					esc_html__( 'An error occurred when trying to get Gmail aliases: %s', 'wp-mail-smtp' ),
					esc_html( $exception->getMessage() )
				)
			);

			$this->aliases = [];
		}

		return $this->aliases;
	}

	/**
	 * Get the Google oAuth 2.0 redirect URL.
	 *
	 * This is the URL that Google will redirect after the access to the Gmail account is granted or rejected.
	 * The below endpoint will then redirect back to the user's WP site (to self::get_plugin_auth_url() URL).
	 *
	 * @since 2.5.0
	 *
	 * @return string
	 */
	public static function get_oauth_redirect_url() {

		if ( self::use_self_oauth_redirect_url() ) {
			return remove_query_arg( 'state', self::get_plugin_auth_url() );
		} else {
			return 'https://connect.wpmailsmtp.com/google/';
		}
	}

	/**
	 * Get the state parameter for the Google oAuth redirect URL.
	 *
	 * @since 3.10.0
	 *
	 * @param ConnectionInterface $connection The Connection object.
	 *
	 * @return string
	 */
	private static function get_state_param( $connection ) {

		$state = [
			wp_create_nonce( 'wp_mail_smtp_provider_client_state' ),
			$connection->get_id(),
		];

		return implode( '-', $state );
	}

	/**
	 * Whether to use self website redirect URL for the Google oAuth.
	 *
	 * @since 3.10.0
	 *
	 * @return bool
	 */
	private static function use_self_oauth_redirect_url() {

		/**
		 * Filter whether to use self website redirect URL for the Google oAuth.
		 *
		 * @since 3.10.0
		 *
		 * @param bool $use Whether to use self website redirect URL for the Google oAuth.
		 */
		return apply_filters( 'wp_mail_smtp_providers_gmail_auth_use_self_oauth_redirect_url', false );
	}
}
