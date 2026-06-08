<?php

namespace WPForms\Integrations\PayPalCommerce\PaymentMethods\Card;

use WPForms\Integrations\PayPalCommerce\Frontend\PaymentMethodInterface;

/**
 * Handles the PayPal SDK component registration for card payments.
 *
 * @since 1.10.0
 */
class PaymentMethod implements PaymentMethodInterface {

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

		return ! empty( $field['credit_card'] );
	}

	/**
	 * Retrieves the list of SDK components for card payments.
	 *
	 * Returns only the 'hosted-fields' component which is required for
	 * secure card input fields.
	 *
	 * @since 1.10.0
	 *
	 * @param bool $is_single Whether to consider One-Time Payment or Recurring. Defaults to One-Time.
	 *
	 * @return array List of components.
	 */
	public function get_components( bool $is_single = true ): array {

		return $is_single ? [ 'hosted-fields' ] : [];
	}
}
