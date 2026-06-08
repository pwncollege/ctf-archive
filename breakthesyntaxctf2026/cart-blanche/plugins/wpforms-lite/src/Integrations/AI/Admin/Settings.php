<?php

namespace WPForms\Integrations\AI\Admin;

use WPForms\Integrations\AI\Helpers;

/**
 * AI Settings class.
 *
 * @since 1.9.1
 */
class Settings {

	/**
	 * Initialize.
	 *
	 * @since 1.9.1
	 */
	public function init() {

		$this->hooks();
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.9.1
	 */
	private function hooks() {

		add_filter( 'wpforms_settings_defaults', [ $this, 'register_settings' ] );
	}

	/**
	 * Add toggle to the Settings > Misc admin page.
	 *
	 * @since 1.9.1
	 *
	 * @param array|mixed $settings WPForms settings.
	 *
	 * @return array
	 */
	public function register_settings( $settings ): array {

		$settings = (array) $settings;

		$ai_settings = [
			'id'       => Helpers::DISABLE_KEY,
			'name'     => esc_html__( 'Hide AI Features', 'wpforms-lite' ),
			'desc'     => esc_html__( 'Hide everything related to AI in WPForms.', 'wpforms-lite' ),
			'type'     => 'toggle',
			'status'   => true,
			'value'    => Helpers::is_disabled(),
			'disabled' => Helpers::is_disabled_by_rule(),
		];

		if ( $ai_settings['disabled'] ) {
			$ai_settings['disabled_desc'] = wp_kses(
				__( '<strong>AI features were hidden by filter or constant.</strong>', 'wpforms-lite' ), // phpcs:ignore WordPress.WP.I18n.NoHtmlWrappedStrings
				[
					'strong' => [],
				]
			);
		}

		// Add after the "Hide Admin Bar Menu" toggle.
		$settings['misc'] = wpforms_array_insert( $settings['misc'], [ Helpers::DISABLE_KEY => $ai_settings ], 'hide-admin-bar' );

		return $settings;
	}
}
