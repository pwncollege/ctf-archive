<?php

namespace WPMailSMTP\Providers\SMTP2GO;

use WPMailSMTP\Helpers\Helpers;
use WPMailSMTP\Providers\MailerAbstract;
use WPMailSMTP\WP;

/**
 * Class Mailer.
 *
 * @since 4.1.0
 */
class Mailer extends MailerAbstract {

	/**
	 * Which response code from HTTP provider is considered to be successful?
	 *
	 * @since 4.1.0
	 *
	 * @var int
	 */
	protected $email_sent_code = 200;


	/**
	 * URL to make an API request to.
	 *
	 * @since 4.1.0
	 *
	 * @var string
	 */
	protected $url = 'https://api.smtp2go.com/v3/email/send';

	/**
	 * Mailer constructor.
	 *
	 * @since 4.1.0
	 *
	 * @param MailCatcherInterface $phpmailer  The MailCatcher object.
	 * @param ConnectionInterface  $connection The Connection object.
	 */
	public function __construct( $phpmailer, $connection = null ) {

		// We want to prefill everything from MailCatcher class, which extends PHPMailer.
		parent::__construct( $phpmailer, $connection );

		// Set mailer specific headers.
		$this->set_header( 'X-Smtp2go-Api-Key', $this->connection_options->get( $this->mailer, 'api_key' ) );
		$this->set_header( 'Accept', 'application/json' );
		$this->set_header( 'Content-Type', 'application/json' );
	}

	/**
	 * Redefine the way custom headers are processed for this mailer - they should be in body.
	 *
	 * @since 4.1.0
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
	 * @since 4.1.0
	 *
	 * @param string $name  Header name.
	 * @param string $value Header value.
	 */
	public function set_body_header( $name, $value ) {

		$name = sanitize_text_field( $name );

		if ( empty( $name ) ) {
			return;
		}

		$headers = isset( $this->body['custom_headers'] ) ? (array) $this->body['custom_headers'] : [];

		// Index by key to remove duplicates.
		$headers = wp_list_pluck( $headers, 'value', 'header' );

		// Sanitize headers.
		$headers[ $name ] = $this->sanitize_header_value( $name, $value );

		// Turn headers into a list of header => value entries.
		$data = array_reduce(
			array_keys( $headers ),
			function ( $data, $header ) use ( $headers ) {

				$data[] = [
					'header' => $header,
					'value'  => $headers[ $header ],
				];

				return $data;
			},
			[]
		);

		$this->body['custom_headers'] = $data;
	}

	/**
	 * Set the From information for an email.
	 *
	 * @since 4.1.0
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
				'sender' => $this->address_format( [ $email, $name ] ),
			]
		);
	}

	/**
	 * Set email recipients: to, cc, bcc.
	 *
	 * @since 4.1.0
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
	 * @since 4.1.1
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
			$this->set_body_header(
				'Reply-To',
				implode( ',', $data )
			);
		}
	}

	/**
	 * Set email subject.
	 *
	 * @since 4.1.0
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
	 * @since 4.1.0
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
						'text_body' => $content['text'],
					]
				);
			}

			if ( ! empty( $content['html'] ) ) {
				$this->set_body_param(
					[
						'html_body' => $content['html'],
					]
				);
			}
		} else {
			if ( $this->phpmailer->ContentType === 'text/plain' ) {
				$this->set_body_param(
					[
						'text_body' => $content,
					]
				);
			} else {
				$this->set_body_param(
					[
						'html_body' => $content,
					]
				);
			}
		}
	}

	/**
	 * Set attachments for an email.
	 *
	 * @since 4.1.0
	 *
	 * @param array $attachments Attachments array.
	 */
	public function set_attachments( $attachments ) { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		if ( empty( $attachments ) ) {
			return;
		}

		$data = [];

		// Split attachments into "attachments" and "inlines" groups.
		foreach ( $attachments as $attachment ) {
			$mode = in_array( $attachment[6], [ 'inline', 'attachment' ], true ) ? $attachment[6] : 'attachment';

			if ( $mode === 'inline' ) {
				$data['inlines'][] = $attachment;
			} else {
				$data['attachments'][] = $attachment;
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
	 * Doesn't support this.
	 * So we do nothing.
	 *
	 * @since 4.1.0
	 *
	 * @param string $email Return Path email address.
	 */
	public function set_return_path( $email ) {}

	/**
	 * Redefine the way email body is returned.
	 * By default, we are sending an array of data.
	 * SMTP2GO requires a JSON, so we encode the body.
	 *
	 * @since 4.1.0
	 */
	public function get_body() {

		$body = parent::get_body();

		return wp_json_encode( $body );
	}

	/**
	 * Prepare attachments data.
	 *
	 * @since 4.1.0
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
				'filename' => empty( $attachment[2] ) ? 'file-' . wp_hash( microtime() ) . '.' . $filetype : trim( $attachment[2] ),
				// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
				'fileblob' => base64_encode( $file ),
				'mimetype' => $attachment[4],
			];
		}

		return $data;
	}

	/**
	 * We might need to do something after the email was sent to the API.
	 * In this method we preprocess the response from the API.
	 *
	 * @since 4.1.0
	 *
	 * @param mixed $response Response data.
	 */
	protected function process_response( $response ) {

		parent::process_response( $response );

		if (
			! is_wp_error( $response ) &&
			! empty( $this->response['body']->data ) &&
			! empty( $this->response['body']->data->email_id )
		) {
			$this->phpmailer->addCustomHeader( 'X-Msg-ID', $this->response['body']->data->email_id );
			$this->verify_sent_status = true;
		}
	}

	/**
	 * Whether the email is sent or not.
	 * We check response code and a non-empty `email_id` field in the response body.
	 *
	 * @since 4.1.0
	 *
	 * @return bool
	 */
	public function is_email_sent() {

		$is_sent = false;

		if (
			wp_remote_retrieve_response_code( $this->response ) === $this->email_sent_code &&
			! empty( $this->response['body']->data ) &&
			! empty( $this->response['body']->data->email_id )
		) {
			$is_sent = true;
		}

		// phpcs:disable WPForms.Comments.Since.MissingPhpDoc, WPForms.PHP.ValidateHooks.InvalidHookName

		/** This filter is documented in src/Providers/MailerAbstract.php. */
		return apply_filters( 'wp_mail_smtp_providers_mailer_is_email_sent', $is_sent, $this->mailer );
		// phpcs:enable WPForms.Comments.Since.MissingPhpDoc, WPForms.PHP.ValidateHooks.InvalidHookName
	}

	/**
	 * Get a SMTP2GO-specific response with a helpful error.
	 *
	 * @since 4.1.0
	 *
	 * @return string
	 */
	public function get_response_error() { // phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded, Generic.Metrics.CyclomaticComplexity.TooHigh

		$error_text = [
			$this->error_message,
		];

		if ( ! empty( $this->response ) ) {
			$body = wp_remote_retrieve_body( $this->response );

			if ( ! empty( $body->data ) ) {
				if ( ! empty( $body->data->failures ) ) {
					foreach ( $body->data->failures as $error ) {
						$error_text[] = Helpers::format_error_message( $error );
					}
				}

				if ( ! empty( $body->data->error ) ) {
					$error_code = ! empty( $body->data->error_code ) ? $body->data->error_code : '';

					$error_text[] = Helpers::format_error_message( $body->data->error, $error_code );
				}

				if ( ! empty( $body->data->field_validation_errors ) ) {
					$message = '';
					$code    = '';

					if ( ! empty( $body->data->field_validation_errors->message ) ) {
						$message = $body->data->field_validation_errors->message;
					}

					if ( ! empty( $body->data->field_validation_errors->fieldname ) ) {
						$code = $body->data->field_validation_errors->fieldname;
					}

					if ( ! empty( $message ) ) {
						$error_text[] = Helpers::format_error_message( $message, $code );
					}
				}
			} else {
				$error_text[] = WP::wp_remote_get_response_error_message( $this->response );
			}
		}

		return implode( WP::EOL, array_map( 'esc_textarea', array_filter( $error_text ) ) );
	}

	/**
	 * Get the error code from the response.
	 *
	 * @since 4.8.0
	 *
	 * @return string
	 */
	public function get_response_error_code() {

		if ( ! empty( $this->response ) ) {
			$body = wp_remote_retrieve_body( $this->response );

			// API-level error code (non-200 responses).
			if ( ! empty( $body->data->error_code ) ) {
				return $body->data->error_code;
			}
		}

		return parent::get_response_error_code();
	}

	/**
	 * Whether the mailer has all its settings correctly set up and saved.
	 *
	 * @since 4.1.0
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
	 * Prepare address param.
	 *
	 * @since 4.1.0
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
			$result = "\"{$name}\" <{$email}>";
		}

		return $result;
	}
}
