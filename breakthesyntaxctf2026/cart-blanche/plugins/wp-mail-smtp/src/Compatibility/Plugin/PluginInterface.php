<?php

namespace WPMailSMTP\Compatibility\Plugin;

/**
 * Compatibility plugin interface.
 *
 * @since 2.8.0
 */
interface PluginInterface {

	/**
	 * Is plugin can be loaded.
	 *
	 * @since 2.8.0
	 *
	 * @return bool
	 */
	public static function is_applicable();

	/**
	 * Is plugin activated.
	 *
	 * @since 2.8.0
	 *
	 * @return bool
	 */
	public static function is_activated();

	/**
	 * Execute after plugins loaded.
	 *
	 * @since 2.8.0
	 */
	public function after_plugins_loaded();

	/**
	 * Execute on init action in admin area.
	 *
	 * @since 2.8.0
	 */
	public function load_admin();

	/**
	 * Execute on init action.
	 *
	 * @since 2.8.0
	 */
	public function load();

	/**
	 * Get plugin name.
	 *
	 * @since 2.8.0
	 *
	 * @return string
	 */
	public static function get_name();

	/**
	 * Get plugin path.
	 *
	 * @since 2.8.0
	 *
	 * @return string
	 */
	public static function get_path();
}
