<?php

namespace WPForms\Integrations\WPCode;

use WPForms\Integrations\IntegrationInterface;

/**
 * Register the WPCode library username.
 *
 * @since 1.8.5
 */
class RegisterLibrary implements IntegrationInterface {

	/**
	 * Determine if the class is allowed to load.
	 *
	 * @since 1.8.5
	 *
	 * @return bool
	 * @noinspection  PhpMissingReturnTypeInspection
	 * @noinspection  ReturnTypeCanBeDeclaredInspection
	 */
	public function allow_load() {

		return is_admin();
	}

	/**
	 * Load the class.
	 *
	 * @since 1.8.5
	 */
	public function load() {

		$this->hooks();
	}

	/**
	 * Hooks.
	 *
	 * @since 1.8.5
	 */
	private function hooks() {

		add_action( 'plugins_loaded', [ $this, 'wpforms_register_wpcode_username' ], 20 );
	}

	/**
	 * Register a WPCode Library username so that it's loaded in the library inside the WPCode plugin.
	 *
	 * @since 1.8.5
	 */
	public function wpforms_register_wpcode_username() {

		if ( ! function_exists( 'wpcode_register_library_username' ) ) {
			return;
		}

		wpcode_register_library_username( 'wpforms', 'WPForms' );
	}
}
