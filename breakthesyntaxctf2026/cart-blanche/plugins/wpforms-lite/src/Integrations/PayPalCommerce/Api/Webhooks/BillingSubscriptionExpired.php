<?php

namespace WPForms\Integrations\PayPalCommerce\Api\Webhooks;

use stdClass;
use RuntimeException;

/**
 * Handle PayPal event BILLING.SUBSCRIPTION.EXPIRED.
 *
 * @since 1.10.0
 */
class BillingSubscriptionExpired extends Base {

	/**
	 * Update subscription status to complete.
	 *
	 * @since 1.10.0
	 *
	 * @return bool True on success.
	 *
	 * @throws RuntimeException If a payment isn't found or not updated.
	 */
	public function handle(): bool {

		$payment = wpforms()->obj( 'payment' )->get_by( 'subscription_id', $this->data->id );

		if ( ! $payment ) {
			return false;
		}

		$billing = $this->get_payment_billing_data();

		if ( ! $billing ) {
			return false;
		}

		$total_cycles     = (int) ( $billing->total_cycles ?? 0 );
		$cycles_completed = (int) ( $billing->cycles_completed ?? 0 );

		// Only complete if regular billing cycles finished.
		if ( $total_cycles <= 0 || $cycles_completed <= 0 ) {
			return false;
		}

		if ( $total_cycles !== $cycles_completed ) {
			return false;
		}

		if ( ! wpforms()->obj( 'payment' )->update( $payment->id, [ 'subscription_status' => 'completed' ] ) ) {
			throw new RuntimeException( 'Payment not updated' );
		}

		wpforms()->obj( 'payment_meta' )->add_log(
			$payment->id,
			sprintf( 'PayPal Commerce subscription was completed.' )
		);

		return true;
	}

	/**
	 * Retrieve the REGULAR billing cycle execution data from the subscription payload.
	 *
	 * PayPal may return multiple cycle executions (e.g., TRIAL, REGULAR).
	 * This method extracts only the REGULAR tenure cycle. Returns null if not found.
	 *
	 * @since 1.10.0
	 *
	 * @return stdClass|null The REGULAR cycle execution object, or null if unavailable.
	 */
	private function get_payment_billing_data(): ?stdClass {

		// Find the REGULAR tenure cycle.
		$cycles  = $this->data->billing_info->cycle_executions ?? [];
		$regular = null;

		foreach ( $cycles as $cycle ) {
			if ( isset( $cycle->tenure_type ) && $cycle->tenure_type === 'REGULAR' ) {
				$regular = $cycle;

				break;
			}
		}

		return $regular;
	}
}
