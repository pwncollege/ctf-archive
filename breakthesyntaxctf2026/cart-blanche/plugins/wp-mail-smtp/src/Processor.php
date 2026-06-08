<?php

namespace WPMailSMTP;

/**
 * Class Processor modifies the behaviour of wp_mail() function.
 *
 * @since 1.0.0
 */
class Processor {

	/**
	 * This attribute will hold the "original" WP from email address passed to the wp_mail_from filter,
	 * that is not equal to the default email address.
	 *
	 * It should hold an email address set via the wp_mail_from filter, before we might overwrite it.
	 *
	 * @since 2.1.0
	 *
	 * @var string
	 */
	protected $wp_mail_from;

	/**
	 * Connections manager.
	 *
	 * @since 3.7.0
	 *
	 * @var ConnectionsManager
	 */
	private $connections_manager;

	/**
	 * This attribute will hold the arguments passed to the `wp_mail` function.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	private $original_wp_mail_args;

	/**
	 * This attribute will hold the arguments passed to the `wp_mail` function and filtered via `wp_mail` filter.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	private $filtered_wp_mail_args;

	/**
	 * This attribute will hold the From address filtered via the `wp_mail_from` filter.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	private $filtered_from_email;

	/**
	 * This attribute will hold the From name filtered via the `wp_mail_from_name` filter.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	private $filtered_from_name;

	/**
	 * Class constructor.
	 *
	 * @since 3.7.0
	 *
	 * @param ConnectionsManager $connections_manager Connections manager.
	 */
	public function __construct( $connections_manager = null ) {

		if ( is_null( $connections_manager ) ) {
			$this->connections_manager = wp_mail_smtp()->get_connections_manager();
		} else {
			$this->connections_manager = $connections_manager;
		}
	}

	/**
	 * Assign all hooks to proper places.
	 *
	 * @since 1.0.0
	 */
	public function hooks() {

		add_action( 'phpmailer_init', array( $this, 'phpmailer_init' ) );

		// High priority number tries to ensure our plugin code executes last and respects previous hooks, if not forced.
		add_filter( 'wp_mail_from', array( $this, 'filter_mail_from_email' ), PHP_INT_MAX );
		add_filter( 'wp_mail_from_name', array( $this, 'filter_mail_from_name' ), PHP_INT_MAX );

		add_action( 'wp_mail', [ $this, 'capture_early_wp_mail_filter_call' ], - PHP_INT_MAX );
		add_action( 'wp_mail', [ $this, 'capture_late_wp_mail_filter_call' ], PHP_INT_MAX );
	}

	/**
	 * Redefine certain PHPMailer options with our custom ones.
	 *
	 * @since 1.0.0
	 *
	 * @param \PHPMailer $phpmailer It's passed by reference, so no need to return anything.
	 */
	public function phpmailer_init( $phpmailer ) { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.MaxExceeded

		$connection         = $this->connections_manager->get_mail_connection();
		$connection_options = $connection->get_options();
		$mailer             = $connection->get_mailer_slug();

		// Check that mailer is not blank, and if mailer=smtp, host is not blank.
		if (
			! $mailer ||
			( 'smtp' === $mailer && ! $connection_options->get( 'smtp', 'host' ) )
		) {
			return;
		}

		// If the mailer is pepipost, make sure we have a username and password.
		if (
			'pepipost' === $mailer &&
			( ! $connection_options->get( 'pepipost', 'user' ) && ! $connection_options->get( 'pepipost', 'pass' ) )
		) {
			return;
		}

		// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

		// Set the mailer type as per config above, this overrides the already called isMail method.
		// It's basically always 'smtp'.
		$phpmailer->Mailer = $mailer;

		// Set the Sender (return-path) if required.
		if ( $connection_options->get( 'mail', 'return_path' ) ) {
			$phpmailer->Sender = $phpmailer->From;
		}

		// Set the SMTPSecure value, if set to none, leave this blank. Possible values: 'ssl', 'tls', ''.
		if ( 'none' === $connection_options->get( $mailer, 'encryption' ) ) {
			$phpmailer->SMTPSecure = '';
		} else {
			$phpmailer->SMTPSecure = $connection_options->get( $mailer, 'encryption' );
		}

		// Check if user has disabled SMTPAutoTLS.
		if ( $connection_options->get( $mailer, 'encryption' ) !== 'tls' && ! $connection_options->get( $mailer, 'autotls' ) ) {
			$phpmailer->SMTPAutoTLS = false;
		}

		// Check if original WP from email can be set as the reply_to attribute.
		if ( $this->allow_setting_original_from_email_to_reply_to( $phpmailer->getReplyToAddresses(), $mailer ) ) {
			$phpmailer->addReplyTo( $this->wp_mail_from );
		}

		// If we're sending via SMTP, set the host.
		if ( 'smtp' === $mailer ) {
			// Set the other options.
			$phpmailer->Host = $connection_options->get( $mailer, 'host' );
			$phpmailer->Port = $connection_options->get( $mailer, 'port' );

			// If we're using smtp auth, set the username & password.
			if ( $connection_options->get( $mailer, 'auth' ) ) {
				$phpmailer->SMTPAuth = true;
				$phpmailer->Username = $connection_options->get( $mailer, 'user' );
				$phpmailer->Password = $connection_options->get( $mailer, 'pass' );
			}
		} elseif ( 'pepipost' === $mailer ) {
			// Set the Pepipost settings for BC.
			$phpmailer->Mailer     = 'smtp';
			$phpmailer->Host       = 'smtp.pepipost.com';
			$phpmailer->Port       = $connection_options->get( $mailer, 'port' );
			$phpmailer->SMTPSecure = $connection_options->get( $mailer, 'encryption' ) === 'none' ? '' : $connection_options->get( $mailer, 'encryption' );
			$phpmailer->SMTPAuth   = true;
			$phpmailer->Username   = $connection_options->get( $mailer, 'user' );
			$phpmailer->Password   = $connection_options->get( $mailer, 'pass' );
		}

		$phpmailer->Timeout = 30;
		// phpcs:enable

		// Maybe set default reply-to header.
		$this->set_default_reply_to( $phpmailer );

		// You can add your own options here.
		// See the phpmailer documentation for more info: https://github.com/PHPMailer/PHPMailer/tree/5.2-stable.
		/* @noinspection PhpUnusedLocalVariableInspection It's passed by reference. */
		$phpmailer = apply_filters( 'wp_mail_smtp_custom_options', $phpmailer );
	}

	/**
	 * Check if it's allowed to set the original WP from email to the reply_to field.
	 *
	 * @since 2.1.0
	 *
	 * @param array  $reply_to Array of currently set reply to emails.
	 * @param string $mailer   The slug of current mailer.
	 *
	 * @return bool
	 */
	protected function allow_setting_original_from_email_to_reply_to( $reply_to, $mailer ) {

		$connection         = $this->connections_manager->get_mail_connection();
		$connection_options = $connection->get_options();
		$forced             = $connection_options->get( 'mail', 'from_email_force' );
		$from_email         = $connection_options->get( 'mail', 'from_email' );

		if ( ! empty( $reply_to ) || empty( $this->wp_mail_from ) ) {
			return false;
		}

		if ( in_array( $mailer, [ 'zoho' ], true ) ) {
			$sender     = $connection_options->get( $mailer, 'user_details' );
			$from_email = ! empty( $sender['email'] ) ? $sender['email'] : '';
			$forced     = true;
		}

		if (
			$from_email === $this->wp_mail_from ||
			! $forced
		) {
			return false;
		}

		return true;
	}

	/**
	 * This method will be called every time 'smtp' and 'mail' mailers will be used to send emails.
	 *
	 * @since 1.3.0
	 * @since 1.5.0 Added a do_action() to be able to hook into.
	 *
	 * @param bool   $is_sent If the email was sent.
	 * @param array  $to      To email address.
	 * @param array  $cc      CC email addresses.
	 * @param array  $bcc     BCC email addresses.
	 * @param string $subject The email subject.
	 * @param string $body    The email body.
	 * @param string $from    The from email address.
	 */
	public static function send_callback( $is_sent, $to, $cc, $bcc, $subject, $body, $from ) {

		if ( ! $is_sent ) {
			// Add mailer to the beginning and save to display later.
			Debug::set(
				'Mailer: ' . esc_html( wp_mail_smtp()->get_providers()->get_options( wp_mail_smtp()->get_connections_manager()->get_mail_connection()->get_mailer_slug() )->get_title() ) . "\r\n" .
				'PHPMailer was able to connect to SMTP server but failed while trying to send an email.'
			);
		} else {
			Debug::clear();
		}

		do_action( 'wp_mail_smtp_mailcatcher_smtp_send_after', $is_sent, $to, $cc, $bcc, $subject, $body, $from );
	}

	/**
	 * Validate the email address.
	 *
	 * @since 3.6.0
	 *
	 * @param string $email The email address.
	 *
	 * @return boolean True if email address is valid, false on failure.
	 */
	public static function is_email_callback( $email ) {

		return (bool) is_email( $email );
	}

	/**
	 * Modify the email address that is used for sending emails.
	 *
	 * @since 1.0.0
	 * @since 1.3.0 Forcing email rewrite if option is selected.
	 * @since 1.7.0 Default email may be empty, so pay attention to that as well.
	 *
	 * @param string $wp_email The email address passed by the filter.
	 *
	 * @return string
	 */
	public function filter_mail_from_email( $wp_email ) {

		// Save the original from address.
		$this->filtered_from_email = filter_var( $wp_email, FILTER_VALIDATE_EMAIL );

		$connection         = $this->connections_manager->get_mail_connection();
		$connection_options = $connection->get_options();
		$forced             = $connection_options->get( 'mail', 'from_email_force' );
		$from_email         = $connection_options->get( 'mail', 'from_email' );
		$def_email          = WP::get_default_email();

		// Save the "original" set WP email from address for later use.
		if ( $wp_email !== $def_email ) {
			$this->wp_mail_from = filter_var( $wp_email, FILTER_VALIDATE_EMAIL );
		}

		// Return FROM EMAIL if forced in settings.
		if ( $forced && ! empty( $from_email ) ) {
			return $from_email;
		}

		// If the FROM EMAIL is not the default, return it unchanged.
		if ( ! empty( $def_email ) && $wp_email !== $def_email ) {
			return $wp_email;
		}

		return ! empty( $from_email ) ? $from_email : $wp_email;
	}

	/**
	 * Modify the sender name that is used for sending emails.
	 *
	 * @since 1.0.0
	 * @since 1.3.0 Forcing name rewrite if option is selected.
	 *
	 * @param string $name The from name passed through the filter.
	 *
	 * @return string
	 */
	public function filter_mail_from_name( $name ) {

		// Save the original from name.
		$this->filtered_from_name = $name;

		$connection         = $this->connections_manager->get_mail_connection();
		$connection_options = $connection->get_options();
		$force              = $connection_options->get( 'mail', 'from_name_force' );

		// If the FROM NAME is not the default and not forced, return it unchanged.
		if ( ! $force && $name !== $this->get_default_name() ) {
			return $name;
		}

		$name = $connection_options->get( 'mail', 'from_name' );

		return $name;
	}

	/**
	 * Get the default email address based on domain name.
	 *
	 * @since 1.0.0
	 * @since 1.7.0 May return an empty string.
	 *
	 * @return string Empty string when we aren't able to get the site domain (CLI, misconfigured server etc).
	 */
	public function get_default_email() {

		$server_name = Geo::get_site_domain();

		if ( empty( $server_name ) ) {
			return '';
		}

		// Get rid of www.
		$sitename = strtolower( $server_name );
		if ( substr( $sitename, 0, 4 ) === 'www.' ) {
			$sitename = substr( $sitename, 4 );
		}

		return 'wordpress@' . $sitename;
	}

	/**
	 * Get the default email FROM NAME generated by WordPress.
	 *
	 * @since 1.3.0
	 *
	 * @return string
	 */
	public function get_default_name() {
		return 'WordPress';
	}

	/**
	 * Get or create the phpmailer.
	 *
	 * @since 1.9.0
	 *
	 * @return MailCatcherInterface
	 */
	public function get_phpmailer() {

		global $phpmailer;

		// Make sure the PHPMailer class has been instantiated.
		if ( ! is_object( $phpmailer ) || ! is_a( $phpmailer, 'PHPMailer' ) ) {
			$phpmailer = wp_mail_smtp()->generate_mail_catcher( true ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		}

		return $phpmailer;
	}

	/**
	 * Set the default reply_to header, if:
	 * - no other reply_to headers are already set and,
	 * - the default reply_to address filter `wp_mail_smtp_processor_default_reply_to_addresses` is configured.
	 *
	 * @since 2.1.1
	 *
	 * @param MailCatcherInterface $phpmailer The PHPMailer object.
	 */
	private function set_default_reply_to( $phpmailer ) {

		if ( ! empty( $phpmailer->getReplyToAddresses() ) ) {
			return;
		}

		$default_reply_to_emails = apply_filters( 'wp_mail_smtp_processor_set_default_reply_to', '' );

		if ( empty( $default_reply_to_emails ) ) {
			return;
		}

		foreach ( explode( ',', $default_reply_to_emails ) as $email ) {
			$email = trim( $email );

			if ( filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
				$phpmailer->addReplyTo( $email );
			}
		}
	}

	/**
	 * Capture `wp_mail` filter call on earliest priority.
	 *
	 * Currently used to capture the original `wp_mail` arguments before they are filtered.
	 *
	 * @since 4.0.0
	 *
	 * @param array $args The original `wp_mail` arguments.
	 *
	 * @return array
	 */
	public function capture_early_wp_mail_filter_call( $args ) {

		$this->original_wp_mail_args = $args;

		return $args;
	}

	/**
	 * Capture `wp_mail` filter call on latest priority.
	 *
	 * Currently used to capture the `wp_mail` arguments after they are filtered
	 * and capture `wp_mail` function call.
	 *
	 * @since 4.0.0
	 *
	 * @param array $args The filtered `wp_mail` arguments.
	 *
	 * @return array
	 */
	public function capture_late_wp_mail_filter_call( $args ) {

		$this->filtered_wp_mail_args = $args;

		$this->capture_wp_mail_call();

		return $args;
	}

	/**
	 * Capture `wp_mail` function call.
	 *
	 * @since 4.0.0
	 */
	private function capture_wp_mail_call() {

		/**
		 * Fires on `wp_mail` function call.
		 *
		 * @since 4.0.0
		 */
		do_action( 'wp_mail_smtp_processor_capture_wp_mail_call' );
	}

	/**
	 * Get the original `wp_mail` arguments.
	 *
	 * @since 4.0.0
	 *
	 * @return array
	 */
	public function get_original_wp_mail_args() {

		return $this->original_wp_mail_args;
	}

	/**
	 * Get the filtered `wp_mail` arguments.
	 *
	 * @since 4.0.0
	 *
	 * @return array
	 */
	public function get_filtered_wp_mail_args() {

		return $this->filtered_wp_mail_args;
	}

	/**
	 * Get the filtered `wp_mail_from` value.
	 *
	 * @since 4.0.0
	 *
	 * @return string
	 */
	public function get_filtered_from_email() {

		return $this->filtered_from_email;
	}

	/**
	 * Get the filtered `wp_mail_from_name` value.
	 *
	 * @since 4.0.0
	 *
	 * @return string
	 */
	public function get_filtered_from_name() {

		return $this->filtered_from_name;
	}
}
