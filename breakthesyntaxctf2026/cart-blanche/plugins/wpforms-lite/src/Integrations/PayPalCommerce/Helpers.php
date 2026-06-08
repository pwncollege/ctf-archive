<?php

namespace WPForms\Integrations\PayPalCommerce;

/**
 * PayPal Commerce related helper methods.
 *
 * @since 1.10.0
 */
class Helpers {

	/**
	 * Sandbox mode.
	 *
	 * @since 1.10.0
	 */
	public const SANDBOX = 'sandbox';

	/**
	 * Production mode.
	 *
	 * @since 1.10.0
	 */
	public const PRODUCTION = 'live';

	/**
	 * Determine whether PayPal Commerce single payment is enabled for a form.
	 *
	 * @since 1.10.0
	 *
	 * @param array $form_data Form data and settings.
	 *
	 * @return bool
	 */
	public static function is_paypal_commerce_single_enabled( array $form_data ): bool {

		return ! empty( $form_data['payments'][ PayPalCommerce::SLUG ]['enable_one_time'] );
	}

	/**
	 * Determine whether PayPal Commerce subscriptions payment is enabled for a form.
	 *
	 * @since 1.10.0
	 *
	 * @param array $form_data Form data and settings.
	 *
	 * @return bool
	 */
	public static function is_paypal_commerce_subscriptions_enabled( array $form_data ): bool {

		return ! empty( $form_data['payments'][ PayPalCommerce::SLUG ]['enable_recurring'] );
	}

	/**
	 * Determine whether PayPal Commerce payment is enabled for a form.
	 *
	 * @since 1.10.0
	 *
	 * @param array $form_data Form data.
	 *
	 * @return bool
	 */
	public static function is_paypal_commerce_enabled( array $form_data ): bool {

		return self::is_paypal_commerce_single_enabled( $form_data ) || self::is_paypal_commerce_subscriptions_enabled( $form_data );
	}

	/**
	 * Determine whether subscriptions have all required data.
	 *
	 * @since 1.10.0
	 *
	 * @param array $form_data Form data.
	 *
	 * @return bool
	 */
	public static function is_subscriptions_configured( array $form_data ): bool {

		if ( ! self::is_paypal_commerce_subscriptions_enabled( $form_data ) ) {
			return true;
		}

		foreach ( $form_data['payments'][ PayPalCommerce::SLUG ]['recurring'] as $plan ) {

			if ( ! empty( $plan['pp_plan_id'] ) ) {
				continue;
			}

			return false;
		}

		return true;
	}

	/**
	 * Determine whether PayPal Commerce is in use on a page.
	 *
	 * @since 1.10.0
	 *
	 * @param array $forms List of forms (e.g. forms on a current page).
	 *
	 * @return bool
	 */
	public static function is_paypal_commerce_forms_enabled( array $forms ): bool {

		foreach ( $forms as $form_data ) {

			if ( self::is_paypal_commerce_enabled( $form_data ) && self::is_subscriptions_configured( $form_data ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Determine whether a form has a PayPal Commerce field.
	 *
	 * @since 1.10.0
	 *
	 * @param array $forms    Form data (e.g., forms on a current page).
	 * @param bool  $multiple Must be 'true' if $forms contain multiple forms.
	 *
	 * @return bool
	 */
	public static function has_paypal_commerce_field( array $forms, bool $multiple = false ): bool {

		return wpforms_has_field_type( 'paypal-commerce', $forms, $multiple );
	}

	/**
	 * Determine whether PayPal Commerce is in sandbox mode.
	 *
	 * @since 1.10.0
	 *
	 * @return bool
	 */
	public static function is_sandbox_mode(): bool {

		return self::get_mode() === self::SANDBOX;
	}

	/**
	 * Determine whether PayPal Commerce is in production mode.
	 *
	 * @since 1.10.0
	 *
	 * @return bool
	 */
	public static function is_production_mode(): bool {

		return self::get_mode() === self::PRODUCTION;
	}

	/**
	 * Retrieve PayPal Commerce mode.
	 *
	 * @since 1.10.0
	 *
	 * @return string
	 */
	public static function get_mode(): string {

		return wpforms_setting( 'paypal-commerce-sandbox-mode' ) ? self::SANDBOX : self::PRODUCTION;
	}

	/**
	 * Set/update PayPal Commerce mode.
	 *
	 * @since 1.10.0
	 *
	 * @param string $mode PayPal mode that will be set.
	 *
	 * @return bool
	 */
	public static function set_mode( string $mode ): bool {

		$key              = 'paypal-commerce-sandbox-mode';
		$settings         = (array) get_option( 'wpforms_settings', [] );
		$settings[ $key ] = $mode === self::SANDBOX;

		return update_option( 'wpforms_settings', $settings );
	}

	/**
	 * Retrieve PayPal Commerce available modes.
	 *
	 * @since 1.10.0
	 *
	 * @return array
	 */
	public static function get_available_modes(): array {

		return [ self::SANDBOX, self::PRODUCTION ];
	}

	/**
	 * Validate PayPal Commerce mode to ensure it's either 'production' or 'sandbox'.
	 * If a given mode is invalid, fetches a current mode.
	 *
	 * @since 1.10.0
	 *
	 * @param string $mode PayPal Commerce mode to validate.
	 *
	 * @return string
	 */
	public static function validate_mode( string $mode ): string {

		return in_array( $mode, self::get_available_modes(), true ) ? $mode : self::get_mode();
	}

	/**
	 * Retrieve the WPForms > Payments settings page URL.
	 *
	 * @since 1.10.0
	 *
	 * @return string
	 */
	public static function get_settings_page_url(): string {

		return add_query_arg(
			[
				'page' => 'wpforms-settings',
				'view' => 'payments',
			],
			admin_url( 'admin.php' )
		);
	}

	/**
	 * Look for the PayPal Commerce field.
	 *
	 * @since 1.10.0
	 *
	 * @param array $fields Form fields.
	 *
	 * @return array
	 */
	public static function get_paypal_field( array $fields ): array {

		foreach ( $fields as $field ) {

			if ( empty( $field['type'] ) || $field['type'] !== 'paypal-commerce' ) {
				continue;
			}

			return $field;
		}

		return [];
	}

	/**
	 * Log payment errors.
	 *
	 * @since 1.10.0
	 *
	 * @param string       $title    Error title.
	 * @param string       $form_id  Form ID.
	 * @param array|string $messages Error messages.
	 * @param string       $level    Error level to add to 'payment' error level.
	 *
	 * @noinspection PhpMissingParamTypeInspection
	 */
	public static function log_errors( string $title, $form_id, $messages = [], $level = 'error' ): void {

		wpforms_log(
			$title,
			$messages,
			[
				'type'    => [ 'payment', $level ],
				'form_id' => $form_id,
			]
		);
	}

	/**
	 * Get the subscription plan id without CL rules.
	 *
	 * @since 1.10.0
	 *
	 * @param array $form_data Form settings.
	 *
	 * @return string
	 */
	public static function get_subscription_plan_id_without_rule( array $form_data ): string {

		if ( ! self::is_paypal_commerce_subscriptions_enabled( $form_data ) ) {
			return '';
		}

		// If the addon is not activated, we can't use conditional logic.
		$is_addon_active = self::is_pro();

		foreach ( $form_data['payments'][ PayPalCommerce::SLUG ]['recurring'] as $plan_id => $recurring ) {

			if ( empty( $recurring['conditional_logic'] ) || ! $is_addon_active ) {
				return $plan_id;
			}
		}

		return '';
	}

	/**
	 * Prepare amount value for the API call.
	 *
	 * @since 1.10.0
	 *
	 * @param float|int|string $amount Amount value. Number or numeric string.
	 *                                 NOTE: The string should be a regular numeric value `1500.00`, but not the currency amount `1.500,00€`.
	 *
	 * @return string
	 */
	public static function format_amount_for_api_call( $amount ): string {

		// Reformat the amount to prevent API errors.
		return number_format( (float) $amount, wpforms_get_currency_decimals( wpforms_get_currency() ), '.', '' );
	}

	/**
	 * Determine whether the addon is activated and the appropriate license is set.
	 *
	 * @since 1.10.0
	 *
	 * @return bool
	 */
	public static function is_pro(): bool {

		return self::is_addon_active() && self::is_allowed_license_type();
	}

	/**
	 * Determine whether the addon is activated.
	 *
	 * @since 1.10.0
	 *
	 * @return bool
	 */
	public static function is_addon_active(): bool {

		return wpforms_is_addon_initialized( 'paypal-commerce' );
	}

	/**
	 * Determine whether a license is ok.
	 *
	 * @since 1.10.0
	 *
	 * @return bool
	 */
	public static function is_license_ok(): bool {

		return self::is_license_active() && self::is_allowed_license_type();
	}

	/**
	 * Determine whether a license type is allowed.
	 *
	 * @since 1.10.0
	 *
	 * @return bool
	 */
	public static function is_allowed_license_type(): bool {

		return in_array( wpforms_get_license_type(), [ 'pro', 'elite', 'agency', 'ultimate' ], true );
	}

	/**
	 * Determine whether a license key is active.
	 *
	 * @since 1.10.0
	 *
	 * @return bool
	 */
	public static function is_license_active(): bool {

		$license = (array) get_option( 'wpforms_license', [] );

		return ! empty( wpforms_get_license_key() ) &&
			empty( $license['is_expired'] ) &&
			empty( $license['is_disabled'] ) &&
			empty( $license['is_invalid'] );
	}

	/**
	 * Webhooks: get endpoint data similar to Stripe.
	 *
	 * @since 1.10.0
	 *
	 * @return array
	 */
	public static function get_webhook_endpoint_data(): array {

		return [
			'namespace' => 'wpforms',
			'route'     => 'ppc/webhooks',
			'fallback'  => 'wpforms_paypal_commerce_webhooks',
		];
	}

	/**
	 * Determine if PayPal Commerce webhooks are enabled (settings toggle).
	 *
	 * @since 1.10.0
	 *
	 * @return bool
	 */
	public static function is_webhook_enabled(): bool {

		$settings = get_option( 'wpforms_settings', [] );

		// Default to true if the setting doesn't exist.
		if ( ! isset( $settings['paypal-commerce-webhooks-enabled'] ) ) {
			return true;
		}

		return (bool) $settings['paypal-commerce-webhooks-enabled'];
	}

	/**
	 * Get selected webhooks communication method: 'rest' or 'curl'.
	 *
	 * @since 1.10.0
	 *
	 * @return string
	 */
	public static function get_webhook_communication(): string {

		$method = (string) wpforms_setting( 'paypal-commerce-webhooks-communication', 'rest' );

		return in_array( $method, [ 'rest', 'curl' ], true ) ? $method : 'rest';
	}

	/**
	 * Build REST webhooks URL.
	 *
	 * @since 1.10.0
	 *
	 * @return string
	 */
	public static function get_webhook_url_for_rest(): string {

		$data = self::get_webhook_endpoint_data();

		return rest_url( $data['namespace'] . '/' . $data['route'] );
	}

	/**
	 * Build PHP listener (URL param) webhooks URL.
	 *
	 * @since 1.10.0
	 *
	 * @return string
	 */
	public static function get_webhook_url_for_curl(): string {

		$fallback = self::get_webhook_endpoint_data()['fallback'];

		return add_query_arg( $fallback, '1', site_url() );
	}

	/**
	 * Get effective webhooks URL depending on settings.
	 *
	 * @since 1.10.0
	 *
	 * @return string
	 */
	public static function get_webhook_url(): string {

		// Allow overriding the webhook URL via constant.
		if ( defined( 'WPFORMS_PAYPAL_COMMERCE_WHURL' ) && WPFORMS_PAYPAL_COMMERCE_WHURL ) {
			return WPFORMS_PAYPAL_COMMERCE_WHURL;
		}

		return self::get_webhook_communication() === 'curl'
			? self::get_webhook_url_for_curl()
			: self::get_webhook_url_for_rest();
	}

	/**
	 * Determine if PayPal Commerce webhooks are configured.
	 *
	 * Webhooks are considered configured if the mode-specific Webhooks ID
	 * is present in WPForms settings.
	 *
	 * @since 1.10.0
	 *
	 * @return bool
	 */
	public static function is_webhook_configured(): bool {

		$mode = self::get_mode();

		return ! empty( wpforms_setting( 'paypal-commerce-webhooks-id-' . $mode ) );
	}

	/**
	 * Determinate if a legacy (first party) integration is used.
	 *
	 * @since 1.10.0
	 *
	 * @return bool Return true if the legacy (first party) is used. Otherwise, false.
	 */
	public static function is_legacy(): bool {

		$mode        = self::get_mode();
		$connections = (array) get_option( 'wpforms_paypal_commerce_connections', [] );

		return ! isset( $connections[ $mode ]['type'] ) || $connections[ $mode ]['type'] !== Connection::TYPE_THIRD_PARTY;
	}

	/**
	 * Determine if the selected currency is supported.
	 *
	 * @since 1.10.0
	 *
	 * @link https://developer.paypal.com/docs/reports/reference/paypal-supported-currencies/
	 *
	 * @return bool
	 */
	public static function is_currency_supported(): bool {

		return in_array( wpforms_get_currency(), [ 'AUD', 'BRL', 'CAD', 'CNY', 'CZK', 'DKK', 'EUR', 'HKD', 'HUF', 'ILS', 'JPY', 'MYR', 'MXN', 'TWD', 'NZD', 'NOK', 'PHP', 'PLN', 'GBP', 'SGD', 'SEK', 'CHF', 'THB', 'USD' ], true );
	}
}
