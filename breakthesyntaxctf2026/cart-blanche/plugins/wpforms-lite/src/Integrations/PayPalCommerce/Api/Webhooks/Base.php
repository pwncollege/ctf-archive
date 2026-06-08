<?php

namespace WPForms\Integrations\PayPalCommerce\Api\Webhooks;

use RuntimeException;
use WPForms\Integrations\PayPalCommerce\Connection;
use WPForms\Integrations\PayPalCommerce\PayPalCommerce;

/**
 * Webhook base class.
 *
 * @since 1.10.0
 */
abstract class Base {

	/**
	 * Event type.
	 *
	 * @since 1.10.0
	 *
	 * @var string
	 */
	protected $type;

	/**
	 * Event data.
	 *
	 * @since 1.10.0
	 *
	 * @var object
	 */
	protected $data;

	/**
	 * Payment object.
	 *
	 * @since 1.10.0
	 *
	 * @var object
	 */
	protected $db_payment;

	/**
	 * Webhook setup.
	 *
	 * @since 1.10.0
	 *
	 * @param object $event Webhook event object.
	 */
	public function setup( object $event ): void {

		$this->data = $event->resource;
		$this->type = $event->event_type;

		$this->hooks();
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.10.0
	 */
	private function hooks(): void {

		add_filter( 'wpforms_current_user_can', '__return_true' );
	}

	/**
	 * Handle the Webhook's data.
	 *
	 * @since 1.10.0
	 *
	 * @return bool
	 */
	abstract public function handle(): bool;

	/**
	 * Set the payment object from a database. If payment is not registered yet in DB, throw the exception.
	 *
	 * @since 1.10.0
	 */
	protected function set_payment(): void {

		$transaction_id = $this->get_transaction_id_from_data();

		$this->db_payment = wpforms()->obj( 'payment' )->get_by( 'transaction_id', $transaction_id );
	}

	/**
	 * Extract the transaction_id from the event data.
	 *
	 * @since 1.10.0
	 *
	 * @return string Transaction ID.
	 */
	protected function get_transaction_id_from_data(): string {

		return $this->data->id ?? '';
	}

	/**
	 * Delay webhook handling.
	 *
	 * PPC sends some webhooks before payment is saved in our database.
	 * Sometimes it is required to wait until form submission has ended and payment is saved in the database.
	 *
	 * @since 1.10.0
	 */
	protected function delay(): void {

		sleep( 5 );
	}

	/**
	 * Check if the payment is initial (first) for the subscription.
	 *
	 * @since 1.10.0
	 *
	 * @return string
	 */
	protected function is_initial_subscription_payment(): string {

		// Try to get the data from the webhook if present.
		if ( isset( $this->data->is_initial ) ) {
			return true;
		}

		// Check if it's a subscription simulation renewal.
		if ( isset( $this->data->is_renewal ) ) {
			return false;
		}

		$subscription_id = $this->data->billing_agreement_id ?? '';

		$completed_cycles_count = $this->get_subscription_completed_cycle_number( $subscription_id );

		// The first ever payment or the first before activation.
		return $completed_cycles_count === 0 || $completed_cycles_count === 1;
	}

	/**
	 * Get the refunded amount from the database.
	 *
	 * @param string $payment_id Payment ID.
	 *
	 * @since 1.10.0
	 *
	 * @return float Refunded amount.
	 */
	protected function get_refunded_amount( string $payment_id ): float {

		$refunded_amount = wpforms()->obj( 'payment_meta' )->get_last_by(
			'refunded_amount',
			$payment_id
		);

		if ( ! $refunded_amount ) {
			return 0.0;
		}

		return (float) $refunded_amount->meta_value;
	}

	/**
	 * Validate the refund amount to prevent duplicate webhook processing.
	 *
	 * @since 1.10.0
	 *
	 * @param string $refunded_amount    Refunded amount from PayPal (formatted).
	 * @param string $db_refunded_amount Refunded amount from the database (formatted).
	 * @param string $last_refund_amount Last refund amount (formatted).
	 *
	 * @return bool
	 */
	protected function is_valid_refund_amount( string $refunded_amount, string $db_refunded_amount, string $last_refund_amount ): bool {

		$expected_amount = wpforms_format_amount( (float) $db_refunded_amount + (float) $last_refund_amount );

		return $refunded_amount === $expected_amount;
	}

	/**
	 * Check if this is a full refund.
	 *
	 * @since 1.10.0
	 *
	 * @param float $refunded_amount Refunded amount.
	 * @param float $total_amount    Total payment amount.
	 *
	 * @return string
	 */
	protected function get_payment_status( float $refunded_amount, float $total_amount ): string {

		return $refunded_amount >= $total_amount ? 'refunded' : 'partrefund';
	}


	/**
	 * Update the payment status.
	 *
	 * @since 1.10.0
	 *
	 * @param string $payment_id Payment ID.
	 * @param string $status     Available values.
	 *
	 * @throws RuntimeException If payment status wasn't updated.
	 */
	protected function update_payment_status( string $payment_id, string $status ): void {

		$updated_payment = wpforms()->obj( 'payment' )->update(
			$payment_id,
			[
				'status' => $status,
			]
		);

		if ( ! $updated_payment ) {
			throw new RuntimeException( 'Payment not updated' );
		}
	}

	/**
	 * Update the refunded amount meta.
	 *
	 * @since 1.10.0
	 *
	 * @param string $payment_id      Payment ID.
	 * @param float  $refunded_amount Refunded amount.
	 *
	 * @throws RuntimeException If payment meta wasn't updated.
	 */
	protected function update_refunded_amount_payment_meta( string $payment_id, float $refunded_amount ): void {

		$updated_payment_meta = wpforms()->obj( 'payment_meta' )->update_or_add(
			$payment_id,
			'refunded_amount',
			$refunded_amount
		);

		if ( ! $updated_payment_meta ) {
			throw new RuntimeException( 'Payment meta not updated' );
		}
	}

	/**
	 * Add a log entry for the refund.
	 *
	 * @since 1.10.0
	 *
	 * @param string $payment_id         Payment ID.
	 * @param float  $last_refund_amount Last refund amount.
	 * @param string $currency           Currency code.
	 */
	protected function add_refund_log( string $payment_id, float $last_refund_amount, string $currency ): void {

		$formatted_amount = wpforms_format_amount( $last_refund_amount, true, $currency );

		wpforms()->obj( 'payment_meta' )->add_log(
			$payment_id,
			sprintf(
				'PayPal Commerce payment refunded from the PayPal dashboard. Refunded amount: %1$s.',
				$formatted_amount
			)
		);
	}

	/**
	 * Get current completed cycles count of the subscription.
	 *
	 * @since 1.10.0
	 *
	 * @param string $subscription_id Subscription ID.
	 *
	 * @return int
	 */
	private function get_subscription_completed_cycle_number( string $subscription_id ): int {

		$api          = PayPalCommerce::get_api( Connection::get() );
		$subscription = $api->get_subscription( $subscription_id );

		if ( empty( $subscription ) ) {
			return 0;
		}

		$executions = $subscription['billing_info']['cycle_executions'] ?? [];

		foreach ( $executions as $execution ) {
			if ( $execution['tenure_type'] === 'REGULAR' ) {
				return (int) ( $execution['cycles_completed'] ?? 0 );
			}
		}

		return 0;
	}
}
