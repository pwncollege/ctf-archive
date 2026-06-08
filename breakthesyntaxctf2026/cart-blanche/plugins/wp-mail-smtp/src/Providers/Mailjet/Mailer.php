<?php

namespace WPMailSMTP\Providers\Mailjet;

use WPMailSMTP\Helpers\Helpers;
use WPMailSMTP\Providers\MailerAbstract;
use WPMailSMTP\WP;

/**
 * Class Mailer.
 *
 * @since 4.2.0
 */
class Mailer extends MailerAbstract {

	/**
	 * Which response code from HTTP provider is considered to be successful?
	 *
	 * @since 4.2.0
	 *
	 * @var int
	 */
	protected $email_sent_code = 200;

	/**
	 * URL to make an API request to.
	 *
	 * @since 4.2.0
	 *
	 * @var string
	 */
	protected $url = 'https://api.mailjet.com/v3.1/send';

	/**
	 * Mailer constructor.
	 *
	 * @since 4.2.0
	 *
	 * @param MailCatcherInterface $phpmailer  The MailCatcher object.
	 * @param ConnectionInterface  $connection The Connection object.
	 */
	public function __construct( $phpmailer, $connection = null ) {

		// We want to prefill everything from MailCatcher class, which extends PHPMailer.
		parent::__construct( $phpmailer, $connection );

		// Set mailer specific headers.
		$user = $this->connection_options->get( $this->mailer, 'api_key' );
		$pass = $this->connection_options->get( $this->mailer, 'secret_key' );

		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		$this->set_header( 'Authorization', 'Basic ' . base64_encode( "$user:$pass" ) );
		$this->set_header( 'Accept', 'application/json' );
		$this->set_header( 'Content-Type', 'application/json' );
	}

	/**
	 * Redefine the way custom headers are processed for this mailer - they should be in body.
	 *
	 * @since 4.2.0
	 *
	 * @param array $headers Headers array.
	 */
	public function set_headers( $headers ) {

		foreach ( $headers as $header ) {
			$name  = isset( $header[0] ) ? $header[0] : false;
			$value = isset( $header[1] ) ? $header[1] : false;

			$this->set_body_header( $name, $value );
		}
	}

	/**
	 * This mailer supports email-related custom headers inside a body of the message.
	 *
	 * @since 4.2.0
	 *
	 * @param string $name  Header name.
	 * @param string $value Header value.
	 */
	public function set_body_header( $name, $value ) {

		$name = sanitize_text_field( $name );

		if ( empty( $name ) ) {
			return;
		}

		$headers = isset( $this->body['Headers'] ) ? (array) $this->body['Headers'] : [];

		$headers[ $name ] = $this->sanitize_header_value( $name, $value );

		$this->set_body_param(
			[
				'Headers' => $headers,
			]
		);
	}

	/**
	 * Set the From information for an email.
	 *
	 * @since 4.2.0
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
				'From' => $this->address_format( [ $email, $name ] ),
			]
		);
	}

	/**
	 * Set email recipients: to, cc, bcc.
	 *
	 * @since 4.2.0
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

			$type = ucfirst( $type );

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
	 * @since 4.2.0
	 *
	 * @param array $emails Reply To email addresses.
	 */
	public function set_reply_to( $emails ) {

		if ( empty( $emails ) ) {
			return;
		}

		$email = array_shift( $emails );

		if ( ! isset( $email[0] ) || ! filter_var( $email[0], FILTER_VALIDATE_EMAIL ) ) {
			return;
		}

		$this->set_body_param(
			[
				'ReplyTo' => $this->address_format( $email ),
			]
		);
	}

	/**
	 * Set email subject.
	 *
	 * @since 4.2.0
	 *
	 * @param string $subject Email subject.
	 */
	public function set_subject( $subject ) {

		$this->set_body_param(
			[
				'Subject' => $subject,
			]
		);
	}

	/**
	 * Set email content.
	 *
	 * @since 4.2.0
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
						'TextPart' => $content['text'],
					]
				);
			}

			if ( ! empty( $content['html'] ) ) {
				$this->set_body_param(
					[
						'HTMLPart' => $content['html'],
					]
				);
			}
		} else {
			if ( $this->phpmailer->ContentType === 'text/plain' ) {
				$this->set_body_param(
					[
						'TextPart' => $content,
					]
				);
			} else {
				$this->set_body_param(
					[
						'HTMLPart' => $content,
					]
				);
			}
		}
	}

	/**
	 * Set attachments for an email.
	 *
	 * @since 4.2.0
	 *
	 * @param array $attachments Attachments array.
	 */
	public function set_attachments( $attachments ) { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		if ( empty( $attachments ) ) {
			return;
		}

		$data = [];

		// Split attachments into "attachments" and "inline" groups.
		foreach ( $attachments as $attachment ) {
			$mode = in_array( $attachment[6], [ 'inline', 'attachment' ], true ) ? $attachment[6] : 'attachment';

			if ( $mode === 'inline' ) {
				$data['InlinedAttachments'][] = $attachment;
			} else {
				$data['Attachments'][] = $attachment;
			}
		}

		// Prepare attachments.
		foreach ( $data as $disposition => $attachments ) {
			$data[ $disposition ] = $this->prepare_attachments( $attachments );
		}

		if ( ! empty( $data ) ) {
			$this->set_body_param( $data );
		}
	}

	/**
	 * Prepare attachments data for SendLayer API.
	 *
	 * @since 4.2.0
	 *
	 * @param array $attachments Array of attachments.
	 *
	 * @return array
	 */
	protected function prepare_attachments( $attachments ) {

		$data = [];

		foreach ( $attachments as $attachment ) {
			$file = $this->get_attachment_file_content( $attachment );

			if ( $file === false ) {
				continue;
			}

			$filetype = str_replace( ';', '', trim( $attachment[4] ) );
			$entry    = [
				'Filename'      => empty( $attachment[2] ) ? 'file-' . wp_hash( microtime() ) . '.' . $filetype : trim( $attachment[2] ),
				// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
				'Base64Content' => base64_encode( $file ),
				'ContentType'   => $attachment[4],
			];

			if ( $attachment[6] === 'inline' ) {
				$entry['ContentID'] = $entry['Filename'];
			}

			$data[] = $entry;
		}

		return $data;
	}

	/**
	 * Doesn't support this.
	 * So we do nothing.
	 *
	 * @since 4.2.0
	 *
	 * @param string $email Return Path email address.
	 */
	public function set_return_path( $email ) {}

	/**
	 * Redefine the way email body is returned.
	 * By default, we are sending an array of data.
	 * This mailer requires a JSON, so we encode the body.
	 *
	 * @since 4.2.0
	 */
	public function get_body() {

		$body = [
			'Messages' => [
				parent::get_body(),
			],
		];

		return wp_json_encode( $body );
	}

	/**
	 * Whether the email is sent or not.
	 * We check response code and a non-empty `email_id` field in the response body.
	 *
	 * @since 4.2.0
	 *
	 * @return bool
	 */
	public function is_email_sent() {

		$is_sent = false;

		if ( wp_remote_retrieve_response_code( $this->response ) === $this->email_sent_code ) {
			$is_sent = true;
		}

		// phpcs:disable WPForms.Comments.Since.MissingPhpDoc, WPForms.PHP.ValidateHooks.InvalidHookName

		/** This filter is documented in src/Providers/MailerAbstract.php. */
		return apply_filters( 'wp_mail_smtp_providers_mailer_is_email_sent', $is_sent, $this->mailer );
		// phpcs:enable WPForms.Comments.Since.MissingPhpDoc, WPForms.PHP.ValidateHooks.InvalidHookName
	}

	/**
	 * We might need to do something after the email was sent to the API.
	 * In this method we preprocess the response from the API.
	 *
	 * @since 4.2.0
	 *
	 * @param mixed $response Response data.
	 */
	protected function process_response( $response ) { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		parent::process_response( $response );

		// Bail if request failed, or has no message entries.
		if (
			is_wp_error( $response ) ||
			empty( $this->response['body']->Messages ) ||
			! is_array( $this->response['body']->Messages )
		) {
			return;
		}

		// Only pick successful messages.
		$messages = array_filter(
			$this->response['body']->Messages,
			function( $message ) {
				return strtolower( $message->Status ) === 'success';
			}
		);

		// Bail if all messages failed.
		if ( empty( $messages ) ) {
			return;
		}

		// Bail if no primary TO is defined. Unlikely, but just in case.
		if ( ! isset( $this->body['To'] ) ||
		     ! is_array( $this->body['To'] ) ||
		     ! isset( $this->body['To'][0]['Email'] ) ||
		     empty( $this->body['To'][0]['Email'] )
		) {
			return;
		}

		$primary_to = $this->body['To'][0]['Email'];

		foreach ( $messages as $message ) {
			foreach ( $message->To as $to ) {
				if ( strtolower( $to->Email ) === $primary_to ) {
					$this->phpmailer->addCustomHeader( 'X-Msg-ID', $to->MessageID );
					$this->verify_sent_status = true;

					return;
				}
			}
		}
	}

	/**
	 * Gather errors from a nested array.
	 *
	 * @since 4.2.0
	 *
	 * @param array $data      Array of data.
	 * @param int   $recursion Current recursion step.
	 *
	 * @return array
	 */
	private function gather_response_errors( $data, $recursion = 10 ) {

		$errors = [];

		// Bail if recursion exceeds the limit.
		if ( $recursion <= 0 ) {
			return $errors;
		}

		// Bail if provided data is not an array.
		if ( ! is_array( $data ) ) {
			return $errors;
		}

		if ( ! empty( $data['ErrorMessage'] ) ) {
			$errors[] = $data;

			return $errors;
		}

		foreach ( $data as $datum ) {
			$errors = array_merge( $errors, $this->gather_response_errors( $datum, $recursion - 1 ) );
		}

		return $errors;
	}

	/**
	 * Get a mailer-specific response with a helpful error.
	 *
	 * @since 4.2.0
	 *
	 * @return string
	 */
	public function get_response_error() { // phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded

		$error_text[] = $this->error_message;

		if ( ! empty( $this->response ) ) {
			$body   = wp_remote_retrieve_body( $this->response );
			$body   = json_decode( wp_json_encode( $body ), true );
			$errors = $this->gather_response_errors( $body );

			foreach ( $errors as $error ) {
				$message = $error['ErrorMessage'];
				$code    = ! empty( $error['ErrorCode'] ) ? $error['ErrorCode'] : '';

				if ( ! empty( $error['ErrorRelatedTo'] ) ) {
					$related_to = implode( ', ', $error['ErrorRelatedTo'] );
					$message    = "{$message} [{$related_to}]";
				}

				$error_text[] = Helpers::format_error_message( $message, $code );
			}
		} else {
			$error_text[] = WP::wp_remote_get_response_error_message( $this->response );
		}

		return implode( WP::EOL, array_map( 'esc_textarea', array_filter( $error_text ) ) );
	}

	/**
	 * Get the mailer-specific error code from the API response.
	 *
	 * @since 4.8.0
	 *
	 * @return string
	 */
	public function get_response_error_code() {

		if ( ! empty( $this->response ) ) {
			$body   = wp_remote_retrieve_body( $this->response );
			$body   = json_decode( wp_json_encode( $body ), true );
			$errors = $this->gather_response_errors( $body );

			if ( ! empty( $errors[0]['ErrorCode'] ) ) {
				return $errors[0]['ErrorCode'];
			}
		}

		return parent::get_response_error_code();
	}

	/**
	 * Whether the mailer has all its settings correctly set up and saved.
	 *
	 * @since 4.2.0
	 *
	 * @return bool
	 */
	public function is_mailer_complete() {

		$options = $this->connection_options->get_group( $this->mailer );

		// API key is the only required option.
		if ( ! empty( $options['api_key'] ) && ! empty( $options['secret_key'] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Prepare address param.
	 *
	 * @since 4.2.0
	 *
	 * @param array $address Address array.
	 *
	 * @return array
	 */
	private function address_format( $address ) {

		$result = [];
		$email  = isset( $address[0] ) ? $address[0] : false;
		$name   = isset( $address[1] ) ? $address[1] : false;

		$result['Email'] = $email;

		if ( ! empty( $name ) ) {
			$result['Name'] = $name;
		}

		return $result;
	}
}
