<?php

namespace WPForms\Integrations\Stripe\Admin\Payments;

use WPForms\Integrations\Stripe\Api\PaymentIntents;
use WPForms\Db\Payments\UpdateHelpers;
use WPForms\Integrations\Stripe\Helpers;

/**
 * Things related to Stripe functionality on single payment screen.
 *
 * @since 1.8.4
 */
class SingleActionsHandler {

	/**
	 * Gateway name.
	 *
	 * @since 1.8.4
	 *
	 * @var string
	 */
	const GATEWAY = 'stripe';

	/**
	 * PaymentIntents API.
	 *
	 * @since 1.8.4
	 *
	 * @var PaymentIntents
	 */
	private $payment_intents;

	/**
	 * Initialize.
	 *
	 * @since 1.8.4
	 *
	 * @param PaymentIntents $payment_intents PaymentIntents API.
	 *
	 * @return $this
	 */
	public function init( $payment_intents ) {

		$this->payment_intents = $payment_intents;

		$this->hooks();

		return $this;
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.8.4
	 */
	private function hooks() {

		if ( wpforms_is_admin_ajax() ) {
			add_action( 'wp_ajax_wpforms_stripe_payments_refund', [ $this, 'ajax_single_payment_refund' ] );
			add_action( 'wp_ajax_wpforms_stripe_payments_cancel', [ $this, 'ajax_single_payment_cancel' ] );

			return;
		}

		add_filter( 'wpforms_admin_strings', [ $this, 'admin_strings' ] );
	}

	/**
	 * Add admin strings related to payments.
	 *
	 * @since 1.8.4
	 *
	 * @param array $admin_strings Admin strings.
	 *
	 * @return array
	 */
	public function admin_strings( $admin_strings ) {

		$admin_strings['single_payment_button_handlers'][] = self::GATEWAY;

		return $admin_strings;
	}

	/**
	 * Refund a single payment.
	 *
	 * Handler for ajax request with action "wpforms_payments_refund".
	 *
	 * @since 1.8.4
	 */
	public function ajax_single_payment_refund() {

		if ( ! isset( $_POST['payment_id'] ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Missing payment ID.', 'wpforms-lite' ) ] );
		}

		if ( ! wpforms_current_user_can( wpforms_get_capability_manage_options() ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'You are not allowed to perform this action.', 'wpforms-lite' ) ] );
		}

		$this->check_payment_collection_type();
		check_ajax_referer( 'wpforms-admin', 'nonce' );

		$payment_id = (int) $_POST['payment_id'];
		$payment_db = wpforms()->obj( 'payment' )->get( $payment_id );

		if ( empty( $payment_db ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Payment not found in the database.', 'wpforms-lite' ) ] );
		}

		$args = [
			'metadata' => [
				'refunded_by' => 'wpforms_dashboard',
			],
			'reason'   => 'requested_by_customer',
		];

		$refund = $this->payment_intents->refund_payment( $payment_db->transaction_id, $args );

		if ( ! $refund ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Refund failed.', 'wpforms-lite' ) ] );
		}

		if ( $payment_db->status === 'partrefund' ) {

			$already_refunded = wpforms()->obj( 'payment_meta' )->get_single( $payment_db->id, 'refunded_amount' );
			$amount_to_log    = $payment_db->total_amount - $already_refunded;
		} else {
			$amount_to_log = $payment_db->total_amount;
		}

		$log = sprintf(
			'Stripe payment refunded from the WPForms plugin interface. Refunded amount: %1$s.',
			wpforms_format_amount( wpforms_sanitize_amount( $amount_to_log ), true )
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
	 * @since 1.8.4
	 */
	public function ajax_single_payment_cancel() {

		if ( ! isset( $_POST['payment_id'] ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Payment ID not provided.', 'wpforms-lite' ) ] );
		}

		if ( ! wpforms_current_user_can( wpforms_get_capability_manage_options() ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'You are not allowed to perform this action.', 'wpforms-lite' ) ] );
		}

		$this->check_payment_collection_type();
		check_ajax_referer( 'wpforms-admin', 'nonce' );

		$payment_id = (int) $_POST['payment_id'];
		$payment_db = wpforms()->obj( 'payment' )->get( $payment_id );

		if ( empty( $payment_db ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Subscription not found in the database.', 'wpforms-lite' ) ] );
		}

		$cancel = $this->payment_intents->cancel_subscription( $payment_db->subscription_id );

		if ( ! $cancel ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Subscription cancellation failed.', 'wpforms-lite' ) ] );
		}

		if ( UpdateHelpers::cancel_subscription( $payment_db->id, 'Stripe subscription cancelled from the WPForms plugin interface.' ) ) {
			wp_send_json_success( [ 'message' => esc_html__( 'Subscription cancelled.', 'wpforms-lite' ) ] );
		}

		wp_send_json_error( [ 'message' => esc_html__( 'Updating subscription in the database failed.', 'wpforms-lite' ) ] );
	}

	/**
	 * Check the current payment collection type.
	 * If the deprecated type is still used, then warn users about it.
	 *
	 * When it's dropped from the addon, this method can be safely removed.
	 *
	 * @since 1.8.4
	 */
	private function check_payment_collection_type() {

		if ( ! Helpers::is_pro() || absint( wpforms_setting( 'stripe-api-version' ) ) !== 2 ) {
			return;
		}

		$message = sprintf(
			wp_kses( /* translators: %s - Payments settings page URL. */
				__( "The used Stripe payment collection type doesn't support this action.<br><br> Please <a href='%s'>update your payment collection type</a> to continue processing payments successfully.", 'wpforms-lite' ),
				[
					'br' => [],
					'a'  => [
						'href' => [],
					],
				]
			),
			esc_url( admin_url( 'admin.php?page=wpforms-settings&view=payments#wpforms-setting-row-stripe-api-version' ) )
		);

		wp_send_json_error( [ 'modal_msg' => $message ] );
	}
}
