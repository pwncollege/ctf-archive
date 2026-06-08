<?php

namespace WPForms\Integrations\PayPalCommerce\Process;

use stdClass;
use WPForms\Integrations\PayPalCommerce\Connection;
use WPForms\Integrations\PayPalCommerce\Helpers;
use WPForms\Integrations\PayPalCommerce\PayPalCommerce;

/**
 * PayPal Commerce Subscription payment processing.
 *
 * @since 1.10.0
 */
class ProcessSubscriptionAjax extends Base {

	/**
	 * Register hooks.
	 *
	 * @since 1.10.0
	 */
	public function hooks(): void {

		add_action( 'wp_ajax_wpforms_paypal_commerce_create_subscription', [ $this, 'create_subscription_order_ajax' ] );
		add_action( 'wp_ajax_nopriv_wpforms_paypal_commerce_create_subscription', [ $this, 'create_subscription_order_ajax' ] );

		$this->init_hook();
	}

	/**
	 * Create the subscription order.
	 *
	 * @since 1.10.0
	 */
	public function create_subscription_order_ajax(): void {

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

		$this->connection  = Connection::get();
		$this->form_data   = wpforms()->obj( 'form' )->get( $this->form_id, [ 'content_only' => true ] );
		$subscription_data = $this->prepare_subscription_order_data();

		if ( ! $this->is_form_ok() ) {
			wp_send_json_error( $this->errors );
		}

		$error_title = esc_html__( 'This subscription cannot be created because there was an error with the create subscription API call.', 'wpforms-lite' );
		$api         = PayPalCommerce::get_api( $this->connection );

		if ( is_null( $api ) ) {
			wp_send_json_error( $error_title );
		}

		$subscription_response = $api->create_subscription( $subscription_data );

		if ( $subscription_response->has_errors() ) {
			$subscription_response_message = $subscription_response->get_response_message();
			$error_description             = $this->get_order_error_description( $subscription_response_message );

			$this->log_errors( $error_title, $subscription_response_message );

			wp_send_json_error( $error_description ?? $error_title );
		}

		$subscription = $subscription_response->get_body();

		wp_send_json_success( $subscription );
	}

	/**
	 * Prepare subscription payment order data.
	 *
	 * @since 1.10.0
	 *
	 * @return array
	 */
	private function prepare_subscription_order_data(): array { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		// phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotValidated
		$submitted_data = wp_unslash( $_POST['wpforms'] );

		// The amount is submitted as a numeric string. We should sanitize it as a number, e.g., without conversion from the current currency.
		$this->amount = Helpers::format_amount_for_api_call( sanitize_text_field( wp_unslash( $_POST['total'] ) ) );

		$plan_id = isset( $_POST['planId'] ) && $_POST['planId'] !== '' ? sanitize_text_field( wp_unslash( $_POST['planId'] ) ) : Helpers::get_subscription_plan_id_without_rule( $this->form_data );
		// phpcs:enable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotValidated

		$error_title = esc_html__( 'This subscription cannot be processed because there was an error with the subscription processing API call.', 'wpforms-lite' );

		if ( $plan_id === '' ) {

			$this->log_errors(
				$error_title,
				'This subscription cannot be processed because the plan does not exist.'
			);

			wp_send_json_error( $error_title );
		}

		$this->fields   = $submitted_data['fields'];
		$this->currency = $this->get_currency();
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

		$subscription_data = [];

		$plan                = new stdClass();
		$payment_preferences = new stdClass();
		$billing_cycle       = new stdClass();
		$taxes               = new stdClass();

		$billing_cycle->sequence     = 1;
		$billing_cycle->total_cycles = $recurring_plan['total_cycles'];

		$billing_cycle->pricing_scheme = new stdClass();

		$billing_cycle->pricing_scheme->fixed_price                = new stdClass();
		$billing_cycle->pricing_scheme->fixed_price->value         = $this->amount;
		$billing_cycle->pricing_scheme->fixed_price->currency_code = $this->currency;

		$plan->billing_cycles[] = $billing_cycle;

		// Pass Merchant ID to forward the events correctly.
		$subscription_data['custom_id'] = $this->connection->get_merchant_id();

		$subscription_data['plan_id']                   = $recurring_plan['pp_plan_id'];
		$payment_preferences->payment_failure_threshold = isset( $recurring_plan['bill_retry'] ) ? 2 : 1;

		$plan->payment_preferences = $payment_preferences;

		$taxes->inclusive  = true;
		$taxes->percentage = 0;

		$plan->taxes = $taxes;

		$subscription_data['plan'] = $plan;

		$application_context              = new stdClass();
		$application_context->user_action = 'CONTINUE';

		$is_shipping_address = isset( $recurring_plan['shipping_address'] ) && $recurring_plan['shipping_address'] !== '' && ProcessHelper::is_address_field_valid( $submitted_data, $recurring_plan['shipping_address'], $this->form_data );

		if ( $is_shipping_address ) {
			$subscriber                            = new stdClass();
			$subscriber->shipping_address          = new stdClass();
			$subscriber->shipping_address->address = new stdClass();
			$subscriber->shipping_address->name    = new stdClass();

			$subscriber->shipping_address->address->address_line_1 = sanitize_text_field( $submitted_data['fields'][ $recurring_plan['shipping_address'] ]['address1'] );
			$subscriber->shipping_address->address->address_line_2 = isset( $submitted_data['fields'][ $recurring_plan['shipping_address'] ]['address2'] ) ? sanitize_text_field( $submitted_data['fields'][ $recurring_plan['shipping_address'] ]['address2'] ) : '';
			$subscriber->shipping_address->address->admin_area_1   = isset( $submitted_data['fields'][ $recurring_plan['shipping_address'] ]['state'] ) ? sanitize_text_field( $submitted_data['fields'][ $recurring_plan['shipping_address'] ]['state'] ) : '';
			$subscriber->shipping_address->address->admin_area_2   = sanitize_text_field( $submitted_data['fields'][ $recurring_plan['shipping_address'] ]['city'] );
			$subscriber->shipping_address->address->postal_code    = sanitize_text_field( $submitted_data['fields'][ $recurring_plan['shipping_address'] ]['postal'] );
			$subscriber->shipping_address->address->country_code   = isset( $submitted_data['fields'][ $recurring_plan['shipping_address'] ]['country'] ) ? sanitize_text_field( $submitted_data['fields'][ $recurring_plan['shipping_address'] ]['country'] ) : 'US';
			$subscriber->shipping_address->name->full_name         = ProcessHelper::get_submitted_shipping_name_value( $submitted_data, $recurring_plan );

			$subscription_data['subscriber'] = $subscriber;
		}

		$application_context->shipping_preference = $is_shipping_address ? 'SET_PROVIDED_ADDRESS' : 'NO_SHIPPING';

		$subscription_data['application_context'] = $application_context;

		/**
		 * Filter subscription data before sending to PayPal.
		 *
		 * @since 1.10.0
		 *
		 * @param array $subscription_data Subscription data.
		 * @param array $form_data         Form data.
		 * @param float $amount            Order amount.
		 */
		return (array) apply_filters( 'wpforms_paypal_commerce_process_subscription_ajax_subscription_data', $subscription_data, $this->form_data, $this->amount ); // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName
	}
}
