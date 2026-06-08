<?php

namespace WPForms\Forms\Fields\Address;

use WPForms\Forms\Fields\Base\Frontend as FrontendBase;

/**
 * Address field frontend class.
 *
 * @since 1.9.5
 */
class Frontend extends FrontendBase {

	/**
	 * Register hooks.
	 *
	 * @since 1.9.5
	 *
	 * @noinspection ReturnTypeCanBeDeclaredInspection
	 */
	public function hooks() {

		add_filter( 'wpforms_frontend_strings', [ $this, 'strings' ] );
		add_action( 'wpforms_wp_footer', [ $this, 'assets_footer' ], 15 );
	}

	/**
	 * Add address field related settings to a wpforms_settings array.
	 *
	 * @since 1.9.5
	 *
	 * @param array|mixed $strings The wpforms_settings array.
	 *
	 * @return array
	 */
	public function strings( $strings ): array {

		$strings = (array) $strings;

		/**
		 * Modify the list of countries without states.
		 *
		 * @since 1.9.5
		 *
		 * @param array $countries The list of country codes, defaults to [ 'GB', 'DE', 'CH', 'NL' ].
		 *
		 * @return array
		 */
		$countries = (array) apply_filters( 'wpforms_forms_fields_address_frontend_strings_list_countries_without_states', [ 'GB', 'DE', 'CH', 'NL' ] );

		$strings['address_field']['list_countries_without_states'] = array_map( 'strtoupper', $countries );

		return $strings;
	}

	/**
	 * Load the assets needed for the Address field.
	 *
	 * @since 1.9.5
	 */
	public function assets_footer(): void {

		$min = wpforms_get_min_suffix();

		wp_enqueue_script(
			'wpforms-address-field',
			WPFORMS_PLUGIN_URL . "assets/js/frontend/fields/address{$min}.js",
			[ 'wpforms' ],
			WPFORMS_VERSION,
			true
		);
	}
}
