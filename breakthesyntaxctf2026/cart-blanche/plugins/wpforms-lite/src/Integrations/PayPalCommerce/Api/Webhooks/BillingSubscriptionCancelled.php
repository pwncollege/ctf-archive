<?php

namespace WPForms\Integrations\PayPalCommerce\Api\Webhooks;

use RuntimeException;
use WPForms\Db\Payments\UpdateHelpers;

/**
 * Handle PayPal event BILLING.SUBSCRIPTION.CANCELLED.
 *
 * @since 1.10.0
 */
class BillingSubscriptionCancelled extends Base {

	/**
	 * Cancel subscription. Update status.
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

		if ( ! UpdateHelpers::cancel_subscription( $payment->id, 'PayPal Commerce subscription cancelled from the PayPal dashboard.' ) ) {
			throw new RuntimeException( 'Payment not updated' );
		}

		return true;
	}
}
