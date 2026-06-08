<?php

namespace WPForms\Integrations\PayPalCommerce\Frontend;

use WP_Post;
use WPForms\Integrations\PayPalCommerce\Connection;
use WPForms\Integrations\PayPalCommerce\Helpers;
use WPForms\Integrations\PayPalCommerce\PaypalCommerce; // phpcs:ignore WPForms.PHP.UseStatement.UnusedUseStatement
use Generator;

/**
 * Frontend-related functionality.
 *
 * @since 1.10.0
 */
class Frontend {

	/**
	 * An array to hold the enabled funding sources for a specific configuration or operation.
	 *
	 * @since 1.10.0
	 *
	 * @var PaymentMethodInterface[]
	 */
	private $payment_methods = [];

	/**
	 * Represents the currency associated with a particular transaction or operation.
	 *
	 * @since 1.10.0
	 *
	 * @var string
	 */
	private $currency = '';

	/**
	 * Initialize the payment methods array and fire the init action hook.
	 *
	 * This method resets the payment methods array and triggers an action hook that allows
	 * payment method modules to register themselves using the add_payment_method() method.
	 *
	 * @since 1.10.0
	 *
	 * @see add_payment_method() For adding payment methods to the registration.
	 */
	public function init(): void {

		$this->currency = strtoupper( wpforms_get_currency() );

		$this->hooks();

		/**
		 * Fires after the Enqueues class is initialized, allowing payment methods to register.
		 *
		 * Payment method modules (Apple Pay, Google Pay, RegionalPayments, etc.) should hook into
		 * this action to add their PaymentMethod instances to the Enqueues class using the
		 * add_payment_method() method.
		 *
		 * @since 1.10.0
		 *
		 * @param Frontend $enqueues The Enqueues instance for adding payment methods.
		 */
		do_action( 'wpforms_integrations_paypal_commerce_frontend_init', $this ); // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.10.0
	 */
	private function hooks(): void {

		add_action( 'wpforms_frontend_container_class', [ $this, 'form_container_class' ], 10, 2 );
		add_action( 'wpforms_frontend_output_form_before', [ $this, 'maybe_refresh_tokens' ], 10, 2 );

		// Load assets on later stage after all our payment addons.
		add_action( 'wpforms_wp_footer', [ $this, 'enqueues' ], PHP_INT_MAX );
		add_filter( 'script_loader_tag', [ $this, 'set_script_attributes' ], 10, 2 );
	}

	/**
	 * Add the class to a form container if PayPal Commerce is enabled.
	 *
	 * @since 1.10.0
	 *
	 * @param array $classes   Array of form classes.
	 * @param array $form_data Form data of current form.
	 *
	 * @return array
	 *
	 * @noinspection PhpMissingParamTypeInspection
	 */
	public function form_container_class( $classes, array $form_data ): array {

		$classes = (array) $classes;

		if ( ! Connection::get() ) {
			return $classes;
		}

		if ( ! Helpers::has_paypal_commerce_field( $form_data ) ) {
			return $classes;
		}

		if ( ! Helpers::is_subscriptions_configured( $form_data ) ) {
			return $classes;
		}

		if ( Helpers::is_paypal_commerce_enabled( $form_data ) ) {
			$classes[] = 'wpforms-paypal-commerce';
		}

		return $classes;
	}

	/**
	 * Enqueue assets in the frontend if PayPal Commerce is in use on the page.
	 *
	 * @since 1.10.0
	 *
	 * @param array $forms Form data of forms on the current page.
	 */
	public function enqueues( $forms ): void {

		$forms = (array) $forms;

		$connection = Connection::get();

		if (
			! $connection ||
			! $connection->is_usable() ||
			! Helpers::has_paypal_commerce_field( $forms, true ) ||
			! Helpers::is_paypal_commerce_forms_enabled( $forms )
		) {
			return;
		}

		$this->enqueues_styles();
		$this->set_payment_methods( $forms );

		$min = wpforms_get_min_suffix();

		wp_enqueue_script(
			'wpforms-paypal-commerce',
			WPFORMS_PLUGIN_URL . "assets/js/integrations/paypal-commerce/wpforms-paypal-commerce{$min}.js",
			[ 'jquery' ],
			WPFORMS_VERSION,
			true
		);

		// Register PaymentHandler class first (required by Apple Pay and Google Pay).
		wp_register_script(
			'wpforms-paypal-commerce-payment-method',
			WPFORMS_PLUGIN_URL . "assets/js/integrations/paypal-commerce/payment-methods/base-payment-method{$min}.js",
			[ 'wpforms-paypal-commerce' ],
			WPFORMS_VERSION,
			true
		);

		// Get enabled payment types to conditional load scripts.
		[ $is_single, $is_recurring ] = $this->get_enabled_payment_types( $forms );

		// Enqueue a single payment script if one-time payments are enabled.
		if ( $is_single ) {
			$this->enqueue_single_sdk_script( $connection );
		}

		// Enqueue subscriptions script if recurring payments are enabled.
		if ( $is_recurring ) {
			$this->enqueue_subscriptions_sdk_script( $connection );
		}

		$this->enqueue_sdk_components(
            [
				'single'    => $is_single,
				'recurring' => $is_recurring,
            ]
        );

		wp_localize_script(
			'wpforms-paypal-commerce',
			'wpforms_paypal_commerce',
			$this->get_localized_strings( $forms )
		);
	}

	/**
	 * Enqueue SDK components for enabled funding sources.
	 *
	 * @since 1.10.0
	 *
	 * @param array $payment_types Array of payment type flags (e.g., ['single' => bool, 'recurring' => bool]).
	 */
	private function enqueue_sdk_components( array $payment_types ): void {

		foreach ( $this->iterate_payment_methods_with_assets() as $sdk_component ) {
			$sdk_component->enqueue( $payment_types );
		}
	}

	/**
	 * Get localized strings for front-end forms.
	 *
	 * This method generates and returns an array of localized strings, payment options,
	 * conditional rules, nonces, and internationalized error messages for form handling.
	 *
	 * @since 1.10.0
	 *
	 * @param array $forms An array of forms data for the current page.
	 *
	 * @return array The array of localized strings, options, and related data.
	 */
	protected function get_localized_strings( array $forms ): array {

		$strings = [
			'payment_options'   => $this->get_payment_options( $forms ),
			'mode'              => Helpers::get_mode(),
			'total_price_label' => esc_html__( 'Total', 'wpforms-lite' ),
			'nonces'            => [
				'create'              => wp_create_nonce( 'wpforms-paypal-commerce-create-order' ),
				'approve'             => wp_create_nonce( 'wpforms-paypal-commerce-approve-order' ),
				'create_subscription' => wp_create_nonce( 'wpforms-paypal-commerce-create-subscription' ),
			],
			'i18n'              => [
				'missing_sdk_script'         => esc_html__( 'PayPal.js failed to load properly.', 'wpforms-lite' ),
				'on_cancel'                  => esc_html__( 'PayPal payment was canceled.', 'wpforms-lite' ),
				'on_error'                   => esc_html__( 'There was an error processing this payment. Please contact the site administrator.', 'wpforms-lite' ),
				'api_error'                  => esc_html__( 'API error:', 'wpforms-lite' ),
				'subscription_error'         => esc_html__( 'There was an error creating this subscription. Please contact the site administrator.', 'wpforms-lite' ),
				'secure_error'               => esc_html__( 'This payment cannot be processed because there was an error with 3D Secure authentication.', 'wpforms-lite' ),
				'card_not_supported'         => esc_html__( 'is not supported. Please enter the details for a supported credit card.', 'wpforms-lite' ),
				'number'                     => esc_html__( 'Please enter a valid card number.', 'wpforms-lite' ),
				'expirationDate'             => esc_html__( 'Please enter a valid date.', 'wpforms-lite' ),
				'cvv'                        => esc_html__( 'Please enter the CVV number.', 'wpforms-lite' ),
				'card_name'                  => esc_html__( 'Please enter the Card Holder Name.', 'wpforms-lite' ),
				'empty_amount'               => esc_html__( 'This payment cannot be processed because the payment amount is not set, or is set to an invalid amount.', 'wpforms-lite' ),
				'fastlane_account_error'     => esc_html__( 'No Fastlane account found with this email.', 'wpforms-lite' ),
				'fastlane_eligibility_error' => esc_html__( 'Please enter a valid Fastlane details.', 'wpforms-lite' ),
				'fastlane_invalid_billing'   => esc_html__( 'Please enter a valid Fastlane billing address.', 'wpforms-lite' ),
			],
		];

		/**
		 * Filter the localized strings for PayPal Commerce payment.
		 *
		 * @since 1.10.0
		 *
		 * @param array $strings An array of localized strings, options, and related data.
		 * @param array $forms   An array of forms data for the current page.
		 */
		return apply_filters( 'wpforms_integrations_paypal_commerce_frontend_localized_strings', $strings, $forms ); // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName
	}

	/**
	 * Enqueue styles in the frontend if PayPal Commerce is in use on the page.
	 *
	 * @since 1.10.0
	 */
	private function enqueues_styles(): void {

		// Include styles if the "Include Form Styling > No Styles" is not set.
		if ( wpforms_setting( 'disable-css', '1' ) === '3' ) {
			return;
		}

		$min = wpforms_get_min_suffix();

		wp_enqueue_style(
			'wpforms-paypal-commerce',
			WPFORMS_PLUGIN_URL . "assets/css/integrations/paypal-commerce/wpforms-paypal-commerce{$min}.css",
			[],
			WPFORMS_VERSION
		);
	}

	/**
	 * Add attributes to PayPal script tags.
	 *
	 * @since 1.10.0
	 *
	 * @param string $tag    HTML for the script tag.
	 * @param string $handle Handle of a script.
	 *
	 * @return string
	 *
	 * @noinspection PhpMissingParamTypeInspection
	 */
	public function set_script_attributes( $tag, string $handle ): string {

		$tag = (string) $tag;

		// Add an async attribute to an external SDK.
		if ( $this->is_async_script( $handle ) ) {
			$new_attr = sprintf( ' async onload="%s" src', "WPFormsPaypalCommerce.onLoadSDK( '$handle' )" );

			return str_replace( ' src', $new_attr, $tag );
		}

		if ( ! in_array( $handle, [ 'wpforms-paypal-single', 'wpforms-paypal-subscriptions' ], true ) ) {
			return $tag;
		}

		$connection = Connection::get();

		if ( ! $connection ) {
			return $tag;
		}

		$attributes  = ' data-namespace="' . esc_attr( str_replace( '-', '_', $handle ) ) . '"';
		$attributes .= ' data-client-token="' . esc_attr( $connection->get_client_token() ) . '"';
		$attributes .= ' data-sdk-client-token="' . esc_attr( $connection->get_sdk_client_token() ) . '"';
		$attributes .= ' data-partner-merchant-id="' . esc_attr( $connection->get_partner_merchant_id() ) . '"';
		$attributes .= ' data-partner-attribution-id="' . esc_attr( $connection->get_partner_id() ) . '"';

		return str_replace( ' src', $attributes . ' src', $tag );
	}

	/**
	 * Process options by payment methods.
	 *
	 * This method processes the provided options and extends them using the localized settings
	 * of each registered payment method.
	 *
	 * @since 1.10.0
	 *
	 * @param array $options Existing payment options configuration.
	 * @param array $field   Field data related to the current payment method.
	 * @param array $form    Form data related to the current payment method.
	 *
	 * @return array Updated payment options with additional localized settings.
	 */
	private function process_options_by_methods( array $options, array $field, array $form ): array {

		$new_options = [];

		foreach ( $this->payment_methods as $payment_method ) {
			if ( ! ( $payment_method instanceof PaymentMethodAssetsInterface ) ) {
				continue;
			}

			$new_options[] = $payment_method->get_localized_settings( $field, $form );
		}

		return array_merge( $options, ...$new_options );
	}

	/**
	 * Get enabled payment types.
	 *
	 * Determines whether single payments and recurring payments are enabled
	 * across the provided forms.
	 *
	 * @since 1.10.0
	 *
	 * @param array $forms Form data of the forms to check for enabled payment types.
	 *
	 * @return array List containing boolean flags:
	 *               - First value indicates if single payments are enabled.
	 *               - Second value indicates if recurring payments are enabled.
	 */
	private function get_enabled_payment_types( array $forms ): array {

		$is_single_enabled    = false;
		$is_recurring_enabled = false;

		foreach ( $forms as $form_data ) {
			if ( Helpers::is_paypal_commerce_single_enabled( $form_data ) ) {
				$is_single_enabled = true;
			}

			if ( Helpers::is_paypal_commerce_subscriptions_enabled( $form_data ) ) {
				$is_recurring_enabled = true;
			}

			// Early exit if both types are enabled.
			if ( $is_single_enabled && $is_recurring_enabled ) {
				break;
			}
		}

		return [ $is_single_enabled, $is_recurring_enabled ];
	}

	/**
	 * Get PayPal SDK components string based on enabled payment methods.
	 *
	 * @since 1.10.0
	 *
	 * @param bool $is_single Whether to consider One-Time Payment or Recurring. Defaults to One-Time.
	 *
	 * @return string Comma-separated list of SDK components.
	 */
	private function get_sdk_components( bool $is_single = true ): string {

		$components = [];

		foreach ( $this->payment_methods as $sdk_component ) {
			$components = array_merge( $components, (array) $sdk_component->get_components( $is_single ) );
		}

		return implode( ',', $components );
	}

	/**
	 * Get PayPal SDK base URL with shared query parameters.
	 *
	 * @since 1.10.0
	 *
	 * @param object $connection Connection object.
	 * @param bool   $is_single  Whether to consider One-Time Payment or Recurring. Defaults to One-Time.
	 *
	 * @return string Base SDK URL with query parameters.
	 */
	private function get_paypal_sdk_base_url( object $connection, bool $is_single = true ): string {

		$query_args = [
			'client-id'       => $connection->get_client_id(),
			'merchant-id'     => $connection->get_merchant_id(),
			'currency'        => $this->currency,
			'disable-funding' => $this->get_disabled_funding_sources( $is_single ),
			'enable-funding'  => $this->get_enabled_funding_sources( $is_single ),
		];

		foreach ( $query_args as $key => $value ) {
			if ( ! empty( $value ) ) {
				continue;
			}

			unset( $query_args[ $key ] );
		}

		return add_query_arg(
			$query_args,
			'https://www.paypal.com/sdk/js'
		);
	}

	/**
	 * Enqueue PayPal SDK script for single payments.
	 *
	 * @since 1.10.0
	 *
	 * @param object $connection Connection object.
	 */
	private function enqueue_single_sdk_script( object $connection ): void {

		// Get dynamic components based on enabled payment methods.
		$components = $this->get_sdk_components();
		// Get base SDK URL with shared parameters.
		$base_url = $this->get_paypal_sdk_base_url( $connection );
		$args     = [ 'components' => $components ];

		// phpcs:disable WordPress.WP.EnqueuedResourceParameters.MissingVersion
		wp_enqueue_script(
			'wpforms-paypal-single',
			add_query_arg( $args, $base_url ),
			[],
			null,
			false
		);
		// phpcs:enable WordPress.WP.EnqueuedResourceParameters.MissingVersion
	}

	/**
	 * Enqueue PayPal SDK script for subscription payments.
	 *
	 * @since 1.10.0
	 *
	 * @param object $connection Connection object.
	 */
	private function enqueue_subscriptions_sdk_script( object $connection ): void {

		// Get dynamic components based on enabled payment methods.
		$components = $this->get_sdk_components( false );
		// Get base SDK URL with shared parameters.
		$base_url = $this->get_paypal_sdk_base_url( $connection, false );
		$args     = [
			'components' => $components,
			'vault'      => 'true',
		];

		if ( Helpers::is_license_ok() ) {
			$args['intent'] = 'subscription';
		}

		// phpcs:disable WordPress.WP.EnqueuedResourceParameters.MissingVersion
		wp_enqueue_script(
			'wpforms-paypal-subscriptions',
			add_query_arg( $args, $base_url ),
			[],
			null,
			false
		);
		// phpcs:enable WordPress.WP.EnqueuedResourceParameters.MissingVersion
	}

	/**
	 * Checks if a script handle is an asynchronous script.
	 *
	 * @since 1.10.0
	 *
	 * @param string $handle Script handle to check.
	 *
	 * @return bool
	 */
	private function is_async_script( string $handle ): bool {

		foreach ( $this->iterate_payment_methods_with_assets() as $sdk_component ) {
			if ( in_array( $handle, $sdk_component->get_async_scripts(), true ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get disabled funding sources.
	 *
	 * @since 1.10.0
	 *
	 * @param bool $is_single Whether to consider One-Time Payment or Recurring. Defaults to One-Time.
	 *
	 * @return string Comma-separated list of disabled funding sources.
	 */
	private function get_disabled_funding_sources( bool $is_single = true ): string {

		$disabled = $is_single ? [] : [ 'credit', 'card' ];

		foreach ( $this->payment_methods as $payment_method ) {
			if ( ! ( $payment_method instanceof PaymentMethodFundingInterface ) ) {
				continue;
			}

			$disabled = array_merge( $disabled, (array) $payment_method->get_disabled_methods( $is_single ) );
		}

		/**
		 * Filter the disabled funding sources for PayPal Commerce payment.
		 *
		 * @since 1.10.0
		 *
		 * @param array $disabled  An array of the disabled funding sources.
		 * @param bool  $is_single Whether to consider One-Time Payment or Recurring. Defaults to One-Time.
		 */
		$disabled = (array) apply_filters( 'wpforms_integrations_paypal_commerce_frontend_get_disabled_funding_sources', $disabled, $is_single ); // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName

		return implode( ',', $disabled );
	}

	/**
	 * Get enabled funding sources based on field settings.
	 *
	 * @since 1.10.0
	 *
	 * @param bool $is_single Whether to consider One-Time Payment or Recurring. Defaults to One-Time.
	 *
	 * @return string Comma-separated list of enabled funding sources.
	 */
	private function get_enabled_funding_sources( bool $is_single = true ): string {

		$enabled = [];

		foreach ( $this->payment_methods as $payment_method ) {
			if ( ! ( $payment_method instanceof PaymentMethodFundingInterface ) ) {
				continue;
			}

			$enabled = array_merge( $enabled, (array) $payment_method->get_enabled_methods( $is_single ) );
		}

		/**
		 * Filter the enabled funding sources for PayPal Commerce payment.
		 *
		 * @since 1.10.0
		 *
		 * @param array $disabled  An array of the disabled funding sources.
		 * @param bool  $is_single Whether to consider One-Time Payment or Recurring. Defaults to One-Time.
		 */
		$enabled = (array) apply_filters( 'wpforms_integrations_paypal_commerce_frontend_get_enabled_funding_sources', $enabled, $is_single ); // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName

		return implode( ',', $enabled );
	}

	/**
	 * Iterate over payment methods that implement the PaymentMethodAssetsInterface.
	 *
	 * This generator yields only payment methods that have assets (JS/CSS) to enqueue,
	 * filtering out payment methods that don't implement the required interface.
	 *
	 * @since 1.10.0
	 *
	 * @return Generator<PaymentMethodAssetsInterface> Generator yielding payment methods with assets.
	 */
	private function iterate_payment_methods_with_assets(): Generator {

		foreach ( $this->payment_methods as $payment_method ) {
			if ( ! ( $payment_method instanceof PaymentMethodAssetsInterface ) ) {
				continue;
			}

			yield $payment_method;
		}
	}

	/**
	 * Add a payment method to the registration.
	 *
	 * This method allows payment method modules to register themselves with the Enqueues class.
	 * Registered payment methods will have their components, scripts, and assets loaded based on
	 * the form field configuration.
	 *
	 * @since 1.10.0
	 *
	 * @param PaymentMethodInterface $payment_method The payment method instance to add.
	 */
	public function add_payment_method( PaymentMethodInterface $payment_method ): void {

		$this->payment_methods[] = $payment_method;
	}

	/**
	 * Get Payment Options.
	 *
	 * @since 1.10.0
	 *
	 * @param array $forms Form data of forms on the current page.
	 *
	 * @return array
	 */
	private function get_payment_options( array $forms ): array { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		$options = [];

		foreach ( $forms as $form_id => $form ) {

			if ( ! isset( $form['payments'][ PayPalCommerce::SLUG ] ) ) {
				continue;
			}

			$options[ $form_id ]['is_license_ok']      = Helpers::is_license_ok();
			$options[ $form_id ]['enable_one_time']    = Helpers::is_paypal_commerce_single_enabled( $form );
			$options[ $form_id ]['enable_recurring']   = Helpers::is_paypal_commerce_subscriptions_enabled( $form );
			$options[ $form_id ]['recurring_no_rules'] = Helpers::get_subscription_plan_id_without_rule( $form ) !== '';

			foreach ( $form['fields'] as $field ) {

				if ( $field['type'] !== 'paypal-commerce' ) {
					continue;
				}

				$options[ $form_id ]['button_size']     = $field['button_size'] ?? '';
				$options[ $form_id ]['paypal_checkout'] = isset( $field['paypal_checkout'] );
				$options[ $form_id ]['credit_card']     = isset( $field['credit_card'] );
				$options[ $form_id ]['fastlane']        = isset( $field['fastlane'] );
				$options[ $form_id ]['shape']           = $field['shape'];
				$options[ $form_id ]['color']           = $field['color'];

				// If both Credit Card and Fastlane are enabled, disable Fastlane.
				if ( ! empty( $options[ $form_id ]['credit_card'] ) && ! empty( $options[ $form_id ]['fastlane'] ) ) {
					$options[ $form_id ]['fastlane'] = false;
				}

				$options[ $form_id ] = $this->process_options_by_methods( $options[ $form_id ], $field, $form );

				if ( ! isset( $field['credit_card'] ) ) {
					continue;
				}

				$options[ $form_id ]['supported_cards'] = [
					isset( $field['amex'] ) ? 'american-express' : '',
					isset( $field['maestro'] ) ? 'maestro' : '',
					isset( $field['discover'] ) ? 'discover' : '',
					isset( $field['mastercard'] ) ? 'master-card' : '',
					isset( $field['visa'] ) ? 'visa' : '',
				];

				$options[ $form_id ]['sublabel_hide']      = isset( $field['sublabel_hide'] );
				$options[ $form_id ]['card_number']        = ! empty( $field['card_number'] ) ? $field['card_number'] : esc_html__( 'Card Number', 'wpforms-lite' );
				$options[ $form_id ]['expiration_date']    = ! empty( $field['expiration_date'] ) ? $field['expiration_date'] : esc_html__( 'Expiration Date', 'wpforms-lite' );
				$options[ $form_id ]['security_code']      = ! empty( $field['security_code'] ) ? $field['security_code'] : esc_html__( 'Security Code', 'wpforms-lite' );
				$options[ $form_id ]['card_holder_enable'] = isset( $field['card_holder_enable'] );
				$options[ $form_id ]['card_holder_name']   = ! empty( $field['card_holder_name'] ) ? $field['card_holder_name'] : esc_html__( 'Card Holder Name', 'wpforms-lite' );
			}
		}

		return $options;
	}

	/**
	 * Refresh tokens if conditions are met.
	 *
	 * @since 1.10.0
	 *
	 * @param array   $form_data Form data to process.
	 * @param WP_Post $form      The form object.
	 */
	public function maybe_refresh_tokens( $form_data, WP_Post $form ): void { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed

		$form_data = (array) $form_data;

		$connection = Connection::get();

		if (
			! $connection ||
			! $connection->is_valid() ||
			! Helpers::has_paypal_commerce_field( $form_data ) ||
			! Helpers::is_paypal_commerce_enabled( $form_data )
		) {
			return;
		}

		$connection->refresh_expired_tokens();
	}

	/**
	 * Set Funding Sources.
	 *
	 * Resets the list of enabled funding sources based on the provided forms, ensuring only supported funding sources are retained.
	 *
	 * @since 1.10.0
	 *
	 * @param array $forms Form data of forms on the current page.
	 */
	private function set_payment_methods( array $forms ): void {

		// Reset the list of enabled funding sources.
		[ $payment_methods, $this->payment_methods ] = [ $this->payment_methods, [] ];

		foreach ( $payment_methods as $funding_source ) {
			foreach ( $forms as $form ) {
				$field = Helpers::get_paypal_field( $form['fields'] ?? [] );

				if ( ! $funding_source->is_supported( $field ) ) {
					continue;
				}

				$this->payment_methods[] = $funding_source;

				break;
			}
		}
	}

	/**
	 * Get the current currency.
	 *
	 * @since 1.10.0
	 *
	 * @return string
	 */
	public function get_currency(): string {

		return $this->currency;
	}
}
