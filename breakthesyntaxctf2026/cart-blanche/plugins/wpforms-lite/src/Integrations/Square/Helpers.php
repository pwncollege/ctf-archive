<?php

namespace WPForms\Integrations\Square;

use WPForms\Vendor\Square\Environment;
use WPForms\Vendor\Square\Models\SubscriptionCadence;
use WPForms\Helpers\Transient;

/**
 * Square related helper methods.
 *
 * @since 1.9.5
 */
class Helpers {

	/**
	 * Determine whether the addon is activated and appropriate license is set.
	 *
	 * @since 1.9.5
	 *
	 * @return bool
	 */
	public static function is_pro(): bool {

		return self::is_addon_active() && self::is_allowed_license_type();
	}

	/**
	 * Determine whether the addon is activated.
	 *
	 * @since 1.9.5
	 *
	 * @return bool
	 */
	public static function is_addon_active(): bool {

		return wpforms_is_addon_initialized( 'square' );
	}

	/**
	 * Determine whether a license is ok.
	 *
	 * @since 1.9.5
	 *
	 * @return bool
	 */
	public static function is_license_ok(): bool {

		return self::is_license_active() && self::is_allowed_license_type();
	}

	/**
	 * Determine whether a license type is allowed.
	 *
	 * @since 1.9.5
	 *
	 * @return bool
	 */
	public static function is_allowed_license_type(): bool {

		return in_array( wpforms_get_license_type(), [ 'pro', 'elite', 'agency', 'ultimate' ], true );
	}

	/**
	 * Determine whether a license key is active.
	 *
	 * @since 1.9.5
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
	 * Determine if Square single payment is enabled for the form.
	 *
	 * @since 1.9.5
	 *
	 * @param array $form_data Form data and settings.
	 *
	 * @return bool
	 */
	private static function is_square_single_enabled( array $form_data ): bool {

		return ! empty( $form_data['payments']['square']['enable'] ) || ! empty( $form_data['payments']['square']['enable_one_time'] );
	}

	/**
	 * Determine if Square recurring payment is enabled for the form.
	 *
	 * @since 1.9.5
	 *
	 * @param array $form_data Form data and settings.
	 *
	 * @return bool
	 */
	public static function is_square_recurring_enabled( array $form_data ): bool {

		return ! empty( $form_data['payments']['square']['enable_recurring'] );
	}

	/**
	 * Determine if Square payment enabled for the form.
	 *
	 * @since 1.9.5
	 *
	 * @param array $form_data Form data.
	 *
	 * @return bool
	 */
	public static function is_payments_enabled( array $form_data ): bool {

		return self::is_square_single_enabled( $form_data ) || self::is_square_recurring_enabled( $form_data );
	}

	/**
	 * Determine if Square is in use on the page.
	 *
	 * @since 1.9.5
	 *
	 * @param array $forms Forms data (e.g. forms on a current page).
	 *
	 * @return bool
	 */
	public static function has_square_enabled( array $forms ): bool {

		foreach ( $forms as $form_data ) {
			if ( self::is_payments_enabled( $form_data ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Determine if Square field is in the form.
	 *
	 * @since 1.9.5
	 *
	 * @param array $forms    Form data (e.g. forms on a current page).
	 * @param bool  $multiple Must be 'true' if $forms contain multiple forms.
	 *
	 * @return bool
	 */
	public static function has_square_field( array $forms, bool $multiple = false ): bool {

		return wpforms_has_field_type( 'square', $forms, $multiple );
	}

	/**
	 * Determine whether Square is in sandbox mode.
	 *
	 * @since 1.9.5
	 *
	 * @return bool
	 */
	public static function is_sandbox_mode(): bool {

		return self::get_mode() === Environment::SANDBOX;
	}

	/**
	 * Determine whether Square is in production mode.
	 *
	 * @since 1.9.5
	 *
	 * @return bool
	 */
	public static function is_production_mode(): bool {

		return self::get_mode() === Environment::PRODUCTION;
	}

	/**
	 * Retrieve Square mode from WPForms settings.
	 *
	 * @since 1.9.5
	 *
	 * @return string
	 */
	public static function get_mode(): string {

		return wpforms_setting( 'square-sandbox-mode' ) ? Environment::SANDBOX : Environment::PRODUCTION;
	}

	/**
	 * Set/update Square mode from WPForms settings.
	 *
	 * @since 1.9.5
	 *
	 * @param string $mode Square mode that will be set.
	 *
	 * @return bool
	 */
	public static function set_mode( string $mode ): bool {

		$key              = 'square-sandbox-mode';
		$settings         = (array) get_option( 'wpforms_settings', [] );
		$settings[ $key ] = $mode === Environment::SANDBOX;

		return update_option( 'wpforms_settings', $settings );
	}

	/**
	 * Retrieve Square Business Location ID from WPForms settings.
	 *
	 * @since 1.9.5
	 *
	 * @param string $mode Square mode.
	 *
	 * @return string
	 */
	public static function get_location_id( string $mode = '' ): string {

		$mode = self::validate_mode( $mode );

		return wpforms_setting( 'square-location-id-' . $mode, '' );
	}

	/**
	 * Set/update Square Business Location ID from WPForms settings.
	 *
	 * @since 1.9.5
	 *
	 * @param string $id   The location ID.
	 * @param string $mode Square mode.
	 *
	 * @return bool
	 */
	public static function set_locataion_id( string $id, string $mode ): bool {

		$mode             = self::validate_mode( $mode );
		$key              = 'square-location-id-' . $mode;
		$settings         = (array) get_option( 'wpforms_settings', [] );
		$settings[ $key ] = $id;

		return update_option( 'wpforms_settings', $settings );
	}

	/**
	 * Delete transients by mode.
	 *
	 * @since 1.9.5
	 *
	 * @param string $mode Square mode.
	 */
	public static function detete_transients( string $mode ) {

		Transient::delete( 'wpforms_square_account_' . $mode );
		Transient::delete( 'wpforms_square_active_locations_' . $mode );
	}

	/**
	 * Retrieve Square available modes.
	 *
	 * @since 1.9.5
	 *
	 * @return array
	 */
	public static function get_available_modes(): array {

		return [ Environment::SANDBOX, Environment::PRODUCTION ];
	}

	/**
	 * Validate Square mode to ensure it's either 'production' or 'sandbox'.
	 * If given mode is invalid, fetches current Square mode.
	 *
	 * @since 1.9.5
	 *
	 * @param string $mode Square mode to validate.
	 *
	 * @return string
	 */
	public static function validate_mode( string $mode ): string {

		return in_array( $mode, self::get_available_modes(), true ) ? $mode : self::get_mode();
	}

	/**
	 * Retrieve the WPForms > Payments settings page URL.
	 *
	 * @since 1.9.5
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
	 * The `array_key_first` polyfill.
	 *
	 * @since 1.9.5
	 *
	 * @param array $arr Input array.
	 *
	 * @return mixed|null
	 */
	public static function array_key_first( array $arr ) {

		if ( function_exists( 'array_key_first' ) ) {
			return array_key_first( $arr );
		}

		foreach ( $arr as $key => $unused ) {
			return $key;
		}

		return null;
	}

	/**
	 * Determine if webhook ID and secret are set in WPForms settings.
	 *
	 * @since 1.9.5
	 *
	 * @return bool
	 */
	public static function is_webhook_configured(): bool {

		$mode = self::get_mode();

		return wpforms_setting( 'square-webhooks-id-' . $mode ) && wpforms_setting( 'square-webhooks-secret-' . $mode );
	}

	/**
	 * Determine if Square is configured and valid.
	 *
	 * @since 1.9.5
	 *
	 * @return bool
	 */
	public static function is_square_configured(): bool {

		$connection = Connection::get();

		// Check if connection is configured and valid.
		return ! ( ! $connection || ! $connection->is_configured() || ! $connection->is_valid() );
	}

	/**
	 * Get webhook URL for REST API.
	 *
	 * @since 1.9.5
	 *
	 * @return string
	 */
	public static function get_webhook_url_for_rest(): string {

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
	 * Reset Square webhooks settings.
	 *
	 * @since 1.9.5
	 *
	 * @param bool $reset_enable Optional. Whether to reset the webhook enabled status. Default is false.
	 *
	 * @return bool
	 */
	public static function reset_webhook_configuration( bool $reset_enable = false ): bool {

		$settings = (array) get_option( 'wpforms_settings', [] );

		$mode = self::get_mode();

		if ( $reset_enable ) {
			$settings['square-webhooks-enabled'] = false; // Switch off webhooks.
		}

		$settings[ 'square-webhooks-id-' . $mode ]     = '';
		$settings[ 'square-webhooks-secret-' . $mode ] = '';

		return update_option( 'wpforms_settings', $settings );
	}

	/**
	 * Determine the billing cadences of a Subscription.
	 *
	 * @since 1.9.5
	 *
	 * @return array
	 */
	public static function get_subscription_cadences(): array {

		/**
		 * Filter the available billing cadences of a Subscription.
		 *
		 * @since 1.9.5
		 *
		 * @param array $cadences Subscription billing cadences.
		 */
		return (array) apply_filters(
			'wpforms_integrations_square_helpers_get_subscription_cadences',
			[
				'daily'      => [
					'slug'  => 'daily',
					'name'  => esc_html__( 'Daily', 'wpforms-lite' ),
					'value' => SubscriptionCadence::DAILY,
				],
				'weekly'     => [
					'slug'  => 'weekly',
					'name'  => esc_html__( 'Weekly', 'wpforms-lite' ),
					'value' => SubscriptionCadence::WEEKLY,
				],
				'monthly'    => [
					'slug'  => 'monthly',
					'name'  => esc_html__( 'Monthly', 'wpforms-lite' ),
					'value' => SubscriptionCadence::MONTHLY,
				],
				'quarterly'  => [
					'slug'  => 'quarterly',
					'name'  => esc_html__( 'Quarterly', 'wpforms-lite' ),
					'value' => SubscriptionCadence::QUARTERLY,
				],
				'semiyearly' => [
					'slug'  => 'semiyearly',
					'name'  => esc_html__( 'Semi-Yearly', 'wpforms-lite' ),
					'value' => SubscriptionCadence::EVERY_SIX_MONTHS,
				],
				'yearly'     => [
					'slug'  => 'yearly',
					'name'  => esc_html__( 'Yearly', 'wpforms-lite' ),
					'value' => SubscriptionCadence::ANNUAL,
				],
			]
		);
	}

	/**
	 * Return a formatted amount required by Square API.
	 *
	 * @since 1.9.5
	 *
	 * @param string $amount Price amount.
	 *
	 * @return float|int
	 */
	public static function format_amount( string $amount ) {

		return wpforms_sanitize_amount( $amount ) * wpforms_get_currency_multiplier();
	}

	/**
	 * Get Square webhook endpoint data.
	 *
	 * @since 1.9.5
	 *
	 * @return array
	 */
	public static function get_webhook_endpoint_data(): array {

		return [
			'namespace' => 'wpforms',
			'route'     => 'square/webhooks',
			'fallback'  => 'wpforms_square_webhooks',
		];
	}

	/**
	 * Get Square webhook endpoint URL.
	 *
	 * If the constant WPFORMS_SQUARE_WHURL is defined, it will be used as the webhook URL.
	 *
	 * @since 1.9.5
	 *
	 * @return string
	 */
	public static function get_webhook_url(): string {

		if ( defined( 'WPFORMS_SQUARE_WHURL' ) ) {
			return WPFORMS_SQUARE_WHURL;
		}

		if ( self::is_rest_api_set() ) {
			return self::get_webhook_url_for_rest();
		}

		return self::get_webhook_url_for_curl();
	}

	/**
	 * Determine if the REST API is set in WPForms settings.
	 *
	 * @since 1.9.5
	 *
	 * @return bool
	 */
	public static function is_rest_api_set(): bool {

		return wpforms_setting( 'square-webhooks-communication', 'rest' ) === 'rest';
	}

	/**
	 * Get webhook URL for cURL fallback.
	 *
	 * @since 1.9.5
	 *
	 * @return string
	 */
	public static function get_webhook_url_for_curl(): string {

		return add_query_arg( self::get_webhook_endpoint_data()['fallback'], '1', site_url() );
	}

	/**
	 * Determine if webhooks are enabled in WPForms settings.
	 *
	 * @since 1.9.5
	 *
	 * @return bool
	 */
	public static function is_webhook_enabled(): bool {

		return wpforms_setting( 'square-webhooks-enabled' );
	}

	/**
	 * Determine whether the application fee is supported.
	 *
	 * @since 1.9.5
	 *
	 * @param string $currency Currency.
	 *
	 * @return bool
	 */
	public static function is_application_fee_supported( string $currency = '' ): bool {

		$currency = ! $currency ? wpforms_get_currency() : $currency;

		return strtoupper( $currency ) === 'USD';
	}
}
