<?php

namespace WPForms\Integrations\PayPalCommerce\PaymentMethods\RegionalMethods\Frontend;

use WPForms\Integrations\PayPalCommerce\PaymentMethods\RegionalMethods\Process;

/**
 * Handles the frontend functionalities of the PayPal Commerce integration.
 *
 * @since 1.10.0
 */
class Blik extends RegionalPaymentMethodBase {

	/**
	 * Get the list of supported currency codes for this payment method.
	 *
	 * @since 1.10.0
	 *
	 * @return array List of supported currency codes.
	 */
	public function get_supported_currencies(): array {

		return [ 'PLN' ];
	}

	/**
	 * Get the payment method type identifier.
	 *
	 * @since 1.10.0
	 *
	 * @return string Payment method type.
	 */
	public function get_type(): string {

		return Process\Blik::SLUG;
	}
}
