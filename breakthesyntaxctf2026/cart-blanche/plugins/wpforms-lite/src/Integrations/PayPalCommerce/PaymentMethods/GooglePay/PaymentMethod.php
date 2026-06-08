<?php

namespace WPForms\Integrations\PayPalCommerce\PaymentMethods\GooglePay;

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
	 * Supported locales for Google Pay.
	 *
	 * @since 1.10.0
	 *
	 * @link https://developers.google.com/pay/api/web/reference/request-objects#ButtonOptions
	 */
	private const SUPPORTED_LOCALES = [ 'en', 'ar', 'bg', 'ca', 'cs', 'da', 'de', 'el', 'es', 'et', 'fi', 'fr', 'hr', 'id', 'it', 'ja', 'ko', 'ms', 'nl', 'no', 'pl', 'pt', 'ru', 'sk', 'sl', 'sr', 'sv', 'th', 'tr', 'uk', 'zh' ];

	/**
	 * Color mapper instance for handling button and logo colors.
	 *
	 * @since 1.10.0
	 *
	 * @var ColorMapInterface
	 */
	private $color_mapper;

	/**
	 * The locale setting for the application.
	 *
	 * @since 1.10.0
	 *
	 * @var string
	 */
	private $locale;

	/**
	 * Constructor.
	 *
	 * @since 1.10.0
	 *
	 * @param ColorMapInterface $color_mapper Color mapper instance.
	 */
	public function __construct( ColorMapInterface $color_mapper ) {

		$this->color_mapper = $color_mapper;

		$this->set_locale();
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
	 * Retrieves the list of components for integration.
	 *
	 * @since 1.10.0
	 *
	 * @param bool $is_single Whether to consider One-Time Payment or Recurring. Defaults to One-Time.
	 *
	 * @return array The list of components.
	 */
	public function get_components( bool $is_single = true ): array {

		return $is_single ? [ 'googlepay' ] : [];
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
			'wpforms-paypal-commerce-google-pay-sdk',
		];
	}

	/**
	 * Enqueues the necessary assets for the Google Pay integration
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

		// Load Google Pay (extends PaymentHandler).
		wp_enqueue_script(
			'wpforms-paypal-commerce-googlepay',
			WPFORMS_PLUGIN_URL . "assets/js/integrations/paypal-commerce/payment-methods/googlepay{$min}.js",
			[ 'wpforms-paypal-commerce', 'wpforms-paypal-commerce-payment-method' ],
			WPFORMS_VERSION,
			true
		);

		// Load Google Pay SDK for Google Pay button integration.
		// phpcs:disable WordPress.WP.EnqueuedResourceParameters.MissingVersion
		wp_enqueue_script(
			'wpforms-paypal-commerce-google-pay-sdk',
			'https://pay.google.com/gp/p/js/pay.js',
			[],
			null,
			false
		);
		// phpcs:enable WordPress.WP.EnqueuedResourceParameters.MissingVersion
	}

	/**
	 * Retrieves localized settings for the specified field.
	 *
	 * @since 1.10.0
	 *
	 * @param array $field Field properties including shape and color.
	 * @param array $form  The form properties.
	 *
	 * @return array Localized settings for the given field.
	 */
	public function get_localized_settings( array $field, array $form ): array {

		$is_rect = $field['shape'] === 'rect';

		return [
			'googlepay' => [
				'buttonColor'  => $this->color_mapper->get_button_color( $field['color'] ),
				'buttonRadius' => $is_rect ? 4 : 23,
				'locale'       => $this->locale,
				'buttonType'   => 'plain',
				'borderType'   => 'default_border',
				'sizeMode'     => 'fill',
			],
		];
	}

	/**
	 * Sets the locale of the application based on the user's locale settings.
	 *
	 * If the user's locale is not supported, defaults to 'en'.
	 *
	 * @since 1.10.0
	 */
	private function set_locale(): void {

		[ $this->locale ] = explode( '_', get_user_locale() );

		if ( ! in_array( $this->locale, self::SUPPORTED_LOCALES, true ) ) {
			$this->locale = 'en';
		}
	}
}
