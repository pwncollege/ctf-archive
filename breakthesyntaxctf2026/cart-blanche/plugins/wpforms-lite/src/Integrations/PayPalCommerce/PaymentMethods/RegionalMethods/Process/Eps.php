<?php

namespace WPForms\Integrations\PayPalCommerce\PaymentMethods\RegionalMethods\Process;

use WPForms\Integrations\PayPalCommerce\PaymentMethods\RegionalMethods\Traits\WithBasicContactInfo;

/**
 * EPS payment method process implementation.
 *
 * @since 1.10.0
 */
class Eps extends RegionalMethodProcessBase {

	use WithBasicContactInfo;

	/**
	 * Represents the slug identifier.
	 *
	 * @since 1.10.0
	 */
	public const SLUG = 'eps';

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
