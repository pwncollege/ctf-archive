<?php

namespace WPForms\Integrations\PayPalCommerce\Api\Webhooks;

use WPForms\Integrations\PayPalCommerce\Api\Webhooks\Exceptions\AmountMismatchException;

/**
 * Handle PayPal event PAYMENT.CAPTURE.REFUNDED.
 *
 * @since 1.10.0
 */
class PaymentSaleRefunded extends Base {

	/**
	 * Updates the DB with a refunded amount.
	 * Change the status of the payment to refund or party refund.
	 *
	 * @since 1.10.0
	 *
	 * @return bool True on success.
	 *
	 * @throws AmountMismatchException If the payment isn't found or not updated.Or if the refund amount doesn't match the expected amount.
	 */
	public function handle(): bool {

		$this->set_payment();

		if ( ! $this->db_payment ) {
			return false;
		}

		// Get currency for formatting.
		$currency = strtoupper( $this->data->amount->currency );

		// Get and format amounts using wpforms_format_amount for consistency.
		$refunded_amount    = wpforms_format_amount( $this->data->total_refunded_amount->value, false, $currency );
		$last_refund_amount = wpforms_format_amount( $this->get_last_refund_amount(), false, $currency );
		$db_refunded_amount = wpforms_format_amount( $this->get_refunded_amount( $this->db_payment->id ), false, $currency );

		// Validate refund amount to prevent duplicate webhook processing.
		if ( ! $this->is_valid_refund_amount( $refunded_amount, $db_refunded_amount, $last_refund_amount ) ) {
			throw new AmountMismatchException( 'Refund amount mismatch detected. Possible reasons: duplicate webhook processing or webhooks received out of order.' );
		}

		// Determine status based on whether this is a full or partial refund.
		$status = $this->get_payment_status( (float) $refunded_amount, (float) $this->db_payment->total_amount );

		// Update payment in separate steps.
		$this->update_payment_status( $this->db_payment->id, $status );
		$this->update_refunded_amount_payment_meta( $this->db_payment->id, (float) $refunded_amount );
		$this->add_refund_log( $this->db_payment->id, (float) $last_refund_amount, $currency );

		return true;
	}

	/**
	 * Get the last refund amount (this specific refund, not cumulative).
	 *
	 * @since 1.10.0
	 *
	 * @return float Last refund amount.
	 */
	private function get_last_refund_amount(): float {

		return (float) ( $this->data->amount->total ?? 0 );
	}

	/**
	 * Extract the transaction_id from the event data.
	 *
	 * @since 1.10.0
	 *
	 * @return string Transaction ID.
	 */
	protected function get_transaction_id_from_data(): string {

		return $this->data->sale_id ?? '';
	}
}
