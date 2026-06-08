<?php

namespace WPMailSMTP\Providers\Sendlayer;

use WPMailSMTP\ConnectionInterface;
use WPMailSMTP\Helpers\Helpers;
use WPMailSMTP\WP;
use WPMailSMTP\MailCatcherInterface;
use WPMailSMTP\Providers\MailerAbstract;

/**
 * Class Mailer.
 *
 * @since 3.4.0
 */
class Mailer extends MailerAbstract {

	/**
	 * URL to make an API request to.
	 *
	 * @since 3.4.0
	 *
	 * @var string
	 */
	protected $url = 'https://console.sendlayer.com/api/v1/email';

	/**
	 * Mailer constructor.
	 *
	 * @since 3.4.0
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
	 * Redefine the way custom headers are processed for this mailer - they should be in body.
	 *
	 * @since 3.4.0
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
	 * This mailer supports email-related custom headers inside a body of the message.
	 *
	 * @since 3.4.0
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
	 * @since 3.4.0
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
	 * @since 3.4.0
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
	 * @since 3.4.0
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
					'ReplyTo' => $data,
				]
			);
		}
	}

	/**
	 * Set email subject.
	 *
	 * @since 3.4.0
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
	 * @since 3.4.0
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
						'ContentType'  => 'plain',
						'PlainContent' => $content['text'],
					]
				);
			}

			if ( ! empty( $content['html'] ) ) {
				$this->set_body_param(
					[
						'ContentType' => 'html',
						'HTMLContent' => $content['html'],
					]
				);
			}
		} else {
			if ( $this->phpmailer->ContentType === 'text/plain' ) {
				$this->set_body_param(
					[
						'ContentType'  => 'plain',
						'PlainContent' => $content,
					]
				);
			} else {
				$this->set_body_param(
					[
						'ContentType' => 'html',
						'HTMLContent' => $content,
					]
				);
			}
		}
	}

	/**
	 * Set attachments for an email.
	 *
	 * @since 3.4.0
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
	 * Prepare attachments data for SendLayer API.
	 *
	 * @since 3.4.0
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
				'Filename'    => empty( $attachment[2] ) ? 'file-' . wp_hash( microtime() ) . '.' . $filetype : trim( $attachment[2] ),
				// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
				'Content'     => base64_encode( $file ),
				'Type'        => $attachment[4],
				'Disposition' => in_array( $attachment[6], [ 'inline', 'attachment' ], true ) ? $attachment[6] : 'attachment',
				'ContentId'   => empty( $attachment[7] ) ? '' : trim( (string) $attachment[7] ),
			];
		}

		return $data;
	}

	/**
	 * Doesn't support this.
	 * So we do nothing.
	 *
	 * @since 3.4.0
	 *
	 * @param string $email Return Path email address.
	 */
	public function set_return_path( $email ) { }

	/**
	 * Redefine the way email body is returned.
	 * By default, we are sending an array of data.
	 * SendLayer requires a JSON, so we encode the body.
	 *
	 * @since 3.4.0
	 */
	public function get_body() {

		$body = parent::get_body();

		return wp_json_encode( $body );
	}

	/**
	 * We might need to do something after the email was sent to the API.
	 * In this method we preprocess the response from the API.
	 *
	 * @since 3.4.0
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
	 * Get a SendLayer-specific response with a helpful error.
	 *
	 * @since 3.4.0
	 *
	 * @return string
	 */
	public function get_response_error() { // phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded

		$error_text[] = $this->error_message;

		if ( ! empty( $this->response ) ) {
			$body = wp_remote_retrieve_body( $this->response );

			// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			if ( ! empty( $body->Errors ) && is_array( $body->Errors ) ) {
				foreach ( $body->Errors as $error ) {
					if ( ! empty( $error->Message ) ) {
						$message = $error->Message;
						$code    = ! empty( $error->Code ) ? $error->Code : '';

						$error_text[] = Helpers::format_error_message( $message, $code );
					}
				}
			} else {
				$error_text[] = WP::wp_remote_get_response_error_message( $this->response );
			}
			// phpcs:enable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		}

		return implode( WP::EOL, array_map( 'esc_textarea', array_filter( $error_text ) ) );
	}

	/**
	 * Get the error code from the SendLayer API response.
	 *
	 * @since 4.8.0
	 *
	 * @return string
	 */
	public function get_response_error_code() {

		if ( ! empty( $this->response ) ) {
			$body = wp_remote_retrieve_body( $this->response );

			// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			if ( ! empty( $body->Errors ) && is_array( $body->Errors ) && ! empty( $body->Errors[0]->Code ) ) {
				return $body->Errors[0]->Code; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			}
		}

		return parent::get_response_error_code();
	}

	/**
	 * Get mailer debug information, that is helpful during support.
	 *
	 * @since 3.4.0
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
	 * This mailer is configured when `server_api_token` setting is defined.
	 *
	 * @since 3.4.0
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
	 * Prepare address param.
	 *
	 * @since 3.4.0
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
