<?php

namespace WPForms\Integrations\PayPalCommerce\PaymentMethods\Checkout;

use WPForms\Integrations\PayPalCommerce\Process\ProcessHelper;
use WPForms\Integrations\PayPalCommerce\Process\ProcessMethodBase;

/**
 * Represents the process implementation for PayPal Checkout payment method.
 *
 * @since 1.10.0
 */
class ProcessMethod extends ProcessMethodBase {

	/**
	 * A constant that defines the slug identifier for PayPal checkout.
	 *
	 * @since 1.10.0
	 */
	private const SLUG = 'paypal';

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
	 * Builds the PayPal payment source including billing address if provided.
	 *
	 * @since 1.10.0
	 *
	 * @param array $submitted_data The submitted form data.
	 *
	 * @return array The configuration array for capture settings.
	 */
	public function get_payment_source_on_create( array $submitted_data ): array {

		$payment_source = [];
		$settings       = $this->process->get_settings();
		$form_data      = $this->process->get_form_data();

		// Add a billing address if configured and valid.
		if ( isset( $settings['billing_address'] ) && $settings['billing_address'] !== '' && ProcessHelper::is_address_field_valid( $submitted_data, $settings['billing_address'], $form_data ) ) {
			$payment_source[ self::SLUG ]['address'] = ProcessHelper::map_address_field( $submitted_data, $settings['billing_address'] );
		}

		return $payment_source;
	}

	/**
	 * Retrieves the customer's name from the provided order data.
	 *
	 * @since 1.10.0
	 *
	 * @param array $order_data An array containing order details, including payment source information.
	 *
	 * @return string The customer's name if available, or an empty string if not.
	 */
	public function get_customer_name( array $order_data ): string {

		$payer = $order_data['payer'] ?? [];

		if ( empty( $payer['name'] ) ) {
			return '';
		}

		return trim( implode( ' ', array_values( $payer['name'] ) ) );
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

		$payer = $order_data['payer'] ?? [];
		$email = $payer['email_address'] ?? '';

		return $this->get_customer_name( $order_data ) . ( $email ? "\n" . $email : '' );
	}
}
