<?php

namespace WPMailSMTP\Providers\MailerSend;

use WPMailSMTP\ConnectionInterface;
use WPMailSMTP\Helpers\Helpers;
use WPMailSMTP\WP;
use WPMailSMTP\MailCatcherInterface;
use WPMailSMTP\Providers\MailerAbstract;

/**
 * Class Mailer.
 *
 * @since 4.5.0
 */
class Mailer extends MailerAbstract {

	/**
	 * URL to make an API request to.
	 *
	 * @since 4.5.0
	 *
	 * @var string
	 */
	protected $url = 'https://api.mailersend.com/v1/email';

	/**
	 * Which response code from HTTP provider is considered to be successful?
	 *
	 * @since 4.5.0
	 *
	 * @var int
	 */
	protected $email_sent_code = 202;

	/**
	 * Mailer constructor.
	 *
	 * @since 4.5.0
	 *
	 * @param MailCatcherInterface $phpmailer  The MailCatcher object.
	 * @param ConnectionInterface  $connection The Connection object.
	 */
	public function __construct( $phpmailer, $connection = null ) {

		// We want to prefill everything from MailCatcher class, which extends PHPMailer.
		parent::__construct( $phpmailer, $connection );

		// Set mailer specific headers.
		$this->set_header( 'Authorization', 'Bearer ' . $this->connection_options->get( $this->mailer, 'api_key' ) );
		$this->set_header( 'Accept', 'application/json' );
		$this->set_header( 'Content-Type', 'application/json' );
	}

	/**
	 * Check if custom headers are supported based on the plan setting.
	 *
	 * @since 4.5.0
	 *
	 * @return bool
	 */
	private function has_pro_features(): bool {

		return (bool) $this->connection_options->get( $this->mailer, 'has_pro_plan' ); // phpcs:ignore WPForms.Formatting.EmptyLineBeforeReturn.RemoveEmptyLineBeforeReturnStatement
	}

	/**
	 * Process the custom headers for this mailer.
	 * Headers are only supported on Professional plan and higher.
	 *
	 * @since 4.5.0
	 *
	 * @param array $headers Headers array.
	 */
	public function set_headers( $headers ) { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		if ( empty( $headers ) ) {
			return;
		}

		if ( ! $this->has_pro_features() ) {
			return;
		}

		foreach ( $headers as $header ) {
			$name  = isset( $header[0] ) ? $header[0] : false;
			$value = isset( $header[1] ) ? $header[1] : false;

			if ( empty( $name ) || empty( $value ) ) {
				continue;
			}

			$this->set_body_header( $name, $value );
		}

		$this->set_body_header( 'X-Mailer', 'WPMailSMTP/Mailer/' . $this->mailer . ' ' . WPMS_PLUGIN_VER );
	}

	/**
	 * This mailer supports email-related custom headers inside a body of the message.
	 *
	 * @since 4.5.0
	 *
	 * @param string $name  Header name.
	 * @param string $value Header value.
	 */
	public function set_body_header( $name, $value ) {

		$name  = sanitize_text_field( $name );
		$value = sanitize_text_field( $value );

		if ( empty( $name ) ) {
			return;
		}

		$headers   = isset( $this->body['headers'] ) ? (array) $this->body['headers'] : [];
		$headers[] = [
			'name'  => $name,
			'value' => $value,
		];

		$this->set_body_param(
			[
				'headers' => $headers,
			]
		);
	}

	/**
	 * Set the FROM addresses.
	 *
	 * @since 4.5.0
	 *
	 * @param string $email From email address.
	 * @param string $name  From name.
	 */
	public function set_from( $email, $name = '' ) {

		if ( ! filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
			return;
		}

		$from['email'] = $email;

		if ( ! empty( $name ) ) {
			$from['name'] = sanitize_text_field( $name );
		}

		$this->set_body_param(
			[
				'from' => $from,
			]
		);
	}

	/**
	 * Set email recipients: to, cc, bcc.
	 *
	 * @since 4.5.0
	 *
	 * @param array $recipients Email recipients.
	 */
	public function set_recipients( $recipients ) { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.MaxExceeded

		if ( empty( $recipients ) ) {
			return;
		}

		// Allow for now only these recipient types.
		$default = [ 'to', 'cc', 'bcc' ];
		$data    = [];

		foreach ( $recipients as $type => $emails ) {
			if (
				! in_array( $type, $default, true ) ||
				empty( $emails ) ||
				! is_array( $emails )
			) {
				continue;
			}

			$data[ $type ] = [];

			foreach ( $emails as $email ) {
				$holder = [];
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

		if ( ! empty( $data ) ) {
			foreach ( $data as $type => $type_data ) {
				$this->set_body_param(
					[
						$type => $type_data,
					]
				);
			}
		}
	}

	/**
	 * Set reply to.
	 *
	 * @since 4.5.0
	 *
	 * @param array $emails Reply to email addresses.
	 */
	public function set_reply_to( $emails ) {

		if ( empty( $emails ) ) {
			return;
		}

		$first_email = reset( $emails );

		if ( ! isset( $first_email[0] ) || ! filter_var( $first_email[0], FILTER_VALIDATE_EMAIL ) ) {
			return;
		}

		$reply_to = [
			'email' => $first_email[0],
		];

		if ( ! empty( $first_email[1] ) ) {
			$reply_to['name'] = $first_email[1];
		}

		if ( ! empty( $reply_to ) ) {
			$this->set_body_param(
				[
					'reply_to' => $reply_to,
				]
			);
		}
	}

	/**
	 * Set email subject.
	 *
	 * @since 4.5.0
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
	 * @since 4.5.0
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
	 * @since 4.5.0
	 *
	 * @param array $attachments Attachments array.
	 */
	public function set_attachments( $attachments ) {

		if ( empty( $attachments ) ) {
			return;
		}

		$data = $this->prepare_attachments( $attachments );

		if ( ! empty( $data ) ) {
			$this->set_body_param(
				[
					'attachments' => $data,
				]
			);
		}
	}

	/**
	 * Prepare attachments data for MailerSend API.
	 *
	 * @since 4.5.0
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

			$data[] = [
				'filename'    => empty( $attachment[2] ) ? 'file-' . wp_hash( microtime() ) . '.' . $filetype : trim( $attachment[2] ),
				// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
				'content'     => base64_encode( $file ),
				'type'        => $attachment[4],
				'disposition' => in_array( $attachment[6], [ 'inline', 'attachment' ], true ) ? $attachment[6] : 'attachment',
				'id'          => empty( $attachment[7] ) ? '' : trim( (string) $attachment[7] ),
			];
		}

		return $data;
	}

	/**
	 * Redefine the way email body is returned.
	 * By default, we are sending an array of data.
	 * MailerSend requires a JSON, so we encode the body.
	 *
	 * @since 4.5.0
	 */
	public function get_body() {

		$body = parent::get_body();

		return wp_json_encode( $body );
	}

	/**
	 * We might need to do something after the email was sent to the API.
	 * In this method we preprocess the response from the API.
	 *
	 * @since 4.5.0
	 *
	 * @param mixed $response Response data.
	 */
	protected function process_response( $response ) {

		parent::process_response( $response );

		if ( ! is_wp_error( $response ) ) {
			$message_id = wp_remote_retrieve_header( $response, 'x-message-id' );

			if ( ! empty( $message_id ) ) {
				$this->phpmailer->addCustomHeader( 'X-Msg-ID', $message_id );
				$this->verify_sent_status = true;
			}
		}
	}

	/**
	 * Get a MailerSend-specific response with a helpful error.
	 *
	 * @since 4.5.0
	 *
	 * @return string
	 */
	public function get_response_error() {

		$body          = (object) wp_remote_retrieve_body( $this->response );
		$error_message = $this->get_response_error_message( $body );

		return $error_message ? $error_message : WP::wp_remote_get_response_error_message( $this->response );
	}

	/**
	 * Get the error code from the MailerSend API response.
	 *
	 * @since 4.8.0
	 *
	 * @return string
	 */
	public function get_response_error_code() {

		if ( ! empty( $this->response ) ) {
			$body = wp_remote_retrieve_body( $this->response );

			// Extract #MS error reference code from message (e.g. #MS42207).
			if ( ! empty( $body->message ) && preg_match( '/#(MS\d+)/', $body->message, $matches ) ) {
				return $matches[1];
			}

			// Fall back to first error field key (e.g. "from.email").
			if ( ! empty( $body->errors ) && is_object( $body->errors ) ) {
				$keys = array_keys( (array) $body->errors );

				if ( ! empty( $keys[0] ) ) {
					return $keys[0];
				}
			}
		}

		return parent::get_response_error_code();
	}

	/**
	 * Get formatted error message from response body.
	 *
	 * @since 4.5.0
	 *
	 * @param object $body Response body object.
	 *
	 * @return string
	 */
	private function get_response_error_message( $body ) {

		$error_messages = [];

		// Handle main error message.
		if ( ! empty( $body->message ) ) {
			$error_messages[] = Helpers::format_error_message( $body->message );
		}

		// Handle array of errors with context.
		if ( ! empty( $body->errors ) && is_array( $body->errors ) && count( $body->errors ) > 1 ) {
			foreach ( $body->errors as $field => $field_errors ) {
				$field_errors = (array) $field_errors;

				foreach ( $field_errors as $error ) {
					$error_messages[] = sprintf( '%s (%s)', $error, $field );
				}
			}
		}

		return implode( PHP_EOL, $error_messages );
	}

	/**
	 * Whether the mailer is ready to be used.
	 *
	 * @since 4.5.0
	 *
	 * @return bool
	 */
	public function is_mailer_complete() {

		$options = $this->connection_options->get_group( $this->mailer );

		// Only API key is required.
		if ( ! empty( $options['api_key'] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Doesn't support this.
	 * So we do nothing.
	 *
	 * @since 4.5.0
	 *
	 * @param string $email Return Path email address.
	 */
	public function set_return_path( $email ) { }

	/**
	 * Get mailer debug information, that is helpful during support.
	 *
	 * @since 4.5.0
	 *
	 * @return string
	 */
	public function get_debug_info() {

		$mailersend = $this->connection_options->get_group( $this->mailer );

		$text[] = '<strong>Api Key:</strong> ' . ( ! empty( $mailersend['api_key'] ) ? 'Yes' : 'No' );

		return implode( '<br>', $text );
	}
}
