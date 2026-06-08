<?php
/**
 * Uninstall WPForms.
 *
 * Remove:
 * - Entries table
 * - Entry Meta table
 * - Entry fields table
 * - Form Preview page
 * - wpforms_log post type posts and post_meta
 * - wpforms post type posts and post_meta
 * - WPForms settings/options
 * - WPForms user meta
 * - WPForms term meta
 * - WPForms Uploads
 *
 * @since 1.4.5
 *
 * @var WP_Filesystem_Base $wp_filesystem
 */

// Exit if accessed directly.
use WPForms\Db\Payments\Meta as PaymentsMeta;
use WPForms\Db\Payments\Payment;
use WPForms\Logger\Repository;
use WPForms\Tasks\Meta as TasksMeta;
use WPForms\Tasks\Tasks;

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Load plugin file.
require_once 'wpforms.php';

// Disable Action Schedule Queue Runner.
if ( class_exists( 'ActionScheduler_QueueRunner' ) ) {
	ActionScheduler_QueueRunner::instance()->unhook_dispatch_async_request();
}

// Confirm user has decided to remove all data, otherwise stop.
$settings = get_option( 'wpforms_settings', [] );

if (
	empty( $settings['uninstall-data'] ) ||
	is_plugin_active( 'wpforms/wpforms.php' ) ||
	is_plugin_active( 'wpforms-lite/wpforms.php' )
) {
	return;
}

global $wpdb;

// phpcs:disable WordPress.DB.DirectDatabaseQuery

// Delete entries table.
$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'wpforms_entries' );

// Delete entry meta table.
$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'wpforms_entry_meta' );

// Delete entry fields table.
$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'wpforms_entry_fields' );

// Delete payments table.
// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
$wpdb->query( 'DROP TABLE IF EXISTS ' . Payment::get_table_name() );

// Delete payment meta table.
// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
$wpdb->query( 'DROP TABLE IF EXISTS ' . PaymentsMeta::get_table_name() );

// Delete tasks meta table.
// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
$wpdb->query( 'DROP TABLE IF EXISTS ' . TasksMeta::get_table_name() );

// Delete logger table.
// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
$wpdb->query( 'DROP TABLE IF EXISTS ' . Repository::get_table_name() );

// Delete file restrictions table.
// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'wpforms_file_restrictions' );

// Delete protected files table.
// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'wpforms_protected_files' );

/**
 * Delete tables that might be created by "Add-ons".
 *
 * 1. Form Locker.
 * 2. User Journey.
 * 3. Coupons.
 * 4. Entry Automation.
 */
$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'wpforms_form_locker_email_verification' );
$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'wpforms_user_journey' );
$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'wpforms_coupons' );
$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'wpforms_coupons_forms' );
$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'wpforms_entry_automation_tasks' );

// Delete Preview page.
$preview_page = get_option( 'wpforms_preview_page', false );

if ( ! empty( $preview_page ) ) {
	wp_delete_post( $preview_page, true );
}

// Delete wpforms, wpforms-template and wpforms_log post type posts/post_meta.
$wpforms_posts = get_posts(
	[
		'post_type'   => [ 'wpforms_log', 'wpforms', 'wpforms-template' ],
		'post_status' => [ 'any', 'trash', 'auto-draft' ],
		'numberposts' => -1,
		'fields'      => 'ids',
	]
);

if ( $wpforms_posts ) {
	foreach ( $wpforms_posts as $wpforms_post ) {
		wp_delete_post( $wpforms_post, true );
	}
}

// Delete all the plugin settings.
$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE 'wpforms\_%'" );

// Delete widget settings.
$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE 'widget\_wpforms%'" );

// Delete options from the previous version of the Notifications functionality.
$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '\_amn\_wpforms\_%'" );

// Delete plugin user meta.
$wpdb->query( "DELETE FROM $wpdb->usermeta WHERE meta_key LIKE 'wpforms\_%'" );

// Delete plugin term meta.
$wpdb->query( "DELETE FROM $wpdb->termmeta WHERE meta_key LIKE 'wpforms\_%'" );

// Remove any transients we've left behind.
$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '\_transient\_wpforms\_%'" );
$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '\_site\_transient\_wpforms\_%'" );
$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '\_transient\_timeout\_wpforms\_%'" );
$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '\_site\_transient\_timeout\_wpforms\_%'" );
$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '\_wpforms\_transient\_%'" );

global $wp_filesystem;

// Remove uploaded files.
$uploads_directory = wp_upload_dir();

if ( empty( $uploads_directory['error'] ) ) {
	$wp_filesystem->rmdir( $uploads_directory['basedir'] . '/wpforms/', true );
}

// Remove translation files.
$languages_directory = defined( 'WP_LANG_DIR' ) ? trailingslashit( WP_LANG_DIR ) : trailingslashit( WP_CONTENT_DIR ) . 'languages/';
$translations        = glob( wp_normalize_path( $languages_directory . 'plugins/wpforms-*' ) );

if ( ! empty( $translations ) ) {
	foreach ( $translations as $file ) {
		$wp_filesystem->delete( $file );
	}
}

// Remove plugin cron jobs.
wp_clear_scheduled_hook( 'wpforms_email_summaries_cron' );

// Check if the event is scheduled before attempting to clear it.
// This event is only registered for the Lite edition of the plugin.
if ( wp_next_scheduled( 'wpforms_weekly_entries_count_cron' ) ) {
	wp_clear_scheduled_hook( 'wpforms_weekly_entries_count_cron' );
}

// Un-schedule all plugin ActionScheduler actions.
// Don't use wpforms() because 'tasks' in core are registered on `init` hook,
// which is not executed on uninstallation.
( new Tasks() )->cancel_all();
