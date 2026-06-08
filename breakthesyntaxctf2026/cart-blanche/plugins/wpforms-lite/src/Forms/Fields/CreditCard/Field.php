<?php

namespace WPForms\Forms\Fields\CreditCard;

use WPForms\Forms\Fields\Traits\ProField as ProFieldTrait;
use WPForms_Field;

/**
 * Credit card field (legacy).
 *
 * @since 1.0.0
 */
class Field extends WPForms_Field {

	use ProFieldTrait;

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function init() {

		// Define field type information.
		$this->name  = esc_html__( 'Credit Card', 'wpforms-lite' );
		$this->type  = 'credit-card';
		$this->icon  = 'fa-credit-card';
		$this->order = 90;
		$this->group = 'payment';

		$this->init_pro_field();
		$this->hooks();
	}

	/**
	 * Hooks.
	 *
	 * @since 1.8.1
	 */
	protected function hooks(): void {
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
				'markup'      => 'open',
				'after_title' => $this->get_field_options_notice(),
			]
		);

		// Label.
		$this->field_option( 'label', $field );

		// Description.
		$this->field_option( 'description', $field );

		// Required toggle.
		$this->field_option( 'required', $field );

		// Options close markup.
		$args = [
			'markup' => 'close',
		];

		$this->field_option( 'basic-options', $field, $args );

		/*
		 * Advanced field options.
		 */

		// Options open markup.
		$args = [
			'markup' => 'open',
		];

		$this->field_option( 'advanced-options', $field, $args );

		// Size.
		$this->field_option( 'size', $field );

		// Card Number.
		$cardnumber_placeholder = ! empty( $field['cardnumber_placeholder'] ) ? esc_attr( $field['cardnumber_placeholder'] ) : '';

		printf(
			'<div class="wpforms-clear wpforms-field-option-row wpforms-field-option-row-cardnumber" id="wpforms-field-option-row-%1$d-cardnumber" data-subfield="cardnumber" data-field-id="%1$d">',
			absint( $field['id'] )
		);
		$this->field_element(
			'label',
			$field,
			[
				'slug'  => 'cardnumber_placeholder',
				'value' => esc_html__( 'Card Number Placeholder Text', 'wpforms-lite' ),
			]
		);

		echo '<div class="placeholder">';
		printf(
			'<input type="text" class="placeholder-update" id="wpforms-field-option-%1$d-cardnumber_placeholder" name="fields[%1$d][cardnumber_placeholder]" value="%2$s" data-field-id="%1$d" data-subfield="credit-card-cardnumber">',
			absint( $field['id'] ),
			esc_attr( $cardnumber_placeholder )
		);
		echo '</div>';
		echo '</div>';

		// CVC/Security Code.
		$cardcvc_placeholder = ! empty( $field['cardcvc_placeholder'] ) ? $field['cardcvc_placeholder'] : '';

		printf(
			'<div class="wpforms-clear wpforms-field-option-row wpforms-field-option-row-cvc" id="wpforms-field-option-row-%1$d-cvc" data-subfield="cvc" data-field-id="%1$d">',
			absint( $field['id'] )
		);
		$this->field_element(
			'label',
			$field,
			[
				'slug'  => 'cardcvc_placeholder',
				'value' => esc_html__( 'Security Code Placeholder Text', 'wpforms-lite' ),
			]
		);

		echo '<div class="placeholder">';
		printf(
			'<input type="text" class="placeholder-update" id="wpforms-field-option-%1$d-cardcvc_placeholder" name="fields[%1$d][cardcvc_placeholder]" value="%2$s" data-field-id="%1$d" data-subfield="credit-card-cardcvc">',
			absint( $field['id'] ),
			esc_attr( $cardcvc_placeholder )
		);
		echo '</div>';
		echo '</div>';

		// Card Name.
		$cardname_placeholder = ! empty( $field['cardname_placeholder'] ) ? $field['cardname_placeholder'] : '';

		printf(
			'<div class="wpforms-clear wpforms-field-option-row wpforms-field-option-row-cardname" id="wpforms-field-option-row-%1$d-cardname" data-subfield="cardname" data-field-id="%1$d">',
			absint( $field['id'] )
		);
		$this->field_element(
			'label',
			$field,
			[
				'slug'  => 'cardname_placeholder',
				'value' => esc_html__( 'Name on Card Placeholder Text', 'wpforms-lite' ),
			]
		);

		echo '<div class="placeholder">';
		printf(
			'<input type="text" class="placeholder-update" id="wpforms-field-option-%1$d-cardname_placeholder" name="fields[%1$d][cardname_placeholder]" value="%2$s" data-field-id="%1$d" data-subfield="credit-card-cardname">',
			absint( $field['id'] ),
			esc_attr( $cardname_placeholder )
		);
		echo '</div>';
		echo '</div>';

		// Custom CSS classes.
		$this->field_option( 'css', $field );

		// Hide Label.
		$this->field_option( 'label_hide', $field );

		// Hide sublabels.
		$this->field_option( 'sublabel_hide', $field );

		// Options close markup.
		$args = [
			'markup' => 'close',
		];

		$this->field_option( 'advanced-options', $field, $args );
	}

	/**
	 * Field preview inside the builder.
	 *
	 * @since 1.0.0
	 *
	 * @param array $field Field settings.
	 */
	public function field_preview( $field ) {

		// Define data.
		$number_placeholder = ! empty( $field['cardnumber_placeholder'] ) ? esc_attr( $field['cardnumber_placeholder'] ) : '';
		$cvc_placeholder    = ! empty( $field['cardcvc_placeholder'] ) ? esc_attr( $field['cardcvc_placeholder'] ) : '';
		$name_placeholder   = ! empty( $field['cardname_placeholder'] ) ? esc_attr( $field['cardname_placeholder'] ) : '';

		// Label.
		$this->field_preview_option(
			'label',
			$field,
			[
				'label_badge' => $this->get_field_preview_badge(),
			]
		);
		?>

		<div class="format-selected format-selected-full">

			<div class="wpforms-field-row">
				<div class="wpforms-credit-card-cardnumber">
					<label class="wpforms-sub-label"><?php esc_html_e( 'Card Number', 'wpforms-lite' ); ?></label>
					<input type="text" placeholder="<?php echo esc_attr( $number_placeholder ); ?>" readonly>
				</div>

				<div class="wpforms-credit-card-cardcvc">
					<label class="wpforms-sub-label"><?php esc_html_e( 'Security Code', 'wpforms-lite' ); ?></label>
					<input type="text" placeholder="<?php echo esc_attr( $cvc_placeholder ); ?>" readonly>
				</div>
			</div>

			<div class="wpforms-field-row">
				<div class="wpforms-credit-card-cardname">
					<label class="wpforms-sub-label"><?php esc_html_e( 'Name on Card', 'wpforms-lite' ); ?></label>
					<input type="text" placeholder="<?php echo esc_attr( $name_placeholder ); ?>" readonly>
				</div>

				<div class="wpforms-credit-card-expiration">
					<label class="wpforms-sub-label"><?php esc_html_e( 'Expiration', 'wpforms-lite' ); ?></label>
					<div class="wpforms-credit-card-cardmonth">
						<select readonly>
							<option>MM</option>
						</select>
					</div>
					<span>/</span>
					<div class="wpforms-credit-card-cardyear">
						<select readonly>
							<option>YY</option>
						</select>
					</div>
				</div>
			</div>

		</div>

		<?php
		// Description.
		$this->field_preview_option( 'description', $field );
	}

	/**
	 * Field display on the form front-end.
	 *
	 * @since 1.0.0
	 *
	 * @param array $field      Field data and settings.
	 * @param array $deprecated Deprecated field attributes. Use field properties.
	 * @param array $form_data  Form data and settings.
	 */
	public function field_display( $field, $deprecated, $form_data ) {
	}
}
