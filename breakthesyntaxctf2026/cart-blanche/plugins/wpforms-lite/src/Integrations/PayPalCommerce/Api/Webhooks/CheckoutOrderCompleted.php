<?php

namespace WPForms\Integrations\PayPalCommerce\Api\Webhooks;

use stdClass;

/**
 * Handle PayPal event CHECKOUT.ORDER.COMPLETED.
 *
 * @since 1.10.0
 */
class CheckoutOrderCompleted extends Base {

	/**
	 * Execute handler logic.
	 *
	 * @since 1.10.0
	 *
	 * @return bool True on success.
	 */
	public function handle(): bool {

		$this->set_payment();

		if ( ! $this->db_payment ||
			$this->db_payment->status === 'refunded' ||
			empty( $this->data->payment_source ) ||
			! empty( $this->db_payment->subscription_id )
		) {
			return false;
		}

		// Update payment method details to keep them up to date.
		$this->update_payment_method_details( $this->data->payment_source );

		wpforms()->obj( 'payment_meta' )->add_log(
			$this->db_payment->id,
			'Checkout Order Completed. Payment data is updated.'
		);

		return true;
	}

	/**
	 * Update payment method details meta data.
	 *
	 * @since 1.10.0
	 *
	 * @param stdClass $details Payment details object from PayPal API.
	 */
	private function update_payment_method_details( stdClass $details ): void {

		$payment_id = $this->db_payment->id;

		// Prepare the metadata based on payment source details.
		$meta = $this->prepare_meta_data( $details );

		$payment_meta_obj = wpforms()->obj( 'payment_meta' );

		if ( ! $payment_meta_obj ) {
			return;
		}

		// Store payment meta.
		$payment_meta_obj->bulk_add( $payment_id, $meta );
	}

	/**
	 * Build the structured metadata array from payment source details.
	 * Supports PayPal, Card, Apple Pay, Google Pay, Venmo, and Fastlane.
	 *
	 * @since 1.10.0
	 *
	 * @param stdClass $details Payment details object.
	 *
	 * @return array Prepared metadata key/value pairs.
	 */
	private function prepare_meta_data( stdClass $details ): array { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.MaxExceeded

		$meta = [];

		// Determine the payment source and extract the corresponding fields.
		if ( isset( $details->card ) ) {

			$meta['method_type']         = 'card';
			$meta['credit_card_last4']   = sanitize_text_field( $details->card->last_digits ?? '' );
			$meta['credit_card_method']  = sanitize_text_field( $details->card->brand ?? '' );
			$meta['credit_card_expires'] = sanitize_text_field( $details->card->expiry ?? '' );

		} elseif ( isset( $details->paypal ) ) {

			$meta['method_type']    = 'paypal';
			$meta['paypal_email']   = sanitize_email( $details->paypal->email_address ?? '' );
			$meta['paypal_account'] = sanitize_text_field( $details->paypal->account_id ?? '' );
			$meta['payer_name']     = trim(
				sanitize_text_field( $details->paypal->name->given_name ?? '' ) . ' ' .
				sanitize_text_field( $details->paypal->name->surname ?? '' )
			);
			$meta['country_code']   = sanitize_text_field( $details->paypal->address->country_code ?? '' );

		} elseif ( isset( $details->apple_pay ) ) {

			$meta['method_type']      = 'apple_pay';
			$meta['apple_pay_wallet'] = sanitize_text_field( $details->apple_pay->wallet ?? 'Apple Pay' );
			$meta['country_code']     = sanitize_text_field( $details->apple_pay->address->country_code ?? '' );

			if ( ! empty( $details->apple_pay->card ) ) {
				$meta['credit_card_last4']  = sanitize_text_field( $details->apple_pay->card->last_digits ?? '' );
				$meta['credit_card_method'] = sanitize_text_field( $details->apple_pay->card->brand ?? '' );
			}
		} elseif ( isset( $details->google_pay ) || isset( $details->gpay ) ) {

			$gpay = $details->google_pay ?? $details->gpay;

			$meta['method_type']       = 'google_pay';
			$meta['google_pay_wallet'] = sanitize_text_field( $gpay->wallet ?? 'Google Pay' );
			$meta['country_code']      = sanitize_text_field( $gpay->address->country_code ?? '' );

			if ( ! empty( $gpay->card ) ) {
				$meta['credit_card_last4']  = sanitize_text_field( $gpay->card->last_digits ?? '' );
				$meta['credit_card_method'] = sanitize_text_field( $gpay->card->brand ?? '' );
			}
		} elseif ( isset( $details->venmo ) ) {

			$meta['method_type']    = 'venmo';
			$meta['venmo_username'] = sanitize_text_field( $details->venmo->username ?? '' );
			$meta['country_code']   = sanitize_text_field( $details->venmo->address->country_code ?? '' );

		} else {

			$meta['method_type'] = 'unknown';
		}

		return $meta;
	}

	/**
	 * Extract the transaction_id from the event data.
	 *
	 * @since 1.10.0
	 *
	 * @return string Transaction ID.
	 */
	protected function get_transaction_id_from_data(): string {

		return $this->data->purchase_units[0]->payments->captures[0]->id ?? '';
	}
}
