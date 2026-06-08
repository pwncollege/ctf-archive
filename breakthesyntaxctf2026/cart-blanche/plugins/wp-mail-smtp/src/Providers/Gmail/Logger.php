<?php

namespace WPMailSMTP\Providers\Gmail;

use WPMailSMTP\Vendor\Psr\Log\LoggerInterface;
use WPMailSMTP\Vendor\Psr\Log\LogLevel;
use WPMailSMTP\Debug;

/**
 * Custom logger for Gmail provider to replace Monolog dependency.
 *
 * @since 4.7.0
 */
class Logger implements LoggerInterface {

	/**
	 * System is unusable.
	 *
	 * @since 4.7.0
	 *
	 * @param string $message The log message.
	 * @param array  $context The log context.
	 */
	public function emergency( $message, array $context = [] ) {

		$this->log( LogLevel::EMERGENCY, $message, $context );
	}

	/**
	 * Action must be taken immediately.
	 *
	 * @since 4.7.0
	 *
	 * @param string $message The log message.
	 * @param array  $context The log context.
	 */
	public function alert( $message, array $context = [] ) {

		$this->log( LogLevel::ALERT, $message, $context );
	}

	/**
	 * Critical conditions.
	 *
	 * @since 4.7.0
	 *
	 * @param string $message The log message.
	 * @param array  $context The log context.
	 */
	public function critical( $message, array $context = [] ) {

		$this->log( LogLevel::CRITICAL, $message, $context );
	}

	/**
	 * Runtime errors that do not require immediate action but should typically
	 * be logged and monitored.
	 *
	 * @since 4.7.0
	 *
	 * @param string $message The log message.
	 * @param array  $context The log context.
	 */
	public function error( $message, array $context = [] ) {

		$this->log( LogLevel::ERROR, $message, $context );
	}

	/**
	 * Exceptional occurrences that are not errors.
	 *
	 * @since 4.7.0
	 *
	 * @param string $message The log message.
	 * @param array  $context The log context.
	 */
	public function warning( $message, array $context = [] ) {

		$this->log( LogLevel::WARNING, $message, $context );
	}

	/**
	 * Normal but significant events.
	 *
	 * @since 4.7.0
	 *
	 * @param string $message The log message.
	 * @param array  $context The log context.
	 */
	public function notice( $message, array $context = [] ) {

		$this->log( LogLevel::NOTICE, $message, $context );
	}

	/**
	 * Interesting events.
	 *
	 * @since 4.7.0
	 *
	 * @param string $message The log message.
	 * @param array  $context The log context.
	 */
	public function info( $message, array $context = [] ) {

		$this->log( LogLevel::INFO, $message, $context );
	}

	/**
	 * Detailed debug information.
	 *
	 * @since 4.7.0
	 *
	 * @param string $message The log message.
	 * @param array  $context The log context.
	 */
	public function debug( $message, array $context = [] ) {

		$this->log( LogLevel::DEBUG, $message, $context );
	}

	/**
	 * Logs with an arbitrary level.
	 *
	 * @since 4.7.0
	 *
	 * @param mixed  $level   The log level.
	 * @param string $message The log message.
	 * @param array  $context The log context.
	 */
	public function log( $level, $message, array $context = [] ) {

		// Only log errors and warnings to avoid spam.
		if ( ! in_array( $level, [ LogLevel::ERROR, LogLevel::WARNING, LogLevel::CRITICAL, LogLevel::EMERGENCY, LogLevel::ALERT ], true ) ) {
			return;
		}

		// Interpolate context values into the message placeholders.
		$message = $this->interpolate( $message, $context );

		// Format the log message.
		$formatted_message = sprintf(
			'[%s] Gmail API: %s',
			strtoupper( $level ),
			$message
		);

		// Use WP Mail SMTP's Debug class to log the message.
		Debug::set( $formatted_message );
	}

	/**
	 * Interpolates context values into the message placeholders.
	 *
	 * @since 4.7.0
	 *
	 * @param string $message The message with placeholders.
	 * @param array  $context The context array.
	 *
	 * @return string The interpolated message.
	 */
	private function interpolate( $message, array $context = [] ) {

		// Build a replacement array with braces around the context keys.
		$replace = [];

		foreach ( $context as $key => $val ) {
			// Check that the value can be cast to string.
			if ( ! is_array( $val ) && ( ! is_object( $val ) || method_exists( $val, '__toString' ) ) ) {
				$replace[ '{' . $key . '}' ] = $val;
			}
		}

		// Interpolate replacement values into the message and return.
		return strtr( $message, $replace );
	}
}
