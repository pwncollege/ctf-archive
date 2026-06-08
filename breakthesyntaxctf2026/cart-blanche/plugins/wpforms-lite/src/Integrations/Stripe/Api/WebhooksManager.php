<?php

namespace WPForms\Integrations\Stripe\Api;

use Exception;
use WPForms\Vendor\Stripe\WebhookEndpoint;
use WPForms\Integrations\Stripe\Helpers;
use WPForms\Integrations\Stripe\WebhooksHealthCheck;

/**
 * Webhooks Manager.
 *
 * @since 1.8.4
 */
class WebhooksManager {

	/**
	 * API version.
	 *
	 * @since 1.8.4
	 *
	 * @var string
	 */
	const STRIPE_API_VERSION = '2023-08-16';

	/**
	 * Determine whether a webhook endpoint is valid.
	 *
	 * @since 1.8.4
	 *
	 * @return bool
	 */
	public function is_valid(): bool {

		$webhook_id = $this->get_id();

		// It's not valid if either endpoint ID or secret is empty.
		if ( empty( $webhook_id ) || empty( $this->get_secret() ) ) {
			return false;
		}

		$webhook = $this->get( $webhook_id );

		if (
			! $webhook ||
			$webhook->status !== 'enabled' ||
			$webhook->url !== Helpers::get_webhook_url() ||
			! empty( array_diff( WebhookRoute::get_webhooks_events_list(), $webhook->enabled_events ) ) // Has unconfigured events.
		) {
			return false;
		}

		return true;
	}

	/**
	 * Retrieve a webhook endpoint ID.
	 *
	 * @since 1.8.4
	 *
	 * @return string
	 */
	public function get_id() {

		return wpforms_setting( 'stripe-webhooks-id-' . Helpers::get_stripe_mode(), '' );
	}

	/**
	 * Retrieve a webhook endpoint secret.
	 *
	 * @since 1.8.4
	 *
	 * @return string
	 */
	private function get_secret() {

		return wpforms_setting( 'stripe-webhooks-secret-' . Helpers::get_stripe_mode(), '' );
	}

	/**
	 * Retrieve a webhook endpoint object.
	 *
	 * @since 1.8.4
	 *
	 * @param string $webhook_id Endpoint ID.
	 *
	 * @return WebhookEndpoint|null
	 */
	private function get( $webhook_id ) {

		try {
			$webhook = WebhookEndpoint::retrieve( $webhook_id, Helpers::get_auth_opts() );
		} catch ( Exception $e ) {
			return null;
		}

		return $webhook;
	}

	/**
	 * Update a webhook endpoint.
	 *
	 * @since 1.8.4
	 *
	 * @param string $id     Endpoint ID.
	 * @param array  $params Params.
	 *
	 * @return WebhookEndpoint|null
	 */
	public function update( $id, $params ) {

		try {
			$webhook = WebhookEndpoint::update( $id, $params, Helpers::get_auth_opts() );
		} catch ( Exception $e ) {
			return null;
		}

		return $webhook;
	}

	/**
	 * Connect webhook endpoint.
	 *
	 * Remove existing endpoints and create a new one.
	 *
	 * @since 1.8.4
	 *
	 * @return bool
	 */
	public function connect(): bool {

		// Prevent duplication of endpoints.
		if ( $this->is_valid() ) {
			return true;
		}

		// Clean up existing endpoints.
		$this->cleanup();

		// Always create a new because you can't get a secret for existing.
		$webhook = $this->create();

		// Register AS task.
		( new WebhooksHealthCheck() )->maybe_schedule_task();

		// Store endpoint ID and secret.
		if ( $webhook ) {
			$this->save_settings( $webhook );

			return true;
		}

		return false;
	}

	/**
	 * Cleanup endpoints.
	 *
	 * @since 1.8.4
	 *
	 * @return bool
	 */
	private function cleanup() {

		try {
			$webhooks = $this->get_all();

			if ( ! $webhooks ) {
				return false;
			}

			$valid_urls = $this->get_valid_urls();

			foreach ( $webhooks as $wh ) {
				if ( in_array( $wh->url, $valid_urls, true ) ) {
					$wh->delete();
				}
			}
		} catch ( Exception $e ) {
			return false;
		}

		return true;
	}

	/**
	 * Retrieve possible endpoint URLs.
	 *
	 * @since 1.8.4
	 *
	 * @return array
	 */
	private function get_valid_urls() {

		$urls = [
			Helpers::get_webhook_url_for_rest(),
			Helpers::get_webhook_url_for_curl(),
		];

		if ( defined( 'WPFORMS_STRIPE_WHURL' ) ) {
			$urls[] = WPFORMS_STRIPE_WHURL;
		}

		return $urls;
	}

	/**
	 * Create a webhook endpoint.
	 *
	 * @since 1.8.4
	 *
	 * @return WebhookEndpoint|bool
	 */
	private function create() {

		try {
			$webhook = WebhookEndpoint::create(
				[
					'url'            => Helpers::get_webhook_url(),
					'enabled_events' => WebhookRoute::get_webhooks_events_list(),
					'connect'        => false,
					'api_version'    => self::STRIPE_API_VERSION,
					'description'    => sprintf(
						'WPForms endpoint (%1$s mode)',
						Helpers::get_stripe_mode()
					),
				],
				Helpers::get_auth_opts()
			);
		} catch ( Exception $e ) {
			return false;
		}

		return $webhook;
	}

	/**
	 * Get list of all webhook endpoints.
	 *
	 * @since 1.8.4
	 *
	 * @return array
	 */
	private function get_all(): array {

		try {
			$webhooks = WebhookEndpoint::all( [], Helpers::get_auth_opts() );
		} catch ( Exception $e ) {
			return [];
		}

		return isset( $webhooks->data ) ? (array) $webhooks->data : [];
	}

	/**
	 * Save webhook settings.
	 *
	 * @since 1.8.4
	 *
	 * @param WebhookEndpoint $webhook Webhook endpoint.
	 */
	private function save_settings( $webhook ) {

		$mode     = Helpers::get_stripe_mode();
		$settings = (array) get_option( 'wpforms_settings', [] );

		// Save webhooks endpoint ID.
		$settings[ 'stripe-webhooks-id-' . $mode ] = sanitize_text_field( $webhook->id );

		// Store webhooks endpoint secret, but it is not defined on ::update() call.
		if ( ! empty( $webhook->secret ) ) {
			$settings[ 'stripe-webhooks-secret-' . $mode ] = sanitize_text_field( $webhook->secret );

			WebhooksHealthCheck::save_status( WebhooksHealthCheck::SIGNATURE_OPTION, WebhooksHealthCheck::STATUS_OK );
		}

		WebhooksHealthCheck::save_status( WebhooksHealthCheck::ENDPOINT_OPTION, WebhooksHealthCheck::STATUS_OK );

		// Enable webhooks setting shouldn't be rewritten.
		if ( ! isset( $settings['stripe-webhooks-enabled'] ) ) {
			$settings['stripe-webhooks-enabled'] = true;
		}

		update_option( 'wpforms_settings', $settings );
	}

	/**
	 * Disconnect webhook endpoints.
	 *
	 * @since 1.9.8
	 *
	 * @return void
	 */
	public function disconnect(): void {

		$this->cleanup();
	}
}
