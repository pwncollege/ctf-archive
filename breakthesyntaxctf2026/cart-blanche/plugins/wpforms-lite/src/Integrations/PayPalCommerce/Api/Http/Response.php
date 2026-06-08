<?php

namespace WPForms\Integrations\PayPalCommerce\Api\Http;

/**
 * Wrapper class to parse responses.
 *
 * @since 1.10.0
 */
class Response {

	/**
	 * Input data.
	 *
	 * @since 1.10.0
	 *
	 * @var array|\WP_Error
	 */
	private $input;

	/**
	 * Response constructor.
	 *
	 * @since 1.10.0
	 *
	 * @param array|\WP_Error $input The response data.
	 */
	public function __construct( $input ) {

		$this->input = $input;
	}

	/**
	 * Retrieve only the response code from the raw response.
	 *
	 * @since 1.10.0
	 *
	 * @return int The response code as an integer.
	 */
	public function get_response_code(): int {

		return absint( wp_remote_retrieve_response_code( $this->input ) );
	}

	/**
	 * Retrieve only the response message from the raw response.
	 *
	 * @since 1.10.0
	 *
	 * @return array The response error.
	 */
	public function get_response_message(): array {

		$response_body = $this->get_body();
		$body          = $response_body['body'] ?? [];
		$error         = [];

		if ( ! empty( $body['message'] ) ) {
			$error['message'] = $body['message'];
		} elseif ( ! empty( $body['error'] ) ) {
			$error['message'] = $body['error'];
		} else {
			$message          = wp_remote_retrieve_response_message( $this->input );
			$error['message'] = ! empty( $message ) ? $message : 'Response error';
		}

		$debug_id = $this->get_debug_id();

		if ( $debug_id ) {
			$error['message'] .= ' PayPal Debug ID: ' . $this->get_debug_id();
		}

		if ( isset( $body['details'] ) ) {
			$error['details'] = $body['details'];
		}

		if ( ! empty( $response_body['log_id'] ) ) {
			$error['log_id'] = $response_body['log_id'];
		}

		return $error;
	}

	/**
	 * Retrieve only the body from the raw response.
	 *
	 * @since 1.10.0
	 *
	 * @return array The body of the response.
	 */
	public function get_body(): array {

		$body = wp_remote_retrieve_body( $this->input );

		if ( empty( $body ) ) {
			return [];
		}

		$body = json_decode( $body, true );

		if ( empty( $body ) ) {
			return [];
		}

		return $body;
	}

	/**
	 * Retrieve only the headers from the raw response.
	 *
	 * @since 1.10.0
	 *
	 * @return string The body of the response.
	 */
	private function get_debug_id(): string {

		$debug_id = wp_remote_retrieve_header( $this->input, 'Paypal-Debug-Id' );

		return ! empty( $debug_id ) ? $debug_id : '';
	}

	/**
	 * Whether we received errors in the response.
	 *
	 * @since 1.10.0
	 *
	 * @return bool True if the response has errors.
	 */
	public function has_errors(): bool {

		$code = $this->get_response_code();

		return $code < 200 || $code > 299;
	}
}
