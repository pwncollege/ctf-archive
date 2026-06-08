<?php

namespace WPMailSMTP\Providers\Mandrill;

use WPMailSMTP\ConnectionInterface;
use WPMailSMTP\MailCatcherInterface;
use WPMailSMTP\Providers\MailerAbstract;
use WPMailSMTP\Helpers\Helpers;
use WPMailSMTP\WP;

/**
 * Class Mailer.
 *
 * @since 4.6.0
 *
 * @package WPMailSMTP\Providers\Mandrill
 */
class Mailer extends MailerAbstract {

	/**
	 * Which response code from HTTP provider is considered to be successful?
	 *
	 * @since 4.6.0
	 *
	 * @var int
	 */
	protected $email_sent_code = 200;

	/**
	 * URL to make an API request to.
	 *
	 * @since 4.6.0
	 *
	 * @var string
	 */
	protected $url = 'https://mandrillapp.com/api/1.0/messages/send';

	/**
	 * Mailer constructor.
	 *
	 * @since 4.6.0
	 *
	 * @param MailCatcherInterface $phpmailer  The MailCatcher object.
	 * @param ConnectionInterface  $connection The Connection object.
	 */
	public function __construct( $phpmailer, $connection = null ) {

		// We want to prefill everything from MailCatcher class, which extends PHPMailer.
		parent::__construct( $phpmailer, $connection );

		// Set mailer specific headers.
		$this->set_header( 'Content-Type', 'application/json' );

		$this->set_body_param(
			[
				'key'     => $this->connection_options->get( $this->mailer, 'api_key' ),
				'message' => [], // Initialize the message structure.
			]
		);
	}

	/**
	 * Get the email body.
	 *
	 * @since 4.6.0
	 *
	 * @return string|array
	 */
	public function get_body() {

		return wp_json_encode( parent::get_body() );
	}

	/**
	 * Redefine the way custom headers are processed for this mailer - they should be in body.
	 *
	 * @since 4.6.0
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
	 * This mailer supports email-related custom headers inside the body of the message.
	 *
	 * @since 4.6.0
	 *
	 * @param string $name  Header name.
	 * @param string $value Header value.
	 */
	public function set_body_header( $name, $value ) {

		$name = sanitize_text_field( $name );

		if ( empty( $name ) ) {
			return;
		}

		$headers = isset( $this->body['message']['headers'] ) ? (array) $this->body['message']['headers'] : [];

		$headers[ $name ] = $this->sanitize_header_value( $name, $value );

		$this->set_body_param(
			[
				'message' => [
					'headers' => $headers,
				],
			]
		);
	}

	/**
	 * Set the From information for an email.
	 *
	 * @since 4.6.0
	 *
	 * @param string $email The sender email address.
	 * @param string $name  The sender name.
	 */
	public function set_from( $email, $name = '' ) {

		if ( ! filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
			return;
		}

		$this->set_body_param(
			[
				'message' => [
					'from_email' => $email,
					'from_name'  => ! empty( $name ) ? $name : '',
				],
			]
		);
	}

	/**
	 * Set email recipients: to, cc, bcc.
	 *
	 * @since 4.6.0
	 *
	 * @param array $recipients Email recipients.
	 */
	public function set_recipients( $recipients ) { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		if ( empty( $recipients ) ) {
			return;
		}

		$data    = [];
		$default = [ 'to', 'cc', 'bcc' ];

		// Process all recipient types (to, cc, bcc).
		foreach ( $recipients as $kind => $emails ) {
			if (
				! in_array( $kind, $default, true ) ||
				empty( $emails ) ||
				! is_array( $emails )
			) {
				continue;
			}

			// Process each email address for this type.
			foreach ( $emails as $email ) {
				$addr = isset( $email[0] ) ? $email[0] : false;
				$name = isset( $email[1] ) ? $email[1] : false;

				if ( ! filter_var( $addr, FILTER_VALIDATE_EMAIL ) ) {
					continue;
				}

				$data[] = [
					'email' => $addr,
					'name'  => ! empty( $name ) ? $name : '',
					'type'  => $kind,
				];
			}
		}

		if ( ! empty( $data ) ) {
			$this->set_body_param(
				[
					'message' => [
						'to' => $data,
					],
				]
			);
		}
	}

	/**
	 * Set the Reply To information for an email.
	 *
	 * @since 4.6.0
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

			$data[] = $this->phpmailer->addrFormat( $email );
		}

		if ( ! empty( $data ) ) {
			$this->set_body_param(
				[
					'message' => [
						'headers' => [
							'Reply-To' => implode( ',', $data ),
						],
					],
				]
			);
		}
	}

	/**
	 * Set email subject.
	 *
	 * @since 4.6.0
	 *
	 * @param string $subject Email subject.
	 */
	public function set_subject( $subject ) {

		$this->set_body_param(
			[
				'message' => [
					'subject' => $subject,
				],
			]
		);
	}

	/**
	 * Set email content.
	 *
	 * @since 4.6.0
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
						'message' => [
							'text' => $content['text'],
						],
					]
				);
			}

			if ( ! empty( $content['html'] ) ) {
				$this->set_body_param(
					[
						'message' => [
							'html' => $content['html'],
						],
					]
				);
			}
		} else {
			if ( $this->phpmailer->ContentType === 'text/plain' ) {
				$this->set_body_param(
					[
						'message' => [
							'text' => $content,
						],
					]
				);
			} else {
				$this->set_body_param(
					[
						'message' => [
							'html' => $content,
						],
					]
				);
			}
		}
	}

	/**
	 * Set attachments for an email.
	 *
	 * @since 4.6.0
	 *
	 * @param array $attachments Attachments array.
	 */
	public function set_attachments( $attachments ) {

		if ( empty( $attachments ) ) {
			return;
		}

		$data = $this->prepare_attachments( $attachments );

		if ( ! empty( $data['attachments'] ) ) {
			$this->set_body_param(
				[
					'message' => [
						'attachments' => $data['attachments'],
					],
				]
			);
		}
	}

	/**
	 * Prepare attachments data for Mandrill API.
	 *
	 * @since 4.6.0
	 *
	 * @param array $attachments Array of attachments.
	 *
	 * @return array
	 */
	protected function prepare_attachments( $attachments ) {

		$data = [
			'attachments' => [],
		];

		foreach ( $attachments as $attachment ) {
			$file = $this->get_attachment_file_content( $attachment );

			if ( $file === false ) {
				continue;
			}

			$filetype = str_replace( ';', '', trim( $attachment[4] ) );

			$data['attachments'][] = [
				'type'    => $filetype,
				'name'    => empty( $attachment[2] ) ? 'file-' . wp_hash( microtime() ) . '.' . $filetype : trim( $attachment[2] ),
				// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
				'content' => base64_encode( $file ),
			];
		}

		return $data;
	}

	/**
	 * We might need to do something after the email was sent to the API.
	 * In this method we preprocess the response from the API.
	 *
	 * @since 4.6.0
	 *
	 * @param mixed $response Response data.
	 */
	protected function process_response( $response ) {

		parent::process_response( $response );

		if ( is_wp_error( $response ) ) {
			return;
		}

		$body = isset( $this->response['body'] ) ? $this->response['body'] : '';

		if ( ! empty( $body ) && is_array( $body ) && ! empty( $body[0]->_id ) ) {
			$this->phpmailer->addCustomHeader( 'X-Msg-ID', $body[0]->_id );

			// Skip verification for failed emails.
			if ( $this->is_email_sent() ) {
				$this->verify_sent_status = true;
			}
		}
	}

	/**
	 * Get a Mandrill-specific response with a helpful error message.
	 *
	 * @since 4.6.0
	 *
	 * @return string
	 */
	public function get_response_error() {

		$error_text[] = $this->error_message;

		if ( ! empty( $this->response ) ) {
			$body = wp_remote_retrieve_body( $this->response );

			if ( is_array( $body ) && ! empty( $body[0] ) && is_object( $body[0] ) && isset( $body[0]->status ) ) {
				$reason       = ! empty( $body[0]->reject_reason ) ? $body[0]->reject_reason : $body[0]->status;
				$error_text[] = Helpers::format_error_message(
					/* translators: %s - The reason the email was rejected. */
					sprintf( esc_html__( 'The email failed to be sent. Reason: %s', 'wp-mail-smtp' ), $reason )
				);
			} elseif ( is_object( $body ) && isset( $body->message ) && isset( $body->code ) ) {
				$error_text[] = Helpers::format_error_message( $body->message, $body->code );
			} else {
				$error_text[] = WP::wp_remote_get_response_error_message( $this->response );
			}
		}

		return implode( WP::EOL, array_map( 'esc_textarea', array_filter( $error_text ) ) );
	}

	/**
	 * Get the error code from the Mandrill API response.
	 *
	 * @since 4.8.0
	 *
	 * @return string
	 */
	public function get_response_error_code() { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		if ( ! empty( $this->response ) ) {
			$body = wp_remote_retrieve_body( $this->response );

			// Array response with reject_reason.
			if ( is_array( $body ) && ! empty( $body[0] ) && is_object( $body[0] ) ) {
				if ( ! empty( $body[0]->reject_reason ) ) {
					return $body[0]->reject_reason;
				}

				if ( ! empty( $body[0]->status ) ) {
					return $body[0]->status;
				}
			}

			// Object response with error name/code.
			if ( is_object( $body ) ) {
				if ( ! empty( $body->name ) ) {
					return $body->name;
				}

				if ( ! empty( $body->code ) ) {
					return $body->code;
				}
			}
		}

		return parent::get_response_error_code();
	}

	/**
	 * Get mailer debug information that is helpful during support.
	 *
	 * @since 4.6.0
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
	 * Whether the mailer has all its settings correctly set up and saved.
	 *
	 * This mailer is configured when `api_key` setting is defined.
	 *
	 * @since 4.6.0
	 *
	 * @return bool
	 */
	public function is_mailer_complete() {

		$options = $this->connection_options->get_group( $this->mailer );

		if ( ! empty( $options['api_key'] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Set the return path email.
	 *
	 * @since 4.6.0
	 *
	 * @param string $email The return path email address.
	 */
	public function set_return_path( $email ) {}

	/**
	 * Whether the email was sent.
	 *
	 * @since 4.6.0
	 *
	 * @return bool
	 */
	public function is_email_sent() {

		$is_sent = false;

		if ( wp_remote_retrieve_response_code( $this->response ) === $this->email_sent_code ) {
			$body = isset( $this->response['body'] ) ? $this->response['body'] : '';

			if ( ! empty( $body ) && is_array( $body ) && isset( $body[0]->status ) && in_array( $body[0]->status, [ 'sent', 'queued' ], true ) ) {
				$is_sent = true;
			}
		}

		/**
		 * Filters whether the email is sent or not.
		 *
		 * @since 3.1.0
		 *
		 * @param bool           $is_sent Whether the email is sent or not.
		 * @param MailerAbstract $mailer  Mailer object.
		 */
		return apply_filters( 'wp_mail_smtp_providers_mandrill_mailer_is_email_sent', $is_sent, $this->mailer );
	}
}
