<?php

namespace WPForms\Integrations\PayPalCommerce\Process;

use WPForms\Integrations\PayPalCommerce\Connection;
use WPForms\Integrations\PayPalCommerce\Helpers;
use WPForms\Integrations\PayPalCommerce\PayPalCommerce;
use WPForms\Integrations\PayPalCommerce\Process\Exceptions\MissingRequiredShippingParam;

/**
 * PayPal Commerce Single payment processing.
 *
 * @since 1.10.0
 */
class ProcessSingleAjax extends Base {

	/**
	 * Register hooks.
	 *
	 * @since 1.10.0
	 */
	public function hooks(): void {

		add_action( 'wp_ajax_wpforms_paypal_commerce_create_order', [ $this, 'single_checkout_create_order_ajax' ] );
		add_action( 'wp_ajax_nopriv_wpforms_paypal_commerce_create_order', [ $this, 'single_checkout_create_order_ajax' ] );

		$this->init_hook();
	}

	/**
	 * Create the single checkout order.
	 *
	 * @since 1.10.0
	 */
	public function single_checkout_create_order_ajax(): void {

		if (
			! isset( $_POST['nonce'] ) ||
			! wp_verify_nonce( sanitize_key( $_POST['nonce'] ), 'wpforms-paypal-commerce-create-order' )
		) {
			wp_send_json_error( esc_html__( 'You are not allowed to perform this action.', 'wpforms-lite' ) );
		}

		$this->form_id = isset( $_POST['wpforms']['id'] ) ? absint( $_POST['wpforms']['id'] ) : 0;

		if ( empty( $this->form_id ) || ! isset( $_POST['wpforms'], $_POST['total'] ) ) {
			wp_send_json_error( esc_html__( 'Something went wrong. Please contact site administrator.', 'wpforms-lite' ) );
		}

		$this->connection = Connection::get();
		$this->form_data  = wpforms()->obj( 'form' )->get( $this->form_id, [ 'content_only' => true ] );
		$order_data       = $this->prepare_single_order_data();

		if ( ! $this->is_form_ok() ) {
			wp_send_json_error( $this->errors );
		}

		$error_title = esc_html__( 'This order cannot be created because there was an error with the create order API call.', 'wpforms-lite' );

		$api = PayPalCommerce::get_api( $this->connection );

		if ( is_null( $api ) ) {
			wp_send_json_error( $error_title );
		}

		$order_response = $api->create_order( $order_data );

		if ( $order_response->has_errors() ) {
			$order_response_message = $order_response->get_response_message();
			$error_description      = $this->get_order_error_description( $order_response_message );

			$this->log_errors( $error_title, $order_response_message );

			wp_send_json_error( $error_description ?? $error_title ); // phpcs:ignore Universal.Operators.DisallowShortTernary.Found
		}

		$order = $order_response->get_body();

		wp_send_json_success( $order );
	}

	/**
	 * Prepare single payment order data.
	 *
	 * @since 1.10.0
	 *
	 * @return array
	 */
	private function prepare_single_order_data(): array {

		$settings       = $this->get_settings();
		$submitted_data = wp_unslash( $_POST['wpforms'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotValidated
		$this->fields   = $submitted_data['fields'];
		$order_data     = [];

		$order_data['intent']                             = 'CAPTURE';
		$order_data['application_context']['user_action'] = 'CONTINUE';

		$this->currency = $this->get_currency();

		// Get the payment source (PayPal, Apple Pay, Google Pay, venmo, card).
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotValidated
		$payment_source = isset( $_POST['payment_source'] ) ? sanitize_text_field( wp_unslash( $_POST['payment_source'] ) ) : 'paypal';

		// The amount is submitted as a numeric string. We should sanitize it as a number, e.g., without conversion from the current currency.
		$this->amount = Helpers::format_amount_for_api_call( sanitize_text_field( wp_unslash( $_POST['total'] ) ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotValidated

		$order_data['purchase_units'][0] = [
			'amount'      => [
				'value'         => $this->amount,
				'currency_code' => $this->currency,
				'breakdown'     => [
					'item_total' => [
						'value'         => $this->amount,
						'currency_code' => $this->currency,
					],
					'shipping'   => [
						'value'         => 0,
						'currency_code' => $this->currency,
					],
				],
			],
			'description' => empty( $settings['payment_description'] ) ? $this->get_form_name() : html_entity_decode( $settings['payment_description'], ENT_COMPAT, 'UTF-8' ),
			'items'       => $this->get_order_items(),
		];

		$is_supported_shipping = true;

		try {
			// Add payment source configuration.
			$payment_source_config = $this->get_payment_source( $payment_source, $submitted_data );
		} catch ( MissingRequiredShippingParam $exception ) {
			$is_supported_shipping = false;

			$this->log_errors( 'PayPal Commerce: Skipping shipping due to empty required parameter', $exception->getMessage() );
		}

		if ( ! empty( $payment_source_config ) ) {
			$order_data['payment_source'] = $payment_source_config;
		}

		$order_data = $this->update_order_with_shipping( $order_data, $submitted_data, $is_supported_shipping );

		/**
		 * Filter order data before sending to PayPal.
		 *
		 * @since 1.10.0
		 *
		 * @param array $order_data Order data.
		 * @param array $form_data  Form data.
		 * @param float $amount     Order amount.
		 */
		return (array) apply_filters( 'wpforms_paypal_commerce_process_single_ajax_order_data', $order_data, $this->form_data, $this->amount ); // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName
	}

	/**
	 * Retrieve a Form Name.
	 *
	 * @since 1.10.0
	 *
	 * @return string
	 */
	private function get_form_name(): string {

		if ( ! empty( $this->form_data['settings']['form_title'] ) ) {
			return sanitize_text_field( $this->form_data['settings']['form_title'] );
		}

		$form = wpforms()->obj( 'form' )->get( $this->form_data['id'] );

		return $form instanceof \WP_Post ? $form->post_title : sprintf( /* translators: %d - Form ID. */ esc_html__( 'Form #%d', 'wpforms-lite' ), $this->form_data['id'] );
	}

	/**
	 * Get payment source configuration for PayPal API.
	 *
	 * Returns payment source configuration based on the payment method used.
	 * For Apple Pay, Google Pay, and Venmo, specific configurations may be needed.
	 * For standard PayPal and card payments, returns an empty array as SDK handles these.
	 *
	 * @since 1.10.0
	 *
	 * @param string $payment_source Payment source (paypal, applepay, googlepay, venmo, card).
	 * @param array  $submitted_data The submitted form data.
	 *
	 * @throws MissingRequiredShippingParam If required shipping parameter is missing.
	 *
	 * @return array Payment source configuration for PayPal API.
	 *
	 * @noinspection PhpDocRedundantThrowsInspection
	 */
	private function get_payment_source( string $payment_source, array $submitted_data ): array {

		$config         = [];
		$process_method = $this->get_supported_process_method( $payment_source );

		if ( ! $process_method ) {
			return $config;
		}

		$process_method->set_process( $this );

		return $process_method->get_payment_source_on_create( $submitted_data );
	}

	/**
	 * Update the order data with shipping information based on the submitted data.
	 *
	 * @since 1.10.0
	 *
	 * @param array $order_data            The original order data to be updated.
	 * @param array $submitted_data        The data submitted by the user, including shipping details.
	 * @param bool  $is_supported_shipping Whether shipping is supported for the current payment method.
	 *
	 * @return array The updated order data with shipping information included.
	 */
	private function update_order_with_shipping( array $order_data, array $submitted_data, bool $is_supported_shipping ): array {

		if ( ! $is_supported_shipping ) {
			$order_data['application_context']['shipping_preference'] = 'NO_SHIPPING';

			return $order_data;
		}

		$shipping_preference = $this->get_shipping_preference( $submitted_data );

		if ( $shipping_preference === 'NO_SHIPPING' ) {
			$order_data['application_context']['shipping_preference'] = $shipping_preference;

			return $order_data;
		}

		$settings = $this->get_settings();

		$order_data['application_context']['shipping_preference'] = $shipping_preference;
		$order_data['purchase_units'][0]['shipping']              = [
			'address' => ProcessHelper::map_address_field( $submitted_data, $settings['shipping_address'] ),
			'name'    => [
				'full_name' => ProcessHelper::get_submitted_shipping_name_value( $submitted_data, $settings ),
			],
		];

		$email_address = ProcessHelper::get_email_from_settings( $submitted_data, $settings );

		if ( ! $email_address ) {
			return $order_data;
		}

		$order_data['purchase_units'][0]['shipping']['email_address'] = $email_address;

		return $order_data;
	}
}
