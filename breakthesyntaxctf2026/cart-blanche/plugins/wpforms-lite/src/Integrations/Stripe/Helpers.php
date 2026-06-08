<?php

namespace WPForms\Integrations\Stripe;

/**
 * Stripe related helper methods.
 *
 * @since 1.8.2
 */
class Helpers {

	/**
	 * Stripe connection modes.
	 *
	 * @since 1.8.2
	 */
	const CONNECTION_MODES = [ 'live', 'test' ];

	/**
	 * Get field slug.
	 *
	 * @since 1.8.2
	 *
	 * @return string
	 */
	public static function get_field_slug() {

		return self::is_pro() ? wpforms_stripe()->api->get_config( 'field_slug' ) : 'stripe-credit-card';
	}

	/**
	 * Determine whether the Stripe field is in the form.
	 *
	 * @since 1.8.2
	 *
	 * @param array $forms    Form data (e.g. forms on a current page).
	 * @param bool  $multiple Must be 'true' if $forms contain multiple forms.
	 *
	 * @return bool
	 */
	public static function has_stripe_field( $forms, $multiple = false ) {

		$slug = self::get_field_slug();

		if ( empty( $slug ) ) {
			return false;
		}

		return wpforms_has_field_type( $slug, $forms, $multiple ) !== false;
	}

	/**
	 * Determine whether the Stripe is enabled in forms used on the page.
	 *
	 * @since 1.8.2
	 *
	 * @param array $forms Form data (e.g. forms on a current page).
	 *
	 * @return bool
	 */
	public static function has_stripe_enabled( $forms ) {

		foreach ( $forms as $form ) {
			if ( self::is_payments_enabled( $form ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Determine whether Stripe keys are configured on the Payments settings page.
	 *
	 * @since 1.8.2
	 *
	 * @param string $mode Stripe mode to check the keys for.
	 *
	 * @return bool
	 */
	public static function has_stripe_keys( $mode = '' ) {

		$mode = self::validate_stripe_mode( $mode );

		return wpforms_setting( "stripe-{$mode}-secret-key" ) && wpforms_setting( "stripe-{$mode}-publishable-key" );
	}

	/**
	 * Validate Stripe mode name to ensure it's either 'live' or 'test'.
	 * If given mode is invalid, fetches current Stripe mode.
	 *
	 * @since 1.8.2
	 *
	 * @param string $mode Stripe mode to validate.
	 *
	 * @return string
	 */
	public static function validate_stripe_mode( $mode ) {

		if ( empty( $mode ) || ! in_array( $mode, self::CONNECTION_MODES, true ) ) {
			return self::get_stripe_mode();
		}

		return $mode;
	}

	/**
	 * Get Stripe mode from the WPForms settings.
	 *
	 * @since 1.8.2
	 *
	 * @return string
	 */
	public static function get_stripe_mode() {

		return wpforms_setting( 'stripe-test-mode' ) ? 'test' : 'live';
	}

	/**
	 * Get Stripe key from the WPForms settings.
	 *
	 * @since 1.8.2
	 *
	 * @param string $type Key type (e.g. 'publishable' or 'secret').
	 * @param string $mode Stripe mode (e.g. 'live' or 'test').
	 *
	 * @return string
	 */
	public static function get_stripe_key( $type, $mode = '' ) {

		$mode = self::validate_stripe_mode( $mode );

		if ( ! in_array( $type, [ 'publishable', 'secret' ], true ) ) {
			return '';
		}

		$key = wpforms_setting( "stripe-{$mode}-{$type}-key" );

		if ( ! empty( $key ) && is_string( $key ) ) {
			return sanitize_text_field( $key );
		}

		return '';
	}

	/**
	 * Set Stripe key from the WPForms settings.
	 *
	 * @since 1.8.2
	 *
	 * @param string $value Key string to set.
	 * @param string $type  Key type (e.g. 'publishable' or 'secret').
	 * @param string $mode  Stripe mode (e.g. 'live' or 'test').
	 *
	 * @return bool
	 */
	public static function set_stripe_key( $value, $type, $mode = '' ) {

		$mode = self::validate_stripe_mode( $mode );

		if ( ! in_array( $type, [ 'publishable', 'secret' ], true ) ) {
			return false;
		}

		$key              = "stripe-{$mode}-{$type}-key";
		$settings         = (array) get_option( 'wpforms_settings', [] );
		$settings[ $key ] = sanitize_text_field( $value );

		return wpforms_update_settings( $settings );
	}

	/**
	 * Determine whether a license key is active.
	 *
	 * @since 1.8.2
	 *
	 * @return bool
	 */
	public static function is_license_active() {

		$license = (array) get_option( 'wpforms_license', [] );

		return ! empty( wpforms_get_license_key() ) &&
			empty( $license['is_expired'] ) &&
			empty( $license['is_disabled'] ) &&
			empty( $license['is_invalid'] );
	}

	/**
	 * Determine whether a license type is allowed.
	 *
	 * @since 1.8.2
	 *
	 * @return bool
	 */
	public static function is_allowed_license_type() {

		return in_array( wpforms_get_license_type(), [ 'pro', 'elite', 'agency', 'ultimate' ], true );
	}

	/**
	 * Determine whether a license is ok.
	 *
	 * @since 1.8.2
	 *
	 * @return bool
	 */
	public static function is_license_ok() {

		return self::is_license_active() && self::is_allowed_license_type();
	}

	/**
	 * Determine whether the addon is activated.
	 *
	 * @since 1.8.2
	 * @since 1.9.5 Added a fallback for legacy versions of the Stripe addon.
	 *
	 * @return bool
	 */
	public static function is_addon_active(): bool {

		// Legacy versions of the Stripe addon do not support the Requirements core feature.
		if ( defined( 'WPFORMS_STRIPE_VERSION' ) && version_compare( WPFORMS_STRIPE_VERSION, '3.0.1', '<=' ) ) {
			return function_exists( 'wpforms_stripe' );
		}

		return wpforms_is_addon_initialized( 'stripe' );
	}

	/**
	 * Determine whether the addon is activated and appropriate license is set.
	 *
	 * @since 1.8.2
	 *
	 * @return bool
	 */
	public static function is_pro() {

		return self::is_addon_active() && self::is_allowed_license_type();
	}

	/**
	 * Get authorization options used for every Stripe transaction as recommended in Stripe official docs.
	 *
	 * @link https://stripe.com/docs/connect/authentication#api-keys
	 *
	 * @since 1.8.2
	 *
	 * @return array
	 */
	public static function get_auth_opts() {

		return [ 'api_key' => self::get_stripe_key( 'secret' ) ];
	}

	/**
	 * Determine whether the Payment element mode is enabled.
	 *
	 * @since 1.8.2
	 *
	 * @return bool
	 */
	public static function is_payment_element_enabled() {

		return wpforms_setting( 'stripe-card-mode' ) === 'payment';
	}

	/**
	 * Determine whether the application fee is supported.
	 *
	 * @since 1.8.2
	 *
	 * @return bool
	 */
	public static function is_application_fee_supported() {

		return ! in_array( self::get_account_country(), [ 'br', 'in', 'mx' ], true );
	}

	/**
	 * Get Stripe webhook endpoint data.
	 *
	 * @since 1.8.4
	 *
	 * @return array
	 */
	public static function get_webhook_endpoint_data() {

		return [
			'namespace' => 'wpforms',
			'route'     => 'stripe/webhooks',
			'fallback'  => 'wpforms_stripe_webhooks',
		];
	}

	/**
	 * Get webhook URL for REST API.
	 *
	 * @since 1.8.4
	 *
	 * @return string
	 */
	public static function get_webhook_url_for_rest() {

		$path = implode(
			'/',
			[
				self::get_webhook_endpoint_data()['namespace'],
				self::get_webhook_endpoint_data()['route'],
			]
		);

		return rest_url( $path );
	}

	/**
	 * Get webhook URL for cURL fallback.
	 *
	 * @since 1.8.4
	 *
	 * @return string
	 */
	public static function get_webhook_url_for_curl() {

		return add_query_arg( self::get_webhook_endpoint_data()['fallback'], '1', site_url() );
	}

	/**
	 * Determine if webhook ID and secret is set in WPForms settings.
	 *
	 * @since 1.8.4
	 *
	 * @return bool
	 */
	public static function is_webhook_configured() {

		$mode = self::get_stripe_mode();

		return wpforms_setting( 'stripe-webhooks-id-' . $mode ) && wpforms_setting( 'stripe-webhooks-secret-' . $mode );
	}

	/**
	 * Determine if webhooks are enabled in WPForms settings.
	 *
	 * @since 1.8.4
	 *
	 * @return bool
	 */
	public static function is_webhook_enabled() {

		return wpforms_setting( 'stripe-webhooks-enabled' );
	}

	/**
	 * Determine if REST API is set in WPForms settings.
	 *
	 * @since 1.8.4
	 *
	 * @return bool
	 */
	public static function is_rest_api_set() {

		return wpforms_setting( 'stripe-webhooks-communication', 'rest' ) === 'rest';
	}

	/**
	 * Get decimals amount.
	 *
	 * @since 1.8.4
	 * @deprecated 1.9.5
	 *
	 * @param string $currency Currency.
	 *
	 * @return int
	 */
	public static function get_decimals_amount( $currency = '' ) {

		_deprecated_function( __METHOD__, '1.9.5 of the WPForms plugin', 'wpforms_get_currency_multiplier()' );

		return wpforms_get_currency_multiplier( $currency );
	}

	/**
	 * Get Stripe webhook endpoint URL.
	 *
	 * If the constant WPFORMS_STRIPE_WHURL is defined, it will be used as the webhook URL.
	 *
	 * @since 1.8.4
	 *
	 * @return string
	 */
	public static function get_webhook_url() {

		if ( defined( 'WPFORMS_STRIPE_WHURL' ) ) {
			return WPFORMS_STRIPE_WHURL;
		}

		if ( self::is_rest_api_set() ) {
			return self::get_webhook_url_for_rest();
		}

		return self::get_webhook_url_for_curl();
	}

	/**
	 * Is Stripe payment enabled for the form.
	 *
	 * @since 1.8.4
	 *
	 * @param array $form_data Form data.
	 *
	 * @return bool
	 */
	public static function is_payments_enabled( $form_data ) {

		return self::is_modern_settings_enabled( $form_data ) || ! empty( $form_data['payments']['stripe']['enable'] );
	}

	/**
	 * Is Stripe modern payment enabled for the form.
	 *
	 * @since 1.8.4
	 *
	 * @param array $form_data Form data.
	 *
	 * @return bool
	 */
	public static function is_modern_settings_enabled( $form_data ) {

		return ! empty( $form_data['payments']['stripe']['enable_one_time'] ) || ! empty( $form_data['payments']['stripe']['enable_recurring'] );
	}

	/**
	 * Detect if form supports multiple subscription plans.
	 *
	 * @since 1.8.4
	 *
	 * @param array $form_data Form data.
	 *
	 * @return bool
	 */
	public static function is_form_supports_multiple_recurring_plans( $form_data ) {

		return ! isset( $form_data['payments']['stripe'] ) || empty( $form_data['payments']['stripe']['recurring']['enable'] );
	}

	/**
	 * Determine if legacy payment settings should be displayed.
	 *
	 * @since 1.8.4
	 *
	 * @param array $form_data Form data.
	 *
	 * @return bool
	 */
	public static function is_legacy_payment_settings( $form_data ) {

		$has_legacy_settings = ! self::is_form_supports_multiple_recurring_plans( $form_data );

		// Return early if form has legacy payment settings.
		if ( $has_legacy_settings ) {
			return true;
		}

		$addon_compat = ( new StripeAddonCompatibility() )->init();

		// Return early if Stripe Pro addon doesn't support modern settings (multiple plans).
		if ( $addon_compat && ! $addon_compat->is_supported_modern_settings() ) {
			return true;
		}

		return false;
	}

	/**
	 * Determine whether the Link is supported.
	 *
	 * @link https://docs.stripe.com/payments/payment-methods/integration-options#payment-method-availability
	 *
	 * @since 1.8.8
	 *
	 * @return bool
	 */
	public static function is_link_supported(): bool {

		return ! in_array( self::get_account_country(), [ 'br', 'in', 'id', 'th' ], true );
	}

	/**
	 * Get the maximum number of cycles for recurring plans.
	 *
	 * @since 1.9.8
	 *
	 * @return int
	 */
	public static function recurring_plan_cycles_max(): int {

		/**
		 * Filters the maximum number of cycles for recurring plans.
		 *
		 * @since 1.9.8
		 *
		 * @param int $max Maximum number of cycles.
		 */
		return (int) apply_filters( 'wpforms_stripe_recurring_plan_cycles_max', 100 ); // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName
	}

	/**
	 * Get account country.
	 *
	 * @since 1.8.8
	 *
	 * @return string
	 */
	private static function get_account_country(): string {

		$mode = self::get_stripe_mode();

		return get_option( "wpforms_stripe_{$mode}_account_country", '' );
	}
}
