<?php

namespace WPMailSMTP\Providers;

use WPMailSMTP\MailCatcher;
use WPMailSMTP\MailCatcherV6;

/**
 * Interface MailerInterface.
 *
 * @since 1.0.0
 */
interface MailerInterface {

	/**
	 * Send the email.
	 *
	 * @since 1.0.0
	 */
	public function send();

	/**
	 * Whether the email is sent or not.
	 * We basically check the response code from a request to provider.
	 * Might not be 100% correct, not guarantees that email is delivered.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function is_email_sent();

	/**
	 * Whether the mailer supports the current PHP version or not.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function is_php_compatible();

	/**
	 * Whether the mailer has all its settings correctly set up and saved.
	 *
	 * @since 1.4.0
	 *
	 * @return bool
	 */
	public function is_mailer_complete();

	/**
	 * Get the email body.
	 *
	 * @since 1.0.0
	 *
	 * @return string|array
	 */
	public function get_body();

	/**
	 * Get the email headers.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_headers();

	/**
	 * Get an array of all debug information relevant to the mailer.
	 *
	 * @since 1.2.0
	 *
	 * @return array
	 */
	public function get_debug_info();

	/**
	 * Re-use the MailCatcher class methods and properties.
	 *
	 * @since 1.2.0
	 *
	 * @param MailCatcherInterface $phpmailer The MailCatcher object.
	 */
	public function process_phpmailer( $phpmailer );
}
