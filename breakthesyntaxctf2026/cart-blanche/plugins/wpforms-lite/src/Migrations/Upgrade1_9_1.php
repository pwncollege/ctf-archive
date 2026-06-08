<?php

namespace WPForms\Migrations;

/**
 * Class upgrade for 1.9.1 release.
 *
 * @since 1.9.1
 */
class Upgrade1_9_1 extends UpgradeBase {

	/**
	 * Delete existed notifications for the customer.
	 *
	 * @since 1.9.1
	 *
	 * @return bool|null Upgrade result:
	 *                    true - the upgrade completed successfully,
	 *                    false - in the case of failure,
	 *                    null - upgrade started but not yet finished (background task).
	 */
	public function run() {

		$this->clean_summaries_cron_event();

		$notifications_option_key = 'wpforms_notifications';
		$notifications            = get_option( $notifications_option_key, [] );

		if ( empty( $notifications['events'] ) ) {
			return true;
		}

		$notifications['events'] = [];

		update_option( 'wpforms_notifications', $notifications );

		return true;
	}

	/**
	 * Clean summaries and entries count cron events,
	 * Since the 1.9.1 release these cron events recurrences have been changed to single event.
	 * The events will be recreated on the next page load.
	 *
	 * @since 1.9.1
	 */
	private function clean_summaries_cron_event() {

		if ( wp_next_scheduled( 'wpforms_weekly_entries_count_cron' ) ) {
			wp_clear_scheduled_hook( 'wpforms_weekly_entries_count_cron' );
		}

		if ( wp_next_scheduled( 'wpforms_email_summaries_cron' ) ) {
			wp_clear_scheduled_hook( 'wpforms_email_summaries_cron' );
		}
	}
}
