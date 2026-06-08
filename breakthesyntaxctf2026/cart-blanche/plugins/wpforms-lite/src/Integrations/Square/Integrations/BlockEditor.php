<?php

namespace WPForms\Integrations\Square\Integrations;

use WPForms\Integrations\Square\Connection;
use WPForms\Integrations\Square\Helpers;

/**
 * Integration with Block Editor.
 *
 * @since 1.9.5
 */
class BlockEditor implements IntegrationInterface {

	/**
	 * Handle name for wp_register_styles handle.
	 *
	 * @since 1.9.5
	 *
	 * @var string
	 */
	const HANDLE = 'wpforms-square-card-placeholder';

	/**
	 * Indicate whether current integration is allowed to load.
	 *
	 * @since 1.9.5
	 *
	 * @return bool
	 */
	public function allow_load(): bool {

		return true;
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.9.5
	 */
	public function hooks() {

		// Field styles for Gutenberg.
		add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_assets' ] );

		// Set editor style for block type editor. Must run at 20 in add-ons.
		add_filter( 'register_block_type_args', [ $this, 'block_editor_assets' ], 20, 2 );
	}

	/**
	 * Determine whether editor page is loaded.
	 *
	 * @since 1.9.5
	 *
	 * @return bool
	 */
	public function is_editor_page(): bool {

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return defined( 'REST_REQUEST' ) && REST_REQUEST && ! empty( $_REQUEST['context'] ) && $_REQUEST['context'] === 'edit';
	}

	/**
	 * Enqueue assets.
	 *
	 * @since 1.9.5
	 */
	public function enqueue_assets() {

		$this->enqueue_css();
	}

	/**
	 * Enqueue css.
	 *
	 * @since 1.9.5
	 */
	private function enqueue_css() {
		// Do not include styles if the "Include Form Styling > No Styles" is set.
		if ( wpforms_setting( 'disable-css', '1' ) === '3' ) {
			return;
		}

		$min = wpforms_get_min_suffix();

		wp_enqueue_style(
			'wpforms-square',
			WPFORMS_PLUGIN_URL . "assets/css/integrations/square/wpforms-square{$min}.css",
			[],
			WPFORMS_VERSION
		);

		wp_enqueue_style(
			self::HANDLE,
			WPFORMS_PLUGIN_URL . "assets/css/integrations/square/wpforms-square-card-placeholder{$min}.css",
			[],
			WPFORMS_VERSION
		);
	}

	/**
	 * Set editor style for block type editor.
	 *
	 * @since 1.9.5
	 *
	 * @param array  $args       Array of arguments for registering a block type.
	 * @param string $block_type Block type name including namespace.
	 *
	 * @return array
	 */
	public function block_editor_assets( $args, string $block_type ): array {

		$args = (array) $args;

		if ( $block_type !== 'wpforms/form-selector' || ! is_admin() ) {
			return $args;
		}

		// Do not include styles if the "Include Form Styling > No Styles" is set.
		if ( wpforms_setting( 'disable-css', '1' ) === '3' ) {
			return $args;
		}

		$min = wpforms_get_min_suffix();

		wp_register_style(
			self::HANDLE,
			WPFORMS_PLUGIN_URL . "assets/css/integrations/square/wpforms-square-card-placeholder{$min}.css",
			[ $args['editor_style'] ],
			WPFORMS_VERSION
		);

		$args['editor_style'] = self::HANDLE;

		return $args;
	}
}
