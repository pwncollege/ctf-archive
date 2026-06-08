<?php

namespace WPForms\Integrations\PayPalCommerce\PaymentMethods\RegionalMethods;

use WPForms\Integrations\PayPalCommerce\Frontend\Frontend as MainFrontend;
use WPForms\Integrations\PayPalCommerce\Process\Base;

/**
 * Regional payment methods initializer.
 *
 * Registers all regional payment methods with the PayPal Commerce process.
 *
 * @since 1.10.0
 */
final class RegionalMethods {

	/**
	 * Initialize the regional payment methods.
	 *
	 * @since 1.10.0
	 */
	public function init(): void {

		$this->hooks();
	}

	/**
	 * Registers the necessary hooks.
	 *
	 * @since 1.10.0
	 */
	private function hooks(): void {

		add_action( 'wpforms_integrations_paypal_commerce_process_init', [ $this, 'register_process_methods' ] );

		if ( is_admin() ) {
			return;
		}

		add_action( 'wpforms_integrations_paypal_commerce_frontend_init', [ $this, 'register_payment_method' ] );
	}

	/**
	 * Register all regional payment process methods.
	 *
	 * @since 1.10.0
	 *
	 * @param mixed $process The process instance.
	 */
	public function register_process_methods( $process ): void {

		if ( ! ( $process instanceof Base ) ) {
			return;
		}

		// Register payment methods with full contact info (name, country_code, email).
		$process->add_process_method( new Process\Bancontact() );
		$process->add_process_method( new Process\Ideal() );
		$process->add_process_method( new Process\MyBank() );
		$process->add_process_method( new Process\Trustly() );

		// Register payment methods with basic contact info (name, country_code).
		$process->add_process_method( new Process\Blik() );
		$process->add_process_method( new Process\Eps() );
		$process->add_process_method( new Process\Multibanco() );
		$process->add_process_method( new Process\P24() );
	}

	/**
	 * Register the Venmo payment source with the Frontend class.
	 *
	 * @since 1.10.0
	 *
	 * @param MainFrontend $frontend The Frontend instance.
	 *
	 * @noinspection PhpMissingParamTypeInspection
	 */
	public function register_payment_method( $frontend ): void {

		if ( ! ( $frontend instanceof MainFrontend ) ) {
			return;
		}

		$payment_methods = [
			new Frontend\Bankcontact(),
			new Frontend\Ideal(),
			new Frontend\MyBank(),
			new Frontend\Trustly(),
			new Frontend\Blik(),
			new Frontend\Eps(),
			new Frontend\Multibanco(),
			new Frontend\P24(),
		];

		foreach ( $payment_methods as $payment_method ) {
			$payment_method->set_currency( $frontend->get_currency() );
			$frontend->add_payment_method( $payment_method );
		}
	}
}
