<?php

namespace WPForms\Integrations\Stripe\Fields;

use WPForms_Field;
use WPForms\Integrations\Stripe\Fields\Traits\CreditCard;

/**
 * Stripe Payment element credit card field.
 *
 * @since 1.8.2
 */
class PaymentElementCreditCard extends WPForms_Field {

	use CreditCard;

	/**
	 * Field preview CVC icon SVG code.
	 *
	 * @since 1.8.2
	 */
	const FIELD_PREVIEW_CVC_ICON_SVG = '<svg width="24" height="24" viewBox="0 0 24 24"><path opacity=".2" fill-rule="evenodd" clip-rule="evenodd" d="M15.337 4A5.493 5.493 0 0013 8.5c0 1.33.472 2.55 1.257 3.5H4a1 1 0 00-1 1v1a1 1 0 001 1h16a1 1 0 001-1v-.6a5.526 5.526 0 002-1.737V18a2 2 0 01-2 2H3a2 2 0 01-2-2V6a2 2 0 012-2h12.337zm6.707.293c.239.202.46.424.662.663a2.01 2.01 0 00-.662-.663z"></path><path opacity=".4" fill-rule="evenodd" clip-rule="evenodd" d="M13.6 6a5.477 5.477 0 00-.578 3H1V6h12.6z"></path><path fill-rule="evenodd" clip-rule="evenodd" d="M18.5 14a5.5 5.5 0 110-11 5.5 5.5 0 010 11zm-2.184-7.779h-.621l-1.516.77v.786l1.202-.628v3.63h.943V6.22h-.008zm1.807.629c.448 0 .762.251.762.613 0 .393-.37.668-.904.668h-.235v.668h.283c.565 0 .95.282.95.691 0 .393-.377.66-.911.66-.393 0-.786-.126-1.194-.37v.786c.44.189.88.291 1.312.291 1.029 0 1.736-.526 1.736-1.288 0-.535-.33-.967-.88-1.14.472-.157.778-.573.778-1.045 0-.738-.652-1.241-1.595-1.241a3.143 3.143 0 00-1.234.267v.77c.378-.212.763-.33 1.132-.33zm3.394 1.713c.574 0 .974.338.974.778 0 .463-.4.785-.974.785-.346 0-.707-.11-1.076-.337v.809c.385.173.778.26 1.163.26.204 0 .392-.032.573-.08a4.313 4.313 0 00.644-2.262l-.015-.33a1.807 1.807 0 00-.967-.252 3 3 0 00-.448.032V6.944h1.132a4.423 4.423 0 00-.362-.723h-1.587v2.475a3.9 3.9 0 01.943-.133z"></path></svg>';

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

		// Save form data for future usage in the class.
		$this->form_data = $form_data;

		unset( $properties['label']['attr']['for'] );

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

		// Link Email field map.
		$output = $this->field_element(
			'label',
			$field,
			[
				'slug'    => 'link_email',
				'value'   => esc_html__( 'Link Email', 'wpforms-lite' ),
				'tooltip' => esc_html__( 'Select an Email field to autofill your customers’ payment information using Link.', 'wpforms-lite' ),
			],
			false
		);

		$output .= $this->field_element(
			'select',
			$field,
			[
				'slug'    => 'link_email',
				'value'   => ! empty( $field['link_email'] ) ? esc_attr( $field['link_email'] ) : '',
				'options' => $this->get_email_field_options(),
				'class'   => 'wpforms-field-map-select',
				'data'    => [
					'field-map-allowed'     => 'email',
					'field-map-placeholder' => esc_attr__( 'Stripe Credit Card Email', 'wpforms-lite' ),
				],
			],
			false
		);

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'link_email',
				'content' => $output,
			]
		);

		$output = $this->field_element(
			'label',
			$field,
			[
				'slug'  => 'sublabel_position',
				'value' => esc_html__( 'Sublabel Position', 'wpforms-lite' ),
			],
			false
		);

		$output .= $this->field_element(
			'select',
			$field,
			[
				'slug'    => 'sublabel_position',
				'value'   => ! empty( $field['sublabel_position'] ) ? esc_attr( $field['sublabel_position'] ) : '',
				'options' => [
					'above'    => esc_html__( 'Above', 'wpforms-lite' ),
					'floating' => esc_html__( 'Floating', 'wpforms-lite' ),
				],
			],
			false
		);

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'sublabel_position',
				'content' => $output,
			]
		);
	}

	/**
	 * Array of available form email fields.
	 *
	 * @since 1.8.2
	 *
	 * @return array
	 */
	private function get_email_field_options() {

		$fields        = [ '' => esc_html__( 'Stripe Credit Card Email', 'wpforms-lite' ) ];
		$email_options = wpforms_get_form_fields( $this->form_data, [ 'email' ] );

		if ( empty( $email_options ) ) {
			return $fields;
		}

		foreach ( $email_options as $id => $email_option ) {
			$fields[ $id ] = ! empty( $email_option['label'] )
				? esc_attr( $email_option['label'] )
				: sprintf( /* translators: %d - field ID. */
					esc_html__( 'Field #%d', 'wpforms-lite' ),
					absint( $id )
				);
		}

		return $fields;
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

		$sublabels         = $this->get_sublabels();
		$sublabel_position = ! empty( $field['sublabel_position'] ) ? $field['sublabel_position'] : 'above';
		$hide_link_email   = ! empty( $field['link_email'] ) ? 'wpforms-hidden' : '';
		?>

		<div class="format-selected wpforms-stripe-payment-element <?php echo esc_attr( $sublabel_position ); ?>">
			<div class="wpforms-field-row wpforms-stripe-link-email <?php echo esc_attr( $hide_link_email ); ?>">
				<?php $this->input_preview( $sublabels['email'] ); ?>
			</div>
			<div class="wpforms-field-row">
				<?php $this->input_preview( $sublabels['number'] ); ?>
				<div class="wpforms-stripe-cardnumber-pics"></div>
			</div>
			<div class="wpforms-field-row">
				<div class="wpforms-one-half">
					<?php $this->input_preview( $sublabels['exp'] ); ?>
				</div>
				<div class="wpforms-one-half last wpforms-stripe-cvc">
					<?php $this->input_preview( $sublabels['cvv'] ); ?>
					<?php echo self::FIELD_PREVIEW_CVC_ICON_SVG; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
			</div>
			<div class="wpforms-field-row">
				<label class="wpforms-sub-label"><?php echo esc_attr( $sublabels['country'] ); ?></label>
				<?php $this->get_country_dropdown_preview( $field ); ?>
			</div>
		</div>

		<?php
		// Description.
		$this->field_preview_option( 'description', $field );
	}

	/**
	 * Input preview html output.
	 *
	 * @since 1.8.2
	 *
	 * @param string $label Label text.
	 */
	private function input_preview( $label ) {

		echo '<label class="wpforms-sub-label">' . esc_html( $label ) . '</label>';
		echo '<input type="text" placeholder="' . esc_attr( $label ) . '" readonly>';
	}

	/**
	 * Get Sublabels strings.
	 *
	 * @since 1.8.2
	 *
	 * @return array
	 */
	private function get_sublabels() {

		return [
			'email'   => __( 'Email', 'wpforms-lite' ),
			'number'  => __( 'Card Number', 'wpforms-lite' ),
			'exp'     => __( 'Expiration', 'wpforms-lite' ),
			'cvv'     => __( 'CVC', 'wpforms-lite' ),
			'country' => __( 'Country', 'wpforms-lite' ),
		];
	}

	/**
	 * Get Country dropdown preview.
	 *
	 * @since 1.8.2
	 *
	 * @param array $field Field settings.
	 */
	private function get_country_dropdown_preview( $field ) {

		$display_label = ! empty( $field['sublabel_position'] ) && $field['sublabel_position'] === 'above';
		$sublabels     = $this->get_sublabels();

		echo '<select readonly>';
			echo '<option value="empty" ' . selected( $display_label, true, false ) . '></option>';
			echo '<option value="country" ' . selected( ! $display_label, true, false ) . '>';
			echo esc_attr( $sublabels['country'] );
			echo '</option>';
		echo '</select>';
	}

	/**
	 * Block editor field preview.
	 *
	 * @since 1.8.2
	 *
	 * @param array $field Field settings.
	 */
	private function block_editor_field_display( $field ) {

		$hide_sub_label    = ! empty( $field['sublabel_hide'] );
		$sublabel_position = ! empty( $field['sublabel_position'] ) ? $field['sublabel_position'] : 'above';
		$field_class       = 'wpforms-field-row wpforms-field-row-responsive wpforms-field-' . sanitize_html_class( $field['size'] );
		$no_columns_class  = 'wpforms-field-row wpforms-no-columns wpforms-field-' . sanitize_html_class( $field['size'] );
		$sublabels         = $this->get_sublabels();
		?>

		<div class="format-selected wpforms-stripe-payment-element">

			<?php if ( empty( $field['link_email'] ) ) : ?>
				<div class="<?php echo esc_attr( $no_columns_class ); ?> ">
					<?php $this->block_editor_input_preview( $sublabels['email'], $sublabel_position, $hide_sub_label ); ?>
				</div>
			<?php endif; ?>

			<div class="<?php echo esc_attr( $no_columns_class ); ?>">
				<?php $this->block_editor_input_preview( $sublabels['number'], $sublabel_position, $hide_sub_label, '1234 1234 1234 1234' ); ?>
				<div class="wpforms-stripe-payment-element-cardnumber-preview"></div>
			</div>

			<div class="<?php echo esc_attr( $field_class ); ?>">
				<div class="wpforms-field-row-block wpforms-one-half wpforms-first">
					<?php $this->block_editor_input_preview( $sublabels['exp'], $sublabel_position, $hide_sub_label, 'MM / YY' ); ?>
				</div>
				<div class="wpforms-field-row-block wpforms-one-half wpforms-stripe-payment-element-cvc-preview">
					<?php $this->block_editor_input_preview( $sublabels['cvv'], $sublabel_position, $hide_sub_label, 'CVC' ); ?>
					<?php echo self::FIELD_PREVIEW_CVC_ICON_SVG; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
			</div>

			<div class="<?php echo esc_attr( $no_columns_class ); ?>">
				<?php if ( $sublabel_position === 'above' && ! $hide_sub_label ) : ?>
					<label class="wpforms-field-sublabel before"><?php echo esc_attr( $sublabels['country'] ); ?></label>
				<?php endif; ?>
				<?php $this->get_country_dropdown_preview( $field ); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Get block editor input preview html.
	 *
	 * @since 1.8.2
	 *
	 * @param string $label       Label text.
	 * @param string $position    Label Position.
	 * @param bool   $hide        Hide label.
	 * @param string $placeholder Placeholder text.
	 */
	private function block_editor_input_preview( $label, $position, $hide, $placeholder = '' ) {

		if ( $hide ) {
			echo '<input type="text" readonly placeholder="' . esc_attr( $placeholder ) . '">';

			return;
		}

		if ( $position === 'above' ) {
			echo '<label class="wpforms-field-sublabel before">' . esc_html( $label ) . '</label><input type="text" readonly placeholder="' . esc_attr( $placeholder ) . '">';

			return;
		}

		echo '<input type="text" readonly placeholder="' . esc_attr( $label ) . '">';
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

		$form_id = absint( $form_data['id'] );

		$hide_sub_label    = ! empty( $field['sublabel_hide'] ) ? 'wpforms-sublabel-hide' : '';
		$sublabel_position = ! empty( $field['sublabel_position'] ) ? $field['sublabel_position'] : 'above';
		$link_email        = ! empty( $field['link_email'] ) ? $field['link_email'] : '';

		// Row wrapper.
		echo '<div class="wpforms-field-row wpforms-no-columns wpforms-field-' . sanitize_html_class( $field['size'] ) . ' ' . sanitize_html_class( $hide_sub_label ) . '" data-sublabel-position="' . esc_attr( $sublabel_position ) . '" data-link-email="' . esc_attr( $link_email ) . '" data-required="' . (int) ! empty( $field['required'] ) . '">';

		if ( ! $link_email ) {
			echo '<div id="wpforms-field-stripe-link-element-' . absint( $form_id ) . '"></div>';
		}

		echo '<div id="wpforms-field-stripe-payment-element-' . absint( $form_id ) . '"></div>';
		echo '<input type="text" class="wpforms-stripe-credit-card-hidden-input" name="wpforms[stripe-credit-card-hidden-input-' . absint( $form_data['id'] ) . ']" disabled style="display: none;">';
		echo '</div>';
	}
}
