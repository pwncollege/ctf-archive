<?php

namespace WPForms\Integrations\AI\API\Http;

use WPForms\Integrations\AI\Helpers;
use WPForms\Integrations\LiteConnect\LiteConnect;
use WPForms\Integrations\LiteConnect\Integration;

/**
 * Request class.
 *
 * @since 1.9.1
 */
class Request {

	/**
	 * API URL.
	 *
	 * @since 1.9.1
	 */
	private const URL = 'https://wpformsapi.com/api/v1';

	/**
	 * Request timeout.
	 *
	 * @since 1.9.1
	 */
	private const TIMEOUT = 60;

	/**
	 * Send a POST request.
	 *
	 * @since 1.9.1
	 *
	 * @param string $endpoint Endpoint to request.
	 * @param array  $args     Request arguments.
	 *
	 * @return Response Response from the API.
	 */
	public function post( string $endpoint, array $args = [] ): Response {

		return $this->request( 'POST', $endpoint, $args );
	}

	/**
	 * Make a request to the API.
	 *
	 * @since 1.9.1
	 *
	 * @param string $method   Request method.
	 * @param string $endpoint Endpoint to request.
	 * @param array  $args     Arguments to send.
	 *
	 * @return Response Response from the API.
	 * @noinspection PhpSameParameterValueInspection
	 */
	private function request( string $method, string $endpoint, array $args ): Response {

		// Once mark AI features as used when making a first request.
		Helpers::set_ai_used();

		// Add domain to the request.
		$args['domain'] = preg_replace( '/(https?:\/\/)?(www\.)?(.*)\/?/', '$3', home_url() );

		$args = $this->maybe_add_lite_connect_credentials( $args );

		$options = [
			'method'  => $method,
			'headers' => $this->get_headers(),
			'timeout' => $this->get_timeout(),
			'body'    => wp_json_encode( $args ),
		];

		$url = $this->get_request_url( $endpoint );

		return new Response( wp_safe_remote_request( $url, $options ) );
	}

	/**
	 * Get AI API request URL.
	 *
	 * @since 1.9.3
	 *
	 * @param string $endpoint Endpoint to request.
	 *
	 * @return string
	 */
	private function get_request_url( string $endpoint ): string {

		/**
		 * Filter AI API request URL.
		 *
		 * @since 1.9.3
		 *
		 * @param string $url      API request URL.
		 * @param string $endpoint Endpoint to request.
		 */
		return (string) apply_filters( 'wpforms_integrations_aiapi_http_request_url', self::URL . $endpoint, $endpoint );
	}

	/**
	 * Maybe add Lite Connect credentials to the request.
	 *
	 * @since 1.9.1
	 *
	 * @param array $args Arguments to send.
	 *
	 * @return array
	 */
	private function maybe_add_lite_connect_credentials( array $args ): array {

		if ( wpforms()->is_pro() ) {
			return $args;
		}

		if ( ! LiteConnect::is_allowed() || ! LiteConnect::is_enabled() ) {
			return $args;
		}

		return array_merge( $args, Integration::get_site_credentials() );
	}

	/**
	 * Retrieve request headers.
	 *
	 * @since 1.9.1
	 *
	 * @return array
	 */
	private function get_headers(): array {

		$headers = [
			'Content-Type' => 'application/json',
		];

		if ( wpforms()->is_pro() ) {
			$headers['x-wpforms-licensekey'] = wpforms_get_license_key();
		}

		return $headers;
	}

	/**
	 * Retrieve request timeout.
	 *
	 * @since 1.9.1
	 *
	 * @return int
	 */
	private function get_timeout(): int {

		/**
		 * Filter the API request timeout.
		 *
		 * @since 1.9.1
		 *
		 * @param int $timeout Request timeout.
		 */
		return (int) apply_filters( 'wpforms_integrations_ai_api_http_request_timeout', self::TIMEOUT ); // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName
	}
}
