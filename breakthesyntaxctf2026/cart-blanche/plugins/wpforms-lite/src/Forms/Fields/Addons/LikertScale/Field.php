<?php

namespace WPForms\Forms\Fields\Addons\LikertScale;

use WPForms\Forms\Fields\Traits\ProField as ProFieldTrait;
use WPForms_Field;

/**
 * Likert Scale field.
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
		$this->name       = esc_html__( 'Likert Scale', 'wpforms-lite' );
		$this->keywords   = esc_html__( 'survey, rating scale', 'wpforms-lite' );
		$this->type       = 'likert_scale';
		$this->icon       = 'fa-ellipsis-h';
		$this->order      = 400;
		$this->group      = 'fancy';
		$this->addon_slug = 'surveys-polls';

		$this->default_settings = [
			'size'    => 'large',
			'style'   => 'modern',
			'survey'  => '1',
			'rows'    => [
				1 => esc_html__( 'Item #1', 'wpforms-lite' ),
				2 => esc_html__( 'Item #2', 'wpforms-lite' ),
				3 => esc_html__( 'Item #3', 'wpforms-lite' ),
			],
			'columns' => [
				1 => esc_html__( 'Strongly Disagree', 'wpforms-lite' ),
				2 => esc_html__( 'Disagree', 'wpforms-lite' ),
				3 => esc_html__( 'Neutral', 'wpforms-lite' ),
				4 => esc_html__( 'Agree', 'wpforms-lite' ),
				5 => esc_html__( 'Strongly Agree', 'wpforms-lite' ),
			],
		];

		$this->init_pro_field();
		$this->hooks();
	}

	/**
	 * Add hooks.
	 *
	 * @since 1.9.4
	 */
	protected function hooks() {}

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

		// Rows.
		$values = ! empty( $field['rows'] ) ? $field['rows'] : $this->default_settings['rows'];
		$lbl    = $this->field_element(
			'label',
			$field,
			[
				'slug'    => 'rows',
				'value'   => esc_html__( 'Rows', 'wpforms-lite' ),
				'tooltip' => esc_html__( 'Add rows to the likert scale.', 'wpforms-lite' ),
			],
			false
		);
		$fld    = sprintf(
			'<ul id="wpforms-field-option-%1$d-rows-list" data-next-id="%2$s" class="choices-list wpforms-undo-redo-container %3$s" data-field-id="%1$d" data-field-type="%4$s" data-choice-type="%5$s">',
			esc_attr( $field['id'] ),
			max( array_keys( $values ) ) + 1,
			! empty( $field['single_row'] ) ? 'wpforms-hidden' : '',
			$this->type,
			'rows'
		);

		foreach ( $values as $key => $value ) {
			$fld .= sprintf( '<li data-key="%d">', $key );
			$fld .= '<span class="move"><i class="fa fa-grip-lines" aria-hidden="true"></i></span>';
			$fld .= sprintf( '<input type="text" name="fields[%s][rows][%s]" value="%s" class="label">', esc_attr( $field['id'] ), $key, esc_attr( $value ) );
			$fld .= '<a class="add" href="#" title="' . esc_attr__( 'Add likert scale row', 'wpforms-lite' ) . '"><i class="fa fa-plus-circle"></i></a>';
			$fld .= '<a class="remove" href="# title="' . esc_attr__( 'Remove likert scale row', 'wpforms-lite' ) . '"><i class="fa fa-minus-circle"></i></a>';
			$fld .= '</li>';
		}
		$fld .= '</ul>';

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'rows',
				'content' => $lbl . $fld,
			]
		);

		// Single rows.
		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'single_row',
				'content' => $this->field_element(
					'toggle',
					$field,
					[
						'slug'    => 'single_row',
						'value'   => isset( $field['single_row'] ) ? '1' : '0',
						'desc'    => esc_html__( 'Make this a single-row rating scale', 'wpforms-lite' ),
						'tooltip' => esc_html__( 'Check this option to make this a single-row rating scale and remove the row choices.', 'wpforms-lite' ),
					],
					false
				),
			]
		);

		// Multiple row responses.
		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'multiple_responses',
				'content' => $this->field_element(
					'toggle',
					$field,
					[
						'slug'    => 'multiple_responses',
						'value'   => isset( $field['multiple_responses'] ) ? '1' : '0',
						'desc'    => esc_html__( 'Allow multiple responses per row', 'wpforms-lite' ),
						'tooltip' => esc_html__( 'Check this option to allow multiple responses per row (uses checkboxes).', 'wpforms-lite' ),
					],
					false
				),
			]
		);

		// Columns.
		$values = ! empty( $field['columns'] ) ? $field['columns'] : $this->default_settings['columns'];
		$lbl    = $this->field_element(
			'label',
			$field,
			[
				'slug'    => 'columns',
				'value'   => esc_html__( 'Columns', 'wpforms-lite' ),
				'tooltip' => esc_html__( 'Add columns to the likert scale.', 'wpforms-lite' ),
			],
			false
		);
		$fld    = sprintf(
			'<ul id="wpforms-field-option-%1$d-columns-list" data-next-id="%2$s" class="choices-list wpforms-undo-redo-container" data-field-id="%1$d" data-field-type="%3$s" data-choice-type="%4$s">',
			esc_attr( $field['id'] ),
			max( array_keys( $values ) ) + 1,
			$this->type,
			'columns'
		);

		foreach ( $values as $key => $value ) {
			$fld .= sprintf( '<li data-key="%d">', $key );
			$fld .= '<span class="move"><i class="fa fa-grip-lines" aria-hidden="true"></i></span>';
			$fld .= sprintf( '<input type="text" name="fields[%s][columns][%s]" value="%s">', $field['id'], $key, esc_attr( $value ) );
			$fld .= '<a class="add" href="#" title="' . esc_attr__( 'Add likert scale column', 'wpforms-lite' ) . '"><i class="fa fa-plus-circle"></i></a>';
			$fld .= '<a class="remove" href="# title="' . esc_attr__( 'Remove likert scale column', 'wpforms-lite' ) . '"><i class="fa fa-minus-circle"></i></a>';
			$fld .= '</li>';
		}
		$fld .= '</ul>';

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'columns',
				'content' => $lbl . $fld,
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

		// Style (theme).
		$lbl = $this->field_element(
			'label',
			$field,
			[
				'slug'    => 'style',
				'value'   => esc_html__( 'Style', 'wpforms-lite' ),
				'tooltip' => esc_html__( 'Select the style for the likert scale.', 'wpforms-lite' ),
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
	public function field_preview( $field ) { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		// Define data.
		$rows       = ! empty( $field['rows'] ) ? $field['rows'] : $this->default_settings['rows'];
		$columns    = ! empty( $field['columns'] ) ? $field['columns'] : $this->default_settings['columns'];
		$input_type = ! empty( $field['multiple_responses'] ) ? 'checkbox' : 'radio';
		$style      = ! empty( $field['style'] ) ? sanitize_html_class( $field['style'] ) : 'modern';
		$single     = ! empty( $field['single_row'] );
		$width      = $single ? round( 100 / count( $columns ), 4 ) : round( 80 / count( $columns ), 4 );

		// Label.
		$this->field_preview_option(
			'label',
			$field,
			[
				'label_badge' => $this->get_field_preview_badge(),
			]
		);
		?>

		<table class="<?php echo esc_attr( $style ); ?><?php echo $single ? ' single-row' : ''; ?>">
			<thead>
				<tr>
					<?php
					if ( ! $single ) {
						echo '<th style="width:20%;"></th>';
					}
					foreach ( $columns as $column ) {
						printf(
							'<th style="width:%d%%;">%s</th>',
							esc_attr( $width ),
							esc_html( sanitize_text_field( $column ) )
						);
					}
					?>
				</tr>
			</thead>
			<tbody>
				<?php
				foreach ( $rows as $row ) {
					echo '<tr>';
						if ( ! $single ) {
							echo '<th>' . esc_html( sanitize_text_field( $row ) ) . '</th>';
						}
					/**
					 * Column is needed for foreach syntax.
					 *
					 * @noinspection PhpUnusedLocalVariableInspection
					 */
					foreach ( $columns as $column ) {
							echo '<td>';
								echo '<input type="' . esc_attr( $input_type ) . '" readonly>';
								echo '<label></label>';
							echo '</td>';
						}
					echo '</tr>';
					if ( $single ) {
						break;
					}
				}
				?>
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
