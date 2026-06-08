<?php

namespace WPMailSMTP\Compatibility\Plugin;

/**
 * Polylang compatibility plugin.
 *
 * @since 4.6.0
 */
class PolylangPro extends Polylang {

	/**
	 * Get plugin name.
	 *
	 * @since 4.6.0
	 *
	 * @return string
	 */
	public static function get_name() {

		return 'Polylang Pro';
	}

	/**
	 * Get plugin path.
	 *
	 * @since 4.6.0
	 *
	 * @return string
	 */
	public static function get_path() {

		return 'polylang-pro/polylang.php';
	}
}

