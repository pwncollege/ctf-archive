<?php

namespace WPForms\Lite\Reports;

/**
 * Generate form submissions reports.
 *
 * @since 1.5.4
 */
class EntriesCount {

	/**
	 * Constructor.
	 *
	 * @since 1.5.4
	 */
	public function __construct() {}

	/**
	 * Get entries count grouped by form.
	 * Main point of entry to fetch form entry count data from DB.
	 * Cache the result.
	 *
	 * @since 1.5.4
	 *
	 * @return array
	 */
	public function get_by_form() {

		// Get form IDs.
		$forms = wpforms()->obj( 'form' )->get( '', [ 'fields' => 'ids' ] );

		// Return early if no forms found.
		if ( empty( $forms ) || ! is_array( $forms ) ) {
			return [];
		}

		$results = [];

		// Iterate through form IDs.
		foreach ( $forms as $form_id ) {
			// Get entries count for the form.
			$count = absint( get_post_meta( $form_id, 'wpforms_entries_count', true ) );

			// Skip if the count is empty.
			if ( empty( $count ) ) {
				continue;
			}

			// Add form details to the result.
			$results[ $form_id ] = [
				'form_id' => $form_id,
				'count'   => $count,
				'title'   => get_the_title( $form_id ),
			];
		}

		// Sort forms by entries count (desc).
		if ( ! empty( $results ) ) {
			uasort(
				$results,
				function ( $a, $b ) {
					return ( $a['count'] > $b['count'] ) ? -1 : 1;
				}
			);
		}

		return $results;
	}

	/**
	 * Retrieve and calculate form trends data for Lite users.
	 *
	 * This function calculates and returns trends data for Lite users based on the total number
	 * of entries submitted per week compared to the previous week's total entries. Optionally
	 * updates the database with the calculated data.
	 *
	 * @since 1.8.8
	 *
	 * @return array
	 */
	public function get_form_trends() {

		// Get form IDs.
		$results = $this->get_by_form();

		// Collection of form IDs that don't have valid previous week's count data.
		$maybe_unset_form_ids = [];

		foreach ( $results as $form_id => &$form ) {
			// Retrieve the previous week's count data from post meta.
			$previous_week_count = get_post_meta( $form_id, 'wpforms_entries_count_previous_week', true );

			// Continue to the next form if the count data is not valid.
			if ( ! is_array( $previous_week_count ) || count( $previous_week_count ) !== 3 ) {
				$maybe_unset_form_ids[] = $form_id;

				continue;
			}

			// Continue to the next form if the previous week's count data is not valid.
			if ( count( array_unique( $previous_week_count ) ) === 1 ) {
				continue;
			}

			list( $total_previous_week, $count_previous_week, $prev_count_previous_week ) = $previous_week_count;

			// Calculate the form's trends data.
			$form['total']               = $total_previous_week + $count_previous_week;
			$form['count']               = $form['total'] - $total_previous_week;
			$form['count_previous_week'] = $prev_count_previous_week;

			// If both the current week's count and the previous week's count are zero, set trends to zero.
			if ( $form['count_previous_week'] === 0 && $form['count'] === 0 ) {
				$form['trends'] = 0;

				continue;
			}

			// If trends are set to be skipped, set trends to zero, and set the previous week's count to zero.
			// Thies's been needed since at this stage we don't know the number of entries submitted in the previous week.
			if ( (bool) get_post_meta( $form_id, 'wpforms_entries_count_previous_week_skip_trends', true ) ) {
				$form['trends']              = 0;
				$form['count_previous_week'] = 0;

				continue;
			}

			$form['trends'] = $this->get_calculated_trends( $form['count'], $form['count_previous_week'] );
		}

		// Unset forms that don't have valid previous week's count data.
		return $this->maybe_unset_form_ids( $results, $maybe_unset_form_ids );
	}

	/**
	 * Unsets forms from the results array that lack valid previous week's count data.
	 *
	 * This function checks for the presence of valid previous week's count data for each form in the
	 * provided results array. If all forms in the array lack valid data, the original results array is
	 * returned without any changes. Otherwise, forms without valid data are unset from the array.
	 *
	 * @since 1.8.8
	 *
	 * @param array $results              The original array of form results.
	 * @param array $maybe_unset_form_ids The form IDs that may need to be unset.
	 *
	 * @return array
	 */
	private function maybe_unset_form_ids( $results, $maybe_unset_form_ids ) {

		if ( empty( $maybe_unset_form_ids ) ) {
			return $results;
		}

		// If all forms don't have valid previous week's count data, return early.
		if ( count( $maybe_unset_form_ids ) === count( $results ) ) {
			return $results;
		}

		// Unset forms that don't have valid previous week's count data.
		foreach ( $maybe_unset_form_ids as $form_id ) {
			unset( $results[ $form_id ] );
		}

		return $results;
	}

	/**
	 * Get the calculated trends based on the count and count from the previous week.
	 *
	 * This function calculates and returns the trends based on the current count
	 * and the count from the previous week.
	 *
	 * @since 1.8.8
	 *
	 * @param int $count               The current count.
	 * @param int $count_previous_week The count from the previous week.
	 *
	 * @return int
	 */
	private function get_calculated_trends( $count, $count_previous_week ) {

		// If count from the previous week is zero, set trends to 100 to avoid division by zero.
		return ( $count_previous_week === 0 ) ? 100 : round( ( $count - $count_previous_week ) / $count_previous_week * 100 );
	}
}
