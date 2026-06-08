<?php

namespace WPForms\Integrations\Square\Admin\Builder;

use WPForms\Integrations\Square\Helpers;

/**
 * Script enqueues for the Square Builder settings panel.
 *
 * @since 1.9.5
 */
class Enqueues {

	/**
	 * Initialize.
	 *
	 * @since 1.9.5
	 */
	public function init() {

		$this->hooks();
	}

	/**
	 * Builder hooks.
	 *
	 * @since 1.9.5
	 */
	private function hooks() {

		add_action( 'wpforms_builder_enqueues', [ $this, 'enqueues' ] );
		add_filter( 'wpforms_builder_strings',  [ $this, 'javascript_strings' ] );
	}

	/**
	 * Enqueue assets for the builder.
	 *
	 * @since 1.9.5
	 */
	public function enqueues() {

		$min = wpforms_get_min_suffix();

		wp_enqueue_script(
			'wpforms-builder-square',
			WPFORMS_PLUGIN_URL . "assets/js/integrations/square/admin/builder-square{$min}.js",
			[ 'wpforms-builder' ],
			WPFORMS_VERSION,
			true
		);

		wp_enqueue_style(
			'wpforms-square-placeholder',
			WPFORMS_PLUGIN_URL . "assets/css/integrations/square/wpforms-square-card-placeholder{$min}.css",
			[],
			WPFORMS_VERSION
		);

		/**
		 * Currently, we would like to limit number of the Square Credit Card fields
		 * and allows only one field per form cause there is technical issue with the Web Payments SDK.
		 */
		wp_add_inline_style( 'wpforms-builder', '.wpforms-add-fields-group .wpforms-add-fields-button-disabled:hover { background-color: #036aab; cursor: no-drop; }' );
	}

	/**
	 * Add localized strings to be available in the form builder.
	 *
	 * @since 1.9.5
	 *
	 * @param array $strings Form builder JS strings.
	 *
	 * @return array
	 */
	public function javascript_strings( $strings ): array {

		$strings = (array) $strings;

		$strings['square_connection_required'] = wp_kses(
			__( '<p>Square account connection is required when using the Square field.</p><p>To proceed, please go to <strong>WPForms Settings » Payments » Square</strong> and press <strong>Connect with Square</strong> button.</p>', 'wpforms-lite' ),
			[
				'p'      => [],
				'strong' => [],
			]
		);

		$strings['square_payments_enabled_required'] = wp_kses(
			__( '<p>Square Payments must be enabled when using the Square field.</p><p>To proceed, please go to <strong>Payments » Square</strong> and check <strong>Enable Square Payments</strong>.</p>', 'wpforms-lite' ),
			[
				'p'      => [],
				'strong' => [],
			]
		);

		$strings['square_ajax_required'] = wp_kses(
			__( '<p>AJAX form submissions are required when using the Square field.</p><p>To proceed, please go to <strong>Settings » General » Advanced</strong> and check <strong>Enable AJAX form submission</strong>.</p>', 'wpforms-lite' ),
			[
				'p'      => [],
				'strong' => [],
			]
		);

		$strings['square_recurring_payments_fields_heading']  = esc_html__( 'Missing Required Fields', 'wpforms-lite' );
		$strings['square_recurring_payments_fields_required'] = esc_html__( 'When recurring subscription payments are enabled, the Customer Email and Customer Name are required.', 'wpforms-lite' );
		$strings['square_recurring_payments_fields_settings'] = wp_kses(
			__( 'Please go to the <a href="#" class="wpforms-square-settings-redirect">Square payment settings</a> and select a Customer Email and Customer Name.', 'wpforms-lite' ),
			[
				'a' => [
					'href'  => [],
					'class' => [],
				],
			]
		);
		$strings['square_is_pro']                             = Helpers::is_pro();

		return $strings;
	}
}
