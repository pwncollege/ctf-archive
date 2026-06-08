<?php

namespace WPForms\Integrations\PayPalCommerce\PaymentMethods\Card;

use WPForms\Integrations\PayPalCommerce\Frontend\Frontend;
use WPForms\Integrations\PayPalCommerce\Process\Base;

/**
 * Manages Card payment integration for PayPal Commerce.
 *
 * This class provides card payment functionality through PayPal's Hosted Fields,
 * allowing users to enter credit/debit card information securely.
 *
 * @since 1.10.0
 */
final class Card {

	/**
	 * Initializes the necessary components and hooks for the card payment method.
	 *
	 * @since 1.10.0
	 */
	public function init(): void {

		$this->hooks();
	}

	/**
	 * Registers the necessary hooks for card payment processing.
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
	 * Registers the card process method to the provided process instance.
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
}
