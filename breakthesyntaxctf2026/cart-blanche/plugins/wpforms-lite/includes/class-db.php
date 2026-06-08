<?php

// phpcs:disable WPForms.Comments.PHPDocHooks.RequiredHookDocumentation, WPForms.PHP.ValidateHooks.InvalidHookName
// phpcs:disable Generic.Commenting.DocComment.MissingShort
/** @noinspection AutoloadingIssuesInspection */
/** @noinspection PhpIllegalPsrClassPathInspection */
// phpcs:disable Generic.Commenting.DocComment.MissingShort

use WPForms\Helpers\DB;

/**
 * DB class.
 *
 * This handy class originated from Pippin's Easy Digital Downloads.
 * See https://github.com/easydigitaldownloads/easy-digital-downloads/blob/master/includes/class-edd-db.php
 *
 * Subclasses should define $table_name, $version, and $primary_key in __construct() method.
 *
 * @since 1.1.6
 */
abstract class WPForms_DB {

	/**
	 * Maximum length of index key.
	 *
	 * Indexes have a maximum size of 767 bytes. Historically, we haven't needed to be concerned about that.
	 * As of WP 4.2, however, WP moved to utf8mb4, which uses 4 bytes per character. This means that an index, which
	 * used to have room for floor(767/3) = 255 characters, now only has room for floor(767/4) = 191 characters.
	 *
	 * @since 1.8.2
	 */
	const MAX_INDEX_LENGTH = 191;

	/**
	 * The dedicated cache key to store the All Keys array.
	 *
	 * @since 1.9.0
	 */
	const ALL_KEYS = '_all_keys';

	/**
	 * Database table name.
	 *
	 * @since 1.1.6
	 *
	 * @var string
	 */
	public $table_name;

	/**
	 * Database version.
	 *
	 * @since 1.1.6
	 *
	 * @var string
	 */
	public $version;

	/**
	 * Primary key (unique field) for the database table.
	 *
	 * @since 1.1.6
	 *
	 * @var string
	 */
	public $primary_key;

	/**
	 * Database type identifier.
	 *
	 * @since 1.5.1
	 *
	 * @var string
	 */
	public $type;

	/**
	 * Cache group.
	 *
	 * @since 1.9.0
	 *
	 * @var string
	 */
	private $cache_group;

	/**
	 * Cache disabled.
	 *
	 * @since 1.9.0
	 *
	 * @var bool
	 */
	private $cache_disabled;

	/**
	 * WPForms_DB constructor.
	 *
	 * @since 1.9.0
	 */
	public function __construct() {

		$this->cache_group    = static::class . '_cache';
		$this->cache_disabled = defined( 'WPFORMS_DISABLE_DB_CACHE' ) && WPFORMS_DISABLE_DB_CACHE;

		$this->hooks();
	}

	/**
	 * Query filter.
	 *
	 * @since 1.9.0
	 *
	 * @return void
	 */
	private function hooks() {

		add_filter( 'query', [ $this, 'query_filter' ] );
	}

	/**
	 * Retrieve the list of columns for the database table.
	 * Subclasses should define an array of columns here.
	 *
	 * @since 1.1.6
	 *
	 * @return array List of columns.
	 */
	public function get_columns() {

		return [];
	}

	/**
	 * Retrieve column defaults.
	 * Subclasses can define default for any/all columns defined in the get_columns() method.
	 *
	 * @since 1.1.6
	 *
	 * @return array All defined column defaults.
	 */
	public function get_column_defaults() {

		return [];
	}

	/**
	 * Filter the query.
	 *
	 * @since 1.9.0
	 *
	 * @param string|mixed $query Query.
	 *
	 * @return string
	 */
	public function query_filter( $query ): string {

		$query = (string) $query;

		if ( strpos( $query, $this->table_name ) === false ) {
			// Not a query for our table, bail out.
			return $query;
		}

		if ( ! $this->is_select( $query ) ) {
			// Flush cache on non-SELECT queries.
			$this->cache_flush_group();
		}

		return $query;
	}

	/**
	 * Retrieve a row from the database based on a given row ID.
	 *
	 * @since 1.1.6
	 *
	 * @param int $row_id Row ID.
	 *
	 * @return null|object
	 */
	public function get( $row_id ) {

		global $wpdb;

		$key = md5( __METHOD__ . $row_id );
		$row = $this->cache_get( $key, $found );

		if ( $found ) {
			return $row;
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$row = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM $this->table_name WHERE $this->primary_key = %d LIMIT 1;", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				(int) $row_id
			)
		);

		$this->cache_set( $key, $row );

		return $row;
	}

	/**
	 * Retrieve a row based on column and row ID.
	 *
	 * @since 1.1.6
	 *
	 * @param string     $column Column name.
	 * @param int|string $value  Column value.
	 *
	 * @return object|null Database query result, object or null on failure.
	 */
	public function get_by( $column, $value ) {

		global $wpdb;

		if (
			empty( $value ) ||
			! array_key_exists( $column, $this->get_columns() )
		) {
			return null;
		}

		$key = md5( __METHOD__ . $column . $value );
		$row = $this->cache_get( $key, $found );

		if ( $found ) {
			return $row;
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$row = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM $this->table_name WHERE $column = %s LIMIT 1;", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$value
			)
		);

		$this->cache_set( $key, $row );

		return $row;
	}

	/**
	 * Retrieve a value based on column name and row ID.
	 *
	 * @since 1.1.6
	 *
	 * @param string     $column Column name.
	 * @param int|string $row_id Row ID.
	 *
	 * @return string|null Database query result (as string), or null on failure.
	 * @noinspection PhpUnused
	 */
	public function get_column( $column, $row_id ) {

		global $wpdb;

		if ( empty( $row_id ) || ! array_key_exists( $column, $this->get_columns() ) ) {
			return null;
		}

		$key = md5( __METHOD__ . $column . $row_id );
		$var = $this->cache_get( $key, $found );

		if ( $found ) {
			return $var;
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$var = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT $column FROM $this->table_name WHERE $this->primary_key = %d LIMIT 1;", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				(int) $row_id
			)
		);

		$this->cache_set( $key, $var );

		return $var;
	}

	/**
	 * Retrieve one column value based on another given column and matching value.
	 *
	 * @since 1.1.6
	 *
	 * @param string $column       Column name.
	 * @param string $column_where Column to match against in the WHERE clause.
	 * @param string $column_value Value to match to the column in the WHERE clause.
	 *
	 * @return string|null Database query result (as string), or null on failure.
	 * @noinspection PhpUnused
	 */
	public function get_column_by( $column, $column_where, $column_value ) {

		global $wpdb;

		if (
			empty( $column ) ||
			empty( $column_where ) ||
			empty( $column_value ) ||
			! array_key_exists( $column_where, $this->get_columns() ) ||
			! array_key_exists( $column, $this->get_columns() )
		) {
			return null;
		}

		$key = md5( __METHOD__ . $column . $column_where . $column_value );
		$var = $this->cache_get( $key, $found );

		if ( $found ) {
			return $var;
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$var = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT $column FROM $this->table_name WHERE $column_where = %s LIMIT 1;", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$column_value
			)
		);

		$this->cache_set( $key, $var );

		return $var;
	}

	/**
	 *  Clone of $wpdb->query() with caching.
	 *
	 * @since 1.9.0
	 *
	 * @param string $query Database query.
	 *
	 * @return int|bool Boolean true for CREATE, ALTER, TRUNCATE and DROP queries. Number of rows
	 *                  affected/selected for all other queries. Boolean false on error.
	 *
	 * @noinspection PhpMissingParamTypeInspection
	 */
	public function query( $query ) {

		global $wpdb;

		if ( ! $this->is_select( $query ) ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
			return $wpdb->query( $query );
		}

		$key     = md5( __METHOD__ . $query );
		$results = $this->cache_get( $key, $found );

		if ( $found ) {
			return $results;
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		$results = $wpdb->query( $query );

		$this->cache_set( $key, $results );

		return $results;
	}

	/**
	 * Clone of $wpdb->get_results() with caching.
	 *
	 * @since        1.9.0
	 *
	 * @param string|null $query  SQL query.
	 * @param string      $output Any of ARRAY_A | ARRAY_N | OBJECT | OBJECT_K constants.
	 *
	 * @return array|object|null Database query results.
	 * @noinspection PhpMissingParamTypeInspection
	 */
	public function get_results( $query = null, $output = OBJECT ) {

		global $wpdb;

		if ( ! $this->is_select( $query ) ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
			return $wpdb->get_results( $query, $output );
		}

		$key     = md5( __METHOD__ . $query . $output );
		$results = $this->cache_get( $key, $found );

		if ( $found ) {
			return $results;
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		$results = $wpdb->get_results( $query, $output );

		$this->cache_set( $key, $results );

		return $results;
	}

	/**
	 * Clone of $wpdb->get_col() with caching.
	 *
	 * @since 1.9.4
	 *
	 * @param string|null $query SQL query.
	 * @param int         $x     Column to return. Indexed from 0.
	 *
	 * @return array Database query results.
	 * @noinspection PhpMissingParamTypeInspection
	 */
	public function get_col( $query = null, $x = 0 ) {

		global $wpdb;

		if ( ! $this->is_select( $query ) ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
			return $wpdb->get_col( $query );
		}

		$key = md5( __METHOD__ . $query . $x );
		$col = $this->cache_get( $key, $found );

		if ( $found ) {
			return $col;
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		$col = $wpdb->get_col( $query, $x );

		$this->cache_set( $key, $col );

		return $col;
	}

	/**
	 * Clone of $wpdb->get_row() with caching.
	 *
	 * @since        1.9.0
	 *
	 * @param string|null $query  SQL query.
	 * @param string      $output Optional. The required return type. One of OBJECT, ARRAY_A, or ARRAY_N, which
	 *                            correspond to an stdClass object, an associative array, or a numeric array,
	 *                            respectively. Default OBJECT.
	 * @param int         $y      Optional. Row to return. Indexed from 0. Default 0.
	 *
	 * @return array|int|object|stdClass|null Database query result in format specified by $output or null on failure.
	 * @noinspection PhpMissingParamTypeInspection
	 */
	public function get_row( $query = null, $output = OBJECT, $y = 0 ) {

		global $wpdb;

		if ( ! $query ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
			return $wpdb->get_row( $query, $output, $y );
		}

		$key = md5( __METHOD__ . $query . $output . $y );
		$row = $this->cache_get( $key, $found );

		if ( $found ) {
			return $row;
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		$row = $wpdb->get_row( $query, $output, $y );

		$this->cache_set( $key, $row );

		return $row;
	}

	/**
	 * Clone of $wpdb->get_var() with caching.
	 *
	 * @since        1.9.0
	 *
	 * @param string|null $query Optional. SQL query. Defaults to null, use the result from the previous query.
	 * @param int         $x     Optional. Column of value to return. Indexed from 0. Default 0.
	 * @param int         $y     Optional. Row of value to return. Indexed from 0. Default 0.
	 *
	 * @return string|null Database query result (as string), or null on failure.
	 *
	 * @noinspection PhpMissingParamTypeInspection
	 */
	public function get_var( $query = null, $x = 0, $y = 0 ) {

		global $wpdb;

		if ( ! $query ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
			return $wpdb->get_var( $query, $x, $y );
		}

		$key = md5( __METHOD__ . $query . $x . $y );
		$var = $this->cache_get( $key, $found );

		if ( $found ) {
			return $var;
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		$var = $wpdb->get_var( $query, $x, $y );

		$this->cache_set( $key, $var );

		return $var;
	}

	/**
	 * Insert a new record into the database.
	 *
	 * @since 1.1.6
	 *
	 * @param array  $data Column data.
	 * @param string $type Optional. Data type context.
	 *
	 * @return int ID for the newly inserted record. Zero otherwise.
	 */
	public function add( $data, $type = '' ) {

		global $wpdb;

		// Set default values.
		$data = wp_parse_args( $data, $this->get_column_defaults() );

		do_action( 'wpforms_pre_insert_' . $type, $data );

		// Initialise column format array.
		$column_formats = $this->get_columns();

		// Force fields to lower a case.
		$data = array_change_key_case( $data );

		// Whitelist columns.
		$data = array_intersect_key( $data, $column_formats );

		// Reorder $column_formats to match the order of columns given in $data.
		$data_keys      = array_keys( $data );
		$column_formats = array_merge( array_flip( $data_keys ), $column_formats );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$wpdb->insert( $this->table_name, $data, $column_formats );

		do_action( 'wpforms_post_insert_' . $type, $wpdb->insert_id, $data );

		return $wpdb->insert_id;
	}

	/**
	 * Insert a new record into the database. This runs the add() method.
	 *
	 * @see add()
	 *
	 * @since 1.1.6
	 *
	 * @param array $data Column data.
	 *
	 * @return int ID for the newly inserted record.
	 */
	public function insert( $data ) {

		return $this->add( $data );
	}

	/**
	 * Update an existing record in the database.
	 *
	 * @since 1.1.6
	 *
	 * @param int|string $row_id Row ID for the record being updated.
	 * @param array      $data   Optional. Array of columns and associated data to update. Default empty array.
	 * @param string     $where  Optional. Column to match against in the WHERE clause. If empty, $primary_key
	 *                           will be used. Default empty.
	 * @param string     $type   Optional. Data type context, e.g. 'affiliate', 'creative', etc. Default empty.
	 *
	 * @return bool False if the record could not be updated, true otherwise.
	 */
	public function update( $row_id, $data = [], $where = '', $type = '' ) {

		global $wpdb;

		// Row ID must be a positive integer.
		$row_id = absint( $row_id );

		if ( empty( $row_id ) ) {
			return false;
		}

		if ( empty( $where ) ) {
			$where = $this->primary_key;
		}

		/**
		 * Fires before updating a record in the database.
		 *
		 * @since 1.5.9
		 * @since 1.9.2 Added $row_id parameter.
		 *
		 * @param array $data   Array of columns and associated data to update.
		 * @param int   $row_id Row ID for the record being updated.
		 */
		do_action( "wpforms_pre_update_{$type}", $data, $row_id );

		// Initialise column format array.
		$column_formats = $this->get_columns();

		// Force fields to the lower case.
		$data = array_change_key_case( $data );

		// Whitelist columns.
		$data = array_intersect_key( $data, $column_formats );

		// Reorder $column_formats to match the order of columns given in $data.
		$data_keys      = array_keys( $data );
		$column_formats = array_merge( array_flip( $data_keys ), $column_formats );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		if ( $wpdb->update( $this->table_name, $data, [ $where => $row_id ], $column_formats ) === false ) {
			return false;
		}

		/**
		 * Fires after a record has been updated in the database.
		 *
		 * @since 1.1.6
		 * @since 1.9.2 Added $row_id parameter.
		 *
		 * @param array $data   Array of columns and associated data that were updated.
		 * @param int   $row_id Row ID for the record that was updated.
		 */
		do_action( "wpforms_post_update_{$type}", $data, $row_id );

		return true;
	}

	/**
	 * Delete a record from the database.
	 *
	 * @since 1.1.6
	 *
	 * @param int|string $row_id Row ID.
	 *
	 * @return bool False if the record could not be deleted, true otherwise.
	 */
	public function delete( $row_id = 0 ): bool {

		global $wpdb;

		// Row ID must be a positive integer.
		$row_id = absint( $row_id );

		if ( empty( $row_id ) ) {
			return false;
		}

		/**
		 * Fires before a record is deleted from the database.
		 *
		 * @since 1.5.9
		 *
		 * @param int $row_id Row ID.
		 */
		do_action( 'wpforms_pre_delete', $row_id );

		/**
		 * Fires before a record is deleted from the database by type.
		 *
		 * @since 1.5.9
		 * @since 1.8.6 Added `$primary_key` parameter.
		 *
		 * @param int    $row_id      Column value.
		 * @param string $primary_key Column name.
		 */
		do_action( 'wpforms_pre_delete_' . $this->type, $row_id, $this->primary_key );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $wpdb->query(
			$wpdb->prepare(
				"DELETE FROM $this->table_name WHERE $this->primary_key = %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$row_id
			)
		);

		if ( $result === false ) {
			return false;
		}

		do_action( 'wpforms_post_delete', $row_id );
		do_action( 'wpforms_post_delete_' . $this->type, $row_id );

		return true;
	}

	/**
	 * Delete a record from the database by column.
	 *
	 * @since 1.1.6
	 *
	 * @param string     $column       Column name.
	 * @param int|string $column_value Column value.
	 *
	 * @return bool False if the record could not be deleted, true otherwise.
	 */
	public function delete_by( $column, $column_value ) {

		global $wpdb;

		if (
			empty( $column ) ||
			empty( $column_value ) ||
			! array_key_exists( $column, $this->get_columns() )
		) {
			return false;
		}

		// This action is documented in includes/class-db.php method delete().
		do_action( 'wpforms_pre_delete', $column_value );

		// This action is documented in includes/class-db.php method delete().
		do_action( 'wpforms_pre_delete_' . $this->type, $column_value, $column );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $wpdb->query(
			$wpdb->prepare(
				"DELETE FROM $this->table_name WHERE $column = %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$column_value
			)
		);

		if ( $result === false ) {
			return false;
		}

		do_action( 'wpforms_post_delete', $column_value );
		do_action( 'wpforms_post_delete_' . $this->type, $column_value );

		return true;
	}

	/**
	 * Delete record(s) from the database using WHERE IN syntax.
	 *
	 * @since 1.6.4
	 *
	 * @param string $column        Column name.
	 * @param mixed  $column_values Column values.
	 *
	 * @return int|bool Number of deleted records, false otherwise.
	 */
	public function delete_where_in( $column, $column_values ) {

		global $wpdb;

		if ( empty( $column ) || empty( $column_values ) ) {
			return false;
		}

		if ( ! array_key_exists( $column, $this->get_columns() ) ) {
			return false;
		}

		$values = (array) $column_values;

		foreach ( $values as $key => $value ) {
			// Check if a string contains an integer and sanitize accordingly.
			if ( (string) (int) $value === $value ) {
				$values[ $key ]       = (int) $value;
				$placeholders[ $key ] = '%d';
			} else {
				$values[ $key ]       = sanitize_text_field( $value );
				$placeholders[ $key ] = '%s';
			}
		}

		$placeholders = isset( $placeholders ) ? implode( ',', $placeholders ) : '';
		$sql          = "DELETE FROM $this->table_name WHERE $column IN ( $placeholders )";

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		return $wpdb->query( $wpdb->prepare( $sql, $values ) );
	}

	/**
	 * Check if the given table exists.
	 *
	 * @since 1.1.6
	 * @since 1.5.9 Default value is now the current child class table name.
	 *
	 * @param string $table The table name. Defaults to the child class table name.
	 *
	 * @return bool If the table name exists.
	 */
	public function table_exists( string $table = '' ): bool {

		$table = ! empty( $table ) ? sanitize_text_field( $table ) : $this->table_name;

		return DB::table_exists( $table );
	}

	/**
	 * Build WHERE for a query.
	 *
	 * @since 1.7.2.2
	 *
	 * @param array           $args    Optional args.
	 * @param array           $keys    Allowed arg items.
	 * @param string|string[] $formats Formats of arg items.
	 *
	 * @return string
	 */
	protected function build_where( $args, $keys = [], $formats = [] ) {

		$formats = array_pad( $formats, count( $keys ), '%d' );
		$where   = '';

		foreach ( $keys as $index => $key ) {
			// Value `$args[ $key ]` can be a natural number and a numeric string.
			// We should skip empty string values, but continue working with '0'.
			if ( empty( $args[ $key ] ) && $args[ $key ] !== '0' ) {
				continue;
			}

			$ids = wpforms_wpdb_prepare_in( $args[ $key ], $formats[ $index ] );

			$where .= empty( $where ) ? 'WHERE' : 'AND';
			$where .= " `{$key}` IN ( {$ids} ) ";
		}

		return $where;
	}

	/**
	 * WP Cache Get wrapper.
	 *
	 * @since 1.9.0
	 *
	 * @param int|string $key   Cache key.
	 * @param bool|null  $found Whether the key was found in the cache.
	 *
	 * @return false|mixed
	 * @noinspection PhpMissingParamTypeInspection
	 */
	private function cache_get( $key, &$found ) {

		if ( $this->cache_disabled ) {
			$found = false;

			return false;
		}

		$all_keys = wp_cache_get( self::ALL_KEYS, $this->cache_group, false, $found );
		$all_keys = $found ? (array) $all_keys : [];

		if ( ! in_array( $key, $all_keys, true ) ) {
			$found = false;

			return false;
		}

		$data = wp_cache_get( $key, $this->cache_group, false, $found );

		return $found ? $data : false;
	}

	/**
	 * WP Cache Set wrapper.
	 *
	 * @since 1.9.0
	 *
	 * @param string $key  Cache key.
	 * @param mixed  $data Cache data.
	 *
	 * @return bool
	 * @noinspection PhpReturnValueOfMethodIsNeverUsedInspection
	 */
	private function cache_set( string $key, $data ): bool {

		if ( $this->cache_disabled ) {
			return false;
		}

		$all_keys = wp_cache_get( self::ALL_KEYS, $this->cache_group, false, $found );
		$all_keys = $found ? array_unique( array_merge( (array) $all_keys, [ $key ] ) ) : [ $key ];

		return (
			wp_cache_set( $key, $data, $this->cache_group ) &&
			wp_cache_set( self::ALL_KEYS, $all_keys, $this->cache_group )
		);
	}

	/**
	 * Flush the cache group.
	 *
	 * @since 1.9.0
	 *
	 * @return bool
	 * @noinspection PhpReturnValueOfMethodIsNeverUsedInspection
	 */
	private function cache_flush_group(): bool {

		if ( $this->cache_disabled ) {
			return false;
		}

		$all_keys = wp_cache_get( self::ALL_KEYS, $this->cache_group, false, $found );

		if ( ! $found ) {
			return true;
		}

		$result = wp_cache_delete( self::ALL_KEYS, $this->cache_group );

		foreach ( (array) $all_keys as $key ) {
			$result = wp_cache_delete( $key, $this->cache_group ) && $result;
		}

		return $result;
	}

	/**
	 * Check if the query is a SELECT query.
	 *
	 * @since 1.9.0
	 *
	 * @param string|null $query SQL query.
	 *
	 * @return bool
	 * @noinspection PhpMissingParamTypeInspection
	 */
	private function is_select( $query ): bool {

		return stripos( trim( (string) $query ), 'SELECT' ) === 0;
	}

	/**
	 * Get an instance of the current class.
	 * Used to reload the class while going through the blogs of multisite.
	 *
	 * @see WPForms_Install::maybe_create_tables()
	 *
	 * @since 1.8.9
	 */
	public static function get_instance(): WPForms_DB {

		return new static();
	}
}
