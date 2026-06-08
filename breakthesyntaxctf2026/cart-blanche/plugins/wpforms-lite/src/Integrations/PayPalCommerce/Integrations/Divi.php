<?php

namespace WPForms\Integrations\PayPalCommerce\Integrations;

/**
 * Integration with Divi.
 *
 * @since 1.10.0
 */
class Divi implements IntegrationInterface {

	/**
	 * Indicate if the current integration is allowed to load.
	 *
	 * @since 1.10.0
	 *
	 * @return bool
	 */
	public function allow_load(): bool {

		return wpforms_is_divi_active();
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.10.0
	 */
	public function hooks(): void {

		add_action( 'wpforms_frontend_css', [ $this, 'frontend_styles' ], 12 );

		if ( $this->is_integration_page_loaded() ) {
			add_action( 'wp_enqueue_scripts', [ $this, 'builder_styles' ], 12 );
		}
	}

	/**
	 * Determine whether the integration page is loaded.
	 *
	 * The method should be reconsidered once the minimum core version is raised to 1.9.4.
	 *
	 * @since 1.10.0
	 *
	 * @return bool
	 */
	public function is_integration_page_loaded(): bool {

		return wpforms_is_divi_editor();
	}

	/**
	 * Load builder styles.
	 *
	 * @since 1.10.0
	 */
	public function builder_styles(): void {

		// Do not include styles if the "Include Form Styling > No Styles" is set.
		if ( wpforms_setting( 'disable-css', '1' ) === '3' ) {
			return;
		}

		$min = wpforms_get_min_suffix();

		wp_enqueue_style(
			'wpforms-paypal-commerce-divi-editor-integrations',
			WPFORMS_PLUGIN_URL . "assets/css/integrations/paypal-commerce/integrations/divi-editor-paypal-commerce{$min}.css",
			[],
			WPFORMS_VERSION
		);
	}

	/**
	 * Load frontend styles.
	 *
	 * @since 1.10.0
	 */
	public function frontend_styles(): void {

		if ( ! $this->is_divi_plugin_loaded() && ! $this->is_divi_theme_loaded() ) {
			return;
		}

		// Do not include styles if the "Include Form Styling > No Styles" is set.
		if ( wpforms_setting( 'disable-css', '1' ) === '3' ) {
			return;
		}

		$min = wpforms_get_min_suffix();

		wp_enqueue_style(
			'wpforms-paypal-commerce-divi-frontend-integrations',
			WPFORMS_PLUGIN_URL . "assets/css/integrations/paypal-commerce/integrations/divi-frontend-paypal-commerce{$min}.css",
			[],
			WPFORMS_VERSION
		);
	}

	/**
	 * Determine if the Divi Builder plugin is loaded.
	 *
	 * @since 1.10.0
	 *
	 * @return bool
	 */
	private function is_divi_plugin_loaded(): bool {

		if ( ! is_singular() ) {
			return false;
		}

		return function_exists( 'et_is_builder_plugin_active' ) && et_is_builder_plugin_active();
	}

	/**
	 * Determine if the Divi theme is loaded.
	 *
	 * @since 1.10.0
	 *
	 * @return bool
	 */
	private function is_divi_theme_loaded(): bool {

		if ( ! is_singular() ) {
			return false;
		}

		return function_exists( 'et_get_theme_version' ) && et_get_theme_version();
	}
}
