<?php

namespace WPForms\Integrations\PayPalCommerce\PaymentMethods\ApplePay;

use WPForms\Integrations\PayPalCommerce\PaymentMethods\ColorMapInterface;
use WPForms\Integrations\PayPalCommerce\PaymentMethods\ColorMapTrait;

/**
 * Provides color mapping for Apple Pay buttons and logos.
 *
 * @since 1.10.0
 */
class ColorMapper implements ColorMapInterface {

	use ColorMapTrait;

	/**
	 * Returns a mapping of button colors to their actual rendered colors.
	 *
	 * @since 1.10.0
	 *
	 * @return array<string, string> An associative array mapping input colors to output colors.
	 */
	public function get_button_map(): array {

		return [
			'black'  => 'black',
			'white'  => 'white',
			'silver' => 'white',
			'gold'   => 'white',
			'blue'   => 'black',
		];
	}

	/**
	 * Returns a mapping of button colors to their corresponding logo colors.
	 *
	 * @since 1.10.0
	 *
	 * @return array<string, string> An associative array mapping button colors to logo colors.
	 */
	public function get_logo_map(): array {

		return [
			'black' => 'white',
			'white' => 'black',
		];
	}
}
