<?php

namespace WPForms\Admin\Helpers;

use DateInterval;
use DatePeriod;
use DateTimeImmutable;

/**
 * Chart dataset processing helper methods.
 *
 * @since 1.8.2
 */
class Chart {

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
	 * Processes the provided dataset to make sure the formatting needed for the "Chart.js" instance is provided.
	 *
	 * @since 1.8.2
	 *
	 * @param array             $query      Dataset retrieved from the database.
	 * @param DateTimeImmutable $start_date Start date for the timespan.
	 * @param DateTimeImmutable $end_date   End date for the timespan.
	 *
	 * @return array
	 */
	public static function process_chart_dataset_data( $query, $start_date, $end_date ) {

		// Bail early if the given query contains no records to iterate.
		if ( ! is_array( $query ) || empty( $query ) ) {
			return [ 0, [] ];
		}

		$dataset        = [];
		$timezone       = wp_timezone(); // Retrieve the timezone object for the site.
		$mysql_timezone = timezone_open( 'UTC' ); // In the database, all datetime are stored in UTC.

		foreach ( $query as $row ) {

			$row_day   = isset( $row['day'] ) ? sanitize_text_field( $row['day'] ) : '';
			$row_count = isset( $row['count'] ) ? abs( (float) $row['count'] ) : 0;

			// Skip the rest of the current iteration if the date (day) is unavailable.
			if ( empty( $row_day ) ) {
				continue;
			}

			// Since we won’t need the initial datetime instances after the query,
			// there is no need to create immutable date objects.
			$row_datetime = date_create_from_format( self::DATETIME_FORMAT, $row_day, $mysql_timezone );

			// Skip the rest of the current iteration if the date creation function fails.
			if ( ! $row_datetime ) {
				continue;
			}

			$row_datetime->setTimezone( $timezone );

			$row_date_formatted = $row_datetime->format( self::DATE_FORMAT );

			// We must take into account entries submitted at different hours of the day,
			// because it is possible that more than one entry could be submitted on a given day.
			if ( ! isset( $dataset[ $row_date_formatted ] ) ) {
				$dataset[ $row_date_formatted ] = $row_count;

				continue;
			}

			$dataset_count                  = $dataset[ $row_date_formatted ];
			$dataset[ $row_date_formatted ] = $dataset_count + $row_count;
		}

		return self::format_chart_dataset_data( $dataset, $start_date, $end_date );
	}

	/**
	 * Format given forms dataset to ensure correct data structure is parsed for serving the "chart.js" instance.
	 * i.e., [ '2023-02-11' => [ 'day' => '2023-02-11', 'count' => 12 ] ].
	 *
	 * @since 1.8.2
	 *
	 * @param array             $dataset    Dataset for the chart.
	 * @param DateTimeImmutable $start_date Start date for the timespan.
	 * @param DateTimeImmutable $end_date   End date for the timespan.
	 *
	 * @return array
	 */
	private static function format_chart_dataset_data( $dataset, $start_date, $end_date ) {

		// In the event that there is no dataset to process, leave early.
		if ( empty( $dataset ) ) {
			return [ 0, [] ];
		}

		$interval           = new DateInterval( 'P1D' ); // Variable that store the date interval of period 1 day.
		$period             = new DatePeriod( $start_date, $interval, $end_date ); // Used for iteration between start and end date period.
		$data               = []; // Placeholder for the actual chart dataset data.
		$total_entries      = 0;
		$has_non_zero_count = false;

		// Use loop to store date into array.
		foreach ( $period as $date ) {

			$date_formatted          = $date->format( self::DATE_FORMAT );
			$count                   = isset( $dataset[ $date_formatted ] ) ? (float) $dataset[ $date_formatted ] : 0;
			$total_entries          += $count;
			$data[ $date_formatted ] = [
				'day'   => $date_formatted,
				'count' => $count,
			];

			// This check helps determine whether there is at least one non-zero count value in the dataset being processed.
			// It's used to optimize the function's behavior and to decide whether to include certain data in the returned result.
			if ( $count > 0 && ! $has_non_zero_count ) {
				$has_non_zero_count = true;
			}
		}

		return [
			$total_entries,
			$has_non_zero_count ? $data : [], // We will return an empty array to indicate that there is no data to display.
		];
	}
}
