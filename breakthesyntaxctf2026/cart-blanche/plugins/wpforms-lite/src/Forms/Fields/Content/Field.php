<?php

namespace WPForms\Forms\Fields\Content;

use WPForms\Forms\Fields\Traits\ProField as ProFieldTrait;
use WPForms\Forms\Fields\Traits\ContentInput;
use WPForms_Field;

/**
 * The Content Field Class.
 *
 * @since 1.9.4
 */
class Field extends WPForms_Field {

	use ProFieldTrait;
	use ContentInput;

	/**
	 * Class initialization method.
	 *
	 * @since 1.9.4
	 */
	public function init() {

		// Define field type information.
		$this->name            = esc_html__( 'Content', 'wpforms-lite' );
		$this->keywords        = esc_html__( 'image, text, table, list, heading, wysiwyg, visual', 'wpforms-lite' );
		$this->type            = 'content';
		$this->icon            = 'fa-file-image-o';
		$this->order           = 180;
		$this->group           = 'fancy';
		$this->allow_read_only = false;

		$this->default_settings = [
			'label_disable' => '1',
		];

		$this->init_pro_field();
		$this->hooks();
	}

	/**
	 * Register WP hooks.
	 *
	 * @since 1.9.4
	 */
	protected function hooks() {
	}

	/**
	 * Show field options in the builder left panel.
	 *
	 * @since 1.9.4
	 *
	 * @param array $field Field data.
	 */
	public function field_options( $field ) {

		// Options open markup.
		$this->field_option(
			'basic-options',
			$field,
			[
				'markup'      => 'open',
				'after_title' => $this->get_field_options_notice(),
			]
		);

		$this->field_option_content( $field );

		// Set label to the disabled.
		$args = [
			'type'  => 'hidden',
			'slug'  => 'label_disable',
			'value' => '1',
		];

		$this->field_element( 'text', $field, $args );

		// Options close markup.
		$this->field_option( 'basic-options', $field, [ 'markup' => 'close' ] );

		// Options open markup.
		$this->field_option( 'advanced-options', $field, [ 'markup' => 'open' ] );

		// Size.
		$this->field_option( 'size', $field );

		// Custom CSS classes.
		$this->field_option( 'css', $field );

		// Options close markup.
		$this->field_option( 'advanced-options', $field, [ 'markup' => 'close' ] );
	}

	/**
	 * Show the field preview in the builder right panel.
	 *
	 * @since 1.9.4
	 *
	 * @param array $field Field data.
	 */
	public function field_preview( $field ) {

		if ( ! empty( $this->is_disabled_field ) ) {
			// Label.
			$field['label'] = empty( $field['label'] ) ? esc_html__( 'Content', 'wpforms-lite' ) : $field['label'];

			$this->field_preview_option(
				'label',
				$field,
				[
					'label_badge' => $this->get_field_preview_badge(),
				]
			);
		}

		$this->content_input_preview( $field );
	}

	/**
	 * Field display on the form front-end.
	 *
	 * @since 1.9.4
	 *
	 * @param array $field      Field data and settings.
	 * @param array $deprecated Deprecated field attributes. Use field properties instead.
	 * @param array $form_data  Form data and settings.
	 */
	public function field_display( $field, $deprecated, $form_data ) {
	}
}
