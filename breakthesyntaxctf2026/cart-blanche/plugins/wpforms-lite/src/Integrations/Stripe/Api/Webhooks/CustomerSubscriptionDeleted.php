<?php

namespace WPForms\Integrations\Stripe\Api\Webhooks;

use RuntimeException;
use WPForms\Db\Payments\UpdateHelpers;

/**
 * Webhook customer.subscription.deleted class.
 *
 * @since 1.8.4
 */
class CustomerSubscriptionDeleted extends Base {

	/**
	 * Handle the Webhook's data.
	 *
	 * @since 1.8.4
	 *
	 * @throws RuntimeException If payment not found or not updated.
	 *
	 * @return bool
	 */
	public function handle() {

		$payment = wpforms()->obj( 'payment' )->get_by( 'subscription_id', $this->data->object->id );

		if ( ! $payment ) {
			return false;
		}

		if ( isset( $this->data->object->metadata->canceled_by ) && $this->data->object->metadata->canceled_by === 'wpforms_dashboard' ) {
			return false;
		}

		if ( ! UpdateHelpers::cancel_subscription( $payment->id, 'Stripe subscription cancelled from the Stripe dashboard.' ) ) {
			throw new RuntimeException( 'Payment not updated' );
		}

		return true;
	}
}
