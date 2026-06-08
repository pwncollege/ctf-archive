<?php
/**
 * Helper functions to work with colors.
 *
 * @since 1.8.0
 */

/**
 * Detect if we should use a light or dark color based on the color given.
 *
 * @link https://docs.woocommerce.com/wc-apidocs/source-function-wc_light_or_dark.html#608-627
 *
 * @since 1.2.5
 *
 * @param mixed  $color Color value.
 * @param string $dark  Dark color value (default: '#000000').
 * @param string $light Light color value (default: '#FFFFFF').
 *
 * @return string
 */
function wpforms_light_or_dark( $color, $dark = '#000000', $light = '#FFFFFF' ) {

	$hex = str_replace( '#', '', $color );

	$c_r = hexdec( substr( $hex, 0, 2 ) );
	$c_g = hexdec( substr( $hex, 2, 2 ) );
	$c_b = hexdec( substr( $hex, 4, 2 ) );

	$brightness = ( ( $c_r * 299 ) + ( $c_g * 587 ) + ( $c_b * 114 ) ) / 1000;

	return $brightness > 155 ? $dark : $light;
}

/**
 * Convert hex color value to RGB.
 *
 * @since 1.7.9
 * @since 1.8.5 New param and return type were added.
 *
 * @param string $hex       Color value in hex format.
 * @param bool   $as_string Whether to return the RGB value as a string or array.
 *
 * @return string|array Color value in RGB format.
 */
function wpforms_hex_to_rgb( $hex, $as_string = true ) {

	$hex = ltrim( $hex, '#' );

	// Convert shorthand colors to full format, e.g. "FFF" -> "FFFFFF".
	$rgb_parts = preg_replace( '~^(.)(.)(.)$~', '$1$1$2$2$3$3', $hex );

	$rgb      = [];
	$rgb['R'] = hexdec( $rgb_parts[0] . $rgb_parts[1] );
	$rgb['G'] = hexdec( $rgb_parts[2] . $rgb_parts[3] );
	$rgb['B'] = hexdec( $rgb_parts[4] . $rgb_parts[5] );

	// Return the RGB value as a string.
	if ( $as_string ) {
		return sprintf(
			'%1$d, %2$d, %3$d',
			$rgb['R'],
			$rgb['G'],
			$rgb['B']
		);
	}

	return $rgb; // This is an array.
}

/**
 * Get a lighter color hex value.
 *
 * @since 1.8.5
 *
 * @param string $color  Color hex value.
 * @param int    $factor Factor to lighten the color.
 *
 * @return string Lighter color hex value.
 */
function wpforms_hex_lighter( $color, $factor = 30 ) {

	$base = wpforms_hex_to_rgb( $color, false );

	// Leave if we can't get the RGB values.
	if ( empty( $base ) || count( $base ) !== 3 ) {
		return '';
	}

	$hex = '#';

	foreach ( $base as $channel ) {
		$amount      = 255 - $channel;
		$amount      = $amount / 100;
		$amount      = round( floatval( $amount * $factor ) );
		$new_decimal = $channel + $amount;

		$new_hex_component = dechex( $new_decimal );

		if ( strlen( $new_hex_component ) < 2 ) {
			$new_hex_component = '0' . $new_hex_component;
		}

		$hex .= $new_hex_component;
	}

	return $hex;
}

/**
 * Get a darker color hex value.
 *
 * @since 1.8.5
 *
 * @param string $color  Color hex value.
 * @param int    $factor Factor to darken the color.
 *
 * @return string Darker color hex value.
 */
function wpforms_hex_darker( $color, $factor = 30 ) {

	$base = wpforms_hex_to_rgb( $color, false );

	// Leave if we can't get the RGB values.
	if ( empty( $base ) || count( $base ) !== 3 ) {
		return '';
	}

	$hex = '#';

	foreach ( $base as $channel ) {
		$amount      = $channel / 100;
		$amount      = round( floatval( $amount * $factor ) );
		$new_decimal = $channel - $amount;

		$new_hex_component = dechex( $new_decimal );

		if ( strlen( $new_hex_component ) < 2 ) {
			$new_hex_component = '0' . $new_hex_component;
		}

		$hex .= $new_hex_component;
	}

	return $hex;
}

/**
 * Generate a contrasting color based on the given color.
 *
 * This function calculates a contrasting color to ensure readability based on the provided color.
 *
 * @since 1.8.5
 *
 * @param string $color        The original color value. Color hex value.
 * @param int    $light_factor The factor to lighten the color.
 * @param int    $dark_factor  The factor to darken the color.
 *
 * @return string The contrasting color value.
 */
function wpforms_generate_contrasting_color( $color, $light_factor = 30, $dark_factor = 30 ) {

	$is_dark = wpforms_light_or_dark( $color, 'light', 'dark' ) === 'dark';

	return $is_dark ? wpforms_hex_lighter( $color, $light_factor ) : wpforms_hex_darker( $color, $dark_factor );
}
