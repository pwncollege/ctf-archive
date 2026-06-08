<?php

namespace WPForms\Integrations\ConstantContact\V3\Api\Http;

use WPForms\Integrations\ConstantContact\V3\ConstantContact;

/**
 * HTTP requests class.
 *
 * @since 1.9.3
 */
class Request {

	/**
	 * Base URL.
	 *
	 * @since 1.9.3
	 *
	 * @var string
	 */
	private $base_url;

	/**
	 * Access token.
	 *
	 * @since 1.9.3
	 *
	 * @var string
	 */
	private $access_token;

	/**
	 * Constructor.
	 *
	 * @since 1.9.3
	 *
	 * @param string $access_token Access token.
	 */
	public function __construct( string $access_token ) {

		$this->access_token = $access_token;
		$this->base_url     = ConstantContact::get_api_url();
	}

	/**
	 * Perform a request.
	 *
	 * @since 1.9.3
	 *
	 * @param string $method   Method.
	 * @param string $endpoint Endpoint to attach to the base URL.
	 * @param array  $args     Submitted arguments.
	 *
	 * @return Response
	 */
	private function request( string $method, string $endpoint, array $args = [] ): Response {

		$request_args = [
			'method'  => $method,
			'timeout' => 5,
			'headers' => $this->get_headers(),
		];

		if ( $args ) {
			$request_args['body'] = wp_json_encode( $args );
		}

		/**
		 * Allow modifying the HTTP request arguments.
		 *
		 * @since 1.9.3
		 *
		 * @param array $args List of request arguments.
		 */
		$request_args = (array) apply_filters( 'wpforms_integrations_constant_contact_v3_api_http_request_args', $request_args );

		$response = wp_remote_request( $this->base_url . $endpoint, $request_args );

		return new Response( $response );
	}

	/**
	 * GET request.
	 *
	 * @since 1.9.3
	 *
	 * @param string $endpoint Endpoint to attach to the base URL.
	 * @param array  $args     Query arguments.
	 *
	 * @return Response
	 */
	public function get( string $endpoint, array $args = [] ): Response {

		$endpoint = add_query_arg( $args, $endpoint );

		return $this->request( 'GET', $endpoint );
	}

	/**
	 * POST request.
	 *
	 * @since 1.9.3
	 *
	 * @param string $endpoint Endpoint to attach to the base URL.
	 * @param array  $args     Submitted arguments.
	 *
	 * @return Response
	 */
	public function post( string $endpoint, array $args = [] ): Response {

		return $this->request( 'POST', $endpoint, $args );
	}

	/**
	 * Send DELETE request.
	 *
	 * @since 1.9.3
	 *
	 * @param string $endpoint Endpoint.
	 *
	 * @return Response
	 */
	public function delete( string $endpoint ): Response {

		return $this->request( 'DELETE', $endpoint );
	}

	/**
	 * PUT request.
	 *
	 * @since 1.9.3
	 *
	 * @param string $endpoint Endpoint.
	 * @param array  $args     Submitted arguments.
	 *
	 * @return Response
	 */
	public function put( string $endpoint, array $args = [] ): Response {

		return $this->request( 'PUT', $endpoint, $args );
	}

	/**
	 * Get headers.
	 *
	 * @since 1.9.3
	 *
	 * @return array
	 */
	private function get_headers(): array {

		return [
			'Authorization' => 'Bearer ' . $this->access_token,
			'Content-Type'  => 'application/json',
		];
	}
}
