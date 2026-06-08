<?php

namespace WPMailSMTP;

use WPMailSMTP\Providers\MailerAbstract;

/**
 * Interface ConnectionInterface.
 *
 * @since 3.7.0
 */
interface ConnectionInterface {

	/**
	 * Get the connection identifier.
	 *
	 * @since 3.7.0
	 *
	 * @return string
	 */
	public function get_id();

	/**
	 * Get the connection name.
	 *
	 * @since 3.7.0
	 *
	 * @return string
	 */
	public function get_name();

	/**
	 * Get the connection title. Includes mailer name.
	 *
	 * @since 3.7.0
	 *
	 * @return string
	 */
	public function get_title();

	/**
	 * Get connection mailer slug.
	 *
	 * @since 3.7.0
	 *
	 * @return string
	 */
	public function get_mailer_slug();

	/**
	 * Get connection mailer object.
	 *
	 * @since 3.7.0
	 *
	 * @return MailerAbstract
	 */
	public function get_mailer();

	/**
	 * Get connection options object.
	 *
	 * @since 3.7.0
	 *
	 * @return Options
	 */
	public function get_options();

	/**
	 * Whether the connection is primary or not.
	 *
	 * @since 3.7.0
	 *
	 * @return bool
	 */
	public function is_primary();
}
