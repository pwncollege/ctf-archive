<?php

namespace WPForms\Integrations\Square\Api\Webhooks;

use RuntimeException;
use WPForms\Db\Payments\Queries;

/**
 * Webhook payment.created class.
 *
 * @since 1.9.5
 */
class PaymentCreated extends Base {

	/**
	 * Invoice object.
	 *
	 * @since 1.9.5
	 *
	 * @var Invoice|null
	 */
	private $invoice;

	/**
	 * Set the transaction ID.
	 * Create renewal payment.
	 *
	 * @since 1.9.5
	 *
	 * @return bool
	 *
	 * @throws RuntimeException If subscription ID or order ID is missing.
	 */
	public function handle(): bool {

		$order_id      = $this->data->object->payment->order_id ?? '';
		$this->invoice = $this->api->get_invoice_by_order_id( $order_id );

		// Ensure the invoice was retrieved.
		if ( ! $this->invoice ) {
			throw new RuntimeException( 'Invoice not found for order ID: ' . esc_html( $order_id ) );
		}

		$subscription_id = $this->invoice->getSubscriptionId();

		if ( ! $subscription_id ) {
			throw new RuntimeException( 'Missing subscription ID in payment.created event.' );
		}

		$original_subscription = ( new Queries() )->get_subscription( $subscription_id );

		if ( is_null( $original_subscription ) ) {
			return false; // Original subscription not found.
		}

		$payment = wpforms()->obj( 'payment' )->get_by( 'subscription_id', $subscription_id );

		if ( ! $payment ) {
			return false;
		}

		$this->set_transaction_id( $payment );

		// If this is the first invoice in the subscription, we don't want to create a renewal.
		if ( $this->is_initial_invoice_for_subscription( $subscription_id ) ) {
			return false;
		}

		$renewal = ( new Queries() )->get_renewal_by_invoice_id( $this->invoice->getId() );

		if ( ! is_null( $renewal ) ) {
			return false; // Renewal already exists.
		}

		$renewal_id = $this->insert_renewal( $original_subscription );

		if ( ! $renewal_id ) {
			throw new RuntimeException( 'Subscription renewal not saved in database' );
		}

		$this->insert_renewal_meta( $renewal_id, $original_subscription );

		wpforms()->obj( 'payment_meta' )->add_log(
			$renewal_id,
			sprintf(
				'Square renewal was created (Invoice ID: %1$s).',
				$this->invoice->getId()
			)
		);

		return true;
	}

	/**
	 * Insert renewal.
	 *
	 * @since 1.9.5
	 *
	 * @param object $original_subscription Original subscription.
	 *
	 * @return int|false
	 */
	private function insert_renewal( $original_subscription ) {

		// Retrieve payment requests from the invoice.
		$payment_requests = $this->invoice->getPaymentRequests();

		if ( empty( $payment_requests ) ) {
			return false;
		}

		// Use the first payment request.
		$first_payment_request = $payment_requests[0];
		$computed_amount_money = $first_payment_request->getComputedAmountMoney();
		$currency              = strtoupper( $computed_amount_money->getCurrency() );
		$amount                = $computed_amount_money->getAmount() / wpforms_get_currency_multiplier( $currency );

		return wpforms()->obj( 'payment' )->add(
			[
				'mode'             => $original_subscription->mode,
				'form_id'          => $original_subscription->form_id ?? 0,
				'entry_id'         => $original_subscription->entry_id ?? 0,
				'status'           => 'pending',
				'type'             => 'renewal',
				'gateway'          => 'square',
				'title'            => $original_subscription->title,
				'subtotal_amount'  => $amount,
				'total_amount'     => $amount,
				'currency'         => $currency,
				'transaction_id'   => '',
				'subscription_id'  => $original_subscription->subscription_id,
				'customer_id'      => $original_subscription->customer_id,
				'date_created_gmt' => gmdate( 'Y-m-d H:i:s', strtotime( $this->invoice->getCreatedAt() ) ),
				'date_updated_gmt' => gmdate( 'Y-m-d H:i:s' ),
			]
		);
	}

	/**
	 * Insert renewal meta.
	 *
	 * @since 1.9.5
	 *
	 * @param int    $renewal_id            Renewal ID.
	 * @param object $original_subscription Original subscription.
	 */
	private function insert_renewal_meta( int $renewal_id, $original_subscription ) {

		$meta = $this->copy_meta_from_db( $original_subscription->id );

		$meta['invoice_id']     = $this->invoice->getId();
		$meta['customer_email'] = $this->invoice->getPrimaryRecipient()->getEmailAddress() ?? '';

		wpforms()->obj( 'payment_meta' )->bulk_add( $renewal_id, $meta );
	}

	/**
	 * Copy meta from the original subscription.
	 *
	 * @since 1.9.5
	 *
	 * @param int $original_subscription_id Original subscription ID.
	 *
	 * @return array
	 */
	private function copy_meta_from_db( int $original_subscription_id ): array {

		$all_meta     = wpforms()->obj( 'payment_meta' )->get_all( $original_subscription_id );
		$db_meta_keys = [
			'fields',
			'subscription_period',
			'coupon_value',
			'coupon_info',
			'coupon_id',
		];
		$meta         = [];

		foreach ( $db_meta_keys as $key ) {
			if ( isset( $all_meta[ $key ]->value ) ) {
				$meta[ $key ] = $all_meta[ $key ]->value;
			}
		}

		return $meta;
	}

	/**
	 * Set the transaction ID for the initial payment.
	 *
	 * @since 1.9.5
	 *
	 * @param object $payment Payment object.
	 *
	 * @return bool
	 *
	 * @throws RuntimeException If subscription ID or order ID is missing.
	 */
	private function set_transaction_id( $payment ): bool {

		$subscription_id = $this->invoice->getSubscriptionId();
		$transaction_id  = $this->get_latest_subscription_transaction_id( $subscription_id );

		if ( ! $transaction_id ) {
			return false;
		}

		wpforms()->obj( 'payment' )->update(
			$payment->id,
			[
				'transaction_id' => $transaction_id,
			]
		);

		wpforms()->obj( 'payment_meta' )->add_log(
			$payment->id,
			sprintf(
				'Square subscription was created. (Invoice ID: %s)',
				$this->invoice->getId()
			)
		);

		return true;
	}
}
