<?php

namespace WPForms\Integrations\Square\Api\Webhooks;

use RuntimeException;
use WPForms\Integrations\Square\Api\Api;
use WPForms\Integrations\Square\Connection;

/**
 * Webhook base class.
 *
 * @since 1.9.5
 */
abstract class Base {

	/**
	 * Event type.
	 *
	 * @since 1.9.5
	 *
	 * @var string
	 */
	protected $type;

	/**
	 * Event data from a Square object.
	 *
	 * @since 1.9.5
	 *
	 * @var object
	 */
	protected $data;

	/**
	 * Payment object.
	 *
	 * @since 1.9.5
	 *
	 * @var object
	 */
	protected $db_payment;

	/**
	 * Main class that communicates with the Square API.
	 *
	 * @since 1.9.5
	 *
	 * @var Api
	 */
	protected $api;

	/**
	 * Webhook setup.
	 *
	 * @since 1.9.5
	 *
	 * @param object $event Webhook event object.
	 *
	 * @throws RuntimeException When Square connection is not available.
	 */
	public function setup( $event ) {

		$this->data = $event->data;
		$this->type = $event->type;

		if ( ! Connection::get() ) {
			throw new RuntimeException( 'Square connection is not available.' );
		}

		$this->api = new Api( Connection::get() );

		$this->hooks();
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.9.5
	 */
	private function hooks() {

		add_filter( 'wpforms_current_user_can', '__return_true' );
	}

	/**
	 * Handle the Webhook's data.
	 *
	 * @since 1.9.5
	 *
	 * return bool
	 */
	abstract public function handle();

	/**
	 * Set payment object.
	 *
	 * Set payment object from a database. If payment is not registered yet in DB, throw exception.
	 *
	 * @since 1.9.5
	 */
	protected function set_payment() {

		$transaction_id = $this->data->object->payment->id ?? '';

		if ( $this->type === 'refund.updated' ) {
			$transaction_id = $this->data->object->refund->payment_id;
		}

		$this->db_payment = wpforms()->obj( 'payment' )->get_by( 'transaction_id', $transaction_id );
	}

	/**
	 * Update payment method details.
	 *
	 * @since 1.9.5
	 *
	 * @param int    $payment_id Payment ID.
	 * @param object $details    Charge details.
	 */
	protected function update_payment_method_details( int $payment_id, $details ) {

		$meta['method_type'] = ! empty( $details->source_type ) ? sanitize_text_field( $details->source_type ) : '';

		if ( ! empty( $details->card_details->card->last_4 ) ) {
			$meta['credit_card_last4']   = $details->card_details->card->last_4;
			$meta['credit_card_method']  = $details->card_details->card->card_brand;
			$meta['credit_card_expires'] = $details->card_details->card->exp_month . '/' . $details->card_details->card->exp_year;
		}

		$payment_meta_obj = wpforms()->obj( 'payment_meta' );

		if ( ! $payment_meta_obj ) {
			return;
		}

		$payment_meta_obj->bulk_add( $payment_id, $meta );
	}

	/**
	 * Update total refunded amount.
	 *
	 * @since 1.9.5
	 *
	 * @param int    $payment_id     Payment ID.
	 * @param object $refund_details Refund details.
	 */
	protected function update_total_refund( int $payment_id, $refund_details ) {

		$decimals_amount       = wpforms_get_currency_multiplier( $refund_details->currency );
		$total_refunded_amount = ( $decimals_amount !== 0 ) ? ( $refund_details->amount / $decimals_amount ) : 0;

		if ( ! $total_refunded_amount ) {
			return;
		}

		wpforms()->obj( 'payment_meta' )->update_or_add(
			$payment_id,
			'total_refunded_amount',
			$total_refunded_amount
		);
	}

	/**
	 * Get latest transaction ID from subscription.
	 *
	 * @since 1.9.5
	 *
	 * @param string $subscription_id Subscription ID.
	 *
	 * @return string
	 */
	protected function get_latest_subscription_transaction_id( string $subscription_id ): string {

		$subscription = $this->api->retrieve_subscription( $subscription_id );

		if ( ! $subscription ) {
			return '';
		}

		$invoice = $this->api->get_latest_subscription_invoice( $subscription );

		if ( ! $invoice ) {
			return '';
		}

		$transaction_id = $this->api->get_latest_invoice_transaction_id( $invoice );

		if ( ! $transaction_id ) {
			return '';
		}

		return $transaction_id;
	}

	/**
	 * Check if the invoice is initial for the subscription.
	 *
	 * @since 1.9.5
	 *
	 * @param string $subscription_id Subscription ID.
	 *
	 * @return bool
	 */
	protected function is_initial_invoice_for_subscription( string $subscription_id ): bool {

		$subscription = $this->api->retrieve_subscription( $subscription_id );

		if ( ! $subscription ) {
			return false;
		}

		$invoices = $subscription->getInvoiceIds();

		if ( empty( $invoices ) ) {
			return false;
		}

		return count( $invoices ) <= 1;
	}
}
