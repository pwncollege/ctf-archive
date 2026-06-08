<?php

namespace WPForms\Integrations\PayPalCommerce\Process;

/**
 * Helper class for PayPal Commerce payment processing.
 *
 * Provides static utility methods for address validation, address mapping,
 * and name field value retrieval used across different payment methods.
 *
 * @since 1.10.0
 */
class ProcessHelper {

	/**
	 * Validates if an address field in submitted data is complete and valid.
	 *
	 * @since 1.10.0
	 *
	 * @param array  $submitted_data The submitted form data.
	 * @param string $address_field  The address field ID.
	 * @param array  $form_data      The form data.
	 *
	 * @return bool True if the address is valid, false otherwise.
	 */
	public static function is_address_field_valid( array $submitted_data, string $address_field, array $form_data ): bool {

		return ! empty( $submitted_data['fields'][ $address_field ]['address1'] ) &&
			! empty( $submitted_data['fields'][ $address_field ]['city'] ) &&
			! empty( $submitted_data['fields'][ $address_field ]['postal'] ) &&
			( $form_data['fields'][ $address_field ]['scheme'] !== 'international' || ! empty( $submitted_data['fields'][ $address_field ]['country'] ) );
	}

	/**
	 * Maps address field data from submitted data to PayPal API format.
	 *
	 * @since 1.10.0
	 *
	 * @param array  $submitted_data The submitted form data.
	 * @param string $address_field  The address field ID.
	 *
	 * @return array The mapped address data in PayPal API format.
	 */
	public static function map_address_field( array $submitted_data, string $address_field ): array {

		return [
			'address_line_1' => sanitize_text_field( $submitted_data['fields'][ $address_field ]['address1'] ),
			'address_line_2' => isset( $submitted_data['fields'][ $address_field ]['address2'] ) ? sanitize_text_field( $submitted_data['fields'][ $address_field ]['address2'] ) : '',
			'admin_area_1'   => isset( $submitted_data['fields'][ $address_field ]['state'] ) ? sanitize_text_field( $submitted_data['fields'][ $address_field ]['state'] ) : '',
			'admin_area_2'   => sanitize_text_field( $submitted_data['fields'][ $address_field ]['city'] ),
			'postal_code'    => sanitize_text_field( $submitted_data['fields'][ $address_field ]['postal'] ),
			'country_code'   => self::get_country_code_from_settings( $submitted_data, $address_field ),
		];
	}

	/**
	 * Retrieves the submitted name field value.
	 *
	 * @since 1.10.0
	 *
	 * @param array  $submitted_data The submitted form data.
	 * @param string $field_id       The name field ID.
	 *
	 * @return string The name value.
	 */
	public static function get_submitted_name_value( array $submitted_data, string $field_id ): string {

		$name = $submitted_data['fields'][ $field_id ] ?? '';

		return sanitize_text_field( ! is_array( $name ) ? $name : implode( ' ', $name ) );
	}

	/**
	 * Retrieves the submitted shipping name field value.
	 *
	 * @since 1.10.0
	 *
	 * @param array $submitted_data The submitted form data.
	 * @param array $settings       The settings data.
	 *
	 * @return string The name value.
	 */
	public static function get_submitted_shipping_name_value( array $submitted_data, array $settings ): string {

		if ( ! isset( $settings['shipping_name'] ) || $settings['shipping_name'] === '' || empty( $submitted_data['fields'][ $settings['shipping_name'] ] ) ) {
			return esc_html__( 'Not specified', 'wpforms-lite' );
		}

		$name = $submitted_data['fields'][ $settings['shipping_name'] ];

		return sanitize_text_field( ! is_array( $name ) ? $name : implode( ' ', $name ) );
	}

	/**
	 * Extracts country code from billing address settings if configured and present in submitted data.
	 *
	 * @since 1.10.0
	 *
	 * @param array  $submitted_data The submitted form data.
	 * @param string $address_field  The address field ID.
	 *
	 * @return string The country code, or empty string if not found.
	 */
	public static function get_country_code_from_settings( array $submitted_data, string $address_field ): string {

		return isset( $submitted_data['fields'][ $address_field ]['country'] ) ? sanitize_text_field( $submitted_data['fields'][ $address_field ]['country'] ) : 'US';
	}

	/**
	 * Extracts email from shipping email settings if configured and present in submitted data.
	 *
	 * @since 1.10.0
	 *
	 * @param array $submitted_data The submitted form data.
	 * @param array $settings       The settings data.
	 *
	 * @return string The email value, or empty string if not found.
	 */
	public static function get_email_from_settings( array $submitted_data, array $settings ): string {

		if ( ! isset( $settings['shipping_email'] ) || $settings['shipping_email'] === '' || empty( $submitted_data['fields'][ $settings['shipping_email'] ] ) ) {
			return '';
		}

		return sanitize_email( $submitted_data['fields'][ $settings['shipping_email'] ] );
	}
}
