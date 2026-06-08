<?php
/**
 * Helper functions to work with Providers API.
 *
 * @since 1.8.0
 */

/**
 * Get an array of all the active provider addons.
 *
 * @since 1.4.7
 *
 * @return array
 */
function wpforms_get_providers_available() {

	return (array) apply_filters( 'wpforms_providers_available', [] );
}

/**
 * Get options for all providers.
 *
 * @since 1.4.7
 *
 * @param string $provider Define a single provider to get options for this one only.
 *
 * @return array
 */
function wpforms_get_providers_options( $provider = '' ) {

	$options  = get_option( 'wpforms_providers', [] );
	$provider = sanitize_key( $provider );
	$data     = $options;

	if ( ! empty( $provider ) ) {
		$data = $options[ $provider ] ?? [];
	}

	return (array) apply_filters( 'wpforms_get_providers_options', $data, $provider );
}

/**
 * Update options for all providers.
 *
 * @since 1.4.7
 *
 * @param string      $provider Provider slug.
 * @param array|false $options  If false is passed - provider will be removed. Otherwise saved.
 * @param string      $key      Optional key to identify which connection to update. If empty - generate a new one.
 */
function wpforms_update_providers_options( $provider, $options, $key = '' ) {

	$providers = wpforms_get_providers_options();
	$id        = ! empty( $key ) ? $key : uniqid();
	$provider  = sanitize_key( $provider );

	if ( $options ) {
		$providers[ $provider ][ $id ] = (array) $options;
	} else {
		unset( $providers[ $provider ] );
	}

	/**
	 * A collection of service providers used for dependency injection or service registration
	 * within the application.
	 *
	 * @since 1.9.6
	 *
	 * @param array  $providers List of all registered providers.
	 * @param string $provider  Provider slug.
	 * @param array  $options   Provider options data. If false - provider will be removed.
	 * @param string $id        Provider connection ID.
	 */
	$providers = (array) apply_filters( 'wpforms_update_providers_options', $providers, $provider, $options, $id );

	update_option( 'wpforms_providers', $providers );
}
