<?php

namespace WPForms\Integrations\AI\API\Http;

// phpcs:ignore WPForms.PHP.UseStatement.UnusedUseStatement
use WP_Error;

/**
 * Response class.
 *
 * @since 1.9.1
 */
class Response {

	/**
	 * Response.
	 *
	 * @since 1.9.1
	 *
	 * @var array
	 */
	protected $response;

	/**
	 * Response constructor.
	 *
	 * @since 1.9.1
	 *
	 * @param array|WP_Error $response Response.
	 */
	public function __construct( $response ) {

		$this->response = $response;
	}

	/**
	 * Retrieve only the body from the raw response.
	 *
	 * @since 1.9.1
	 *
	 * @return array The body of the response.
	 */
	public function get_body(): array {

		$body = wp_remote_retrieve_body( $this->response );

		if ( empty( $body ) ) {
			return [];
		}

		return json_decode( $body, true ) ?? [];
	}

	/**
	 * Get error data.
	 *
	 * @since 1.9.1
	 *
	 * @return array
	 */
	public function get_error_data(): array {

		$code = $this->get_response_code();

		return [
			'error' => $this->get_response_message(),
			'code'  => empty( $code ) ? 'wp_error' : $code,
		];
	}

	/**
	 * Retrieve only the response message from the raw response.
	 *
	 * @since 1.9.1
	 *
	 * @return string The response error.
	 */
	public function get_response_message(): string {

		if ( is_wp_error( $this->response ) ) {
			if ( $this->response->get_error_code() === 'http_request_failed' ) {
				return __( 'There appears to be a network error.', 'wpforms-lite' );
			}

			return $this->response->get_error_message();
		}

		$body = $this->get_body();

		return $body['error_message'] ?? wp_remote_retrieve_response_message( $this->response );
	}

	/**
	 * Get the error log message.
	 *
	 * @since 1.9.2
	 *
	 * @param array $error_data Error data.
	 *
	 * @return string The error log message.
	 */
	public function get_log_message( array $error_data ): string {

		return sprintf( /* translators: %1$s - error code, %2$s - error message. */
			__( 'API response: %1$s %2$s', 'wpforms-lite' ),
			$error_data['code'],
			$error_data['error']
		);
	}

	/**
	 * Retrieve only the response code from the raw response.
	 *
	 * @since 1.9.1
	 *
	 * @return int The response code as an integer.
	 */
	private function get_response_code(): int {

		return absint( wp_remote_retrieve_response_code( $this->response ) );
	}

	/**
	 * Whether we received errors in the response.
	 *
	 * @since 1.9.1
	 *
	 * @return bool True if response has errors.
	 */
	public function has_errors(): bool {

		$code = $this->get_response_code();

		return $code < 200 || $code > 299;
	}
}
