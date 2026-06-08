<?php

namespace WPMailSMTP;

/**
 * Class Connection.
 *
 * @since 3.7.0
 */
class Connection extends AbstractConnection {

	/**
	 * Connection Options object.
	 *
	 * @since 3.7.0
	 *
	 * @var Options
	 */
	private $options;

	/**
	 * Constructor.
	 *
	 * @since 3.7.0
	 */
	public function __construct() {

		$this->options = Options::init();
	}

	/**
	 * Get the connection identifier.
	 *
	 * @since 3.7.0
	 *
	 * @return string
	 */
	public function get_id() {

		return 'primary';
	}

	/**
	 * Get the connection name.
	 *
	 * @since 3.7.0
	 *
	 * @return string
	 */
	public function get_name() {

		return esc_html__( 'Primary', 'wp-mail-smtp' );
	}

	/**
	 * Get connection options object.
	 *
	 * @since 3.7.0
	 *
	 * @return Options
	 */
	public function get_options() {

		return $this->options;
	}

	/**
	 * Whether the connection is primary or not.
	 *
	 * @since 3.7.0
	 *
	 * @return bool
	 */
	public function is_primary() {

		return true;
	}
}
