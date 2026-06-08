<?php

namespace WPForms\Integrations\PayPalCommerce\Api\Webhooks;

use RuntimeException;

/**
 * Handle PayPal event BILLING.SUBSCRIPTION.UPDATED.
 *
 * @since 1.10.0
 */
class BillingSubscriptionUpdated extends Base {

	/**
	 * Update subscription status based on webhook data.
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

		// Determine the new subscription status from the webhook resource.
		$status = isset( $this->data->status ) ? strtolower( $this->data->status ) : '';

		if ( $status === '' ) {
			return false;
		}

		// Let the dedicated cancellation handler process cancellations.
		if ( $status === 'cancelled' || $status === 'canceled' ) {
			return true;
		}

		// If status hasn't changed, nothing to do.
		if ( $payment->subscription_status === $status ) {
			return true;
		}

		if ( ! wpforms()->obj( 'payment' )->update( $payment->id, [ 'subscription_status' => $status ] ) ) {
			throw new RuntimeException( 'Payment not updated' );
		}

		wpforms()->obj( 'payment_meta' )->add_log(
			$payment->id,
			sprintf( 'PayPal Commerce subscription was set to %1$s.', $status )
		);

		return true;
	}
}
