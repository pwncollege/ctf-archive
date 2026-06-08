<?php

namespace WPForms\Admin\Education\Builder;

use WPForms\Admin\Education\AddonsItemBase;
use WPForms\Admin\Education\Helpers;


/**
 * Builder/Geolocation Education feature for Lite and Pro.
 *
 * @since 1.6.6
 */
class Geolocation extends AddonsItemBase {

	/**
	 * Indicate if the current Education feature is allowed to load.
	 *
	 * @since 1.6.6
	 *
	 * @return bool
	 *
	 * @noinspection ReturnTypeCanBeDeclaredInspection
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public function allow_load() {

		return wpforms_is_admin_page( 'builder' ) || wp_doing_ajax();
	}

	/**
	 * Hooks.
	 *
	 * @since 1.6.6
	 *
	 * @noinspection ReturnTypeCanBeDeclaredInspection
	 */
	public function hooks() {

		add_action( 'wpforms_field_options_bottom_advanced-options', [ $this, 'geolocation_options' ], 10, 2 );
	}

	/**
	 * Display geolocation options.
	 *
	 * @since 1.6.6
	 *
	 * @param array  $field    Field data.
	 * @param object $instance Builder instance.
	 *
	 * @noinspection ReturnTypeCanBeDeclaredInspection
	 * @noinspection PhpMissingParamTypeInspection
	 */
	public function geolocation_options( $field, $instance ) {

		if ( ! in_array( $field['type'], [ 'text', 'address' ], true ) ) {
			return;
		}

		$addon = $this->addons->get_addon( 'geolocation' );

		if (
			empty( $addon ) ||
			empty( $addon['action'] ) ||
			empty( $addon['status'] ) || (
				$addon['status'] === 'active' &&
				$addon['action'] !== 'upgrade'
			)
		) {
			return;
		}

		$row_args            = $this->get_address_autocomplete_row_attributes( $addon );
		$row_args['content'] = $instance->field_element(
			'toggle',
			$field,
			$this->get_address_autocomplete_field_attributes( $addon ),
			false
		);

		$instance->field_element( 'row', $field, $row_args );
	}

	/**
	 * Get attributes for address autocomplete row.
	 *
	 * @since 1.6.6
	 *
	 * @param array $addon Current addon information.
	 *
	 * @return array
	 */
	private function get_address_autocomplete_row_attributes( array $addon ): array {

		$data    = $this->prepare_field_action_data( $addon );
		$default = [
			'slug' => 'enable_address_autocomplete',
		];

		if ( ! empty( $data ) ) {
			return wp_parse_args( $data, $default );
		}

		return wp_parse_args(
			[
				'data'  => [
					'action'      => 'upgrade',
					'name'        => esc_html__( 'Address Autocomplete', 'wpforms-lite' ),
					'utm-content' => 'Address Autocomplete',
					'licence'     => 'pro',
					'message'     => esc_html__( 'We\'re sorry, Address Autocomplete is part of the Geolocation Addon and not available on your plan. Please upgrade to the PRO plan to unlock all these awesome features.', 'wpforms-lite' ),
				],
				'class' => 'education-modal',
			],
			$default
		);
	}

	/**
	 * Get attributes for address autocomplete field.
	 *
	 * @since 1.6.6
	 *
	 * @param array $addon Current addon information.
	 *
	 * @return array
	 */
	private function get_address_autocomplete_field_attributes( array $addon ): array {

		$default = [
			'slug'  => 'enable_address_autocomplete',
			'value' => '0',
			'desc'  => esc_html__( 'Enable Address Autocomplete', 'wpforms-lite' ),
		];

		if ( $addon['plugin_allow'] ) {
			return $default;
		}

		return wp_parse_args(
			[
				'desc'  => sprintf(
					'%1$s%2$s',
					esc_html__( 'Enable Address Autocomplete', 'wpforms-lite' ),
					Helpers::get_badge( 'Pro', 'sm', 'inline', 'slate' )
				),
				'attrs' => [
					'disabled' => 'disabled',
				],
			],
			$default
		);
	}
}
