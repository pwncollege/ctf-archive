<?php

namespace WPForms\Integrations\PayPalCommerce\Integrations;

/**
 * Integration with Block Editor.
 *
 * @since 1.10.0
 */
class BlockEditor implements IntegrationInterface {

	/**
	 * Handle name for wp_register_styles handle.
	 *
	 * @since 1.10.0
	 *
	 * @var string
	 */
	private const HANDLE = 'wpforms-paypal-commerce-integrations';

	/**
	 * Indicate if the current integration is allowed to load.
	 *
	 * @since 1.10.0
	 *
	 * @return bool
	 */
	public function allow_load(): bool {

		return true;
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.10.0
	 */
	public function hooks(): void {

		// Set editor style for block type editor. Must run at 20 in add-ons.
		add_filter( 'register_block_type_args', [ $this, 'block_editor_assets' ], 20, 2 );
	}

	/**
	 * Determine whether the integration page is loaded.
	 *
	 * @since 1.10.0
	 *
	 * @return bool
	 */
	public function is_integration_page_loaded(): bool {

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return defined( 'REST_REQUEST' ) && REST_REQUEST && ! empty( $_REQUEST['context'] ) && $_REQUEST['context'] === 'edit';
	}

	/**
	 * Set editor style for block type editor.
	 *
	 * @since 1.10.0
	 *
	 * @param array  $args       Array of arguments for registering a block type.
	 * @param string $block_type Block type name including namespace.
	 *
	 * @return array
	 *
	 * @noinspection PhpMissingParamTypeInspection
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
			WPFORMS_PLUGIN_URL . "assets/css/integrations/paypal-commerce/integrations/integrations-paypal-commerce{$min}.css",
			[ $args['editor_style'] ],
			WPFORMS_VERSION
		);

		$args['editor_style'] = self::HANDLE;

		return $args;
	}
}
