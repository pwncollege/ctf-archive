<?php

namespace WPMailSMTP\Compatibility\Plugin;

/**
 * WPForms compatibility plugin.
 *
 * @since 4.0.0
 */
class WPForms extends WPFormsLite {

	/**
	 * Get plugin name.
	 *
	 * @since 4.0.0
	 *
	 * @return string
	 */
	public static function get_name() {

		return 'WPForms';
	}

	/**
	 * Get plugin path.
	 *
	 * @since 4.0.0
	 *
	 * @return string
	 */
	public static function get_path() {

		return 'wpforms/wpforms.php';
	}
}
