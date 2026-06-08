<?php

namespace WPForms\Integrations\Square\Api\Webhooks;

use RuntimeException;

/**
 * Webhook subscription.created class.
 *
 * @since 1.9.5
 */
class SubscriptionCreated extends Base {

	/**
	 * Set subscription status to active.
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

		if ( ! wpforms()->obj( 'payment' )->update( $payment->id, [ 'subscription_status' => 'active' ] ) ) {
			throw new RuntimeException( 'Payment not updated' );
		}

		wpforms()->obj( 'payment_meta' )->add_log(
			$payment->id,
			'Square subscription was set to active.'
		);

		return true;
	}
}
