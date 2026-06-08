<?php

namespace WPMailSMTP\Tasks\Queue;

use DateTime;
use DateTimeZone;
use WPMailSMTP\Queue\Attachments;
use WPMailSMTP\Tasks\Task;
use WPMailSMTP\Tasks\Tasks;

/**
 * Class CleanupQueueTask.
 *
 * @since 4.0.0
 */
class CleanupQueueTask extends Task {

	/**
	 * Action name for this task.
	 *
	 * @since 4.0.0
	 */
	const ACTION = 'wp_mail_smtp_queue_cleanup';

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

		// Exit if this task the queue is disabled, or it's already scheduled.
		if (
			! wp_mail_smtp()->get_queue()->is_enabled() ||
			Tasks::is_scheduled( self::ACTION ) !== false
		) {
			return;
		}

		// Schedule the task.
		$this->recurring( strtotime( 'now' ), DAY_IN_SECONDS )
			 ->unique()
			 ->register();
	}

	/**
	 * Perform email sending.
	 *
	 * @since 4.0.0
	 */
	public function process() {

		$queue       = wp_mail_smtp()->get_queue();
		$attachments = new Attachments();

		// Cleanup processed emails.
		$queue->cleanup();

		// Cleanup older-than-a-month attachments.
		$attachments->delete_attachments( null, new DateTime( '1 month ago', new DateTimeZone( 'UTC' ) ) );

		if ( ! $queue->is_enabled() ) {
			// If the query has been disabled in the meanwhile,
			// and there aren't any emails left,
			// cancel the cleanup task.
			$queued_emails_count    = $queue->count_queued_emails();
			$processed_emails_count = $queue->count_processed_emails();

			if ( $queued_emails_count === 0 && $processed_emails_count === 0 ) {
				// Cleanup any remaining, older-than-an-hour attachments.
				$attachments->delete_attachments( null, new DateTime( '1 hour ago', new DateTimeZone( 'UTC' ) ) );

				$this->cancel_force();
			}
		}
	}
}
