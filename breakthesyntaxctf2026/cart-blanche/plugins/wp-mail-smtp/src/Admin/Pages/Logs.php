<?php

namespace WPMailSMTP\Admin\Pages;

use WPMailSMTP\Admin\Area;
use WPMailSMTP\Admin\PageAbstract;
use WPMailSMTP\WP;

/**
 * Class Logs
 */
class Logs extends PageAbstract {

	/**
	 * Slug of a page.
	 *
	 * @since 1.5.0
	 *
	 * @var string
	 */
	protected $slug = 'logs';

	/**
	 * Get the page/tab link.
	 *
	 * @since 1.5.0
	 * @since 2.1.0 Changed the URL to point to the email log settings tab.
	 *
	 * @return string
	 */
	public function get_link() {

		return add_query_arg(
			'tab',
			$this->slug,
			WP::admin_url( 'admin.php?page=' . Area::SLUG )
		);
	}

	/**
	 * Link label of a tab.
	 *
	 * @since 1.5.0
	 *
	 * @return string
	 */
	public function get_label() {
		return esc_html__( 'Email Log', 'wp-mail-smtp' );
	}

	/**
	 * Title of a tab.
	 *
	 * @since 1.5.0
	 *
	 * @return string
	 */
	public function get_title() {
		return $this->get_label();
	}

	/**
	 * Tab content.
	 *
	 * @since 2.1.0 Moved the display content to the email log settings tab.
	 */
	public function display() {}
}
