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
trait ColorMapTrait {

	/**
	 * Gets the actual button color based on the provided color input.
	 *
	 * @since 1.10.0
	 *
	 * @param string $color The input color to map.
	 *
	 * @return string The mapped button color, or the input color if no mapping exists.
	 */
	public function get_button_color( string $color ): string {

		$button_map = $this->get_button_map();

		return $button_map[ $color ] ?? $color;
	}

	/**
	 * Gets the logo color based on the button color.
	 *
	 * @since 1.10.0
	 *
	 * @param string $button_color The button color to use for determining the logo color.
	 *
	 * @return string The mapped logo color, or the button color if no mapping exists.
	 */
	public function get_logo_color( string $button_color ): string {

		$logo_map = $this->get_logo_map();

		return $logo_map[ $button_color ] ?? $button_color;
	}
}
