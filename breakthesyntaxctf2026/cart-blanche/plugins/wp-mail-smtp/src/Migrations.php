<?php

namespace WPMailSMTP;

use WP_Upgrader;
use WPMailSMTP\Admin\DebugEvents\Migration as DebugEventsMigration;
use WPMailSMTP\Queue\Migration as QueueMigration;

/**
 * Class Migrations.
 *
 * @since 4.0.0
 */
class Migrations {

	/**
	 * Register hooks.
	 *
	 * @since 4.0.0
	 */
	public function hooks() {

		// Initialize migrations during request in the admin panel only.
		add_action( 'admin_init', [ $this, 'init_migrations_on_request' ] );

		// Initialize migrations after plugin update.
		add_action( 'upgrader_process_complete', [ $this, 'init_migrations_after_upgrade' ], PHP_INT_MAX, 2 );
		add_action(
			'wp_ajax_nopriv_wp_mail_smtp_init_migrations',
			[ $this, 'init_migrations_ajax_handler' ]
		);
	}

	/**
	 * Initialize DB migrations during request.
	 *
	 * @since 4.0.0
	 */
	public function init_migrations_on_request() {

		// Do not initialize migrations during AJAX and cron requests.
		if ( WP::is_doing_ajax() || wp_doing_cron() ) {
			return;
		}

		$this->init_migrations();
	}

	/**
	 * Initialize DB migrations.
	 *
	 * @since 4.0.0
	 */
	private function init_migrations() {

		$migrations = $this->get_migrations();

		foreach ( $migrations as $migration ) {
			if ( is_subclass_of( $migration, MigrationAbstract::class ) && $migration::is_enabled() ) {
				( new $migration() )->init();
			}
		}
	}

	/**
	 * Get migrations classes.
	 *
	 * @since 4.0.0
	 *
	 * @return array Migrations classes.
	 */
	private function get_migrations() {

		$migrations = [
			Migration::class,
			DebugEventsMigration::class,
			QueueMigration::class,
		];

		/**
		 * Filters DB migrations classes.
		 *
		 * @deprecated 4.0.0
		 *
		 * @since 3.0.0
		 *
		 * @param array $migrations Migrations classes.
		 */
		$migrations = apply_filters_deprecated(
			'wp_mail_smtp_core_init_migrations',
			[ $migrations ],
			'3.10.0',
			'wp_mail_smtp_migrations_get_migrations'
		);

		/**
		 * Filters DB migrations classes.
		 *
		 * @since 4.0.0
		 *
		 * @param array $migrations Migrations classes.
		 */
		return apply_filters( 'wp_mail_smtp_migrations_get_migrations', $migrations );
	}

	/**
	 * Initialize DB migrations after plugin update.
	 * Initiate ajax call to perform the migration with the new plugin version code.
	 *
	 * @since 4.0.0
	 *
	 * @param WP_Upgrader $upgrader WP_Upgrader instance.
	 * @param array       $options  Array of update data.
	 */
	public function init_migrations_after_upgrade( $upgrader, $options ) {

		if (
			// Skip if in admin panel.
			( is_admin() && ! wp_doing_ajax() ) ||
			// Skip if it's update from plugins list page.
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			( wp_doing_ajax() && isset( $_REQUEST['action'] ) && $_REQUEST['action'] === 'update-plugin' )
		) {
			return;
		}

		$plugins = [];

		if ( isset( $options['plugins'] ) && is_array( $options['plugins'] ) ) {
			$plugins = $options['plugins'];
		} elseif ( isset( $options['plugin'] ) && is_string( $options['plugin'] ) ) {
			$plugins = [ $options['plugin'] ];
		}

		if (
			! in_array( 'wp-mail-smtp/wp_mail_smtp.php', $plugins, true ) &&
			! in_array( 'wp-mail-smtp-pro/wp_mail_smtp.php', $plugins, true )
		) {
			return;
		}

		$secret = wp_hash( 'wp_mail_smtp_init_migrations' . wp_salt() );

		$url = add_query_arg(
			[
				'action' => 'wp_mail_smtp_init_migrations',
			],
			admin_url( 'admin-ajax.php' )
		);

		$timeout = (int) ini_get( 'max_execution_time' );

		$args = [
			'sslverify' => false,
			'timeout'   => $timeout ? $timeout : 30,
			'body'      => [
				'secret' => $secret,
			],
		];

		wp_remote_post( $url, $args );
	}

	/**
	 * Initialize migrations via AJAX request.
	 *
	 * @since 4.0.0
	 */
	public function init_migrations_ajax_handler() {

		// Verify the secret hash since this is a nopriv endpoint.
		$secret   = ! empty( $_POST['secret'] ) ? sanitize_text_field( wp_unslash( $_POST['secret'] ) ) : '';
		$expected = wp_hash( 'wp_mail_smtp_init_migrations' . wp_salt() );

		if ( ! hash_equals( $expected, $secret ) ) {
			wp_send_json_error();
		}

		$this->init_migrations();

		wp_send_json_success();
	}
}
