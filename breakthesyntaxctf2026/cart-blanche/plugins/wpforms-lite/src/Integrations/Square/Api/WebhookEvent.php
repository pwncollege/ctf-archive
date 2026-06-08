<?php

namespace WPForms\Integrations\Square\Api;

use RuntimeException;
use WPForms\Integrations\Square\Helpers;
use WPForms\Vendor\Square\Utils\WebhooksHelper;

/**
 * Webhook event handler.
 *
 * @since 1.9.5
 */
class WebhookEvent {

	/**
	 * Construct and validate the Square webhook event.
	 *
	 * @since 1.9.5
	 *
	 * @param string $payload        The raw JSON payload from Square.
	 * @param string $signature      The Square webhook signature from headers.
	 * @param string $webhook_secret The webhook signing secret from Square Developer Dashboard.
	 *
	 * @return object The decoded event data.
	 *
	 * @throws RuntimeException If the webhook payload structure is invalid.
	 */
	public static function construct_event( string $payload, string $signature, string $webhook_secret ) {

		// Validate the webhook signature.
		if ( ! WebhooksHelper::isValidWebhookEventSignature( $payload, $signature, $webhook_secret, Helpers::get_webhook_url() ) ) {
			throw new RuntimeException( 'Invalid webhook signature. Possible unauthorized request.' );
		}

		// Decode JSON payload.
		$event = json_decode( $payload, false );

		// Check for JSON decoding errors.
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			throw new RuntimeException( 'Invalid JSON payload' );
		}

		if ( ! $event || ! isset( $event->type, $event->data ) ) {
			throw new RuntimeException( 'Invalid webhook payload structure.' );
		}

		return $event;
	}
}
