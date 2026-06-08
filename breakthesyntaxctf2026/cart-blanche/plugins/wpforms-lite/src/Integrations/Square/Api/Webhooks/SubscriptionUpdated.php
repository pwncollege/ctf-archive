<?php

namespace WPForms\Integrations\Square\Api\Webhooks;

use RuntimeException;
use WPForms\Db\Payments\UpdateHelpers;

/**
 * Webhook subscription.updated class.
 *
 * @since 1.9.5
 */
class SubscriptionUpdated extends Base {

	/**
	 * Update the subscription status.
	 *
	 * @since 1.9.5
	 *
	 * @return bool
	 *
	 * @throws RuntimeException If payment isn't found or not updated.
	 */
	public function handle(): bool {

		$payment = wpforms()->obj( 'payment' )->get_by( 'subscription_id', $this->data->object->subscription->id );

		if ( ! $payment ) {
			return false;
		}

		// Track canceled subscriptions.
		if ( isset( $this->data->object->subscription->canceled_date ) ) {
			if ( ! UpdateHelpers::cancel_subscription( $payment->id, 'Square subscription cancelled from the Square dashboard.' ) ) {
				throw new RuntimeException( 'Subscription cancellation was not updated.' );
			}

			return true;
		}

		$status = strtolower( $this->data->object->subscription->status );

		// Return true if the subscription status is the same as the status in the webhook data.
		if ( $payment->subscription_status === $status ) {
			return true;
		}

		// Update subscription status.
		if ( ! wpforms()->obj( 'payment' )->update( $payment->id, [ 'subscription_status' => $status ] ) ) {
			throw new RuntimeException( 'Payment not updated' );
		}

		wpforms()->obj( 'payment_meta' )->add_log(
			$payment->id,
			sprintf( 'Square subscription was set to %1$s.', $status )
		);

		return true;
	}
}
