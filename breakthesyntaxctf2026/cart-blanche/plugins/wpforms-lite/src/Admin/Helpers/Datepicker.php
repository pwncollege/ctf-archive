<?php

namespace WPForms\Admin\Helpers;

use DateTimeImmutable;

/**
 * Timespan and popover date-picker helper methods.
 *
 * @since 1.8.2
 */
class Datepicker {

	/**
	 * Number of timespan days by default.
	 * "Last 30 Days", by default.
	 *
	 * @since 1.8.2
	 */
	const TIMESPAN_DAYS = '30';

	/**
	 * Timespan (date range) delimiter.
	 *
	 * @since 1.8.2
	 */
	const TIMESPAN_DELIMITER = ' - ';

	/**
	 * Default date format.
	 *
	 * @since 1.8.2
	 */
	const DATE_FORMAT = 'Y-m-d';

	/**
	 * Default date-time format.
	 *
	 * @since 1.8.2
	 */
	const DATETIME_FORMAT = 'Y-m-d H:i:s';

	/**
	 * Sets the timespan (or date range) selected.
	 *
	 * Includes:
	 * 1. Start date object in WP timezone.
	 * 2. End date object in WP timezone.
	 * 3. Number of "Last X days", if applicable, otherwise returns "custom".
	 * 4. Label associated with the selected date filter choice. @see "get_date_filter_choices".
	 *
	 * @since 1.8.2
	 *
	 * @return array
	 */
	public static function process_timespan() {

		$dates = (string) filter_input( INPUT_GET, 'date', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

		// Return default timespan if dates are empty.
		if ( empty( $dates ) ) {
			return self::get_timespan_dates( self::TIMESPAN_DAYS );
		}

		$dates = self::maybe_validate_string_timespan( $dates );

		list( $start_date, $end_date ) = explode( self::TIMESPAN_DELIMITER, $dates );

		// Return default timespan if start date is more recent than end date.
		if ( strtotime( $start_date ) > strtotime( $end_date ) ) {
			return self::get_timespan_dates( self::TIMESPAN_DAYS );
		}

		$timezone   = wp_timezone(); // Retrieve the timezone string for the site.
		$start_date = date_create_immutable( $start_date, $timezone );
		$end_date   = date_create_immutable( $end_date, $timezone );

		// Return default timespan if date creation fails.
		if ( ! $start_date || ! $end_date ) {
			return self::get_timespan_dates( self::TIMESPAN_DAYS );
		}

		// Set time to 0:0:0 for start date and 23:59:59 for end date.
		$start_date = $start_date->setTime( 0, 0, 0 );
		$end_date   = $end_date->setTime( 23, 59, 59 );

		$days_diff    = '';
		$current_date = date_create_immutable( 'now', $timezone )->setTime( 23, 59, 59 );

		// Calculate days difference only if end date is equal to current date.
		if ( ! $current_date->diff( $end_date )->format( '%a' ) ) {
			$days_diff = $end_date->diff( $start_date )->format( '%a' );
		}

		list( $days, $timespan_label ) = self::get_date_filter_choices( $days_diff );

		return [
			$start_date,     // WP timezone.
			$end_date,       // WP timezone.
			$days,           // e.g., 22.
			$timespan_label, // e.g., Custom.
		];
	}

	/**
	 * Sets the timespan (or date range) for performing mysql queries.
	 *
	 * Includes:
	 * 1. Start date object in WP timezone.
	 * 2. End date object in WP timezone.
	 *
	 * @param null|array $timespan Given timespan (dates) preferably in WP timezone.
	 *
	 * @since 1.8.2
	 *
	 * @return array
	 */
	public static function process_timespan_mysql( $timespan = null ) {

		// Retrieve and validate timespan if none is given.
		if ( empty( $timespan ) || ! is_array( $timespan ) ) {
			$timespan = self::process_timespan();
		}

		list( $start_date, $end_date ) = $timespan; // Ideally should be in WP timezone.

		// If the time period is not a date object, return empty values.
		if ( ! ( $start_date instanceof DateTimeImmutable ) || ! ( $end_date instanceof DateTimeImmutable ) ) {
			return [ '', '' ];
		}

		// If given timespan is already in UTC timezone, return as it is.
		if ( date_timezone_get( $start_date )->getName() === 'UTC' && date_timezone_get( $end_date )->getName() === 'UTC' ) {
			return [
				$start_date, // UTC timezone.
				$end_date,   // UTC timezone.
			];
		}

		$mysql_timezone = timezone_open( 'UTC' );

		return [
			$start_date->setTimezone( $mysql_timezone ), // UTC timezone.
			$end_date->setTimezone( $mysql_timezone ),   // UTC timezone.
		];
	}

	/**
	 * Helper method to generate WP and UTC based date-time instances.
	 *
	 * Includes:
	 * 1. Start date object in WP timezone.
	 * 2. End date object in WP timezone.
	 * 3. Start date object in UTC timezone.
	 * 4. End date object in UTC timezone.
	 *
	 * @since 1.8.2
	 *
	 * @param string $dates Given timespan (dates) in string. i.e. "2023-01-16 - 2023-02-15".
	 *
	 * @return bool|array
	 */
	public static function process_string_timespan( $dates ) {

		$dates = self::maybe_validate_string_timespan( $dates );

		list( $start_date, $end_date ) = explode( self::TIMESPAN_DELIMITER, $dates );

		// Return false if the start date is more recent than the end date.
		if ( strtotime( $start_date ) > strtotime( $end_date ) ) {
			return false;
		}

		$timezone   = wp_timezone(); // Retrieve the timezone object for the site.
		$start_date = date_create_immutable( $start_date, $timezone );
		$end_date   = date_create_immutable( $end_date, $timezone );

		// Return false if the date creation fails.
		if ( ! $start_date || ! $end_date ) {
			return false;
		}

		// Set the time to 0:0:0 for the start date and 23:59:59 for the end date.
		$start_date = $start_date->setTime( 0, 0, 0 );
		$end_date   = $end_date->setTime( 23, 59, 59 );

		// Since we will need the initial datetime instances after the query,
		// we need to return new objects when modifications made.
		// Convert the dates to UTC timezone.
		$mysql_timezone = timezone_open( 'UTC' );
		$utc_start_date = $start_date->setTimezone( $mysql_timezone );
		$utc_end_date   = $end_date->setTimezone( $mysql_timezone );

		return [
			$start_date,     // WP timezone.
			$end_date,       // WP timezone.
			$utc_start_date, // UTC timezone.
			$utc_end_date,   // UTC timezone.
		];
	}

	/**
	 * Sets the timespan (or date range) for performing mysql queries.
	 *
	 * Includes:
	 * 1. A list of date filter options for the datepicker module.
	 * 2. Currently selected filter or date range values. Last "X" days, or i.e. Feb 8, 2023 - Mar 9, 2023.
	 * 3. Assigned timespan dates.
	 *
	 * @param null|array $timespan Given timespan (dates) preferably in WP timezone.
	 *
	 * @since 1.8.2
	 *
	 * @return array
	 */
	public static function process_datepicker_choices( $timespan = null ) {

		// Retrieve and validate timespan if none is given.
		if ( empty( $timespan ) || ! is_array( $timespan ) ) {
			$timespan = self::process_timespan();
		}

		list( $start_date, $end_date, $days ) = $timespan;

		$filters       = self::get_date_filter_choices();
		$selected      = isset( $filters[ $days ] ) ? $days : 'custom';
		$value         = self::concat_dates( $start_date, $end_date );
		$chosen_filter = $selected === 'custom' ? $value : $filters[ $selected ];
		$choices       = [];

		foreach ( $filters as $choice => $label ) {

			$timespan_dates = self::get_timespan_dates( $choice );
			$checked        = checked( $selected, $choice, false );
			$choices[]      = sprintf(
				'<label class="%s">%s<input type="radio" aria-hidden="true" name="timespan" value="%s" %s></label>',
				$checked ? 'is-selected' : '',
				esc_html( $label ),
				esc_attr( self::concat_dates( ...$timespan_dates ) ),
				esc_attr( $checked )
			);
		}

		return [
			$choices,
			$chosen_filter,
			$value,
		];
	}

	/**
	 * Based on the specified date-time range, calculates the comparable prior time period to estimate trends.
	 *
	 * Includes:
	 * 1. Start date object in the given (original) timezone.
	 * 2. End date object in the given (original) timezone.
	 *
	 * @since 1.8.2
	 * @since 1.8.8 Added $days_diff optional parameter.
	 *
	 * @param DateTimeImmutable $start_date Start date for the timespan.
	 * @param DateTimeImmutable $end_date   End date for the timespan.
	 * @param null|int          $days_diff  Optional. Number of days in the timespan. If provided, it won't be calculated.
	 *
	 * @return bool|array
	 */
	public static function get_prev_timespan_dates( $start_date, $end_date, $days_diff = null ) {

		if ( ! ( $start_date instanceof DateTimeImmutable ) || ! ( $end_date instanceof DateTimeImmutable ) ) {
			return false;
		}

		// Calculate $days_diff if not provided.
		if ( ! is_numeric( $days_diff ) ) {
			$days_diff = $end_date->diff( $start_date )->format( '%a' );
		}

		// If $days_diff is non-positive, set $days_modifier to 1; otherwise, use $days_diff.
		$days_modifier = max( (int) $days_diff, 1 );

		return [
			$start_date->modify( "-{$days_modifier} day" ),
			$start_date->modify( '-1 second' ),
		];
	}

	/**
	 * Get the site's date format from WordPress settings and convert it to a format compatible with Moment.js.
	 *
	 * @since 1.8.5.4
	 *
	 * @return string
	 */
	public static function get_wp_date_format_for_momentjs() {

		// Get the date format from WordPress settings.
		$date_format = get_option( 'date_format', 'F j, Y' );

		// Define a mapping of PHP date format characters to Moment.js format characters.
		$format_mapping = [
			'd' => 'DD',
			'D' => 'ddd',
			'j' => 'D',
			'l' => 'dddd',
			'S' => '', // PHP's S (English ordinal suffix) is not directly supported in Moment.js.
			'w' => 'd',
			'z' => '', // PHP's z (Day of the year) is not directly supported in Moment.js.
			'W' => '', // PHP's W (ISO-8601 week number of year) is not directly supported in Moment.js.
			'F' => 'MMMM',
			'm' => 'MM',
			'M' => 'MMM',
			'n' => 'M',
			't' => '', // PHP's t (Number of days in the given month) is not directly supported in Moment.js.
			'L' => '', // PHP's L (Whether it's a leap year) is not directly supported in Moment.js.
			'o' => 'YYYY',
			'Y' => 'YYYY',
			'y' => 'YY',
			'a' => 'a',
			'A' => 'A',
			'B' => '', // PHP's B (Swatch Internet time) is not directly supported in Moment.js.
			'g' => 'h',
			'G' => 'H',
			'h' => 'hh',
			'H' => 'HH',
			'i' => 'mm',
			's' => 'ss',
			'u' => '', // PHP's u (Microseconds) is not directly supported in Moment.js.
			'e' => '', // PHP's e (Timezone identifier) is not directly supported in Moment.js.
			'I' => '', // PHP's I (Whether or not the date is in daylight saving time) is not directly supported in Moment.js.
			'O' => '', // PHP's O (Difference to Greenwich time (GMT) without colon) is not directly supported in Moment.js.
			'P' => '', // PHP's P (Difference to Greenwich time (GMT) with colon) is not directly supported in Moment.js.
			'T' => '', // PHP's T (Timezone abbreviation) is not directly supported in Moment.js.
			'Z' => '', // PHP's Z (Timezone offset in seconds) is not directly supported in Moment.js.
			'c' => 'YYYY-MM-DD', // PHP's c (ISO 8601 date) is not directly supported in Moment.js.
			'r' => 'ddd, DD MMM YYYY', // PHP's r (RFC 2822 formatted date) is not directly supported in Moment.js.
			'U' => '', // PHP's U (Seconds since the Unix Epoch) is not directly supported in Moment.js.
		];

		// Convert PHP format to JavaScript format.
		$momentjs_format = strtr( $date_format, $format_mapping );

		// Use 'MMM D, YYYY' as a fallback if the conversion is not available.
		return empty( $momentjs_format ) ? 'MMM D, YYYY' : $momentjs_format;
	}

	/**
	 * The number of days is converted to the start and end date range.
	 *
	 * @since 1.8.2
	 *
	 * @param string $days Timespan days.
	 *
	 * @return array
	 */
	private static function get_timespan_dates( $days ) {

		list( $timespan_key, $timespan_label ) = self::get_date_filter_choices( $days );

		// Bail early, if the given number of days is NOT a number nor a numeric string.
		if ( ! is_numeric( $days ) ) {
			return [ '', '', $timespan_key, $timespan_label ];
		}

		$end_date   = date_create_immutable( 'now', wp_timezone() );
		$start_date = $end_date;

		if ( (int) $days > 0 ) {
			$start_date = $start_date->modify( "-{$days} day" );
		}

		$start_date = $start_date->setTime( 0, 0, 0 );
		$end_date   = $end_date->setTime( 23, 59, 59 );

		return [
			$start_date,     // WP timezone.
			$end_date,       // WP timezone.
			$timespan_key,   // i.e. 30.
			$timespan_label, // i.e. Last 30 days.
		];
	}

	/**
	 * Check the delimiter to see if the end date is specified.
	 * We can assume that the start and end dates are the same if the end date is missing.
	 *
	 * @since 1.8.2
	 *
	 * @param string $dates Given timespan (dates) in string. i.e. "2023-01-16 - 2023-02-15" or "2023-01-16".
	 *
	 * @return string
	 */
	private static function maybe_validate_string_timespan( $dates ) {

		// "-" (en dash) is used as a delimiter for the datepicker module.
		if ( strpos( $dates, self::TIMESPAN_DELIMITER ) !== false ) {
			return $dates;
		}

		return $dates . self::TIMESPAN_DELIMITER . $dates;
	}

	/**
	 * Returns a list of date filter options for the datepicker module.
	 *
	 * @since 1.8.2
	 *
	 * @param string|null $key Optional. Key associated with available filters.
	 *
	 * @return array
	 */
	private static function get_date_filter_choices( $key = null ) {

		// Available date filters.
		$choices = [
			'0'      => esc_html__( 'Today', 'wpforms-lite' ),
			'1'      => esc_html__( 'Yesterday', 'wpforms-lite' ),
			'7'      => esc_html__( 'Last 7 days', 'wpforms-lite' ),
			'30'     => esc_html__( 'Last 30 days', 'wpforms-lite' ),
			'90'     => esc_html__( 'Last 90 days', 'wpforms-lite' ),
			'365'    => esc_html__( 'Last 1 year', 'wpforms-lite' ),
			'custom' => esc_html__( 'Custom', 'wpforms-lite' ),
		];

		// Bail early, and return the full list of options.
		if ( is_null( $key ) ) {
			return $choices;
		}

		// Return the "Custom" filter if the given key is not found.
		$key = isset( $choices[ $key ] ) ? $key : 'custom';

		return [ $key, $choices[ $key ] ];
	}

	/**
	 * Concatenate given dates into a single string. i.e. "2023-01-16 - 2023-02-15".
	 *
	 * @since 1.8.2
	 *
	 * @param DateTimeImmutable $start_date Start date.
	 * @param DateTimeImmutable $end_date   End date.
	 * @param int|string        $fallback   Fallback value if dates are not valid.
	 *
	 * @return string
	 */
	private static function concat_dates( $start_date, $end_date, $fallback = '' ) {

		// Bail early, if the given dates are not valid.
		if ( ! ( $start_date instanceof DateTimeImmutable ) || ! ( $end_date instanceof DateTimeImmutable ) ) {
			return $fallback;
		}

		return implode(
			self::TIMESPAN_DELIMITER,
			[
				$start_date->format( self::DATE_FORMAT ),
				$end_date->format( self::DATE_FORMAT ),
			]
		);
	}
}
