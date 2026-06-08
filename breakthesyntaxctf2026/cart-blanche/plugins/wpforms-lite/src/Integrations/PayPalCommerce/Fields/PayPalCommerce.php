<?php

namespace WPForms\Integrations\PayPalCommerce\Fields;

use WPForms_Field;
use WPForms\Integrations\PayPalCommerce\Connection;
use WPForms\Integrations\PayPalCommerce\Helpers;
use WPForms\Integrations\PayPalCommerce\Integrations\Loader;

/**
 * PayPal Commerce credit card field.
 *
 * @since 1.10.0
 */
class PayPalCommerce extends WPForms_Field {

	/**
	 * Integrations loader instance.
	 *
	 * @since 1.10.0
	 *
	 * @var Loader
	 */
	private $integrations;

	/**
	 * Determine if a new field was added.
	 *
	 * @since 1.10.0
	 *
	 * @var bool
	 */
	private $is_new_field = false;

	/**
	 * Primary class constructor.
	 *
	 * @since 1.10.0
	 */
	public function init(): void {

		// Define field type information.
		$this->name     = esc_html__( 'PayPal Commerce', 'wpforms-lite' );
		$this->keywords = esc_html__( 'store, ecommerce, credit card, pay, payment, debit card', 'wpforms-lite' );
		$this->type     = 'paypal-commerce';
		$this->icon     = 'fa-credit-card';
		$this->order    = 89;
		$this->group    = 'payment';

		$this->integrations = new Loader();

		$this->hooks();
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.10.0
	 */
	private function hooks(): void {

		add_filter( 'wpforms_field_properties_paypal-commerce', [ $this, 'field_properties' ], 5, 3 );
		add_filter( 'wpforms_field_new_required', [ $this, 'default_required' ], 10, 2 );
		add_filter( 'wpforms_builder_field_button_attributes', [ $this, 'field_button_atts' ], 10, 3 );
		add_filter( 'wpforms_field_new_display_duplicate_button', [ $this, 'field_display_duplicate_button' ], 10, 2 );
		add_filter( 'wpforms_field_preview_display_duplicate_button', [ $this, 'field_display_duplicate_button' ], 10, 2 );
		add_filter( 'wpforms_pro_fields_entry_preview_is_field_support_preview_paypal-commerce_field', [ $this, 'entry_preview_availability' ], 10, 4 );
		add_filter( 'wpforms_field_display_sublabel_for', [ $this, 'modify_sublabel_for' ], 10, 3 );
		add_filter( 'wpforms_frontend_foot_submit_classes', [ $this, 'submit_button_classes' ], 10, 2 );
		add_action( 'wpforms_display_submit_after', [ $this, 'submit_button' ], 9, 2 );
	}

	/**
	 * Define additional field properties.
	 *
	 * @since 1.10.0
	 *
	 * @param array $properties Field properties.
	 * @param array $field      Field settings.
	 * @param array $form_data  Form data and settings.
	 *
	 * @return array
	 *
	 * @noinspection PhpMissingParamTypeInspection
	 */
	public function field_properties( $properties, array $field, array $form_data ): array { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		$properties = (array) $properties;

		// Remove primary for expanded formats since we have first, middle, last.
		// Remove for attribute from the label as there is no id for it.
		unset( $properties['inputs']['primary'], $properties['label']['attr']['for'] );

		if ( ! isset( $field['credit_card'] ) ) {
			$is_fastlane_only = isset( $field['fastlane'] ) && empty( $field['paypal_checkout'] );

			$properties['container']['class'][] = 'wpforms-field-paypal-commerce';
			$properties['label']['disabled']    = ! $is_fastlane_only && empty( $field['description'] );

			return $properties;
		}

		$default_labels        = $this->get_default_labels();
		$form_id               = absint( $form_data['id'] );
		$field_id              = absint( $field['id'] );
		$is_card_holder_enable = isset( $field['card_holder_enable'] );

		$props = [
			'inputs' => [
				'number' => [
					'attr'     => [
						'name'  => '',
						'value' => '',
					],
					'block'    => [
						'wpforms-field-paypal-commerce-number',
					],
					'class'    => [
						'wpforms-field-paypal-commerce-cardnumber',
					],
					'data'     => [],
					'id'       => "wpforms-{$form_id}-field_{$field_id}-cardnumber",
					'required' => ! empty( $field['required'] ) ? 'required' : '',
					'sublabel' => [
						'hidden'   => ! empty( $field['sublabel_hide'] ),
						'value'    => ! empty( $field['card_number'] ) ? esc_html( $field['card_number'] ) : $default_labels['card_number'],
						'position' => 'after',
					],
				],
				'date'   => [
					'attr'     => [
						'name'  => '',
						'value' => '',
					],
					'block'    => [
						'wpforms-field-paypal-commerce-date',
					],
					'class'    => [
						'wpforms-field-paypal-commerce-carddate',
					],
					'data'     => [],
					'id'       => "wpforms-{$form_id}-field_{$field_id}-carddate",
					'required' => ! empty( $field['required'] ) ? 'required' : '',
					'sublabel' => [
						'hidden'   => ! empty( $field['sublabel_hide'] ),
						'value'    => ! empty( $field['expiration_date'] ) ? esc_html( $field['expiration_date'] ) : $default_labels['expiration_date'],
						'position' => 'after',
					],
				],
				'code'   => [
					'attr'     => [
						'name'  => '',
						'value' => '',
					],
					'block'    => [
						'wpforms-field-paypal-commerce-code',
					],
					'class'    => [
						'wpforms-field-paypal-commerce-cardcode',
					],
					'data'     => [],
					'id'       => "wpforms-{$form_id}-field_{$field_id}-cardcode",
					'required' => ! empty( $field['required'] ) ? 'required' : '',
					'sublabel' => [
						'hidden'   => ! empty( $field['sublabel_hide'] ),
						'value'    => ! empty( $field['security_code'] ) ? esc_html( $field['security_code'] ) : $default_labels['security_code'],
						'position' => 'after',
					],
				],
			],
		];

		if ( $is_card_holder_enable ) {
			$props['inputs']['name'] = [
				'attr'     => [
					'name'        => "wpforms[fields][{$field_id}][cardname]",
					'placeholder' => ! empty( $field['cardname_placeholder'] ) ? $field['cardname_placeholder'] : '',
				],
				'block'    => [
					'wpforms-field-paypal-commerce-name',
				],
				'class'    => [
					'wpforms-field-paypal-commerce-cardname',
				],
				'data'     => [],
				'id'       => "wpforms-{$form_id}-field_{$field_id}-cardname",
				'required' => ! empty( $field['required'] ) ? 'required' : '',
				'sublabel' => [
					'hidden'   => ! empty( $field['sublabel_hide'] ),
					'value'    => ! empty( $field['card_holder_name'] ) ? esc_html( $field['card_holder_name'] ) : $default_labels['card_holder_name'],
					'position' => 'after',
				],
			];
		}

		$properties = array_merge_recursive( $properties, $props );

		// If this field is required, we need to make some adjustments.
		if ( ! empty( $field['required'] ) ) {

			// Add the required class if needed (for multipage validation).
			$properties['inputs']['number']['class'][] = 'wpforms-field-required';
			$properties['inputs']['date']['class'][]   = 'wpforms-field-required';
			$properties['inputs']['code']['class'][]   = 'wpforms-field-required';

			if ( $is_card_holder_enable ) {
				$properties['inputs']['name']['class'][] = 'wpforms-field-required';
			}
		}

		return $properties;
	}

	/**
	 * Default to the required.
	 *
	 * @since 1.10.0
	 *
	 * @param bool  $required Required status, true if required.
	 * @param array $field    Field settings.
	 *
	 * @return bool
	 *
	 * @noinspection PhpMissingParamTypeInspection
	 */
	public function default_required( $required, array $field ): bool {

		if ( $this->type !== $field['type'] ) {
			return (bool) $required;
		}

		$this->is_new_field = true;

		return true;
	}

	/**
	 * Define additional "Add Field" button attributes.
	 *
	 * @since 1.10.0
	 *
	 * @param array $atts      Add Field button attributes.
	 * @param array $field     Field settings.
	 * @param array $form_data Form data and settings.
	 *
	 * @return array
	 *
	 * @noinspection PhpMissingParamTypeInspection
	 */
	public function field_button_atts( $atts, array $field, array $form_data ): array {

		if ( $field['type'] !== $this->type ) {
			return $atts;
		}

		if ( Helpers::has_paypal_commerce_field( $form_data ) ) {
			$atts['atts']['disabled'] = 'true';
			$atts['class'][]          = 'wpforms-add-fields-button-disabled';

			return $atts;
		}

		if ( Connection::get() ) {
			return $atts;
		}

		$atts['class'][] = 'warning-modal';
		$atts['class'][] = 'paypal-commerce-connection-required';

		return $atts;
	}

	/**
	 * Disallow a field preview "Duplicate" button.
	 *
	 * @since 1.10.0
	 *
	 * @param bool  $display Display switch.
	 * @param array $field   Field settings.
	 *
	 * @return bool
	 *
	 * @noinspection PhpMissingParamTypeInspection
	 */
	public function field_display_duplicate_button( $display, array $field ): bool {

		$display = (bool) $display;

		return $field['type'] === $this->type ? false : $display;
	}

	/**
	 * The field value availability for the Entry Preview field.
	 *
	 * @since 1.10.0
	 *
	 * @param bool   $is_supported The field availability.
	 * @param string $value        The submitted Credit Card detail.
	 * @param array  $field        Field data.
	 * @param array  $form_data    Form data.
	 *
	 * @return bool
	 *
	 * @noinspection PhpMissingParamTypeInspection
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function entry_preview_availability( $is_supported, $value, $field, $form_data ): bool {

		return ! empty( $value );
	}

	/**
	 * Disallow dynamic population.
	 *
	 * @since 1.10.0
	 *
	 * @param array $properties Field properties.
	 * @param array $field      Current field specific data.
	 *
	 * @return bool
	 */
	public function is_dynamic_population_allowed( $properties, $field ): bool {

		return false;
	}

	/**
	 * Disallow fallback population.
	 *
	 * @since 1.10.0
	 *
	 * @param array $properties Field properties.
	 * @param array $field      Current field specific data.
	 *
	 * @return bool
	 */
	public function is_fallback_population_allowed( $properties, $field ): bool {

		return false;
	}

	/**
	 * Field options panel inside the builder.
	 *
	 * @since 1.10.0
	 *
	 * @param array $field Field settings.
	 */
	public function field_options( $field ): void {

		$this->basic_options( $field );
		$this->advanced_options( $field );
	}

	/**
	 * Basic options.
	 *
	 * @since 1.10.0
	 *
	 * @param array $field Current field specific data.
	 */
	private function basic_options( array $field ): void {

		$this->field_option( 'basic-options', $field, [ 'markup' => 'open' ] );
		$this->field_option( 'label', $field );
		$this->field_option( 'description', $field );
		$this->payment_methods_options( $field );
		$this->supported_credit_cards_options( $field );
		$this->sublabels_options( $field );
		$this->fastlane_labels_options( $field );
		$this->field_option( 'required', $field );
		$this->field_option( 'basic-options', $field, [ 'markup' => 'close' ] );
	}

	/**
	 * Advanced options.
	 *
	 * @since 1.10.0
	 *
	 * @param array $field Current field specific data.
	 */
	private function advanced_options( array $field ): void {

		$this->field_option( 'advanced-options', $field, [ 'markup' => 'open' ] );
		$this->field_option( 'size', $field );
		$this->button_size_option( $field );
		$this->shape_option( $field );
		$this->color_option( $field );
		$this->field_option( 'css', $field );
		$this->field_option( 'label_hide', $field );
		$this->field_option( 'advanced-options', $field, [ 'markup' => 'close' ] );
	}

	/**
	 * Button size option.
	 *
	 * @since 1.10.0
	 *
	 * @param array $field Current field specific data.
	 */
	private function button_size_option( array $field ): void {

		$output = $this->field_element(
			'label',
			$field,
			[
				'slug'    => 'button_size',
				'value'   => esc_html__( 'Button Size', 'wpforms-lite' ),
				'tooltip' => esc_html__( 'PayPal checkout button size.', 'wpforms-lite' ),
			],
			false
		);

		$output .= $this->field_element(
			'select',
			$field,
			[
				'slug'    => 'button_size',
				'value'   => ! empty( $field['button_size'] ) ? esc_attr( $field['button_size'] ) : '',
				'options' => [
					'responsive' => esc_html__( 'Responsive', 'wpforms-lite' ),
					'small'      => esc_html__( 'Small', 'wpforms-lite' ),
					'medium'     => esc_html__( 'Medium', 'wpforms-lite' ),
					'large'      => esc_html__( 'Large', 'wpforms-lite' ),
				],
			],
			false
		);

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'button_size',
				'content' => $output,
			]
		);
	}

	/**
	 * Shape option.
	 *
	 * @since 1.10.0
	 *
	 * @param array $field Current field specific data.
	 */
	private function shape_option( array $field ): void {

		$output = $this->field_element(
			'label',
			$field,
			[
				'slug'    => 'shape',
				'value'   => esc_html__( 'Button Shape', 'wpforms-lite' ),
				'tooltip' => esc_html__( 'PayPal checkout button shape.', 'wpforms-lite' ),
			],
			false
		);

		$output .= $this->field_element(
			'select',
			$field,
			[
				'slug'    => 'shape',
				'value'   => ! empty( $field['shape'] ) ? esc_attr( $field['shape'] ) : '',
				'options' => [
					'pill' => esc_html__( 'Pill', 'wpforms-lite' ),
					'rect' => esc_html__( 'Rectangle', 'wpforms-lite' ),
				],
			],
			false
		);

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'shape',
				'content' => $output,
			]
		);
	}

	/**
	 * Color option.
	 *
	 * @since 1.10.0
	 *
	 * @param array $field Current field specific data.
	 */
	private function color_option( array $field ): void {

		$output = $this->field_element(
			'label',
			$field,
			[
				'slug'    => 'color',
				'value'   => esc_html__( 'Button Color', 'wpforms-lite' ),
				'tooltip' => esc_html__( 'PayPal checkout button color.', 'wpforms-lite' ),
			],
			false
		);

		$output .= $this->field_element(
			'select',
			$field,
			[
				'slug'    => 'color',
				'value'   => ! empty( $field['color'] ) ? esc_attr( $field['color'] ) : '',
				'options' => [
					'blue'   => esc_html__( 'Blue', 'wpforms-lite' ),
					'black'  => esc_html__( 'Black', 'wpforms-lite' ),
					'white'  => esc_html__( 'White', 'wpforms-lite' ),
					'gold'   => esc_html__( 'Gold', 'wpforms-lite' ),
					'silver' => esc_html__( 'Silver', 'wpforms-lite' ),
				],
			],
			false
		);

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'color',
				'content' => $output,
			]
		);
	}

	/**
	 * Display payment methods options.
	 *
	 * @since 1.10.0
	 *
	 * @param array $field Current field specific data.
	 */
	private function payment_methods_options( array $field ): void {

		// Ensure Credit Card and Fastlane are not both enabled, prefer Credit Card and disable Fastlane if both are enabled.
		if ( ! empty( $field['credit_card'] ) && ! empty( $field['fastlane'] ) ) {
			unset( $field['fastlane'] );

			if ( ! empty( $field['default_method'] ) && $field['default_method'] === 'fastlane' ) {
				$field['default_method'] = 'credit_card';
			}
		}

		$payment_methods = $this->field_element(
			'label',
			$field,
			[
				'slug'    => 'payment_methods',
				'value'   => esc_html__( 'Supported Payment Methods', 'wpforms-lite' ),
				'tooltip' => esc_html__( 'Select payment methods to enable.', 'wpforms-lite' ),
			],
			false
		);

		$payment_methods .= $this->field_element(
			'toggle',
			$field,
			[
				'slug'  => 'paypal_checkout',
				'value' => isset( $field['paypal_checkout'] ) || $this->is_new_field ? '1' : '0',
				'desc'  => esc_html__( 'PayPal Checkout', 'wpforms-lite' ),
				'class' => 'wpforms-field-option-paypal-checkout',
			],
			false
		);

		$payment_methods .= $this->field_element(
			'toggle',
			$field,
			[
				'slug'  => 'credit_card',
				'value' => isset( $field['credit_card'] ) || $this->is_new_field ? '1' : '0',
				'desc'  => esc_html__( 'Credit Card', 'wpforms-lite' ),
				'class' => 'wpforms-field-option-credit-card',
			],
			false
		);

		$payment_methods .= $this->field_element(
			'toggle',
			$field,
			[
				'slug'  => 'fastlane',
				'value' => isset( $field['fastlane'] ) ? '1' : '0',
				'desc'  => esc_html__( 'Fastlane', 'wpforms-lite' ),
				'class' => 'wpforms-field-option-fastlane',
			],
			false
		);

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'payment_methods',
				'content' => $payment_methods,
			]
		);

		$default_method_field = $this->field_element(
			'label',
			$field,
			[
				'slug'    => 'default_method',
				'value'   => esc_html__( 'Default Payment Method', 'wpforms-lite' ),
				'tooltip' => esc_html__( 'Select the default payment method.', 'wpforms-lite' ),
			],
			false
		);

		$default_method_field .= $this->field_element(
			'select',
			$field,
			[
				'slug'    => 'default_method',
				'value'   => ! empty( $field['default_method'] ) ? esc_attr( $field['default_method'] ) : '',
				'options' => [
					'paypal_checkout' => esc_html__( 'PayPal Checkout', 'wpforms-lite' ),
					'credit_card'     => esc_html__( 'Credit Card', 'wpforms-lite' ),
					'fastlane'        => esc_html__( 'Fastlane', 'wpforms-lite' ),
				],
			],
			false
		);

		// Show toggle when two and more options are available.
		$hidden_class = ( (int) ! empty( $field['paypal_checkout'] ) + (int) ! empty( $field['credit_card'] ) + (int) ! empty( $field['fastlane'] ) ) < 2;

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'default_method',
				'content' => $default_method_field,
				'class'   => $hidden_class && ! $this->is_new_field ? 'wpforms-hidden' : '',
			]
		);
	}

	/**
	 * Display supported credit cards options.
	 *
	 * @since 1.10.0
	 *
	 * @param array $field Current field specific data.
	 */
	private function supported_credit_cards_options( array $field ): void {

		$credit_cards = $this->field_element(
			'label',
			$field,
			[
				'slug'    => 'credit_cards',
				'value'   => esc_html__( 'Supported Credit Cards', 'wpforms-lite' ),
				'tooltip' => esc_html__( 'Select supported credit cards.', 'wpforms-lite' ),
			],
			false
		);

		$credit_cards .= $this->field_element(
			'toggle',
			$field,
			[
				'slug'  => 'amex',
				'value' => isset( $field['amex'] ) || $this->is_new_field ? '1' : '0',
				'desc'  => esc_html__( 'American Express', 'wpforms-lite' ),
				'data'  => [ 'card' => 'amex' ],
			],
			false
		);

		$credit_cards .= $this->field_element(
			'toggle',
			$field,
			[
				'slug'  => 'discover',
				'value' => isset( $field['discover'] ) || $this->is_new_field ? '1' : '0',
				'desc'  => esc_html__( 'Discover', 'wpforms-lite' ),
				'data'  => [ 'card' => 'discover' ],
			],
			false
		);

		$credit_cards .= $this->field_element(
			'toggle',
			$field,
			[
				'slug'  => 'maestro',
				'value' => isset( $field['maestro'] ) && ! $this->is_new_field ? '1' : '0',
				'desc'  => esc_html__( 'Maestro', 'wpforms-lite' ),
				'data'  => [ 'card' => 'maestro' ],
			],
			false
		);

		$credit_cards .= $this->field_element(
			'toggle',
			$field,
			[
				'slug'  => 'mastercard',
				'value' => isset( $field['mastercard'] ) || $this->is_new_field ? '1' : '0',
				'desc'  => esc_html__( 'Mastercard', 'wpforms-lite' ),
				'data'  => [ 'card' => 'mastercard' ],
			],
			false
		);

		$credit_cards .= $this->field_element(
			'toggle',
			$field,
			[
				'slug'  => 'visa',
				'value' => isset( $field['visa'] ) || $this->is_new_field ? '1' : '0',
				'desc'  => esc_html__( 'Visa', 'wpforms-lite' ),
				'data'  => [ 'card' => 'visa' ],
			],
			false
		);

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'credit_cards',
				'content' => $credit_cards,
				'class'   => ! isset( $field['credit_card'] ) && ! $this->is_new_field ? 'wpforms-hidden' : '',
			]
		);
	}

	/**
	 * Display sublabel_options options.
	 *
	 * @since 1.10.0
	 *
	 * @param array $field Current field specific data.
	 */
	private function sublabels_options( array $field ): void {

		$default_labels = $this->get_default_labels();

		$sublabels = $this->field_element(
			'label',
			$field,
			[
				'slug'    => 'sublabels',
				'value'   => esc_html__( 'Sublabels', 'wpforms-lite' ),
				'tooltip' => esc_html__( 'Additional credit card fields.', 'wpforms-lite' ),
			],
			false
		);

		$card_number = $this->field_element(
			'text',
			$field,
			[
				'slug'        => 'card_number',
				'value'       => ! empty( $field['card_number'] ) ? esc_attr( $field['card_number'] ) : '',
				'before'      => $default_labels['card_number'],
				'placeholder' => $default_labels['card_number'],
				'data'        => [ 'sublabel' => 'card-number' ],
			],
			false
		);

		$sublabels .= $this->field_element(
			'row',
			$field,
			[
				'slug'    => 'card_number',
				'content' => $card_number,
			],
			false
		);

		$expiration_date = $this->field_element(
			'text',
			$field,
			[
				'slug'        => 'expiration_date',
				'value'       => ! empty( $field['expiration_date'] ) ? esc_attr( $field['expiration_date'] ) : '',
				'before'      => $default_labels['expiration_date'],
				'placeholder' => $default_labels['expiration_date'],
				'data'        => [ 'sublabel' => 'expiration-date' ],
			],
			false
		);

		$sublabels .= $this->field_element(
			'row',
			$field,
			[
				'slug'    => 'expiration_date',
				'content' => $expiration_date,
			],
			false
		);

		$security_code = $this->field_element(
			'text',
			$field,
			[
				'slug'        => 'security_code',
				'value'       => ! empty( $field['security_code'] ) ? esc_attr( $field['security_code'] ) : '',
				'before'      => $default_labels['security_code'],
				'placeholder' => $default_labels['security_code'],
				'data'        => [ 'sublabel' => 'security-code' ],
			],
			false
		);

		$sublabels .= $this->field_element(
			'row',
			$field,
			[
				'slug'    => 'security_code',
				'content' => $security_code,
			],
			false
		);

		$card_holder = $this->field_element(
			'toggle',
			$field,
			[
				'slug'  => 'card_holder_enable',
				'value' => isset( $field['card_holder_enable'] ) || $this->is_new_field ? '1' : '0',
				'desc'  => $default_labels['card_holder_name'],
			],
			false
		);

		$card_holder .= $this->field_element(
			'text',
			$field,
			[
				'slug'        => 'card_holder_name',
				'value'       => ! empty( $field['card_holder_name'] ) ? esc_attr( $field['card_holder_name'] ) : '',
				'placeholder' => $default_labels['card_holder_name'],
				'class'       => ! isset( $field['card_holder_enable'] ) && ! $this->is_new_field ? 'wpforms-hidden' : '',
				'data'        => [ 'sublabel' => 'card-holder-name' ],
			],
			false
		);

		$sublabels .= $this->field_element(
			'row',
			$field,
			[
				'slug'    => 'card_holder',
				'content' => $card_holder,
			],
			false
		);

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'sublabels',
				'content' => $sublabels,
				'class'   => ! isset( $field['credit_card'] ) && ! $this->is_new_field ? 'wpforms-hidden' : '',
			]
		);
	}

	/**
	 * Display Fastlane labels options.
	 *
	 * @since 1.10.0
	 *
	 * @param array $field Current field specific data.
	 */
	private function fastlane_labels_options( array $field ): void {

		$default_labels = $this->get_default_labels();

		$labels = $this->field_element(
			'label',
			$field,
			[
				'slug'    => 'fastlane_labels',
				'value'   => esc_html__( 'Fastlane Labels', 'wpforms-lite' ),
				'tooltip' => esc_html__( 'Customize Fastlane email placeholder and button text.', 'wpforms-lite' ),
			],
			false
		);

		$email_placeholder = $this->field_element(
			'text',
			$field,
			[
				'slug'        => 'fastlane_email_placeholder',
				'value'       => ! empty( $field['fastlane_email_placeholder'] ) ? esc_attr( $field['fastlane_email_placeholder'] ) : '',
				'before'      => $default_labels['fastlane_email_placeholder'],
				'placeholder' => $default_labels['fastlane_email_placeholder'],
				'data'        => [ 'fastlane' => 'email-placeholder' ],
			],
			false
		);

		$labels .= $this->field_element(
			'row',
			$field,
			[
				'slug'    => 'fastlane_email_placeholder',
				'content' => $email_placeholder,
			],
			false
		);

		$continue_label = $this->field_element(
			'text',
			$field,
			[
				'slug'        => 'fastlane_continue_label',
				'value'       => ! empty( $field['fastlane_continue_label'] ) ? esc_attr( $field['fastlane_continue_label'] ) : '',
				'before'      => $default_labels['fastlane_continue_label'],
				'placeholder' => $default_labels['fastlane_continue_label'],
				'data'        => [ 'fastlane' => 'continue-label' ],
			],
			false
		);

		$labels .= $this->field_element(
			'row',
			$field,
			[
				'slug'    => 'fastlane_continue_label',
				'content' => $continue_label,
			],
			false
		);

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'fastlane_labels',
				'content' => $labels,
				'class'   => ! isset( $field['fastlane'] ) && ! $this->is_new_field ? 'wpforms-hidden' : '',
			]
		);
	}

	/**
	 * Get default labels.
	 *
	 * @since 1.10.0
	 *
	 * @return array
	 */
	private function get_default_labels(): array {

		return [
			'card_number'                => __( 'Card Number', 'wpforms-lite' ),
			'expiration_date'            => __( 'Expiration Date', 'wpforms-lite' ),
			'security_code'              => __( 'Security Code', 'wpforms-lite' ),
			'card_holder_name'           => __( 'Card Holder Name', 'wpforms-lite' ),
			'fastlane_email_placeholder' => __( 'Email', 'wpforms-lite' ),
			'fastlane_continue_label'    => __( 'Continue', 'wpforms-lite' ),
		];
	}

	/**
	 * Field preview inside the builder.
	 *
	 * @since 1.10.0
	 *
	 * @param array $field Field settings.
	 */
	public function field_preview( $field ): void { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		$default_labels = $this->get_default_labels();

		// Define data.
		$name_label                 = ! empty( $field['card_holder_name'] ) ? $field['card_holder_name'] : $default_labels['card_holder_name'];
		$code_label                 = ! empty( $field['security_code'] ) ? $field['security_code'] : $default_labels['security_code'];
		$date_label                 = ! empty( $field['expiration_date'] ) ? $field['expiration_date'] : $default_labels['expiration_date'];
		$card_label                 = ! empty( $field['card_number'] ) ? $field['card_number'] : $default_labels['card_number'];
		$fastlane_email_placeholder = ! empty( $field['fastlane_email_placeholder'] ) ? $field['fastlane_email_placeholder'] : $default_labels['fastlane_email_placeholder'];
		$fastlane_continue_label    = ! empty( $field['fastlane_continue_label'] ) ? $field['fastlane_continue_label'] : $default_labels['fastlane_continue_label'];

		// What methods are enabled?
		$has_cc       = ! empty( $field['credit_card'] );
		$has_checkout = ! empty( $field['paypal_checkout'] );
		$has_fastlane = ! empty( $field['fastlane'] );

		$enabled_count = (int) $has_cc + (int) $has_checkout + (int) $has_fastlane;

		// Resolve the effective default method.
		$effective_default = $this->resolve_default_method( (array) $field );

		// Selected method label for the (readonly) dropdown.
		$labels = [
			'paypal_checkout' => __( 'PayPal Checkout', 'wpforms-lite' ),
			'credit_card'     => __( 'Credit Card', 'wpforms-lite' ),
			'fastlane'        => __( 'Fastlane', 'wpforms-lite' ),
		];

		$selected_method_label = $labels[ $effective_default ] ?? $labels['paypal_checkout'];

		// Visibility booleans derived from the effective default.
		$show_method_select = $enabled_count >= 2 || $this->is_new_field; // show only when multiple methods are enabled or for new field.
		$show_fastlane      = $has_fastlane && $effective_default === 'fastlane';
		$show_credit_card   = $has_cc && $effective_default === 'credit_card';
		$show_checkout_warn = $has_checkout && ( ! $has_cc && ! $has_fastlane );

		// Label.
		$this->field_preview_option( 'label', $field );
		?>

		<div class="format-selected format-selected-full">

			<div class="wpforms-paypal-commerce-payment-method <?php echo $show_method_select ? '' : 'wpforms-hidden'; ?>">
				<select class="primary-input" readonly>
					<option><?php echo esc_html( $selected_method_label ); ?></option>
				</select>
			</div>

			<p class="wpforms-alert wpforms-alert-danger wpforms-paypal-commerce-no-payment-method-warning <?php echo isset( $field['paypal_checkout'] ) || isset( $field['credit_card'] ) || isset( $field['fastlane'] ) || $this->is_new_field ? 'wpforms-hidden' : ''; ?>"><?php esc_html_e( 'Please enable at least one payment method.', 'wpforms-lite' ); ?></p>
			<p class="wpforms-alert wpforms-alert-warning wpforms-paypal-commerce-paypal-checkout-warning <?php echo ( $show_checkout_warn && ! $this->is_new_field ) ? '' : 'wpforms-hidden'; ?>"><?php esc_html_e( 'PayPal Checkout is enabled. The form’s submit button has been replaced by PayPal’s smart buttons.', 'wpforms-lite' ); ?></p>

			<div class="wpforms-paypal-commerce-credit-card-fields <?php echo ( $show_credit_card ) ? '' : 'wpforms-hidden'; ?>">

				<p class="wpforms-alert wpforms-alert-danger wpforms-paypal-commerce-no-credit-card-type-warning <?php echo isset( $field['amex'] ) || isset( $field['discover'] ) || isset( $field['maestro'] ) || isset( $field['mastercard'] ) || isset( $field['visa'] ) || $this->is_new_field ? 'wpforms-hidden' : ''; ?>"><?php esc_html_e( 'Please enable at least one credit card type.', 'wpforms-lite' ); ?></p>

				<div class="wpforms-field-row">
					<div class="wpforms-paypal-commerce-supported-cards">
						<div class="wpforms-paypal-commerce-amex-icon <?php echo ! isset( $field['amex'] ) && ! $this->is_new_field ? 'wpforms-hidden' : ''; ?>"></div>
						<div class="wpforms-paypal-commerce-discover-icon <?php echo ! isset( $field['discover'] ) && ! $this->is_new_field ? 'wpforms-hidden' : ''; ?>"></div>
						<div class="wpforms-paypal-commerce-maestro-icon <?php echo ! isset( $field['maestro'] ) || $this->is_new_field ? 'wpforms-hidden' : ''; ?>"></div>
						<div class="wpforms-paypal-commerce-mastercard-icon <?php echo ! isset( $field['mastercard'] ) && ! $this->is_new_field ? 'wpforms-hidden' : ''; ?>"></div>
						<div class="wpforms-paypal-commerce-visa-icon <?php echo ! isset( $field['visa'] ) && ! $this->is_new_field ? 'wpforms-hidden' : ''; ?>"></div>
					</div>
				</div>

				<div class="wpforms-field-row">
					<div class="wpforms-paypal-commerce-card-number">
						<input type="text" readonly>
						<label class="wpforms-sub-label"><?php echo esc_html( $card_label ); ?></label>
					</div>
				</div>

				<div class="wpforms-field-row">
					<div class="wpforms-paypal-commerce-expiration-date wpforms-one-half">
						<input type="text" readonly>
						<label class="wpforms-sub-label"><?php echo esc_html( $date_label ); ?></label>
					</div>
					<div class="wpforms-paypal-commerce-security-code wpforms-one-half last">
						<input type="text" readonly>
						<label class="wpforms-sub-label"><?php echo esc_html( $code_label ); ?></label>
					</div>
				</div>

				<div class="wpforms-field-row">
					<div class="wpforms-paypal-commerce-card-holder-name <?php echo ! isset( $field['card_holder_enable'] ) && ! $this->is_new_field ? 'wpforms-hidden' : ''; ?>">
						<input type="text" readonly>
						<label class="wpforms-sub-label"><?php echo esc_html( $name_label ); ?></label>
					</div>
				</div>
			</div>
			<div class="wpforms-paypal-commerce-fastlane-fields <?php echo ( $show_fastlane ) ? '' : 'wpforms-hidden'; ?>">
				<div class="wpforms-field-row">
					<div class="wpforms-paypal-commerce-fastlane-container">
						<div class="wpforms-paypal-commerce-fastlane-email-container">
							<input type="email" placeholder="<?php echo esc_attr( $fastlane_email_placeholder ); ?>" readonly>
							<div class="wpforms-paypal-commerce-fastlane-watermark"></div>
						</div>
						<div class="wpforms-paypal-commerce-fastlane-continue-container">
							<button type="button" class="wpforms-paypal-commerce-fastlane-email-continue wpforms-page-button" title="<?php echo esc_attr( $fastlane_continue_label ); ?>"><?php echo esc_html( $fastlane_continue_label ); ?></button>
						</div>
					</div>
				</div>
			</div>
		</div>

		<?php
		// Description.
		$this->field_preview_option( 'description', $field );
	}

    /**
	 * Resolve the effective default payment method based on field settings.
	 *
	 * Prefers an explicit default_method when provided. If not provided and only one.
	 * method is enabled, that method becomes the default. Falls back to PayPal Checkout.
	 *
	 * @since 1.10.0
	 *
	 * @param array $field Field data and settings.
	 *
	 * @return string One of 'paypal_checkout', 'credit_card', or 'fastlane'.
	 */
	private function resolve_default_method( array $field ): string {

		$has_cc       = ! empty( $field['credit_card'] );
		$has_checkout = ! empty( $field['paypal_checkout'] );
		$has_fastlane = ! empty( $field['fastlane'] );

		// Honor explicit default only if that method is enabled.
		if ( ! empty( $field['default_method'] ) ) {
			$dm = $field['default_method'];

			if (
				( $dm === 'paypal_checkout' && $has_checkout ) ||
				( $dm === 'credit_card' && $has_cc ) ||
				( $dm === 'fastlane' && $has_fastlane )
			) {
				return $dm;
			}
		}

		if ( $has_cc ) {
			return 'credit_card';
		}
		if ( $has_fastlane ) {
			return 'fastlane';
		}

		// Fallback preference among enabled methods.
		if ( $has_checkout ) {
			return 'paypal_checkout';
		}

		// Default ultimate fallback.
		return 'paypal_checkout';
	}

	/**
	 * Add submit button.
	 *
	 * @since 1.10.0
	 *
	 * @param array  $form_data Form data and settings.
	 * @param string $button    Button type.
	 */
	public function submit_button( array $form_data, string $button = '' ): void {

		if ( $button !== 'submit' ) {
			return;
		}

		if ( wpforms_is_admin_page( 'builder' ) ) {
			$this->builder_submit_button( $form_data );

			return;
		}

		// If not on the integration page (Divi / Elementor / Block Editor), display the frontend submit button.
		// If on the integration page and credit card method were selected, return without action.
		// Otherwise, display the builder submit button.
		if ( ! $this->integrations->is_integration_page_loaded() ) {
			$this->frontend_submit_button( $form_data );

			return;
		}

		if ( $this->is_credit_card_method_selected( $form_data['fields'] ) ) {
			return;
		}

		$this->builder_submit_button( $form_data );
	}

	/**
	 * Add the submit button hidden class.
	 *
	 * @since 1.10.0
	 *
	 * @param array $classes   Button classes.
	 * @param array $form_data Form data and settings.
	 *
	 * @return array
	 *
	 * @noinspection PhpMissingParamTypeInspection
	 */
	public function submit_button_classes( $classes, array $form_data ): array {

		$classes = (array) $classes;

		if ( ! $this->integrations->is_integration_page_loaded() ) {
			return $classes;
		}

		if ( $this->is_credit_card_method_selected( $form_data['fields'] ) ) {
			return $classes;
		}

		$classes[] = 'wpforms-hidden';

		return $classes;
	}

	/**
	 * Check if a credit card method is selected for the field.
	 *
	 * @since 1.10.0
	 *
	 * @param array $fields Form fields data.
	 *
	 * @return bool
	 */
	private function is_credit_card_method_selected( array $fields ): bool {

		$field = Helpers::get_paypal_field( $fields );

		return empty( $field['paypal_checkout'] ) ||
			( ! empty( $field['credit_card'] ) && $field['default_method'] === 'credit_card' ) ||
			( ! empty( $field['conditional_logic'] ) && $field['conditional_type'] === 'show' );
	}

	/**
	 * Display a 'submit' button on the builder.
	 *
	 * @since 1.10.0
	 *
	 * @param array $form_data Form data and settings.
	 */
	private function builder_submit_button( array $form_data ): void {

		if ( ! isset( $form_data['fields'] ) ) {
			return;
		}

		$field = Helpers::get_paypal_field( $form_data['fields'] );

		if ( ! isset( $field['shape'] ) ) {
			$field['shape'] = 'pill';
		}

		if ( ! isset( $field['color'] ) ) {
			$field['color'] = 'blue';
		}

		$wrapper_hide = isset( $field['paypal_checkout'] ) && ! empty( $form_data['fields'] ) ? '' : 'wpforms-hidden';
		$size_class   = ! isset( $field['button_size'] ) || $field['button_size'] === 'responsive' ? 'size-medium' : 'size-' . $field['button_size'];

		?>

		<div id="wpforms-paypal-commerce-buttons-wrapper" class="<?php echo esc_attr( implode( ' ', [ $wrapper_hide, $size_class ] ) ); ?>">
			<?php
			/**
			 * Fires after the PayPal Commerce checkout button in the form builder.
			 *
			 * @since 1.10.0
			 *
			 * @param array $field PayPal Commerce field data.
			 */
			do_action( 'wpforms_integrations_paypal_commerce_fields_paypal_commerce_builder_submit_button', $field ); // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName
			?>
		</div>

		<?php
	}

	/**
	 * Display a 'submit' button on the form front-end.
	 *
	 * @since 1.10.0
	 *
	 * @param array $form_data Form data and settings.
	 */
	private function frontend_submit_button( array $form_data ): void {

		$connection = Connection::get();

		if ( ! $connection ||
			! $connection->is_usable() ||
			! Helpers::is_paypal_commerce_enabled( $form_data ) ||
			! Helpers::is_subscriptions_configured( $form_data )
		) {
			return;
		}

		$field = Helpers::get_paypal_field( $form_data['fields'] );

		if ( empty( $field ) ) {
			return;
		}

		if ( ! isset( $field['credit_card'] ) && ! isset( $field['paypal_checkout'] ) ) {
			return;
		}

		$this->render_submit_button_container( 'single', $form_data, $field, $connection );
		$this->render_submit_button_container( 'subscriptions', $form_data, $field, $connection );
	}

	/**
	 * Get submit spinner html.
	 *
	 * @since 1.10.0
	 *
	 * @param array  $form_data  Form data and settings.
	 * @param string $class_name Spinner class.
	 *
	 * @return string
	 * @noinspection HtmlUnknownTarget
	 */
	private function get_submit_spinner( array $form_data, string $class_name ): string {

		/** This filter is documented in includes/class-frontend.php. */
		$src = apply_filters( 'wpforms_display_submit_spinner_src', WPFORMS_PLUGIN_URL . 'assets/images/submit-spin.svg', $form_data ); // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName

		return sprintf(
			'<img src="%s" class="%s" style="display: none;" width="26" height="26" alt="%s">',
			esc_url( $src ),
			esc_attr( $class_name ),
			esc_attr__( 'Loading', 'wpforms-lite' )
		);
	}

	/**
	 * Render submit button container.
	 *
	 * @since 1.10.0
	 *
	 * @param string $type       Button type: 'single' or 'subscription'.
	 * @param array  $form_data  Form data and settings.
	 * @param array  $field      Current field specific data.
	 * @param object $connection PayPal connection data.
	 */
	private function render_submit_button_container( string $type, array $form_data, array $field, object $connection ): void {

		if ( ! in_array( $type, [ 'single', 'subscriptions' ], true ) ) {
			return;
		}

		$spinner_type = $type === 'single' ? 'single' : 'recurring';
		$spinner      = $this->get_submit_spinner( $form_data, "wpforms-paypal-commerce-{$spinner_type}-spinner" );
		$size_class   = ! isset( $field['button_size'] ) || $field['button_size'] === 'responsive' ? 'size-medium' : 'size-' . $field['button_size'];

		printf( '<div class="%s %s">', esc_attr( "wpforms-paypal-commerce-{$type}-submit-button" ), esc_attr( $size_class ) );
		echo $spinner; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		/**
		 * Fires after PayPal Commerce submit buttons are rendered.
		 *
		 * Allows payment sources to add their button containers.
		 *
		 * @since 1.10.0
		 *
		 * @param array  $field      Current field specific data.
		 * @param array  $connection PayPal connection data.
		 */
		do_action( "wpforms_integrations_paypal_commerce_fields_paypal_commerce_{$type}_submit_button", $field, $connection ); // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName
		echo '</div>';
	}

	/**
	 * Field display on the form front-end.
	 *
	 * @since 1.10.0
	 *
	 * @param array $field      Field data and settings.
	 * @param array $deprecated Deprecated field attributes. Use field properties.
	 * @param array $form_data  Form data and settings.
	 */
	public function field_display( $field, $deprecated, $form_data ): void {

		// Display warning for non SSL pages.
		if ( ! is_ssl() ) {
			echo '<div class="wpforms-cc-warning wpforms-error-alert">';
			esc_html_e( 'This page is not secure. PayPal Commerce payments should be used for testing purposes only.', 'wpforms-lite' );
			echo '</div>';
		}

		$connection = Connection::get();

		if ( ! $connection ) {
			echo '<div class="wpforms-cc-warning wpforms-error-alert">';
			esc_html_e( 'PayPal Commerce payments are disabled. Please set up a connection with PayPal in your form’s settings.', 'wpforms-lite' );
			echo '</div>';

			return;
		}

		if ( ! $connection->is_usable() ) {
			echo '<div class="wpforms-cc-warning wpforms-error-alert">';
			esc_html_e( 'PayPal Commerce payments are disabled because your connection is not set up correctly. Please ask your site administrator to check the connection settings.', 'wpforms-lite' );
			echo '</div>';

			return;
		}

		if ( ! Helpers::is_paypal_commerce_enabled( $form_data ) ) {
			echo '<div class="wpforms-cc-warning wpforms-error-alert">';
			esc_html_e( 'PayPal Commerce payments are not enabled in the form settings.', 'wpforms-lite' );
			echo '</div>';

			return;
		}

		if ( ! isset( $field['credit_card'] ) && ! isset( $field['paypal_checkout'] ) && ! isset( $field['fastlane'] ) ) {
			echo '<div class="wpforms-cc-warning wpforms-error-alert">';
			esc_html_e( 'PayPal Commerce payments are disabled. Please enable at least one payment method in the form settings.', 'wpforms-lite' );
			echo '</div>';

			return;
		}

		if ( ! Helpers::is_subscriptions_configured( $form_data ) ) {
			echo '<div class="wpforms-cc-warning wpforms-error-alert">';
			esc_html_e( 'PayPal Commerce payments are disabled because details are missing from one of the recurring plans. Please ask your site administrator to check the form settings.', 'wpforms-lite' );
			echo '</div>';

			return;
		}

		echo '<input type="hidden" name="wpforms[fields][' . esc_attr( $field['id'] ) . '][orderID]" class="wpforms-paypal-commerce-order-id" />';
		echo '<input type="hidden" name="wpforms[fields][' . esc_attr( $field['id'] ) . '][subscriptionID]" class="wpforms-paypal-commerce-subscription-id" />';
		echo '<input type="hidden" name="wpforms[fields][' . esc_attr( $field['id'] ) . '][subscriptionProcessorID]" class="wpforms-paypal-commerce-subscription-processor-id" />';
		echo '<input type="hidden" name="wpforms[fields][' . esc_attr( $field['id'] ) . '][source]" class="wpforms-paypal-commerce-source" />';
		echo '<input type="hidden" name="wpforms[fields][' . esc_attr( $field['id'] ) . '][fastlane_token]" class="wpforms-paypal-commerce-fastlane-token" />';

		$this->field_display_default_payment( $field );

		$this->field_display_credit_card( $field, $form_data );

		$this->field_display_fastlane( $field, $form_data );
	}

	/**
	 * Field display default payment on the form front-end.
	 *
	 * @since 1.10.0
	 *
	 * @param array $field Field data and settings.
	 */
	private function field_display_default_payment( array $field ): void {

		// Collect available payment types dynamically.
		$available_methods = [];

		if ( ! empty( $field['paypal_checkout'] ) ) {
			$available_methods['checkout'] = esc_html__( 'PayPal Checkout', 'wpforms-lite' );
		}

		if ( ! empty( $field['credit_card'] ) ) {
			$available_methods['credit_card'] = esc_html__( 'Credit Card', 'wpforms-lite' );
		}

		if ( ! empty( $field['fastlane'] ) ) {
			$available_methods['fastlane'] = esc_html__( 'Fastlane', 'wpforms-lite' );
		}

		// Render selector only when 2 or more methods are available.
		if ( count( $available_methods ) < 2 ) {
			return;
		}

		$default_method = $field['default_method'] ?? '';

		echo '<div class="wpforms-field-row wpforms-field-' . sanitize_html_class( $field['size'] ?? 'medium' ) . '">';
		echo '<select class="wpforms-paypal-commerce-payment-method">';

		foreach ( $available_methods as $value => $label ) {
			$selected = selected( $default_method, $value, false );
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo '<option value="' . esc_attr( $value ) . '" ' . $selected . '>' . esc_html( $label ) . '</option>';
		}

		echo '</select>';
		echo '</div>';
	}

	/**
	 * Field display default payment on the form front-end.
	 *
	 * @since 1.10.0
	 *
	 * @param array $field     Field data and settings.
	 * @param array $form_data Form data and settings.
	 */
	private function field_display_fastlane( $field, $form_data ) {

		if ( ! isset( $field['fastlane'] ) ) {
			return;
		}

		$hide_class = ( ( (int) ! empty( $field['credit_card'] ) + (int) ! empty( $field['paypal_checkout'] ) + (int) ! empty( $field['fastlane'] ) ) > 1 && ( $field['default_method'] ?? '' ) !== 'fastlane' ) ? 'wpforms-hidden' : '';

		$defaults          = $this->get_default_labels();
		$email_placeholder = ! empty( $field['fastlane_email_placeholder'] ) ? $field['fastlane_email_placeholder'] : $defaults['fastlane_email_placeholder'];
		$continue_label    = ! empty( $field['fastlane_continue_label'] ) ? $field['fastlane_continue_label'] : $defaults['fastlane_continue_label'];
		?>

		<div class="wpforms-paypal-commerce-fastlane-fields <?php echo sanitize_html_class( $hide_class ); ?>">

			<div class="wpforms-field-row wpforms-field-<?php echo sanitize_html_class( $field['size'] ); ?>">

				<div class="wpforms-paypal-commerce-fastlane-container">
					<div class="wpforms-paypal-commerce-fastlane-email-container">
						<input <?php echo ( $field['required'] ?? false ) ? 'required' : ''; ?> class="wpforms-paypal-commerce-fastlane-email-input" maxlength="255" name="email" type="email" placeholder="<?php echo esc_attr( $email_placeholder ); ?>" autocomplete="email" />
						<div class="wpforms-paypal-commerce-fastlane-watermark-container">
							<img src="<?php echo esc_url( WPFORMS_PLUGIN_URL . 'assets/images/integrations/paypal-commerce/fastlane.svg' ); ?>" alt="<?php echo esc_attr__( 'Fastlane', 'wpforms-lite' ); ?>" />
						</div>
					</div>

					<div class="wpforms-paypal-commerce-fastlane-continue-container">
						<button class="wpforms-paypal-commerce-fastlane-email-continue wpforms-page-button" type="button" title="<?php echo esc_attr( $continue_label ); ?>" disabled><?php echo esc_html( $continue_label ); ?></button>
					</div>
				</div>

			</div>

			<!-- Payment section -->
			<section class="wpforms-paypal-commerce-fastlane-payment" style="display: none">
				<div class="wpforms-field-row wpforms-field-<?php echo sanitize_html_class( $field['size'] ); ?>">
					<div class="wpforms-paypal-commerce-fastlane-payment-component"></div>
				</div>
			</section>

		</div>

		<?php
	}

	/**
	 * Field display default payment on the form front-end.
	 *
	 * @since 1.10.0
	 *
	 * @param array $field     Field data and settings.
	 * @param array $form_data Form data and settings.
	 *
	 * @noinspection HtmlUnknownAttribute
	 */
	private function field_display_credit_card( array $field, array $form_data ): void { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		if ( ! isset( $field['credit_card'] ) ) {
			return;
		}

		$hide_class = ( ( (int) ! empty( $field['credit_card'] ) + (int) ! empty( $field['paypal_checkout'] ) + (int) ! empty( $field['fastlane'] ) ) > 1 && ( $field['default_method'] ?? '' ) !== 'credit_card' ) ? 'wpforms-hidden' : '';

		// Define data.
		$number = ! empty( $field['properties']['inputs']['number'] ) ? $field['properties']['inputs']['number'] : [];
		$date   = ! empty( $field['properties']['inputs']['date'] ) ? $field['properties']['inputs']['date'] : [];
		$code   = ! empty( $field['properties']['inputs']['code'] ) ? $field['properties']['inputs']['code'] : [];
		$name   = ! empty( $field['properties']['inputs']['name'] ) ? $field['properties']['inputs']['name'] : [];

		echo '<div class="wpforms-paypal-commerce-card-fields ' . sanitize_html_class( $hide_class ) . '">';

		echo '<div class="wpforms-field-row wpforms-field-' . sanitize_html_class( $field['size'] ) . '">';
		echo '<div class="wpforms-paypal-commerce-supported-cards">';
		echo isset( $field['amex'] ) ? '<div class="wpforms-paypal-commerce-amex-icon"></div>' : '';
		echo isset( $field['discover'] ) ? '<div class="wpforms-paypal-commerce-discover-icon"></div>' : '';
		echo isset( $field['maestro'] ) ? '<div class="wpforms-paypal-commerce-maestro-icon"></div>' : '';
		echo isset( $field['mastercard'] ) ? '<div class="wpforms-paypal-commerce-mastercard-icon"></div>' : '';
		echo isset( $field['visa'] ) ? '<div class="wpforms-paypal-commerce-visa-icon"></div>' : '';
		echo '</div>';
		echo '</div>';

		// Row wrapper.
		echo '<div class="wpforms-field-row wpforms-field-' . sanitize_html_class( $field['size'] ) . '">';
		echo '<div ' . wpforms_html_attributes( false, $number['block'] ) . '>';
		$this->field_display_sublabel( 'number', 'before', $field );
		printf(
			'<div %s data-required="%s"></div>',
			wpforms_html_attributes( $number['id'], $number['class'], $number['data'], $number['attr'] ),
			esc_attr( $number['required'] )
		);

		// Hidden input is needed for validation.
		printf( '<input type="text" class="wpforms-paypal-commerce-credit-card-hidden-input" name="wpforms[paypal-commerce-credit-card-hidden-input-%1$d]" id="wpforms-paypal-commerce-credit-card-hidden-input-%1$d-number" disabled style="display: none;">', (int) $form_data['id'] );
		$this->field_display_sublabel( 'number', 'after', $field );
		$this->field_display_error( 'number', $field );
		echo '</div>';
		echo '</div>';

		// Row wrapper.
		echo '<div class="wpforms-field-row wpforms-field-row-responsive wpforms-field-' . sanitize_html_class( $field['size'] ) . '">';
		echo '<div class="wpforms-field-row-block wpforms-one-half wpforms-first ' . wpforms_sanitize_classes( $date['block'], true ) . '">';
		$this->field_display_sublabel( 'date', 'before', $field );
		printf(
			'<div %s data-required="%s"></div>',
			wpforms_html_attributes( $date['id'], $date['class'], $date['data'], $date['attr'] ),
			esc_attr( $date['required'] )
		);

		// Hidden input is needed for validation.
		printf( '<input type="text" class="wpforms-paypal-commerce-credit-card-hidden-input" name="wpforms[paypal-commerce-credit-card-hidden-input-%1$d]" id="wpforms-paypal-commerce-credit-card-hidden-input-%1$d-date" disabled style="display: none;">', (int) $form_data['id'] );
		$this->field_display_sublabel( 'date', 'after', $field );
		$this->field_display_error( 'date', $field );
		echo '</div>';

		echo '<div class="wpforms-field-row-block wpforms-one-half ' . wpforms_sanitize_classes( $code['block'], true ) . '">';
		$this->field_display_sublabel( 'code', 'before', $field );
		printf(
			'<div %s data-required="%s"></div>',
			wpforms_html_attributes( $code['id'], $code['class'], $code['data'], $code['attr'] ),
			esc_attr( $code['required'] )
		);

		// Hidden input is needed for validation.
		printf( '<input type="text" class="wpforms-paypal-commerce-credit-card-hidden-input" name="wpforms[paypal-commerce-credit-card-hidden-input-%1$d]" id="wpforms-paypal-commerce-credit-card-hidden-input-%1$d-code" disabled style="display: none;">', (int) $form_data['id'] );
		$this->field_display_sublabel( 'code', 'after', $field );
		$this->field_display_error( 'code', $field );
		echo '</div>';
		echo '</div>';

		if ( ! isset( $field['card_holder_enable'] ) ) {
			echo '</div>';

			return;
		}

		// Row wrapper.
		echo '<div class="wpforms-field-row wpforms-field-' . sanitize_html_class( $field['size'] ) . '">';
		// Name.
		echo '<div ' . wpforms_html_attributes( false, $name['block'] ) . '>';
		$this->field_display_sublabel( 'name', 'before', $field );
		printf(
			'<input type="text" %s %s>',
			wpforms_html_attributes( $name['id'], $name['class'], $name['data'], $name['attr'] ),
			esc_attr( $name['required'] )
		);
		$this->field_display_sublabel( 'name', 'after', $field );
		$this->field_display_error( 'name', $field );
		echo '</div>';
		echo '</div>';

		echo '</div>';
	}

	/**
	 * Currently, validation happens on the front end. We do not do
	 * generic server-side validation because we do not allow the card
	 * details to POST to the server.
	 *
	 * @since 1.10.0
	 *
	 * @param int   $field_id     Field ID.
	 * @param array $field_submit Submitted field value.
	 * @param array $form_data    Form data and settings.
	 */
	public function validate( $field_id, $field_submit, $form_data ) {}

	/**
	 * Format field.
	 *
	 * @since 1.10.0
	 *
	 * @param int   $field_id     Field ID.
	 * @param array $field_submit Submitted field value.
	 * @param array $form_data    Form data and settings.
	 */
	public function format( $field_id, $field_submit, $form_data ): void {

		// Define data.
		$field_name = ! empty( $form_data['fields'][ $field_id ]['label'] ) ? $form_data['fields'][ $field_id ]['label'] : '';
		$card_name  = ! empty( $field_submit['cardname'] ) ? $field_submit['cardname'] : '';

		// Set final field details.
		wpforms()->obj( 'process' )->fields[ $field_id ] = [
			'name'     => sanitize_text_field( $field_name ),
			'cardname' => sanitize_text_field( $card_name ),
			'value'    => '',
			'id'       => absint( $field_id ),
			'type'     => $this->type,
		];
	}

	/**
	 * Modify for attribute value for sublabels to be the same as its IDs.
	 *
	 * @since 1.10.0
	 *
	 * @param string $value For attribute value.
	 * @param string $key   Input key.
	 * @param array  $field Field data and settings.
	 *
	 * @return string
	 *
	 * @noinspection PhpMissingParamTypeInspection
	 */
	public function modify_sublabel_for( $value, string $key, array $field ): string {

		$value = (string) $value;

		if ( $field['type'] !== $this->type ) {
			return $value;
		}

		if ( in_array( $key, [ 'date', 'code', 'number' ], true ) ) {
			return sprintf( 'wpforms-paypal-commerce-credit-card-hidden-input-%1$d-%2$s', $this->form_data['id'], $key );
		}

		return $value;
	}
}
