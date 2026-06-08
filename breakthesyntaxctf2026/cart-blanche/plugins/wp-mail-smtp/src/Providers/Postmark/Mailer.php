<?php

namespace WPMailSMTP\Providers\Postmark;

use WPMailSMTP\ConnectionInterface;
use WPMailSMTP\Helpers\Helpers;
use WPMailSMTP\WP;
use WPMailSMTP\MailCatcherInterface;
use WPMailSMTP\Providers\MailerAbstract;

/**
 * Class Mailer.
 *
 * @since 3.1.0
 */
class Mailer extends MailerAbstract {

	/**
	 * URL to make an API request to.
	 *
	 * @since 3.1.0
	 *
	 * @var string
	 */
	protected $url = 'https://api.postmarkapp.com/email';

	/**
	 * Mailer constructor.
	 *
	 * @since 3.1.0
	 *
	 * @param MailCatcherInterface $phpmailer  The MailCatcher object.
	 * @param ConnectionInterface  $connection The Connection object.
	 */
	public function __construct( $phpmailer, $connection = null ) {

		// We want to prefill everything from MailCatcher class, which extends PHPMailer.
		parent::__construct( $phpmailer, $connection );

		// Set mailer specific headers.
		$this->set_header( 'X-Postmark-Server-Token', $this->connection_options->get( $this->mailer, 'server_api_token' ) );
		$this->set_header( 'Accept', 'application/json' );
		$this->set_header( 'Content-Type', 'application/json' );

		// Set mailer specific body parameters.
		$message_stream = $this->get_message_stream();

		if ( ! empty( $message_stream ) ) {
			$this->set_body_param(
				[
					'MessageStream' => $message_stream,
				]
			);
		}
	}

	/**
	 * Redefine the way custom headers are processed for this mailer - they should be in body.
	 *
	 * @since 3.1.0
	 *
	 * @param array $headers Headers array.
	 */
	public function set_headers( $headers ) {

		foreach ( $headers as $header ) {
			$name  = isset( $header[0] ) ? $header[0] : false;
			$value = isset( $header[1] ) ? $header[1] : false;

			$this->set_body_header( $name, $value );
		}

		// Add custom PHPMailer-specific header.
		$this->set_body_header( 'X-Mailer', 'WPMailSMTP/Mailer/' . $this->mailer . ' ' . WPMS_PLUGIN_VER );
		$this->set_body_header( 'Message-ID', $this->phpmailer->getLastMessageID() );
	}

	/**
	 * This mailer supports email-related custom headers inside a body of the message.
	 *
	 * @since 3.1.0
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
		$value   = $this->sanitize_header_value( $name, $value );

		// Prevent duplicates.
		$key = array_search( $name, array_column( $headers, 'Name' ), true );

		if ( $key !== false ) {
			unset( $headers[ $key ] );
		}

		$headers[] = [
			'Name'  => $name,
			'Value' => $value,
		];

		$this->body['Headers'] = array_values( $headers );
	}

	/**
	 * Set the From information for an email.
	 *
	 * @since 3.1.0
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
				'From' => $this->phpmailer->addrFormat( [ $email, $name ] ),
			]
		);
	}

	/**
	 * Set email recipients: to, cc, bcc.
	 *
	 * @since 3.1.0
	 *
	 * @param array $recipients Email recipients.
	 */
	public function set_recipients( $recipients ) { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		if ( empty( $recipients ) ) {
			return;
		}

		$default = [ 'to', 'cc', 'bcc' ];

		foreach ( $recipients as $type => $emails ) {
			if (
				! in_array( $type, $default, true ) ||
				empty( $emails ) ||
				! is_array( $emails )
			) {
				continue;
			}

			$data = [];

			foreach ( $emails as $email ) {
				$addr = isset( $email[0] ) ? $email[0] : false;

				if ( ! filter_var( $addr, FILTER_VALIDATE_EMAIL ) ) {
					continue;
				}

				$data[] = $this->phpmailer->addrFormat( $email );
			}

			if ( ! empty( $data ) ) {
				$this->set_body_param(
					[
						ucfirst( $type ) => implode( ',', $data ),
					]
				);
			}
		}
	}

	/**
	 * Set the Reply To information for an email.
	 *
	 * @since 3.1.0
	 *
	 * @param array $emails Reply To email addresses.
	 */
	public function set_reply_to( $emails ) {

		if ( empty( $emails ) ) {
			return;
		}

		$data = [];

		foreach ( $emails as $email ) {
			$addr = isset( $email[0] ) ? $email[0] : false;

			if ( ! filter_var( $addr, FILTER_VALIDATE_EMAIL ) ) {
				continue;
			}

			$data[] = $this->phpmailer->addrFormat( $email );
		}

		if ( ! empty( $data ) ) {
			$this->set_body_param(
				[
					'ReplyTo' => implode( ',', $data ),
				]
			);
		}
	}

	/**
	 * Set email subject.
	 *
	 * @since 3.1.0
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
	 * @since 3.1.0
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
						'TextBody' => $content['text'],
					]
				);
			}

			if ( ! empty( $content['html'] ) ) {
				$this->set_body_param(
					[
						'HtmlBody' => $content['html'],
					]
				);
			}
		} else {
			if ( $this->phpmailer->ContentType === 'text/plain' ) {
				$this->set_body_param(
					[
						'TextBody' => $content,
					]
				);
			} else {
				$this->set_body_param(
					[
						'HtmlBody' => $content,
					]
				);
			}
		}
	}

	/**
	 * Set attachments for an email.
	 *
	 * @since 3.1.0
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
					'Attachments' => $data,
				]
			);
		}
	}

	/**
	 * Prepare attachments data for Postmark API.
	 *
	 * @since 3.1.0
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

			$data[] = [
				'Name'        => $this->get_attachment_file_name( $attachment ),
				'Content'     => base64_encode( $file ), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
				'ContentType' => $attachment[4],
			];
		}

		return $data;
	}

	/**
	 * Doesn't support this.
	 * Return path can be configured in Postmark account.
	 *
	 * @since 3.1.0
	 *
	 * @param string $email Return Path email address.
	 */
	public function set_return_path( $email ) { }

	/**
	 * Redefine the way email body is returned.
	 * By default, we are sending an array of data.
	 * Postmark requires a JSON, so we encode the body.
	 *
	 * @since 3.1.0
	 */
	public function get_body() {

		$body = parent::get_body();

		return wp_json_encode( $body );
	}

	/**
	 * We might need to do something after the email was sent to the API.
	 * In this method we preprocess the response from the API.
	 *
	 * @since 3.1.0
	 *
	 * @param mixed $response Response data.
	 */
	protected function process_response( $response ) {

		parent::process_response( $response );

		if (
			! is_wp_error( $response ) &&
			! empty( $this->response['body']->MessageID )
		) {
			$this->phpmailer->addCustomHeader( 'X-Msg-ID', $this->response['body']->MessageID );
			$this->verify_sent_status = true;
		}
	}

	/**
	 * Get the error code from the Postmark API response.
	 *
	 * @since 4.8.0
	 *
	 * @return string
	 */
	public function get_response_error_code() {

		if ( ! empty( $this->response ) ) {
			$body = wp_remote_retrieve_body( $this->response );

			// phpcs:ignore WordPress.NamingConventions.ValidVariableName
			if ( ! empty( $body->ErrorCode ) ) {
				// phpcs:ignore WordPress.NamingConventions.ValidVariableName
				return $body->ErrorCode;
			}
		}

		return parent::get_response_error_code();
	}

	/**
	 * Get a Postmark-specific response with a helpful error.
	 *
	 * @since 3.1.0
	 *
	 * @return string
	 */
	public function get_response_error() {

		$error_text[] = $this->error_message;

		if ( ! empty( $this->response ) ) {
			$body = wp_remote_retrieve_body( $this->response );

			// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			if ( ! empty( $body->Message ) ) {
				$message = $body->Message;
				$code    = ! empty( $body->ErrorCode ) ? $body->ErrorCode : '';

				$error_text[] = Helpers::format_error_message( $message, $code );
			} else {
				$error_text[] = WP::wp_remote_get_response_error_message( $this->response );
			}
			// phpcs:enable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		}

		return implode( WP::EOL, array_map( 'esc_textarea', array_filter( $error_text ) ) );
	}

	/**
	 * Get mailer debug information, that is helpful during support.
	 *
	 * @since 3.1.0
	 *
	 * @return string
	 */
	public function get_debug_info() {

		$options = $this->connection_options->get_group( $this->mailer );

		$text[] = '<strong>' . esc_html__( 'Server API Token:', 'wp-mail-smtp' ) . '</strong> ' .
							( ! empty( $options['server_api_token'] ) ? 'Yes' : 'No' );
		$text[] = '<strong>' . esc_html__( 'Message Stream ID:', 'wp-mail-smtp' ) . '</strong> ' .
							( ! empty( $this->get_message_stream() ) ? esc_html( $this->get_message_stream() ) : 'No' );

		return implode( '<br>', $text );
	}

	/**
	 * Get the Message Stream ID.
	 *
	 * @since 3.1.0
	 *
	 * @link https://postmarkapp.com/message-streams
	 *
	 * @return string
	 */
	private function get_message_stream() {

		$message_stream = $this->connection_options->get( $this->mailer, 'message_stream' );

		/**
		 * Filters Message Stream ID.
		 *
		 * @since 3.1.0
		 *
		 * @link https://postmarkapp.com/message-streams
		 *
		 * @param string $message_stream Message Stream ID.
		 */
		return apply_filters( 'wp_mail_smtp_providers_postmark_mailer_get_message_stream', $message_stream );
	}

	/**
	 * Whether the mailer has all its settings correctly set up and saved.
	 *
	 * This mailer is configured when `server_api_token` setting is defined.
	 *
	 * @since 3.1.0
	 *
	 * @return bool
	 */
	public function is_mailer_complete() {

		$options = $this->connection_options->get_group( $this->mailer );

		if ( ! empty( $options['server_api_token'] ) ) {
			return true;
		}

		return false;
	}
}
