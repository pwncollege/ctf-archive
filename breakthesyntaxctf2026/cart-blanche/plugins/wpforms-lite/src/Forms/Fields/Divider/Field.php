<?php

namespace WPForms\Forms\Fields\Divider;

use WPForms\Forms\Fields\Traits\ProField as ProFieldTrait;
use WPForms_Field;

/**
 * Section Divider field.
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
		$this->name            = esc_html__( 'Section Divider', 'wpforms-lite' );
		$this->keywords        = esc_html__( 'line, hr', 'wpforms-lite' );
		$this->type            = 'divider';
		$this->icon            = 'fa-arrows-h';
		$this->order           = 170;
		$this->group           = 'fancy';
		$this->allow_read_only = false;

		$this->default_settings = [
			'label_disable' => '1',
		];

		$this->init_pro_field();
		$this->hooks();
	}

	/**
	 * Hooks.
	 *
	 * @since 1.9.4
	 */
	protected function hooks() {
	}

	/**
	 * Field options panel inside the builder.
	 *
	 * @since 1.9.4
	 *
	 * @param array $field Field data.
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
		$this->field_option( 'label', $field );

		// Description.
		$this->field_option( 'description', $field );

		// Set label to the disabled.
		$args = [
			'type'  => 'hidden',
			'slug'  => 'label_disable',
			'value' => '1',
		];

		$this->field_element( 'text', $field, $args );

		// Options close markup.
		$args = [
			'markup' => 'close',
		];

		$this->field_option( 'basic-options', $field, $args );

		/*
		 * Advanced field options.
		 */

		// Options open markup.
		$args = [
			'markup' => 'open',
		];

		$this->field_option( 'advanced-options', $field, $args );

		// Custom CSS classes.
		$this->field_option( 'css', $field );

		// Hide Divider Line toggle.
		$this->hide_divider_line_option( $field );

		// Options close markup.
		$args = [
			'markup' => 'close',
		];

		$this->field_option( 'advanced-options', $field, $args );
	}

	/**
	 * Hide the Divider Line option.
	 *
	 * @since 1.9.7
	 *
	 * @param array $field Field data.
	 */
	private function hide_divider_line_option( array $field ): void {

		$hide_divider_line_value = $field['hide_divider_line'] ?? '0';
		$hide_divider_line       = $this->field_element(
			'toggle',
			$field,
			[
				'slug'    => 'hide_divider_line',
				'value'   => $hide_divider_line_value,
				'desc'    => esc_html__( 'Hide Divider Line', 'wpforms-lite' ),
				'tooltip' => esc_html__( 'Do not show the horizontal divider line.', 'wpforms-lite' ),
			],
			false
		);

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'hide_divider_line',
				'content' => $hide_divider_line,
			]
		);
	}

	/**
	 * Field preview inside the builder.
	 *
	 * @since 1.9.4
	 *
	 * @param array $field Field data.
	 */
	public function field_preview( $field ) {

		// Label.
		$this->field_preview_option(
			'label',
			$field,
			[
				'label_badge' => $this->get_field_preview_badge(),
			]
		);

		// Description.
		$this->field_preview_option( 'description', $field );
	}

	/**
	 * Field display on the form front-end.
	 *
	 * @since 1.9.4
	 *
	 * @param array $field      Field data and settings.
	 * @param array $deprecated Deprecated field attributes. Use field properties.
	 * @param array $form_data  Form data and settings.
	 */
	public function field_display( $field, $deprecated, $form_data ) {
	}
}
