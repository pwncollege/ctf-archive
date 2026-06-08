<?php

namespace WPForms\Integrations\Square;

use WP_Post;
use WPForms\Integrations\Square\Api\Api;
use WPForms\Vendor\Square\Models\Card;
use WPForms\Vendor\Square\Models\ErrorCode;

/**
 * Square payment processing.
 *
 * @since 1.9.5
 */
class Process {

	/**
	 * Form ID.
	 *
	 * @since 1.9.5
	 *
	 * @var int
	 */
	public $form_id = 0;

	/**
	 * Sanitized submitted field values and data.
	 *
	 * @since 1.9.5
	 *
	 * @var array
	 */
	public $fields = [];

	/**
	 * Form submission raw data ($_POST).
	 *
	 * @since 1.9.5
	 *
	 * @var array
	 */
	public $entry = [];

	/**
	 * Form data and settings.
	 *
	 * @since 1.9.5
	 *
	 * @var array
	 */
	public $form_data = [];

	/**
	 * Square payment settings.
	 *
	 * @since 1.9.5
	 *
	 * @var array
	 */
	public $settings = [];

	/**
	 * Square credit card field.
	 *
	 * @since 1.9.5
	 *
	 * @var array
	 */
	public $cc_field = [];

	/**
	 * Payment amount.
	 *
	 * @since 1.9.5
	 *
	 * @var string
	 */
	public $amount = '';

	/**
	 * Payment currency.
	 *
	 * @since 1.9.5
	 *
	 * @var string
	 */
	public $currency = '';

	/**
	 * Connection data.
	 *
	 * @since 1.9.5
	 *
	 * @var Connection
	 */
	public $connection;

	/**
	 * Main class that communicates with the Square API.
	 *
	 * @since 1.9.5
	 *
	 * @var Api
	 */
	protected $api;

	/**
	 * Processing errors.
	 *
	 * @since 1.9.5
	 *
	 * @var array
	 */
	protected $errors;

	/**
	 * Save matched subscription settings.
	 *
	 * @since 1.9.5
	 *
	 * @var array
	 */
	private $subscription_settings = [];

	/**
	 * Whether the payment has been processed.
	 *
	 * @since 1.9.5
	 *
	 * @var bool
	 */
	protected $is_payment_processed = false;

	/**
	 * Initialize.
	 *
	 * @since 1.9.5
	 */
	public function init(): Process {

		$this->hooks();

		return $this;
	}

	/**
	 * Hooks.
	 *
	 * @since 1.9.5
	 */
	protected function hooks() {

		add_action( 'wpforms_process', [ $this, 'process_entry' ], 10, 3 );
		add_action( 'wpforms_process_entry_saved', [ $this, 'update_entry_meta' ], 10, 4 );
		add_filter( 'wpforms_forms_submission_prepare_payment_data', [ $this, 'prepare_payment_data' ], 10, 3 );
		add_filter( 'wpforms_forms_submission_prepare_payment_meta', [ $this, 'prepare_payment_meta' ], 10, 3 );
		add_action( 'wpforms_process_payment_saved', [ $this, 'process_payment_saved' ], 10, 3 );
	}

	/**
	 * Check if a payment exists with an entry, if so validate and process.
	 *
	 * @since 1.9.5
	 *
	 * @param array $fields    Final/sanitized submitted fields data.
	 * @param array $entry     Copy of original $_POST.
	 * @param array $form_data Form data and settings.
	 */
	public function process_entry( $fields, array $entry, array $form_data ) {

		$fields = (array) $fields;

		// Check if payment method exists and is enabled.
		if ( ! Helpers::is_payments_enabled( $form_data ) ) {
			return;
		}

		$this->fields     = $fields;
		$this->entry      = $entry;
		$this->form_data  = $form_data;
		$this->form_id    = isset( $form_data['id'] ) ? (int) $form_data['id'] : 0;
		$this->settings   = $form_data['payments']['square'];
		$this->cc_field   = $this->get_credit_card_field();
		$this->currency   = $this->get_currency();
		$this->amount     = $this->get_amount();
		$this->connection = Connection::get();

		// Before proceeding, check if any basic errors were detected.
		if ( ! $this->is_form_processed() ) {
			$this->display_errors();

			return;
		}

		// Set an API instance.
		$this->api = new Api( $this->connection );

		// Set tokens provided by Web Payments SDK.
		$this->api->set_payment_tokens( $entry );

		// Proceed to executing the purchase.
		$this->process_payment();

		// Update the card field value to contain basic details.
		$this->update_credit_card_field_value();
	}

	/**
	 * Bypass captcha if payment has been processed.
	 *
	 * @since 1.9.5
	 * @deprecated 1.9.6
	 *
	 * @param bool $bypass_captcha Whether to bypass captcha.
	 *
	 * @return bool
	 */
	public function bypass_captcha( $bypass_captcha ): bool {

		_deprecated_function( __METHOD__, '1.9.6 of the WPForms plugin' );

		if ( (bool) $bypass_captcha ) {
			return true;
		}

		return $this->is_payment_processed;
	}

	/**
	 * Check if form has errors before payment processing.
	 *
	 * @since 1.9.5
	 *
	 * @return bool
	 */
	private function is_form_processed(): bool {

		// Bail in case there are form processing errors.
		if ( ! empty( wpforms()->obj( 'process' )->errors[ $this->form_id ] ) ) {
			return false;
		}

		if ( ! $this->is_card_field_visibility_ok() ) {
			return false;
		}

		return $this->is_form_ok();
	}

	/**
	 * Check form settings, fields, etc.
	 *
	 * @since 1.9.5
	 *
	 * @return bool
	 */
	private function is_form_ok(): bool {

		// Check for Square connection.
		if ( ! $this->is_connection_ok() ) {
			$error_title    = esc_html__( 'Square payment stopped, account connection is missing.', 'wpforms-lite' );
			$this->errors[] = $error_title;

			$this->log_errors( $error_title );

			return false;
		}

		// Check total charge amount.
		// Square has different minimum amount limits by country.
		if ( ! $this->is_amount_ok() ) {
			$error_title    = esc_html__( 'Square payment stopped, amount is smaller than the allowed minimum amount for a payment.', 'wpforms-lite' );
			$this->errors[] = $error_title;

			$this->log_errors(
				$error_title,
				[
					'amount'   => $this->amount,
					'currency' => $this->currency,
				]
			);

			return false;
		}

		// Check that, despite how the form is configured, the form and
		// entry actually contain payment fields, otherwise no need to proceed.
		if ( empty( $this->cc_field ) || ! wpforms_has_payment( 'form', $this->fields ) ) {
			$error_title    = esc_html__( 'Square payment stopped, missing payment fields.', 'wpforms-lite' );
			$this->errors[] = $error_title;

			$this->log_errors( $error_title );

			return false;
		}

		return true;
	}

	/**
	 * Check if the Square credit card field in the form is visible (not hidden by conditional logic).
	 *
	 * @since 1.9.5
	 *
	 * @return bool
	 */
	private function is_card_field_visibility_ok(): bool {

		// If the form doesn't contain the credit card field.
		if ( empty( $this->cc_field ) ) {
			return false;
		}

		// If the form contains no fields with conditional logic, the credit card field is visible by default.
		if ( empty( $this->form_data['conditional_fields'] ) ) {
			return true;
		}

		// If the credit card field is NOT in array of conditional fields, it's visible.
		if ( ! in_array( $this->cc_field['id'], $this->form_data['conditional_fields'], true ) ) {
			return true;
		}

		// If the credit card field IS in array of conditional fields and marked as visible, it's visible.
		if ( ! empty( $this->cc_field['visible'] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if connection exists, configured and valid.
	 *
	 * @since 1.9.5
	 *
	 * @return bool
	 */
	private function is_connection_ok(): bool {

		return $this->connection !== null && $this->connection->is_usable();
	}

	/**
	 * Check if an amount is greater than the minimum amount.
	 *
	 * @since 1.9.5
	 *
	 * @link https://developer.squareup.com/docs/build-basics/working-with-monetary-amounts#monetary-amount-limits
	 *
	 * @return bool
	 */
	private function is_amount_ok(): bool {

		$amount = Helpers::format_amount( $this->amount );

		if ( $amount < 1 && in_array( $this->currency, [ 'USD', 'CAD' ], true ) ) {
			return false;
		}

		if ( $amount < 100 && in_array( $this->currency, [ 'EUR', 'GBP', 'AUD', 'JPY' ], true ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Process a payment.
	 *
	 * @since 1.9.5
	 */
	private function process_payment() {

		if ( ! empty( $this->settings['enable_recurring'] ) ) {

			$this->process_payment_subscription();

			return;
		}

		$this->process_payment_single();
	}

	/**
	 * Process a single payment.
	 *
	 * @since 1.9.5
	 */
	protected function process_payment_single() {

		$args = $this->get_payment_args_single();

		$this->api->process_single_transaction( $args );

		// Set payment processing flag.
		$this->is_payment_processed = true;

		$this->process_api_errors( 'single' );
	}

	/**
	 * Process a subscription payment.
	 *
	 * @since 1.9.5
	 */
	private function process_payment_subscription() {

		$args = $this->get_payment_args_general();

		foreach ( $this->settings['recurring'] as $recurring ) {

			if ( ! $this->is_subscription_plan_valid( $recurring ) ) {
				continue;
			}

			// Put subscription arguments into its own key.
			$args['subscription']        = $this->get_payment_args_subscription( $recurring );
			$this->subscription_settings = $args['subscription'];

			$this->api->process_subscription_transaction( $args );

			// Set payment processing flag.
			$this->is_payment_processed = true;

			$this->process_api_errors( 'subscription' );

			return;
		}

		if ( ! empty( $this->settings['enable_one_time'] ) ) {
			$this->process_payment_single();

			return;
		}

		$this->log_errors(
			esc_html__( 'Square Subscription payment stopped, validation error.', 'wpforms-lite' ),
			$this->fields,
			'conditional_logic'
		);
	}

	/**
	 * Retrieve subscription payment args.
	 *
	 * @since 1.9.5
	 *
	 * @param array $plan Plan settings.
	 *
	 * @return array
	 */
	private function get_payment_args_subscription( array $plan ): array {

		$args_sub['customer']['email']      = sanitize_email( $this->fields[ $plan['customer_email'] ]['value'] );
		$args_sub['customer']['first_name'] = sanitize_text_field( $this->fields[ $plan['customer_name'] ]['first'] );
		$args_sub['customer']['last_name']  = sanitize_text_field( $this->fields[ $plan['customer_name'] ]['last'] );

		// If a Name field has the `Simple` format.
		if (
			empty( $args_sub['customer']['first_name'] ) &&
			empty( $args_sub['customer']['last_name'] ) &&
			! empty( $this->fields[ $plan['customer_name'] ]['value'] )
		) {
			$args_sub['customer']['last_name'] = sanitize_text_field( $this->fields[ $plan['customer_name'] ]['value'] );
		}

		// Customer address.
		if ( isset( $plan['customer_address'] ) && $plan['customer_address'] !== '' && wpforms()->is_pro() ) {
			$args_sub['customer']['address'] = $this->fields[ $plan['customer_address'] ];
		}

		$cadences_list = Helpers::get_subscription_cadences();
		$phase_cadence = $cadences_list[ $plan['phase_cadence'] ] ?? $cadences_list['yearly'];

		// Subscription cadence.
		$args_sub['phase_cadence'] = $phase_cadence;

		$plan_name  = $this->get_form_name();
		$plan_name .= empty( $plan['name'] ) ? '' : ': ' . $plan['name'];

		$args_sub['plan_name']           = sprintf( '%s (%s)', $plan_name, $phase_cadence['name'] );
		$args_sub['plan_variation_name'] = sprintf( '%s (%s %s %s)', $plan['name'], $this->amount, $this->currency, $phase_cadence['name'] );

		// Card holder.
		$args_sub['card_name'] = empty( $this->fields[ $this->cc_field['id'] ]['cardname'] ) ? '' : sanitize_text_field( $this->fields[ $this->cc_field['id'] ]['cardname'] );

		/**
		 * Filter subscription payment arguments.
		 *
		 * @since 1.9.5
		 *
		 * @param array   $args    The subscription payment arguments.
		 * @param Process $process The Process instance.
		 */
		return (array) apply_filters( 'wpforms_integrations_square_process_get_payment_args_subscription', $args_sub, $this );
	}

	/**
	 * Validate plan before process.
	 *
	 * @since 1.9.5
	 *
	 * @param array $plan Plan settings.
	 *
	 * @return bool
	 */
	protected function is_subscription_plan_valid( array $plan ): bool {

		return ! empty( $plan['customer_email'] ) && $this->is_recurring_settings_ok( $plan );
	}

	/**
	 * Check if recurring settings is configured correctly.
	 *
	 * @since 1.9.5
	 *
	 * @param array $settings Settings data.
	 *
	 * @return bool
	 */
	protected function is_recurring_settings_ok( array $settings ): bool {

		$error = '';

		// Check subscription settings are provided.
		if ( empty( $settings['phase_cadence'] ) || empty( $settings['customer_email'] ) || empty( $settings['customer_name'] ) ) {
			$error = esc_html__( 'Square subscription payment stopped, missing form settings.', 'wpforms-lite' );
		}

		// Check for required customer email.
		if ( ! $error && empty( $this->fields[ $settings['customer_email'] ]['value'] ) ) {
			$error = esc_html__( 'Square subscription payment stopped, customer email not found.', 'wpforms-lite' );
		}

		// Check for required customer name.
		if ( ! $error && empty( $this->fields[ $settings['customer_name'] ]['value'] ) ) {
			$error = esc_html__( 'Square subscription payment stopped, customer name not found.', 'wpforms-lite' );
		}

		// Before proceeding, check if any basic errors were detected.
		if ( $error ) {
			$this->log_errors( $error, $settings );

			return false;
		}

		return true;
	}

	/**
	 * Retrieve single payment args.
	 *
	 * @since 1.9.5
	 *
	 * @return array
	 */
	private function get_payment_args_single(): array {

		$args           = $this->get_payment_args_general();
		$customer_name  = $this->get_customer_name();
		$customer_email = $this->get_customer_email();

		// Billing Name.
		if ( isset( $customer_name['first_name'] ) ) {
			$args['billing']['first_name'] = sanitize_text_field( $customer_name['first_name'] );
		}

		if ( isset( $customer_name['last_name'] ) ) {
			$args['billing']['last_name'] = sanitize_text_field( $customer_name['last_name'] );
		}

		// Billing Address.
		if ( ! empty( $this->fields[ $this->settings['billing_address'] ] ) ) {
			$args['billing']['address'] = $this->fields[ $this->settings['billing_address'] ];
		}

		// Buyer Email.
		if ( ! empty( $customer_email ) ) {
			$args['buyer_email'] = sanitize_email( $customer_email );
		}

		// Payment description.
		$description = empty( $this->settings['payment_description'] ) ? $this->get_form_name() : html_entity_decode( $this->settings['payment_description'], ENT_COMPAT, 'UTF-8' );

		// The maximum length for the Square notes field is 500 characters.
		$args['note'] = wp_html_excerpt( Square::APP_NAME . ': ' . $description, 500 );

		// Order items.
		$args['order_items'] = $this->get_order_items();

		/**
		 * Filter single payment arguments.
		 *
		 * @param array   $args    The single payment arguments.
		 * @param Process $process The Process instance.
		 *
		 *@since 1.9.5
		 */
		return (array) apply_filters( 'wpforms_square_process_get_payment_args_single', $args, $this ); // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName
	}

	/**
	 * Retrieve arguments for any type of payment.
	 *
	 * @since 1.9.5
	 *
	 * @return array
	 */
	private function get_payment_args_general(): array {
		/**
		 * Filter arguments for any type of payment.
		 *
		 * @since 1.9.5
		 *
		 * @param array   $args    The general payment arguments.
		 * @param Process $process The Process instance.
		 */
		return (array) apply_filters( // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName
			'wpforms_square_process_get_payment_args_general',
			[
				'amount'      => Helpers::format_amount( $this->amount ),
				'currency'    => $this->currency,
				'location_id' => Helpers::get_location_id(),
			],
			$this
		);
	}

	/**
	 * Retrieve a payment currency.
	 *
	 * @since 1.9.5
	 *
	 * @return string
	 */
	private function get_currency(): string {

		return strtoupper( wpforms_get_currency() );
	}

	/**
	 * Retrieve a payment amount.
	 *
	 * @since 1.9.5
	 *
	 * @return string
	 */
	private function get_amount(): string {

		$amount = wpforms_get_total_payment( $this->fields );

		return $amount === false ? wpforms_sanitize_amount( 0 ) : $amount;
	}

	/**
	 * Retrieve order items.
	 *
	 * @since 1.9.5
	 *
	 * @return array
	 */
	private function get_order_items(): array {

		/**
		 * Filter order items types.
		 *
		 * @since 1.9.5
		 *
		 * @param array $types The order items types.
		 */
		$types = (array) apply_filters( 'wpforms_square_process_get_order_items_types', wpforms_payment_fields() ); // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName
		$items = [];

		foreach ( $this->fields as $field_id => $field ) {

			if (
				empty( $field['type'] ) ||
				! in_array( $field['type'], $types, true )
			) {
				continue;
			}

			// Skip payment field that is not filled in.
			if (
				! isset( $this->entry['fields'][ $field_id ] ) ||
				wpforms_is_empty_string( $this->entry['fields'][ $field_id ] )
			) {
				continue;
			}

			$items[] = $this->prepare_order_line_item( $field );
		}

		return $items;
	}

	/**
	 * Retrieve a Form Name.
	 *
	 * @since 1.9.5
	 *
	 * @return string
	 */
	private function get_form_name(): string {

		if ( ! empty( $this->form_data['settings']['form_title'] ) ) {
			return sanitize_text_field( $this->form_data['settings']['form_title'] );
		}

		$fallback = sprintf( /* translators: %d - Form ID. */
			esc_html__( 'Form #%d', 'wpforms-lite' ),
			$this->form_id
		);

		$form_obj = wpforms()->obj( 'form' );

		if ( ! $form_obj ) {
			return $fallback;
		}

		$form = $form_obj->get( $this->form_id );

		return $form instanceof WP_Post ? $form->post_title : $fallback;
	}

	/**
	 * Retrieve a Square credit card field.
	 *
	 * @since 1.9.5
	 *
	 * @return array
	 */
	private function get_credit_card_field(): array {

		if ( ! is_array( $this->fields ) ) {
			return [];
		}

		foreach ( $this->fields as $field ) {
			if ( ! empty( $field['type'] ) && $field['type'] === 'square' ) {
				return $field;
			}
		}

		return [];
	}

	/**
	 * Prepare order line item.
	 *
	 * @since 1.9.5
	 *
	 * @param array $field Field data.
	 *
	 * @return array
	 */
	private function prepare_order_line_item( array $field ): array {

		$field_id = absint( $field['id'] );
		$quantity = isset( $field['quantity'] ) ? (int) $field['quantity'] : 1;
		$name     = empty( $field['name'] ) ? sprintf( /* translators: %d - Field ID. */ esc_html__( 'Field #%d', 'wpforms-lite' ), $field_id ) : $field['name'];
		$item     = [
			'name'     => $name,
			'quantity' => $quantity,
		];

		if ( empty( $field['value_raw'] ) ) {
			$item['amount'] = Helpers::format_amount( $field['amount_raw'] );

			return $item;
		}

		return $this->prepare_order_line_item_variations( $item, $field, $field_id );
	}

	/**
	 * Prepare order line item variations.
	 *
	 * @since 1.9.5
	 *
	 * @param array $item     Item data.
	 * @param array $field    Field data.
	 * @param int   $field_id Field ID.
	 *
	 * @return array
	 */
	private function prepare_order_line_item_variations( array $item, array $field, int $field_id ): array {

		$values = explode( ',', $field['value_raw'] );

		foreach ( $values as $value ) {

			if ( empty( $this->form_data['fields'][ $field_id ]['choices'][ $value ] ) ) {
				continue;
			}

			$choice = $this->form_data['fields'][ $field_id ]['choices'][ $value ];

			$item['variations'][] = [
				'name'           => $item['name'],
				'quantity'       => $item['quantity'],
				'variation_name' => empty( $choice['label'] ) ? sprintf( /* translators: %d - Choice ID. */ esc_html__( 'Choice %d', 'wpforms-lite' ), absint( $value ) ) : $choice['label'],
				'amount'         => Helpers::format_amount( $choice['value'] ),
			];
		}

		return $item;
	}

	/**
	 * Display form errors.
	 *
	 * @since 1.9.5
	 *
	 * @param array $errors Errors to display.
	 */
	private function display_errors( array $errors = [] ) {

		if ( ! $errors ) {
			$errors = $this->errors;
		}

		if ( ! $errors || ! is_array( $errors ) ) {
			return;
		}

		// Check if the form contains a required credit card. If it does
		// and there was an error, return the error to the user and prevent
		// the form from being submitted. This should not occur under normal
		// circumstances.
		if ( empty( $this->cc_field ) || empty( $this->form_data['fields'][ $this->cc_field['id'] ] ) ) {
			return;
		}

		if ( ! empty( $this->form_data['fields'][ $this->cc_field['id'] ]['required'] ) ) {
			wpforms()->obj( 'process' )->errors[ $this->form_id ]['footer'] = implode( '<br>', $errors );
		}
	}

	/**
	 * Collect errors from API and turn it into form errors.
	 *
	 * @since 1.9.5
	 *
	 * @param string $type Payment type (e.g. 'single').
	 */
	private function process_api_errors( string $type ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found

		$errors = $this->api->get_errors();

		if ( empty( $errors ) || ! is_array( $errors ) ) {
			return;
		}

		$this->display_errors( $errors );

		if ( $type === 'subscription' ) {
			$title = esc_html__( 'Square subscription payment stopped', 'wpforms-lite' );
		} else {
			$title = esc_html__( 'Square payment stopped', 'wpforms-lite' );
		}

		$_errors = $this->api->get_response_errors();

		if ( ! empty( $_errors ) ) {
			$this->process_api_errors_codes( $_errors );
			$errors[] = $_errors;
		}

		// Log transaction specific errors.
		$this->log_errors( $title, $errors );
	}

	/**
	 * Check specific error codes.
	 *
	 * @since 1.9.5
	 *
	 * @param array $errors The last API call errors.
	 */
	private function process_api_errors_codes( array $errors ) {

		$codes = $this->get_oauth_error_codes();

		foreach ( $errors as $error ) {

			if (
				empty( $error['code'] ) ||
				! in_array( $error['code'], $codes, true )
			) {
				continue;
			}

			// If the error indicates that access token is bad, set a connection as invalid.
			$this->connection
				->set_status( Connection::STATUS_INVALID )
				->save();
		}
	}

	/**
	 * Retrieve OAuth-related errors.
	 *
	 * @since 1.9.5
	 *
	 * @link https://developer.squareup.com/docs/oauth-api/best-practices#ensure-api-calls-made-with-oauth-tokens-handle-token-based-errors-appropriately
	 *
	 * @return array
	 */
	private function get_oauth_error_codes(): array {

		return [ ErrorCode::ACCESS_TOKEN_EXPIRED, ErrorCode::ACCESS_TOKEN_REVOKED, ErrorCode::UNAUTHORIZED ];
	}

	/**
	 * Log payment errors.
	 *
	 * @since 1.9.5
	 *
	 * @param string       $title    Error title.
	 * @param array|string $messages Error messages.
	 * @param string       $level    Error level to add to 'payment' error level.
	 */
	protected function log_errors( string $title, $messages = [], string $level = 'error' ) {

		wpforms_log(
			$title,
			$messages,
			[
				'type'    => [ 'payment', $level ],
				'form_id' => $this->form_id,
			]
		);
	}

	/**
	 * Update the credit card field value to contain basic details.
	 *
	 * @since 1.9.5
	 */
	private function update_credit_card_field_value() {

		if ( $this->errors || ! $this->api ) {
			return;
		}

		// Get a card.
		$card = $this->get_card();

		if ( empty( $card ) ) {
			return;
		}

		$details = [
			'brand'  => $card->getCardBrand(),
			'last4'  => $card->getLast4(),
			'holder' => $this->get_card_holder( $card ),
		];
		$details = implode( "\n", array_filter( $details ) );

		/**
		 * Filter a credit card field value by card details.
		 *
		 * @since 1.9.5
		 *
		 * @param string  $details Card details.
		 * @param Process $process Process object.
		 */
		$details = apply_filters( // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName
			'wpforms_square_process_update_credit_card_field_value',
			$details,
			$this
		);

		wpforms()->obj( 'process' )->fields[ $this->cc_field['id'] ]['value'] = $details;
	}

	/**
	 * Get card object.
	 *
	 * @since 1.9.5
	 *
	 * @return Card|array
	 */
	private function get_card() {

		$resource = $this->api->get_response_resource();

		if ( empty( $resource ) ) {
			return [];
		}

		$type = Helpers::array_key_first( $resource );

		return $type === 'subscription' ? $this->api->get_subscription_card( $resource[ $type ] ) : $resource[ $type ]->getCardDetails()->getCard();
	}

	/**
	 * Update entry details and add meta for a successful payment.
	 *
	 * @since 1.9.5
	 *
	 * @param array  $fields    Final/sanitized submitted field data.
	 * @param array  $entry     Copy of original $_POST.
	 * @param array  $form_data Form data and settings.
	 * @param string $entry_id  Entry ID.
	 */
	public function update_entry_meta( $fields, $entry, $form_data, $entry_id ) {

		if ( empty( $entry_id ) || $this->errors || ! $this->api ) {
			return;
		}

		$resource = $this->api->get_response_resource();

		if ( empty( $resource ) ) {
			return;
		}

		wpforms()->obj( 'entry' )->update(
			$entry_id,
			[
				'type' => 'payment',
			],
			'',
			'',
			[ 'cap' => false ]
		);

		/**
		 * Fire when entry details and add meta was successfully updated.
		 *
		 * @since 1.9.5
		 *
		 * @param array   $fields    Final/sanitized submitted field data.
		 * @param array   $form_data Form data and settings.
		 * @param string  $entry_id  Entry ID.
		 * @param array   $resource  Response resource data.
		 * @param Process $process   Process class instance.
		 */
		do_action( 'wpforms_square_process_update_entry_meta', $fields, $form_data, $entry_id, $resource, $this ); // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName
	}

	/**
	 * Add details to payment data.
	 *
	 * @since 1.9.5
	 *
	 * @param array $payment_data Payment data args.
	 * @param array $fields       Final/sanitized submitted field data.
	 * @param array $form_data    Form data and settings.
	 *
	 * @return array
	 */
	public function prepare_payment_data( $payment_data, array $fields, array $form_data ): array { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed

		$payment_data = (array) $payment_data;

		// If there are errors or API is not initialized, return the original payment data.
		if ( $this->errors || ! $this->api ) {
			return $payment_data;
		}

		$resource = $this->api->get_response_resource();

		// If the resource is empty, return the original payment meta.
		if ( empty( $resource ) ) {
			return $payment_data;
		}

		$type            = Helpers::array_key_first( $resource );
		$payment         = $resource[ $type ];
		$is_subscription = $type === 'subscription';

		$payment_data['status']      = 'processed';
		$payment_data['gateway']     = 'square';
		$payment_data['mode']        = Helpers::is_sandbox_mode() ? 'test' : 'live';
		$payment_data['customer_id'] = sanitize_text_field( $payment->getCustomerId() );
		$payment_data['title']       = $this->get_payment_title( $payment );

		if ( $is_subscription ) {
			$payment_data['subscription_id']     = sanitize_text_field( $payment->getId() );
			$payment_data['subscription_status'] = 'not-synced';

			return $payment_data;
		}

		$payment_data['transaction_id'] = sanitize_text_field( $payment->getId() );

		return $payment_data;
	}

	/**
	 * Get Payment title.
	 *
	 * @since 1.9.5
	 *
	 * @param object $payment Payment object.
	 *
	 * @return string Payment title.
	 */
	private function get_payment_title( $payment ): string {

		// Look for the cardholder name.
		$card          = $this->get_card();
		$customer_name = $card ? $this->get_card_holder( $card ) : '';

		if ( $customer_name ) {
			return sanitize_text_field( $customer_name );
		}

		$customer_name = $this->get_customer_name();

		if ( $customer_name ) {
			return sanitize_text_field( implode( ' ', array_values( $customer_name ) ) );
		}

		$customer_email = $this->get_customer_email();

		if ( $customer_email ) {
			return sanitize_email( $customer_email );
		}

		return '';
	}

	/**
	 * Add payment meta for a successful one-time or subscription payment.
	 *
	 * @since 1.9.5
	 *
	 * @param array $payment_meta Payment meta.
	 * @param array $fields       Final/sanitized submitted field data.
	 * @param array $form_data    Form data and settings.
	 *
	 * @return array
	 */
	public function prepare_payment_meta( $payment_meta, array $fields, array $form_data ): array { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed

		$payment_meta = (array) $payment_meta;

		// If there are errors or API is not initialized, return the original payment meta.
		if ( $this->errors || ! $this->api ) {
			return $payment_meta;
		}

		$resource = $this->api->get_response_resource();

		// If the resource is empty, return the original payment meta.
		if ( empty( $resource ) ) {
			return $payment_meta;
		}

		$type                = Helpers::array_key_first( $resource );
		$credit_card_details = $this->get_card();
		$is_subscription     = $type === 'subscription';

		if ( $is_subscription ) {
			$payment_meta['subscription_period'] = $this->subscription_settings['phase_cadence']['slug'];
		}

		$payment_meta['method_type'] = 'card';

		if ( ! empty( $credit_card_details ) ) {
			$payment_meta['credit_card_last4']   = $credit_card_details->getLast4();
			$payment_meta['credit_card_expires'] = $credit_card_details->getExpMonth() . '/' . $credit_card_details->getExpYear();
			$payment_meta['credit_card_method']  = strtolower( $credit_card_details->getCardBrand() );
			$payment_meta['credit_card_name']    = $this->get_card_holder( $credit_card_details );
		}

		// Add a log indicating that the charge was successful.
		$payment_meta['log'] = $this->format_payment_log( 'Square payment was created.' );

		return $payment_meta;
	}

	/**
	 * Add payment info for successful payment.
	 *
	 * @since 1.9.5
	 *
	 * @param string $payment_id Payment ID.
	 * @param array  $fields     Final/sanitized submitted field data.
	 * @param array  $form_data  Form data and settings.
	 */
	public function process_payment_saved( $payment_id, array $fields, array $form_data ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed

		$payment_id = (string) $payment_id;

		// If there are errors or API is not initialized, return the original payment meta.
		if ( $this->errors || ! $this->api ) {
			return;
		}

		$resource = $this->api->get_response_resource();

		// If the resource is empty, return the original payment meta.
		if ( empty( $resource ) ) {
			return;
		}

		$type = Helpers::array_key_first( $resource );

		if ( $type === 'subscription' ) {

			$this->api->update_subscription(
				[
					'id'         => $resource[ $type ]->getId(),
					'payment_id' => $payment_id,
				]
			);

			return;
		}

		wpforms()->obj( 'payment_meta' )->add_log(
			$payment_id,
			sprintf(
				'Square payment was processed. (Receipt ID: %s)',
				$resource[ $type ]->getReceiptNumber()
			)
		);
	}

	/**
	 * Return payment log value.
	 *
	 * @since 1.9.5
	 *
	 * @param string $value Log value.
	 *
	 * @return string
	 */
	private function format_payment_log( string $value ): string {

		return wp_json_encode(
			[
				'value' => sanitize_text_field( $value ),
				'date'  => gmdate( 'Y-m-d H:i:s' ),
			]
		);
	}

	/**
	 * Get Customer name.
	 *
	 * @since 1.9.5
	 *
	 * @return array
	 */
	private function get_customer_name(): array {

		$customer_name = [];

		// Billing first name.
		if ( ! empty( $this->fields[ $this->settings['billing_name'] ]['first'] ) ) {
			$customer_name['first_name'] = $this->fields[ $this->settings['billing_name'] ]['first'];
		}

		// Billing last name.
		if ( ! empty( $this->fields[ $this->settings['billing_name'] ]['last'] ) ) {
			$customer_name['last_name'] = $this->fields[ $this->settings['billing_name'] ]['last'];
		}

		// If a Name field has the `Simple` format.
		if (
			empty( $customer_name['first_name'] ) &&
			empty( $customer_name['last_name'] ) &&
			! empty( $this->fields[ $this->settings['billing_name'] ]['value'] )
		) {
			$customer_name['first_name'] = $this->fields[ $this->settings['billing_name'] ]['value'];
		}

		return $customer_name;
	}

	/**
	 * Get Customer email.
	 *
	 * @since 1.9.5
	 *
	 * @return string
	 */
	private function get_customer_email(): string {

		return ! empty( $this->fields[ $this->settings['buyer_email'] ]['value'] ) ? $this->fields[ $this->settings['buyer_email'] ]['value'] : '';
	}

	/**
	 * Retrieve a Cardholder Name.
	 *
	 * @since 1.9.5
	 *
	 * @param Card $card Card object.
	 *
	 * @return string
	 */
	private function get_card_holder( $card ): string {

		$holder = '';

		if ( $card instanceof Card ) {
			$holder = $card->getCardholderName();
		}

		if ( empty( $holder ) && isset( $this->cc_field['cardname'] ) ) {
			$holder = $this->cc_field['cardname'];
		}

		return $holder;
	}
}
