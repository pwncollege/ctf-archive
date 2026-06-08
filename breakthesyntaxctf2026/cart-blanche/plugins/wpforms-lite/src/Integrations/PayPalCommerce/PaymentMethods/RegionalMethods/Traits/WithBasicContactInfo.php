<?php

namespace WPForms\Integrations\PayPalCommerce\PaymentMethods\RegionalMethods\Traits;

use WPForms\Integrations\PayPalCommerce\Process\Exceptions\MissingRequiredShippingParam;
use WPForms\Integrations\PayPalCommerce\Process\ProcessHelper;

/**
 * Trait for regional payment methods that require basic contact information.
 * Provides name and country_code only.
 *
 * @since 1.10.0
 */
trait WithBasicContactInfo {

	/**
	 * Get contact data including name and country_code.
	 *
	 * @since 1.10.0
	 *
	 * @param array $submitted_data The submitted form data.
	 *
	 * @throws MissingRequiredShippingParam If required shipping parameter is missing.
	 *
	 * @return array Contact data array.
	 */
	protected function get_contact_data( array $submitted_data ): array {

		$settings = $this->process->get_settings();

		return [
			'name'         => $this->get_contact_name( $submitted_data, $settings ),
			'country_code' => $this->get_contact_country_code( $submitted_data, $settings ),
		];
	}

	/**
	 * Get the contact name from submitted data.
	 *
	 * @since 1.10.0
	 *
	 * @param array $submitted_data The submitted form data.
	 * @param array $settings       The settings configuration containing shipping name.
	 *
	 * @throws MissingRequiredShippingParam If the name is not found in the submitted data.
	 *
	 * @return string Contact name.
	 */
	protected function get_contact_name( array $submitted_data, array $settings ): string {

		$name = ProcessHelper::get_submitted_shipping_name_value( $submitted_data, $settings );

		if ( ! empty( $name ) ) {
			return $name;
		}

		throw new MissingRequiredShippingParam( sprintf( 'Name is required for %s payment method.', esc_html( $this->get_type() ) ) );
	}

	/**
	 * Get the country code from submitted data.
	 *
	 * @since 1.10.0
	 *
	 * @param array $submitted_data The submitted form data.
	 * @param array $settings       The settings configuration containing shipping address.
	 *
	 * @throws MissingRequiredShippingParam If the country code is not found in the submitted data.
	 *
	 * @return string Country code.
	 */
	protected function get_contact_country_code( array $submitted_data, array $settings ): string {

		$country_code = ProcessHelper::get_country_code_from_settings( $submitted_data, $settings['shipping_address'] );

		if ( ! empty( $country_code ) ) {
			return $country_code;
		}

		throw new MissingRequiredShippingParam( sprintf( 'Country code is required for %s payment method.', esc_html( $this->get_type() ) ) );
	}
}
