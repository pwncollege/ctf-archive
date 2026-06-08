<?php

namespace WPForms\Integrations\PayPalCommerce\PaymentMethods\RegionalMethods\Process;

use WPForms\Integrations\PayPalCommerce\PaymentMethods\RegionalMethods\Traits\WithFullContactInfo;

/**
 * Przelewy24 (P24) payment method process implementation.
 *
 * @since 1.10.0
 */
class P24 extends RegionalMethodProcessBase {

	use WithFullContactInfo;

	/**
	 * Represents the slug identifier.
	 *
	 * @since 1.10.0
	 */
	public const SLUG = 'p24';

	/**
	 * Get the method type.
	 *
	 * @since 1.10.0
	 *
	 * @return string
	 */
	public function get_type(): string {

		return self::SLUG;
	}
}
