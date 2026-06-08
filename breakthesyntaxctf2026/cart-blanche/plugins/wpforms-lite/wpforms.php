<?php
/**
 * Plugin Name:       WPForms Lite
 * Plugin URI:        https://wpforms.com
 * Description:       Beginner friendly WordPress contact form plugin. Use our Drag & Drop form builder to create your WordPress forms.
 * Requires at least: 5.5
 * Requires PHP:      7.2
 * Author:            WPForms
 * Author URI:        https://wpforms.com
 * Version:           1.10.0.4
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wpforms-lite
 * Domain Path:       /assets/languages
 *
 * WPForms is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * WPForms is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with WPForms. If not, see <https://www.gnu.org/licenses/>.
 */

// Exit if accessed directly.
use WPForms\Requirements\Requirements;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( is_multisite() ) {
	$is_pro = file_exists( __DIR__ . '/pro/wpforms-pro.php' );

	if ( ! $is_pro ) { // <- is lite.
		$lite_base = plugin_basename( __FILE__ );

		$active_plugins         = get_option( 'active_plugins', [] );
		$active_network_plugins = get_site_option( 'active_sitewide_plugins' );

		if (
			isset( $active_network_plugins[ $lite_base ] )
			&& in_array( 'wpforms/wpforms.php', $active_plugins, true )
		) {
			// Keep plugin active but silent.
			return;
		}
	}
}

if ( ! defined( 'WPFORMS_VERSION' ) ) {
	/**
	 * Plugin version.
	 *
	 * @since 1.0.0
	 */
	define( 'WPFORMS_VERSION', '1.10.0.4' ); // NOSONAR.
}

if ( ! defined( 'WPFORMS_PLUGIN_DIR' ) ) {
	/**
	 * Plugin Folder Path.
	 *
	 * @since 1.3.8
	 */
	define( 'WPFORMS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'WPFORMS_PLUGIN_URL' ) ) {
	/**
	 * Plugin Folder URL.
	 *
	 * @since 1.3.8
	 */
	define( 'WPFORMS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'WPFORMS_PLUGIN_FILE' ) ) {
	/**
	 * Plugin Root File.
	 *
	 * @since 1.3.8
	 */
	define( 'WPFORMS_PLUGIN_FILE', __FILE__ );
}

// Don't allow multiple versions to be active.
if ( function_exists( 'wpforms' ) ) {
	if ( ! function_exists( 'wpforms_lite_just_activated' ) ) {
		/**
		 * Store temporarily that the Lite version of the plugin was activated.
		 * This is needed because WP does a redirect after activation,
		 * and we need to preserve this state to know whether the user activated Lite or not.
		 *
		 * @since 1.5.8
		 */
		function wpforms_lite_just_activated() {

			set_transient( 'wpforms_lite_just_activated', true );
		}
		add_action( 'activate_wpforms-lite/wpforms.php', 'wpforms_lite_just_activated' );
	}

	if ( ! function_exists( 'wpforms_deactivate' ) ) {
		/**
		 * Deactivate Lite if WPForms already activated.
		 *
		 * @since 1.0.0
		 */
		function wpforms_deactivate() {

			$pro_file  = wpforms()->is_pro() ? WPFORMS_PLUGIN_FILE : __FILE__;
			$lite_file = wpforms()->is_pro() ? __FILE__ : WPFORMS_PLUGIN_FILE;

			$lite_base = plugin_basename( $lite_file );
			$pro_base  = plugin_basename( $pro_file );

			if (
				! is_multisite()
				|| is_plugin_active_for_network( $pro_base )
				|| ( ! is_plugin_active_for_network( $pro_base ) && ! is_plugin_active_for_network( $lite_base ) )
			) {
				deactivate_plugins( $lite_base );

				/**
				 * Fires on plugin deactivation.
				 *
				 * @since 1.6.3.1
				 *
				 * @param string $plugin_basename The plugin basename.
				 */
				do_action( 'wpforms_plugin_deactivated', $lite_base );

				// Run the installation on the next admin visit.
				add_option( 'wpforms_install', 1 );
			}
		}
	}
	add_action( 'admin_init', 'wpforms_deactivate' );

	if ( ! function_exists( 'wpforms_lite_notice' ) ) {
		/**
		 * Display the notice after deactivation when Pro is still active
		 * and user wanted to activate the Lite version of the plugin.
		 *
		 * @since 1.0.0
		 *
		 * @noinspection HtmlUnknownTarget
		 */
		function wpforms_lite_notice() {

			$pro_file  = wpforms()->is_pro() ? WPFORMS_PLUGIN_FILE : __FILE__;
			$lite_file = wpforms()->is_pro() ? __FILE__ : WPFORMS_PLUGIN_FILE;

			$lite_base = plugin_basename( $lite_file );
			$pro_base  = plugin_basename( $pro_file );

			// Do not show the notice if upgrade from Lite to Pro.
			if ( (bool) get_transient( 'wpforms_lite_just_activated' ) === false ) {
				return;
			}

			if (
				! is_multisite()
				|| is_plugin_active_for_network( $pro_base )
				|| ( ! is_plugin_active_for_network( $pro_base ) && ! is_plugin_active_for_network( $lite_base ) )
			) {
				$message = sprintf(
				/* translators: %s - Path to installed plugins. */
					__( 'Your site already has WPForms Pro activated. If you want to switch to WPForms Lite, please first go to %s and deactivate WPForms. Then, you can activate WPForms Lite.', 'wpforms-lite' ),
					is_multisite() ? __( 'Network Admin → Plugins → Installed Plugins', 'wpforms-lite' ) : __( 'Plugins → Installed Plugins', 'wpforms-lite' )
				);

				// Currently tried to activate Lite with Pro still active, so display the message.
				printf(
					'<div class="notice wpforms-notice notice-warning wpforms-license-notice" id="wpforms-notice-pro-active">
					<h3 style="margin: .75em 0 0 0;">
						<img src="%1$s" alt="" style="vertical-align: text-top; width: 20px; margin-right: 7px;">%2$s
					</h3>
					<p>%3$s</p>
				</div>',
					esc_url( WPFORMS_PLUGIN_URL . 'assets/images/exclamation-triangle.svg' ),
					esc_html__( 'Heads up!', 'wpforms-lite' ),
					esc_html( $message )
				);

				delete_transient( 'wpforms_lite_just_activated' );

				if ( isset( $_GET['activate'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					unset( $_GET['activate'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				}
			}
		}
	}
	add_action( 'admin_notices', 'wpforms_lite_notice' );
	add_action( 'network_admin_notices', 'wpforms_lite_notice' );

	// Do not process the plugin code further.
	return;
}

if ( ! function_exists( 'wpforms_php52_notice' ) ) {

	/**
	 * Display the notice about incompatible PHP version after deactivation.
	 *
	 * @since 1.5.0
	 * @deprecated 1.9.6
	 */
	function wpforms_php52_notice() {

		_deprecated_function( __FUNCTION__, '1.9.6 of the WPForms plugin' );
	}
}

if ( ! function_exists( 'wpforms_wp_notice' ) ) {

	/**
	 * Display the notice about incompatible WP version after deactivation.
	 *
	 * @since 1.7.3
	 * @deprecated 1.9.6
	 */
	function wpforms_wp_notice() {

		_deprecated_function( __FUNCTION__, '1.9.6 of the WPForms plugin' );
	}
}

require_once WPFORMS_PLUGIN_DIR . 'src/Requirements/Requirements.php';
require_once WPFORMS_PLUGIN_DIR . 'includes/functions.php';

$requirements = [
	'file' => __FILE__,
];

if ( ! Requirements::get_instance()->validate( $requirements ) ) {
	return;
}

// Define the class and the function.
require_once __DIR__ . '/src/WPForms.php';

if ( function_exists( 'wpforms' ) ) {
	wpforms();
}
