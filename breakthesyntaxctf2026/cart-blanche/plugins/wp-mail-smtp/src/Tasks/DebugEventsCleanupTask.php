<?php

namespace WPMailSMTP\Tasks;

use DateTime;
use Exception;
use WPMailSMTP\Admin\DebugEvents\DebugEvents;
use WPMailSMTP\Options;
use WPMailSMTP\WP;

/**
 * Class DebugEventsCleanupTask.
 *
 * @since 3.6.0
 */
class DebugEventsCleanupTask extends Task {

	/**
	 * Action name for this task.
	 *
	 * @since 3.6.0
	 */
	const ACTION = 'wp_mail_smtp_process_debug_events_cleanup';

	/**
	 * Class constructor.
	 *
	 * @since 3.6.0
	 */
	public function __construct() {

		parent::__construct( self::ACTION );
	}

	/**
	 * Initialize the task with all the proper checks.
	 *
	 * @since 3.6.0
	 */
	public function init() { // phpcs:ignore WPForms.PHP.HooksMethod.InvalidPlaceForAddingHooks

		// Register the action handler.
		add_action( self::ACTION, [ $this, 'process' ] );

		// Get the retention period value from the Debug Events settings.
		$retention_period = Options::init()->get( 'debug_events', 'retention_period' );

		// Exit if the retention period is not defined (set to "forever") or this task is already scheduled.
		if ( empty( $retention_period ) || Tasks::is_scheduled( self::ACTION ) !== false ) {
			return;
		}

		// Schedule the task.
		$this->recurring(
			strtotime( 'tomorrow' ),
			$this->get_debug_events_cleanup_interval()
		)
			->params( $retention_period )
			->register();
	}

	/**
	 * Get the cleanup interval for the debug events.
	 *
	 * @since 3.6.0
	 *
	 * @return int
	 */
	private function get_debug_events_cleanup_interval() {

		$day_in_seconds = DAY_IN_SECONDS;

		/**
		 * Filter for the debug events cleanup interval.
		 *
		 * @since 3.6.0
		 *
		 * @param int $day_in_seconds Debug events cleanup interval.
		 */
		return (int) apply_filters( 'wpmailsmtp_tasks_get_debug_events_cleanup_interval', $day_in_seconds );
	}

	/**
	 * Perform the cleanup action: remove outdated debug events.
	 *
	 * @since 3.6.0
	 *
	 * @param int $meta_id The Meta ID with the stored task parameters.
	 *
	 * @throws Exception Exception will be logged in the Action Scheduler logs table.
	 */
	public function process( $meta_id ) {

		$task_meta = new Meta();
		$meta      = $task_meta->get( (int) $meta_id );

		// We should actually receive the passed parameter.
		if ( empty( $meta ) || empty( $meta->data ) || count( $meta->data ) !== 1 ) {
			return;
		}

		/**
		 * Date in seconds (examples: 86400, 100500).
		 * Debug Events older than this period will be deleted.
		 *
		 * @var int $retention_period
		 */
		$retention_period = (int) $meta->data[0];

		if ( empty( $retention_period ) ) {
			return;
		}

		// Bail if DB tables was not created.
		if ( ! DebugEvents::is_valid_db() ) {
			return;
		}

		$wpdb  = WP::wpdb();
		$table = DebugEvents::get_table_name();
		$date  = ( new DateTime( "- $retention_period seconds" ) )->format( WP::datetime_mysql_format() );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->prepare( "DELETE FROM `$table` WHERE created_at < %s", $date )
		);
	}
}
