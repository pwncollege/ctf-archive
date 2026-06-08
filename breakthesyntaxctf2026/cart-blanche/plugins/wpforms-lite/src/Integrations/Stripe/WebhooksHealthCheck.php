<?php

namespace WPForms\Integrations\Stripe;

use WPForms\Admin\Notice;
use WPForms\Integrations\Stripe\Api\WebhooksManager;

/**
 * Webhooks Health Check class.
 *
 * @since 1.8.4
 */
class WebhooksHealthCheck {

	/**
	 * Endpoint status option name.
	 *
	 * @since 1.8.4
	 */
	const ENDPOINT_OPTION = 'wpforms_stripe_webhooks_endpoint_status';

	/**
	 * Signature status option name.
	 *
	 * @since 1.8.4
	 */
	const SIGNATURE_OPTION = 'wpforms_stripe_webhooks_signature_status';

	/**
	 * Signature verified key.
	 *
	 * @since 1.8.4
	 */
	const STATUS_OK = 'ok';

	/**
	 * Signature error key.
	 *
	 * @since 1.8.4
	 */
	const STATUS_ERROR = 'error';

	/**
	 * AS task name.
	 *
	 * @since 1.8.4
	 */
	const ACTION = 'wpforms_stripe_webhooks_health_check';

	/**
	 * Admin notice ID.
	 *
	 * @since 1.8.4
	 */
	const NOTICE_ID = 'wpforms_stripe_webhooks_site_health';

	/**
	 * Webhooks manager.
	 *
	 * @since 1.8.4
	 *
	 * @var WebhooksManager
	 */
	private $webhooks_manager;

	/**
	 * Initialization.
	 *
	 * @since 1.8.4
	 */
	public function init() {

		$this->webhooks_manager = new WebhooksManager();

		$this->hooks();
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.8.4
	 */
	private function hooks() {

		add_action( 'admin_notices', [ $this, 'admin_notice' ] );
		add_action( self::ACTION, [ $this, 'process_webhooks_status_action' ] );
		add_action( 'action_scheduler/migration_complete', [ $this, 'maybe_schedule_task' ] );
		add_action( 'wpforms_settings_updated', [ $this, 'maybe_webhook_settings_is_updated' ], 10, 3 );
	}

	/**
	 * Schedule webhooks health check.
	 *
	 * @since 1.8.4
	 */
	public function maybe_schedule_task() {

		/**
		 * Allow customers to disable webhooks health check task.
		 *
		 * @since 1.8.4
		 *
		 * @param bool $cancel True if task needs to be canceled.
		 */
		$is_canceled = (bool) apply_filters( 'wpforms_integrations_stripe_webhooks_health_check_cancel', false );

		$tasks = wpforms()->obj( 'tasks' );

		// Bail early in some instances.
		if (
			$is_canceled ||
			! Helpers::has_stripe_keys() ||
			$tasks->is_scheduled( self::ACTION )
		) {
			return;
		}

		/**
		 * Filters the webhooks health check interval.
		 *
		 * @since 1.8.4
		 *
		 * @param int $interval Interval in seconds.
		 */
		$interval = (int) apply_filters( 'wpforms_integrations_stripe_webhooks_health_check_interval', HOUR_IN_SECONDS );

		$tasks->create( self::ACTION )
			->recurring( time(), $interval )
			->register();
	}

	/**
	 * Process webhooks status.
	 *
	 * @since 1.8.4
	 */
	public function process_webhooks_status_action() {

		// Bail out if user unchecked option to enable webhooks.
		if ( ! Helpers::is_webhook_enabled() ) {
			return;
		}

		$last_payment = $this->get_last_stripe_payment();

		// Bail out if there is no Stripe payment,
		// and remove options for reason to avoid any edge cases.
		if ( ! $last_payment ) {
			delete_option( self::SIGNATURE_OPTION );
			delete_option( self::ENDPOINT_OPTION );

			return;
		}

		// Signing secret is expired, try to reconnect.
		if (
			( get_option( self::SIGNATURE_OPTION, self::STATUS_OK ) !== self::STATUS_OK ) &&
			! $this->webhooks_manager->connect()
		) {
			return;
		}

		// If a last Stripe payment has processed status and webhooks are not valid,
		// most likely there is issue with webhooks.
		if (
			$last_payment['status'] === 'processed' &&
			time() > strtotime( $last_payment['date_created_gmt'] ) + 15 * MINUTE_IN_SECONDS &&
			! $this->webhooks_manager->is_valid()
		) {
			self::save_status( self::ENDPOINT_OPTION, self::STATUS_ERROR );

			return;
		}

		self::save_status( self::ENDPOINT_OPTION, self::STATUS_OK );
	}

	/**
	 * Determine whether there is Stripe payment.
	 *
	 * @since 1.8.4
	 *
	 * @return array
	 */
	private function get_last_stripe_payment(): array {

		$payment = wpforms()->obj( 'payment' )->get_payments(
			[
				'gateway' => 'stripe',
				'mode'    => 'any',
				'number'  => 1,
			]
		);

		return ! empty( $payment[0] ) ? $payment[0] : [];
	}

	/**
	 * Display notice about issues with webhooks.
	 *
	 * @since 1.8.4
	 */
	public function admin_notice() {

		// Bail out if Stripe account is not connected.
		if ( ! Helpers::has_stripe_keys() ) {
			return;
		}

		// Bail out if webhooks is not enabled.
		if ( ! Helpers::is_webhook_enabled() ) {
			return;
		}

		// Bail out if webhooks is configured and active.
		if ( Helpers::is_webhook_configured() && $this->is_webhooks_active() ) {
			return;
		}

		// Bail out if there are no Stripe payments.
		if ( ! $this->get_last_stripe_payment() ) {
			return;
		}

		$notice = sprintf(
			wp_kses( /* translators: %s - WPForms.com URL for Stripe webhooks documentation. */
				__( 'Heads up! Looks like you have a problem with your webhooks configuration. Please check and confirm that you\'ve configured the WPForms webhooks in your Stripe account. This notice will disappear automatically when a new Stripe request comes in. See our <a href="%1$s" rel="nofollow noopener" target="_blank">documentation</a> for more information.', 'wpforms-lite' ),
				[
					'a' => [
						'href'   => [],
						'target' => [],
						'rel'    => [],
					],
				]
			),
			esc_url( wpforms_utm_link( 'https://wpforms.com/docs/setting-up-stripe-webhooks/', 'Admin', 'Stripe Webhooks not active' ) )
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
	 * Maybe perform updating of endpoint URL or register AS task in certain cases.
	 *
	 * @since 1.8.4
	 *
	 * @param array $settings     An array of plugin settings.
	 * @param bool  $updated      Whether an option was updated or not.
	 * @param array $old_settings An old array of plugin settings.
	 */
	public function maybe_webhook_settings_is_updated( $settings, $updated, $old_settings ) {

		// Bail out early if Webhooks is not enabled.
		if ( empty( $settings['stripe-webhooks-enabled'] ) ) {
			return;
		}

		// Bail out early if it's not Settings > Payments admin page.
		if (
			! isset( $_POST['nonce'] ) ||
			! wpforms_is_admin_page( 'settings', 'payments' ) ||
			! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'wpforms-settings-nonce' )
		) {
			return;
		}

		// If Webhooks Method is changed, we have to update an endpoint's URL.
		if (
			! empty( $settings['stripe-webhooks-communication'] ) &&
			! empty( $old_settings['stripe-webhooks-communication'] ) &&
			$settings['stripe-webhooks-communication'] !== $old_settings['stripe-webhooks-communication']
		) {
			$this->webhooks_manager->update(
				$this->webhooks_manager->get_id(),
				[
					'url'      => Helpers::get_webhook_url(),
					'disabled' => false,
				]
			);

			return;
		}

		$this->maybe_schedule_task();
	}

	/**
	 * Determine whether webhooks is active.
	 *
	 * @since 1.8.4
	 *
	 * @return bool
	 */
	public function is_webhooks_active() {

		if ( get_option( self::ENDPOINT_OPTION, self::STATUS_OK ) !== self::STATUS_OK ) {
			return false;
		}

		if ( get_option( self::SIGNATURE_OPTION, self::STATUS_OK ) !== self::STATUS_OK ) {
			return false;
		}

		return true;
	}

	/**
	 * Save webhooks status.
	 *
	 * @since 1.8.4
	 *
	 * @param string $option Option name.
	 * @param string $value  Status value.
	 */
	public static function save_status( $option, $value ) {

		if ( ! in_array( $value, [ self::STATUS_OK, self::STATUS_ERROR ], true ) ) {
			return;
		}

		update_option( $option, $value );
	}
}
