<?php

namespace WPForms\Integrations\Square\Api;

use Exception;
use WPForms\Vendor\Square\SquareClient;
use WPForms\Integrations\Square\Helpers;
use WPForms\Integrations\Square\WebhooksHealthCheck;
use WPForms\Vendor\Square\Models\WebhookSubscription;
use WPForms\Vendor\Square\Models\CreateWebhookSubscriptionRequest;
use WPForms\Vendor\Square\Models\UpdateWebhookSubscriptionRequest;

/**
 * Webhooks Manager.
 *
 * @since 1.9.5
 */
class WebhooksManager {

	/**
	 * Square client.
	 *
	 * @since 1.9.5
	 *
	 * @var SquareClient
	 */
	private $client;

	/**
	 * Create webhook endpoint.
	 * Retrieve the existing one when the endpoint already exists.
	 *
	 * @since 1.9.5
	 */
	public function connect() {

		// Security and permissions check.
		if (
			! check_ajax_referer( 'wpforms-admin', 'nonce', false ) ||
			! wpforms_current_user_can()
		) {
			wp_send_json_error( [ 'message ' => esc_html__( 'You are not allowed to perform this action', 'wpforms-lite' ) ] );
		}

		$personal_access_token = ! empty( $_POST['token'] ) ? sanitize_text_field( wp_unslash( $_POST['token'] ) ) : '';

		if ( empty( $personal_access_token ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Personal access token is required.', 'wpforms-lite' ) ] );
		}

		$webhook = $this->create( $personal_access_token );

		// Register AS task.
		( new WebhooksHealthCheck() )->maybe_schedule_task();

		// Store endpoint ID and secret.
		if ( ! empty( $webhook ) ) {
			$this->save_settings( $webhook );

			wp_send_json_success( [ 'message' => esc_html__( 'Webhook created successfully!', 'wpforms-lite' ) ] );
		}

		wp_send_json_error( [ 'message' => esc_html__( 'Failed to create webhook.', 'wpforms-lite' ) ] );
	}

	/**
	 * Create or update a webhook endpoint.
	 *
	 * @since 1.9.5
	 *
	 * @param string $personal_access_token Personal access token.
	 *
	 * @return array Endpoint ID and secret.
	 */
	private function create( string $personal_access_token ): array {

		$this->client = new SquareClient(
			[
				'accessToken' => $personal_access_token,
				'environment' => Helpers::get_mode(),
			]
		);

		// Check if the webhook already exists.
		$existing_webhook = $this->webhook_exists();

		// Prepare a webhook subscription object.
		$webhook_subscription = new WebhookSubscription();
		$webhook_subscription->setName( sprintf( 'WPForms endpoint (%1$s mode)', Helpers::get_mode() ) );
		$webhook_subscription->setNotificationUrl( Helpers::get_webhook_url() );
		$webhook_subscription->setEventTypes( WebhookRoute::get_webhooks_events_list() );

		$webhooks_api = $this->client->getWebhookSubscriptionsApi();

		if ( $existing_webhook ) {
			try {
				// Create an update request and set the subscription payload.
				$request = new UpdateWebhookSubscriptionRequest();

				$request->setSubscription( $webhook_subscription );

				// Update the existing webhook subscription.
				$response = $webhooks_api->updateWebhookSubscription( $existing_webhook['id'], $request );

				if ( $response->isSuccess() ) {
					$subscription = $response->getResult()->getSubscription();

					return [
						'id'            => $subscription->getId(),
						'signature_key' => $existing_webhook['signature_key'] ?? '', // getSignatureKey() isn't available in the update response, fall back to the existing webhook's signature key.
					];
				}
				// If the update fails, return the existing webhook details.
				return $existing_webhook;
			} catch ( Exception $e ) {
				return $existing_webhook;
			}
		}

		// Create a new webhook subscription if none exists.
		$request = new CreateWebhookSubscriptionRequest( $webhook_subscription );

		$request->setIdempotencyKey( uniqid() );

		try {
			// Create the webhook subscription.
			$response = $webhooks_api->createWebhookSubscription( $request );

			if ( $response->isSuccess() ) {
				$subscription = $response->getResult()->getSubscription();

				return [
					'id'            => $subscription->getId(),
					'signature_key' => $subscription->getSignatureKey(),
				];
			}

			return [];
		} catch ( Exception $e ) {
			return [];
		}
	}

	/**
	 * Check if webhook already exists.
	 *
	 * @since 1.9.5
	 *
	 * @return array
	 */
	private function webhook_exists(): array {

		try {
			$response = $this->client->getWebhookSubscriptionsApi()->listWebhookSubscriptions();

			if ( ! $response->isSuccess() || empty( $response->getResult()->getSubscriptions() ) ) {
				return [];
			}

			foreach ( $response->getResult()->getSubscriptions() as $subscription ) {

				if ( $subscription->getNotificationUrl() !== Helpers::get_webhook_url() ) {
					continue;
				}

				$signature = $this->client->getWebhookSubscriptionsApi()->retrieveWebhookSubscription( $subscription->getId() );

				return $signature->isSuccess() ? [
					'id'            => $signature->getResult()->getSubscription()->getId(),
					'signature_key' => $signature->getResult()->getSubscription()->getSignatureKey(),
				] : [];
			}
		} catch ( Exception $e ) {
			return [];
		}

		return [];
	}


	/**
	 * Save webhook settings.
	 *
	 * @since 1.9.5
	 *
	 * @param array $webhook Webhook endpoint.
	 */
	private function save_settings( array $webhook ) {

		$mode     = Helpers::get_mode();
		$settings = (array) get_option( 'wpforms_settings', [] );

		// Save webhooks endpoint ID and secret.
		$settings[ 'square-webhooks-id-' . $mode ]     = sanitize_text_field( $webhook['id'] );
		$settings[ 'square-webhooks-secret-' . $mode ] = sanitize_text_field( $webhook['signature_key'] );

		WebhooksHealthCheck::save_status( WebhooksHealthCheck::ENDPOINT_OPTION, WebhooksHealthCheck::STATUS_OK );

		// Enable webhooks setting shouldn't be rewritten.
		if ( empty( $settings['square-webhooks-enabled'] ) ) {
			$settings['square-webhooks-enabled'] = true;
		}

		update_option( 'wpforms_settings', $settings );
	}
}
