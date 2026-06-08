<?php

namespace WPForms\Integrations\PayPalCommerce\Admin\Payments;

use WPForms\Db\Payments\UpdateHelpers;
use WPForms\Integrations\PayPalCommerce\Api\Api;
use WPForms\Integrations\PayPalCommerce\Helpers;
use WPForms\Integrations\PayPalCommerce\Connection;
use WPForms\Integrations\PayPalCommerce\PayPalCommerce;

/**
 * Things related to PayPal Commerce functionality on a single payment screen.
 *
 * @since 1.10.0
 */
class SingleActionsHandler {

	/**
	 * Main class that communicates with the PayPal Commerce API.
	 *
	 * @since 1.10.0
	 *
	 * @var Api
	 */
	private $api;

	/**
	 * Initialize.
	 *
	 * @since 1.10.0
	 */
	public function init(): void {

		// Set an API instance.
		$this->api = PayPalCommerce::get_api( Connection::get() );

		$this->hooks();
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.10.0
	 */
	private function hooks(): void {

		if ( wpforms_is_admin_ajax() ) {
			add_action( 'wp_ajax_wpforms_paypal_commerce_payments_refund', [ $this, 'ajax_payment_refund' ] );
			add_action( 'wp_ajax_wpforms_paypal_commerce_payments_cancel', [ $this, 'ajax_payments_cancel' ] );

			return;
		}

		add_filter( 'wpforms_admin_strings', [ $this, 'admin_strings' ] );
	}

	/**
	 * Add admin strings related to payments.
	 *
	 * @since 1.10.0
	 *
	 * @param array $admin_strings Admin strings.
	 *
	 * @return array
	 *
	 * @noinspection PhpMissingParamTypeInspection
	 */
	public function admin_strings( $admin_strings ): array {

		$admin_strings = (array) $admin_strings;

		$admin_strings['single_payment_button_handlers'][] = PayPalCommerce::SLUG;

		return $admin_strings;
	}

	/**
	 * Refund a single payment.
	 *
	 * Handler for ajax request with the action "wpforms_payments_refund".
	 *
	 * @since 1.10.0
	 */
	public function ajax_payment_refund(): void {

		$payment_db  = $this->get_db_payment();
		$is_refunded = false;

		if ( $payment_db && isset( $payment_db->transaction_id ) ) {
			$is_refunded = $this->api->refund_payment( $payment_db->transaction_id );
		}

		if ( ! $is_refunded ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Refund failed.', 'wpforms-lite' ) ] );
		}

		if ( $payment_db->status === 'partrefund' ) {
			$already_refunded = wpforms()->obj( 'payment_meta' )->get_single( $payment_db->id, 'refunded_amount' );
			$amount_to_log    = ( (float) $payment_db->total_amount ) - ( (float) $already_refunded );
		} else {
			$amount_to_log = $payment_db->total_amount;
		}

		$log = sprintf(
			'PayPal Commerce payment refunded from the WPForms plugin interface. Refunded amount: %1$s.',
			wpforms_format_amount( wpforms_sanitize_amount( $amount_to_log ), true )
		);

		if ( UpdateHelpers::refund_payment( $payment_db, $payment_db->total_amount, $log ) ) {
			wp_send_json_success( [ 'message' => esc_html__( 'Refund successful.', 'wpforms-lite' ) ] );
		}

		wp_send_json_error( [ 'message' => esc_html__( 'Saving refund in the database failed.', 'wpforms-lite' ) ] );
	}

	/**
	 * Handle AJAX subscription cancellation.
	 *
	 * Triggered when the user clicks "Cancel" in WPForms Payments admin single payment page.
	 *
	 * @since 1.10.0
	 */
	public function ajax_payments_cancel(): void {

		$payment_db = $this->get_db_payment();

		if ( ! $payment_db || empty( $payment_db->subscription_id ) ) {
			wp_send_json_error(
				[
					'message' => esc_html__( 'Missing or invalid subscription information.', 'wpforms-lite' ),
				]
			);
		}

		$result = Helpers::is_license_ok()
			? $this->cancel_via_paypal( $payment_db )
			: $this->cancel_via_processor( $payment_db );

		if ( $result['success'] ) {
			wp_send_json_success( [ 'message' => $result['message'] ] );
		}

		wp_send_json_error( [ 'message' => $result['message'] ] );
	}

	/**
	 * Cancel a subscription via the PayPal Commerce API.
	 *
	 * @since 1.10.0
	 *
	 * @param object $payment_db Payment record from the database.
	 *
	 * @return array
	 */
	private function cancel_via_paypal( object $payment_db ): array {

		try {
			$subscription = $this->api->get_subscription( $payment_db->subscription_id );

			if ( empty( $subscription ) ) {
				return [
					'success' => false,
					'message' => esc_html__( 'Unable to fetch subscription details from PayPal. Please try again later.', 'wpforms-lite' ),
				];
			}

			$status              = strtoupper( (string) ( $subscription['status'] ?? '' ) );
			$cancelable_statuses = [ 'ACTIVE', 'SUSPENDED' ];

			if ( ! in_array( $status, $cancelable_statuses, true ) ) {
				return [
					'success' => false,
					'message' => sprintf( /* translators: %s - PayPal subscription status. */
						esc_html__( 'Subscription cannot be cancelled because its PayPal status is %s. Only Active or Suspended subscriptions can be cancelled.', 'wpforms-lite' ),
						esc_html( ucfirst( strtolower( $status ) ) )
					),
				];
			}

			$cancel_note = 'PayPal Commerce subscription cancelled from the WPForms plugin interface.';

			$response = $this->api->cancel_subscription( $payment_db->subscription_id );

			if ( ! $response ) {
				return [
					'success' => false,
					'message' => esc_html__( 'PayPal API subscription cancellation failed.', 'wpforms-lite' ),
				];
			}

			// Update local database.
			if ( UpdateHelpers::cancel_subscription( $payment_db->id, $cancel_note ) ) {
				return [
					'success' => true,
					'message' => esc_html__( 'Subscription cancelled successfully.', 'wpforms-lite' ),
				];
			}

			return [
				'success' => false,
				'message' => esc_html__( 'Subscription was cancelled on PayPal but failed to update locally.', 'wpforms-lite' ),
			];

		} catch ( \Throwable $e ) {
			return [
				'success' => false,
				'message' => sprintf( /* translators: %s - PayPal API error message. */
					esc_html__( 'Unexpected error while cancelling subscription: %s', 'wpforms-lite' ),
					esc_html( $e->getMessage() )
				),
			];
		}
	}

	/**
	 * Cancel a subscription for Lite users.
	 * Used when a license is invalid or not set.
	 *
	 * @since 1.10.0
	 *
	 * @param object $payment_db Payment record from the database.
	 *
	 * @return array
	 */
	private function cancel_via_processor( object $payment_db ): array {

		try {

			$cancel_note = 'PayPal Commerce subscription cancelled from the WPForms plugin interface.';
			$response    = $this->api->subscription_processor_cancel( $payment_db->subscription_id );

			if ( $response->has_errors() ) {
				return [
					'success' => false,
					'message' => esc_html__( 'PayPal API subscription cancellation failed.', 'wpforms-lite' ),
				];
			}

			if ( UpdateHelpers::cancel_subscription( $payment_db->id, $cancel_note ) ) {
				return [
					'success' => true,
					'message' => esc_html__( 'Subscription cancelled successfully.', 'wpforms-lite' ),
				];
			}

			return [
				'success' => false,
				'message' => esc_html__( 'Subscription was cancelled remotely but not updated locally.', 'wpforms-lite' ),
			];

		} catch ( \Throwable $e ) {
			return [
				'success' => false,
				'message' => sprintf( /* translators: %s - PayPal API error message. */
					esc_html__( 'Unexpected error while cancelling via processor: %s', 'wpforms-lite' ),
					esc_html( $e->getMessage() )
				),
			];
		}
	}

	/**
	 * Retrieve the payment from the database.
	 *
	 * @since 1.10.0
	 *
	 * @return object
	 */
	private function get_db_payment(): object {

		if ( ! isset( $_POST['payment_id'] ) || ! wpforms_current_user_can( wpforms_get_capability_manage_options() ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'You are not allowed to perform this action.', 'wpforms-lite' ) ] );
		}

		check_ajax_referer( 'wpforms-admin', 'nonce' );

		$payment_id = (int) $_POST['payment_id'];
		$payment_db = wpforms()->obj( 'payment' )->get( $payment_id );

		if ( empty( $payment_db ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Payment not found in the database.', 'wpforms-lite' ) ] );
		}

		return $payment_db;
	}
}
