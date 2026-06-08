<?php

// phpcs:disable Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpIllegalPsrClassPathInspection */
/** @noinspection AutoloadingIssuesInspection */
// phpcs:enable Generic.Commenting.DocComment.MissingShort

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPForms\Integrations\ConstantContact\V3\ConstantContact;

/**
 * Load the providers.
 *
 * @since 1.3.6
 */
class WPForms_Providers {

	/**
	 * Primary class constructor.
	 *
	 * @since 1.3.6
	 */
	public function __construct() {

		$this->init();
	}

	/**
	 * Load and init the base provider class.
	 *
	 * @since 1.3.6
	 */
	public function init() { // phpcs:ignore WPForms.PHP.HooksMethod.InvalidPlaceForAddingHooks

		// Parent class template.
		require_once WPFORMS_PLUGIN_DIR . 'includes/providers/class-base.php';

		// Load default templates on WP init.
		add_action( 'wpforms_loaded', [ $this, 'load' ] );
	}

	/**
	 * Load default marketing providers.
	 *
	 * @since 1.3.6
	 */
	public function load() {

		$providers = [];

		if ( ConstantContact::get_current_version() === 2 ) {
			$providers[] = 'constant-contact';
		}

		/**
		 * Allow third-party plugins to load their own providers.
		 *
		 * @since 1.7.0
		 *
		 * @param array $providers Array of providers to load.
		 */
		$providers = (array) apply_filters( 'wpforms_load_providers', $providers ); // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName

		foreach ( $providers as $provider ) {

			$provider = sanitize_file_name( $provider );
			$path     = WPFORMS_PLUGIN_DIR . 'includes/providers/class-' . $provider . '.php';

			if ( file_exists( $path ) ) {
				require_once $path;
			}

			/**
			 * Allow third-party plugins to load their own providers.
			 *
			 * @since 1.7.0
			 */
			do_action( "wpforms_load_{$provider}_provider" ); // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName
		}
	}
}

new WPForms_Providers();
