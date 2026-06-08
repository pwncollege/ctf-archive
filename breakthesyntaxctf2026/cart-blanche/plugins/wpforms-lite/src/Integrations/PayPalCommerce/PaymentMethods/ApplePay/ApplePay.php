<?php

namespace WPForms\Integrations\PayPalCommerce\PaymentMethods\ApplePay;

use WPForms\Integrations\PayPalCommerce\PaymentMethods\ColorMapInterface;
use WPForms\Integrations\PayPalCommerce\Process\Base;

/**
 * Manages Apple Pay integration by registering the necessary hooks and adding SDK components.
 *
 * This class provides the ability to handle backend and frontend contexts, ensuring Apple Pay functionality
 * is properly initialized and integrated into the payment system.
 *
 * @since 1.10.0
 */
final class ApplePay {

	/**
	 * Color mapper instance for handling button and logo colors.
	 *
	 * @since 1.10.0
	 *
	 * @var ColorMapInterface
	 */
	private $color_mapper;

	/**
	 * Domain manager instance for handling domain verification.
	 *
	 * @since 1.10.0
	 *
	 * @var DomainManager
	 */
	private $domain_manager;

	/**
	 * Whether Apple Pay is allowed to load.
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

		$this->color_mapper   = new ColorMapper();
		$this->domain_manager = new DomainManager();

		/**
		 * Filters whether Apple Pay is allowed to load.
		 *
		 * @since 1.10.0
		 *
		 * @param bool $allowed_load Whether Apple Pay is allowed to load. Default true.
		 */
		$this->allowed_load = (bool) apply_filters( 'wpforms_integrations_paypal_commerce_payment_methods_apple_pay_allow_load', true ); // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName
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

		// Initialize domain health check.
		$this->domain_manager->init();
		$this->hooks();
	}

	/**
	 * Registers the necessary hooks based on the current request context.
	 *
	 * If in the admin context, it initializes and triggers the hooks for the Builder class.
	 * Otherwise, register the payment source with the Enqueues class.
	 *
	 * @since 1.10.0
	 */
	private function hooks(): void {

		add_action( 'wpforms_integrations_paypal_commerce_process_init', [ $this, 'register_process_method' ] );
		( new Builder( $this->color_mapper, $this->domain_manager ) )->hooks();

		if ( is_admin() ) {
			return;
		}

		( new Frontend( $this->color_mapper ) )->hooks();
	}

	/**
	 * Registers a process method to the provided process instance.
	 *
	 * @since 1.10.0
	 *
	 * @param Base $process An instance of the process to which the method will be added.
	 */
	public function register_process_method( $process ): void {

		// Only register Apple Pay if the user agent is supported.
		if ( ! ( $process instanceof Base ) || ! PaymentMethod::is_user_agent_supported() ) {
			return;
		}

		$process->add_process_method( new ProcessMethod() );
	}

	/**
	 * Gets the domain manager instance.
	 *
	 * @since 1.10.0
	 *
	 * @return DomainManager The domain manager instance.
	 */
	public function get_domain_manager(): DomainManager {

		return $this->domain_manager;
	}

	/**
	 * Checks if Apple Pay is allowed to load.
	 *
	 * @since 1.10.0
	 *
	 * @return bool True, if Apple Pay is allowed to load, false otherwise.
	 */
	public function is_allowed_load(): bool {

		return $this->allowed_load;
	}
}
