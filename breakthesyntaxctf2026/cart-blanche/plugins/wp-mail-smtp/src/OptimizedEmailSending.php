<?php

namespace WPMailSMTP;

/**
 * OptimizedEmailSending class.
 *
 * @since 4.0.0
 */
class OptimizedEmailSending {

	/**
	 * The slug of the option that toggles optimized email sending.
	 *
	 * @since 4.0.0
	 */
	const SETTINGS_SLUG = 'optimize_email_sending_enabled';

	/**
	 * Register hooks.
	 *
	 * @since 4.0.0
	 */
	public function hooks() {

		if ( self::is_enabled() ) {
			// Enable the queue.
			add_filter( 'wp_mail_smtp_queue_is_enabled', '__return_true' );

			// Avoid enqueueing emails if current request
			// is a cron request, a CLI request,
			// or an ActionScheduler task as 3rd party plugins might
			// be carrying out their own sending optimizations
			// through it.
			if (
				! ( defined( 'WP_CLI' ) && WP_CLI ) &&
				! wp_doing_cron() &&
				! doing_action( 'action_scheduler_run_queue' )
			) {
				// Start enqueueing emails.
				add_filter( 'wp_mail_smtp_mail_catcher_send_enqueue_email', '__return_true' );
			}
		}
	}

	/**
	 * Whether optimized email sending is enabled.
	 *
	 * @since 4.0.0
	 *
	 * @return bool
	 */
	public static function is_enabled() {

		$value = Options::init()->get( 'general', self::SETTINGS_SLUG );

		return (bool) $value;
	}
}
