<?php

namespace WPForms\Db\Payments;

use WPForms_DB;

/**
 * Class for the Payment Meta database table.
 *
 * @since 1.8.2
 */
class Meta extends WPForms_DB {

	/**
	 * Primary class constructor.
	 *
	 * @since 1.8.2
	 */
	public function __construct() {

		parent::__construct();

		$this->table_name  = self::get_table_name();
		$this->primary_key = 'id';
		$this->type        = 'payment_meta';
	}

	/**
	 * Get the table name.
	 *
	 * @since 1.8.2
	 *
	 * @return string
	 */
	public static function get_table_name() {

		global $wpdb;

		return $wpdb->prefix . 'wpforms_payment_meta';
	}

	/**
	 * Get table columns.
	 *
	 * @since 1.8.2
	 *
	 * @return array
	 */
	public function get_columns() {

		return [
			'id'         => '%d',
			'payment_id' => '%d',
			'meta_key'   => '%s', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value' => '%s', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
		];
	}

	/**
	 * Default column values.
	 *
	 * @since 1.8.2
	 *
	 * @return array
	 */
	public function get_column_defaults() {

		return [
			'payment_id' => 0,
			'meta_key'   => '', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value' => '', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
		];
	}

	/**
	 * Create the table.
	 *
	 * @since 1.8.2
	 */
	public function create_table() {

		global $wpdb;

		$charset_collate  = $wpdb->get_charset_collate();
		$max_index_length = self::MAX_INDEX_LENGTH;

		/**
		 * Note: there must be two spaces between the words PRIMARY KEY and the definition of primary key.
		 *
		 * @link https://codex.wordpress.org/Creating_Tables_with_Plugins#Creating_or_Updating_the_Table
		 */
		$query = "CREATE TABLE $this->table_name (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			payment_id bigint(20) NOT NULL,
			meta_key varchar(255),
			meta_value longtext,
			PRIMARY KEY  (id),
			KEY payment_id (payment_id),
			KEY meta_key (meta_key($max_index_length)),
			KEY meta_value (meta_value($max_index_length))
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $query );
	}

	/**
	 * Insert payment meta's.
	 *
	 * @since 1.8.2
	 *
	 * @param int   $payment_id Payment ID.
	 * @param array $meta       Payment meta to be inserted.
	 */
	public function bulk_add( $payment_id, $meta ) {

		global $wpdb;

		$values = [];

		foreach ( $meta as $meta_key => $meta_value ) {

			// Empty strings are skipped.
			if ( $meta_value === '' ) {
				continue;
			}

			$values[] = $wpdb->prepare(
				'( %d, %s, %s )',
				$payment_id,
				$meta_key,
				maybe_serialize( $meta_value )
			);
		}

		if ( ! $values ) {
			return;
		}

		$values = implode( ', ', $values );

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query(
			"INSERT INTO $this->table_name
			( payment_id, meta_key, meta_value )
			VALUES $values"
		);
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching
	}

	/**
	 * Update or add payment meta.
	 *
	 * If the meta key already exists for given payment id, update the meta value. Otherwise, add the meta key and value.
	 *
	 * @since 1.8.4
	 *
	 * @param int    $payment_id Payment ID.
	 * @param string $meta_key   Payment meta key.
	 * @param mixed  $meta_value Payment meta value.
	 *
	 * @return bool
	 */
	public function update_or_add( $payment_id, $meta_key, $meta_value ) {

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.SlowDBQuery.slow_db_query_meta_key, WordPress.DB.SlowDBQuery.slow_db_query_meta_value
		$row = $this->get_last_by( $meta_key, $payment_id );

		if ( $row ) {
			return $this->update( $row->id, [ 'meta_value' => maybe_serialize( $meta_value ) ], '', $this->type );
		}

		return (bool) $this->add(
			[
				'payment_id' => $payment_id,
				'meta_key'   => $meta_key,
				'meta_value' => maybe_serialize( $meta_value ),
			],
			$this->type
		);
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.SlowDBQuery.slow_db_query_meta_key, WordPress.DB.SlowDBQuery.slow_db_query_meta_value
	}

	/**
	 * Add payment log.
	 *
	 * @since 1.8.4
	 *
	 * @param int    $payment_id Payment ID.
	 * @param string $content    Log content.
	 *
	 * @return bool
	 */
	public function add_log( $payment_id, $content ) {

		return (bool) $this->add(
			[
				'payment_id' => $payment_id,
				'meta_key'   => 'log', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_value' => wp_json_encode( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
					[
						'value' => wp_kses_post( $content ),
						'date'  => gmdate( 'Y-m-d H:i:s' ),
					]
				),
			],
			$this->type
		);
	}

	/**
	 * Get single payment meta.
	 *
	 * @since 1.8.2
	 *
	 * @param int         $payment_id Payment ID.
	 * @param string|null $meta_key   Payment meta to be retrieved.
	 *
	 * @return mixed Meta value.
	 */
	public function get_single( $payment_id, $meta_key ) {

		global $wpdb;

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching
		$meta_value = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT meta_value FROM $this->table_name
				WHERE payment_id = %d AND meta_key = %s ORDER BY id DESC LIMIT 1",
				$payment_id,
				$meta_key
			)
		);
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching

		return maybe_unserialize( $meta_value );
	}

	/**
	 * Get all payment meta.
	 *
	 * @since 1.8.2
	 *
	 * @param int $payment_id Payment ID.
	 *
	 * @return array|null
	 */
	public function get_all( $payment_id ) {

		global $wpdb;

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT meta_key, meta_value as value FROM $this->table_name
				WHERE payment_id = %d ORDER BY id DESC",
				$payment_id
			),
			OBJECT_K
		);
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching
	}

	/**
	 * Retrieve all rows based on meta_key value.
	 *
	 * @since 1.8.2
	 *
	 * @param string $meta_key   Meta key value.
	 * @param int    $payment_id Payment ID.
	 *
	 * @return object|null
	 */
	public function get_all_by( $meta_key, $payment_id ) {

		global $wpdb;

		if ( empty( $meta_key ) ) {
			return null;
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT meta_value as value FROM $this->table_name WHERE payment_id = %d AND meta_key = %s ORDER BY id DESC", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$payment_id,
				$meta_key
			),
			ARRAY_A
		);
	}

	/**
	 * Check if there are valid entries with a specific meta key.
	 *
	 * @since 1.8.4
	 *
	 * @param string $meta_key The meta key to check.
	 *
	 * @return bool
	 */
	public function is_valid_meta_by_meta_key( $meta_key ) {

		// Check if the meta key is empty and return false.
		if ( empty( $meta_key ) ) {
			return false;
		}

		// Retrieve the global database instance.
		global $wpdb;

		$payment_handler        = wpforms()->obj( 'payment' );
		$payment_table_name     = $payment_handler->table_name;
		$secondary_where_clause = $payment_handler->add_secondary_where_conditions();

		// Prepare and execute the SQL query to check if there are valid entries with the given meta key.
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching
		return (bool) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT 1 FROM {$this->table_name} AS pm
				WHERE meta_key = %s AND meta_value IS NOT NULL
				AND EXISTS (SELECT 1 FROM {$payment_table_name} AS p WHERE p.id = pm.payment_id {$secondary_where_clause})
				LIMIT 1",
				$meta_key
			)
		);
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching
	}

	/**
	 * Check if the given meta key and value exist in the payment meta table.
	 *
	 * @since 1.8.4
	 *
	 * @param string $meta_key   Meta key value.
	 * @param string $meta_value Meta value.
	 *
	 * @return bool
	 */
	public function is_valid_meta( $meta_key, $meta_value ) {

		// Check if the meta key or value is empty and return false.
		if ( empty( $meta_key ) || empty( $meta_value ) ) {
			return false;
		}

		// Retrieve the global database instance.
		global $wpdb;

		// Prepare and execute the SQL query to check if the given meta key and value exist in the payment meta table.
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return (bool) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT EXISTS( SELECT 1 FROM {$this->table_name} WHERE meta_key = %s AND meta_value = %s )",
				$meta_key,
				$meta_value
			)
		);
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	}

	/**
	 * Retrieve payment meta data by given meta key and value.
	 *
	 * @since 1.8.4
	 *
	 * @param string $meta_key   Meta key value.
	 * @param string $meta_value Meta value.
	 *
	 * @return array
	 */
	public function get_all_by_meta( $meta_key, $meta_value ) {

		// Check if the meta key or value is empty and return null.
		if ( empty( $meta_key ) || empty( $meta_value ) ) {
			return [];
		}

		// Retrieve the global database instance.
		global $wpdb;

		// Prepare and execute the SQL query to retrieve payment meta data based on the given meta key and value.
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT meta_key, meta_value AS value FROM {$this->table_name}
				WHERE payment_id = ( SELECT payment_id FROM {$this->table_name}
				WHERE meta_key = %s AND meta_value = %s LIMIT 1 )",
				$meta_key,
				$meta_value
			),
			OBJECT_K
		);
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	}

	/**
	 * Get row from the payment meta table for given payment id and meta key.
	 *
	 * @since 1.8.4
	 *
	 * @param string $meta_key   Meta key value.
	 * @param int    $payment_id Payment ID.
	 *
	 * @return object|null
	 */
	public function get_last_by( $meta_key, $payment_id ) {

		global $wpdb;

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM $this->table_name
				WHERE payment_id = %d AND meta_key = %s
				ORDER BY id DESC LIMIT 1",
				$payment_id,
				$meta_key
			)
		);
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.SlowDBQuery.slow_db_query_meta_key
	}
}
