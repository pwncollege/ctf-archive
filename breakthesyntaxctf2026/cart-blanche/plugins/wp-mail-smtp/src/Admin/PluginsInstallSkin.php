<?php

namespace WPMailSMTP\Admin;

use Automatic_Upgrader_Skin;

/**
 * WordPress class extended for on-the-fly plugin installations.
 *
 * @since 1.5.0
 * @since 1.7.1 Removed feedback() method override to be compatible with PHP5.3+ and WP5.3.
 * @since 3.11.0 Updated to extend Automatic_Upgrader_Skin.
 */
class PluginsInstallSkin extends Automatic_Upgrader_Skin {

	/**
	 * Empty out the header of its HTML content and only check to see if it has
	 * been performed or not.
	 *
	 * @since 1.5.0
	 */
	public function header() {
	}

	/**
	 * Empty out the footer of its HTML contents.
	 *
	 * @since 1.5.0
	 */
	public function footer() {
	}

	/**
	 * Instead of outputting HTML for errors, json_encode the errors and send them
	 * back to the Ajax script for processing.
	 *
	 * @since 1.5.0
	 *
	 * @param array $errors Array of errors with the install process.
	 */
	public function error( $errors ) {

		if ( ! empty( $errors ) ) {
			wp_send_json_error( $errors );
		}
	}

	/**
	 * Empty out JavaScript output that calls function to decrement the update counts.
	 *
	 * @since 1.5.0
	 *
	 * @param string $type Type of update count to decrement.
	 */
	public function decrement_update_count( $type ) {
	}
}

