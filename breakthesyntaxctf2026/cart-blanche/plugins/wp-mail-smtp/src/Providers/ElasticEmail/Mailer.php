<?php

namespace WPMailSMTP\Providers\ElasticEmail;

use WPMailSMTP\Helpers\Helpers;
use WPMailSMTP\Providers\MailerAbstract;
use WPMailSMTP\WP;

/**
 * Class Mailer.
 *
 * @since 4.3.0
 */
class Mailer extends MailerAbstract {

	/**
	 * Which response code from HTTP provider is considered to be successful?
	 *
	 * @since 4.3.0
	 *
	 * @var int
	 */
	protected $email_sent_code = 200;


	/**
	 * URL to make an API request to.
	 *
	 * @since 4.3.0
	 *
	 * @var string
	 */
	protected $url = 'https://api.elasticemail.com/v4/emails/transactional';

	/**
	 * Mailer constructor.
	 *
	 * @since 4.3.0
	 *
	 * @param MailCatcherInterface $phpmailer  The MailCatcher object.
	 * @param ConnectionInterface  $connection The Connection object.
	 */
	public function __construct( $phpmailer, $connection = null ) {

		// We want to prefill everything from MailCatcher class, which extends PHPMailer.
		parent::__construct( $phpmailer, $connection );

		// Set mailer specific headers.
		$this->set_header( 'Accept', 'application/json' );
		$this->set_header( 'Content-Type', 'application/json' );
		$this->set_header( 'X-ElasticEmail-ApiKey', $this->connection_options->get( $this->mailer, 'api_key' ) );
	}

	/**
	 * Redefine the way custom headers are processed for this mailer - they should be in body.
	 *
	 * @since 4.3.0
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
	 * @since 4.3.0
	 *
	 * @param string $name  Header name.
	 * @param string $value Header value.
	 */
	public function set_body_header( $name, $value ) {

		$name = sanitize_text_field( $name );

		if ( empty( $name ) ) {
			return;
		}

		$this->set_body_param(
			[
				'Content' => [
					'Headers' => [
						$name => $this->sanitize_header_value( $name, $value ),
					],
				],
			]
		);
	}

	/**
	 * Set the From information for an email.
	 *
	 * @since 4.3.0
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
				'Content' => [
					'From' => $this->address_format( [ $email, $name ] ),
				],
			]
		);
	}

	/**
	 * Set email recipients: to, cc, bcc.
	 *
	 * @since 4.3.0
	 *
	 * @param array $recipients Email recipients.
	 */
	public function set_recipients( $recipients ) { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		if ( empty( $recipients ) ) {
			return;
		}

		// Allow only these recipient types.
		$recipient_mappings = [
			'to'  => 'To',
			'cc'  => 'CC',
			'bcc' => 'BCC',
		];

		$allowed_types = array_keys( $recipient_mappings );
		$data          = [];

		foreach ( $recipients as $type => $emails ) {
			if (
				! in_array( $type, $allowed_types, true ) ||
				empty( $emails ) ||
				! is_array( $emails )
			) {
				continue;
			}

			$field = $recipient_mappings[ $type ];

			// Iterate over all emails for each type.
			// There might be multiple cc/to/bcc emails.
			foreach ( $emails as $email ) {
				if ( ! isset( $email[0] ) || ! filter_var( $email[0], FILTER_VALIDATE_EMAIL ) ) {
					continue;
				}

				$data[ $field ][] = $this->address_format( $email );
			}
		}

		if ( ! empty( $data ) ) {
			$this->set_body_param(
				[
					'Recipients' => $data,
				]
			);
		}
	}

	/**
	 * Set the Reply To information for an email.
	 *
	 * @since 4.3.0
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
					'Content' => [
						'ReplyTo' => $data[0],
					],
				]
			);
		}
	}

	/**
	 * Set email subject.
	 *
	 * @since 4.3.0
	 *
	 * @param string $subject Email subject.
	 */
	public function set_subject( $subject ) {

		$this->set_body_param(
			[
				'Content' => [
					'Subject' => $subject,
				],
			]
		);
	}

	/**
	 * Set email content.
	 *
	 * @since 4.3.0
	 *
	 * @param string|array $content Email content.
	 */
	public function set_content( $content ) {

		if ( empty( $content ) ) {
			return;
		}

		$data = [];

		if ( is_array( $content ) ) {
			if ( ! empty( $content['text'] ) ) {
				$data[] = [
					'ContentType' => 'PlainText',
					'Content'     => $content['text'],
				];
			}

			if ( ! empty( $content['html'] ) ) {
				$data[] = [
					'ContentType' => 'HTML',
					'Content'     => $content['html'],
				];
			}
		} else {
			if ( $this->phpmailer->ContentType === 'text/plain' ) {
				$data[] = [
					'ContentType' => 'PlainText',
					'Content'     => $content,
				];
			} else {
				$data[] = [
					'ContentType' => 'HTML',
					'Content'     => $content,
				];
			}
		}

		$this->set_body_param(
			[
				'Content' => [
					'Body' => $data,
				],
			]
		);
	}

	/**
	 * Set attachments for an email.
	 *
	 * @since 4.3.0
	 *
	 * @param array $attachments Attachments array.
	 */
	public function set_attachments( $attachments ) { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		if ( empty( $attachments ) ) {
			return;
		}

		$data = $this->prepare_attachments( $attachments );

		if ( ! empty( $data ) ) {
			$this->set_body_param(
				[
					'Content' => [
						'Attachments' => $data,
					],
				]
			);
		}
	}

	/**
	 * Prepare attachments data for SendLayer API.
	 *
	 * @since 4.3.0
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
				'Name'          => empty( $attachment[2] ) ? 'file-' . wp_hash( microtime() ) . '.' . $filetype : trim( $attachment[2] ), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
				'BinaryContent' => base64_encode( $file ),
				'ContentType'   => $attachment[4],
			];
		}

		return $data;
	}

	/**
	 * Doesn't support this.
	 * So we do nothing.
	 *
	 * @since 4.3.0
	 *
	 * @param string $email Return Path email address.
	 */
	public function set_return_path( $email ) {}

	/**
	 * Redefine the way email body is returned.
	 * By default, we are sending an array of data.
	 * ElasticEmail requires a JSON, so we encode the body.
	 *
	 * @since 4.3.0
	 */
	public function get_body() {

		$body = parent::get_body();

		return wp_json_encode( $body );
	}

	/**
	 * We might need to do something after the email was sent to the API.
	 * In this method we preprocess the response from the API.
	 *
	 * @since 4.3.0
	 *
	 * @param mixed $response Response data.
	 */
	protected function process_response( $response ) {

		parent::process_response( $response );

		if (
			! is_wp_error( $response ) &&
			! empty( $this->response['body'] ) &&
			! empty( $this->response['body']->TransactionID )
		) {
			$this->phpmailer->addCustomHeader( 'X-Msg-ID', $this->response['body']->TransactionID );
			$this->verify_sent_status = true;
		}
	}

	/**
	 * Whether the email is sent or not.
	 * We check response code and a non-empty `TransactionID` field in the response body.
	 *
	 * @since 4.3.0
	 *
	 * @return bool
	 */
	public function is_email_sent() {

		$is_sent = false;

		if (
			wp_remote_retrieve_response_code( $this->response ) === $this->email_sent_code &&
			! empty( $this->response['body'] ) &&
			! empty( $this->response['body']->TransactionID )
		) {
			$is_sent = true;
		}

		// phpcs:disable WPForms.Comments.Since.MissingPhpDoc, WPForms.PHP.ValidateHooks.InvalidHookName

		/** This filter is documented in src/Providers/MailerAbstract.php. */
		return apply_filters( 'wp_mail_smtp_providers_mailer_is_email_sent', $is_sent, $this->mailer );
		// phpcs:enable WPForms.Comments.Since.MissingPhpDoc, WPForms.PHP.ValidateHooks.InvalidHookName
	}

	/**
	 * Get an Elastic Email specific response with a helpful error.
	 *
	 * @since 4.3.0
	 *
	 * @return string
	 */
	public function get_response_error() { // phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded, Generic.Metrics.CyclomaticComplexity.TooHigh

		$error_text[] = $this->error_message;

		if ( ! empty( $this->response ) ) {
			$body = wp_remote_retrieve_body( $this->response );

			// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			if ( ! empty( $body->Error ) ) {
				$error_text[] = Helpers::format_error_message( $body->Error );
			} else {
				$error_text[] = WP::wp_remote_get_response_error_message( $this->response );
			}
			// phpcs:enable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		}

		return implode( WP::EOL, array_map( 'esc_textarea', array_filter( $error_text ) ) );
	}

	/**
	 * Whether the mailer has all its settings correctly set up and saved.
	 *
	 * @since 4.3.0
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
	 * @since 4.3.0
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
			$result = "{$name} <{$email}>";
		}

		return $result;
	}
}
