<?php

namespace WPMailSMTP;

use PHPMailer\PHPMailer\Exception;

/**
 * Class MailCatcher replaces the \PHPMailer\PHPMailer\PHPMailer introduced in WP 5.5 and
 * modifies the email sending logic. Thus, we can use other mailers API to do what we need, or stop emails completely.
 *
 * @since 2.2.0
 */
class MailCatcherV6 extends \PHPMailer\PHPMailer\PHPMailer implements MailCatcherInterface {

	use MailCatcherTrait;

	/**
	 * Callback Action function name.
	 *
	 * The function that handles the result of the send email action.
	 * It is called out by send() for each email sent.
	 *
	 * @since 2.2.0
	 *
	 * @var string
	 */
	public $action_function = '\WPMailSMTP\Processor::send_callback';

	/**
	 * Which validator to use by default when validating email addresses.
	 * We are using built-in WordPress function `is_email` to validate the email address.
	 *
	 * @see PHPMailer::validateAddress()
	 *
	 * @since 3.6.0
	 *
	 * @var string|callable
	 */
	public static $validator = [ Processor::class, 'is_email_callback' ];

	/**
	 * Get the PHPMailer line ending.
	 *
	 * @since 2.2.0
	 *
	 * @return string
	 */
	public function get_line_ending() {

		return static::$LE; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
	}

	/**
	 * Throw PHPMailer exception.
	 *
	 * @since 4.0.0
	 *
	 * @param string $error Error message.
	 *
	 * @throws Exception PHPMailer exception.
	 */
	protected function throw_exception( $error ) {

		throw new Exception( $error );
	}
}
