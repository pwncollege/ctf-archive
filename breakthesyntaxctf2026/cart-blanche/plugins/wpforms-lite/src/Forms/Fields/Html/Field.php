<?php

namespace WPForms\Forms\Fields\Html;

use WPForms\Forms\Fields\Traits\ProField as ProFieldTrait;
use WPForms_Field;

/**
 * HTML block text field.
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
		$this->name            = esc_html__( 'HTML', 'wpforms-lite' );
		$this->keywords        = esc_html__( 'code', 'wpforms-lite' );
		$this->type            = 'html';
		$this->icon            = 'fa-code';
		$this->order           = 185;
		$this->group           = 'fancy';
		$this->allow_read_only = false;

		$this->default_settings = [
			'name' => '',
		];

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
	 * Extend from `parent::field_option()` to add `name` option.
	 *
	 * @since 1.9.4
	 *
	 * @param string $option  Field option to render.
	 * @param array  $field   Field data and settings.
	 * @param array  $args    Field preview arguments.
	 * @param bool   $do_echo Print or return the value. Print by default.
	 *
	 * @return string|null
	 * @noinspection PhpMissingReturnTypeInspection
	 * @noinspection ReturnTypeCanBeDeclaredInspection
	 */
	public function field_option( $option, $field, $args = [], $do_echo = true ) { // phpcs:ignore Universal.NamingConventions.NoReservedKeywordParameterNames.echoFound

		if ( $option !== 'name' ) {
			return parent::field_option( $option, $field, $args, $do_echo );
		}

		$output  = $this->field_element(
			'label',
			$field,
			[
				'slug'    => 'name',
				'value'   => esc_html__( 'Label', 'wpforms-lite' ),
				'tooltip' => esc_html__( 'Enter text for the form field label. It will help identify your HTML blocks inside the form builder, but will not be displayed in the form.', 'wpforms-lite' ),
			],
			false
		);
		$output .= $this->field_element(
			'text',
			$field,
			[
				'slug'  => 'name',
				'value' => ! empty( $field['name'] ) ? esc_attr( $field['name'] ) : '',
			],
			false
		);
		$output  = $this->field_element(
			'row',
			$field,
			[
				'slug'    => 'name',
				'content' => $output,
			],
			false
		);

		if ( $do_echo ) {
			echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

			return null;
		}

		return $output;
	}

	/**
	 * Field options panel inside the builder.
	 *
	 * @since 1.9.4
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

		// Name (Label).
		$this->field_option( 'name', $field );

		// Code.
		$this->field_option( 'code', $field );

		// Set the label to disable.
		$args = [
			'type'  => 'hidden',
			'slug'  => 'label_disable',
			'value' => '1',
		];

		$this->field_element( 'text', $field, $args );

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

		// Custom CSS classes.
		$this->field_option( 'css', $field );

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
	 * @param array $field Field settings.
	 */
	public function field_preview( $field ) {

		$label = ! empty( $field['name'] ) ? $field['name'] : '';

		$label_badge = empty( $label ) ? '' : $this->get_field_preview_badge();
		$code_badge  = empty( $label ) ? $this->get_field_preview_badge() : '';

		?>
		<label class="label-title">
			<div class="text">
				<?php echo esc_html( $label ) . $label_badge; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
			<div class="grey">
				<i class="fa fa-code"></i>
				<?php esc_html_e( 'HTML / Code Block', 'wpforms-lite' ); ?>
				<?php echo $code_badge; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
		</label>
		<div class="description"><?php esc_html_e( 'Contents of this field are not displayed in the form builder preview.', 'wpforms-lite' ); ?></div>
		<?php
	}

	/**
	 * Field display on the form front-end.
	 *
	 * @since 1.9.4
	 *
	 * @param array $field      Field data and settings.
	 * @param array $deprecated Deprecated field attributes. Use field properties.
	 * @param array $form_data  Form data and settings.
	 *
	 * @noinspection HtmlUnknownAttribute
	 */
	public function field_display( $field, $deprecated, $form_data ) {
	}
}
