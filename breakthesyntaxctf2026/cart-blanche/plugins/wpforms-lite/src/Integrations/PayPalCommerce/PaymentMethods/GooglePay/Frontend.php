<?php

namespace WPForms\Integrations\PayPalCommerce\PaymentMethods\GooglePay;

use WPForms\Integrations\PayPalCommerce\Connection;
use WPForms\Integrations\PayPalCommerce\Frontend\Frontend as MainFrontend;
use WPForms\Integrations\PayPalCommerce\PaymentMethods\ColorMapInterface;

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
	 * Renders the Google Pay button container.
	 *
	 * @since 1.10.0
	 *
	 * @param array                                        $field      Current field specific data.
	 * @param Connection|\WPFormsPaypalCommerce\Connection $connection PayPal connection data.
	 *
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function render_button_container( $field, $connection ): void { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed

		echo '<div class="wpforms-paypal-commerce-googlepay-button wpforms-hidden"></div>';
	}

	/**
	 * Register the Google Pay payment source with the Frontend class.
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
