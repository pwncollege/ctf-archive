<?php

namespace WPMailSMTP\Providers\SparkPost;

use WPMailSMTP\ConnectionInterface;
use WPMailSMTP\Helpers\Helpers;
use WPMailSMTP\WP;
use WPMailSMTP\MailCatcherInterface;
use WPMailSMTP\Providers\MailerAbstract;

/**
 * Class Mailer.
 *
 * @since 3.2.0
 */
class Mailer extends MailerAbstract {

	/**
	 * API endpoint used for sites from all regions.
	 *
	 * @since 3.2.0
	 *
	 * @var string
	 */
	const API_BASE_US = 'https://api.sparkpost.com/api/v1';

	/**
	 * API endpoint used for sites from EU region.
	 *
	 * @since 3.2.0
	 *
	 * @var string
	 */
	const API_BASE_EU = 'https://api.eu.sparkpost.com/api/v1';

	/**
	 * Mailer constructor.
	 *
	 * @since 3.2.0
	 *
	 * @param MailCatcherInterface $phpmailer  The MailCatcher object.
	 * @param ConnectionInterface  $connection The Connection object.
	 */
	public function __construct( $phpmailer, $connection = null ) {

		// Default value should be defined before the parent class constructor fires.
		$this->url = self::API_BASE_US;

		// We want to prefill everything from MailCatcher class, which extends PHPMailer.
		parent::__construct( $phpmailer, $connection );

		// We have a special API URL to query in case of EU region.
		if ( $this->connection_options->get( $this->mailer, 'region' ) === 'EU' ) {
			$this->url = self::API_BASE_EU;
		}

		$this->url .= '/transmissions';

		// Set mailer specific headers.
		$this->set_header( 'Authorization', $this->connection_options->get( $this->mailer, 'api_key' ) );
		$this->set_header( 'Content-Type', 'application/json' );

		// Set default body params.
		$this->set_body_param(
			[
				'options' => [
					'open_tracking'  => false,
					'click_tracking' => false,
					'transactional'  => true,
				],
			]
		);

		/**
		 * Filters return path.
		 *
		 * Email address to use for envelope FROM.
		 * The domain of the return_path address must be a CNAME-verified sending domain.
		 * The local part of the return_path address will be overwritten by SparkPost.
		 *
		 * @since 3.2.0
		 *
		 * @param string  $return_path Email address, by default will be used value configured in SparkPost dashboard.
		 */
		$return_path = apply_filters( 'wp_mail_smtp_providers_sparkpost_mailer_return_path', '' );

		if ( $return_path && filter_var( $return_path, FILTER_VALIDATE_EMAIL ) ) {
			$this->set_body_param(
				[
					'return_path' => $return_path,
				]
			);
		}
	}

	/**
	 * Redefine the way custom headers are processed for this mailer - they should be in body.
	 *
	 * @since 3.2.0
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
	 * @since 3.2.0
	 *
	 * @param string $name  Header name.
	 * @param string $value Header value.
	 */
	public function set_body_header( $name, $value ) {

		$name = sanitize_text_field( $name );

		if ( empty( $name ) ) {
			return;
		}

		$headers = isset( $this->body['content']['headers'] ) ? (array) $this->body['content']['headers'] : [];
		$value   = $this->sanitize_header_value( $name, $value );

		$headers[ $name ] = $value;

		$this->set_body_param(
			[
				'content' => [
					'headers' => $headers,
				],
			]
		);
	}

	/**
	 * Set the From information for an email.
	 *
	 * @since 3.2.0
	 *
	 * @param string $email The sender email address.
	 * @param string $name  The sender name.
	 */
	public function set_from( $email, $name ) {

		if ( ! filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
			return;
		}

		$from['email'] = $email;

		if ( ! empty( $name ) ) {
			$from['name'] = $name;
		}

		$this->set_body_param(
			[
				'content' => [
					'from' => $from,
				],
			]
		);
	}

	/**
	 * Set email recipients: to, cc, bcc.
	 *
	 * @since 3.2.0
	 *
	 * @param array $recipients Email recipients.
	 */
	public function set_recipients( $recipients ) { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		if ( empty( $recipients ) ) {
			return;
		}

		$recipients_to = isset( $recipients['to'] ) && is_array( $recipients['to'] ) ? $recipients['to'] : [];
		$header_to     = implode( ',', array_map( [ $this->phpmailer, 'addrFormat' ], $recipients_to ) );

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

				$data[] = [
					'address' => $this->build_recipient( $email, $header_to ),
				];
			}

			// CC recipients must be also included as header.
			if ( $type === 'cc' ) {
				$this->set_body_header( 'CC', implode( ',', array_map( [ $this->phpmailer, 'addrFormat' ], $emails ) ) );
			}

			if ( ! empty( $data ) ) {
				$this->set_body_param(
					[
						'recipients' => $data,
					]
				);
			}
		}
	}

	/**
	 * Set the Reply To information for an email.
	 *
	 * @since 3.2.0
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
					'content' => [
						'reply_to' => implode( ',', $data ),
					],
				]
			);
		}
	}

	/**
	 * Set email subject.
	 *
	 * @since 3.2.0
	 *
	 * @param string $subject Email subject.
	 */
	public function set_subject( $subject ) {

		$this->set_body_param(
			[
				'content' => [
					'subject' => $subject,
				],
			]
		);
	}

	/**
	 * Set email content.
	 *
	 * @since 3.2.0
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
						'content' => [
							'text' => $content['text'],
						],
					]
				);
			}

			if ( ! empty( $content['html'] ) ) {
				$this->set_body_param(
					[
						'content' => [
							'html' => $content['html'],
						],
					]
				);
			}
		} else {
			if ( $this->phpmailer->ContentType === 'text/plain' ) {
				$this->set_body_param(
					[
						'content' => [
							'text' => $content,
						],
					]
				);
			} else {
				$this->set_body_param(
					[
						'content' => [
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
	 * @since 3.2.0
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
					'content' => [
						'attachments' => $data,
					],
				]
			);
		}
	}

	/**
	 * Prepare attachments data for SparkPost API.
	 *
	 * @since 3.2.0
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
				'name' => $this->get_attachment_file_name( $attachment ),
				'data' => base64_encode( $file ), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
				'type' => $attachment[4],
			];
		}

		return $data;
	}

	/**
	 * Doesn't support this.
	 * Return path can be configured in SparkPost account.
	 *
	 * @since 3.2.0
	 *
	 * @param string $email Return Path email address.
	 */
	public function set_return_path( $email ) { }

	/**
	 * Redefine the way email body is returned.
	 * By default, we are sending an array of data.
	 * SparkPost requires a JSON, so we encode the body.
	 *
	 * @since 3.2.0
	 */
	public function get_body() {

		$body = parent::get_body();

		return wp_json_encode( $body );
	}

	/**
	 * We might need to do something after the email was sent to the API.
	 * In this method we preprocess the response from the API.
	 *
	 * @since 3.2.0
	 *
	 * @param mixed $response Response data.
	 */
	protected function process_response( $response ) {

		parent::process_response( $response );

		if (
			! is_wp_error( $response ) &&
			! empty( $this->response['body']->results->id )
		) {
			$this->phpmailer->addCustomHeader( 'X-Msg-ID', $this->response['body']->results->id );
			$this->verify_sent_status = true;
		}
	}

	/**
	 * Get a SparkPost-specific response with a helpful error.
	 *
	 * @since 3.2.0
	 *
	 * @return string
	 */
	public function get_response_error() { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh, Generic.Metrics.NestingLevel.MaxExceeded

		$error_text[] = $this->error_message;

		if ( ! empty( $this->response ) ) {
			$body = wp_remote_retrieve_body( $this->response );

			if ( ! empty( $body->errors ) && is_array( $body->errors ) ) {
				foreach ( $body->errors as $error ) {
					if ( ! empty( $error->message ) ) {
						$message     = $error->message;
						$code        = ! empty( $error->code ) ? $error->code : '';
						$description = ! empty( $error->description ) ? $error->description : '';

						$error_text[] = Helpers::format_error_message( $message, $code, $description );
					}
				}
			} else {
				$error_text[] = WP::wp_remote_get_response_error_message( $this->response );
			}
		}

		return implode( WP::EOL, array_map( 'esc_textarea', array_filter( $error_text ) ) );
	}

	/**
	 * Get the error code from the SparkPost API response.
	 *
	 * @since 4.8.0
	 *
	 * @return string
	 */
	public function get_response_error_code() {

		if ( ! empty( $this->response ) ) {
			$body = wp_remote_retrieve_body( $this->response );

			if ( ! empty( $body->errors ) && is_array( $body->errors ) && ! empty( $body->errors[0]->code ) ) {
				return $body->errors[0]->code;
			}
		}

		return parent::get_response_error_code();
	}

	/**
	 * Get mailer debug information, that is helpful during support.
	 *
	 * @since 3.2.0
	 *
	 * @return string
	 */
	public function get_debug_info() {

		$options = $this->connection_options->get_group( $this->mailer );

		$text[] = '<strong>' . esc_html__( 'API Key:', 'wp-mail-smtp' ) . '</strong> ' .
							( ! empty( $options['api_key'] ) ? 'Yes' : 'No' );
		$text[] = '<strong>' . esc_html__( 'Region:', 'wp-mail-smtp' ) . '</strong> ' .
							( ! empty( $options['region'] ) ? 'Yes' : 'No' );

		return implode( '<br>', $text );
	}

	/**
	 * Whether the mailer has all its settings correctly set up and saved.
	 *
	 * This mailer is configured when `api_key` setting is defined.
	 *
	 * @since 3.2.0
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
	 * Build recipient array.
	 *
	 * @since 3.2.0
	 *
	 * @param array  $address   Email address array.
	 * @param string $header_to Email recipients To header.
	 *
	 * @return array
	 */
	private function build_recipient( $address, $header_to ) {

		$holder = [];

		$holder['email'] = $address[0];

		if ( ! empty( $address[1] ) ) {
			$holder['name'] = $address[1];
		}

		if ( ! empty( $header_to ) ) {
			$holder['header_to'] = $header_to;
			unset( $holder['name'] );
		}

		return $holder;
	}
}
