<?php

namespace WPForms\Integrations\PayPalCommerce\Api\Webhooks;

use RuntimeException;

/**
 * Handle PayPal event PAYMENT.CAPTURE.COMPLETED.
 *
 * @since 1.10.0
 */
class PaymentCaptureCompleted extends Base {

	/**
	 * Check the DB amount and webhooks received amount.
	 * Update the payment status to complete.
	 *
	 * @since 1.10.0
	 *
	 * @return bool True on success.
	 *
	 * @throws RuntimeException If a payment isn't found or not updated.
	 */
	public function handle(): bool {

		$this->set_payment();

		if ( ! $this->db_payment ) {
			return false;
		}

		// Update the status if it wasn't updated already.
		// Update only if payment has COMPLETED status.
		if ( $this->db_payment->status !== 'processed' || $this->data->status !== 'COMPLETED' ) {
			return false;
		}

		$db_amount = wpforms_format_amount( $this->db_payment->total_amount );
		$amount    = wpforms_format_amount( $this->data->amount->value );

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
			'PayPal Commerce payment was completed.'
		);

		return true;
	}
}
