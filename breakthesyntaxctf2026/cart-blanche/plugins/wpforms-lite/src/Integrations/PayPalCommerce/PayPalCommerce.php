<?php

namespace WPForms\Integrations\PayPalCommerce;

use WPForms\Integrations\IntegrationInterface;
use WPForms\Integrations\PayPalCommerce\Api\Api;
use WPForms\Integrations\PayPalCommerce\Api\WebhookRoute;
use WPForms\Integrations\PayPalCommerce\Api\WebhooksManager;
use WPForms\Integrations\PayPalCommerce\Frontend\Frontend;
use WPForms\Integrations\PayPalCommerce\PaymentMethods\ApplePay\ApplePay;
use WPForms\Integrations\PayPalCommerce\PaymentMethods\Card\Card;
use WPForms\Integrations\PayPalCommerce\PaymentMethods\Fastlane\Fastlane;
use WPForms\Integrations\PayPalCommerce\PaymentMethods\GooglePay\GooglePay;
use WPForms\Integrations\PayPalCommerce\PaymentMethods\Checkout\Checkout;
use WPForms\Integrations\PayPalCommerce\PaymentMethods\RegionalMethods\RegionalMethods;
use WPForms\Integrations\PayPalCommerce\PaymentMethods\Venmo\Venmo;
use WPForms\Integrations\PayPalCommerce\Process\ProcessSingleAjax;
use WPForms\Integrations\PayPalCommerce\Process\ProcessSubscriptionAjax;
use WPForms\Integrations\PayPalCommerce\Process\ProcessSubscriptionProcessorAjax;

/**
 * PayPal Commerce integration.
 *
 * @since 1.10.0
 */
final class PayPalCommerce implements IntegrationInterface {

	/**
	 * Payment slug.
	 *
	 * @since 1.10.0
	 */
	public const SLUG = 'paypal_commerce';

	/**
	 * Determine if the integration is allowed to load.
	 *
	 * @since 1.10.0
	 *
	 * @return bool
	 *
	 * @noinspection PhpMissingReturnTypeInspection
	 * @noinspection ReturnTypeCanBeDeclaredInspection
	 */
	public function allow_load() {

		// Determine whether the PayPal Commerce addon version is compatible with the WPForms plugin version.
		$addon_compat = ( new AddonCompatibility() )->init();

		// Do not load integration if an unsupported version of the PayPal Commerce addon is active.
		if ( $addon_compat && ! $addon_compat->is_supported_version() ) {
			$addon_compat->hooks();

			return false;
		}

		/**
		 * Whether the integration is allowed to load.
		 *
		 * @since 1.10.0
		 *
		 * @param bool $is_allowed Integration loading state.
		 */
		return (bool) apply_filters( 'wpforms_integrations_paypal_commerce_allow_load', true ); // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName
	}

	/**
	 * Load the integration.
	 *
	 * @since 1.10.0
	 *
	 * @noinspection ReturnTypeCanBeDeclaredInspection
	 */
	public function load(): void {

		$this->load_admin_entries();
		$this->load_connect();
		$this->load_field();
		$this->load_builder();
		$this->load_settings();
		$this->load_payment_methods();
		$this->load_frontend();
		$this->load_integrations();
		$this->load_payments_actions();
		$this->load_process_submission();
		$this->load_webhooks();
		$this->load_system_info();

		// Bail early for paid users with active PayPal Commerce addon.
		if ( Helpers::is_pro() ) {
			return;
		}

		$this->load_processing();
		$this->load_builder_settings();
	}

	/**
	 * Load admin entries functionality.
	 *
	 * @since 1.10.0
	 */
	private function load_admin_entries(): void {

		if ( wpforms_is_admin_page( 'entries' ) ) {
			( new Admin\Entries() )->hooks();
		}
	}

	/**
	 * Load process submission functionality.
	 *
	 * @since 1.10.0
	 */
	private function load_process_submission(): void {

		if ( wp_doing_ajax() ) {
			( new ProcessSingleAjax() )->hooks();
			( new ProcessSubscriptionAjax() )->hooks();
			( new ProcessSubscriptionProcessorAjax() )->hooks();
		}
	}

	/**
	 * Load settings page functionality.
	 *
	 * @since 1.10.0
	 */
	private function load_settings(): void {

		if ( wpforms_is_admin_page( 'settings', 'payments' ) ) {
			( new Admin\Settings() )->init();
			( new Admin\Notices() )->init();
		}
	}

	/**
	 * Load connect handler.
	 *
	 * @since 1.10.0
	 */
	private function load_connect(): void {

		( new Admin\Connect() )->init();
	}

	/**
	 * Load field functionality.
	 *
	 * @since 1.10.0
	 */
	private function load_field(): void {

		( new Fields\PayPalCommerce() );
	}

	/**
	 * Load builder functionality.
	 *
	 * @since 1.10.0
	 */
	private function load_builder(): void {

		if ( wp_doing_ajax() || wpforms_is_admin_page( 'builder' ) ) {
			( new Admin\Builder\Enqueues() )->init();
			( new Admin\Builder() )->hooks();
		}
	}

	/**
	 * Load frontend functionality.
	 *
	 * @since 1.10.0
	 */
	private function load_frontend(): void {

		if ( ! is_admin() ) {
			( new Frontend() )->init();
		}
	}

	/**
	 * Load processing functionality.
	 *
	 * @since 1.10.0
	 */
	private function load_processing(): void {

		if ( ! is_admin() || wpforms_is_frontend_ajax() ) {
			( new Process\Process() )->hooks();
		}
	}

	/**
	 * Load builder settings functionality.
	 *
	 * @since 1.10.0
	 */
	private function load_builder_settings(): void {

		if ( wpforms_is_admin_page( 'builder' ) ) {
			( new Admin\Builder\Settings() )->init();
			( new Admin\Builder\Notifications() )->init();
		}
	}

	/**
	 * Load payments actions.
	 *
	 * @since 1.10.0
	 */
	private function load_payments_actions(): void {

		if ( ! Connection::get() ) {
			return;
		}

		( new Admin\Payments\SingleActionsHandler() )->init();
	}

	/**
	 * Load integrations.
	 *
	 * @since 1.10.0
	 */
	private function load_integrations(): void {

		( new Integrations\Loader() );
	}

	/**
	 * Initializes the payment methods.
	 *
	 * @since 1.10.0
	 */
	private function load_payment_methods(): void {

		( new Card() )->init();
		( new ApplePay() )->init();
		( new GooglePay() )->init();
		( new Fastlane() )->init();
		( new Venmo() )->init();
		( new RegionalMethods() )->init();
		( new Checkout() )->init();
	}

	/**
	 * Load the webhooks functionality.
	 *
	 * @since 1.10.0
	 */
	private function load_webhooks(): void {

		( new WebhookRoute() )->init();
		( new WebhooksHealthCheck() )->init();
	}

	/**
	 * Load system info functionality.
	 *
	 * @since 1.10.0
	 */
	private function load_system_info(): void {

		if ( wpforms_is_admin_page( 'tools', 'system' ) ) {
			( new Admin\SystemInfo() )->init();
		}
	}

	/**
	 * Get the correct API instance.
	 *
	 * @since 1.10.0
	 *
	 * @param mixed $connection Connection instance.
	 *
	 * @return Api|\WPFormsPaypalCommerce\Api\Api
	 */
	public static function get_api( $connection ) {

		if ( Helpers::is_pro() && Helpers::is_legacy() ) {
			return wpforms_paypal_commerce()->get_api( $connection );
		}

		return new Api( $connection );
	}

	/**
	 * Get the correct Webhooks Manager instance.
	 *
	 * @since 1.10.0
	 *
	 * @return WebhooksManager|\WPFormsPaypalCommerce\Api\WebhooksManager
	 */
	public static function get_webhooks_manager() {

		if ( Helpers::is_pro() && Helpers::is_legacy() ) {
			return wpforms_paypal_commerce()->get_webhooks_manager();
		}

		return new WebhooksManager();
	}
}
