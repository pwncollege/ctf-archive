<?php

namespace WPMailSMTP\Providers;

use WPMailSMTP\Admin\DebugEvents\DebugEvents;
use WPMailSMTP\ConnectionInterface;
use WPMailSMTP\Debug;
use WPMailSMTP\Helpers\Helpers;
use WPMailSMTP\MailCatcherInterface;
use WPMailSMTP\Options;
use WPMailSMTP\WP;

/**
 * Class MailerAbstract.
 *
 * @since 1.0.0
 */
abstract class MailerAbstract implements MailerInterface {

	/**
	 * Which response code from HTTP provider is considered to be successful?
	 *
	 * @since 1.0.0
	 *
	 * @var int
	 */
	protected $email_sent_code = 200;

	/**
	 * @since 1.0.0
	 *
	 * @var Options
	 */
	protected $options;

	/**
	 * @since 1.0.0
	 *
	 * @var MailCatcherInterface
	 */
	protected $phpmailer;

	/**
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected $mailer = '';

	/**
	 * URL to make an API request to.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected $url = '';

	/**
	 * @since 1.0.0
	 *
	 * @var array
	 */
	protected $headers = array();

	/**
	 * @since 1.0.0
	 *
	 * @var array
	 */
	protected $body = array();

	/**
	 * @since 1.0.0
	 *
	 * @var mixed
	 */
	protected $response = array();

	/**
	 * The error message recorded when email sending failed and the error can't be processed from the API response.
	 *
	 * @since 2.5.0
	 *
	 * @var string
	 */
	protected $error_message = '';

	/**
	 * The error code recorded when email sending failed.
	 *
	 * @since 4.8.0
	 *
	 * @var string
	 */
	protected $error_code = '';

	/**
	 * Should the email sent by this mailer have its "sent status" verified via its API?
	 *
	 * @since 2.5.0
	 *
	 * @var bool
	 */
	protected $verify_sent_status = false;

	/**
	 * The Connection object.
	 *
	 * @since 3.7.0
	 *
	 * @var ConnectionInterface
	 */
	protected $connection;

	/**
	 * The connection options object.
	 *
	 * @since 3.7.0
	 *
	 * @var Options
	 */
	protected $connection_options;

	/**
	 * Mailer constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param MailCatcherInterface $phpmailer  The MailCatcher object.
	 * @param ConnectionInterface  $connection The Connection object.
	 */
	public function __construct( MailCatcherInterface $phpmailer, $connection = null ) {

		if ( ! is_null( $connection ) ) {
			$this->connection = $connection;
		} else {
			$this->connection = wp_mail_smtp()->get_connections_manager()->get_primary_connection();
		}

		$this->connection_options = $this->connection->get_options();
		$this->mailer             = $this->connection->get_mailer_slug();
		$this->options            = Options::init();

		// Only non-SMTP mailers need URL and extra processing for PHPMailer class.
		if ( ! $this->connection_options->is_mailer_smtp() && empty( $this->url ) ) {
			return;
		}

		$this->process_phpmailer( $phpmailer );
	}

	/**
	 * Re-use the MailCatcher class methods and properties.
	 *
	 * @since 1.0.0
	 *
	 * @param MailCatcherInterface $phpmailer The MailCatcher object.
	 */
	public function process_phpmailer( $phpmailer ) {

		// Make sure that we have access to PHPMailer class methods.
		if ( ! wp_mail_smtp()->is_valid_phpmailer( $phpmailer ) ) {
			return;
		}

		$this->phpmailer = $phpmailer;

		// Prevent working with those methods, as they are not needed for SMTP-like mailers.
		if ( $this->connection_options->is_mailer_smtp() ) {
			return;
		}

		$this->set_headers( $this->phpmailer->getCustomHeaders() );
		$this->set_from( $this->phpmailer->From, $this->phpmailer->FromName );
		$this->set_recipients(
			array(
				'to'  => $this->phpmailer->getToAddresses(),
				'cc'  => $this->phpmailer->getCcAddresses(),
				'bcc' => $this->phpmailer->getBccAddresses(),
			)
		);
		$this->set_subject( $this->phpmailer->Subject );
		if ( $this->phpmailer->ContentType === 'text/plain' ) {
			$this->set_content( $this->phpmailer->Body );
		} else {
			$this->set_content(
				array(
					'text' => $this->phpmailer->AltBody,
					'html' => $this->phpmailer->Body,
				)
			);
		}
		$this->set_return_path( $this->phpmailer->From );
		$this->set_reply_to( $this->phpmailer->getReplyToAddresses() );

		/*
		 * In some cases we will need to modify the internal structure
		 * of the body content, if attachments are present.
		 * So lets make this call the last one.
		 */
		$this->set_attachments( $this->phpmailer->getAttachments() );
	}

	/**
	 * Set the email headers.
	 *
	 * @since 1.0.0
	 *
	 * @param array $headers List of key=>value pairs.
	 */
	public function set_headers( $headers ) {

		foreach ( $headers as $header ) {
			$name  = isset( $header[0] ) ? $header[0] : false;
			$value = isset( $header[1] ) ? $header[1] : false;

			if ( empty( $name ) || empty( $value ) ) {
				continue;
			}

			$this->set_header( $name, $value );
		}
	}

	/**
	 * Set individual header key=>value pair for the email.
	 *
	 * @since 1.0.0
	 *
	 * @param string $name
	 * @param string $value
	 */
	public function set_header( $name, $value ) {

		$name = sanitize_text_field( $name );

		$this->headers[ $name ] = WP::sanitize_value( $value );
	}

	/**
	 * Set email subject.
	 *
	 * @since 1.0.0
	 *
	 * @param string $subject
	 */
	public function set_subject( $subject ) {

		$this->set_body_param(
			array(
				'subject' => $subject,
			)
		);
	}

	/**
	 * Set the request params, that goes to the body of the HTTP request.
	 *
	 * @since 1.0.0
	 *
	 * @param array $param Key=>value of what should be sent to a 3rd party API.
	 *
	 * @internal param array $params
	 */
	protected function set_body_param( $param ) {

		$this->body = Options::array_merge_recursive( $this->body, $param );
	}

	/**
	 * Get the email body.
	 *
	 * @since 1.0.0
	 *
	 * @return string|array
	 */
	public function get_body() {

		return apply_filters( 'wp_mail_smtp_providers_mailer_get_body', $this->body, $this->mailer );
	}

	/**
	 * Get the email headers.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_headers() {

		return apply_filters( 'wp_mail_smtp_providers_mailer_get_headers', $this->headers, $this->mailer );
	}

	/**
	 * Send the email.
	 *
	 * @since 1.0.0
	 * @since 1.8.0 Added timeout for requests, same as max_execution_time.
	 */
	public function send() {

		$timeout = (int) ini_get( 'max_execution_time' );

		$params = Options::array_merge_recursive(
			$this->get_default_params(),
			array(
				'headers' => $this->get_headers(),
				'body'    => $this->get_body(),
				'timeout' => $timeout ? $timeout : 30,
			)
		);

		$response = wp_safe_remote_post( $this->url, $params );

		DebugEvents::add_debug(
			esc_html__( 'An email request was sent.', 'wp-mail-smtp' )
		);

		$this->process_response( $response );
	}

	/**
	 * We might need to do something after the email was sent to the API.
	 * In this method we preprocess the response from the API.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $response Response array.
	 */
	protected function process_response( $response ) {

		if ( is_wp_error( $response ) ) {
			// Save the error text.
			foreach ( $response->errors as $error_code => $error_message ) {
				$this->error_message .= Helpers::format_error_message( $error_message, $error_code ) . WP::EOL;
			}

			return;
		}

		if ( wp_remote_retrieve_response_code( $response ) !== $this->email_sent_code ) {
			$this->error_code = wp_remote_retrieve_response_code( $response );
		}

		if ( isset( $response['body'] ) && WP::is_json( $response['body'] ) ) {
			$response['body'] = json_decode( $response['body'] );
		}

		$this->response = $response;
	}

	/**
	 * Get the default params, required for wp_safe_remote_post().
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	protected function get_default_params() {

		return apply_filters(
			'wp_mail_smtp_providers_mailer_get_default_params',
			array(
				'timeout'     => 15,
				'httpversion' => '1.1',
				'blocking'    => true,
			),
			$this->mailer
		);
	}

	/**
	 * Whether the email is sent or not.
	 * We basically check the response code from a request to provider.
	 * Might not be 100% correct, not guarantees that email is delivered.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function is_email_sent() {

		$is_sent = false;

		if ( wp_remote_retrieve_response_code( $this->response ) === $this->email_sent_code ) {
			$is_sent = true;
		}

		/**
		 * Filters whether the email is sent or not.
		 *
		 * @since 3.1.0
		 *
		 * @param bool           $is_sent Whether the email is sent or not.
		 * @param MailerAbstract $mailer  Mailer object.
		 */
		return apply_filters( 'wp_mail_smtp_providers_mailer_is_email_sent', $is_sent, $this->mailer );
	}

	/**
	 * The error message when email sending failed.
	 * Should be overwritten when appropriate.
	 *
	 * @since 1.2.0
	 * @since 2.5.0 Return a non-empty error_message attribute.
	 *
	 * @return string
	 */
	public function get_response_error() {

		return ! empty( $this->error_message ) ? $this->error_message : '';
	}

	/**
	 * The error code when email sending failed.
	 * Should be overwritten when appropriate.
	 *
	 * @since 4.8.0
	 *
	 * @return string
	 */
	public function get_response_error_code() {

		return ! empty( $this->error_code ) ? $this->error_code : '';
	}

	/**
	 * Get the HTTP response code.
	 *
	 * @since 4.8.0
	 *
	 * @return int
	 */
	public function get_response_code() {

		if ( empty( $this->response ) || ! is_array( $this->response ) ) {
			return 0;
		}

		return (int) wp_remote_retrieve_response_code( $this->response );
	}

	/**
	 * Whether the mailer supports the current PHP version or not.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function is_php_compatible() {

		$options = wp_mail_smtp()->get_providers()->get_options( $this->mailer, $this->connection );

		return version_compare( phpversion(), $options->get_php_version(), '>=' );
	}

	/**
	 * This method is relevant to SMTP and Pepipost.
	 * All other custom mailers should override it with own information.
	 *
	 * @since 1.2.0
	 *
	 * @return string
	 */
	public function get_debug_info() {

		global $phpmailer;

		$smtp_text = array();

		// Mail mailer has nothing to return.
		if ( $this->connection_options->is_mailer_smtp() ) {
			// phpcs:disable
			$smtp_text[] = '<strong>ErrorInfo:</strong> ' . make_clickable( wp_strip_all_tags( $phpmailer->ErrorInfo ) );
			$smtp_text[] = '<strong>Host:</strong> ' . $phpmailer->Host;
			$smtp_text[] = '<strong>Port:</strong> ' . $phpmailer->Port;
			$smtp_text[] = '<strong>SMTPSecure:</strong> ' . Debug::pvar( $phpmailer->SMTPSecure );
			$smtp_text[] = '<strong>SMTPAutoTLS:</strong> ' . Debug::pvar( $phpmailer->SMTPAutoTLS );
			$smtp_text[] = '<strong>SMTPAuth:</strong> ' . Debug::pvar( $phpmailer->SMTPAuth );
			if ( ! empty( $phpmailer->SMTPOptions ) ) {
				$smtp_text[] = '<strong>SMTPOptions:</strong> <code>' . wp_json_encode( $phpmailer->SMTPOptions ) . '</code>';
			}
			// phpcs:enable
		}

		$smtp_text[] = '<br><strong>Server:</strong>';
		$smtp_text[] = '<strong>OpenSSL:</strong> ' . ( extension_loaded( 'openssl' ) && defined( 'OPENSSL_VERSION_TEXT' ) ? OPENSSL_VERSION_TEXT : 'No' );
		if ( function_exists( 'apache_get_modules' ) ) {
			$modules     = apache_get_modules();
			$smtp_text[] = '<strong>Apache.mod_security:</strong> ' . ( in_array( 'mod_security', $modules, true ) || in_array( 'mod_security2', $modules, true ) ? 'Yes' : 'No' );
		}
		if ( function_exists( 'selinux_is_enabled' ) ) {
			$smtp_text[] = '<strong>OS.SELinux:</strong> ' . ( selinux_is_enabled() ? 'Yes' : 'No' );
		}
		if ( function_exists( 'grsecurity_is_enabled' ) ) {
			$smtp_text[] = '<strong>OS.grsecurity:</strong> ' . ( grsecurity_is_enabled() ? 'Yes' : 'No' );
		}

		return implode( '<br>', $smtp_text );
	}

	/**
	 * Get the email addresses for the reply to email parameter.
	 *
	 * @deprecated 2.1.1
	 *
	 * @since 2.1.0
	 * @since 2.1.1 Not used anymore.
	 *
	 * @return array
	 */
	public function get_reply_to_addresses() {

		_deprecated_function( __CLASS__ . '::' . __METHOD__, '2.1.1 of WP Mail SMTP plugin' );

		$reply_to = $this->phpmailer->getReplyToAddresses();

		// Return the passed reply to addresses, if defined.
		if ( ! empty( $reply_to ) ) {
			return $reply_to;
		}

		// Return the default reply to addresses.
		return apply_filters(
			'wp_mail_smtp_providers_mailer_default_reply_to_addresses',
			$this->default_reply_to_addresses()
		);
	}

	/**
	 * Get the default email addresses for the reply to email parameter.
	 *
	 * @deprecated 2.1.1
	 *
	 * @since 2.1.0
	 * @since 2.1.1 Not used anymore.
	 *
	 * @return array
	 */
	public function default_reply_to_addresses() {

		_deprecated_function( __CLASS__ . '::' . __METHOD__, '2.1.1 of WP Mail SMTP plugin' );

		return [
			$this->phpmailer->From => [
				$this->phpmailer->From,
				$this->phpmailer->FromName,
			],
		];
	}

	/**
	 * Should the email sent by this mailer have its "sent status" verified via its API?
	 *
	 * @since 2.5.0
	 *
	 * @return bool
	 */
	public function should_verify_sent_status() {

		return $this->verify_sent_status;
	}

	/**
	 * Verify the "sent status" of the provided email log ID.
	 * The actual verification background task is triggered in the below action hook.
	 *
	 * @since 2.5.0
	 *
	 * @param int $email_log_id The ID of the email log.
	 */
	public function verify_sent_status( $email_log_id ) {

		if ( ! $this->should_verify_sent_status() ) {
			return;
		}

		do_action( 'wp_mail_smtp_providers_mailer_verify_sent_status', $email_log_id, $this );
	}

	/**
	 * Get the name/slug of the current mailer.
	 *
	 * @since 2.5.0
	 *
	 * @return string
	 */
	public function get_mailer_name() {

		return $this->mailer;
	}

	/**
	 * Get PHPMailer attachment file content.
	 *
	 * @since 3.1.0
	 *
	 * @param array $attachment PHPMailer attachment.
	 *
	 * @return string|false
	 */
	public function get_attachment_file_content( $attachment ) {

		$file = false;

		/*
		 * We are not using WP_Filesystem API as we can't reliably work with it.
		 * It is not always available, same as credentials for FTP.
		 */
		try {
			if ( $attachment[5] === true ) {  // Whether there is string attachment.
				$file = $attachment[0];
			} elseif ( is_file( $attachment[0] ) && is_readable( $attachment[0] ) ) {
				$file = file_get_contents( $attachment[0] );
			}
		} catch ( \Exception $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
			// We don't handle this exception as we define a default value above.
		}

		return $file;
	}

	/**
	 * Get PHPMailer attachment file size.
	 *
	 * @since 3.4.0
	 *
	 * @param array $attachment PHPMailer attachment.
	 *
	 * @return int|false
	 */
	public function get_attachment_file_size( $attachment ) {

		$size = false;

		if ( $attachment[5] === true ) {  // Whether there is string attachment.
			$size = Helpers::strsize( $attachment[0] );
		} elseif ( is_file( $attachment[0] ) && is_readable( $attachment[0] ) ) {
			$size = filesize( $attachment[0] );
		}

		return $size;
	}

	/**
	 * Get PHPMailer attachment file name.
	 *
	 * @since 3.4.0
	 *
	 * @param array $attachment PHPMailer attachment.
	 *
	 * @return string
	 */
	public function get_attachment_file_name( $attachment ) {

		$filetype = str_replace( ';', '', trim( $attachment[4] ) );

		return ! empty( $attachment[2] ) ? trim( $attachment[2] ) : 'file-' . wp_hash( microtime() ) . '.' . $filetype;
	}

	/**
	 * Perform remote request with merged default params.
	 *
	 * @since 3.4.0
	 *
	 * @param string $url    Request url.
	 * @param array  $params Request params.
	 *
	 * @return array
	 */
	public function remote_request( $url, $params ) {

		if ( ! isset( $params['method'] ) ) {
			$params['method'] = 'POST';
		}

		$params = Options::array_merge_recursive( $this->get_default_params(), $params );

		/**
		 * Filters request params.
		 *
		 * @since 3.4.0
		 *
		 * @param array          $params Request params.
		 * @param MailerAbstract $mailer Mailer object.
		 */
		$params = apply_filters( 'wp_mail_smtp_providers_mailer_remote_request_params', $params, $this );

		/**
		 * Filters request url.
		 *
		 * @since 3.4.0
		 *
		 * @param string         $url    Request url.
		 * @param MailerAbstract $mailer Mailer object.
		 */
		$url = apply_filters( 'wp_mail_smtp_providers_mailer_remote_request_url', $url, $this );

		return wp_safe_remote_request( $url, $params );
	}

	/**
	 * Get the Connection object.
	 *
	 * @since 3.7.0
	 *
	 * @return ConnectionInterface
	 */
	public function get_connection() {

		return $this->connection;
	}

	/**
	 * Sanitize email header values.
	 *
	 * @param string $name  Name of the header.
	 * @param string $value Value of the header.
	 *
	 * @since 3.11.1
	 */
	public function sanitize_header_value( $name, $value ) {

		if (
			in_array(
				strtolower( $name ),
				[
					'cc',
					'bcc',
					'reply-to',
					'message-id',
					'list-unsubscribe',
					'references',
					'in-reply-to'
				],
				true
			)
		) {
			return $value;
		}

		return WP::sanitize_value( $value );
	}
}
