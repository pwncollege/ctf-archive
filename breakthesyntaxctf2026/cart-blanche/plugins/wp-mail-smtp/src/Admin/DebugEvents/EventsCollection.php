<?php

namespace WPMailSMTP\Admin\DebugEvents;

use WPMailSMTP\WP;

/**
 * Debug Events Collection.
 *
 * @since 3.0.0
 */
class EventsCollection implements \Countable, \Iterator {

	/**
	 * Default number of log entries per page.
	 *
	 * @since 3.0.0
	 *
	 * @var int
	 */
	const PER_PAGE = 10;

	/**
	 * Number of log entries per page.
	 *
	 * @since 3.0.0
	 *
	 * @var int
	 */
	public static $per_page;

	/**
	 * List of all Event instances.
	 *
	 * @since 3.0.0
	 *
	 * @var array
	 */
	private $list = [];

	/**
	 * List of current collection instance parameters.
	 *
	 * @since 3.0.0
	 *
	 * @var array
	 */
	private $params;

	/**
	 * Used for \Iterator when iterating through Queue in loops.
	 *
	 * @since 3.0.0
	 *
	 * @var int
	 */
	private $iterator_position = 0;

	/**
	 * Collection constructor.
	 * $events = new EventsCollection( [ 'type' => 0 ] );
	 *
	 * @since 3.0.0
	 *
	 * @param array $params The events collection parameters.
	 */
	public function __construct( array $params = [] ) {

		$this->set_per_page();
		$this->params = $this->process_params( $params );
	}

	/**
	 * Set the per page attribute to the screen options value.
	 *
	 * @since 3.0.0
	 */
	protected function set_per_page() {

		$per_page = (int) get_user_meta(
			get_current_user_id(),
			'wp_mail_smtp_debug_events_per_page',
			true
		);

		if ( $per_page < 1 ) {
			$per_page = self::PER_PAGE;
		}

		self::$per_page = $per_page;
	}

	/**
	 * Verify, sanitize, and populate with default values
	 * all the passed parameters, which participate in DB queries.
	 *
	 * @since 3.0.0
	 *
	 * @param array $params The events collection parameters.
	 *
	 * @return array
	 */
	public function process_params( $params ) { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.MaxExceeded

		$params    = (array) $params;
		$processed = [];

		/*
		 * WHERE.
		 */
		// Single ID.
		if ( ! empty( $params['id'] ) ) {
			$processed['id'] = (int) $params['id'];
		}

		// Multiple IDs.
		if (
			! empty( $params['ids'] ) &&
			is_array( $params['ids'] )
		) {
			$processed['ids'] = array_unique( array_filter( array_map( 'intval', array_values( $params['ids'] ) ) ) );
		}

		// Type.
		if (
			isset( $params['type'] ) &&
			in_array( $params['type'], array_keys( Event::get_types() ), true )
		) {
			$processed['type'] = (int) $params['type'];
		}

		// Search.
		if ( ! empty( $params['search'] ) ) {
			$processed['search'] = sanitize_text_field( $params['search'] );
		}

		/*
		 * LIMIT.
		 */
		if ( ! empty( $params['offset'] ) ) {
			$processed['offset'] = (int) $params['offset'];
		}

		if ( ! empty( $params['per_page'] ) ) {
			$processed['per_page'] = (int) $params['per_page'];
		}

		/*
		 * Sent date.
		 */
		if ( ! empty( $params['date'] ) ) {
			if ( is_string( $params['date'] ) ) {
				$params['date'] = array_fill( 0, 2, $params['date'] );
			} elseif ( is_array( $params['date'] ) && count( $params['date'] ) === 1 ) {
				$params['date'] = array_fill( 0, 2, $params['date'][0] );
			}

			// We pass array and treat it as a range from:to.
			if ( is_array( $params['date'] ) && count( $params['date'] ) === 2 ) {
				$date_start = WP::get_day_period_date( 'start_of_day', strtotime( $params['date'][0] ), 'Y-m-d H:i:s', true );
				$date_end   = WP::get_day_period_date( 'end_of_day', strtotime( $params['date'][1] ), 'Y-m-d H:i:s', true );

				if ( ! empty( $date_start ) && ! empty( $date_end ) ) {
					$processed['date'] = [ $date_start, $date_end ];
				}
			}
		}

		// Merge missing values with defaults.
		return wp_parse_args(
			$processed,
			$this->get_default_params()
		);
	}

	/**
	 * Get the list of default params for a usual query.
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	protected function get_default_params() {

		return [
			'offset'   => 0,
			'per_page' => self::$per_page,
			'order'    => 'DESC',
			'orderby'  => 'id',
			'search'   => '',
		];
	}

	/**
	 * Get the SQL-ready string of WHERE part for a query.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	private function build_where() { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		global $wpdb;

		$where = [ '1=1' ];

		// Shortcut single ID or multiple IDs.
		if ( ! empty( $this->params['id'] ) || ! empty( $this->params['ids'] ) ) {
			if ( ! empty( $this->params['id'] ) ) {
				$where[] = $wpdb->prepare( 'id = %d', $this->params['id'] );
			} elseif ( ! empty( $this->params['ids'] ) ) {
				$where[] = 'id IN (' . implode( ',', $this->params['ids'] ) . ')';
			}

			// When some ID(s) defined - we should ignore all other possible filtering options.
			return implode( ' AND ', $where );
		}

		// Type.
		if ( isset( $this->params['type'] ) ) {
			$where[] = $wpdb->prepare( 'event_type = %d', $this->params['type'] );
		}

		// Search.
		if ( ! empty( $this->params['search'] ) ) {
			$where[] = '(' .
				$wpdb->prepare(
					'content LIKE %s',
					'%' . $wpdb->esc_like( $this->params['search'] ) . '%'
				)
				. ' OR ' .
				$wpdb->prepare(
					'initiator LIKE %s',
					'%' . $wpdb->esc_like( $this->params['search'] ) . '%'
				)
				. ')';
		}

		// Sent date.
		if (
			! empty( $this->params['date'] ) &&
			is_array( $this->params['date'] ) &&
			count( $this->params['date'] ) === 2
		) {
			$where[] = $wpdb->prepare(
				'( created_at >= %s AND created_at <= %s )',
				$this->params['date'][0],
				$this->params['date'][1]
			);
		}

		return implode( ' AND ', $where );
	}

	/**
	 * Get the SQL-ready string of ORDER part for a query.
	 * Order is always in the params, as per our defaults.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	private function build_order() {

		return 'ORDER BY ' . $this->params['orderby'] . ' ' . $this->params['order'];
	}

	/**
	 * Get the SQL-ready string of LIMIT part for a query.
	 * Limit is always in the params, as per our defaults.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	private function build_limit() {

		return 'LIMIT ' . $this->params['offset'] . ', ' . $this->params['per_page'];
	}

	/**
	 * Count the number of DB records according to filters.
	 * Do not retrieve actual records.
	 *
	 * @since 3.0.0
	 *
	 * @return int
	 */
	public function get_count() {

		$table = DebugEvents::get_table_name();

		$where = $this->build_where();

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return (int) WP::wpdb()->get_var(
			"SELECT COUNT(id) FROM $table
			WHERE {$where}"
		);
		// phpcs:enable
	}

	/**
	 * Get the list of DB records.
	 * You can either use array returned there OR iterate over the whole object,
	 * as it implements Iterator interface.
	 *
	 * @since 3.0.0
	 *
	 * @return EventsCollection
	 */
	public function get() {

		$table = DebugEvents::get_table_name();

		$where = $this->build_where();
		$limit = $this->build_limit();
		$order = $this->build_order();

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$data = WP::wpdb()->get_results(
			"SELECT * FROM $table
			WHERE {$where}
			{$order}
			{$limit}"
		);
		// phpcs:enable

		if ( ! empty( $data ) ) {
			// As we got raw data we need to convert each row to Event.
			foreach ( $data as $row ) {
				$this->list[] = new Event( $row );
			}
		}

		return $this;
	}

	/*********************************************************************************************
	 * ****************************** \Counter interface method. *********************************
	 *********************************************************************************************/

	/**
	 * Count number of Record in a Queue.
	 *
	 * @since 3.0.0
	 *
	 * @return int
	 */
	#[\ReturnTypeWillChange]
	public function count() {

		return count( $this->list );
	}

	/*********************************************************************************************
	 * ****************************** \Iterator interface methods. *******************************
	 *********************************************************************************************/

	/**
	 * Rewind the Iterator to the first element.
	 *
	 * @since 3.0.0
	 */
	#[\ReturnTypeWillChange]
	public function rewind() {

		$this->iterator_position = 0;
	}

	/**
	 * Return the current element.
	 *
	 * @since 3.0.0
	 *
	 * @return Event|null Return null when no items in collection.
	 */
	#[\ReturnTypeWillChange]
	public function current() {

		return $this->valid() ? $this->list[ $this->iterator_position ] : null;
	}

	/**
	 * Return the key of the current element.
	 *
	 * @since 3.0.0
	 *
	 * @return int
	 */
	#[\ReturnTypeWillChange]
	public function key() {

		return $this->iterator_position;
	}

	/**
	 * Move forward to next element.
	 *
	 * @since 3.0.0
	 */
	#[\ReturnTypeWillChange]
	public function next() {

		++ $this->iterator_position;
	}

	/**
	 * Checks if current position is valid.
	 *
	 * @since 3.0.0
	 *
	 * @return bool
	 */
	#[\ReturnTypeWillChange]
	public function valid() {

		return isset( $this->list[ $this->iterator_position ] );
	}

}
