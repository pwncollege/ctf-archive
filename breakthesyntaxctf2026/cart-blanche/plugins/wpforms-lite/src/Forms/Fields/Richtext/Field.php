<?php

namespace WPForms\Forms\Fields\Richtext;

use WPForms\Forms\Fields\Traits\ProField as ProFieldTrait;
use WPForms_Field;

/**
 * Rich Text field.
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
		$this->name     = esc_html__( 'Rich Text', 'wpforms-lite' );
		$this->keywords = esc_html__( 'image, text, table, list, heading, wysiwyg, visual', 'wpforms-lite' );
		$this->type     = 'richtext';
		$this->icon     = 'fa-pencil-square-o';
		$this->order    = 170;
		$this->group    = 'fancy';

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
	 * @param array $field Field data and settings.
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

		$this->field_option( 'label', $field );
		$this->field_option( 'description', $field );

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'media_enabled',
				'content' => $this->field_element(
					'toggle',
					$field,
					[
						'slug'    => 'media_enabled',
						'value'   => isset( $field['media_enabled'] ) ? '1' : '0',
						'desc'    => esc_html__( 'Allow Media Uploads', 'wpforms-lite' ),
						'tooltip' => esc_html__( 'Check this option to allow uploading and embedding files.', 'wpforms-lite' ),
					],
					false
				),
			]
		);

		$media_library = $this->field_element(
			'toggle',
			$field,
			[
				'slug'    => 'media_library',
				'value'   => isset( $field['media_library'] ) ? '1' : '0',
				'desc'    => esc_html__( 'Store files in WordPress Media Library', 'wpforms-lite' ),
				'tooltip' => esc_html__( 'Check this option to store files in the WordPress Media Library.', 'wpforms-lite' ),
			],
			false
		);

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'media_controls',
				'class'   => ! isset( $field['media_enabled'] ) ? 'wpforms-hide' : '',
				'content' => $media_library,
			]
		);

		$this->field_option( 'required', $field );
		$this->field_option( 'basic-options', $field, [ 'markup' => 'close' ] );

		$this->field_option( 'advanced-options', $field, [ 'markup' => 'open' ] );

		$output_style = $this->field_element(
			'label',
			$field,
			[
				'slug'  => 'style',
				'value' => esc_html__( 'Field Style', 'wpforms-lite' ),
			],
			false
		);

		$output_style .= $this->field_element(
			'select',
			$field,
			[
				'slug'    => 'style',
				'value'   => ! empty( $field['style'] ) ? esc_attr( $field['style'] ) : 'full',
				'options' => [
					'full'  => esc_html__( 'Full', 'wpforms-lite' ),
					'basic' => esc_html__( 'Basic', 'wpforms-lite' ),
				],
			],
			false
		);

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'style',
				'content' => $output_style,
			]
		);

		$this->field_option( 'size', $field );
		$this->field_option( 'css', $field );
		$this->field_option( 'label_hide', $field );
		$this->field_option( 'advanced-options', $field, [ 'markup' => 'close' ] );
	}

	/**
	 * The field preview inside the builder.
	 *
	 * @since 1.9.4
	 *
	 * @param array $field Field data and settings.
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

		$style         = ! empty( $field['style'] ) && $field['style'] === 'basic' ? 'wpforms-field-richtext-toolbar-basic' : '';
		$media_enabled = ! empty( $field['media_enabled'] ) ? 'wpforms-field-richtext-media-enabled' : '';
		?>

		<div class="wpforms-richtext-wrap tmce-active">
			<div class="wp-editor-tabs">
				<button type="button" class="wp-switch-editor switch-tmce"><?php esc_html_e( 'Visual', 'wpforms-lite' ); ?></button>
				<button type="button" class="wp-switch-editor"><?php esc_html_e( 'Text', 'wpforms-lite' ); ?></button>
			</div>
			<div class="wp-editor-container ">
				<div class="mce-container-body">
					<div class="mce-toolbar-grp <?php echo esc_attr( $style ); ?> <?php echo esc_attr( $media_enabled ); ?>"></div>
				</div>
				<textarea id="wpforms-richtext-<?php echo wpforms_validate_field_id( $field['id'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>"></textarea>
				<div class="mce-statusbar">
					<i class="mce-ico mce-i-resize"></i>
				</div>
			</div>
		</div>

		<?php
		$this->field_preview_option( 'description', $field );
	}

	/**
	 * The field display on the form front-end.
	 *
	 * @since 1.9.4
	 *
	 * @param array $field      Field data and settings.
	 * @param array $deprecated Field attributes.
	 * @param array $form_data  Form data and settings.
	 */
	public function field_display( $field, $deprecated, $form_data ) {
	}
}
