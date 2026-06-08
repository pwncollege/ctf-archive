<?php

namespace WPMailSMTP\Admin\Pages;

use WPMailSMTP\ConnectionInterface;
use WPMailSMTP\Providers\AuthAbstract;

/**
 * Class AuthTab.
 *
 * @since 1.0.0
 */
class AuthTab {

	/**
	 * @var string Slug of a tab.
	 */
	protected $slug = 'auth';

	/**
	 * Launch mailer specific Auth logic.
	 *
	 * @since 1.0.0
	 */
	public function process_auth() {

		$connection = wp_mail_smtp()->get_connections_manager()->get_primary_connection();

		/**
		 * Filters auth connection object.
		 *
		 * @since 3.7.0
		 *
		 * @param ConnectionInterface $connection The Connection object.
		 */
		$connection = apply_filters( 'wp_mail_smtp_admin_pages_auth_tab_process_auth_connection', $connection );

		$auth = wp_mail_smtp()->get_providers()->get_auth( $connection->get_mailer_slug(), $connection );

		if (
			$auth &&
			$auth instanceof AuthAbstract &&
			method_exists( $auth, 'process' )
		) {
			$auth->process();
		}
	}

	/**
	 * Return nothing, as we don't need this functionality.
	 *
	 * @since 1.0.0
	 */
	public function get_label() {
		return '';
	}

	/**
	 * Return nothing, as we don't need this functionality.
	 *
	 * @since 1.0.0
	 */
	public function get_title() {
		return '';
	}

	/**
	 * Do nothing, as we don't need this functionality.
	 *
	 * @since 1.0.0
	 */
	public function display() {
	}
}
