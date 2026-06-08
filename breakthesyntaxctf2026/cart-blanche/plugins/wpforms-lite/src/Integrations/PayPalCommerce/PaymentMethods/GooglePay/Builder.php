<?php

namespace WPForms\Integrations\PayPalCommerce\PaymentMethods\GooglePay;

use WPForms\Integrations\PayPalCommerce\PaymentMethods\ColorMapInterface;

/**
 * Handles builder functionalities for rendering Google Pay button in the form builder.
 *
 * @since 1.10.0
 */
class Builder {

	/**
	 * Color mapper instance for handling button and logo color mappings.
	 *
	 * @since 1.10.0
	 *
	 * @var ColorMapInterface
	 */
	private $color_map;

	/**
	 * Constructor.
	 *
	 * @since 1.10.0
	 *
	 * @param ColorMapInterface $color_map An instance of ColorMapInterface to be set.
	 */
	public function __construct( ColorMapInterface $color_map ) {

		$this->color_map = $color_map;
	}

	/**
	 * Registers the necessary hooks for the builder.
	 *
	 * @since 1.10.0
	 */
	public function hooks(): void {

		add_action( 'wpforms_integrations_paypal_commerce_fields_paypal_commerce_builder_submit_button', [ $this, 'render_button_container' ] );
		add_filter( 'wpforms_builder_strings', [ $this, 'javascript_strings' ] );
	}

	/**
	 * Renders Google Pay button container in the form builder.
	 *
	 * @since 1.10.0
	 *
	 * @param array $field PayPal Commerce field data.
	 */
	public function render_button_container( $field ): void {

		$field = (array) $field;

		$button_color = $this->color_map->get_button_color( $field['color'] );

		printf(
			'<div class="wpforms-paypal-commerce-button googlepay-button" data-button-color="%1$s" data-button-shape="%2$s">
				<span class="wpforms-paypal-commerce-button-logo googlepay-logo" data-logo-color="%3$s"></span>
			</div>',
			esc_attr( $button_color ),
			esc_attr( $field['shape'] ),
			esc_attr( $this->color_map->get_logo_color( $button_color ) )
		);
	}

	/**
	 * Modifies and returns an array of JavaScript strings with additional data for Apple Pay.
	 *
	 * @since 1.10.0
	 *
	 * @param array $strings An array of JavaScript strings to be modified.
	 *
	 * @return array The modified array of JavaScript strings including the Apple Pay data.
	 *
	 * @noinspection PhpMissingParamTypeInspection
	 */
	public function javascript_strings( $strings ): array {

		$strings = (array) $strings;

		$strings['paypal_commerce_methods'] = $strings['paypal_commerce_methods'] ?? [];

		$strings['paypal_commerce_methods']['googlepay'] = [
			'buttonColors' => $this->color_map->get_button_map(),
			'logoColors'   => $this->color_map->get_logo_map(),
		];

		return $strings;
	}
}
