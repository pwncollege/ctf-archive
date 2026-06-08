<?php

// phpcs:disable Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpIllegalPsrClassPathInspection */
/** @noinspection AutoloadingIssuesInspection */
// phpcs:enable Generic.Commenting.DocComment.MissingShort

use WPForms\Helpers\DB;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handle plugin installation upon activation.
 *
 * @since 1.0.0
 */
class WPForms_Install {

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// When activated, trigger install method.
		register_activation_hook( WPFORMS_PLUGIN_FILE, [ $this, 'install' ] );
		register_deactivation_hook( WPFORMS_PLUGIN_FILE, [ $this, 'deactivate' ] );

		$this->hooks();
	}

	/**
	 * Hooks.
	 *
	 * @since 1.9.0
	 *
	 * @return void
	 */
	private function hooks() {

		// Watch for new multisite blogs.
		add_action( 'wp_initialize_site', [ $this, 'new_multisite_blog' ], 10, 2 );

		// Watch for delayed admin install.
		add_action( 'admin_init', [ $this, 'admin' ] );
	}

	/**
	 * Perform certain actions on plugin activation.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $network_wide Whether to enable the plugin for all sites in the network
	 *                           or just the current site. Multisite only. Default is false.
	 *
	 * @noinspection DisconnectedForeachInstructionInspection
	 */
	public function install( $network_wide = false ) {

		// Check if we are on multisite and network activating.
		if ( $network_wide && is_multisite() ) {

			// Multisite - go through each subsite and run the installer.
			$sites = get_sites(
				[
					'fields' => 'ids',
					'number' => 0,
				]
			);

			foreach ( $sites as $blog_id ) {
				switch_to_blog( $blog_id );
				$this->run();
				restore_current_blog();
			}
		} else {

			// Normal single site.
			$this->run();
		}

		set_transient( 'wpforms_just_activated', wpforms()->is_pro() ? 'pro' : 'lite', 60 );

		// Abort, so we only set the transient for single site installs.
		if ( isset( $_GET['activate-multi'] ) || is_network_admin() ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}

		// Add transient to trigger redirect to the Welcome screen.
		set_transient( 'wpforms_activation_redirect', true, 30 );
	}

	/**
	 * Run manual installation.
	 *
	 * @since 1.5.4.2
	 *
	 * @param bool $silent Silent install, disables welcome page.
	 */
	public function manual( $silent = false ) {

		$this->install( is_plugin_active_for_network( plugin_basename( WPFORMS_PLUGIN_FILE ) ) );

		if ( $silent ) {
			delete_transient( 'wpforms_activation_redirect' );
		}
	}

	/**
	 * Perform certain actions on plugin deactivation.
	 *
	 * @since 1.5.9
	 */
	public function deactivate() {

		// Unschedule all ActionScheduler actions by group.
		wpforms()->obj( 'tasks' )->cancel_all();

		// Remove plugin cron jobs.
		wp_clear_scheduled_hook( 'wpforms_email_summaries_cron' );

		// Check if the event is scheduled before attempting to clear it.
		// This event is only registered for the Lite edition of the plugin.
		// It's advisable to verify if the CRON event is scheduled using `wp_next_scheduled`.
		// This precaution ensures that you are not attempting to clear a scheduled
		// hook that may not exist, which could result in unexpected behavior.
		if ( wp_next_scheduled( 'wpforms_weekly_entries_count_cron' ) ) {
			wp_clear_scheduled_hook( 'wpforms_weekly_entries_count_cron' );
		}
	}

	/**
	 * Watch for delayed install procedure from WPForms admin.
	 *
	 * @since 1.5.4.2
	 */
	public function admin() {

		if ( ! is_admin() ) {
			return;
		}

		$install = get_option( 'wpforms_install', false );

		if ( empty( $install ) ) {
			return;
		}

		$this->manual( true );

		delete_option( 'wpforms_install' );
	}

	/**
	 * Run the actual installer.
	 *
	 * @since 1.5.4.2
	 */
	protected function run() {

		// Create custom database tables.
		$this->maybe_create_tables();

		// Hook for Pro users.
		/**
		 * Fires before WPForms plugin installation is performed.
		 *
		 * @since 1.3.0
		 */
		do_action( 'wpforms_install' );

		/*
		 * Set the current version to be referenced in future updates.
		 */
		// Used by Pro migrations.
		update_option( 'wpforms_version', WPFORMS_VERSION );
		// Used by Lite migrations.
		update_option( 'wpforms_version_lite', WPFORMS_VERSION );

		// Store the date when the initial activation was performed.
		$type      = class_exists( 'WPForms_Lite', false ) ? 'lite' : 'pro';
		$activated = (array) get_option( 'wpforms_activated', [] );

		if ( empty( $activated[ $type ] ) ) {
			$activated[ $type ] = time();

			update_option( 'wpforms_activated', $activated );
		}
	}

	/**
	 * When a new site is created in multisite, see if we are network activated,
	 * and if so run the installer.
	 *
	 * @since 1.3.0
	 * @since 1.8.4 Added $new_site and $args parameters and removed $blog_id, $user_id, $domain, $path, $site_id,
	 *              and $meta parameters.
	 *
	 * @param WP_Site $new_site New site object.
	 * @param array   $args     Arguments for the initialization.
	 *
	 * @noinspection PhpUnusedParameterInspection
	 * @noinspection PhpMissingParamTypeInspection
	 */
	public function new_multisite_blog( $new_site, $args ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed

		if ( is_plugin_active_for_network( plugin_basename( WPFORMS_PLUGIN_FILE ) ) ) {
			switch_to_blog( $new_site->blog_id );
			$this->run();
			restore_current_blog();
		}
	}

	/**
	 * Create database tables if they do not exist.
	 * It covers new installations.
	 *
	 * @since 1.8.2
	 */
	private function maybe_create_tables() {

		DB::create_custom_tables( true );
	}
}

new WPForms_Install();
