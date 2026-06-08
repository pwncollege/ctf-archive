<?php

namespace WPMailSMTP\Providers\Sendinblue;

use WPMailSMTP\ConnectionInterface;
use WPMailSMTP\Helpers\Helpers;
use WPMailSMTP\MailCatcherInterface;
use WPMailSMTP\Providers\MailerAbstract;
use WPMailSMTP\WP;

/**
 * Class Mailer.
 *
 * @since 1.6.0
 */
class Mailer extends MailerAbstract {

	/**
	 * Which response code from HTTP provider is considered to be successful?
	 *
	 * @since 1.6.0
	 *
	 * @var int
	 */
	protected $email_sent_code = 201;

	/**
	 * Response code for scheduled email.
	 *
	 * @since 3.9.0
	 *
	 * @var int
	 */
	protected $email_scheduled_code = 202;

	/**
	 * URL to make an API request to.
	 *
	 * @since 1.6.0
	 * @since 3.9.0 Update to use Brevo API.
	 *
	 * @var string
	 */
	protected $url = 'https://api.brevo.com/v3/smtp/email';

	/**
	 * Mailer constructor.
	 *
	 * @since 3.9.0
	 *
	 * @param MailCatcherInterface $phpmailer  The MailCatcher object.
	 * @param ConnectionInterface  $connection The Connection object.
	 */
	public function __construct( $phpmailer, $connection = null ) {

		parent::__construct( $phpmailer, $connection );

		$this->set_header( 'api-key', $this->connection_options->get( $this->mailer, 'api_key' ) );
		$this->set_header( 'Accept', 'application/json' );
		$this->set_header( 'content-type', 'application/json' );
	}

	/**
	 * The list of allowed attachment files extensions.
	 *
	 * @see   https://developers.sendinblue.com/reference#sendTransacEmail_attachment__title
	 *
	 * @since 1.6.0
	 *
	 * @var array
	 */
	// @formatter:off
	protected $allowed_attach_ext = array( 'xlsx', 'xls', 'ods', 'docx', 'docm', 'doc', 'csv', 'pdf', 'txt', 'gif', 'jpg', 'jpeg', 'png', 'tif', 'tiff', 'rtf', 'bmp', 'cgm', 'css', 'shtml', 'html', 'htm', 'zip', 'xml', 'ppt', 'pptx', 'tar', 'ez', 'ics', 'mobi', 'msg', 'pub', 'eps', 'odt', 'mp3', 'm4a', 'm4v', 'wma', 'ogg', 'flac', 'wav', 'aif', 'aifc', 'aiff', 'mp4', 'mov', 'avi', 'mkv', 'mpeg', 'mpg', 'wmv' );
	// @formatter:on

	/**
	 * Redefine the way custom headers are processed for this mailer - they should be in body.
	 *
	 * @since 3.9.0
	 *
	 * @param array $headers List of key=>value pairs.
	 */
	public function set_headers( $headers ) {

		foreach ( $headers as $header ) {
			$name  = isset( $header[0] ) ? $header[0] : false;
			$value = isset( $header[1] ) ? $header[1] : false;

			$this->set_body_header( $name, $value );
		}

		// Add custom PHPMailer-specific header.
		$this->set_body_header( 'X-Mailer', 'WPMailSMTP/Mailer/' . $this->mailer . ' ' . WPMS_PLUGIN_VER );
	}

	/**
	 * This mailer supports email-related custom headers inside a body of the message.
	 *
	 * @since 3.9.0
	 *
	 * @param string $name  Key.
	 * @param string $value Value.
	 */
	public function set_body_header( $name, $value ) {

		$name = sanitize_text_field( $name );

		if ( empty( $name ) ) {
			return;
		}

		$headers          = isset( $this->body['headers'] ) ? (array) $this->body['headers'] : [];
		$headers[ $name ] = $this->sanitize_header_value( $name, $value );

		$this->set_body_param(
			[
				'headers' => $headers,
			]
		);
	}

	/**
	 * Set the From information for an email.
	 *
	 * @since 1.6.0
	 *
	 * @param string $email
	 * @param string $name
	 */
	public function set_from( $email, $name ) {

		if ( ! filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
			return;
		}

		$this->body['sender'] = array(
			'email' => $email,
			'name'  => ! empty( $name ) ? WP::sanitize_value( $name ) : '',
		);
	}

	/**
	 * Set email recipients: to, cc, bcc.
	 *
	 * @since 1.6.0
	 *
	 * @param array $recipients
	 */
	public function set_recipients( $recipients ) {

		if ( empty( $recipients ) ) {
			return;
		}

		// Allow for now only these recipient types.
		$default = array( 'to', 'cc', 'bcc' );
		$data    = array();

		foreach ( $recipients as $type => $emails ) {

			if (
				! in_array( $type, $default, true ) ||
				empty( $emails ) ||
				! is_array( $emails )
			) {
				continue;
			}

			$data[ $type ] = array();

			// Iterate over all emails for each type.
			// There might be multiple cc/to/bcc emails.
			foreach ( $emails as $email ) {
				$holder = array();
				$addr   = isset( $email[0] ) ? $email[0] : false;
				$name   = isset( $email[1] ) ? $email[1] : false;

				if ( ! filter_var( $addr, FILTER_VALIDATE_EMAIL ) ) {
					continue;
				}

				$holder['email'] = $addr;
				if ( ! empty( $name ) ) {
					$holder['name'] = $name;
				}

				array_push( $data[ $type ], $holder );
			}
		}

		foreach ( $data as $type => $type_recipients ) {
			$this->body[ $type ] = $type_recipients;
		}
	}

	/**
	 * @inheritDoc
	 *
	 * @since 1.6.0
	 */
	public function set_subject( $subject ) {

		$this->body['subject'] = $subject;
	}

	/**
	 * Set email content.
	 *
	 * @since 1.6.0
	 *
	 * @param string|array $content
	 */
	public function set_content( $content ) {

		if ( empty( $content ) ) {
			return;
		}

		if ( is_array( $content ) ) {

			if ( ! empty( $content['text'] ) ) {
				$this->body['textContent'] = $content['text'];
			}

			if ( ! empty( $content['html'] ) ) {
				$this->body['htmlContent'] = $content['html'];
			}
		} else {
			if ( $this->phpmailer->ContentType === 'text/plain' ) {
				$this->body['textContent'] = $content;
			} else {
				$this->body['htmlContent'] = $content;
			}
		}
	}

	/**
	 * Doesn't support this.
	 *
	 * @since 1.6.0
	 *
	 * @param string $email
	 */
	public function set_return_path( $email ) {

	}

	/**
	 * Set the Reply To headers if not set already.
	 *
	 * @since 1.6.0
	 *
	 * @param array $emails
	 */
	public function set_reply_to( $emails ) {

		if ( empty( $emails ) ) {
			return;
		}

		$data = array();

		foreach ( $emails as $user ) {
			$holder = array();
			$addr   = isset( $user[0] ) ? $user[0] : false;
			$name   = isset( $user[1] ) ? $user[1] : false;

			if ( ! filter_var( $addr, FILTER_VALIDATE_EMAIL ) ) {
				continue;
			}

			$holder['email'] = $addr;
			if ( ! empty( $name ) ) {
				$holder['name'] = $name;
			}

			$data[] = $holder;
		}

		if ( ! empty( $data ) ) {
			$this->body['replyTo'] = $data[0];
		}
	}

	/**
	 * Set attachments for an email.
	 *
	 * @since 1.6.0
	 *
	 * @param array $attachments The array of attachments data.
	 */
	public function set_attachments( $attachments ) {

		if ( empty( $attachments ) ) {
			return;
		}

		foreach ( $attachments as $attachment ) {

			$ext = pathinfo( $attachment[1], PATHINFO_EXTENSION );

			if ( ! in_array( $ext, $this->allowed_attach_ext, true ) ) {
				continue;
			}

			$file = $this->get_attachment_file_content( $attachment );

			if ( $file === false ) {
				continue;
			}

			$this->body['attachment'][] = [
				'name'    => $this->get_attachment_file_name( $attachment ),
				'content' => base64_encode( $file ), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
			];
		}
	}

	/**
	 * Get the email body.
	 *
	 * @since 1.6.0
	 * @since 3.9.0 Returns email body array instead of `SendSmtpEmail` object.
	 * @since 3.10.0 Returns JSON encoded email body instead of array.
	 *
	 * @return string
	 */
	public function get_body() {

		/**
		 * Filters Sendinblue email body.
		 *
		 * @since 3.5.0
		 *
		 * @param array $body Email body.
		 */
		$body = apply_filters( 'wp_mail_smtp_providers_sendinblue_mailer_get_body', $this->body );

		return wp_json_encode( $body );
	}

	/**
	 * We might need to do something after the email was sent to the API.
	 * In this method we preprocess the response from the API.
	 *
	 * @since 1.6.0
	 * @since 3.9.0 Expect a generic class object instead of `CreateSmtpEmail`.
	 *
	 * @param mixed $response Response from the API.
	 */
	protected function process_response( $response ) {

		parent::process_response( $response );

		if ( $this->has_message_id() ) {
			$this->phpmailer->MessageID = $this->response['body']->messageId;
			$this->verify_sent_status   = true;
		}
	}

	/**
	 * Get a Sendinblue-specific response with a helpful error.
	 *
	 * @since 3.9.0
	 *
	 * @return string
	 */
	public function get_response_error() {

		$error_text = [];

		if ( ! empty( $this->error_message ) ) {
			$error_text[] = $this->error_message;
		}

		if ( ! empty( $this->response ) ) {
			$body = wp_remote_retrieve_body( $this->response );

			if ( ! empty( $body->message ) ) {
				$error_text[] = Helpers::format_error_message( $body->message, ! empty( $body->code ) ? $body->code : '' );
			} else {
				$error_text[] = WP::wp_remote_get_response_error_message( $this->response );
			}
		}

		return implode( WP::EOL, array_map( 'esc_textarea', array_filter( $error_text ) ) );
	}

	/**
	 * Get the error code from the Brevo API response.
	 *
	 * @since 4.8.0
	 *
	 * @return string
	 */
	public function get_response_error_code() {

		if ( ! empty( $this->response ) ) {
			$body = wp_remote_retrieve_body( $this->response );

			if ( ! empty( $body->code ) ) {
				return $body->code;
			}
		}

		return parent::get_response_error_code();
	}

	/**
	 * Check whether the response has `messageId` property.
	 *
	 * @since 3.9.0
	 *
	 * @return bool
	 */
	private function has_message_id() {

		if (
			! in_array(
				wp_remote_retrieve_response_code( $this->response ),
				[ $this->email_sent_code, $this->email_scheduled_code ],
				true
			) ||
			empty( $this->response['body']->messageId )
		) {
			return false;
		}

		return true;
	}

	/**
	 * Check whether the email was sent.
	 *
	 * @since 1.6.0
	 * @since 3.9.0 Check if `$this->response` has `messageId` property to check if the email was sent.
	 *
	 * @return bool
	 */
	public function is_email_sent() {

		/** This filter is documented in src/Providers/MailerAbstract.php. */
		return apply_filters( 'wp_mail_smtp_providers_mailer_is_email_sent', $this->has_message_id(), $this->mailer ); // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName
	}

	/**
	 * @inheritdoc
	 *
	 * @since 1.6.0
	 */
	public function get_debug_info() {

		$mailjet_text[] = '<strong>API Key:</strong> ' . ( $this->is_mailer_complete() ? 'Yes' : 'No' );

		return implode( '<br>', $mailjet_text );
	}

	/**
	 * @inheritdoc
	 *
	 * @since 1.6.0
	 */
	public function is_mailer_complete() {

		$options = $this->connection_options->get_group( $this->mailer );

		// API key is the only required option.
		if ( ! empty( $options['api_key'] ) ) {
			return true;
		}

		return false;
	}
}
