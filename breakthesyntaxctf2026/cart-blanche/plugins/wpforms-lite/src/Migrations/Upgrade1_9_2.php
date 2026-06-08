<?php

namespace WPForms\Migrations;

use WPForms\Integrations\Stripe\Helpers;
use WPForms\Tasks\Actions\WebhooksAutoConfigurationTask;

/**
 * Class upgrade for 1.9.2 release.
 *
 * @since 1.9.2
 *
 * @noinspection PhpUnused
 */
class Upgrade1_9_2 extends UpgradeBase {

	/**
	 * Run upgrade.
	 *
	 * @since 1.9.2
	 *
	 * @return bool|null
	 */
	public function run() {

		$this->set_webhooks_settings();

		return $this->run_async( WebhooksAutoConfigurationTask::class );
	}

	/**
	 * Set Stripe webhooks settings.
	 *
	 * @since 1.9.2
	 */
	private function set_webhooks_settings() {

		$settings = (array) get_option( 'wpforms_settings', [] );

		// Enable Stripe webhooks by default if account is connected.
		if ( ! isset( $settings['stripe-webhooks-enabled'] ) && Helpers::has_stripe_keys() ) {
			$settings['stripe-webhooks-enabled'] = true;

			update_option( 'wpforms_settings', $settings );
		}
	}
}
