<?php

namespace WPForms\Admin\Builder;

use WPForms\Helpers\CacheBase;

/**
 * Form Builder Help Cache.
 *
 * @since 1.8.2
 */
class HelpCache extends CacheBase {

	/**
	 * Remote source URL.
	 *
	 * @since 1.9.3
	 *
	 * @var string
	 */
	const REMOTE_SOURCE = 'https://wpformsapi.com/feeds/v1/docs/';

	/**
	 * Determine if the class is allowed to load.
	 *
	 * @since 1.8.2
	 *
	 * @return bool
	 */
	protected function allow_load() {

		if ( wp_doing_cron() || wpforms_doing_wp_cli() ) {
			return true;
		}

		if ( ! wpforms_current_user_can( [ 'create_forms', 'edit_forms' ] ) ) {
			return false;
		}

		return wpforms_is_admin_page( 'builder' );
	}

	/**
	 * Setup settings and other things.
	 *
	 * @since 1.8.2
	 */
	protected function setup() {

		return [
			'remote_source' => self::REMOTE_SOURCE,
			'cache_file'    => 'docs.json',
			/**
			 * Allow modifying Help Docs cache TTL (time to live).
			 *
			 * @since 1.6.3
			 *
			 * @param int $cache_ttl Cache TTL in seconds. Defaults to 1 week.
			 */
			'cache_ttl'     => (int) apply_filters( 'wpforms_admin_builder_help_cache_ttl', WEEK_IN_SECONDS ),
			'update_action' => 'wpforms_builder_help_cache_update',
		];
	}
}
