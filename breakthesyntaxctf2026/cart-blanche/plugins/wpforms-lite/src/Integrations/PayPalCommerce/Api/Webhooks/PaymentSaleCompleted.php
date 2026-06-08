<?php

namespace WPForms\Integrations\PayPalCommerce\Api\Webhooks;

use RuntimeException;
use WPForms\Db\Payments\Queries;

/**
 * Handle PayPal event PAYMENT.SALE.COMPLETED.
 *
 * @since 1.10.0
 */
class PaymentSaleCompleted extends Base {

	/**
	 * Insert subscription renewal.
	 *
	 * @since 1.10.0
	 *
	 * @return bool True on success.
	 *
	 * @throws RuntimeException If the original subscription wasn't found or not updated.
	 */
	public function handle(): bool {

		$subscription_id = $this->data->billing_agreement_id ?? '';

		if ( empty( $subscription_id ) ) {
			return false;
		}

		$original_subscription = ( new Queries() )->get_subscription( $subscription_id );

		if ( is_null( $original_subscription ) ) {
			return false; // Original subscription not found.
		}

		$is_initial = $this->is_initial_subscription_payment();

		// Skip early for the first initial renewal. Update the payment status only.
		if ( $is_initial ) {
			return $this->update_initial_payment_status( $original_subscription );
		}

		$transaction_id = $this->get_transaction_id_from_data();

		if ( empty( $transaction_id ) ) {
			return false;
		}

		$existing_renewal = wpforms()->obj( 'payment' )->get_by( 'transaction_id', $transaction_id );

		// Avoid creating duplicate renewals if PayPal sends the webhook twice.
		if ( ! empty( $existing_renewal ) ) {
			return true;
		}

		$renewal_id = $this->insert_renewal( $original_subscription );

		if ( ! $renewal_id ) {
			throw new RuntimeException( 'Subscription renewal not saved in database' );
		}

		$this->insert_renewal_meta( $renewal_id, $original_subscription );

		wpforms()->obj( 'payment_meta' )->add_log(
			$renewal_id,
			sprintf(
				'PayPal Commerce renewal was created (Transaction ID: %1$s).',
				$this->data->id
			)
		);

		return true;
	}

	/**
	 * Insert renewal.
	 *
	 * @since 1.10.0
	 *
	 * @param object $original_subscription Original subscription.
	 *
	 * @return int|false
	 */
	private function insert_renewal( object $original_subscription ) {

		$currency = strtoupper( $this->data->amount->currency );
		$amount   = $this->data->amount->total;

		return wpforms()->obj( 'payment' )->add(
			[
				'mode'             => $original_subscription->mode,
				'form_id'          => $original_subscription->form_id ?? 0,
				'entry_id'         => $original_subscription->entry_id ?? 0,
				'status'           => 'completed',
				'type'             => 'renewal',
				'gateway'          => 'paypal_commerce',
				'title'            => $original_subscription->title,
				'subtotal_amount'  => $amount,
				'total_amount'     => $amount,
				'currency'         => $currency,
				'transaction_id'   => $this->data->id ?? '',
				'subscription_id'  => $original_subscription->subscription_id,
				'customer_id'      => $original_subscription->customer_id,
				'date_created_gmt' => gmdate( 'Y-m-d H:i:s', strtotime( $this->data->update_time ) ),
				'date_updated_gmt' => gmdate( 'Y-m-d H:i:s' ),
			]
		);
	}

	/**
	 * Insert renewal meta.
	 *
	 * @since 1.10.0
	 *
	 * @param int    $renewal_id            Renewal ID.
	 * @param object $original_subscription Original subscription.
	 */
	private function insert_renewal_meta( int $renewal_id, object $original_subscription ): void {

		$meta = $this->copy_meta_from_db( $original_subscription->id );

		$meta['customer_email'] = $this->data->payer->email_address ?? '';

		wpforms()->obj( 'payment_meta' )->bulk_add( $renewal_id, $meta );
	}

	/**
	 * Copy meta from the original subscription.
	 *
	 * @since 1.10.0
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
			// Necessary for the Product API subscriptions.
			'processor_type',
			'payer_email',
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
	 * Update initial payment status.
	 *
	 * @since 1.10.0
	 *
	 * @param object $original_subscription Original subscription.
	 *
	 * @return bool True on success.
	 */
	private function update_initial_payment_status( object $original_subscription ): bool {

		$payment_data = [
			'status'           => 'completed',
			'date_updated_gmt' => gmdate( 'Y-m-d H:i:s' ),
		];

		$process_type = wpforms()->obj( 'payment_meta' )->get_single( $original_subscription->id, 'processor_type' );

		// Update the transaction ID only for the native PayPal Commerce subscriptions.
		if ( empty( $process_type ) ) {
			$payment_data['transaction_id'] = $this->data->id ?? '';
		}

		wpforms()->obj( 'payment' )->update(
			$original_subscription->id,
			$payment_data
		);

		return true;
	}
}
