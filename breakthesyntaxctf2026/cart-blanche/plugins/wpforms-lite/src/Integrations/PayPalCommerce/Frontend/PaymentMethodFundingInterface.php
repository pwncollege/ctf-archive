<?php

namespace WPForms\Integrations\PayPalCommerce\Frontend;

/**
 * Interface representing payment method funding operations.
 *
 * @since 1.10.0
 */
interface PaymentMethodFundingInterface {

	/**
	 * Retrieves a list of disabled methods.
	 *
	 * @since 1.10.0
	 *
	 * @param bool $is_single Whether to consider One-Time Payment or Recurring. Defaults to One-Time.
	 *
	 * @return array An array of disabled method names.
	 */
	public function get_disabled_methods( bool $is_single = true ): array;

	/**
	 * Retrieves a list of enabled methods.
	 *
	 * @since 1.10.0
	 *
	 * @param bool $is_single Whether to consider One-Time Payment or Recurring. Defaults to One-Time.
	 *
	 * @return array An array of enabled method names.
	 */
	public function get_enabled_methods( bool $is_single = true ): array;
}
