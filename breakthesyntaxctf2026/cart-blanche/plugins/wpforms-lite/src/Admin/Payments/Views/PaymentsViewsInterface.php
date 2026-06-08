<?php

namespace WPForms\Admin\Payments\Views;

interface PaymentsViewsInterface {

	/**
	 * Initialize class.
	 *
	 * @since 1.8.2
	 */
	public function init();

	/**
	 * Check if the current user has the capability to view the page.
	 *
	 * @since 1.8.2
	 *
	 * @return bool
	 */
	public function current_user_can();

	/**
	 * Page heading content.
	 *
	 * @since 1.8.2
	 */
	public function heading();

	/**
	 * Page content.
	 *
	 * @since 1.8.2
	 */
	public function display();

	/**
	 * Get the Tab label.
	 *
	 * @since 1.8.2.2
	 */
	public function get_tab_label();
}
