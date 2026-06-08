<?php

namespace WPForms\Lite\Integrations\Gutenberg;

use WPForms\Integrations\Gutenberg\FormSelector as FormSelectorBase;
use WPForms\Integrations\Gutenberg\RestApi;

/**
 * Gutenberg block for Lite.
 *
 * @since 1.8.8
 */
class FormSelector extends FormSelectorBase {

	/**
	 * Load an integration.
	 *
	 * @since 1.8.8
	 */
	public function load() {

		$this->themes_data_obj = new ThemesData();

		parent::load();
	}

	/**
	 * Integration hooks.
	 *
	 * @since 1.8.8
	 */
	protected function hooks() {

		add_action( 'rest_api_init', [ $this, 'init_rest' ] );

		parent::hooks();
	}

	/**
	 * Initialize rest API.
	 *
	 * @since 1.8.8
	 */
	public function init_rest() {

		if ( ! $this->rest_api_obj ) {
			$this->rest_api_obj = new RestApi( $this, $this->themes_data_obj );
		}
	}

	/**
	 * Register WPForms Gutenberg block styles.
	 *
	 * @since 1.8.8
	 */
	protected function register_styles() {

		if ( ! is_admin() ) {
			return;
		}

		parent::register_styles();

		// FontAwesome.
		wp_enqueue_style(
			'wpforms-font-awesome',
			WPFORMS_PLUGIN_URL . 'assets/lib/font-awesome/css/all.min.css',
			null,
			'7.0.1'
		);

		// FontAwesome v4 compatibility shims.
		wp_enqueue_style(
			'wpforms-font-awesome-v4-shim',
			WPFORMS_PLUGIN_URL . 'assets/lib/font-awesome/css/v4-shims.min.css',
			null,
			'4.7.0'
		);
	}

	/**
	 * Load WPForms Gutenberg block scripts.
	 *
	 * @since 1.8.8
	 */
	public function enqueue_block_editor_assets() {

		parent::enqueue_block_editor_assets();

		$min = wpforms_get_min_suffix();

		wp_enqueue_script(
			'wpforms-generic-utils',
			WPFORMS_PLUGIN_URL . "assets/js/share/utils{$min}.js",
			[ 'jquery' ],
			WPFORMS_VERSION,
			true
		);

		if ( ! $this->is_legacy_block() ) {
			wp_enqueue_script(
				'wpforms-gutenberg-form-selector',
				WPFORMS_PLUGIN_URL . "assets/lite/js/integrations/gutenberg/formselector.es5{$min}.js",
				[ 'wp-blocks', 'wp-i18n', 'wp-element', 'jquery', 'wpforms-admin-education-core', 'wpforms-generic-utils' ],
				WPFORMS_VERSION,
				true
			);
		}

		wp_localize_script(
			'wpforms-gutenberg-form-selector',
			'wpforms_gutenberg_form_selector',
			$this->get_localize_data()
		);
	}
}
