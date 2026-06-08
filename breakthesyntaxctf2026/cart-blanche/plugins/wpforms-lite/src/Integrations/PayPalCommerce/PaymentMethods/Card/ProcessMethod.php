<?php

namespace WPForms\Integrations\PayPalCommerce\PaymentMethods\Card;

use WPForms\Integrations\PayPalCommerce\Process\ProcessHelper;
use WPForms\Integrations\PayPalCommerce\Process\ProcessMethodBase;

/**
 * Represents the process implementation for the card payment method.
 *
 * @since 1.10.0
 */
class ProcessMethod extends ProcessMethodBase {

	/**
	 * A constant that defines the slug identifier for card payments.
	 *
	 * @since 1.10.0
	 */
	private const SLUG = 'card';

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
	 * Builds the card payment source including billing address and cardholder name.
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
			$payment_source[ self::SLUG ]['billing_address'] = ProcessHelper::map_address_field( $submitted_data, $settings['billing_address'] );
		}

		// Add a cardholder name if configured.
		if ( isset( $settings['name'] ) && $settings['name'] !== '' ) {
			$name = ProcessHelper::get_submitted_name_value( $submitted_data, $settings['name'] );

			if ( ! empty( $name ) ) {
				$payment_source[ self::SLUG ]['name'] = $name;
			}
		}

		return $payment_source;
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

		return $this->get_customer_name( $order_data );
	}
}
