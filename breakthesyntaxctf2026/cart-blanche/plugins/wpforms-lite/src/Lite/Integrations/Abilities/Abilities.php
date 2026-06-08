<?php

namespace WPForms\Lite\Integrations\Abilities;

use WP_Error;
use WPForms\Integrations\Abilities\Abilities as AbilitiesBase;

/**
 * WordPress Abilities API Integration for WPForms Lite.
 *
 * @since 1.9.9
 */
class Abilities extends AbilitiesBase {

	/**
	 * Register WPForms abilities for Lite version.
	 *
	 * @since 1.9.9
	 */
	public function register_abilities(): void {

		// Register common abilities (list_forms, get_form).
		$this->register_common_abilities();

		// Lite-specific: Register form stats ability (basic).
		$this->register_form_stats_ability();
	}

	/**
	 * Register the form_stats ability (Lite version - basic stats with upsell).
	 *
	 * @since 1.9.9
	 */
	protected function register_form_stats_ability(): void {

		wp_register_ability(
			self::ABILITY_NAMESPACE . '/get-form-stats',
			[
				'label'               => __( 'Get Form Stats', 'wpforms-lite' ),
				'description'         => __( 'Get basic statistics for a WPForms form. Upgrade to Pro for detailed entry data.', 'wpforms-lite' ),
				'category'            => self::CATEGORY_SLUG,
				'execute_callback'    => [ $this, 'ability_get_form_stats' ],
				'permission_callback' => [ $this, 'check_view_single_form_permission' ],
				'input_schema'        => [
					'type'       => 'object',
					'properties' => [
						'form_id' => [
							'description' => __( 'The ID of the form to get stats for.', 'wpforms-lite' ),
							'type'        => 'integer',
							'required'    => true,
							'minimum'     => 1,
						],
					],
					'required'   => [ 'form_id' ],
				],
				'output_schema'       => [
					'type'       => 'object',
					'properties' => [
						'form_id'           => [ 'type' => 'integer' ],
						'entries_available' => [ 'type' => 'boolean' ],
						'message'           => [ 'type' => 'string' ],
					],
				],
				'meta'                => [
					'annotations'  => [
						'readonly'    => true,
						'destructive' => false,
						'idempotent'  => true,
					],
					'show_in_rest' => true,
					'mcp'          => [
						'public' => true,
					],
				],
			]
		);
	}

	/**
	 * Ability callback: Get form stats (Lite version).
	 *
	 * @since 1.9.9
	 *
	 * @param mixed $input Input data.
	 *
	 * @return array|WP_Error
	 */
	public function ability_get_form_stats( $input = null ) {

		$args    = $this->normalize_input( $input );
		$form_id = absint( $args['form_id'] ?? 0 );

		$form_handler = $this->get_form_handler();

		if ( is_wp_error( $form_handler ) ) {
			return $form_handler;
		}

		$form = $form_handler->get( $form_id );

		if ( empty( $form ) ) {
			return new WP_Error(
				'wpforms_form_not_found',
				__( 'Form not found.', 'wpforms-lite' ),
				[ 'status' => 404 ]
			);
		}

		// Lite version returns limited stats with the upsell message.
		return [
			'form_id'           => $form_id,
			'entries_available' => false,
			'message'           => __( 'Entry statistics require WPForms Pro. Upgrade to access detailed form submission data.', 'wpforms-lite' ),
		];
	}
}
