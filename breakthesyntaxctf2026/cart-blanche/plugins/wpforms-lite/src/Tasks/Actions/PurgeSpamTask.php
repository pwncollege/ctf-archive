<?php

namespace WPForms\Tasks\Actions;

use WPForms\Tasks\Task;

/**
 * Class PurgeSpamTask.
 *
 * @since 1.9.1
 */
class PurgeSpamTask extends Task {

	/**
	 * Action name for this task.
	 *
	 * @since 1.9.1
	 */
	const ACTION = 'wpforms_process_purge_spam';

	/**
	 * Interval in seconds.
	 *
	 * @since 1.9.1
	 *
	 * @var int
	 */
	private $interval;

	/**
	 * Tasks class instance.
	 *
	 * @since 1.9.1
	 *
	 * @var Tasks
	 */
	private $tasks;

	/**
	 * Log title.
	 *
	 * @since 1.9.1
	 *
	 * @var string
	 */
	protected $log_title = 'Purge Spam';

	/**
	 * Class constructor.
	 *
	 * @since 1.9.1
	 */
	public function __construct() {

		parent::__construct( self::ACTION );

		$this->init();
		$this->hooks();
	}

	/**
	 * Init.
	 *
	 * @since 1.9.1
	 */
	public function init() {

		/**
		 * Filter the interval for the purge spam task, in seconds.
		 *
		 * @since 1.9.1
		 *
		 * @param int $interval Interval in seconds.
		 *
		 * @return int
		 */
		$this->interval = (int) apply_filters( 'wpforms_tasks_actions_purge_spam_task_interval', DAY_IN_SECONDS );

		$this->tasks = wpforms()->obj( 'tasks' );

		// Do not add a new one if scheduled.
		if ( $this->tasks->is_scheduled( self::ACTION ) !== false ) {

			if ( $this->interval <= 0 ) {
				$this->cancel();
			}

			return;
		}

		$this->add_scan_task();
	}

	/**
	 * Add hooks.
	 *
	 * @since 1.9.1
	 */
	public function hooks() {

		add_action( self::ACTION, [ $this, 'process' ] );
	}

	/**
	 * Add a new task.
	 *
	 * @since 1.9.1
	 */
	private function add_scan_task() {

		if ( $this->interval <= 0 ) {
			return;
		}

		$this->tasks->create( self::ACTION )
			->recurring( time(), $this->interval )
			->params()
			->register();
	}

	/**
	 * Purge spam action.
	 *
	 * @since 1.9.1
	 */
	public function process() {

		$entry_obj = wpforms()->obj( 'entry' );

		if ( ! $entry_obj ) {
			return;
		}

		$entry_obj->purge_spam();
		$this->log( 'Purge spam completed.' );
	}
}
