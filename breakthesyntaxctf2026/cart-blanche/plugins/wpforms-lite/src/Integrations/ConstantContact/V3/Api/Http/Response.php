<?php

namespace WPForms\Integrations\ConstantContact\V3\Api\Http;

// phpcs:ignore WPForms.PHP.UseStatement.UnusedUseStatement
use WP_Error;
use RuntimeException;

/**
 * Wrapper class to parse responses.
 *
 * @since 1.9.3
 */
class Response {

	/**
	 * Input data.
	 *
	 * @since 1.9.3
	 *
	 * @var array|WP_Error
	 */
	private $input;

	/**
	 * Error message.
	 *
	 * @since 1.9.3
	 *
	 * @var string
	 */
	private static $error;

	/**
	 * Request constructor.
	 *
	 * @since 1.9.3
	 *
	 * @param array|WP_Error $input The response data.
	 */
	public function __construct( $input ) {

		$this->input = $input;
		self::$error = $this->has_errors() ? $this->get_error_from_body() : '';
	}

	/**
	 * Get an error message.
	 *
	 * @since 1.9.3
	 *
	 * @return string
	 */
	public static function get_error_message(): string {

		return self::$error;
	}

	/**
	 * Retrieve only the response code from the raw response.
	 *
	 * @since 1.9.3
	 *
	 * @return int The response code as an integer.
	 */
	public function get_response_code(): int {

		return absint( wp_remote_retrieve_response_code( $this->input ) );
	}

	/**
	 * Retrieve only the response message from the raw response.
	 *
	 * @since 1.9.3
	 *
	 * @return string The response message.
	 */
	public function get_response_message(): string {

		if ( $this->has_errors() ) {
			return 'Response error';
		}

		$body = $this->get_body();

		if ( ! empty( $body['message'] ) ) {
			return $body['message'];
		}

		return wp_remote_retrieve_response_message( $this->input );
	}

	/**
	 * Retrieve only the body from the raw response.
	 *
	 * @since 1.9.3
	 *
	 * @throws RuntimeException If the response has errors.
	 *
	 * @return array The body of the response.
	 */
	public function get_body(): array {

		if ( $this->has_errors() ) {
			$error = $this->get_error_from_body();

			throw new RuntimeException( esc_html( $error ) );
		}

		return (array) json_decode( wp_remote_retrieve_body( $this->input ), true );
	}

	/**
	 * Whether we received errors in the response.
	 *
	 * @since 1.9.3
	 *
	 * @return bool True if response has errors.
	 */
	public function has_errors(): bool {

		$code = $this->get_response_code();

		return $code < 200 || $code > 299;
	}

	/**
	 * Get an error message from the body.
	 *
	 * @since 1.9.3
	 *
	 * @return string
	 */
	private function get_error_from_body(): string {

		if ( ! $this->has_errors() ) {
			return '';
		}

		$body = json_decode( wp_remote_retrieve_body( $this->input ), true );

		if ( isset( $body['error_message'] ) ) {
			return $body['error_message'];
		}

		$messages = [];

		foreach ( $body as $id => $value ) {
			$messages[] = $value['error_message'] ?? '';

			if ( $id === 'message' ) {
				$messages[] = $value;
			}
		}

		return implode( ', ', $messages );
	}
}
