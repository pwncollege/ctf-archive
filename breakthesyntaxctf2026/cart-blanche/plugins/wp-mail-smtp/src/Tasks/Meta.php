<?php

namespace WPMailSMTP\Tasks;

/**
 * Class Meta helps to manage the tasks meta information
 * between Action Scheduler and WP Mail SMTP hooks arguments.
 * We can't pass arguments longer than >191 chars in JSON to AS,
 * so we need to store them somewhere (and clean from time to time).
 *
 * @since 2.1.0
 */
class Meta {

	/**
	 * Database table name.
	 *
	 * @since 2.1.0
	 *
	 * @var string
	 */
	public $table_name;

	/**
	 * Database version.
	 *
	 * @since 2.1.0
	 *
	 * @var string
	 */
	public $version;

	/**
	 * Primary key (unique field) for the database table.
	 *
	 * @since 2.1.0
	 *
	 * @var string
	 */
	public $primary_key = 'id';

	/**
	 * Database type identifier.
	 *
	 * @since 2.1.0
	 *
	 * @var string
	 */
	public $type = 'tasks_meta';

	/**
	 * Primary class constructor.
	 *
	 * @since 2.1.0
	 */
	public function __construct() {

		$this->table_name = self::get_table_name();
	}

	/**
	 * Get the DB table name.
	 *
	 * @since 2.1.0
	 *
	 * @return string
	 */
	public static function get_table_name() {

		global $wpdb;

		return $wpdb->prefix . 'wpmailsmtp_tasks_meta';
	}

	/**
	 * Get table columns.
	 *
	 * @since 2.1.0
	 */
	public function get_columns() {

		return array(
			'id'     => '%d',
			'action' => '%s',
			'data'   => '%s',
			'date'   => '%s',
		);
	}

	/**
	 * Default column values.
	 *
	 * @since 2.1.0
	 *
	 * @return array
	 */
	public function get_column_defaults() {

		return array(
			'action' => '',
			'data'   => '',
			'date'   => gmdate( 'Y-m-d H:i:s' ),
		);
	}

	/**
	 * Retrieve a row from the database based on a given row ID.
	 *
	 * @since 2.1.0
	 *
	 * @param int $row_id Row ID.
	 *
	 * @return null|object
	 */
	private function get_from_db( $row_id ) {

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$this->table_name} WHERE {$this->primary_key} = %s LIMIT 1;", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$row_id
			)
		);
	}

	/**
	 * Retrieve a row based on column and row ID.
	 *
	 * @since 2.1.0
	 *
	 * @param string     $column Column name.
	 * @param int|string $row_id Row ID.
	 *
	 * @return object|null|bool Database query result, object or null on failure.
	 */
	public function get_by( $column, $row_id ) {

		global $wpdb;

		if ( empty( $row_id ) || ! array_key_exists( $column, $this->get_columns() ) ) {
			return false;
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM $this->table_name WHERE $column = '%s' LIMIT 1;", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.QuotedSimplePlaceholder
				$row_id
			)
		);
	}

	/**
	 * Retrieve a value based on column name and row ID.
	 *
	 * @since 2.1.0
	 *
	 * @param string     $column Column name.
	 * @param int|string $row_id Row ID.
	 *
	 * @return string|null Database query result (as string), or null on failure.
	 */
	public function get_column( $column, $row_id ) {

		global $wpdb;

		if ( empty( $row_id ) || ! array_key_exists( $column, $this->get_columns() ) ) {
			return false;
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->get_var(
			$wpdb->prepare(
				"SELECT $column FROM $this->table_name WHERE $this->primary_key = '%s' LIMIT 1;", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.QuotedSimplePlaceholder
				$row_id
			)
		);
	}

	/**
	 * Retrieve one column value based on another given column and matching value.
	 *
	 * @since 2.1.0
	 *
	 * @param string $column       Column name.
	 * @param string $column_where Column to match against in the WHERE clause.
	 * @param string $column_value Value to match to the column in the WHERE clause.
	 *
	 * @return string|null Database query result (as string), or null on failure.
	 */
	public function get_column_by( $column, $column_where, $column_value ) {

		global $wpdb;

		if ( empty( $column ) || empty( $column_where ) || empty( $column_value ) || ! array_key_exists( $column, $this->get_columns() ) ) {
			return false;
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->get_var(
			$wpdb->prepare(
				"SELECT $column FROM $this->table_name WHERE $column_where = %s LIMIT 1;", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$column_value
			)
		);
	}

	/**
	 * Insert a new record into the database.
	 *
	 * @since 2.1.0
	 *
	 * @param array  $data Column data.
	 * @param string $type Optional. Data type context.
	 *
	 * @return int ID for the newly inserted record. 0 otherwise.
	 */
	private function add_to_db( $data, $type = '' ) {

		global $wpdb;

		// Set default values.
		$data = wp_parse_args( $data, $this->get_column_defaults() );

		do_action( 'wp_mail_smtp_pre_insert_' . $type, $data );

		// Initialise column format array.
		$column_formats = $this->get_columns();

		// Force fields to lower case.
		$data = array_change_key_case( $data );

		// White list columns.
		$data = array_intersect_key( $data, $column_formats );

		// Reorder $column_formats to match the order of columns given in $data.
		$data_keys      = array_keys( $data );
		$column_formats = array_merge( array_flip( $data_keys ), $column_formats );

		$wpdb->insert( $this->table_name, $data, $column_formats );

		do_action( 'wp_mail_smtp_post_insert_' . $type, $wpdb->insert_id, $data );

		return $wpdb->insert_id;
	}

	/**
	 * Update an existing record in the database.
	 *
	 * @since 2.1.0
	 *
	 * @param int|string $row_id Row ID for the record being updated.
	 * @param array      $data   Optional. Array of columns and associated data to update. Default empty array.
	 * @param string     $where  Optional. Column to match against in the WHERE clause. If empty, $primary_key
	 *                           will be used. Default empty.
	 * @param string     $type   Optional. Data type context, e.g. 'affiliate', 'creative', etc. Default empty.
	 *
	 * @return bool False if the record could not be updated, true otherwise.
	 */
	public function update( $row_id, $data = array(), $where = '', $type = '' ) {

		global $wpdb;

		// Row ID must be a positive integer.
		$row_id = absint( $row_id );

		if ( empty( $row_id ) ) {
			return false;
		}

		if ( empty( $where ) ) {
			$where = $this->primary_key;
		}

		do_action( 'wp_mail_smtp_pre_update_' . $type, $data );

		// Initialise column format array.
		$column_formats = $this->get_columns();

		// Force fields to lower case.
		$data = array_change_key_case( $data );

		// White list columns.
		$data = array_intersect_key( $data, $column_formats );

		// Reorder $column_formats to match the order of columns given in $data.
		$data_keys      = array_keys( $data );
		$column_formats = array_merge( array_flip( $data_keys ), $column_formats );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching
		if ( false === $wpdb->update( $this->table_name, $data, array( $where => $row_id ), $column_formats ) ) {
			return false;
		}

		do_action( 'wp_mail_smtp_post_update_' . $type, $data );

		return true;
	}

	/**
	 * Delete a record from the database.
	 *
	 * @since 2.1.0
	 *
	 * @param int|string $row_id Row ID.
	 *
	 * @return bool False if the record could not be deleted, true otherwise.
	 */
	public function delete( $row_id = 0 ) {

		global $wpdb;

		// Row ID must be positive integer.
		$row_id = absint( $row_id );

		if ( empty( $row_id ) ) {
			return false;
		}

		do_action( 'wp_mail_smtp_pre_delete', $row_id );
		do_action( 'wp_mail_smtp_pre_delete_' . $this->type, $row_id );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		if ( false === $wpdb->query( $wpdb->prepare( "DELETE FROM {$this->table_name} WHERE {$this->primary_key} = %d", $row_id ) ) ) {
			return false;
		}

		do_action( 'wp_mail_smtp_post_delete', $row_id );
		do_action( 'wp_mail_smtp_post_delete_' . $this->type, $row_id );

		return true;
	}

	/**
	 * Delete a record from the database by column.
	 *
	 * @since 2.1.0
	 *
	 * @param string     $column       Column name.
	 * @param int|string $column_value Column value.
	 *
	 * @return bool False if the record could not be deleted, true otherwise.
	 */
	public function delete_by( $column, $column_value ) {

		global $wpdb;

		if ( empty( $column ) || empty( $column_value ) || ! array_key_exists( $column, $this->get_columns() ) ) {
			return false;
		}

		do_action( 'wp_mail_smtp_pre_delete', $column_value );
		do_action( 'wp_mail_smtp_pre_delete_' . $this->type, $column_value );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		if ( false === $wpdb->query( $wpdb->prepare( "DELETE FROM {$this->table_name} WHERE $column = %s", $column_value ) ) ) {
			return false;
		}

		do_action( 'wp_mail_smtp_post_delete', $column_value );
		do_action( 'wp_mail_smtp_post_delete_' . $this->type, $column_value );

		return true;
	}

	/**
	 * Check if the given table exists.
	 *
	 * @since 2.1.0
	 *
	 * @param string $table The table name. Defaults to the child class table name.
	 *
	 * @return string|null If the table name exists.
	 */
	public function table_exists( $table = '' ) {

		global $wpdb;

		if ( ! empty( $table ) ) {
			$table = sanitize_text_field( $table );
		} else {
			$table = $this->table_name;
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching
		$db_result = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );

		if ( is_null( $db_result ) ) {
			return false;
		}

		return strtolower( $db_result ) === strtolower( $table );
	}

	/**
	 * Create custom entry meta database table.
	 * Used in migration.
	 *
	 * @since 2.1.0
	 */
	public function create_table() {

		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset_collate = '';

		if ( ! empty( $wpdb->charset ) ) {
			$charset_collate .= "DEFAULT CHARACTER SET {$wpdb->charset}";
		}
		if ( ! empty( $wpdb->collate ) ) {
			$charset_collate .= " COLLATE {$wpdb->collate}";
		}

		$sql = "CREATE TABLE {$this->table_name} (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			action varchar(255) NOT NULL,
			data longtext NOT NULL,
			date datetime NOT NULL,
			PRIMARY KEY  (id)
		) {$charset_collate};";

		dbDelta( $sql );
	}

	/**
	 * Remove queue records for a defined period of time in the past.
	 * Calling this method will remove queue records that are older than $period seconds.
	 *
	 * @since 2.1.0
	 *
	 * @param string $action   Action that should be cleaned up.
	 * @param int    $interval Number of seconds from now.
	 *
	 * @return int Number of removed tasks meta records.
	 */
	public function clean_by( $action, $interval ) {

		global $wpdb;

		if ( empty( $action ) || empty( $interval ) ) {
			return 0;
		}

		$table  = self::get_table_name();
		$action = sanitize_key( $action );
		$date   = gmdate( 'Y-m-d H:i:s', time() - (int) $interval );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching
		return (int) $wpdb->query(
			$wpdb->prepare(
				"DELETE FROM `$table` WHERE action = %s AND date < %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$action,
				$date
			)
		);
	}

	/**
	 * Inserts a new record into the database.
	 *
	 * @since 2.1.0
	 *
	 * @param array  $data Column data.
	 * @param string $type Optional. Data type context.
	 *
	 * @return int ID for the newly inserted record. 0 otherwise.
	 */
	public function add( $data, $type = '' ) {

		if ( empty( $data['action'] ) || ! is_string( $data['action'] ) ) {
			return 0;
		}

		$data['action'] = sanitize_key( $data['action'] );

		if ( isset( $data['data'] ) ) {
			$string = wp_json_encode( $data['data'] );

			if ( $string === false ) {
				$string = '';
			}

			/*
			 * We are encoding the string representation of all the data
			 * to make sure that nothing can harm the database.
			 * This is not an encryption, and we need this data later as is,
			 * so we are using one of the fastest way to do that.
			 * This data is removed from DB on a daily basis.
			 */
			// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
			$data['data'] = base64_encode( $string );
		}

		if ( empty( $type ) ) {
			$type = $this->type;
		}

		return $this->add_to_db( $data, $type );
	}

	/**
	 * Retrieve a row from the database based on a given row ID.
	 *
	 * @since 2.1.0}
	 *
	 * @param int $meta_id Meta ID.
	 *
	 * @return null|object
	 */
	public function get( $meta_id ) {

		$meta = $this->get_from_db( $meta_id );

		if ( empty( $meta ) || empty( $meta->data ) ) {
			return $meta;
		}

		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
		$decoded = base64_decode( $meta->data );

		if ( $decoded === false || ! is_string( $decoded ) ) {
			$meta->data = '';
		} else {
			$meta->data = json_decode( $decoded, true );
		}

		return $meta;
	}
}
