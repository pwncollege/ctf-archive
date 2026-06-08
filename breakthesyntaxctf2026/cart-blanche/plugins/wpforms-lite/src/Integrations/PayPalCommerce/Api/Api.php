<?php

namespace WPForms\Integrations\PayPalCommerce\Api;

use WPForms\Helpers\Transient;
use WPForms\Integrations\PayPalCommerce\Admin\Connect;
use WPForms\Integrations\PayPalCommerce\Api\Http\Request;
use WPForms\Integrations\PayPalCommerce\Api\Http\Response;
use WPForms\Integrations\PayPalCommerce\Connection;
use WPForms\Integrations\PayPalCommerce\Helpers;

/**
 * API class.
 *
 * @since 1.10.0
 */
class Api {

	/**
	 * Active connection.
	 *
	 * @since 1.10.0
	 *
	 * @var Connection
	 */
	private $connection;

	/**
	 * Request instance.
	 *
	 * @since 1.10.0
	 *
	 * @var Request
	 */
	private $request;

	/**
	 * API constructor.
	 *
	 * @since 1.10.0
	 *
	 * @param Connection $connection Active connection.
	 */
	public function __construct( $connection ) {

		$this->connection = $connection;
		$this->request    = new Request( $connection );
	}

	/**
	 * Generate an access token.
	 * Get merchant information.
	 *
	 * @since 1.10.0
	 *
	 * @return array
	 */
	public function generate_access_token(): array {

		$token_response = $this->access_token_response();

		if ( $token_response->has_errors() ) {
			Helpers::log_errors(
				'PayPal Access Token error.',
				'',
				$token_response->get_response_message()
			);
		}

		return $token_response->get_body();
	}

	/**
	 * Access token response.
	 *
	 * @since 1.10.0
	 *
	 * @return Response
	 */
	private function access_token_response(): Response {

		$args = [
			'headers' => [
				'Authorization' => 'Basic ' . base64_encode( $this->connection->get_merchant_id() . ':' . $this->connection->get_secret() ), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
				'Content-Type'  => 'application/x-www-form-urlencoded',
			],
		];

		return $this->request->request( 'POST', 'oauth/access-token', $args );
	}

	/**
	 * Get merchant information.
	 *
	 * @since 1.10.0
	 *
	 * @return array
	 */
	public function get_merchant_info(): array {

		$mode = $this->connection->get_mode();

		$merchant_info = Transient::get( Connect::MERCHANT_INFO_TRANSIENT_NAME . $mode );

		if ( ! empty( $merchant_info ) ) {
			return $merchant_info;
		}

		if ( Helpers::is_legacy() ) {
			return [];
		}

		$merchant_response = $this->request->get( 'customers/merchant-info' );

		if ( $merchant_response->has_errors() ) {
			Helpers::log_errors(
				'PayPal Merchant Info error.',
				'',
				$merchant_response->get_response_message()
			);

			return [];
		}

		$merchant_info = $merchant_response->get_body();

		Transient::set( Connect::MERCHANT_INFO_TRANSIENT_NAME . $mode, $merchant_info, DAY_IN_SECONDS );

		return $merchant_info;
	}

	/**
	 * Disconnect the merchant.
	 *
	 * @since 1.10.0
	 *
	 * @return bool
	 */
	public function disconnect(): bool {

		$response = $this->request->post( 'customers/disconnect' );

		if ( $response->has_errors() ) {
			$token_response = $this->access_token_response();

			// Force disconnect in case token not found on the server, since we're unable to perform any API call in that case.
			if ( $token_response->get_response_code() === 401 ) {
				return true;
			}

			return false;
		}

		return true;
	}

	/**
	 * Create a new order.
	 *
	 * @since 1.10.0
	 *
	 * @param array $data Order data.
	 *
	 * @return Response
	 */
	public function create_order( array $data ): Response {

		return $this->request->post( 'orders/create', [ 'data' => $data ] );
	}

	/**
	 * Capture an order.
	 *
	 * @since 1.10.0
	 *
	 * @param string $order_id Order ID.
	 *
	 * @return Response
	 */
	public function capture( string $order_id ): Response {

		return $this->request->post( 'orders/capture', [ 'id' => $order_id ] );
	}

	/**
	 * Get an order detail by ID.
	 *
	 * @since 1.10.0
	 *
	 * @param string $order_id Order ID.
	 *
	 * @return array
	 */
	public function get_order( string $order_id ): array {

		$order_response = $this->request->get( 'orders/get', [ 'id' => $order_id ] );

		if ( $order_response->has_errors() ) {
			Helpers::log_errors(
				'PayPal Get Order error.',
				$order_id,
				$order_response->get_response_message()
			);

			return [];
		}

		return $order_response->get_body();
	}

	/**
	 * Refund a captured PayPal payment.
	 *
	 * @since 1.10.0
	 *
	 * @param string $capture_id Capture (transaction) ID.
	 *
	 * @return bool
	 */
	public function refund_payment( string $capture_id ): bool {

		$refund_response = $this->request->post( 'orders/refund', [ 'id' => $capture_id ] );

		if ( ! $refund_response->has_errors() ) {
			return true;
		}

		Helpers::log_errors(
			'PayPal Refund Order error.',
			$capture_id,
			$refund_response->get_response_message()
		);

		return false;
	}

	/**
	 * Create the new subscription processor order.
	 *
	 * @since 1.10.0
	 *
	 * @param array $data Subscription processor order data.
	 *
	 * @return Response
	 */
	public function subscription_processor_create( array $data ): Response {

		return $this->request->post( 'subscriptions/processor/create', [ 'data' => $data ] );
	}

	/**
	 * Capture subscription processor order.
	 *
	 * @since 1.10.0
	 *
	 * @param string $subscription_processor_id Subscription processor order ID.
	 *
	 * @return Response
	 */
	public function subscription_processor_capture( string $subscription_processor_id ): Response {

		return $this->request->post( 'subscriptions/processor/capture', [ 'id' => $subscription_processor_id ] );
	}

	/**
	 * Get the subscription processor order details by ID.
	 *
	 * @since 1.10.0
	 *
	 * @param string $subscription_processor_id Subscription processor order ID.
	 *
	 * @return array
	 */
	public function subscription_processor_get( string $subscription_processor_id ): array {

		$subscription_processor_response = $this->request->get( 'subscriptions/processor/get', [ 'id' => $subscription_processor_id ] );

		if ( $subscription_processor_response->has_errors() ) {
			Helpers::log_errors(
				'PayPal Get Subscription error.',
				$subscription_processor_id,
				$subscription_processor_response->get_response_message()
			);

			return [];
		}

		return $subscription_processor_response->get_body();
	}

	/**
	 * Cancel the subscription processor order.
	 *
	 * @since 1.10.0
	 *
	 * @param string $subscription_processor_id Subscription processor order ID.
	 *
	 * @return Response
	 */
	public function subscription_processor_cancel( string $subscription_processor_id ): Response {

		return $this->request->post( 'subscriptions/processor/cancel', [ 'id' => $subscription_processor_id ] );
	}

	/**
	 * Create a new product.
	 *
	 * @since 1.10.0
	 *
	 * @param array $data Product data.
	 *
	 * @return Response
	 */
	public function create_product( array $data ): Response {

		return $this->request->post( 'products/create', [ 'data' => $data ] );
	}

	/**
	 * Create a new plan.
	 *
	 * @since 1.10.0
	 *
	 * @param array $data Plan data.
	 *
	 * @return Response
	 */
	public function create_plan( array $data ): Response {

		return $this->request->post( 'plans/create', [ 'data' => $data ] );
	}

	/**
	 * Get a subscription.
	 *
	 * @since 1.10.0
	 *
	 * @param string $id           Subscription ID.
	 * @param array  $query_params Query params.
	 *
	 * @return array
	 */
	public function get_subscription( string $id, array $query_params = [] ): array {

		$subscription_response = $this->request->get(
			'subscriptions/get',
			[
				'id'     => $id,
				'params' => $query_params,
			]
		);

		if ( $subscription_response->has_errors() ) {
			Helpers::log_errors(
				'PayPal Get Subscription error.',
				$id,
				$subscription_response->get_response_message()
			);

			return [];
		}

		return $subscription_response->get_body();
	}

	/**
	 * Get a subscription transactions list.
	 *
	 * @since 1.10.0
	 *
	 * @param string $id Subscription ID.
	 *
	 * @return array
	 */
	public function get_subscription_transactions( string $id ): array {

		$start_time = time() - HOUR_IN_SECONDS;

		$params = [
			'start_time' => gmdate( 'Y-m-d\TH:i:s\Z', $start_time ),
			'end_time'   => gmdate( 'Y-m-d\TH:i:s\Z' ),
		];

		$transactions_response = $this->request->get(
			'subscriptions/transactions',
			[
				'id'     => $id,
				'params' => $params,
			]
		);

		if ( $transactions_response->has_errors() ) {
			Helpers::log_errors(
				'PayPal Get Subscription transactions error.',
				$id,
				$transactions_response->get_response_message()
			);

			return [];
		}

		$transactions = $transactions_response->get_body();

		return ! empty( $transactions['transactions'] ) ? (array) $transactions['transactions'] : [];
	}

	/**
	 * Create a new subscription.
	 *
	 * @since 1.10.0
	 *
	 * @param array $data Subscription data.
	 *
	 * @return Response
	 */
	public function create_subscription( array $data ): Response {

		return $this->request->post( 'subscriptions/create', [ 'data' => $data ] );
	}

	/**
	 * Activate an already approved subscription.
	 *
	 * @since 1.10.0
	 *
	 * @param string $subscription_id Approved subscription ID.
	 *
	 * @return Response
	 */
	public function activate_subscription( string $subscription_id ): Response {

		return $this->request->post( 'subscriptions/activate', [ 'id' => $subscription_id ] );
	}

	/**
	 * Cancel a new subscription.
	 *
	 * @since 1.10.0
	 *
	 * @param string $subscription_id Subscription data.
	 *
	 * @return bool
	 */
	public function cancel_subscription( string $subscription_id ): bool {

		try {
			$this->request->post( 'subscriptions/cancel',  [ 'id' => $subscription_id ] );
		} catch ( \Exception $e ) {
			return false;
		}

		return true;
	}

	/**
	 * Generate a client token.
	 *
	 * @since 1.10.0
	 *
	 * @return array
	 */
	public function generate_client_token(): array {

		$token_response = $this->request->post( 'oauth/client-token' );

		if ( $token_response->has_errors() ) {
			return [];
		}

		return $token_response->get_body();
	}

	/**
	 * Generate the SDK client token.
	 *
	 * @since 1.10.0
	 *
	 * @return array
	 */
	public function generate_sdk_client_token(): array {

		$token_response = $this->request->post( 'oauth/sdk-client-token' );

		if ( $token_response->has_errors() ) {
			return [];
		}

		return $token_response->get_body();
	}

	/**
	 * Update customer data.
	 * Allowed data keys:
	 * - license_key.
	 * - webhooks_url.
	 *
	 * @since 1.10.0
	 *
	 * @param array $data Updated data.
	 *
	 * @return Response
	 */
	public function update_customer( array $data ): Response {

		return $this->request->post( 'customers/update', $data );
	}

	/**
	 * Register a domain.
	 *
	 * @since 1.10.0
	 *
	 * @param string $domain Domain to register.
	 *
	 * @return Response
	 */
	public function register_domain( string $domain ): Response {

		return $this->request->post( 'domains/register', [ 'domain' => $domain ] );
	}

	/**
	 * De-register a domain.
	 *
	 * @since 1.10.0
	 *
	 * @param string $domain Domain to de-register.
	 * @param string $reason Reason.
	 *
	 * @return Response
	 */
	public function deregister_domain( string $domain, string $reason ): Response {

		return $this->request->post(
			'domains/deregister',
			[
				'domain' => $domain,
				'reason' => $reason,
			]
		);
	}

	/**
	 * List registered domains.
	 *
	 * @since 1.10.0
	 *
	 * @param int $page_size Domains per page.
	 * @param int $page      Current page number.
	 *
	 * @return array
	 */
	public function list_domains( int $page_size, int $page ): array {

		$domains_response = $this->request->get(
			'domains/list',
			[
				'page_size' => $page_size,
				'page'      => $page,
			]
		);

		if ( $domains_response->has_errors() ) {
			Helpers::log_errors(
				'PayPal List Domains error.',
				'',
				$domains_response->get_response_message()
			);

			return [];
		}

		return $domains_response->get_body();
	}
}
