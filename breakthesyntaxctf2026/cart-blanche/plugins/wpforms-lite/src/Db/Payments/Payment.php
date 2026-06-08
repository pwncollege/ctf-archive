<?php

namespace WPForms\Db\Payments;

use WPForms_DB;

/**
 * Class for the Payments database table.
 *
 * @since 1.8.2
 */
class Payment extends WPForms_DB {

	/**
	 * Primary class constructor.
	 *
	 * @since 1.8.2
	 */
	public function __construct() {

		parent::__construct();

		$this->table_name  = self::get_table_name();
		$this->primary_key = 'id';
		$this->type        = 'payment';
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

		return $wpdb->prefix . 'wpforms_payments';
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
			'id'                  => '%d',
			'form_id'             => '%d',
			'status'              => '%s',
			'subtotal_amount'     => '%f',
			'discount_amount'     => '%f',
			'total_amount'        => '%f',
			'currency'            => '%s',
			'entry_id'            => '%d',
			'gateway'             => '%s',
			'type'                => '%s',
			'mode'                => '%s',
			'transaction_id'      => '%s',
			'customer_id'         => '%s',
			'subscription_id'     => '%s',
			'subscription_status' => '%s',
			'title'               => '%s',
			'date_created_gmt'    => '%s',
			'date_updated_gmt'    => '%s',
			'is_published'        => '%d',
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

		$date = gmdate( 'Y-m-d H:i:s' );

		return [
			'form_id'             => 0,
			'status'              => '',
			'subtotal_amount'     => 0,
			'discount_amount'     => 0,
			'total_amount'        => 0,
			'currency'            => '',
			'entry_id'            => 0,
			'gateway'             => '',
			'type'                => '',
			'mode'                => '',
			'transaction_id'      => '',
			'customer_id'         => '',
			'subscription_id'     => '',
			'subscription_status' => '',
			'title'               => '',
			'date_created_gmt'    => $date,
			'date_updated_gmt'    => $date,
			'is_published'        => 1,
		];
	}

	/**
	 * Insert a new payment into the database.
	 *
	 * @since 1.8.2
	 *
	 * @param array  $data Column data.
	 * @param string $type Optional. Data type context.
	 *
	 * @return int ID for the newly inserted payment. Zero otherwise.
	 */
	public function add( $data, $type = '' ) {

		// Return early if the status is not allowed.
		// TODO: consider validating other properties as well or get rid of it.
		if ( isset( $data['status'] ) && ! ValueValidator::is_valid( $data['status'], 'status' ) ) {
			return 0;
		}

		// Use database type identifier if a context is empty.
		$type = empty( $type ) ? $this->type : $type;

		return parent::add( $data, $type );
	}

	/**
	 * Retrieve a payment from the database based on a given payment ID.
	 *
	 * @since 1.8.2
	 *
	 * @param int   $payment_id Payment ID.
	 * @param array $args       Additional arguments.
	 *
	 * @return object|null
	 */
	public function get( $payment_id, $args = [] ) {

		if ( ! $this->current_user_can( $payment_id, $args ) && wpforms()->obj( 'access' )->init_allowed() ) {
			return null;
		}

		$payment = parent::get( $payment_id );

		return $payment ? $this->cast_amounts_to_float( $payment ) : null;
	}

	/**
	 * Retrieve a row based on column value.
	 *
	 * @since 1.8.7
	 *
	 * @param string     $column Column name.
	 * @param int|string $value  Column value.
	 *
	 * @return object|null Database query result, object or null on failure.
	 */
	public function get_by( $column, $value ) {

		$payment = parent::get_by( $column, $value );

		return $payment ? $this->cast_amounts_to_float( $payment ) : null;
	}

	/**
	 * Cast amounts to float in the given payment data object.
	 *
	 * @since 1.8.7
	 *
	 * @param object $payment Payment ID.
	 *
	 * @return object
	 */
	private function cast_amounts_to_float( $payment ) {

		if ( empty( $payment ) || ! is_object( $payment ) ) {
			return $payment;
		}

		// Amounts is stored in DB as decimal(26,8), but appear here as strings.
		// Therefore, they should be cast to float to avoid further multi-time currency conversion.
		$payment->subtotal_amount = $payment->subtotal_amount ? (float) $payment->subtotal_amount : 0;
		$payment->discount_amount = $payment->discount_amount ? (float) $payment->discount_amount : 0;
		$payment->total_amount    = $payment->total_amount ? (float) $payment->total_amount : 0;

		return $payment;
	}

	/**
	 * Update an existing payment in the database.
	 *
	 * @since 1.8.2
	 *
	 * @param string $payment_id Payment ID.
	 * @param array  $data       Array of columns and associated data to update.
	 * @param string $where      Column to match against in the WHERE clause. If empty, $primary_key will be used.
	 * @param string $type       Data type context.
	 * @param array  $args       Additional arguments.
	 *
	 * @return bool
	 */
	public function update( $payment_id, $data = [], $where = '', $type = '', $args = [] ) {

		if ( ! $this->current_user_can( $payment_id, $args ) ) {
			return false;
		}

		// TODO: consider validating other properties as well or get rid of it.
		if ( isset( $data['status'] ) && ! ValueValidator::is_valid( $data['status'], 'status' ) ) {
			return false;
		}

		// Use database type identifier if a context is empty.
		$type = empty( $type ) ? $this->type : $type;

		return parent::update( $payment_id, $data, $where, $type );
	}

	/**
	 * Delete a payment from the database, also removes payment meta.
	 *
	 * @since 1.8.2
	 *
	 * @param int   $payment_id Payment ID.
	 * @param array $args       Additional arguments.
	 *
	 * @return bool False if the payment and meta could not be deleted, true otherwise.
	 */
	public function delete( $payment_id = 0, $args = [] ): bool {

		if ( ! $this->current_user_can( $payment_id, $args ) ) {
			return false;
		}

		$is_payment_deleted = parent::delete( $payment_id );
		$is_meta_deleted    = wpforms()->obj( 'payment_meta' )->delete_by( 'payment_id', $payment_id );

		return $is_payment_deleted && $is_meta_deleted;
	}

	/**
	 * Retrieve a list of payments.
	 *
	 * @since 1.8.2
	 *
	 * @param array $args Arguments.
	 *
	 * @return array
	 */
	public function get_payments( $args = [] ) {

		global $wpdb;

		$args = $this->sanitize_get_payments_args( $args );

		if ( ! $this->current_user_can( 0, $args ) ) {
			return [];
		}

		// Prepare query.
		$query[] = "SELECT p.* FROM {$this->table_name} as p";

		/**
		 * Filter the query for get_payments method before the WHERE clause.
		 *
		 * @since 1.8.2
		 *
		 * @param string $where Before the WHERE clause in DB query.
		 * @param array  $args  Query arguments.
		 *
		 * @return string
		 */
		$query[] = apply_filters( 'wpforms_db_payments_payment_get_payments_query_before_where', '', $args );
		$query[] = 'WHERE 1=1';
		$query[] = $this->add_columns_where_conditions( $args );
		$query[] = $this->add_secondary_where_conditions( $args );

		/**
		 * Extend the query for the get_payments method after the WHERE clause.
		 *
		 * This hook provides the flexibility to modify the SQL query by appending custom conditions
		 * right after the WHERE clause.
		 *
		 * @since 1.8.4
		 *
		 * @param string $where After the WHERE clause in the database query.
		 * @param array  $args  Query arguments.
		 *
		 * @return string
		 */
		$query[] = apply_filters( 'wpforms_db_payments_payment_get_payments_query_after_where', '', $args );

		// Order.
		$query[] = sprintf( 'ORDER BY %s', sanitize_sql_orderby( "{$args['orderby']} {$args['order']}" ) );

		// Limit.
		$query[] = $wpdb->prepare( 'LIMIT %d, %d', $args['offset'], $args['number'] );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		$result = $wpdb->get_results( implode( ' ', $query ), ARRAY_A );

		// Get results.
		return ! $result ? [] : $result;
	}

	/**
	 * Create the table.
	 *
	 * @since 1.8.2
	 */
	public function create_table() {

		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		/**
		 * To avoid any possible issues during migration from entries to payments' table,
		 * all data types are preserved.
		 *
		 * Note: there must be two spaces between the words PRIMARY KEY and the definition of primary key.
		 *
		 * @link https://codex.wordpress.org/Creating_Tables_with_Plugins#Creating_or_Updating_the_Table
		 */
		$query = "CREATE TABLE $this->table_name (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			form_id bigint(20) NOT NULL,
			status varchar(10) NOT NULL DEFAULT '',
			subtotal_amount decimal(26,8) NOT NULL DEFAULT 0,
			discount_amount decimal(26,8) NOT NULL DEFAULT 0,
			total_amount decimal(26,8) NOT NULL DEFAULT 0,
			currency varchar(3) NOT NULL DEFAULT '',
			entry_id bigint(20) NOT NULL DEFAULT 0,
			gateway varchar(20) NOT NULL DEFAULT '',
			type varchar(12) NOT NULL DEFAULT '',
			mode varchar(4) NOT NULL DEFAULT '',
			transaction_id varchar(40) NOT NULL DEFAULT '',
			customer_id varchar(40) NOT NULL DEFAULT '',
			subscription_id varchar(40) NOT NULL DEFAULT '',
			subscription_status varchar(10) NOT NULL DEFAULT '',
			title varchar(255) NOT NULL DEFAULT '',
			date_created_gmt datetime NOT NULL,
			date_updated_gmt datetime NOT NULL,
			is_published tinyint(1) NOT NULL DEFAULT 1,
			PRIMARY KEY  (id),
			KEY form_id (form_id),
			KEY status (status(8)),
			KEY total_amount (total_amount),
			KEY type (type(8)),
			KEY transaction_id (transaction_id(32)),
			KEY customer_id (customer_id(32)),
			KEY subscription_id (subscription_id(32)),
			KEY subscription_status (subscription_status(8)),
			KEY title (title(64))
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $query );
	}

	/**
	 * Check if the current user has capabilities to manage payments.
	 *
	 * @since 1.8.2
	 *
	 * @param int   $payment_id Payment ID.
	 * @param array $args       Additional arguments.
	 *
	 * @return bool
	 * @noinspection IfReturnReturnSimplificationInspection
	 */
	private function current_user_can( $payment_id, $args = [] ) {

		$manage_cap = wpforms_get_capability_manage_options();

		if ( ! isset( $args['cap'] ) ) {
			$args['cap'] = $manage_cap;
		}

		if ( ! empty( $args['cap'] ) && ! wpforms_current_user_can( $args['cap'], $payment_id ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Construct where clauses for selected columns.
	 *
	 * @since 1.8.4
	 *
	 * @param array $args Query arguments.
	 *
	 * @return string
	 */
	public function add_columns_where_conditions( $args = [] ) {

		// Allowed columns for filtering.
		$allowed_cols = [
			'form_id',
			'entry_id',
			'status',
			'subscription_status',
			'type',
			'gateway',
		];

		$where = '';

		// Determine if this is a table query.
		$is_table_query   = ! empty( $args['table_query'] );
		$keys_to_validate = [ 'status', 'subscription_status', 'type', 'gateway' ];

		foreach ( $args as $key => $value ) {
			if ( empty( $value ) || ! in_array( $key, $allowed_cols, true ) ) {
				continue;
			}

			// Explode values if needed.
			$values = explode( '|', $value );

			// Run some keys through the "ValueValidator" class to make sure they are valid.
			if ( in_array( $key, $keys_to_validate, true ) ) {
				$values = array_filter(
					$values,
					static function ( $v ) use ( $key ) {

						return ValueValidator::is_valid( $v, $key );
					}
				);
			}

			// Skip if no valid values found.
			if ( empty( $values ) ) {
				continue;
			}

			// Merge "Partially Refunded" status with "Refunded" status.
			if ( $is_table_query && $key === 'status' && in_array( 'refunded', $values, true ) ) {
				$values[] = 'partrefund';
			}

			$placeholders = wpforms_wpdb_prepare_in( $values );

			// Prepare and add to WHERE clause.
			$where .= " AND {$key} IN ({$placeholders})";
		}

		return $where;
	}

	/**
	 * Construct secondary where clauses.
	 *
	 * @since 1.8.2
	 *
	 * @param array $args Query arguments.
	 *
	 * @return string
	 */
	public function add_secondary_where_conditions( $args = [] ) {

		global $wpdb;

		/**
		 * Filter arguments needed for all query.
		 *
		 * @since 1.8.2
		 *
		 * @param array $args Query arguments.
		 */
		$args = (array) apply_filters( 'wpforms_db_payments_payment_add_secondary_where_conditions_args', $args );
		$args = wp_parse_args(
			(array) $args,
			[
				'currency'     => wpforms_get_currency(),
				'mode'         => 'live',
				'is_published' => 1,
			]
		);

		$where = '';

		// If it's a valid mode, add it to a WHERE clause.
		if ( ValueValidator::is_valid( $args['mode'], 'mode' ) ) {
			$where .= $wpdb->prepare( ' AND mode = %s', $args['mode'] );
		}

		$where .= $wpdb->prepare( ' AND currency = %s', $args['currency'] );
		$where .= $wpdb->prepare( ' AND is_published = %d', $args['is_published'] );

		return $where;
	}

	/**
	 * Sanitize query arguments for get_payments() method.
	 *
	 * @since 1.8.2
	 *
	 * @param array $args Query arguments.
	 *
	 * @return array
	 */
	private function sanitize_get_payments_args( $args ) {

		$defaults = [
			'number'  => 20,
			'offset'  => 0,
			'orderby' => 'id',
			'order'   => 'DESC',
		];

		$args = wp_parse_args( (array) $args, $defaults );

		// Sanitize.
		$args['number'] = absint( $args['number'] );
		$args['offset'] = absint( $args['offset'] );

		if ( $args['number'] === 0 ) {
			$args['number'] = $defaults['number'];
		}

		return $args;
	}
}
