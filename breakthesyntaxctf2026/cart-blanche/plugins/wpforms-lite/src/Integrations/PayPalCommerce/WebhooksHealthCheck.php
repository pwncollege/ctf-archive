<?php

namespace WPForms\Integrations\PayPalCommerce;

use WPForms\Admin\Notice;
use WPForms\Integrations\PayPalCommerce\Api\WebhooksManager;

/**
 * The Webhooks Health Check class.
 *
 * @since 1.10.0
 */
class WebhooksHealthCheck {

	/**
	 * Endpoint status option name.
	 *
	 * @since 1.10.0
	 */
	public const ENDPOINT_OPTION = 'wpforms_paypal_commerce_webhooks_endpoint_status';

	/**
	 * Status OK key.
	 *
	 * @since 1.10.0
	 */
	public const STATUS_OK = 'ok';

	/**
	 * Status ERROR key.
	 *
	 * @since 1.10.0
	 */
	private const STATUS_ERROR = 'error';

	/**
	 * Action Scheduler task name.
	 *
	 * @since 1.10.0
	 */
	private const ACTION = 'wpforms_paypal_commerce_webhooks_health_check';

	/**
	 * Admin notice ID.
	 *
	 * @since 1.10.0
	 */
	private const NOTICE_ID = 'wpforms_paypal_commerce_webhooks_site_health';

	/**
	 * Webhooks manager.
	 *
	 * @since 1.10.0
	 *
	 * @var WebhooksManager
	 */
	private $webhooks_manager;

	/**
	 * Initialization.
	 *
	 * @since 1.10.0
	 */
	public function init(): void {

		$this->webhooks_manager = PayPalCommerce::get_webhooks_manager();

		$this->hooks();
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.10.0
	 */
	private function hooks(): void {

		add_action( 'admin_notices', [ $this, 'admin_notice' ] );
		add_action( self::ACTION, [ $this, 'process_webhooks_status_action' ] );
		add_action( 'action_scheduler/migration_complete', [ $this, 'maybe_schedule_task' ] );
		add_action( 'wpforms_settings_updated', [ $this, 'maybe_webhook_settings_is_updated' ], 10, 3 );
	}

	/**
	 * Schedule webhook health check.
	 *
	 * @since 1.10.0
	 */
	public function maybe_schedule_task(): void {

		/**
		 * Allow disabling the webhook health check task.
		 *
		 * @since 1.10.0
		 *
		 * @param bool $cancel True if the task needs to be canceled.
		 */
		$is_canceled = (bool) apply_filters( 'wpforms_integrations_paypal_commerce_webhooks_health_check_cancel', false ); // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName

		$tasks = wpforms()->obj( 'tasks' );

		// Bail early in some instances.
		if (
			$is_canceled ||
			$tasks === null ||
			! Connection::get() ||
			$tasks->is_scheduled( self::ACTION )
		) {
			return;
		}

		/**
		 * Filters the webhook health check interval.
		 *
		 * @since 1.10.0
		 *
		 * @param int $interval Interval in seconds.
		 */
		$interval = (int) apply_filters( 'wpforms_integrations_paypal_commerce_webhooks_health_check_interval', HOUR_IN_SECONDS ); // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName

		$tasks->create( self::ACTION )
			->recurring( time(), $interval )
			->register();
	}

	/**
	 * Process webhook status.
	 *
	 * @since 1.10.0
	 */
	public function process_webhooks_status_action(): void {

		// Bail out if the user unchecked the option to enable webhooks.
		if ( ! Helpers::is_webhook_enabled() ) {
			return;
		}

		$last_payment = $this->get_last_paypal_payment();

		// Bail out if there is no PayPal Payment and remove the option to avoid edge cases.
		if ( ! $last_payment ) {
			delete_option( self::ENDPOINT_OPTION );

			return;
		}

		// If webhooks previously failed, try to reconnect.
		if ( get_option( self::ENDPOINT_OPTION, self::STATUS_OK ) !== self::STATUS_OK ) {
			$this->webhooks_manager->reconnect();
		}

		// If the last PayPal payment has processed status and some time passed since it was created,
		// assume there might be an issue with webhooks (endpoint not hit).
		if (
			isset( $last_payment['status'], $last_payment['date_created_gmt'] ) &&
			$last_payment['status'] === 'processed' &&
			time() > ( strtotime( $last_payment['date_created_gmt'] ) + ( 15 * MINUTE_IN_SECONDS ) )
		) {
			self::save_status( self::ENDPOINT_OPTION, self::STATUS_ERROR );

			return;
		}

		self::save_status( self::ENDPOINT_OPTION, self::STATUS_OK );
	}

	/**
	 * Determine whether there is a PayPal Commerce payment.
	 *
	 * @since 1.10.0
	 *
	 * @return array
	 */
	private function get_last_paypal_payment(): array {

		$payment = wpforms()->obj( 'payment' )->get_payments(
			[
				'gateway' => 'paypal_commerce',
				'mode'    => 'any',
				'number'  => 1,
			]
		);

		return ! empty( $payment[0] ) ? $payment[0] : [];
	}

	/**
	 * Display notice about issues with webhooks.
	 *
	 * @since 1.10.0
	 */
	public function admin_notice(): void {

		// Bail out if a PayPal account is not connected.
		if ( ! Connection::get() ) {
			return;
		}

		// Bail out if webhooks are not enabled.
		if ( ! Helpers::is_webhook_enabled() ) {
			return;
		}

		// Show notice only in case if ENDPOINT_OPTION has error status.
		if ( get_option( self::ENDPOINT_OPTION, self::STATUS_OK ) === self::STATUS_OK ) {
			return;
		}

		// Bail out if there are no PayPal payments.
		if ( ! $this->get_last_paypal_payment() ) {
			return;
		}

		$notice = sprintf(
			wp_kses( /* translators: %s - WPForms.com URL for PayPal Commerce webhooks documentation. */
				__( 'Looks like you have a problem with your webhooks configuration. Please check and confirm that you\'ve configured the WPForms webhooks in your PayPal account. This notice will disappear automatically when a new PayPal request comes in. See our <a href="%1$s" rel="nofollow noopener" target="_blank">documentation</a> for more information.', 'wpforms-lite' ),
				[
					'a' => [
						'href'   => [],
						'target' => [],
						'rel'    => [],
					],
				]
			),
			esc_url( wpforms_utm_link( 'https://wpforms.com/docs/setting-up-paypal-commerce-webhooks/', 'Admin', 'PayPal Webhooks not active' ) )
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
	 * Maybe perform updating of endpoint URL or register AS the task in certain cases.
	 *
	 * @since 1.10.0
	 *
	 * @param array $settings     An array of plugin settings.
	 * @param bool  $updated      Whether an option was updated or not.
	 * @param array $old_settings An old array of plugin settings.
	 */
	public function maybe_webhook_settings_is_updated( $settings, bool $updated, array $old_settings ): void {

		$settings = (array) $settings;

		// Bail out early if Webhooks is not enabled.
		if ( empty( $settings['paypal-commerce-webhooks-enabled'] ) ) {
			return;
		}

		// Bail out early if it's not the Settings > Payments admin page.
		if (
			! isset( $_POST['nonce'] ) ||
			! wpforms_is_admin_page( 'settings', 'payments' ) ||
			! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'wpforms-settings-nonce' )
		) {
			return;
		}

		// If the Webhooks Method is changed, we have to update an endpoint's URL.
		if (
			! empty( $settings['paypal-commerce-webhooks-communication'] ) &&
			! empty( $old_settings['paypal-commerce-webhooks-communication'] ) &&
			$settings['paypal-commerce-webhooks-communication'] !== $old_settings['paypal-commerce-webhooks-communication']
		) {
			$this->webhooks_manager->update();

			return;
		}

		$this->maybe_schedule_task();
	}

	/**
	 * Save webhooks status.
	 *
	 * @since 1.10.0
	 *
	 * @param string $option Option name.
	 * @param string $value  Status value.
	 */
	public static function save_status( string $option, string $value ): void {

		if ( ! in_array( $value, [ self::STATUS_OK, self::STATUS_ERROR ], true ) ) {
			return;
		}

		update_option( $option, $value );
	}

	/**
	 * Determine whether webhooks are active.
	 *
	 * @since 1.10.0
	 *
	 * @return bool
	 */
	public function is_webhooks_active(): bool {

		if ( get_option( self::ENDPOINT_OPTION, self::STATUS_OK ) !== self::STATUS_OK ) {
			return false;
		}

		return true;
	}
}
