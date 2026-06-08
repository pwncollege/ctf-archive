<?php

namespace WPForms\Forms\Fields\Pagebreak;

use WPForms\Forms\Fields\Traits\IndicatorRendererTrait;
use WPForms\Forms\Fields\Traits\ProField as ProFieldTrait;
use WPForms_Field;

/**
 * Pagebreak field.
 *
 * @since 1.9.4
 */
class Field extends WPForms_Field {

	use ProFieldTrait;
	use IndicatorRendererTrait;

	/**
	 * Default indicator color.
	 *
	 * @since 1.9.4
	 */
	private const DEFAULT_INDICATOR_COLOR = [
		'classic' => '#72b239',
		'modern'  => '#066aab',
	];

	/**
	 * Pages information.
	 *
	 * @since 1.9.4
	 *
	 * @var array|bool
	 */
	protected $pagebreak;

	/**
	 * Primary class constructor.
	 *
	 * @since 1.9.4
	 */
	public function init() {

		// Define field type information.
		$this->name            = esc_html__( 'Page Break', 'wpforms-lite' );
		$this->keywords        = esc_html__( 'progress bar, multi step, multi part', 'wpforms-lite' );
		$this->type            = 'pagebreak';
		$this->icon            = 'fa-files-o';
		$this->order           = 160;
		$this->group           = 'fancy';
		$this->allow_read_only = false;

		$this->init_pro_field();
		$this->hooks();
	}

	/**
	 * Hooks.
	 *
	 * @since 1.9.4
	 */
	protected function hooks() {

		add_filter( 'wpforms_field_preview_class', [ $this, 'preview_field_class' ], 10, 2 );
		add_filter( 'wpforms_field_preview_display_duplicate_button', [ $this, 'field_display_duplicate_button' ], 10, 2 );
		add_filter( 'wpforms_field_new_display_duplicate_button', [ $this, 'field_display_duplicate_button' ], 10, 2 );
	}

	/**
	 * Get allow page navigation tooltip strings.
	 *
	 * @since 1.10.0
	 *
	 * @return array Associative array with 'enabled' and 'disabled' keys.
	 */
	protected function get_allow_page_navigation_strings(): array {

		return [
			'enabled'  => esc_html__( 'Lets visitors move between pages even if required fields are empty. Required fields are checked when the form is submitted.', 'wpforms-lite' ),
			'disabled' => esc_html__( 'Only available when using the Circles or Connector Progress Indicator.', 'wpforms-lite' ),
		];
	}

	/**
	 * Field options panel inside the builder.
	 *
	 * @since 1.9.4
	 *
	 * @param array $field Field data.
	 */
	public function field_options( $field ) {

		$position       = ! empty( $field['position'] ) ? esc_attr( $field['position'] ) : '';
		$position_class = ! empty( $field['position'] ) ? 'wpforms-pagebreak-' . $position : '';

		$this->field_options_basic( $field, $position, $position_class );
		$this->field_options_advanced( $field, $position, $position_class );
	}

	/**
	 * Advanced field options panel inside the builder.
	 *
	 * @since 1.9.4
	 *
	 * @param array  $field          Field data.
	 * @param string $position       Position.
	 * @param string $position_class Position CSS class.
     */
	private function field_options_basic( array $field, string $position, string $position_class ): void {

		// Hidden field indicating the position.
		$this->field_element(
			'text',
			$field,
			[
				'type'  => 'hidden',
				'slug'  => 'position',
				'value' => $position,
				'class' => 'position',
			]
		);

		// Options open markup.
		$this->field_option(
			'basic-options',
			$field,
			[
				'markup'      => 'open',
				'class'       => $position_class,
				'after_title' => $this->get_field_options_notice(),
			]
		);

		$this->field_options_basic_top( $field, $position );
		$this->render_page_title_option( $field, $position );
		$this->render_next_button_option( $field, $position );
		$this->render_previous_button_options( $field, $position );

		// Options close markup.
		$this->field_option(
			'basic-options',
			$field,
			[
				'markup' => 'close',
			]
		);
	}

	/**
	 * Render page title option.
	 *
	 * @since 1.10.0
	 *
	 * @param array  $field    Field data.
	 * @param string $position Position.
	 */
	private function render_page_title_option( array $field, string $position ): void {

		// Don't display for bottom page breaks.
		if ( $position === 'bottom' ) {
			return;
		}

		$lbl = $this->field_element(
			'label',
			$field,
			[
				'slug'    => 'title',
				'value'   => esc_html__( 'Page Title', 'wpforms-lite' ),
				'tooltip' => esc_html__( 'Enter text for the page title.', 'wpforms-lite' ),
			],
			false
		);

		$fld = $this->field_element(
			'text',
			$field,
			[
				'slug'  => 'title',
				'value' => ! empty( $field['title'] ) ? esc_attr( $field['title'] ) : '',
			],
			false
		);

		$indicator = ! empty( $field['indicator'] ) ? esc_attr( $field['indicator'] ) : 'progress';

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'title',
				'content' => $lbl . $fld,
				'class'   => $indicator === 'none' ? 'wpforms-hidden' : '',
			]
		);

		// Allow Page Navigation toggle (only for top position).
		if ( $position === 'top' ) {
			$this->render_page_navigation_toggle( $field );
		}
	}

	/**
	 * Render page navigation toggle option.
	 *
	 * @since 1.10.0
	 *
	 * @param array $field Field data.
	 */
	private function render_page_navigation_toggle( array $field ): void {

		$indicator  = ! empty( $field['indicator'] ) ? esc_attr( $field['indicator'] ) : 'progress';
		$is_enabled = in_array( $indicator, [ 'circles', 'connector' ], true );

		// Set tooltip text based on the enabled state.
		$strings     = $this->get_allow_page_navigation_strings();
		$toggle_data = [
			'slug'    => 'allow_page_navigation',
			'value'   => ! empty( $field['allow_page_navigation'] ),
			'desc'    => esc_html__( 'Allow Page Navigation', 'wpforms-lite' ),
			'tooltip' => $is_enabled ? $strings['enabled'] : $strings['disabled'],
			'class'   => [ 'wpforms-pagebreak-allow-page-navigation' ],
		];

		if ( ! $is_enabled ) {
			$toggle_data['attrs']         = [ 'disabled' => 'disabled' ];
			$toggle_data['control-class'] = 'wpforms-toggle-control-disabled';
		}

		$fld = $this->field_element(
			'toggle',
			$field,
			$toggle_data,
			false
		);

		$classes = [];

		if ( $indicator === 'none' ) {
			$classes[] = 'wpforms-hidden';
		}

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'allow_page_navigation',
				'content' => $fld,
				'class'   => $classes,
				'data'    => [
					'indicator-dependent' => 'circles,connector',
				],
			]
		);
	}

	/**
	 * Render next button option.
	 *
	 * @since 1.10.0
	 *
	 * @param array  $field    Field data.
	 * @param string $position Position.
	 */
	private function render_next_button_option( array $field, string $position ): void {

		// The next label is only for normal (non-top, non-bottom) pagebreaks.
		if ( ! empty( $position ) ) {
			return;
		}

		$lbl = $this->field_element(
			'label',
			$field,
			[
				'slug'    => 'next',
				'value'   => esc_html__( 'Next Label', 'wpforms-lite' ),
				'tooltip' => esc_html__( 'Enter text for Next page navigation button.', 'wpforms-lite' ),
			],
			false
		);

		$fld = $this->field_element(
			'text',
			$field,
			[
				'slug'  => 'next',
				'value' => ! empty( $field['next'] ) ? esc_attr( $field['next'] ) : esc_html__( 'Next', 'wpforms-lite' ),
			],
			false
		);

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'next',
				'content' => $lbl . $fld,
			]
		);
	}

	/**
	 * Render previous button options (toggle and label).
	 *
	 * @since 1.10.0
	 *
	 * @param array  $field    Field data.
	 * @param string $position Position.
	 */
	private function render_previous_button_options( array $field, string $position ): void {

		// Previous options are not available to top page breaks.
		if ( $position === 'top' ) {
			return;
		}

		// Previous button toggle.
		$fld = $this->field_element(
			'toggle',
			$field,
			[
				'slug'    => 'prev_toggle',
				// Backward compatibility for forms that were created before the toggle was added.
				'value'   => ! empty( $field['prev_toggle'] ) || ! empty( $field['prev'] ),
				'desc'    => esc_html__( 'Display Previous', 'wpforms-lite' ),
				'tooltip' => esc_html__( 'Toggle displaying the Previous page navigation button.', 'wpforms-lite' ),
			],
			false
		);

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'prev_toggle',
				'content' => $fld,
			]
		);

		// Previous button label.
		$lbl = $this->field_element(
			'label',
			$field,
			[
				'slug'    => 'prev',
				'value'   => esc_html__( 'Previous Label', 'wpforms-lite' ),
				'tooltip' => esc_html__( 'Enter text for Previous page navigation button.', 'wpforms-lite' ),
			],
			false
		);

		$fld = $this->field_element(
			'text',
			$field,
			[
				'slug'  => 'prev',
				'value' => ! empty( $field['prev'] ) ? esc_attr( $field['prev'] ) : '',
			],
			false
		);

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'prev',
				'content' => $lbl . $fld,
				'class'   => empty( $field['prev_toggle'] ) ? 'wpforms-hidden' : '',
			]
		);
	}

	/**
	 * Generate the field UI for progress text configuration within a form.
	 *
	 * @since 1.9.7
	 *
	 * @param array $field The field data used to generate the progress text UI elements.
	 */
	private function field_progress_text( array $field ): void {

		$lbl = $this->field_element(
			'label',
			$field,
			[
				'slug'    => 'progress_text',
				'value'   => esc_html__( 'Progress Text', 'wpforms-lite' ),
				'tooltip' => esc_html__( 'Enter text for the progress indicator.', 'wpforms-lite' ),
			],
			false
		);

		$fld = $this->field_element(
			'text',
			$field,
			[
				'slug'  => 'progress_text',
				'value' => ! empty( $field['progress_text'] ) ? esc_html( $field['progress_text'] ) : 'Step {current_page} of {last_page}',
				'after' => esc_html__( 'Enter text to show the user\'s progress. You can use {current_page} and {last_page} to indicate the current and last steps.', 'wpforms-lite' ),
				'class' => [ 'wpforms-pagebreak-progress-text' ],
			],
			false
		);

		$indicator = ! empty( $field['indicator'] ) ? esc_attr( $field['indicator'] ) : 'progress';

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'progress_text',
				'content' => $lbl . $fld,
				'class'   => $indicator !== 'progress' ? 'wpforms-hidden' : '', // Hide if the indicator is not set to progress.
			]
		);
	}

	/**
	 * Field options panel inside the builder.
	 *
	 * @since 1.9.4
	 *
	 * @param array  $field    Field data.
	 * @param string $position Position.
	 */
	private function field_options_basic_top( array $field, string $position ): void {

		// Options specific to the top pagebreak.
		if ( $position !== 'top' ) {
			return;
		}

		// Indicator themes.
		$themes = [
			'progress'  => esc_html__( 'Progress Bar', 'wpforms-lite' ),
			'circles'   => esc_html__( 'Circles', 'wpforms-lite' ),
			'connector' => esc_html__( 'Connector', 'wpforms-lite' ),
			'none'      => esc_html__( 'None', 'wpforms-lite' ),
		];

		/**
		 * Filter the available Pagebreak Indicator themes.
		 *
		 * @since 1.6.6
		 *
		 * @param array $themes Available themes.
		 */
		$themes = apply_filters( 'wpforms_pagebreak_indicator_themes', $themes ); // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName
		$lbl    = $this->field_element(
			'label',
			$field,
			[
				'slug'    => 'indicator',
				'value'   => esc_html__( 'Progress Indicator', 'wpforms-lite' ),
				'tooltip' => esc_html__( 'Select theme for Page Indicator which is displayed at the top of the form.', 'wpforms-lite' ),
			],
			false
		);

		$indicator = ! empty( $field['indicator'] ) ? esc_attr( $field['indicator'] ) : 'progress';

		$fld = $this->field_element(
			'select',
			$field,
			[
				'slug'    => 'indicator',
				'value'   => $indicator,
				'options' => $themes,
				'class'   => [ 'wpforms-pagebreak-progress-indicator' ],
			],
			false
		);

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'indicator',
				'content' => $lbl . $fld,
			]
		);

		// Indicator color picker.
		$lbl = $this->field_element(
			'label',
			$field,
			[
				'slug'    => 'indicator_color',
				'value'   => esc_html__( 'Page Indicator Color', 'wpforms-lite' ),
				'tooltip' => esc_html__( 'Select the primary color for the Page Indicator theme.', 'wpforms-lite' ),
			],
			false
		);

		$indicator_color = isset( $field['indicator_color'] ) ? wpforms_sanitize_hex_color( $field['indicator_color'] ) : self::get_default_indicator_color();

		$fld = $this->field_element(
			'color',
			$field,
			[
				'slug'  => 'indicator_color',
				'value' => $indicator_color,
				'data'  => [
					'fallback-color' => $indicator_color,
				],
				'class' => [ 'wpforms-pagebreak-indicator-color' ],
			],
			false
		);

		$indicator_color_classes = [ 'color-picker-row' ];

		if ( $indicator === 'none' ) {
			$indicator_color_classes[] = 'wpforms-hidden';
		}

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'indicator_color',
				'content' => $lbl . $fld,
				'class'   => $indicator_color_classes,
			]
		);

		$this->field_progress_text( $field );
	}

	/**
	 * Advanced field options panel inside the builder.
	 *
	 * @since 1.9.4
	 *
	 * @param array  $field          Field data.
	 * @param string $position       Position.
	 * @param string $position_class Position CSS class.
     */
	private function field_options_advanced( array $field, string $position, string $position_class ): void {

		if ( $position === 'bottom' ) {
			return;
		}

		/**
		 * Advanced field options.
		 */

		// Options open markup.
		$this->field_option(
			'advanced-options',
			$field,
			[
				'markup' => 'open',
				'class'  => $position_class,
			]
		);

		// Navigation alignment, only available to the top.
		if ( $position === 'top' ) {
			$lbl = $this->field_element(
				'label',
				$field,
				[
					'slug'    => 'nav_align',
					'value'   => esc_html__( 'Page Navigation Alignment', 'wpforms-lite' ),
					'tooltip' => esc_html__( 'Select the alignment for the Next/Previous page navigation buttons', 'wpforms-lite' ),
				],
				false
			);
			$fld = $this->field_element(
				'select',
				$field,
				[
					'slug'    => 'nav_align',
					'value'   => ! empty( $field['nav_align'] ) ? esc_attr( $field['nav_align'] ) : '',
					'options' => [
						'left'  => esc_html__( 'Left', 'wpforms-lite' ),
						'right' => esc_html__( 'Right', 'wpforms-lite' ),
						''      => esc_html__( 'Center', 'wpforms-lite' ),
						'split' => esc_html__( 'Split', 'wpforms-lite' ),
					],
				],
				false
			);

			$this->field_element(
				'row',
				$field,
				[
					'slug'    => 'nav_align',
					'content' => $lbl . $fld,
				]
			);

			// Scroll animation toggle.
			$fld = $this->field_element(
				'toggle',
				$field,
				[
					'slug'    => 'scroll_disabled',
					'value'   => ! empty( $field['scroll_disabled'] ),
					'desc'    => esc_html__( 'Disable Scroll Animation', 'wpforms-lite' ),
					'tooltip' => esc_html__( 'By default, a user\'s view is pulled to the top of each form page. Set to ON to disable this animation.', 'wpforms-lite' ),
				],
				false
			);

			$this->field_element(
				'row',
				$field,
				[
					'slug'    => 'scroll_disabled',
					'content' => $fld,
				]
			);
		}

		// Custom CSS classes.
		$this->field_option( 'css', $field );

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
	public function field_preview( $field ) { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		$nav_align  = 'wpforms-pagebreak-buttons-left';
		$prev       = ! empty( $field['prev'] ) ? $field['prev'] : esc_html__( 'Previous', 'wpforms-lite' );
		$prev_class = empty( $field['prev'] ) && empty( $field['prev_toggle'] ) ? 'wpforms-hidden' : '';
		$next       = ! empty( $field['next'] ) ? $field['next'] : esc_html__( 'Next', 'wpforms-lite' );
		$next_class = empty( $next ) ? 'wpforms-hidden' : '';
		$position   = ! empty( $field['position'] ) ? $field['position'] : 'normal';
		$title      = ! empty( $field['title'] ) ? $field['title'] : '';
		$label      = $position === 'top' ? esc_html__( 'First Page / Progress Indicator', 'wpforms-lite' ) : '';
		$label      = $position === 'normal' && empty( $label ) ? esc_html__( 'Page Break', 'wpforms-lite' ) : $label;

		/**
		 * Fires before the page break is displayed on the preview.
		 *
		 * @since 1.7.9
		 *
		 * @param array $form_data Form data and settings.
		 * @param array $field     Field data.
		 */
		do_action( 'wpforms_field_page_break_field_preview_before', $this->form_data, $field ); // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName

		if ( $position !== 'top' ) {
			if ( empty( $this->form_data ) && ! empty( $this->form_id ) ) {
				$this->form_data = wpforms()->obj( 'form' )->get( $this->form_id, [ 'content_only' => true ] );
			}

			if ( empty( $this->pagebreak ) ) {
				$this->pagebreak = wpforms_get_pagebreak_details( $this->form_data );
			}

			if ( ! empty( $this->pagebreak['top']['nav_align'] ) ) {
				$nav_align = 'wpforms-pagebreak-buttons-' . $this->pagebreak['top']['nav_align'];
			}

			echo '<div class="wpforms-pagebreak-buttons ' . sanitize_html_class( $nav_align ) . '">';
			printf(
				'<button class="wpforms-pagebreak-button wpforms-pagebreak-prev %s">%s</button>',
				sanitize_html_class( $prev_class ),
				esc_html( $prev )
			);

			if ( $position !== 'bottom' ) {
				printf(
					'<button class="wpforms-pagebreak-button wpforms-pagebreak-next %s">%s</button>',
					sanitize_html_class( $next_class ),
					esc_html( $next )
				);

				if ( $next_class !== 'wpforms-hidden' ) {

					/** This action is documented in includes/class-frontend.php. */
					do_action( 'wpforms_display_submit_after', $this->form_data, 'next' ); // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName
				}
			}
			echo '</div>';
		}

		// Visual divider.
		echo '<div class="wpforms-pagebreak-divider">';
		if ( $position !== 'bottom' ) {
			printf(
				'<span class="pagebreak-label">%1$s <span class="wpforms-pagebreak-title">%2$s</span>%3$s</span>',
				esc_html( $label ),
				esc_html( $title ),
				$this->get_field_preview_badge() // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			);
		}
		echo '<span class="line"></span>';
		echo '</div>';

		// Display a page indicator for the top position.
		if ( $position === 'top' ) {
			$this->field_preview_page_indicator( $field );
		}

		/**
		 * Fires after a page break is displayed on the preview.
		 *
		 * @since 1.7.9
		 *
		 * @param array $form_data Form data and settings.
		 * @param array $field     Field data.
		 */
		do_action( 'wpforms_field_page_break_field_preview_after', $this->form_data, $field ); // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName
	}

	/**
	 * Display page indicator preview in the builder.
	 *
	 * @since 1.10.0
	 *
	 * @param array $field Field data.
	 */
	private function field_preview_page_indicator( array $field ): void {

		$indicator = ! empty( $field['indicator'] ) ? sanitize_html_class( $field['indicator'] ) : 'progress';
		$color     = ! empty( $field['indicator_color'] ) ? wpforms_sanitize_hex_color( $field['indicator_color'] ) : self::get_default_indicator_color();

		// Get all pagebreak fields to determine total pages.
		if ( empty( $this->form_data ) && ! empty( $this->form_id ) ) {
			$this->form_data = wpforms()->obj( 'form' )->get( $this->form_id, [ 'content_only' => true ] );
		}

		$pages         = $this->get_preview_pages();
		$wrapper_style = $indicator === 'none' ? ' style=display:none;' : '';

		echo '<div class="wpforms-page-indicator wpforms-page-indicator-' . esc_attr( $indicator ) . '" data-allow-page-navigation="' . esc_attr( $field['allow_page_navigation'] ?? false ) . '"' . esc_attr( $wrapper_style ) . '>';

		if ( $indicator === 'circles' ) {
			$this->field_preview_indicator_circles( $pages, $color );
		} elseif ( $indicator === 'connector' ) {
			$this->field_preview_indicator_connector( $pages, $color );
		} elseif ( $indicator === 'progress' ) {
			$this->field_preview_indicator_progress( $pages, $color, $field );
		}

		echo '</div>';
	}

	/**
	 * Get preview pages for indicator.
	 *
	 * @since 1.10.0
	 *
	 * @return array
	 */
	private function get_preview_pages(): array {

		if ( empty( $this->form_data['fields'] ) ) {
			return [];
		}

		$pages = [];

		foreach ( $this->form_data['fields'] as $form_field ) {
			if ( $form_field['type'] === 'pagebreak' && isset( $form_field['position'] ) && $form_field['position'] !== 'bottom' ) {
				$pages[] = [
					'title' => $form_field['title'] ?? '',
				];
			}
		}

		return $pages;
	}

	/**
	 * Display circles indicator preview.
	 *
	 * @since 1.10.0
	 *
	 * @param array  $pages Pages data.
	 * @param string $color Indicator color.
	 */
	private function field_preview_indicator_circles( array $pages, string $color ): void {

		$page_num = 1;

		foreach ( $pages as $page ) {
			$this->render_circles_indicator_item( $page, $page_num, $color );
			++$page_num;
		}
	}

	/**
	 * Display connector indicator preview.
	 *
	 * @since 1.10.0
	 *
	 * @param array  $pages Pages data.
	 * @param string $color Indicator color.
	 */
	private function field_preview_indicator_connector( array $pages, string $color ): void {

		$page_num    = 1;
		$total_pages = max( count( $pages ), 2 );
		$width       = 100 / $total_pages . '%';

		foreach ( $pages as $page ) {
			$this->render_connector_indicator_item( $page, $page_num, $color, $width );
			++$page_num;
		}
	}

	/**
	 * Display progress indicator preview.
	 *
	 * @since 1.10.0
	 *
	 * @param array  $pages Pages data.
	 * @param string $color Indicator color.
	 * @param array  $field Field data.
	 */
	private function field_preview_indicator_progress( array $pages, string $color, array $field ): void {

		$title            = ! empty( $pages[0]['title'] ) ? $pages[0]['title'] : '';
		$total_pages      = max( count( $pages ), 2 );
		$width            = 100 / $total_pages . '%';
		$background_color = ! empty( $color ) ? $color : '';

		printf(
			'<span class="wpforms-page-indicator-page-title">%s</span>',
			esc_html( $title )
		);
		printf(
			'<span class="wpforms-page-indicator-page-title-sep" %s> - </span>',
			empty( $title ) ? 'style="display:none;"' : ''
		);

		// Render progress text.
		$this->render_progress_text( $field, $total_pages );

		// Render progress bar.
		$this->render_progress_bar( $width, $background_color );
	}

	/**
	 * Render progress text for progress indicator.
	 *
	 * @since 1.10.0
	 *
	 * @param array $field       Field data containing progress_text.
	 * @param int   $total_pages Total number of pages.
	 */
	protected function render_progress_text( array $field, int $total_pages ): void {

		$progress_text = ! empty( $field['progress_text'] ) ?
			str_replace( [ '{current_page}', '{last_page}' ], [ '%1$s', '%2$s' ], str_replace( '%', '%%', $field['progress_text'] ) ) :
			/* translators: %1$s - current step in multipage form, %2$d - total number of pages. */
			esc_html__( 'Step %1$s of %2$d', 'wpforms-lite' );

		printf(
			'<span class="wpforms-page-indicator-steps">' . esc_html( $progress_text ) . '</span>',
			'<span class="wpforms-page-indicator-steps-current">1</span>',
			esc_attr( $total_pages )
		);
	}

	/**
	 * Render progress bar.
	 *
	 * @since 1.10.0
	 *
	 * @param string $width            Width percentage.
	 * @param string $background_color Background color.
	 */
	protected function render_progress_bar( string $width, string $background_color ): void {

		printf(
			'<div class="wpforms-page-indicator-page-progress-wrap"><div class="wpforms-page-indicator-page-progress" style="width:%s;%s"></div></div>',
			esc_attr( $width ),
			! empty( $background_color ) ? 'background-color:' . sanitize_hex_color( $background_color ) : ''
		);
	}

	/**
	 * Add a class to the builder field preview.
	 *
	 * @since 1.9.4
	 *
	 * @param string|mixed $css   CSS classes.
	 * @param array        $field Field data and settings.
	 *
	 * @return string
	 */
	public function preview_field_class( $css, $field ): string {

		$css = (string) $css;

		if ( $field['type'] !== 'pagebreak' ) {
			return $css;
		}

		if ( ! empty( $field['position'] ) && $field['position'] === 'top' ) {
			$css .= ' wpforms-field-stick wpforms-pagebreak-top';
		} elseif ( ! empty( $field['position'] ) && $field['position'] === 'bottom' ) {
			$css .= ' wpforms-field-stick wpforms-pagebreak-bottom';
		} else {
			$css .= ' wpforms-pagebreak-normal';
		}

		return $css;
	}

	/**
	 * Field display on the form front-end.
	 *
	 * @since 1.9.4
	 *
	 * @param array $field      Field data and settings.
	 * @param array $deprecated Field attributes.
	 * @param array $form_data  Form data and settings.
	 */
	public function field_display( $field, $deprecated, $form_data ) {
	}

	/**
	 * Get the default indicator color.
	 *
	 * @since 1.9.4
	 *
	 * @return string
	 */
	public static function get_default_indicator_color(): string {

		$render_engine = wpforms_get_render_engine();

		return array_key_exists( $render_engine, self::DEFAULT_INDICATOR_COLOR ) ? self::DEFAULT_INDICATOR_COLOR[ $render_engine ] : self::DEFAULT_INDICATOR_COLOR['modern'];
	}

	/**
	 * Disallow the field preview "Duplicate" button.
	 *
	 * @since 1.9.9
	 *
	 * @param bool|mixed $display Display switch.
	 * @param array      $field   Field settings.
	 *
	 * @return bool
	 */
	public function field_display_duplicate_button( $display, array $field ): bool {

		$type = $field['type'] ?? '';

		if ( $type === $this->type ) {
			// Pagebreak fields cannot be duplicated.
			return false;
		}

		return (bool) $display;
	}
}
