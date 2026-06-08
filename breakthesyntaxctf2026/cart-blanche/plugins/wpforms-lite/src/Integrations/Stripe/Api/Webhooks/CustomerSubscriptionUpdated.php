<?php

namespace WPForms\Integrations\Stripe\Api\Webhooks;

use RuntimeException;
use WPForms\Db\Payments\ValueValidator;

/**
 * Webhook customer.subscription.updated class.
 *
 * @since 1.8.4
 */
class CustomerSubscriptionUpdated extends Base {

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

		$this->delay();

		if (
			( isset( $this->data->object->metadata->canceled_by ) && $this->data->object->metadata->canceled_by === 'wpforms_dashboard' ) ||
			! ValueValidator::is_valid( $this->data->object->status, 'status' )
		) {
			return false;
		}

		$payment = wpforms()->obj( 'payment' )->get_by( 'subscription_id', $this->data->object->id );

		if ( ! $payment ) {
			return false;
		}

		if (
			$payment->subscription_status === $this->data->object->status ||
			( ! empty( $this->data->previous_attributes->status ) && $this->data->previous_attributes->status !== $payment->subscription_status )
		) {
			return true;
		}

		if ( ! wpforms()->obj( 'payment' )->update( $payment->id, [ 'subscription_status' => $this->data->object->status ] ) ) {
			throw new RuntimeException( 'Payment not updated' );
		}

		wpforms()->obj( 'payment_meta' )->add_log(
			$payment->id,
			sprintf( 'Stripe subscription was set to %1$s.', $this->data->object->status )
		);

		return true;
	}
}
