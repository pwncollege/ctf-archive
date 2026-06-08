<?php

namespace WPForms\Integrations\PayPalCommerce\Frontend;

/**
 * Defines the contract for SDK components to be implemented.
 *
 * Provides methods for retrieving a unique identifier (slug) for the component
 * and for enqueuing necessary resources.
 *
 * @since 1.10.0
 */
interface PaymentMethodInterface {

	/**
	 * Checks if the given payment method is supported.
	 *
	 * @since 1.10.0
	 *
	 * @param array $field The field to be checked for support.
	 *
	 * @return bool True if the payment method is supported, false otherwise.
	 */
	public function is_supported( array $field ): bool;

	/**
	 * Retrieves the list of components.
	 *
	 * @since 1.10.0
	 *
	 * @param bool $is_single Whether to consider One-Time Payment or Recurring. Defaults to One-Time.
	 *
	 * @return array The array of components.
	 */
	public function get_components( bool $is_single = true ): array;
}
