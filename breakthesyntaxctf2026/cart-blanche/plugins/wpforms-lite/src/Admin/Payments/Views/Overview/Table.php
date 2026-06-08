<?php

namespace WPForms\Admin\Payments\Views\Overview;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPForms\Db\Payments\ValueValidator;
use WPForms\Db\Payments\Queries;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Payments Overview Table class.
 *
 * @since 1.8.2
 */
class Table extends \WP_List_Table {

	/**
	 * Trait for using notices.
	 *
	 * @since 1.8.4
	 */
	use Traits\ResetNotices;

	/**
	 * Payment type: one-time.
	 *
	 * @since 1.8.2
	 *
	 * @var string
	 */
	const ONE_TIME = 'one-time';

	/**
	 * Payment status: trash.
	 *
	 * @since 1.8.2
	 *
	 * @var string
	 */
	const TRASH = 'trash';

	/**
	 * Total number of payments.
	 *
	 * @since 1.8.2
	 *
	 * @var array
	 */
	private $counts;

	/**
	 * Table query arguments.
	 *
	 * @since 1.8.4
	 *
	 * @var array
	 */
	private $table_query_args = [];

	/**
	 * Retrieve the table columns.
	 *
	 * @since 1.8.2
	 *
	 * @return array $columns Array of all the list table columns.
	 */
	public function get_columns() {

		static $columns;

		if ( ! empty( $columns ) ) {
			return $columns;
		}

		$columns = [
			'cb'    => '<input type="checkbox" />',
			'title' => esc_html__( 'Payment', 'wpforms-lite' ),
			'date'  => esc_html__( 'Date', 'wpforms-lite' ),
		];

		if ( wpforms()->obj( 'payment_queries' )->has_different_values( 'gateway' ) ) {
			$columns['gateway'] = esc_html__( 'Gateway', 'wpforms-lite' );
		}

		if ( wpforms()->obj( 'payment_queries' )->has_different_values( 'type' ) ) {
			$columns['type'] = esc_html__( 'Type', 'wpforms-lite' );
		}

		if ( wpforms()->obj( 'payment_meta' )->is_valid_meta_by_meta_key( 'coupon_id' ) ) {
			$columns['coupon'] = esc_html__( 'Coupon', 'wpforms-lite' );
		}

		$columns['total'] = esc_html__( 'Total', 'wpforms-lite' );

		if ( wpforms()->obj( 'payment_queries' )->has_subscription() ) {
			$columns['subscription'] = esc_html__( 'Subscription', 'wpforms-lite' );
		}

		$columns['form']   = esc_html__( 'Form', 'wpforms-lite' );
		$columns['status'] = esc_html__( 'Status', 'wpforms-lite' );

		/**
		 * Filters the columns in the Payments Overview table.
		 *
		 * @since 1.8.2
		 *
		 * @param array $columns Array of columns.
		 */
		return (array) apply_filters( 'wpforms_admin_payments_views_overview_table_get_columns', $columns );
	}

	/**
	 * Determine whether it is a trash view.
	 *
	 * @since 1.8.2
	 *
	 * @return bool
	 */
	private function is_trash_view() {

		return $this->is_current_view( 'trash' );
	}

	/**
	 * Define the table's sortable columns.
	 *
	 * @since 1.8.2
	 *
	 * @return array Array of all the sortable columns.
	 */
	protected function get_sortable_columns() {

		return [
			'title' => [ 'id', false ],
			'date'  => [ 'date', false ],
			'total' => [ 'total', false ],
		];
	}

	/**
	 * Prepare the table with different parameters, pagination, columns and table elements.
	 *
	 * @since 1.8.2
	 */
	public function prepare_items() {

		$page      = $this->get_pagenum();
		$per_page  = $this->get_items_per_page( 'wpforms_payments_per_page', 20 );
		$data_args = [
			'number'            => $per_page,
			'offset'            => $per_page * ( $page - 1 ),
			'orderby'           => $this->get_order_by(),
			'search'            => $this->get_search_query(),
			'search_conditions' => $this->get_search_conditions(),
			'status'            => $this->get_valid_status_from_request(),
			'is_published'      => $this->is_trash_view() ? 0 : 1,
		];

		// Set the table query arguments for later use.
		$this->table_query_args = $this->prepare_table_query_args( $data_args );

		// Retrieve the payment records for the given data arguments.
		$this->items = wpforms()->obj( 'payment' )->get_payments( $this->table_query_args );

		// Setup the counts.
		$this->setup_counts();

		// Check if we can continue.
		$this->can_prepare_records();

		// Get the proper total number of records depending on the current status view.
		$total_items = $this->get_valid_status_count_from_request();
		$total_pages = ceil( $total_items / $per_page );

		// Finalize pagination.
		$this->set_pagination_args(
			[
				'total_items' => $total_items,
				'total_pages' => (int) $total_pages,
				'per_page'    => $per_page,
			]
		);
	}

	/**
	 * Prepare the query arguments for the overview table.
	 *
	 * @since 1.8.4
	 *
	 * @param array $args Array of data arguments.
	 *
	 * @return array
	 */
	private function prepare_table_query_args( $args = [] ) {

		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		return wp_parse_args(
			$args,
			[
				'table_query'         => true,
				'order'               => isset( $_GET['order'] ) ? sanitize_key( $_GET['order'] ) : 'DESC',
				'form_id'             => isset( $_GET['form_id'] ) ? absint( $_GET['form_id'] ) : '',
				'type'                => isset( $_GET['type'] ) ? sanitize_text_field( wp_unslash( $_GET['type'] ) ) : '',
				'gateway'             => isset( $_GET['gateway'] ) ? sanitize_text_field( wp_unslash( $_GET['gateway'] ) ) : '',
				'subscription_status' => isset( $_GET['subscription_status'] ) ? sanitize_text_field( wp_unslash( $_GET['subscription_status'] ) ) : '',
			]
		);
		// phpcs:enable WordPress.Security.NonceVerification.Recommended
	}

	/**
	 * Message to be displayed when there are no payments.
	 *
	 * @since 1.8.2
	 */
	public function no_items() {

		if ( $this->is_trash_view() ) {
			esc_html_e( 'No payments found in the trash.', 'wpforms-lite' );

			return;
		}

		if ( $this->is_current_view( 'search' ) ) {
			esc_html_e( 'No payments found, please try a different search.', 'wpforms-lite' );

			return;
		}

		esc_html_e( 'No payments found.', 'wpforms-lite' );
	}

	/**
	 * Generates content for a single row of the table.
	 *
	 * @since 1.8.4
	 *
	 * @param array $item Item data.
	 */
	public function single_row( $item ) {

		// Leave the default row if the item is not a subscription.
		if ( empty( $item['subscription_id'] ) || empty( $item['subscription_status'] ) ) {
			parent::single_row( $item );

			return;
		}

		$has_renewal = wpforms()->obj( 'payment_queries' )->if_subscription_has_renewal( $item['subscription_id'] );

		// Leave the default row if the subscription has no renewal.
		if ( ! $has_renewal ) {
			parent::single_row( $item );

			return;
		}

		echo '<tr class="subscription-has-renewal">';
		$this->single_row_columns( $item );
		echo '</tr>';
	}

	/**
	 * Column default values.
	 *
	 * @since 1.8.2
	 *
	 * @param array  $item        Item data.
	 * @param string $column_name Column name.
	 *
	 * @return string
	 */
	protected function column_default( $item, $column_name ) {

		if ( method_exists( $this, "get_column_{$column_name}" ) ) {
			return $this->{"get_column_{$column_name}"}( $item );
		}

		if ( isset( $item[ $column_name ] ) ) {
			return esc_html( $item[ $column_name ] );
		}

		/**
		 * Allow to filter default column value.
		 *
		 * @since 1.8.2
		 *
		 * @param string $value       Default column value.
		 * @param array  $item        Item data.
		 * @param string $column_name Column name.
		 */
		return apply_filters( 'wpforms_admin_payments_views_overview_table_column_default_value', '', $item, $column_name );
	}

	/**
	 * Define the checkbox column.
	 *
	 * @since 1.8.2
	 *
	 * @param array $item The current item.
	 *
	 * @return string
	 */
	protected function column_cb( $item ) {

		return '<input type="checkbox" name="payment_id[]" value="' . absint( $item['id'] ) . '" />';
	}

	/**
	 * Prepare the items and display the table.
	 *
	 * @since 1.8.2
	 */
	public function display() {

		?>
		<form id="wpforms-payments-table" method="GET" action="<?php echo esc_url( Page::get_url() ); ?>">
			<?php
			$this->display_hidden_fields();
			$this->show_reset_filter();
			$this->views();
			$this->search_box( esc_html__( 'Search Payments', 'wpforms-lite' ), 'wpforms-payments-search-input' );
			parent::display();
			?>
		</form>
		<?php
	}

	/**
	 * Extra filtering controls to be displayed between bulk actions and pagination.
	 *
	 * @since 1.8.4
	 *
	 * @param string $which Position of the extra controls: 'top' or 'bottom'.
	 */
	protected function extra_tablenav( $which ) {

		// We only want to show the extra controls on the top.
		if ( $which !== 'top' ) {
			return;
		}

		$tablenav_data = [
			'type'                => [
				'data'         => ValueValidator::get_allowed_types(),
				'plural_label' => __( 'types', 'wpforms-lite' ),
			],
			'gateway'             => [
				'data'         => ValueValidator::get_allowed_gateways(),
				'plural_label' => __( 'gateways', 'wpforms-lite' ),
			],
			'subscription_status' => [
				'data'         => ValueValidator::get_allowed_subscription_statuses(),
				'plural_label' => __( 'subscriptions', 'wpforms-lite' ),
			],
		];

		// Special case for showing all available types, gateways and subscription statuses.
		if ( ! $this->has_items() ) {
			unset(
				$this->table_query_args['type'],
				$this->table_query_args['gateway'],
				$this->table_query_args['subscription_status']
			);
		}

		// Output the reset filter notice.
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo wpforms_render(
			'admin/payments/tablenav-filters',
			[
				'filters' => $this->prepare_extra_tablenav_filters( $tablenav_data ),
			],
			true
		);
	}

	/**
	 * Iterate through each given filter option and remove the ones that don't have any records.
	 *
	 * @since 1.8.4
	 *
	 * @param array $tablenav_data Array of filter options.
	 *
	 * @return string
	 */
	private function prepare_extra_tablenav_filters( $tablenav_data ) {

		$rendered_nav_data = [];

		foreach ( $tablenav_data as $nav_key => $nav_attributes ) {

			$filtered_data = [];

			// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$selected = isset( $_GET[ $nav_key ] ) ? explode( '|', wp_unslash( $_GET[ $nav_key ] ) ) : [];

			foreach ( $nav_attributes['data'] as $attribute_key => $attribute_value ) {
				$query_args = array_merge( $this->table_query_args, [ $nav_key => $attribute_key ] );

				if ( in_array( $attribute_key, $selected, true ) || wpforms()->obj( 'payment_queries' )->if_exists( $query_args ) ) {
					$filtered_data[ $attribute_key ] = $attribute_value;
				}
			}

			$selected = array_filter(
				$selected,
				static function ( $value ) use ( $filtered_data ) {

					return isset( $filtered_data[ $value ] );
				}
			);

			if ( empty( $filtered_data ) || ( count( $filtered_data ) === 1 && empty( $selected ) ) ) {
				continue;
			}

			$rendered_nav_data[] = wpforms_render(
				'admin/payments/tablenav-filter-multiselect',
				[
					'selected'      => $selected,
					'options'       => $filtered_data,
					'name'          => $nav_key,
					'data_settings' => [
						'i18n' => [
							'multiple' => sprintf( /* translators: %s - plural label. */
								__( 'Multiple %s selected', 'wpforms-lite' ),
								esc_attr( $nav_attributes['plural_label'] )
							),
							'all'      => sprintf( /* translators: %s - plural label. */
								__( 'All %s', 'wpforms-lite' ),
								esc_attr( $nav_attributes['plural_label'] )
							),
						],
					],
				],
				true
			);
		}

		return implode( '', $rendered_nav_data );
	}

	/**
	 * Display the search box.
	 *
	 * @since 1.8.2
	 *
	 * @param string $text     The 'submit' button label.
	 * @param string $input_id ID attribute value for the search input field.
	 */
	public function search_box( $text, $input_id ) {

		$search_where = $this->get_search_where_key();
		$search_mode  = $this->get_search_mode_key();
		?>
		<p class="search-box">
			<label class="screen-reader-text" for="search_where"><?php esc_html_e( 'Select which field to use when searching for payments', 'wpforms-lite' ); ?></label>
			<select name="search_where">
				<option value="<?php echo esc_attr( Search::TITLE ); ?>" <?php selected( $search_where, Search::TITLE ); ?> ><?php echo esc_html( $this->get_search_where( Search::TITLE ) ); ?></option>
				<option value="<?php echo esc_attr( Search::TRANSACTION_ID ); ?>" <?php selected( $search_where, Search::TRANSACTION_ID ); ?> ><?php echo esc_html( $this->get_search_where( Search::TRANSACTION_ID ) ); ?></option>
				<option value="<?php echo esc_attr( Search::SUBSCRIPTION_ID ); ?>" <?php selected( $search_where, Search::SUBSCRIPTION_ID ); ?> ><?php echo esc_html( $this->get_search_where( Search::SUBSCRIPTION_ID ) ); ?></option>
				<option value="<?php echo esc_attr( Search::EMAIL ); ?>" <?php selected( $search_where, Search::EMAIL ); ?> ><?php echo esc_html( $this->get_search_where( Search::EMAIL ) ); ?></option>
				<option value="<?php echo esc_attr( Search::CREDIT_CARD ); ?>" <?php selected( $search_where, Search::CREDIT_CARD ); ?> ><?php echo esc_html( $this->get_search_where( Search::CREDIT_CARD ) ); ?></option>
				<option value="<?php echo esc_attr( Search::ANY ); ?>" <?php selected( $search_where, Search::ANY ); ?> ><?php echo esc_html( $this->get_search_where( Search::ANY ) ); ?></option>
			</select>
			<label class="screen-reader-text" for="search_mode"><?php esc_html_e( 'Select which comparison method to use when searching for payments', 'wpforms-lite' ); ?></label>
			<select name="search_mode">
				<option value="<?php echo esc_attr( Search::MODE_CONTAINS ); ?>" <?php selected( $search_mode, Search::MODE_CONTAINS ); ?> ><?php echo esc_html( $this->get_search_mode( Search::MODE_CONTAINS ) ); ?></option>
				<option value="<?php echo esc_attr( Search::MODE_EQUALS ); ?>" <?php selected( $search_mode, Search::MODE_EQUALS ); ?> ><?php echo esc_html( $this->get_search_mode( Search::MODE_EQUALS ) ); ?></option>
				<option value="<?php echo esc_attr( Search::MODE_STARTS ); ?>" <?php selected( $search_mode, Search::MODE_STARTS ); ?> ><?php echo esc_html( $this->get_search_mode( Search::MODE_STARTS ) ); ?></option>
			</select>
			<label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>"><?php echo esc_html( $text ); ?></label>
			<input type="search" id="<?php echo esc_attr( $input_id ); ?>" name="s" value="<?php echo esc_attr( $this->get_search_query() ); ?>" />
			<input type="submit" class="button" value="<?php echo esc_attr( $text ); ?>" />
		</p>
		<?php
	}

	/**
	 * Get bulk actions to be displayed in bulk action dropdown.
	 *
	 * @since 1.8.2
	 *
	 * @return array
	 */
	protected function get_bulk_actions() {

		if ( $this->is_trash_view() ) {
			return [
				'restore' => esc_html__( 'Restore', 'wpforms-lite' ),
				'delete'  => esc_html__( 'Delete Permanently', 'wpforms-lite' ),
			];
		}

		return [
			'trash' => esc_html__( 'Move to Trash', 'wpforms-lite' ),
		];
	}

	/**
	 * Generates the table navigation above or below the table.
	 *
	 * @since 1.8.2
	 *
	 * @param string $which The location of the bulk actions: 'top' or 'bottom'.
	 */
	protected function display_tablenav( $which ) {

		if ( $this->has_items() ) {
			parent::display_tablenav( $which );

			return;
		}

		echo '<div class="tablenav ' . esc_attr( $which ) . '">';

		if ( $this->is_trash_view() ) {
			echo '<div class="alignleft actions bulkactions">';
			$this->bulk_actions();
			echo '</div>';
		}

		$this->extra_tablenav( $which );
		echo '<br class="clear" />';
		echo '</div>';
	}

	/**
	 * List of CSS classes for the "WP_List_Table" table tag.
	 *
	 * @global string $mode List table view mode.
	 *
	 * @since 1.8.2
	 *
	 * @return array
	 */
	protected function get_table_classes() {

		global $mode;

		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$mode       = get_user_setting( 'posts_list_mode', 'list' );
		$mode_class = esc_attr( 'table-view-' . $mode );
		$classes    = [
			'widefat',
			'striped',
			'wpforms-table-list',
			'wpforms-table-list-payments',
			$mode_class,
		];

		// For styling purposes, we'll add a dedicated class name for determining the number of visible columns.
		// The ideal threshold for applying responsive styling is set at "5" columns based on the need for "Tablet" view.
		$columns_class = $this->get_column_count() > 5 ? 'many' : 'few';

		$classes[] = "has-{$columns_class}-columns";

		return $classes;
	}

	/**
	 * Get valid status from request.
	 *
	 * @since 1.8.2
	 *
	 * @return string
	 */
	private function get_valid_status_from_request() {

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		return ! empty( $_REQUEST['status'] ) && ( ValueValidator::is_valid( $_REQUEST['status'], 'status' ) || $_REQUEST['status'] === self::TRASH ) ? $_REQUEST['status'] : '';
	}

	/**
	 * Get number of payments for the current status.
	 * Note that this function also validates the status internally.
	 *
	 * @since 1.8.4
	 *
	 * @return string
	 */
	private function get_valid_status_count_from_request() {

		// Retrieve the current status.
		$current_status = $this->get_valid_status_from_request();

		return $current_status && isset( $this->counts[ $current_status ] ) ? $this->counts[ $current_status ] : $this->counts['total'];
	}

	/**
	 * Get search where value.
	 *
	 * @since 1.8.2
	 *
	 * @param string $search_key Search where key.
	 *
	 * @return string Return default search where value if not valid key provided.
	 */
	private function get_search_where( $search_key ) {

		$allowed_values = $this->get_allowed_search_where();

		return $search_key && isset( $allowed_values[ $search_key ] ) ? $allowed_values[ $search_key ] : $allowed_values[ Search::TITLE ];
	}

	/**
	 * Get search where key.
	 *
	 * @since 1.8.2
	 *
	 * @return string
	 */
	private function get_search_where_key() {

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$where_key = isset( $_GET['search_where'] ) ? sanitize_key( $_GET['search_where'] ) : '';

		return isset( $this->get_allowed_search_where()[ $where_key ] ) ? $where_key : Search::TITLE;
	}

	/**
	 * Get allowed search where values.
	 *
	 * @since 1.8.2
	 *
	 * @return array
	 */
	private function get_allowed_search_where() {

		static $search_values;

		if ( ! $search_values ) {

			$search_values = [
				Search::TITLE           => __( 'Payment Title', 'wpforms-lite' ),
				Search::TRANSACTION_ID  => __( 'Transaction ID', 'wpforms-lite' ),
				Search::EMAIL           => __( 'Customer Email', 'wpforms-lite' ),
				Search::SUBSCRIPTION_ID => __( 'Subscription ID', 'wpforms-lite' ),
				Search::CREDIT_CARD     => __( 'Last 4 digits of credit card', 'wpforms-lite' ),
				Search::ANY             => __( 'Any payment field', 'wpforms-lite' ),
			];
		}

		return $search_values;
	}

	/**
	 * Get search where value.
	 *
	 * @since 1.8.2
	 *
	 * @param string $mode_key Search mode key.
	 *
	 * @return string Return default search mode value if not valid key provided.
	 */
	private function get_search_mode( $mode_key ) {

		$allowed_modes = $this->get_allowed_search_modes();

		return $mode_key && isset( $allowed_modes[ $mode_key ] ) ? $allowed_modes[ $mode_key ] : $allowed_modes[ Search::MODE_EQUALS ];
	}

	/**
	 * Get search mode key.
	 *
	 * @since 1.8.2
	 *
	 * @return string
	 */
	private function get_search_mode_key() {

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$where_mode = isset( $_GET['search_mode'] ) ? sanitize_key( $_GET['search_mode'] ) : '';

		return isset( $this->get_allowed_search_modes()[ $where_mode ] ) ? $where_mode : Search::MODE_CONTAINS;
	}

	/**
	 * Get allowed search mode params.
	 *
	 * @since 1.8.2
	 *
	 * @return array
	 */
	private function get_allowed_search_modes() {

		static $search_modes;

		if ( ! $search_modes ) {

			$search_modes = [
				Search::MODE_CONTAINS => __( 'contains', 'wpforms-lite' ),
				Search::MODE_EQUALS   => __( 'equals', 'wpforms-lite' ),
				Search::MODE_STARTS   => __( 'starts with', 'wpforms-lite' ),
			];
		}

		return $search_modes;
	}

	/**
	 * Prepare counters.
	 *
	 * @since 1.8.2
	 */
	private function setup_counts() {

		// Define the general views with their respective arguments.
		$views = [
			'published' => [
				'is_published' => 1,
				'status'       => '',
			],
			'trash'     => [
				'is_published' => 0,
				'status'       => '',
			],
		];

		// Generate filterable status views with their respective arguments.
		foreach ( ValueValidator::get_allowed_one_time_statuses() as $status => $label ) {
			$views[ $status ] = [
				'is_published' => 1,
				'status'       => $status,
			];
		}

		// Calculate the counts for each view and store them in the $this->counts array.
		foreach ( $views as $status => $status_args ) {
			$this->counts[ $status ] = wpforms()->obj( 'payment_queries' )->count_all( array_merge( $this->table_query_args, $status_args ) );
		}

		// If the current view is the trash view, set the 'total' count to the 'trash' count.
		if ( $this->is_trash_view() ) {
			$this->counts['total'] = $this->counts['trash'];

			return;
		}

		// Otherwise, set the 'total' count to the 'published' count.
		$this->counts['total'] = $this->counts['published'];
	}

	/**
	 * Get the orderby value.
	 *
	 * @since 1.8.2
	 *
	 * @return string
	 */
	private function get_order_by() {

		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		if ( ! isset( $_GET['orderby'] ) ) {
			return 'id';
		}

		if ( $_GET['orderby'] === 'date' ) {
			return 'date_updated_gmt';
		}

		if ( $_GET['orderby'] === 'total' ) {
			return 'total_amount';
		}

		return 'id';
		// phpcs:enable WordPress.Security.NonceVerification.Recommended
	}

	/**
	 * Get payment column value.
	 *
	 * @since 1.8.2
	 *
	 * @param array $item Payment item.
	 *
	 * @return string
	 */
	private function get_column_title( array $item ) {

		$title     = $this->get_payment_title( $item );
		$na_status = empty( $title ) ? sprintf( '<span class="payment-title-is-empty">- %s</span>', Helpers::get_placeholder_na_text() ) : '';

		if ( ! $item['is_published'] ) {
			return sprintf( '<span>#%1$d %2$s</span> %3$s', $item['id'], esc_html( $title ), $na_status );
		}

		$single_url = add_query_arg(
			[
				'page'       => 'wpforms-payments',
				'view'       => 'payment',
				'payment_id' => absint( $item['id'] ),
			],
			admin_url( 'admin.php' )
		);

		return sprintf( '<a href="%1$s">#%2$d %3$s</a> %4$s', esc_url( $single_url ), $item['id'], esc_html( $title ), $na_status );
	}

	/**
	 * Get date column value.
	 *
	 * @since 1.8.2
	 *
	 * @param array $item Payment item.
	 *
	 * @return string
	 */
	private function get_column_date( array $item ): string {

		$item_date_gmt      = $item['date_updated_gmt'];
		$item_date          = get_date_from_gmt( $item_date_gmt, 'Y-m-d H:i' );
		$item_timestamp     = strtotime( $item_date );
		$item_timestamp_gmt = strtotime( $item_date_gmt );

		// Check if the $timestamp represents a time within the last 24 hours and is not in the future.
		if ( $item_timestamp_gmt <= time() ) {
			/* translators: %s - relative time difference, e.g. "5 minutes", "12 days". */
			$human = sprintf( esc_html__( '%s ago', 'wpforms-lite' ), human_time_diff( $item_timestamp_gmt ) );
		} else {
			$human = wpforms_datetime_format( $item_timestamp, 'M j, Y', false );
		}

		return sprintf( '<span title="%s">%s</span>', wpforms_datetime_format( $item_timestamp, 'Y-m-d H:i', false ), $human );
	}

	/**
	 * Get gateway column value.
	 *
	 * @since 1.8.2
	 *
	 * @param array $item Payment item.
	 *
	 * @return string
	 */
	private function get_column_gateway( array $item ) {

		if ( ! isset( $item['gateway'] ) || ! ValueValidator::is_valid( $item['gateway'], 'gateway' ) ) {
			return '';
		}

		return ValueValidator::get_allowed_gateways()[ $item['gateway'] ];
	}

	/**
	 * Get total column value.
	 *
	 * @since 1.8.2
	 *
	 * @param array $item Payment item.
	 *
	 * @return string
	 */
	private function get_column_total( array $item ) {

		return esc_html( $this->get_formatted_amount_from_item( $item ) );
	}

	/**
	 * Get form column value.
	 *
	 * @since 1.8.2
	 *
	 * @param array $item Payment item.
	 *
	 * @return string
	 */
	private function get_column_form( array $item ) {

		// Display "N/A" placeholder text if the form is not found or not published.
		if ( empty( $item['form_id'] ) || get_post_status( $item['form_id'] ) !== 'publish' ) {
			return Helpers::get_placeholder_na_text();
		}

		$form = wpforms()->obj( 'form' )->get( $item['form_id'] );

		if ( ! $form || $form->post_status !== 'publish' ) {
			return Helpers::get_placeholder_na_text();
		}

		// Display the form name with a link to the form builder.
		$name = ! empty( $form->post_title ) ? $form->post_title : $form->post_name;
		$url  = add_query_arg(
			'form_id',
			absint( $form->ID ),
			remove_query_arg( 'paged' )
		);

		return sprintf( '<a href="%s">%s</a>', esc_url( $url ), wp_kses_post( $name ) );
	}

	/**
	 * Get status column value.
	 *
	 * @since 1.8.2
	 *
	 * @param array $item Payment item.
	 *
	 * @return string
	 */
	private function get_column_status( array $item ) {

		if ( ! isset( $item['status'] ) || ! ValueValidator::is_valid( $item['status'], 'status' ) ) {
			return Helpers::get_placeholder_na_text();
		}

		return sprintf(
			wp_kses(
				'<span class="wpforms-payment-status status-%1$s">%2$s</span>',
				[
					'span' => [
						'class' => [],
					],
					'i'    => [
						'class' => [],
						'title' => [],
					],
				]
			),
			strtolower( $item['status'] ),
			$item['status'] === 'partrefund' ? __( '% Refunded', 'wpforms-lite' ) : ValueValidator::get_allowed_statuses()[ $item['status'] ]
		);
	}

	/**
	 * Get subscription column value.
	 *
	 * @since 1.8.2
	 *
	 * @param array $item Payment item.
	 *
	 * @return string
	 */
	private function get_column_subscription( array $item ) {

		if ( $item['type'] === self::ONE_TIME ) {
			return Helpers::get_placeholder_na_text();
		}

		$amount      = $this->get_formatted_amount_from_item( $item );
		$description = Helpers::get_subscription_description( $item['id'], $amount );
		$status      = $this->get_subscription_status( $item );

		return sprintf(
			'<span class="wpforms-subscription-status status-%1$s" title="%2$s">%3$s</span>',
			sanitize_html_class( $status ),
			$status ? ValueValidator::get_allowed_subscription_statuses()[ $status ] : '',
			$description
		);
	}

	/**
	 * Get type column value.
	 *
	 * @since 1.8.2
	 *
	 * @param array $item Payment item.
	 *
	 * @return string
	 */
	private function get_column_type( array $item ) {

		if ( ! isset( $item['type'] ) || ! ValueValidator::is_valid( $item['type'], 'type' ) ) {
			return Helpers::get_placeholder_na_text();
		}

		return ValueValidator::get_allowed_types()[ $item['type'] ];
	}

	/**
	 * Show the coupon code used for the payment.
	 * If the coupon code is not found, show N/A.
	 *
	 * @since 1.8.4
	 *
	 * @param array $item Payment item.
	 *
	 * @return string
	 */
	private function get_column_coupon( $item ) {

		$payment_meta = wpforms()->obj( 'payment_meta' )->get_all( $item['id'] );

		// If the coupon info is empty, show N/A.
		if ( empty( $payment_meta['coupon_info'] ) || empty( $payment_meta['coupon_id'] ) ) {
			return Helpers::get_placeholder_na_text();
		}

		$url = add_query_arg(
			'coupon_id',
			$payment_meta['coupon_id']->value,
			remove_query_arg( 'paged' )
		);

		return sprintf(
			'<a href="%1$s" aria-label="%2$s">%3$s</a>',
			esc_url( $url ),
			esc_attr__( 'Filter entries by coupon',  'wpforms-lite' ),
			esc_html( $this->get_coupon_name_by_info( $payment_meta['coupon_info']->value ) )
		);
	}

	/**
	 * Get subscription status.
	 *
	 * @since 1.8.4
	 *
	 * @param array $item Payment item.
	 *
	 * @return string
	 */
	private function get_subscription_status( $item ) {

		if ( ! in_array( $item['type'], [ 'subscription', 'renewal' ], true ) ) {
			return '';
		}

		if ( $item['type'] === 'subscription' ) {
			return $item['subscription_status'];
		}

		// For renewals, get subscription status from the parent subscription.
		$parent_subscription = ( new Queries() )->get_subscription( $item['subscription_id'] );

		return ! empty( $parent_subscription->subscription_status ) ? $parent_subscription->subscription_status : '';
	}

	/**
	 * Get payment title.
	 *
	 * @param array $item Payment item.
	 *
	 * @since 1.8.2
	 *
	 * @return string
	 */
	private function get_payment_title( array $item ) {

		if ( empty( $item['title'] ) ) {
			return '';
		}

		return ' - ' . $item['title'];
	}

	/**
	 * Get subscription icon.
	 *
	 * @since 1.8.2
	 *
	 * @param array $item Payment item.
	 *
	 * @return string
	 */
	private function get_subscription_status_icon( array $item ) {

		if ( empty( $item['subscription_id'] ) ) {
			return '';
		}

		return '<span class="dashicons dashicons-marker"></span>';
	}

	/**
	 * Get formatted amount from item.
	 *
	 * @since 1.8.2
	 *
	 * @param array $item Payment item.
	 *
	 * @return string
	 */
	private function get_formatted_amount_from_item( $item ) {

		if ( empty( $item['total_amount'] ) ) {
			return '';
		}

		return wpforms_format_amount( wpforms_sanitize_amount( $item['total_amount'], $item['currency'] ), true, $item['currency'] );
	}

	/**
	 * Get selectors which will be displayed over the bulk action menu.
	 *
	 * @since 1.8.2
	 *
	 * @return array
	 */
	protected function get_views() {

		$base          = remove_query_arg( [ 'status', 'paged' ] );
		$is_trash_view = $this->is_trash_view();

		$views = [
			'all' => sprintf(
				'<a href="%s"%s>%s <span class="count">(%d)</span></a>',
				esc_url( $base ),
				$this->is_current_view( 'all' ) ? ' class="current"' : '',
				esc_html__( 'All', 'wpforms-lite' ),
				(int) $this->counts['published']
			),
		];

		// Iterate through the filterable statuses and add them to the "$views" array.
		$views = array_merge( $views, $this->get_views_for_filterable_statuses( $base ) );

		/** This filter is documented in \WPForms\Admin\Payments\Views\Overview\Table::display_tablenav(). */
		if ( $this->counts['trash'] || $is_trash_view ) {
			$views['trash'] = sprintf(
				'<a href="%s"%s>%s <span class="count">(%d)</span></a>',
				esc_url( add_query_arg( [ 'status' => 'trash' ], $base ) ),
				$is_trash_view ? ' class="current"' : '',
				esc_html__( 'Trash', 'wpforms-lite' ),
				(int) $this->counts['trash']
			);
		}

		return array_filter( $views );
	}

	/**
	 * Determine whether it is a passed view.
	 *
	 * @since 1.8.2
	 *
	 * @param string $view Current view to validate.
	 *
	 * @return bool
	 */
	private function is_current_view( $view ) {

		// phpcs:disable WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		if ( $view === 'trash' && isset( $_GET['status'] ) && $_GET['status'] === self::TRASH ) {
			return true;
		}

		if ( ( $view === 'search' || $view === 'all' ) && Search::is_search() ) {
			return ! isset( $_GET['status'] );
		}

		if ( ValueValidator::is_valid( $view, 'status' ) && isset( $_GET['status'] ) && $_GET['status'] === $view ) {
			return true;
		}

		if ( $view === 'all' && ! isset( $_GET['status'] ) && ! Search::is_search() ) {
			return true;
		}
		// phpcs:enable WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash

		return false;
	}

	/**
	 * Get value provided in search field.
	 *
	 * @since 1.8.2
	 *
	 * @return string
	 */
	private function get_search_query() {

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotValidated
		return Search::is_search() ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
	}

	/**
	 * Get search conditions.
	 *
	 * @since 1.8.2
	 *
	 * @return array
	 */
	private function get_search_conditions() {

		if ( ! Search::is_search() ) {
			return [];
		}

		return [
			'search_where' => $this->get_search_where_key(),
			'search_mode'  => $this->get_search_mode_key(),
		];
	}

	/**
	 * This function is responsible for determining whether the table items could be displayed.
	 *
	 * @since 1.8.4
	 */
	private function can_prepare_records() {

		// phpcs:disable WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		if ( isset( $_GET['form_id'] ) && get_post_status( $_GET['form_id'] ) !== 'publish' ) {
			wp_safe_redirect( Page::get_url() );
			exit;
		}

		if ( isset( $_GET['status'] ) && $_GET['status'] !== $this->get_valid_status_from_request() ) {
			wp_safe_redirect( Page::get_url() );
			exit;
		}

		if ( isset( $_GET['coupon_id'] ) && ! wpforms()->obj( 'payment_meta' )->is_valid_meta( 'coupon_id', absint( $_GET['coupon_id'] ) ) ) {
			wp_safe_redirect( Page::get_url() );
			exit;
		}

		// Validate the "type," "gateway," and "subscription_status" parameters.
		foreach ( [ 'type', 'gateway', 'subscription_status' ] as $column_name ) {
			// Leave the loop if the parameter is not set.
			if ( empty( $_GET[ $column_name ] ) ) {
				continue;
			}

			foreach ( explode( '|', $_GET[ $column_name ] ) as $value ) {
				if ( ! ValueValidator::is_valid( $value, $column_name ) ) {
					wp_safe_redirect( Page::get_url() );
					exit;
				}
			}
		}
		// phpcs:enable WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
	}

	/**
	 * Display table form's hidden fields.
	 *
	 * @since 1.8.2
	 */
	private function display_hidden_fields() {
		?>
		<input type="hidden" name="page" value="wpforms-payments">
		<input type="hidden" name="paged" value="1">
		<?php

		$this->display_status_hidden_field();
		$this->display_order_hidden_fields();
		$this->display_coupon_id_hidden_field();
		$this->display_form_id_hidden_field();
	}

	/**
	 * Display hidden field with status value.
	 *
	 * @since 1.8.2
	 */
	private function display_status_hidden_field() {

		$status = $this->get_valid_status_from_request();

		// Bail early if status is not valid.
		if ( ! $status ) {
			return;
		}

		// Output the hidden field.
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo wpforms_render(
			'admin/payments/hidden-field',
			[
				'name'  => 'status',
				'value' => $status,
			],
			true
		);
	}

	/**
	 * Display hidden fields with order and orderby values.
	 *
	 * @since 1.8.2
	 */
	private function display_order_hidden_fields() {

		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		foreach ( [ 'orderby', 'order' ] as $param ) {
			// Skip if param is not set.
			if ( empty( $_GET[ $param ] ) ) {
				continue;
			}

			// Output the hidden field.
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo wpforms_render(
				'admin/payments/hidden-field',
				[
					'name'  => $param,
					'value' => sanitize_text_field( wp_unslash( $_GET[ $param ] ) ),
				],
				true
			);
		}
		// phpcs:enable WordPress.Security.NonceVerification.Recommended
	}

	/**
	 * Display hidden field with coupon ID value.
	 *
	 * @since 1.8.4
	 */
	private function display_coupon_id_hidden_field() {

		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		if ( empty( $_GET['coupon_id'] ) ) {
			return;
		}

		// Output the hidden field.
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo wpforms_render(
			'admin/payments/hidden-field',
			[
				'name'  => 'coupon_id',
				'value' => absint( $_GET['coupon_id'] ),
			],
			true
		);
		// phpcs:enable WordPress.Security.NonceVerification.Recommended
	}

	/**
	 * Display hidden field with form ID value.
	 *
	 * @since 1.8.4
	 */
	private function display_form_id_hidden_field() {

		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		if ( empty( $_GET['form_id'] ) ) {
			return;
		}

		// Output the hidden field.
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo wpforms_render(
			'admin/payments/hidden-field',
			[
				'name'  => 'form_id',
				'value' => absint( $_GET['form_id'] ),
			],
			true
		);
		// phpcs:enable WordPress.Security.NonceVerification.Recommended
	}

	/**
	 * Get the coupon name from the coupon info.
	 *
	 * @since 1.8.4
	 *
	 * @param string $coupon_info Coupon information.
	 *
	 * @return string
	 */
	private function get_coupon_name_by_info( $coupon_info ) {

		// Extract the coupon code from the coupon info using regex.
		if ( preg_match( '/^(.+)/i', $coupon_info, $coupon_code ) ) {
			return $coupon_code[0];
		}

		return Helpers::get_placeholder_na_text();
	}

	/**
	 * Get the filterable statuses views for the overview table.
	 *
	 * @since 1.8.4
	 *
	 * @param string $base Base URL for the view links.
	 *
	 * @return array
	 */
	private function get_views_for_filterable_statuses( $base ) {

		$views    = [];
		$statuses = ValueValidator::get_allowed_one_time_statuses();

		// Remove the "Partially Refunded" status from the views.
		unset( $statuses['partrefund'] );

		foreach ( $statuses as $status => $label ) {
			// Skip if the count is zero and the view is not the current status.
			if ( ! $this->counts[ $status ] && ! $this->is_current_view( $status ) ) {
				continue;
			}

			// Add the view link to the $views array with the status as the key.
			$views[ $status ] = sprintf(
				'<a href="%s"%s>%s <span class="count">(%d)</span></a>',
				esc_url( add_query_arg( [ 'status' => $status ], $base ) ),
				$this->is_current_view( $status ) ? ' class="current"' : '',
				esc_html( $label ),
				(int) $this->counts[ $status ]
			);
		}

		return $views;
	}
}
