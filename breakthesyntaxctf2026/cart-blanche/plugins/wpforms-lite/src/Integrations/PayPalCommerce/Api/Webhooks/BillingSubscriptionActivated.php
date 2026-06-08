<?php

namespace WPForms\Integrations\PayPalCommerce\Api\Webhooks;

use RuntimeException;

/**
 * Handle PayPal event BILLING.SUBSCRIPTION.ACTIVATED.
 *
 * @since 1.10.0
 */
class BillingSubscriptionActivated extends Base {

	/**
	 * Update subscription status to active.
	 *
	 * @since 1.10.0
	 *
	 * @return bool True on success.
	 *
	 * @throws RuntimeException If a payment isn't found or not updated.
	 */
	public function handle(): bool {

		$this->delay();

		$payment = wpforms()->obj( 'payment' )->get_by( 'subscription_id', $this->data->id );

		if ( ! $payment ) {
			return false;
		}

		// The subscription is already activated.
		if ( $payment->subscription_status === 'active' ) {
			return true;
		}

		if ( ! wpforms()->obj( 'payment' )->update( $payment->id, [ 'subscription_status' => 'active' ] ) ) {
			throw new RuntimeException( 'Payment not updated' );
		}

		wpforms()->obj( 'payment_meta' )->add_log(
			$payment->id,
			'PayPal Commerce subscription was set to active.'
		);

		return true;
	}
}
