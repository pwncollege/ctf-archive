<?php

namespace WPForms\Integrations\PayPalCommerce\Api;

use WPForms\Integrations\PayPalCommerce\Helpers;
use WPForms\Integrations\PayPalCommerce\Connection;
use WPForms\Integrations\PayPalCommerce\PayPalCommerce;
use WPForms\Integrations\PayPalCommerce\WebhooksHealthCheck;

/**
 * Webhooks Manager.
 *
 * @since 1.10.0
 */
class WebhooksManager {

	/**
	 * Use the pre-default webhook id for partner's app type.
	 *
	 * @since 1.10.0
	 */
	private const WEBHOOK_ID = 'WEBHOOK_PARTNER';

	/**
	 * Connect webhook endpoint.
	 *
	 * @since 1.10.0
	 *
	 * @return bool
	 */
	public function connect(): bool {

		// Register AS task.
		( new WebhooksHealthCheck() )->maybe_schedule_task();

		$this->save_settings();

		return true;
	}

	/**
	 * Update a webhook endpoint URL for first-party integration.
	 *
	 * Attempts to update the webhook URL stored on our connected server. Saves the health status accordingly.
	 *
	 * @since 1.10.0
	 */
	public function update() {

		$connection = Connection::get();

		if ( ! $connection ) {
			return;
		}

		$api         = PayPalCommerce::get_api( $connection );
		$webhook_url = Helpers::get_webhook_url();

		$api->update_customer( [ 'webhooks_url' => $webhook_url ] );
	}

	/**
	 * Save webhook settings.
	 *
	 * @since 1.10.0
	 */
	private function save_settings() {

		$mode     = Helpers::get_mode();
		$settings = (array) get_option( 'wpforms_settings', [] );

		$settings[ 'paypal-commerce-webhooks-id-' . $mode ] = self::WEBHOOK_ID;

		WebhooksHealthCheck::save_status( WebhooksHealthCheck::ENDPOINT_OPTION, WebhooksHealthCheck::STATUS_OK );

		// Enable webhooks setting shouldn't be rewritten.
		if ( ! isset( $settings['paypal-commerce-webhooks-enabled'] ) ) {
			$settings['paypal-commerce-webhooks-enabled'] = true;
		}

		update_option( 'wpforms_settings', $settings );
	}

	/**
	 * Disconnect webhook.
	 *
	 * @since 1.10.0
	 */
	public function disconnect_webhook() {

		$mode     = Helpers::get_mode();
		$settings = (array) get_option( 'wpforms_settings', [] );

		// Reset webhook health option.
		delete_option( WebhooksHealthCheck::ENDPOINT_OPTION );

		unset( $settings[ 'paypal-commerce-webhooks-id-' . $mode ] );
		update_option( 'wpforms_settings', $settings );
	}

	/**
	 * Attempt to reconnect webhooks if eligible.
	 *
	 * Core manager does not support auto-reconnect; this is a no-op.
	 *
	 * @since 1.10.0
	 *
	 * @return bool False, to indicate no reconnection was attempted.
	 */
	public function reconnect(): bool {

		return false;
	}
}
