<?php

namespace WPForms\Admin\Payments\Views\Overview;

use WPForms\Admin\Payments\Payments;

/**
 * Generic functionality for interacting with the Coupons data.
 *
 * @since 1.8.4
 */
class Coupon {

	/**
	 * Initialize the Coupon class.
	 *
	 * @since 1.8.4
	 */
	public function init() {

		$this->hooks();
	}

	/**
	 * Attach hooks for filtering payments by coupon ID.
	 *
	 * @since 1.8.4
	 */
	private function hooks() {

		// This filter has been added for backward compatibility with older versions of the Coupons addon.
		add_filter( 'wpforms_admin_payments_views_overview_table_get_columns', [ $this, 'remove_legacy_coupon_column' ], 99, 1 );

		// Bail early if the current page is not the Payments page
		// or if no coupon ID is given in the URL.
		if ( ! self::is_coupon() ) {
			return;
		}

		add_filter( 'wpforms_db_payments_payment_get_payments_query_after_where', [ $this, 'filter_by_coupon_id' ], 10, 2 );
		add_filter( 'wpforms_db_payments_queries_count_all_query_after_where', [ $this, 'filter_by_coupon_id' ], 10, 2 );
		add_filter( 'wpforms_admin_payments_views_overview_filters_renewals_by_subscription_id_query_after_where', [ $this, 'filter_by_coupon_id' ], 10, 2 );
		add_filter( 'wpforms_admin_payments_views_overview_search_inner_join_query', [ $this, 'join_search_by_coupon_id' ], 10, 2 );
	}

	/**
	 * Remove the legacy coupon column from the Payments page.
	 *
	 * This function has been added for backward compatibility with older versions of the Coupons addon.
	 * The legacy coupon column is no longer used by the Coupons addon.
	 *
	 * @since 1.8.4
	 *
	 * @param array $columns List of columns to be displayed on the Payments page.
	 *
	 * @return array
	 */
	public function remove_legacy_coupon_column( $columns ) {

		// Bail early if the Coupons addon is not active.
		if ( ! $this->is_addon_active() ) {
			return $columns;
		}

		// Remove the legacy coupon column from the Payments page.
		unset( $columns['coupon_id'] );

		return $columns;
	}

	/**
	 * Retrieve payment entries based on a given coupon ID.
	 *
	 * @since 1.8.4
	 *
	 * @param string $after_where SQL query after the WHERE clause.
	 * @param array  $args        Query arguments.
	 *
	 * @return string
	 */
	public function filter_by_coupon_id( $after_where, $args ) {

		// Check if the query is for the Payments Overview table.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( empty( $args['table_query'] ) ) {
			return $after_where;
		}

		// Retrieve the coupon ID from the URL.
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated, WordPress.Security.NonceVerification.Recommended
		$coupon_id = absint( $_GET['coupon_id'] );

		global $wpdb;

		$table_name = wpforms()->obj( 'payment_meta' )->table_name;

		// Prepare and return the modified SQL query.
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return $wpdb->prepare(
			" AND EXISTS (
				SELECT 1 FROM {$table_name} AS pm_coupon
				WHERE pm_coupon.payment_id = p.id AND pm_coupon.meta_key = 'coupon_id' AND pm_coupon.meta_value = %d
			)",
			$coupon_id
		);
	}

	/**
	 * Further filter down the search results by coupon ID.
	 *
	 * @since 1.8.4
	 *
	 * @param string $query The SQL JOIN clause.
	 * @param int    $n     The number of the JOIN clause.
	 *
	 * @return string
	 */
	public function join_search_by_coupon_id( $query, $n ) {

		// Retrieve the coupon ID from the URL.
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated, WordPress.Security.NonceVerification.Recommended
		$coupon_id = absint( $_GET['coupon_id'] );

		// Retrieve the global database instance.
		global $wpdb;

		$n          = absint( $n );
		$table_name = wpforms()->obj( 'payment_meta' )->table_name;

		// Build the derived query using a prepared statement.
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$derived_query = $wpdb->prepare(
			"RIGHT JOIN (
				SELECT payment_id, meta_key, meta_value FROM {$table_name}
				WHERE meta_key = 'coupon_id' AND meta_value = %d
			) AS pm_coupon{$n} ON p.id = pm_coupon{$n}.payment_id",
			$coupon_id
		);
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		// Combine the original query and the derived query.
		return "$query $derived_query";
	}

	/**
	 * Determine if the overview page is being viewed, and coupon ID is given.
	 *
	 * @since 1.8.4
	 *
	 * @return bool
	 */
	public static function is_coupon() {

		// Check if the URL parameters contain a coupon ID and if the current page is the Payments page.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return ! empty( $_GET['coupon_id'] ) && ! empty( $_GET['page'] ) && $_GET['page'] === Payments::SLUG;
	}

	/**
	 * Determine whether the addon is activated.
	 *
	 * @since 1.8.4
	 *
	 * @return bool
	 */
	private function is_addon_active() {

		return function_exists( 'wpforms_coupons' );
	}
}
