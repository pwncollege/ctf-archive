<?php

namespace WPForms\Admin\Settings;

/**
 * Payments setting page.
 * Settings will be accessible via “WPForms” → “Settings” → “Payments”.
 *
 * @since 1.8.2
 */
class Payments {

	/**
	 * Initialize class.
	 *
	 * @since 1.8.2
	 */
	public function init() {

		$this->hooks();
	}

	/**
	 * Hooks.
	 *
	 * @since 1.8.2
	 */
	private function hooks() {

		add_filter( 'wpforms_settings_tabs', [ $this, 'register_settings_tabs' ], 5 );
		add_filter( 'wpforms_settings_defaults', [ $this, 'register_settings_fields' ], 5 );
	}

	/**
	 * Register "Payments" settings tab.
	 *
	 * @since 1.8.2
	 *
	 * @param array $tabs Admin area tabs list.
	 *
	 * @return array
	 */
	public function register_settings_tabs( $tabs ) {

		$payments = [
			'payments' => [
				'name'   => esc_html__( 'Payments', 'wpforms-lite' ),
				'form'   => true,
				'submit' => esc_html__( 'Save Settings', 'wpforms-lite' ),
			],
		];

		return wpforms_array_insert( $tabs, $payments, 'validation' );
	}

	/**
	 * Register "Payments" settings fields.
	 *
	 * @since 1.8.2
	 *
	 * @param array $settings Admin area settings list.
	 *
	 * @return array
	 */
	public function register_settings_fields( $settings ) {

		$currency_option = [];
		$currencies      = wpforms_get_currencies();

		// Format currencies for select element.
		foreach ( $currencies as $code => $currency ) {
			$currency_option[ $code ] = sprintf( '%s (%s %s)', $currency['name'], $code, $currency['symbol'] );
		}

		$settings['payments'] = [
			'heading'  => [
				'id'       => 'payments-heading',
				'content'  => '<h4>' . esc_html__( 'Payments', 'wpforms-lite' ) . '</h4>',
				'type'     => 'content',
				'no_label' => true,
				'class'    => [ 'section-heading', 'no-desc' ],
			],
			'currency' => [
				'id'        => 'currency',
				'name'      => esc_html__( 'Currency', 'wpforms-lite' ),
				'type'      => 'select',
				'choicesjs' => true,
				'search'    => true,
				'default'   => 'USD',
				'options'   => $currency_option,
			],
		];

		return $settings;
	}
}
