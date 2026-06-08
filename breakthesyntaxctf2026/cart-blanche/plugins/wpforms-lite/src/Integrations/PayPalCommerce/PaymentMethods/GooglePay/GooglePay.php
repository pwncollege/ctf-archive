<?php

namespace WPForms\Integrations\PayPalCommerce\PaymentMethods\GooglePay;

use WPForms\Integrations\PayPalCommerce\PaymentMethods\ColorMapInterface;
use WPForms\Integrations\PayPalCommerce\Process\Base;

/**
 * Handles the integration of Google Pay as a payment method.
 *
 * This class is responsible for registering the necessary hooks and mapping the SDK component
 * to the payment methods array to enable Google Pay functionality.
 *
 * @since 1.10.0
 */
final class GooglePay {

	/**
	 * Color mapper instance for handling button and logo color mappings.
	 *
	 * @since 1.10.0
	 *
	 * @var ColorMapInterface
	 */
	private $color_map;

	/**
	 * Whether Google Pay is allowed to load.
	 *
	 * @since 1.10.0
	 *
	 * @var bool
	 */
	private $allowed_load;

	/**
	 * Constructor.
	 *
	 * @since 1.10.0
	 */
	public function __construct() {

		$this->color_map = new ColorMapper();

		/**
		 * Filters whether the Google Pay is allowed to load.
		 *
		 * @since 1.10.0
		 *
		 * @param bool $allowed_load Whether Google Pay is allowed to load. Default true.
		 */
		$this->allowed_load = (bool) apply_filters( 'wpforms_integrations_paypal_commerce_payment_methods_google_pay_allow_load', true ); // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName
	}

	/**
	 * Initializes the necessary components and hooks for the application.
	 *
	 * @since 1.10.0
	 */
	public function init(): void {

		if ( ! $this->is_allowed_load() ) {
			return;
		}

		$this->hooks();
	}

	/**
	 * Registers the necessary hooks.
	 *
	 * @since 1.10.0
	 */
	private function hooks(): void {

		add_action( 'wpforms_integrations_paypal_commerce_process_init', [ $this, 'register_process_method' ] );
		( new Builder( $this->color_map ) )->hooks();

		if ( is_admin() ) {
			return;
		}

		( new Frontend( $this->color_map ) )->hooks();
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

	/**
	 * Checks if Google Pay is allowed to load.
	 *
	 * @since 1.10.0
	 *
	 * @return bool True if Google Pay is allowed to load, false otherwise.
	 */
	public function is_allowed_load(): bool {

		return $this->allowed_load;
	}
}
