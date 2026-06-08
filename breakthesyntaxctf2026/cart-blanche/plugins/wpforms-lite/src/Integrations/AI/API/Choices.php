<?php

namespace WPForms\Integrations\AI\API;

use WPForms\Integrations\AI\Helpers;

/**
 * Choices class.
 *
 * @since 1.9.1
 */
class Choices extends API {

	/**
	 * Get choices from the API.
	 *
	 * @since 1.9.1
	 *
	 * @param string $prompt     Prompt to get choices for.
	 * @param string $session_id Session ID.
	 *
	 * @return array
	 */
	public function choices( string $prompt, string $session_id = '' ): array {

		$args = [
			'userPrompt' => $this->prepare_prompt( $prompt ),
			'limit'      => $this->get_limit(),
		];

		if ( ! empty( $session_id ) ) {
			$args['sessionId'] = $session_id;
		}

		$endpoint = '/ai-choices';

		$response = $this->request->post( $endpoint, $args );

		if ( $response->has_errors() ) {
			$error_data = $response->get_error_data();

			Helpers::log_error( $response->get_log_message( $error_data ), $endpoint, $args );

			return $error_data;
		}

		$result = $response->get_body();

		// Limit the number of choices.
		// In some cases, the API may return more choices than requested.
		$choices = array_slice( $result['choices'], 0, $this->get_limit() );

		// Remove numeration from choices.
		$choices = array_map(
			static function ( $choice ) {

				return preg_replace( '/^\d+\.\s+/', '', $choice );
			},
			$choices
		);

		$result['choices'] = $choices;

		return $result;
	}
}
