<?php

namespace WPForms\Forms\Fields\Traits;

/**
 * Numbers and Number Slider Field trait, designed for use with `WPForms_Field`.
 *
 * @since 1.9.4
 */
trait NumberField {

	/**
	 * Enqueues required scripts for the form builder.
	 *
	 * @since 1.9.4
	 */
	private function number_hooks() {

		add_action( 'wpforms_builder_enqueues', [ $this, 'number_builder_enqueues' ] );
	}

	/**
	 * Enqueue wpforms-number-field script.
	 *
	 * @since 1.9.4
	 *
	 * @param string $view Current view.
	 *
	 * @noinspection PhpUnusedParameterInspection, PhpUnnecessaryCurlyVarSyntaxInspection
	 */
	public function number_builder_enqueues( $view ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found

		$min = wpforms_get_min_suffix();

		wp_enqueue_script(
			'wpforms-number-field',
			WPFORMS_PLUGIN_URL . "assets/js/admin/builder/fields/numbers{$min}.js",
			[ 'wpforms-builder', 'wpforms-utils' ],
			WPFORMS_VERSION,
			false
		);
	}

	/**
	 * Helper function to create field option elements.
	 *
	 * Field option elements are pieces that help create a field option.
	 * They are used to quickly build field options.
	 *
	 * This method is intended to be used within classes that implement or extend
	 * the `WPForms_Field` functionality.
	 *
	 * @since 1.9.4
	 *
	 * @param string $option      Field option to render.
	 * @param array  $field       Field data and settings.
	 * @param array  $args        Field preview arguments.
	 * @param bool   $echo_output Print or return the value. Print by default.
	 *
	 * @return mixed echo or return string
	 */
	abstract public function field_element( $option, $field, $args = [], $echo_output = true );

	/**
	 * Helper function to create a number field option element.
	 *
	 * @since 1.9.4
	 *
	 * @param array $field       Field data and settings.
	 * @param array $args        Field preview arguments.
	 * @param bool  $echo_output Whether to print the generated output. Default true.
	 *
	 * @return string
	 */
	private function field_number_element( $field, $args = [], $echo_output = true ) { //phpcs:ignore Generic.Metrics.CyclomaticComplexity.MaxExceeded

		if ( ! isset( $args['slug'], $args['label'] ) ) {
			return '';
		}

		$slug  = $args['slug'];
		$label = $args['label'];
		$value = $field[ $slug ] ?? $args['value'] ?? '';
		$attrs = [];

		if ( isset( $args['min'] ) && is_numeric( $args['min'] ) ) {
			$attrs['min'] = (float) $args['min'];
		}

		if ( isset( $args['max'] ) && is_numeric( $args['max'] ) ) {
			$attrs['max'] = (float) $args['max'];
		}

		if (
			isset( $args['step'] ) &&
			(
				$args['step'] === 'any' ||
				( is_numeric( $args['step'] ) && $args['step'] > 0 )
			)
		) {
			$attrs['step'] = (string) $args['step'];
		}

		$number_label_markup = $this->field_element(
			'label',
			$field,
			[
				'slug'    => $slug,
				'value'   => $label,
				'tooltip' => $args['tooltip'] ?? '',
			],
			false
		);

		$number_input_markup = $this->field_element(
			'text',
			$field,
			[
				'type'  => 'number',
				'slug'  => $slug,
				'value' => is_numeric( $value ) ? (float) $value : '',
				'attrs' => $attrs,
				'class' => $args['class'] ?? '',
			],
			false
		);

		$output = $this->field_element(
			'row',
			$field,
			[
				'slug'    => $slug,
				'content' => $number_label_markup . $number_input_markup,
			],
			false
		);

		if ( ! $output ) {
			return '';
		}

		if ( $echo_output ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $output;
		}

		return $output;
	}

	/**
	 * Helper function to create `min_max` field option markup.
	 *
	 * @since 1.9.4
	 *
	 * @param array $field       Field data and settings.
	 * @param array $args        Field preview arguments.
	 * @param bool  $echo_output Print or return the value. Print by default.
	 *
	 * @return string
	 */
	private function field_number_option_min_max( $field, $args, $echo_output = true ) {

		$class              = $args['class'] ?? 'number_min_max';
		$range_label_markup = $this->field_element(
			'label',
			$field,
			[
				'slug'    => 'min',
				'value'   => $args['label'] ?? esc_html__( 'Range', 'wpforms-lite' ),
				'tooltip' => $args['tooltip'] ?? esc_html__( 'Define the minimum and the maximum values for the field.', 'wpforms-lite' ),
			],
			false
		);

		$min_value      = $field['min'] ?? null;
		$input_min_args = [
			'type'  => 'number',
			'slug'  => 'min',
			'value' => is_numeric( $min_value ) ? (float) $min_value : '',
			'class' => $class . '-min',
			'attrs' => [
				'step' => 'any',
			],
		];

		$range_input_min_markup = $this->field_element(
			'text',
			$field,
			$input_min_args,
			false
		);

		$max_value      = $field['max'] ?? null;
		$input_max_args = [
			'type'  => 'number',
			'slug'  => 'max',
			'value' => is_numeric( $max_value ) ? (float) $max_value : '',
			'class' => $class . '-max',
			'attrs' => [
				'step' => 'any',
			],
		];

		$range_input_max_markup = $this->field_element(
			'text',
			$field,
			$input_max_args,
			false
		);

		return $this->field_element(
			'row',
			$field,
			[
				'slug'    => 'min_max',
				'content' => $range_label_markup . sprintf(
					'<div class="wpforms-input-row">
						<div class="minimum">%s<label for="wpforms-field-option-%d-min" class="sub-label">%s</label></div>
						<div class="maximum">%s<label for="wpforms-field-option-%d-max" class="sub-label">%s</label></div>
					</div>',
					$range_input_min_markup,
					(int) $field['id'],
					esc_html__( 'Minimum', 'wpforms-lite' ),
					$range_input_max_markup,
					(int) $field['id'],
					esc_html__( 'Maximum', 'wpforms-lite' )
				),
			],
			$echo_output
		);
	}

	/**
	 * Helper function to create `default_value` field option markup.
	 *
	 * @since 1.9.4
	 *
	 * @param array $field       Field data and settings.
	 * @param array $args        Field preview arguments.
	 * @param bool  $echo_output Print or return the value. Print by default.
	 *
	 * @return string
	 */
	private function field_number_option_default_value( $field, $args, $echo_output = true ) {

		$default_value_args = [
			'slug'    => 'default_value',
			'label'   => esc_html__( 'Default Value', 'wpforms-lite' ),
			'tooltip' => esc_html__( 'Enter a default value for this field.', 'wpforms-lite' ),
			'class'   => $args['class'] ?? '',
			'value'   => $args['value'] ?? '',
			'min'     => $field['min'] ?? '',
			'max'     => $field['max'] ?? '',
			'step'    => $field['step'] ?? '',
		];

		return $this->field_number_element(
			$field,
			$default_value_args,
			$echo_output
		);
	}

	/**
	 * Helper function to create `step` field option markup.
	 *
	 * @since 1.9.4
	 *
	 * @param array $field       Field data and settings.
	 * @param array $args        Field preview arguments.
	 * @param bool  $echo_output Print or return the value. Print by default.
	 *
	 * @return string
	 */
	private function field_number_option_step( $field, $args, $echo_output = true ) {

		$step_args = [
			'slug'    => 'step',
			'label'   => esc_html__( 'Increment', 'wpforms-lite' ),
			'tooltip' => $args['tooltip'] ?? esc_html__( 'Determines the increment between selectable values on the field.', 'wpforms-lite' ),
			'class'   => $args['class'] ?? '',
			'min'     => 0,
			'step'    => 'any',
			'value'   => 1,
		];

		$min = is_numeric( $field['min'] ?? null ) ? (float) $field['min'] : null;
		$max = is_numeric( $field['max'] ?? null ) ? (float) $field['max'] : null;

		if ( ! is_null( $min ) && ! is_null( $max ) ) {
			$step_args['max'] = $max - $min;
		}

		return $this->field_number_element(
			$field,
			$step_args,
			$echo_output
		);
	}
}
