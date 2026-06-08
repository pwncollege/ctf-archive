<?php

namespace WPForms\Forms\Fields\PaymentSingle;

/**
 * Single item payment field.
 *
 * @since 1.8.2
 */
class Field extends \WPForms_Field {

	/**
	 * User field format.
	 *
	 * @since 1.8.2
	 *
	 * @var string
	 */
	const FORMAT_USER = 'user';

	/**
	 * Single field format.
	 *
	 * @since 1.8.2
	 *
	 * @var string
	 */
	const FORMAT_SINGLE = 'single';

	/**
	 * Hidden field format.
	 *
	 * @since 1.8.2
	 *
	 * @var string
	 */
	const FORMAT_HIDDEN = 'hidden';

	/**
	 * Minimum price default value.
	 *
	 * @since 1.8.6
	 *
	 * @var int
	 */
	const MIN_PRICE_DEFAULT = 10;

	/**
	 * Primary class constructor.
	 *
	 * @since 1.8.2
	 */
	public function init() {

		// Define field type information.
		$this->name     = esc_html__( 'Single Item', 'wpforms-lite' );
		$this->keywords = esc_html__( 'product, store, ecommerce, pay, payment', 'wpforms-lite' );
		$this->type     = 'payment-single';
		$this->icon     = 'fa-file-o';
		$this->order    = 30;
		$this->group    = 'payment';

		$this->hooks();
	}

	/**
	 * Define additional field hooks.
	 *
	 * @since 1.8.2
	 */
	private function hooks() {

		// Define additional field properties.
		add_filter( "wpforms_field_properties_{$this->type}", [ $this, 'field_properties' ], 5, 3 );

		add_action( 'wpforms_display_field_after', [ $this, 'field_minimum_price_description' ], 10, 2 );
		add_filter( 'wpforms_field_preview_class', [ $this, 'preview_field_class' ], 10, 2 );

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
	public function field_properties( $properties, $field, $form_data ) { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		// Basic IDs.
		$form_id  = absint( $form_data['id'] );
		$field_id = absint( $field['id'] );

		// Set options container (<select>) properties.
		$properties['input_container'] = [
			'class' => [ 'wpforms-payment-price' ],
			'data'  => [],
			'id'    => "wpforms-{$form_id}-field_{$field_id}",
		];

		// User format data and class.
		$field_format = ! empty( $field['format'] ) ? $field['format'] : self::FORMAT_SINGLE;

		if ( $this->is_user_defined( $field ) ) {
			$properties['inputs']['primary']['data']['rule-currency'] = '["$",false]';

			$properties['inputs']['primary']['class'][] = 'wpforms-payment-user-input';

			if ( ! empty( $field['min_price'] ) ) {
				$properties['inputs']['primary']['data']['rule-required-minimum-price'] = wpforms_sanitize_amount( $field['min_price'] );
			}
		}

		// Null 'for' value for label as there no input for it.
		if ( ! $this->is_user_defined( $field ) ) {
			unset( $properties['label']['attr']['for'] );
		}

		$properties['inputs']['primary']['class'][] = 'wpforms-payment-price';

		// Check size.
		if ( ! empty( $field['size'] ) ) {
			$properties['inputs']['primary']['class'][] = 'wpforms-field-' . esc_attr( $field['size'] );
		}

		$required = ! empty( $form_data['fields'][ $field_id ]['required'] );

		if ( $required ) {
			$properties['inputs']['primary']['data']['rule-required-positive-number'] = true;
		}

		// Price.
		if ( ! empty( $field['price'] ) ) {
			$field_value = wpforms_sanitize_amount( $field['price'] );
		} elseif ( $required && $field_format === self::FORMAT_SINGLE ) {
			$field_value = wpforms_format_amount( 0 );
		} else {
			$field_value = '';
		}

		$properties['inputs']['primary']['attr']['value'] = ! empty( $field_value ) ? wpforms_format_amount( $field_value, true ) : $field_value;

		// Single item and hidden format should hide the input field.
		if ( $this->is_hidden( $field ) ) {
			$properties['container']['class'][] = 'wpforms-field-hidden';
			$properties['label']['class'][]     = 'wpforms-hidden';
		}

		if ( $this->is_payment_quantities_enabled( $field ) ) {
			$properties['container']['class'][] = ' wpforms-payment-quantities-enabled';
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

		if ( ! is_string( $raw_value ) ) {
			return $properties;
		}

		if ( ! $this->is_user_defined( $field ) ) {
			return $properties;
		}

		$get_value           = stripslashes( sanitize_text_field( $raw_value ) );
		$get_value           = ! empty( $get_value ) ? wpforms_sanitize_amount( $get_value ) : '';
		$get_value_formatted = ! empty( $get_value ) ? wpforms_format_amount( $get_value ) : '';

		// `primary` by default.
		if (
			! empty( $input ) &&
			isset( $properties['inputs'][ $input ] )
		) {
			$properties['inputs'][ $input ]['attr']['value'] = $get_value_formatted;
		}

		return $properties;
	}

	/**
	 * Field options panel inside the builder.
	 *
	 * @since 1.8.2
	 *
	 * @param array $field Field data and settings.
	 */
	public function field_options( $field ) {
		/*
		 * Basic field options.
		 */

		$this->field_option( 'basic-options', $field, [ 'markup' => 'open' ] );
		$this->field_option( 'label', $field );
		$this->field_option( 'description', $field );
		$this->price_option( $field );
		$this->format_option( $field );
		$this->min_price_option( $field );
		$this->field_option( 'quantity', $field, [ 'hidden' => ! $this->is_single_item( $field ) ] );
		$this->field_option( 'required', $field );
		$this->field_option( 'basic-options', $field, [ 'markup' => 'close' ] );
		$this->field_option( 'advanced-options', $field, [ 'markup' => 'open' ] );
		$this->field_option( 'size', $field );
		$this->price_label_option( $field );
		$visibility = ! empty( $field['format'] ) && $this->is_user_defined( $field ) ? '' : 'wpforms-hidden';
		$this->field_option( 'placeholder', $field, [ 'class' => $visibility ] );

		$this->field_option( 'css', $field );
		$this->field_option( 'label_hide', $field );
		$this->field_option( 'advanced-options', $field, [ 'markup' => 'close' ] );
	}

	/**
	 * Price label option.
	 *
	 * @since 1.8.8
	 *
	 * @param array $field Field Data.
	 *
	 * @return void
	 */
	private function price_label_option( array $field ) {

		// Price display.
		$output = $this->field_element(
			'label',
			$field,
			[
				'slug'    => 'price_label',
				'value'   => esc_html__( 'Price Display', 'wpforms-lite' ),
				'tooltip' => esc_html__( 'Specify how the price is displayed under the product name.', 'wpforms-lite' ),
			],
			false
		);

		$output .= $this->field_element(
			'text',
			$field,
			[
				'slug'  => 'price_label',
				'class' => 'wpforms-single-item-price-label-display',
				'value' => $this->get_single_item_price_label( $field ),
			],
			false
		);

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'price_label',
				'content' => $output,
				'class'   => $this->is_single_item( $field ) ? '' : 'wpforms-hidden',
			]
		);
	}

	/**
	 * Get price label for single item type.
	 *
	 * @since 1.8.8
	 *
	 * @param array $field Field data and settings.
	 */
	private function get_single_item_price_label( array $field ) {

		if ( ! isset( $field['price_label'] ) ) {
			return sprintf( /* translators: %s - Single item field price label. */
				esc_html__( 'Price: %s', 'wpforms-lite' ),
				'{price}'
			);
		}

		return $field['price_label'];
	}

	/**
	 * Field price option.
	 *
	 * @since 1.8.6
	 *
	 * @param array $field Field data and settings.
	 */
	private function price_option( $field ) {

		$price   = ! empty( $field['price'] ) ? wpforms_format_amount( wpforms_sanitize_amount( $field['price'] ) ) : '';
		$tooltip = esc_html__( 'Enter the price of the item, without a currency symbol.', 'wpforms-lite' );

		$output = $this->field_element(
			'label',
			$field,
			[
				'slug'    => 'price',
				'value'   => esc_html__( 'Item Price', 'wpforms-lite' ),
				'tooltip' => $tooltip,
			],
			false
		);

		$output .= $this->field_element(
			'text',
			$field,
			[
				'slug'        => 'price',
				'value'       => $price,
				'class'       => 'wpforms-money-input',
				'placeholder' => wpforms_format_amount( 0 ),
			],
			false
		);

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'price',
				'content' => $output,
			]
		);
	}

	/**
	 * Field format option.
	 *
	 * @since 1.8.6
	 *
	 * @param array $field Field data and settings.
	 */
	private function format_option( $field ) {

		$format  = ! empty( $field['format'] ) ? esc_attr( $field['format'] ) : self::FORMAT_SINGLE;
		$tooltip = esc_html__( 'Select the item type.', 'wpforms-lite' );
		$options = [
			self::FORMAT_SINGLE => esc_html__( 'Single Item', 'wpforms-lite' ),
			self::FORMAT_USER   => esc_html__( 'User Defined', 'wpforms-lite' ),
			self::FORMAT_HIDDEN => esc_html__( 'Hidden', 'wpforms-lite' ),
		];

		$output = $this->field_element(
			'label',
			$field,
			[
				'slug'    => 'format',
				'value'   => esc_html__( 'Item Type', 'wpforms-lite' ),
				'tooltip' => $tooltip,
			],
			false
		);

		$output .= $this->field_element(
			'select',
			$field,
			[
				'slug'    => 'format',
				'value'   => $format,
				'options' => $options,
			],
			false
		);

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'format',
				'content' => $output,
			]
		);
	}

	/**
	 * Field minimum price option.
	 *
	 * @since 1.8.6
	 *
	 * @param array $field Field data and settings.
	 */
	private function min_price_option( $field ) {

		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( isset( $_POST['action'] ) && $_POST['action'] === 'wpforms_new_field_payment-single' ) {
			// Use a default minimum price when adding new field.
			$min_price = wpforms_format_amount( self::MIN_PRICE_DEFAULT );
		} elseif ( isset( $field['min_price'] ) ) {
			// Use saved minimum price if it exists.
			$min_price = wpforms_format_amount( wpforms_sanitize_amount( $field['min_price'] ) );
		} else {
			// Use 0 as a fallback for old forms.
			$min_price = 0;
		}

		$tooltip   = esc_html__( 'Enter the minimum price of the item, without a currency symbol.', 'wpforms-lite' );
		$is_hidden = empty( $field['format'] ) || ! $this->is_user_defined( $field ) ? 'wpforms-hidden' : '';

		$output = $this->field_element(
			'label',
			$field,
			[
				'slug'    => 'min_price',
				'value'   => esc_html__( 'Minimum Price', 'wpforms-lite' ),
				'tooltip' => $tooltip,
			],
			false
		);

		$output .= $this->field_element(
			'text',
			$field,
			[
				'slug'  => 'min_price',
				'value' => $min_price,
				'data'  =>
					[
						'minimum-price' => self::MIN_PRICE_DEFAULT,
					],
				'class' => 'wpforms-money-input',
			],
			false
		);

		$notice           = sprintf( /* translators: %1$s - the default minimum price. */
			esc_html__( 'Requiring a minimum price of at least %1$s helps protect you against card testing by fraudsters.', 'wpforms-lite' ),
			esc_html( wpforms_format_amount( self::MIN_PRICE_DEFAULT, true ) )
		);
		$is_notice_hidden = $this->is_min_price_passed( $field ) || $is_hidden ? 'wpforms-hidden' : '';

		$output .= sprintf(
			'<div class="wpforms-alert-warning wpforms-alert wpforms-item-minimum-price-alert %1$s">
				<h4>%2$s</h4>
				<p>%3$s</p>
			</div>',
			esc_attr( $is_notice_hidden ),
			esc_html__( 'Security Recommendation', 'wpforms-lite' ),
			$notice
		);

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'min_price',
				'content' => $output,
				'class'   => $is_hidden,
			]
		);
	}

	/**
	 * Field preview inside the builder.
	 *
	 * @since 1.8.2
	 *
	 * @param array $field Field data and settings.
	 */
	public function field_preview( $field ) {

		$price        = ! empty( $field['price'] ) ? wpforms_format_amount( wpforms_sanitize_amount( $field['price'] ), true ) : wpforms_format_amount( 0, true );
		$min_price    = ! empty( $field['min_price'] ) ? wpforms_format_amount( wpforms_sanitize_amount( $field['min_price'] ), true ) : wpforms_format_amount( self::MIN_PRICE_DEFAULT, true );
		$placeholder  = ! empty( $field['placeholder'] ) ? $field['placeholder'] : wpforms_format_amount( 0 );
		$format       = ! empty( $field['format'] ) ? $field['format'] : self::FORMAT_SINGLE;
		$value        = ! empty( $field['price'] ) ? wpforms_format_amount( wpforms_sanitize_amount( $field['price'] ) ) : '';
		$is_single    = $this->is_single_item( $field );
		$single_label = str_replace( '{price}', '<span class="price">' . esc_html( $price ) . '</span>', wp_kses( $this->get_single_item_price_label( $field ), wpforms_builder_preview_get_allowed_tags() ) );

		$this->field_preview_option( 'label', $field );

		echo '<div class="format-selected-' . esc_attr( $format ) . ' format-selected">';

		$hidden = ! $is_single ? 'wpforms-hidden' : '';

		echo '<p class="item-price item-price-single ' . esc_attr( $hidden ) . '">';
		echo wp_kses(
			'<span class="price-label">' . $single_label . '</span>',
			[
				'span' => [
					'class' => [],
				],
			]
		);

		echo '</p>';

		$hidden = ! $this->is_hidden( $field ) ? 'wpforms-hidden' : '';

		echo '<p class="item-price item-price-hidden ' . esc_attr( $hidden ) . '">';
		printf(
			wp_kses( /* translators: %1$s - Item Price value. */
				__( 'Price: <span class="price">%1$s</span>', 'wpforms-lite' ),
				[
					'span' => [
						'class' => [],
					],
				]
			),
			esc_html( $price )
		);

		echo '</p>';

		$hidden = ! $is_single ? 'wpforms-hidden' : '';

		$this->field_preview_option( 'quantity', $field, [ 'class' => $hidden ] );

		echo '<div class="single-item-user-defined-block">';

		printf(
			'<input type="text" placeholder="%s" class="primary-input" value="%s" readonly>',
			esc_attr( $placeholder ),
			esc_attr( $value )
		);

		$hidden = $this->is_min_price_passed( $field ) ? 'wpforms-hidden' : '';

		echo '<i class="fa fa-exclamation-triangle ' . esc_attr( $hidden ) . '"></i>';

		echo '</div>';

		$this->field_preview_option( 'description', $field );

		$hidden = ! isset( $field['min_price'] ) || empty( (float) wpforms_sanitize_amount( $field['min_price'] ) ) ? 'wpforms-hidden' : '';

		echo '<div class="item-min-price ' . esc_attr( $hidden ) . '">';
		printf(
			wp_kses( /* translators: %1$s - Minimum Price value. */
				__( 'Minimum Price: <span class="min-price">%1$s</span>', 'wpforms-lite' ),
				[
					'span' => [
						'class' => [],
					],
				]
			),
			esc_html( $min_price )
		);
		echo '</div>';

		echo '<p class="item-price-hidden-note">';
		esc_html_e( 'Note: Item type is set to hidden and will not be visible when viewing the form.', 'wpforms-lite' );
		echo '</p>';

		echo '</div>';
	}

	/**
	 * Field display on the form front-end.
	 *
	 * @since 1.8.2
	 *
	 * @param array $field      Field data and settings.
	 * @param array $deprecated Deprecated field attributes.
	 * @param array $form_data  Form data and settings.
	 */
	public function field_display( $field, $deprecated, $form_data ) {

		// Shortcut for easier access.
		$primary      = $field['properties']['inputs']['primary'];
		$field_format = ! empty( $field['format'] ) ? $field['format'] : self::FORMAT_SINGLE;

		// Placeholder attribute is only applicable to password, search, tel, text and url inputs, not hidden.
		// aria-errormessage attribute is not allowed for hidden inputs.
		if ( ! $this->is_user_defined( $field ) ) {
			unset( $primary['attr']['placeholder'], $primary['attr']['aria-errormessage'] );
		}

		switch ( $field_format ) {
			case self::FORMAT_SINGLE:
			case self::FORMAT_HIDDEN:
				if ( $field_format === self::FORMAT_SINGLE ) {
					$price       = ! empty( $field['price'] ) ? $field['price'] : 0;
					$field_label = str_replace( '{price}', '<span class="wpforms-price">' . esc_html( wpforms_format_amount( wpforms_sanitize_amount( $price ), true ) ) . '</span>', $this->get_single_item_price_label( $field ) );

					echo '<div class="wpforms-single-item-price-content">';
					echo '<div class="wpforms-single-item-price ' . wpforms_sanitize_classes( $primary['class'], true ) . '">';
					echo wp_kses(
						$field_label,
						[
							'span' => [
								'class' => [],
							],
						]
					);
					echo '</div>';

					$this->display_quantity_dropdown( $field );

					echo '</div>';
				}

				// Primary price field.
				printf(
					'<input type="hidden" %s>',
					wpforms_html_attributes( $primary['id'], $primary['class'], $primary['data'], $primary['attr'] ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				);
				break;

			case self::FORMAT_USER:
				printf(
					'<input type="text" %s>',
					wpforms_html_attributes( $primary['id'], $primary['class'], $primary['data'], $primary['attr'] ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				);
				break;

			default:
				break;
		}
	}

	/**
	 * Validate field on form submit.
	 *
	 * @since 1.8.2
	 *
	 * @param int    $field_id     Field ID.
	 * @param string $field_submit Submitted field value (raw data).
	 * @param array  $form_data    Form data and settings.
	 */
	public function validate( $field_id, $field_submit, $form_data ) {

		$is_required = ! empty( $form_data['fields'][ $field_id ]['required'] );

		// If field is required, check for data.
		if (
			empty( $field_submit ) &&
			$is_required
		) {
			wpforms()->obj( 'process' )->errors[ $form_data['id'] ][ $field_id ] = wpforms_get_required_label();

			return;
		}

		/**
		 * Whether to validate amount or not of the Payment Single item field.
		 *
		 * @since 1.8.4
		 *
		 * @param bool   $validate     Whether to validate amount or not. Default true.
		 * @param int    $field_id     Field ID.
		 * @param string $field_submit Field data submitted by a user.
		 * @param array  $form_data    Form data and settings.
		 */
		$validate_amount = apply_filters( 'wpforms_forms_fields_payment_single_field_validate_amount', true, $field_id, $field_submit, $form_data );

		// If field format is not user provided, validate the amount posted.
		if (
			! empty( $field_submit ) &&
			$validate_amount &&
			! $this->is_user_defined( $form_data['fields'][ $field_id ] )
		) {

			$price  = wpforms_sanitize_amount( $form_data['fields'][ $field_id ]['price'] );
			$submit = wpforms_sanitize_amount( $field_submit );

			if ( $price !== $submit ) {
				wpforms()->obj( 'process' )->errors[ $form_data['id'] ][ $field_id ] = esc_html__( 'Amount mismatch', 'wpforms-lite' );
			}
		}

		// If field format is provided by user, additionally compare the amount with a minimum price.
		if (
			! empty( $field_submit ) &&
			$validate_amount &&
			$this->is_user_defined( $form_data['fields'][ $field_id ] )
		) {
			$submit = wpforms_sanitize_amount( $field_submit );

			if ( $submit < 0 ) {
				wpforms()->obj( 'process' )->errors[ $form_data['id'] ][ $field_id ] = esc_html__( 'Amount can\'t be negative' , 'wpforms-lite' );
			}

			if ( empty( $form_data['fields'][ $field_id ]['min_price'] ) && ! $is_required ) {
				return;
			}

			$min_price = wpforms_sanitize_amount( $form_data['fields'][ $field_id ]['min_price'] );

			if ( $submit < $min_price ) {
				wpforms()->obj( 'process' )->errors[ $form_data['id'] ][ $field_id ] = esc_html__( 'Amount can\'t be less than the required minimum.' , 'wpforms-lite' );
			}
		}
	}

	/**
	 * Format and sanitize field.
	 *
	 * @since 1.8.2
	 *
	 * @param int    $field_id     Field ID.
	 * @param string $field_submit Field data submitted by a user.
	 * @param array  $form_data    Form data and settings.
	 */
	public function format( $field_id, $field_submit, $form_data ) {

		$field = $form_data['fields'][ $field_id ];
		$name  = ! empty( $field['label'] ) ? sanitize_text_field( $field['label'] ) : '';

		// Only trust the value if the field has the user defined format OR it is the entry preview.
		if ( $this->is_user_defined( $field ) || wpforms_is_ajax( 'wpforms_get_entry_preview' ) ) {
			$amount = wpforms_sanitize_amount( $field_submit );
		} else {
			$amount = wpforms_sanitize_amount( $field['price'] );
		}

		$field_data = [
			'name'       => $name,
			'value'      => wpforms_format_amount( $amount, true ),
			'amount'     => wpforms_format_amount( $amount ),
			'amount_raw' => $amount,
			'currency'   => wpforms_get_currency(),
			'id'         => absint( $field_id ),
			'type'       => sanitize_key( $this->type ),
		];

		if ( $this->is_payment_quantities_enabled( $field ) ) {
			$field_data['quantity'] = $this->get_submitted_field_quantity( $field, $form_data );
		}

		wpforms()->obj( 'process' )->fields[ $field_id ] = $field_data;
	}

	/**
	 * Display the minimum price description for the field.
	 *
	 * @since 1.8.6
	 *
	 * @param array $field     Field data and settings.
	 * @param array $form_data Form data and settings.
	 */
	public function field_minimum_price_description( $field, $form_data ) {

		if ( ! $this->is_user_defined( $field ) || ! isset( $field['min_price'] ) || empty( (float) wpforms_sanitize_amount( $field['min_price'] ) ) ) {
			return;
		}

		$description = sprintf( /* translators: %1$s - Minimum Price value. */
			__( 'Minimum Price: %1$s', 'wpforms-lite' ),
			wpforms_format_amount( wpforms_sanitize_amount( $field['min_price'] ), true )
		);

		printf(
			'<div class="wpforms-field-description">%s</div>',
			esc_html( $description )
		);
	}

	/**
	 * Add class to the builder field preview.
	 *
	 * @since 1.8.6
	 *
	 * @param string $css   Class names.
	 * @param array  $field Field properties.
	 *
	 * @return string
	 */
	public function preview_field_class( $css, $field ) {

		$css = parent::preview_field_class( $css, $field );

		if ( $field['type'] !== $this->type ) {
			return $css;
		}

		if ( ! $this->is_user_defined( $field ) ) {
			return $css;
		}

		if ( $this->is_min_price_passed( $field ) ) {
			return $css;
		}

		$css .= ' min-price-warning';

		return $css;
	}

	/**
	 * Define if format of field is User Defined.
	 *
	 * @since 1.8.6
	 *
	 * @param array $field Field data.
	 *
	 * @return bool
	 */
	private function is_user_defined( $field ) {

		return ! empty( $field['format'] ) && $field['format'] === self::FORMAT_USER;
	}

	/**
	 * Define if format of field is Single Item.
	 *
	 * @since 1.8.7
	 *
	 * @param array $field Field data.
	 *
	 * @return bool
	 */
	private function is_single_item( $field ) {

		return empty( $field['format'] ) || $field['format'] === self::FORMAT_SINGLE;
	}

	/**
	 * Define if format of field is Hidden.
	 *
	 * @since 1.8.8
	 *
	 * @param array $field Field data.
	 *
	 * @return bool
	 */
	private function is_hidden( $field ) {

		return empty( $field['format'] ) || $field['format'] === self::FORMAT_HIDDEN;
	}

	/**
	 * Define if minimum price is equal or more than default one.
	 *
	 * @since 1.8.6
	 *
	 * @param array $field Field data.
	 *
	 * @return bool
	 */
	private function is_min_price_passed( $field ) {

		return isset( $field['min_price'] ) && (float) wpforms_sanitize_amount( $field['min_price'] ) >= (float) self::MIN_PRICE_DEFAULT;
	}
}
