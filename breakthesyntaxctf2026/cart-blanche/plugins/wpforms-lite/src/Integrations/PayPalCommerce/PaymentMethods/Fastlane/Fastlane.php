<?php

namespace WPForms\Integrations\PayPalCommerce\PaymentMethods\Fastlane;

use WPForms\Integrations\PayPalCommerce\Frontend\Frontend;

/**
 * Initializes the necessary components and hooks for the application.
 *
 * @since 1.10.0
 */
final class Fastlane {

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
	 * Registers the payment source with the Enqueues class on the frontend.
	 *
	 * @since 1.10.0
	 */
	private function hooks(): void {

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
