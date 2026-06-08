<?php

namespace WPForms\Integrations\PayPalCommerce\Process;

use WPForms\Tasks\Meta;
use WPForms\Integrations\PayPalCommerce\Connection;
use WPForms\Integrations\PayPalCommerce\Helpers;
use WPForms\Integrations\PayPalCommerce\PayPalCommerce;

/**
 * PayPal Commerce payment processing.
 *
 * @since 1.10.0
 */
class Process extends Base {

	/**
	 * Task name to update subscription payment.
	 *
	 * @since 1.10.0
	 *
	 * @var string
	 */
	private const SUBSCRIPTION_TASK = 'wpforms_paypal_commerce_subscription_payment_data_update';

	/**
	 * PayPal Commerce field.
	 *
	 * @since 1.10.0
	 *
	 * @var array
	 */
	protected $field = [];

	/**
	 * Form submission data ($_POST).
	 *
	 * @since 1.10.0
	 *
	 * @var array
	 */
	protected $entry = [];

	/**
	 * Main class that communicates with the PayPal Commerce API.
	 *
	 * @since 1.10.0
	 *
	 * @var Api|\WPFormsPaypalCommerce\Api\Api
	 */
	protected $api;

	/**
	 * Register hooks.
	 *
	 * @since 1.10.0
	 */
	public function hooks(): void {

		add_action( 'wpforms_process', [ $this, 'process_entry' ], 10, 3 );
		add_action( 'wpforms_process_entry_saved', [ $this, 'update_entry_meta' ], 10, 4 );
		add_filter( 'wpforms_entry_email_process', [ $this, 'process_email' ], 70, 5 );
		add_filter( 'wpforms_forms_submission_prepare_payment_data', [ $this, 'prepare_payment_data' ], 10, 3 );
		add_filter( 'wpforms_forms_submission_prepare_payment_meta', [ $this, 'prepare_payment_meta' ], 10, 3 );
		add_action( 'wpforms_process_payment_saved', [ $this, 'process_payment_saved' ], 10, 3 );
		add_action( self::SUBSCRIPTION_TASK, [ $this, 'update_subscription_data_scheduled_task' ] );

		$this->init_hook();
	}

	/**
	 * Validate and process if a payment exists with an entry.
	 *
	 * @since 1.10.0
	 *
	 * @param array $fields    Final/sanitized submitted field data.
	 * @param array $entry     Copy of the original $_POST.
	 * @param array $form_data Form data and settings.
	 */
	public function process_entry( $fields, array $entry, array $form_data ): void {

		$fields = (array) $fields;

		if ( ! Helpers::is_paypal_commerce_enabled( $form_data ) ) {
			return;
		}

		$this->form_data  = $form_data;
		$this->fields     = $fields;
		$this->entry      = $entry;
		$this->form_id    = (int) $form_data['id'];
		$this->amount     = $this->get_amount();
		$this->field      = Helpers::get_paypal_field( $this->fields );
		$this->connection = Connection::get();
		$this->api        = PayPalCommerce::get_api( $this->connection );

		if ( is_null( $this->api ) ) {
			return;
		}

		if ( $this->is_submitted_payment_amount_corrupted( $entry ) ) {
			return;
		}

		if (
			empty( $entry['fields'][ $this->field['id'] ]['orderID'] )
			&& empty( $entry['fields'][ $this->field['id'] ]['subscriptionProcessorID'] )
			&& empty( $entry['fields'][ $this->field['id'] ]['subscriptionID'] )
		) {
			$this->display_errors();

			$this->maybe_add_conditional_logic_log();

			return;
		}

		// Before proceeding, check if any basic errors were detected.
		if ( ! $this->is_form_ok() || ! $this->is_form_processed() ) {
			$this->display_errors();

			return;
		}

		if ( ! empty( $entry['fields'][ $this->field['id'] ]['orderID'] ) ) {
			$this->capture_single();
		}

		if ( ! empty( $entry['fields'][ $this->field['id'] ]['subscriptionProcessorID'] ) ) {
			$this->subscription_processor_capture();
		}

		if ( ! empty( $entry['fields'][ $this->field['id'] ]['subscriptionID'] ) ) {
			$this->activate_subscription();
		}

		// Check and display an error if exists.
		$this->display_errors();
	}

	/**
	 * Capture a single order.
	 *
	 * @since 1.10.0
	 */
	private function capture_single(): void {

		// For the Fastlane, use the one-step order.
		if ( $this->entry['fields'][ $this->field['id'] ]['source'] === 'fastlane' ) {

			$this->process_fastlane();

			return;
		}

		$order_response = $this->api->capture( $this->entry['fields'][ $this->field['id'] ]['orderID'] );

		if ( $order_response->has_errors() ) {
			$error_title    = esc_html__( 'This payment cannot be processed because there was an error with the capture order API call.', 'wpforms-lite' );
			$this->errors[] = $error_title;

			$this->log_errors( $error_title, $order_response->get_response_message() );

			return;
		}

		$order_data = $order_response->get_body();

		// Validate card payment status.
		if (
			empty( $order_data['purchase_units'][0]['payments']['captures'][0]['status'] ) || $order_data['purchase_units'][0]['payments']['captures'][0]['status'] !== 'COMPLETED'
		) {
			$error_title    = esc_html__( 'This payment cannot be processed because it was declined by payment processor.', 'wpforms-lite' );
			$this->errors[] = $error_title;

			$this->log_errors( $error_title, $order_data );

			return;
		}

		if ( isset( $order_data['payment_source']['card'] ) ) {
			$card_details = $order_data['payment_source']['card'];

			$details = [
				'name'   => $card_details['name'] ?? '',
				'last4'  => $card_details['last_digits'] ?? '',
				'expiry' => $card_details['expiry'] ?? '',
				'brand'  => $card_details['brand'] ?? '',
			];

			wpforms()->obj( 'process' )->fields[ $this->field['id'] ]['value'] = implode( "\n", array_filter( $details ) );

			return;
		}

		$this->set_form_field_value_for_method( $order_data );
	}

	/**
	 * Create order for Fastlane.
	 *
	 * @since 1.10.0
	 */
	private function process_fastlane(): void {

		$fastlane_token = trim( $this->entry['fields'][ $this->field['id'] ]['orderID'] );
		$order_payload  = $this->build_fastlane_order_data( $fastlane_token );

		$create_order_response = $this->api->create_order( $order_payload );

		if ( $create_order_response->has_errors() ) {
			$error_title    = esc_html__( 'This payment cannot be processed because there was an error with the create order API call.', 'wpforms-lite' );
			$this->errors[] = $error_title;

			$this->log_errors( $error_title, $create_order_response->get_response_message() );

			return;
		}

		$create_order_response_body = $create_order_response->get_body();
		// Replace a token with the real Order ID for downstream logic.
		$this->entry['fields'][ $this->field['id'] ]['orderID'] = sanitize_text_field( $create_order_response_body['id'] );

		wpforms()->obj( 'process' )->fields[ $this->field['id'] ]['value'] = 'Fastlane';
	}

	/**
	 * Activate subscription.
	 *
	 * @since 1.10.0
	 */
	private function activate_subscription(): void {

		$subscription_id = $this->entry['fields'][ $this->field['id'] ]['subscriptionID'];

		$subscription_response = $this->api->activate_subscription( $subscription_id );

		if ( $subscription_response->has_errors() ) {
			$error_title    = esc_html__( 'This subscription cannot be activated because there was an error with the activation API call.', 'wpforms-lite' );
			$this->errors[] = $error_title;

			$this->log_errors( $error_title, $subscription_response->get_response_message() );

			return;
		}

		$subscription_data = $this->get_subscription_data();

		if ( empty( $subscription_data['subscriber'] ) ) {
			wpforms()->obj( 'process' )->fields[ $this->field['id'] ]['value'] = 'Checkout';

			return;
		}

		wpforms()->obj( 'process' )->fields[ $this->field['id'] ]['value'] = implode( ' ', array_values( $subscription_data['subscriber']['name'] ) ) . "\n" . $subscription_data['subscriber']['email_address'];
	}

	/**
	 * Capture subscription processor order.
	 *
	 * @since 1.10.0
	 */
	private function subscription_processor_capture(): void {

		$subscription_processor_id = $this->entry['fields'][ $this->field['id'] ]['subscriptionProcessorID'];

		$subscription_response = $this->api->subscription_processor_capture( $subscription_processor_id );

		if ( $subscription_response->has_errors() ) {
			$error_title    = esc_html__( 'This subscription cannot be activated because there was an error with the capture API call.', 'wpforms-lite' );
			$this->errors[] = $error_title;

			$this->log_errors( $error_title, $subscription_response->get_response_message() );

			return;
		}

		wpforms()->obj( 'process' )->fields[ $this->field['id'] ]['value'] = 'Checkout';
	}

	/**
	 * Update entry details and add meta for a successful payment.
	 *
	 * @since 1.10.0
	 *
	 * @param array  $fields    Final/sanitized submitted field data.
	 * @param array  $entry     Copy of the original $_POST.
	 * @param array  $form_data Form data and settings.
	 * @param string $entry_id  Entry ID.
	 *
	 * @noinspection PhpMissingParamTypeInspection
	 */
	public function update_entry_meta( $fields, array $entry, array $form_data, string $entry_id ): void {

		$fields = (array) $fields;

		if ( empty( $entry_id ) || $this->errors || ! $this->api || empty( $this->field ) || ( empty( $entry['fields'][ $this->field['id'] ]['orderID'] ) && empty( $entry['fields'][ $this->field['id'] ]['subscriptionProcessorID'] ) && empty( $entry['fields'][ $this->field['id'] ]['subscriptionID'] ) ) ) {
			return;
		}

		$order_data = $this->get_order_data();

		if ( empty( $order_data ) ) {
			$order_data = $this->get_subscription_processor_data();
		}

		if ( empty( $order_data ) ) {
			$order_data = $this->get_subscription_data();
		}

		// If we don't have order data, bail.
		if ( empty( $order_data ) ) {
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
		 * Fire when entry details and meta were successfully updated.
		 *
		 * @since 1.10.0
		 *
		 * @param array   $fields     Final/sanitized submitted field data.
		 * @param array   $form_data  Form data and settings.
		 * @param string  $entry_id   Entry ID.
		 * @param array   $order_data Response order data.
		 * @param Process $process    Process class instance.
		 */
		do_action( 'wpforms_paypal_commerce_process_update_entry_meta', $fields, $form_data, $entry_id, $order_data, $this ); // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName
	}

	/**
	 * Logic that helps decide if we should send completed payments notifications.
	 *
	 * @since 1.10.0
	 *
	 * @param bool   $process         Whether to process or not.
	 * @param array  $fields          Form fields.
	 * @param array  $form_data       Form data.
	 * @param int    $notification_id Notification ID.
	 * @param string $context         In which context this email is sent.
	 *
	 * @return bool
	 */
	public function process_email( $process, array $fields, array $form_data, int $notification_id, string $context ): bool {

		if ( ! $process ) {
			return false;
		}

		if ( ! Helpers::is_paypal_commerce_enabled( $form_data ) ) {
			return (bool) $process;
		}

		if ( empty( $form_data['settings']['notifications'][ $notification_id ][ PayPalCommerce::SLUG ] ) ) {
			return (bool) $process;
		}

		if ( empty( $this->entry['fields'][ $this->field['id'] ]['orderID'] ) && empty( $this->entry['fields'][ $this->field['id'] ]['subscriptionProcessorID'] ) && empty( $this->entry['fields'][ $this->field['id'] ]['subscriptionID'] ) ) {
			return false;
		}

		return ! $this->errors && $this->api;
	}

	/**
	 * Get order data.
	 *
	 * @since 1.10.0
	 *
	 * @return array
	 */
	private function get_order_data(): array {

		// If the payment processing is not allowed, bail.
		if ( ! $this->is_payment_saving_allowed() ) {
			return [];
		}

		static $order_data;

		if ( $order_data === null ) {

			$order_id = $this->entry['fields'][ $this->field['id'] ]['orderID'] ?? null;

			$order_data = $order_id ? $this->api->get_order( $order_id ) : [];
		}

		return $order_data;
	}

	/**
	 * Get subscription processor data.
	 *
	 * @since 1.10.0
	 *
	 * @return array
	 */
	private function get_subscription_processor_data(): array {

		// If the payment processing is not allowed, bail.
		if ( ! $this->is_payment_saving_allowed() ) {
			return [];
		}

		static $subscription_processor_data;

		if ( $subscription_processor_data === null ) {

			$id = $this->entry['fields'][ $this->field['id'] ]['subscriptionProcessorID'] ?? null;

			$subscription_processor_data = $id ? $this->api->subscription_processor_get( $id ) : [];
		}

		return $subscription_processor_data;
	}

	/**
	 * Get Subscription data.
	 *
	 * @since 1.10.0
	 *
	 * @return array
	 */
	private function get_subscription_data(): array {

		// If the payment processing is not allowed, bail.
		if ( ! $this->is_payment_saving_allowed() ) {
			return [];
		}

		static $subscription_data;

		if ( $subscription_data === null ) {

			$subscription_id = $this->entry['fields'][ $this->field['id'] ]['subscriptionID'] ?? null;

			$subscription_data = $subscription_id ? $this->api->get_subscription( $subscription_id ) : [];
		}

		return $subscription_data;
	}

	/**
	 * Add details to payment data.
	 *
	 * @since 1.10.0
	 *
	 * @param array $payment_data Payment data args.
	 * @param array $fields       Form fields.
	 * @param array $form_data    Form data.
	 *
	 * @return array
	 *
	 * @noinspection PhpMissingParamTypeInspection
	 */
	public function prepare_payment_data( $payment_data, array $fields, array $form_data ): array {

		$payment_data = (array) $payment_data;

		// If there are errors or the API is not initialized, return the original payment data.
		if ( $this->errors || ! $this->api ) {
			return $payment_data;
		}

		// Determine whether this is a one-time payment.
		$order_data = $this->get_order_data();

		if ( ! empty( $order_data ) ) {
			$payment_data['transaction_id'] = sanitize_text_field( $order_data['purchase_units'][0]['payments']['captures'][0]['id'] );
			$payment_data['title']          = $this->get_payment_title( $order_data, $form_data );

			return $this->add_generic_payment_data( $payment_data );
		}

		$subscription_processor_data = $this->get_subscription_processor_data();

		if ( ! empty( $subscription_processor_data ) ) {
			$payment_data['transaction_id']      = sanitize_text_field( $subscription_processor_data['purchase_units'][0]['payments']['captures'][0]['id'] );
			$payment_data['title']               = $this->get_payment_title( $subscription_processor_data, $form_data );
			$payment_data['subscription_status'] = (int) $form_data['payments'][ PayPalCommerce::SLUG ]['recurring'][0]['total_cycles'] === 1 ? 'completed' : 'active';
			$payment_data['subscription_id']     = sanitize_text_field( $subscription_processor_data['id'] );
			$payment_data['customer_id']         = sanitize_text_field( $subscription_processor_data['payment_source']['paypal']['account_id'] );

			return $this->add_generic_payment_data( $payment_data );
		}

		// Determine whether it is a subscription.
		$subscription_data = $this->get_subscription_data();

		if ( ! empty( $subscription_data ) ) {
			$payment_data['subscription_status'] = $this->get_subscription_total_cycles( $form_data, $subscription_data['plan_id'] ) === 1 ? 'completed' : 'not-synced';
			$payment_data['subscription_id']     = sanitize_text_field( $subscription_data['id'] );
			$payment_data['customer_id']         = sanitize_text_field( $subscription_data['subscriber']['payer_id'] );
			$payment_data['title']               = $this->get_payment_title( $subscription_data, $form_data );

			$this->maybe_log_matched_subscriptions( $subscription_data['plan_id'] );

			return $this->add_generic_payment_data( $payment_data );
		}

		return $payment_data;
	}

	/**
	 * Get Payment title.
	 *
	 * @since 1.10.0
	 *
	 * @param array $order_data Order data.
	 * @param array $form_data  Form data.
	 *
	 * @return string Payment title.
	 */
	private function get_payment_title( array $order_data, array $form_data ): string {

		// Check if the card was used before using the card name field.
		if ( $this->get_payment_method_type() === 'card' && ! empty( $this->entry['fields'][ $this->field['id'] ]['cardname'] ) ) {
			return sanitize_text_field( $this->entry['fields'][ $this->field['id'] ]['cardname'] );
		}

		$customer_name = $this->get_customer_name( $order_data, $form_data );

		if ( $customer_name ) {
			return sanitize_text_field( $customer_name );
		}

		// Use the name on the card provided if customer name not available.
		if ( ! empty( $order_data['payment_source']['card']['name'] ) ) {
			return sanitize_text_field( $order_data['payment_source']['card']['name'] );
		}

		if ( ! empty( $order_data['subscriber']['email_address'] ) ) {
			return sanitize_email( $order_data['subscriber']['email_address'] );
		}

		return '';
	}

	/**
	 * Add payment meta for a successful one-time or subscription payment.
	 *
	 * @since 1.10.0
	 *
	 * @param array $payment_meta Payment meta.
	 * @param array $fields       Sanitized submitted field data.
	 * @param array $form_data    Form data and settings.
	 *
	 * @return array
	 *
	 * @noinspection PhpMissingParamTypeInspection
	 */
	public function prepare_payment_meta( $payment_meta, array $fields, array $form_data ): array {

		$payment_meta = (array) $payment_meta;

		// Retrieve order data for one-time payments.
		$order_data = $this->get_order_data();

		if ( ! empty( $order_data ) ) {
			$payment_meta                = $this->add_credit_card_meta( $payment_meta, $order_data );
			$payment_meta['method_type'] = sanitize_text_field( $this->get_payment_method_type() );
			$payment_meta['log']         = $this->format_payment_log(
				sprintf(
					'PayPal Commerce Order created. (Order ID: %s)',
					$order_data['id']
				)
			);

			return $payment_meta;
		}

		$subscription_processor_data = $this->get_subscription_processor_data();

		if ( ! empty( $subscription_processor_data ) ) {
			$payment_meta['processor_type']      = 'paypal';
			$payment_meta['method_type']         = 'checkout';
			$payment_meta['subscription_period'] = $subscription_processor_data['interval'];
			$payment_meta['payer_email']         = sanitize_email( $subscription_processor_data['payment_source']['paypal']['email_address'] ?? '' );
			$payment_meta['log']                 = $this->format_payment_log(
				sprintf(
					'PayPal Commerce Subscription created. (Subscription ID: %s)',
					$subscription_processor_data['id']
				)
			);

			return $payment_meta;
		}

		// Retrieve subscription data.
		$subscription_data = $this->get_subscription_data();

		if ( ! empty( $subscription_data ) ) {
			$payment_meta['method_type']         = 'checkout';
			$payment_meta['subscription_period'] = $this->get_subscription_period( $form_data, $subscription_data['plan_id'] );
			$payment_meta['log']                 = $this->format_payment_log(
				sprintf(
					'PayPal Commerce Subscription created. (Subscription ID: %s)',
					$subscription_data['id']
				)
			);

			return $payment_meta;
		}

		// If no order or subscription data was found, return the payment meta.
		return $payment_meta;
	}

	/**
	 * Add payment info for the successful payment.
	 *
	 * @since 1.10.0
	 *
	 * @param int   $payment_id Payment ID.
	 * @param array $fields     Final/sanitized submitted field data.
	 * @param array $form_data  Form data and settings.
	 */
	public function process_payment_saved( int $payment_id, array $fields, array $form_data ): void {

		// Determine whether this is a subscription payment.
		$subscription_data = $this->get_subscription_data();

		if ( ! empty( $subscription_data ) ) {
			$this->schedule_subscription_update( $payment_id, $subscription_data['id'] );

			return;
		}

		// Determine whether this is a subscription processor payment.
		$subscription_processor_data = $this->get_subscription_processor_data();

		if ( ! empty( $subscription_processor_data ) ) {

			$this->add_processed_log( $payment_id, $subscription_processor_data['purchase_units'][0]['payments']['captures'][0]['id'] );

			return;
		}

		// Determine whether this is a one-time payment.
		$order_data = $this->get_order_data();

		if ( empty( $order_data ) ) {
			return;
		}

		$this->add_processed_log( $payment_id, $order_data['purchase_units'][0]['payments']['captures'][0]['id'] );
	}

	/**
	 * Schedule update subscription due to some delay in PayPal API.
	 *
	 * @since 1.10.0
	 *
	 * @param int    $payment_id      Payment ID.
	 * @param string $subscription_id Subscription ID.
	 */
	private function schedule_subscription_update( int $payment_id, string $subscription_id ): void {

		$tasks = wpforms()->obj( 'tasks' );

		$tasks->create( self::SUBSCRIPTION_TASK )
			->params( $payment_id, $subscription_id )
			->once( time() + 60 )
			->register();
	}

	/**
	 * Update subscription transaction ID in task due to some delay in PayPal API.
	 *
	 * @since 1.10.0
	 *
	 * @param int $meta_id Action meta id.
	 */
	public function update_subscription_data_scheduled_task( $meta_id ): void {

		$meta_id = (int) $meta_id;

		$params = ( new Meta() )->get( $meta_id );

		if ( ! $params ) {
			return;
		}

		[ $payment_id, $subscription_id ] = $params->data;

		$api            = PayPalCommerce::get_api( Connection::get() );
		$transactions   = $api->get_subscription_transactions( $subscription_id );
		$transaction_id = $transactions ? end( $transactions )['id'] : '';

		$this->add_processed_log( $payment_id, $transaction_id );

		wpforms()->obj( 'payment' )->update( $payment_id, [ 'transaction_id' => $transaction_id ], '', '', [ 'cap' => false ] );
	}

	/**
	 * Add the processed payment log.
	 *
	 * @since 1.10.0
	 *
	 * @param string $payment_id     Payment id.
	 * @param string $transaction_id Transaction id.
	 */
	private function add_processed_log( string $payment_id, string $transaction_id ): void {

		wpforms()->obj( 'payment_meta' )->add_log(
			$payment_id,
			sprintf(
				'PayPal Commerce Payment processed. (Transaction ID: %s)',
				$transaction_id
			)
		);
	}

	/**
	 * Return payment log value.
	 *
	 * @since 1.10.0
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
	 * Determine the payment method name.
	 * If PayPal, return 'checkout', otherwise 'card'.
	 *
	 * @since 1.10.0
	 *
	 * @return string
	 */
	private function get_payment_method_type(): string {

		$source         = $this->entry['fields'][ $this->field['id'] ]['source'] ?? '';
		$process_method = $this->get_supported_process_method( $source );

		if ( $process_method ) {
			return $process_method->get_type();
		}

		return $source === 'paypal' ? 'checkout' : 'card';
	}

	/**
	 * Get Customer name.
	 *
	 * @since 1.10.0
	 *
	 * @param array $order_data Order data.
	 * @param array $form_data  Form data and settings.
	 *
	 * @return string
	 */
	private function get_customer_name( array $order_data, array $form_data ): string {

		if ( ! empty( $order_data['payer']['name'] ) ) {
			return implode( ' ', array_values( $order_data['payer']['name'] ) );
		}

		if ( ! empty( $order_data['subscriber']['name'] ) ) {
			return implode( ' ', array_values( $order_data['subscriber']['name'] ) );
		}

		$customer_name = $this->get_customer_title_for_method( $order_data );

		if ( $customer_name ) {
			return $customer_name;
		}

		$customer_name = [];
		$form_settings = $form_data['payments'][ PayPalCommerce::SLUG ];

		// Billing first name.
		if ( ! empty( $this->fields[ $form_settings['name'] ]['first'] ) ) {
			$customer_name['first_name'] = $this->fields[ $form_settings['name'] ]['first'];
		}

		// Billing last name.
		if ( ! empty( $this->fields[ $form_settings['name'] ]['last'] ) ) {
			$customer_name['last_name'] = $this->fields[ $form_settings['name'] ]['last'];
		}

		if (
			empty( $customer_name['first_name'] ) &&
			empty( $customer_name['last_name'] ) &&
			! empty( $this->fields[ $form_settings['name'] ]['value'] )
		) {
			$customer_name['first_name'] = $this->fields[ $form_settings['name'] ]['value'];
		}

		return implode( ' ', array_values( $customer_name ) );
	}

	/**
	 * Add generic payment data.
	 *
	 * @since 1.10.0
	 *
	 * @param array $payment_data Payment data.
	 *
	 * @return array
	 */
	private function add_generic_payment_data( array $payment_data ): array {

		$payment_data['status']  = 'processed';
		$payment_data['gateway'] = PayPalCommerce::SLUG;
		$payment_data['mode']    = Helpers::is_sandbox_mode() ? 'test' : 'live';

		return $payment_data;
	}

	/**
	 * Add credit card meta.
	 *
	 * @since 1.10.0
	 *
	 * @param array $payment_meta Payment meta.
	 * @param array $order_data   Order data.
	 *
	 * @return array
	 */
	private function add_credit_card_meta( array $payment_meta, array $order_data ): array {

		// Bail early if the payment source is not available.
		if ( empty( $order_data['payment_source'] ) ) {
			return $payment_meta;
		}

		$payment_source = $order_data['payment_source'];

		// Add the credit card holder name, e.g., John Doe.
		if ( ! empty( $this->entry['fields'][ $this->field['id'] ]['cardname'] ) ) {
			$payment_meta['credit_card_name'] = sanitize_text_field( $this->entry['fields'][ $this->field['id'] ]['cardname'] );
		}

		// Add the credit card brand name, e.g., Visa, MasterCard, etc.
		if ( ! empty( $payment_source['card']['brand'] ) ) {
			$payment_meta['credit_card_method'] = sanitize_text_field( strtolower( $payment_source['card']['brand'] ) );
		}

		// Add credit card last 4 digits, e.g., 1234, 5678, etc.
		if ( ! empty( $payment_source['card']['last_digits'] ) ) {
			$payment_meta['credit_card_last4'] = sanitize_text_field( $payment_source['card']['last_digits'] );
		}

		// Add credit card expiry date, e.g., 2029-11, 2024-10, etc.
		if ( ! empty( $payment_source['card']['expiry'] ) ) {
			$payment_meta['credit_card_expires'] = sanitize_text_field( $payment_source['card']['expiry'] );
		}

		return $payment_meta;
	}

	/**
	 * Get the subscription period by plan id.
	 *
	 * @since 1.10.0
	 *
	 * @param array  $form_data  Form data.
	 * @param string $pp_plan_id Subscription plan id.
	 *
	 * @return string
	 */
	private function get_subscription_period( array $form_data, string $pp_plan_id ): string {

		$plan_setting = $this->get_plan_settings_by_plan_id( $form_data, $pp_plan_id );

		return str_replace( '-', '', $plan_setting['recurring_times'] ?? '' );
	}

	/**
	 * Get the subscription total cycles by plan id.
	 *
	 * @since 1.10.0
	 *
	 * @param array  $form_data  Form data.
	 * @param string $pp_plan_id Subscription plan id.
	 *
	 * @return int
	 */
	private function get_subscription_total_cycles( array $form_data, string $pp_plan_id ): int {

		$plan_setting = $this->get_plan_settings_by_plan_id( $form_data, $pp_plan_id );

		return (int) $plan_setting['total_cycles'] ?? 0;
	}

	/**
	 * Get the subscription plan settings by plan id.
	 *
	 * @since 1.10.0
	 *
	 * @param array  $form_data  Form data.
	 * @param string $pp_plan_id Subscription plan id.
	 *
	 * @return array
	 */
	private function get_plan_settings_by_plan_id( array $form_data, string $pp_plan_id ): array {

		foreach ( $form_data['payments'][ PayPalCommerce::SLUG ]['recurring'] as $recurring ) {
			if ( $recurring['pp_plan_id'] !== $pp_plan_id ) {
				continue;
			}

			return $recurring;
		}

		return [];
	}

	/**
	 * Check if the form has errors before payment processing.
	 *
	 * @since 1.10.0
	 *
	 * @return bool
	 */
	private function is_form_processed(): bool {

		// Bail in case there are form processing errors.
		if ( ! empty( wpforms()->obj( 'process' )->errors[ $this->form_id ] ) ) {
			return false;
		}

		return $this->is_card_field_visibility_ok();
	}

	/**
	 * Check if there is at least one visible (not hidden by conditional logic) card field in the form.
	 *
	 * @since 1.10.0
	 *
	 * @return bool
	 */
	private function is_card_field_visibility_ok(): bool {

		if ( empty( $this->field ) ) {
			return false;
		}

		// If the form contains no fields with conditional logic, the card field is visible by default.
		if ( empty( $this->form_data['conditional_fields'] ) ) {
			return true;
		}

		// If the field is NOT in the array of conditional fields, it's visible.
		if ( ! in_array( $this->field['id'], $this->form_data['conditional_fields'], true ) ) {
			return true;
		}

		// If the field IS in the array of conditional fields and marked as visible, it's visible.
		if ( ! empty( $this->field['visible'] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Display form errors.
	 *
	 * @since 1.10.0
	 */
	private function display_errors(): void {

		if ( ! $this->errors || ! is_array( $this->errors ) ) {
			return;
		}

		// Check if the form contains a required credit card. If it does
		// and there was an error, return the error to the user and prevent
		// the form from being submitted. This should not occur under normal
		// circumstances.
		if ( empty( $this->field ) || empty( $this->form_data['fields'][ $this->field['id'] ] ) ) {
			return;
		}

		if ( ! empty( $this->form_data['fields'][ $this->field['id'] ]['required'] ) ) {
			wpforms()->obj( 'process' )->errors[ $this->form_id ]['footer'] = implode( '<br>', $this->errors );
		}
	}

	/**
	 * Determine if payment saving allowed, by checking if the form has a payment field, and the API is available.
	 *
	 * @since 1.10.0
	 *
	 * @return bool
	 */
	private function is_payment_saving_allowed(): bool {

		return ! empty( $this->field ) && $this->api;
	}

	/**
	 * Check the submitted payment amount whether it was corrupted.
	 * If so, throw an error and block submission.
	 *
	 * @since 1.10.0
	 *
	 * @param array $entry Submitted entry data.
	 *
	 * @return bool
	 */
	private function is_submitted_payment_amount_corrupted( array $entry ): bool {

		$amount_corrupted = false;

		$source = ! empty( $entry['fields'][ $this->field['id'] ]['source'] ) ? $entry['fields'][ $this->field['id'] ]['source'] : '';

		// Skip for Fastlane with since the order is not created yet.
		if ( $source === 'fastlane' ) {
			return false;
		}

		// Check form amount for a single payment.
		if ( ! empty( $entry['fields'][ $this->field['id'] ]['orderID'] ) ) {
			$order = $this->api->get_order( $this->entry['fields'][ $this->field['id'] ]['orderID'] );

			// Add tax if it has been applied through WP filter.
			$tax_total        = ! empty( $order['purchase_units'][0]['amount']['breakdown']['tax_total']['value'] ) ? (float) $order['purchase_units'][0]['amount']['breakdown']['tax_total']['value'] : 0;
			$submitted_amount = Helpers::format_amount_for_api_call( (float) $this->amount + $tax_total );
			$amount_corrupted = ! empty( $order ) && (float) $submitted_amount !== (float) $order['purchase_units'][0]['amount']['value'];
		}

		// Check the form amount for subscription processor payment.
		if ( ! $amount_corrupted && ! empty( $entry['fields'][ $this->field['id'] ]['subscriptionProcessorID'] ) ) {
			$subscription_processor = $this->api->subscription_processor_get( $this->entry['fields'][ $this->field['id'] ]['subscriptionProcessorID'] );

			// Add tax if it has been applied through WP filter.
			$tax_total        = ! empty( $subscription_processor['purchase_units'][0]['amount']['breakdown']['tax_total']['value'] ) ? (float) $subscription_processor['purchase_units'][0]['amount']['breakdown']['tax_total']['value'] : 0;
			$submitted_amount = Helpers::format_amount_for_api_call( (float) $this->amount + $tax_total );
			$amount_corrupted = ! empty( $subscription_processor ) && (float) $submitted_amount !== (float) $subscription_processor['purchase_units'][0]['amount']['value'];
		}

		// Check form amount for subscription payment.
		if ( ! $amount_corrupted && ! empty( $entry['fields'][ $this->field['id'] ]['subscriptionID'] ) ) {
			$subscription     = $this->api->get_subscription( $entry['fields'][ $this->field['id'] ]['subscriptionID'], [ 'fields' => 'plan' ] );
			$amount_corrupted = ! empty( $subscription ) && (float) $this->amount !== (float) $subscription['plan']['billing_cycles'][0]['pricing_scheme']['fixed_price']['value'];
		}

		// Prevent form submission and throw an error.
		if ( $amount_corrupted ) {
			wpforms()->obj( 'process' )->errors[ $this->form_id ]['footer'] = esc_html__( 'Irregular activity detected. Your submission has been declined.', 'wpforms-lite' );

			return true;
		}

		return false;
	}

	/**
	 * Log if more than one plan matched on the form submission.
	 *
	 * @since 1.10.0
	 *
	 * @param string $matched_plan_id Already matched and executed plan.
	 *
	 * @noinspection PhpMissingParamTypeInspection
	 */
	protected function maybe_log_matched_subscriptions( $matched_plan_id ): void {
	}

	/**
	 * Add a log record if a payment was stopped by conditional logic.
	 *
	 * @since 1.10.0
	 */
	protected function maybe_add_conditional_logic_log(): void {
	}

	/**
	 * Build Fastlane order payload to match ProcessSingleAjax::prepare_single_order_data().
	 * Intentionally skipping billing address as it comes with a single use token.
	 *
	 * @since 1.10.0
	 *
	 * @param string $fastlane_token Fastlane single-use token.
	 *
	 * @return array
	 */
	private function build_fastlane_order_data( string $fastlane_token ): array {

		$settings       = $this->form_data['payments'][ PayPalCommerce::SLUG ] ?? [];
		$this->currency = $this->get_currency();
		$amount_string  = Helpers::format_amount_for_api_call( (float) $this->amount );

		$is_shipping_address = isset( $settings['shipping_address'] ) && $settings['shipping_address'] !== '' && $this->is_address_field_valid_from_fields( $settings['shipping_address'] );

		$order_data = [];

		$order_data['intent']                                     = 'CAPTURE';
		$order_data['application_context']['shipping_preference'] = $is_shipping_address ? 'SET_PROVIDED_ADDRESS' : 'NO_SHIPPING';
		$order_data['application_context']['user_action']         = 'CONTINUE';

		$order_data['purchase_units'][0] = [
			'amount'      => [
				'value'         => $amount_string,
				'currency_code' => $this->currency,
				'breakdown'     => [
					'item_total' => [
						'value'         => $amount_string,
						'currency_code' => $this->currency,
					],
					'shipping'   => [
						'value'         => 0,
						'currency_code' => $this->currency,
					],
				],
			],
			'description' => $this->get_order_description(),
			'items'       => $this->get_order_items(),
			'shipping'    => [
				'name' => [
					'full_name' => '',
				],
			],
		];

		if ( $is_shipping_address ) {
			$order_data['purchase_units'][0]['shipping']['address'] = $this->map_address_field_from_fields( $settings['shipping_address'] );
		}

		// Build the payment source for the card (Fastlane token).
		$order_data['payment_source']['card'] = [
			'single_use_token' => $fastlane_token,
			'attributes'       => [
				'vault' => [
					'store_in_vault' => 'ON_SUCCESS',
				],
			],
		];

		/**
		 * Allow 3rd-parties to filter Fastlane order data in the Process context.
		 *
		 * @since 1.10.0
		 *
		 * @param array $order_data Order data.
		 * @param array $form_data  Form data.
		 * @param float $amount     Order amount.
		 */
		return (array) apply_filters( 'wpforms_paypal_commerce_process_fastlane_order_data', $order_data, $this->form_data, (float) $this->amount ); // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName
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
				! isset( $this->entry['fields'][ $field_id ] ) ||
				wpforms_is_empty_string( $this->entry['fields'][ $field_id ] )
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

		if ( ! empty( $field['enable_quantity'] ) ) {
			$quantity = isset( $this->entry['quantities'][ $field['id'] ] ) ? (int) $this->entry['quantities'][ $field['id'] ] : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		}

		if ( ! $quantity ) {
			return $items;
		}

		if ( empty( $field['choices'] ) ) {
			$items[] = [
				'name'        => wp_html_excerpt( $name, 124, '...' ), // Limit to 127 characters.
				'quantity'    => $quantity,
				'unit_amount' => [
					'value'         => Helpers::format_amount_for_api_call( wpforms_sanitize_amount( $this->entry['fields'][ $field_id ] ) ),
					'currency_code' => $this->currency,
				],
			];

			return $items;
		}

		$choices = ! is_array( $this->entry['fields'][ $field_id ] ) ? [ $this->entry['fields'][ $field_id ] ] : $this->entry['fields'][ $field_id ];

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
	 * Retrieve the customer title associated with the processing method for the given order data.
	 *
	 * @since 1.10.0
	 *
	 * @param array $order_data The order data used to determine the processing method.
	 *
	 * @return string
	 */
	private function get_customer_title_for_method( array $order_data ): string {

		$process_method = $this->get_supported_process_method_for_order( $order_data );

		if ( ! $process_method ) {
			return '';
		}

		return $process_method->get_customer_name( $order_data );
	}

	/**
	 * Sets the form field value using the appropriate processing method for the given order data.
	 *
	 * @since 1.10.0
	 *
	 * @param array $order_data The order data used to determine the processing method and extract the field value.
	 */
	private function set_form_field_value_for_method( array $order_data ): void {

		$process_method = $this->get_supported_process_method_for_order( $order_data );

		if ( ! $process_method ) {
			wpforms()->obj( 'process' )->fields[ $this->field['id'] ]['value'] = 'Checkout';

			return;
		}

		wpforms()->obj( 'process' )->fields[ $this->field['id'] ]['value'] = $process_method->get_form_field_value( $order_data );
	}
}
