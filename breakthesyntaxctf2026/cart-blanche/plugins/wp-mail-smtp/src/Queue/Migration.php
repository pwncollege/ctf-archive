<?php

namespace WPMailSMTP\Queue;

use WPMailSMTP\MigrationAbstract;

/**
 * Class Migration.
 *
 * @since 4.0.0
 */
class Migration extends MigrationAbstract {

	/**
	 * Version of the database table(s) for queue functionality.
	 *
	 * @since 4.0.0
	 */
	const DB_VERSION = 1;

	/**
	 * Option key where we save the current DB version for queue functionality.
	 *
	 * @since 4.0.0
	 */
	const OPTION_NAME = 'wp_mail_smtp_queue_db_version';

	/**
	 * Option key where we save any errors while creating the queue DB table.
	 *
	 * @since 4.0.0
	 */
	const ERROR_OPTION_NAME = 'wp_mail_smtp_queue_db_error';

	/**
	 * Whether the queue is enabled.
	 *
	 * @since 4.0.0
	 *
	 * @return bool
	 */
	public static function is_enabled() {

		return wp_mail_smtp()->get_queue()->is_enabled();
	}

	/**
	 * Initial migration - create the table structure.
	 *
	 * @since 4.0.0
	 */
	protected function migrate_to_1() {

		global $wpdb;

		$table   = Queue::get_table_name();
		$collate = ! empty( $wpdb->collate ) ? "COLLATE='{$wpdb->collate}'" : '';

		/*
		 * Create the table.
		 */
		$sql = "
		CREATE TABLE `$table` (
		    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
		    `data` LONGTEXT NULL,
		    `status` TINYINT UNSIGNED NOT NULL DEFAULT '0',
		    `date_enqueued` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
		    `date_processed` TIMESTAMP NULL,
		    PRIMARY KEY (id),
		    INDEX status (status),
		    INDEX date_processed (date_processed)
		)
		ENGINE='InnoDB'
		{$collate};";

		$result = $wpdb->query( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching

		if ( ! empty( $wpdb->last_error ) ) {
			update_option( self::ERROR_OPTION_NAME, $wpdb->last_error, false );
		}

		// Save the current version to DB.
		if ( $result !== false ) {
			$this->update_db_ver( 1 );
		}
	}
}
