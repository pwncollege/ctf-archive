<?php

namespace WPForms\Forms\Fields\Phone;

use WPForms\Forms\Fields\Traits\ProField as ProFieldTrait;
use WPForms_Field;

/**
 * Phone number field.
 *
 * @since 1.9.4
 */
class Field extends WPForms_Field {

	use ProFieldTrait;

	/**
	 * International Telephone Input library CSS.
	 *
	 * @since 1.9.4
	 */
	public const INTL_VERSION = '25.11.3';

	/**
	 * Primary class constructor.
	 *
	 * @since 1.9.4
	 */
	public function init() {

		// Define field type information.
		$this->name     = esc_html__( 'Phone', 'wpforms-lite' );
		$this->keywords = esc_html__( 'telephone, mobile, cell', 'wpforms-lite' );
		$this->type     = 'phone';
		$this->icon     = 'fa-phone';
		$this->order    = 50;
		$this->group    = 'fancy';

		$this->default_settings = [
			'format' => 'smart',
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

		// Format.
		$lbl = $this->field_element(
			'label',
			$field,
			[
				'slug'    => 'format',
				'value'   => esc_html__( 'Format', 'wpforms-lite' ),
				'tooltip' => esc_html__( 'Select format for the phone form field', 'wpforms-lite' ),
			],
			false
		);
		$fld = $this->field_element(
			'select',
			$field,
			[
				'slug'    => 'format',
				'value'   => ! empty( $field['format'] ) ? esc_attr( $field['format'] ) : 'smart',
				'options' => [
					'smart'         => esc_html__( 'Smart', 'wpforms-lite' ),
					'us'            => esc_html__( 'US', 'wpforms-lite' ),
					'international' => esc_html__( 'International', 'wpforms-lite' ),
				],
			],
			false
		);

		$args = [
			'slug'    => 'format',
			'content' => $lbl . $fld,
		];

		$this->field_element( 'row', $field, $args );

		// Description.
		$this->field_option( 'description', $field );

		// Required toggle.
		$this->field_option( 'required', $field );

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

		// Size.
		$this->field_option( 'size', $field );

		// Placeholder.
		$this->field_option( 'placeholder', $field );

		// Default value.
		$this->field_option( 'default_value', $field );

		// Custom CSS classes.
		$this->field_option( 'css', $field );

		// Hide Label.
		$this->field_option( 'label_hide', $field );

		// Options close markup.
		$args = [
			'markup' => 'close',
		];

		$this->field_option( 'advanced-options', $field, $args );
	}

	/**
	 * Field preview inside the builder.
	 *
	 * @since 1.9.4
	 *
	 * @param array $field Field data.
	 */
	public function field_preview( $field ) {

		// Define data.
		$placeholder   = ! empty( $field['placeholder'] ) ? $field['placeholder'] : '';
		$default_value = ! empty( $field['default_value'] ) ? $field['default_value'] : '';
		$format        = ! empty( $field['format'] ) ? $field['format'] : 'smart';
		$size          = ! empty( $field['size'] ) ? $field['size'] : 'medium';

		// Label.
		$this->field_preview_option(
			'label',
			$field,
			[
				'label_badge' => $this->get_field_preview_badge(),
			]
		);

		// Primary input inside container for Smart format preview.
		printf(
			'<div class="wpforms-field-phone-input-container" data-format="%1$s">
				<input type="text" placeholder="%2$s" value="%3$s" class="primary-input wpforms-field-%4$s" readonly>
				<div class="wpforms-field-phone-country-container">
					<div class="wpforms-field-phone-flag"></div>
					<div class="wpforms-field-phone-arrow"></div>
				</div>
			</div>',
			esc_attr( $format ),
			esc_attr( $placeholder ),
			esc_attr( $default_value ),
			esc_attr( $size )
		);

		// Description.
		$this->field_preview_option( 'description', $field );
	}

	/**
	 * Get a preview option.
	 *
	 * @since 1.9.4
	 *
	 * @param string $option  Option name.
	 * @param array  $field   Field data.
	 * @param array  $args    Additional arguments.
	 * @param bool   $do_echo Echo or return.
	 */
	public function field_preview_option( $option, $field, $args = [], $do_echo = true ) { // phpcs:ignore Universal.NamingConventions.NoReservedKeywordParameterNames.echoFound

		// Skip preview option for the editor.
		if ( wpforms_is_editor_page() ) {
			return;
		}

		parent::field_preview_option( $option, $field, $args, $do_echo );
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
