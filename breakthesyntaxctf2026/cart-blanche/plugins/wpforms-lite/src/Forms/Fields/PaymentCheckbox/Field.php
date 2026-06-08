<?php

namespace WPForms\Forms\Fields\PaymentCheckbox;

use WPForms_Field;

/**
 * Checkbox payment field.
 *
 * @since 1.8.2
 */
class Field extends WPForms_Field {

	/**
	 * Primary class constructor.
	 *
	 * @since 1.8.2
	 */
	public function init() {

		// Define field type information.
		$this->name     = esc_html__( 'Checkbox Items', 'wpforms-lite' );
		$this->keywords = esc_html__( 'product, store, ecommerce, pay, payment', 'wpforms-lite' );
		$this->type     = 'payment-checkbox';
		$this->icon     = 'fa-check-square-o';
		$this->order    = 50;
		$this->group    = 'payment';
		$this->defaults = [
			1 => [
				'label'      => esc_html__( 'First Item', 'wpforms-lite' ),
				'value'      => '10',
				'image'      => '',
				'icon'       => '',
				'icon_style' => '',
				'default'    => '',
			],
			2 => [
				'label'      => esc_html__( 'Second Item', 'wpforms-lite' ),
				'value'      => '25',
				'image'      => '',
				'icon'       => '',
				'icon_style' => '',
				'default'    => '',
			],
			3 => [
				'label'      => esc_html__( 'Third Item', 'wpforms-lite' ),
				'value'      => '50',
				'image'      => '',
				'icon'       => '',
				'icon_style' => '',
				'default'    => '',
			],
		];

		$this->default_settings = [
			'choices' => $this->defaults,
		];

		$this->hooks();
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.8.1
	 */
	private function hooks() {

		// Customize HTML field values.
		add_filter( 'wpforms_html_field_value', [ $this, 'field_html_value' ], 10, 4 );
		add_filter( "wpforms_{$this->type}_field_html_value_images", [ $this, 'field_html_value_images' ], 10, 3 );

		// Define additional field properties.
		add_filter( "wpforms_field_properties_{$this->type}", [ $this, 'field_properties' ], 5, 3 );

		// This field requires fieldset+legend instead of the field label.
		add_filter( "wpforms_frontend_modern_is_field_requires_fieldset_{$this->type}", '__return_true', PHP_INT_MAX, 2 );
	}

	/**
	 * Define additional field properties.
	 *
	 * @since 1.8.2
	 *
	 * @param array $properties Field properties.
	 * @param array $field      Field settings.
	 * @param array $form_data  Form data and settings.
	 *
	 * @return array
	 */
	public function field_properties( $properties, $field, $form_data ) { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		// Define data.
		$form_id  = absint( $form_data['id'] );
		$field_id = absint( $field['id'] );
		$choices  = $field['choices'];

		// Remove primary input, unset for attribute for label.
		unset( $properties['inputs']['primary'], $properties['label']['attr']['for'] );

		// Set input container (ul) properties.
		$properties['input_container'] = [
			'class' => [],
			'data'  => [],
			'attr'  => [],
			'id'    => "wpforms-{$form_id}-field_{$field_id}",
		];

		$is_choice_limit_set = ! empty( $field['choice_limit'] ) && (int) $field['choice_limit'] > 0;

		if ( $is_choice_limit_set ) {
			$properties['input_container']['data']['choice-limit'] = $field['choice_limit'];
		}

		// Set input properties.
		foreach ( $choices as $key => $choice ) {

			// Choice labels should not be left blank, but if they are, we provide a basic value.
			$label = $choice['label'];

			if ( $label === '' ) {
				if ( 1 === count( $choices ) ) {
					$label = esc_html__( 'Checked', 'wpforms-lite' );
				} else {
					/* translators: %s - item number. */
					$label = sprintf( esc_html__( 'Item %s', 'wpforms-lite' ), $key );
				}
			}

			$properties['inputs'][ $key ] = [
				'container'  => [
					'attr'  => [],
					'class' => [ "choice-{$key}" ],
					'data'  => [],
					'id'    => '',
				],
				'label'      => [
					'attr'  => [
						'for' => "wpforms-{$form_id}-field_{$field_id}_{$key}",
					],
					'class' => [ 'wpforms-field-label-inline' ],
					'data'  => [],
					'id'    => '',
					'text'  => $label,
				],
				'attr'       => [
					'name'  => "wpforms[fields][{$field_id}][]",
					'value' => $key,
				],
				'class'      => [ 'wpforms-payment-price' ],
				'data'       => [
					'amount' => wpforms_format_amount( wpforms_sanitize_amount( $choice['value'] ) ),
				],
				'id'         => "wpforms-{$form_id}-field_{$field_id}_{$key}",
				'icon'       => $choice['icon'] ?? '',
				'icon_style' => $choice['icon_style'] ?? '',
				'image'      => $choice['image'] ?? '',
				'required'   => ! empty( $field['required'] ) ? 'required' : '',
				'default'    => isset( $choice['default'] ),
			];

			// Rule for validator only if needed.
			if ( $is_choice_limit_set ) {
				$properties['inputs'][ $key ]['data']['rule-check-limit'] = 'true';
			}
		}

		// Required class for pagebreak validation.
		if ( ! empty( $field['required'] ) ) {
			$properties['input_container']['class'][] = 'wpforms-field-required';
		}

		// Custom properties if image choices are enabled.
		if ( ! empty( $field['choices_images'] ) ) {

			$properties['input_container']['class'][] = 'wpforms-image-choices';
			$properties['input_container']['class'][] = 'wpforms-image-choices-' . sanitize_html_class( $field['choices_images_style'] );

			foreach ( $properties['inputs'] as $key => $inputs ) {
				$properties['inputs'][ $key ]['container']['class'][] = 'wpforms-image-choices-item';

				if ( in_array( $field['choices_images_style'], [ 'modern', 'classic' ], true ) ) {
					$properties['inputs'][ $key ]['class'][] = 'wpforms-screen-reader-element';
				}
			}
		} elseif ( ! empty( $field['choices_icons'] ) ) {
			$properties = wpforms()->obj( 'icon_choices' )->field_properties( $properties, $field );
		}

		// Add selected class for choices with defaults.
		foreach ( $properties['inputs'] as $key => $inputs ) {
			if ( ! empty( $inputs['default'] ) ) {
				$properties['inputs'][ $key ]['container']['class'][] = 'wpforms-selected';
			}
		}

		return $properties;
	}

	/**
	 * Get field populated single property value.
	 *
	 * @since 1.8.2
	 *
	 * @param string $raw_value  Value from a GET param, always a string.
	 * @param string $input      Represent a subfield inside the field. May be empty.
	 * @param array  $properties Field properties.
	 * @param array  $field      Current field specific data.
	 *
	 * @return array Modified field properties.
	 */
	protected function get_field_populated_single_property_value( $raw_value, $input, $properties, $field ) {
		/*
		 * When the form is submitted, we get only choice values from the Fallback.
		 * As payment-checkbox (checkboxes) field doesn't support 'show_values' option -
		 * we should transform that into label to check against using general logic in parent method.
		 */

		if (
			! is_string( $raw_value ) ||
			empty( $field['choices'] ) ||
			! is_array( $field['choices'] )
		) {
			return $properties;
		}

		// The form submits only the sum, so shortcut for Dynamic.
		if ( ! is_numeric( $raw_value ) ) {
			return parent::get_field_populated_single_property_value( $raw_value, $input, $properties, $field );
		}

		$get_value = wpforms_format_amount( wpforms_sanitize_amount( $raw_value ) );

		foreach ( $field['choices'] as $choice ) {
			if (
				isset( $choice['label'], $choice['value'] ) &&
				wpforms_format_amount( wpforms_sanitize_amount( $choice['value'] ) ) === $get_value
			) {
				$trans_value = $choice['label'];
				// Stop iterating over choices.
				break;
			}
		}

		if ( empty( $trans_value ) ) {
			return $properties;
		}

		return parent::get_field_populated_single_property_value( $trans_value, $input, $properties, $field );
	}

	/**
	 * Field options panel inside the builder.
	 *
	 * @since 1.8.2
	 *
	 * @param array $field Field settings.
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
				'markup' => 'open',
			]
		);

		// Label.
		$this->field_option( 'label', $field );

		// Choices option.
		$this->field_option( 'choices_payments', $field );

		// Show price after item labels.
		$fld  = $this->field_element(
			'toggle',
			$field,
			[
				'slug'    => 'show_price_after_labels',
				'value'   => isset( $field['show_price_after_labels'] ) ? '1' : '0',
				'desc'    => esc_html__( 'Show Price After Item Labels', 'wpforms-lite' ),
				'tooltip' => esc_html__( 'Check this option to show price of the item after the label.', 'wpforms-lite' ),
			],
			false
		);
		$args = [
			'slug'    => 'show_price_after_labels',
			'content' => $fld,
		];

		$this->field_element( 'row', $field, $args );

		// Choices Images.
		$this->field_option( 'choices_images', $field );

		// Hide Choices Images.
		$this->field_option( 'choices_images_hide', $field );

		// Choice Images Style (theme).
		$this->field_option( 'choices_images_style', $field );

		// Choices Icons.
		$this->field_option( 'choices_icons', $field );

		// Choices Icons Color.
		$this->field_option( 'choices_icons_color', $field );

		// Choices Icons Size.
		$this->field_option( 'choices_icons_size', $field );

		// Choices Icons Style.
		$this->field_option( 'choices_icons_style', $field );

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

		// Input columns.
		$this->field_option( 'input_columns', $field );

		// Choice Limit.
		$this->field_option( 'choice_limit', $field );

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
	 * Field preview inside the builder.
	 *
	 * @since 1.8.2
	 *
	 * @param array $field Field settings.
	 */
	public function field_preview( $field ) {

		// Label.
		$this->field_preview_option( 'label', $field );

		// Choices.
		$this->field_preview_option( 'choices', $field );

		// Description.
		$this->field_preview_option( 'description', $field );
	}

	/**
	 * Field display on the form front-end.
	 *
	 * @since 1.8.2
	 *
	 * @param array $field      Field settings.
	 * @param array $deprecated Deprecated array.
	 * @param array $form_data  Form data and settings.
	 *
	 * @noinspection HtmlUnknownAttribute
	 * @noinspection HtmlUnknownTarget
	 */
	public function field_display( $field, $deprecated, $form_data ) {

		// Define data.
		$container = $field['properties']['input_container'];
		$choices   = $field['properties']['inputs'];

		printf(
			'<ul %s>',
			wpforms_html_attributes( $container['id'], $container['class'], $container['data'], $container['attr'] ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		);

			foreach ( $choices as $key => $choice ) {

				$label = $choice['label']['text'] ?? '';

				/* translators: %s - item number. */
				$label = $label !== '' ? $label : sprintf( esc_html__( 'Item %s', 'wpforms-lite' ), $key );

				$label .= ! empty( $field['show_price_after_labels'] ) && isset( $choice['data']['amount'] ) ? $this->get_price_after_label( $choice['data']['amount'] ) : '';

				printf(
					'<li %s>',
					wpforms_html_attributes( $choice['container']['id'], $choice['container']['class'], $choice['container']['data'], $choice['container']['attr'] ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				);

					if ( empty( $field['dynamic_choices'] ) && ! empty( $field['choices_images'] ) ) {

						// Image choices.
						printf(
							'<label %s>',
							wpforms_html_attributes( $choice['label']['id'], $choice['label']['class'], $choice['label']['data'], $choice['label']['attr'] )  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						);

							echo '<span class="wpforms-image-choices-image">';

							if ( ! empty( $choice['image'] ) ) {
								printf(
									'<img src="%s" alt="%s"%s>',
									esc_url( $choice['image'] ),
									esc_attr( $choice['label']['text'] ),
									! empty( $choice['label']['text'] ) ? ' title="' . esc_attr( $choice['label']['text'] ) . '"' : ''
								);
							}

							echo '</span>';

							if ( $field['choices_images_style'] === 'none' ) {
								echo '<br>';
							}

							printf(
								'<input type="checkbox" %s %s %s>',
								wpforms_html_attributes( $choice['id'], $choice['class'], $choice['data'], $choice['attr'] ),  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								esc_attr( $choice['required'] ),
								checked( '1', $choice['default'], false )
							);

							echo '<span class="wpforms-image-choices-label">' . wp_kses_post( $label ) . '</span>';

						echo '</label>';

					} elseif ( empty( $field['dynamic_choices'] ) && ! empty( $field['choices_icons'] ) ) {
						// Icon Choices.
						wpforms()->obj( 'icon_choices' )->field_display( $field, $choice, 'checkbox', $label );

					} else {

						// Normal display.
						printf(
							'<input type="checkbox" %s %s %s>',
							wpforms_html_attributes( $choice['id'], $choice['class'], $choice['data'], $choice['attr'] ),  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							esc_attr( $choice['required'] ),
							checked( '1', $choice['default'], false )
						);

						printf(
							'<label %s>%s</label>',
							wpforms_html_attributes( $choice['label']['id'], $choice['label']['class'], $choice['label']['data'], $choice['label']['attr'] ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							wp_kses_post( $label )
						);
					}

				echo '</li>';
			}

		echo '</ul>';
	}

	/**
	 * Validate field on submitting the form.
	 *
	 * @since 1.8.2
	 *
	 * @param int   $field_id     Field ID.
	 * @param array $field_submit Submitted field value (raw data).
	 * @param array $form_data    Form data and settings.
	 */
	public function validate( $field_id, $field_submit, $form_data ) {

		$field_id = (int) $field_id;
		$error    = '';

		// Basic required check - If field is marked as required, check for entry data.
		if ( ! empty( $form_data['fields'][ $field_id ]['required'] ) && empty( $field_submit ) ) {
			$error = wpforms_get_required_label();
		}

		if ( ! empty( $field_submit ) ) {
			foreach ( (array) $field_submit as $checked_choice ) {
				// Validate that the option selected is real.
				if ( empty( $form_data['fields'][ $field_id ]['choices'][ (int) $checked_choice ] ) ) {
					$error = esc_html__( 'Invalid payment option.', 'wpforms-lite' );

					break;
				}
			}
		}

		$field_submit = (array) $field_submit;

		$this->validate_field_choice_limit( $field_id, $field_submit, $form_data );

		if ( ! empty( $error ) ) {
			wpforms()->obj( 'process' )->errors[ $form_data['id'] ][ $field_id ] = $error;
		}
	}

	/**
	 * Format and sanitize field.
	 *
	 * @since 1.8.2
	 *
	 * @param int   $field_id     Field ID.
	 * @param array $field_submit Array of selected choice IDs.
	 * @param array $form_data    Form data and settings.
	 */
	public function format( $field_id, $field_submit, $form_data ) { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh, Generic.Metrics.NestingLevel.MaxExceeded

		$field_submit  = array_values( (array) $field_submit );
		$field         = $form_data['fields'][ $field_id ];
		$name          = sanitize_text_field( $field['label'] );
		$amount        = 0;
		$images        = [];
		$choice_values = [];
		$choice_labels = [];
		$choice_keys   = [];

		if ( ! empty( $field_submit ) ) {
			foreach ( $field_submit as $choice_checked ) {
				foreach ( $field['choices'] as $choice_id => $choice ) {
					// Exit early.
					if ( (int) $choice_checked !== (int) $choice_id ) {
						continue;
					}

					$value = (float) wpforms_sanitize_amount( $choice['value'] );

					// Increase the total amount.
					$amount += $value;

					$value        = wpforms_format_amount( $value, true );
					$choice_label = '';

					if ( ! empty( $choice['label'] ) ) {
						$choice_label = sanitize_text_field( $choice['label'] );
						$value        = $choice_label . ' - ' . $value;
					}

					$choice_labels[] = $choice_label;
					$choice_values[] = $value;
					$choice_keys[]   = $choice_id;
				}
			}

			if ( ! empty( $choice_keys ) && ! empty( $field['choices_images'] ) ) {
				foreach ( $choice_keys as $choice_key ) {
					$images[] = ! empty( $field['choices'][ $choice_key ]['image'] ) ? esc_url_raw( $field['choices'][ $choice_key ]['image'] ) : '';
				}
			}
		}

		wpforms()->obj( 'process' )->fields[ $field_id ] = [
			'name'         => $name,
			'value'        => implode( "\r\n", $choice_values ),
			'value_choice' => implode( "\r\n", $choice_labels ),
			'value_raw'    => implode( ',', array_map( 'absint', $field_submit ) ),
			'amount'       => wpforms_format_amount( $amount ),
			'amount_raw'   => $amount,
			'currency'     => wpforms_get_currency(),
			'images'       => $images,
			'id'           => absint( $field_id ),
			'type'         => sanitize_key( $this->type ),
		];
	}
}
