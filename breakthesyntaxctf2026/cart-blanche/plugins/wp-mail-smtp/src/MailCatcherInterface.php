<?php

namespace WPMailSMTP;

/**
 * Interface MailCatcherInterface.
 *
 * @since 2.2.0
 */
interface MailCatcherInterface {

	/**
	 * Modify the default send() behaviour.
	 * For those mailers, that relies on PHPMailer class - call it directly.
	 * For others - init the correct provider and process it.
	 *
	 * @since 2.2.0
	 *
	 * @throws \phpmailerException|\PHPMailer\PHPMailer\Exception When sending via PhpMailer fails for some reason.
	 *
	 * @return bool
	 */
	public function send();

	/**
	 * Get the PHPMailer line ending.
	 *
	 * @since 2.2.0
	 *
	 * @return string
	 */
	public function get_line_ending();

	/**
	 * Create a unique ID to use for multipart email boundaries.
	 *
	 * @since 2.4.0
	 *
	 * @return string
	 */
	public function generate_id();

	/**
	 * Get debug event ID.
	 *
	 * @since 3.5.0
	 *
	 * @return bool|int
	 */
	public function get_debug_event_id();

	/**
	 * Whether the current email is a test email.
	 *
	 * @since 3.5.0
	 *
	 * @return bool
	 */
	public function is_test_email();

	/**
	 * Whether the current email is a Setup Wizard test email.
	 *
	 * @since 3.5.0
	 *
	 * @return bool
	 */
	public function is_setup_wizard_test_email();
}
