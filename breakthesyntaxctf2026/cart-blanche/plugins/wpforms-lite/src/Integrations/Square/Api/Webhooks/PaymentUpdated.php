<?php

namespace WPForms\Integrations\Square\Api\Webhooks;

use RuntimeException;
use WPForms\Db\Payments\Queries;

/**
 * Webhook payment.updated class.
 * Set the status to 'completed' if payment is paid.
 *
 * @since 1.9.5
 */
class PaymentUpdated extends Base {

	/**
	 * Invoice object.
	 *
	 * @since 1.9.5
	 *
	 * @var Invoice|null
	 */
	private $invoice;

	/**
	 * Handle the Webhook's data.
	 *
	 * @since 1.9.5
	 *
	 * @throws RuntimeException If payment isn't found or not updated.
	 *
	 * @return bool
	 */
	public function handle(): bool {

		$order_id        = $this->data->object->payment->order_id ?? '';
		$this->invoice   = $this->api->get_invoice_by_order_id( $order_id );
		$subscription_id = $this->invoice ? $this->invoice->getSubscriptionId() : '';

		// If a subscription ID exists, process the subscription-specific logic.
		if ( $subscription_id ) {
			$this->update_subscription_payment( $subscription_id );
		}

		$this->set_payment();

		if ( $this->db_payment === null ) {
			throw new RuntimeException( 'Payment Update Event: Payment has not been found and set.' );
		}

		// Update payment method details to keep them up to date.
		if ( isset( $this->data->object->payment ) && $this->data->object->payment !== null ) {
			$this->update_payment_method_details( $this->db_payment->id, $this->data->object->payment );
		}

		// Update total refunded amount if set.
		if ( ! empty( $this->data->object->payment->refunded_money ) ) {
			$this->update_total_refund( $this->db_payment->id, $this->data->object->payment->refunded_money );
		}

		if ( $this->db_payment->status !== 'processed' || $this->data->object->payment->status !== 'COMPLETED' ) {
			return false;
		}

		$currency  = strtoupper( $this->data->object->payment->total_money->currency );
		$db_amount = wpforms_format_amount( $this->db_payment->total_amount );
		$amount    = wpforms_format_amount( $this->data->object->payment->total_money->amount / wpforms_get_currency_multiplier( $currency ) );

		if ( $amount !== $db_amount ) {
			return false;
		}

		$updated_payment = wpforms()->obj( 'payment' )->update(
			$this->db_payment->id,
			[
				'status'           => 'completed',
				'date_updated_gmt' => gmdate( 'Y-m-d H:i:s' ),
			]
		);

		if ( ! $updated_payment ) {
			throw new RuntimeException( 'Payment not updated' );
		}

		wpforms()->obj( 'payment_meta' )->add_log(
			$this->db_payment->id,
			'Square payment was completed.'
		);

		return true;
	}

	/**
	 * Update subscription payment.
	 *
	 * @since 1.9.5
	 *
	 * @param string $subscription_id Subscription ID.
	 *
	 * @return bool
	 */
	private function update_subscription_payment( string $subscription_id ): bool {

		// If this is the first invoice in the subscription, do not create a renewal.
		if ( $this->is_initial_invoice_for_subscription( $subscription_id ) ) {
			return false;
		}

		// Retrieve the renewal record from the database.
		$db_renewal = ( new Queries() )->get_renewal_by_invoice_id( $this->invoice->getId() );

		if ( is_null( $db_renewal ) ) {
			return false; // The newest renewal not found.
		}

		// Check if the renewal payment is already completed.
		if ( $db_renewal->status === 'completed' ) {
			return true;
		}

		// Retrieve the payment requests from the invoice.
		$payment_requests = $this->invoice->getPaymentRequests();

		if ( empty( $payment_requests ) ) {
			return false;
		}

		// Use the first payment request to get the final paid amount.
		$total_completed = $payment_requests[0]->getTotalCompletedAmountMoney();
		$currency        = strtoupper( $total_completed->getCurrency() );
		$amount          = $total_completed->getAmount() / wpforms_get_currency_multiplier( $currency );

		// Retrieve the transaction ID using the subscription ID.
		$transaction_id = $this->get_latest_subscription_transaction_id( $subscription_id );

		if ( empty( $transaction_id ) ) {
			$transaction_id = '';
		}

		// Update the renewal payment with the final amount and transaction ID.
		wpforms()->obj( 'payment' )->update(
			$db_renewal->id,
			[
				'total_amount'    => $amount,
				'subtotal_amount' => $amount,
				'status'          => 'completed',
				'transaction_id'  => $transaction_id,
			]
		);

		// Copy additional meta data from the transaction details.
		$this->copy_meta_from_transaction_details( (int) $db_renewal->id, $transaction_id );

		wpforms()->obj( 'payment_meta' )->add_log(
			$db_renewal->id,
			sprintf(
				'Square renewal was successfully paid. (Payment ID: %1$s)',
				$transaction_id
			)
		);

		return true;
	}

	/**
	 * Copy meta from transaction.
	 *
	 * @since 1.9.5
	 *
	 * @param int    $renewal_id     Renewal ID.
	 * @param string $transaction_id Transaction ID.
	 */
	private function copy_meta_from_transaction_details( int $renewal_id, string $transaction_id ) {

		$card_details = $this->api->get_card_details_from_transaction_id( $transaction_id );

		if ( ! $card_details ) {
			return;
		}

		$this->update_payment_method_details( $renewal_id, $card_details );
	}
}
