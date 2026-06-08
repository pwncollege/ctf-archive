<?php

namespace WPMailSMTP\Tasks\Reports;

use WPMailSMTP\Tasks\Tasks;
use WPMailSMTP\WP;
use WPMailSMTP\Tasks\Task;
use WPMailSMTP\Reports\Emails\Summary as SummaryReportEmail;

/**
 * Class SummaryEmailTask.
 *
 * @since 3.0.0
 */
class SummaryEmailTask extends Task {

	/**
	 * Action name for this task.
	 *
	 * @since 3.0.0
	 */
	const ACTION = 'wp_mail_smtp_summary_report_email';

	/**
	 * Class constructor.
	 *
	 * @since 3.0.0
	 */
	public function __construct() {

		parent::__construct( self::ACTION );
	}

	/**
	 * Initialize the task with all the proper checks.
	 *
	 * @since 3.0.0
	 */
	public function init() {

		// Register the action handler.
		add_action( self::ACTION, array( $this, 'process' ) );

		$is_disabled = SummaryReportEmail::is_disabled();

		// Exit if summary report email is disabled or this task is already scheduled.
		if ( ! empty( $is_disabled ) || Tasks::is_scheduled( self::ACTION ) !== false ) {
			return;
		}

		$date = new \DateTime( 'next monday 2pm', WP::wp_timezone() );

		// Schedule the task.
		$this
			->recurring( $date->getTimestamp(), WEEK_IN_SECONDS )
			->unique()
			->register();
	}

	/**
	 * Process summary report email send.
	 *
	 * @since 3.0.0
	 *
	 * @param int $meta_id The Meta ID with the stored task parameters.
	 */
	public function process( $meta_id ) {

		// Prevent email sending if summary report email is disabled.
		if ( SummaryReportEmail::is_disabled() || ! $this->is_allowed() ) {
			return;
		}

		// Update the last sent week at the top to prevent multiple emails in case of task failure and retry.
		update_option( 'wp_mail_smtp_summary_report_email_last_sent_week', current_time( 'W' ) );

		$reports = wp_mail_smtp()->get_reports();

		$email = $reports->get_summary_report_email();

		$email->send();
	}

	/**
	 * Check if the summary report email is allowed to be sent.
	 *
	 * The email is allowed to be sent if it was not sent in the current week.
	 *
	 * @since 4.1.1
	 *
	 * @return bool
	 */
	private function is_allowed() {

		$last_sent_week = get_option( 'wp_mail_smtp_summary_report_email_last_sent_week' );
		$current_week   = current_time( 'W' );

		if ( $last_sent_week === false || ( (int) $current_week !== (int) $last_sent_week ) ) {
			return true;
		}

		return false;
	}
}
