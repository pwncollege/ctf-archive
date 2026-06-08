<?php

namespace WPForms\Integrations\Stripe\Api\Webhooks;

use RuntimeException;
use WPForms\Db\Payments\UpdateHelpers;
use WPForms\Integrations\Stripe\Api\PaymentIntents;
use WPForms\Integrations\Stripe\Api\Webhooks\Exceptions\AmountMismatchException;

/**
 * Webhook charge.refunded class.
 *
 * @since 1.8.4
 */
class ChargeRefunded extends Base {

	/**
	 * Decimals amount.
	 *
	 * @since 1.8.4
	 *
	 * @var int
	 */
	private $decimals_amount;

	/**
	 * Handle the Webhook's data.
	 *
	 * Save refunded amount in the payment meta with key refunded_amount.
	 * Update payment status to 'partrefund' or 'refunded' if refunded amount is equal to total amount.
	 *
	 * @since 1.8.4
	 *
	 * @throws RuntimeException If payment not updated.
	 *
	 * @return bool
	 */
	public function handle() {

		$this->set_payment();

		if ( ! $this->db_payment ) {
			return false;
		}

		$currency              = strtoupper( $this->data->object->currency );
		$this->decimals_amount = wpforms_get_currency_multiplier( $currency );

		$charge = ( new PaymentIntents() )->get_charge( $this->data->object->id );

		if ( isset( $charge->refunds->data[0]->metadata->refunded_by ) && $charge->refunds->data[0]->metadata->refunded_by === 'wpforms_dashboard' ) {
			return false;
		}

		$event_previous_refunded_amount = isset( $this->data->previous_attributes->amount_refunded ) ? $this->data->previous_attributes->amount_refunded : 0;

		if ( $this->get_refunded_amount() !== $event_previous_refunded_amount ) {
			throw new AmountMismatchException( 'Refund amount mismatch detected. Possible reasons: duplicate webhook processing or webhooks received out of order.' );
		}

		// We need to format amount since it doesn't contain decimals, e.g. 525 instead of 5.25.
		$refunded_amount       = $this->data->object->amount_refunded / $this->decimals_amount;
		$last_refund_amount    = $this->get_last_refund_amount() / $this->decimals_amount;
		$last_refund_formatted = wpforms_format_amount( $last_refund_amount, true, $currency );
		$log                   = sprintf( 'Stripe payment refunded from the Stripe dashboard. Refunded amount: %1$s.', $last_refund_formatted );

		if ( ! UpdateHelpers::refund_payment( $this->db_payment, $refunded_amount, $log ) ) {
			throw new RuntimeException( 'Payment not updated' );
		}

		return true;
	}

	/**
	 * Get refunded amount from the database.
	 *
	 * @since 1.9.3
	 *
	 * @return int The refunded amount from the database, in cents.
	 */
	private function get_refunded_amount() {

		$refunded_amount = wpforms()->obj( 'payment_meta' )->get_last_by(
			'refunded_amount',
			$this->db_payment->id
		);

		if ( ! $refunded_amount ) {
			return 0;
		}

		return (int) ( $refunded_amount->meta_value * $this->decimals_amount );
	}

	/**
	 * Get last refund amount.
	 *
	 * @since 1.8.4
	 *
	 * @return int Last refund amount in cents.
	 */
	private function get_last_refund_amount() {

		if ( isset( $this->data->object->refunds->data[0]->amount ) ) {
			return $this->data->object->refunds->data[0]->amount;
		}

		if ( isset( $this->data->previous_attributes->amount_refunded ) ) {
			return $this->data->object->amount_refunded - $this->data->previous_attributes->amount_refunded;
		}

		return $this->data->object->amount_refunded - $this->get_refunded_amount();
	}
}
