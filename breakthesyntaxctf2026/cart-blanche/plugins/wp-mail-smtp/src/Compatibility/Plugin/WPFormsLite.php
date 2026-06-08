<?php

namespace WPMailSMTP\Compatibility\Plugin;

/**
 * WPForms Lite compatibility plugin.
 *
 * @since 4.0.0
 */
class WPFormsLite extends PluginAbstract {

	/**
	 * Get plugin name.
	 *
	 * @since 4.0.0
	 *
	 * @return string
	 */
	public static function get_name() {

		return 'WPForms Lite';
	}

	/**
	 * Get plugin path.
	 *
	 * @since 4.0.0
	 *
	 * @return string
	 */
	public static function get_path() {

		return 'wpforms-lite/wpforms.php';
	}

	/**
	 * Execute on init action.
	 *
	 * @since 4.0.0
	 */
	public function load() { // phpcs:ignore WPForms.PHP.HooksMethod.InvalidPlaceForAddingHooks

		if ( wp_mail_smtp()->get_queue()->is_enabled() ) {
			add_filter( 'wpforms_tasks_entry_emails_trigger_send_same_process', '__return_true', PHP_INT_MAX );
		}
	}
}
