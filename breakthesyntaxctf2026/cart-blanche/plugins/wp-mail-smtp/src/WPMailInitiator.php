<?php

namespace WPMailSMTP;

/**
 * The `wp_mail` function initiator. It has centralized initiator data that can be used across all processes.
 *
 * @since 3.7.0
 */
class WPMailInitiator {

	/**
	 * The path where the `wp_mail` function was called.
	 *
	 * @since 3.7.0
	 *
	 * @var string
	 */
	private $file;

	/**
	 * Line in the file where the `wp_mail` function was called.
	 *
	 * @since 3.7.0
	 *
	 * @var int
	 */
	private $line;

	/**
	 * The `wp_mail` function call backtrace.
	 *
	 * @since 3.7.0
	 *
	 * @var array
	 */
	private $backtrace;

	/**
	 * Initiator name (plugin or theme name).
	 *
	 * @since 3.7.0
	 *
	 * @var string
	 */
	private $name;

	/**
	 * Initiator type. Available options: plugin, mu-plugin, theme, wp-core, unknown.
	 *
	 * @since 3.7.0
	 *
	 * @var string
	 */
	private $type;

	/**
	 * Initiator slug (plugin or theme slug).
	 *
	 * @since 3.7.0
	 *
	 * @var string
	 */
	private $slug;

	/**
	 * Whether performance-costly properties were initialized.
	 *
	 * @since 3.7.0
	 *
	 * @var array
	 */
	private $initialized = false;

	/**
	 * Register hooks.
	 *
	 * @since 3.7.0
	 */
	public function hooks() {

		// Capture `wp_mail` function call.
		add_action( 'wp_mail_smtp_processor_capture_wp_mail_call', [ $this, 'capture_wp_mail_call' ], 0 );
	}

	/**
	 * Capture `wp_mail` function call.
	 *
	 * @since 4.0.0
	 */
	public function capture_wp_mail_call() {

		$this->set_initiator();
	}

	/**
	 * Get the path where the `wp_mail` function was called.
	 *
	 * @since 3.7.0
	 *
	 * @return string
	 */
	public function get_file() {

		return $this->file;
	}

	/**
	 * Get the line in the file where the `wp_mail` function was called.
	 *
	 * @since 3.7.0
	 *
	 * @return int
	 */
	public function get_line() {

		return $this->line;
	}

	/**
	 * Get the `wp_mail` function call backtrace.
	 *
	 * @since 3.7.0
	 *
	 * @return array
	 */
	public function get_backtrace() {

		return $this->backtrace;
	}

	/**
	 * Get the initiator name (plugin or theme name).
	 *
	 * @since 3.7.0
	 *
	 * @return string
	 */
	public function get_name() {

		$this->lazy_init();

		return $this->name;
	}

	/**
	 * Get the initiator type. Available options: plugin, mu-plugin, theme, wp-core, unknown.
	 *
	 * @since 3.7.0
	 *
	 * @return string
	 */
	public function get_type() {

		$this->lazy_init();

		return $this->type;
	}

	/**
	 * Get the initiator slug (plugin or theme slug).
	 *
	 * @since 3.7.0
	 *
	 * @return string
	 */
	public function get_slug() {

		$this->lazy_init();

		return $this->slug;
	}

	/**
	 * Initialize initiator data.
	 *
	 * @since 3.7.0
	 */
	public function set_initiator() {

		// Reset previous values.
		$this->reset();

		$backtrace = $this->get_wpmail_backtrace();

		/**
		 * Filter the `wp_mail` function initiator data.
		 *
		 * @since 4.0.0
		 *
		 * @param array $backtrace Backtrace data.
		 */
		$backtrace = apply_filters( 'wp_mail_smtp_wp_mail_initiator_set_initiator', $backtrace );

		if ( empty( $backtrace['file'] ) ) {
			return;
		}

		$this->file      = $backtrace['file'];
		$this->backtrace = $backtrace['backtrace'];

		if ( ! empty( $backtrace['line'] ) ) {
			$this->line = $backtrace['line'];
		}
	}

	/**
	 * Initialize performance-costly properties.
	 *
	 * @since 3.7.0
	 */
	private function lazy_init() {

		if ( empty( $this->file ) || $this->initialized ) {
			return;
		}

		$data = WP::get_initiator( $this->file );

		$this->name = $data['name'];
		$this->type = $data['type'];

		if ( isset( $data['slug'] ) ) {
			$this->slug = $data['slug'];
		}

		$this->initialized = true;
	}

	/**
	 * Reset previous initiator data before the new email sending.
	 *
	 * @since 3.7.0
	 */
	private function reset() {

		$this->initialized = false;
		$this->file        = null;
		$this->line        = null;
		$this->backtrace   = null;
		$this->name        = null;
		$this->type        = null;
		$this->slug        = null;
	}

	/**
	 * Get the `wp_mail` function backtrace data, if it exists.
	 *
	 * @since 3.7.0
	 *
	 * @return array
	 */
	private function get_wpmail_backtrace() {

		$backtrace = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_debug_backtrace

		foreach ( $backtrace as $i => $item ) {
			if ( $item['function'] === 'wp_mail' ) {
				if ( isset( $item['function'] ) ) {
					unset( $item['function'] );
				}

				$item['backtrace'] = array_slice( $backtrace, $i );

				return $item;
			}
		}

		return [];
	}
}
