<?php

// phpcs:ignore Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpUnused */

namespace WPForms\Migrations;

use WPForms\Helpers\Transient;

/**
 * Class v1.8.2 upgrade.
 *
 * @since 1.8.2
 */
class Upgrade182 extends UpgradeBase {

	/**
	 * Run upgrade.
	 *
	 * @since 1.8.2
	 *
	 * @return bool|null Upgrade result:
	 *                   true  - the upgrade completed successfully,
	 *                   false - in the case of failure,
	 *                   null  - upgrade started but not yet finished (background task).
	 */
	public function run() {

		$cache_dir           = $this->get_cache_dir();
		$templates_cache_dir = $cache_dir . 'templates/';

		$this->set_cache_time( $cache_dir, 'addons.json', 'wpforms_admin_addons_cache_ttl' );
		$this->set_cache_time( $cache_dir, 'docs.json', 'wpforms_admin_builder_help_cache_ttl' );
		$this->set_cache_time( $cache_dir, 'templates.json', 'wpforms_admin_builder_templates_cache_ttl' );

		$files = glob( $templates_cache_dir . '*.json' );

		foreach ( $files as $filename ) {
			$this->set_cache_time( $templates_cache_dir, basename( $filename ), 'wpforms_admin_builder_templates_cache_ttl' );
		}

		return true;
	}

	/**
	 * Set cache time to transient.
	 *
	 * @since 1.8.2
	 *
	 * @param string $cache_dir  Cache directory.
	 * @param string $cache_file Cache filename.
	 * @param string $filter     Filter name.
	 *
	 * @return void
	 */
	private function set_cache_time( $cache_dir, $cache_file, $filter ) {

		// phpcs:ignore WPForms.Comments.PHPDocHooks.RequiredHookDocumentation, WPForms.PHP.ValidateHooks.InvalidHookName, WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
		$cache_ttl           = (int) apply_filters( $filter, WEEK_IN_SECONDS );
		$cache_file_path     = $cache_dir . $cache_file;
		$cache_modified_time = 0;
		$transient           = $cache_file;
		$time                = time();

		if ( is_file( $cache_file_path ) && is_readable( $cache_file_path ) ) {
			clearstatcache( true, $cache_file_path );

			// On WPVIP and similar filesystems, filemtime() could return false.
			$cache_modified_time = (int) filemtime( $cache_file_path );
		}

		if ( $cache_modified_time === 0 || $cache_modified_time + $cache_ttl <= $time ) {
			// Do not set transient for non-existing or expired cache.
			return;
		}

		$expiration = $cache_modified_time + $cache_ttl - $time;

		Transient::set( $transient, $cache_modified_time, $expiration );
	}

	/**
	 * Get cache directory path.
	 * Copy of the CacheBase method.
	 *
	 * @since 1.8.2
	 */
	private function get_cache_dir() {

		static $cache_dir;

		if ( $cache_dir ) {
			/**
			 * Since wpforms_upload_dir() relies on hooks, and hooks can be added unpredictably,
			 * we need to cache the result of this method.
			 * Otherwise, it is the risk to save cache file to one dir and try to get from another.
			 */
			return $cache_dir;
		}

		$upload_dir  = wpforms_upload_dir();
		$upload_path = ! empty( $upload_dir['path'] )
			? trailingslashit( wp_normalize_path( $upload_dir['path'] ) )
			: trailingslashit( WP_CONTENT_DIR ) . 'uploads/wpforms/';

		$cache_dir = $upload_path . 'cache/';

		return $cache_dir;
	}
}
