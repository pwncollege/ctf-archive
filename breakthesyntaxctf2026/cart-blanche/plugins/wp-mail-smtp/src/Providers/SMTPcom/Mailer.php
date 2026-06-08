<?php

namespace WPMailSMTP\Providers\SMTPcom;

use WPMailSMTP\ConnectionInterface;
use WPMailSMTP\Helpers\Helpers;
use WPMailSMTP\MailCatcherInterface;
use WPMailSMTP\Providers\MailerAbstract;
use WPMailSMTP\WP;

/**
 * Class Mailer for SMTP.com integration.
 *
 * @see https://www.smtp.com/smtp-api-documentation/ for the API documentation.
 *
 * @since 2.0.0
 */
class Mailer extends MailerAbstract {

	/**
	 * Which response code from HTTP provider is considered to be successful?
	 *
	 * @since 2.0.0
	 *
	 * @var int
	 */
	protected $email_sent_code = 200;

	/**
	 * URL to make an API request to.
	 *
	 * @since 2.0.0
	 *
	 * @var string
	 */
	protected $url = 'https://api.smtp.com/v4/messages';

	/**
	 * Mailer constructor.
	 *
	 * @since 2.0.0
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
		$this->set_header( 'content-type', 'application/json' );

		// Set mailer specific body parameters.
		$this->set_body_param(
			array(
				'channel' => $this->connection_options->get( $this->mailer, 'channel' ),
			)
		);
	}

	/**
	 * Redefine the way email body is returned.
	 * By default we are sending an array of data.
	 * SMTP.com requires a JSON, so we encode the body.
	 *
	 * @since 2.0.0
	 */
	public function get_body() {

		$body = parent::get_body();

		return wp_json_encode( $body );
	}

	/**
	 * Define the FROM (name and email).
	 *
	 * @since 2.0.0
	 *
	 * @param string $email From Email address.
	 * @param string $name  From Name.
	 */
	public function set_from( $email, $name = '' ) {

		if ( ! filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
			return;
		}

		$from['address'] = $email;

		if ( ! empty( $name ) ) {
			$from['name'] = $name;
		}

		$this->set_body_param(
			array(
				'originator' => array(
					'from' => $from,
				),
			)
		);
	}

	/**
	 * Define the CC/BCC/TO (with names and emails).
	 *
	 * @since 2.0.0
	 *
	 * @param array $recipients
	 */
	public function set_recipients( $recipients ) {

		if ( empty( $recipients ) ) {
			return;
		}

		// Allow only these recipient types.
		$allowed_types = array( 'to', 'cc', 'bcc' );
		$data          = array();

		foreach ( $recipients as $type => $emails ) {
			if (
				! in_array( $type, $allowed_types, true ) ||
				empty( $emails ) ||
				! is_array( $emails )
			) {
				continue;
			}

			$data[ $type ] = array();

			// Iterate over all emails for each type.
			// There might be multiple cc/to/bcc emails.
			foreach ( $emails as $email ) {
				$holder  = array();
				$address = isset( $email[0] ) ? $email[0] : false;
				$name    = isset( $email[1] ) ? $email[1] : false;

				if ( ! filter_var( $address, FILTER_VALIDATE_EMAIL ) ) {
					continue;
				}

				$holder['address'] = $address;
				if ( ! empty( $name ) ) {
					$holder['name'] = $name;
				}

				array_push( $data[ $type ], $holder );
			}
		}

		if ( ! empty( $data ) ) {
			$this->set_body_param(
				array(
					'recipients' => $data,
				)
			);
		}
	}

	/**
	 * Set the email content.
	 *
	 * @since 2.0.0
	 *
	 * @param array|string $content String when text/plain, array otherwise.
	 */
	public function set_content( $content ) { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		if ( empty( $content ) ) {
			return;
		}

		$parts = [];

		if ( is_array( $content ) ) {
			$allowed = [ 'text', 'html' ];

			foreach ( $content as $type => $body ) {
				if (
					! in_array( $type, $allowed, true ) ||
					empty( $body )
				) {
					continue;
				}

				$content_type  = 'text/plain';
				$content_value = $body;

				if ( $type === 'html' ) {
					$content_type = 'text/html';
				}

				$parts[] = [
					'type'    => $content_type,
					'content' => $content_value,
					'charset' => $this->phpmailer->CharSet,
				];
			}
		} else {
			$content_type  = 'text/html';
			$content_value = $content;

			if ( $this->phpmailer->ContentType === 'text/plain' ) {
				$content_type = 'text/plain';
			}

			$parts[] = [
				'type'    => $content_type,
				'content' => $content_value,
				'charset' => $this->phpmailer->CharSet,
			];
		}

		$this->set_body_param(
			[
				'body' => [
					'parts' => $parts,
				],
			]
		);
	}

	/**
	 * Redefine the way custom headers are processed for this mailer - they should be in body.
	 *
	 * @since 2.0.0
	 *
	 * @param array $headers
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
	 * @since 2.0.0
	 *
	 * @param string $name
	 * @param string $value
	 */
	public function set_body_header( $name, $value ) {

		$name = sanitize_text_field( $name );
		if ( empty( $name ) ) {
			return;
		}

		$headers = isset( $this->body['custom_headers'] ) ? (array) $this->body['custom_headers'] : array();

		$headers[ $name ] = $this->sanitize_header_value( $name, $value );

		$this->set_body_param(
			array(
				'custom_headers' => $headers,
			)
		);
	}

	/**
	 * SMTP.com accepts an array of attachments in body.attachments section of the JSON payload.
	 *
	 * @since 2.0.0
	 *
	 * @param array $attachments The array of attachments data.
	 */
	public function set_attachments( $attachments ) {

		if ( empty( $attachments ) ) {
			return;
		}

		$data = [];

		foreach ( $attachments as $attachment ) {
			$file = $this->get_attachment_file_content( $attachment );

			if ( $file === false ) {
				continue;
			}

			$filetype = str_replace( ';', '', trim( $attachment[4] ) );

			$data[] = [
				'content'     => chunk_split( base64_encode( $file ) ), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
				'type'        => $filetype,
				'encoding'    => 'base64',
				'filename'    => $this->get_attachment_file_name( $attachment ),
				'disposition' => in_array( $attachment[6], [ 'inline', 'attachment' ], true ) ? $attachment[6] : 'attachment', // either inline or attachment.
				'cid'         => empty( $attachment[7] ) ? '' : trim( (string) $attachment[7] ),
			];
		}

		if ( ! empty( $data ) ) {
			$this->set_body_param(
				[
					'body' => [
						'attachments' => $data,
					],
				]
			);
		}
	}

	/**
	 * Set Reply-To part of the message.
	 *
	 * @since 2.0.0
	 *
	 * @param array $reply_to
	 */
	public function set_reply_to( $reply_to ) {

		if ( empty( $reply_to ) ) {
			return;
		}

		$data = array();

		foreach ( $reply_to as $key => $emails ) {
			if (
				empty( $emails ) ||
				! is_array( $emails )
			) {
				continue;
			}

			$address = isset( $emails[0] ) ? $emails[0] : false;
			$name    = isset( $emails[1] ) ? $emails[1] : false;

			if ( ! filter_var( $address, FILTER_VALIDATE_EMAIL ) ) {
				continue;
			}

			$data['address'] = $address;
			if ( ! empty( $name ) ) {
				$data['name'] = $name;
			}

			// Let the first valid email from the passed $reply_to serve as the reply_to parameter in STMP.com API.
			// Only one email address and name is allowed in the `reply_to` parameter in the SMTP.com API payload.
			break;
		}

		if ( ! empty( $data ) ) {
			$this->set_body_param(
				array(
					'originator' => array(
						'reply_to' => $data,
					),
				)
			);
		}
	}

	/**
	 * SMTP.com doesn't support return_path params.
	 * So we do nothing.
	 *
	 * @since 2.0.0
	 *
	 * @param string $from_email
	 */
	public function set_return_path( $from_email ) {}

	/**
	 * We might need to do something after the email was sent to the API.
	 * In this method we preprocess the response from the API.
	 *
	 * @since 2.5.0
	 *
	 * @param mixed $response Response data.
	 */
	protected function process_response( $response ) {

		parent::process_response( $response );

		if (
			! is_wp_error( $response ) &&
			! empty( $this->response['body']->data->message )
		) {
			preg_match( '/msg_id: (.*)/', $this->response['body']->data->message, $output );

			if ( ! empty( $output[1] ) ) {
				$this->phpmailer->addCustomHeader( 'X-Msg-ID', $output[1] );
				$this->verify_sent_status = true;
			}
		}
	}

	/**
	 * Get a SMTP.com-specific response with a helpful error.
	 *
	 * SMTP.com API error response (non 200 error code responses) is:
	 * {
	 *   "status": "fail",
	 *   "data": {
	 *     "error_key": "short error message",
	 *   }
	 * }
	 *
	 * It's good to combine the error_key and the message together for the best error explanation.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_response_error() {

		$error_text[] = $this->error_message;

		if ( ! empty( $this->response ) ) {
			$body = wp_remote_retrieve_body( $this->response );

			if ( ! empty( $body->data ) ) {
				foreach ( (array) $body->data as $error_key => $error_message ) {
					$error_text[] = Helpers::format_error_message( $error_message, $error_key );
				}
			} else {
				$error_text[] = WP::wp_remote_get_response_error_message( $this->response );
			}
		}

		return implode( WP::EOL, array_map( 'esc_textarea', array_filter( $error_text ) ) );
	}

	/**
	 * Get the error code from the SMTP.com API response.
	 *
	 * @since 4.8.0
	 *
	 * @return string
	 */
	public function get_response_error_code() {

		if ( ! empty( $this->response ) ) {
			$body = wp_remote_retrieve_body( $this->response );

			// Only extract error keys when the API reports failure.
			if ( ! empty( $body->status ) && $body->status === 'fail' && ! empty( $body->data ) ) {
				$keys = array_keys( (array) $body->data );

				if ( ! empty( $keys[0] ) ) {
					return $keys[0];
				}
			}
		}

		return parent::get_response_error_code();
	}

	/**
	 * Get mailer debug information, that is helpful during support.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_debug_info() {

		$options = $this->connection_options->get_group( $this->mailer );

		$text[] = '<strong>' . esc_html__( 'Api Key:', 'wp-mail-smtp' ) . '</strong> ' .
							( ! empty( $options['api_key'] ) ? 'Yes' : 'No' );
		$text[] = '<strong>' . esc_html__( 'Channel:', 'wp-mail-smtp' ) . '</strong> ' .
							( ! empty( $options['channel'] ) ? 'Yes' : 'No' );

		return implode( '<br>', $text );
	}

	/**
	 * Whether the mailer has all its settings correctly set up and saved.
	 *
	 * This mailer is configured when `api_key` and `channel` settings are defined.
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	public function is_mailer_complete() {

		$options = $this->connection_options->get_group( $this->mailer );

		if ( ! empty( $options['api_key'] ) && ! empty( $options['channel'] ) ) {
			return true;
		}

		return false;
	}
}
