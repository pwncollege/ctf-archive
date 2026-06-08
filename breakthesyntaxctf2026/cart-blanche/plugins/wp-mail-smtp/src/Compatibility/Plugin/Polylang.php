<?php

namespace WPMailSMTP\Compatibility\Plugin;

/**
 * Polylang compatibility plugin.
 *
 * @since 4.6.0
 */
class Polylang extends PluginAbstract {

	/**
	 * Class constructor.
	 *
	 * @since 4.6.0
	 */
	public function __construct() { // phpcs:ignore WPForms.PHP.HooksMethod.InvalidPlaceForAddingHooks

		add_action( 'pll_init', [ $this, 'load' ], PHP_INT_MAX );
	}

	/**
	 * Get plugin name.
	 *
	 * @since 4.6.0
	 *
	 * @return string
	 */
	public static function get_name() {

		return 'Polylang';
	}

	/**
	 * Get plugin path.
	 *
	 * @since 4.6.0
	 *
	 * @return string
	 */
	public static function get_path() {

		return 'polylang/polylang.php';
	}

	/**
	 * Execute after the Polylang plugin is loaded.
	 *
	 * @since 4.6.0
	 */
	public function load() { // phpcs:ignore WPForms.PHP.HooksMethod.InvalidPlaceForAddingHooks

		if (
			! function_exists( 'PLL' ) ||
			! property_exists( PLL(), 'options' ) ||
			! isset( PLL()->options['force_lang'] ) ||
			PLL()->options['force_lang'] !== 3
		) {
			return;
		}

		// Use unfiltered site URL for multidomain setup.
		add_filter( 'wp_mail_smtp_wp_get_site_url_unfiltered', '__return_true' );
	}
}
