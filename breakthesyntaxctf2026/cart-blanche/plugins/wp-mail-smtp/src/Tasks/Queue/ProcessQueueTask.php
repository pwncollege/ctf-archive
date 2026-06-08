<?php

namespace WPMailSMTP\Tasks\Queue;

use WPMailSMTP\Tasks\Task;
use WPMailSMTP\Tasks\Tasks;

/**
 * Class ProcessQueueTask.
 *
 * @since 4.0.0
 */
class ProcessQueueTask extends Task {

	/**
	 * Action name for this task.
	 *
	 * @since 4.0.0
	 */
	const ACTION = 'wp_mail_smtp_queue_process';

	/**
	 * Class constructor.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {

		parent::__construct( self::ACTION );
	}

	/**
	 * Initialize the task.
	 *
	 * @since 4.0.0
	 */
	public function init() { // phpcs:ignore WPForms.PHP.HooksMethod.InvalidPlaceForAddingHooks

		// Register the action handler.
		add_action( self::ACTION, [ $this, 'process' ] );

		// Cleanup completed task occurrences.
		add_action( 'action_scheduler_after_process_queue', [ $this, 'cleanup' ] );

		// Exit if this task the queue is disabled, or it's already scheduled.
		if (
			! wp_mail_smtp()->get_queue()->is_enabled() ||
			Tasks::is_scheduled( self::ACTION ) !== false
		) {
			return;
		}

		// Schedule the task.
		$this->recurring( strtotime( 'now' ), MINUTE_IN_SECONDS )
			 ->unique()
			 ->register();
	}

	/**
	 * Perform email sending.
	 *
	 * @since 4.0.0
	 */
	public function process() {

		$queue = wp_mail_smtp()->get_queue();

		$queue->process();

		if ( ! $queue->is_enabled() ) {
			$this->cancel_force();
		}
	}

	/**
	 * Cleanup completed tasks.
	 *
	 * @since 4.1.0
	 */
	public function cleanup() {

		$this->remove_completed( 10 );
	}
}
