<?php

namespace WPForms\Integrations\Square;

use WPForms\Admin\Notice;

/**
 * Webhooks Health Check class.
 *
 * @since 1.9.5
 */
class WebhooksHealthCheck {

	/**
	 * Endpoint status option name.
	 *
	 * @since 1.9.5
	 */
	public const ENDPOINT_OPTION = 'wpforms_square_webhooks_endpoint_status';

	/**
	 * Signature verified key.
	 *
	 * @since 1.9.5
	 */
	public const STATUS_OK = 'ok';

	/**
	 * Signature error key.
	 *
	 * @since 1.9.5
	 */
	private const STATUS_ERROR = 'error';

	/**
	 * AS task name.
	 *
	 * @since 1.9.5
	 */
	private const ACTION = 'wpforms_square_webhooks_health_check';

	/**
	 * Admin notice ID.
	 *
	 * @since 1.9.5
	 */
	private const NOTICE_ID = 'wpforms_square_webhooks_site_health';

	/**
	 * Initialization.
	 *
	 * @since 1.9.5
	 */
	public function init() {

		$this->hooks();
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.9.5
	 */
	private function hooks() {

		add_action( 'admin_notices', [ $this, 'admin_notice' ] );
		add_action( self::ACTION, [ $this, 'process_webhooks_status_action' ] );
		add_action( 'action_scheduler/migration_complete', [ $this, 'maybe_schedule_task' ] );
	}

	/**
	 * Schedule webhook health check.
	 *
	 * @since 1.9.5
	 */
	public function maybe_schedule_task() {

		/**
		 * Allow customers to disable a webhook health check task.
		 *
		 * @since 1.9.5
		 *
		 * @param bool $cancel True if a task needs to be canceled.
		 */
		$is_canceled = (bool) apply_filters( 'wpforms_integrations_square_webhooks_health_check_cancel', false );

		$tasks = wpforms()->obj( 'tasks' );

		// Bail early in some instances.
		if (
			$is_canceled ||
			$tasks === null ||
			! Helpers::is_square_configured() ||
			$tasks->is_scheduled( self::ACTION )
		) {
			return;
		}

		/**
		 * Filters the webhook health check interval.
		 *
		 * @since 1.9.5
		 *
		 * @param int $interval Interval in seconds.
		 */
		$interval = (int) apply_filters( 'wpforms_integrations_square_webhooks_health_check_interval', HOUR_IN_SECONDS );

		$tasks->create( self::ACTION )
			->recurring( time(), $interval )
			->register();
	}

	/**
	 * Process webhook status.
	 *
	 * @since 1.9.5
	 */
	public function process_webhooks_status_action() {

		// Bail out if user unchecked option to enable webhooks.
		if ( ! Helpers::is_webhook_enabled() ) {
			return;
		}

		$last_payment = $this->get_last_square_payment();

		// Bail out if there is no Square payment,
		// and remove options for reason to avoid any edge cases.
		if ( ! $last_payment ) {
			delete_option( self::ENDPOINT_OPTION );

			return;
		}

		// If a last Square payment has processed status and webhooks are not valid,
		// most likely there is an issue with webhooks.
		if (
			$last_payment['status'] === 'processed' &&
			time() > ( strtotime( $last_payment['date_created_gmt'] ) + ( 15 * MINUTE_IN_SECONDS ) )
		) {
			Helpers::reset_webhook_configuration();
			self::save_status( self::ENDPOINT_OPTION, self::STATUS_ERROR );

			return;
		}

		self::save_status( self::ENDPOINT_OPTION, self::STATUS_OK );
	}

	/**
	 * Determine whether there is Square payment.
	 *
	 * @since 1.9.5
	 *
	 * @return array
	 */
	private function get_last_square_payment(): array {

		$payment = wpforms()->obj( 'payment' )->get_payments(
			[
				'gateway' => 'square',
				'mode'    => 'any',
				'number'  => 1,
			]
		);

		return ! empty( $payment[0] ) ? $payment[0] : [];
	}

	/**
	 * Display notice about issues with webhooks.
	 *
	 * @since 1.9.5
	 */
	public function admin_notice() {

		// Bail out if a Square account is not connected.
		if ( ! Helpers::is_square_configured() ) {
			return;
		}

		// Bail out if webhooks are not enabled.
		if ( ! Helpers::is_webhook_enabled() ) {
			return;
		}

		// Bail out if webhooks are configured and active.
		if ( Helpers::is_webhook_configured() ) {
			return;
		}

		// Show notice only in case if ENDPOINT_OPTION has error status.
		if ( get_option( self::ENDPOINT_OPTION, self::STATUS_OK ) === self::STATUS_OK ) {
			return;
		}

		// Bail out if there are no Square payments.
		if ( ! $this->get_last_square_payment() ) {
			return;
		}

		$notice = sprintf(
			wp_kses( /* translators: %s - WPForms.com URL for Square webhooks documentation. */
				__( 'Looks like you have a problem with your webhooks configuration. Please check and confirm that you\'ve configured the WPForms webhooks in your Square account. This notice will disappear automatically when a new Square request comes in. See our <a href="%1$s" rel="nofollow noopener" target="_blank">documentation</a> for more information.', 'wpforms-lite' ),
				[
					'a' => [
						'href'   => [],
						'target' => [],
						'rel'    => [],
					],
				]
			),
			esc_url( wpforms_utm_link( 'https://wpforms.com/docs/setting-up-square-webhooks/', 'Admin', 'Square Webhooks not active' ) )
		);

		Notice::error(
			$notice,
			[
				'dismiss' => true,
				'slug'    => self::NOTICE_ID,
			]
		);
	}

	/**
	 * Save webhooks status.
	 *
	 * @since 1.9.5
	 *
	 * @param string $option Option name.
	 * @param string $value  Status value.
	 */
	public static function save_status( string $option, string $value ) {

		if ( ! in_array( $value, [ self::STATUS_OK, self::STATUS_ERROR ], true ) ) {
			return;
		}

		update_option( $option, $value );
	}
}
