<?php

namespace WPMailSMTP\Providers\PepipostAPI;

use WPMailSMTP\MailCatcherInterface;
use WPMailSMTP\Options as PluginOptions;
use WPMailSMTP\Providers\MailerAbstract;
use WPMailSMTP\WP;

/**
 * Pepipost API mailer.
 *
 * @since 1.8.0 Pepipost - SendGrid migration API.
 * @since 2.2.0 Rewrote this class to use native Pepipost API.
 */
class Mailer extends MailerAbstract {

	/**
	 * Which response code from HTTP provider is considered to be successful?
	 *
	 * @since 1.8.0
	 *
	 * @var int
	 */
	protected $email_sent_code = 202;

	/**
	 * URL to make an API request to.
	 *
	 * @since 1.8.0
	 * @since 2.2.0 Changed the API url to Pepipost API v5.
	 *
	 * @var string
	 */
	protected $url = 'https://api.pepipost.com/v5/mail/send';

	/**
	 * Mailer constructor.
	 *
	 * @since 1.8.0
	 * @since 2.2.0 Changed the API key header (API v5 changes).
	 *
	 * @param MailCatcherInterface $phpmailer The MailCatcher instance.
	 */
	public function __construct( $phpmailer ) {

		// We want to prefill everything from MailCatcher class, which extends PHPMailer.
		parent::__construct( $phpmailer );

		$this->set_header( 'api_key', $this->options->get( $this->mailer, 'api_key' ) );
		$this->set_header( 'content-type', 'application/json' );
	}

	/**
	 * Redefine the way email body is returned.
	 * By default we are sending an array of data.
	 * Pepipost requires a JSON, so we encode the body.
	 *
	 * @since 1.8.0
	 *
	 * @return string
	 */
	public function get_body() {

		$body = parent::get_body();

		return wp_json_encode( $body );
	}

	/**
	 * Set the FROM header of the email.
	 *
	 * @since 1.8.0
	 * @since 2.2.0 Changed the attribute names (API v5 changes).
	 *
	 * @param string $email From mail.
	 * @param string $name  From name.
	 */
	public function set_from( $email, $name = '' ) {

		if ( ! filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
			return;
		}

		$from['email'] = $email;

		if ( ! empty( $name ) ) {
			$from['name'] = $name;
		}

		$this->set_body_param(
			[
				'from' => $from,
			]
		);
	}

	/**
	 * Set the names/emails of people who will receive the email.
	 *
	 * @since 1.8.0
	 * @since 2.2.0 change the attribute names (API v5 changes).
	 *
	 * @param array $recipients List of recipients: cc/bcc/to.
	 */
	public function set_recipients( $recipients ) {

		if ( empty( $recipients ) ) {
			return;
		}

		$data = [];

		if ( ! empty( $recipients['to'] ) ) {
			$data['to'] = $this->prepare_list_of_to_emails( $recipients['to'] );
		}

		if ( ! empty( $recipients['cc'] ) ) {
			$data['cc'] = $this->prepare_list_of_emails( $recipients['cc'] );
		}

		if ( ! empty( $recipients['bcc'] ) ) {
			$data['bcc'] = $this->prepare_list_of_emails( $recipients['bcc'] );
		}

		$this->set_body_personalizations( $data );
	}

	/**
	 * Set the email content.
	 * Pepipost API only supports HTML emails, so we have to replace new lines in plain text emails with <br>.
	 *
	 * @since 1.8.0
	 * @since 2.2.0 Change the way the content is prepared (API v5 changes).
	 *
	 * @param array|string $content Email content.
	 */
	public function set_content( $content ) {

		if ( empty( $content ) ) {
			return;
		}

		$html = '';

		if ( ! is_array( $content ) ) {
			$html = $content;

			if ( $this->phpmailer->ContentType === 'text/plain' ) {
				$html = nl2br( $html );
			}
		} else {

			if ( ! empty( $content['html'] ) ) {
				$html = $content['html'];
			} elseif ( ! empty( $content['text'] ) ) {
				$html = nl2br( $content['text'] );
			}
		}

		$this->set_body_param(
			[
				'content' => [
					[
						'type'  => 'html',
						'value' => $html,
					],
				],
			]
		);
	}

	/**
	 * Redefine the way custom headers are processed for this mailer - they should be in body (personalizations).
	 *
	 * @since 1.8.0
	 * @since 2.2.0 Change the way the headers are processed (API v5 changes).
	 *
	 * @param array $headers The email headers to be applied.
	 */
	public function set_headers( $headers ) {

		$valid_headers = [];

		foreach ( $headers as $header ) {
			$name  = isset( $header[0] ) ? $header[0] : false;
			$value = isset( $header[1] ) ? $header[1] : false;

			$valid_headers[ $name ] = WP::sanitize_value( $value );
		}

		// Add custom PHPMailer-specific header.
		$valid_headers['X-Mailer'] = WP::sanitize_value( 'WPMailSMTP/Mailer/' . $this->mailer . ' ' . WPMS_PLUGIN_VER );

		if ( ! empty( $valid_headers ) ) {
			$this->set_body_personalizations( [ 'headers' => $valid_headers ] );
		}
	}

	/**
	 * Pepipost API accepts an array of files content in body, so we will include all files and send.
	 * Doesn't handle exceeding the limits etc, as this will be reported by the API response.
	 *
	 * @since 1.8.0
	 * @since 2.2.0 Change the way the attachments are processed (API v5 changes).
	 *
	 * @param array $attachments The list of attachments data.
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
	 * Prepare the attachments data for Pepipost API.
	 *
	 * @since 2.2.0
	 *
	 * @param array $attachments Array of attachments.
	 *
	 * @return array
	 */
	protected function prepare_attachments( $attachments ) {

		$data = [];

		foreach ( $attachments as $attachment ) {
			$file = false;

			/*
			 * We are not using WP_Filesystem API as we can't reliably work with it.
			 * It is not always available, same as credentials for FTP.
			 */
			try {
				if ( is_file( $attachment[0] ) && is_readable( $attachment[0] ) ) {
					$file = file_get_contents( $attachment[0] );
				}
			} catch ( \Exception $e ) {
				$file = false;
			}

			if ( $file === false ) {
				continue;
			}

			$data[] = [
				'content' => base64_encode( $file ), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
				'name'    => $attachment[2],
			];
		}

		return $data;
	}

	/**
	 * Set the reply-to property of the email.
	 * Pepipost API only supports one reply_to email, so we take the first one and discard the rest.
	 *
	 * @since 1.8.0
	 * @since 2.2.0 Change the way the reply_to is processed (API v5 changes).
	 *
	 * @param array $reply_to Name/email for reply-to feature.
	 */
	public function set_reply_to( $reply_to ) {

		if ( empty( $reply_to ) ) {
			return;
		}

		$email_array = array_shift( $reply_to );

		if ( empty( $email_array[0] ) ) {
			return;
		}

		$email = $email_array[0];

		if ( ! filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
			return;
		}

		if ( ! empty( $email ) ) {
			$this->set_body_param(
				[
					'reply_to' => $email,
				]
			);
		}
	}

	/**
	 * Pepipost API doesn't support sender or return_path params.
	 * So we do nothing.
	 *
	 * @since 1.8.0
	 *
	 * @param string $from_email The from email address.
	 */
	public function set_return_path( $from_email ) {}

	/**
	 * Get a Pepipost-specific response with a helpful error.
	 *
	 * @see https://developers.pepipost.com/email-api/email-api/sendemail#responses
	 *
	 * @since 1.8.0
	 * @since 2.2.0 Change the way the response error message is processed (API v5 changes).
	 *
	 * @return string
	 */
	public function get_response_error() { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.MaxExceeded

		$body = (array) wp_remote_retrieve_body( $this->response );

		$error   = ! empty( $body['error'] ) ? $body['error'] : '';
		$info    = ! empty( $body['info'] ) ? $body['info'] : '';
		$message = '';

		if ( ! empty( $this->error_message ) ) {
			$message = $this->error_message;
		} elseif ( is_string( $error ) ) {
			$message = $error . ( ( ! empty( $info ) ) ? ' - ' . $info : '' );
		} elseif ( is_array( $error ) ) {
			$message = '';

			foreach ( $error as $item ) {
				$message .= sprintf(
					'%1$s (%2$s - %3$s)',
					! empty( $item->description ) ? $item->description : esc_html__( 'General error', 'wp-mail-smtp' ),
					! empty( $item->message ) ? $item->message : esc_html__( 'Error', 'wp-mail-smtp' ),
					! empty( $item->field ) ? $item->field : ''
				) . PHP_EOL;
			}
		}

		return $message;
	}

	/**
	 * Get mailer debug information, that is helpful during support.
	 *
	 * @since 1.8.0
	 *
	 * @return string
	 */
	public function get_debug_info() {

		$sendgrid_text[] = '<strong>Api Key:</strong> ' . ( $this->is_mailer_complete() ? 'Yes' : 'No' );

		return implode( '<br>', $sendgrid_text );
	}

	/**
	 * Whether the mailer has all its settings correctly set up and saved.
	 *
	 * @since 1.8.0
	 *
	 * @return bool
	 */
	public function is_mailer_complete() {

		$options = $this->options->get_group( $this->mailer );

		// API key is the only required option.
		if ( ! empty( $options['api_key'] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * A special set method for Pepipost API "personalizations" attribute.
	 * We are sending one email at a time, so we should set just the first
	 * personalization item.
	 *
	 * Mainly used in set_headers and set_recipients.
	 *
	 * @see https://developers.pepipost.com/email-api/email-api/sendemail
	 *
	 * @since 2.2.0
	 *
	 * @param array $data The personalizations array of data (array of arrays).
	 */
	private function set_body_personalizations( $data ) {

		if ( empty( $data ) ) {
			return;
		}

		if ( ! empty( $this->body['personalizations'][0] ) ) {
			$this->body['personalizations'][0] = PluginOptions::array_merge_recursive(
				$this->body['personalizations'][0],
				$data
			);
		} else {
			$this->set_body_param(
				[
					'personalizations' => [
						$data,
					],
				]
			);
		}
	}

	/**
	 * Prepare list of emails by filtering valid emails first.
	 *
	 * @since 2.2.0
	 *
	 * @param array $items A 2D array of email and name pair items (0 = email, 1 = name).
	 *
	 * @return array 2D array with 'email' keys.
	 */
	private function prepare_list_of_emails( $items ) {

		$valid_emails = array_filter(
			array_column( $items, 0 ),
			function ( $email ) {
				return filter_var( $email, FILTER_VALIDATE_EMAIL );
			}
		);

		return array_map(
			function( $email ) {
				return [ 'email' => $email ];
			},
			$valid_emails
		);
	}

	/**
	 * Prepare list of TO emails by filtering valid emails first
	 * and returning array of arrays (email, name).
	 *
	 * @since 2.2.0
	 *
	 * @param array $items A 2D array of email and name pair items (0 = email, 1 = name).
	 *
	 * @return array 2D array with 'email' and optional 'name' attributes.
	 */
	private function prepare_list_of_to_emails( $items ) {

		$data = [];

		foreach ( $items as $item ) {
			$email = filter_var( $item[0], FILTER_VALIDATE_EMAIL );

			if ( empty( $email ) ) {
				continue;
			}

			$pair['email'] = $email;

			if ( ! empty( $item[1] ) ) {
				$pair['name'] = $item[1];
			}

			$data[] = $pair;
		}

		return $data;
	}
}
