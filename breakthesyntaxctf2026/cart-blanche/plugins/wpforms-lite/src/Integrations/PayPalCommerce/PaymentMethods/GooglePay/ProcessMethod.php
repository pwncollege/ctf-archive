<?php

namespace WPForms\Integrations\PayPalCommerce\PaymentMethods\GooglePay;

use WPForms\Integrations\PayPalCommerce\Process\ProcessMethodBase;

/**
 * Represents the process implementation for payment methods.
 *
 * @since 1.10.0
 */
class ProcessMethod extends ProcessMethodBase {

	/**
	 * A constant that defines the slug identifier for Google Pay.
	 *
	 * @since 1.10.0
	 */
	private const SLUG = 'google_pay';

	/**
	 * Retrieves the method type slug.
	 *
	 * @since 1.10.0
	 *
	 * @return string The method type slug.
	 */
	public function get_type(): string {

		return self::SLUG;
	}

	/**
	 * Retrieves the configuration settings applied during capture.
	 *
	 * @since 1.10.0
	 *
	 * @param array $submitted_data The submitted form data.
	 *
	 * @return array The configuration array for capture settings.
	 */
	public function get_payment_source_on_create( array $submitted_data ): array {

		return [
			self::SLUG => [
				'attributes' => [
					'verification' => [
						'method' => 'SCA_WHEN_REQUIRED',
					],
				],
			],
		];
	}
}
