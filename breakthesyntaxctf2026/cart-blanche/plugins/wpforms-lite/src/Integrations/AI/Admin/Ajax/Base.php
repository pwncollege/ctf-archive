<?php

namespace WPForms\Integrations\AI\Admin\Ajax;

use WPForms\Integrations\AI\API\API;

/**
 * Base class.
 *
 * @since 1.9.1
 */
abstract class Base {

	/**
	 * API instance.
	 *
	 * @since 1.9.1
	 *
	 * @var API
	 */
	protected $api;

	/**
	 * Initialize.
	 *
	 * @since 1.9.1
	 */
	public function init() {

		$this->api = new API();

		$this->api->init();
		$this->hooks();
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.9.1
	 */
	private function hooks(): void {

		add_action( 'wp_ajax_wpforms_rate_ai_response', [ $this, 'rate_response' ] );
	}

	/**
	 * Rate choices response.
	 *
	 * @since 1.9.1
	 */
	public function rate_response(): void {

		if ( ! $this->validate_nonce() ) {
			wp_send_json_error();
		}

		$helpful     = $this->get_post_data( 'helpful', 'bool' );
		$response_id = $this->get_post_data( 'response_id' );

		$response = $this->api->rate( $helpful, $response_id );

		wp_send_json_success( $response );
	}

	/**
	 * Validate nonce.
	 *
	 * @since 1.9.1
	 *
	 * @return bool|int
	 */
	protected function validate_nonce() {

		return check_ajax_referer( 'wpforms-ai-nonce', 'nonce', false );
	}

	/**
	 * Get the post's data by key.
	 *
	 * @since 1.9.1
	 *
	 * @param string $key  Key to get data for.
	 * @param string $type Type of data to get.
	 *
	 * @return mixed
	 */
	protected function get_post_data( string $key, string $type = 'text' ) {

		switch ( $type ) {
			case 'int':
				$value = filter_input( INPUT_POST, $key, FILTER_SANITIZE_NUMBER_INT ) ?? 0;
				break;

			case 'array':
				$value = filter_input( INPUT_POST, $key, FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_REQUIRE_ARRAY ) ?? [];
				break;

			case 'bool':
				$value = filter_input( INPUT_POST, $key, FILTER_VALIDATE_BOOLEAN ) ?? false;
				break;

			case 'json':
				$value = json_decode( filter_input( INPUT_POST, $key ), true );
				break;

			default:
				// We should use this alternative to FILTER_SANITIZE_FULL_SPECIAL_CHARS filter,
				// because htmlspecialchars() function does double encoding of special characters,
				// which is necessary to properly handle the encoded HTML in chat questions.
				$value = htmlspecialchars( filter_input( INPUT_POST, $key ) ?? '' );
				break;
		}

		return $value;
	}

	/**
	 * Determine whether a given prompt is empty.
	 *
	 * It must contain a minimum of one character.
	 *
	 * @since 1.9.1
	 *
	 * @param string $prompt The prompt to check.
	 *
	 * @return bool True if the prompt is empty.
	 */
	protected function is_empty_prompt( string $prompt ): bool {

		$special_chars = [ '@', '!', '#', '$', '%', '^', '&', '*', '(', ')', '-', '+', '=', '{', '}', '[', ']', '|', '\\', ':', ';', '"', "'", '<', '>', ',', '.', '?', '/' ];
		$prompt        = str_replace( $special_chars, '', $prompt );

		return empty( $prompt );
	}
}
