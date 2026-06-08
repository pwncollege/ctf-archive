<?php

namespace WPForms\Integrations\PayPalCommerce\PaymentMethods\ApplePay;

use WPForms\Integrations\PayPalCommerce\Frontend\PaymentMethodAssetsInterface;
use WPForms\Integrations\PayPalCommerce\Frontend\PaymentMethodInterface;
use WPForms\Integrations\PayPalCommerce\PaymentMethods\ColorMapInterface;

/**
 * Handles the frontend functionalities of the PayPal Commerce integration.
 *
 * @since 1.10.0
 */
class PaymentMethod implements PaymentMethodInterface, PaymentMethodAssetsInterface {

	/**
	 * Represents the SDK handle for PayPal Commerce Apple Pay integration.
	 * This constant is used to identify the SDK within the system.
	 *
	 * @since 1.10.0
	 */
	private const SDK_HANDLE = 'wpforms-paypal-commerce-apple-pay-sdk';

	/**
	 * List of disallowed user agents for Apple Pay.
	 *
	 * @since 1.10.0
	 */
	private const DISALLOWED_USER_AGENTS = [
		'Chrome/',
		'CriOS/',
		'Firefox/',
		'OPR/',
		'Edg/',
	];

	/**
	 * List of allowed browsers for Apple Pay.
	 *
	 * @since 1.10.0
	 */
	private const ALLOWED_USER_BROWSERS = [ 'Safari' ];

	/**
	 * List of allowed devices for Apple Pay.
	 *
	 * @since 1.10.0
	 */
	private const ALLOWED_USER_DEVICES = [ 'Macintosh', 'iPhone', 'iPad', 'iPod' ];

	/**
	 * Color mapper instance for handling button and logo color mappings.
	 *
	 * @since 1.10.0
	 *
	 * @var ColorMapInterface
	 */
	private $color_map;

	/**
	 * Constructor.
	 *
	 * @since 1.10.0
	 *
	 * @param ColorMapInterface $color_map An instance of ColorMapInterface to be set.
	 */
	public function __construct( ColorMapInterface $color_map ) {

		$this->color_map = $color_map;
	}

	/**
	 * Checks if the given payment method is supported.
	 *
	 * @since 1.10.0
	 *
	 * @param array $field The field to be checked for support.
	 *
	 * @return bool True if the payment method is supported, false otherwise.
	 */
	public function is_supported( array $field ): bool {

		return ! empty( $field['paypal_checkout'] );
	}

	/**
	 * Retrieves the list of components for the integration.
	 *
	 * @since 1.10.0
	 *
	 * @param bool $is_single Whether to consider One-Time Payment or Recurring. Defaults to One-Time.
	 *
	 * @return array List of components.
	 */
	public function get_components( bool $is_single = true ): array {

		return $is_single ? [ 'applepay' ] : [];
	}

	/**
	 * Retrieves a list of script handles to be loaded asynchronously.
	 *
	 * @since 1.10.0
	 *
	 * @return array List of asynchronous script handles.
	 */
	public function get_async_scripts(): array {

		return [
			self::SDK_HANDLE,
		];
	}

	/**
	 * Enqueues the necessary assets for the Apple Pay integration
	 * within the PayPal Commerce functionality.
	 *
	 * @since 1.10.0
	 *
	 * @param array $payment_types Array of payment type flags (e.g., ['single' => bool, 'recurring' => bool]).
	 */
	public function enqueue( array $payment_types ): void {

		$has_single = ! empty( $payment_types['single'] );

		if ( ! $has_single ) {
			return;
		}

		$min = wpforms_get_min_suffix();

		// Load Apple Pay (extends PaymentHandler).
		wp_enqueue_script(
			'wpforms-paypal-commerce-applepay',
			WPFORMS_PLUGIN_URL . "assets/js/integrations/paypal-commerce/payment-methods/applepay{$min}.js",
			[ 'wpforms-paypal-commerce', 'wpforms-paypal-commerce-payment-method' ],
			WPFORMS_VERSION,
			true
		);

		// Load Apple Pay SDK for Apple Pay button integration.
		// phpcs:disable WordPress.WP.EnqueuedResourceParameters.MissingVersion
		wp_enqueue_script(
			self::SDK_HANDLE,
			'https://applepay.cdn-apple.com/jsapi/1.latest/apple-pay-sdk.js',
			[],
			null,
			false
		);
		// phpcs:enable WordPress.WP.EnqueuedResourceParameters.MissingVersion
	}

	/**
	 * Retrieves localized settings for a given field.
	 *
	 * @since 1.10.0
	 *
	 * @param array $field Field configuration array.
	 * @param array $form  Form configuration array.
	 *
	 * @return array Localized settings.
	 */
	public function get_localized_settings( array $field, array $form ): array {

		$color = $this->color_map->get_button_color( $field['color'] );

		return [
			'applepay' => [
				'buttonColor' => $color === 'white' ? 'white-outline' : $color,
			],
		];
	}

	/**
	 * Checks if the current user agent is supported for Apple Pay.
	 *
	 * Apple Pay is only supported on Safari browser running on Apple devices.
	 *
	 * @since 1.10.0
	 *
	 * @return bool True if a user agent is supported, false otherwise.
	 */
	public static function is_user_agent_supported(): bool {

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) : '';

		if ( empty( $user_agent ) || self::is_disallowed_user_agent( $user_agent ) ) {
			return false;
		}

		return self::is_browser_allowed( $user_agent ) && self::is_device_allowed( $user_agent );
	}

	/**
	 * Checks if the user agent is in the disallowed list.
	 *
	 * @since 1.10.0
	 *
	 * @param string $user_agent The user agent string to check.
	 *
	 * @return bool True if a user agent is disallowed, false otherwise.
	 */
	private static function is_disallowed_user_agent( string $user_agent ): bool {

		foreach ( self::DISALLOWED_USER_AGENTS as $disallowed_agent ) {
			if ( strpos( $user_agent, $disallowed_agent ) !== false ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Checks if the browser is allowed for Apple Pay.
	 *
	 * @since 1.10.0
	 *
	 * @param string $user_agent The user agent string to check.
	 *
	 * @return bool True if a browser is allowed, false otherwise.
	 */
	private static function is_browser_allowed( string $user_agent ): bool {

		foreach ( self::ALLOWED_USER_BROWSERS as $allowed_browser ) {
			if ( strpos( $user_agent, $allowed_browser ) !== false ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Checks if the device is allowed for Apple Pay.
	 *
	 * @since 1.10.0
	 *
	 * @param string $user_agent The user agent string to check.
	 *
	 * @return bool True if a device is allowed, false otherwise.
	 */
	private static function is_device_allowed( string $user_agent ): bool {

		foreach ( self::ALLOWED_USER_DEVICES as $allowed_device ) {
			if ( strpos( $user_agent, $allowed_device ) !== false ) {
				return true;
			}
		}

		return false;
	}
}
