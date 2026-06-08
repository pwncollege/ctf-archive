<?php

namespace WPForms\Integrations\PayPalCommerce\PaymentMethods\Venmo;

use WPForms\Integrations\PayPalCommerce\Frontend\PaymentMethodFundingInterface;
use WPForms\Integrations\PayPalCommerce\Frontend\PaymentMethodInterface;

/**
 * Handles the frontend functionalities of the PayPal Commerce Venmo integration.
 *
 * @since 1.10.0
 */
class PaymentMethod implements PaymentMethodInterface, PaymentMethodFundingInterface {

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

		return ! empty( $field['paypal_checkout'] );
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

		return [];
	}

	/**
	 * Retrieves a list of disabled methods.
	 *
	 * @since 1.10.0
	 *
	 * @param bool $is_single Whether to consider One-Time Payment or Recurring. Defaults to One-Time.
	 *
	 * @return array An array of disabled method names.
	 */
	public function get_disabled_methods( bool $is_single = true ): array {

		return [];
	}

	/**
	 * Retrieves a list of enabled methods.
	 *
	 * @since 1.10.0
	 *
	 * @param bool $is_single Whether to consider One-Time Payment or Recurring. Defaults to One-Time.
	 *
	 * @return array An array of enabled method names.
	 */
	public function get_enabled_methods( bool $is_single = true ): array {

		return $is_single ? [ 'venmo' ] : [];
	}
}
