<?php

namespace WPForms\Admin\Payments\Views\Overview;

/**
 * Search related methods for Payment and Payment Meta.
 *
 * @since 1.8.2
 */
class Search {

	/**
	 * Credit card meta key.
	 *
	 * @since 1.8.2
	 *
	 * @var string
	 */
	const CREDIT_CARD = 'credit_card_last4';

	/**
	 * Customer email meta key.
	 *
	 * @since 1.8.2
	 *
	 * @var string
	 */
	const EMAIL = 'customer_email';

	/**
	 * Payment title column name.
	 *
	 * @since 1.8.2
	 *
	 * @var string
	 */
	const TITLE = 'title';

	/**
	 * Transaction ID column name.
	 *
	 * @since 1.8.2
	 *
	 * @var string
	 */
	const TRANSACTION_ID = 'transaction_id';

	/**
	 * Subscription ID column name.
	 *
	 * @since 1.8.2
	 *
	 * @var string
	 */
	const SUBSCRIPTION_ID = 'subscription_id';

	/**
	 * Any column indicator key.
	 *
	 * @since 1.8.2
	 *
	 * @var string
	 */
	const ANY = 'any';

	/**
	 * Equals mode.
	 *
	 * @since 1.8.2
	 *
	 * @var string
	 */
	const MODE_EQUALS = 'equals';

	/**
	 * Starts with mode.
	 *
	 * @since 1.8.2
	 *
	 * @var string
	 */
	const MODE_STARTS = 'starts';

	/**
	 * Contains mode.
	 *
	 * @since 1.8.2
	 *
	 * @var string
	 */
	const MODE_CONTAINS = 'contains';

	/**
	 * Init.
	 *
	 * @since 1.8.2
	 */
	public function init() {

		if ( ! self::is_search() ) {
			return;
		}

		$this->hooks();
	}

	/**
	 * Hooks.
	 *
	 * @since 1.8.2
	 */
	private function hooks() {

		add_filter( 'wpforms_db_payments_queries_count_all_query_before_where', [ $this, 'add_search_where_conditions' ], 10, 2 );
		add_filter( 'wpforms_db_payments_payment_get_payments_query_before_where', [ $this, 'add_search_where_conditions' ], 10, 2 );
		add_filter( 'wpforms_admin_payments_views_overview_filters_renewals_by_subscription_id_query_before_where', [ $this, 'add_search_where_conditions' ], 10, 2 );
	}

	/**
	 * Check if search query.
	 *
	 * @since 1.8.2
	 *
	 * @return bool
	 */
	public static function is_search() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return ! empty( $_GET['s'] );
	}

	/**
	 * Add search where conditions.
	 *
	 * @since 1.8.2
	 *
	 * @param string $where Query where string.
	 * @param array  $args  Query arguments.
	 *
	 * @return string
	 */
	public function add_search_where_conditions( $where, $args ) {

		if ( empty( $args['search'] ) ) {
			return $where;
		}

		if ( ! empty( $args['search_conditions']['search_mode'] ) && $args['search_conditions']['search_mode'] === self::MODE_CONTAINS ) {
			$to_search = explode( ' ', $args['search'] );
		} else {
			$to_search = [ $args['search'] ];
		}

		$query = [];

		foreach ( $to_search as $counter => $single ) {
			$query[] = $this->add_single_search_condition( $single, $args, $counter );
		}

		return implode( ' ', $query );
	}

	/**
	 * Add single search condition.
	 *
	 * @since 1.8.2
	 *
	 * @param string $word Single searched part.
	 * @param array  $args Query arguments.
	 * @param int    $n    Word counter.
	 *
	 * @return string
	 */
	private function add_single_search_condition( $word, $args, $n ) {

		if ( empty( $word ) ) {
			return '';
		}

		$mode  = $this->prepare_mode( $args );
		$where = $this->prepare_where( $args );

		list( $operator, $word ) = $this->prepare_operator_and_word( $word, $mode );

		$column = $this->prepare_column( $where );

		if ( in_array( $column, [ self::EMAIL, self::CREDIT_CARD ], true ) ) {
			return $this->select_from_meta_table( $column, $operator, $word, $n );
		}

		if ( $column === self::ANY ) {
			return $this->select_from_any( $operator, $word, $n );
		}

		$payment_table = wpforms()->obj( 'payment' )->table_name;

		$query = "SELECT id FROM {$payment_table} 
					WHERE {$payment_table}.{$column} {$operator} {$word}";

		return $this->wrap_in_inner_join( $query, $n );
	}

	/**
	 * Prepare search mode part.
	 *
	 * @since 1.8.2
	 *
	 * @param array $args Query arguments.
	 *
	 * @return string Mode part for search.
	 */
	private function prepare_mode( $args ) {

		return isset( $args['search_conditions']['search_mode'] ) ? $args['search_conditions']['search_mode'] : self::MODE_EQUALS;
	}

	/**
	 * Prepare search where part.
	 *
	 * @since 1.8.2
	 *
	 * @param array $args Query arguments.
	 *
	 * @return string Where part for search.
	 */
	private function prepare_where( $args ) {

		return isset( $args['search_conditions']['search_where'] ) ? $args['search_conditions']['search_where'] : self::TITLE;
	}

	/**
	 * Prepare operator and word parts.
	 *
	 * @since 1.8.2
	 *
	 * @param string $word Single word.
	 * @param string $mode Search mode.
	 *
	 * @return array Array with operator and word parts for search.
	 */
	private function prepare_operator_and_word( $word, $mode ) {

		global $wpdb;

		if ( $mode === self::MODE_CONTAINS ) {
			return [
				'LIKE',
				$wpdb->prepare( '%s', '%' . $wpdb->esc_like( $word ) . '%' ),
			];
		}

		if ( $mode === self::MODE_STARTS ) {
			return [
				'LIKE',
				$wpdb->prepare( '%s', $wpdb->esc_like( $word ) . '%' ),
			];
		}

		return [
			'=',
			$wpdb->prepare( '%s', $word ),
		];
	}

	/**
	 * Prepare column to search in.
	 *
	 * @since 1.8.2
	 *
	 * @param string $where Search where.
	 *
	 * @return string Column to search in.
	 */
	private function prepare_column( $where ) {

		if ( in_array( $where, [ self::TRANSACTION_ID, self::SUBSCRIPTION_ID, self::EMAIL, self::CREDIT_CARD, self::ANY ], true ) ) {
			return $where;
		}

		return self::TITLE;
	}

	/**
	 * Prepare select part to select from payments meta table.
	 *
	 * @since 1.8.2
	 *
	 * @param string $meta_key Meta key.
	 * @param string $operator Comparison operator.
	 * @param string $word     Word to search.
	 * @param int    $n        Word count.
	 *
	 * @return string
	 * @noinspection CallableParameterUseCaseInTypeContextInspection
	 */
	private function select_from_meta_table( $meta_key, $operator, $word, $n ) {

		global $wpdb;

		$payment_table = wpforms()->obj( 'payment' )->table_name;
		$meta_table    = wpforms()->obj( 'payment_meta' )->table_name;
		$meta_key      = $wpdb->prepare( '%s', $meta_key );

		$query = "SELECT id FROM $payment_table
				WHERE id IN ( 
					SELECT DISTINCT payment_id FROM $meta_table 
					WHERE meta_value $operator $word 
					AND meta_key = $meta_key 
				)";

		return $this->wrap_in_inner_join( $query, $n );
	}

	/**
	 * Prepare select part to select from places from both tables.
	 *
	 * @since 1.8.2
	 *
	 * @param string $operator Comparison operator.
	 * @param string $word     Word to search.
	 * @param int    $n        Word count.
	 *
	 * @return string
	 */
	private function select_from_any( $operator, $word, $n ) {

		$payment_table = wpforms()->obj( 'payment' )->table_name;
		$meta_table    = wpforms()->obj( 'payment_meta' )->table_name;

		$query = sprintf(
			"SELECT id FROM {$payment_table}
				WHERE (
					{$payment_table}.%s {$operator} {$word}
					OR {$payment_table}.%s {$operator} {$word}
					OR {$payment_table}.%s {$operator} {$word}
					OR id IN (
						SELECT DISTINCT payment_id
						FROM {$meta_table}
						WHERE meta_value {$operator} {$word}
						AND meta_key IN ( '%s', '%s' )
					)
				)",
			self::TITLE,
			self::TRANSACTION_ID,
			self::SUBSCRIPTION_ID,
			self::CREDIT_CARD,
			self::EMAIL
		);

		return $this->wrap_in_inner_join( $query, $n );
	}

	/**
	 * Wrap the query in INNER JOIN part.
	 *
	 * @since 1.8.2
	 *
	 * @param string $query Partial query.
	 * @param int    $n     Word count.
	 *
	 * @return string
	 */
	private function wrap_in_inner_join( $query, $n ) {

		/**
		 * Filter to modify the inner join query.
		 *
		 * @since 1.8.4
		 *
		 * @param string $query Partial query.
		 * @param int    $n     The number of the JOIN clause.
		 */
		return apply_filters(
			'wpforms_admin_payments_views_overview_search_inner_join_query',
			sprintf( 'INNER JOIN ( %1$s ) AS p%2$d ON p.id = p%2$d.id', $query, $n ),
			$n
		);
	}
}
