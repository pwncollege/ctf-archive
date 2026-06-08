<?php

namespace WPForms\Integrations\PayPalCommerce\Process;

use WPForms\Integrations\PayPalCommerce\Admin\Connect;
use WPForms\Integrations\PayPalCommerce\Connection;
use WPForms\Integrations\PayPalCommerce\Helpers;
use WPForms\Integrations\PayPalCommerce\PayPalCommerce;

/**
 * Base payment processing.
 *
 * @since 1.10.0
 */
abstract class Base {

	/**
	 * Form ID.
	 *
	 * @since 1.10.0
	 *
	 * @var int
	 */
	protected $form_id = 0;

	/**
	 * Sanitized submitted field values and data.
	 *
	 * @since 1.10.0
	 *
	 * @var array
	 */
	protected $fields = [];

	/**
	 * Form data and settings.
	 *
	 * @since 1.10.0
	 *
	 * @var array
	 */
	protected $form_data = [];

	/**
	 * Payment amount.
	 *
	 * @since 1.10.0
	 *
	 * @var string
	 */
	protected $amount = '';

	/**
	 * Payment currency.
	 *
	 * @since 1.10.0
	 *
	 * @var string
	 */
	protected $currency = '';

	/**
	 * Connection data.
	 *
	 * @since 1.10.0
	 *
	 * @var Connection
	 */
	protected $connection;

	/**
	 * PayPal Commerce form errors.
	 *
	 * @since 1.10.0
	 *
	 * @var array
	 */
	protected $errors = [];

	/**
	 * List of methods.
	 *
	 * @since 1.10.0
	 *
	 * @var ProcessMethodBase[]
	 */
	protected $methods = [];

	/**
	 * Initialize the hook for processing.
	 *
	 * @since 1.10.0
	 */
	protected function init_hook(): void {

		/**
		 * Fires after the PayPal Commerce process is initialized.
		 *
		 * @since 1.10.0
		 *
		 * @param Base $this Process instance.
		 */
		do_action( 'wpforms_integrations_paypal_commerce_process_init', $this ); // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName
	}

	/**
	 * Check form settings, fields, etc.
	 *
	 * @since 1.10.0
	 *
	 * @return bool
	 */
	protected function is_form_ok(): bool {

		if ( ! $this->connection ) {
			$error_title    = esc_html__( 'This payment cannot be processed because the account connection is missing.', 'wpforms-lite' );
			$this->errors[] = $error_title;

			$this->log_errors( $error_title );

			return false;
		}

		if ( $this->connection->is_access_token_expired() ) {
			Connect::refresh_access_token( $this->connection );
		}

		// For API calls during the payment processing, we need to have a configured and valid connection.
		// It's supposed that the access token is NOT expired or has already refreshed above.
		if ( ! $this->connection->is_configured() || ! $this->connection->is_valid() ) {
			$error_title    = esc_html__( 'This payment cannot be processed because the account connection is expired or invalid.', 'wpforms-lite' );
			$this->errors[] = $error_title;

			$this->log_errors( $error_title );

			return false;
		}

		return true;
	}

	/**
	 * Retrieve a payment currency.
	 *
	 * @since 1.10.0
	 *
	 * @return string
	 */
	protected function get_currency(): string {

		return strtoupper( wpforms_get_currency() );
	}

	/**
	 * Retrieve a payment amount.
	 *
	 * @since 1.10.0
	 *
	 * @return string
	 */
	protected function get_amount(): string {

		$amount = wpforms_get_total_payment( $this->fields );

		return $amount === false ? 0 : $amount;
	}

	/**
	 * Log payment errors.
	 *
	 * @since 1.10.0
	 *
	 * @param string       $title    Error title.
	 * @param array|string $messages Error messages.
	 * @param string       $level    Error level to add to 'payment' error level.
	 */
	protected function log_errors( string $title, $messages = [], string $level = 'error' ): void {

		Helpers::log_errors( $title, $this->form_id, $messages, $level );
	}

	/**
	 * Retrieve order response error details.
	 *
	 * @since 1.10.0
	 *
	 * @param array $order_response_message Order response message.
	 *
	 * @return string
	 */
	protected function get_order_error_description( array $order_response_message ): string {

		$issue       = sanitize_text_field( $order_response_message['details'][0]['issue'] ?? '' );
		$description = sanitize_text_field( $order_response_message['details'][0]['description'] ?? '' );

		if ( ! $issue && ! $description ) {
			return '';
		}

		return 'API:' . ( $issue ? " ($issue)" : '' ) . ( $description ? " $description" : '' );
	}

	/**
	 * Retrieve order items.
	 *
	 * @since 1.10.0
	 *
	 * @return array
	 */
	protected function get_order_items(): array {

		/**
		 * Filter order items types.
		 *
		 * @since 1.10.0
		 *
		 * @param array $types The order items types.
		 */
		$types = (array) apply_filters( 'wpforms_paypal_commerce_process_single_ajax_get_types', wpforms_payment_fields() ); // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName
		$items = [];

		foreach ( $this->form_data['fields'] as $field_id => $field ) {

			if (
				empty( $field['type'] ) ||
				! in_array( $field['type'], $types, true )
			) {
				continue;
			}

			// Skip the payment field that is not filled in or hidden by CL.
			if (
				! isset( $this->fields[ $field_id ] ) ||
				wpforms_is_empty_string( $this->fields[ $field_id ] )
			) {
				continue;
			}

			$items = $this->prepare_order_line_item( $items, $field );
		}

		return $items;
	}

	/**
	 * Prepare order line item.
	 *
	 * @since 1.10.0
	 *
	 * @param array $items Items.
	 * @param array $field Field data.
	 *
	 * @return array
	 */
	protected function prepare_order_line_item( array $items, array $field ): array {

		$field_id = absint( $field['id'] );
		$quantity = 1;
		$name     = empty( $field['label'] ) ? sprintf( /* translators: %d - Field ID. */ esc_html__( 'Field #%d', 'wpforms-lite' ), $field_id ) : $field['label'];

		if ( wpforms_payment_has_quantity( $field, $this->form_data ) ) {
			$quantity = isset( $_POST['wpforms']['quantities'][ $field['id'] ] ) ? (int) $_POST['wpforms']['quantities'][ $field['id'] ] : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		}

		if ( ! $quantity ) {
			return $items;
		}

		if ( empty( $field['choices'] ) ) {
			$items[] = [
				'name'        => wp_html_excerpt( $name, 124, '...' ), // Limit to 127 characters.
				'quantity'    => $quantity,
				'unit_amount' => [
					'value'         => Helpers::format_amount_for_api_call( wpforms_sanitize_amount( $this->fields[ $field_id ] ) ),
					'currency_code' => $this->currency,
				],
			];

			return $items;
		}

		$choices = ! is_array( $this->fields[ $field_id ] ) ? [ $this->fields[ $field_id ] ] : $this->fields[ $field_id ];

		foreach ( $choices as $choice ) {

			if ( empty( $field['choices'][ $choice ] ) ) {
				continue;
			}

			$choice_name = empty( $field['choices'][ $choice ]['label'] ) ? sprintf( /* translators: %d - choice ID. */ esc_html__( 'Choice %d', 'wpforms-lite' ), absint( $choice ) ) : $field['choices'][ $choice ]['label'];

			$items[] = [
				'name'        => wp_html_excerpt( $name . ': ' . $choice_name, 124, '...' ), // Limit to 127 characters.
				'quantity'    => $quantity,
				'unit_amount' => [
					'value'         => Helpers::format_amount_for_api_call( wpforms_sanitize_amount( $field['choices'][ $choice ]['value'] ) ),
					'currency_code' => $this->currency,
				],
			];
		}

		return $items;
	}

	/**
	 * Map address field from sanitized submitted fields stored in $this->fields.
	 *
	 * @since 1.10.0
	 *
	 * @param string $address_field_id Address field ID.
	 *
	 * @return array
	 */
	protected function map_address_field_from_fields( string $address_field_id ): array {

		$addr = $this->entry['fields'][ $address_field_id ] ?? [];

		return [
			'address_line_1' => sanitize_text_field( $addr['address1'] ?? '' ),
			'address_line_2' => sanitize_text_field( $addr['address2'] ?? '' ),
			'admin_area_1'   => sanitize_text_field( $addr['state'] ?? '' ),
			'admin_area_2'   => sanitize_text_field( $addr['city'] ?? '' ),
			'postal_code'    => sanitize_text_field( $addr['postal'] ?? '' ),
			'country_code'   => sanitize_text_field( $addr['country'] ?? 'US' ),
		];
	}

	/**
	 * Determine if required address parts exist in sanitized fields.
	 *
	 * @since 1.10.0
	 *
	 * @param string $address_field_id Address field ID.
	 *
	 * @return bool
	 */
	protected function is_address_field_valid_from_fields( string $address_field_id ): bool {

		$addr   = $this->fields[ $address_field_id ] ?? [];
		$scheme = $this->form_data['fields'][ $address_field_id ]['scheme'] ?? '';

		return ! empty( $addr['address1'] ) && ! empty( $addr['city'] ) && ! empty( $addr['postal'] ) && ( $scheme !== 'international' || ! empty( $addr['country'] ) );
	}

	/**
	 * Get a fallback form name for order descriptions.
	 * Mirrors Single AJAX implementation.
	 *
	 * @since 1.10.0
	 *
	 * @return string
	 */
	protected function get_form_name_for_order(): string {

		if ( ! empty( $this->form_data['settings']['form_title'] ) ) {
			return sanitize_text_field( $this->form_data['settings']['form_title'] );
		}

		$form = wpforms()->obj( 'form' )->get( $this->form_data['id'] );

		return $form instanceof \WP_Post ? $form->post_title : sprintf( /* translators: %d - Form ID. */ esc_html__( 'Form #%d', 'wpforms-lite' ), $this->form_data['id'] );
	}

	/**
	 * Get the order description based on settings or form name.
	 *
	 * @since 1.10.0
	 *
	 * @return string
	 */
	protected function get_order_description(): string {

		$settings = $this->form_data['payments'][ PayPalCommerce::SLUG ] ?? [];

		return empty( $settings['payment_description'] ) ? $this->get_form_name_for_order() : html_entity_decode( $settings['payment_description'], ENT_COMPAT, 'UTF-8' );
	}

	/**
	 * Get form data.
	 *
	 * @since 1.10.0
	 *
	 * @return array The form data.
	 */
	public function get_form_data(): array {

		return $this->form_data;
	}

	/**
	 * Retrieves the settings configuration for the PayPal Commerce integration.
	 *
	 * @since 1.10.0
	 *
	 * @return array The PayPal Commerce settings as an associative array.
	 */
	public function get_settings(): array {

		return (array) ( $this->form_data['payments'][ PayPalCommerce::SLUG ] ?? [] );
	}

	/**
	 * Get shipping preference based on form settings and submitted data.
	 *
	 * @since 1.10.0
	 *
	 * @param array $submitted_data The submitted form data.
	 *
	 * @return string The shipping preference: 'SET_PROVIDED_ADDRESS' or 'NO_SHIPPING'.
	 */
	public function get_shipping_preference( array $submitted_data ): string {

		$settings = $this->form_data['payments'][ PayPalCommerce::SLUG ] ?? [];

		$is_shipping_address = isset( $settings['shipping_address'] ) &&
								$settings['shipping_address'] !== '' &&
								ProcessHelper::is_address_field_valid( $submitted_data, $settings['shipping_address'], $this->form_data );

		return $is_shipping_address ? 'SET_PROVIDED_ADDRESS' : 'NO_SHIPPING';
	}

	/**
	 * Add a process method.
	 *
	 * @since 1.10.0
	 *
	 * @param ProcessMethodBase $process_method The process method to be added.
	 */
	public function add_process_method( ProcessMethodBase $process_method ): void {

		$this->methods[] = $process_method;
	}

	/**
	 * Retrieve a supported payment method for the provided order data.
	 *
	 * @since 1.10.0
	 *
	 * @param array $order_data Order data containing payment source information.
	 *
	 * @return ProcessMethodBase|null
	 */
	protected function get_supported_process_method_for_order( array $order_data ): ?ProcessMethodBase {

		if ( empty( $order_data['payment_source'] ) || ! is_array( $order_data['payment_source'] ) ) {
			return null;
		}

		$payment_source = key( $order_data['payment_source'] );

		return $this->get_supported_process_method( (string) $payment_source );
	}

	/**
	 * Retrieve the supported process method for a given payment source.
	 *
	 * @since 1.10.0
	 *
	 * @param string $payment_source The payment source identifier.
	 *
	 * @return ProcessMethodBase|null The supported method ProcessMethodBase object or null if none is supported.
	 */
	protected function get_supported_process_method( string $payment_source ): ?ProcessMethodBase {

		foreach ( $this->methods as $method ) {
			if ( $method->get_type() === $payment_source ) {
				return $method;
			}
		}

		return null;
	}
}
