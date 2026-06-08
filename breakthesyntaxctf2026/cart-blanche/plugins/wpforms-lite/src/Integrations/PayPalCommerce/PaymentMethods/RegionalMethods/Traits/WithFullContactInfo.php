<?php

namespace WPForms\Integrations\PayPalCommerce\PaymentMethods\RegionalMethods\Traits;

use WPForms\Integrations\PayPalCommerce\Process\Exceptions\MissingRequiredShippingParam;
use WPForms\Integrations\PayPalCommerce\Process\ProcessHelper;

/**
 * Trait for regional payment methods that require full contact information.
 * Provides name, country_code, and email.
 *
 * @since 1.10.0
 */
trait WithFullContactInfo {

	use WithBasicContactInfo;

	/**
	 * Get contact data including name, country_code, and email.
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
			'email'        => $this->get_contact_email( $submitted_data, $settings ),
		];
	}

	/**
	 * Get the email from submitted data.
	 *
	 * @since 1.10.0
	 *
	 * @param array $submitted_data The submitted form data.
	 * @param array $settings       The settings configuration containing shipping email.
	 *
	 * @throws MissingRequiredShippingParam If the email is not found in the submitted data.
	 *
	 * @return string Email address.
	 */
	protected function get_contact_email( array $submitted_data, array $settings ): string {

		$email = ProcessHelper::get_email_from_settings( $submitted_data, $settings );

		if ( ! empty( $email ) ) {
			return $email;
		}

		throw new MissingRequiredShippingParam( sprintf( 'Email is required for %s payment method.', esc_html( $this->get_type() ) ) );
	}
}
