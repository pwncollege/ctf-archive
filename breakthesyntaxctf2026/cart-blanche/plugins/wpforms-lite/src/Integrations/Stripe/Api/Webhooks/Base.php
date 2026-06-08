<?php

namespace WPForms\Integrations\Stripe\Api\Webhooks;

use WPForms\Vendor\Stripe\Event as StripeEvent;
use WPForms\Integrations\Stripe\Helpers;

/**
 * Webhook base class.
 *
 * @since 1.8.4
 */
abstract class Base {

	/**
	 * Event data from Stripe object.
	 *
	 * @since 1.8.4
	 *
	 * @var object
	 */
	protected $data;

	/**
	 * Payment object.
	 *
	 * @since 1.8.4
	 *
	 * @var object
	 */
	protected $db_payment;

	/**
	 * Webhook setup.
	 *
	 * @since 1.8.4
	 *
	 * @param StripeEvent $event Stripe event.
	 */
	public function setup( StripeEvent $event ) {

		$this->data = $event->data;

		$this->hooks();
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.8.4
	 */
	private function hooks() {

		add_filter( 'wpforms_current_user_can', '__return_true' );
	}

	/**
	 * Handle the Webhook's data.
	 *
	 * @since 1.8.4
	 *
	 * return bool
	 */
	abstract public function handle();

	/**
	 * Set payment object.
	 *
	 * Set payment object from database. If payment not registered yet in DB, throw exception.
	 *
	 * @since 1.8.4
	 */
	protected function set_payment() {

		// Determine whether a legacy API version bundled into the addon is still used.
		// When it's dropped from the addon, this line can be safely removed.
		$is_legacy_api = Helpers::is_pro() && absint( wpforms_setting( 'stripe-api-version' ) ) === 2;

		if ( $is_legacy_api && ! isset( $this->data->object->id ) ) {
			return; // Payment id not found.
		}

		if ( ! $is_legacy_api && ! isset( $this->data->object->payment_intent ) ) {
			return; // Payment intent not found.
		}

		$transaction_id = $is_legacy_api ? $this->data->object->id : $this->data->object->payment_intent;

		$this->db_payment = wpforms()->obj( 'payment' )->get_by( 'transaction_id', $transaction_id );
	}

	/**
	 * Delay webhook handling.
	 *
	 * Stripe sends some webhooks before payment is saved in our database.
	 * Sometimes it is required to wait until form submission has ended and payment is saved in the database.
	 *
	 * @since 1.8.4
	 */
	protected function delay() {

		sleep( 5 );
	}

	/**
	 * Check if previous statuses are matched.
	 *
	 * If webhook payload contains previous payment status and it's not matching with the status in the database, return false.
	 *
	 * @since 1.8.4
	 *
	 * @depecated 1.9.3
	 *
	 * @return bool
	 */
	protected function is_previous_statuses_matched(): bool {

		_deprecated_function( __METHOD__, '1.9.3 of the WPForms plugin' );

		$db_stripe = [
			'partrefund' => 'refunded',
			'refunded'   => 'refunded',
			'completed'  => 'succeeded',
			'pending'    => 'processing',
		];

		if (
			isset( $this->data->previous_attributes->status ) &&
			in_array( $this->data->previous_attributes->status, $db_stripe, true ) &&
			$db_stripe[ $this->db_payment->status ] !== $this->data->previous_attributes->status
		) {
			return false;
		}

		return true;
	}

	/**
	 * Update payment method details.
	 *
	 * @since 1.8.7
	 *
	 * @param int   $payment_id Payment ID.
	 * @param array $details    Charge details.
	 *
	 * @noinspection PhpMissingParamTypeInspection
	 */
	protected function update_payment_method_details( $payment_id, $details ) {

		$meta['method_type'] = ! empty( $details->type ) ? sanitize_text_field( $details->type ) : '';

		if ( ! empty( $details->card->last4 ) ) {
			$meta['method_type']         = $meta['method_type'] ?? 'card';
			$meta['credit_card_last4']   = $details->card->last4;
			$meta['credit_card_method']  = $details->card->brand;
			$meta['credit_card_expires'] = $details->card->exp_month . '/' . $details->card->exp_year;
		}

		$payment_meta_obj = wpforms()->obj( 'payment_meta' );

		if ( ! $payment_meta_obj ) {
			return;
		}

		$payment_meta_obj->bulk_add( $payment_id, $meta );
	}
}
