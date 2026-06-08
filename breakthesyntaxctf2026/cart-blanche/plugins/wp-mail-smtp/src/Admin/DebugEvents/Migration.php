<?php

namespace WPMailSMTP\Admin\DebugEvents;

use WPMailSMTP\MigrationAbstract;

/**
 * Debug Events Migration Class
 *
 * @since 3.0.0
 */
class Migration extends MigrationAbstract {

	/**
	 * Version of the debug events database table.
	 *
	 * @since 3.0.0
	 */
	const DB_VERSION = 1;

	/**
	 * Option key where we save the current debug events DB version.
	 *
	 * @since 3.0.0
	 */
	const OPTION_NAME = 'wp_mail_smtp_debug_events_db_version';

	/**
	 * Option key where we save any errors while creating the debug events DB table.
	 *
	 * @since 3.0.0
	 */
	const ERROR_OPTION_NAME = 'wp_mail_smtp_debug_events_db_error';

	/**
	 * Create the debug events DB table structure.
	 *
	 * @since 3.0.0
	 */
	protected function migrate_to_1() {

		global $wpdb;

		$table           = DebugEvents::get_table_name();
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS `$table` (
		    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
		    `content` TEXT DEFAULT NULL,
		    `initiator` TEXT DEFAULT NULL,
		    `event_type` TINYINT UNSIGNED NOT NULL DEFAULT '0',
		    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
		    PRIMARY KEY (id)
		)
		ENGINE='InnoDB'
		{$charset_collate};";

		$result = $wpdb->query( $sql ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared

		if ( ! empty( $wpdb->last_error ) ) {
			update_option( self::ERROR_OPTION_NAME, $wpdb->last_error, false );
		}

		// Save the current version to DB.
		if ( $result !== false ) {
			$this->update_db_ver( 1 );
		}
	}
}
