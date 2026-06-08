<?php

namespace WPForms\Integrations\PayPalCommerce\PaymentMethods\Checkout;

use WPForms\Integrations\PayPalCommerce\PaymentMethods\ColorMapInterface;
use WPForms\Integrations\PayPalCommerce\PaymentMethods\ColorMapTrait;

/**
 * Provides color mapping for Checkout buttons and logos.
 *
 * @since 1.10.0
 */
class ColorMapper implements ColorMapInterface {

	use ColorMapTrait;

	/**
	 * Get a button color map.
	 *
	 * Maps button style colors to actual button colors for Checkout.
	 *
	 * @since 1.10.0
	 *
	 * @return array<string, string> Button color map.
	 */
	public function get_button_map(): array {

		return [];
	}

	/**
	 * Get logo color map.
	 *
	 * Maps button colors to logo colors for Checkout.
	 *
	 * @since 1.10.0
	 *
	 * @return array<string, string> Logo color map.
	 */
	public function get_logo_map(): array {

		return [
			'black'  => 'white',
			'blue'   => 'white',
			'white'  => 'blue',
			'gold'   => 'blue',
			'silver' => 'blue',
		];
	}
}
