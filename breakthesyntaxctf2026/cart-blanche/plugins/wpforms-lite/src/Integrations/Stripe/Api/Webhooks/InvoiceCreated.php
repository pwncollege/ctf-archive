<?php

namespace WPForms\Integrations\Stripe\Api\Webhooks;

use RuntimeException;
use Exception;
use WPForms\Vendor\Stripe\Invoice;
use WPForms\Integrations\Stripe\Helpers;
use WPForms\Db\Payments\Queries;

/**
 * Webhook invoice.created class.
 *
 * @since 1.8.4
 */
class InvoiceCreated extends Base {

	/**
	 * Handle invoice.created webhook.
	 *
	 * @since 1.8.4
	 *
	 * @throws RuntimeException       If original subscription not found or not updated.
	 *
	 * @return bool
	 */
	public function handle() {

		if ( ! isset( $this->data->object->billing_reason ) || $this->data->object->billing_reason !== 'subscription_cycle' ) {
			return false; // Webhook handler for Invoice.Create supports only billing_reason = subscription_cycle.
		}

		$original_subscription = ( new Queries() )->get_subscription( $this->data->object->subscription );

		if ( is_null( $original_subscription ) ) {
			return false; // Original subscription not found.
		}

		$renewal = ( new Queries() )->get_renewal_by_invoice_id( $this->data->object->id );

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
				'Stripe renewal was created (Invoice ID: %1$s).',
				$this->data->object->id
			)
		);

		$this->finalize_invoice();

		return true;
	}

	/**
	 * Insert renewal.
	 *
	 * @since 1.8.4
	 *
	 * @param object $original_subscription Original subscription.
	 *
	 * @return int|false
	 */
	private function insert_renewal( $original_subscription ) {

		$currency = strtoupper( $this->data->object->currency );
		$amount   = $this->data->object->amount_due / wpforms_get_currency_multiplier( $currency );

		return wpforms()->obj( 'payment' )->add(
			[
				'mode'             => $original_subscription->mode,
				'form_id'          => isset( $original_subscription->form_id ) ? $original_subscription->form_id : 0,
				'entry_id'         => isset( $original_subscription->entry_id ) ? $original_subscription->entry_id : 0,
				'status'           => 'pending',
				'type'             => 'renewal',
				'gateway'          => 'stripe',
				'title'            => $original_subscription->title,
				'subtotal_amount'  => $amount,
				'total_amount'     => $amount,
				'currency'         => $currency,
				'transaction_id'   => '',
				'subscription_id'  => $original_subscription->subscription_id,
				'customer_id'      => $original_subscription->customer_id,
				'date_created_gmt' => gmdate( 'Y-m-d H:i:s', $this->data->object->lines->data[0]->period->start ),
				'date_updated_gmt' => gmdate( 'Y-m-d H:i:s' ),
			]
		);
	}

	/**
	 * Insert renewal meta.
	 *
	 * @since 1.8.4
	 *
	 * @param int    $renewal_id            Renewal ID.
	 * @param object $original_subscription Original subscription.
	 */
	private function insert_renewal_meta( $renewal_id, $original_subscription ) {

		$meta = $this->copy_meta_from_db( $original_subscription->id );

		$meta['invoice_id']     = $this->data->object->id;
		$meta['customer_email'] = isset( $this->data->object->customer_email ) ? $this->data->object->customer_email : '';

		wpforms()->obj( 'payment_meta' )->bulk_add( $renewal_id, $meta );
	}

	/**
	 * Copy meta from original subscription.
	 *
	 * @since 1.8.4
	 *
	 * @param int $original_subscription_id Original subscription ID.
	 *
	 * @return array
	 */
	private function copy_meta_from_db( $original_subscription_id ) {

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
	 * Finalize invoice.
	 *
	 * @since 1.8.4
	 *
	 * @throws RuntimeException If invoice not finalized.
	 */
	private function finalize_invoice() {

		try {
			$invoice = new Invoice();
			$invoice = $invoice->retrieve( $this->data->object->id, Helpers::get_auth_opts() );

			if ( empty( $invoice->finalized_at ) ) {
				$invoice->finalizeInvoice();
			}
		} catch ( Exception $e ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			throw new RuntimeException( esc_html( $e->getMessage() ) );
		}
	}
}
