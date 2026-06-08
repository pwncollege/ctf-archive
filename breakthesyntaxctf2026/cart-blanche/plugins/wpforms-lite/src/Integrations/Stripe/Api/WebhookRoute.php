<?php

namespace WPForms\Integrations\Stripe\Api;

use Exception;
use WPForms\Integrations\Stripe\Api\Webhooks\Exceptions\AmountMismatchException;
use WPForms\Vendor\Stripe\Webhook;
use RuntimeException;
use BadMethodCallException;
use WPForms\Vendor\Stripe\Event as StripeEvent;
use WPForms\Vendor\Stripe\Exception\SignatureVerificationException;
use WPForms\Integrations\Stripe\Helpers;
use WPForms\Integrations\Stripe\WebhooksHealthCheck;

/**
 * Webhooks Rest Route handler.
 *
 * @since 1.8.4
 */
class WebhookRoute extends Common {

	/**
	 * Event type.
	 *
	 * @since 1.8.4
	 *
	 * @var string
	 */
	private $event_type = 'unknown';

	/**
	 * Payload.
	 *
	 * @since 1.8.4
	 *
	 * @var array
	 */
	private $payload = [];

	/**
	 * Response.
	 *
	 * @since 1.8.4
	 *
	 * @var string
	 */
	private $response = '';

	/**
	 * Response code.
	 *
	 * @since 1.8.4
	 *
	 * @var int
	 */
	private $response_code = 200;

	/**
	 * Initialize.
	 *
	 * @since 1.8.4
	 */
	public function init() {

		$this->hooks();
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.8.4
	 */
	private function hooks() {

		if ( $this->is_rest_verification() ) {
			add_action( 'rest_api_init', [ $this, 'register_rest_routes' ] );

			return;
		}

		// Do not serve regular page when it seems Stripe Webhooks are still sending requests to disabled CURL endpoint.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET[ Helpers::get_webhook_endpoint_data()['fallback'] ] )
			&& (
				! Helpers::is_webhook_enabled()
				|| Helpers::is_rest_api_set()
			) ) {
			add_action( 'wp', [ $this, 'dispatch_with_error_500' ] );

			return;
		}

		if ( ! Helpers::is_webhook_enabled() || ! Helpers::is_webhook_configured() ) {
			return;
		}

		if ( Helpers::is_rest_api_set() ) {
			add_action( 'rest_api_init', [ $this, 'register_rest_routes' ] );
		} else {
			add_action( 'wp', [ $this, 'dispatch_with_url_param' ] );
		}
	}

	/**
	 * Register webhook REST route.
	 *
	 * @since 1.8.4
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
				'callback'            => [ $this, 'dispatch_stripe_webhooks_payload' ],
				'show_in_index'       => false,
				'permission_callback' => '__return_true',
			]
		);
	}

	/**
	 * Dispatch Stripe webhooks payload for the url param.
	 *
	 * @since 1.8.4
	 */
	public function dispatch_with_url_param() {

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! isset( $_GET[ Helpers::get_webhook_endpoint_data()['fallback'] ] ) ) {
			return;
		}

		$this->dispatch_stripe_webhooks_payload();
	}

	/**
	 * Dispatch Stripe webhooks payload for the url param with error 500.
	 *
	 * Runs when url param is not configured or webhooks are not enabled at all.
	 *
	 * @since 1.8.4
	 */
	public function dispatch_with_error_500() {

		$this->response      = esc_html__( 'It seems to be request to Stripe PHP Listener method handler but the site is not configured to use it.', 'wpforms-lite' );
		$this->response_code = 500;

		$this->respond();
	}

	/**
	 * Dispatch Stripe webhooks payload.
	 *
	 * @since 1.8.4
	 */
	public function dispatch_stripe_webhooks_payload() {

		if ( $this->is_rest_verification() ) {
			wp_send_json_success();
		}

		try {
			$this->payload = file_get_contents( 'php://input' );
			$event         = Webhook::constructEvent(
				$this->payload,
				$this->get_webhook_signature(),
				$this->get_webhook_signing_secret()
			);

			// Update webhooks site health status.
			WebhooksHealthCheck::save_status( WebhooksHealthCheck::ENDPOINT_OPTION, WebhooksHealthCheck::STATUS_OK );
			WebhooksHealthCheck::save_status( WebhooksHealthCheck::SIGNATURE_OPTION,WebhooksHealthCheck::STATUS_OK );

			$this->event_type = $event->type;
			$this->response   = 'WPForms Stripe: ' . $this->event_type . ' event received.';

			$processed = $this->process_event( $event );

			$this->response_code = $processed ? 200 : 202;

			$this->respond();
		} catch ( AmountMismatchException $e ) {

			$this->response_code = 202;
			$this->response      = $e->getMessage();

			$this->respond();
		} catch ( SignatureVerificationException $e ) {

			WebhooksHealthCheck::save_status( WebhooksHealthCheck::SIGNATURE_OPTION, WebhooksHealthCheck::STATUS_ERROR );

			$this->response_code = 500;
			$this->response      = $e->getMessage();

			$this->respond();
		} catch ( Exception $e ) {
			$this->handle_exception( $e );

			$this->response      = $e->getMessage();
			$this->response_code = $e instanceof BadMethodCallException ? 501 : 500;

			$this->respond();
		}
	}

	/**
	 * Get webhook stripe signature.
	 *
	 * @since 1.8.4
	 *
	 * @throws RuntimeException When Stripe signature is not set.
	 *
	 * @return string
	 */
	private function get_webhook_signature() {

		if ( ! isset( $_SERVER['HTTP_STRIPE_SIGNATURE'] ) ) {
			throw new RuntimeException( 'Stripe signature is not set.' );
		}

		return $_SERVER['HTTP_STRIPE_SIGNATURE']; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
	}

	/**
	 * Get webhook signing secret.
	 *
	 * @since 1.8.4
	 *
	 * @throws RuntimeException When webhook signing secret is not set.
	 *
	 * @return string
	 */
	private function get_webhook_signing_secret() {

		$secret = wpforms_setting( 'stripe-webhooks-secret-' . Helpers::get_stripe_mode() );

		if ( empty( $secret ) ) {
			throw new RuntimeException( 'Webhook signing secret is not set.' );
		}

		return $secret;
	}

	/**
	 * Process Stripe event.
	 *
	 * @since 1.8.4
	 *
	 * @param StripeEvent $event Stripe event.
	 *
	 * @return bool True if event has handling class, false otherwise.
	 */
	private function process_event( StripeEvent $event ) {

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
	 * Get event whitelist.
	 *
	 * @since 1.8.4
	 *
	 * @return array
	 */
	private static function get_event_whitelist() {

		return [
			'charge.refunded'               => Webhooks\ChargeRefunded::class,
			'charge.refund.updated'         => Webhooks\ChargeRefundUpdated::class,
			'invoice.payment_succeeded'     => Webhooks\InvoicePaymentSucceeded::class,
			'invoice.created'               => Webhooks\InvoiceCreated::class,
			'charge.succeeded'              => Webhooks\ChargeSucceeded::class,
			'customer.subscription.created' => Webhooks\CustomerSubscriptionCreated::class,
			'customer.subscription.updated' => Webhooks\CustomerSubscriptionUpdated::class,
			'customer.subscription.deleted' => Webhooks\CustomerSubscriptionDeleted::class,
		];
	}

	/**
	 * Check if rest verification is requested.
	 *
	 * @since 1.8.4
	 *
	 * @return bool
	 */
	private function is_rest_verification() {

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return isset( $_GET['verify'] ) && $_GET['verify'] === '1';
	}

	/**
	 * Respond to the request.
	 *
	 * @since 1.8.4
	 */
	private function respond() {

		$this->log_webhook();

		wp_die( esc_html( $this->response ), '', (int) $this->response_code );
	}

	/**
	 * Log webhook request.
	 *
	 * @since 1.8.4
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

		// If it is set to explictly display logs on output, return: this would make response to Stripe malformed.
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
	 * Get webhooks events list.
	 *
	 * @since 1.8.4
	 *
	 * @return array
	 */
	public static function get_webhooks_events_list() {

		return array_keys( self::get_event_whitelist() );
	}
}
