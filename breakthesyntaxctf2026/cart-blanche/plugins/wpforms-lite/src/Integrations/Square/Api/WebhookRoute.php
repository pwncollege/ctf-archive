<?php

namespace WPForms\Integrations\Square\Api;

use Exception;
use RuntimeException;
use BadMethodCallException;
use WPForms\Integrations\Square\Helpers;
use WPForms\Integrations\Square\WebhooksHealthCheck;

/**
 * Webhooks Rest Route handler.
 *
 * @since 1.9.5
 */
class WebhookRoute {

	/**
	 * Event type.
	 *
	 * @since 1.9.5
	 *
	 * @var string
	 */
	private $event_type = 'unknown';

	/**
	 * Payload.
	 *
	 * @since 1.9.5
	 *
	 * @var array
	 */
	private $payload = [];

	/**
	 * Response.
	 *
	 * @since 1.9.5
	 *
	 * @var string
	 */
	private $response = '';

	/**
	 * Response code.
	 *
	 * @since 1.9.5
	 *
	 * @var int
	 */
	private $response_code = 200;

	/**
	 * Initialize.
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

		if ( $this->is_rest_verification() ) {
			add_action( 'rest_api_init', [ $this, 'register_rest_routes' ] );

			return;
		}

		// Do not serve regular page when it seems Square Webhooks are still sending requests to disabled CURL endpoint.
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		if (
			isset( $_GET[ Helpers::get_webhook_endpoint_data()['fallback'] ] ) &&
			( ! Helpers::is_webhook_enabled() || Helpers::is_rest_api_set() )
		) {
			add_action( 'wp', [ $this, 'dispatch_with_error_500' ] );

			return;
		}

		// Check if Square is configured.
		if ( ! Helpers::is_square_configured() ) {
			return;
		}

		// phpcs:enable WordPress.Security.NonceVerification.Recommended
		if ( ! Helpers::is_webhook_enabled() || ! Helpers::is_webhook_configured() ) {
			return;
		}

		if ( Helpers::is_rest_api_set() ) {
			add_action( 'rest_api_init', [ $this, 'register_rest_routes' ] );

			return;
		}

		add_action( 'wp', [ $this, 'dispatch_with_url_param' ] );
	}

	/**
	 * Register webhook REST route.
	 *
	 * @since 1.9.5
	 */
	public function register_rest_routes() {

		$methods = [ 'POST' ];

		if ( $this->is_rest_verification() ) {
			$methods[] = 'GET';
		}

		register_rest_route(
			Helpers::get_webhook_endpoint_data()['namespace'],
			'/' . Helpers::get_webhook_endpoint_data()['route'],
			[
				'methods'             => $methods,
				'callback'            => [ $this, 'dispatch_square_webhooks_payload' ],
				'show_in_index'       => false,
				'permission_callback' => '__return_true',
			]
		);
	}

	/**
	 * Dispatch Square webhooks payload for the url param.
	 *
	 * @since 1.9.5
	 */
	public function dispatch_with_url_param() {

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! isset( $_GET[ Helpers::get_webhook_endpoint_data()['fallback'] ] ) ) {
			return;
		}

		$this->dispatch_square_webhooks_payload();
	}

	/**
	 * Dispatch Square webhooks payload for the url param with error 500.
	 *
	 * Runs when url param is not configured or webhooks are not enabled at all.
	 *
	 * @since 1.9.5
	 */
	public function dispatch_with_error_500() {

		$this->response      = esc_html__( 'It seems to be request to Square PHP Listener method handler but the site is not configured to use it.', 'wpforms-lite' );
		$this->response_code = 500;

		$this->respond();
	}

	/**
	 * Dispatch Square webhooks' payload.
	 *
	 * @since 1.9.5
	 *
	 * @throws RuntimeException When Square signature is not set.
	 */
	public function dispatch_square_webhooks_payload() {

		if ( $this->is_rest_verification() ) {
			wp_send_json_success();
		}

		try {
			// Get raw payload and signature.
			$this->payload = file_get_contents( 'php://input' );

			// Construct event.
			$event = WebhookEvent::construct_event(
				$this->payload,
				$this->get_webhook_signature(),
				$this->get_webhook_signing_secret()
			);

			$event_whitelist = self::get_webhooks_events_list();

			if ( ! in_array( $event->type, $event_whitelist, true ) ) {
				throw new RuntimeException( 'Square event type is not whitelisted.' );
			}

			// Update webhook site health status.
			WebhooksHealthCheck::save_status( WebhooksHealthCheck::ENDPOINT_OPTION, WebhooksHealthCheck::STATUS_OK );

			$this->event_type = $event->type;
			$this->response   = 'WPForms Square: ' . $this->event_type . ' event received.';

			$processed = $this->process_event( $event );

			$this->response_code = $processed ? 200 : 202;

			$this->respond();
		} catch ( Exception $e ) {

			$this->response      = $e->getMessage();
			$this->response_code = $e instanceof BadMethodCallException ? 501 : 500;

			$this->respond();
		}
	}

	/**
	 * Get webhook signature.
	 *
	 * @since 1.9.5
	 *
	 * @throws RuntimeException When Square signature is not set.
	 *
	 * @return string
	 */
	private function get_webhook_signature(): string {

		if ( ! isset( $_SERVER['HTTP_X_SQUARE_HMACSHA256_SIGNATURE'] ) ) {
			throw new RuntimeException( 'Square signature is not set.' );
		}

		return sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_SQUARE_HMACSHA256_SIGNATURE'] ) );
	}

	/**
	 * Get webhook signing secret.
	 *
	 * @since 1.9.5
	 *
	 * @throws RuntimeException When webhook signing secret is not set.
	 *
	 * @return string
	 */
	private function get_webhook_signing_secret(): string {

		$secret = wpforms_setting( 'square-webhooks-secret-' . Helpers::get_mode() );

		if ( empty( $secret ) ) {
			throw new RuntimeException( 'Webhook signing secret is not set.' );
		}

		return $secret;
	}

	/**
	 * Process Square event.
	 *
	 * @since 1.9.5
	 *
	 * @param object $event Square event.
	 *
	 * @return bool True if event has handling class, false otherwise.
	 */
	private function process_event( $event ): bool {

		$webhooks = self::get_event_whitelist();

		// Event can't be handled.
		if ( ! isset( $webhooks[ $event->type ] ) || ! class_exists( $webhooks[ $event->type ] ) ) {
			return false;
		}

		$handler = new $webhooks[ $event->type ]();

		$handler->setup( $event );

		return $handler->handle();
	}

	/**
	 * Get event allowlist.
	 *
	 * @since 1.9.5
	 *
	 * @return array
	 */
	private static function get_event_whitelist(): array {

		return [
			'refund.updated'       => Webhooks\RefundUpdated::class,
			'payment.updated'      => Webhooks\PaymentUpdated::class,
			'payment.created'      => Webhooks\PaymentCreated::class,
			'subscription.created' => Webhooks\SubscriptionCreated::class,
			'subscription.updated' => Webhooks\SubscriptionUpdated::class,
		];
	}

	/**
	 * Check if rest verification is requested.
	 *
	 * @since 1.9.5
	 *
	 * @return bool
	 */
	private function is_rest_verification(): bool {

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return isset( $_GET['verify'] ) && $_GET['verify'] === '1';
	}

	/**
	 * Respond to the request.
	 *
	 * @since 1.9.5
	 */
	private function respond() {

		$this->log_webhook();

		wp_die( esc_html( $this->response ), '', (int) $this->response_code );
	}

	/**
	 * Log webhook request.
	 *
	 * @since 1.9.5
	 */
	private function log_webhook() {

		// log only if WP_DEBUG_LOG and WPFORMS_WEBHOOKS_DEBUG are set to true.
		if (
			! defined( 'WPFORMS_WEBHOOKS_DEBUG' ) ||
			! WPFORMS_WEBHOOKS_DEBUG ||
			! defined( 'WP_DEBUG_LOG' ) ||
			! WP_DEBUG_LOG
		) {
			return;
		}

		// If it is set to explicitly display logs on output, return: this would make response to Square malformed.
		if ( defined( 'WP_DEBUG_DISPLAY' ) && WP_DEBUG_DISPLAY ) {
			return;
		}

		$webhook_log = maybe_serialize(
			[
				'event_type'    => $this->event_type,
				'response_code' => $this->response_code,
				'response'      => $this->response,
				'payload'       => $this->payload,
			]
		);

		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		error_log( $webhook_log );
	}

	/**
	 * Get a webhook events list.
	 *
	 * @since 1.9.5
	 *
	 * @return array
	 */
	public static function get_webhooks_events_list(): array {

		return array_keys( self::get_event_whitelist() );
	}
}
