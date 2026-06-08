<?php

namespace WPMailSMTP;

/**
 * Class WPMailArgs. This class responsible for `wp_mail` function arguments parsing.
 *
 * Parsing algorithms copied from `wp_mail` function.
 *
 * @since 3.7.0
 */
class WPMailArgs {

	/**
	 * Array of the `wp_mail` function arguments.
	 *
	 * @since 3.7.0
	 *
	 * @var array
	 */
	private $args;

	/**
	 * Parsed headers.
	 *
	 * @since 3.7.0
	 *
	 * @var array
	 */
	private $headers = null;

	/**
	 * Constructor.
	 *
	 * @since 3.7.0
	 *
	 * @param array $args {
	 *     Array of the `wp_mail` function arguments.
	 *
	 *     @type string|string[] $to          Array or comma-separated list of email addresses to send message.
	 *     @type string          $subject     Email subject.
	 *     @type string          $message     Message contents.
	 *     @type string|string[] $headers     Additional headers.
	 *     @type string|string[] $attachments Paths to files to attach.
	 * }
	 */
	public function __construct( $args ) {

		$this->args = $args;
	}

	/**
	 * Get arguments.
	 *
	 * @since 3.7.0
	 *
	 * @return array
	 */
	public function get_args() {

		return $this->args;
	}

	/**
	 * Get to email.
	 *
	 * @since 3.7.0
	 *
	 * @return string
	 */
	public function get_to_email() {

		return $this->get_arg( 'to' );
	}

	/**
	 * Get subject.
	 *
	 * @since 3.7.0
	 *
	 * @return string
	 */
	public function get_subject() {

		return $this->get_arg( 'subject' );
	}

	/**
	 * Get message.
	 *
	 * @since 3.7.0
	 *
	 * @return string
	 */
	public function get_message() {

		return $this->get_arg( 'message' );
	}

	/**
	 * Get from email.
	 *
	 * @since 3.7.0
	 *
	 * @return string
	 */
	public function get_from_email() {

		$from = $this->get_from();

		return $from['email'];
	}

	/**
	 * Get from name.
	 *
	 * @since 3.7.0
	 *
	 * @return string
	 */
	public function get_from_name() {

		$from = $this->get_from();

		return $from['name'];
	}

	/**
	 * Get parsed headers.
	 *
	 * @since 3.7.0
	 *
	 * @return array
	 */
	public function get_headers() { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		if ( ! is_null( $this->headers ) ) {
			return $this->headers;
		}

		$this->headers = [];

		if ( ! empty( $this->args['headers'] ) ) {
			if ( ! is_array( $this->args['headers'] ) ) {
				$headers = explode( "\n", str_replace( "\r\n", "\n", $this->args['headers'] ) );
			} else {
				$headers = $this->args['headers'];
			}

			foreach ( (array) $headers as $header ) {
				if ( strpos( $header, ':' ) === false ) {
					continue;
				}

				list( $name, $content ) = array_map( 'trim', explode( ':', trim( $header ), 2 ) );

				$name = strtolower( $name );

				if ( isset( $this->headers[ $name ] ) && in_array( $name, [ 'cc', 'bcc', 'reply-to' ], true ) ) {
					$this->headers[ $name ] .= ', ' . $content;
				} else {
					$this->headers[ $name ] = $content;
				}
			}
		}

		return $this->headers;
	}

	/**
	 * Get particular header value.
	 *
	 * @since 3.7.0
	 *
	 * @param string $name Header name.
	 *
	 * @return null|string
	 */
	public function get_header( $name ) {

		$name    = strtolower( $name );
		$headers = $this->get_headers();

		return isset( $headers[ $name ] ) ? $headers[ $name ] : null;
	}

	/**
	 * Get argument value.
	 *
	 * @since 3.7.0
	 *
	 * @param string $key     Argument key.
	 * @param mixed  $default Default value.
	 *
	 * @return string
	 */
	private function get_arg( $key, $default = '' ) {

		return isset( $this->args[ $key ] ) ? $this->args[ $key ] : $default;
	}

	/**
	 * Get from address.
	 *
	 * @since 3.7.0
	 *
	 * @return array
	 */
	private function get_from() {

		$from_email = '';
		$from_name  = '';
		$value      = $this->get_header( 'from' );
		$value      = is_null( $value ) ? '' : $value;

		$bracket_pos = strpos( $value, '<' );

		if ( $bracket_pos !== false ) {
			// Text before the bracketed email is the "From" name.
			if ( $bracket_pos > 0 ) {
				$from_name = substr( $value, 0, $bracket_pos - 1 );
				$from_name = str_replace( '"', '', $from_name );
				$from_name = trim( $from_name );
			}

			$from_email = substr( $value, $bracket_pos + 1 );
			$from_email = str_replace( '>', '', $from_email );
			$from_email = trim( $from_email );

			// Avoid setting an empty $from_email.
		} elseif ( trim( $value ) !== '' ) {
			$from_email = trim( $value );
		}

		return [
			'email' => $from_email,
			'name'  => $from_name,
		];
	}

	/**
	 * Get attachments.
	 *
	 * @since 4.0.0
	 *
	 * @return array
	 */
	public function get_attachments() {

		return $this->get_arg( 'attachments', [] );
	}
}
