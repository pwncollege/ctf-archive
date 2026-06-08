<?php

namespace WPForms\Integrations\ConstantContact\V3\Settings;

use WPForms\Integrations\ConstantContact\V3\ConstantContact;

/**
 * Class FieldMapping.
 *
 * @since 1.9.3
 */
class FieldMapping {

	/**
	 * Connection data.
	 *
	 * @since 1.9.3
	 *
	 * @var array
	 */
	private $connection;

	/**
	 * Submitted fields.
	 *
	 * @since 1.9.3
	 *
	 * @var array
	 */
	private $fields;

	/**
	 * Constructor.
	 *
	 * @since 1.9.3
	 *
	 * @param array $connection Connection data.
	 * @param array $fields     Fields data.
	 */
	public function __construct( array $connection, array $fields ) {

		$this->connection = $connection;
		$this->fields     = $fields;
	}

	/**
	 * Get a list ID.
	 *
	 * @since 1.9.3
	 *
	 * @return string
	 */
	public function get_list_id(): string {

		return $this->connection['list'] ?? '';
	}

	/**
	 * Get field value.
	 *
	 * @since 1.9.3
	 *
	 * @param string $connection_key Connection key.
	 */
	public function get_field( string $connection_key ): string {

		if ( ! isset( $this->connection[ $connection_key ], $this->fields[ $this->connection[ $connection_key ] ]['value'] ) ) {
			return '';
		}

		$limit = $connection_key === 'opt_out_reason' ? 255 : 50;

		return $this->trim_value( (string) $this->fields[ $this->connection[ $connection_key ] ]['value'], $limit );
	}

	/**
	 * Get field value from connection custom fields.
	 *
	 * @since 1.9.3
	 *
	 * @param string $connection_key Connection key.
	 *
	 * @return string
	 */
	public function get_meta_field( string $connection_key ): string {

		$field_id_full = $this->get_field_meta_id( $connection_key );
		$limit         = $connection_key === 'phone' ? 25 : 50;

		return $this->trim_value( $this->get_field_value( $field_id_full ), $limit );
	}

	/**
	 * Get field value by ID.
	 *
	 * @since 1.9.3
	 *
	 * @param string $field_id Field ID. Can be integer or string in the {field_id}.{subfield} format.
	 *
	 * @return string
	 */
	private function get_field_value( string $field_id ): string {

		$field_parts = explode( '.', $field_id );
		$field_id    = $field_parts[0];
		$field_key   = $field_parts[1] ?? 'value';

		if ( $field_key === 'full' ) {
			$field_key = 'value';
		}

		return $this->fields[ $field_id ][ $field_key ] ?? '';
	}

	/**
	 * Get connection custom fields.
	 *
	 * @since 1.9.3
	 *
	 * @return array
	 */
	private function get_connection_custom_fields(): array {

		if ( empty( $this->connection['fields_meta'] ) ) {
			return [];
		}

		$predefined_custom_fields = ConstantContact::get_predefined_custom_fields();

		$fields_meta = [];

		foreach ( $this->connection['fields_meta'] as $field ) {
			if ( ! isset( $field['name'], $field['field_id'] ) ) {
				continue;
			}

			if ( in_array( $field['name'], $predefined_custom_fields, true ) ) {
				continue;
			}

			$fields_meta[ $field['name'] ] = $field['field_id'];
		}

		return $fields_meta;
	}

	/**
	 * Get a list of CC custom fields.
	 *
	 * @since 1.9.3
	 *
	 * @param array $custom_fields_formats Constant Contact custom fields formats.
	 *
	 * @return array
	 */
	public function get_custom_fields( array $custom_fields_formats ): array {

		$fields_meta   = $this->get_connection_custom_fields();
		$custom_fields = [];

		foreach ( $fields_meta as $custom_field_id => $field_id ) {
			$field_format = $custom_fields_formats[ $custom_field_id ] ?? 'string';
			$value        = $this->get_custom_field_value( (string) $field_id, $field_format );

			if ( wpforms_is_empty_string( $value ) ) {
				continue;
			}

			$custom_fields[] = [
				'custom_field_id' => $custom_field_id,
				'value'           => $this->trim_value( $value, 255 ),
			];
		}

		return $custom_fields;
	}

	/**
	 * Get a custom field value.
	 *
	 * @since 1.9.3
	 *
	 * @param string $field_id     Field ID.
	 * @param string $field_format Constant Contact custom field format.
	 *
	 * @return string
	 */
	private function get_custom_field_value( string $field_id, string $field_format ): string {

		if ( $field_format !== 'date' ) {
			return $this->trim_value( $this->get_field_value( $field_id ), 255 );
		}

		$field = $this->fields[ $field_id ] ?? [];

		// Only Date / Time field is allowed to be sent as a date custom field format.
		if ( empty( $field['unix'] ) ) {
			return '';
		}

		return (string) gmdate( 'm/d/Y', $field['unix'] );
	}

	/**
	 * Get street address from connection data.
	 *
	 * @since 1.9.3
	 *
	 * @return array
	 */
	public function get_street_address(): array {

		$field_id = $this->get_field_meta_id( 'address' );

		if ( empty( $field_id ) || empty( $this->fields[ $field_id ] ) ) {
			return [];
		}

		$address_fields = $this->build_address_fields( $this->fields[ $field_id ] );

		return $this->is_valid_address( $address_fields ) ? $address_fields : [];
	}

	/**
	 * Get meta field ID.
	 *
	 * @since 1.9.3
	 *
	 * @param string $connection_key Connection key.
	 *
	 * @return string
	 */
	private function get_field_meta_id( string $connection_key ): string {

		$fields = wp_list_pluck( $this->connection['fields_meta'], 'field_id', 'name' );

		return $fields[ $connection_key ] ?? '';
	}

	/**
	 * Get address kind.
	 *
	 * @since 1.9.3
	 *
	 * @param array $address Address data.
	 *
	 * @return string
	 */
	private function get_address_kind( array $address ): string {

		$default_kind = 'other';

		/**
		 * Kind of address to be saved in the Constant Contact account.
		 *
		 * Possible values are 'other', 'home', 'work'.
		 *
		 * @since 1.9.3
		 *
		 * @param array        $default_kind  Default kind of address, possible values are 'other', 'home', 'work'.
		 * @param array        $address       Address data.
		 * @param FieldMapping $field_mapping Instance of the FieldMapping class.
		 *
		 * @return string Default value is 'other'.
		 */
		$kind = apply_filters( 'wpforms_integrations_constant_contact_v3_settings_field_mapping_get_address_kind', $default_kind, $address, $this );

		if ( in_array( $kind, [ $default_kind, 'home', 'work' ], true ) ) {
			return $kind;
		}

		return $default_kind;
	}

	/**
	 * Get address street.
	 *
	 * @since 1.9.3
	 *
	 * @param array $address Address data.
	 *
	 * @return string
	 */
	private function get_address_street( array $address ): string {

		$street = $address['address1'] ?? '';

		return ! empty( $address['address2'] )
			? $street . ' ' . $address['address2']
			: $street;
	}

	/**
	 * Build address fields.
	 *
	 * @since 1.9.3
	 *
	 * @param array $address Address data.
	 *
	 * @return array
	 */
	private function build_address_fields( array $address ): array {

		return [
			'kind'        => $this->get_address_kind( $address ),
			'street'      => $this->trim_value( $this->get_address_street( $address ), 255 ),
			'city'        => $this->trim_value( $address['city'] ?? '' ),
			'state'       => $this->trim_value( $address['state'] ?? '' ),
			'postal_code' => $this->trim_value( $address['postal'] ?? '' ),
			'country'     => $this->trim_value( $address['country'] ?? '' ),
		];
	}

	/**
	 * Check if the address is valid.
	 *
	 * @since 1.9.3
	 *
	 * @param array $address_fields Address fields.
	 *
	 * @return bool
	 */
	private function is_valid_address( array $address_fields ): bool {

		$filtered = array_filter( $address_fields );

		return count( $filtered ) > 1;
	}

	/**
	 * Trim value to the specified length.
	 *
	 * @see https://v3.developer.constantcontact.com/api_reference/index.html#!/Contacts/createOrUpdateContact
	 *
	 * @since 1.9.3
	 *
	 * @param string $value  Value to trim.
	 * @param int    $length Length to trim to.
	 *
	 * @return string
	 */
	private function trim_value( string $value, int $length = 50 ): string {

		return wp_html_excerpt( $value, $length );
	}
}
