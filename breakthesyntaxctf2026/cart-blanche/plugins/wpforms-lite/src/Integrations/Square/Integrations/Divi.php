<?php

namespace WPForms\Integrations\Square\Integrations;

/**
 * Integration with Divi.
 *
 * @since 1.9.5
 */
class Divi implements IntegrationInterface {

	/**
	 * Indicate whether current integration is allowed to load.
	 *
	 * @since 1.9.5
	 *
	 * @return bool
	 */
	public function allow_load(): bool {

		return wpforms_is_divi_active();
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.9.5
	 */
	public function hooks() {

		add_action( 'wpforms_frontend_css', [ $this, 'frontend_styles' ], 12 );

		if ( $this->is_editor_page() ) {
			add_action( 'wp_enqueue_scripts', [ $this, 'editor_styles' ], 12 );
		}
	}

	/**
	 * Determine whether editor page is loaded.
	 *
	 * @since 1.9.5
	 *
	 * @return bool
	 */
	public function is_editor_page(): bool {

		return wpforms_is_divi_editor();
	}

	/**
	 * Load editor styles.
	 *
	 * @since 1.9.5
	 */
	public function editor_styles() {

		// Do not include styles if the "Include Form Styling > No Styles" is set.
		if ( wpforms_setting( 'disable-css', '1' ) === '3' ) {
			return;
		}

		$min = wpforms_get_min_suffix();

		wp_enqueue_style(
			'wpforms-square-divi-integration-card-placeholder',
			WPFORMS_PLUGIN_URL . "assets/css/integrations/square/divi/wpforms-square-card-placeholder{$min}.css",
			[],
			WPFORMS_VERSION
		);
	}

	/**
	 * Load frontend styles.
	 *
	 * @since 1.9.5
	 */
	public function frontend_styles() {

		if ( ! $this->is_divi_plugin_loaded() ) {
			return;
		}

		// Do not include styles if the "Include Form Styling > No Styles" is set.
		if ( wpforms_setting( 'disable-css', '1' ) === '3' ) {
			return;
		}

		$min = wpforms_get_min_suffix();

		wp_enqueue_style(
			'wpforms-square-divi-integration-frontend',
			WPFORMS_PLUGIN_URL . "assets/css/integrations/square/divi/wpforms-square{$min}.css",
			[],
			WPFORMS_VERSION
		);
	}

	/**
	 * Determine whether the Divi Builder plugin is loaded.
	 *
	 * @since 1.9.5
	 *
	 * @return bool
	 */
	private function is_divi_plugin_loaded(): bool {

		if ( ! is_singular() ) {
			return false;
		}

		return function_exists( 'et_is_builder_plugin_active' ) && et_is_builder_plugin_active();
	}
}
