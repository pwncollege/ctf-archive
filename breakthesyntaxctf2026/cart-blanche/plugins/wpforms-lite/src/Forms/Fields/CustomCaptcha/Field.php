<?php

namespace WPForms\Forms\Fields\CustomCaptcha;

use WPForms\Forms\Fields\Traits\ProField as ProFieldTrait;
use WPForms_Field;

/**
 * Custom Captcha field.
 *
 * @since 1.9.4
 */
class Field extends WPForms_Field {

	use ProFieldTrait;

	/**
	 * The field type.
	 *
	 * @since 1.9.4
	 */
	public const TYPE = 'captcha';

	/**
	 * Min & max values to participate in equation and operators.
	 *
	 * @since 1.9.4
	 *
	 * @var array
	 */
	public $math;

	/**
	 * Questions to ask.
	 *
	 * @since 1.9.4
	 *
	 * @var array
	 */
	protected $qs;

	/**
	 *
	 * Init class.
	 *
	 * @since 1.9.4
	 */
	public function init() {

		// Define field type information.
		$this->name            = esc_html__( 'Custom Captcha', 'wpforms-lite' );
		$this->keywords        = esc_html__( 'spam, math, maths, question', 'wpforms-lite' );
		$this->type            = self::TYPE;
		$this->icon            = 'fa-question-circle';
		$this->order           = 300;
		$this->group           = 'fancy';
		$this->allow_read_only = false;
		$this->qs              = [
			1 => [
				'question' => esc_html__( 'What is 7+4?', 'wpforms-lite' ),
				'answer'   => esc_html__( '11', 'wpforms-lite' ),
			],
		];
		$this->math            = [
			'min' => 1,
			'max' => 15,
			'cal' => [ '+', '*' ],
		];

		$this->init_pro_field();
		$this->hooks();
	}

	/**
	 * Register hooks.
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

		// Defaults.
		$format = ! empty( $field['format'] ) ? esc_attr( $field['format'] ) : 'math';
		$qs     = ! empty( $field['questions'] ) ? $field['questions'] : $this->qs;
		$qs     = array_filter( $qs );

		// Field is always required.
		$this->field_element(
			'text',
			$field,
			[
				'type'  => 'hidden',
				'slug'  => 'required',
				'value' => '1',
			]
		);

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

		// Format.
		$lbl = $this->field_element(
			'label',
			$field,
			[
				'slug'    => 'format',
				'value'   => esc_html__( 'Type', 'wpforms-lite' ),
				'tooltip' => esc_html__( 'Select type of captcha to use.', 'wpforms-lite' ),
			],
			false
		);
		$fld = $this->field_element(
			'select',
			$field,
			[
				'slug'    => 'format',
				'value'   => $format,
				'options' => [
					'math' => esc_html__( 'Math', 'wpforms-lite' ),
					'qa'   => esc_html__( 'Question and Answer', 'wpforms-lite' ),
				],
			],
			false
		);

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'format',
				'content' => $lbl . $fld,
			]
		);

		// Questions.
		$lbl = $this->field_element(
			'label',
			$field,
			[
				'slug'    => 'questions',
				'value'   => esc_html__( 'Questions and Answers', 'wpforms-lite' ),
				'tooltip' => esc_html__( 'Add questions to ask the user. Questions are randomly selected.', 'wpforms-lite' ),
			],
			false
		);
		$fld = sprintf(
			'<ul id="wpforms-field-option-%1$d-questions-list" data-next-id="%2$s" data-field-id="%1$d" data-field-type="%3$s" class="choices-list wpforms-undo-redo-container">',
			esc_attr( $field['id'] ),
			max( array_keys( $qs ) ) + 1,
			esc_attr( $this->type )
		);

		foreach ( $qs as $key => $value ) {
			$fld .= '<li data-key="' . absint( $key ) . '">';
			$fld .= sprintf(
				'<input type="text" name="fields[%1$d][questions][%2$s][question]" value="%3$s" data-prev-value="%3$s" class="question" placeholder="%4$s">',
				(int) $field['id'],
				esc_attr( $key ),
				esc_attr( $value['question'] ),
				esc_html__( 'Question', 'wpforms-lite' )
			);
			$fld .= '<a class="add" href="#"><i class="fa fa-plus-circle"></i></a><a class="remove" href="#"><i class="fa fa-minus-circle"></i></a>';
			$fld .= sprintf(
				'<input type="text" name="fields[%d][questions][%s][answer]" value="%s" class="answer" placeholder="%s">',
				(int) $field['id'],
				esc_attr( $key ),
				esc_attr( $value['answer'] ),
				esc_html__( 'Answer', 'wpforms-lite' )
			);
			$fld .= '</li>';
		}
		$fld .= '</ul>';

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'questions',
				'content' => $lbl . $fld,
				'class'   => $format === 'math' ? 'wpforms-hidden' : '',
			]
		);

		// Description.
		$this->field_option( 'description', $field );

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
		$this->field_option(
			'size',
			$field,
			[
				'class' => $format === 'math' ? 'wpforms-hidden' : '',
			]
		);

		// Custom CSS classes.
		$this->field_option( 'css', $field );

		// Placeholder.
		$this->field_option( 'placeholder', $field );

		// Hide Label.
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
		$placeholder = ! empty( $field['placeholder'] ) ? $field['placeholder'] : '';
		$format      = ! empty( $field['format'] ) ? $field['format'] : 'math';
		$num1        = wp_rand( $this->math['min'], $this->math['max'] );
		$num2        = wp_rand( $this->math['min'], $this->math['max'] );
		$cal         = $this->math['cal'][ wp_rand( 0, count( $this->math['cal'] ) - 1 ) ];
		$questions   = ! empty( $field['questions'] ) ? $field['questions'] : $this->qs;

		// Label.
		$this->field_preview_option(
			'label',
			$field,
			[
				'label_badge' => $this->get_field_preview_badge(),
			]
		);

		$first_question = array_shift( $questions );
		?>

		<div class="format-selected-<?php echo esc_attr( $format ); ?> format-selected">

			<span class="wpforms-equation"><?php echo esc_html( "$num1 $cal $num2 = " ); ?></span>

			<p class="wpforms-question"><?php echo wp_kses( $first_question['question'], wpforms_builder_preview_get_allowed_tags() ); ?></p>

			<input type="text" placeholder="<?php echo esc_attr( $placeholder ); ?>" class="primary-input" readonly>

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
	 * @param array $field      Field settings.
	 * @param array $deprecated Deprecated array.
	 * @param array $form_data  Form data and settings.
	 */
	public function field_display( $field, $deprecated, $form_data ) {
	}
}
