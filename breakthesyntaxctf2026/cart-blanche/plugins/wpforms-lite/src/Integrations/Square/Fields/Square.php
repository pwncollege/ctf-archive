<?php

namespace WPForms\Integrations\Square\Fields;

use WPForms_Field;
use WPForms\Integrations\Square\Connection;
use WPForms\Integrations\Square\Helpers;

/**
 * Square credit card field.
 *
 * @since 1.9.5
 */
class Square extends WPForms_Field {

	/**
	 * Primary class constructor.
	 *
	 * @since 1.9.5
	 */
	public function init() {

		// Define field type information.
		$this->name     = esc_html__( 'Square', 'wpforms-lite' );
		$this->keywords = esc_html__( 'store, ecommerce, credit card, pay, payment, debit card', 'wpforms-lite' );
		$this->type     = 'square';
		$this->icon     = 'fa-credit-card';
		$this->order    = 92;
		$this->group    = 'payment';

		$this->hooks();
	}

	/**
	 * Field specific hooks.
	 *
	 * @since 1.14.0
	 *
	 * @return void
	 */
	private function hooks() {

		add_filter( 'wpforms_field_properties_square', [ $this, 'field_properties' ], 5, 3 );
		add_filter( 'wpforms_field_new_required', [ $this, 'default_required' ], 10, 2 );
		add_filter( 'wpforms_builder_field_button_attributes', [ $this, 'field_button_atts' ], 10, 3 );
		add_filter( 'wpforms_field_new_display_duplicate_button', [ $this, 'field_display_duplicate_button' ], 10, 2 );
		add_filter(
			'wpforms_field_preview_display_duplicate_button',
			[ $this, 'field_display_duplicate_button' ],
			10,
			2
		);
		add_filter(
			'wpforms_pro_fields_entry_preview_is_field_support_preview_square_field',
			[ $this, 'entry_preview_availability' ],
			10,
			4
		);
		add_filter( 'wpforms_field_display_sublabel_skip_for', [ $this, 'skip_sublabel_for_attribute' ], 10, 3 );
	}

	/**
	 * Define additional field properties.
	 *
	 * @since 1.9.5
	 *
	 * @param array $properties Field properties.
	 * @param array $field      Field settings.
	 * @param array $form_data  Form data and settings.
	 *
	 * @return array
	 */
	public function field_properties( $properties, array $field, array $form_data ): array {

		$properties = (array) $properties;

		unset( $properties['label']['attr']['for'] );

		$form_id  = absint( $form_data['id'] );
		$field_id = absint( $field['id'] );
		$props    = [
			'inputs' => [
				'number' => [
					'attr'     => [
						'name'  => '',
						'value' => '',
					],
					'block'    => [
						'wpforms-field-square-number',
					],
					'class'    => [
						'wpforms-field-square-cardnumber',
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
						'name'        => "wpforms[fields][{$field_id}][cardname]",
						'placeholder' => ! empty( $field['cardname_placeholder'] ) ? $field['cardname_placeholder'] : '',
					],
					'block'    => [
						'wpforms-field-square-name',
					],
					'class'    => [
						'wpforms-field-square-cardname',
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

		// If this field is required, we need to make some adjustments.
		if ( ! empty( $field['required'] ) ) {

			// Add required class if needed (for multipage validation).
			$properties['inputs']['number']['class'][] = 'wpforms-field-required';
			$properties['inputs']['name']['class'][]   = 'wpforms-field-required';
		}

		return $properties;
	}

	/**
	 * Default to the required.
	 *
	 * @since 1.9.5
	 *
	 * @param bool  $required Required status, true is required.
	 * @param array $field    Field settings.
	 *
	 * @return bool
	 */
	public function default_required( $required, array $field ): bool {

		return $this->type === $field['type'] ? true : (bool) $required;
	}

	/**
	 * Define additional "Add Field" button attributes.
	 *
	 * @since 1.9.5
	 *
	 * @param array $atts      Add Field button attributes.
	 * @param array $field     Field settings.
	 * @param array $form_data Form data and settings.
	 *
	 * @return array
	 */
	public function field_button_atts( $atts, array $field, array $form_data ): array {

		$atts = (array) $atts;

		if ( $field['type'] !== $this->type ) {
			return $atts;
		}

		if ( Helpers::has_square_field( $form_data ) ) {
			$atts['atts']['disabled'] = 'true';
			$atts['class'][]          = 'wpforms-add-fields-button-disabled';

			return $atts;
		}

		if ( ! Connection::get() ) {
			$atts['class'][] = 'warning-modal';
			$atts['class'][] = 'square-connection-required';
		}

		return $atts;
	}

	/**
	 * Disallow field preview "Duplicate" button.
	 *
	 * @since 1.9.5
	 *
	 * @param bool  $display Display switch.
	 * @param array $field   Field settings.
	 *
	 * @return bool
	 */
	public function field_display_duplicate_button( $display, array $field ): bool {

		return $field['type'] === $this->type ? false : (bool) $display;
	}

	/**
	 * The field value availability for the Entry Preview field.
	 *
	 * @since 1.9.5
	 *
	 * @param bool         $is_supported The field availability.
	 * @param string|array $value        The submitted Credit Card detail.
	 * @param array        $field        Field data.
	 * @param array        $form_data    Form data.
	 *
	 * @return bool
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function entry_preview_availability( $is_supported, $value, array $field, array $form_data ): bool {

		return ! empty( $value );
	}

	/**
	 * Disallow dynamic population.
	 *
	 * @since 1.9.5
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
	 * @since 1.9.5
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
	 * @since 1.9.5
	 *
	 * @param array $field Field settings.
	 */
	public function field_options( $field ) {
		/*
		 * Basic field options.
		 */

		// Options open markup.
		$args = [
			'markup' => 'open',
		];

		$this->field_option( 'basic-options', $field, $args );

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

		// Card Name.
		$cardname_placeholder = ! empty( $field['cardname_placeholder'] ) ? esc_attr( $field['cardname_placeholder'] ) : '';
		$cardname_field       = sprintf( '<div class="placeholder"><input type="text" class="placeholder-update" id="wpforms-field-option-%1$d-cardname_placeholder" name="fields[%1$d][cardname_placeholder]" value="%2$s" data-field-id="%1$d" data-subfield="square-cardname"></div>', absint( $field['id'] ), esc_html( $cardname_placeholder ) );

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
			echo $cardname_field; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
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
	 * @since 1.9.5
	 *
	 * @param array $field Field settings.
	 */
	public function field_preview( $field ) {

		// Label.
		$this->field_preview_option( 'label', $field );

		// Placeholder.
		$this->field_preview_placeholder( $field );

		// Description.
		$this->field_preview_option( 'description', $field );
	}

	/**
	 * Field display on the form front-end.
	 *
	 * @since 1.9.5
	 *
	 * @param array $field      Field data and settings.
	 * @param array $deprecated Deprecated field attributes. Use field properties.
	 * @param array $form_data  Form data and settings.
	 *
	 * @noinspection HtmlUnknownAttribute
	 */
	public function field_display( $field, $deprecated, $form_data ) {

		if ( wpforms_is_editor_page() ) {
			$this->field_preview_placeholder( $field );

			return;
		}

		// Define data.
		$number = ! empty( $field['properties']['inputs']['number'] ) ? $field['properties']['inputs']['number'] : [];
		$name   = ! empty( $field['properties']['inputs']['name'] ) ? $field['properties']['inputs']['name'] : [];

		// Display warning for non SSL pages.
		if ( ! is_ssl() ) {
			echo '<div class="wpforms-cc-warning wpforms-error-alert">';
				esc_html_e( 'This page is insecure. Credit Card field should be used for testing purposes only.', 'wpforms-lite' );
			echo '</div>';
		}

		$connection = Connection::get();

		if ( ! $connection ) {
			echo '<div class="wpforms-cc-warning wpforms-error-alert">';
				esc_html_e( 'Credit Card field is disabled, Square account connection is missing.', 'wpforms-lite' );
			echo '</div>';

			return;
		}

		if ( ! $connection->is_usable() ) {
			echo '<div class="wpforms-cc-warning wpforms-error-alert">';
				esc_html_e( 'Credit Card field is disabled, Square account connection is invalid. Please, contact to the site administrator.', 'wpforms-lite' );
			echo '</div>';

			return;
		}

		if ( ! Helpers::is_payments_enabled( $form_data ) ) {
			echo '<div class="wpforms-cc-warning wpforms-error-alert">';
				esc_html_e( 'Credit Card field is disabled, Square payments are not enabled in the form settings.', 'wpforms-lite' );
			echo '</div>';

			return;
		}

		if ( $connection->is_expired() && wpforms_current_user_can() ) {
			echo '<div class="wpforms-cc-warning wpforms-error-alert">';
				esc_html_e( 'Heads up! Square account connection is expired. Tokens must be refreshed.', 'wpforms-lite' );
			echo '</div>';
		}

		// Row wrapper.
		echo '<div class="wpforms-field-row wpforms-field-' . sanitize_html_class( $field['size'] ) . '">';
			echo '<div ' . wpforms_html_attributes( false, $number['block'] ) . '>';
				$this->field_display_sublabel( 'number', 'before', $field );
				printf(
					'<div %s data-required="%s"><!-- Square credit card will be inserted here. --></div>',
					wpforms_html_attributes( $number['id'], $number['class'], $number['data'], $number['attr'] ),
					esc_attr( $number['required'] )
				);

				// Hidden input is needed for validation on the frontend and as a substitute in Block Editor previews.
				printf(
					'<input
						type="text"
						class="wpforms-square-credit-card-hidden-input"
						name="wpforms[square-credit-card-hidden-input-%1$d]"
						id="wpforms-square-credit-card-hidden-input-%1$d"
						%2$s>',
					(int) $form_data['id'],
					wpforms_is_editor_page() ? '' : 'style="display: none;" disabled'
				);
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
					esc_attr( $name['required'] )
				);
				$this->field_display_sublabel( 'name', 'after', $field );
				$this->field_display_error( 'name', $field );
			echo '</div>';
		echo '</div>';
	}

	/**
	 * Currently validation happens on the front end. We do not do
	 * generic server-side validation because we do not allow the card
	 * details to POST to the server.
	 *
	 * @since 1.9.5
	 *
	 * @param int   $field_id     Field ID.
	 * @param array $field_submit Submitted field value.
	 * @param array $form_data    Form data and settings.
	 */
	public function validate( $field_id, $field_submit, $form_data ) {}

	/**
	 * Format field.
	 *
	 * @since 1.9.5
	 *
	 * @param int   $field_id     Field ID.
	 * @param array $field_submit Submitted field value.
	 * @param array $form_data    Form data and settings.
	 */
	public function format( $field_id, $field_submit, $form_data ) {

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
	 * Card field placeholder.
	 *
	 * @since 1.9.5
	 *
	 * @param array $field Current field specific data.
	 */
	private function field_preview_placeholder( array $field ) {

		// Define data.
		$name_placeholder = ! empty( $field['cardname_placeholder'] ) ? esc_attr( $field['cardname_placeholder'] ) : '';
		$size             = ! empty( $field['size'] ) ? sprintf( 'wpforms-field-%s', sanitize_html_class( $field['size'] ) ) : '';
		$hide_sub_label   = ! empty( $field['sublabel_hide'] );
		?>
		<div class="format-selected format-selected-full">

			<div class="wpforms-field-row">
				<div class="wpforms-square-cardnumber">
					<div class="wpforms-square-cardnumber-wrapper <?php echo esc_attr( $size ); ?>">
						<div class="card-number">
							<div class="card-icon">
								<svg width="36" height="24">
									<linearGradient id="a" x1="18" x2="18" y1="54.1" y2="-16.7" gradientTransform="matrix(1 0 0 -1 0 26)" gradientUnits="userSpaceOnUse">
										<stop offset="0" stop-color="#626364"/>
										<stop offset="1" stop-color="#414447"/>
									</linearGradient>
									<path fill="url(#a)" d="M4 0h28a4 4 0 0 1 4 4v16a4 4 0 0 1-4 4H4a4 4 0 0 1-4-4V4a4 4 0 0 1 4-4z"/>
									<path fill="#fff" fill-opacity=".3" d="M7 12h22c.6 0 1 .4 1 1s-.4 1-1 1H7c-.6 0-1-.4-1-1s.4-1 1-1zm-.5 5h6c.3 0 .5.2.5.5s-.2.5-.5.5h-6c-.3 0-.5-.2-.5-.5s.2-.5.5-.5z"/>
								</svg>
							</div>
							<input type="text" placeholder="<?php esc_html_e( 'Card number', 'wpforms-lite' ); ?>" disabled>
						</div>
						<div class="card-data">
							<input type="text" class="exp-input-wrapper" placeholder="<?php esc_html_e( 'MM/YY', 'wpforms-lite' ); ?>" disabled>
							<input type="text" class="cvv-input-wrapper" placeholder="<?php esc_html_e( 'CVV', 'wpforms-lite' ); ?>" disabled>
						</div>
					</div>
					<label class="wpforms-sub-label wpforms-field-sublabel <?php echo $hide_sub_label ? 'wpforms-sublabel-hide' : ''; ?>"><?php esc_html_e( 'Card', 'wpforms-lite' ); ?></label>
				</div>
			</div>

			<div class="wpforms-field-row">
				<div class="wpforms-square-cardname">
					<input type="text" class="<?php echo esc_attr( $size ); ?>" placeholder="<?php echo esc_attr( $name_placeholder ); ?>" disabled>
					<label class="wpforms-sub-label wpforms-field-sublabel <?php echo $hide_sub_label ? 'wpforms-sublabel-hide' : ''; ?>"><?php esc_html_e( 'Name on Card', 'wpforms-lite' ); ?></label>
				</div>
			</div>
		</div>

		<?php
	}

	/**
	 * Do not add the `for` attribute to certain sublabels.
	 *
	 * @since 1.9.5
	 *
	 * @param bool   $skip  Whether to skip the `for` attribute.
	 * @param string $key   Input key.
	 * @param array  $field Field data and settings.
	 *
	 * @return bool
	 */
	public function skip_sublabel_for_attribute( $skip, string $key, array $field ): bool {

		$skip = (bool) $skip;

		if ( $field['type'] !== $this->type ) {
			return $skip;
		}

		if ( in_array( $key, [ 'name', 'number' ], true ) ) {
			return true;
		}

		return $skip;
	}
}
