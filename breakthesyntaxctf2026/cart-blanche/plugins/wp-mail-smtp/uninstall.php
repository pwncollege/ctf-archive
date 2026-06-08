<?php
/**
 * Uninstall all WP Mail SMTP data.
 *
 * @since 1.3.0
 */

// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Prevent data removal if Pro plugin is active.
if ( is_plugin_active( 'wp-mail-smtp-pro/wp_mail_smtp.php' ) ) {
	return;
}

// Load plugin file.
require_once 'wp_mail_smtp.php';
require_once dirname( __FILE__ ) . '/vendor/woocommerce/action-scheduler/action-scheduler.php';

global $wpdb;

/*
 * Remove Legacy options.
 */
$options = [
	'_amn_smtp_last_checked',
	'pepipost_ssl',
	'pepipost_port',
	'pepipost_pass',
	'pepipost_user',
	'smtp_pass',
	'smtp_user',
	'smtp_auth',
	'smtp_ssl',
	'smtp_port',
	'smtp_host',
	'mail_set_return_path',
	'mailer',
	'mail_from_name',
	'mail_from',
];

/**
 * Remove AM announcement posts.
 */
$am_announcement_params = [
	'post_type'   => [ 'amn_smtp' ],
	'post_status' => 'any',
	'numberposts' => - 1,
	'fields'      => 'ids',
];

/**
 * Disable Action Schedule Queue Runner, to prevent a fatal error on the shutdown WP hook.
 */
if ( class_exists( 'ActionScheduler_QueueRunner' ) ) {
	$as_queue_runner = \ActionScheduler_QueueRunner::instance();

	if ( method_exists( $as_queue_runner, 'unhook_dispatch_async_request' ) ) {
		$as_queue_runner->unhook_dispatch_async_request();
	}
}

// WP MS uninstall process.
//phpcs:disable WPForms.Formatting.EmptyLineAfterAssigmentVariables.AddEmptyLine, WPForms.PHP.BackSlash.UseShortSyntax
if ( is_multisite() ) {
	$main_site_settings = get_blog_option( get_main_site_id(), 'wp_mail_smtp', [] );
	$network_wide       = ! empty( $main_site_settings['general']['network_wide'] );
	$network_uninstall  = ! empty( $main_site_settings['general']['uninstall'] );

	$sites = get_sites();

	foreach ( $sites as $site ) {
		$settings = get_blog_option( $site->blog_id, 'wp_mail_smtp', [] );

		// Confirm network site admin has decided to remove all data, otherwise skip.
		if (
			( $network_wide && ! $network_uninstall ) ||
			( ! $network_wide && empty( $settings['general']['uninstall'] ) )
		) {
			continue;
		}

		/*
		 * Delete network site plugin options.
		 */
		foreach ( $options as $option ) {
			delete_blog_option( $site->blog_id, $option );
		}

		// Switch to the current network site.
		switch_to_blog( $site->blog_id );

		// Delete plugin settings.
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'wp\_mail\_smtp%'" ); // phpcs:ignore WordPress.DB

		// Delete plugin user meta.
		$wpdb->query( "DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE 'wp\_mail\_smtp\_%'" ); // phpcs:ignore WordPress.DB

		// Remove any transients we've left behind.
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '\_transient\_wp\_mail\_smtp\_%'" ); // phpcs:ignore WordPress.DB
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '\_site\_transient\_wp\_mail\_smtp\_%'" ); // phpcs:ignore WordPress.DB
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '\_transient\_timeout\_wp\_mail\_smtp\_%'" ); // phpcs:ignore WordPress.DB
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '\_site\_transient\_timeout\_wp\_mail\_smtp\_%'" ); // phpcs:ignore WordPress.DB

		// Delete debug events table.
		$debug_events_table = \WPMailSMTP\Admin\DebugEvents\DebugEvents::get_table_name();
		$wpdb->query( "DROP TABLE IF EXISTS $debug_events_table;" ); // phpcs:ignore WordPress.DB

		/*
		 * Delete network site product announcements.
		 */
		$announcements = get_posts( $am_announcement_params );

		if ( ! empty( $announcements ) ) {
			foreach ( $announcements as $announcement ) {
				wp_delete_post( $announcement, true );
			}
		}

		// Delete queue table.
		$queue_table = \WPMailSMTP\Queue\Queue::get_table_name();
		$wpdb->query( "DROP TABLE IF EXISTS $queue_table;" ); // phpcs:ignore WordPress.DB

		// Delete all queue attachments.
		( new \WPMailSMTP\Queue\Attachments() )->delete_attachments();

		/*
		 * Cleanup network site data for Pro plugin only.
		 */
		if (
			function_exists( 'wp_mail_smtp' ) &&
			is_readable( wp_mail_smtp()->plugin_path . '/src/Pro/Pro.php' )
		) {

			// Delete logs table.
			$table = \WPMailSMTP\Pro\Emails\Logs\Logs::get_table_name();
			$wpdb->query( "DROP TABLE IF EXISTS $table;" ); // phpcs:ignore WordPress.DB

			// Delete attachments tables.
			$attachment_files_table = \WPMailSMTP\Pro\Emails\Logs\Attachments\Attachments::get_attachment_files_table_name();
			$wpdb->query( "DROP TABLE IF EXISTS $attachment_files_table;" ); // phpcs:ignore WordPress.DB

			$email_attachments_table = \WPMailSMTP\Pro\Emails\Logs\Attachments\Attachments::get_email_attachments_table_name();
			$wpdb->query( "DROP TABLE IF EXISTS $email_attachments_table;" ); // phpcs:ignore WordPress.DB

			// Delete all attachments if any.
			( new \WPMailSMTP\Pro\Emails\Logs\Attachments\Attachments() )->delete_all_attachments();

			// Delete tracking tables.
			$tracking_events_table = \WPMailSMTP\Pro\Emails\Logs\Tracking\Tracking::get_events_table_name();
			$wpdb->query( "DROP TABLE IF EXISTS $tracking_events_table;" ); // phpcs:ignore WordPress.DB

			$tracking_links_table = \WPMailSMTP\Pro\Emails\Logs\Tracking\Tracking::get_links_table_name();
			$wpdb->query( "DROP TABLE IF EXISTS $tracking_links_table;" ); // phpcs:ignore WordPress.DB
		}

		/*
		 * Drop all Action Scheduler data and unschedule all plugin ActionScheduler actions.
		 */
		( new \WPMailSMTP\Tasks\Tasks() )->remove_all();

		$meta_table = \WPMailSMTP\Tasks\Meta::get_table_name();
		$wpdb->query( "DROP TABLE IF EXISTS $meta_table;" ); // phpcs:ignore WordPress.DB

		// Delete current sub-site wp-mail-smtp uploads folder.
		\WPMailSMTP\Uploads::delete_upload_dir();

		// Restore the current network site back to the original one.
		restore_current_blog();
	}
} else { // Non WP MS uninstall process (for normal WP installs).

	// Confirm user has decided to remove all data, otherwise stop.
	$settings = get_option( 'wp_mail_smtp', [] );
	if ( empty( $settings['general']['uninstall'] ) ) {
		return;
	}

	/*
	 * Delete plugin options.
	 */
	foreach ( $options as $option ) {
		delete_option( $option );
	}

	// Delete plugin settings.
	$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'wp\_mail\_smtp%'" ); // phpcs:ignore WordPress.DB

	// Delete plugin user meta.
	$wpdb->query( "DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE 'wp\_mail\_smtp\_%'" ); // phpcs:ignore WordPress.DB

	// Remove any transients we've left behind.
	$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '\_transient\_wp\_mail\_smtp\_%'" ); // phpcs:ignore WordPress.DB
	$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '\_site\_transient\_wp\_mail\_smtp\_%'" ); // phpcs:ignore WordPress.DB
	$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '\_transient\_timeout\_wp\_mail\_smtp\_%'" ); // phpcs:ignore WordPress.DB
	$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '\_site\_transient\_timeout\_wp\_mail\_smtp\_%'" ); // phpcs:ignore WordPress.DB

	// Delete debug events table.
	$debug_events_table = \WPMailSMTP\Admin\DebugEvents\DebugEvents::get_table_name();
	$wpdb->query( "DROP TABLE IF EXISTS $debug_events_table;" ); // phpcs:ignore WordPress.DB

	/*
	 * Remove product announcements.
	 */
	$announcements = get_posts( $am_announcement_params );
	if ( ! empty( $announcements ) ) {
		foreach ( $announcements as $announcement ) {
			wp_delete_post( $announcement, true );
		}
	}

	// Delete queue table.
	$queue_table = \WPMailSMTP\Queue\Queue::get_table_name();
	$wpdb->query( "DROP TABLE IF EXISTS $queue_table;" ); // phpcs:ignore WordPress.DB

	// Delete all queue attachments.
	( new \WPMailSMTP\Queue\Attachments() )->delete_attachments();

	/*
	 * Cleanup data for Pro plugin only.
	 */
	if (
		function_exists( 'wp_mail_smtp' ) &&
		is_readable( wp_mail_smtp()->plugin_path . '/src/Pro/Pro.php' )
	) {

		// Delete logs table.
		$table = \WPMailSMTP\Pro\Emails\Logs\Logs::get_table_name();
		$wpdb->query( "DROP TABLE IF EXISTS $table;" ); // phpcs:ignore WordPress.DB

		// Delete attachments tables.
		$attachment_files_table = \WPMailSMTP\Pro\Emails\Logs\Attachments\Attachments::get_attachment_files_table_name();
		$wpdb->query( "DROP TABLE IF EXISTS $attachment_files_table;" ); // phpcs:ignore WordPress.DB

		$email_attachments_table = \WPMailSMTP\Pro\Emails\Logs\Attachments\Attachments::get_email_attachments_table_name();
		$wpdb->query( "DROP TABLE IF EXISTS $email_attachments_table;" ); // phpcs:ignore WordPress.DB

		// Delete all attachments if any.
		( new \WPMailSMTP\Pro\Emails\Logs\Attachments\Attachments() )->delete_all_attachments();

		// Delete tracking tables.
		$tracking_events_table = \WPMailSMTP\Pro\Emails\Logs\Tracking\Tracking::get_events_table_name();
		$wpdb->query( "DROP TABLE IF EXISTS $tracking_events_table;" ); // phpcs:ignore WordPress.DB

		$tracking_links_table = \WPMailSMTP\Pro\Emails\Logs\Tracking\Tracking::get_links_table_name();
		$wpdb->query( "DROP TABLE IF EXISTS $tracking_links_table;" ); // phpcs:ignore WordPress.DB
	}

	/*
	 * Drop all Action Scheduler data and unschedule all plugin ActionScheduler actions.
	 */
	( new \WPMailSMTP\Tasks\Tasks() )->remove_all();

	$meta_table = \WPMailSMTP\Tasks\Meta::get_table_name();
	$wpdb->query( "DROP TABLE IF EXISTS $meta_table;" ); // phpcs:ignore WordPress.DB

	// Delete wp-mail-smtp uploads folder.
	\WPMailSMTP\Uploads::delete_upload_dir();
}
//phpcs:enable WPForms.Formatting.EmptyLineAfterAssigmentVariables.AddEmptyLine, WPForms.PHP.BackSlash.UseShortSyntax
