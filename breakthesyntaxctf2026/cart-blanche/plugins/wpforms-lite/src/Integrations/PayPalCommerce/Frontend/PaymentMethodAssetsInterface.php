<?php

namespace WPForms\Integrations\PayPalCommerce\Frontend;

/**
 * Defines the contract for SDK components to be implemented.
 *
 * Provides methods for retrieving a unique identifier (slug) for the component
 * and for the enqueuing the necessary resources.
 *
 * @since 1.10.0
 */
interface PaymentMethodAssetsInterface {

	/**
	 * Adds an element to the end of the queue.
	 *
	 * @since 1.10.0
	 *
	 * @param array $payment_types Array of payment type flags (e.g., ['single' => bool, 'recurring' => bool]).
	 */
	public function enqueue( array $payment_types ): void;

	/**
	 * Retrieves an array of asynchronous scripts.
	 *
	 * @since 1.10.0
	 *
	 * @return array The list of asynchronous script URLs or handles.
	 */
	public function get_async_scripts(): array;

	/**
	 * Retrieves the localized settings for a given field.
	 *
	 * @since 1.10.0
	 *
	 * @param array $field The field configuration array to localize.
	 * @param array $form  The form configuration array.
	 *
	 * @return array The localized settings of the specified field.
	 */
	public function get_localized_settings( array $field, array $form ): array;
}
