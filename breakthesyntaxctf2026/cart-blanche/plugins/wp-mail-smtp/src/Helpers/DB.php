<?php

namespace WPMailSMTP\Helpers;

/**
 * Class for Database functionality.
 *
 * @since 3.6.0
 */
class DB {

	/**
	 * The function is used to check if the given index exists in the given table.
	 *
	 * @since 3.6.0
	 *
	 * @param string $table The table name.
	 * @param string $index The index name.
	 *
	 * @return bool If index exists then return true else returns false.
	 */
	public static function index_exists( $table, $index ) {

		global $wpdb;

		$query = $wpdb->prepare(
			'SELECT COUNT(1) IndexIsThere
				FROM INFORMATION_SCHEMA.STATISTICS
				WHERE table_schema = DATABASE()
				AND table_name = %s
				AND index_name = %s',
			$table,
			$index
		);

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		$result = $wpdb->get_var( $query );

		return $result === '1';
	}
}
