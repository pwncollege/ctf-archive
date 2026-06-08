<?php

namespace WPForms\Integrations\PayPalCommerce\PaymentMethods\RegionalMethods\Process;

use WPForms\Integrations\PayPalCommerce\PaymentMethods\RegionalMethods\Traits\WithFullContactInfo;

/**
 * Trustly payment method process implementation.
 *
 * @since 1.10.0
 */
class Trustly extends RegionalMethodProcessBase {

	use WithFullContactInfo;

	/**
	 * Represents the slug identifier.
	 *
	 * @since 1.10.0
	 */
	public const SLUG = 'trustly';

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
