<?php

namespace WPMailSMTP\Compatibility\Plugin;

use SitePress;

/**
 * WPML compatibility plugin.
 *
 * @since 4.6.0
 */
class WPML extends PluginAbstract {

	/**
	 * Class constructor.
	 *
	 * @since 4.6.0
	 */
	public function __construct() { // phpcs:ignore WPForms.PHP.HooksMethod.InvalidPlaceForAddingHooks

		add_action( 'wpml_loaded', [ $this, 'load' ], PHP_INT_MAX );
	}

	/**
	 * Get plugin name.
	 *
	 * @since 4.6.0
	 *
	 * @return string
	 */
	public static function get_name() {

		return 'WPML Multilingual CMS';
	}

	/**
	 * Get plugin path.
	 *
	 * @since 4.6.0
	 *
	 * @return string
	 */
	public static function get_path() {

		return 'sitepress-multilingual-cms/sitepress.php';
	}

	/**
	 * Execute after the WPML plugin is loaded.
	 *
	 * @param SitePress $wpml The WPML instance.
	 *
	 * @since 4.6.0
	 */
	public function load( $wpml = null ) { // phpcs:ignore WPForms.PHP.HooksMethod.InvalidPlaceForAddingHooks

		if (
			! $wpml instanceof SitePress ||
			! method_exists( $wpml, 'get_setting' ) ||
			! defined( 'WPML_LANGUAGE_NEGOTIATION_TYPE_DOMAIN' ) ||
			WPML_LANGUAGE_NEGOTIATION_TYPE_DOMAIN !== (int) $wpml->get_setting( 'language_negotiation_type' )
		) {
			return;
		}

		// Use unfiltered site URL for multidomain setup.
		add_filter( 'wp_mail_smtp_wp_get_site_url_unfiltered', '__return_true' );
	}
}
