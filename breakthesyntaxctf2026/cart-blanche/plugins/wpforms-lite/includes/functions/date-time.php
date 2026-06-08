<?php
/**
 * Helper functions to work with dates, time and timezones.
 *
 * @since 1.8.0
 */

/**
 * Return date and time formatted as expected.
 *
 * @since 1.6.3
 *
 * @param string|int $date       Date to format.
 * @param string     $format     Optional. Format for the date and time.
 * @param bool       $gmt_offset Optional. GTM offset.
 *
 * @return string
 */
function wpforms_datetime_format( $date, $format = '', $gmt_offset = false ) {

	if ( is_numeric( $date ) ) {
		$date = (int) $date;
	}

	if ( is_string( $date ) ) {
		$date = strtotime( $date );
	}

	if ( $gmt_offset ) {
		$date += (int) ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS );
	}

	if ( $format === '' ) {
		return sprintf( /* translators: %1$s - formatted date, %2$s - formatted time. */
			__( '%1$s at %2$s', 'wpforms-lite' ),
			date_i18n( get_option( 'date_format' ), $date ),
			date_i18n( get_option( 'time_format' ), $date )
		);
	}

	return date_i18n( $format, $date );
}

/**
 * Return date formatted as expected.
 *
 * @since 1.6.3
 *
 * @param string|int $date       Date to format.
 * @param string     $format     Optional. Format for the date.
 * @param bool       $gmt_offset Optional. GTM offset.
 *
 * @return string
 */
function wpforms_date_format( $date, $format = '', $gmt_offset = false ) {

	if ( $format === '' ) {
		$format = (string) get_option( 'date_format', 'M j, Y' );
	}

	return wpforms_datetime_format( $date, $format, $gmt_offset );
}

/**
 * Return time formatted as expected.
 *
 * @since 1.8.5
 *
 * @param string|int $date       Date to format.
 * @param string     $format     Optional. Format for the time.
 * @param bool       $gmt_offset Optional. GTM offset.
 *
 * @return string
 */
function wpforms_time_format( $date, $format = '', $gmt_offset = false ) {

	if ( $format === '' ) {
		$format = (string) get_option( 'time_format', 'g:ia' );
	}

	return wpforms_datetime_format( $date, $format, $gmt_offset );
}

/**
 * Get the certain date of a specified day in a specified format.
 *
 * @since 1.4.4
 * @since 1.6.3 Added $use_gmt_offset parameter.
 *
 * @param string $period         Supported values: start, end.
 * @param string $timestamp      Default is the current timestamp, if left empty.
 * @param string $format         Default is a MySQL format.
 * @param bool   $use_gmt_offset Use GTM offset.
 *
 * @return string
 */
function wpforms_get_day_period_date( $period, $timestamp = '', $format = 'Y-m-d H:i:s', $use_gmt_offset = false ) {

	$date = '';

	if ( empty( $timestamp ) ) {
		$timestamp = time();
	}

	$offset_sec = $use_gmt_offset ? get_option( 'gmt_offset' ) * 3600 : 0;

	switch ( $period ) {
		case 'start_of_day':
			$date = gmdate( $format, strtotime( 'today', $timestamp ) - $offset_sec );
			break;

		case 'end_of_day':
			$date = gmdate( $format, strtotime( 'tomorrow', $timestamp ) - 1 - $offset_sec );
			break;
	}

	return $date;
}
