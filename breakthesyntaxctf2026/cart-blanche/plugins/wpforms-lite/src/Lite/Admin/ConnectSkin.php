<?php

namespace WPForms\Lite\Admin;

use WP_Ajax_Upgrader_Skin;
use WP_Error; // phpcs:ignore WPForms.PHP.UseStatement.UnusedUseStatement

/**
 * WPForms Connect Skin.
 *
 * WPForms Connect is our service that makes it easy for non-techy users to
 * upgrade to WPForms Pro without having to manually install WPForms Pro plugin.
 *
 * @since 1.5.5
 * @since 1.5.6.1 Extend PluginSilentUpgraderSkin and clean up the class.
 * @since 1.9.5 Extend WP_Ajax_Upgrader_Skin class.
 */
class ConnectSkin extends WP_Ajax_Upgrader_Skin {

	/**
	 * Instead of outputting HTML for errors, json_encode the errors and send them
	 * back to the Ajax script for processing.
	 *
	 * @since 1.5.5
	 *
	 * @param string|WP_Error $errors  Errors.
	 * @param mixed           ...$args Optional text replacements.
     */
	public function error( $errors, ...$args ) {

		if ( ! empty( $errors ) ) {
			wp_send_json_error( esc_html__( 'There was an error installing WPForms Pro. Please try again.', 'wpforms-lite' ) );
		}
	}
}
