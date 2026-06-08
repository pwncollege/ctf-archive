<?php

namespace WPForms\Forms\Fields\Traits;

trait CameraTrait {

	/**
	 * Add camera options to the field.
	 *
	 * @since 1.9.8
	 *
	 * @param array $field Field data and settings.
	 */
	public function camera_options( array $field ): void {

		$this->add_camera_enabled_toggle( $field );
		$this->add_camera_format_options( $field );
		$this->add_camera_aspect_ratio_options( $field );
		$this->add_camera_custom_ratio_options( $field );
		$this->add_camera_time_limit_options( $field );
	}

	/**
	 * Add camera-enabled toggle.
	 *
	 * @since 1.9.8
	 *
	 * @param array $field Field data and settings.
	 */
	private function add_camera_enabled_toggle( array $field ): void {

		// Check if this is a Camera field (not FileUpload with camera options).
		$is_camera_field = $this->type === 'camera';

		$camera_enabled = $this->field_element(
			'toggle',
			$field,
			[
				'slug'    => 'camera_enabled',
				'value'   => $this->is_camera_enabled_for_field( $field ) ? 1 : '',
				'desc'    => esc_html__( 'Enable Camera', 'wpforms-lite' ),
				'tooltip' => esc_html__( 'Check this option to enable the camera field.', 'wpforms-lite' ),
				'class'   => 'wpforms-file-upload-camera-enabled-toggle',
			],
			false
		);

		// Hide the toggle for the Camera field, show for FileUpload field.
		$row_class = [ 'wpforms-file-upload-camera-enabled-row' ];

		if ( $is_camera_field ) {
			$row_class[] = 'wpforms-hidden';
		}

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'camera',
				'content' => $camera_enabled,
				'class'   => $row_class,
			]
		);
	}

	/**
	 * Add camera format options.
	 *
	 * @since 1.9.8
	 *
	 * @param array $field Field data and settings.
	 */
	private function add_camera_format_options( array $field ): void {

		$format_label = $this->field_element(
			'label',
			$field,
			[
				'slug'    => 'camera_format',
				'value'   => esc_html__( 'Format', 'wpforms-lite' ),
				'tooltip' => esc_html__( 'Select the camera format.', 'wpforms-lite' ),
				'class'   => 'wpforms-file-upload-camera-format-label',
			],
			false
		);

		$format_select = $this->field_element(
			'select',
			$field,
			[
				'slug'    => 'camera_format',
				'value'   => ! empty( $field['camera_format'] ) ? $field['camera_format'] : 'photo',
				'options' => [
					'photo' => esc_html__( 'Photo', 'wpforms-lite' ),
					'video' => esc_html__( 'Video', 'wpforms-lite' ),
				],
				'class'   => 'wpforms-file-upload-camera-format-select',
			],
			false
		);

		// Check if the camera is enabled to determine visibility.
		$hidden_class = $this->is_camera_enabled_for_field( $field ) ? [] : [ 'wpforms-hidden' ];

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'camera_format',
				'content' => $format_label . $format_select,
				'class'   => array_merge( [ 'wpforms-file-upload-camera-format' ], $hidden_class ),
			]
		);
	}

	/**
	 * Add camera aspect ratio options.
	 *
	 * @since 1.9.8
	 *
	 * @param array $field Field data and settings.
	 */
	private function add_camera_aspect_ratio_options( array $field ): void {

		$aspect_ratio_label = $this->field_element(
			'label',
			$field,
			[
				'slug'    => 'camera_aspect_ratio',
				'value'   => esc_html__( 'Aspect Ratio', 'wpforms-lite' ),
				'tooltip' => esc_html__( 'Select the camera aspect ratio.', 'wpforms-lite' ),
				'class'   => 'wpforms-file-upload-camera-aspect-ratio-label',
			],
			false
		);

		// Build aspect ratio options - always include freeform.
		$aspect_ratio_options = [
			'original'  => esc_html__( 'Original', 'wpforms-lite' ),
			'custom'    => esc_html__( 'Custom', 'wpforms-lite' ),
			'freeform'  => esc_html__( 'Freeform', 'wpforms-lite' ),
			'landscape' => [
				'optgroup' => esc_html__( 'Landscape orientation', 'wpforms-lite' ),
				'16:9'     => esc_html__( '16:9', 'wpforms-lite' ),
				'5:4'      => esc_html__( '5:4', 'wpforms-lite' ),
				'3:2'      => esc_html__( '3:2', 'wpforms-lite' ),
			],
			'portrait'  => [
				'optgroup' => esc_html__( 'Portrait orientation', 'wpforms-lite' ),
				'9:16'     => esc_html__( '9:16', 'wpforms-lite' ),
				'4:5'      => esc_html__( '4:5', 'wpforms-lite' ),
				'2:3'      => esc_html__( '2:3', 'wpforms-lite' ),
			],
		];

		// Add class to hide freeform if a format is not a photo.
		$camera_format      = ! empty( $field['camera_format'] ) ? $field['camera_format'] : 'photo';
		$aspect_ratio_class = [ 'wpforms-file-upload-camera-aspect-ratio-select' ];

		if ( $camera_format !== 'photo' ) {
			$aspect_ratio_class[] = 'wpforms-file-upload-camera-aspect-ratio-no-freeform';
		}

		$aspect_ratio_select = $this->field_element(
			'select',
			$field,
			[
				'slug'    => 'camera_aspect_ratio',
				'value'   => ! empty( $field['camera_aspect_ratio'] ) ? $field['camera_aspect_ratio'] : 'original',
				'options' => $aspect_ratio_options,
				'class'   => $aspect_ratio_class,
			],
			false
		);

		// Check if the camera is enabled to determine visibility.
		$hidden_class = $this->is_camera_enabled_for_field( $field ) ? [] : [ 'wpforms-hidden' ];

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'camera_aspect_ratio',
				'content' => $aspect_ratio_label . $aspect_ratio_select,
				'class'   => array_merge( [ 'wpforms-file-upload-camera-aspect-ratio' ], $hidden_class ),
			]
		);
	}

	/**
	 * Add camera custom ratio options.
	 *
	 * @since 1.9.8
	 *
	 * @param array $field Field data and settings.
	 */
	private function add_camera_custom_ratio_options( array $field ): void {

		// Check if an aspect ratio is custom to determine visibility.
		$camera_aspect_ratio       = ! empty( $field['camera_aspect_ratio'] ) ? $field['camera_aspect_ratio'] : 'original';
		$custom_ratio_hidden_class = ( $this->is_camera_enabled_for_field( $field ) && $camera_aspect_ratio === 'custom' ) ? [] : [ 'wpforms-hidden' ];

		// Ratio Width field.
		$ratio_width_field = '<div class="wpforms-file-upload-camera-ratio-width">' . $this->field_element(
			'text',
			$field,
			[
				'slug'  => 'camera_ratio_width',
				'type'  => 'number',
				'value' => ! empty( $field['camera_ratio_width'] ) && $field['camera_ratio_width'] >= 1 ? $field['camera_ratio_width'] : '4',
				'attrs' => [
					'min'  => 1,
					'step' => 1,
				],
				'after' => esc_html__( 'Ratio Width', 'wpforms-lite' ),
				'class' => 'wpforms-file-upload-camera-ratio-width-input',
			],
			false
		) . '</div>';

		// Ratio Height field.
		$ratio_height_field = '<div class="wpforms-file-upload-camera-ratio-height">' . $this->field_element(
			'text',
			$field,
			[
				'slug'  => 'camera_ratio_height',
				'type'  => 'number',
				'value' => ! empty( $field['camera_ratio_height'] ) && $field['camera_ratio_height'] >= 1 ? $field['camera_ratio_height'] : '3',
				'attrs' => [
					'min'  => 1,
					'step' => 1,
				],
				'after' => esc_html__( 'Ratio Height', 'wpforms-lite' ),
				'class' => 'wpforms-file-upload-camera-ratio-height-input',
			],
			false
		) . '</div>';

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'camera_custom_ratio',
				'content' => '<div class="wpforms-field-option-row-columns wpforms-field-option-row-columns-2 wpforms-file-upload-camera-ratio-columns">' . $ratio_width_field . $ratio_height_field . '</div>',
				'class'   => array_merge( [ 'wpforms-file-upload-camera-custom-ratio' ], $custom_ratio_hidden_class ),
			]
		);
	}

	/**
	 * Add camera time limit options.
	 *
	 * @since 1.9.8
	 *
	 * @param array $field Field data and settings.
	 */
	private function add_camera_time_limit_options( array $field ): void {

		$time_limit_label = $this->field_element(
			'label',
			$field,
			[
				'slug'    => 'camera_time_limit',
				'value'   => esc_html__( 'Time Limit', 'wpforms-lite' ),
				'tooltip' => esc_html__( 'Set the time limit for camera recording.', 'wpforms-lite' ),
				'class'   => 'wpforms-file-upload-camera-time-limit-label',
			],
			false
		);

		// Minutes field.
		$minutes_field = '<div class="wpforms-file-upload-camera-time-limit-minutes">' . $this->field_element(
			'text',
			$field,
			[
				'slug'  => 'camera_time_limit_minutes',
				'type'  => 'number',
				'value' => ! empty( $field['camera_time_limit_minutes'] ) && $field['camera_time_limit_minutes'] >= 0 ? $field['camera_time_limit_minutes'] : '1',
				'attrs' => [
					'min'  => 0,
					'step' => 1,
				],
				'after' => esc_html__( 'Minutes', 'wpforms-lite' ),
				'class' => 'wpforms-file-upload-camera-time-limit-minutes-input',
			],
			false
		) . '</div>';

		// Seconds field.
		$seconds_field = '<div class="wpforms-file-upload-camera-time-limit-seconds">' . $this->field_element(
			'text',
			$field,
			[
				'slug'  => 'camera_time_limit_seconds',
				'type'  => 'number',
				'value' => ! empty( $field['camera_time_limit_seconds'] ) && $field['camera_time_limit_seconds'] >= 0 && $field['camera_time_limit_seconds'] <= 59 ? $field['camera_time_limit_seconds'] : '30',
				'attrs' => [
					'min'  => 0,
					'max'  => 59,
					'step' => 1,
				],
				'after' => esc_html__( 'Seconds', 'wpforms-lite' ),
				'class' => 'wpforms-file-upload-camera-time-limit-seconds-input',
			],
			false
		) . '</div>';

		// Check if a format is video to determine time limit visibility.
		$camera_format           = ! empty( $field['camera_format'] ) ? $field['camera_format'] : 'photo';
		$time_limit_hidden_class = ( $this->is_camera_enabled_for_field( $field ) && $camera_format === 'video' ) ? [] : [ 'wpforms-hidden' ];

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'camera_time_limit',
				'content' => $time_limit_label . '<div class="wpforms-field-option-row-columns wpforms-field-option-row-columns-2 wpforms-file-upload-camera-time-limit-columns">' . $minutes_field . $seconds_field . '</div>',
				'class'   => array_merge( [ 'wpforms-file-upload-camera-time-limit' ], $time_limit_hidden_class ),
			]
		);
	}

	/**
	 * Whether the provided form has a camera field.
	 *
	 * @since 1.9.8
	 *
	 * @param array|mixed $form Form data.
	 */
	protected function is_camera_enabled( $form ): bool {

		if ( empty( $form['fields'] ) ) {
			return false;
		}

		foreach ( $form['fields'] as $field ) {
			if ( ! empty( $field['camera_enabled'] ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Whether the field is a camera field or has camera enabled.
	 *
	 * @since 1.9.8
	 *
	 * @param array $field Field data and settings.
	 */
	private function is_camera_enabled_for_field( array $field ): bool {

		return $this->type === 'camera' || ! empty( $field['camera_enabled'] );
	}

	/**
	 * Get the camera time limit in seconds.
	 *
	 * @since 1.9.8
	 *
	 * @param array $field Field data.
	 *
	 * @return int Camera time limit in seconds.
	 */
	public function get_camera_time_limit( array $field ): int {

		$field = wp_parse_args(
			$field,
			[
				'camera_enabled'            => false,
				'camera_format'             => '',
				'camera_time_limit_minutes' => 0,
				'camera_time_limit_seconds' => 0,
			]
		);

		if ( empty( $field['camera_enabled'] ) || $field['camera_format'] !== 'video' ) {
			return 0;
		}

		return absint( $field['camera_time_limit_minutes'] ) * 60 + absint( $field['camera_time_limit_seconds'] );
	}
}
