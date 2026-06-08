<?php

namespace WPMailSMTP\Admin\Pages;

use WPMailSMTP\Admin\ParentPageAbstract;

/**
 * About parent page.
 *
 * @since 1.5.0
 * @since 2.9.0 changed parent class from PageAbstract to ParentPageAbstract.
 */
class About extends ParentPageAbstract {

	/**
	 * Slug of a page.
	 *
	 * @since 1.5.0
	 *
	 * @var string Slug of a page.
	 */
	protected $slug = 'about';

	/**
	 * Page default tab slug.
	 *
	 * @since 2.9.0
	 *
	 * @var string
	 */
	protected $default_tab = 'about';

	/**
	 * Get label for a tab.
	 * Process only those that exists.
	 * Defaults to "About Us".
	 *
	 * @since 1.5.0
	 *
	 * @param string $tab Tab to get label for.
	 *
	 * @return string
	 */
	public function get_label( $tab = '' ) {

		if ( ! empty( $tab ) ) {
			return $this->get_tab_label( $tab );
		}

		return esc_html__( 'About Us', 'wp-mail-smtp' );
	}

	/**
	 * Title of a page.
	 *
	 * @since 1.5.0
	 *
	 * @return string
	 */
	public function get_title() {

		return $this->get_label();
	}

	/**
	 * Active the given plugin.
	 *
	 * @deprecated 2.9.0
	 *
	 * @since 1.5.0
	 */
	public static function ajax_plugin_activate() {

		_deprecated_function( __METHOD__, '2.9.0', '\WPMailSMTP\Admin\Pages\AboutTab::ajax_plugin_activate' );

		AboutTab::ajax_plugin_activate();
	}

	/**
	 * Install & activate the given plugin.
	 *
	 * @deprecated 2.9.0
	 *
	 * @since 1.5.0
	 */
	public static function ajax_plugin_install() {

		_deprecated_function( __METHOD__, '2.9.0', '\WPMailSMTP\Admin\Pages\AboutTab::ajax_plugin_install' );

		AboutTab::ajax_plugin_install();
	}
}
