<?php

namespace WPForms\Integrations\Square\Admin\Payments;

use WPForms\Db\Payments\UpdateHelpers;
use WPForms\Integrations\Square\Api\Api;
use WPForms\Integrations\Square\Connection;
use WPForms\Integrations\Square\Helpers;

/**
 * Things related to Square  functionality on single payment screen.
 *
 * @since 1.9.5
 */
class SingleActionsHandler {

	/**
	 * Main class that communicates with the Square API.
	 *
	 * @since 1.9.5
	 *
	 * @var Api
	 */
	private $api;

	/**
	 * Initialize.
	 *
	 * @since 1.9.5
	 */
	public function init() {

		// Set an API instance.
		$this->api = new Api( Connection::get() );

		$this->hooks();
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.9.5
	 */
	private function hooks() {

		if ( wpforms_is_admin_ajax() ) {
			add_action( 'wp_ajax_wpforms_square_payments_refund', [ $this, 'ajax_payment_refund' ] );
			add_action( 'wp_ajax_wpforms_square_payments_cancel', [ $this, 'ajax_payments_cancel' ] );

			return;
		}

		add_filter( 'wpforms_admin_strings', [ $this, 'admin_strings' ] );
	}

	/**
	 * Add admin strings related to payments.
	 *
	 * @since 1.9.5
	 *
	 * @param array $admin_strings Admin strings.
	 *
	 * @return array
	 */
	public function admin_strings( $admin_strings ): array {

		$admin_strings = (array) $admin_strings;

		$admin_strings['single_payment_button_handlers'][] = 'square';

		return $admin_strings;
	}

	/**
	 * Refund a single payment.
	 *
	 * Handler for ajax request with action "wpforms_payments_refund".
	 *
	 * @since 1.9.5
	 */
	public function ajax_payment_refund() {

		$payment_db = $this->get_db_payment();

		$amount_to_refund = $payment_db->total_amount;

		if ( $payment_db->status === 'partrefund' ) {

			$already_refunded = wpforms()->obj( 'payment_meta' )->get_single( $payment_db->id, 'refunded_amount' );
			$amount_to_refund = $payment_db->total_amount - $already_refunded;
		}

		$args = [
			'amount'   => Helpers::format_amount( $amount_to_refund ),
			'currency' => $payment_db->currency,
			'reason'   => 'Requested by customer',
		];

		$refund = $this->api->refund_payment( $payment_db->transaction_id, $args );

		if ( ! $refund ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Refund failed.', 'wpforms-lite' ) ] );
		}

		$log = sprintf(
			'Square payment refunded from the WPForms plugin interface. Refunded amount: %1$s.',
			wpforms_format_amount( wpforms_sanitize_amount( $amount_to_refund ), true )
		);

		if ( UpdateHelpers::refund_payment( $payment_db, $payment_db->total_amount, $log ) ) {
			wp_send_json_success( [ 'message' => esc_html__( 'Refund successful.', 'wpforms-lite' ) ] );
		}

		wp_send_json_error( [ 'message' => esc_html__( 'Saving refund in the database failed.', 'wpforms-lite' ) ] );
	}

	/**
	 * Cancel subscription.
	 *
	 * Handler for ajax request with action "wpforms_payments_cancel_subscription".
	 *
	 * @since 1.9.5
	 */
	public function ajax_payments_cancel() {

		$payment_db = $this->get_db_payment();

		$cancel = $this->api->cancel_subscription( $payment_db->subscription_id );

		if ( ! $cancel ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Subscription cancellation failed.', 'wpforms-lite' ) ] );
		}

		if ( UpdateHelpers::cancel_subscription( $payment_db->id, 'Square subscription cancelled from the WPForms plugin interface.' ) ) {
			wp_send_json_success( [ 'message' => esc_html__( 'Subscription cancelled.', 'wpforms-lite' ) ] );
		}

		wp_send_json_error( [ 'message' => esc_html__( 'Updating subscription in the database failed.', 'wpforms-lite' ) ] );
	}

	/**
	 * Retrieve the payment from the database.
	 *
	 * @since 1.9.5
	 *
	 * @return object|null
	 */
	private function get_db_payment() {

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
