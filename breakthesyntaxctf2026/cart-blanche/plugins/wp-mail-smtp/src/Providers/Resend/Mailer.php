<?php

namespace WPMailSMTP\Providers\Resend;

use WPMailSMTP\ConnectionInterface;
use WPMailSMTP\Helpers\Helpers;
use WPMailSMTP\MailCatcherInterface;
use WPMailSMTP\Providers\MailerAbstract;
use WPMailSMTP\WP;

/**
 * Class Mailer.
 *
 * @since 4.7.0
 */
class Mailer extends MailerAbstract {

	/**
	 * Which response code from HTTP provider is considered to be successful?
	 *
	 * @since 4.7.0
	 *
	 * @var int
	 */
	protected $email_sent_code = 200;

	/**
	 * URL to make an API request to.
	 *
	 * @since 4.7.0
	 *
	 * @var string
	 */
	protected $url = 'https://api.resend.com/emails';

	/**
	 * Mailer constructor.
	 *
	 * @since 4.7.0
	 *
	 * @param MailCatcherInterface $phpmailer  The MailCatcher object.
	 * @param ConnectionInterface  $connection The Connection object.
	 */
	public function __construct( MailCatcherInterface $phpmailer, $connection = null ) {

		parent::__construct( $phpmailer, $connection );

		$this->set_header( 'Authorization', 'Bearer ' . $this->connection_options->get( $this->mailer, 'api_key' ) );
		$this->set_header( 'Accept', 'application/json' );
		$this->set_header( 'Content-Type', 'application/json' );
	}

	/**
	 * Set the From information for an email.
	 *
	 * @since 4.7.0
	 *
	 * @param string $email The sender email address.
	 * @param string $name  The sender name.
	 */
	public function set_from( $email, $name ) {

		if ( ! filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
			return;
		}

		$this->set_body_param(
			[
				'from' => $this->address_format( [ $email, $name ] ),
			]
		);
	}

	/**
	 * Set email recipients: to, cc, bcc.
	 *
	 * @since 4.7.0
	 *
	 * @param array $recipients Email recipients.
	 */
	public function set_recipients( $recipients ) { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		if ( empty( $recipients ) ) {
			return;
		}

		// Allow only these recipient types.
		$allowed_types = [ 'to', 'cc', 'bcc' ];
		$data          = [];

		foreach ( $recipients as $type => $emails ) {
			if (
				! in_array( $type, $allowed_types, true ) ||
				empty( $emails ) ||
				! is_array( $emails )
			) {
				continue;
			}

			// Iterate over all emails for each type.
			// There might be multiple cc/to/bcc emails.
			foreach ( $emails as $email ) {
				if ( ! isset( $email[0] ) || ! filter_var( $email[0], FILTER_VALIDATE_EMAIL ) ) {
					continue;
				}

				$data[ $type ][] = $this->address_format( $email );
			}
		}

		if ( ! empty( $data ) ) {
			$this->set_body_param( $data );
		}
	}

	/**
	 * Set the Reply To information for an email.
	 *
	 * @since 4.7.0
	 *
	 * @param array $emails Reply To email addresses.
	 */
	public function set_reply_to( $emails ) {

		if ( empty( $emails ) ) {
			return;
		}

		$data = [];

		foreach ( $emails as $email ) {
			if ( ! isset( $email[0] ) || ! filter_var( $email[0], FILTER_VALIDATE_EMAIL ) ) {
				continue;
			}

			$data[] = $this->address_format( $email );
		}

		if ( ! empty( $data ) ) {
			$this->set_body_param(
				[
					'reply_to' => $data,
				]
			);
		}
	}

	/**
	 * Set email subject.
	 *
	 * @since 4.7.0
	 *
	 * @param string $subject Email subject.
	 */
	public function set_subject( $subject ) {

		$this->set_body_param(
			[
				'subject' => $subject,
			]
		);
	}

	/**
	 * Set email content.
	 *
	 * @since 4.7.0
	 *
	 * @param string|array $content Email content.
	 */
	public function set_content( $content ) {

		if ( empty( $content ) ) {
			return;
		}

		if ( is_array( $content ) ) {
			if ( ! empty( $content['text'] ) ) {
				$this->set_body_param(
					[
						'text' => $content['text'],
					]
				);
			}

			if ( ! empty( $content['html'] ) ) {
				$this->set_body_param(
					[
						'html' => $content['html'],
					]
				);
			}
		} else {
			if ( $this->phpmailer->ContentType === 'text/plain' ) {
				$this->set_body_param(
					[
						'text' => $content,
					]
				);
			} else {
				$this->set_body_param(
					[
						'html' => $content,
					]
				);
			}
		}
	}

	/**
	 * Set attachments for an email.
	 *
	 * @since 4.7.0
	 *
	 * @param array $attachments Attachments array.
	 */
	public function set_attachments( $attachments ) { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		if ( empty( $attachments ) ) {
			return;
		}

		$data = [];

		// Prepare attachments.
		foreach ( $attachments as $attachment ) {
			$file = $this->get_attachment_file_content( $attachment );

			if ( $file === false ) {
				continue;
			}

			$attachment_data = [
				'filename'     => $this->get_attachment_file_name( $attachment ),
				'content_type' => $attachment[4],
				'content'      => base64_encode( $file ), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
			];

			if ( $attachment[6] === 'inline' ) {
				$attachment_data['content_id'] = empty( $attachment[7] ) ? '' : trim( (string) $attachment[7] );
			}

			$data[] = $attachment_data;
		}

		if ( ! empty( $data ) ) {
			$this->set_body_param(
				[
					'attachments' => $data,
				]
			);
		}
	}

	/**
	 * Set return path.
	 *
	 * @since 4.7.0
	 *
	 * @param string $email Return Path email address.
	 */
	public function set_return_path( $email ) {}

	/**
	 * Set headers.
	 *
	 * @since 4.7.0
	 *
	 * @param array $headers Headers array.
	 */
	public function set_headers( $headers ) {

		foreach ( $headers as $header ) {
			$name  = isset( $header[0] ) ? $header[0] : false;
			$value = isset( $header[1] ) ? $header[1] : false;

			$this->set_body_header( $name, $value );
		}

		// Add custom header.
		$this->set_body_header( 'X-Mailer', 'WPMailSMTP/Mailer/' . $this->mailer . ' ' . WPMS_PLUGIN_VER );
	}

	/**
	 * Set body header.
	 *
	 * @since 4.7.0
	 *
	 * @param string $name  Header name.
	 * @param string $value Header value.
	 */
	public function set_body_header( $name, $value ) {

		$name = sanitize_text_field( $name );

		if ( empty( $name ) ) {
			return;
		}

		$headers = isset( $this->body['headers'] ) ? (array) $this->body['headers'] : [];

		// Sanitize headers.
		$headers[ $name ] = $this->sanitize_header_value( $name, $value );

		$this->set_body_param(
			[
				'headers' => $headers,
			]
		);
	}

	/**
	 * Get request body.
	 *
	 * @since 4.7.0
	 */
	public function get_body() {

		$body = parent::get_body();

		return wp_json_encode( $body );
	}

	/**
	 * We might need to do something after the email was sent to the API.
	 * In this method we preprocess the response from the API.
	 *
	 * @since 4.7.0
	 *
	 * @param mixed $response Response data.
	 */
	protected function process_response( $response ) {

		parent::process_response( $response );

		if (
			! is_wp_error( $response ) &&
			! empty( $this->response['body']->id )
		) {
			$this->phpmailer->addCustomHeader( 'X-Msg-ID', $this->response['body']->id );
			$this->verify_sent_status = true;
		}
	}

	/**
	 * Whether the email is sent or not.
	 *
	 * @since 4.7.0
	 *
	 * @return bool
	 */
	public function is_email_sent() {

		$is_sent = false;

		if (
			wp_remote_retrieve_response_code( $this->response ) === $this->email_sent_code &&
			! empty( $this->response['body']->id )
		) {
			$is_sent = true;
		}

		// phpcs:disable WPForms.Comments.Since.MissingPhpDoc, WPForms.PHP.ValidateHooks.InvalidHookName

		/** This filter is documented in src/Providers/MailerAbstract.php. */
		return apply_filters( 'wp_mail_smtp_providers_mailer_is_email_sent', $is_sent, $this->mailer );
		// phpcs:enable WPForms.Comments.Since.MissingPhpDoc, WPForms.PHP.ValidateHooks.InvalidHookName
	}

	/**
	 * Get a Resend-specific response with a helpful error.
	 *
	 * @since 4.7.0
	 *
	 * @return string
	 */
	public function get_response_error() { // phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded, Generic.Metrics.CyclomaticComplexity.TooHigh

		$error_text = [
			$this->error_message,
		];

		if ( ! empty( $this->response ) ) {
			$body = wp_remote_retrieve_body( $this->response );

			if ( ! empty( $body->message ) ) {
				$error_code   = ! empty( $body->statusCode ) ? $body->statusCode : ''; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
				$error_name   = ! empty( $body->name ) ? $body->name : '';
				$error_text[] = Helpers::format_error_message( $error_name, $error_code, $body->message );
			}
		}

		return implode( WP::EOL, array_map( 'esc_textarea', array_filter( $error_text ) ) );
	}

	/**
	 * Get the error code from the Resend API response.
	 *
	 * @since 4.8.0
	 *
	 * @return string
	 */
	public function get_response_error_code() {

		if ( ! empty( $this->response ) ) {
			$body = wp_remote_retrieve_body( $this->response );

			if ( ! empty( $body->name ) ) {
				return $body->name;
			}
		}

		return parent::get_response_error_code();
	}

	/**
	 * Whether the mailer has all its settings correctly set up and saved.
	 *
	 * @since 4.7.0
	 *
	 * @return bool
	 */
	public function is_mailer_complete() {

		$options = $this->connection_options->get_group( $this->mailer );

		// API key is the only required option.
		if ( ! empty( $options['api_key'] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Get mailer debug information, that is helpful during support.
	 *
	 * @since 4.7.0
	 *
	 * @return string
	 */
	public function get_debug_info() {

		$options = $this->connection_options->get_group( $this->mailer );

		$text[] = '<strong>' . esc_html__( 'API Key:', 'wp-mail-smtp' ) . '</strong> ' .
		          ( ! empty( $options['api_key'] ) ? 'Yes' : 'No' );

		return implode( '<br>', $text );
	}

	/**
	 * Prepare address param.
	 *
	 * @since 4.7.0
	 *
	 * @param array $address Address array.
	 *
	 * @return array
	 */
	private function address_format( $address ) {

		$email = isset( $address[0] ) ? $address[0] : false;
		$name  = isset( $address[1] ) ? $address[1] : false;

		$result = $email;

		if ( ! empty( $name ) ) {
			$result = "$name <$email>";
		}

		return $result;
	}
}
