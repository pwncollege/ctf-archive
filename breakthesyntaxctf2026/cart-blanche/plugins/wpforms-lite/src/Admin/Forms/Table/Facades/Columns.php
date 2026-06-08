<?php

namespace WPForms\Admin\Forms\Table\Facades;

use WPForms\Admin\Base\Tables\Facades\ColumnsBase;
use WPForms\Admin\Forms\Table\DataObjects\Column;
use WPForms\Integrations\LiteConnect\LiteConnect;

/**
 * Column facade class.
 *
 * Hides the complexity of columns' collection behind a simple interface.
 *
 * @since 1.8.6
 */
class Columns extends ColumnsBase {

	/**
	 * Saved columns order user meta name.
	 *
	 * @since 1.8.6
	 */
	const COLUMNS_USER_META_NAME = 'wpforms_overview_table_columns';

	/**
	 * Legacy saved columns order user meta name.
	 *
	 * @since 1.8.6
	 */
	const LEGACY_COLUMNS_USER_META_NAME = 'managetoplevel_page_wpforms-overviewcolumnshidden';

	/**
	 * Get columns.
	 *
	 * Returns all possible columns for the Forms table.
	 *
	 * @since 1.8.6
	 *
	 * @return Column[] Array of columns as objects.
	 */
	protected static function get_all(): array {

		static $columns = null;

		if ( ! $columns ) {
			$columns = self::get_columns();
		}

		return $columns;
	}

	/**
	 * Get forms' list table columns.
	 *
	 * @since 1.8.6
	 *
	 * @return Column[] Array of columns as objects.
	 */
	public static function get_columns(): array {

		$columns_data = [
			'id'        => [
				'label' => esc_html__( 'ID', 'wpforms-lite' ),
			],
			'name'      => [
				'label'    => esc_html__( 'Name', 'wpforms-lite' ),
				'readonly' => true,
			],
			'tags'      => [
				'label' => esc_html__( 'Tags', 'wpforms-lite' ),
			],
			'author'    => [
				'label' => esc_html__( 'Author', 'wpforms-lite' ),
			],
			'shortcode' => [
				'label' => esc_html__( 'Shortcode', 'wpforms-lite' ),
			],
			'created'   => [
				'label' => esc_html__( 'Date', 'wpforms-lite' ),
			],
			'entries'   => [
				'label' => esc_html__( 'Entries', 'wpforms-lite' ),
			],
		];

		// In Lite, we should not show Entries column if Lite Connect is not enabled.
		if ( ! wpforms()->is_pro() && ! ( LiteConnect::is_allowed() && LiteConnect::is_enabled() ) ) {
			unset( $columns_data['entries'] );
		}

		/**
		 * Filters the forms overview table columns data.
		 *
		 * @since 1.8.6
		 *
		 * @param array[] $columns Columns data.
		 */
		$columns_data = apply_filters( 'wpforms_admin_forms_table_facades_columns_data', $columns_data );

		$columns_data = self::set_columns_data_defaults( $columns_data );

		$columns = [];

		foreach ( $columns_data as $id => $column ) {
			$columns[ $id ] = new Column( $id, $column );
		}

		return $columns;
	}

	/**
	 * Get columns' keys for the columns which user selected to be displayed.
	 *
	 * It returns an array of keys in the order they should be displayed.
	 * It returns draggable and non-draggable columns.
	 *
	 * @since 1.8.6
	 *
	 * @return array
	 */
	public static function get_selected_columns_keys(): array {

		$user_id = get_current_user_id();

		$user_meta_columns               = get_user_meta( $user_id, self::COLUMNS_USER_META_NAME, true );
		$user_meta_legacy_columns_hidden = get_user_meta( $user_id, self::LEGACY_COLUMNS_USER_META_NAME, true );

		$user_meta_columns               = $user_meta_columns ? $user_meta_columns : [];
		$user_meta_legacy_columns_hidden = $user_meta_legacy_columns_hidden ? $user_meta_legacy_columns_hidden : [];

		// Make form id column hidden by default.
		if ( empty( $user_meta_columns ) ) {
			$user_meta_legacy_columns_hidden[] = 'id';
		}

		// Always include readonly columns.
		$user_meta_columns = array_unique( array_merge( $user_meta_columns, self::get_readonly_columns_keys() ) );

		if ( ! empty( $user_meta_columns ) && empty( $user_meta_legacy_columns_hidden ) ) {
			return $user_meta_columns;
		}

		// If custom order is not saved, let's check if there is a legacy user meta-option.
		// It is a kind of migration from legacy user meta-option to the new one.
		$user_meta_columns = array_diff( array_keys( self::get_all() ), $user_meta_legacy_columns_hidden );

		// Update user meta option.
		if ( update_user_meta( $user_id, self::COLUMNS_USER_META_NAME, $user_meta_columns ) ) {
			// Remove legacy user meta-option.
			delete_user_meta( $user_id, self::LEGACY_COLUMNS_USER_META_NAME );
		}

		return $user_meta_columns;
	}

	/**
	 * Get draggable columns ordered keys.
	 *
	 * It will return custom order if user has already saved it, otherwise it will return default order.
	 *
	 * @since 1.8.6
	 *
	 * @return array
	 */
	private static function get_draggable_ordered_keys(): array {

		// First, let's check if user has already saved custom order.
		$custom_order = self::get_selected_columns_keys();
		$all_columns  = self::get_all();

		if ( $custom_order ) {
			// If a user has saved custom order, let's filter out columns which are not draggable.
			return array_filter(
				$custom_order,
				static function ( $column ) use ( $all_columns ) {

					return isset( $all_columns[ $column ] ) && $all_columns[ $column ]->is_draggable();
				}
			);
		}

		// If a user has not saved custom order, let's use the default order.
		$draggable = array_filter(
			$all_columns,
			static function ( $column ) {

				return $column->is_draggable();
			}
		);

		return array_keys( $draggable );
	}

	/**
	 * Save columns keys array into user meta.
	 *
	 * @since 1.8.6
	 *
	 * @param array $columns_keys Array of columns keys in desired display order.
	 *
	 * @return bool
	 */
	public static function sanitize_and_save_columns( array $columns_keys ): bool {

		$columns_keys = array_map( [ __CLASS__, 'sanitize_column_key' ], $columns_keys );
		$columns_keys = array_filter( $columns_keys, [ __CLASS__, 'validate_column_key' ] );

		// Add readonly columns.
		$columns_keys = array_unique( array_merge( $columns_keys, self::get_readonly_columns_keys() ) );

		$user_id           = get_current_user_id();
		$user_meta_columns = get_user_meta( $user_id, self::COLUMNS_USER_META_NAME, true );

		// If user has already saved custom order, let's check if it has been changed.
		if ( $user_meta_columns === $columns_keys ) {
			return true;
		}

		// Update user meta option.
		return update_user_meta( $user_id, self::COLUMNS_USER_META_NAME, $columns_keys );
	}

	/**
	 * Sanitize column key.
	 *
	 * @since 1.8.6
	 *
	 * @param string $key Column key.
	 *
	 * @return string
	 */
	public static function sanitize_column_key( string $key ): string {

		return sanitize_key( $key );
	}

	/**
	 * Get columns' data ready to use in the list table object.
	 *
	 * @since 1.8.6
	 *
	 * @return array
	 */
	public static function get_list_table_columns(): array {

		$columns = [
			'cb' => '<input type="checkbox" />',
		];

		$order       = self::get_draggable_ordered_keys();
		$all_columns = self::get_all();

		foreach ( $order as $column_id ) {
			$columns[ $column_id ] = $all_columns[ $column_id ]->get_label_html();
		}

		/**
		 * Filters the forms overview table columns.
		 *
		 * @since 1.0.0
		 *
		 * @param array $columns Columns data.
		 */
		$columns = apply_filters( 'wpforms_overview_table_columns', $columns ); // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName

		// Add empty column for the cog icon.
		$columns['cog'] = '';

		return $columns;
	}

	/**
	 * Get readonly columns keys.
	 *
	 * @since 1.8.6
	 *
	 * @return array
	 */
	private static function get_readonly_columns_keys(): array {

		$readonly = array_filter(
			self::get_all(),
			static function ( $column ) {

				return $column->is_readonly();
			}
		);

		return array_keys( $readonly );
	}

	/**
	 * Set columns data defaults.
	 *
	 * @since 1.8.6
	 *
	 * @param array $columns_data Columns data.
	 *
	 * @return array
	 */
	private static function set_columns_data_defaults( array $columns_data ): array {

		return array_map(
			static function ( $column ) {
				$column['type']       = $column['type'] ?? '';
				$column['draggable']  = $column['draggable'] ?? true;
				$column['label_html'] = $column['label_html'] ?? '';
				$column['readonly']   = $column['readonly'] ?? false;

				return $column;
			},
			$columns_data
		);
	}
}
