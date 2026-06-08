<?php

namespace WPForms\Forms\Fields\Camera;

use WPForms_Field;
use WPForms\Forms\Fields\Traits\CameraTrait;
use WPForms\Forms\Fields\Traits\ProField as ProFieldTrait;
use WPForms\Forms\Fields\Traits\AccessRestrictionsTrait;

/**
 * Camera field.
 *
 * @since 1.9.8
 */
class Field extends WPForms_Field {

	use ProFieldTrait;
	use CameraTrait;
	use AccessRestrictionsTrait;

	protected const STYLE_BUTTON = 'button';
	protected const STYLE_LINK   = 'link';
	public const STYLE_CLASSIC   = 'classic';
	public const STYLE_MODERN    = 'modern';

	/**
	 * Primary class constructor.
	 *
	 * @since 1.9.8
	 */
	public function init() {

		// Define field type information.
		$this->name     = esc_html__( 'Camera', 'wpforms-lite' );
		$this->keywords = esc_html__( 'photo, image, capture, webcam', 'wpforms-lite' );
		$this->type     = 'camera';
		$this->icon     = 'fa-camera';
		$this->order    = 105;
		$this->group    = 'fancy';

		$this->default_settings = [
			'style' => 'button',
		];

		$this->init_pro_field();

		$this->hooks();
	}

	/**
	 * Add hooks.
	 *
	 * @since 1.9.8
	 */
	private function hooks(): void {

		add_action( 'wpforms_builder_enqueues', [ $this, 'builder_enqueues' ] );
	}

	/**
	 * Enqueue script for the admin form builder.
	 *
	 * @since 1.9.8
	 */
	public function builder_enqueues(): void {

		$min = wpforms_get_min_suffix();

		if ( ! wpforms_is_pro() ) {
			return;
		}

		wp_enqueue_script(
			'wpforms-builder-file-upload-field',
			WPFORMS_PLUGIN_URL . "assets/pro/js/admin/builder/fields/file-upload{$min}.js",
			[ 'jquery', 'wpforms-builder' ],
			WPFORMS_VERSION,
			false
		);

		wp_enqueue_script(
			'wpforms-builder-camera',
			WPFORMS_PLUGIN_URL . "assets/pro/js/admin/builder/fields/camera{$min}.js",
			[ 'jquery', 'wpforms-builder' ],
			WPFORMS_VERSION,
			false
		);

		// Localize strings for the camera field.
		wp_localize_script(
			'wpforms-builder-camera',
			'wpforms_camera_builder',
			[
				'button_link_text_label'   => esc_html__( 'Button Link Text', 'wpforms-lite' ),
				'link_text_label'          => esc_html__( 'Link Text', 'wpforms-lite' ),
				'button_link_text_tooltip' => esc_html__( 'Enter the text for the button link.', 'wpforms-lite' ),
				'link_text_tooltip'        => esc_html__( 'Enter the text for the link.', 'wpforms-lite' ),
				'error_message'            => esc_html__( 'Camera field with Link style cannot have empty Link Text. Please enter text or change style to Button.', 'wpforms-lite' ),
				'error_title'              => esc_html__( 'Missing Link Text', 'wpforms-lite' ),
				'error_ok'                 => esc_html__( 'OK', 'wpforms-lite' ),
			]
		);
	}

	/**
	 * Field options panel inside the builder.
	 *
	 * @since 1.9.8
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

		// Camera options.
		$this->add_camera_enabled_toggle( $field );
		$this->add_camera_format_options( $field );
		$this->add_camera_aspect_ratio_options( $field );
		$this->add_camera_custom_ratio_options( $field );
		$this->add_camera_time_limit_options( $field );

		// Max file size.
		$this->add_max_file_size_options( $field );

		// Required toggle.
		$this->field_option( 'required', $field );

		// Options close markup.
		$this->field_option( 'basic-options', $field, [ 'markup' => 'close' ] );

		// Advanced field options.
		$this->field_option( 'advanced-options', $field, [ 'markup' => 'open' ] );

		// Style (Button or Link).
		$this->add_style_options( $field );

		// Button link text.
		$this->add_button_link_text_options( $field );

		// Custom CSS classes.
		$this->field_option( 'css', $field );

		// Media Library toggle.
		$fld = $this->field_element(
			'toggle',
			$field,
			[
				'slug'    => 'media_library',
				'value'   => ! empty( $field['media_library'] ) ? 1 : '',
				'desc'    => esc_html__( 'Store Files in WordPress Media Library', 'wpforms-lite' ),
				'tooltip' => esc_html__( 'Check this option to store the final uploaded file in the WordPress Media Library', 'wpforms-lite' ),
				'class'   => 'wpforms-camera-media-library',
			],
			false
		);

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'media_library',
				'content' => $fld,
			]
		);

		// Access Restrictions.
		$this->access_restrictions_options( $field );

		// Hide label.
		$this->field_option( 'label_hide', $field );

		// Options close markup.
		$this->field_option( 'advanced-options', $field, [ 'markup' => 'close' ] );
	}

	/**
	 * Field preview inside the builder.
	 *
	 * @since 1.9.8
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

		$style = ! empty( $field['style'] ) ? $field['style'] : self::STYLE_BUTTON;

		$field_id = absint( $field['id'] );
		$text     = $field['button_link_text'] ?? esc_html__( 'Capture With Your Camera', 'wpforms-lite' );

		// Always render both button and link, but hide/show based on the selected style.
		$button_class = $style === self::STYLE_BUTTON ? 'wpforms-camera-button wpforms-btn-secondary' : 'wpforms-camera-button wpforms-btn-secondary wpforms-hidden';
		$link_class   = $style === self::STYLE_LINK ? 'wpforms-camera-link' : 'wpforms-camera-link wpforms-hidden';

		printf(
			'<button type="button" class="%s" id="%d">%s %s</button>',
			esc_attr( $button_class ),
			(int) $field_id,
			$this->get_camera_icon_svg(), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			esc_html( $text )
		);

		printf(
			'<a href="#" class="%s" data-field-id="%d">%s</a>',
			esc_attr( $link_class ),
			(int) $field_id,
			esc_html( $text )
		);

		// Description.
		$this->field_preview_option( 'description', $field );
	}

	/**
	 * Field display on the form front-end.
	 *
	 * @since 1.9.8
	 *
	 * @param array $field      Field data and settings.
	 * @param array $deprecated Deprecated field attributes. Use field properties.
	 * @param array $form_data  Form data and settings.
	 */
	public function field_display( $field, $deprecated, $form_data ) {
		// Implemented in Pro only.
	}

	/**
	 * Add max file size options.
	 *
	 * @since 1.9.8
	 *
	 * @param array $field Field data.
	 */
	private function add_max_file_size_options( array $field ): void {

		$lbl = $this->field_element(
			'label',
			$field,
			[
				'slug'    => 'max_size',
				'value'   => esc_html__( 'Max File Size', 'wpforms-lite' ),
				'tooltip' => sprintf( /* translators: %s - max upload size. */
					esc_html__( 'Enter the max size of each file, in megabytes, to allow. If left blank, the value defaults to the maximum size the server allows which is %s.', 'wpforms-lite' ),
					wpforms_max_upload()
				),
			],
			false
		);
		$fld = $this->field_element(
			'text',
			$field,
			[
				'slug'  => 'max_size',
				'type'  => 'number',
				'attrs' => [
					'min'     => 1,
					'max'     => 512,
					'step'    => 1,
					'pattern' => '[0-9]',
				],
				'value' => ! empty( $field['max_size'] ) ? abs( $field['max_size'] ) : '',
			],
			false
		);

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'max_size',
				'content' => $lbl . $fld,
			]
		);
	}

	/**
	 * Add style options, Button or Link.
	 *
	 * @since 1.9.8
	 *
	 * @param array $field Field data.
	 */
	private function add_style_options( array $field ): void {

		// Style (Button or Link).
		$lbl = $this->field_element(
			'label',
			$field,
			[
				'slug'    => 'style',
				'value'   => esc_html__( 'Style', 'wpforms-lite' ),
				'tooltip' => esc_html__( 'Choose the style of the camera button.', 'wpforms-lite' ),
			],
			false
		);

		$fld = $this->field_element(
			'select',
			$field,
			[
				'slug'    => 'style',
				'value'   => ! empty( $field['style'] ) ? $field['style'] : self::STYLE_BUTTON,
				'options' => [
					self::STYLE_BUTTON => esc_html__( 'Button', 'wpforms-lite' ),
					self::STYLE_LINK   => esc_html__( 'Link', 'wpforms-lite' ),
				],
			],
			false
		);

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'style',
				'content' => $lbl . $fld,
				'class'   => 'wpforms-camera-style',
			]
		);
	}

	/**
	 * Add button link text options.
	 *
	 * @since 1.9.8
	 *
	 * @param array $field Field data.
	 */
	private function add_button_link_text_options( array $field ): void {

		$style = ! empty( $field['style'] ) ? $field['style'] : self::STYLE_BUTTON;

		// Button link text.
		$lbl = $this->field_element(
			'label',
			$field,
			[
				'slug'    => 'button_link_text',
				'value'   => $style === self::STYLE_BUTTON ? esc_html__( 'Button Link Text', 'wpforms-lite' ) : esc_html__( 'Link Text', 'wpforms-lite' ),
				'tooltip' => $style === self::STYLE_BUTTON ? esc_html__( 'Enter the text for the button link.', 'wpforms-lite' ) : esc_html__( 'Enter the text for the link.', 'wpforms-lite' ),
			],
			false
		);

		$fld = $this->field_element(
			'text',
			$field,
			[
				'slug'  => 'button_link_text',
				'value' => $field['button_link_text'] ?? esc_html__( 'Capture With Your Camera', 'wpforms-lite' ),
			],
			false
		);

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'button_link_text',
				'content' => $lbl . $fld,
			]
		);
	}

	/**
	 * Get camera icon SVG.
	 *
	 * @since 1.9.8
	 *
	 * @return string Camera icon SVG code.
	 * @noinspection HtmlDeprecatedAttribute
	 */
	protected function get_camera_icon_svg(): string {

		return '<svg width="16" height="14" viewBox="0 0 16 14" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4.65625 1.03125C4.875 0.40625 5.4375 0 6.09375 0H9.90625C10.5625 0 11.125 0.40625 11.3438 1.03125L11.6562 2H14C15.0938 2 16 2.90625 16 4V12C16 13.0938 15.0938 14 14 14H2C0.90625 14 0 13.0938 0 12V4C0 2.90625 0.90625 2 2 2H4.34375L4.65625 1.03125ZM8 5C6.34375 5 5 6.34375 5 8C5 9.65625 6.34375 11 8 11C9.65625 11 11 9.65625 11 8C11 6.34375 9.65625 5 8 5Z"/></svg>';
	}

	/**
	 * Get remove selected file icon SVG.
	 *
	 * @since 1.9.8
	 *
	 * @return string Remove icon SVG code.
	 * @noinspection HtmlDeprecatedAttribute
	 */
	protected function get_camera_remove_file_icon(): string {

		return '<svg width="13" height="15" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4.121.914a.853.853 0 0 1 .82-.602H8.06c.382 0 .71.247.82.602l.246.711h2.625c.492 0 .875.383.875.875a.864.864 0 0 1-.875.875H1.25A.864.864 0 0 1 .375 2.5c0-.492.383-.875.875-.875h2.625l.246-.71Zm7.629 3.774-.574 8.832c-.055.683-.63 1.23-1.313 1.23H3.137c-.684 0-1.258-.547-1.313-1.23L1.25 4.688h10.5Z"/></svg>';
	}

	/**
	 * Check if the field is modern upload style.
	 *
	 * @since 1.9.8
	 *
	 * @param array $field_data Field data.
	 *
	 * @return bool
	 */
	public static function is_modern_upload( $field_data ): bool {

		return isset( $field_data['style'] ) && $field_data['style'] === self::STYLE_MODERN;
	}

	/**
	 * Format field value for display in Entries.
	 *
	 * @since 1.9.8
	 *
	 * @param int   $field_id     Field ID.
	 * @param mixed $field_submit Field value that was submitted.
	 * @param array $form_data    Form data and settings.
	 */
	public function format( $field_id, $field_submit, $form_data ) {

		$field_id    = absint( $field_id );
		$field_label = ! empty( $form_data['fields'][ $field_id ]['label'] ) ? sanitize_text_field( $form_data['fields'][ $field_id ]['label'] ) : '';
		$style       = ! empty( $form_data['fields'][ $field_id ]['style'] ) && $form_data['fields'][ $field_id ]['style'] === self::STYLE_MODERN
			? self::STYLE_MODERN
			: self::STYLE_CLASSIC;

		if ( $style === self::STYLE_CLASSIC ) {
			wpforms()->obj( 'process' )->fields[ $field_id ] = [
				'name'          => $field_label,
				'value'         => '',
				'file'          => '',
				'file_original' => '',
				'ext'           => '',
				'id'            => $field_id,
				'type'          => $this->type,
			];

			return;
		}

		wpforms()->obj( 'process' )->fields[ $field_id ] = [
			'name'      => $field_label,
			'value'     => '',
			'value_raw' => '',
			'id'        => $field_id,
			'type'      => $this->type,
			'style'     => self::STYLE_MODERN,
		];
	}
}
