<?php

namespace WPForms\Integrations\Stripe\Api;

use Exception;

/**
 * Payment API interface.
 *
 * @since 1.8.2
 */
interface ApiInterface {

	/**
	 * API class initialization.
	 *
	 * @since 1.8.2
	 */
	public function init();

	/**
	 * Set API configuration.
	 *
	 * @since 1.8.2
	 */
	public function set_config();

	/**
	 * Initial Stripe app configuration.
	 *
	 * @since 1.8.2
	 */
	public function setup_stripe();

	/**
	 * Set payment tokens from a submitted form data.
	 *
	 * @since 1.8.2
	 *
	 * @param array $entry Copy of original $_POST.
	 */
	public function set_payment_tokens( $entry );

	/**
	 * Process single payment.
	 *
	 * @since 1.8.2
	 *
	 * @param array $args Single payment arguments.
	 */
	public function process_single( $args );

	/**
	 * Process subscription.
	 *
	 * @since 1.8.2
	 *
	 * @param array $args Subscription arguments.
	 */
	public function process_subscription( $args );

	/**
	 * Get API configuration array or its key.
	 *
	 * @since 1.8.2
	 *
	 * @param string $key Name of the key to retrieve.
	 *
	 * @return mixed
	 */
	public function get_config( $key = '' );

	/**
	 * Get saved Stripe payment object or its key.
	 *
	 * @since 1.8.2
	 *
	 * @param string $key Name of the key to retrieve.
	 *
	 * @return mixed
	 */
	public function get_payment( $key = '' );

	/**
	 * Get saved Stripe customer object or its key.
	 *
	 * @since 1.8.2
	 *
	 * @param string $key Name of the key to retrieve.
	 *
	 * @return mixed
	 */
	public function get_customer( $key = '' );

	/**
	 * Get saved Stripe subscription object or its key.
	 *
	 * @since 1.8.2
	 *
	 * @param string $key Name of the key to retrieve.
	 *
	 * @return mixed
	 */
	public function get_subscription( $key = '' );

	/**
	 * Get details from a saved Charge object.
	 *
	 * @since 1.8.2
	 *
	 * @param string|array $keys Key or an array of keys to retrieve.
	 *
	 * @return array
	 */
	public function get_charge_details( $keys );

	/**
	 * Get API error message.
	 *
	 * @since 1.8.2
	 *
	 * @return string
	 */
	public function get_error();

	/**
	 * Get API exception.
	 *
	 * @since 1.8.2
	 *
	 * @return Exception
	 */
	public function get_exception();
}
