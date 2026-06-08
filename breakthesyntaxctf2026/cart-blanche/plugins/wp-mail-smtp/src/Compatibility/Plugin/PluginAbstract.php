<?php

namespace WPMailSMTP\Compatibility\Plugin;

use WPMailSMTP\WP;

/**
 * Compatibility plugin.
 *
 * @since 2.8.0
 */
abstract class PluginAbstract implements PluginInterface {

	/**
	 * Class constructor.
	 *
	 * @since 2.8.0
	 */
	public function __construct() {

		add_action( 'init', [ $this, 'load' ], 0 );

		if ( WP::in_wp_admin() ) {
			add_action( 'init', [ $this, 'load_admin' ], 0 );
		}

		$this->after_plugins_loaded();
	}

	/**
	 * Is plugin can be loaded.
	 *
	 * @since 2.8.0
	 *
	 * @return bool
	 */
	public static function is_applicable() {

		return static::is_activated();
	}

	/**
	 * Is plugin activated.
	 *
	 * @since 2.8.0
	 *
	 * @return bool
	 */
	public static function is_activated() {

		return WP::is_plugin_activated( static::get_path() );
	}

	/**
	 * Execute after plugins loaded.
	 *
	 * @since 2.8.0
	 */
	public function after_plugins_loaded() {
	}

	/**
	 * Execute on init action in admin area.
	 *
	 * @since 2.8.0
	 */
	public function load_admin() {
	}

	/**
	 * Execute on init action.
	 *
	 * @since 2.8.0
	 */
	public function load() {
	}
}
