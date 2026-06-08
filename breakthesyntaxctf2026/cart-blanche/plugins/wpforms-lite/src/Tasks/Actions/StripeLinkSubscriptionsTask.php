<?php

namespace WPForms\Tasks\Actions;

use WPForms\Integrations\Stripe\Api\PaymentIntents;
use WPForms\Tasks\Task;
use WPForms\Integrations\Stripe\Helpers;

/**
 * Class StripeLinkSubscriptionsTask.
 *
 * @since 1.8.7
 */
class StripeLinkSubscriptionsTask extends Task {

	/**
	 * Action name for this task.
	 *
	 * @since 1.8.7
	 */
	const ACTION = 'wpforms_process_stripe_link_subscriptions';

	/**
	 * Status option name.
	 *
	 * @since 1.8.7
	 */
	const STATUS = 'wpforms_process_stripe_link_subscriptions_status';

	/**
	 * Start status.
	 *
	 * @since 1.8.7
	 */
	const START = 'start';

	/**
	 * In progress status.
	 *
	 * @since 1.8.7
	 */
	const IN_PROGRESS = 'in_progress';

	/**
	 * Completed status.
	 *
	 * @since 1.8.7
	 */
	const COMPLETED = 'completed';

	/**
	 * Latest processed payment id.
	 *
	 * @since 1.8.7
	 */
	const LATEST_PROCESSED_OPTION = 'wpforms_stripe_link_subscriptions_latest_processed';

	/**
	 * Stripe PaymentIntents API.
	 *
	 * @since 1.8.7
	 *
	 * @var PaymentIntents
	 */
	private $api;

	/**
	 * Log title.
	 *
	 * @since 1.9.1
	 *
	 * @var string
	 */
	protected $log_title = 'Migration';

	/**
	 * Class constructor.
	 *
	 * @since 1.8.7
	 */
	public function __construct() {

		parent::__construct( self::ACTION );
	}

	/**
	 * Initialize the task.
	 *
	 * @since 1.8.7
	 */
	public function init() {

		// Get a task status.
		$status = get_option( self::STATUS );

		// This task is run in \WPForms\Migrations\Upgrade187::run(),
		// and started in \WPForms\Migrations\UpgradeBase::run_async().
		// Bail out if a task is not started or completed.
		if ( ! $status || $status === self::COMPLETED ) {
			return;
		}

		// Mark that the task is in progress.
		if ( $status === self::START ) {
			update_option( self::STATUS, self::IN_PROGRESS );
		}

		// Register hooks.
		$this->hooks();

		$tasks = wpforms()->obj( 'tasks' );

		// Add new if none exists.
		if ( $tasks->is_scheduled( self::ACTION ) !== false ) {
			return;
		}

		// Add a new task if none exists.
		$tasks->create( self::ACTION )
			->async()
			->register();
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.8.7
	 */
	private function hooks() {

		// Register the migrate action.
		add_action( self::ACTION, [ $this, 'run' ] );
	}

	/**
	 * Run a process task.
	 *
	 * @since 1.8.7
	 */
	public function run() {

		// Bail if no Stripe account is connected.
		if ( ! Helpers::has_stripe_keys() ) {
			$this->complete();

			return;
		}

		$link_subscriptions = $this->get_link_subscriptions();

		// Bail if all subscription were processed.
		if ( empty( $link_subscriptions ) ) {
			$this->complete();

			return;
		}

		$this->api = new PaymentIntents();

		$this->process( $link_subscriptions );
	}

	/**
	 * Process subscriptions.
	 *
	 * @since 1.8.7
	 *
	 * @param array $subscriptions Array of subscriptions.
	 */
	private function process( array $subscriptions ) {

		foreach ( $subscriptions as $subscription ) {

			$this->update_latest_processed( $subscription->id );

			// Use subscription mode to cover all cases (e.g. mode might be switched to test while upgrading).
			$payment = $this->api->retrieve_payment_intent( $subscription->transaction_id, [ 'mode' => $subscription->mode ] );

			// Bail if original payment was unsuccessful.
			if ( is_null( $payment ) || empty( $payment->status ) || $payment->status !== 'succeeded' ) {
				continue;
			}

			$setup_intent_data = $this->prepare_setup_intent_data( $payment, $subscription );

			// Bail if subscription has already had correct mandate.
			if ( ! $setup_intent_data ) {
				continue;
			}

			$intent = $this->api->create_setup_intent( $setup_intent_data, [ 'mode' => $subscription->mode ] );

			// Log failed subscription payment id.
			if ( empty( $intent ) ) {
				$this->log( 'Stripe Link Subscriptions: Failed ' . $subscription->id );
			}
		}
	}

	/**
	 * Update latest processed id.
	 *
	 * @since 1.8.7
	 *
	 * @param int $id Subscription ID.
	 */
	private function update_latest_processed( int $id ) {

		update_option( self::LATEST_PROCESSED_OPTION, $id );
	}

	/**
	 * Get all Stripe subscriptions charged through Link.
	 *
	 * @since 1.8.7
	 *
	 * @return array
	 */
	private function get_link_subscriptions(): array {

		global $wpdb;

		$latest_payment    = (int) get_option( self::LATEST_PROCESSED_OPTION, 0 );
		$payments_table    = wpforms()->obj( 'payment' )->table_name;
		$paymentmeta_table = wpforms()->obj( 'payment_meta' )->table_name;

		$query[] = "SELECT p.* FROM {$payments_table} as p";
		$query[] = "INNER JOIN {$paymentmeta_table} as pm ON p.id = pm.payment_id";
		$query[] = "WHERE p.id > %d AND p.gateway = 'stripe' AND p.type = 'subscription' AND pm.meta_key = 'method_type' AND pm.meta_value = 'link'";

		// Stripe API allows up to 100 read operations per second and 100 write operations per second in live mode,
		// and 25 operations per second for each in test mode.
		$query[] = 'ORDER BY p.id LIMIT 20';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
		return $wpdb->get_results( $wpdb->prepare( implode( ' ', $query ), $latest_payment ), OBJECT_K );
	}

	/**
	 * Prepare Setup Intent data.
	 *
	 * @since 1.8.7
	 *
	 * @param object $payment      Stripe payment object.
	 * @param object $subscription Subscription object.
	 *
	 * @return array
	 */
	private function prepare_setup_intent_data( $payment, $subscription ): array {

		if ( ! empty( $payment->mandate ) ) {
			$mandate = $this->api->retrieve_mandate( $payment->mandate, [ 'mode' => $subscription->mode ] );
		}

		$data = [
			'payment_method_types' => [ 'link' ],
			'customer'             => $payment->customer,
			'payment_method'       => $payment->payment_method,
			'usage'                => 'off_session',
			'confirm'              => true,
		];

		// Prepare default data in case mandate is not available.
		if ( empty( $mandate ) ) {

			$subscription_meta = wpforms()->obj( 'payment_meta' )->get_all( $subscription->id );

			$data['mandate_data'] = [
				'customer_acceptance' => [
					'type'   => 'online',
					'online' => [
						'ip_address' => $subscription_meta['ip_address']->value,
						'user_agent' => $subscription_meta['user_agent']->value,
					],
				],
			];

			return $data;
		}

		// Mandate is correct so no actions needed.
		if ( $mandate->type !== 'single_use' ) {
			return [];
		}

		$data['mandate_data'] = [
			'customer_acceptance' => [
				'type'   => 'online',
				'online' => [
					'ip_address' => $mandate->customer_acceptance->online->ip_address,
					'user_agent' => $mandate->customer_acceptance->online->user_agent,
				],
			],
		];

		return $data;
	}

	/**
	 * Mark that the task is completed.
	 *
	 * @since 1.8.7
	 */
	public function complete() {

		$this->log( 'Stripe Link Subscriptions: Completed' );

		update_option( self::STATUS, self::COMPLETED );
	}
}
