<?php

namespace WPForms\Integrations\PayPalCommerce\Api\Webhooks;

/**
 * Handle PayPal event PAYMENT.CAPTURE.DENIED.
 *
 * @since 1.10.0
 */
class PaymentCaptureDenied extends Base {

	/**
	 * Update payment to denied.
	 *
	 * @since 1.10.0
	 *
	 * @return bool
	 */
	public function handle(): bool {

		$this->set_payment();

		if ( ! $this->db_payment ) {
			return false;
		}

		if ( $this->data->status !== 'DENIED' ) {
			return false;
		}

		wpforms()->obj( 'payment' )->update(
			$this->db_payment->id,
			[
				'status'           => 'failed',
				'date_updated_gmt' => gmdate( 'Y-m-d H:i:s' ),
			]
		);

		return true;
	}
}
