<?php

// phpcs:ignore Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpIllegalPsrClassPathInspection */

namespace WPForms\Logger;

use WPForms\Helpers\DB;

/**
 * Class Repository.
 *
 * @since 1.6.3
 */
class Repository {

	/**
	 * Cache key name for total logs.
	 *
	 * @since 1.6.3
	 */
	const CACHE_TOTAL_KEY = 'wpforms_logs_total';

	/**
	 * Records query.
	 *
	 * @since 1.6.3
	 *
	 * @var RecordQuery
	 */
	private $records_query;

	/**
	 * Records.
	 *
	 * @since 1.6.3
	 *
	 * @var Records
	 */
	private $records;

	/**
	 * Get a not-limited total query.
	 *
	 * @since 1.6.4.1
	 *
	 * @var int
	 */
	private $full_total;

	/**
	 * Log constructor.
	 *
	 * @since 1.6.3
	 * @since 1.9.0 Removed the argument.
	 */
	public function __construct() {

		$this->full_total    = false;
		$this->records_query = new RecordQuery();
		$this->records       = new Records();
	}

	/**
	 * Get log table name.
	 *
	 * @since 1.6.3
	 *
	 * @return string
	 */
	public static function get_table_name(): string {

		global $wpdb;

		return $wpdb->prefix . 'wpforms_logs';
	}

	/**
	 * Create table in the database.
	 *
	 * @since 1.6.3
	 */
	public function create_table() {

		global $wpdb;

		$table = self::get_table_name();

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table (
			id BIGINT(20) NOT NULL AUTO_INCREMENT,
			title VARCHAR(255) NOT NULL,
			message LONGTEXT NOT NULL,
			types VARCHAR(255) NOT NULL,
			create_at DATETIME NOT NULL,
			form_id BIGINT(20),
			entry_id BIGINT(20),
			user_id BIGINT(20),
			PRIMARY KEY (id)
		) $charset_collate;";

		dbDelta( $sql );
	}

	/**
	 * Create new record.
	 *
	 * @since 1.6.3
	 *
	 * @param string       $title    Record title.
	 * @param string       $message  Record message.
	 * @param array|string $types    Array, string, or string separated by comma types.
	 * @param int          $form_id  Record form ID.
	 * @param int          $entry_id Record entry ID.
	 * @param int          $user_id  Record user ID.
	 */
	public function add( $title, $message, $types, $form_id, $entry_id, $user_id ) {

		$this->records->push(
			Record::create( $title, $message, $types, $form_id, $entry_id, $user_id )
		);
	}

	/**
	 * Get records.
	 *
	 * @since 1.6.3
	 *
	 * @param int    $limit  Query limit of records.
	 * @param int    $offset Offset of records.
	 * @param string $search Search.
	 * @param string $type   Type of records.
	 *
	 * @return Records
	 */
	public function records( $limit, $offset = 0, $search = '', $type = '' ) {

		$data             = $this->records_query->get( $limit, $offset, $search, $type );
		$this->full_total = true;
		$records          = new Records();

		// As we got raw data, we need to convert to Record.
		foreach ( $data as $row ) {
			$records->push(
				$this->prepare_record( $row )
			);
		}

		return $records;
	}

	/**
	 * Get record.
	 *
	 * @since 1.6.3
	 *
	 * @param int $id Record ID.
	 *
	 * @return Record|null
	 */
	public function record( $id ) {

		global $wpdb;
		//phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$item = $wpdb->get_row(
			$wpdb->prepare(
				'SELECT * FROM ' . self::get_table_name() . ' WHERE id = %d', //phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				absint( $id )
			)
		);

		if ( $item ) {
			$item = $this->prepare_record( $item );
		}

		return $item;
	}

	/**
	 * Create record from DB row.
	 *
	 * @since 1.6.3
	 *
	 * @param object $row Row from DB.
	 *
	 * @return Record
	 */
	private function prepare_record( $row ) {

		return new Record(
			absint( $row->id ),
			$row->title,
			$row->message,
			$row->types,
			$row->create_at,
			absint( $row->form_id ),
			absint( $row->entry_id ),
			absint( $row->user_id )
		);
	}

	/**
	 * Save records to the database.
	 *
	 * @since 1.6.3
	 */
	public function save() {

		global $wpdb;

		// We can't use the empty function because it doesn't work with a Countable object.
		if ( ! count( $this->records ) ) {
			return;
		}

		$sql = 'INSERT INTO ' . self::get_table_name() . ' ( `id`, `title`, `message`, `types`, `create_at`, `form_id`, `entry_id`, `user_id` ) VALUES ';

		foreach ( $this->records as $record ) {
			$sql .= $wpdb->prepare(
				'( NULL, %s, %s, %s, %s, %d, %d, %d ),',
				$record->get_title(),
				$record->get_message(),
				implode( ',', $record->get_types() ),
				$record->get_date( 'sql' ),
				$record->get_form_id(),
				$record->get_entry_id(),
				$record->get_user_id()
			);
		}

		$sql = rtrim( $sql, ',' );

		//phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		$wpdb->query( $sql );
		//phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		wp_cache_delete( self::CACHE_TOTAL_KEY );
	}

	/**
	 * Check if the database table exists.
	 *
	 * @since 1.6.4
	 *
	 * @return bool
	 */
	public function table_exists() {

		return DB::table_exists( self::get_table_name() );
	}

	/**
	 * Get total count of logs.
	 *
	 * @since 1.6.3
	 *
	 * @return int
	 */
	public function get_total() {

		global $wpdb;

		$total = wp_cache_get( self::CACHE_TOTAL_KEY );

		if ( ! $total ) {
			//phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared
			$total = $this->full_total ? $wpdb->get_var( 'SELECT FOUND_ROWS()' ) : $wpdb->get_var( 'SELECT COUNT( ID ) FROM ' . self::get_table_name() );
			//phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared
			wp_cache_set( self::CACHE_TOTAL_KEY, $total, 'wpforms', DAY_IN_SECONDS );
		}

		return absint( $total );
	}

	/**
	 * Clear all records in the Database.
	 *
	 * @since 1.6.3
	 */
	public function clear_all() {

		global $wpdb;

		//phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		$wpdb->query( 'TRUNCATE TABLE ' . self::get_table_name() );
	}
}
