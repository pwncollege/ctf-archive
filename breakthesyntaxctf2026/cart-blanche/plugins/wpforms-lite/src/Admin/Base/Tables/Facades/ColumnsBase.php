<?php

namespace WPForms\Admin\Base\Tables\Facades;

/**
 * Column facade class.
 *
 * Hides the complexity of columns' collection behind a simple interface.
 *
 * @since 1.8.6
 */
abstract class ColumnsBase {

	/**
	 * Get columns.
	 *
	 * Returns all possible columns.
	 *
	 * @since 1.8.6
	 *
	 * @return array Array of columns as objects.
	 */
	protected static function get_all(): array {

		return [];
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

		return [];
	}

	/**
	 * Check if the form has selected columns.
	 *
	 * @since 1.8.6
	 *
	 * @return bool
	 */
	public static function has_selected_columns(): bool {

		return ! empty( static::get_selected_columns_keys() );
	}

	/**
	 * Get columns' keys for the columns which the user has not selected to be displayed.
	 *
	 * It returns draggable and non-draggable columns.
	 *
	 * @since 1.8.6
	 *
	 * @return array
	 */
	public static function get_not_selected_columns_keys(): array {

		$selected = static::get_selected_columns_keys();
		$all      = array_keys( static::get_all() );

		return array_diff( $all, $selected );
	}

	/**
	 * Validate column key.
	 *
	 * @since 1.8.6
	 *
	 * @param string|int $key Column key.
	 *
	 * @return bool
	 */
	public static function validate_column_key( $key ): bool {

		return isset( static::get_all()[ $key ] );
	}
}
