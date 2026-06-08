<?php

namespace WPForms\Integrations\Square\Integrations;

use Elementor\Plugin as ElementorPlugin;

/**
 * Integration with Elementor.
 *
 * @since 1.9.5
 */
class Elementor implements IntegrationInterface {

	/**
	 * Indicate whether current integration is allowed to load.
	 *
	 * @since 1.9.5
	 *
	 * @return bool
	 */
	public function allow_load(): bool {

		return (bool) did_action( 'elementor/loaded' );
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.9.5
	 */
	public function hooks() {

		add_action( 'elementor/frontend/after_enqueue_scripts', [ $this, 'enqueue_editor_assets' ] );
	}

	/**
	 * Determine whether editor page is loaded.
	 *
	 * @since 1.9.5
	 *
	 * @return bool
	 */
	public function is_editor_page(): bool {

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Missing
		return ( ! empty( $_POST['action'] ) && $_POST['action'] === 'elementor_ajax' ) || ( ! empty( $_GET['action'] ) && $_GET['action'] === 'elementor' );
	}

	/**
	 * Load editor assets.
	 *
	 * @since 1.9.5
	 */
	public function enqueue_editor_assets() {

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
			'wpforms-square-elementor-integration-card-placeholder',
			WPFORMS_PLUGIN_URL . "assets/css/integrations/square/wpforms-square-card-placeholder{$min}.css",
			[],
			WPFORMS_VERSION
		);
	}
}
