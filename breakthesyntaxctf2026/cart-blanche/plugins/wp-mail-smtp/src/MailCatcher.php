<?php

namespace WPMailSMTP;

use phpmailerException;

// Load PHPMailer class, so we can subclass it.
if ( ! class_exists( 'PHPMailer', false ) ) {
	require_once ABSPATH . WPINC . '/class-phpmailer.php';
}

/**
 * Class MailCatcher replaces the \PHPMailer and modifies the email sending logic.
 * Thus, we can use other mailers API to do what we need, or stop emails completely.
 *
 * @since 1.0.0
 */
class MailCatcher extends \PHPMailer implements MailCatcherInterface {

	use MailCatcherTrait;

	/**
	 * Callback Action function name.
	 *
	 * The function that handles the result of the send email action.
	 * It is called out by send() for each email sent.
	 *
	 * @since 1.3.0
	 *
	 * @var string
	 */
	public $action_function = '\WPMailSMTP\Processor::send_callback';

	/**
	 * Returns all custom headers.
	 * In older versions of \PHPMailer class this method didn't exist.
	 * As we support WordPress 3.6+ - we need to make sure this method is always present.
	 *
	 * @since 1.5.0
	 *
	 * @return array
	 */
	public function getCustomHeaders() {

		return $this->CustomHeader; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
	}

	/**
	 * Get the PHPMailer line ending.
	 *
	 * @since 2.2.0
	 *
	 * @return string
	 */
	public function get_line_ending() {

		return $this->LE; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
	}

	/**
	 * Throw PHPMailer exception.
	 *
	 * @since 4.0.0
	 *
	 * @param string $error Error message.
	 *
	 * @throws phpmailerException PHPMailer exception.
	 */
	protected function throw_exception( $error ) {

		throw new phpmailerException( $error );
	}
}
