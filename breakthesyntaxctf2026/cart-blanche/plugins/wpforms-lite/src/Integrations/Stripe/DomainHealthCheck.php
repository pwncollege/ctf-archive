<?php

namespace WPForms\Integrations\Stripe;

use WPForms\Admin\Notice;
use WPForms\Integrations\Stripe\Api\DomainManager;

/**
 * Domain Health Check class.
 *
 * @since 1.8.6
 */
class DomainHealthCheck {

	/**
	 * AS task name.
	 *
	 * @since 1.8.6
	 */
	const ACTION = 'wpforms_stripe_domain_health_check';

	/**
	 * Admin notice ID.
	 *
	 * @since 1.8.6
	 */
	const NOTICE_ID = 'wpforms_stripe_domain_site_health';

	/**
	 * Domain manager.
	 *
	 * @since 1.8.6
	 *
	 * @var DomainManager
	 */
	private $domain_manager;

	/**
	 * Initialization.
	 *
	 * @since 1.8.6
	 */
	public function init() {

		$this->domain_manager = new DomainManager();

		$this->hooks();
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.8.6
	 */
	private function hooks() {

		add_action( 'admin_notices', [ $this, 'admin_notice' ] );
		add_action( self::ACTION, [ $this, 'process_domain_status_action' ] );
	}

	/**
	 * Schedule domain health check.
	 *
	 * @since 1.8.6
	 */
	public function maybe_schedule_task() {

		/**
		 * Allow customers to disable domain health check task.
		 *
		 * @since 1.8.6
		 *
		 * @param bool $cancel True if task needs to be canceled.
		 */
		$is_canceled = (bool) apply_filters( 'wpforms_integrations_stripe_domain_health_check_cancel', false );

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
		 * Filters the domain health check interval.
		 *
		 * @since 1.8.6
		 *
		 * @param int $interval Interval in seconds.
		 */
		$interval = (int) apply_filters( 'wpforms_integrations_stripe_domain_health_check_interval', DAY_IN_SECONDS );

		$tasks->create( self::ACTION )
			->recurring( time(), $interval )
			->register();
	}

	/**
	 * Process domain status.
	 *
	 * @since 1.8.6
	 */
	public function process_domain_status_action() {

		// Bail out if Stripe account is not connected.
		if ( ! Helpers::has_stripe_keys() ) {
			return;
		}

		$this->domain_manager->validate();
	}

	/**
	 * Display notice about issues with domain.
	 *
	 * @since 1.8.6
	 */
	public function admin_notice() {

		// Only load if we are actually on the settings page.
		if ( ! wpforms_is_admin_page( 'settings' ) ) {
			return;
		}

		// Bail out if Stripe account is not connected.
		if ( ! Helpers::has_stripe_keys() ) {
			return;
		}

		if ( $this->domain_manager->is_domain_active() ) {
			return;
		}

		$notice = sprintf(
			wp_kses( /* translators: %1$s - Stripe.com URL for domains registration documentation. */
				__( 'Heads up! It looks like there\'s a problem with your domain verification, and Stripe Apple Pay may stop working. If this notice does not disappear in a day, <a href="%1$s" rel="nofollow noopener" target="_blank">please register it manually.</a>' , 'wpforms-lite' ),
				[
					'a' => [
						'href'   => [],
						'target' => [],
						'rel'    => [],
					],
				]
			),
			esc_url( 'https://stripe.com/docs/payments/payment-methods/pmd-registration?platform=dashboard#register-your-domain' )
		);

		Notice::error(
			$notice,
			[
				'dismiss' => true,
				'slug'    => self::NOTICE_ID,
			]
		);
	}
}
