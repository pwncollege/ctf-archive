<?php

namespace WPForms\Forms\Fields\Addons\Coupon;

use WPForms\Forms\Fields\Traits\ProField as ProFieldTrait;
use WPForms_Field;

/**
 * Coupon Field class.
 *
 * @since 1.0.0
 */
class Field extends WPForms_Field {

	use ProFieldTrait;

	/**
	 * Whether the addon is active.
	 *
	 * @since 1.9.4
	 *
	 * @var bool
	 */
	private $is_addon_active = false;

	/**
	 * Define field type information.
	 *
	 * @since 1.9.4
	 */
	public function init() {

		// Define field type information.
		$this->name       = esc_html__( 'Coupon', 'wpforms-lite' );
		$this->keywords   = esc_html__( 'discount, sale', 'wpforms-lite' );
		$this->type       = 'payment-coupon';
		$this->icon       = 'fa-ticket';
		$this->order      = 100;
		$this->group      = 'payment';
		$this->addon_slug = 'coupons';

		$this->is_addon_active = function_exists( 'wpforms_' . $this->addon_slug );

		$this->init_pro_field();
		$this->hooks();
	}

	/**
	 * Define field hooks.
	 *
	 * @since 1.9.4
	 */
	protected function hooks() {

		add_filter( 'wpforms_field_new_display_duplicate_button', [ $this, 'field_display_duplicate_button' ], 20, 2 );
		add_filter( 'wpforms_field_preview_display_duplicate_button', [ $this, 'field_display_duplicate_button' ], 20, 2 );
	}

	/**
	 * Disallow field preview "Duplicate" button.
	 *
	 * @since 1.9.4
	 *
	 * @param bool|mixed $display Display switch.
	 * @param array      $field   Field settings.
	 *
	 * @return bool
	 */
	public function field_display_duplicate_button( $display, array $field ) {

		return $field['type'] === $this->type ? false : $display;
	}

	/**
	 * Define additional field options.
	 *
	 * @since 1.9.4
	 *
	 * @param array $field Field data and settings.
	 */
	public function field_options( $field ) {

		// Options open markup.
		$this->field_option(
			'basic-options',
			$field,
			[
				'markup'      => 'open',
				'after_title' => $this->get_field_options_notice(),
			]
		);

		$this->field_option( 'label', $field );

		$this->field_option( 'description', $field );

		$coupons      = [];
		$form_coupons = [];

		if ( $this->is_addon_active ) {
			$coupons      = wpforms_coupons()->get( 'repository' )->get_coupons(
				[
					'limit'  => -1,
					'fields' => 'id=>name',
				]
			);
			$form_coupons = wpforms_coupons()->get( 'repository' )->get_form_coupons( $this->get_form_id() );
		}

		$warning = sprintf(
			'<p class="wpforms-alert wpforms-alert-warning%1$s">%2$s</p>',
			empty( $form_coupons ) && empty( $this->is_disabled_field ) ? '' : ' wpforms-hidden',
			esc_html__( 'You haven\'t selected any coupons that can be used with this form. Please choose at least one coupon.', 'wpforms-lite' )
		);

		$coupons_field_label = $this->field_element(
			'label',
			$field,
			[
				'slug'    => 'allowed_coupons',
				'value'   => esc_html__( 'Allowed Coupons', 'wpforms-lite' ),
				'tooltip' => esc_html__( 'Choose coupons that can be used in the field.', 'wpforms-lite' ),
			],
			false
		);
		$coupons_field       = $this->get_allowed_coupons_field( $coupons, $form_coupons, $field );
		$allowed_forms_json  = sprintf(
			'<input type="hidden" name="fields[%1$s][allowed_coupons_json]" class="wpforms-coupons-allowed_coupons_json" value="%2$s">',
			$field['id'],
			wp_json_encode( $form_coupons )
		);

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'allowed_coupons',
				'content' => $coupons_field_label . $coupons_field . $allowed_forms_json . $warning,
			]
		);
		$this->field_option( 'required', $field );

		$this->field_option( 'basic-options', $field, [ 'markup' => 'close' ] );

		$this->field_option( 'advanced-options', $field, [ 'markup' => 'open' ] );

		$this->field_option( 'button_text', $field );

		$button_text_label = $this->field_element(
			'label',
			$field,
			[
				'slug'    => 'button_text',
				'value'   => esc_html__( 'Button Text', 'wpforms-lite' ),
				'tooltip' => esc_html__( 'Change button text.', 'wpforms-lite' ),
			],
			false
		);
		$button_text_field = $this->field_element(
			'text',
			$field,
			[
				'slug'  => 'button_text',
				'value' => isset( $field['button_text'] ) && ! wpforms_is_empty_string( $field['button_text'] )
					? $field['button_text']
					: esc_html__( 'Apply', 'wpforms-lite' ),
			],
			false
		);

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'button_text',
				'content' => $button_text_label . $button_text_field,
			]
		);
		$this->field_option( 'css', $field );
		$this->field_option( 'label_hide', $field );
		$this->field_option( 'advanced-options', $field, [ 'markup' => 'close' ] );
	}

	/**
	 * Get allowed coupons' field.
	 *
	 * @since 1.9.4
	 *
	 * @param array $coupons      Coupons.
	 * @param array $form_coupons Form coupons.
	 * @param array $field        Field data.
	 *
	 * @return string
	 * @noinspection HtmlUnknownAttribute
	 */
	private function get_allowed_coupons_field( array $coupons, array $form_coupons, array $field ): string {

		$output = sprintf( '<select id="wpforms-field-option-%1$d-%2$s" name="fields[%1$d][%2$s]" multiple>', $field['id'], 'allowed_coupons' );

		foreach ( $coupons as $arg_key => $arg_option ) {
			$selected = selected( true, in_array( $arg_key, $form_coupons, true ), false );

			$output .= sprintf( '<option value="%s" %s>%s</option>', esc_attr( $arg_key ), $selected, $arg_option );
		}

		$output .= '</select>';

		return $output;
	}

	/**
	 * Field preview inside the builder.
	 *
	 * @since 1.9.4
	 *
	 * @param array $field Field data.
	 */
	public function field_preview( $field ) {

		// Label.
		$this->field_preview_option(
			'label',
			$field,
			[
				'label_badge' => $this->get_field_preview_badge(),
			]
		);

		$allowed_coupons = [];

		if ( $this->is_addon_active ) {
			$allowed_coupons = wpforms_coupons()->get( 'repository' )->get_form_coupons( $this->get_form_id() );
		}

		printf(
			'<div class="wpforms-field-payment-coupon-wrapper">
				<input type="text" class="wpforms-field-payment-coupon-input">
				<button type="button" aria-live="assertive" class="wpforms-field-payment-coupon-button">%1$s</button>
				<i class="fa fa-exclamation-triangle%2$s"></i>
			</div>',
			esc_html( $this->get_button_text( $field ) ),
			empty( $allowed_coupons ) && empty( $this->is_disabled_field ) ? '' : ' wpforms-hidden'
		);

		// Description.
		$this->field_preview_option( 'description', $field );

		// Hide remaining elements.
		$this->field_preview_option( 'hide-remaining', $field );
	}

	/**
	 * Get form ID. In AJAX requests the $form_id property doesn't exist.
	 *
	 * @since 1.9.4
	 *
	 * @return bool|int
	 */
	protected function get_form_id() {

		if ( $this->form_id ) {
			return $this->form_id;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$this->form_id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : false;

		return $this->form_id;
	}

	/**
	 * Get the apply button text.
	 *
	 * @since 1.9.4
	 *
	 * @param array $field Field data.
	 *
	 * @return string
	 */
	protected function get_button_text( array $field ): string {

		return isset( $field['button_text'] ) && ! wpforms_is_empty_string( $field['button_text'] )
			? $field['button_text']
			: __( 'Apply', 'wpforms-lite' );
	}

	/**
	 * Field display on the frontend.
	 *
	 * @since 1.9.4
	 *
	 * @param array $field      Field data.
	 * @param array $deprecated Field attributes.
	 * @param array $form_data  Form data.
	 */
	public function field_display( $field, $deprecated, $form_data ) {
	}
}
