<?php

namespace WPForms\Integrations\Square\Api\Webhooks;

use RuntimeException;
use WPForms\Db\Payments\UpdateHelpers;

/**
 * Webhook refund.updated class.
 *
 * @since 1.9.5
 */
class RefundUpdated extends Base {

	/**
	 * Handle the Webhook's data.
	 *
	 * Save refunded amount in the payment meta with key refunded_amount.
	 * Update payment status to 'partrefund' or 'refunded' if refunded amount is equal to the total amount.
	 *
	 * @since 1.9.5
	 *
	 * @throws RuntimeException If payment isn't updated.
	 *
	 * @return bool
	 */
	public function handle(): bool {

		$this->set_payment();

		if ( $this->db_payment === null ) {
			throw new RuntimeException( 'Refund Update Event: Payment has not been found and set.' );
		}

		// Perform refund only if it's allowed.
		if ( ! $this->is_refund_allowed() ) {
			return false;
		}

		$currency        = strtoupper( $this->data->object->refund->amount_money->currency );
		$decimals_amount = wpforms_get_currency_multiplier( $currency );

		// We need to format the amount since it doesn't contain decimals, e.g., 525 instead of 5.25.
		$refunded_amount           = ( $decimals_amount !== 0 ) ? ( $this->data->object->refund->amount_money->amount / $decimals_amount ) : 0;
		$refunded_amount_formatted = wpforms_format_amount( $refunded_amount, true, $currency );
		$log                       = sprintf( 'Square payment refunded from the Square dashboard. Refunded amount: %1$s.', $refunded_amount_formatted );
		$total_amount_refund       = wpforms()->obj( 'payment_meta' )->get_single( $this->db_payment->id, 'total_refunded_amount' );

		if ( empty( $total_amount_refund ) ) {
			$total_amount_refund = $refunded_amount;
		}

		if ( ! UpdateHelpers::refund_payment( $this->db_payment, $total_amount_refund, $log ) ) {
			/* translators: %s - transaction id. */
			$log = sprintf( __( 'Payment for transaction %s was not updated.', 'wpforms-lite' ), $this->db_payment->transaction_id );

			throw new RuntimeException( esc_html( $log ) );
		}

		return true;
	}

	/**
	 * Check if refund is allowed.
	 *
	 * @since 1.9.5
	 *
	 * @return bool
	 */
	private function is_refund_allowed(): bool {

		if ( ! $this->db_payment ) {
			return false;
		}

		// Do not track uncompleted refunds.
		if ( $this->data->object->refund->status !== 'COMPLETED' ) {
			return false;
		}

		// Do not track refunds that were not requested by the customer.
		if ( ! empty( $this->data->object->refund->reason ) && $this->data->object->refund->reason !== 'Requested by customer' ) {
			return false;
		}

		// Square sends two webhooks for a refund with the same COMPLETED statuses,
		// but the final is the one with the fee included.
		if ( ! isset( $this->data->object->refund->processing_fee ) ) {
			return false;
		}

		return true;
	}
}
