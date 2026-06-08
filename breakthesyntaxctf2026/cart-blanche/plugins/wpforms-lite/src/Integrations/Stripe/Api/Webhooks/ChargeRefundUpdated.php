<?php

namespace WPForms\Integrations\Stripe\Api\Webhooks;

use RuntimeException;
use WPForms\Integrations\Stripe\Api\PaymentIntents;
use WPForms\Integrations\Stripe\Api\Webhooks\Exceptions\AmountMismatchException;

/**
 * Webhook charge.refund.updated class.
 * Currently, this class processes only events where the refund status is 'canceled'.
 *
 * @since 1.9.2
 */
class ChargeRefundUpdated extends Base {

	/**
	 * Handle the Webhook's data.
	 *
	 * Update refunded amount in the payment meta with key refunded_amount.
	 * Update payment status to 'partrefund' or 'completed' if refunded amount is 0.
	 *
	 * @since 1.9.2
	 *
	 * @throws RuntimeException If payment not found or not updated.
	 *
	 * @return bool
	 */
	public function handle() {

		$this->set_payment();

		if ( ! $this->db_payment ) {
			return false;
		}

		// Proceed only if the refund status is 'canceled'.
		if ( $this->data->object->status !== 'canceled' ) {
			return false;
		}

		$charge = ( new PaymentIntents() )->get_charge( $this->data->object->charge );

		if ( ! isset( $charge->amount_refunded ) ) {
			return false;
		}

		$db_refunded_amount = $this->get_refunded_amount();
		$currency           = strtoupper( $this->data->object->currency );
		$decimals_amount    = wpforms_get_currency_multiplier( $currency );

		// We need to format amount since it doesn't contain decimals, e.g. 525 instead of 5.25.
		$refunded_amount        = $charge->amount_refunded / $decimals_amount;
		$canceled_refund_amount = $this->data->object->amount / $decimals_amount;

		// Prevent duplicate webhook processing.
		if ( ! $this->is_valid_refund_amount( $refunded_amount, $db_refunded_amount, $canceled_refund_amount ) ) {
			throw new AmountMismatchException( 'Refund amount mismatch detected. Possible reasons: duplicate webhook processing or webhooks received out of order.' );
		}

		$status = $this->is_full_refund( $canceled_refund_amount, $db_refunded_amount ) ? 'completed' : 'partrefund';

		$this->update_payment_status( $status );
		$this->update_payment_meta( $refunded_amount );
		$this->add_refund_cancel_log( $canceled_refund_amount, $currency );

		return true;
	}

	/**
	 * Validate the refund amount to prevent duplicate webhook processing.
	 *
	 * @since 1.9.2
	 *
	 * @param float $refunded_amount        Refunded amount.
	 * @param float $db_refunded_amount     Refunded amount from the database.
	 * @param float $canceled_refund_amount Canceled refund amount.
	 *
	 * @return bool
	 */
	private function is_valid_refund_amount( float $refunded_amount, float $db_refunded_amount, float $canceled_refund_amount ): bool {

		return $refunded_amount === ( $db_refunded_amount - $canceled_refund_amount );
	}

	/**
	 * Check if this is a full refund.
	 *
	 * @since 1.9.2
	 *
	 * @param float $canceled_refund_amount Canceled refund amount.
	 * @param float $db_refunded_amount     Refunded amount from the database.
	 *
	 * @return bool
	 */
	private function is_full_refund( float $canceled_refund_amount, float $db_refunded_amount ): bool {

		return $canceled_refund_amount >= $db_refunded_amount;
	}

	/**
	 * Update the payment status.
	 *
	 * @since 1.9.2
	 *
	 * @param string $status Available values: 'completed', 'partrefund'.
	 *
	 * @throws RuntimeException If payment status not updated.
	 */
	private function update_payment_status( string $status ) {

		if ( ! in_array( $status, [ 'completed', 'partrefund' ], true ) ) {
			throw new RuntimeException( 'Payment not updated' );
		}

		$updated_payment = wpforms()->obj( 'payment' )->update(
			$this->db_payment->id,
			[
				'status' => $status,
			]
		);

		if ( ! $updated_payment ) {
			throw new RuntimeException( 'Payment not updated' );
		}
	}

	/**
	 * Update the refunded amount meta.
	 *
	 * @since 1.9.2
	 *
	 * @param float $refunded_amount Refunded amount.
	 *
	 * @throws RuntimeException If payment meta not updated.
	 */
	private function update_payment_meta( float $refunded_amount ) {

		$updated_payment_meta = wpforms()->obj( 'payment_meta' )->update_or_add(
			$this->db_payment->id,
			'refunded_amount',
			$refunded_amount
		);

		if ( ! $updated_payment_meta ) {
			throw new RuntimeException( 'Payment meta not updated' );
		}
	}

	/**
	 * Add a log entry for the canceled refund.
	 *
	 * @since 1.9.2
	 *
	 * @param float  $canceled_refund_amount Canceled refund amount.
	 * @param string $currency               Currency code.
	 */
	private function add_refund_cancel_log( float $canceled_refund_amount, string $currency ) {

		$formatted_amount = wpforms_format_amount( $canceled_refund_amount, true, $currency );

		wpforms()->obj( 'payment_meta' )->add_log(
			$this->db_payment->id,
			sprintf(
				'Stripe refund cancelled from the Stripe dashboard. Cancelled refund amount: %1$s.',
				$formatted_amount
			)
		);
	}

	/**
	 * Get refunded amount from the database.
	 *
	 * @since 1.9.2
	 *
	 * @return float
	 */
	private function get_refunded_amount(): float {

		$refunded_amount = wpforms()->obj( 'payment_meta' )->get_last_by(
			'refunded_amount',
			$this->db_payment->id
		);

		return $refunded_amount ? $refunded_amount->meta_value : 0;
	}
}
