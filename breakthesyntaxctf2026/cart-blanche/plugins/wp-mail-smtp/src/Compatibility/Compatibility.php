<?php

namespace WPMailSMTP\Compatibility;

use WPMailSMTP\Compatibility\Plugin\WPML;
use WPMailSMTP\Compatibility\Plugin\WPForms;
use WPMailSMTP\Compatibility\Plugin\Polylang;
use WPMailSMTP\Compatibility\Plugin\Admin2020;
use WPMailSMTP\Compatibility\Plugin\PolylangPro;
use WPMailSMTP\Compatibility\Plugin\WooCommerce;
use WPMailSMTP\Compatibility\Plugin\WPFormsLite;
use WPMailSMTP\Compatibility\Plugin\PluginAbstract;

/**
 * Compatibility.
 * Class for managing compatibility with other plugins.
 *
 * @since 2.8.0
 */
class Compatibility {

	/**
	 * Initialized compatibility plugins.
	 *
	 * @since 2.8.0
	 *
	 * @var array
	 */
	protected $plugins = [];

	/**
	 * Initialize class.
	 *
	 * @since 2.8.0
	 */
	public function init() {

		$this->setup_compatibility();
	}

	/**
	 * Setup compatibility plugins.
	 *
	 * @since 2.8.0
	 */
	public function setup_compatibility() {

		$plugins = [
			'admin-2020'   => Admin2020::class,
			'wpforms-lite' => WPFormsLite::class,
			'wpforms'      => WPForms::class,
			'woocommerce'  => WooCommerce::class,
			'wpml'         => WPML::class,
			'polylang'     => Polylang::class,
			'polylang-pro' => PolylangPro::class,
		];

		foreach ( $plugins as $key => $classname ) {
			if ( class_exists( $classname ) && is_callable( [ $classname, 'is_applicable' ] ) ) {
				if ( $classname::is_applicable() ) {
					$this->plugins[ $key ] = new $classname();
				}
			}
		}
	}

	/**
	 * Get compatibility plugin.
	 *
	 * @since 2.8.0
	 *
	 * @param string $key Plugin key.
	 *
	 * @return PluginAbstract|false
	 */
	public function get_plugin( $key ) {

		return isset( $this->plugins[ $key ] ) ? $this->plugins[ $key ] : false;
	}
}
