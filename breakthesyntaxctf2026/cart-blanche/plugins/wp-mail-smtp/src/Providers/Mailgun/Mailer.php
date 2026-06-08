<?php

namespace WPMailSMTP\Providers\Mailgun;

use WPMailSMTP\ConnectionInterface;
use WPMailSMTP\Helpers\Helpers;
use WPMailSMTP\MailCatcherInterface;
use WPMailSMTP\Providers\MailerAbstract;
use WPMailSMTP\WP;

/**
 * Class Mailer.
 *
 * @since 1.0.0
 */
class Mailer extends MailerAbstract {

	/**
	 * Which response code from HTTP provider is considered to be successful?
	 *
	 * @since 1.0.0
	 *
	 * @var int
	 */
	protected $email_sent_code = 200;

	/**
	 * API endpoint used for sites from all regions.
	 *
	 * @since 1.4.0
	 *
	 * @var string
	 */
	const API_BASE_US = 'https://api.mailgun.net/v3/';

	/**
	 * API endpoint used for sites from EU region.
	 *
	 * @since 1.4.0
	 *
	 * @var string
	 */
	const API_BASE_EU = 'https://api.eu.mailgun.net/v3/';

	/**
	 * URL to make an API request to.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected $url = '';

	/**
	 * Mailer constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param MailCatcherInterface $phpmailer  The MailCatcher object.
	 * @param ConnectionInterface  $connection The Connection object.
	 */
	public function __construct( $phpmailer, $connection = null ) {

		// Default value should be defined before the parent class contructor fires.
		$this->url = self::API_BASE_US;

		// We want to prefill everything from MailCatcher class, which extends PHPMailer.
		parent::__construct( $phpmailer, $connection );

		// We have a special API URL to query in case of EU region.
		if ( $this->connection_options->get( $this->mailer, 'region' ) === 'EU' ) {
			$this->url = self::API_BASE_EU;
		}

		/*
		 * Append the url with a domain,
		 * to avoid passing the domain name as a query parameter with all requests.
		 */
		$this->url .= sanitize_text_field( $this->connection_options->get( $this->mailer, 'domain' ) . '/messages' );

		$this->set_header( 'Authorization', 'Basic ' . base64_encode( 'api:' . $this->connection_options->get( $this->mailer, 'api_key' ) ) );
	}

	/**
	 * @inheritdoc
	 */
	public function set_from( $email, $name = '' ) {

		if ( ! filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
			return;
		}

		if ( ! empty( $name ) ) {
			$this->set_body_param(
				array(
					'from' => $name . ' <' . $email . '>',
				)
			);
		} else {
			$this->set_body_param(
				array(
					'from' => $email,
				)
			);
		}
	}

	/**
	 * @inheritdoc
	 */
	public function set_recipients( $recipients ) {

		if ( empty( $recipients ) ) {
			return;
		}

		$default = array( 'to', 'cc', 'bcc' );

		foreach ( $recipients as $kind => $emails ) {
			if (
				! in_array( $kind, $default, true ) ||
				empty( $emails ) ||
				! is_array( $emails )
			) {
				continue;
			}

			$data = array();

			foreach ( $emails as $email ) {
				$addr = isset( $email[0] ) ? $email[0] : false;
				$name = isset( $email[1] ) ? $email[1] : false;

				if ( ! filter_var( $addr, FILTER_VALIDATE_EMAIL ) ) {
					continue;
				}

				if ( ! empty( $name ) ) {
					$data[] = $name . ' <' . $addr . '>';
				} else {
					$data[] = $addr;
				}
			}

			if ( ! empty( $data ) ) {
				$this->set_body_param(
					array(
						$kind => implode( ', ', $data ),
					)
				);
			}
		}
	}

	/**
	 * @inheritdoc
	 */
	public function set_content( $content ) {

		if ( is_array( $content ) ) {

			$default = array( 'text', 'html' );

			foreach ( $content as $type => $mail ) {
				if (
					! in_array( $type, $default, true ) ||
					empty( $mail )
				) {
					continue;
				}

				$this->set_body_param(
					array(
						$type => $mail,
					)
				);
			}
		} else {

			$type = 'html';

			if ( $this->phpmailer->ContentType === 'text/plain' ) {
				$type = 'text';
			}

			if ( ! empty( $content ) ) {
				$this->set_body_param(
					array(
						$type => $content,
					)
				);
			}
		}
	}

	/**
	 * Redefine the way custom headers are process for this mailer - they should be in body.
	 *
	 * @since 1.5.0
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
	 * This mailer supports email-related custom headers inside a body of the message with a special prefix "h:".
	 *
	 * @since 1.5.0
	 */
	public function set_body_header( $name, $value ) {

		$name = sanitize_text_field( $name );

		$this->set_body_param(
			array(
				'h:' . $name => $this->sanitize_header_value( $name, $value ),
			)
		);
	}

	/**
	 * It's the last one, so we can modify the whole body.
	 *
	 * @since 1.0.0
	 *
	 * @param array $attachments The array of attachments data.
	 */
	public function set_attachments( $attachments ) { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh, Generic.Metrics.NestingLevel.MaxExceeded

		if ( empty( $attachments ) ) {
			return;
		}

		$payload = '';
		$data    = [];

		foreach ( $attachments as $attachment ) {
			$file = $this->get_attachment_file_content( $attachment );

			if ( $file === false ) {
				continue;
			}

			$data[] = [
				'content' => $file,
				'name'    => $this->get_attachment_file_name( $attachment ),
			];
		}

		if ( ! empty( $data ) ) {

			// First, generate a boundary for the multipart message.
			$boundary = $this->phpmailer->generate_id();

			// Iterate through pre-built params and build a payload.
			foreach ( $this->body as $key => $value ) {
				if ( is_array( $value ) ) {
					foreach ( $value as $child_value ) {
						$payload .= '--' . $boundary;
						$payload .= "\r\n";
						$payload .= 'Content-Disposition: form-data; name="' . $key . "\"\r\n\r\n";
						$payload .= $child_value;
						$payload .= "\r\n";
					}
				} else {
					$payload .= '--' . $boundary;
					$payload .= "\r\n";
					$payload .= 'Content-Disposition: form-data; name="' . $key . '"' . "\r\n\r\n";
					$payload .= $value;
					$payload .= "\r\n";
				}
			}

			// Now iterate through our attachments, and add them too.
			foreach ( $data as $key => $attachment ) {
				$payload .= '--' . $boundary;
				$payload .= "\r\n";
				$payload .= 'Content-Disposition: form-data; name="attachment[' . $key . ']"; filename="' . $attachment['name'] . '"' . "\r\n\r\n";
				$payload .= $attachment['content'];
				$payload .= "\r\n";
			}

			$payload .= '--' . $boundary . '--';

			// Redefine the body the "dirty way".
			$this->body = $payload;

			$this->set_header( 'Content-Type', 'multipart/form-data; boundary=' . $boundary );
		}
	}

	/**
	 * @inheritdoc
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

			$addr = isset( $emails[0] ) ? $emails[0] : false;
			$name = isset( $emails[1] ) ? $emails[1] : false;

			if ( ! filter_var( $addr, FILTER_VALIDATE_EMAIL ) ) {
				continue;
			}

			if ( ! empty( $name ) ) {
				$data[] = $name . ' <' . $addr . '>';
			} else {
				$data[] = $addr;
			}
		}

		if ( ! empty( $data ) ) {
			$this->set_body_param(
				array(
					'h:Reply-To' => implode( ',', $data ),
				)
			);
		}
	}

	/**
	 * @inheritdoc
	 */
	public function set_return_path( $email ) {

		if (
			$this->connection_options->get( 'mail', 'return_path' ) !== true ||
			! filter_var( $email, FILTER_VALIDATE_EMAIL )
		) {
			return;
		}

		$this->set_body_param(
			array(
				'sender' => $email,
			)
		);
	}

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

		if ( is_wp_error( $response ) ) {
			return;
		}

		if ( ! empty( $this->response['body']->id ) ) {
			$this->phpmailer->MessageID = $this->response['body']->id;
			$this->verify_sent_status   = true;
		}
	}

	/**
	 * Whether the email is sent or not.
	 * We basically check the response code from a request to provider.
	 * Might not be 100% correct, not guarantees that email is delivered.
	 *
	 * In Mailgun's case it looks like we have to check if the response body has the message ID.
	 * All successful API responses should have `id` key in the response body.
	 *
	 * @since 2.2.0
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

		/** This filter is documented in src/Providers/MailerAbstract.php. */
		return apply_filters( 'wp_mail_smtp_providers_mailer_is_email_sent', $is_sent, $this->mailer );
	}

	/**
	 * Get a Mailgun-specific response with a helpful error.
	 *
	 * @since 1.2.0
	 *
	 * @return string
	 */
	public function get_response_error() {

		$error_text[] = $this->error_message;

		if ( ! empty( $this->response ) ) {
			$body = wp_remote_retrieve_body( $this->response );

			if ( ! empty( $body->message ) ) {
				$error_text[] = Helpers::format_error_message( $body->message );
			} else {
				$error_text[] = WP::wp_remote_get_response_error_message( $this->response );
			}
		}

		return implode( WP::EOL, array_map( 'esc_textarea', array_filter( $error_text ) ) );
	}

	/**
	 * @inheritdoc
	 */
	public function get_debug_info() {

		$mg_text = array();

		$mailgun = $this->connection_options->get_group( $this->mailer );

		$mg_text[] = '<strong>Api Key / Domain:</strong> ' . ( ! empty( $mailgun['api_key'] ) && ! empty( $mailgun['domain'] ) ? 'Yes' : 'No' );

		return implode( '<br>', $mg_text );
	}

	/**
	 * @inheritdoc
	 */
	public function is_mailer_complete() {

		$options = $this->connection_options->get_group( $this->mailer );

		// API key is the only required option.
		if (
			! empty( $options['api_key'] ) &&
			! empty( $options['domain'] )
		) {
			return true;
		}

		return false;
	}
}
