<?php

namespace WPForms\Integrations\Stripe\Api\Webhooks;

use RuntimeException;
use Stripe\Exception\ApiErrorException;
use WPForms\Db\Payments\Queries;
use WPForms\Integrations\Stripe\Helpers;
use WPForms\Vendor\Stripe\PaymentIntent;

/**
 * Webhook invoice.payment_succeeded class.
 *
 * @since 1.8.4
 */
class InvoicePaymentSucceeded extends Base {

	/**
	 * Handle invoice.payment_succeeded webhook for subscription_cycle billing reason (payment renewal).
	 *
	 * @since 1.8.4
	 *
	 * @throws RuntimeException       If subscription not found or not updated.
	 *
	 * @return bool
	 */
	public function handle() {

		if ( ! isset( $this->data->object->billing_reason ) || $this->data->object->billing_reason !== 'subscription_cycle' ) {
			return false; // Webhook handler for Invoice.PaymentSucceeded with reason subscription_cycle not implemented yet.
		}

		if ( $this->data->object->paid !== true ) {
			return false; // Subscription not paid, so we are not going to proceed with update.
		}

		$db_renewal = ( new Queries() )->get_renewal_by_invoice_id( $this->data->object->id );

		if ( is_null( $db_renewal ) ) {
			return false; // Newest renewal not found.
		}

		$currency = strtoupper( $this->data->object->currency );
		$amount   = $this->data->object->amount_paid / wpforms_get_currency_multiplier( $currency );

		wpforms()->obj( 'payment' )->update(
			$db_renewal->id,
			[
				'total_amount'    => $amount,
				'subtotal_amount' => $amount,
				'status'          => 'completed',
				'transaction_id'  => $this->data->object->payment_intent,
			]
		);

		$this->copy_meta_from_payment_intent( $db_renewal->id );

		wpforms()->obj( 'payment_meta' )->add_log(
			$db_renewal->id,
			sprintf(
				'Stripe renewal was successfully paid. (Payment Intent ID: %1$s)',
				$this->data->object->payment_intent
			)
		);

		return true;
	}

	/**
	 * Copy meta from payment intent.
	 *
	 * @since 1.8.4
	 *
	 * @param int $renewal_id Renewal ID.
	 *
	 * @noinspection PhpMissingParamTypeInspection
	 */
	private function copy_meta_from_payment_intent( $renewal_id ) {

		try {
			$payment_intent = PaymentIntent::retrieve( $this->data->object->payment_intent, Helpers::get_auth_opts() );
		} catch ( ApiErrorException $e ) {
			$payment_intent = null;
		}

		if ( ! isset( $payment_intent->charges->data[0]->payment_method_details ) ) {
			return;
		}

		$this->update_payment_method_details( $renewal_id, $payment_intent->charges->data[0]->payment_method_details );
	}
}
