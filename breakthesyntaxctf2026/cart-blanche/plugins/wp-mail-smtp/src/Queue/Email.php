<?php

namespace WPMailSMTP\Queue;

use WPMailSMTP\WP;
use DateTime;
use DateTimeZone;
use Exception;

/**
 * Class Email.
 *
 * @since 4.0.0
 */
class Email {

	/**
	 * This email is enqueued.
	 *
	 * @since 4.0.0
	 */
	const STATUS_QUEUED = 0;

	/**
	 * This email is being processed.
	 *
	 * @since 4.0.0
	 */
	const STATUS_PROCESSING = 1;

	/**
	 * This email has been processed.
	 *
	 * @since 4.0.0
	 */
	const STATUS_PROCESSED = 2;

	/**
	 * ID of the email.
	 *
	 * @since 4.0.0
	 *
	 * @var int
	 */
	private $id = 0;

	/**
	 * Serialized WPMailInitiator state of this email.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	private $initiator_state = [];

	/**
	 * Serialized arguments of this email's original wp_mail call.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	private $wp_mail_args = [];

	/**
	 * Serialized connection data of this email.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	private $connection_data = [];

	/**
	 * Serialized MailCatcher state of this email.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	private $mailer_state = [];

	/**
	 * Status of this email.
	 *
	 * @since 4.0.0
	 *
	 * @var int
	 */
	private $status = 0;

	/**
	 * Date and time this email was enqueued at.
	 *
	 * @since 4.0.0
	 *
	 * @var DateTime
	 */
	private $date_enqueued;

	/**
	 * Date and time this email was processed at.
	 *
	 * @since 4.0.0
	 *
	 * @var DateTime
	 */
	private $date_processed;

	/**
	 * Email constructor.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {

		$this->date_enqueued = new DateTime( 'now', new DateTimeZone( 'UTC' ) );
	}

	/**
	 * Get a list of allowed statuses.
	 *
	 * @since 4.0.0
	 *
	 * @return array
	 */
	public static function get_statuses() {

		return [
			self::STATUS_QUEUED,
			self::STATUS_PROCESSING,
			self::STATUS_PROCESSED,
		];
	}

	/**
	 * Construct an email from an array of data.
	 *
	 * @since 4.0.0
	 *
	 * @param object $data Database row object.
	 *
	 * @throws Exception If supplied data is missing or malformed.
	 *
	 * @return Email
	 */
	public static function from_data( $data ) { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		if ( is_null( $data ) ) {
			throw new Exception( esc_html__( 'Record not found in DB', 'wp-mail-smtp' ) );
		}

		if (
			! is_object( $data ) ||
			! property_exists( $data, 'data' ) ||
			! isset(
				$data->id,
				$data->status,
				$data->date_enqueued
			)
		) {
			throw new Exception( esc_html__( 'Invalid record format', 'wp-mail-smtp' ) );
		}

		// Data can be null if email has been anonymized.
		// Only check for valid JSON if data isn't null.
		if ( ! is_null( $data->data ) && ! WP::is_json( $data->data ) ) {
			throw new Exception(
				sprintf(
					/* translators: %1$s - JSON error message. */
					esc_html__( 'Data JSON decoding error: %1$s', 'wp-mail-smtp' ),
					esc_html( json_last_error_msg() )
				)
			);
		}

		$email      = new Email();
		$email_data = is_null( $data->data ) ? [] : json_decode( $data->data, true );
		$email_data = wp_parse_args(
			$email_data,
			[
				'initiator_state' => [],
				'wp_mail_args'    => [],
				'connection_data' => [],
				'mailer_state'    => [],
			]
		);

		$email->id              = (int) $data->id;
		$email->initiator_state = $email_data['initiator_state'];
		$email->wp_mail_args    = $email_data['wp_mail_args'];
		$email->connection_data = $email_data['connection_data'];
		$email->mailer_state    = $email_data['mailer_state'];
		$email->status          = (int) $data->status;
		$email->date_enqueued   = $email->get_datetime( $data->date_enqueued );

		if ( isset( $data->date_processed ) ) {
			$email->date_processed = $email->get_datetime( $data->date_processed );
		}

		return $email;
	}

	/**
	 * Get this email's ID.
	 *
	 * @since 4.0.0
	 *
	 * @return int
	 */
	public function get_id() {

		return (int) $this->id;
	}

	/**
	 * Get this email's status.
	 *
	 * @since 4.0.0
	 *
	 * @return int
	 */
	public function get_status() {

		return $this->status;
	}

	/**
	 * Set this email's status.
	 *
	 * @since 4.0.0
	 *
	 * @param int $status Email status.
	 *
	 * @return Email
	 */
	public function set_status( $status ) {

		$status = (int) $status;

		if ( ! in_array( $status, self::get_statuses(), true ) ) {
			$status = self::STATUS_QUEUED;
		}

		$this->status = $status;

		return $this;
	}

	/**
	 * Get this email's `wp_mail` call arguments.
	 *
	 * @since 4.0.0
	 *
	 * @return array
	 */
	public function get_wp_mail_args() {

		return $this->wp_mail_args;
	}

	/**
	 * Set this email's `wp_mail` call arguments.
	 *
	 * @since 4.0.0
	 *
	 * @param array $args Array of arguments.
	 *
	 * @return Email
	 */
	public function set_wp_mail_args( $args ) {

		$args = wp_parse_args(
			$args,
			[
				'headers'     => '',
				'attachments' => [],
			]
		);

		$this->wp_mail_args = $args;

		return $this;
	}

	/**
	 * Get this email's MailCatcher state.
	 *
	 * @since 4.0.0
	 *
	 * @return array
	 */
	public function get_connection_data() {

		return $this->connection_data;
	}

	/**
	 * Set this email's connection data.
	 *
	 * @since 4.0.0
	 *
	 * @param array $data Connection data.
	 *
	 * @return Email
	 */
	public function set_connection_data( $data ) {

		$this->connection_data = wp_parse_args(
			$data,
			[
				'from_email' => '',
				'from_name'  => '',
			]
		);

		return $this;
	}

	/**
	 * Get this email's MailCatcher state.
	 *
	 * @since 4.0.0
	 *
	 * @return array
	 */
	public function get_mailer_state() {

		return $this->mailer_state;
	}

	/**
	 * Set this email's MailCatcher state.
	 *
	 * @since 4.0.0
	 *
	 * @param array $state MailCatcher state.
	 *
	 * @return Email
	 */
	public function set_mailer_state( $state ) {

		$this->mailer_state = wp_parse_args(
			$state,
			[
				'CharSet'      => '',
				'ContentType'  => '',
				'Encoding'     => '',
				'CustomHeader' => '',
				'Subject'      => '',
				'Body'         => '',
				'AltBody'      => '',
				'ReplyTo'      => '',
				'to'           => '',
				'cc'           => '',
				'bcc'          => '',
				'attachment'   => '',
			]
		);

		return $this;
	}

	/**
	 * Get this email's WPMailInitiator state.
	 *
	 * @since 4.0.0
	 *
	 * @return array
	 */
	public function get_initiator_state() {

		return $this->initiator_state;
	}

	/**
	 * Set this email's WPMailInitiator state.
	 *
	 * @since 4.0.0
	 *
	 * @param array $state MailCatcher state.
	 *
	 * @return Email
	 */
	public function set_initiator_state( $state ) {

		$this->initiator_state = wp_parse_args(
			$state,
			[
				'file'      => '',
				'line'      => '',
				'backtrace' => '',
			]
		);

		return $this;
	}

	/**
	 * Get the date and time this email
	 * was enqueued at.
	 *
	 * @since 4.0.0
	 *
	 * @return DateTime
	 */
	public function get_date_enqueued() {

		return $this->date_enqueued;
	}

	/**
	 * Set the date and time this email
	 * was enqueued at.
	 *
	 * @since 4.0.0
	 *
	 * @param DateTime $datetime Date and time of enqueueing.
	 *
	 * @return Email
	 */
	public function set_date_enqueued( $datetime ) {

		$this->date_enqueued = $this->get_datetime( $datetime );

		return $this;
	}

	/**
	 * Get the date and time this email
	 * was processed at.
	 *
	 * @since 4.0.0
	 *
	 * @return DateTime
	 */
	public function get_date_processed() {

		return $this->date_processed;
	}

	/**
	 * Set the date and time this email
	 * was processed at.
	 *
	 * @since 4.0.0
	 *
	 * @param DateTime $datetime Date and time of processing.
	 *
	 * @return Email
	 */
	public function set_date_processed( $datetime ) {

		$this->date_processed = $this->get_datetime( $datetime );

		return $this;
	}

	/**
	 * Convert a database string to a DateTime
	 * object, if necessary.
	 *
	 * @since 4.0.0
	 *
	 * @param string $datetime Date and time.
	 *
	 * @return DateTime
	 */
	private function get_datetime( $datetime ) {

		if ( ! is_a( $datetime, DateTime::class ) ) {
			// Validate the date. Time is ignored.
			$mm = substr( $datetime, 5, 2 );
			$jj = substr( $datetime, 8, 2 );
			$aa = substr( $datetime, 0, 4 );

			$valid_date = wp_checkdate( $mm, $jj, $aa, $datetime );
			$timezone   = new DateTimeZone( 'UTC' );

			if ( $valid_date ) {
				$datetime = DateTime::createFromFormat( WP::datetime_mysql_format(), $datetime, $timezone );
			} else {
				$datetime = new DateTime( 'now', $timezone );
			}
		}

		return $datetime;
	}

	/**
	 * Erase any potentially sensitive data.
	 *
	 * @since 4.0.0
	 *
	 * @return @return Email
	 */
	public function anonymize() {

		$this->initiator_state = null;
		$this->wp_mail_args    = null;
		$this->connection_data = null;
		$this->mailer_state    = null;

		return $this;
	}

	/**
	 * Save a new or modified email in DB.
	 *
	 * @since 4.0.0
	 *
	 * @throws Exception If data can't be encoded,
	 *                   or a database error occurred.
	 *
	 * @return int New or updated email ID.
	 */
	public function save() {

		global $wpdb;

		$table = Queue::get_table_name();
		$data  = [
			'initiator_state' => $this->initiator_state,
			'wp_mail_args'    => $this->wp_mail_args,
			'connection_data' => $this->connection_data,
			'mailer_state'    => $this->mailer_state,
		];

		$data = array_filter( $data );

		if ( ! empty( $data ) ) {
			$data = wp_json_encode(
				[
					'initiator_state' => $this->initiator_state,
					'wp_mail_args'    => $this->wp_mail_args,
					'connection_data' => $this->connection_data,
					'mailer_state'    => $this->mailer_state,
				]
			);

			if ( $data === false ) {
				throw new Exception(
					sprintf(
						/* translators: %1$s - JSON error message. */
						esc_html__( 'Data JSON encoding error: %1$s', 'wp-mail-smtp' ),
						esc_html( json_last_error_msg() )
					)
				);
			}
		} else {
			$data = null;
		}

		if ( (bool) $this->get_id() ) {
			// Update the existing DB table record.
			$result = $wpdb->update( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching
				$table,
				[
					'data'           => $data,
					'status'         => $this->status,
					'date_processed' => $this->get_date_processed()->format( WP::datetime_mysql_format() ),
				],
				[
					'id' => $this->get_id(),
				],
				[
					'%s', // data.
					'%s', // status.
					'%s', // date_processed.
				],
				[
					'%d',
				]
			);

			$email_id = $this->get_id();
		} else {
			// Create a new DB table record.
			$result = $wpdb->insert(
				$table,
				[
					'data'          => $data,
					'status'        => $this->status,
					'date_enqueued' => $this->get_date_enqueued()->format( WP::datetime_mysql_format() ),
				],
				[
					'%s', // data.
					'%s', // status.
					'%s', // date_enqueued.
				]
			);

			$email_id = $wpdb->insert_id;
		}

		if ( $result === false ) {
			throw new Exception(
				sprintf(
					/* translators: %1$s - Database error message. */
					esc_html__( 'Insert/update SQL query error: %1$s', 'wp-mail-smtp' ),
					esc_html( $wpdb->last_error )
				)
			);
		}

		return (int) $email_id;
	}
}
