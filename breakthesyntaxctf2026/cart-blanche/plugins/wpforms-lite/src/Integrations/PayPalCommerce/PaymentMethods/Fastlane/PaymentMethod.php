<?php

namespace WPForms\Integrations\PayPalCommerce\PaymentMethods\Fastlane;

use WPForms\Integrations\PayPalCommerce\Frontend\PaymentMethodAssetsInterface;
use WPForms\Integrations\PayPalCommerce\Frontend\PaymentMethodInterface;
use WPForms\Integrations\PayPalCommerce\PayPalCommerce;

/**
 * Handles the frontend functionalities of the PayPal Commerce integration.
 *
 * @since 1.10.0
 */
class PaymentMethod implements PaymentMethodInterface, PaymentMethodAssetsInterface {

	/**
	 * Checks if the given payment method is supported.
	 *
	 * @since 1.10.0
	 *
	 * @param array $field The field to be checked for support.
	 *
	 * @return bool True if the payment method is supported, false otherwise.
	 */
	public function is_supported( array $field ): bool {

		return ! empty( $field['fastlane'] );
	}

	/**
	 * Retrieves the list of components for integration.
	 *
	 * @since 1.10.0
	 *
	 * @param bool $is_single Whether to consider One-Time Payment or Recurring. Defaults to One-Time.
	 *
	 * @return array The list of components.
	 */
	public function get_components( bool $is_single = true ): array {

		return $is_single ? [ 'fastlane' ] : [];
	}

	/**
	 * Retrieves a list of script handles to be loaded asynchronously.
	 *
	 * @since 1.10.0
	 *
	 * @return array List of asynchronous script handles.
	 */
	public function get_async_scripts(): array {

		return [];
	}

	/**
	 * Enqueues the necessary assets for the Google Pay integration
	 * within the PayPal Commerce functionality.
	 *
	 * @since 1.10.0
	 *
	 * @param array $payment_types Array of payment type flags (e.g., ['single' => bool, 'recurring' => bool]).
	 */
	public function enqueue( array $payment_types ): void {

		$has_single = ! empty( $payment_types['single'] );

		if ( ! $has_single ) {
			return;
		}

		$min = wpforms_get_min_suffix();

		// Init fastlane.
		wp_enqueue_script(
			'wpforms-paypal-commerce-fastlane',
			WPFORMS_PLUGIN_URL . "assets/js/integrations/paypal-commerce/wpforms-paypal-commerce-fastlane{$min}.js",
			[ 'jquery', 'wpforms-paypal-commerce' ],
			WPFORMS_VERSION,
			true
		);
	}

	/**
	 * Retrieves localized settings for the specified field.
	 *
	 * @since 1.10.0
	 *
	 * @param array $field Field properties including shape and color.
	 * @param array $form  The form properties.
	 *
	 * @return array Localized settings for the given field.
	 */
	public function get_localized_settings( array $field, array $form ): array {
		// Billing address is only available in Pro.
		if ( ! wpforms()->is_pro() ) {
			return [];
		}

		$paypal_commerce = $form['payments'][ PayPalCommerce::SLUG ] ?? [];

		return [
			'fastlane' => [
				'billingAddress' => $paypal_commerce['billing_address'] ?? false,
			],
		];
	}
}
