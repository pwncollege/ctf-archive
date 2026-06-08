<?php

namespace WPForms\Integrations\AI\API;

use WPForms\Integrations\AI\API\Http\Request;
use WPForms\Integrations\AI\Helpers;

/**
 * API class.
 *
 * @since 1.9.1
 */
class API {

	/**
	 * API limit.
	 *
	 * @since 1.9.1
	 */
	const LIMIT = 100;

	/**
	 * API limit max.
	 *
	 * @since 1.9.1
	 */
	const LIMIT_MAX = 1000;

	/**
	 * Request instance.
	 *
	 * @since 1.9.1
	 *
	 * @var Request
	 */
	protected $request;

	/**
	 * Initialize the API.
	 *
	 * @since 1.9.1
	 */
	public function init() {

		$this->request = new Request();
	}

	/**
	 * Rate the response.
	 *
	 * @since 1.9.1
	 *
	 * @param bool   $helpful     Whether the response was helpful.
	 * @param string $response_id Response ID to rate.
	 *
	 * @return array
	 */
	public function rate( bool $helpful, string $response_id ): array {

		$args = [
			'helpful'    => $helpful,
			'responseId' => $response_id,
		];

		$endpoint = '/rate-response';

		$response = $this->request->post( $endpoint, $args );

		if ( $response->has_errors() ) {
			$error_data = $response->get_error_data();

			Helpers::log_error( $response->get_log_message( $error_data ), $endpoint, $args );

			return $error_data;
		}

		return $response->get_body();
	}

	/**
	 * Get the limit for the API request.
	 * Returns limit set by the filter or the default limit.
	 * The limit is capped at LIMIT_MAX.
	 *
	 * @since 1.9.1
	 *
	 * @return int
	 */
	protected function get_limit(): int {

		return min(
			/**
			 * Filter the limit for the API request.
			 *
			 * @since 1.9.1
			 *
			 * @param int $limit Limit for the API request.
			 */
			(int) apply_filters( 'wpforms_integrations_ai_api_get_limit', self::LIMIT ), // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName
			self::LIMIT_MAX
		);
	}

	/**
	 * Prepare the prompt.
	 *
	 * @since 1.9.1
	 *
	 * @param string $prompt Prompt text.
	 *
	 * @return string
	 */
	protected function prepare_prompt( string $prompt ): string {

		// Remove any HTML tags.
		$prompt = wp_strip_all_tags( $prompt );

		// Remove any extra spaces.
		$prompt = preg_replace( '/\s+/', ' ', $prompt );

		// Remove any extra characters.
		return trim( $prompt, ' .,!?;:' );
	}
}
