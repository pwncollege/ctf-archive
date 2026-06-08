<?php

namespace WPForms\Integrations\PayPalCommerce\Process;

use WPForms\Integrations\PayPalCommerce\PayPalCommerce;

/**
 * Abstract base class for processing various payment methods.
 * Provides common functionality for address validation and mapping,
 * while requiring child classes to implement method-specific logic.
 *
 * @since 1.10.0
 */
abstract class ProcessMethodBase {

	/**
	 * Process instance.
	 *
	 * @since 1.10.0
	 *
	 * @var Base
	 */
	protected $process;

	/**
	 * Retrieves the method type.
	 *
	 * @since 1.10.0
	 *
	 * @return string The method type as a string.
	 */
	abstract public function get_type(): string;

	/**
	 * Retrieves the configuration settings for a capture operation.
	 *
	 * This method returns the complete payment source structure including
	 * billing addresses, cardholder names, and any payment method specific
	 * configuration needed for PayPal API capture operations.
	 *
	 * @since 1.10.0
	 *
	 * @param array $submitted_data The submitted form data.
	 *
	 * @return array The configuration settings as an associative array.
	 */
	abstract public function get_payment_source_on_create( array $submitted_data ): array;

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

		if ( empty( $order_data['payment_source'][ $this->get_type() ]['name'] ) ) {
			return '';
		}

		$name = $order_data['payment_source'][ $this->get_type() ]['name'];

		if ( is_array( $name ) ) {
			$name = implode( ' ', array_values( $name ) );
		}

		return trim( $name );
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

		$email = $order_data['payment_source'][ $this->get_type() ]['email_address'] ?? '';

		return $this->get_customer_name( $order_data ) . ( $email ? "\n" . $email : '' );
	}

	/**
	 * Set a process instance.
	 *
	 * @since 1.10.0
	 *
	 * @param Base $process The process instance.
	 */
	public function set_process( Base $process ): void {

		$this->process = $process;
	}

	/**
	 * Retrieves the settings configuration for the PayPal Commerce integration.
	 *
	 * @since 1.10.0
	 *
	 * @return array The PayPal Commerce settings as an associative array.
	 */
	protected function get_settings(): array {

		return (array) ( $this->process->get_form_data()['payments'][ PayPalCommerce::SLUG ] ?? [] );
	}
}
