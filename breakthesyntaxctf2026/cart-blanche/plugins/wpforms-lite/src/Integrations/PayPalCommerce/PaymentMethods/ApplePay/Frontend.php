<?php

namespace WPForms\Integrations\PayPalCommerce\PaymentMethods\ApplePay;

use WPForms\Integrations\PayPalCommerce\Connection;
use WPForms\Integrations\PayPalCommerce\PaymentMethods\ColorMapInterface;
use WPForms\Integrations\PayPalCommerce\Frontend\Frontend as MainFrontend;

/**
 * Handles frontend functionalities for rendering and managing PayPal integration.
 *
 * @since 1.10.0
 */
class Frontend {

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

		add_action( 'wpforms_integrations_paypal_commerce_fields_paypal_commerce_single_submit_button', [ $this, 'render_button_container' ], 10, 2 );
		add_action( 'wpforms_integrations_paypal_commerce_frontend_init', [ $this, 'register_payment_method' ] );
	}

	/**
	 * Renders Apple Pay button container.
	 *
	 * @since 1.10.0
	 *
	 * @param array                                        $field      Current field specific data.
	 * @param Connection|\WPFormsPaypalCommerce\Connection $connection PayPal connection data.
	 *
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function render_button_container( $field, $connection ): void { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed

		$field = (array) $field;

		$is_rect  = $field['shape'] === 'rect';
		$css_vars = [
			[ '--apple-pay-button-border-radius', $is_rect ? '4px' : '23px' ],
		];
		$css_vars = array_map(
			static function ( $css_rule ) {

				return sprintf( '%s: %s;', $css_rule[0], $css_rule[1] );
			},
			$css_vars
		);

		printf( '<div class="wpforms-paypal-commerce-applepay-button wpforms-hidden" style="%s"></div>', esc_attr( implode( ' ', $css_vars ) ) );
	}

	/**
	 * Register the Apple Pay payment source with the Frontend class.
	 *
	 * @since 1.10.0
	 *
	 * @param MainFrontend $frontend The Frontend instance.
	 */
	public function register_payment_method( $frontend ): void {

		if ( ! ( $frontend instanceof MainFrontend ) ) {
			return;
		}

		$frontend->add_payment_method( new PaymentMethod( $this->color_map ) );
	}
}
