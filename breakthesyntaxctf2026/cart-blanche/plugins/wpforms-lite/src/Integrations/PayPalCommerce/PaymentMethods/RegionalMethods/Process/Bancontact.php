<?php

namespace WPForms\Integrations\PayPalCommerce\PaymentMethods\RegionalMethods\Process;

use WPForms\Integrations\PayPalCommerce\PaymentMethods\RegionalMethods\Traits\WithFullContactInfo;

/**
 * Bancontact payment method process implementation.
 *
 * @since 1.10.0
 */
class Bancontact extends RegionalMethodProcessBase {

	use WithFullContactInfo;

	/**
	 * Represents the slug identifier.
	 *
	 * @since 1.10.0
	 */
	public const SLUG = 'bancontact';

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
