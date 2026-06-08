<?php

namespace WPForms\Integrations\PayPalCommerce\PaymentMethods\RegionalMethods\Frontend;

use WPForms\Integrations\PayPalCommerce\Frontend\PaymentMethodFundingInterface;
use WPForms\Integrations\PayPalCommerce\Frontend\PaymentMethodInterface;

/**
 * Base class for regional payment methods in PayPal Commerce.
 *
 * @since 1.10.0
 */
abstract class RegionalPaymentMethodBase implements PaymentMethodInterface, PaymentMethodFundingInterface {

	/**
	 * Currency code.
	 *
	 * @since 1.10.0
	 *
	 * @var string
	 */
	private $currency = '';

	/**
	 * Get the payment method type identifier.
	 *
	 * @since 1.10.0
	 *
	 * @return string Payment method type.
	 */
	abstract public function get_type(): string;

	/**
	 * Get the list of supported currency codes for this payment method.
	 *
	 * @since 1.10.0
	 *
	 * @return array List of supported currency codes.
	 */
	abstract public function get_supported_currencies(): array;

	/**
	 * Get the list of disabled payment methods based on currency support.
	 *
	 * @since 1.10.0
	 *
	 * @param bool $is_single Whether this is a single payment method context.
	 *
	 * @return array List of disabled payment method types.
	 */
	public function get_disabled_methods( bool $is_single = true ): array {

		if ( ! $is_single ) {
			return [];
		}

		$currencies   = $this->get_supported_currencies();
		$has_currency = empty( $currencies ) || in_array( $this->currency, $currencies, true );

		return $has_currency ? [] : [ $this->get_type() ];
	}

	/**
	 * Get the list of enabled payment methods.
	 *
	 * @since 1.10.0
	 *
	 * @param bool $is_single Whether this is a single payment method context.
	 *
	 * @return array List of enabled payment method types.
	 */
	public function get_enabled_methods( bool $is_single = true ): array {

		return [];
	}

	/**
	 * Check if the payment method is supported for the given field.
	 *
	 * @since 1.10.0
	 *
	 * @param array $field Form field data.
	 *
	 * @return bool True if supported, false otherwise.
	 */
	public function is_supported( array $field ): bool {

		return true;
	}

	/**
	 * Retrieves the list of components for integration.
	 *
	 * @since 1.10.0
	 *
	 * @param bool $is_single Whether to consider One-Time Payment or Recurring. Defaults to One-Time.
	 *
	 * @return array The list of components.
	 */
	public function get_components( bool $is_single = true ): array {

		return [];
	}

	/**
	 * Set the currency code for this payment method.
	 *
	 * @since 1.10.0
	 *
	 * @param string $currency Currency code.
	 *
	 * @return self
	 */
	public function set_currency( string $currency ): self {

		$this->currency = $currency;

		return $this;
	}
}
