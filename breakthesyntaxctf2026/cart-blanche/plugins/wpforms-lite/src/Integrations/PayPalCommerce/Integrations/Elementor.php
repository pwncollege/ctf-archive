<?php

namespace WPForms\Integrations\PayPalCommerce\Integrations;

use Elementor\Plugin as ElementorPlugin;

/**
 * Integration with Elementor.
 *
 * @since 1.10.0
 */
class Elementor implements IntegrationInterface {

	/**
	 * Indicate if the current integration is allowed to load.
	 *
	 * @since 1.10.0
	 *
	 * @return bool
	 */
	public function allow_load(): bool {

		return (bool) did_action( 'elementor/loaded' );
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.10.0
	 */
	public function hooks(): void {

		add_action( 'elementor/frontend/after_enqueue_scripts', [ $this, 'enqueue_editor_assets' ] );
	}

	/**
	 * Determine whether the integration page is loaded.
	 *
	 * @since 1.10.0
	 *
	 * @return bool
	 */
	public function is_integration_page_loaded(): bool {

		return wpforms_is_elementor_editor();
	}

	/**
	 * Load editor assets.
	 *
	 * @since 1.10.0
	 */
	public function enqueue_editor_assets(): void {

		if (
			! class_exists( ElementorPlugin::class ) ||
			empty( ElementorPlugin::instance()->preview ) ||
			! ElementorPlugin::instance()->preview->is_preview_mode()
		) {
			return;
		}

		// Do not include styles if the "Include Form Styling > No Styles" is set.
		if ( wpforms_setting( 'disable-css', '1' ) === '3' ) {
			return;
		}

		$min = wpforms_get_min_suffix();

		wp_enqueue_style(
			'wpforms-paypal-commerce-elementor-editor-integrations',
			WPFORMS_PLUGIN_URL . "assets/css/integrations/paypal-commerce/integrations/elementor-editor-paypal-commerce{$min}.css",
			[],
			WPFORMS_VERSION
		);
	}
}
