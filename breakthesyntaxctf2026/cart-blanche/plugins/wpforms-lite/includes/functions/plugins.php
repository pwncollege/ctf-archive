<?php
/**
 * Helper functions to perform various plugins and addons related actions.
 *
 * @since 1.8.2.2
 */

use WPForms\Requirements\Requirements;

/**
 * Check if addon met requirements.
 *
 * @since 1.8.2.2
 *
 * @param array $requirements Addon requirements.
 *
 * @return bool
 */
function wpforms_requirements( array $requirements ): bool {

	return Requirements::get_instance()->validate( $requirements );
}

/**
 * Determine if an addon is active and passed all requirements.
 *
 * @since 1.9.2
 *
 * @param string $addon_slug Addon slug without `wpforms-` prefix.
 *
 * @return bool
 */
function wpforms_is_addon_initialized( string $addon_slug ): bool {

	$basename = sprintf( 'wpforms-%1$s/wpforms-%1$s.php', $addon_slug );

	if ( is_multisite() ) {
		$active_plugins = (array) get_option( 'active_plugins', [] );

		if ( in_array( $basename, $active_plugins, true ) ) {
			return true;
		}
	}

	return Requirements::get_instance()->is_validated( $basename );
}

/**
 * Check addon requirements and activate addon or plugin.
 *
 * @since 1.8.4
 * @since 1.9.2 Keep addons active even if they don't meet requirements.
 *
 * @param string $plugin Path to the plugin file relative to the plugins' directory.
 *
 * @return null|WP_Error Null on success, WP_Error on invalid file.
 */
function wpforms_activate_plugin( string $plugin ) {

	$activate = activate_plugin( $plugin );

	if ( is_wp_error( $activate ) ) {
		return $activate;
	}

	$requirements = Requirements::get_instance();

	if ( $requirements->is_validated( $plugin ) ) {
		return null;
	}

	return new WP_Error( 'wpforms_addon_incompatible', $requirements->get_notice( $plugin ) );
}

/**
 * Compares two "PHP-standardized" version number strings.
 *
 * Removes any "-RCn", "-beta" from version numbers first.
 *
 * @since 1.9.4
 *
 * @param string $version1 Version number.
 * @param string $version2 Version number.
 * @param string $operator Comparison operator.
 *
 * @return bool
 */
function wpforms_version_compare( $version1, $version2, $operator ): bool {

	// If the version is not a string, return false.
	if ( ! is_string( $version1 ) || ! is_string( $version2 ) ) {
		return false;
	}

	// Strip dash and anything after it.
	$clean_version_number = function ( $version ) {
		return preg_replace( '/-.+/', '', $version );
	};

	return version_compare(
		$clean_version_number( $version1 ),
		$clean_version_number( $version2 ),
		$operator
	);
}
