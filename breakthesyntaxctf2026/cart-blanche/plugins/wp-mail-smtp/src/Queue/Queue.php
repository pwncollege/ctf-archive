<?php

namespace WPMailSMTP\Queue;

use DateTime;
use DateTimeZone;
use Exception;
use WPMailSMTP\Admin\DebugEvents\DebugEvents;
use WPMailSMTP\Tasks\Queue\SendEnqueuedEmailTask;
use WPMailSMTP\WPMailArgs;
use WPMailSMTP\WP;

/**
 * Class Queue.
 *
 * @since 4.0.0
 */
class Queue {

	/**
	 * The email being currently handled.
	 *
	 * @since 4.0.0
	 *
	 * @var Email
	 */
	private $email;

	/**
	 * A list of registered hooks at the time
	 * of email sending.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	private $registered_wp_mail_hooks = [];

	/**
	 * Whether the queue is currently enabled.
	 *
	 * @since 4.0.0
	 *
	 * @return bool
	 */
	public function is_enabled() {

		/**
		 * Filters whether the queue is currently enabled.
		 *
		 * @since 4.0.0
		 *
		 * @param bool  $enabled Whether the queue is currently enabled.
		 */
		return apply_filters( 'wp_mail_smtp_queue_is_enabled', false );
	}

	/**
	 * Short-circuit and handle an ongoing PHPMailer `send` call.
	 *
	 * @since 4.0.0
	 *
	 * @return bool
	 */
	public function enqueue_email() { // phpcs:ignore WPForms.PHP.HooksMethod.InvalidPlaceForAddingHooks

		if ( ! $this->is_valid_db() ) {
			return false;
		}

		global $phpmailer;

		$wp_mail_args    = wp_mail_smtp()->get_processor()->get_filtered_wp_mail_args();
		$initiator       = wp_mail_smtp()->get_wp_mail_initiator();
		$processor       = wp_mail_smtp()->get_processor();
		$initiator_state = [
			'file'      => $initiator->get_file(),
			'line'      => $initiator->get_line(),
			'backtrace' => $initiator->get_backtrace(),
		];
		$connection_data = [
			'from_email' => $processor->get_filtered_from_email(),
			'from_name'  => $processor->get_filtered_from_name(),
		];

		// Keep a reference to the original attachments,
		// if something goes wrong while enqueueing the email.
		$original_attachments = $phpmailer->getAttachments();

		// Obfuscate attachment paths for the enqueued email.
		$processed_attachments = ( new Attachments() )->process_attachments( $original_attachments );

		// Set obfuscated path attachments.
		$this->set_attachments( $processed_attachments );

		// Add queued date header in the same format as "Date" header.
		$phpmailer->addCustomHeader( 'X-WP-Mail-SMTP-Queued', $phpmailer::rfcDate() );

		$email = ( new Email() )
			->set_wp_mail_args( $wp_mail_args )
			->set_initiator_state( $initiator_state )
			->set_connection_data( $connection_data )
			->set_mailer_state( $phpmailer->get_state() );

		// Add the email to the queue.
		try {
			$this->add_email( $email );
		} catch ( Exception $e ) {
			// Cleanup any obfuscated path attachments.
			$this->cleanup_attachments();

			// Reset original attachments.
			$this->set_attachments( $original_attachments );

			$message = sprintf(
				/* translators: %1$s - exception message. */
				esc_html__( '[Emails Queue] Skipped enqueueing email. %1$s.', 'wp-mail-smtp' ),
				esc_html( $e->getMessage() )
			);

			DebugEvents::add_debug( $message );

			return false;
		}

		return true;
	}

	/**
	 * Send an email. Can only be called
	 * by a running SendEnqueuedEmailTask.
	 *
	 * @since 4.0.0
	 *
	 * @param int|string $email_id Email's ID.
	 */
	public function send_email( $email_id ) { // phpcs:ignore WPForms.PHP.HooksMethod.InvalidPlaceForAddingHooks

		// This method can't be called directly.
		if ( ! doing_action( SendEnqueuedEmailTask::ACTION ) ) {
			$message = sprintf(
				/* translators: %1$d - email ID. */
				esc_html__( '[Emails Queue] Skipped email sending from the queue. Queue::send_email method was called directly. Email ID: %1$d.', 'wp-mail-smtp' ),
				$email_id
			);

			DebugEvents::add_debug( $message );

			return;
		}

		try {
			$email = $this->get_email( $email_id );
		} catch ( Exception $e ) {
			$this->delete_email( $email_id );

			$message = sprintf(
				/* translators: %1$s - exception message; %2$s - email ID. */
				esc_html__( '[Emails Queue] Skipped email sending from the queue. %1$s. Email ID:  %2$s', 'wp-mail-smtp' ),
				esc_html( $e->getMessage() ),
				$email_id
			);

			DebugEvents::add_debug( $message );

			return;
		}

		// Bail early if the email still enqueued, or already processed.
		if ( $email->get_status() !== Email::STATUS_PROCESSING ) {
			$message = sprintf(
				/* translators: %1$d - email ID; %2$s - email status. */
				esc_html__( '[Emails Queue] Skipped email sending from the queue. Wrong email status. Email ID: %1$d, email status: %2$s.', 'wp-mail-smtp' ),
				$email_id,
				$email->get_status()
			);

			DebugEvents::add_debug( $message );

			return;
		}

		// Keep a reference to the email
		// being sent so that it's accessible
		// across hooks.
		$this->email = $email;

		// Un-hook all user-defined hooks.
		$this->clear_wp_mail_hooks();

		// Stop enqueueing emails.
		add_filter( 'wp_mail_smtp_mail_catcher_send_enqueue_email', '__return_false', PHP_INT_MAX );

		// Re-hook Processor functionality, before applying PHPMailer state,
		// so that From and From Name are correctly filtered.
		wp_mail_smtp()->get_processor()->hooks();

		// Apply the email's PHPMailer state.
		add_action( 'phpmailer_init', [ $this, 'apply_mailer_state' ], PHP_INT_MAX );

		// Retrieve original wp_mail arguments.
		$wp_mail_args = new WPMailArgs( $email->get_wp_mail_args() );

		// Inject user-filtered From and From Name.
		$wp_mail_headers   = $wp_mail_args->get_headers();
		$wp_mail_headers[] = $this->get_connection_from_header( $email->get_connection_data() );

		// Inject the original initiator state.
		add_filter( 'wp_mail_smtp_wp_mail_initiator_set_initiator', [ $this, 'apply_initiator_state' ] );

		// Send the email.
		wp_mail(
			$wp_mail_args->get_to_email(),
			$wp_mail_args->get_subject(),
			$wp_mail_args->get_message(),
			$wp_mail_headers,
			$wp_mail_args->get_attachments()
		);

		// Update the email.
		try {
			$this->email->set_status( Email::STATUS_PROCESSED )
					->set_date_processed( new DateTime( 'now', new DateTimeZone( 'UTC' ) ) )
					->anonymize()
					->save();
		} catch ( Exception $e ) {
			$this->delete_email( $email_id );

			$message = sprintf(
				/* translators: %1$s - exception message; %2$d - email ID. */
				esc_html__( '[Emails Queue] Failed to update queue record after sending email from the queue. %1$s. Email ID: %2$d', 'wp-mail-smtp' ),
				esc_html( $e->getMessage() ),
				$email_id
			);

			DebugEvents::add_debug( $message );
		}

		// Cleanup any attachments.
		$this->cleanup_attachments();

		// Stop injecting the original initiator state.
		remove_filter( 'wp_mail_smtp_wp_mail_initiator_set_initiator', [ $this, 'apply_initiator_state' ] );

		// Stop applying PHPMailer state.
		remove_action( 'phpmailer_init', [ $this, 'apply_mailer_state' ], PHP_INT_MAX );

		// Clear the email reference.
		$this->email = null;

		// Re-hook all user-defined hooks.
		$this->restore_wp_mail_hooks();

		// Start enqueueing emails again.
		remove_filter( 'wp_mail_smtp_mail_catcher_send_enqueue_email', '__return_false', PHP_INT_MAX );
	}

	/**
	 * Return the current email's WPMailInitiator state.
	 *
	 * @since 4.0.0
	 *
	 * @return array WPMailInitiator state.
	 */
	public function apply_initiator_state() {

		return $this->email->get_initiator_state();
	}

	/**
	 * Apply state to the current mailer.
	 *
	 * @since 4.0.0
	 *
	 * @param PHPMailer $phpmailer PHPMailer instance.
	 */
	public function apply_mailer_state( &$phpmailer ) {

		$phpmailer->set_state( $this->email->get_mailer_state() );
	}

	/**
	 * Get the table name.
	 *
	 * @since 4.0.0
	 *
	 * @return string Table name, prefixed.
	 */
	public static function get_table_name() {

		global $wpdb;

		return $wpdb->prefix . 'wpmailsmtp_emails_queue';
	}

	/**
	 * Count processing or processed emails since a given date.
	 *
	 * @since 4.0.0
	 *
	 * @param null|DateTime $since_datetime Date to count from, or null for all emails.
	 *
	 * @return int Email count.
	 */
	public function count_processed_emails( ?DateTime $since_datetime = null ) {

		if ( ! $this->is_valid_db() ) {
			return 0;
		}

		global $wpdb;

		$table = self::get_table_name();
		$where = $wpdb->prepare(
			'status IN (%d, %d)',
			Email::STATUS_PROCESSING,
			Email::STATUS_PROCESSED
		);

		if ( ! is_null( $since_datetime ) ) {
			$where .= $wpdb->prepare(
				' AND date_processed >= %s',
				$since_datetime->format( WP::datetime_mysql_format() )
			);
		}

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$count = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM $table
			WHERE $where;"
		);
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		return (int) $count;
	}

	/**
	 * Count queued emails.
	 *
	 * @since 4.0.0
	 *
	 * @return int Email count.
	 */
	public function count_queued_emails() {

		if ( ! $this->is_valid_db() ) {
			return 0;
		}

		global $wpdb;

		$table = self::get_table_name();
		$where = $wpdb->prepare( 'status = %d', Email::STATUS_QUEUED );
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$count = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM $table
			WHERE $where;"
		);
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		return (int) $count;
	}

	/**
	 * Schedule emails for sending.
	 *
	 * @since 4.0.0
	 */
	public function process() { // phpcs:ignore WPForms.PHP.HooksMethod.InvalidPlaceForAddingHooks

		if ( ! $this->is_valid_db() ) {
			return;
		}

		/**
		 * Filters the amount of emails the queue should process.
		 *
		 * @since 4.0.0
		 *
		 * @param int|null $count Amount of emails to process.
		 */
		$count = apply_filters( 'wp_mail_smtp_queue_process_count', null );

		// If the queue has been disabled, just process all emails.
		if ( ! $this->is_enabled() ) {
			$count = null;
		}

		$emails = $this->get_emails( $count );
		$task   = new SendEnqueuedEmailTask();

		foreach ( $emails as $email ) {
			try {
				$email->set_status( Email::STATUS_PROCESSING )
				  ->set_date_processed( new DateTime( 'now', new DateTimeZone( 'UTC' ) ) )
				  ->save();
			} catch ( Exception $e ) {
				$this->delete_email( $email->get_id() );

				$message = sprintf(
					/* translators: %1$s - exception message. */
					esc_html__( '[Emails Queue] Skipped processing enqueued email. %1$s. Email ID: %2$d', 'wp-mail-smtp' ),
					esc_html( $e->getMessage() ),
					$email->get_id()
				);

				DebugEvents::add_debug( $message );

				continue;
			}

			$task->schedule( $email->get_id() );
		}
	}

	/**
	 * Cleanup emails processed before a given date.
	 *
	 * @since 4.0.0
	 */
	public function cleanup() { // phpcs:ignore WPForms.PHP.HooksMethod.InvalidPlaceForAddingHooks

		/**
		 * Filters the date before which emails should
		 * be removed from the queue.
		 *
		 * @since 4.0.0
		 *
		 * @param DateTime|null $datetime Date before which to remove emails.
		 */
		$datetime = apply_filters( 'wp_mail_smtp_queue_cleanup_before_datetime', null );

		// If the queue has been disabled, just cleanup all emails.
		if ( ! $this->is_enabled() ) {
			$datetime = null;
		}

		$this->delete_emails_before( $datetime );
	}

	/**
	 * Whether the DB table exists.
	 *
	 * @since 4.0.0
	 *
	 * @return bool
	 */
	public function is_valid_db() {

		global $wpdb;

		static $is_valid = null;

		// Return cached value only if table already exists.
		if ( $is_valid === true ) {
			return true;
		}

		$table = self::get_table_name();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching
		$is_valid = (bool) $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s;', $table ) );

		return $is_valid;
	}

	/**
	 * Set current email's attachments.
	 *
	 * @since 4.0.0
	 *
	 * @param array $attachments List of attachments.
	 */
	private function set_attachments( $attachments ) {

		global $phpmailer;

		$phpmailer->clearAttachments();

		foreach ( $attachments as $attachment ) {
			[ $path, , $name, $encoding, $type, , $disposition ] = $attachment;

			try {
				$phpmailer->addAttachment( $path, $name, $encoding, $type, $disposition );
			} catch ( Exception $e ) {
				continue;
			}
		}
	}

	/**
	 * Remove email attachments after sending.
	 *
	 * @since 4.0.0
	 */
	private function cleanup_attachments() {

		global $phpmailer;

		$attachments = $phpmailer->getAttachments();

		( new Attachments() )->delete_attachments( $attachments );
	}

	/**
	 * Get the From/From Name header
	 * from an email's connection data.
	 *
	 * @since 4.0.0
	 *
	 * @param array $connection_data Email's connection data.
	 */
	private function get_connection_from_header( $connection_data ) {

		[
			'from_email' => $from_email,
			'from_name'  => $from_name
		] = $connection_data;

		$from = (
			$from_name === '' ?
			$from_email :
			sprintf( '%1s <%2s>', $from_name, $from_email )
		);

		$from_header = sprintf(
			'From:%s',
			$from
		);

		return $from_header;
	}

	/**
	 * Return a list of the `wp_mail` related hooks
	 * that should be de-registered before sending
	 * an enqueued email.
	 *
	 * @since 4.0.0
	 *
	 * @return array List of hooks.
	 */
	private function get_wp_mail_hooks() {

		return [
			'wp_mail',
			'pre_wp_mail',
			'wp_mail_from',
			'wp_mail_from_name',
			'wp_mail_succeeded',
			'wp_mail_failed',
		];
	}

	/**
	 * Clear any user-defined `wp_mail` related hooks
	 * before sending an enqueued email.
	 *
	 * @since 4.0.0
	 */
	private function clear_wp_mail_hooks() {

		global $wp_filter;

		$wp_mail_hooks = array_intersect_key(
			$wp_filter,
			array_flip( $this->get_wp_mail_hooks() )
		);

		foreach ( $wp_mail_hooks as $hook_name => $hook ) {
			foreach ( $hook->callbacks as $priority => $callbacks ) {
				foreach ( $callbacks as $callback ) {
					$this->registered_wp_mail_hooks[] = [
						$hook_name,
						$callback['function'],
						$priority,
						$callback['accepted_args'],
					];
				}
			}

			remove_all_filters( $hook_name );
		}
	}

	/**
	 * Re-register any previous de-registered `wp_mail` related hooks
	 * after sending an enqueued email.
	 *
	 * @since 4.0.0
	 */
	private function restore_wp_mail_hooks() { // phpcs:ignore WPForms.PHP.HooksMethod.InvalidPlaceForAddingHooks

		foreach ( $this->registered_wp_mail_hooks as $hook ) {
			list( $hook_name, $callback, $priority, $accepted_args ) = $hook;

			add_filter( $hook_name, $callback, $priority, $accepted_args );
		}
	}

	/**
	 * Add an email to the queue.
	 *
	 * @since 4.0.0
	 *
	 * @throws Exception When email couldn't be saved.
	 *
	 * @param Email $email The email to enqueue.
	 */
	private function add_email( Email $email ) {

		if ( ! $this->is_valid_db() ) {
			return;
		}

		$email->set_date_enqueued( new DateTime( 'now', new DateTimeZone( 'UTC' ) ) )
			  ->set_status( Email::STATUS_QUEUED )
			  ->save();
	}

	/**
	 * Get an email.
	 *
	 * @since 4.0.0
	 *
	 * @param int|string $email_id The email's ID.
	 *
	 * @return null|Email The email, or null if not found.
	 */
	private function get_email( $email_id ) {

		if ( ! $this->is_valid_db() ) {
			return null;
		}

		global $wpdb;

		$table = self::get_table_name();
		$where = $wpdb->prepare( 'ID = %d', (int) $email_id );
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$data = $wpdb->get_row( "SELECT * FROM $table WHERE $where" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		$email = Email::from_data( $data );

		return $email;
	}

	/**
	 * Get queued emails from the queue.
	 *
	 * @since 4.0.0
	 *
	 * @param null|int $count Amount of emails to return, or null for all emails.
	 *
	 * @return Email[] Array of emails.
	 */
	private function get_emails( $count = null ) {

		if ( ! $this->is_valid_db() ) {
			return [];
		}

		global $wpdb;

		$table = self::get_table_name();
		$where = $wpdb->prepare( 'status = %d', Email::STATUS_QUEUED );
		$limit = '';

		if ( ! is_null( $count ) ) {
			$limit = $wpdb->prepare(
				'LIMIT 0, %d',
				max( 0, intval( $count ) )
			);
		}

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$data = $wpdb->get_results(
			"SELECT *
			FROM $table
			WHERE $where
			ORDER BY date_enqueued ASC
			$limit;"
		);
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		$emails = [];

		foreach ( $data as $row ) {
			try {
				$emails[] = Email::from_data( $row );
			} catch ( Exception $e ) {
				$this->delete_email( $row->id );

				$message = sprintf(
					/* translators: %1$s - exception message. */
					esc_html__( '[Emails Queue] Skipped processing enqueued email. %1$s. Email ID: %2$d', 'wp-mail-smtp' ),
					esc_html( $e->getMessage() ),
					$row->id
				);

				DebugEvents::add_debug( $message );
			}
		}

		return $emails;
	}

	/**
	 * Delete emails processed before a given date.
	 *
	 * @since 4.0.0
	 *
	 * @param DateTime|null $before_datetime Date before which to remove emails, or null for all emails.
	 */
	private function delete_emails_before( $before_datetime ) {

		if ( ! $this->is_valid_db() ) {
			return;
		}

		global $wpdb;

		$table = self::get_table_name();
		$where = $wpdb->prepare( 'status = %d', Email::STATUS_PROCESSED );

		if ( is_a( $before_datetime, DateTime::class ) ) {
			$where .= $wpdb->prepare(
				' AND date_processed < %s',
				$before_datetime->format( WP::datetime_mysql_format() )
			);
		}

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$wpdb->query( "DELETE FROM $table WHERE $where" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	}

	/**
	 * Delete an email.
	 *
	 * @since 4.0.0
	 *
	 * @param int $email_id ID of the email.
	 */
	private function delete_email( $email_id ) {

		if ( ! $this->is_valid_db() ) {
			return;
		}

		global $wpdb;

		$table = self::get_table_name();

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$wpdb->query(
			$wpdb->prepare( "DELETE FROM $table WHERE ID = %d", $email_id )
		);
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	}
}
