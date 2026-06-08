<?php

namespace WPForms\Forms\Fields\Traits;

/**
 * Trait ReadOnlyField.
 *
 * Methods for read-only fields.
 *
 * @since 1.9.8
 */
trait ReadOnlyField {

	/**
	 * Whether the Read-Only option is allowed.
	 *
	 * @since 1.9.8
	 *
	 * @var bool
	 */
	protected $allow_read_only = true;

	/**
	 * Init Read-Only field functionality.
	 *
	 * @since 1.9.8
	 */
	public function read_only_init(): void {

		// Read-only field hooks.
		add_action( 'wpforms_field_options_bottom_advanced-options', [ $this, 'field_option_read_only_toggle' ], -10 );
		add_filter( 'wpforms_field_properties', [ $this, 'read_only_field_properties' ], 100, 3 );
		add_filter( "wpforms_admin_builder_ajax_save_form_field_{$this->type}", [ $this, 'read_only_save_form_field' ], 100, 3 );
		add_filter( 'wpforms_frontend_strings', [ $this, 'read_only_frontend_strings' ] );
	}

	/**
	 * Display the Read-Only toggle on the Advanced Options tab.
	 *
	 * @since 1.9.8
	 *
	 * @param array $field Field data.
	 *
	 * @return void
	 */
	public function field_option_read_only_toggle( array $field ): void {

		if ( $field['type'] !== $this->type || ! $this->allow_read_only ) {
			return;
		}

		$value   = $field['read_only'] ?? '0';
		$tooltip = esc_html__( 'Check this option to show the field’s value without allowing changes. It will still be submitted.', 'wpforms-lite' );

		$output = $this->field_element(
			'toggle',
			$field,
			[
				'slug'    => 'read_only',
				'value'   => $value,
				'desc'    => esc_html__( 'Read-Only', 'wpforms-lite' ),
				'tooltip' => $tooltip,
			],
			false
		);

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'read_only',
				'content' => $output,
			]
		);
	}

	/**
	 * Add a Read-Only field CSS class.
	 *
	 * @since 1.9.8
	 *
	 * @param array|mixed $properties Field properties.
	 * @param array       $field      Field data and settings.
	 * @param array       $form_data  Form data and settings.
	 *
	 * @return array
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function read_only_field_properties( $properties, array $field, array $form_data ): array { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed

		$properties = (array) $properties;

		if ( $field['type'] !== $this->type || ! $this->allow_read_only || empty( $field['read_only'] ) ) {
			return $properties;
		}

		$properties['container']['class'][] = 'wpforms-field-readonly';

		return $properties;
	}

	/**
	 * Filter field data before saving the form.
	 *
	 * @since 1.9.8
	 *
	 * @param array $field_data      Field data.
	 * @param array $form_data       Forms data.
	 * @param array $saved_form_data Saved form data.
	 *
	 * @noinspection PhpMissingParamTypeInspection
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function read_only_save_form_field( $field_data, array $form_data, array $saved_form_data ): array { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed

		$field_data = (array) $field_data;

		// Unset the `required` field option if the field is Read-Only.
		if ( ! empty( $field_data['read_only'] ) ) {
			unset( $field_data['required'] );
		}

		return $field_data;
	}

	/**
	 * Add read-only related strings to the frontend.
	 *
	 * @since 1.9.8
	 *
	 * @param array|mixed $strings Frontend strings.
	 *
	 * @return array Frontend strings.
	 */
	public function read_only_frontend_strings( $strings ): array {

		$strings                             = (array) $strings;
		$strings['readOnlyDisallowedFields'] = $strings['readOnlyDisallowedFields'] ?? [];

		if ( $this->allow_read_only ) {
			return $strings;
		}

		$strings['readOnlyDisallowedFields'][] = $this->type;

		return $strings;
	}
}
