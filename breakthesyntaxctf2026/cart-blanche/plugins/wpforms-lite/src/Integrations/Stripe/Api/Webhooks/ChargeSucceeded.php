<?php

namespace WPForms\Integrations\Stripe\Api\Webhooks;

use WPForms\Db\Payments\Queries;
use WPForms\Integrations\Stripe\Helpers;
use RuntimeException;

/**
 * Webhook charge.succeeded class.
 *
 * @since 1.8.4
 */
class ChargeSucceeded extends Base {

	/**
	 * Handle the Webhook's data.
	 *
	 * @since 1.8.4
	 *
	 * @throws RuntimeException If payment not found or not updated.
	 *
	 * @return bool
	 */
	public function handle() {

		$this->delay();

		$this->set_payment();

		if ( ! $this->db_payment ) {

			// Handle a case when charge.succeeded was sent before invoice.payment_succeeded to update a payment method details.
			if ( ! empty( $this->data->object->invoice ) ) {
				$db_renewal = ( new Queries() )->get_renewal_by_invoice_id( $this->data->object->invoice );

				if ( is_null( $db_renewal ) || empty( $this->data->object->payment_method_details ) ) {
					return false;
				}

				$this->update_payment_method_details( $db_renewal->id, $this->data->object->payment_method_details );
			}

			return false;
		}

		// Update payment method details to keep them up to date.
		if ( ! empty( $this->data->object->payment_method_details ) ) {
			$this->update_payment_method_details( $this->db_payment->id, $this->data->object->payment_method_details );
		}

		if ( $this->db_payment->status !== 'processed' ) {
			return false;
		}

		$currency  = strtoupper( $this->data->object->currency );
		$db_amount = wpforms_format_amount( $this->db_payment->total_amount );
		$amount    = wpforms_format_amount( $this->data->object->amount_captured / wpforms_get_currency_multiplier( $currency ) );

		if ( $amount !== $db_amount || ! $this->data->object->paid ) {
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
			'Stripe payment was completed.'
		);

		return true;
	}
}
