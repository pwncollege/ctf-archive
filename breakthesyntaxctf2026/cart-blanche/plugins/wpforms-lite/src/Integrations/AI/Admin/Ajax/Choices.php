<?php

namespace WPForms\Integrations\AI\Admin\Ajax;

use WPForms\Integrations\AI\API\Choices as ChoicesAPI;

/**
 * Choices class.
 *
 * @since 1.9.1
 */
class Choices extends Base {

	/**
	 * API Choices instance.
	 *
	 * @since 1.9.1
	 *
	 * @var ChoicesAPI
	 */
	protected $api_choices;

	/**
	 * Initialize.
	 *
	 * @since 1.9.1
	 */
	public function init() {

		parent::init();

		$this->api_choices = new ChoicesAPI();

		$this->api_choices->init();
		$this->hooks();
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.9.1
	 */
	private function hooks() {

		add_action( 'wp_ajax_wpforms_get_ai_choices', [ $this, 'get_choices' ] );
	}

	/**
	 * Get choices.
	 *
	 * @since 1.9.1
	 */
	public function get_choices() {

		if ( ! $this->validate_nonce() ) {
			wp_send_json_error(
				[ 'error' => esc_html__( 'Your session expired. Please reload the builder.', 'wpforms-lite' ) ]
			);
		}

		$prompt = $this->get_post_data( 'prompt' );

		if ( $this->is_empty_prompt( $prompt ) ) {
			wp_send_json_success( [ 'choices' => [] ] );
		}

		$session_id = $this->get_post_data( 'session_id' );

		$choices = $this->api_choices->choices( $prompt, $session_id );

		wp_send_json_success( $choices );
	}
}
