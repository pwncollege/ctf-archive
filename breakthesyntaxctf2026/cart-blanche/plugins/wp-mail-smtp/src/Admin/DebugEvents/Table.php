<?php

namespace WPMailSMTP\Admin\DebugEvents;

use WPMailSMTP\Helpers\Helpers;

if ( ! class_exists( 'WP_List_Table', false ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Class Table that displays the list of debug events.
 *
 * @since 3.0.0
 */
class Table extends \WP_List_Table {

	/**
	 * Number of debug events by different types.
	 *
	 * @since 3.0.0
	 *
	 * @var array
	 */
	public $counts;

	/**
	 * Set up a constructor that references the parent constructor.
	 * Using the parent reference to set some default configs.
	 *
	 * @since 3.0.0
	 */
	public function __construct() {

		// Set parent defaults.
		parent::__construct(
			[
				'singular' => 'event',
				'plural'   => 'events',
				'ajax'     => false,
			]
		);

		// Include polyfill if mbstring PHP extension is not enabled.
		if ( ! function_exists( 'mb_substr' ) || ! function_exists( 'mb_strlen' ) ) {
			Helpers::include_mbstring_polyfill();
		}
	}

	/**
	 * Get the debug event types for filtering purpose.
	 *
	 * @since 3.0.0
	 *
	 * @return array Associative array of debug event types StatusCode=>Name.
	 */
	public function get_types() {

		return Event::get_types();
	}

	/**
	 * Get the items counts for various types of debug logs.
	 *
	 * @since 3.0.0
	 */
	public function get_counts() {

		$this->counts = [];

		// Base params with applied filters.
		$base_params = $this->get_filters_query_params();

		$total_params = $base_params;
		unset( $total_params['type'] );
		$this->counts['total'] = ( new EventsCollection( $total_params ) )->get_count();

		foreach ( $this->get_types() as $type => $name ) {
			$collection = new EventsCollection( array_merge( $base_params, [ 'type' => $type ] ) );

			$this->counts[ 'type_' . $type ] = $collection->get_count();
		}

		/**
		 * Filters items counts by various types of debug events.
		 *
		 * @since 3.0.0
		 *
		 * @param array $counts {
		 *     Items counts by types.
		 *
		 *     @type integer $total Total items count.
		 *     @type integer $status_{$type_key} Items count by type.
		 * }
		 */
		$this->counts = apply_filters( 'wp_mail_smtp_admin_debug_events_table_get_counts', $this->counts );
	}

	/**
	 * Retrieve the view types.
	 *
	 * @since 3.0.0
	 */
	public function get_views() {

		$base_url     = $this->get_filters_base_url();
		$current_type = $this->get_filtered_types();

		$views = [];

		$views['all'] = sprintf(
			'<a href="%1$s" %2$s>%3$s&nbsp;<span class="count">(%4$d)</span></a>',
			esc_url( remove_query_arg( 'type', $base_url ) ),
			$current_type === false ? 'class="current"' : '',
			esc_html__( 'All', 'wp-mail-smtp' ),
			intval( $this->counts['total'] )
		);

		foreach ( $this->get_types() as $type => $type_label ) {

			$count = intval( $this->counts[ 'type_' . $type ] );

			// Skipping types with no events.
			if ( $count === 0 && $current_type !== $type ) {
				continue;
			}

			$views[ $type ] = sprintf(
				'<a href="%1$s" %2$s>%3$s&nbsp;<span class="count">(%4$d)</span></a>',
				esc_url( add_query_arg( 'type', $type, $base_url ) ),
				$current_type === $type ? 'class="current"' : '',
				esc_html( $type_label ),
				$count
			);

		}

		/**
		 * Filters debug event item views.
		 *
		 * @since 3.0.0
		 *
		 * @param array $views {
		 *     Debug event items views by types.
		 *
		 *     @type string  $all        Total items view.
		 *     @type integer $status_key Items views by type.
		 * }
		 * @param array $counts {
		 *     Items counts by types.
		 *
		 *     @type integer $total                Total items count.
		 *     @type integer $status_{$status_key} Items count by types.
		 * }
		 */
		return apply_filters( 'wp_mail_smtp_admin_debug_events_table_get_views', $views, $this->counts );
	}

	/**
	 * Define the table columns.
	 *
	 * @since 3.0.0
	 *
	 * @return array Associative array of slug=>Name columns data.
	 */
	public function get_columns() {

		return [
			'event'      => esc_html__( 'Event', 'wp-mail-smtp' ),
			'type'       => esc_html__( 'Type', 'wp-mail-smtp' ),
			'content'    => esc_html__( 'Content', 'wp-mail-smtp' ),
			'initiator'  => esc_html__( 'Source', 'wp-mail-smtp' ),
			'created_at' => esc_html__( 'Date', 'wp-mail-smtp' ),
		];
	}

	/**
	 * Display the main event title with a link to open event details.
	 *
	 * @since 3.0.0
	 *
	 * @param Event $item Event object.
	 *
	 * @return string
	 */
	public function column_event( $item ) {

		return '<strong>' .
			'<a href="#" data-event-id="' . esc_attr( $item->get_id() ) . '"' .
			' class="js-wp-mail-smtp-debug-event-preview row-title event-preview" title="' . esc_attr( $item->get_title() ) . '">' .
				esc_html( $item->get_title() ) .
			'</a>' .
			'</strong>';
	}

	/**
	 * Display event's type.
	 *
	 * @since 3.0.0
	 *
	 * @param Event $item Event object.
	 *
	 * @return string
	 */
	public function column_type( $item ) {

		return esc_html( $item->get_type_name() );
	}

	/**
	 * Display event's content.
	 *
	 * @since 3.0.0
	 *
	 * @param Event $item Event object.
	 *
	 * @return string
	 */
	public function column_content( $item ) {

		$content = $item->get_content();

		if ( mb_strlen( $content ) > 100 ) {
			$content = mb_substr( $content, 0, 100 ) . '...';
		}

		return wp_kses_post( $content );
	}

	/**
	 * Display event's wp_mail initiator.
	 *
	 * @since 3.0.0
	 *
	 * @param Event $item Event object.
	 *
	 * @return string
	 */
	public function column_initiator( $item ) {

		return esc_html( $item->get_initiator() );
	}

	/**
	 * Display event's created date.
	 *
	 * @since 3.0.0
	 *
	 * @param Event $item Event object.
	 *
	 * @return string
	 */
	public function column_created_at( $item ) {

		return $item->get_created_at_formatted();
	}

	/**
	 * Return type filter value or FALSE.
	 *
	 * @since 3.0.0
	 *
	 * @return bool|integer
	 */
	public function get_filtered_types() {

		if ( ! isset( $_REQUEST['type'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return false;
		}

		return intval( $_REQUEST['type'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}

	/**
	 * Return date filter value or FALSE.
	 *
	 * @since 3.0.0
	 *
	 * @return bool|array
	 */
	public function get_filtered_dates() {

		if ( empty( $_REQUEST['date'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return false;
		}

		$dates = (array) explode( ' - ', sanitize_text_field( wp_unslash( $_REQUEST['date'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		return array_map( 'sanitize_text_field', $dates );
	}

	/**
	 * Return search filter values or FALSE.
	 *
	 * @since 3.0.0
	 *
	 * @return bool|array
	 */
	public function get_filtered_search() {

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( empty( $_REQUEST['search'] ) ) {
			return false;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return sanitize_text_field( wp_unslash( $_REQUEST['search'] ) );
	}

	/**
	 * Whether the event log is filtered or not.
	 *
	 * @since 3.0.0
	 *
	 * @return bool
	 */
	public function is_filtered() {

		$is_filtered = false;

		if (
			$this->get_filtered_search() !== false ||
			$this->get_filtered_dates() !== false ||
			$this->get_filtered_types() !== false
		) {
			$is_filtered = true;
		}

		return $is_filtered;
	}

	/**
	 * Get current filters query parameters.
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	public function get_filters_query_params() {

		$params = [
			'search' => $this->get_filtered_search(),
			'type'   => $this->get_filtered_types(),
			'date'   => $this->get_filtered_dates(),
		];

		return array_filter(
			$params,
			function ( $v ) {
				return $v !== false;
			}
		);
	}

	/**
	 * Get current filters base url.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public function get_filters_base_url() {

		$base_url       = DebugEvents::get_page_url();
		$filters_params = $this->get_filters_query_params();

		if ( isset( $filters_params['search'] ) ) {
			$base_url = add_query_arg( 'search', $filters_params['search'], $base_url );
		}

		if ( isset( $filters_params['type'] ) ) {
			$base_url = add_query_arg( 'type', $filters_params['type'], $base_url );
		}

		if ( isset( $filters_params['date'] ) ) {
			$base_url = add_query_arg( 'date', implode( ' - ', $filters_params['date'] ), $base_url );
		}

		return $base_url;
	}

	/**
	 * Get the data, prepare pagination, process bulk actions.
	 * Prepare columns for display.
	 *
	 * @since 3.0.0
	 */
	public function prepare_items() {

		// Retrieve count.
		$this->get_counts();

		// Prepare all the params to pass to our Collection. All sanitization is done in that class.
		$params = $this->get_filters_query_params();

		// Total amount for pagination with WHERE clause - super quick count DB request.
		$total_items = ( new EventsCollection( $params ) )->get_count();

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! empty( $_REQUEST['orderby'] ) && in_array( $_REQUEST['orderby'], [ 'event', 'type', 'content', 'initiator', 'created_at' ], true ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$params['orderby'] = sanitize_key( $_REQUEST['orderby'] );
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! empty( $_REQUEST['order'] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$params['order'] = strtoupper( sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ) ) === 'DESC' ? 'DESC' : 'ASC';
		}

		$params['offset'] = ( $this->get_pagenum() - 1 ) * EventsCollection::$per_page;

		// Get the data from the DB using parameters defined above.
		$collection  = new EventsCollection( $params );
		$this->items = $collection->get();

		/*
		 * Register our pagination options & calculations.
		 */
		$this->set_pagination_args(
			[
				'total_items' => $total_items,
				'per_page'    => EventsCollection::$per_page,
			]
		);
	}

	/**
	 * Display the search box.
	 *
	 * @since 1.7.0
	 *
	 * @param string $text     The 'submit' button label.
	 * @param string $input_id ID attribute value for the search input field.
	 */
	public function search_box( $text, $input_id ) {

		if ( ! $this->is_filtered() && ! $this->has_items() ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$search = ! empty( $_REQUEST['search'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['search'] ) ) : '';

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! empty( $_REQUEST['orderby'] ) && in_array( $_REQUEST['orderby'], [ 'event', 'type', 'content', 'initiator', 'created_at' ], true ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$order_by = sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) );
			echo '<input type="hidden" name="orderby" value="' . esc_attr( $order_by ) . '" />';
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! empty( $_REQUEST['order'] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$order = strtoupper( sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ) ) === 'DESC' ? 'DESC' : 'ASC';
			echo '<input type="hidden" name="order" value="' . esc_attr( $order ) . '" />';
		}
		?>

		<p class="search-box">
			<label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>"><?php echo esc_html( $text ); ?>:</label>
			<input type="search" id="<?php echo esc_attr( $input_id ); ?>" name="search" value="<?php echo esc_attr( $search ); ?>" />
			<?php submit_button( $text, '', '', false, [ 'id' => 'search-submit' ] ); ?>
		</p>

		<?php
	}

	/**
	 * Whether the table has items to display or not.
	 *
	 * @since 3.0.0
	 *
	 * @return bool
	 */
	public function has_items() {

		return count( $this->items ) > 0;
	}

	/**
	 * Message to be displayed when there are no items.
	 *
	 * @since 3.0.0
	 */
	public function no_items() {

		if ( $this->is_filtered() ) {
			esc_html_e( 'No events found.', 'wp-mail-smtp' );
		} else {
			esc_html_e( 'No events have been logged for now.', 'wp-mail-smtp' );
		}
	}

	/**
	 * Displays the table.
	 *
	 * @since 3.0.0
	 */
	public function display() {

		$this->_column_headers = [ $this->get_columns(), [], [] ];

		parent::display();
	}

	/**
	 * Hide the tablenav if there are no items in the table.
	 * And remove the bulk action nonce and code.
	 *
	 * @since 3.0.0
	 *
	 * @param string $which Which tablenav: top or bottom.
	 */
	protected function display_tablenav( $which ) {

		if ( ! $this->has_items() ) {
			return;
		}
		?>

		<div class="tablenav <?php echo esc_attr( $which ); ?>">

			<?php
			$this->extra_tablenav( $which );
			$this->pagination( $which );
			?>

			<br class="clear" />
		</div>
		<?php
	}

	/**
	 * Extra controls to be displayed between bulk actions and pagination.
	 *
	 * @since 3.0.0
	 *
	 * @param string $which Which tablenav: top or bottom.
	 */
	protected function extra_tablenav( $which ) {

		if ( $which !== 'top' || ! $this->has_items() ) {
			return;
		}

		$date = $this->get_filtered_dates() !== false ? implode( ' - ', $this->get_filtered_dates() ) : '';
		?>
		<div class="alignleft actions wp-mail-smtp-filter-date">

			<input type="text" name="date" class="regular-text wp-mail-smtp-filter-date-selector wp-mail-smtp-filter-date__control"
						 placeholder="<?php esc_attr_e( 'Select a date range', 'wp-mail-smtp' ); ?>"
						 value="<?php echo esc_attr( $date ); ?>">

			<button type="submit" name="action" value="filter_date" class="button wp-mail-smtp-filter-date__btn">
				<?php esc_html_e( 'Filter', 'wp-mail-smtp' ); ?>
			</button>

		</div>
		<?php
		if ( current_user_can( wp_mail_smtp()->get_capability_manage_options() ) ) {
			wp_nonce_field( 'wp_mail_smtp_debug_events', 'wp-mail-smtp-debug-events-nonce', false );
			printf(
				'<button id="wp-mail-smtp-delete-all-debug-events-button" type="button" class="button">%s</button>',
				esc_html__( 'Delete All Events', 'wp-mail-smtp' )
			);
		}
	}

	/**
	 * Get the name of the primary column.
	 * Important for the mobile view.
	 *
	 * @since 3.0.0
	 *
	 * @return string The name of the primary column.
	 */
	protected function get_primary_column_name() {

		return 'event';
	}
}
