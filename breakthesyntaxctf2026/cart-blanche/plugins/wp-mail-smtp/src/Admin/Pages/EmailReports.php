<?php

namespace WPMailSMTP\Admin\Pages;

use WPMailSMTP\Admin\ParentPageAbstract;

/**
 * Class EmailReports.
 *
 * @since 3.0.0
 */
class EmailReports extends ParentPageAbstract {

	/**
	 * Page default tab slug.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	protected $default_tab = 'reports';

	/**
	 * Slug of a page.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	protected $slug = 'reports';

	/**
	 * Link label of a page.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public function get_label() {

		return esc_html__( 'Email Reports', 'wp-mail-smtp' );
	}

	/**
	 * Title of a page.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public function get_title() {

		return $this->get_label();
	}
}
