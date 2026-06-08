<?php

namespace WPForms\Forms\Fields\Address;

use WPForms\Forms\Fields\Traits\ProField as ProFieldTrait;
use WPForms_Field;

/**
 * Address field.
 *
 * @since 1.9.4
 */
class Field extends WPForms_Field {

	use ProFieldTrait;

	/**
	 * Address schemes: 'us' or 'international' by default.
	 *
	 * @since 1.9.4
	 *
	 * @var array
	 */
	public $schemes;

	/**
	 * Primary class constructor.
	 *
	 * @since 1.9.4
	 */
	public function init() {

		// Define field type information.
		$this->name  = esc_html__( 'Address', 'wpforms-lite' );
		$this->type  = 'address';
		$this->icon  = 'fa-map-marker';
		$this->order = 70;
		$this->group = 'fancy';

		// Allow for additional or customizing address schemes.
		$default_schemes = [
			'us'            => [
				'label'          => esc_html__( 'US', 'wpforms-lite' ),
				'address1_label' => esc_html__( 'Address Line 1', 'wpforms-lite' ),
				'address2_label' => esc_html__( 'Address Line 2', 'wpforms-lite' ),
				'city_label'     => esc_html__( 'City', 'wpforms-lite' ),
				'postal_label'   => esc_html__( 'Zip Code', 'wpforms-lite' ),
				'state_label'    => esc_html__( 'State', 'wpforms-lite' ),
				'states'         => wpforms_us_states(),
			],
			'international' => [
				'label'          => esc_html__( 'International', 'wpforms-lite' ),
				'address1_label' => esc_html__( 'Address Line 1', 'wpforms-lite' ),
				'address2_label' => esc_html__( 'Address Line 2', 'wpforms-lite' ),
				'city_label'     => esc_html__( 'City', 'wpforms-lite' ),
				'postal_label'   => esc_html__( 'Postal Code', 'wpforms-lite' ),
				'state_label'    => esc_html__( 'State / Province / Region', 'wpforms-lite' ),
				'states'         => '',
				'country_label'  => esc_html__( 'Country', 'wpforms-lite' ),
				'countries'      => wpforms_countries(),
			],
		];

		/**
		 * Allow modifying address schemes.
		 *
		 * @since 1.2.7
		 *
		 * @param array $schemes Address schemes.
		 */
		$this->schemes = apply_filters( 'wpforms_address_schemes', $default_schemes ); // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName

		$this->init_pro_field();
		$this->hooks();
	}

	/**
	 * Hooks.
	 *
	 * @since 1.9.4
	 */
	protected function hooks() {
	}

	/**
	 * Field options panel inside the builder.
	 *
	 * @since 1.9.4
	 *
	 * @param array $field Field data.
	 */
	public function field_options( $field ) { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

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

		// Address Scheme - was "format" key prior to 1.2.7.
		$scheme = ! empty( $field['scheme'] ) ? esc_attr( $field['scheme'] ) : 'us';

		if ( empty( $scheme ) && ! empty( $field['format'] ) ) {
			$scheme = esc_attr( $field['format'] );
		}

		$tooltip = esc_html__( 'Select scheme format for the address field.', 'wpforms-lite' );

		$options = array_map(
			static function ( $s ) {

				return $s['label'];
			},
			$this->schemes
		);

		$output = $this->field_element(
			'label',
			$field,
			[
				'slug'    => 'scheme',
				'value'   => esc_html__( 'Scheme', 'wpforms-lite' ),
				'tooltip' => $tooltip,
			],
			false
		);

		$output .= $this->field_element(
			'select',
			$field,
			[
				'slug'    => 'scheme',
				'value'   => $scheme,
				'options' => $options,
			],
			false
		);

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'scheme',
				'content' => $output,
			]
		);

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

		// Size.
		$this->field_option( 'size', $field );

		// Address Line 1.
		$address1_placeholder = ! empty( $field['address1_placeholder'] ) ? esc_attr( $field['address1_placeholder'] ) : '';
		$address1_default     = ! empty( $field['address1_default'] ) ? esc_attr( $field['address1_default'] ) : '';

		printf(
			'<div class="wpforms-clear wpforms-field-option-row wpforms-field-option-row-address1"
				id="wpforms-field-option-row-%1$d-address1"
				data-subfield="address-1"
				data-field-id="%1$s">',
			wpforms_validate_field_id( $field['id'] ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		);

			$this->field_element(
				'label',
				$field,
				[
					'slug'  => 'address1_placeholder',
					'value' => esc_html__( 'Address Line 1', 'wpforms-lite' ),
				]
			);

			// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
			echo '<div class="wpforms-field-options-columns-2 wpforms-field-options-columns">';
				echo '<div class="placeholder wpforms-field-options-column">';
					printf( '<input type="text" class="placeholder" id="wpforms-field-option-%1$s-address1_placeholder" name="fields[%1$s][address1_placeholder]" value="%2$s">', wpforms_validate_field_id( $field['id'] ), esc_attr( $address1_placeholder ) );
					printf( '<label for="wpforms-field-option-%s-address1_placeholder" class="sub-label">%s</label>', wpforms_validate_field_id( $field['id'] ), esc_html__( 'Placeholder', 'wpforms-lite' ) );
				echo '</div>';
				echo '<div class="default wpforms-field-options-column">';
					printf( '<input type="text" class="default" id="wpforms-field-option-%1$d-address1_default" name="fields[%1$s][address1_default]" value="%2$s">', wpforms_validate_field_id( $field['id'] ), esc_attr( $address1_default ) );
					printf( '<label for="wpforms-field-option-%s-address1_default" class="sub-label">%s</label>', wpforms_validate_field_id( $field['id'] ), esc_html__( 'Default Value', 'wpforms-lite' ) );
				echo '</div>';
			echo '</div>';
			// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped

		echo '</div>';

		// Address Line 2.
		$address2_placeholder = ! empty( $field['address2_placeholder'] ) ? esc_attr( $field['address2_placeholder'] ) : '';
		$address2_default     = ! empty( $field['address2_default'] ) ? esc_attr( $field['address2_default'] ) : '';
		$address2_hide        = ! empty( $field['address2_hide'] );

		printf(
			'<div class="wpforms-clear wpforms-field-option-row wpforms-field-option-row-address2"
				id="wpforms-field-option-row-%1$d-address2"
				data-subfield="address-2"
				data-field-id="%1$s">',
			wpforms_validate_field_id( $field['id'] ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		);

		echo '<div class="wpforms-field-header">';

			$this->field_element(
				'label',
				$field,
				[
					'slug'  => 'address2_placeholder',
					'value' => esc_html__( 'Address Line 2', 'wpforms-lite' ),
				]
			);

			$this->field_element(
				'toggle',
				$field,
				[
					'slug'          => 'address2_hide',
					'value'         => $address2_hide,
					'desc'          => esc_html__( 'Hide', 'wpforms-lite' ),
					'title'         => esc_html__( 'Turn On if you want to hide this sub field.', 'wpforms-lite' ),
					'label-left'    => true,
					'control-class' => 'wpforms-field-option-in-label-right',
					'class'         => 'wpforms-subfield-hide',
				]
			);

			echo '</div>';

			// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
			echo '<div class="wpforms-field-options-columns-2 wpforms-field-options-columns">';
				echo '<div class="placeholder wpforms-field-options-column">';
					printf( '<input type="text" class="placeholder" id="wpforms-field-option-%1$d-address2_placeholder" name="fields[%1$s][address2_placeholder]" value="%2$s">', wpforms_validate_field_id( $field['id'] ), esc_attr( $address2_placeholder ) );
					printf( '<label for="wpforms-field-option-%s-address2_placeholder" class="sub-label">%s</label>', wpforms_validate_field_id( $field['id'] ), esc_html__( 'Placeholder', 'wpforms-lite' ) );
				echo '</div>';
				echo '<div class="default wpforms-field-options-column">';
					printf( '<input type="text" class="default" id="wpforms-field-option-%1$d-address2_default" name="fields[%1$s][address2_default]" value="%2$s">', wpforms_validate_field_id( $field['id'] ), esc_attr( $address2_default ) );
					printf( '<label for="wpforms-field-option-%s-address2_default" class="sub-label">%s</label>', wpforms_validate_field_id( $field['id'] ), esc_html__( 'Default Value', 'wpforms-lite' ) );
				echo '</div>';
			echo '</div>';
			// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped

		echo '</div>';

		// City.
		$city_placeholder = ! empty( $field['city_placeholder'] ) ? esc_attr( $field['city_placeholder'] ) : '';
		$city_default     = ! empty( $field['city_default'] ) ? esc_attr( $field['city_default'] ) : '';

		printf(
			'<div class="wpforms-clear wpforms-field-option-row wpforms-field-option-row-city"
				id="wpforms-field-option-row-%1$s-city"
				data-subfield="city"
				data-field-id="%1$s">',
			wpforms_validate_field_id( $field['id'] ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		);

			$this->field_element(
				'label',
				$field,
				[
					'slug'  => 'city_placeholder',
					'value' => esc_html__( 'City', 'wpforms-lite' ),
				]
			);

			// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
			echo '<div class="wpforms-field-options-columns-2 wpforms-field-options-columns">';
				echo '<div class="placeholder wpforms-field-options-column">';
					printf( '<input type="text" class="placeholder" id="wpforms-field-option-%1$s-city_placeholder" name="fields[%1$s][city_placeholder]" value="%2$s">', wpforms_validate_field_id( $field['id'] ), esc_attr( $city_placeholder ) );
					printf( '<label for="wpforms-field-option-%s-city_placeholder" class="sub-label">%s</label>', wpforms_validate_field_id( $field['id'] ), esc_html__( 'Placeholder', 'wpforms-lite' ) );
				echo '</div>';
				echo '<div class="default wpforms-field-options-column">';
					printf( '<input type="text" class="default" id="wpforms-field-option-%1$s-city_default" name="fields[%1$s][city_default]" value="%2$s">', wpforms_validate_field_id( $field['id'] ), esc_attr( $city_default ) );
					printf( '<label for="wpforms-field-option-%s-city_default" class="sub-label">%s</label>', wpforms_validate_field_id( $field['id'] ), esc_html__( 'Default Value', 'wpforms-lite' ) );
				echo '</div>';
			echo '</div>';
			// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped

		echo '</div>';

		// State.
		$state_placeholder = ! empty( $field['state_placeholder'] ) ? $field['state_placeholder'] : '';

		printf(
			'<div class="wpforms-clear wpforms-field-option-row wpforms-field-option-row-state"
				id="wpforms-field-option-row-%1$s-state"
				data-subfield="state"
				data-field-id="%1$s">',
			wpforms_validate_field_id( $field['id'] ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		);

			$this->field_element(
				'label',
				$field,
				[
					'slug'  => 'state_placeholder',
					'value' => esc_html__( 'State / Province / Region', 'wpforms-lite' ),
				]
			);

			// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
			echo '<div class="wpforms-field-options-columns-2 wpforms-field-options-columns">';
				echo '<div class="placeholder wpforms-field-options-column">';
					printf( '<input type="text" class="placeholder" id="wpforms-field-option-%1$s-state_placeholder" name="fields[%1$s][state_placeholder]" value="%2$s">', wpforms_validate_field_id( $field['id'] ), esc_attr( $state_placeholder ) );
					printf( '<label for="wpforms-field-option-%s-state_placeholder" class="sub-label">%s</label>', wpforms_validate_field_id( $field['id'] ), esc_html__( 'Placeholder', 'wpforms-lite' ) );
				echo '</div>';
				echo '<div class="default wpforms-field-options-column">';
					$this->subfield_default( $field, 'state', 'states' );
					printf( '<label for="wpforms-field-option-%s-state_default" class="sub-label">%s</label>', wpforms_validate_field_id( $field['id'] ), esc_html__( 'Default Value', 'wpforms-lite' ) );
				echo '</div>';
			echo '</div>';
			// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped

		echo '</div>';

		// ZIP/Postal.
		$postal_placeholder = ! empty( $field['postal_placeholder'] ) ? esc_attr( $field['postal_placeholder'] ) : '';
		$postal_default     = ! empty( $field['postal_default'] ) ? esc_attr( $field['postal_default'] ) : '';
		$postal_hide        = ! empty( $field['postal_hide'] );
		$postal_visibility  = ! isset( $this->schemes[ $scheme ]['postal_label'] ) ? 'wpforms-hidden' : '';

		printf(
			'<div class="wpforms-clear wpforms-field-option-row wpforms-field-option-row-postal %1$s"
				id="wpforms-field-option-row-%2$s-postal"
				data-subfield="postal"
				data-field-id="%2$s">',
			sanitize_html_class( $postal_visibility ),
			wpforms_validate_field_id( $field['id'] ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		);

		echo '<div class="wpforms-field-header">';

			$this->field_element(
				'label',
				$field,
				[
					'slug'  => 'postal_placeholder',
					'value' => esc_html__( 'ZIP / Postal', 'wpforms-lite' ),
				]
			);

			$this->field_element(
				'toggle',
				$field,
				[
					'slug'          => 'postal_hide',
					'value'         => $postal_hide,
					'desc'          => esc_html__( 'Hide', 'wpforms-lite' ),
					'title'         => esc_html__( 'Turn On if you want to hide this sub field.', 'wpforms-lite' ),
					'label-left'    => true,
					'control-class' => 'wpforms-field-option-in-label-right',
					'class'         => 'wpforms-subfield-hide',
				]
			);

			echo '</div>';

			// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
			echo '<div class="wpforms-field-options-columns-2 wpforms-field-options-columns">';
				echo '<div class="placeholder wpforms-field-options-column">';
					printf( '<input type="text" class="placeholder" id="wpforms-field-option-%1$s-postal_placeholder" name="fields[%1$s][postal_placeholder]" value="%2$s">', wpforms_validate_field_id( $field['id'] ), esc_attr( $postal_placeholder ) );
					printf( '<label for="wpforms-field-option-%s-postal_placeholder" class="sub-label">%s</label>', wpforms_validate_field_id( $field['id'] ), esc_html__( 'Placeholder', 'wpforms-lite' ) );
				echo '</div>';
				echo '<div class="default wpforms-field-options-column">';
					printf( '<input type="text" class="default" id="wpforms-field-option-%1$s-postal_default" name="fields[%1$s][postal_default]" value="%2$s">', wpforms_validate_field_id( $field['id'] ), esc_attr( $postal_default ) );
					printf( '<label for="wpforms-field-option-%s-postal_default" class="sub-label">%s</label>', wpforms_validate_field_id( $field['id'] ), esc_html__( 'Default Value', 'wpforms-lite' ) );
				echo '</div>';
			echo '</div>';
			// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped

		echo '</div>';

		// Country.
		$country_placeholder = ! empty( $field['country_placeholder'] ) ? $field['country_placeholder'] : '';
		$country_hide        = ! empty( $field['country_hide'] );
		$country_visibility  = ! isset( $this->schemes[ $scheme ]['countries'] ) ? 'wpforms-hidden' : '';

		printf(
			'<div class="wpforms-clear wpforms-field-option-row wpforms-field-option-row-country %1$s"
				id="wpforms-field-option-row-%2$s-country"
				data-subfield="country"
				data-field-id="%2$s">',
			sanitize_html_class( $country_visibility ),
			wpforms_validate_field_id( $field['id'] ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		);

			echo '<div class="wpforms-field-header">';

				$this->field_element(
					'label',
					$field,
					[
						'slug'  => 'country_placeholder',
						'value' => esc_html__( 'Country', 'wpforms-lite' ),
					]
				);

				$this->field_element(
					'toggle',
					$field,
					[
						'slug'          => 'country_hide',
						'value'         => $country_hide,
						'desc'          => esc_html__( 'Hide', 'wpforms-lite' ),
						'title'         => esc_html__( 'Turn On if you want to hide this sub field.', 'wpforms-lite' ),
						'label-left'    => true,
						'control-class' => 'wpforms-field-option-in-label-right',
						'class'         => 'wpforms-subfield-hide',
					]
				);

			echo '</div>';

			// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
			echo '<div class="wpforms-field-options-columns-2 wpforms-field-options-columns">';
				echo '<div class="placeholder wpforms-field-options-column">';
					printf( '<input type="text" class="placeholder" id="wpforms-field-option-%1$s-country_placeholder" name="fields[%1$s][country_placeholder]" value="%2$s">', wpforms_validate_field_id( $field['id'] ), esc_attr( $country_placeholder ) );
					printf( '<label for="wpforms-field-option-%s-country_placeholder" class="sub-label">%s</label>', wpforms_validate_field_id( $field['id'] ), esc_html__( 'Placeholder', 'wpforms-lite' ) );
				echo '</div>';
				echo '<div class="default wpforms-field-options-column">';
					$this->subfield_default( $field, 'country', 'countries' );
					printf( '<label for="wpforms-field-option-%s-country_default" class="sub-label">%s</label>', wpforms_validate_field_id( $field['id'] ), esc_html__( 'Default Value', 'wpforms-lite' ) );
				echo '</div>';
			echo '</div>';
			// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped

		echo '</div>';

		// Custom CSS classes.
		$this->field_option( 'css', $field );

		// Hide label.
		$this->field_option( 'label_hide', $field );

		// Hide sublabel.
		$this->field_option( 'sublabel_hide', $field );

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
	 * @since 1.9.4
	 *
	 * @param array $field Field data.
	 */
	public function field_preview( $field ) { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.MaxExceeded

		// Define data.
		$address1_placeholder = ! empty( $field['address1_placeholder'] ) ? $field['address1_placeholder'] : '';
		$address1_default     = ! empty( $field['address1_default'] ) ? $field['address1_default'] : '';
		$address2_placeholder = ! empty( $field['address2_placeholder'] ) ? $field['address2_placeholder'] : '';
		$address2_default     = ! empty( $field['address2_default'] ) ? $field['address2_default'] : '';
		$address2_hide        = ! empty( $field['address2_hide'] ) ? 'wpforms-hide' : '';
		$city_placeholder     = ! empty( $field['city_placeholder'] ) ? $field['city_placeholder'] : '';
		$city_default         = ! empty( $field['city_default'] ) ? $field['city_default'] : '';
		$postal_placeholder   = ! empty( $field['postal_placeholder'] ) ? $field['postal_placeholder'] : '';
		$postal_default       = ! empty( $field['postal_default'] ) ? $field['postal_default'] : '';
		$postal_hide          = ! empty( $field['postal_hide'] ) ? 'wpforms-hide' : '';
		$country_hide         = ! empty( $field['country_hide'] ) ? 'wpforms-hide' : '';
		$format               = ! empty( $field['format'] ) ? $field['format'] : 'us';
		$scheme_selected      = ! empty( $field['scheme'] ) ? $field['scheme'] : $format;

		// Label.
		$this->field_preview_option(
			'label',
			$field,
			[
				'label_badge' => $this->get_field_preview_badge(),
			]
		);

		// Field elements.
		foreach ( $this->schemes as $slug => $scheme ) {

			$address1_label = $scheme['address1_label'] ?? esc_html__( 'Address Line 1', 'wpforms-lite' );
			$address2_label = $scheme['address2_label'] ?? esc_html__( 'Address Line 2', 'wpforms-lite' );
			$city_label     = $scheme['city_label'] ?? esc_html__( 'City', 'wpforms-lite' );
			$state_label    = $scheme['state_label'] ?? esc_html__( 'State / Province / Region', 'wpforms-lite' );
			$postal_label   = $scheme['postal_label'] ?? esc_html__( 'Postal Code', 'wpforms-lite' );
			$country_label  = $scheme['country_label'] ?? esc_html__( 'Country', 'wpforms-lite' );

			$is_active_scheme  = $slug === $scheme_selected;
			$scheme_hide_class = ! $is_active_scheme ? 'wpforms-hide' : '';

			$state_placeholder   = ! empty( $field['state_placeholder'] ) ? $field['state_placeholder'] : '';
			$state_default       = $is_active_scheme && ! empty( $field['state_default'] ) ? $field['state_default'] : '';
			$country_placeholder = ! empty( $field['country_placeholder'] ) ? $field['country_placeholder'] : '';
			$country_default     = $is_active_scheme && ! empty( $field['country_default'] ) ? $field['country_default'] : '';

			// Wrapper.
			printf(
				'<div class="wpforms-address-scheme wpforms-address-scheme-%s %s">',
				wpforms_sanitize_classes( $slug ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				wpforms_sanitize_classes( $scheme_hide_class ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			);

			// Row 1 - Address Line 1.
			printf(
				'<div class="wpforms-field-row wpforms-address-1">
					<input type="text" placeholder="%s" value="%s" readonly>
					<label class="wpforms-sub-label">%s</label>
				</div>',
				esc_attr( $address1_placeholder ),
				esc_attr( $address1_default ),
				esc_html( $address1_label )
			);

			// Row 2 - Address Line 2.
			printf(
				'<div class="wpforms-field-row wpforms-address-2 %s">
					<input type="text" placeholder="%s" value="%s" readonly>
					<label class="wpforms-sub-label">%s</label>
				</div>',
				wpforms_sanitize_classes( $address2_hide ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				esc_attr( $address2_placeholder ),
				esc_attr( $address2_default ),
				esc_html( $address2_label )
			);

			// Row 3 - City & State.
			echo '<div class="wpforms-field-row">';

			// City.
			printf(
				'<div class="wpforms-city wpforms-one-half ">
					<input type="text" placeholder="%s" value="%s" readonly>
					<label class="wpforms-sub-label">%s</label>
				</div>',
				esc_attr( $city_placeholder ),
				esc_attr( $city_default ),
				esc_html( $city_label )
			);

			// State / Providence / Region.
			echo '<div class="wpforms-state wpforms-one-half last">';

				if ( isset( $scheme['states'] ) && empty( $scheme['states'] ) ) {

					// State text input.
					printf( '<input type="text" placeholder="%s" value="%s" readonly>', esc_attr( $state_placeholder ), esc_attr( $state_default ) );

				} elseif ( ! empty( $scheme['states'] ) && is_array( $scheme['states'] ) ) {

					$state_option = $this->dropdown_empty_value( (string) $state_label );

					if ( ! empty( $state_placeholder ) ) {
						$state_option = $state_placeholder;
					}

					if ( $is_active_scheme && ! empty( $state_default ) ) {
						$state_option = $scheme['states'][ $state_default ];
					}

					// State select.
					printf( '<select readonly> <option class="placeholder" selected>%s</option> </select>', esc_html( $state_option ) );
				}

			printf( '<label class="wpforms-sub-label">%s</label>', esc_html( $state_label ) );
			echo '</div>';

			// End row 3 - City & State.
			echo '</div>';

			// Row 4 - Zip & Country.
			echo '<div class="wpforms-field-row">';

			// ZIP / Postal.
			printf(
				'<div class="wpforms-postal wpforms-one-half %s">
					<input type="text" placeholder="%s" value="%s" readonly>
					<label class="wpforms-sub-label">%s</label>
				</div>',
				wpforms_sanitize_classes( $postal_hide ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				esc_attr( $postal_placeholder ),
				esc_attr( $postal_default ),
				esc_html( $postal_label )
			);

			// Country.
			printf( '<div class="wpforms-country wpforms-one-half last %s">', sanitize_html_class( $country_hide ) );

				if ( isset( $scheme['countries'] ) && empty( $scheme['countries'] ) ) {

					// Country text input.
					printf( '<input type="text" placeholder="%s" value="%s" readonly>', esc_attr( $country_placeholder ), esc_attr( $country_default ) );

				} elseif ( ! empty( $scheme['countries'] ) && is_array( $scheme['countries'] ) ) {

					$country_option = $this->dropdown_empty_value( (string) $country_label );

					if ( ! empty( $country_placeholder ) ) {
						$country_option = $country_placeholder;
					}

					if ( $is_active_scheme && ! empty( $country_default ) ) {
						$country_option = $scheme['countries'][ $country_default ];
					}

					// Country select.
					printf( '<select readonly><option class="placeholder" selected>%s</option></select>', esc_html( $country_option ) );
					printf( '<label class="wpforms-sub-label">%s</label>', esc_html( $country_label ) );
				}

			echo '</div>';

			// End row 4 - Zip & Country.
			echo '</div>';

			// End wrapper.
			echo '</div>';
		}

		// Description.
		$this->field_preview_option( 'description', $field );
	}

	/**
	 * Field display on the form front-end.
	 *
	 * @since 1.9.4
	 *
	 * @param array $field      Field data and settings.
	 * @param array $deprecated Deprecated field attributes. Use field properties instead.
	 * @param array $form_data  Form data and settings.
	 */
	public function field_display( $field, $deprecated, $form_data ) {
	}

	/**
	 * Output "Default" option fields for State/Country subfields.
	 *
	 * The default value should be set only for the scheme it belongs to.
	 *
	 * @since 1.9.4
	 *
	 * @param array  $field         Address field data.
	 * @param string $subfield_slug Subfield slug, either `state` or `country`.
	 * @param string $subfield_key  Subfield key in `$scheme` data, either `states` or `countries`.
	 *
	 * @noinspection HtmlUnknownAttribute
	 */
	private function subfield_default( array $field, string $subfield_slug, string $subfield_key ): void {

		// Scheme or default value may not be set yet.
		$active_scheme = ! empty( $field['scheme'] ) ? $field['scheme'] : 'us';
		$default_value = ! empty( $field[ "{$subfield_slug}_default" ] ) ? $field[ "{$subfield_slug}_default" ] : '';

		foreach ( $this->schemes as $scheme_slug => $scheme_data ) {

			$subfield_label   = empty( $scheme_data[ $subfield_slug . '_label' ] ) ? ucfirst( $subfield_slug ) : $scheme_data[ $subfield_slug . '_label' ];
			$empty_value      = $this->dropdown_empty_value( $subfield_label );
			$is_active_scheme = $scheme_slug === $active_scheme;

			// If a scheme contains an array of values, we display a select dropdown. Otherwise, text input.
			if ( ! empty( $scheme_data[ $subfield_key ] ) && is_array( $scheme_data[ $subfield_key ] ) ) {

				$options_escaped = sprintf( '<option value="">%s</option>', esc_html( $empty_value ) );

				foreach ( $scheme_data[ $subfield_key ] as $value => $label ) {
					$options_escaped .= sprintf(
						'<option value="%s"%s>%s</option>',
						esc_attr( $value ),
						$is_active_scheme ? selected( $default_value, $value, false ) : '',
						esc_html( $label )
					);
				}

				if ( $is_active_scheme ) {
					printf(
						'<select class="default" id="wpforms-field-option-%1$s-%2$s_default" name="fields[%1$s][%2$s_default]" data-scheme="%3$s">%4$s</select>',
						wpforms_validate_field_id( $field['id'] ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						esc_attr( $subfield_slug ),
						esc_attr( $scheme_slug ),
						$options_escaped // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					);

					continue;
				}

				printf(
					'<select class="default wpforms-hidden-strict" id="" name="" data-scheme="%s">%s</select>',
					esc_attr( $scheme_slug ),
					$options_escaped // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				);

				continue;
			}

			if ( $is_active_scheme ) {
				printf(
					'<input type="text" class="default" id="wpforms-field-option-%1$s-%2$s_default" name="fields[%1$s][%2$s_default]" value="%3$s" data-scheme="%4$s">',
					wpforms_validate_field_id( $field['id'] ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					esc_attr( $subfield_slug ),
					esc_attr( $default_value ),
					esc_attr( $scheme_slug )
				);

				continue;
			}

			printf(
				'<input type="text" class="default wpforms-hidden-strict" id="" name="" value="" data-scheme="%s">',
				esc_attr( $scheme_slug )
			);
		}
	}

	/**
	 * Get a select dropdown "placeholder" option which is displayed if nothing is selected.
	 *
	 * @since 1.9.4
	 *
	 * @param string $name Select field name, can be lowercase or uppercase.
	 *
	 * @return string
	 */
	protected function dropdown_empty_value( string $name ): string {

		return sprintf( /* translators: %s - subfield name, e.g., state, country. */
			__( '--- Select %s ---', 'wpforms-lite' ),
			$name
		);
	}
}
