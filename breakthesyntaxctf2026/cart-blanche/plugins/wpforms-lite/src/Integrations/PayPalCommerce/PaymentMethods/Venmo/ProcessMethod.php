<?php

namespace WPForms\Integrations\PayPalCommerce\PaymentMethods\Venmo;

use WPForms\Integrations\PayPalCommerce\Process\ProcessMethodBase;

/**
 * Represents the process implementation for payment methods.
 *
 * @since 1.10.0
 */
class ProcessMethod extends ProcessMethodBase {

	/**
	 * A constant that defines the slug identifier for Venmo.
	 *
	 * @since 1.10.0
	 */
	private const SLUG = 'venmo';

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
				'experience_context' => [
					'brand_name'          => get_bloginfo( 'name' ),
					'shipping_preference' => $this->process->get_shipping_preference( $submitted_data ),
				],
			],
		];
	}

	/**
	 * Retrieves the form field value from the provided order data.
	 *
	 * @since 1.10.0
	 *
	 * @param array $order_data The array containing order information.
	 *
	 * @return string The form field value extracted from the order data.
	 */
	public function get_form_field_value( array $order_data ): string {

		$email = $order_data['payment_source'][ self::SLUG ]['email_address'] ?? '';

		return $this->get_customer_name( $order_data ) . ( $email ? "\n" . $email : '' );
	}
}
