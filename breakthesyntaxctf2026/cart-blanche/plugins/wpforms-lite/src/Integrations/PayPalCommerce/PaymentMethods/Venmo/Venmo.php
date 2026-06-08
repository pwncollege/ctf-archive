<?php

namespace WPForms\Integrations\PayPalCommerce\PaymentMethods\Venmo;

use WPForms\Integrations\PayPalCommerce\Frontend\Frontend;
use WPForms\Integrations\PayPalCommerce\Process\Base;

/**
 * Handles the integration of Venmo as a payment method.
 *
 * This class is responsible for registering the necessary hooks and mapping the SDK component
 * to the payment methods array to enable Venmo functionality.
 *
 * @since 1.10.0
 */
final class Venmo {

	/**
	 * Initializes the necessary components and hooks for the application.
	 *
	 * @since 1.10.0
	 */
	public function init(): void {

		$this->hooks();
	}

	/**
	 * Registers the necessary hooks based on the current request context.
	 *
	 * If in the admin context, it initializes and triggers the hooks for the Builder class.
	 * Otherwise, registers the payment source with the Enqueues class.
	 *
	 * @since 1.10.0
	 */
	private function hooks(): void {

		add_action( 'wpforms_integrations_paypal_commerce_process_init', [ $this, 'register_process_method' ] );

		if ( is_admin() ) {
			return;
		}

		add_action( 'wpforms_integrations_paypal_commerce_frontend_init', [ $this, 'register_payment_method' ] );
	}

	/**
	 * Register the Venmo payment source with the Frontend class.
	 *
	 * @since 1.10.0
	 *
	 * @param Frontend $frontend The Frontend instance.
	 */
	public function register_payment_method( $frontend ): void {

		if ( ! ( $frontend instanceof Frontend ) ) {
			return;
		}

		$frontend->add_payment_method( new PaymentMethod() );
	}

	/**
	 * Registers a process method to the provided process instance.
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
}
