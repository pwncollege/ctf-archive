<?php

namespace WPForms\Integrations\Square;

/**
 * Square form frontend related functionality.
 *
 * @since 1.9.5
 */
class Frontend {

	/**
	 * Initialize.
	 *
	 * @since 1.9.5
	 */
	public function init() {

		$this->hooks();

		return $this;
	}

	/**
	 * Frontend hooks.
	 *
	 * @since 1.9.5
	 */
	private function hooks() {

		add_action( 'wpforms_frontend_container_class', [ $this, 'form_container_class' ], 10, 2 );
		add_action( 'wpforms_wp_footer',                [ $this, 'enqueues' ] );
	}

	/**
	 * Add class to form container if Square is enabled.
	 *
	 * @since 1.9.5
	 *
	 * @param array $classes   Array of form classes.
	 * @param array $form_data Form data of current form.
	 *
	 * @return array
	 */
	public function form_container_class( $classes, array $form_data ): array {

		$classes = (array) $classes;

		if ( ! Connection::get() ) {
			return $classes;
		}

		if ( ! Helpers::has_square_field( $form_data ) || ! Helpers::is_payments_enabled( $form_data ) ) {
			return $classes;
		}

		if ( Helpers::is_square_recurring_enabled( $form_data ) ) {
			$classes[] = 'wpforms-square-is-recurring';
		}

		$classes[] = 'wpforms-square';

		return $classes;
	}

	/**
	 * Enqueue assets in the frontend if Square is in use on the page.
	 *
	 * @since 1.9.5
	 *
	 * @param array $forms Form data of forms on current page.
	 */
	public function enqueues( $forms ) {

		$connection = Connection::get();

		if ( ! $connection || ! $connection->is_usable() ) {
			return;
		}

		$forms = (array) $forms;

		if ( ! Helpers::has_square_field( $forms, true ) ) {
			return;
		}

		if ( ! Helpers::has_square_enabled( $forms ) ) {
			return;
		}

		$min = wpforms_get_min_suffix();

		// Include styles if the "Include Form Styling > No Styles" is not set.
		if ( wpforms_setting( 'disable-css', '1' ) !== '3' ) {
			wp_enqueue_style(
				'wpforms-square',
				WPFORMS_PLUGIN_URL . "assets/css/integrations/square/wpforms-square{$min}.css",
				[],
				WPFORMS_VERSION
			);
		}

		// phpcs:disable WordPress.WP.EnqueuedResourceParameters.MissingVersion
		wp_enqueue_script(
			'square-web-payments-sdk',
			Helpers::is_sandbox_mode() ? 'https://sandbox.web.squarecdn.com/v1/square.js' : 'https://web.squarecdn.com/v1/square.js',
			[],
			null,
			true
		);
		// phpcs:enable WordPress.WP.EnqueuedResourceParameters.MissingVersion

		wp_enqueue_script(
			'wpforms-square',
			WPFORMS_PLUGIN_URL . "assets/js/integrations/square/wpforms-square{$min}.js",
			[ 'jquery', 'square-web-payments-sdk' ],
			WPFORMS_VERSION,
			true
		);

		/**
		 * This filter allows to set a card configuration and styles.
		 *
		 * @since 1.9.5
		 *
		 * @link https://developer.squareup.com/reference/sdks/web/payments/card-payments#Card.configure.options
		 *
		 * @param array $card_config Configuration and style options.
		 * @param array $forms       Form data of forms on current page.
		 */
		$card_config = (array) apply_filters( 'wpforms_square_frontend_enqueues_card_config', [], $forms ); // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName

		wp_localize_script(
			'wpforms-square',
			'wpforms_square',
			[
				'client_id'       => $connection->get_client_id(),
				'location_id'     => Helpers::get_location_id(),
				'card_config'     => $card_config,
				'billing_details' => $this->get_mapped_contact_fields( $forms ),
				'i18n'                 => [
					'missing_sdk_script' => esc_html__( 'Square.js failed to load properly.', 'wpforms-lite' ),
					'general_error'      => esc_html__( 'An unexpected Square SDK error has occurred.', 'wpforms-lite' ),
					'missing_creds'      => esc_html__( 'Client ID and/or Location ID is incorrect.', 'wpforms-lite' ),
					'card_init_error'    => esc_html__( 'Initializing Card failed.', 'wpforms-lite' ),
					'token_process_fail' => esc_html__( 'Tokenization of the payment card failed.', 'wpforms-lite' ),
					'token_status_error' => esc_html__( 'Tokenization failed with status:', 'wpforms-lite' ),
					'buyer_verify_error' => esc_html__( 'The verification was not successful. An issue occurred while verifying the buyer.', 'wpforms-lite' ),
					'empty_details'      => esc_html__( 'Please fill out payment details to continue.', 'wpforms-lite' ),
				],
			]
		);
	}

	/**
	 * Map provided billing details with forms on the page.
	 *
	 * @since 1.9.5
	 *
	 * @param array $forms Form data of forms on current page.
	 *
	 * @return array
	 */
	public function get_mapped_contact_fields( array $forms ): array {

		return array_map(
			function ( $form_data ) {
				return [
					'buyer_email'     => $form_data['payments']['square']['buyer_email'] ?? '',
					'billing_address' => $form_data['payments']['square']['billing_address'] ?? '',
					'billing_name'    => $form_data['payments']['square']['billing_name'] ?? '',
				];
				},
			$forms
		);
	}
}
