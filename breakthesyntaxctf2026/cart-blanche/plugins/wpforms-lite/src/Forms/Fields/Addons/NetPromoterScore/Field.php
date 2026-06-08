<?php

namespace WPForms\Forms\Fields\Addons\NetPromoterScore;

use WPForms\Forms\Fields\Traits\ProField as ProFieldTrait;
use WPForms_Field;

/**
 * Net Promoter Score field.
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
		$this->name       = esc_html__( 'Net Promoter Score', 'wpforms-lite' );
		$this->keywords   = esc_html__( 'survey, nps', 'wpforms-lite' );
		$this->type       = 'net_promoter_score';
		$this->icon       = 'fa-tachometer';
		$this->order      = 410;
		$this->group      = 'fancy';
		$this->addon_slug = 'surveys-polls';

		$this->default_settings = [
			'size'   => 'large',
			'survey' => '1',
			'style'  => 'modern',
		];

		$this->init_pro_field();
		$this->hooks();
	}

	/**
	 * Add hooks.
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
	 * @param array $field Field settings.
	 */
	public function field_options( $field ) {

		/**
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

		// Style (theme).
		$lbl = $this->field_element(
			'label',
			$field,
			[
				'slug'    => 'style',
				'value'   => esc_html__( 'Style', 'wpforms-lite' ),
				'tooltip' => esc_html__( 'Select the style for the net promoter score.', 'wpforms-lite' ),
			],
			false
		);
		$fld = $this->field_element(
			'select',
			$field,
			[
				'slug'    => 'style',
				'value'   => ! empty( $field['style'] ) ? esc_attr( $field['style'] ) : 'modern',
				'options' => [
					'modern'  => esc_html__( 'Modern', 'wpforms-lite' ),
					'classic' => esc_html__( 'Classic', 'wpforms-lite' ),
				],
			],
			false
		);

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'style',
				'content' => $lbl . $fld,
			]
		);

		// Size.
		$this->field_option( 'size', $field );

		// Start label.
		$lowest_lbl_label = $this->field_element(
			'label',
			$field,
			[
				'slug'  => 'lowest_label',
				'value' => esc_html__( 'Lowest Score Label', 'wpforms-lite' ),
			],
			false
		);

		$lowest_lbl_field = $this->field_element(
			'text',
			$field,
			[
				'slug'  => 'lowest_label',
				'value' => $field['lowest_label'] ?? esc_html__( 'Not at all Likely', 'wpforms-lite' ),
			],
			false
		);

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'lowest_label',
				'content' => $lowest_lbl_label . $lowest_lbl_field,
			]
		);

		// End label.
		$highest_lbl_label = $this->field_element(
			'label',
			$field,
			[
				'slug'  => 'highest_label',
				'value' => esc_html__( 'Highest Score Label', 'wpforms-lite' ),
			],
			false
		);

		$highest_lbl_field = $this->field_element(
			'text',
			$field,
			[
				'slug'  => 'highest_label',
				'value' => $field['highest_label'] ?? esc_html__( 'Extremely Likely', 'wpforms-lite' ),
			],
			false
		);

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'highest_label',
				'content' => $highest_lbl_label . $highest_lbl_field,
			]
		);

		// Custom CSS classes.
		$this->field_option( 'css', $field );

		// Hide label.
		$this->field_option( 'label_hide', $field );

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
	 * @param array $field Field settings.
	 */
	public function field_preview( $field ) {

		// Define data.
		$style = ! empty( $field['style'] ) ? sanitize_html_class( $field['style'] ) : 'modern';

		// Label.
		$this->field_preview_option(
			'label',
			$field,
			[
				'label_badge' => $this->get_field_preview_badge(),
			]
		);

		// Lowest/Highest labels.
		$lowest_label  = $field['lowest_label'] ?? esc_html__( 'Not at all Likely', 'wpforms-lite' );
		$highest_label = $field['highest_label'] ?? esc_html__( 'Extremely Likely', 'wpforms-lite' );
		?>

		<table class="<?php echo esc_attr( $style ); ?>">
			<thead>
				<tr>
					<th colspan="11">
						<span class="not-likely"><?php echo esc_html( $lowest_label ); ?></span>
						<span class="extremely-likely"><?php echo esc_html( $highest_label ); ?></span>
					</th>
				</tr>
			</thead>
			<tbody>
				<tr>
				<?php
				for ( $i = 0; $i < 11; $i++ ) {
					?>
					<td>
						<input type="radio" readonly>
						<label><?php echo absint( $i ); ?></label>
					</td>
					<?php
				}
				?>
				</tr>
			</tbody>
		</table>
		<?php

		// Description.
		$this->field_preview_option( 'description', $field );

		// Hide remaining elements.
		$this->field_preview_option( 'hide-remaining', $field );
	}

	/**
	 * Field display on the form front-end.
	 *
	 * @since 1.9.4
	 *
	 * @param array $field      Field settings.
	 * @param array $deprecated Deprecated array.
	 * @param array $form_data  Form data and settings.
	 */
	public function field_display( $field, $deprecated, $form_data ) {
	}
}
