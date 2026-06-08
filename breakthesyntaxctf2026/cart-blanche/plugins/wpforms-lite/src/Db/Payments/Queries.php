<?php

namespace WPForms\Db\Payments;

/**
 * Class for the Payments database queries.
 *
 * @since 1.8.2
 */
class Queries extends Payment {

	/**
	 * Check if given payment table column has different values.
	 *
	 * @since 1.8.2
	 *
	 * @param string $column Column name.
	 *
	 * @return bool
	 */
	public function has_different_values( $column ) {

		global $wpdb;

		$subquery[] = "SELECT $column FROM $this->table_name WHERE 1=1";
		$subquery[] = $this->add_secondary_where_conditions();
		$subquery[] = 'LIMIT 1';
		$subquery   = implode( ' ', $subquery );

		$query[] = "SELECT $column FROM $this->table_name WHERE 1=1";
		$query[] = $this->add_secondary_where_conditions();
		$query[] = "AND $column != ( $subquery )";
		$query[] = 'LIMIT 1';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		$result = $wpdb->get_var( implode( ' ', $query ) );

		return ! empty( $result );
	}

	/**
	 * Check if there is a subscription payment.
	 *
	 * @since 1.8.2
	 *
	 * @return bool
	 */
	public function has_subscription() {

		return $this->if_exists(
			[
				'type' => implode( '|', array_keys( ValueValidator::get_allowed_subscription_types() ) ),
			]
		);
	}

	/**
	 * Retrieve the number of all payments.
	 *
	 * @since 1.8.2
	 *
	 * @param array $args Redefine query parameters by providing own arguments.
	 *
	 * @return int Number of payments or count of payments.
	 */
	public function count_all( $args = [] ) {

		// Retrieve the global database instance.
		global $wpdb;

		$query[] = 'SELECT SUM(count) AS total_count FROM (';
		$query[] = "SELECT COUNT(*) AS count FROM {$this->table_name} as p";

		/**
		 * Add parts to the query for count_all method before the WHERE clause.
		 *
		 * @since 1.8.2
		 *
		 * @param string $where Before the WHERE clause in DB query.
		 * @param array  $args  Query arguments.
		 *
		 * @return string
		 */
		$query[] = apply_filters( 'wpforms_db_payments_queries_count_all_query_before_where', '', $args );
		$query[] = 'WHERE 1=1';
		$query[] = $this->add_columns_where_conditions( $args );
		$query[] = $this->add_secondary_where_conditions( $args );

		/**
		 * Append custom query parts after the WHERE clause for the count_all method.
		 *
		 * This hook allows external code to extend the SQL query by adding custom conditions
		 * immediately after the WHERE clause.
		 *
		 * @since 1.8.4
		 *
		 * @param string $where After the WHERE clause in the database query.
		 * @param array  $args  Query arguments.
		 *
		 * @return string
		 */
		$query[] = apply_filters( 'wpforms_db_payments_queries_count_all_query_after_where', '', $args );

		// Close the subquery.
		$query[] = ') AS counts;';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		return (int) $wpdb->get_var( implode( ' ', $query ) );
	}

	/**
	 * Whether at least one payment exists with the given arguments.
	 *
	 * @since 1.8.4
	 *
	 * @param array $args Optionally, you can redefine query parameters by providing custom arguments.
	 *
	 * @return bool False if no results found.
	 */
	public function if_exists( $args = [] ) {

		// Retrieve the global database instance.
		global $wpdb;

		$query[] = "SELECT 1 FROM {$this->table_name}";

		/**
		 * Add parts to the query for if_exists method before the WHERE clause.
		 *
		 * @since 1.8.4
		 *
		 * @param string $where Before the WHERE clause in DB query.
		 * @param array  $args  Query arguments.
		 *
		 * @return string
		 */
		$query[] = apply_filters( 'wpforms_db_payments_queries_count_if_exists_before_where', '', $args );
		$query[] = 'WHERE 1=1';
		$query[] = $this->add_columns_where_conditions( $args );
		$query[] = $this->add_secondary_where_conditions( $args );

		/**
		 * Append custom query parts after the WHERE clause for the if_exists method.
		 *
		 * This hook allows external code to extend the SQL query by adding custom conditions
		 * immediately after the WHERE clause.
		 *
		 * @since 1.8.4
		 *
		 * @param string $where After the WHERE clause in the database query.
		 * @param array  $args  Query arguments.
		 *
		 * @return string
		 */
		$query[] = apply_filters( 'wpforms_db_payments_queries_count_if_exists_after_where', '', $args );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		return (bool) $wpdb->get_var( implode( ' ', $query ) );
	}

	/**
	 * Get next payment.
	 *
	 * @since 1.8.2
	 *
	 * @param int   $payment_id Payment ID.
	 * @param array $args       Where conditions.
	 *
	 * @return object|null Object from DB values or null.
	 */
	public function get_next( $payment_id, $args = [] ) {

		global $wpdb;

		if ( empty( $payment_id ) ) {
			return null;
		}

		$query[] = "SELECT * FROM {$this->table_name}";
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$query[] = $wpdb->prepare( "WHERE $this->primary_key > %d", $payment_id );
		$query[] = $this->add_secondary_where_conditions( $args );
		$query[] = "ORDER BY $this->primary_key LIMIT 1";

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->get_row( implode( ' ', $query ) );
	}

	/**
	 * Get previous payment.
	 *
	 * @since 1.8.2
	 *
	 * @param int   $payment_id Payment ID.
	 * @param array $args       Where conditions.
	 *
	 * @return object|null Object from DB values or null.
	 */
	public function get_prev( $payment_id, $args = [] ) {

		global $wpdb;

		if ( empty( $payment_id ) ) {
			return null;
		}

		$query[] = "SELECT * FROM $this->table_name";
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$query[] = $wpdb->prepare( "WHERE $this->primary_key < %d", $payment_id );
		$query[] = $this->add_secondary_where_conditions( $args );
		$query[] = "ORDER BY $this->primary_key DESC LIMIT 1";

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->get_row( implode( ' ', $query ) );
	}

	/**
	 * Get previous payments count.
	 *
	 * @since 1.8.2
	 *
	 * @param int   $payment_id Payment ID.
	 * @param array $args       Where conditions.
	 *
	 * @return int
	 */
	public function get_prev_count( $payment_id, $args = [] ) {

		global $wpdb;

		if ( empty( $payment_id ) ) {
			return 0;
		}

		$query[] = "SELECT COUNT( $this->primary_key ) FROM $this->table_name";
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$query[] = $wpdb->prepare( "WHERE $this->primary_key < %d", $payment_id );
		$query[] = $this->add_secondary_where_conditions( $args );
		$query[] = "ORDER BY $this->primary_key ASC";

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching
		return (int) $wpdb->get_var( implode( ' ', $query ) );
	}

	/**
	 * Get subscription payment history for the given subscription ID.
	 * This function returns an array of subscription payment object and renewal payments associated with the subscription.
	 *
	 * @global wpdb $wpdb Instantiation of the wpdb class.
	 *
	 * @since 1.8.4
	 *
	 * @param string $subscription_id Subscription ID.
	 * @param string $currency        Currency that the payment was made in.
	 *
	 * @return array Array of payment objects.
	 */
	public function get_subscription_payment_history( $subscription_id, $currency = '' ) {

		$subscription = null;
		$renewals     = [];

		// Bail early if the subscription ID is empty.
		if ( empty( $subscription_id ) ) {
			return [ $subscription, $renewals ];
		}

		// Get the currency, if not provided.
		if ( empty( $currency ) ) {
			$currency = wpforms_get_currency();
		}

		// Get the database instance.
		global $wpdb;

		// Get the general where clause.
		$where_clause = $this->add_secondary_where_conditions( [ 'currency' => $currency ] );

		// Construct the query using a prepared statement.
		// Execute the query and fetch the results.
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$this->table_name}
				WHERE subscription_id = %s AND (type = 'subscription' OR type = 'renewal') {$where_clause}
				ORDER BY type ASC, date_created_gmt DESC",
				$subscription_id
			)
		);
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		// Search for the subscription object in the "$results" array.
		foreach ( $results as $key => $result ) {
			if ( $result->type === 'subscription' ) {
				$subscription = $result;

				unset( $results[ $key ] );
				break; // Exit the loop after finding the subscription object.
			}
		}

		// Assign the remaining results to renewals.
		$renewals = $results;

		return [ $subscription, $renewals ];
	}

	/**
	 * Determine if given subscription has a renewal payment.
	 *
	 * @global wpdb $wpdb Instantiation of the wpdb class.
	 *
	 * @since 1.8.4
	 *
	 * @param string $subscription_id Subscription ID.
	 *
	 * @return bool True if the subscription has a renewal payment, false otherwise.
	 */
	public function if_subscription_has_renewal( $subscription_id ) {

		// Bail early if the subscription ID is empty.
		if ( empty( $subscription_id ) ) {
			return false;
		}

		// Get the database instance.
		global $wpdb;

		$query[] = "SELECT 1 FROM {$this->table_name} AS s";
		$query[] = 'WHERE s.subscription_id = %s';
		$query[] = "AND s.type = 'subscription'";
		$query[] = 'AND EXISTS(';
		$query[] = "SELECT 1 FROM {$this->table_name} AS r";
		$query[] = 'WHERE s.subscription_id = r.subscription_id';
		$query[] = "AND r.type = 'renewal'";
		$query[] = ')';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
		return (bool) $wpdb->get_var( $wpdb->prepare( implode( ' ', $query ), $subscription_id ) );
	}

	/**
	 * Get subscription payment for given subscription ID.
	 *
	 * @since 1.8.4
	 *
	 * @param string $subscription_id Subscription ID.
	 *
	 * @return object|null
	 */
	public function get_subscription( $subscription_id ) {

		global $wpdb;

		$query[] = "SELECT * FROM {$this->table_name}";
		$query[] = "WHERE subscription_id = %s AND type = 'subscription'";
		$query[] = 'ORDER BY id DESC LIMIT 1';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
		return $wpdb->get_row( $wpdb->prepare( implode( ' ', $query ), $subscription_id ) );
	}

	/**
	 * Get renewal payment for given invoice ID.
	 *
	 * @since 1.8.4
	 *
	 * @param string $invoice_id Invoice ID.
	 *
	 * @return object|null
	 */
	public function get_renewal_by_invoice_id( $invoice_id ) {

		global $wpdb;

		$meta_table_name = wpforms()->obj( 'payment_meta' )->table_name;

		$query[] = "SELECT p.* FROM {$this->table_name} as p";
		$query[] = "INNER JOIN {$meta_table_name} as pm ON p.id = pm.payment_id";
		$query[] = "WHERE pm.meta_key = 'invoice_id' AND pm.meta_value = %s";
		$query[] = 'ORDER BY p.id DESC LIMIT 1';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
		return $wpdb->get_row( $wpdb->prepare( implode( ' ', $query ), $invoice_id ) );
	}
}
