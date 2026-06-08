<?php

namespace WPForms\Admin\Payments\Views\Overview;

/**
 * Class for extending SQL queries for filtering payments by multicheckbox fields.
 *
 * @since 1.8.4
 */
class Filters {

	/**
	 * Initialize the Filters class.
	 *
	 * @since 1.8.4
	 */
	public function init() {

		$this->hooks();
	}

	/**
	 * Attach hooks for filtering payments by multicheckbox fields.
	 *
	 * @since 1.8.4
	 */
	private function hooks() {

		add_filter( 'wpforms_db_payments_payment_get_payments_query_after_where', [ $this, 'add_renewals_by_subscription_id' ], 10, 2 );
		add_filter( 'wpforms_db_payments_queries_count_all_query_after_where', [ $this, 'count_renewals_by_subscription_id' ], 10, 2 );
		add_filter( 'wpforms_db_payments_queries_count_if_exists_after_where', [ $this, 'exists_renewals_by_subscription_id' ], 10, 2 );
	}

	/**
	 * Add renewals to the query.
	 *
	 * @since 1.8.4
	 *
	 * @param string $after_where SQL query.
	 * @param array  $args        Query arguments.
	 *
	 * @return string
	 */
	public function add_renewals_by_subscription_id( $after_where, $args ) {

		$query = $this->query_renewals_by_subscription_id( $args );

		if ( empty( $query ) ) {
			return $after_where; // Return early if $query is empty.
		}

		return "{$after_where} UNION {$query}";
	}

	/**
	 * Add renewals to the count query.
	 *
	 * @since 1.8.4
	 *
	 * @param string $after_where SQL query.
	 * @param array  $args        Query arguments.
	 *
	 * @return string
	 */
	public function count_renewals_by_subscription_id( $after_where, $args ) {

		$query = $this->query_renewals_by_subscription_id( $args, 'COUNT(*)' );

		if ( empty( $query ) ) {
			return $after_where; // Return early if $query is empty.
		}

		return "{$after_where} UNION ALL {$query}";
	}

	/**
	 * Add renewals to the exists query.
	 *
	 * @since 1.8.4
	 *
	 * @param string $after_where SQL query.
	 * @param array  $args        Query arguments.
	 *
	 * @return string
	 */
	public function exists_renewals_by_subscription_id( $after_where, $args ) {

		$query = $this->query_renewals_by_subscription_id( $args, '1' );

		if ( empty( $query ) ) {
			return $after_where; // Return early if $query is empty.
		}

		return "{$after_where} UNION ALL {$query}";
	}

	/**
	 * Query renewals by subscription ID.
	 *
	 * @since 1.8.4
	 *
	 * @param array  $args     Query arguments.
	 * @param string $selector SQL selector.
	 *
	 * @return string
	 */
	private function query_renewals_by_subscription_id( $args, $selector = 'p.*' ) {

		// Check if essential arguments are missing.
		if ( empty( $args['table_query'] ) || empty( $args['subscription_status'] ) ) {
			return '';
		}

		// Check if the query type is not 'renewal'.
		if ( ! empty( $args['type'] ) && ! in_array( 'renewal', explode( '|', $args['type'] ), true ) ) {
			return '';
		}

		$payment_handle        = wpforms()->obj( 'payment' );
		$subscription_statuses = explode( '|', $args['subscription_status'] );
		$placeholders          = wpforms_wpdb_prepare_in( $subscription_statuses );

		// This is needed to avoid the count_all method from adding the WHERE clause for the other types.
		$args['type'] = 'renewal';

		// Remove the subscription_status argument from the query.
		// The primary reason for this is that the subscription_status has to be checked in the subquery.
		unset( $args['subscription_status'] );

		// Prepare the query.
		$query[] = "SELECT {$selector} FROM {$payment_handle->table_name} as p";

		/**
		 * Append custom query parts before the WHERE clause.
		 *
		 * This hook allows external code to extend the SQL query by adding custom conditions
		 * immediately before the WHERE clause.
		 *
		 * @since 1.8.4
		 *
		 * @param string $where Before the WHERE clause in the database query.
		 * @param array  $args  Query arguments.
		 *
		 * @return string
		 */
		$query[] = apply_filters( 'wpforms_admin_payments_views_overview_filters_renewals_by_subscription_id_query_before_where', '', $args );

		// Add the WHERE clause.
		$query[] = 'WHERE 1=1';
		$query[] = $payment_handle->add_columns_where_conditions( $args );
		$query[] = $payment_handle->add_secondary_where_conditions( $args );
		$query[] = "AND EXISTS (
			SELECT 1 FROM {$payment_handle->table_name} as subquery_p
			WHERE subquery_p.subscription_id = p.subscription_id
			AND subquery_p.subscription_status IN ({$placeholders})
		)";

		/**
		 * Append custom query parts after the WHERE clause.
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
		$query[] = apply_filters( 'wpforms_admin_payments_views_overview_filters_renewals_by_subscription_id_query_after_where', '', $args );

		return implode( ' ', $query );
	}
}
