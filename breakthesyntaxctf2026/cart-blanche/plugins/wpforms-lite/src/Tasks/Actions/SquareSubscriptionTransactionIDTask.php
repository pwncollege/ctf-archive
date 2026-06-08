<?php

namespace WPForms\Tasks\Actions;

use WPForms\Integrations\Square\Api\Api;
use WPForms\Integrations\Square\Connection;
use WPForms\Tasks\Task;
use WPForms\Tasks\Meta;

/**
 * Class SquareSubscriptionTransactionIDTask.
 *
 * @since 1.9.5
 */
class SquareSubscriptionTransactionIDTask extends Task {

	/**
	 * Action name.
	 *
	 * @since 1.9.5
	 */
	private const ACTION = 'wpforms_process_square_subscription_transaction_id';

	/**
	 * Constructor.
	 *
	 * @since 1.9.5
	 */
	public function __construct() {

		parent::__construct( self::ACTION );

		$this->init();
	}

	/**
	 * Initialize.
	 *
	 * @since 1.9.5
	 */
	private function init() {

		$this->hooks();
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.9.5
	 */
	private function hooks() {

		add_action( 'wpforms_process_payment_saved', [ $this, 'add_task' ], 999, 3 );
		add_action( self::ACTION, [ $this, 'process' ] );
	}

	/**
	 * Add task to the queue.
	 *
	 * @since 1.9.5
	 *
	 * @param string $payment_id Payment ID.
	 * @param array  $fields     Final/sanitized submitted field data.
	 * @param array  $form_data  Form data and settings.
	 */
	public function add_task( $payment_id, array $fields, array $form_data ) {

		$payment_obj = wpforms()->obj( 'payment' );

		if ( ! $payment_obj ) {
			return;
		}

		$payment = $payment_obj->get( (int) $payment_id );

		if ( ! $payment ) {
			return;
		}

		// Bail early if not Square subscription.
		if ( $payment->gateway !== 'square' || $payment->type !== 'subscription' ) {
			return;
		}

		// Bail early if transaction_id is already set via webhooks.
		if ( ! empty( $payment->transaction_id ) ) {
			return;
		}

		// Add task to the queue.
		wpforms()->obj( 'tasks' )
			->create( self::ACTION )
			->once( time() + MINUTE_IN_SECONDS )
			->params( (int) $payment_id )
			->register();
	}

	/**
	 * Process the task.
	 *
	 * @since 1.9.5
	 *
	 * @param int $meta_id Meta ID.
	 */
	public function process( $meta_id ) {

		$task_meta = new Meta();
		$meta      = $task_meta->get( (int) $meta_id );

		if ( empty( $meta ) || empty( $meta->data ) ) {
			return;
		}

		[ $payment_id ] = $meta->data;

		$payment = wpforms()->obj( 'payment' )->get( (int) $payment_id );

		// Bail early if transaction_id is already set via webhooks.
		if ( ! empty( $payment->transaction_id ) ) {
			return;
		}

		if ( ! Connection::get() ) {
			return;
		}

		$api = new Api( Connection::get() );

		$subscription = $api->retrieve_subscription( $payment->subscription_id );

		if ( $subscription === null ) {
			return;
		}

		$invoice = $api->get_latest_subscription_invoice( $subscription );

		if ( $invoice === null ) {
			return;
		}

		$transaction_id = $api->get_latest_invoice_transaction_id( $invoice );

		// Set transaction_id for the subscription in case it not received earlier.
		wpforms()->obj( 'payment' )->update(
			$payment_id,
			[ 'transaction_id' => $transaction_id ],
			'',
			'',
			[ 'cap' => false ]
		);

		// Log.
		wpforms()->obj( 'payment_meta' )->add_log(
			$payment_id,
			sprintf(
				'Square subscription was created. (Invoice ID: %s)',
				$invoice->getId()
			)
		);
	}
}
