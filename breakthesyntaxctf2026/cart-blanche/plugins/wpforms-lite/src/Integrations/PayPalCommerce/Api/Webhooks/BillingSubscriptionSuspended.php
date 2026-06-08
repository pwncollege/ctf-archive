<?php

namespace WPForms\Integrations\PayPalCommerce\Api\Webhooks;

use RuntimeException;

/**
 * Handle PayPal event BILLING.SUBSCRIPTION.SUSPENDED.
 *
 * @since 1.10.0
 */
class BillingSubscriptionSuspended extends Base {

	/**
	 * Set the subscription status to pending when the suspended event is received.
	 *
	 * @since 1.10.0
	 *
	 * @return bool True on success.
	 *
	 * @throws RuntimeException If a payment isn't found or not updated.
	 */
	public function handle(): bool {

		// Some PayPal webhooks may arrive before the payment is saved.
		$this->delay();

		$payment = wpforms()->obj( 'payment' )->get_by( 'subscription_id', $this->data->id );

		if ( ! $payment ) {
			return false;
		}

		// If already pending, nothing to do.
		if ( $payment->subscription_status === 'pending' ) {
			return true;
		}

		if ( ! wpforms()->obj( 'payment' )->update( $payment->id, [ 'subscription_status' => 'pending' ] ) ) {
			throw new RuntimeException( 'Payment not updated' );
		}

		wpforms()->obj( 'payment_meta' )->add_log(
			$payment->id,
			'PayPal Commerce subscription was set to pending.'
		);

		return true;
	}
}
