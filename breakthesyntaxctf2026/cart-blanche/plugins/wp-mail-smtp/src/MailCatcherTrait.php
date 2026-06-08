<?php

namespace WPMailSMTP;

use Exception;
use WPMailSMTP\Admin\DebugEvents\DebugEvents;
use WPMailSMTP\Providers\MailerAbstract;

/**
 * Trait MailCatcherTrait.
 *
 * @since 3.7.0
 */
trait MailCatcherTrait {

	/**
	 * Debug output buffer.
	 *
	 * @since 3.3.0
	 *
	 * @var array
	 */
	private $debug_output_buffer = [];

	/**
	 * Debug event ID.
	 *
	 * @since 3.5.0
	 *
	 * @var int
	 */
	private $debug_event_id = false;

	/**
	 * Whether the current email is a test email.
	 *
	 * @since 3.5.0
	 *
	 * @var bool
	 */
	private $is_test_email = false;

	/**
	 * Whether the current email is a Setup Wizard test email.
	 *
	 * @since 3.5.0
	 *
	 * @var bool
	 */
	private $is_setup_wizard_test_email = false;

	/**
	 * Whether the current email is blocked to be sent.
	 *
	 * @since 3.8.0
	 *
	 * @var bool
	 */
	private $is_emailing_blocked = false;

	/**
	 * Holds the most recent error message.
	 *
	 * @since 3.7.0
	 *
	 * @var string
	 */
	protected $latest_error = '';

	/**
	 * Last error code captured before PHPMailer clears it.
	 *
	 * Populated via setError() override with first-write-wins semantics.
	 *
	 * @since 4.8.0
	 *
	 * @var string
	 */
	private $last_error_code = '';

	/**
	 * Modify the default send() behaviour.
	 * For those mailers, that relies on PHPMailer class - call it directly.
	 * For others - init the correct provider and process it.
	 *
	 * @since 1.0.0
	 * @since 1.4.0 Process "Do Not Send" option, but always allow test email.
	 * @since 4.5.0 Add support for logging blocked emails.
	 *
	 * @throws Exception When sending via PhpMailer fails for some reason.
	 *
	 * @return bool
	 */
	public function send() { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		// Reset email related variables.
		$this->debug_event_id             = false;
		$this->is_test_email              = false;
		$this->is_setup_wizard_test_email = false;
		$this->is_emailing_blocked        = false;
		$this->latest_error               = '';

		if ( wp_mail_smtp()->is_blocked() ) {
			$this->is_emailing_blocked = true;
		}

		// Always allow a test email - check for the specific header.
		foreach ( (array) $this->getCustomHeaders() as $header ) {
			if (
				! empty( $header[0] ) &&
				! empty( $header[1] ) &&
				$header[0] === 'X-Mailer-Type'
			) {
				if ( trim( $header[1] ) === 'WPMailSMTP/Admin/Test' ) {
					$this->is_emailing_blocked = false;
					$this->is_test_email       = true;
				} elseif ( trim( $header[1] ) === 'WPMailSMTP/Admin/SetupWizard/Test' ) {
					$this->is_setup_wizard_test_email = true;
				}
			}
		}

		// Log blocked emails if the option is enabled.
		if ( $this->is_emailing_blocked ) {
			/**
			 * Fires when an email is blocked from being sent.
			 *
			 * @since 4.5.0
			 *
			 * @param MailCatcherInterface $mailcatcher The MailCatcher object.
			 */
			do_action( 'wp_mail_smtp_mail_catcher_send_blocked', $this );

			return false;
		}

		// If it's not a test email,
		// check if the email should be enqueued
		// instead of being sent immediately.
		if ( ! $this->is_test_email && ! $this->is_setup_wizard_test_email ) {

			/**
			 * Filters whether an email should be enqueued or sent immediately.
			 *
			 * @since 4.0.0
			 *
			 * @param bool  $should_enqueue Whether to enqueue an email, or send it.
			 * @param array $wp_mail_args   Original arguments of the `wp_mail` call.
			 */
			$should_enqueue_email = apply_filters(
				'wp_mail_smtp_mail_catcher_send_enqueue_email',
				false,
				wp_mail_smtp()->get_processor()->get_filtered_wp_mail_args()
			);

			$queue = wp_mail_smtp()->get_queue();

			// If we should enqueue the email,
			// and the email has been enqueued,
			// bail.
			if ( $should_enqueue_email && $queue->enqueue_email() ) {
				return true;
			}
		}

		$connection  = wp_mail_smtp()->get_connections_manager()->get_mail_connection();
		$mailer_slug = $connection->get_mailer_slug();

		// Define a custom header, that will be used to identify the plugin and the mailer.
		// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		$this->XMailer = 'WPMailSMTP/Mailer/' . $mailer_slug . ' ' . WPMS_PLUGIN_VER;

		// Use the default PHPMailer, as we inject our settings there for certain providers.
		if (
			$mailer_slug === 'mail' ||
			$mailer_slug === 'smtp' ||
			$mailer_slug === 'pepipost'
		) {
			return $this->smtp_send( $connection );
		} else {
			return $this->api_send( $connection );
		}
	}

	/**
	 * Send email via SMTP.
	 *
	 * @since 4.0.0
	 *
	 * @param ConnectionInterface $connection The connection object.
	 *
	 * @throws Exception When sending via PhpMailer fails for some reason.
	 *
	 * @return bool
	 */
	private function smtp_send( $connection ) { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		// Reset captured SMTP error code from previous send attempts.
		$this->last_error_code = '';

		$mailer_slug = $connection->get_mailer_slug();

		// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		try {
			if ( DebugEvents::is_debug_enabled() && ! $this->is_test_email ) {
				$this->SMTPDebug   = 3;
				$this->Debugoutput = [ $this, 'debug_output_callback' ];
			}

			/**
			 * Fires before email pre send via SMTP.
			 *
			 * Allow to hook early to catch any early failed emails.
			 *
			 * @since 2.9.0
			 *
			 * @param MailCatcherInterface $mailcatcher The MailCatcher object.
			 */
			do_action( 'wp_mail_smtp_mailcatcher_smtp_pre_send_before', $this );

			// Prepare all the headers.
			if ( ! $this->preSend() ) {
				$this->throw_exception( $this->ErrorInfo );
			}

			/**
			 * Fires before email send via SMTP.
			 *
			 * Allow to hook after all the preparation before the actual sending.
			 *
			 * @since 2.9.0
			 *
			 * @param MailCatcherInterface $mailcatcher The MailCatcher object.
			 */
			do_action( 'wp_mail_smtp_mailcatcher_smtp_send_before', $this );

			if ( ! $this->postSend() ) {
				$this->throw_exception( $this->ErrorInfo );
			}

			DebugEvents::add_debug(
				esc_html__( 'An email request was sent.', 'wp-mail-smtp' )
			);

			return true;
		} catch ( Exception $e ) {
			$this->mailHeader = '';

			// We need this to append SMTP error to the `PHPMailer::ErrorInfo` property.
			$this->setError( $e->getMessage() );

			$error_message = 'Mailer: ' . esc_html( wp_mail_smtp()->get_providers()->get_options( $mailer_slug )->get_title() ) . "\r\n" . $this->ErrorInfo;
			$error_code    = $this->get_smtp_error_code();

			// Set the debug error, but not for default PHP mailer.
			if ( $mailer_slug !== 'mail' ) {
				$this->debug_event_id = Debug::set( $error_message );
				$this->latest_error   = $error_message;

				if ( DebugEvents::is_debug_enabled() && ! empty( $this->debug_output_buffer ) ) {
					$debug_message  = $error_message . "\r\n" . esc_html__( 'Debug Output:', 'wp-mail-smtp' ) . "\r\n";
					$debug_message .= implode( "\r\n", $this->debug_output_buffer );

					$this->debug_event_id = DebugEvents::add_debug( $debug_message );
				}
			}

			/**
			 * Fires after email sent failure via SMTP.
			 *
			 * @since 3.5.0
			 *
			 * @param string               $error_message Error message.
			 * @param MailCatcherInterface $mailcatcher   The MailCatcher object.
			 * @param string               $mailer_slug   Current mailer name.
			 * @param string               $error_code    Error code/slug.
			 * @param int                  $response_code HTTP/SMTP response code (0 for non-API mailers).
			 */
			do_action( 'wp_mail_smtp_mailcatcher_send_failed', $this->ErrorInfo, $this, $mailer_slug, $error_code, 0 );

			if ( $this->exceptions ) {
				throw $e;
			}

			return false;
		} finally {

			// Clear debug output buffer.
			$this->debug_output_buffer = [];
		}
		// phpcs:enable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
	}

	/**
	 * Send email via API integration.
	 *
	 * @since 4.0.0
	 *
	 * @param ConnectionInterface $connection The connection object.
	 *
	 * @throws Exception When sending fails for some reason.
	 *
	 * @return bool
	 */
	private function api_send( $connection ) { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		$mailer_slug = $connection->get_mailer_slug();

		// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		try {
			// We need this so that the \PHPMailer class will correctly prepare all the headers.
			$this->Mailer = 'mail';

			/**
			 * Fires before email pre send.
			 *
			 * Allow to hook early to catch any early failed emails.
			 *
			 * @since 2.9.0
			 *
			 * @param MailCatcherInterface $mailcatcher The MailCatcher object.
			 */
			do_action( 'wp_mail_smtp_mailcatcher_pre_send_before', $this );

			// Prepare everything (including the message) for sending.
			if ( ! $this->preSend() ) {
				$this->throw_exception( $this->ErrorInfo );
			}

			$mailer = wp_mail_smtp()->get_providers()->get_mailer( $mailer_slug, $this, $connection );

			if ( ! $mailer ) {
				$this->throw_exception( 'The selected mailer not found.' );
			}

			if ( ! $mailer->is_php_compatible() ) {
				$this->throw_exception( 'The selected mailer is not compatible with your PHP version.' );
			}

			/**
			 * Fires before email send.
			 *
			 * Allows to hook after all the preparation before the actual sending.
			 *
			 * @since 3.3.0
			 *
			 * @param MailerAbstract $mailer The Mailer object.
			 */
			do_action( 'wp_mail_smtp_mailcatcher_send_before', $mailer );

			/*
			 * Send the actual email.
			 * We reuse everything, that was preprocessed for usage in \PHPMailer.
			 */
			$mailer->send();

			$is_sent = $mailer->is_email_sent();

			/**
			 * Fires after email send.
			 *
			 * Allow to perform any actions with the data.
			 *
			 * @since 3.5.0
			 *
			 * @param MailerAbstract       $mailer      The Mailer object.
			 * @param MailCatcherInterface $mailcatcher The MailCatcher object.
			 */
			do_action( 'wp_mail_smtp_mailcatcher_send_after', $mailer, $this );

			if ( $is_sent !== true ) {
				$this->throw_exception( $mailer->get_response_error() );
			}

			// Clear debug messages if email is successfully sent.
			Debug::clear();

			return true;
		} catch ( Exception $e ) {
			// Add mailer to the beginning and save to display later.
			$message = 'Mailer: ' . esc_html( wp_mail_smtp()->get_providers()->get_options( $mailer_slug )->get_title() ) . "\r\n";

			$conflicts = new Conflicts();

			if ( $conflicts->is_detected() ) {
				$conflict_plugin_names = implode( ', ', $conflicts->get_all_conflict_names() );
				$message              .= 'Conflicts: ' . esc_html( $conflict_plugin_names ) . "\r\n";
			}

			$error_code           = ! empty( $mailer ) ? $mailer->get_response_error_code() : '';
			$response_code        = ! empty( $mailer ) ? $mailer->get_response_code() : 0;
			$error_message        = $message . $e->getMessage();
			$this->debug_event_id = Debug::set( $error_message );
			$this->latest_error   = $error_message;

			/**
			 * Fires after email sent failure.
			 *
			 * @since 3.5.0
			 *
			 * @param string               $error_message Error message.
			 * @param MailCatcherInterface $mailcatcher   The MailCatcher object.
			 * @param string               $mailer_slug   Current mailer name.
			 * @param string               $error_code    Error code/slug.
			 * @param int                  $response_code HTTP response code.
			 */
			do_action( 'wp_mail_smtp_mailcatcher_send_failed', $e->getMessage(), $this, $mailer_slug, $error_code, $response_code );

			if ( $this->exceptions ) {
				throw $e;
			}

			return false;
		}
		// phpcs:enable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
	}

	/**
	 * Create a unique ID to use for multipart email boundaries.
	 *
	 * @since 2.4.0
	 *
	 * @return string
	 */
	public function generate_id() {

		return $this->generateId();
	}

	/**
	 * Get SMTP error code combining error category and numeric SMTP code.
	 *
	 * Reverse-lookups PHPMailer language key from ErrorInfo via static::$language,
	 * making the category language-independent even if PHPMailer is translated (WP 6.8+).
	 *
	 * @since 4.8.0
	 *
	 * @return string Error code like "authenticate_535", "connect_host", or empty.
	 */
	private function get_smtp_error_code() {

		$smtp_code = $this->last_error_code;
		$category  = '';

		// Reverse-lookup PHPMailer language key from ErrorInfo.
		// Use getTranslations() which works in both old (instance $language) and new (static $language) PHPMailer.
		$language = method_exists( $this, 'getTranslations' ) ? $this->getTranslations() : [];

		foreach ( $language as $key => $translated ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			if ( ! empty( $translated ) && strpos( $this->ErrorInfo, $translated ) === 0 ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
				$category = $key;

				break;
			}
		}

		$parts = array_filter( [ $category, $smtp_code ] );

		return ! empty( $parts ) ? implode( '_', $parts ) : '';
	}

	/**
	 * Override setError to capture SMTP error codes before PHPMailer clears them.
	 *
	 * PHPMailer's SMTP quit() clears the error via sendCommand success.
	 * By the time we catch the exception, the SMTP error code is gone.
	 * Uses first-write-wins so subsequent setError calls (RSET, connect_host fallback)
	 * cannot overwrite the original meaningful code.
	 *
	 * @since 4.8.0
	 *
	 * @param string $msg Error message.
	 */
	protected function setError( $msg ) { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid

		// Capture SMTP error code with first-write-wins before quit() wipes it.
		if (
			empty( $this->last_error_code ) &&
			isset( $this->smtp ) &&
			$this->smtp instanceof \PHPMailer\PHPMailer\SMTP
		) {
			$error = $this->smtp->getError();

			if ( ! empty( $error['smtp_code'] ) ) {
				$this->last_error_code = (string) $error['smtp_code'];
			}
		}

		parent::setError( $msg );
	}

	/**
	 * Debug output callback.
	 * Save debugging info to buffer array.
	 *
	 * @since 3.3.0
	 *
	 * @param string $str   Message.
	 * @param int    $level Debug level.
	 */
	public function debug_output_callback( $str, $level ) {

		/*
		 * Filter out all higher levels than 3.
		 * SMTPDebug level 3 is commands, data and connection status.
		 */
		if ( $level > 3 ) {
			return;
		}

		$this->debug_output_buffer[] = trim( $str, "\r\n" );
	}

	/**
	 * Get debug event ID.
	 *
	 * @since 3.5.0
	 *
	 * @return bool|int
	 */
	public function get_debug_event_id() {

		return $this->debug_event_id;
	}

	/**
	 * Whether the current email is a test email.
	 *
	 * @since 3.5.0
	 *
	 * @return bool
	 */
	public function is_test_email() {

		return $this->is_test_email;
	}

	/**
	 * Whether the current email is a Setup Wizard test email.
	 *
	 * @since 3.5.0
	 *
	 * @return bool
	 */
	public function is_setup_wizard_test_email() {

		return $this->is_setup_wizard_test_email;
	}

	/**
	 * Whether the current email is blocked to be sent.
	 *
	 * @since 3.8.0
	 *
	 * @return bool
	 */
	public function is_emailing_blocked() {

		return $this->is_emailing_blocked;
	}

	/**
	 * Return the list of properties representing
	 * this class' state.
	 *
	 * @since 4.0.0
	 *
	 * @return array State of this class.
	 */
	private function get_state_properties() {

		return [
			'CharSet',
			'ContentType',
			'Encoding',
			'CustomHeader',
			'Subject',
			'Body',
			'AltBody',
			'ReplyTo',
			'to',
			'cc',
			'bcc',
			'attachment',
		];
	}

	/**
	 * Return an array of relevant properties.
	 *
	 * @since 4.0.0
	 *
	 * @return array State of this class.
	 */
	public function get_state() {

		$state = [];

		foreach ( $this->get_state_properties() as $property ) {
			$state[ $property ] = $this->{$property};
		}

		return $state;
	}

	/**
	 * Set properties from a provided array of data.
	 *
	 * @since 4.0.0
	 *
	 * @param array $state Array of properties to apply.
	 */
	public function set_state( $state ) { // phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded

		// Filter out non-allowed properties.
		$state = array_intersect_key(
			$state,
			array_flip( $this->get_state_properties() )
		);

		foreach ( $state as $property => $value ) {
			if ( $property !== 'attachment' ) {
				$this->{$property} = $value;
			} else {
				// Handle potential I/O exceptions
				// in PHPMailer when attaching files.
				$this->clearAttachments();

				foreach ( $state['attachment'] as $attachment ) {
					[ $path, , $name ] = $attachment;

					try {
						$this->addAttachment( $path, $name );
					} catch ( Exception $e ) {
						continue;
					}
				}
			}
		}
	}

	/**
	 * Set the From and FromName properties.
	 *
	 * @since 4.7.1
	 *
	 * @param string $address Email address.
	 * @param string $name    Name.
	 * @param bool   $auto    Whether to also set the Sender address, defaults to true.
	 *
	 * @return bool Returns true on success and false on failure.
	 */
	public function setFrom( $address, $name = '', $auto = true ) { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid

		// Set `$auto` param as false, to control return-path via plugin settings.
		return parent::setFrom( $address, $name, false );
	}
}
