<?php

namespace WPMailSMTP\Tasks;

use ActionScheduler;

/**
 * Class Task.
 *
 * @since 2.1.0
 */
class Task {

	/**
	 * This task is async (runs asap).
	 *
	 * @since 2.1.0
	 */
	const TYPE_ASYNC = 'async';

	/**
	 * This task is a recurring.
	 *
	 * @since 2.1.0
	 */
	const TYPE_RECURRING = 'scheduled';

	/**
	 * This task is run once.
	 *
	 * @since 2.1.0
	 */
	const TYPE_ONCE = 'once';

	/**
	 * Type of the task.
	 *
	 * @since 2.1.0
	 *
	 * @var string
	 */
	private $type;

	/**
	 * Action that will be used as a hook.
	 *
	 * @since 2.1.0
	 *
	 * @var string
	 */
	private $action;

	/**
	 * Task meta ID.
	 *
	 * @since 2.1.0
	 *
	 * @var int
	 */
	private $meta_id;

	/**
	 * All the params that should be passed to the hook.
	 *
	 * @since 2.1.0
	 *
	 * @var array
	 */
	private $params;

	/**
	 * When the first instance of the job will run.
	 * Used for ONCE ane RECURRING tasks.
	 *
	 * @since 2.1.0
	 *
	 * @var int
	 */
	private $timestamp;

	/**
	 * How long to wait between runs.
	 * Used for RECURRING tasks.
	 *
	 * @since 2.1.0
	 *
	 * @var int
	 */
	private $interval;

	/**
	 * Whether this task is unique.
	 *
	 * @since 4.0.0
	 *
	 * @var bool
	 */
	private $unique = false;

	/**
	 * Task constructor.
	 *
	 * @since 2.1.0
	 *
	 * @param string $action Action of the task.
	 *
	 * @throws \InvalidArgumentException When action is not a string.
	 * @throws \UnexpectedValueException When action is empty.
	 */
	public function __construct( $action ) {

		if ( ! is_string( $action ) ) {
			throw new \InvalidArgumentException( 'Task action should be a string.' );
		}

		$this->action = sanitize_key( $action );

		if ( empty( $this->action ) ) {
			throw new \UnexpectedValueException( 'Task action cannot be empty.' );
		}
	}

	/**
	 * Define the type of the task as async.
	 *
	 * @since 2.1.0
	 *
	 * @return Task
	 */
	public function async() {

		$this->type = self::TYPE_ASYNC;

		return $this;
	}

	/**
	 * Define the type of the task as recurring.
	 *
	 * @since 2.1.0
	 *
	 * @param int $timestamp When the first instance of the job will run.
	 * @param int $interval  How long to wait between runs.
	 *
	 * @return Task
	 */
	public function recurring( $timestamp, $interval ) {

		$this->type      = self::TYPE_RECURRING;
		$this->timestamp = (int) $timestamp;
		$this->interval  = (int) $interval;

		return $this;
	}

	/**
	 * Define the type of the task as one-time.
	 *
	 * @since 2.1.0
	 *
	 * @param int $timestamp When the first instance of the job will run.
	 *
	 * @return Task
	 */
	public function once( $timestamp ) {

		$this->type      = self::TYPE_ONCE;
		$this->timestamp = (int) $timestamp;

		return $this;
	}

	/**
	 * Set this task as unique.
	 *
	 * @since 4.0.0
	 *
	 * @return Task
	 */
	public function unique() {

		$this->unique = true;

		return $this;
	}

	/**
	 * Pass any number of params that should be saved to Meta table.
	 *
	 * @since 2.1.0
	 *
	 * @return Task
	 */
	public function params() {

		$args = func_get_args();

		if ( ! empty( $args ) ) {
			$this->params = $args;
		}

		return $this;
	}

	/**
	 * Register the action.
	 * Should be the final call in a chain.
	 *
	 * @since 2.1.0
	 *
	 * @return null|string Action ID.
	 */
	public function register() { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		$action_id = null;

		// No processing if ActionScheduler is not usable.
		if ( ! Tasks::is_usable() ) {
			return $action_id;
		}

		// Save data to tasks meta table.
		if ( ! is_null( $this->params ) ) {
			$task_meta = new Meta();

			// No processing if meta table was not created.
			if ( ! $task_meta->table_exists() ) {
				return $action_id;
			}

			$this->meta_id = $task_meta->add(
				[
					'action' => $this->action,
					'data'   => isset( $this->params ) ? $this->params : [],
				]
			);

			if ( empty( $this->meta_id ) ) {
				return $action_id;
			}
		}

		// Prevent 500 errors when Action Scheduler tables don't exist.
		try {
			switch ( $this->type ) {
				case self::TYPE_ASYNC:
					$action_id = $this->register_async();
					break;

				case self::TYPE_RECURRING:
					$action_id = $this->register_recurring();
					break;

				case self::TYPE_ONCE:
					$action_id = $this->register_once();
					break;
			}
		} catch ( \RuntimeException $exception ) {
			$action_id = null;
		}

		return $action_id;
	}

	/**
	 * Register the async task.
	 *
	 * @since 2.1.0
	 *
	 * @return null|string Action ID.
	 */
	protected function register_async() {

		if ( ! function_exists( 'as_enqueue_async_action' ) ) {
			return null;
		}

		return as_enqueue_async_action(
			$this->action,
			[ $this->meta_id ],
			Tasks::GROUP,
			$this->unique
		);
	}

	/**
	 * Register the recurring task.
	 *
	 * @since 2.1.0
	 *
	 * @return null|string Action ID.
	 */
	protected function register_recurring() {

		if ( ! function_exists( 'as_schedule_recurring_action' ) ) {
			return null;
		}

		return as_schedule_recurring_action(
			$this->timestamp,
			$this->interval,
			$this->action,
			[ $this->meta_id ],
			Tasks::GROUP,
			$this->unique
		);
	}

	/**
	 * Register the one-time task.
	 *
	 * @since 2.1.0
	 *
	 * @return null|string Action ID.
	 */
	protected function register_once() {

		if ( ! function_exists( 'as_schedule_single_action' ) ) {
			return null;
		}

		return as_schedule_single_action(
			$this->timestamp,
			$this->action,
			[ $this->meta_id ],
			Tasks::GROUP,
			$this->unique
		);
	}

	/**
	 * Cancel all occurrences of this task.
	 *
	 * @since 2.1.0
	 *
	 * @return null|bool|string Null if no matching action found,
	 *                          false if AS library is missing,
	 *                          string of the scheduled action ID if a scheduled action was found and unscheduled.
	 */
	public function cancel() {

		// Exit if AS function does not exist.
		if ( ! function_exists( 'as_unschedule_all_actions' ) || ! Tasks::is_usable() ) {
			return false;
		}

		as_unschedule_all_actions( $this->action );

		return true;
	}

	/**
	 * Cancel all occurrences of this task,
	 * preventing it from re-registering itself.
	 *
	 * @since 4.0.0
	 */
	public function cancel_force() { // phpcs:ignore WPForms.PHP.HooksMethod.InvalidPlaceForAddingHooks

		add_action( 'shutdown', [ $this, 'cancel' ], PHP_INT_MAX );
	}

	/**
	 * Remove completed occurrences of this task.
	 *
	 * @since 4.1.0
	 *
	 * @param int $limit The amount of rows to remove.
	 */
	protected function remove_completed( $limit = 0 ) {

		global $wpdb;

		$limit = max( 0, intval( $limit ) );
		$query = $wpdb->prepare(
			"DELETE FROM {$wpdb->prefix}actionscheduler_actions WHERE hook = %s AND status = %s",
			$this->action,
			'complete'
		);

		if ( $limit > 0 ) {
			$query .= $wpdb->prepare(
				' LIMIT %d',
				$limit
			);
		}

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		$wpdb->query( $query );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
	}

	/**
	 * Remove pending occurrences of this task.
	 *
	 * @since 4.3.0
	 *
	 * @param int $limit The amount of rows to remove.
	 */
	protected function remove_pending( $limit = 0 ) {

		// Make sure that all used functions, classes, and methods exist.
		if (
			! function_exists( 'as_get_scheduled_actions' ) ||
			! class_exists( 'ActionScheduler' ) ||
			! method_exists( 'ActionScheduler', 'store' ) ||
			! class_exists( 'ActionScheduler_Store' ) ||
			! method_exists( 'ActionScheduler_Store', 'delete_action' )
		) {
			return;
		}

		$per_page = max( 0, intval( $limit ) );

		// Get all pending license check actions.
		$action_ids = as_get_scheduled_actions(
			[
				'hook'     => $this->action,
				'status'   => 'pending',
				'per_page' => $per_page,
			],
			'ids'
		);

		if ( empty( $action_ids ) ) {
			return;
		}

		// Delete all pending license check actions.
		foreach ( $action_ids as $action_id ) {
			ActionScheduler::store()->delete_action( $action_id );
		}
	}
}
