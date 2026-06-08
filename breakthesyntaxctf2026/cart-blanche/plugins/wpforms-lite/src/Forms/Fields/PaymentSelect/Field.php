<?php

namespace WPForms\Forms\Fields\PaymentSelect;

use WPForms_Field;

/**
 * Dropdown payment field.
 *
 * @since 1.8.2
 */
class Field extends WPForms_Field {

	/**
	 * Classic (old) style.
	 *
	 * @since 1.8.2
	 *
	 * @var string
	 */
	public const STYLE_CLASSIC = 'classic';

	/**
	 * Modern style.
	 *
	 * @since 1.8.2
	 *
	 * @var string
	 */
	public const STYLE_MODERN = 'modern';

	/**
	 * Primary class constructor.
	 *
	 * @since 1.8.2
	 */
	public function init() {

		// Define field type information.
		$this->name     = esc_html__( 'Dropdown Items', 'wpforms-lite' );
		$this->keywords = esc_html__( 'product, store, ecommerce, pay, payment', 'wpforms-lite' );
		$this->type     = 'payment-select';
		$this->icon     = 'fa-caret-square-o-down';
		$this->order    = 70;
		$this->group    = 'payment';
		$this->defaults = [
			1 => [
				'label'   => esc_html__( 'First Item', 'wpforms-lite' ),
				'value'   => '10',
				'default' => '',
			],
			2 => [
				'label'   => esc_html__( 'Second Item', 'wpforms-lite' ),
				'value'   => '25',
				'default' => '',
			],
			3 => [
				'label'   => esc_html__( 'Third Item', 'wpforms-lite' ),
				'value'   => '50',
				'default' => '',
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
	 * @since 1.8.2
	 */
	private function hooks() {

		// Define additional field properties.
		add_filter( "wpforms_field_properties_{$this->type}", [ $this, 'field_properties' ], 5, 3 );

		// Form frontend CSS enqueues.
		add_action( 'wpforms_frontend_css', [ $this, 'enqueue_frontend_css' ] );

		// Form frontend JS enqueues.
		add_action( 'wpforms_frontend_js', [ $this, 'enqueue_frontend_js' ] );

		// Customize HTML field value.
		add_filter( 'wpforms_html_field_value', [ $this, 'field_html_value' ], 10, 4 );
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
	public function field_properties( $properties, $field, $form_data ) {

		// Remove primary input.
		unset( $properties['inputs']['primary'] );

		// Define data.
		$form_id  = absint( $form_data['id'] );
		$field_id = absint( $field['id'] );
		$choices  = $field['choices'];

		// Set options container (<select>) properties.
		$properties['input_container'] = [
			'class' => [ 'wpforms-payment-price' ],
			'data'  => [],
			'id'    => "wpforms-{$form_id}-field_{$field_id}",
			'attr'  => [
				'name' => "wpforms[fields][{$field_id}]",
			],
		];

		// Set properties.
		foreach ( $choices as $key => $choice ) {

			$properties['inputs'][ $key ] = [
				'container' => [
					'attr'  => [],
					'class' => [ "choice-{$key}" ],
					'data'  => [],
					'id'    => '',
				],
				'label'     => [
					'attr'  => [
						'for' => "wpforms-{$form_id}-field_{$field_id}_{$key}",
					],
					'class' => [ 'wpforms-field-label-inline' ],
					'data'  => [],
					'id'    => '',
					'text'  => $choice['label'],
				],
				'attr'      => [
					'value' => $choice['value'],
					'data'  => [
						'amount' => wpforms_format_amount( wpforms_sanitize_amount( $choice['value'] ) ),
					],
				],
				'class'     => [],
				'data'      => [],
				'id'        => "wpforms-{$form_id}-field_{$field_id}_{$key}",
				'required'  => ! empty( $field['required'] ) ? 'required' : '',
				'default'   => isset( $choice['default'] ),
			];
		}

		// Add a class that changes the field size.
		if ( ! empty( $field['size'] ) ) {
			$properties['input_container']['class'][] = 'wpforms-field-' . esc_attr( $field['size'] );
		}

		// Required class for pagebreak validation.
		if ( ! empty( $field['required'] ) ) {
			$properties['input_container']['class'][] = 'wpforms-field-required';
		}

		// Add additional class for container.
		if (
			! empty( $field['style'] ) &&
			in_array( $field['style'], [ self::STYLE_CLASSIC, self::STYLE_MODERN ], true )
		) {
			$properties['container']['class'][] = "wpforms-field-select-style-{$field['style']}";
		}

		if ( $this->is_payment_quantities_enabled( $field ) ) {
			$properties['container']['class'][] = ' wpforms-payment-quantities-enabled';
		}

		return $properties;
	}

	/**
	 * Get the value, that is used to prefill via dynamic or fallback population.
	 * Based on field data and current properties.
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
		 * When the form is submitted, we get from Fallback only values (choice ID).
		 * As payment-dropdown field doesn't support 'show_values' option -
		 * we should transform value into label to check against using general logic in parent method.
		 */

		if (
			! is_string( $raw_value ) ||
			empty( $field['choices'] ) ||
			! is_array( $field['choices'] )
		) {
			return $properties;
		}

		// The form submits only the choice ID, so shortcut for Dynamic when we have a label there.
		if ( ! is_numeric( $raw_value ) ) {
			return parent::get_field_populated_single_property_value( $raw_value, $input, $properties, $field );
		}

		if (
			! empty( $field['choices'][ $raw_value ]['label'] ) &&
			! empty( $field['choices'][ $raw_value ]['value'] )
		) {
			return parent::get_field_populated_single_property_value( $field['choices'][ $raw_value ]['label'], $input, $properties, $field );
		}

		return $properties;
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
		$this->field_option( 'basic-options', $field, [ 'markup' => 'open' ] );

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

		// Quantity.
		$this->field_option( 'quantity', $field );

		// Description.
		$this->field_option( 'description', $field );

		// Required toggle.
		$this->field_option( 'required', $field );

		// Options close markup.
		$this->field_option( 'basic-options', $field, [ 'markup' => 'close' ] );

		/*
		 * Advanced field options.
		 */

		// Options open markup.
		$this->field_option( 'advanced-options', $field, [ 'markup' => 'open' ] );

		// Style.
		$lbl = $this->field_element(
			'label',
			$field,
			[
				'slug'    => 'style',
				'value'   => esc_html__( 'Style', 'wpforms-lite' ),
				'tooltip' => esc_html__( 'Classic style is the default one generated by your browser. Modern has a fresh look and displays all selected options in a single row.', 'wpforms-lite' ),
			],
			false
		);

		$fld = $this->field_element(
			'select',
			$field,
			[
				'slug'    => 'style',
				'value'   => ! empty( $field['style'] ) ? $field['style'] : self::STYLE_CLASSIC,
				'options' => [
					self::STYLE_CLASSIC => esc_html__( 'Classic', 'wpforms-lite' ),
					self::STYLE_MODERN  => esc_html__( 'Modern', 'wpforms-lite' ),
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
			]
		);

		// Size.
		$this->field_option( 'size', $field );

		// Placeholder.
		$this->field_option( 'placeholder', $field );

		// Custom CSS classes.
		$this->field_option( 'css', $field );

		// Hide label.
		$this->field_option( 'label_hide', $field );

		// Options close markup.
		$this->field_option( 'advanced-options', $field, [ 'markup' => 'close' ] );
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

		// Prepare arguments.
		$args['modern'] = false;

		if (
			! empty( $field['style'] ) &&
			$field['style'] === self::STYLE_MODERN
		) {
			$args['modern'] = true;
			$args['class']  = 'choicesjs-select';
		}

		// Choices.
		$this->field_preview_option( 'choices', $field, $args );

		// Quantity.
		$this->field_preview_option( 'quantity', $field );

		// Description.
		$this->field_preview_option( 'description', $field );
	}

	/**
	 * Field display on the form front-end.
	 *
	 * @since 1.8.2
	 *
	 * @param array $field      Field data and settings.
	 * @param array $deprecated Deprecated array of field attributes.
	 * @param array $form_data  Form data and settings.
	 *
	 * @noinspection HtmlUnknownAttribute*/
	public function field_display( $field, $deprecated, $form_data ) { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		$container         = $field['properties']['input_container'];
		$field_placeholder = ! empty( $field['placeholder'] ) ? $field['placeholder'] : '';
		$is_modern         = ! empty( $field['style'] ) && $field['style'] === self::STYLE_MODERN;
		$choices           = $field['properties']['inputs'];

		if ( ! empty( $field['required'] ) ) {
			$container['attr']['required'] = 'required';
		}

		// Add a class for Choices.js initialization.
		if ( $is_modern ) {
			$container['class'][] = 'choicesjs-select';

			// Add a size-class to data attribute - it is used when Choices.js is initialized.
			if ( ! empty( $field['size'] ) ) {
				$container['data']['size-class'] = 'wpforms-field-row wpforms-field-' . sanitize_html_class( $field['size'] );
			}

			$container['data']['search-enabled'] = $this->is_choicesjs_search_enabled( count( $choices ) );
		}

		$has_default = false;

		// Check to see if any of the options were selected by default.
		foreach ( $choices as $choice ) {
			if ( ! empty( $choice['default'] ) ) {
				$has_default = true;

				break;
			}
		}

		// Preselect default if no other choices were marked as default.
		printf(
			'<select %s>',
			wpforms_html_attributes( $container['id'], $container['class'], $container['data'], $container['attr'] ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		);

		// Optional placeholder.
		if ( ! empty( $field_placeholder ) || $is_modern ) {
			printf(
				'<option value="" class="placeholder" disabled %s>%s</option>',
				selected( false, $has_default, false ),
				esc_html( $field_placeholder )
			);
		}

		// Format string for option.
		if ( $is_modern ) {
			// The `data-custom-properties` is a Choices.js attribute, and it stores a copy of `data-amount` attribute.
			$option_format = '<option value="%1$s" data-amount="%2$s" data-custom-properties="%2$s" %3$s>%4$s</option>';
		} else {
			$option_format = '<option value="%1$s" data-amount="%2$s" %3$s>%4$s</option>';
		}

		// Build the select options.
		foreach ( $choices as $key => $choice ) {
			$amount = wpforms_format_amount( wpforms_sanitize_amount( $choice['attr']['value'] ) );
			$label  = $choice['label']['text'] ?? '';

			/* translators: %s - item number. */
			$label = $label !== '' ? $label : sprintf( esc_html__( 'Item %s', 'wpforms-lite' ), $key );

			$label .= ! empty( $field['show_price_after_labels'] ) && isset( $choice['attr']['value'] ) ? ' - ' . wpforms_format_amount( wpforms_sanitize_amount( $choice['attr']['value'] ), true ) : '';

			printf(
				$option_format, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				esc_attr( $key ),
				esc_attr( $amount ),
				selected( true, ! empty( $choice['default'] ), false ),
				esc_html( $label )
			);
		}

		echo '</select>';

		$this->display_quantity_dropdown( $field );
	}

	/**
	 * Validate field on submitting the form.
	 *
	 * @since 1.8.2
	 *
	 * @param int    $field_id     Field ID.
	 * @param string $field_submit Submitted field value (raw data).
	 * @param array  $form_data    Form data and settings.
	 */
	public function validate( $field_id, $field_submit, $form_data ) {

		// Basic required check - If field is marked as required, check for entry data.
		if ( ! empty( $form_data['fields'][ $field_id ]['required'] ) && empty( $field_submit ) ) {

			wpforms()->obj( 'process' )->errors[ $form_data['id'] ][ $field_id ] = wpforms_get_required_label();
		}

		// Validate that the option selected is real.
		if ( ! empty( $field_submit ) && empty( $form_data['fields'][ $field_id ]['choices'][ $field_submit ] ) ) {

			wpforms()->obj( 'process' )->errors[ $form_data['id'] ][ $field_id ] = esc_html__( 'Invalid payment option', 'wpforms-lite' );
		}
	}

	/**
	 * Format and sanitize field.
	 *
	 * @since 1.8.2
	 *
	 * @param int    $field_id     Field ID.
	 * @param string $field_submit Submitted field value (selected option).
	 * @param array  $form_data    Form data and settings.
	 */
	public function format( $field_id, $field_submit, $form_data ) {

		$choice_label = '';
		$field        = $form_data['fields'][ $field_id ];
		$name         = ! empty( $field['label'] ) ? sanitize_text_field( $field['label'] ) : '';

		// Fetch the amount.
		if ( ! empty( $field['choices'][ $field_submit ]['value'] ) ) {
			$amount = wpforms_sanitize_amount( $field['choices'][ $field_submit ]['value'] );
		} else {
			$amount = 0;
		}

		$value = wpforms_format_amount( $amount, true );

		if ( empty( $field_submit ) ) {
			$value = '';
		} elseif ( ! empty( $field['choices'][ $field_submit ]['label'] ) ) {
			$choice_label = sanitize_text_field( $field['choices'][ $field_submit ]['label'] );
			$value        = $choice_label . ' - ' . $value;
		}

		$field_data = [
			'name'         => $name,
			'value'        => $value,
			'value_choice' => $choice_label,
			'value_raw'    => sanitize_text_field( $field_submit ),
			'amount'       => wpforms_format_amount( $amount ),
			'amount_raw'   => $amount,
			'currency'     => wpforms_get_currency(),
			'id'           => absint( $field_id ),
			'type'         => sanitize_key( $this->type ),
		];

		if ( $this->is_payment_quantities_enabled( $field ) ) {
			$field_data['quantity'] = $this->get_submitted_field_quantity( $field, $form_data );
		}

		wpforms()->obj( 'process' )->fields[ $field_id ] = $field_data;
	}

	/**
	 * Form frontend CSS enqueues.
	 *
	 * @since 1.8.2
	 *
	 * @param array $forms Forms on the current page.
	 */
	public function enqueue_frontend_css( $forms ) {

		$has_modern_select = false;

		foreach ( $forms as $form ) {
			if ( $this->is_field_style( $form, self::STYLE_MODERN ) ) {
				$has_modern_select = true;

				break;
			}
		}

		if ( $has_modern_select || wpforms()->obj( 'frontend' )->assets_global() ) {
			$min = wpforms_get_min_suffix();

			wp_enqueue_style(
				'wpforms-choicesjs',
				WPFORMS_PLUGIN_URL . "assets/css/choices{$min}.css",
				[],
				'10.2.0'
			);
		}
	}

	/**
	 * Form frontend JS enqueues.
	 *
	 * @since 1.8.2
	 *
	 * @param array $forms Forms on the current page.
	 */
	public function enqueue_frontend_js( $forms ) {

		$has_modern_select = false;

		foreach ( $forms as $form ) {
			if ( $this->is_field_style( $form, self::STYLE_MODERN ) ) {
				$has_modern_select = true;

				break;
			}
		}

		if ( $has_modern_select || wpforms()->obj( 'frontend' )->assets_global() ) {
			$this->enqueue_choicesjs_once( $forms );
		}
	}

	/**
	 * Whether the provided form has a dropdown field with a specified style.
	 *
	 * @since 1.8.2
	 *
	 * @param array  $form  Form data.
	 * @param string $style Desired field style.
	 *
	 * @return bool
	 */
	protected function is_field_style( $form, $style ) {

		$is_field_style = false;

		if ( empty( $form['fields'] ) ) {
			return false;
		}

		foreach ( (array) $form['fields'] as $field ) {
			if (
				! empty( $field['type'] ) &&
				$field['type'] === $this->type &&
				! empty( $field['style'] ) &&
				sanitize_key( $style ) === $field['style']
			) {
				$is_field_style = true;

				break;
			}
		}

		return $is_field_style;
	}

	/**
	 * Get field name for an ajax error message.
	 *
	 * @since        1.8.2
	 *
	 * @param string|mixed    $name  Field name for error triggered.
	 * @param array           $field Field settings.
	 * @param array           $props List of properties.
	 * @param string|string[] $error Error message.
	 *
	 * @return string
	 * @noinspection PhpMissingReturnTypeInspection
	 * @noinspection ReturnTypeCanBeDeclaredInspection
	 */
	public function ajax_error_field_name( $name, $field, $props, $error ) {

		$name = (string) $name;

		if ( ! isset( $field['type'] ) || $field['type'] !== $this->type ) {
			return $name;
		}

		return $props['input_container']['attr']['name'] ?? '';
	}
}
