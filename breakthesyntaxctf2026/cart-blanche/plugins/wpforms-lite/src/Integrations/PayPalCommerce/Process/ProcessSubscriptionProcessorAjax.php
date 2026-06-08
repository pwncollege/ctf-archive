<?php

namespace WPForms\Integrations\PayPalCommerce\Process;

use WPForms\Integrations\PayPalCommerce\Connection;
use WPForms\Integrations\PayPalCommerce\Helpers;
use WPForms\Integrations\PayPalCommerce\PayPalCommerce;

/**
 * PayPal Commerce Subscription Processor processing.
 *
 * @since 1.10.0
 */
class ProcessSubscriptionProcessorAjax extends Base {

	/**
	 * Register hooks.
	 *
	 * @since 1.10.0
	 */
	public function hooks(): void {

		add_action( 'wp_ajax_wpforms_paypal_commerce_subscription_processor_create', [ $this, 'subscription_processor_create_ajax' ] );
		add_action( 'wp_ajax_nopriv_wpforms_paypal_commerce_subscription_processor_create', [ $this, 'subscription_processor_create_ajax' ] );

		$this->init_hook();
	}

	/**
	 * Create the subscription processor.
	 *
	 * @since 1.10.0
	 */
	public function subscription_processor_create_ajax(): void {

		if (
			! isset( $_POST['nonce'] ) ||
			! wp_verify_nonce( sanitize_key( $_POST['nonce'] ), 'wpforms-paypal-commerce-create-subscription' )
		) {
			wp_send_json_error( esc_html__( 'You are not allowed to perform this action.', 'wpforms-lite' ) );
		}

		$this->form_id = isset( $_POST['wpforms']['id'] ) ? absint( $_POST['wpforms']['id'] ) : 0;

		if ( empty( $this->form_id ) || ! isset( $_POST['wpforms'], $_POST['total'] ) ) {
			wp_send_json_error( esc_html__( 'Something went wrong. Please contact site administrator.', 'wpforms-lite' ) );
		}

		$this->connection = Connection::get();
		$this->form_data  = wpforms()->obj( 'form' )->get( $this->form_id, [ 'content_only' => true ] );
		$order_data       = $this->prepare_order_data();

		if ( ! $this->is_form_ok() ) {
			wp_send_json_error( $this->errors );
		}

		$error_title = esc_html__( 'This subscription cannot be created because there was an error with the create subscription API call.', 'wpforms-lite' );

		$api = PayPalCommerce::get_api( $this->connection );

		if ( is_null( $api ) ) {
			wp_send_json_error( $error_title );
		}

		$order_response = $api->subscription_processor_create( $order_data );

		if ( $order_response->has_errors() ) {
			$order_response_message = $order_response->get_response_message();
			$error_description      = $this->get_order_error_description( $order_response_message );

			$this->log_errors( $error_title, $order_response_message );

			wp_send_json_error( $error_description ?? $error_title );
		}

		$order = $order_response->get_body();

		wp_send_json_success( $order );
	}

	/**
	 * Prepare payment order data.
	 *
	 * @since 1.10.0
	 *
	 * @return array
	 */
	private function prepare_order_data(): array {

		$plan_id = isset( $_POST['planId'] ) && $_POST['planId'] !== '' ? sanitize_text_field( wp_unslash( $_POST['planId'] ) ) : Helpers::get_subscription_plan_id_without_rule( $this->form_data ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

		$error_title = esc_html__( 'This subscription cannot be processed because there was an error with the subscription processing API call.', 'wpforms-lite' );

		if ( $plan_id === '' ) {

			$this->log_errors(
				$error_title,
				'This subscription cannot be processed because the plan does not exist.'
			);

			wp_send_json_error( $error_title );
		}

		$recurring_plan = $this->form_data['payments'][ PayPalCommerce::SLUG ]['recurring'][ $plan_id ];

		if ( empty( $recurring_plan['pp_plan_id'] ) ) {

			$this->log_errors(
				$error_title,
				sprintf(
					'This subscription cannot be processed because the plan named %s does not exist.',
					$recurring_plan['name']
				)
			);

			wp_send_json_error( $error_title );
		}

		// phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotValidated
		$submitted_data = wp_unslash( $_POST['wpforms'] );

		// The amount is submitted as a numeric string. We should sanitize it as a number, e.g., without conversion from the current currency.
		$this->amount = Helpers::format_amount_for_api_call( sanitize_text_field( wp_unslash( $_POST['total'] ) ) );
		// phpcs:enable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotValidated

		$is_shipping_address = isset( $recurring_plan['shipping_address'] ) && $recurring_plan['shipping_address'] !== '' && ProcessHelper::is_address_field_valid( $submitted_data, $recurring_plan['shipping_address'], $this->form_data );

		$this->fields   = $submitted_data['fields'];
		$this->currency = $this->get_currency();

		$order_data = [];

		$order_data['intent']                                     = 'CAPTURE';
		$order_data['application_context']['shipping_preference'] = $is_shipping_address ? 'SET_PROVIDED_ADDRESS' : 'NO_SHIPPING';
		$order_data['application_context']['user_action']         = 'CONTINUE';
		$order_data['total_cycles']                               = $recurring_plan['total_cycles'];
		$order_data['recurring_times']                            = $recurring_plan['recurring_times'];
		$order_data['source']                                     = 'paypal';

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
			'description' => $recurring_plan['name'],
			'items'       => $this->get_order_items(),
			'shipping'    => [
				'name' => [
					'full_name' => '',
				],
			],
		];

		if ( $is_shipping_address ) {
			$order_data['purchase_units'][0]['shipping'] = [
				'address' => [
					'address_line_1' => sanitize_text_field( $submitted_data['fields'][ $recurring_plan['shipping_address'] ]['address1'] ),
					'address_line_2' => isset( $submitted_data['fields'][ $recurring_plan['shipping_address'] ]['address2'] ) ? sanitize_text_field( $submitted_data['fields'][ $recurring_plan['shipping_address'] ]['address2'] ) : '',
					'admin_area_1'   => isset( $submitted_data['fields'][ $recurring_plan['shipping_address'] ]['state'] ) ? sanitize_text_field( $submitted_data['fields'][ $recurring_plan['shipping_address'] ]['state'] ) : '',
					'admin_area_2'   => sanitize_text_field( $submitted_data['fields'][ $recurring_plan['shipping_address'] ]['city'] ),
					'postal_code'    => sanitize_text_field( $submitted_data['fields'][ $recurring_plan['shipping_address'] ]['postal'] ),
					'country_code'   => isset( $submitted_data['fields'][ $recurring_plan['shipping_address'] ]['country'] ) ? sanitize_text_field( $submitted_data['fields'][ $recurring_plan['shipping_address'] ]['country'] ) : 'US',
				],
			];
		}

		/**
		 * Filter order data before sending to PayPal.
		 *
		 * @since 1.10.0
		 *
		 * @param array $order_data Order data.
		 * @param array $form_data  Form data.
		 * @param float $amount     Order amount.
		 */
		return (array) apply_filters( 'wpforms_paypal_commerce_process_subscription_processor_order_data', $order_data, $this->form_data, $this->amount ); // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName
	}
}
