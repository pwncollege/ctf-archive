<?php

namespace WPForms\Integrations\PayPalCommerce\PaymentMethods;

/**
 * Abstract class for managing color mappings for payment method buttons and logos.
 *
 * This class provides a common interface for payment methods (like Apple Pay and Google Pay)
 * to define their button and logo color mappings.
 *
 * @since 1.10.0
 */
interface ColorMapInterface {

	/**
	 * Returns a mapping of button colors to their actual rendered colors.
	 *
	 * @since 1.10.0
	 *
	 * @return array<string, string> An associative array mapping input colors to output colors.
	 */
	public function get_button_map(): array;

	/**
	 * Returns a mapping of button colors to their corresponding logo colors.
	 *
	 * @since 1.10.0
	 *
	 * @return array<string, string> An associative array mapping button colors to logo colors.
	 */
	public function get_logo_map(): array;

	/**
	 * Gets the actual button color based on the provided color input.
	 *
	 * @since 1.10.0
	 *
	 * @param string $color The input color to map.
	 *
	 * @return string The mapped button color, or the input color if no mapping exists.
	 */
	public function get_button_color( string $color ): string;

	/**
	 * Gets the logo color based on the button color.
	 *
	 * @since 1.10.0
	 *
	 * @param string $button_color The button color to use for determining the logo color.
	 *
	 * @return string The mapped logo color, or the button color if no mapping exists.
	 */
	public function get_logo_color( string $button_color ): string;
}
