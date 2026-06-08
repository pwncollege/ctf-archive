<?php

namespace WPMailSMTP;

use WPMailSMTP\Providers\MailerAbstract;

/**
 * Abstract class AbstractConnection.
 *
 * @since 3.7.0
 */
abstract class AbstractConnection implements ConnectionInterface {

	/**
	 * Get the connection title.
	 *
	 * @since 3.7.0
	 *
	 * @return string
	 */
	public function get_title() {

		return sprintf(
			'%s (%s)',
			$this->get_name(),
			wp_mail_smtp()->get_providers()->get_options( $this->get_mailer_slug(), $this )->get_title()
		);
	}

	/**
	 * Get connection mailer slug.
	 *
	 * @since 3.7.0
	 *
	 * @return string
	 */
	public function get_mailer_slug() {

		return $this->get_options()->get( 'mail', 'mailer' );
	}

	/**
	 * Get connection mailer object.
	 *
	 * @since 3.7.0
	 *
	 * @return MailerAbstract
	 */
	public function get_mailer() {

		$phpmailer = wp_mail_smtp()->get_processor()->get_phpmailer();

		return wp_mail_smtp()->get_providers()->get_mailer( $this->get_mailer_slug(), $phpmailer, $this );
	}

	/**
	 * Whether the connection is primary or not.
	 *
	 * @since 3.7.0
	 *
	 * @return bool
	 */
	public function is_primary() {

		return false;
	}
}
