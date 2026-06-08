<?php

namespace WPMailSMTP\Admin\Pages;

use WPMailSMTP\Admin\ParentPageAbstract;

/**
 * Class Tools.
 *
 * @since 2.8.0
 */
class Tools extends ParentPageAbstract {

	/**
	 * Slug of a page.
	 *
	 * @since 2.8.0
	 *
	 * @var string
	 */
	protected $slug = 'tools';

	/**
	 * Page default tab slug.
	 *
	 * @since 2.8.0
	 *
	 * @var string
	 */
	protected $default_tab = 'test';

	/**
	 * Link label of a page.
	 *
	 * @since 2.8.0
	 *
	 * @return string
	 */
	public function get_label() {

		return esc_html__( 'Tools', 'wp-mail-smtp' );
	}

	/**
	 * Title of a page.
	 *
	 * @since 2.8.0
	 *
	 * @return string
	 */
	public function get_title() {

		return $this->get_label();
	}
}
