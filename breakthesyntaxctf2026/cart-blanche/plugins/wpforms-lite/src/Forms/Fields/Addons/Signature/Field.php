<?php

namespace WPForms\Forms\Fields\Addons\Signature;

use WPForms\Forms\Fields\Traits\ProField as ProFieldTrait;
use WPForms_Field;

/**
 * Signature field.
 *
 * @since 1.9.4
 */
class Field extends WPForms_Field {

	use ProFieldTrait;

	/**
	 * Init class.
	 *
	 * @since 1.9.4
	 */
	public function init() {

		// Define field type information.
		$this->name       = esc_html__( 'Signature', 'wpforms-lite' );
		$this->keywords   = esc_html__( 'user, e-signature', 'wpforms-lite' );
		$this->type       = 'signature';
		$this->icon       = 'fa-pencil';
		$this->order      = 200;
		$this->group      = 'fancy';
		$this->addon_slug = 'signatures';

		$this->default_settings = [
			'size' => 'large',
		];

		$this->init_pro_field();
		$this->hooks();
	}

	/**
	 * Add hooks.
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
	 * @param array $field Field settings.
	 */
	public function field_options( $field ) {

		/**
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

		// Required toggle.
		$this->field_option( 'required', $field );

		// Options close markup.
		$this->field_option(
			'basic-options',
			$field,
			[
				'markup' => 'close',
			]
		);

		/*
		 * Advanced field options.
		 */

		// Options open markup.
		$this->field_option(
			'advanced-options',
			$field,
			[
				'markup' => 'open',
			]
		);

		// Ink color picker.
		$lbl = $this->field_element(
			'label',
			$field,
			[
				'slug'    => 'ink_color',
				'value'   => esc_html__( 'Ink Color', 'wpforms-lite' ),
				'tooltip' => esc_html__( 'Select the color for the signature ink.', 'wpforms-lite' ),
			],
			false
		);

		$ink_color = isset( $field['ink_color'] ) ? wpforms_sanitize_hex_color( $field['ink_color'] ) : '';
		$ink_color = empty( $ink_color ) ? '#000000' : $ink_color;

		$fld = $this->field_element(
			'color',
			$field,
			[
				'slug'  => 'ink_color',
				'value' => $ink_color,
				'data'  => [
					'fallback-color' => $ink_color,
				],
			],
			false
		);

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'ink_color',
				'content' => $lbl . $fld,
				'class'   => 'color-picker-row',
			]
		);

		// Custom CSS classes.
		$this->field_option( 'css', $field );

		// Size.
		$this->field_option( 'size', $field );

		// Hide label.
		$this->field_option( 'label_hide', $field );

		// Options close markup.
		$this->field_option(
			'advanced-options',
			$field,
			[
				'markup' => 'close',
			]
		);
	}

	/**
	 * Field preview inside the builder.
	 *
	 * @since 1.9.4
	 *
	 * @param array $field Field settings.
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

		// Signature placeholder.
		echo '<div class="wpforms-signature-wrap"></div>';

		// Description.
		$this->field_preview_option( 'description', $field );

		// Hide remaining elements.
		$this->field_preview_option( 'hide-remaining', $field );
	}

	/**
	 * Field display on the form front-end.
	 *
	 * @since 1.9.4
	 *
	 * @param array $field      Field settings.
	 * @param array $deprecated Deprecated array.
	 * @param array $form_data  Form data and settings.
	 */
	public function field_display( $field, $deprecated, $form_data ) {
	}
}
