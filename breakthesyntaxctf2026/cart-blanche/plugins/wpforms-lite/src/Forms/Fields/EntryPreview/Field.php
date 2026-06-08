<?php

namespace WPForms\Forms\Fields\EntryPreview;

use WPForms\Forms\Fields\Traits\ProField as ProFieldTrait;
use WPForms_Field;

/**
 * Entry preview field.
 *
 * @since 1.9.4
 */
class Field extends WPForms_Field {

	use ProFieldTrait;

	/**
	 * Init.
	 *
	 * @since 1.9.4
	 */
	public function init() {

		// Define field type information.
		$this->name            = esc_html__( 'Entry Preview', 'wpforms-lite' );
		$this->keywords        = esc_html__( 'confirm', 'wpforms-lite' );
		$this->type            = 'entry-preview';
		$this->icon            = 'fa-file-text-o';
		$this->order           = 190;
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

		add_filter( 'wpforms_builder_strings', [ $this, 'add_builder_strings' ], 10, 2 );
		add_filter( 'wpforms_field_preview_display_duplicate_button', [ $this, 'field_display_duplicate_button' ], 10, 2 );
		add_filter( 'wpforms_field_new_display_duplicate_button', [ $this, 'field_display_duplicate_button' ], 10, 2 );
	}

	/**
	 * Field options panel inside the builder.
	 *
	 * @since 1.9.4
	 *
	 * @param array $field Field data.
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

		if ( empty( $this->is_disabled_field ) ) {
			$this->field_element(
				'row',
				$field,
				[
					'slug'    => 'description',
					'content' => sprintf(
						'<p class="note">%s</p>',
						esc_html__( 'Entry Preview must be displayed on its own page, without other fields. HTML fields are allowed.', 'wpforms-lite' )
					),
				]
			);
		}

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'preview-notice-enable',
				'content' => $this->field_element(
					'toggle',
					$field,
					[
						'slug'    => 'preview-notice-enable',
						// When we add the field to a form, it enabled by default.
						'value'   => ! empty( $field['preview-notice-enable'] ) || wp_doing_ajax(),
						'desc'    => esc_html__( 'Display Preview Notice', 'wpforms-lite' ),
						'tooltip' => esc_html__( 'Check this option to show a message above the entry preview.', 'wpforms-lite' ),
					],
					false
				),
			]
		);

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'preview-notice',
				'content' =>
					$this->field_element(
						'label',
						$field,
						[
							'slug'    => 'preview-notice',
							'value'   => esc_html__( 'Preview Notice', 'wpforms-lite' ),
							'tooltip' => esc_html__( 'Fill in the message to show above the entry preview.', 'wpforms-lite' ),
						],
						false
					) .
					$this->field_element(
						'textarea',
						$field,
						[
							'slug'  => 'preview-notice',
							'value' => $field['preview-notice'] ?? self::get_default_notice(),
						],
						false
					),
			]
		);

		$this->field_option( 'basic-options', $field, [ 'markup' => 'close' ] );

		$this->field_option( 'advanced-options', $field, [ 'markup' => 'open' ] );

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'style',
				'content' =>
					$this->field_element(
						'label',
						$field,
						[
							'slug'    => 'style',
							'value'   => esc_html__( 'Style', 'wpforms-lite' ),
							'tooltip' => esc_html__( 'Choose the entry preview display style.', 'wpforms-lite' ),
						],
						false
					) .
					$this->field_element(
						'select',
						$field,
						[
							'slug'    => 'style',
							'value'   => ! empty( $field['style'] ) ? $field['style'] : 'basic',
							'options' => self::get_styles(),
						],
						false
					),
			]
		);

		$this->field_option( 'css', $field );

		$this->field_option( 'advanced-options', $field, [ 'markup' => 'close' ] );
	}

	/**
	 * Create the field preview.
	 *
	 * @since 1.9.4
	 *
	 * @param array $field Field data and settings.
	 *
	 * @noinspection HtmlUnknownAttribute*/
	public function field_preview( $field ) {

		printf(
			'<label class="label-title">
			<span class="text">%1$s</span>%2$s</label>',
			esc_html__( 'Entry Preview', 'wpforms-lite' ),
			$this->get_field_preview_badge() // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		);

		$is_new_field = wp_doing_ajax();
		$notice       = ! empty( $field['preview-notice-enable'] ) && isset( $field['preview-notice'] ) && ! wpforms_is_empty_string( $field['preview-notice'] )
			? force_balance_tags( $field['preview-notice'] ) : '';
		$notice       = $is_new_field || wpforms_is_empty_string( $notice ) ? self::get_default_notice() : $notice;
		$is_disabled  = $is_new_field || ! empty( $field['preview-notice-enable'] );

		printf(
			'<div class="wpforms-entry-preview-notice nl2br"%2$s>%1$s</div>',
			wp_kses_post( nl2br( $notice ) ),
			! $is_disabled ? ' style="display: none"' : ''
		);

		printf(
			'<div class="wpforms-alert wpforms-alert-info"%2$s>
				<p>%1$s</p>
			</div>',
			esc_html__( 'Entry preview will be displayed here and will contain all fields found on the previous page.', 'wpforms-lite' ),
			$is_disabled ? ' style="display: none"' : ''
		);
	}

	/**
	 * Display the field input elements on the frontend.
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
	 * Add custom JS i18n strings for the builder.
	 *
	 * @since 1.9.4
	 *
	 * @param array|mixed $strings List of strings.
	 * @param array       $form    Current form.
	 *
	 * @return array
	 * @noinspection PhpMissingParamTypeInspection
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function add_builder_strings( $strings, $form ): array {

		$strings = (array) $strings;

		$strings['entry_preview_require_page_break']      = esc_html__( 'Page breaks are required for entry previews to work. If you\'d like to remove page breaks, you\'ll have to first remove the entry preview field.', 'wpforms-lite' );
		$strings['entry_preview_default_notice']          = self::get_default_notice();
		$strings['entry_preview_require_previous_button'] = esc_html__( 'You can\'t hide the previous button because it is required for the entry preview field on this page.', 'wpforms-lite' );

		return $strings;
	}

	/**
	 * Get default notice.
	 *
	 * @since 1.9.4
	 *
	 * @return string
	 */
	protected static function get_default_notice(): string {

		return sprintf(
			"<strong>%s</strong>\n%s",
			esc_html__( 'This is a preview of your submission. It has not been submitted yet!', 'wpforms-lite' ),
			esc_html__( 'Please take a moment to verify your information. You can also go back to make changes.', 'wpforms-lite' )
		);
	}

	/**
	 * Get a list of available styles.
	 *
	 * @since 1.9.4
	 *
	 * @return array
	 */
	protected static function get_styles(): array {

		return [
			'basic'         => esc_html__( 'Basic', 'wpforms-lite' ),
			'compact'       => esc_html__( 'Compact', 'wpforms-lite' ),
			'table'         => esc_html__( 'Table', 'wpforms-lite' ),
			'table_compact' => esc_html__( 'Table, Compact', 'wpforms-lite' ),
		];
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
