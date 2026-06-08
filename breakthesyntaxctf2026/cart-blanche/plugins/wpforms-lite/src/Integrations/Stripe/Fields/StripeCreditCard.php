<?php

namespace WPForms\Integrations\Stripe\Fields;

use WPForms_Field;
use WPForms\Integrations\Stripe\Fields\Traits\CreditCard;

/**
 * Stripe credit card field.
 *
 * @since 1.8.2
 */
class StripeCreditCard extends WPForms_Field {

	use CreditCard;

	/**
	 * Field preview card icon SVG code.
	 *
	 * @since 1.8.2
	 */
	const FIELD_PREVIEW_CARD_ICON_SVG = '<svg viewBox="0 0 32 21"><g transform="translate(0 2)"><path d="M26.58 19H2.42A2.4 2.4 0 0 1 0 16.62V2.38A2.4 2.4 0 0 1 2.42 0h24.16A2.4 2.4 0 0 1 29 2.38v14.25A2.4 2.4 0 0 1 26.58 19zM10 5.83c0-.46-.35-.83-.78-.83H3.78c-.43 0-.78.37-.78.83v3.34c0 .46.35.83.78.83h5.44c.43 0 .78-.37.78-.83V5.83z" opacity=".2"></path><path d="M25 15h-3c-.65 0-1-.3-1-1s.35-1 1-1h3c.65 0 1 .3 1 1s-.35 1-1 1zm-6 0h-3c-.65 0-1-.3-1-1s.35-1 1-1h3c.65 0 1 .3 1 1s-.35 1-1 1zm-6 0h-3c-.65 0-1-.3-1-1s.35-1 1-1h3c.65 0 1 .3 1 1s-.35 1-1 1zm-6 0H4c-.65 0-1-.3-1-1s.35-1 1-1h3c.65 0 1 .3 1 1s-.35 1-1 1z" opacity=".3"></path></g></svg>';


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

		unset( $properties['inputs']['primary'], $properties['label']['attr']['for'] );

		$form_id  = absint( $form_data['id'] );
		$field_id = absint( $field['id'] );

		$props = [
			'inputs' => [
				'number' => [
					'attr'     => [
						'name'  => '',
						'value' => '',
					],
					'block'    => [
						'wpforms-field-stripe-credit-card-number',
					],
					'class'    => [
						'wpforms-field-stripe-credit-card-cardnumber',
					],
					'data'     => [],
					'id'       => "wpforms-{$form_id}-field_{$field_id}",
					'required' => ! empty( $field['required'] ) ? 'required' : '',
					'sublabel' => [
						'hidden'   => ! empty( $field['sublabel_hide'] ),
						'value'    => esc_html__( 'Card', 'wpforms-lite' ),
						'position' => 'after',
					],
				],
				'name'   => [
					'attr'     => [
						'name'        => 'wpforms[stripe-credit-card-cardname]',
						'value'       => '',
						'placeholder' => ! empty( $field['cardname_placeholder'] ) ? $field['cardname_placeholder'] : '',
					],
					'block'    => [
						'wpforms-field-stripe-credit-card-name',
					],
					'class'    => [
						'wpforms-field-stripe-credit-card-cardname',
					],
					'data'     => [],
					'id'       => "wpforms-{$form_id}-field_{$field_id}-cardname",
					'required' => ! empty( $field['required'] ) ? 'required' : '',
					'sublabel' => [
						'hidden'   => ! empty( $field['sublabel_hide'] ),
						'value'    => esc_html__( 'Name on Card', 'wpforms-lite' ),
						'position' => 'after',
					],
				],
			],
		];

		$properties = array_merge_recursive( $properties, $props );

		// If this field is required we need to make some adjustments.
		if ( ! empty( $field['required'] ) ) {

			// Add required class if needed (for multi-page validation).
			$properties['inputs']['number']['class'][] = 'wpforms-field-required';
			$properties['inputs']['name']['class'][]   = 'wpforms-field-required';
		}

		return $properties;
	}

	/**
	 * Advanced section field options.
	 *
	 * @since 1.8.2
	 *
	 * @param array $field Field settings.
	 */
	protected function advanced_options( $field ) {

		// Card Name.
		$cardname_placeholder = ! empty( $field['cardname_placeholder'] ) ? $field['cardname_placeholder'] : '';

		printf( '<div class="wpforms-clear wpforms-field-option-row wpforms-field-option-row-cardname" id="wpforms-field-option-row-%d-cardname" data-subfield="cardname" data-field-id="%d">', absint( $field['id'] ), absint( $field['id'] ) );
			$this->field_element(
				'label',
				$field,
				[
					'slug'  => 'cardname_placeholder',
					'value' => esc_html__( 'Name on Card Placeholder Text', 'wpforms-lite' ),
				]
			);
			echo '<div class="placeholder">';
				printf( '<input type="text" class="placeholder-update" id="wpforms-field-option-%d-cardname_placeholder" name="fields[%d][cardname_placeholder]" value="%s" data-field-id="%d" data-subfield="stripe-credit-card-cardname">', absint( $field['id'] ), absint( $field['id'] ), esc_attr( $cardname_placeholder ), absint( $field['id'] ) );
			echo '</div>';
		echo '</div>';

		// Custom CSS classes.
		$this->field_option( 'css', $field );
	}

	/**
	 * Field preview inside the builder.
	 *
	 * @since 1.8.2
	 *
	 * @param array $field Field settings.
	 */
	public function field_preview( $field ) {

		// Define data.
		$card_placeholder = esc_html__( 'Card number', 'wpforms-lite' );
		$name_placeholder = ! empty( $field['cardname_placeholder'] ) ? $field['cardname_placeholder'] : '';

		// Label.
		$this->field_preview_option( 'label', $field );
		?>

		<div class="format-selected format-selected-full">

			<div class="wpforms-field-row">
				<input type="text" readonly>
				<div class="wpforms-field-preview-wrap">
					<div class="wpforms-field-stripe-credit-card-number-placeholder-preview">
						<?php echo self::FIELD_PREVIEW_CARD_ICON_SVG; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<span><?php echo esc_attr( $card_placeholder ); ?></span>
					</div>
					<div class="wpforms-field-stripe-credit-card-number-expcvc-preview">MM / YY &nbsp; CVC</div>
				</div>
				<label class="wpforms-sub-label"><?php esc_html_e( 'Card', 'wpforms-lite' ); ?></label>
			</div>

			<div class="wpforms-field-row">
				<div class="wpforms-stripe-credit-card-cardname">
					<input type="text" placeholder="<?php echo esc_attr( $name_placeholder ); ?>" readonly>
					<label class="wpforms-sub-label"><?php esc_html_e( 'Name on Card', 'wpforms-lite' ); ?></label>
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
	 * @since 1.8.2
	 *
	 * @param array $field      Field data and settings.
	 * @param array $deprecated Deprecated field attributes. Use field properties.
	 * @param array $form_data  Form data and settings.
	 */
	public function field_display( $field, $deprecated, $form_data ) {

		if ( $this->field_display_errors( $form_data ) ) {
			return;
		}

		if ( wpforms_is_editor_page() ) {
			$this->block_editor_field_display( $field );

			return;
		}

		// Define data.
		$number = ! empty( $field['properties']['inputs']['number'] ) ? $field['properties']['inputs']['number'] : [];
		$name   = ! empty( $field['properties']['inputs']['name'] ) ? $field['properties']['inputs']['name'] : [];

		// Row wrapper.
		echo '<div class="wpforms-field-row wpforms-field-' . sanitize_html_class( $field['size'] ) . '">';

			echo '<div ' . wpforms_html_attributes( false, $number['block'] ) . '>';
			$this->field_display_sublabel( 'number', 'before', $field );
			printf(
				'<div %s data-required="%s"><!-- a Stripe Element will be inserted here. --></div>',
				wpforms_html_attributes( $number['id'], $number['class'], $number['data'], $number['attr'] ),
				esc_html( $number['required'] )
			);
			// Hidden input is needed for styling and validation.
			echo '<input type="text" class="wpforms-stripe-credit-card-hidden-input" name="wpforms[stripe-credit-card-hidden-input-' . absint( $form_data['id'] ) . ']" disabled style="display: none;">';
			$this->field_display_sublabel( 'number', 'after', $field );
			$this->field_display_error( 'number', $field );
			echo '</div>';

		echo '</div>';

		// Row wrapper.
		echo '<div class="wpforms-field-row wpforms-field-' . sanitize_html_class( $field['size'] ) . '">';

			// Name.
			echo '<div ' . wpforms_html_attributes( false, $name['block'] ) . '>';
			$this->field_display_sublabel( 'name', 'before', $field );
			printf(
				'<input type="text" %s %s>',
				wpforms_html_attributes( $name['id'], $name['class'], $name['data'], $name['attr'] ),
				esc_html( $name['required'] )
			);
			$this->field_display_sublabel( 'name', 'after', $field );
			$this->field_display_error( 'name', $field );
			echo '</div>';

		echo '</div>';
	}

	/**
	 * Block editor field preview.
	 *
	 * @since 1.8.2
	 *
	 * @param array $field Field settings.
	 */
	private function block_editor_field_display( $field ) {

		$field_class      = 'wpforms-field-row wpforms-no-columns wpforms-field-' . sanitize_html_class( $field['size'] );
		$card_placeholder = esc_html__( 'Card number', 'wpforms-lite' );
		$name_placeholder = ! empty( $field['properties']['inputs']['name']['attr']['placeholder'] ) ? $field['properties']['inputs']['name']['attr']['placeholder'] : '';
		?>

		<div class="<?php echo esc_attr( $field_class ); ?> ">
			<?php $this->field_display_sublabel( 'number', 'before', $field ); ?>
			<input type="text" class="wpforms-field-stripe-credit-card-number-preview" readonly placeholder="">
			<div class="wpforms-field-stripe-credit-card-number-placeholder-preview">
				<?php echo self::FIELD_PREVIEW_CARD_ICON_SVG; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<span><?php echo esc_attr( $card_placeholder ); ?></span>
			</div>
			<div class="wpforms-field-stripe-credit-card-number-expcvc-preview">MM / YY &nbsp; CVC</div>
			<?php $this->field_display_sublabel( 'number', 'after', $field ); ?>
		</div>
		<div class="<?php echo esc_attr( $field_class ); ?>">
			<?php $this->field_display_sublabel( 'name', 'before', $field ); ?>
			<input type="text" readonly placeholder="<?php echo esc_attr( $name_placeholder ); ?>">
			<?php $this->field_display_sublabel( 'name', 'after', $field ); ?>
		</div>

		<?php
	}
}
