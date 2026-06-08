<?php

namespace WPForms\Db\Payments;

/**
 * Payment values update helpers class.
 *
 * @since 1.8.4
 */
class UpdateHelpers {

	/**
	 * Refund payment in database.
	 *
	 * @since 1.8.4
	 *
	 * @param Payment $payment_db      Payment DB object.
	 * @param int     $refunded_amount Refunded amount with cent separated.
	 * @param string  $log             Log message.
	 *
	 * @return bool
	 */
	public static function refund_payment( $payment_db, $refunded_amount, $log = '' ) {

		$status = $refunded_amount < $payment_db->total_amount ? 'partrefund' : 'refunded';

		if ( ! wpforms()->obj( 'payment' )->update( $payment_db->id, [ 'status' => $status ] ) ) {
			return false;
		}

		if (
			! wpforms()->obj( 'payment_meta' )->update_or_add(
				$payment_db->id,
				'refunded_amount',
				$refunded_amount
			)
		) {
			return false;
		}

		if ( $log ) {
			wpforms()->obj( 'payment_meta' )->add_log( $payment_db->id, $log );
		}

		return true;
	}

	/**
	 * Cancel subscription in database.
	 *
	 * @since 1.8.4
	 *
	 * @param int    $payment_id Payment ID.
	 * @param string $log        Log message.
	 *
	 * @return bool
	 */
	public static function cancel_subscription( $payment_id, $log = '' ) {

		if ( ! wpforms()->obj( 'payment' )->update( $payment_id, [ 'subscription_status' => 'cancelled' ] ) ) {
			return false;
		}

		if ( $log ) {
			wpforms()->obj( 'payment_meta' )->add_log( $payment_id, $log );
		}

		return true;
	}
}
