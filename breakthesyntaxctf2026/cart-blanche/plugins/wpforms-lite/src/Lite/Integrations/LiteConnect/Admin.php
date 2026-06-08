<?php

namespace WPForms\Lite\Integrations\LiteConnect;

use WPForms\Admin\Notice;
use WPForms\Helpers\Transient;

/**
 * Handles admin functionalities and setup for the application.
 *
 * @since 1.10.0.1
 */
class Admin {

	/**
	 * Dismiss notice slug.
	 *
	 * @since 1.10.0.1
	 */
	private const DISMISS_NOTICE_SLUG = 'lite_connect_send_error_alert';

	/**
	 * Initializes the constructor and setup hooks.
	 *
	 * @since 1.10.0.1
	 */
	public function __construct() {

		$this->hooks();
	}

	/**
	 * Registers hooks to manage the display of notices on specific pages.
	 *
	 * @since 1.10.0.1
	 */
	private function hooks(): void {

		// Reset expired dismissed notice to allow re-displaying after one week.
		add_filter( 'option_wpforms_admin_notices', [ $this, 'reset_expired_notice' ] );

		// Display notices only on WPForms pages.
		if ( $this->is_notice_target_page() ) {
			// Display an admin notice if there are entries available to import.
			add_action( 'admin_notices', [ $this, 'display_error_notices' ] );
		}
	}

	/**
	 * Reset the dismissed notice if it was dismissed more than a week ago.
	 *
	 * @since 1.10.0.1
	 *
	 * @param mixed $notices Dismissed notices option value.
	 *
	 * @return array Modified dismissed notices option value.
	 */
	public function reset_expired_notice( $notices ): array { // phpcs:ignore WPForms.PHP.HooksMethod.InvalidPlaceForAddingHooks

		$notices = (array) $notices;

		if ( ! isset( $notices[ self::DISMISS_NOTICE_SLUG ] ) ) {
			return $notices;
		}

		$notice_time = $notices[ self::DISMISS_NOTICE_SLUG ]['time'] ?? 0;

		if ( ( time() - $notice_time ) <= WEEK_IN_SECONDS ) {
			return $notices;
		}

		Transient::delete( SendEntryTask::SEND_ERROR_KEY );

		unset( $notices[ self::DISMISS_NOTICE_SLUG ] );

		remove_filter( 'option_wpforms_admin_notices', [ $this, 'reset_expired_notice' ] );
		update_option( 'wpforms_admin_notices', $notices, true );
		add_filter( 'option_wpforms_admin_notices', [ $this, 'reset_expired_notice' ] );

		return $notices;
	}

	/**
	 * Display error notices for entry backup failures.
	 *
	 * @since 1.10.0.1
	 *
	 * @noinspection HtmlUnknownTarget
	 */
	public function display_error_notices(): void {

		if ( ! self::has_repeated_errors() ) {
			return;
		}

		Notice::error(
			sprintf(
				wp_kses( /* translators: %s - WPForms support URL. */
					__( '<strong>Entry Backup Failures Detected</strong><br>Some entry backups may not have been saved. This is usually caused by a scheduling issue on your site. Please <a href="%s" target="_blank" rel="noopener noreferrer">contact Support</a> so we can help resolve it and ensure your entries are protected.', 'wpforms-lite' ),
					[
						'strong' => [],
						'br'     => [],
						'a'      => [
							'href'   => [],
							'target' => [],
							'rel'    => [],
						],
					]
				),
				esc_url(
					wpforms_utm_link(
						'https://wpforms.com/account/support/',
						'Admin',
						'Contact Support - Lite Connect Backup Failure'
					)
				)
			),
			[
				'dismiss' => Notice::DISMISS_GLOBAL,
				'slug'    => self::DISMISS_NOTICE_SLUG,
			]
		);
	}

	/**
	 * Checks if there are repeated errors in the cached error data.
	 *
	 * @since 1.10.0.1
	 *
	 * @return bool
	 */
	public static function has_repeated_errors(): bool {

		$lite_connect_send_errors = Transient::get( SendEntryTask::SEND_ERROR_KEY );

		if ( empty( $lite_connect_send_errors ) || ! is_array( $lite_connect_send_errors ) ) {
			return false;
		}

		foreach ( $lite_connect_send_errors as $errors ) {
			if ( count( $errors ) >= 4 ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Determines if the current page is the target page for displaying a notice.
	 *
	 * @since 1.10.0.1
	 *
	 * @return bool True if the current page is the target page, false otherwise.
	 */
	private function is_notice_target_page(): bool {

		global $pagenow;

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$is_dashboard_page = $pagenow === 'index.php' && empty( $_GET['page'] );

		return $is_dashboard_page || wpforms_is_admin_page( 'overview' ) || wpforms_is_admin_page( 'settings' );
	}
}
