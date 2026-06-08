<?php

namespace WPForms\Forms\Fields\Password;

use WPForms\Forms\Fields\Traits\ProField as ProFieldTrait;
use WPForms_Field;

/**
 * Password field.
 *
 * @since 1.9.4
 */
class Field extends WPForms_Field {

	use ProFieldTrait;

	/**
	 * Primary class constructor.
	 *
	 * @since 1.9.4
	 */
	public function init() {

		// Define field type information.
		$this->name     = esc_html__( 'Password', 'wpforms-lite' );
		$this->keywords = esc_html__( 'user', 'wpforms-lite' );
		$this->type     = 'password';
		$this->icon     = 'fa-lock';
		$this->order    = 95;
		$this->group    = 'fancy';

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
	 *
	 * @noinspection PackedHashtableOptimizationInspection
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

		// Confirmation toggle.
		$fld  = $this->field_element(
			'toggle',
			$field,
			[
				'slug'    => 'confirmation',
				'value'   => isset( $field['confirmation'] ) ? '1' : '0',
				'desc'    => esc_html__( 'Enable Password Confirmation', 'wpforms-lite' ),
				'tooltip' => esc_html__( 'Check this option to ask users to provide their password twice.', 'wpforms-lite' ),
			],
			false
		);
		$args = [
			'slug'    => 'confirmation',
			'content' => $fld,
		];

		$this->field_element( 'row', $field, $args );

		// Password strength.
		$meter = $this->field_element(
			'toggle',
			$field,
			[
				'slug'    => 'password-strength',
				'value'   => isset( $field['password-strength'] ) ? '1' : '0',
				'desc'    => esc_html__( 'Enable Password Strength', 'wpforms-lite' ),
				'tooltip' => esc_html__( 'Check this option to set minimum password strength.', 'wpforms-lite' ),
			],
			false
		);
		$args  = [
			'slug'    => 'password-strength',
			'content' => $meter,
		];

		$this->field_element( 'row', $field, $args );

		$strength_label = $this->field_element(
			'label',
			$field,
			[
				'value'   => esc_html__( 'Minimum Strength', 'wpforms-lite' ),
				'tooltip' => esc_html__( 'Select minimum password strength level.', 'wpforms-lite' ),
			],
			false
		);

		$strength = $this->field_element(
			'select',
			$field,
			[
				'slug'    => 'password-strength-level',
				'options' => [
					'2' => esc_html__( 'Weak', 'wpforms-lite' ),
					'3' => esc_html__( 'Medium', 'wpforms-lite' ),
					'4' => esc_html__( 'Strong', 'wpforms-lite' ),
				],
				'value'   => $field['password-strength-level'] ?? '3',

			],
			false
		);
		$args = [
			'slug'    => 'password-strength-level',
			'class'   => ! isset( $field['password-strength'] ) ? 'wpforms-hidden' : '',
			'content' => $strength_label . $strength,
		];

		$this->field_element( 'row', $field, $args );

		$visibility = $this->field_element(
			'toggle',
			$field,
			[
				'slug'    => 'password-visibility',
				'value'   => isset( $field['password-visibility'] ) ? '1' : '0',
				'desc'    => esc_html__( 'Enable Password Visibility', 'wpforms-lite' ),
				'tooltip' => esc_html__( 'Check this option to add a toggle for showing and hiding the password.', 'wpforms-lite' ),
			],
			false
		);
		$args       = [
			'slug'    => 'password-visibility',
			'content' => $visibility,
		];

		$this->field_element( 'row', $field, $args );

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

		// Placeholder.
		$this->field_option( 'placeholder', $field );

		// Confirmation Placeholder.
		$lbl  = $this->field_element(
			'label',
			$field,
			[
				'slug'    => 'confirmation_placeholder',
				'value'   => esc_html__( 'Confirmation Placeholder Text', 'wpforms-lite' ),
				'tooltip' => esc_html__( 'Enter text for the confirmation field placeholder.', 'wpforms-lite' ),
			],
			false
		);
		$fld  = $this->field_element(
			'text',
			$field,
			[
				'slug'  => 'confirmation_placeholder',
				'value' => ! empty( $field['confirmation_placeholder'] ) ? esc_attr( $field['confirmation_placeholder'] ) : '',
			],
			false
		);
		$args = [
			'slug'    => 'confirmation_placeholder',
			'content' => $lbl . $fld,
		];

		$this->field_element( 'row', $field, $args );

		// Default value.
		$this->field_option( 'default_value', $field );

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
	 * @since 1.9.4
	 *
	 * @param array $field Current field specific data.
	 *
	 * @noinspection HtmlUnknownAttribute
	 */
	public function field_preview( $field ) {

		$placeholder         = ! empty( $field['placeholder'] ) ? $field['placeholder'] : '';
		$confirm_placeholder = ! empty( $field['confirmation_placeholder'] ) ? $field['confirmation_placeholder'] : '';
		$default_value       = ! empty( $field['default_value'] ) ? $field['default_value'] : '';
		$confirm             = ! empty( $field['confirmation'] ) ? 'enabled' : 'disabled';
		$field_classes       = [
			'wpforms-confirm',
			'wpforms-confirm-' . $confirm,
		];

		if ( ! empty( $field['password-visibility'] ) ) {
			$field_classes[] = 'wpforms-field-password-visibility-enabled';
		}
		// Label.
		$this->field_preview_option(
			'label',
			$field,
			[
				'label_badge' => $this->get_field_preview_badge(),
			]
		);

		$icons = wpforms()->is_pro()
				? '
				<div class="wpforms-field-password-input-icon">
					<svg class="wpforms-field-password-input-icon-invisible" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M288 32c-80.8 0-145.5 36.8-192.6 80.6C48.6 156 17.3 208 2.5 243.7c-3.3 7.9-3.3 16.7 0 24.6C17.3 304 48.6 356 95.4 399.4C142.5 443.2 207.2 480 288 480s145.5-36.8 192.6-80.6c46.8-43.5 78.1-95.4 93-131.1c3.3-7.9 3.3-16.7 0-24.6c-14.9-35.7-46.2-87.7-93-131.1C433.5 68.8 368.8 32 288 32zM144 256a144 144 0 1 1 288 0 144 144 0 1 1 -288 0zm144-64c0 35.3-28.7 64-64 64c-7.1 0-13.9-1.2-20.3-3.3c-5.5-1.8-11.9 1.6-11.7 7.4c.3 6.9 1.3 13.8 3.2 20.7c13.7 51.2 66.4 81.6 117.6 67.9s81.6-66.4 67.9-117.6c-11.1-41.5-47.8-69.4-88.6-71.1c-5.8-.2-9.2 6.1-7.4 11.7c2.1 6.4 3.3 13.2 3.3 20.3z"/></svg>
					<svg class="wpforms-field-password-input-icon-visible" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M38.8 5.1C28.4-3.1 13.3-1.2 5.1 9.2S-1.2 34.7 9.2 42.9l592 464c10.4 8.2 25.5 6.3 33.7-4.1s6.3-25.5-4.1-33.7L525.6 386.7c39.6-40.6 66.4-86.1 79.9-118.4c3.3-7.9 3.3-16.7 0-24.6c-14.9-35.7-46.2-87.7-93-131.1C465.5 68.8 400.8 32 320 32c-68.2 0-125 26.3-169.3 60.8L38.8 5.1zM223.1 149.5C248.6 126.2 282.7 112 320 112c79.5 0 144 64.5 144 144c0 24.9-6.3 48.3-17.4 68.7L408 294.5c8.4-19.3 10.6-41.4 4.8-63.3c-11.1-41.5-47.8-69.4-88.6-71.1c-5.8-.2-9.2 6.1-7.4 11.7c2.1 6.4 3.3 13.2 3.3 20.3c0 10.2-2.4 19.8-6.6 28.3l-90.3-70.8zM373 389.9c-16.4 6.5-34.3 10.1-53 10.1c-79.5 0-144-64.5-144-144c0-6.9 .5-13.6 1.4-20.2L83.1 161.5C60.3 191.2 44 220.8 34.5 243.7c-3.3 7.9-3.3 16.7 0 24.6c14.9 35.7 46.2 87.7 93 131.1C174.5 443.2 239.2 480 320 480c47.8 0 89.9-12.9 126.2-32.5L373 389.9z"/></svg>
				</div>'
				: '';

		$field_markup = '
		<div class="wpforms-field-password-input">
			<input type="password" %1$s>
			%2$s
		</div>';

		?>
		<div class="<?php echo wpforms_sanitize_classes( $field_classes, true ); ?>">
			<div class="wpforms-confirm-primary">
				<?php

				printf( // The `$field_markup` variable is escaped above, we should escape only passed variables to placeholders.
					$field_markup, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					wpforms_html_attributes(
						'',
						[ 'primary-input' ],
						[],
						[
							'readonly'    => 'readonly',
							'placeholder' => $placeholder,
							'value'       => $default_value,
						]
					),
					$icons // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				);

				?>
				<label class="wpforms-sub-label"><?php esc_html_e( 'Password', 'wpforms-lite' ); ?></label>
			</div>

			<div class="wpforms-confirm-confirmation">
				<?php

				printf( // The `$field_markup` variable is escaped above, we should escape only passed variables to placeholders.
					$field_markup, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					wpforms_html_attributes(
						'',
						[ 'secondary-input' ],
						[],
						[
							'readonly'    => 'readonly',
							'placeholder' => $confirm_placeholder,
						]
					),
					$icons // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				);

				?>
				<label class="wpforms-sub-label"><?php esc_html_e( 'Confirm Password', 'wpforms-lite' ); ?></label>
			</div>
		</div>
		<?php

		// Description.
		$this->field_preview_option( 'description', $field );
	}

	/**
	 * Field display on the form front-end.
	 *
	 * @since 1.9.4
	 *
	 * @param array $field      Field data and settings.
	 * @param array $deprecated Deprecated field attributes. Use field properties.
	 * @param array $form_data  Form data and settings.
	 */
	public function field_display( $field, $deprecated, $form_data ) {
	}
}
