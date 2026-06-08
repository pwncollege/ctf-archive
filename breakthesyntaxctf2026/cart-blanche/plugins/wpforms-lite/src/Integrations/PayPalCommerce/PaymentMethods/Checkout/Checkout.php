<?php

namespace WPForms\Integrations\PayPalCommerce\PaymentMethods\Checkout;

use WPForms\Integrations\PayPalCommerce\Connection;
use WPForms\Integrations\PayPalCommerce\Frontend\Frontend;
use WPForms\Integrations\PayPalCommerce\PaymentMethods\ColorMapInterface;
use WPForms\Integrations\PayPalCommerce\Process\Base;

/**
 * Initializes the necessary components and hooks for the application.
 *
 * @since 1.10.0
 */
final class Checkout {

	/**
	 * Color mapper instance for handling button and logo color mappings.
	 *
	 * @since 1.10.0
	 *
	 * @var ColorMapInterface
	 */
	private $color_map;

	/**
	 * The currency used for transactions or pricing within the application.
	 *
	 * @since 1.10.0
	 *
	 * @var string
	 */
	private $currency;

	/**
	 * Constructor.
	 *
	 * @since 1.10.0
	 */
	public function __construct() {

		$this->color_map = new ColorMapper();
		$this->currency  = strtolower( wpforms_get_currency() );
	}

	/**
	 * Initializes the necessary components and hooks for the application.
	 *
	 * @since 1.10.0
	 */
	public function init(): void {

		$this->hooks();
		( new Builder( $this->color_map ) )->hooks();
	}

	/**
	 * Registers the necessary hooks based on the current request context.
	 *
	 * Registers the payment source with the Enqueues class on the frontend.
	 *
	 * @since 1.10.0
	 */
	private function hooks(): void {

		add_action( 'wpforms_integrations_paypal_commerce_process_init', [ $this, 'register_process_method' ] );

		if ( is_admin() ) {
			return;
		}

		add_action( 'wpforms_integrations_paypal_commerce_frontend_init', [ $this, 'register_payment_method' ] );
		add_action( 'wpforms_integrations_paypal_commerce_fields_paypal_commerce_single_submit_button', [ $this, 'render_pay_later_messages_container' ], 10, 2 );
	}

	/**
	 * Registers the checkout process method to the provided process instance.
	 *
	 * @since 1.10.0
	 *
	 * @param Base $process An instance of the process to which the method will be added.
	 */
	public function register_process_method( $process ): void {

		if ( ! ( $process instanceof Base ) ) {
			return;
		}

		$process->add_process_method( new ProcessMethod() );
	}

	/**
	 * Register the Venmo payment source with the Frontend class.
	 *
	 * @since 1.10.0
	 *
	 * @param Frontend $frontend The Frontend instance.
	 *
	 * @noinspection PhpMissingParamTypeInspection
	 */
	public function register_payment_method( $frontend ): void {

		if ( ! ( $frontend instanceof Frontend ) ) {
			return;
		}

		$frontend->add_payment_method( new PaymentMethod() );
	}

	/**
	 * Render the Pay Later messages container.
	 *
	 * This method outputs the HTML container for displaying Pay Later messages.
	 *
	 * @since 1.10.0
	 *
	 * @param array                                        $field      The field configuration array.
	 * @param Connection|\WPFormsPaypalCommerce\Connection $connection The connection instance.
	 *
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function render_pay_later_messages_container( array $field, $connection ): void { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed

		if ( $this->currency !== 'usd' ) {
			return;
		}

		echo '<div class="wpforms-paypal-commerce-messages"></div>';
	}
}
