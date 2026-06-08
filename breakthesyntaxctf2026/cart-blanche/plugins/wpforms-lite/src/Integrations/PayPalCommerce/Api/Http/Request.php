<?php

namespace WPForms\Integrations\PayPalCommerce\Api\Http;

use WPForms\Integrations\PayPalCommerce\Connection;
use WPForms\Integrations\PayPalCommerce\Admin\Connect;

/**
 * Wrapper class for HTTP requests.
 *
 * @since 1.10.0
 */
class Request {

	/**
	 * Active connection.
	 *
	 * @since 1.10.0
	 *
	 * @var Connection
	 */
	private $connection;

	/**
	 * Request constructor.
	 *
	 * @since 1.10.0
	 *
	 * @param Connection $connection Active connection.
	 */
	public function __construct( Connection $connection ) {

		$this->connection = $connection;
	}

	/**
	 * Send a GET request.
	 *
	 * @since 1.10.0
	 *
	 * @param string $url  Request URL.
	 * @param array  $args Request arguments.
	 *
	 * @return Response
	 */
	public function get( string $url, array $args = [] ): Response {

		$args = ! empty( $args ) ? [ 'body' => $args ] : [];

		return $this->request( 'GET', $url, $args );
	}

	/**
	 * Send a POST request.
	 *
	 * @since 1.10.0
	 *
	 * @param string $url  Request URL.
	 * @param array  $args Request arguments.
	 *
	 * @return Response
	 */
	public function post( string $url, array $args = [] ): Response {

		$args = ! empty( $args ) ? [ 'body' => $args ] : [];

		return $this->request( 'POST', $url, $args );
	}

	/**
	 * Send a DELETE request.
	 *
	 * @since 1.10.0
	 *
	 * @param string $url  Request URL.
	 * @param array  $args Arguments for the request.
	 *
	 * @return Response
	 */
	public function delete( string $url, array $args = [] ): Response {

		return $this->request( 'DELETE', $url, $args );
	}

	/**
	 * Send a request based on the method (main interface).
	 *
	 * @since 1.10.0
	 *
	 * @param string $method Request method.
	 * @param string $uri    Request URI.
	 * @param array  $args   Request options.
	 *
	 * @return Response
	 */
	public function request( string $method, string $uri, array $args ): Response {

		$url = $this->get_api_url() . '/' . $uri;

		$options['method']     = $method;
		$options['timeout']    = 15;
		$options['headers']    = ! empty( $args['headers'] ) ? array_filter( $args['headers'] ) : $this->get_default_headers();
		$options['body']       = ! empty( $args['body'] ) ? $args['body'] : '';
		$options['user-agent'] = 'WPForms PayPal Commerce/' . WPFORMS_VERSION . '; ' . get_bloginfo( 'name' );

		// Prepare a request body, as API expect it in a JSON format.
		if (
			! empty( $options['headers']['Content-Type'] ) &&
			$options['headers']['Content-Type'] !== 'application/x-www-form-urlencoded' &&
			! empty( $options['body'] ) &&
			$method !== 'GET'
		) {
			$options['body'] = wp_json_encode( $options['body'] );
		}

		// Retrieve the raw response from a safe HTTP request.
		return new Response( wp_safe_remote_request( $url, $options ) );
	}

	/**
	 * Retrieve default headers for request.
	 *
	 * @since 1.10.0
	 *
	 * @return array
	 */
	private function get_default_headers(): array {

		return [
			'Authorization' => 'Bearer ' . $this->connection->get_access_token(),
			'Content-Type'  => 'application/json',
		];
	}

	/**
	 * Get api server route.
	 *
	 * @since 1.10.0
	 *
	 * @return string
	 */
	private function get_api_url(): string {
		// Use a custom server if constant set.
		if ( defined( 'WPFORMS_PAYPAL_COMMERCE_API_SERVER' ) && WPFORMS_PAYPAL_COMMERCE_API_SERVER ) {
			return WPFORMS_PAYPAL_COMMERCE_API_SERVER;
		}

		return Connect::get_server_url();
	}
}
