<?php

namespace WPMailSMTP\Reports;

use WPMailSMTP\Options;
use WPMailSMTP\Reports\Emails\Summary as SummaryReportEmail;
use WPMailSMTP\Tasks\Reports\SummaryEmailTask;
use WPMailSMTP\WP;

/**
 * Class Reports. Emails stats reports.
 *
 * @since 3.0.0
 */
class Reports {

	/**
	 * The WP option key for storing the total number of sent emails.
	 *
	 * @since 3.0.0
	 *
	 * @const string
	 */
	const SENT_EMAILS_COUNTER_OPTION_KEY = 'wp_mail_smtp_lite_sent_email_counter';

	/**
	 * The WP option key for storing the total number of sent emails by weeks.
	 *
	 * @since 3.0.0
	 *
	 * @const string
	 */
	const WEEKLY_SENT_EMAILS_COUNTER_OPTION_KEY = 'wp_mail_smtp_lite_weekly_sent_email_counter';

	/**
	 * Stats by week retention period. Value in weeks count.
	 * Maximum value is 52 weeks (1 year).
	 *
	 * @since 3.0.0
	 *
	 * @const string
	 */
	const WEEKLY_COUNTER_RETENTION_PERIOD = 12;

	/**
	 * Init class.
	 *
	 * @since 3.0.0
	 */
	public function init() {

		$this->public_hooks();

		if ( WP::in_wp_admin() ) {
			$this->admin_hooks();
		}
	}

	/**
	 * Frontend hooks.
	 *
	 * @since 3.0.0
	 */
	private function public_hooks() {

		// Update sent email counter when SMTP mailer is used.
		add_action( 'wp_mail_smtp_mailcatcher_smtp_send_after', [ $this, 'update_sent_emails_stats' ] );

		// Update sent email counter when all other mailers are used.
		add_action( 'wp_mail_smtp_mailcatcher_send_after', [ $this, 'update_sent_emails_stats' ] );
	}

	/**
	 * Admin hooks.
	 *
	 * @since 3.0.0
	 */
	private function admin_hooks() {

		add_action( 'load-toplevel_page_wp-mail-smtp', [ $this, 'summary_report_email_preview' ] );

		// Detect summary report email constant change.
		if ( Options::init()->is_const_defined( 'general', SummaryReportEmail::SETTINGS_SLUG ) ) {
			add_action( 'admin_init', [ $this, 'detect_summary_report_email_constant_change' ] );
		}
	}

	/**
	 * Update all stats after email sent.
	 *
	 * @since 3.0.0
	 */
	public function update_sent_emails_stats() {

		if ( wp_mail_smtp()->is_pro() ) {
			return;
		}

		$this->increment_sent_emails_counter();
		$this->increment_weekly_sent_emails_counter();
	}

	/**
	 * Increment the number of total emails sent by 1.
	 *
	 * @since 3.0.0
	 */
	private function increment_sent_emails_counter() {

		$value = $this->get_total_emails_sent() + 1;

		update_option( self::SENT_EMAILS_COUNTER_OPTION_KEY, $value, true );
	}

	/**
	 * Get the number of total emails sent.
	 *
	 * @since 3.0.0
	 *
	 * @return int
	 */
	public function get_total_emails_sent() {

		return get_option( self::SENT_EMAILS_COUNTER_OPTION_KEY, 0 );
	}

	/**
	 * Increment the number of total emails sent in this week by 1.
	 *
	 * @since 3.0.0
	 */
	private function increment_weekly_sent_emails_counter() {

		$stats = $this->get_total_weekly_emails_sent();

		$week = $this->get_current_week();

		if ( ! isset( $stats[ $week ] ) ) {
			$stats[ $week ] = 0;
		}

		$stats[ $week ] ++;

		// Cleanup old stats.
		$stats = array_slice( $stats, self::WEEKLY_COUNTER_RETENTION_PERIOD * - 1, null, true );

		update_option( self::WEEKLY_SENT_EMAILS_COUNTER_OPTION_KEY, $stats, true );
	}

	/**
	 * Get the number of total emails sent by week.
	 *
	 * @since 3.0.0
	 *
	 * @param int|string $week Week number or "now", "previous" identifiers.
	 *
	 * @return array|int
	 */
	public function get_total_weekly_emails_sent( $week = null ) {

		$stats = get_option( self::WEEKLY_SENT_EMAILS_COUNTER_OPTION_KEY, [] );

		if ( ! is_null( $week ) ) {
			if ( $week === 'now' ) {
				$week = $this->get_current_week();
			} elseif ( $week === 'previous' ) {
				$week = $this->get_current_week() - 1;
			}

			return isset( $stats[ $week ] ) ? $stats[ $week ] : 0;
		}

		return $stats;
	}

	/**
	 * Generate a summary report email preview and display it for users.
	 *
	 * @since 3.0.0
	 */
	public function summary_report_email_preview() {

		if ( ! current_user_can( wp_mail_smtp()->get_admin()->get_logs_access_capability() ) ) {
			return;
		}

		if ( ! isset( $_GET['mode'] ) || $_GET['mode'] !== 'summary_report_email_preview' ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}

		$email = $this->get_summary_report_email();

		echo $email->get_content(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		exit;
	}

	/**
	 * Get emails stats weekly summary report email.
	 *
	 * @since 3.0.0
	 *
	 * @return SummaryReportEmail
	 */
	public function get_summary_report_email() {

		return new SummaryReportEmail();
	}

	/**
	 * Detect summary report email constant change.
	 *
	 * @since 3.0.0
	 */
	public function detect_summary_report_email_constant_change() {

		if ( ! WP::in_wp_admin() ) {
			return;
		}

		if ( ! current_user_can( wp_mail_smtp()->get_capability_manage_options() ) ) {
			return;
		}

		if ( Options::init()->is_const_changed( 'general', SummaryReportEmail::SETTINGS_SLUG ) ) {
			( new SummaryEmailTask() )->cancel();
		}
	}

	/**
	 * Get current week number.
	 *
	 * @since 3.0.0
	 *
	 * @return int
	 */
	public function get_current_week() {

		return current_time( 'W' );
	}
}
