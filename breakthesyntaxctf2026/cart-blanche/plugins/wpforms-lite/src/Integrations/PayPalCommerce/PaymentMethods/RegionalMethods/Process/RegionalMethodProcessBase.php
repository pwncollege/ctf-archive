<?php

namespace WPForms\Integrations\PayPalCommerce\PaymentMethods\RegionalMethods\Process;

use WPForms\Integrations\PayPalCommerce\Process\ProcessMethodBase;

/**
 * Base class for regional payment methods, extending core process methods.
 *
 * @since 1.10.0
 */
abstract class RegionalMethodProcessBase extends ProcessMethodBase {

	/**
	 * Get contact data including name, country_code, etc.
	 *
	 * @since 1.10.0
	 *
	 * @param array $submitted_data The submitted form data.
	 *
	 * @return array Contact data array.
	 */
	abstract protected function get_contact_data( array $submitted_data ): array;

	/**
	 * Get configuration on capture.
	 *
	 * @since 1.10.0
	 *
	 * @param array $submitted_data The submitted form data.
	 *
	 * @return array
	 */
	public function get_payment_source_on_create( array $submitted_data ): array {

		$settings = $this->process->get_settings();

		// Skip defining a payment source since we can't determine country code correctly.
		if ( empty( $settings['shipping_address'] ) ) {
			return [];
		}

		$payment_source = $this->get_contact_data( $submitted_data );

		// Skip defining a payment source because contact data is required.
		if ( empty( $payment_source ) ) {
			return [];
		}

		$payment_source['experience_context']                        = $payment_source['experience_context'] ?? [];
		$payment_source['experience_context']['shipping_preference'] = $this->process->get_shipping_preference( $submitted_data );

		return [
			$this->get_type() => $payment_source,
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

		$email = $order_data['payment_source'][ $this->get_type() ]['email'] ?? '';

		return $this->get_customer_name( $order_data ) . ( $email ? "\n" . $email : '' );
	}
}
