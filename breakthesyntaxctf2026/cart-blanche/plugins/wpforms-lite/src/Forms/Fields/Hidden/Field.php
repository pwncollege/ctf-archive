<?php

namespace WPForms\Forms\Fields\Hidden;

use WPForms\Forms\Fields\Traits\ProField as ProFieldTrait;
use WPForms_Field;

/**
 * Hidden text field.
 *
 * @since 1.9.4
 */
class Field extends WPForms_Field {

	use ProFieldTrait;

	/**
	 * Primary class constructor.
	 *
	 * @since 1.9.4
	 */
	public function init() {

		// Define field type information.
		$this->name            = esc_html__( 'Hidden Field', 'wpforms-lite' );
		$this->type            = 'hidden';
		$this->icon            = 'fa-eye-slash';
		$this->order           = 98;
		$this->group           = 'fancy';
		$this->allow_read_only = false;

		$this->default_settings = [
			'label_hide' => '1',
		];

		$this->init_pro_field();
		$this->hooks();
	}

	/**
	 * Hooks.
	 *
	 * @since 1.9.4
	 */
	protected function hooks(): void {

		add_filter( 'wpforms_field_new_class', [ $this, 'preview_field_new_class' ], 10, 2 );
	}

	/**
	 * Field options panel inside the builder.
	 *
	 * @since 1.9.4
	 *
	 * @param array $field Field data and settings.
	 */
	public function field_options( $field ) {
		/*
		 * Basic field options.
		 */

		// Options open markup.
		$this->field_option(
			'basic-options',
			$field,
			[
				'markup'      => 'open',
				'after_title' => $this->get_field_options_notice(),
			]
		);

		// Label.
		$this->field_option(
			'label',
			$field,
			[
				'tooltip' => esc_html__( 'Enter text for the form field label. Never displayed on the front-end.', 'wpforms-lite' ),
			]
		);

		// Set the label to disable.
		$this->field_element(
			'text',
			$field,
			[
				'type'  => 'hidden',
				'slug'  => 'label_disable',
				'value' => '1',
			]
		);

		// Options close markup.
		$args = [
			'markup' => 'close',
		];

		$this->field_option( 'basic-options', $field, $args );

		// Advanced options open markup.
		$this->field_option(
			'advanced-options',
			$field,
			[
				'markup' => 'open',
			]
		);

		// Default value.
		$this->field_option( 'default_value', $field );

		// Custom CSS classes.
		$this->field_option( 'css', $field );

		// Hide Label.
		$this->field_option(
			'label_hide',
			$field,
			[
				'class' => 'wpforms-disabled',
			]
		);

		// Advanced options close markup.
		$this->field_option(
			'advanced-options',
			$field,
			[
				'markup' => 'close',
			]
		);
	}

	/**
	 * Get a new field CSS class.
	 *
	 * @since 1.9.4
	 *
	 * @param string|mixed $css_class Preview new field CSS class.
	 * @param array        $field     Field data.
	 *
	 * @return string
	 */
	public function preview_field_new_class( $css_class, array $field ): string {

		$css_class = (string) $css_class;

		if ( empty( $field['type'] ) || $field['type'] !== $this->type ) {
			return $css_class;
		}

		return trim( $css_class . ' label_hide' );
	}

	/**
	 * Field preview inside the builder.
	 *
	 * @since 1.9.4
	 *
	 * @param array $field Field data and settings.
	 */
	public function field_preview( $field ) {

		// Define data.
		$default_value = ! empty( $field['default_value'] ) ? $field['default_value'] : '';

		// The Hidden field label is always hidden.
		$field['label_hide'] = '1';

		// Label.
		$this->field_preview_option(
			'label',
			$field,
			[
				'label_badge' => $this->get_field_preview_badge(),
			]
		);

		// Primary input.
		echo '<input type="text" class="primary-input" value="' . esc_attr( $default_value ) . '" readonly>';
	}

	/**
	 * Field display on the form front-end.
	 *
	 * @since 1.9.4
	 *
	 * @param array $field      Field data and settings.
	 * @param array $deprecated Not used any more field attributes.
	 * @param array $form_data  Form data and settings.
	 */
	public function field_display( $field, $deprecated, $form_data ) {
	}
}
