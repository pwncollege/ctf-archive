<?php

namespace WPForms\Admin\Payments\Views\Overview;

use DateTimeImmutable;
// phpcs:ignore WPForms.PHP.UseStatement.UnusedUseStatement
use wpdb;
use WPForms\Db\Payments\ValueValidator;
use WPForms\Admin\Helpers\Chart as ChartHelper;
use WPForms\Admin\Helpers\Datepicker;

/**
 * "Payments" overview page inside the admin, which lists all payments.
 * This page will be accessible via "WPForms" → "Payments".
 *
 * When requested data is sent via Ajax, this class is responsible for exchanging datasets.
 *
 * @since 1.8.2
 */
class Ajax {

	/**
	 * Database table name.
	 *
	 * @since 1.8.2
	 *
	 * @var string
	 */
	private $table_name;

	/**
	 * Temporary storage for the stat cards.
	 *
	 * @since 1.8.4
	 *
	 * @var array
	 */
	private $stat_cards;

	/**
	 * Hooks.
	 *
	 * @since 1.8.2
	 */
	public function hooks() {

		add_action( 'wp_ajax_wpforms_payments_overview_refresh_chart_dataset_data', [ $this, 'get_chart_dataset_data' ] );
		add_action( 'wp_ajax_wpforms_payments_overview_save_chart_preference_settings', [ $this, 'save_chart_preference_settings' ] );
		add_filter( 'wpforms_db_payments_payment_add_secondary_where_conditions_args', [ $this, 'modify_secondary_where_conditions_args' ] );
	}

	/**
	 * Generate and return the data for our dataset data.
	 *
	 * @since 1.8.2
	 */
	public function get_chart_dataset_data() {

		// Run a security check.
		check_ajax_referer( 'wpforms_payments_overview_nonce' );

		// Check for permissions.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( esc_html__( 'You are not allowed to perform this action.', 'wpforms-lite' ) );
		}

		$report   = ! empty( $_POST['report'] ) ? sanitize_text_field( wp_unslash( $_POST['report'] ) ) : null;
		$dates    = ! empty( $_POST['dates'] ) ? sanitize_text_field( wp_unslash( $_POST['dates'] ) ) : null;
		$fallback = [
			'data'    => [],
			'reports' => [],
		];

		// If the report type or dates for the timespan are missing, leave early.
		if ( ! $report || ! $dates ) {
			wp_send_json_error( $fallback );
		}

		// Validates and creates date objects of given timespan string.
		$timespans = Datepicker::process_string_timespan( $dates );

		// If the timespan is not validated, leave early.
		if ( ! $timespans ) {
			wp_send_json_error( $fallback );
		}

		// Extract start and end timespans in local (site) and UTC timezones.
		list( $start_date, $end_date, $utc_start_date, $utc_end_date ) = $timespans;

		// Payment table name.
		$this->table_name = wpforms()->obj( 'payment' )->table_name;

		// Get the stat cards.
		$this->stat_cards = Chart::stat_cards();

		// Get the payments in the given timespan.
		$results = $this->get_payments_in_timespan( $utc_start_date, $utc_end_date, $report );

		// In case the database's results were empty, leave early.
		if ( $report === Chart::ACTIVE_REPORT && empty( $results ) ) {
			wp_send_json_error( $fallback );
		}

		// Process the results and return the data.
		// The first element of the array is the total number of entries, the second is the data.
		list( , $data ) = ChartHelper::process_chart_dataset_data( $results, $start_date, $end_date );

		// Sends the JSON response back to the Ajax request, indicating success.
		wp_send_json_success(
			[
				'data'    => $data,
				'reports' => $this->get_payments_summary_in_timespan( $start_date, $end_date ),
			]
		);
	}

	/**
	 * Save the user's preferred graph style and color scheme.
	 *
	 * @since 1.8.2
	 */
	public function save_chart_preference_settings() {

		// Run a security check.
		check_ajax_referer( 'wpforms_payments_overview_nonce' );

		// Check for permissions.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( esc_html__( 'You are not allowed to perform this action.', 'wpforms-lite' ) );
		}

		$graph_style = isset( $_POST['graphStyle'] ) ? absint( $_POST['graphStyle'] ) : 2; // Line.

		update_user_meta( get_current_user_id(), 'wpforms_dash_widget_graph_style', $graph_style );

		exit();
	}

	/**
	 * Retrieve and create payment entries from the database within the specified time frame (timespan).
	 *
	 * @global wpdb $wpdb Instantiation of the wpdb class.
	 *
	 * @since 1.8.2
	 *
	 * @param DateTimeImmutable $start_date Start date for the timespan preferably in UTC.
	 * @param DateTimeImmutable $end_date   End date for the timespan preferably in UTC.
	 * @param string            $report     Payment summary stat card name. i.e. "total_payments".
	 *
	 * @return array
	 */
	private function get_payments_in_timespan( $start_date, $end_date, $report ) {

		// Ensure given timespan dates are in UTC timezone.
		list( $utc_start_date, $utc_end_date ) = Datepicker::process_timespan_mysql( [ $start_date, $end_date ] );

		// If the time period is not a date object, leave early.
		if ( ! ( $start_date instanceof DateTimeImmutable ) || ! ( $end_date instanceof DateTimeImmutable ) ) {
			return [];
		}

		// Get the database instance.
		global $wpdb;

		// SELECT clause to construct the SQL statement.
		$column_clause = $this->get_stats_column_clause( $report );

		// JOIN clause to construct the SQL statement for metadata.
		$join_by_meta = $this->add_join_by_meta( $report );

		// WHERE clauses for items query statement.
		$where_clause = $this->get_stats_where_clause( $report );

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT date_created_gmt AS day, $column_clause AS count FROM $this->table_name AS p {$join_by_meta}
					WHERE 1=1 $where_clause AND date_created_gmt BETWEEN %s AND %s GROUP BY day ORDER BY day ASC",
				[
					$utc_start_date->format( Datepicker::DATETIME_FORMAT ),
					$utc_end_date->format( Datepicker::DATETIME_FORMAT ),
				]
			),
			ARRAY_A
		);
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	}

	/**
	 * Fetch and generate payment summary reports from the database.
	 *
	 * @global wpdb $wpdb Instantiation of the wpdb class.
	 *
	 * @since 1.8.2
	 *
	 * @param DateTimeImmutable $start_date Start date for the timespan preferably in UTC.
	 * @param DateTimeImmutable $end_date   End date for the timespan preferably in UTC.
	 *
	 * @return array
	 */
	private function get_payments_summary_in_timespan( $start_date, $end_date ) {

		// Ensure given timespan dates are in UTC timezone.
		list( $utc_start_date, $utc_end_date ) = Datepicker::process_timespan_mysql( [ $start_date, $end_date ] );

		// If the time period is not a date object, leave early.
		if ( ! ( $start_date instanceof DateTimeImmutable ) || ! ( $end_date instanceof DateTimeImmutable ) ) {
			return [];
		}

		// Get the database instance.
		global $wpdb;

		list( $clause, $query ) = $this->prepare_sql_summary_reports( $utc_start_date, $utc_end_date );

		$group_by = Chart::ACTIVE_REPORT;

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$results  = $wpdb->get_row(
			"SELECT $clause FROM (SELECT $query) AS results GROUP BY $group_by",
			ARRAY_A
		);
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		return $this->maybe_format_amounts( $results );
	}

	/**
	 * Generate SQL statements to create a derived (virtual) table for the report stat cards.
	 *
	 * @global wpdb $wpdb Instantiation of the wpdb class.
	 *
	 * @since 1.8.2
	 *
	 * @param DateTimeImmutable $start_date Start date for the timespan.
	 * @param DateTimeImmutable $end_date   End date for the timespan.
	 *
	 * @return array
	 */
	private function prepare_sql_summary_reports( $start_date, $end_date ) {

		// In case there are no report stat cards defined, leave early.
		if ( empty( $this->stat_cards ) ) {
			return [ '', '' ];
		}

		global $wpdb;

		$clause = []; // SELECT clause.
		$query  = []; // Query statement for the derived table.

		// Validates and creates date objects for the previous time spans.
		$prev_timespans = Datepicker::get_prev_timespan_dates( $start_date, $end_date );

		// If the timespan is not validated, leave early.
		if ( ! $prev_timespans ) {
			return [ '', '' ];
		}

		list( $prev_start_date, $prev_end_date ) = $prev_timespans;

		// Get the default number of decimals for the payment currency.
		$current_currency  = wpforms_get_currency();
		$currency_decimals = wpforms_get_currency_decimals( $current_currency );

		// Loop through the reports and create the SQL statements.
		foreach ( $this->stat_cards as $report => $attributes ) {

			// Skip stat card, if it's not supposed to be displayed or disabled (upsell).
			if (
				( isset( $attributes['condition'] ) && ! $attributes['condition'] )
				|| in_array( 'disabled', $attributes['button_classes'], true )
			) {
				continue;
			}

			// Determine whether the number of rows has to be counted.
			$has_count = isset( $attributes['has_count'] ) && $attributes['has_count'];

			// SELECT clause to construct the SQL statement.
			$column_clause = $this->get_stats_column_clause( $report, $has_count );

			// JOIN clause to construct the SQL statement for metadata.
			$join_by_meta = $this->add_join_by_meta( $report );

			// WHERE clauses for items query statement.
			$where_clause = $this->get_stats_where_clause( $report );

			// Get the current and previous values for the report.
			$current_value = "TRUNCATE($report,$currency_decimals)";
			$prev_value    = "TRUNCATE({$report}_prev,$currency_decimals)";

			// Add the current and previous reports to the SELECT clause.
			$clause[] = $report;
			$clause[] = "ROUND( ( ( $current_value - $prev_value ) / $current_value ) * 100 ) AS {$report}_delta";

			// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.MissingReplacements
			$query[] = $wpdb->prepare(
				"(
					SELECT $column_clause
					FROM $this->table_name AS p
					{$join_by_meta}
					WHERE 1=1 $where_clause AND date_created_gmt BETWEEN %s AND %s
				) AS $report,
				(
					SELECT $column_clause
					FROM $this->table_name AS p
					{$join_by_meta}
					WHERE 1=1 $where_clause AND date_created_gmt BETWEEN %s AND %s
				) AS {$report}_prev",
				[
					$start_date->format( Datepicker::DATETIME_FORMAT ),
					$end_date->format( Datepicker::DATETIME_FORMAT ),
					$prev_start_date->format( Datepicker::DATETIME_FORMAT ),
					$prev_end_date->format( Datepicker::DATETIME_FORMAT ),
				]
			);
			// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.MissingReplacements
		}

		return [
			implode( ',', $clause ),
			implode( ',', $query ),
		];
	}

	/**
	 * Helper method to build where clause used to construct the SQL statement.
	 *
	 * @since 1.8.2
	 *
	 * @param string $report Payment summary stat card name. i.e. "total_payments".
	 *
	 * @return string
	 */
	private function get_stats_where_clause( $report ) {

		// Get the default WHERE clause from the Payments database class.
		$clause = wpforms()->obj( 'payment' )->add_secondary_where_conditions();

		// If the report doesn't have any additional funnel arguments, leave early.
		if ( ! isset( $this->stat_cards[ $report ]['funnel'] ) ) {
			return $clause;
		}

		// Get the where arguments for the report.
		$where_args = (array) $this->stat_cards[ $report ]['funnel'];

		// If the where arguments are empty, leave early.
		if ( empty( $where_args ) ) {
			return $clause;
		}

		return $this->prepare_sql_where_clause( $where_args, $clause );
	}

	/**
	 * Prepare SQL where clause for the given funnel arguments.
	 *
	 * @since 1.8.4
	 *
	 * @param array  $where_args Array of where arguments.
	 * @param string $clause     SQL where clause.
	 *
	 * @return string
	 */
	private function prepare_sql_where_clause( $where_args, $clause ) {

		$allowed_funnels = [ 'in', 'not_in' ];

		$filtered_where_args = array_filter(
			$where_args,
			static function ( $key ) use ( $allowed_funnels ) {

				return in_array( $key, $allowed_funnels, true );
			},
			ARRAY_FILTER_USE_KEY
		);

		// Leave early if the filtered where arguments are empty.
		if ( empty( $filtered_where_args ) ) {
			return $clause;
		}

		// Loop through the where arguments and add them to the clause.
		foreach ( $filtered_where_args as $operator => $columns ) {
			foreach ( $columns as $column => $values ) {
				if ( ! is_array( $values ) ) {
					continue;
				}

				// Skip if the value is not valid.
				$valid_values = array_filter(
					$values,
					static function ( $item ) use ( $column ) {

						return ValueValidator::is_valid( $item, $column );
					}
				);

				$placeholders = wpforms_wpdb_prepare_in( $valid_values );
				$clause      .= $operator === 'in' ? " AND {$column} IN ({$placeholders})" : " AND {$column} NOT IN ({$placeholders})";
			}
		}

		return $clause;
	}

	/**
	 * Helper method to build column clause used to construct the SQL statement.
	 *
	 * @since 1.8.2
	 *
	 * @param string $report     Stats card chart type (name). i.e. "total_payments".
	 * @param bool   $with_count Whether to concatenate the count to the clause.
	 *
	 * @return string
	 */
	private function get_stats_column_clause( $report, $with_count = false ) {

		// Default column clause.
		// Count the number of rows as fast as possible.
		$default = 'COUNT(*)';

		// If the report has a meta key, then count the number of unique rows for the meta table.
		if ( isset( $this->stat_cards[ $report ]['meta_key'] ) ) {
			$default = 'COUNT(pm.id)';
		}

		/**
		 * Filters the column clauses for the stat cards.
		 *
		 * @since 1.8.2
		 *
		 * @param array $clauses Array of column clauses.
		 */
		$clauses = (array) apply_filters(
			'wpforms_admin_payments_views_overview_ajax_stats_column_clauses',
			[
				'total_payments'             => "FORMAT({$default},0)",
				'total_sales'                => 'IFNULL(SUM(total_amount),0)',
				'total_refunded'             => 'IFNULL(SUM(pm.meta_value),0)',
				'total_subscription'         => 'IFNULL(SUM(total_amount),0)',
				'total_renewal_subscription' => 'IFNULL(SUM(total_amount),0)',
				'total_coupons'              => "FORMAT({$default},0)",
			]
		);

		$clause = isset( $clauses[ $report ] ) ? $clauses[ $report ] : $default;

		// Several stat cards might include the count of payment records.
		if ( $with_count ) {
			$clause = "CONCAT({$clause}, ' (', {$default}, ')')";
		}

		return $clause;
	}

	/**
	 * Add join by meta table.
	 *
	 * @since 1.8.4
	 *
	 * @param string $report Stats card chart type (name). i.e. "total_payments".
	 *
	 * @return string
	 */
	private function add_join_by_meta( $report ) {

		// Leave early if the meta key is empty.
		if ( ! isset( $this->stat_cards[ $report ]['meta_key'] ) ) {
			return '';
		}

		// Retrieve the global database instance.
		global $wpdb;

		// Retrieve the meta table name.
		$meta_table_name = wpforms()->obj( 'payment_meta' )->table_name;

		return $wpdb->prepare(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			"LEFT JOIN {$meta_table_name} AS pm ON p.id = pm.payment_id AND pm.meta_key = %s",
			$this->stat_cards[ $report ]['meta_key']
		);
	}

	/**
	 * Modify arguments of secondary where clauses.
	 *
	 * @since 1.8.2
	 *
	 * @param array $args Query arguments.
	 *
	 * @return array
	 */
	public function modify_secondary_where_conditions_args( $args ) {

		// Set a current mode.
		if ( ! isset( $args['mode'] ) ) {
			$args['mode'] = Page::get_mode();
		}

		return $args;
	}

	/**
	 * Maybe format the amounts for the given stat cards.
	 *
	 * @since 1.8.4
	 *
	 * @param array $results Query results.
	 *
	 * @return array
	 */
	private function maybe_format_amounts( $results ) {

		// If the input is empty, leave early.
		if ( empty( $results ) ) {
			return [];
		}

		foreach ( $results as $key => $value ) {
			// If the given stat card doesn't have a button class, leave early.
			// If the given stat card doesn't have a button class of "is-amount," leave early.
			if ( ! isset( $this->stat_cards[ $key ]['button_classes'] ) || ! in_array( 'is-amount', $this->stat_cards[ $key ]['button_classes'], true ) ) {
				continue;
			}

			// Split the input by space to look for the count.
			$input_arr = (array) explode( ' ', $value );

			// If the given stat card doesn't have a count, leave early.
			if ( empty( $this->stat_cards[ $key ]['has_count'] ) || ! isset( $input_arr[1] ) ) {
				// Format the given amount and split the input by space.
				$results[ $key ] = wpforms_format_amount( $value, true );

				continue;
			}

			// The fields are stored as a `decimal` in the DB, and appears here as the string.
			// But all strings values, passed to wpforms_format_amount() are sanitized.
			// There is no need to sanitize it, as it is already a regular numeric string.
			$amount = wpforms_format_amount( (float) ( $input_arr[0] ?? $value ), true );

			// Format the amount with the concatenation of count in parentheses.
			// Example: 2185.52000000 (79).
			$results[ $key ] = sprintf(
				'%s <span>%s</span>',
				esc_html( $amount ),
				esc_html( $input_arr[1] ) // 1: Would be count of the records.
			);
		}

		return $results;
	}
}
