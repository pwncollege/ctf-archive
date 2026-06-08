<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Multiple Choice field.
 *
 * @since 1.0.0
 */
class WPForms_Field_Radio extends WPForms_Field {

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function init() {

		// Define field type information.
		$this->name     = esc_html__( 'Multiple Choice', 'wpforms-lite' );
		$this->keywords = esc_html__( 'radio', 'wpforms-lite' );
		$this->type     = 'radio';
		$this->icon     = 'fa-dot-circle-o';
		$this->order    = 110;
		$this->defaults = [
			1 => [
				'label'      => esc_html__( 'First Choice', 'wpforms-lite' ),
				'value'      => '',
				'image'      => '',
				'icon'       => '',
				'icon_style' => '',
				'default'    => '',
			],
			2 => [
				'label'      => esc_html__( 'Second Choice', 'wpforms-lite' ),
				'value'      => '',
				'image'      => '',
				'icon'       => '',
				'icon_style' => '',
				'default'    => '',
			],
			3 => [
				'label'      => esc_html__( 'Third Choice', 'wpforms-lite' ),
				'value'      => '',
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
	 * Hooks.
	 *
	 * @since 1.8.1
	 */
	private function hooks() {

		// Customize HTML field values.
		add_filter( 'wpforms_html_field_value', [ $this, 'field_html_value' ], 10, 4 );
		add_filter( "wpforms_{$this->type}_field_html_value_images", [ $this, 'field_html_value_images' ], 10, 3 );

		// Define additional field properties.
		add_filter( 'wpforms_field_properties_radio', [ $this, 'field_properties' ], 5, 3 );

		// This field requires fieldset+legend instead of the field label.
		add_filter( "wpforms_frontend_modern_is_field_requires_fieldset_{$this->type}", '__return_true', PHP_INT_MAX, 2 );

		// Load assets.
		add_action( 'wpforms_builder_enqueues', [ $this, 'builder_assets' ] );

		// Modify an export data format for the Other option.
		add_filter( 'wpforms_pro_admin_entries_export_ajax_get_entry_fields_data_field', [ $this, 'export_entry_field_data' ] );

		// Allow radio fields to be included in the Keyword Filter.
		add_filter( 'wpforms_pro_anti_spam_keyword_filter_get_filtered_fields', [ $this, 'add_field_to_anti_spam_keyword_filter' ] );

		// Adjust entry field before saving to entry_fields DB table.
		add_filter( 'wpforms_entry_save_fields', [ $this, 'save_field' ], 10, 3 );
	}

	/**
	 * Enqueue assets for the builder.
	 *
	 * @since 1.9.8.3
	 */
	public function builder_assets() {

		$min = wpforms_get_min_suffix();

		wp_enqueue_script(
			'wpforms-multiple-choices',
			WPFORMS_PLUGIN_URL . "assets/js/admin/builder/multiple-choices{$min}.js",
			[ 'jquery', 'wpforms-builder' ],
			WPFORMS_VERSION,
			false
		);
	}

	/**
	 * Adjust builder preview container classes.
	 *
	 * Adds size-{small|medium|large} to the Radio field container in the Builder
	 * when the "Add Other Choice" option is enabled.
	 *
	 * @since 1.9.8.3
	 *
	 * @param string $css   Existing class string.
	 * @param array  $field Field data and settings.
	 *
	 * @return string
	 */
	public function preview_field_class( $css, $field ): string {

		$css = parent::preview_field_class( $css, $field );

		if ( $field['type'] !== $this->type ) {
			return $css;
		}

		// Apply a size class to the field container when Other Choice is enabled.
		if ( $this->has_other_choice( $field ) ) {
			$size = ! empty( $field['other_size'] ) ? sanitize_html_class( $field['other_size'] ) : 'medium';
			$css .= ' size-' . $size;
		}

		return $css;
	}

	/**
	 * Define additional field properties.
	 *
	 * @since 1.4.5
	 *
	 * @param array $properties Field properties.
	 * @param array $field      Field settings.
	 * @param array $form_data  Form data and settings.
	 *
	 * @return array
	 */
	public function field_properties( $properties, $field, $form_data ) {

		// Remove primary input, unset for attribute for label.
		unset( $properties['inputs']['primary'], $properties['label']['attr']['for'] );

		// Define data.
		$form_id  = absint( $form_data['id'] );
		$field_id = wpforms_validate_field_id( $field['id'] );
		$choices  = $field['choices'];
		$dynamic  = wpforms_get_field_dynamic_choices( $field, $form_id, $form_data );

		if ( $dynamic !== false ) {
			$choices              = $dynamic;
			$field['show_values'] = true;
		}

		// Set input container (ul) properties.
		$properties['input_container'] = [
			'class' => [ ! empty( $field['random'] ) ? 'wpforms-randomize' : '' ],
			'data'  => [],
			'attr'  => [],
			'id'    => "wpforms-{$form_id}-field_{$field_id}",
		];

		// Set input properties.
		foreach ( $choices as $key => $choice ) {

			// Used for dynamic choices.
			$depth = isset( $choice['depth'] ) ? absint( $choice['depth'] ) : 1;

			$value = ! empty( $field['show_values'] ) ? $choice['value'] : $choice['label'];
			/* translators: %s - choice number. */
			$value = ( $value === '' ) ? sprintf( esc_html__( 'Choice %s', 'wpforms-lite' ), $key ) : $value;

			// Check if this is the "Other" choice.
			$is_other_choice = isset( $choice['other'] ) && (bool) $choice['other'] === true;

			$properties['inputs'][ $key ] = [
				'container'  => [
					'attr'  => [],
					'class' => [ "choice-{$key}", "depth-{$depth}", $is_other_choice ? 'wpforms-other-choice' : '' ],
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
					'text'  => $choice['label'],
				],
				'attr'       => [
					'name'  => "wpforms[fields][{$field_id}]",
					'value' => $value,
				],
				'class'      => [],
				'data'       => $is_other_choice ? [ 'other-choice' => 'true' ] : [],
				'id'         => "wpforms-{$form_id}-field_{$field_id}_{$key}",
				'icon'       => isset( $choice['icon'] ) ? $choice['icon'] : '',
				'icon_style' => isset( $choice['icon_style'] ) ? $choice['icon_style'] : '',
				'image'      => isset( $choice['image'] ) ? $choice['image'] : '',
				'required'   => ! empty( $field['required'] ) ? 'required' : '',
				'default'    => isset( $choice['default'] ),
			];
		}

		// Required class for pagebreak validation.
		if ( ! empty( $field['required'] ) ) {
			$properties['input_container']['class'][] = 'wpforms-field-required';
		}

		// Custom properties if image choices is enabled.
		if ( ! $dynamic && ! empty( $field['choices_images'] ) ) {

			$properties['input_container']['class'][] = 'wpforms-image-choices';
			$properties['input_container']['class'][] = 'wpforms-image-choices-' . sanitize_html_class( $field['choices_images_style'] );

			foreach ( $properties['inputs'] as $key => $inputs ) {
				$properties['inputs'][ $key ]['container']['class'][] = 'wpforms-image-choices-item';

				if ( in_array( $field['choices_images_style'], [ 'modern', 'classic' ], true ) ) {
					$properties['inputs'][ $key ]['class'][] = 'wpforms-screen-reader-element';
				}
			}
		} elseif ( ! $dynamic && ! empty( $field['choices_icons'] ) ) {
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
	 * Field options panel inside the builder.
	 *
	 * @since 1.0.0
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

		// Choices.
		$this->field_option( 'choices', $field );

		// AI Feature.
		$this->field_option(
			'ai_modal_button',
			$field,
			[
				'value' => esc_html__( 'Generate Choices', 'wpforms-lite' ),
				'type'  => 'choices',
			]
		);

		// Add Other Choice.
		$this->field_option( 'choices_other', $field );

		// Other Field Size for "Other" input.
		$this->field_option( 'other_size', $field );

		// Other Placeholder for "Other" input.
		$this->field_option( 'other_placeholder', $field );

		// Choices Images.
		$this->field_option( 'choices_images', $field );

		// Hide Choices Images.
		$this->field_option( 'choices_images_hide', $field );

		// Choices Images Style (theme).
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

		// Randomize order of choices.
		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'random',
				'content' => $this->field_element(
					'toggle',
					$field,
					[
						'slug'    => 'random',
						'value'   => isset( $field['random'] ) ? '1' : '0',
						'desc'    => esc_html__( 'Randomize Choices', 'wpforms-lite' ),
						'tooltip' => esc_html__( 'Check this option to randomize the order of the choices.', 'wpforms-lite' ),
					],
					false
				),
			]
		);

		// Show Values toggle option. This option will only show if already used
		// or if manually enabled by a filter.
		if ( ! empty( $field['show_values'] ) || wpforms_show_fields_options_setting() ) {
			$this->field_element(
				'row',
				$field,
				[
					'slug'    => 'show_values',
					'content' => $this->field_element(
						'toggle',
						$field,
						[
							'slug'    => 'show_values',
							'value'   => isset( $field['show_values'] ) ? $field['show_values'] : '0',
							'desc'    => esc_html__( 'Show Values', 'wpforms-lite' ),
							'tooltip' => esc_html__( 'Check this option to manually set form field values.', 'wpforms-lite' ),
						],
						false
					),
				]
			);
		}

		// Display format.
		$this->field_option( 'input_columns', $field );

		// Dynamic choice auto-populating toggle.
		$this->field_option( 'dynamic_choices', $field );

		// Dynamic choice source.
		$this->field_option( 'dynamic_choices_source', $field );

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
	 * @since 1.0.0
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
	 * Field display on the form front-end and admin entry edit page.
	 *
	 * @since 1.0.0
	 *
	 * @param array $field      Field settings.
	 * @param array $deprecated Deprecated array.
	 * @param array $form_data  Form data and settings.
	 */
	public function field_display( $field, $deprecated, $form_data ) {

		$using_image_choices = empty( $field['dynamic_choices'] ) && empty( $field['choices_icons'] ) && ! empty( $field['choices_images'] );
		$using_icon_choices  = empty( $field['dynamic_choices'] ) && empty( $field['choices_images'] ) && ! empty( $field['choices_icons'] );

		// Define data.
		$container = $field['properties']['input_container'];
		$choices   = $field['properties']['inputs'];

		// Do not display the field with empty choices on the frontend.
		if ( ! $choices && ! is_admin() ) {
			return;
		}

		// Display a warning message on Entry Edit page.
		if ( ! $choices && is_admin() ) {
			$this->display_empty_dynamic_choices_message( $field );

			return;
		}

		$amp_state_id = '';

		if ( wpforms_is_amp() && ( $using_image_choices || $using_icon_choices ) ) {
			$amp_state_id = str_replace( '-', '_', sanitize_key( $container['id'] ) ) . '_state';
			$state        = [
				'selected' => null,
			];

			foreach ( $choices as $key => $choice ) {
				if ( $choice['default'] ) {
					$state['selected'] = $choice['attr']['value'];

					break;
				}
			}
			printf(
				'<amp-state id="%s"><script type="application/json">%s</script></amp-state>',
				esc_attr( $amp_state_id ),
				wp_json_encode( $state )
			);
		}

		printf(
			'<ul %s>',
			wpforms_html_attributes( $container['id'], $container['class'], $container['data'], $container['attr'] )
		);

			foreach ( $choices as $key => $choice ) {
				$label = $this->get_choices_label( $choice['label']['text'] ?? '', $key, $field );

				if ( wpforms_is_amp() && ( $using_image_choices || $using_icon_choices ) ) {
					$choice['container']['attr']['[class]'] = sprintf(
						'%s + ( %s == %s ? " wpforms-selected" : "")',
						wp_json_encode( implode( ' ', $choice['container']['class'] ) ),
						$amp_state_id,
						wp_json_encode( $choice['attr']['value'] )
					);
				}

				printf(
					'<li %s>',
					wpforms_html_attributes( $choice['container']['id'], $choice['container']['class'], $choice['container']['data'], $choice['container']['attr'] )
				);

					if ( $using_image_choices ) {

						// Make sure the image choices are keyboard-accessible.
						$choice['label']['attr']['tabindex'] = 0;

						if ( wpforms_is_amp() ) {
							$choice['label']['attr']['on']   = sprintf(
								'tap:AMP.setState(%s)',
								wp_json_encode( [ $amp_state_id => $choice['attr']['value'] ] )
							);
							$choice['label']['attr']['role'] = 'button';
						}

						if ( is_array( $choice['label']['class'] ) && wpforms_is_empty_string( $label ) ) {
							$choice['label']['class'][] = 'wpforms-field-label-inline-empty';
						}

						// Image choices.
						printf(
							'<label %s>',
							wpforms_html_attributes( $choice['label']['id'], $choice['label']['class'], $choice['label']['data'], $choice['label']['attr'] )
						);

							echo '<span class="wpforms-image-choices-image">';

							if ( ! empty( $choice['image'] ) ) {
								printf(
									'<img src="%s" alt="%s"%s>',
									esc_url( $choice['image'] ),
									esc_attr( $label ),
									! empty( $label ) ? ' title="' . esc_attr( $label ) . '"' : ''
								);
							}

							echo '</span>';

							if ( $field['choices_images_style'] === 'none' ) {
								echo '<br>';
							}

							$choice['attr']['tabindex'] = '-1';

							if ( wpforms_is_amp() ) {
								$choice['attr']['[checked]'] = sprintf(
									'%s == %s',
									$amp_state_id,
									wp_json_encode( $choice['attr']['value'] )
								);
							}

							printf(
								'<input type="radio" %s %s %s>',
								wpforms_html_attributes( $choice['id'], $choice['class'], $choice['data'], $choice['attr'] ),
								esc_attr( $choice['required'] ),
								checked( '1', $choice['default'], false )
							);

							echo '<span class="wpforms-image-choices-label">' . wp_kses_post( $choice['label']['text'] ) . '</span>';

						echo '</label>';

					} elseif ( $using_icon_choices ) {

						if ( wpforms_is_amp() ) {
							$choice['label']['attr']['on']   = sprintf(
								'tap:AMP.setState(%s)',
								wp_json_encode( [ $amp_state_id => $choice['attr']['value'] ] )
							);
							$choice['label']['attr']['role'] = 'button';
						}

						// Icon Choices.
						wpforms()->obj( 'icon_choices' )->field_display( $field, $choice, 'radio' );

					} else {
						// Normal display.
						printf(
							'<input type="radio" %s %s %s>',
							wpforms_html_attributes( $choice['id'], $choice['class'], $choice['data'], $choice['attr'] ),
							esc_attr( $choice['required'] ),
							checked( '1', $choice['default'], false )
						);

						printf(
							'<label %s>%s</label>',
							wpforms_html_attributes( $choice['label']['id'], $choice['label']['class'], $choice['label']['data'], $choice['label']['attr'] ),
							wp_kses_post( $label )
						);
					}

				// Capture the text field for "Other" choice to render separately below the list.
				if ( ! empty( $choice['data']['other-choice'] ) ) {
					$default_value = '';
					$size          = ! empty( $field['other_size'] ) ? sanitize_html_class( $field['other_size'] ) : 'medium';
					$size_class    = 'wpforms-field-' . $size;

					// Do not hide the Other input if this choice is set as default.
					$hidden_class = ! empty( $choice['default'] ) ? '' : ' wpforms-hidden';

					if ( isset( $field['choices'][ $key ]['value'] ) && $field['choices'][ $key ]['value'] !== '' ) {
						$default_value = $field['choices'][ $key ]['value'];
					}

					/**
					 * Filters the default value of the Other choice field option.
					 *
					 * This filter allows modifying what value should be prefilled for the Other choice inputs.
					 *
					 * @since 1.9.8.3
					 *
					 * @param string $default_value Default value for the "Other" choice input.
					 * @param array  $field         Field data and settings.
					 * @param string $label         Field label.
					 */
					$default_value = apply_filters( 'wpforms_field_radio_other_choice_default_value', $default_value, $field, $label );

					$other_atts = [
						'name'  => "wpforms[fields][{$field['id']}][other]",
						'value' => $default_value,
					];

					if ( empty( $choice['default'] ) ) {
						$other_atts['disabled'] = 'disabled';
					}

					if ( ! empty( $field['other_placeholder'] ) ) {
						$other_atts['placeholder'] = $field['other_placeholder'];
					}

					$other_input_html = sprintf(
						'<input type="text" %s required>',
						wpforms_html_attributes(
							"wpforms-{$form_data['id']}-field_{$field['id']}_other",
							[ 'wpforms-other-input', 'wpforms-field-required', $size_class, $hidden_class ],
							[],
							$other_atts
						)
					);
				}

				echo '</li>';
			}

		echo '</ul>';
		// Render the captured "Other" input separately, under the list of options.
		if ( ! empty( $other_input_html ) ) {
			echo $other_input_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	/**
	 * Validate field.
	 *
	 * @since 1.8.2
	 *
	 * @param int          $field_id     Field ID.
	 * @param string|array $field_submit Submitted field value (raw data).
	 * @param array        $form_data    Form data and settings.
	 */
	public function validate( $field_id, $field_submit, $form_data ) {

		$field = $form_data['fields'][ $field_id ];

		// Skip validation if field is dynamic and choices are empty.
		if ( $this->is_dynamic_choices_empty( $field, $form_data ) ) {
			return;
		}

		parent::validate( $field_id, $field_submit, $form_data );
	}

	/**
	 * Format and sanitize field.
	 *
	 * @since 1.0.2
	 * @since 1.9.8.3 Changed the expected $field_submit from string to mixed as in case with the Other option we can expect the array to arrive here.
	 *
	 * @param int   $field_id     Field ID.
	 * @param mixed $field_submit Submitted form data.
	 * @param array $form_data    Form data and settings.
	 */
	public function format( $field_id, $field_submit, $form_data ) {

		$field     = $form_data['fields'][ $field_id ];
		$dynamic   = ! empty( $field['dynamic_choices'] ) ? $field['dynamic_choices'] : false;
		$name      = sanitize_text_field( $field['label'] );
		$value_raw = sanitize_text_field( $field_submit );

		$data = [
			'name'      => $name,
			'value'     => '',
			'value_raw' => $value_raw,
			'id'        => wpforms_validate_field_id( $field_id ),
			'type'      => $this->type,
		];

		if ( 'post_type' === $dynamic && ! empty( $field['dynamic_post_type'] ) ) {

			// Dynamic population is enabled using post type.
			$data['dynamic']           = 'post_type';
			$data['dynamic_items']     = absint( $value_raw );
			$data['dynamic_post_type'] = $field['dynamic_post_type'];
			$post                      = get_post( $value_raw );

			if ( ! empty( $post ) && ! is_wp_error( $post ) && $data['dynamic_post_type'] === $post->post_type ) {
				$data['value'] = esc_html( wpforms_get_post_title( $post ) );
			}
		} elseif ( 'taxonomy' === $dynamic && ! empty( $field['dynamic_taxonomy'] ) ) {

			// Dynamic population is enabled using taxonomy.
			$data['dynamic']          = 'taxonomy';
			$data['dynamic_items']    = absint( $value_raw );
			$data['dynamic_taxonomy'] = $field['dynamic_taxonomy'];
			$term                     = get_term( $value_raw, $data['dynamic_taxonomy'] );

			if ( ! empty( $term ) && ! is_wp_error( $term ) ) {
				$data['value'] = esc_html( wpforms_get_term_name( $term ) );
			}
		} else {

			// Normal processing, dynamic population is off.
			$choice_key = '';

			// If show_values is true, that means value posted is the raw value
			// and not the label. So we need to set label value. Also store
			// the choice key.
			if ( ! empty( $field['show_values'] ) ) {
				foreach ( $field['choices'] as $key => $choice ) {
					if ( ! empty( $field_submit ) && $choice['value'] === $field_submit ) {
						$data['value'] = sanitize_text_field( $choice['label'] );
						$choice_key    = $key;
						break;
					}
				}
			} else {

				$data['value'] = $value_raw;

				// Determine choice key, this is needed for image choices.
				foreach ( $field['choices'] as $key => $choice ) {
					/* translators: %s - choice number. */
					if ( $field_submit === $choice['label'] || $value_raw === sprintf( esc_html__( 'Choice %s', 'wpforms-lite' ), $key ) ) {
						$choice_key = $key;

						break;
					}
				}
			}

			// Images choices are enabled, lookup and store image URL.
			if ( ! empty( $choice_key ) && ! empty( $field['choices_images'] ) ) {

				$data['image'] = ! empty( $field['choices'][ $choice_key ]['image'] ) ? esc_url_raw( $field['choices'][ $choice_key ]['image'] ) : '';
			}
		}

		// For the Other option the value_raw is the option and the value is text from the input.
		if ( is_array( $field_submit ) && ! empty( $field_submit['other'] ) ) {
			$data['value'] = sanitize_text_field( $field_submit['other'] );
			// Save the flag that the saved value is from the other option field.
			$data['is_other'] = true;

			foreach ( $field['choices'] as $choice ) {

				if ( isset( $choice['other'] ) ) {
					$data['value_raw'] = $choice['label'];
					$data['image']     = ! empty( $field['choices_images'] ) && ! empty( $choice['image'] ) ? esc_url_raw( $choice['image'] ) : '';

					break;
				}
			}
		}

		// Push field details to be saved.
		wpforms()->obj( 'process' )->fields[ $field_id ] = $data;
	}

	/**
	 * Export entry field data.
	 *
	 * @since 1.9.8.3
	 *
	 * @param array|mixed $field Field data.
	 *
	 * @return array
	 */
	public function export_entry_field_data( $field ): array {

		$field = (array) $field;

		if ( empty( $field['is_other'] ) ) {
			return $field;
		}

		$value     = (string) ( $field['value'] ?? '' );
		$value_raw = (string) ( $field['value_raw'] ?? '' );

		$field['value'] = $value_raw . ': ' . $value;

		return $field;
	}

	/**
	 * Include a radio field in allowed field types for keyword search.
	 *
	 * @since 1.9.8.3
	 *
	 * @param array|mixed $fields Array of field types.
	 *
	 * @return array
	 */
	public function add_field_to_anti_spam_keyword_filter( $fields ): array {

		$fields[] = $this->type;

		return $fields;
	}

	/**
	 * Generate the HTML value for a field.
	 *
	 * It overrides the parent method because of fields with Other choices.
	 *
	 * @since 1.9.8.3
	 *
	 * @param mixed  $value     The value of the field.
	 * @param array  $field     Field data and settings.
	 * @param array  $form_data Optional. Additional form data.
	 * @param string $context   Optional. The context in which the value is being rendered.
	 *
	 * @return string
	 */
	public function field_html_value( $value, $field, $form_data = [], $context = '' ) {

		if ( empty( $field['type'] ) || $field['type'] !== $this->type ) {
			return $value;
		}

		$other_value = $this->get_other_choice_value( $field );
		$field_value = $other_value ?? $value ?? '';

		return parent::field_html_value(
			$field_value,
			$field,
			$form_data,
			$context
		);
	}

	/**
	 * Return choice value, including Other label if applicable.

	 * It overrides the parent method because of fields with Other choices.
	 *
	 * @since 1.9.8.3
	 *
	 * @param array $field     Field settings.
	 * @param array $form_data Form data.
	 *
	 * @return string
	 */
	protected function get_choices_value( array $field, array $form_data ): string {

		$other_value = $this->get_other_choice_value( $field );

		return $other_value ?? parent::get_choices_value( $field, $form_data );
	}

	/**
	 * Retrieve the value for the "Other" choice option in a field.
	 *
	 * This method handles the retrieval of the "Other" choice value, considering
	 * both raw and processed values. It supports cases where the "Other" choice
	 * is enabled and attempts to construct a meaningful representation of the value
	 * based on the available inputs.
	 *
	 * @since 1.9.8.3
	 *
	 * @param array $field Field data.
	 *
	 * @return string|null Returns the constructed "Other" choice value if available,
	 *                     or null if the "Other" choice is not enabled.
	 */
	private function get_other_choice_value( array $field ): ?string {

		// Bail out early if it's not an Other choice.
		if ( empty( $field['is_other'] ) ) {
			return null;
		}

		$value     = $field['value'] ?? '';
		$value_raw = $field['value_raw'] ?? '';

		if ( wpforms_is_empty_string( $value_raw ) ) {
			return (string) $value;
		}

		// Return a value with a value_raw as a prefix only if both are not empty.
		if ( ! wpforms_is_empty_string( $value ) ) {
			return sprintf( '%1$s: %2$s', $value_raw, $value );
		}

		return (string) $value_raw;
	}

	/**
	 * Adjust the entry field before saving.
	 *
	 * It's necessary for fields with other choices.
	 *
	 * @since 1.9.8.3
	 *
	 * @param array $field     Field data.
	 * @param array $form_data Form data.
	 * @param int   $entry_id  Entry ID.
	 *
	 * @return array
	 * @noinspection PhpUnused
	 */
	public function save_field( $field, $form_data, $entry_id ) {

		if ( empty( $field['type'] ) || $field['type'] !== $this->type ) {
			return $field;
		}

		// Save `value_raw: value` for fields with Other choices.
		// It's necessary for search functionality on the Form Entries Overview table.
		// Admins should be able to search for entries that have used an other value.
		$other_value = $this->get_other_choice_value( $field );

		if ( $other_value !== null ) {
			$field['value'] = $other_value;
		}

		return $field;
	}
}

new WPForms_Field_Radio();
