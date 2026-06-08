<?php

namespace WPForms\Integrations\PayPalCommerce\Admin;

use WPForms\Integrations\PayPalCommerce\PayPalCommerce;
use WPForms\Integrations\PayPalCommerce\Connection;
use WPForms\Integrations\PayPalCommerce\Helpers;
use stdClass;

/**
 * Builder-related functionality.
 *
 * @since 1.10.0
 */
class Builder {

	/**
	 * Register hooks.
	 *
	 * @since 1.10.0
	 */
	public function hooks(): void {

		add_filter( 'wpforms_builder_save_form_response_data', [ $this, 'maybe_add_plan_data' ], 10, 3 );
	}

	/**
	 * Maybe add plan data.
	 *
	 * @since 1.10.0
	 *
	 * @param array  $response_data Response data.
	 * @param string $form_id       Form ID.
	 * @param array  $form_data     Form data.
	 *
	 * @return array
	 *
	 * @noinspection PhpMissingParamTypeInspection
	 */
	public function maybe_add_plan_data( $response_data, string $form_id, array $form_data ): array {

		$response_data = (array) $response_data;

		// Check required data, settings, and permissions.
		if (
			empty( $form_id ) ||
			! Helpers::is_paypal_commerce_subscriptions_enabled( $form_data ) ||
			! wpforms_current_user_can( 'edit_forms' )
		) {
			return $response_data;
		}

		$response_data['paypal_commerce_plans'] = $this->update_subscription_plans( $form_data );

		return $response_data;
	}

	/**
	 * Update subscription plans data.
	 *
	 * @since 1.10.0
	 *
	 * @param array $form_data Form data.
	 *
	 * @return array
	 */
	private function update_subscription_plans( array $form_data ): array {

		$connection = Connection::get();
		$api        = PayPalCommerce::get_api( $connection );
		$settings   = $form_data['payments'][ PayPalCommerce::SLUG ];
		$data       = [];

		if ( ! isset( $settings['recurring'] ) || is_null( $api ) ) {
			return $data;
		}

		$has_new_plans = false;

		foreach ( $settings['recurring'] as $plan_id => $plan ) {

			if ( ! empty( $plan['pp_plan_id'] ) ) {
				continue;
			}

			$name = sprintf( 'Form ID #%d: %s', absint( $form_data['id'] ), $plan['name'] );

			$plan['pp_product_id'] = $this->create_subscription_product( $name, $plan['product_type'], $api, $form_data['id'] );

			$data[ $plan_id ]['pp_product_id'] = $plan['pp_product_id'];
			$data[ $plan_id ]['pp_plan_id']    = $this->create_subscription_plan( $plan, $name, $api, $form_data['id'] );

			if ( empty( $data[ $plan_id ]['pp_plan_id'] ) ) {
				continue;
			}

			$has_new_plans = true;
		}

		// Update form if new plans were added.
		if ( $has_new_plans ) {
			$form_data['payments'][ PayPalCommerce::SLUG ]['recurring'] = array_replace_recursive( $settings['recurring'], $data );

			wpforms()->obj( 'form' )->update( (int) $form_data['id'], $form_data );
		}

		return $data;
	}

	/**
	 * Create subscription product.
	 *
	 * @since 1.10.0
	 *
	 * @param string $name         Product name.
	 * @param string $product_type Product type.
	 * @param mixed  $api          Api object (current or legacy).
	 * @param string $form_id      Form ID.
	 *
	 * @return string
	 */
	private function create_subscription_product( string $name, string $product_type, $api, string $form_id ): string {

		$product_data = [
			'name' => $name,
			'type' => $product_type,
		];

		$product_response = $api->create_product( $product_data );

		if ( $product_response->has_errors() ) {
			Helpers::log_errors(
				'Create PayPal Subscription Product error.',
				$form_id,
				$product_response->get_response_message()
			);

			return '';
		}

		$body = $product_response->get_body();

		return $body['id'] ?? '';
	}

	/**
	 * Create the subscription plan.
	 *
	 * @since 1.10.0
	 *
	 * @param array  $plan    Plan data.
	 * @param string $name    Plan name.
	 * @param mixed  $api     Api object (current or legacy).
	 * @param string $form_id Form ID.
	 *
	 * @return string
	 */
	private function create_subscription_plan( array $plan, string $name, $api, string $form_id ): string {

		if ( empty( $plan['pp_product_id'] ) ) {
			return '';
		}

		$billing_cycle = new stdClass();

		$billing_cycle->frequency    = $this->get_frequency( $plan['recurring_times'] );
		$billing_cycle->tenure_type  = 'REGULAR';
		$billing_cycle->sequence     = 1;
		$billing_cycle->total_cycles = 0;

		$billing_cycle->pricing_scheme                             = new stdClass();
		$billing_cycle->pricing_scheme->fixed_price                = new stdClass();
		$billing_cycle->pricing_scheme->fixed_price->value         = 1;
		$billing_cycle->pricing_scheme->fixed_price->currency_code = strtoupper( wpforms_get_currency() );

		$plan_data = [
			'product_id'          => $plan['pp_product_id'],
			'name'                => $name,
			'status'              => 'ACTIVE',
			'billing_cycles'      => [ $billing_cycle ],
			'payment_preferences' => [
				'payment_failure_threshold' => isset( $plan['bill_retry'] ) ? 2 : 1,
			],
		];

		$plan_response = $api->create_plan( $plan_data );

		if ( $plan_response->has_errors() ) {
			Helpers::log_errors(
				'PayPal Subscription Plan creation error.',
				$form_id,
				$plan_response->get_response_message()
			);

			return '';
		}

		$body = $plan_response->get_body();

		return $body['id'] ?? '';
	}

	/**
	 * Convert time interval.
	 *
	 * @since 1.10.0
	 *
	 * @param string $recurring_times Time interval.
	 *
	 * @return object
	 */
	private function get_frequency( string $recurring_times ): object {

		$data = new stdClass();

		switch ( $recurring_times ) {
			case 'daily':
				$data->interval_unit  = 'DAY';
				$data->interval_count = 1;
				break;

			case 'weekly':
				$data->interval_unit  = 'WEEK';
				$data->interval_count = 1;
				break;

			case 'monthly':
				$data->interval_unit  = 'MONTH';
				$data->interval_count = 1;
				break;

			case 'quarterly':
				$data->interval_unit  = 'MONTH';
				$data->interval_count = 3;
				break;

			case 'semi-yearly':
				$data->interval_unit  = 'MONTH';
				$data->interval_count = 6;
				break;

			default:
				$data->interval_unit  = 'YEAR';
				$data->interval_count = 1;
				break;
		}

		return $data;
	}
}
