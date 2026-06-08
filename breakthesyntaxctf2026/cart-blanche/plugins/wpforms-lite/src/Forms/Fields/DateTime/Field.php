<?php

namespace WPForms\Forms\Fields\DateTime;

use WPForms\Forms\Fields\Traits\ProField as ProFieldTrait;
use WPForms_Field;

/**
 * Date / Time field.
 *
 * @since 1.9.4
 */
class Field extends WPForms_Field {

	use ProFieldTrait;

	/**
	 * Field settings defaults.
	 *
	 * @since 1.9.4
	 */
	public const DEFAULTS = [
		'format'                      => 'date-time',
		'date_placeholder'            => '',
		'date_format'                 => 'm/d/Y',
		'date_type'                   => 'datepicker',
		'time_placeholder'            => '',
		'time_format'                 => 'g:i A',
		'time_interval'               => '30',
		'date_limit_days_sun'         => '0',
		'date_limit_days_mon'         => '1',
		'date_limit_days_tue'         => '1',
		'date_limit_days_wed'         => '1',
		'date_limit_days_thu'         => '1',
		'date_limit_days_fri'         => '1',
		'date_limit_days_sat'         => '0',
		'time_limit_hours_start_hour' => '09',
		'time_limit_hours_start_min'  => '00',
		'time_limit_hours_start_ampm' => 'am',
		'time_limit_hours_end_hour'   => '06',
		'time_limit_hours_end_min'    => '00',
		'time_limit_hours_end_ampm'   => 'pm',
	];

	/**
	 * Alternative Date Format.
	 *
	 * @since 1.9.4
	 */
	public const ALT_DATE_FORMAT = 'd/m/Y';

	/**
	 * Primary class constructor.
	 *
	 * @since 1.9.4
	 */
	public function init() {

		// Define field type information.
		$this->name  = esc_html__( 'Date / Time', 'wpforms-lite' );
		$this->type  = 'date-time';
		$this->icon  = 'fa-calendar-o';
		$this->order = 60;
		$this->group = 'fancy';

		$this->default_settings = self::DEFAULTS;

		$this->init_pro_field();
		$this->hooks();
	}

	/**
	 * Hooks.
	 *
	 * @since 1.9.4
	 */
	protected function hooks(): void {

		// Set custom option wrapper classes.
		add_filter( 'wpforms_builder_field_option_class', [ $this, 'field_option_class' ], 10, 2 );
	}

	/**
	 * Field options panel inside the builder.
	 *
	 * @since 1.9.4
	 *
	 * @param array $field Field data and settings.
	 *
	 * @noinspection PackedHashtableOptimizationInspection
	 * @noinspection HtmlUnknownAttribute
	 */
	public function field_options( $field ) { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		/**
		 * Basic field options
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

		// Format option.
		$format        = ! empty( $field['format'] ) ? esc_attr( $field['format'] ) : self::DEFAULTS['format'];
		$format_label  = $this->field_element(
			'label',
			$field,
			[
				'slug'    => 'format',
				'value'   => esc_html__( 'Format', 'wpforms-lite' ),
				'tooltip' => esc_html__( 'Select format for the date field.', 'wpforms-lite' ),
			],
			false
		);
		$format_select = $this->field_element(
			'select',
			$field,
			[
				'slug'    => 'format',
				'value'   => $format,
				'options' => [
					'date-time' => esc_html__( 'Date and Time', 'wpforms-lite' ),
					'date'      => esc_html__( 'Date', 'wpforms-lite' ),
					'time'      => esc_html__( 'Time', 'wpforms-lite' ),
				],
			],
			false
		);

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'format',
				'content' => $format_label . $format_select,
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
		 * Advanced field options
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

		// Custom options.
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<div class="format-selected-' . $format . ' format-selected">';

			// Date.
			$date_placeholder = ! empty( $field['date_placeholder'] ) ? $field['date_placeholder'] : '';
			$date_format      = ! empty( $field['date_format'] ) ? esc_attr( $field['date_format'] ) : self::DEFAULTS['date_format'];
			$date_type        = ! empty( $field['date_type'] ) ? esc_attr( $field['date_type'] ) : 'datepicker';
			// Backwards compatibility with old datepicker format.
			if ( $date_format === 'mm/dd/yyyy' ) {
				$date_format = self::DEFAULTS['date_format'];
			} elseif ( $date_format === 'dd/mm/yyyy' ) {
				$date_format = self::ALT_DATE_FORMAT;
			} elseif ( $date_format === 'mmmm d, yyyy' ) {
				$date_format = 'F j, Y';
			}

			$date_formats = wpforms_date_formats();

			printf(
				'<div class="wpforms-clear wpforms-field-option-row wpforms-field-option-row-date no-gap" id="wpforms-field-option-row-%d-date" data-subfield="date" data-field-id="%d">',
				esc_attr( $field['id'] ),
				esc_attr( $field['id'] )
			);
			$this->field_element(
				'label',
				$field,
				[
					'slug'    => 'date_placeholder',
					'value'   => esc_html__( 'Date', 'wpforms-lite' ),
					'tooltip' => esc_html__( 'Advanced date options.', 'wpforms-lite' ),
				]
			);

			echo '<div class="wpforms-field-options-columns-2 wpforms-field-options-columns">';
				echo '<div class="type wpforms-field-options-column">';
					printf(
						'<select id="wpforms-field-option-%d-date_type" name="fields[%d][date_type]">',
						esc_attr( $field['id'] ),
						esc_attr( $field['id'] )
					);
						printf(
							'<option value="datepicker" %s>%s</option>',
							selected( $date_type, 'datepicker', false ),
							esc_html__( 'Date Picker', 'wpforms-lite' )
						);
						printf(
							'<option value="dropdown" %s>%s</option>',
							selected( $date_type, 'dropdown', false ),
							esc_html__( 'Date Dropdown', 'wpforms-lite' )
						);
					echo '</select>';
					printf(
						'<label for="wpforms-field-option-%d-date_type" class="sub-label">%s</label>',
						esc_attr( $field['id'] ),
						esc_html__( 'Type', 'wpforms-lite' )
					);
				echo '</div>';
				echo '<div class="format wpforms-field-options-column">';
					printf(
						'<select id="wpforms-field-option-%d-date_format" name="fields[%d][date_format]">',
						esc_attr( $field['id'] ),
						esc_attr( $field['id'] )
					);
					foreach ( $date_formats as $key => $value ) {
						if ( in_array( $key, $this->get_regular_date_formats(), true ) ) {
							printf(
								'<option value="%s" %s>%s (%s)</option>',
								esc_attr( $key ),
								selected( $date_format, $key, false ),
								esc_html( date( $value ) ), // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
								esc_html( $key )
							);
						} else {
							printf(
								'<option value="%s" class="datepicker-only" %s>%s</option>',
								esc_attr( $key ),
								selected( $date_format, $key, false ),
								esc_html( date( $value ) ) // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
							);
						}
					}
					echo '</select>';
					printf(
						'<label for="wpforms-field-option-%d-date_format" class="sub-label">%s</label>',
						esc_attr( $field['id'] ),
						esc_html__( 'Format', 'wpforms-lite' )
					);
				echo '</div>';
			echo '</div>';
			echo '<div class="placeholder wpforms-field-option-row">';
				printf(
					'<input type="text" class="placeholder" id="wpforms-field-option-%d-date_placeholder" name="fields[%d][date_placeholder]" value="%s">',
					esc_attr( $field['id'] ),
					esc_attr( $field['id'] ),
					esc_attr( $date_placeholder )
				);
				printf(
					'<label for="wpforms-field-option-%d-date_placeholder" class="sub-label">%s</label>',
					esc_attr( $field['id'] ),
					esc_html__( 'Placeholder', 'wpforms-lite' )
				);
			echo '</div>';

			// Limit Days options.
			$this->field_options_limit_days( $field );

		echo '</div>';

		// Time.
		$time_placeholder = ! empty( $field['time_placeholder'] ) ? $field['time_placeholder'] : '';
		$time_format      = ! empty( $field['time_format'] ) ? esc_attr( $field['time_format'] ) : self::DEFAULTS['time_format'];
		$time_formats     = wpforms_time_formats();
		$time_interval    = ! empty( $field['time_interval'] ) ? esc_attr( $field['time_interval'] ) : '30';

		/**
		 * Filters the time intervals available for the Time field.
		 *
		 * @since 1.6.0
		 *
		 * @param array $time_intervals Array of time intervals.
		 */
		$time_intervals = apply_filters( // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName
			'wpforms_datetime_time_intervals',
			[
				'15' => esc_html__( '15 minutes', 'wpforms-lite' ),
				'30' => esc_html__( '30 minutes', 'wpforms-lite' ),
				'60' => esc_html__( '1 hour', 'wpforms-lite' ),
			]
		);

		printf(
			'<div class="wpforms-clear wpforms-field-option-row wpforms-field-option-row-time no-gap" id="wpforms-field-option-row-%d-time" data-subfield="time" data-field-id="%d">',
			esc_attr( $field['id'] ),
			esc_attr( $field['id'] )
		);
			$this->field_element(
				'label',
				$field,
				[
					'slug'    => 'time_placeholder',
					'value'   => esc_html__( 'Time', 'wpforms-lite' ),
					'tooltip' => esc_html__( 'Advanced time options.', 'wpforms-lite' ),
				]
			);

			echo '<div class="wpforms-field-options-columns-2 wpforms-field-options-columns">';
				echo '<div class="interval wpforms-field-options-column">';
					printf(
						'<select id="wpforms-field-option-%d-time_interval" name="fields[%d][time_interval]">',
						esc_attr( $field['id'] ),
						esc_attr( $field['id'] )
					);
						foreach ( $time_intervals as $key => $value ) {
							printf(
								'<option value="%s" %s>%s</option>',
								esc_attr( $key ),
								selected( $time_interval, $key, false ),
								$value // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							);
						}
					echo '</select>';
					printf(
						'<label for="wpforms-field-option-%d-time_interval" class="sub-label">%s</label>',
						esc_attr( $field['id'] ),
						esc_html__( 'Interval', 'wpforms-lite' )
					);
				echo '</div>';

				echo '<div class="format wpforms-field-options-column">';
					printf(
						'<select id="wpforms-field-option-%d-time_format" name="fields[%d][time_format]">',
						esc_attr( $field['id'] ),
						esc_attr( $field['id'] )
					);
						foreach ( $time_formats as $key => $value ) {
							printf(
								'<option value="%s" %s>%s</option>',
								esc_attr( $key ),
								selected( $time_format, $key, false ),
								esc_html( $value )
							);
						}
					echo '</select>';
					printf(
						'<label for="wpforms-field-option-%d-time_format" class="sub-label">%s</label>',
						esc_attr( $field['id'] ),
						esc_html__( 'Format', 'wpforms-lite' )
					);
				echo '</div>';
			echo '</div>';
			echo '<div class="placeholder wpforms-field-option-row">';
				printf(
					'<input type="text" class="placeholder" id="wpforms-field-option-%d-time_placeholder" name="fields[%d][time_placeholder]" value="%s">',
					esc_attr( $field['id'] ),
					esc_attr( $field['id'] ),
					esc_attr( $time_placeholder )
				);
				printf(
					'<label for="wpforms-field-option-%d-time_placeholder" class="sub-label">%s</label>',
					esc_attr( $field['id'] ),
					esc_html__( 'Placeholder', 'wpforms-lite' )
				);
			echo '</div>';

			// Limit Hours options.
			$this->field_options_limit_hours( $field );

		echo '</div>';

		echo '</div>';

		// Custom CSS classes.
		$this->field_option( 'css', $field );

		// Hide label.
		$this->field_option( 'label_hide', $field );

		// Hide sublabels.
		$sublabel_class = isset( $field['format'] ) && $field['format'] !== self::DEFAULTS['format'] ? 'wpforms-hidden' : '';

		$this->field_option( 'sublabel_hide', $field, [ 'class' => $sublabel_class ] );

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
	 * Get regular date formats.
	 *
	 * @since 1.9.8.3
	 *
	 * @return array
	 */
	private function get_regular_date_formats(): array {

		return [
			self::DEFAULTS['date_format'],
			self::ALT_DATE_FORMAT,
			'Y/m/d',
			'm.d.Y',
			'd.m.Y',
			'Y.m.d',
		];
	}

	/**
	 * Display limit days options.
	 *
	 * @since 1.9.4
	 *
	 * @param array $field Field setting.
	 */
	private function field_options_limit_days( array $field ): void {

		echo '<div class="wpforms-clear"></div>';

		$output = $this->field_element(
			'toggle',
			$field,
			[
				'slug'    => 'date_limit_days',
				'value'   => ! empty( $field['date_limit_days'] ) ? '1' : '0',
				'desc'    => esc_html__( 'Limit Days', 'wpforms-lite' ),
				'tooltip' => esc_html__( 'Check this option to adjust which days of the week can be selected.', 'wpforms-lite' ),
				'class'   => 'wpforms-panel-field-toggle',
			],
			false
		);

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'date_limit_days',
				'content' => $output,
				'class'   => 'wpforms-clear',
			]
		);

		$week_days = [
			'sun' => esc_html__( 'Sun', 'wpforms-lite' ),
			'mon' => esc_html__( 'Mon', 'wpforms-lite' ),
			'tue' => esc_html__( 'Tue', 'wpforms-lite' ),
			'wed' => esc_html__( 'Wed', 'wpforms-lite' ),
			'thu' => esc_html__( 'Thu', 'wpforms-lite' ),
			'fri' => esc_html__( 'Fri', 'wpforms-lite' ),
			'sat' => esc_html__( 'Sat', 'wpforms-lite' ),
		];

		// Rearrange days array according to the Start of Week setting.
		$start_of_week = get_option( 'start_of_week' );
		$start_of_week = ! empty( $start_of_week ) ? (int) $start_of_week : 0;

		if ( $start_of_week > 0 ) {
			$days_after = $week_days;
			$days_begin = array_splice( $days_after, 0, $start_of_week );
			$days       = array_merge( $days_after, $days_begin );
		} else {
			$days = $week_days;
		}

		// Limit Days body.
		$field = $this->field_options_limit_days_body( $days, $field );

		// Disable Past Dates.
		$this->field_options_limit_days_disable_past_dates( $field );

		// Disable Today's Date.
		$output = $this->field_element(
			'toggle',
			$field,
			[
				'slug'    => 'date_disable_todays_date',
				'value'   => ! empty( $field['date_disable_todays_date'] ) ? '1' : '0',
				'desc'    => esc_html__( 'Disable Today\'s Date', 'wpforms-lite' ),
				'tooltip' => esc_html__( 'Check this option to prevent today\'s date from being selected.', 'wpforms-lite' ),
			],
			false
		);

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'date_disable_todays_date',
				'content' => $output,
				'class'   => ! isset( $field['date_disable_past_dates'] ) ? 'wpforms-hide' : '',
			]
		);
	}

	/**
	 * Display limit hours options.
	 *
	 * @since 1.9.4
	 *
	 * @param array $field Field setting.
	 */
	private function field_options_limit_hours( array $field ): void {

		echo '<div class="wpforms-clear"></div>';

		$output = $this->field_element(
			'toggle',
			$field,
			[
				'slug'    => 'time_limit_hours',
				'value'   => ! empty( $field['time_limit_hours'] ) ? '1' : '0',
				'desc'    => esc_html__( 'Limit Hours', 'wpforms-lite' ),
				'tooltip' => esc_html__( 'Check this option to adjust the range of times that can be selected.', 'wpforms-lite' ),
				'class'   => 'wpforms-panel-field-toggle',
			],
			false
		);

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'time_limit_hours',
				'content' => $output,
			]
		);

		// Determine a time format type.
		// If the format contains `g` or `h`, then this is 12-hour format, otherwise 24 hours.
		$time_format = empty( $field['time_format'] ) || preg_match( '/[gh]/', $field['time_format'] ) ? 12 : 24;

		// Limit Hours body.
		$output = $this->field_options_limit_hours_body( $field, $time_format );

		printf(
			'<div
				class="wpforms-field-option-row wpforms-field-option-row-%1$s %2$s"
				id="wpforms-field-option-row-%3$d-%1$s"
				data-toggle="%4$s"
				data-toggle-value="1"
				data-field-id="%3$d">%5$s</div>',
			'time_limit_hours_options',
			'wpforms-panel-field-toggle-body',
			esc_attr( $field['id'] ),
			esc_attr( 'fields[' . (int) $field['id'] . '][time_limit_hours]' ),
			$output // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		);
	}

	/**
	 * Generate an array of numeric options for date/time selectors.
	 *
	 * @since 1.9.4
	 *
	 * @param integer $min  Minimum value.
	 * @param integer $max  Maximum value.
	 * @param integer $step Step.
	 *
	 * @return array
	 */
	private function get_selector_numeric_options( int $min, int $max, int $step = 1 ): array {

		$range   = range( $min, $max, $step );
		$options = [];

		foreach ( $range as $i ) {
			$value             = str_pad( $i, 2, '0', STR_PAD_LEFT );
			$options[ $value ] = $value;
		}

		return $options;
	}

	/**
	 * Add class to field options wrapper to indicate if field confirmation is enabled.
	 *
	 * @since 1.9.4
	 *
	 * @param string|mixed $css_class CSS class.
	 * @param array        $field     Field data.
	 *
	 * @return string
	 */
	public function field_option_class( $css_class, array $field ): string {

		$css_class = (string) $css_class;

		if ( $this->type === $field['type'] ) {
			$date_type = ! empty( $field['date_type'] ) ? sanitize_html_class( $field['date_type'] ) : 'datepicker';

			$css_class .= " wpforms-date-type-$date_type";
		}

		return $css_class;
	}

	/**
	 * Field preview inside the builder.
	 *
	 * @since 1.9.4
	 *
	 * @param array $field Field data and settings.
	 */
	public function field_preview( $field ) {

		$date_placeholder = ! empty( $field['date_placeholder'] ) ? $field['date_placeholder'] : '';
		$time_placeholder = ! empty( $field['time_placeholder'] ) ? $field['time_placeholder'] : '';
		$format           = ! empty( $field['format'] ) ? $field['format'] : self::DEFAULTS['format'];
		$date_type        = ! empty( $field['date_type'] ) ? $field['date_type'] : 'datepicker';
		$date_format      = ! empty( $field['date_format'] ) ? $field['date_format'] : self::DEFAULTS['date_format'];

		if ( in_array( $date_format, $this->get_month_day_formats(), true ) ) {
			$date_first_select  = 'MM';
			$date_second_select = 'DD';
			$date_third_select  = 'YYYY';
		} elseif ( in_array( $date_format, $this->get_day_month_formats(), true ) ) {
			$date_first_select  = 'DD';
			$date_second_select = 'MM';
			$date_third_select  = 'YYYY';
		} else {
			$date_first_select  = 'YYYY';
			$date_second_select = 'MM';
			$date_third_select  = 'DD';
		}

		// Label.
		$this->field_preview_option(
			'label',
			$field,
			[
				'label_badge' => $this->get_field_preview_badge(),
			]
		);

		printf(
			'<div class="%s format-selected">',
			sanitize_html_class( 'format-selected-' . $format )
		);

			// Date.
			printf(
				'<div class="wpforms-date %s">',
				sanitize_html_class( 'wpforms-date-type-' . $date_type )
			);
				echo '<div class="wpforms-date-datepicker">';
					printf( '<input type="text" placeholder="%s" class="primary-input" readonly>', esc_attr( $date_placeholder ) );
					printf( '<label class="wpforms-sub-label">%s</label>', esc_html__( 'Date', 'wpforms-lite' ) );
				echo '</div>';
				echo '<div class="wpforms-date-dropdown">';
					printf( '<select readonly class="first"><option>%s</option></select>', esc_html( $date_first_select ) );
					printf( '<select readonly class="second"><option>%s</option></select>', esc_html( $date_second_select ) );
					printf( '<select readonly class="third"><option>%s</option></select>', esc_html( $date_third_select ) );
					printf( '<label class="wpforms-sub-label">%s</label>', esc_html__( 'Date', 'wpforms-lite' ) );
				echo '</div>';
			echo '</div>';

			// Time.
			echo '<div class="wpforms-time">';
				printf( '<input type="text" placeholder="%s" class="primary-input" readonly>', esc_attr( $time_placeholder ) );
				printf( '<label class="wpforms-sub-label">%s</label>', esc_html__( 'Time', 'wpforms-lite' ) );
			echo '</div>';
		echo '</div>';

		// Description.
		$this->field_preview_option( 'description', $field );
	}

	/**
	 * Get month-day date formats.
	 *
	 * @since 1.9.8.3
	 *
	 * @return array
	 */
	private function get_month_day_formats(): array {

		return [ 'mm/dd/yyyy', self::DEFAULTS['date_format'], 'm.d.Y' ];
	}

	/**
	 * Get day-month date formats.
	 *
	 * @since 1.9.8.3
	 *
	 * @return array
	 */
	private function get_day_month_formats(): array {

		return [ 'dd/mm/yyyy', self::ALT_DATE_FORMAT, 'd.m.Y' ];
	}

	/**
	 * Field display on the form front-end.
	 *
	 * @since 1.9.4
	 *
	 * @param array $field      Field data and settings.
	 * @param array $deprecated Deprecated array of field attributes.
	 * @param array $form_data  Form data and settings.
	 */
	public function field_display( $field, $deprecated, $form_data ) {
	}

	/**
	 * Field options: Limit Days body section.
	 *
	 * @since 1.9.4
	 *
	 * @param array $days  Array of days.
	 * @param array $field Field data and settings.
	 *
	 * @return array Modified field data array.
	 */
	public function field_options_limit_days_body( array $days, array $field ): array {

		// Limit Days body.
		$output = '';

		foreach ( $days as $day => $day_translation ) {

			$day_slug = 'date_limit_days_' . $day;

			// Set defaults.
			if ( ! isset( $field['date_format'] ) ) {
				$field[ $day_slug ] = $this->default_settings[ $day_slug ];
			}

			$output .= '<label class="sub-label">';
			$output .= $this->field_element(
				'checkbox',
				$field,
				[
					'slug'   => $day_slug,
					'value'  => ! empty( $field[ $day_slug ] ) ? '1' : '0',
					'nodesc' => '1',
					'class'  => 'wpforms-field-options-column',
				],
				false
			);
			$output .= '<br>' . $day_translation . '</label>';
		}

		printf(
			'<div
				class="wpforms-field-option-row wpforms-field-option-row-date_limit_days_options wpforms-panel-field-toggle-body wpforms-field-options-columns wpforms-field-options-columns-7 checkboxes-row"
				id="wpforms-field-option-row-%1$d-date_limit_days_options"
				data-toggle="%2$s"
				data-toggle-value="1"
				data-field-id="%1$d">%3$s</div>',
			esc_attr( $field['id'] ),
			esc_attr( 'fields[' . (int) $field['id'] . '][date_limit_days]' ),
			$output // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		);

		return $field;
	}

	/**
	 * Field options: Limit Days - Disable Past Dates section.
	 *
	 * @since 1.9.4
	 *
	 * @param array $field Field data.
	 */
	public function field_options_limit_days_disable_past_dates( array $field ): void {

		$output = $this->field_element(
			'toggle',
			$field,
			[
				'slug'    => 'date_disable_past_dates',
				'value'   => ! empty( $field['date_disable_past_dates'] ) ? '1' : '0',
				'desc'    => esc_html__( 'Disable Past Dates', 'wpforms-lite' ),
				'tooltip' => esc_html__( 'Check this option to prevent any previous date from being selected.', 'wpforms-lite' ),
			],
			false
		);

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'date_disable_past_dates',
				'content' => $output,
			]
		);
	}

	/**
	 * Field options: Limit Hours - body section.
	 *
	 * @since 1.9.4
	 *
	 * @param array $field       Field data.
	 * @param int   $time_format Time format.
	 *
	 * @return string
	 */
	private function field_options_limit_hours_body( array $field, int $time_format ): string {

		$output = '';

		foreach ( [ 'start', 'end' ] as $option ) {

			$output .= '<div class="wpforms-field-options-columns wpforms-field-options-columns-4">'; // Open columns container.

			$slug    = 'time_limit_hours_' . $option . '_hour';
			$output .= $this->field_element(
				'select',
				$field,
				[
					'slug'    => $slug,
					'value'   => ! empty( $field[ $slug ] ) ? $field[ $slug ] : $this->default_settings[ $slug ],
					'options' => $time_format === 12
						? $this->get_selector_numeric_options( 1, $time_format )
						: $this->get_selector_numeric_options( 0, $time_format - 1 ),
					'class'   => 'wpforms-field-options-column',
				],
				false
			);

			$slug    = 'time_limit_hours_' . $option . '_min';
			$output .= $this->field_element(
				'select',
				$field,
				[
					'slug'    => $slug,
					'value'   => ! empty( $field[ $slug ] ) ? $field[ $slug ] : $this->default_settings[ $slug ],
					'options' => $this->get_selector_numeric_options( 0, 59, 5 ),
					'class'   => 'wpforms-field-options-column',
				],
				false
			);

			$slug    = 'time_limit_hours_' . $option . '_ampm';
			$output .= $this->field_element(
				'select',
				$field,
				[
					'slug'    => $slug,
					'value'   => ! empty( $field[ $slug ] ) ? $field[ $slug ] : $this->default_settings[ $slug ],
					'options' => [
						'am' => 'AM',
						'pm' => 'PM',
					],
					'class'   => [
						'wpforms-field-options-column',
						$time_format === 24 ? 'wpforms-hidden-strict' : '',
					],
				],
				false
			);

			$slug    = 'time_limit_hours_' . $option . '_hour';
			$output .= $this->field_element(
				'label',
				$field,
				[
					'slug'  => $slug,
					'value' => $option === 'start' ? esc_html__( 'Start Time', 'wpforms-lite' ) : esc_html__( 'End Time', 'wpforms-lite' ),
					'class' => [
						'sub-label',
						'wpforms-field-options-column',
					],
				],
				false
			);

			$output .= sprintf(
				'<div class="%s wpforms-field-options-column"></div>',
				$time_format === 12 ? 'wpforms-hidden-strict' : ''
			);

			$output .= '</div>'; // Close columns container.
		}

		return $output;
	}
}
