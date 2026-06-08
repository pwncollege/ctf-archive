<?php

namespace WPForms\Forms\Fields\Rating;

use WPForms\Forms\Fields\Traits\ProField as ProFieldTrait;
use WPForms_Field;

/**
 * Rating field.
 *
 * @since 1.9.4
 */
class Field extends WPForms_Field {

	use ProFieldTrait;

	/**
	 * Default icon color.
	 *
	 * @since 1.9.4
	 */
	protected const DEFAULT_ICON_COLOR = [
		'classic' => '#e27730',
		'modern'  => '#066aab',
	];

	/**
	 * Primary class constructor.
	 *
	 * @since 1.9.4
	 */
	public function init() {

		// Define field type information.
		$this->name     = esc_html__( 'Rating', 'wpforms-lite' );
		$this->keywords = esc_html__( 'review, emoji, star', 'wpforms-lite' );
		$this->type     = 'rating';
		$this->icon     = 'fa-star';
		$this->order    = 310;
		$this->group    = 'fancy';

		$this->default_settings = [
			'icon_color' => $this->get_default_icon_color(),
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

		add_action( 'wpforms_builder_enqueues', [ $this, 'builder_enqueues' ] );
	}

	/**
	 * Builder enqueues.
	 *
	 * @since 1.9.8
	 */
	public function builder_enqueues(): void {

		$min = wpforms_get_min_suffix();

		wp_enqueue_script(
			'wpforms-rating-field',
			WPFORMS_PLUGIN_URL . "assets/js/admin/builder/fields/rating{$min}.js",
			[ 'wpforms-builder', 'wpforms-utils' ],
			WPFORMS_VERSION,
			false
		);
	}

	/**
	 * Field options panel inside the builder.
	 *
	 * @since 1.9.4
	 *
	 * @param array $field Field settings.
	 *
	 * @noinspection PackedHashtableOptimizationInspection
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

		// Scale.
		$lbl = $this->field_element(
			'label',
			$field,
			[
				'slug'    => 'scale',
				'value'   => esc_html__( 'Scale', 'wpforms-lite' ),
				'tooltip' => esc_html__( 'Select rating scale', 'wpforms-lite' ),
			],
			false
		);

		$fld = $this->field_element(
			'select',
			$field,
			[
				'slug'    => 'scale',
				'value'   => ! empty( $field['scale'] ) ? esc_attr( $field['scale'] ) : '5',
				'options' => [
					'2'  => '2',
					'3'  => '3',
					'4'  => '4',
					'5'  => '5',
					'6'  => '6',
					'7'  => '7',
					'8'  => '8',
					'9'  => '9',
					'10' => '10',
				],
			],
			false
		);

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'scale',
				'content' => $lbl . $fld,
			]
		);

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

		// Icon.
		$lbl = $this->field_element(
			'label',
			$field,
			[
				'slug'    => 'icon',
				'value'   => esc_html__( 'Icon', 'wpforms-lite' ),
				'tooltip' => esc_html__( 'Select icon to display', 'wpforms-lite' ),
			],
			false
		);

		$fld = $this->field_element(
			'select',
			$field,
			[
				'slug'    => 'icon',
				'value'   => ! empty( $field['icon'] ) ? esc_attr( $field['icon'] ) : 'star',
				'options' => [
					'star'   => esc_html__( 'Star', 'wpforms-lite' ),
					'heart'  => esc_html__( 'Heart', 'wpforms-lite' ),
					'thumb'  => esc_html__( 'Thumb', 'wpforms-lite' ),
					'smiley' => esc_html__( 'Smiley Face', 'wpforms-lite' ),
				],
			],
			false
		);

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'icon',
				'content' => $lbl . $fld,
			]
		);

		// Icon size.
		$lbl = $this->field_element(
			'label',
			$field,
			[
				'slug'    => 'icon_size',
				'value'   => esc_html__( 'Icon Size', 'wpforms-lite' ),
				'tooltip' => esc_html__( 'Select the size of the rating icon', 'wpforms-lite' ),
			],
			false
		);
		$fld = $this->field_element(
			'select',
			$field,
			[
				'slug'    => 'icon_size',
				'value'   => ! empty( $field['icon_size'] ) ? esc_attr( $field['icon_size'] ) : 'medium',
				'options' => [
					'small'  => esc_html__( 'Small', 'wpforms-lite' ),
					'medium' => esc_html__( 'Medium', 'wpforms-lite' ),
					'large'  => esc_html__( 'Large', 'wpforms-lite' ),
				],
			],
			false
		);

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'icon_size',
				'content' => $lbl . $fld,
			]
		);

		$this->score_labels( $field );

		// Icon color picker.
		$lbl = $this->field_element(
			'label',
			$field,
			[
				'slug'    => 'icon_color',
				'value'   => esc_html__( 'Icon Color', 'wpforms-lite' ),
				'tooltip' => esc_html__( 'Select the color for the rating icon', 'wpforms-lite' ),
			],
			false
		);

		$icon_color = isset( $field['icon_color'] ) ? wpforms_sanitize_hex_color( $field['icon_color'] ) : '';
		$icon_color = empty( $icon_color ) ? $this->get_default_icon_color() : $icon_color;

		$fld = $this->field_element(
			'color',
			$field,
			[
				'slug'  => 'icon_color',
				'value' => $icon_color,
				'data'  => [
					'fallback-color' => $icon_color,
				],
			],
			false
		);

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'icon_color',
				'content' => $lbl . $fld,
				'class'   => 'color-picker-row',
			]
		);

		// Custom CSS classes.
		$this->field_option( 'css', $field );

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
	 * Score labels.
	 *
	 * @since 1.9.8
	 *
	 * @param array $field Field settings.
	 */
	private function score_labels( array $field ): void {

		// Lowest score label.
		$lowest_label = $this->field_element(
			'label',
			$field,
			[
				'slug'    => 'lowest_label',
				'value'   => esc_html__( 'Lowest Score Label', 'wpforms-lite' ),
				'tooltip' => esc_html__( 'This label indicates the lowest score on the scale.', 'wpforms-lite' ),
			],
			false
		);

		$lowest_field = $this->field_element(
			'text',
			$field,
			[
				'slug'  => 'lowest_label',
				'value' => $field['lowest_label'] ?? '',
			],
			false
		);

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'lowest_label',
				'content' => $lowest_label . $lowest_field,
			]
		);

		// Highest score label.
		$highest_label = $this->field_element(
			'label',
			$field,
			[
				'slug'    => 'highest_label',
				'value'   => esc_html__( 'Highest Score Label', 'wpforms-lite' ),
				'tooltip' => esc_html__( 'This label indicates the highest score on the scale.', 'wpforms-lite' ),
			],
			false
		);

		$highest_field = $this->field_element(
			'text',
			$field,
			[
				'slug'  => 'highest_label',
				'value' => $field['highest_label'] ?? '',
			],
			false
		);

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'highest_label',
				'content' => $highest_label . $highest_field,
			]
		);

		// Label position.
		$label_position = $this->field_element(
			'label',
			$field,
			[
				'slug'    => 'label_position',
				'value'   => esc_html__( 'Label Position', 'wpforms-lite' ),
				'tooltip' => esc_html__( 'Select the position of the label', 'wpforms-lite' ),
			],
			false
		);

		$select_position = $this->field_element(
			'select',
			$field,
			[
				'slug'    => 'label_position',
				'value'   => ! empty( $field['label_position'] ) ? esc_attr( $field['label_position'] ) : 'below',
				'options' => [
					'above' => esc_html__( 'Above', 'wpforms-lite' ),
					'below' => esc_html__( 'Below', 'wpforms-lite' ),
				],
			],
			false
		);

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'label_position',
				'content' => $label_position . $select_position,
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
	public function field_preview( $field ): void {

		// Label.
		$this->field_preview_option(
			'label',
			$field,
			[
				'label_badge' => $this->get_field_preview_badge(),
			]
		);

		echo '<div class="wpforms-rating-field">';

		$this->get_field_preview_icons( $field );

		$this->get_field_preview_labels( $field );

		echo '</div>';

		// Description.
		$this->field_preview_option( 'description', $field );
	}

	/**
	 * Get field preview icons.
	 *
	 * @since 1.9.8
	 *
	 * @param array $field Field settings.
	 */
	private function get_field_preview_icons( array $field ): void {

		// Define data.
		$scale      = ! empty( $field['scale'] ) ? esc_attr( $field['scale'] ) : 5;
		$icon       = ! empty( $field['icon'] ) ? esc_attr( $field['icon'] ) : 'star';
		$icon_size  = ! empty( $field['icon_size'] ) ? esc_attr( $field['icon_size'] ) : 'medium';
		$icon_color = ! empty( $field['icon_color'] ) ? esc_attr( $field['icon_color'] ) : $this->get_default_icon_color();
		$icon_class = $this->get_preview_icon_class( $icon );

		// Set icon size.
		$icon_size_css = $this->get_icon_size_css( $icon_size );

		echo '<div class="wpforms-rating-field-icons">';

		// Primary input.
		for ( $i = 1; $i <= 10; $i++ ) {
			printf(
				'<i class="fa %s %s rating-icon" aria-hidden="true" style="color:%s; display:%s; font-size:%dpx;"></i>',
				esc_attr( $icon_class ),
				esc_attr( $icon_size ),
				esc_attr( $icon_color ),
				$i <= $scale ? 'inline-block' : 'none',
				esc_attr( $icon_size_css )
			);
		}

		echo '</div>';
	}

	/**
	 * Get preview icon class based on the selected icon.
	 *
	 * @since 1.9.8
	 *
	 * @param string $icon Selected icon.
	 *
	 * @return string Icon class.
	 */
	private function get_preview_icon_class( string $icon ): string {

		$icon_class = '';

		// Set icon class.
		switch ( $icon ) {
			case 'star':
				$icon_class = 'fa-star';
				break;

			case 'heart':
				$icon_class = 'fa-heart';
				break;

			case 'thumb':
				$icon_class = 'fa-thumbs-up';
				break;

			case 'smiley':
				$icon_class = 'fa-smile-o';
				break;
		}

		return $icon_class;
	}

	/**
	 * Get field preview labels.
	 *
	 * @since 1.9.8
	 *
	 * @param array $field Field settings.
	 */
	private function get_field_preview_labels( array $field ): void {

		// Lowest score label.
		$lowest_label = ! empty( $field['lowest_label'] ) ? esc_html( $field['lowest_label'] ) : '';

		// Highest score label.
		$highest_label = ! empty( $field['highest_label'] ) ? esc_html( $field['highest_label'] ) : '';

		$class = [ 'wpforms-rating-field-labels' ];

		if ( ! empty( $field['label_position'] ) && $field['label_position'] === 'above' ) {
			$class[] = 'wpforms-rating-field-labels-position-above';
		}

		if ( empty( $lowest_label ) && empty( $highest_label ) ) {
			$class[] = 'wpforms-hidden';
		}

		echo '<div class=" ' . wpforms_sanitize_classes( $class, true ) . ' ">';

		printf(
			'<span class="wpforms-rating-field-lowest-label wpforms-sub-label">%s</span>',
			esc_html( $lowest_label )
		);

		printf(
			'<span class="wpforms-rating-field-highest-label wpforms-sub-label">%s</span>',
			esc_html( $highest_label )
		);

		echo '</div>';
	}

	/**
	 * Field display on the form front-end.
	 *
	 * @since 1.9.4
	 *
	 * @param array $field      Field settings.
	 * @param array $deprecated Deprecated, don't use.
	 * @param array $form_data  Form data and settings.
	 */
	public function field_display( $field, $deprecated, $form_data ) {
	}

	/**
	 * Get icon size CSS value in pixels.
	 *
	 * @since 1.9.4
	 *
	 * @param string $icon_size Icon size value.
	 */
	protected function get_icon_size_css( $icon_size ): string {

		$render_engine = wpforms_get_render_engine();

		$icon_sizes = [
			'classic' => [
				'small'  => '18',
				'medium' => '28',
				'large'  => '38',
			],
			'modern'  => [
				'small'  => '16',
				'medium' => '24',
				'large'  => '38',
			],
		];

		$default = $render_engine === 'modern' ? '24' : '28';

		return ! empty( $icon_sizes[ $render_engine ][ $icon_size ] )
			? $icon_sizes[ $render_engine ][ $icon_size ]
			: $default;
	}

	/**
	 * Get default icon color.
	 *
	 * @since 1.9.4
	 *
	 * @return string
	 */
	public function get_default_icon_color(): string {

		$render_engine = wpforms_get_render_engine();

		return array_key_exists( $render_engine, self::DEFAULT_ICON_COLOR ) ? self::DEFAULT_ICON_COLOR[ $render_engine ] : self::DEFAULT_ICON_COLOR['modern'];
	}
}
