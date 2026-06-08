<?php

// phpcs:ignore Generic.Commenting.DocComment.MissingShort
/** @noinspection AutoloadingIssuesInspection */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Internal information field class.
 *
 * @since 1.7.6
 */
class WPForms_Field_Internal_Information extends WPForms_Field {

	/**
	 * The key used to save form checkboxes in the post meta table.
	 *
	 * @since 1.7.6
	 *
	 * @var string
	 */
	private const CHECKBOX_META_KEY = 'wpforms_iif_checkboxes';

	/**
	 * Class initialization method.
	 *
	 * @since 1.7.6
	 */
	public function init() {

		$this->name  = $this->is_editable() ? esc_html__( 'Internal Information', 'wpforms-lite' ) : esc_html__( 'This field is not editable', 'wpforms-lite' );
		$this->type  = 'internal-information';
		$this->icon  = 'fa fa-sticky-note-o';
		$this->order = 550;

		$this->hooks();
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.7.6
	 *
	 * @noinspection PhpUnnecessaryCurlyVarSyntaxInspection
	 */
	private function hooks() {

		add_filter( 'wpforms_entries_table_fields_disallow', [ $this, 'hide_column_in_entries_table' ], 10, 2 );
		add_filter( 'wpforms_field_preview_class', [ $this, 'add_css_class_for_field_wrapper' ], 10, 2 );
		add_filter( 'wpforms_field_new_class', [ $this, 'add_css_class_for_field_wrapper' ], 10, 2 );
		add_filter( "wpforms_pro_admin_entries_edit_is_field_displayable_{$this->type}", '__return_false' );
		add_filter( 'wpforms_builder_strings', [ $this, 'builder_strings' ], 10, 2 );
		add_filter( 'wpforms_frontend_form_data', [ $this, 'remove_internal_fields_on_front_end' ] );
		add_filter( 'wpforms_pro_fields_entry_preview_get_ignored_fields', [ $this, 'ignore_entry_preview' ] );
		add_filter( 'wpforms_process_before_form_data', [ $this, 'process_before_form_data' ], 10, 2 );
		add_filter( 'wpforms_field_preview_display_duplicate_button', [ $this, 'display_duplicate_button' ], 10, 3 );
		add_action( 'wpforms_builder_enqueues', [ $this, 'builder_enqueues' ] );
		add_action( 'wp_ajax_wpforms_builder_save_internal_information_checkbox', [ $this, 'save_internal_information_checkbox' ] );
	}

	/**
	 * Whether the current field can be populated dynamically.
	 *
	 * @since 1.7.6
	 *
	 * @param array $properties Field properties.
	 * @param array $field      Current field specific data.
	 *
	 * @return bool
	 */
	public function is_dynamic_population_allowed( $properties, $field ): bool {

		return false;
	}

	/**
	 * Whether the current field can be populated using a fallback.
	 *
	 * @since 1.7.6
	 *
	 * @param array $properties Field properties.
	 * @param array $field      Current field specific data.
	 *
	 * @return bool
	 */
	public function is_fallback_population_allowed( $properties, $field ): bool {

		return false;
	}

	/**
	 * Define field options to display in the left panel.
	 *
	 * @since 1.7.6
	 *
	 * @param array $field Field data and settings.
	 */
	public function field_options( $field ) {

		$this->field_option(
			'basic-options',
			$field,
			[
				'markup' => 'open',
			]
		);

		$this->heading_option( $field );
		$this->field_option( 'description', $field );
		$this->expanded_description_option( $field );
		$this->cta_label_option( $field );
		$this->cta_link_option( $field );

		$this->field_option(
			'basic-options',
			$field,
			[
				'markup' => 'close',
			]
		);

		$this->field_code( $field );
	}

	/**
	 * Define field preview on the right side on builder.
	 *
	 * @since 1.7.6
	 *
	 * @param array $field Field data and settings.
	 */
	public function field_preview( $field ) {

		$class = wpforms_sanitize_classes( $field['class'] ?? '' );

		printf(
			'<div class="internal-information-wrap wpforms-clear %s">',
			esc_attr( $class )
		);

			// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
			echo wpforms_render( 'fields/internal-information/icon-lightbulb' );

			echo '<div class="internal-information-content">';

				$this->render_preview( 'heading', $field );
				$this->render_preview( 'description', $field );
				$this->render_preview( 'expanded-description', $field );
				$this->render_preview( 'addon', $field );

				if ( $this->is_button_displayable( $field ) ) {
					echo '<div class="wpforms-field-internal-information-row wpforms-field-internal-information-row-cta-button">';
					echo $this->render_custom_preview( 'cta-button', $field );
					echo '</div>';
				}

			echo '</div>';
			// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '</div>';
	}

	/**
	 * Checks if the button is displayable.
	 *
	 * @since 1.7.6
	 *
	 * @param array $field Field data.
	 *
	 * @return bool
	 */
	private function is_button_displayable( $field ): bool {

		return ! empty( $field['expanded-description'] ) ||
			( ! empty( $field['cta-label'] ) && ! empty( $field['cta-link'] ) ) ||
			$this->is_editable();
	}

	/**
	 * Stub to make the field not visible in the front-end.
	 *
	 * @since 1.7.6
	 *
	 * @param array $field      Field data and settings.
	 * @param array $deprecated Field attributes.
	 * @param array $form_data  Form data.
	 */
	public function field_display( $field, $deprecated, $form_data ) {
	}

	/**
	 * Heading option.
	 *
	 * @since 1.7.6
	 *
	 * @param array $field Field data and settings.
	 */
	private function heading_option( $field ) {

		$output = $this->field_element(
			'label',
			$field,
			[
				'slug'    => 'heading',
				'value'   => esc_html__( 'Heading', 'wpforms-lite' ),
				'tooltip' => esc_attr__( 'Enter text for the form field heading.', 'wpforms-lite' ),
			],
			false
		);

		$output .= $this->field_element(
			'text',
			$field,
			[
				'slug'  => 'label',
				'value' => ! empty( $field['label'] ) ? esc_attr( $field['label'] ) : '',
			],
			false
		);

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'heading',
				'content' => $output,
			]
		);
	}

	/**
	 * Expanded description option.
	 *
	 * @since 1.7.6
	 *
	 * @param array $field Field data and settings.
	 */
	private function expanded_description_option( $field ) {

		$output = $this->field_element(
			'label',
			$field,
			[
				'slug'    => 'expanded-description',
				'value'   => esc_html__( 'Expanded Content', 'wpforms-lite' ),
				'tooltip' => esc_attr__( 'Enter text for the form field expanded description.', 'wpforms-lite' ),
			],
			false
		);

		$output .= $this->field_element(
			'textarea',
			$field,
			[
				'slug'  => 'expanded-description',
				'value' => ! empty( $field['expanded-description'] ) ? esc_html( $field['expanded-description'] ) : '',
			],
			false
		);

		$output .= sprintf(
			'<p class="note">%s</p>',
			esc_html__( 'Adds an expandable content area below the description.', 'wpforms-lite' )
		);

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'expanded-description',
				'content' => $output,
			]
		);
	}

	/**
	 * CTA label option.
	 *
	 * @since 1.7.6
	 *
	 * @param array $field Field data and settings.
	 */
	private function cta_label_option( $field ) {

		$output = $this->field_element(
			'label',
			$field,
			[
				'slug'    => 'cta-label',
				'value'   => esc_html__( 'CTA Label', 'wpforms-lite' ),
				'tooltip' => esc_attr__( 'Enter label for the form field call to action button. The label will be ignored if the field has extended description content: in that case button will be used to expand the description content.', 'wpforms-lite' ),
			],
			false
		);

		$output .= $this->field_element(
			'text',
			$field,
			[
				'slug'  => 'cta-label',
				'value' => ! empty( $field['cta-label'] ) ? esc_attr( $field['cta-label'] ) : esc_attr__( 'Learn More', 'wpforms-lite' ),
			],
			false
		);

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'cta-label',
				'content' => $output,
			]
		);
	}

	/**
	 * CTA link option.
	 *
	 * @since 1.7.6
	 *
	 * @param array $field Field data and settings.
	 */
	private function cta_link_option( $field ) {

		$output = $this->field_element(
			'label',
			$field,
			[
				'slug'    => 'cta-link',
				'value'   => esc_html__( 'CTA Link', 'wpforms-lite' ),
				'tooltip' => esc_attr__( 'Enter the URL for the form field call to action button. URL will be ignored if the field has extended description content: in that case button will be used to expand the description content.', 'wpforms-lite' ),
			],
			false
		);

		$output .= $this->field_element(
			'text',
			$field,
			[
				'slug'  => 'cta-link',
				'value' => ! empty( $field['cta-link'] ) ? esc_url( $field['cta-link'] ) : '',
			],
			false
		);

		$output .= sprintf(
			'<p class="note">%s</p>',
			esc_html__( 'CTA is hidden if Expanded Content is used.', 'wpforms-lite' )
		);

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'cta-link',
				'content' => $output,
			]
		);
	}

	/**
	 * Add hidden input with code identifier.
	 *
	 * @since 1.8.9
	 *
	 * @param array $field Field data and settings.
	 */
	private function field_code( $field ) {

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'code',
				'content' => sprintf(
					'<input type="hidden" name="fields[%1$s][code]" value="%2$s">',
					$field['id'],
					! empty( $field['code'] ) ? esc_attr( $field['code'] ) : ''
				),
			]
		);
	}

	/**
	 * Add a CSS class to hide field settings when the field is not editable.
	 *
	 * @since 1.7.6
	 *
	 * @param string $option  Field option to render.
	 * @param array  $field   Field data and settings.
	 * @param array  $args    Field preview arguments.
	 * @param bool   $do_echo Print or return the value. Print by default.
	 *
	 * @return string|null
	 */
	public function field_element( $option, $field, $args = [], $do_echo = true ) {

		if ( ! isset( $args['class'] ) ) {
			$args['class'] = '';
		}

		if ( ! $this->is_editable() ) {
			$args['class'] .= ' wpforms-hidden ';
		}

		return parent::field_element( $option, $field, $args, $do_echo );
	}

	/**
	 * Render a custom option preview on the right side of the builder.
	 *
	 * @since 1.7.6
	 *
	 * @param string $option Field option to render.
	 * @param array  $field  Field data and settings.
	 * @param array  $args   Field arguments.
	 *
	 * @return string
	 * @noinspection HtmlUnknownTarget
	 */
	private function render_custom_preview( $option, $field, $args = [] ): string { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		$class        = ! empty( $args['class'] ) ? wpforms_sanitize_classes( $args['class'] ) : '';
		$allowed_tags = $this->get_allowed_tags();

		switch ( $option ) {
			case 'heading':
				$label = isset( $field['label'] ) && ! wpforms_is_empty_string( $field['label'] ) ? esc_html( $field['label'] ) : '';

				if ( ! $label ) {
					$class .= ' hidden ';
				}

				return sprintf(
					'<label class="label-title heading %s"><span class="text">%s</span><span class="required">*</span></label>',
					esc_attr( $class ),
					esc_html( $label )
				);

			case 'description': // phpcs:ignore WPForms.Formatting.Switch.AddEmptyLineBefore
				$description = ! empty( $field['description'] ) ? wp_kses( $field['description'], $allowed_tags ) : '';
				$description = wpautop( $this->replace_checkboxes( $description, $field ) );
				$description = $this->add_link_attributes( $description );

				return sprintf( '<div class="description %s">%s</div>', $class, $description );

			case 'expanded-description': // phpcs:ignore WPForms.Formatting.Switch.AddEmptyLineBefore
				$description = isset( $field['expanded-description'] ) && ! wpforms_is_empty_string( $field['expanded-description'] ) ? wp_kses( $field['expanded-description'], $allowed_tags ) : '';
				$description = wpautop( $this->replace_checkboxes( $description, $field ) );
				$description = $this->add_link_attributes( $description );

				return sprintf( '<div class="expanded-description %s">%s</div>', esc_attr( $class ), wp_kses( $description, $allowed_tags ) );

			case 'cta-button': // phpcs:ignore WPForms.Formatting.Switch.AddEmptyLineBefore
				$label = ! empty( $field['cta-label'] ) && empty( $field['expanded-description'] ) ? esc_attr( $field['cta-label'] ) : esc_attr__( 'Learn More', 'wpforms-lite' );

				if ( ! empty( $field['expanded-description'] ) ) {
					return sprintf(
						'<div class="cta-button cta-expand-description not-expanded %s"><a href="#" target="_blank" rel="noopener noreferrer"><span class="button-label">%s</span> %s %s</a></div>',
						esc_attr( $class ),
						esc_html( $label ),
						wpforms_render( 'fields/internal-information/icon-not-expanded' ),
						wpforms_render( 'fields/internal-information/icon-expanded' )
					);
				}

				if ( ! empty( $field['cta-link'] ) ) {
					return sprintf( '<div class="cta-button cta-link-external %s"><a href="%s" target="_blank" rel="noopener noreferrer">%s</a></div>', esc_attr( $class ), esc_url( $this->add_url_utm( $field ) ), esc_html( $label ) );
				}

				return sprintf( '<div class="cta-button cta-link-external %s"><a href="" target="_blank" rel="noopener noreferrer" class="hidden"><span class="button-label"></span></a></div>', esc_attr( $class ) );

			case 'addon':
				if ( empty( $field['addon'] ) ) {
					return '';
				}

				return sprintf( '<input type="hidden" name="fields[%1$s][addon]" value="%2$s">', esc_attr( $field['id'] ), esc_attr( $field['addon'] ) );
		}

		return '';
	}

	/**
	 * Display the field button in the left panel only if the field is editable.
	 *
	 * @since 1.7.6
	 *
	 * @param array $fields All fields to display in the left panel.
	 *
	 * @return array
	 */
	public function field_button( $fields ) {

		if ( $this->is_editable() ) {
			return parent::field_button( $fields );
		}

		return $fields;
	}

	/**
	 * When the form is going to be displayed on the front-end, remove internal information fields.
	 *
	 * @since 1.7.6
	 *
	 * @param array $form_data Form data.
	 *
	 * @return array
	 */
	public function remove_internal_fields_on_front_end( $form_data ) {

		if ( empty( $form_data['fields'] ) ) {
			return $form_data;
		}

		foreach ( $form_data['fields'] as $id => $field ) {
			if ( $field['type'] === $this->type ) {
				unset( $form_data['fields'][ $id ] );
			}
		}

		return $form_data;
	}

	/**
	 * Add the internal information field to the list of ignored fields for entry preview.
	 *
	 * @since 1.9.1
	 *
	 * @param array|mixed $ignored_fields Ignored fields.
	 *
	 * @return array
	 */
	public function ignore_entry_preview( $ignored_fields ): array {

		$ignored_fields   = (array) $ignored_fields;
		$ignored_fields[] = $this->type;

		return $ignored_fields;
	}

	/**
	 * Remove field from form data before processing the form submit.
	 *
	 * @since 1.7.6
	 *
	 * @param array $form_data Form data.
	 * @param array $entry     Form submission raw data ($_POST).
	 *
	 * @return array
	 * @noinspection PhpMissingParamTypeInspection
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function process_before_form_data( $form_data, $entry ) {

		return $this->remove_internal_fields_on_front_end( $form_data );
	}

	/**
	 * Do not display the duplicate button.
	 *
	 * @since 1.7.6
	 *
	 * @param bool  $is_visible If true, the duplicate button will be displayed.
	 * @param array $field      Field data and settings.
	 * @param array $form_data  Form data and settings.
	 *
	 * @return bool
	 * @noinspection PhpMissingParamTypeInspection
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function display_duplicate_button( $is_visible, $field, $form_data ) {

		if ( $this->is_internal_information_field( $field ) && ! $this->is_editable() ) {
			return false;
		}

		return $is_visible;
	}

	/**
	 * Hide the column from the entry list table.
	 *
	 * @since 1.7.6
	 *
	 * @param array|mixed $disallowed Table columns.
	 *
	 * @return array
	 */
	public function hide_column_in_entries_table( $disallowed ): array {

		$disallowed   = (array) $disallowed;
		$disallowed[] = $this->type;

		return $disallowed;
	}

	/**
	 * Add a CSS class for the field parent div informing about mode (editable or not).
	 *
	 * @since 1.7.6
	 *
	 * @param string $css   CSS classes.
	 * @param array  $field Field data and settings.
	 *
	 * @return string
	 */
	public function add_css_class_for_field_wrapper( $css, $field ) {

		if ( ! $this->is_internal_information_field( $field ) ) {
			return $css;
		}

		// If the Internal Information field is added by some add-ons, it will be hidden by default.
		// Add styles to the addon assets to display the field.
		// When the addon is disabled, the field is hidden.
		if ( ! empty( $field['addon'] ) ) {
			$css .= sprintf( ' wpforms-field-internal-information-%s-addon wpforms-hidden', $field['addon'] );
		}

		if ( $this->is_editable() ) {
			$css .= ' internal-information-editable ';

			return $css;
		}

		$css .= ' ui-sortable-disabled internal-information-not-editable internal-information-not-draggable ';

		return str_replace( 'ui-sortable-handle', '', $css );
	}

	/**
	 * Save the checkbox state to the post meta table.
	 *
	 * @since 1.7.6
	 */
	public function save_internal_information_checkbox(): void {

		$form_id = isset( $_POST['formId'] ) ? absint( $_POST['formId'] ) : 0;

		// Run several checks: required items, security, permissions.
		if (
			! $form_id ||
			! isset( $_POST['name'], $_POST['checked'] ) ||
			! check_ajax_referer( 'wpforms-builder', 'nonce', false ) ||
			! wpforms_current_user_can( 'edit_forms', $form_id )
		) {
			wp_send_json_error();
		}

		$checked   = (int) $_POST['checked'];
		$name      = sanitize_text_field( wp_unslash( $_POST['name'] ) );
		$post_meta = get_post_meta( $form_id, self::CHECKBOX_META_KEY, true );
		$post_meta = ! empty( $post_meta ) ? (array) $post_meta : [];

		if ( $checked ) {
			$post_meta[ $name ] = $checked;
		} else {
			unset( $post_meta[ $name ] );
		}

		update_post_meta( $form_id, self::CHECKBOX_META_KEY, $post_meta );

		wp_send_json_success();
	}

	/**
	 * Localized strings for a wpforms-internal-information-field JS script.
	 *
	 * @since 1.7.6
	 *
	 * @param array $strings Localized strings.
	 * @param array $form    The form element.
	 *
	 * @return array
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function builder_strings( $strings, $form ) {

		$strings['iif_redirect_url_field_error'] = esc_html__( 'You should enter a valid absolute address to the CTA Link field or leave it empty.', 'wpforms-lite' );
		$strings['iif_dismiss']                  = esc_html__( 'Dismiss', 'wpforms-lite' );
		$strings['iif_more']                     = esc_html__( 'Learn More', 'wpforms-lite' );

		return $strings;
	}

	/**
	 * Enqueue wpforms-internal-information-field script.
	 *
	 * @since 1.7.6
	 *
	 * @param string $view Current view.
	 *
	 * @noinspection PhpUnusedParameterInspection, PhpUnnecessaryCurlyVarSyntaxInspection
	 */
	public function builder_enqueues( $view ) {

		$min = wpforms_get_min_suffix();

		wp_enqueue_script(
			'wpforms-md5-hash',
			WPFORMS_PLUGIN_URL . 'assets/lib/md5.min.js',
			[ 'wpforms-builder' ],
			'2.19.0',
			false
		);

		wp_enqueue_script(
			'wpforms-internal-information-field',
			WPFORMS_PLUGIN_URL . "assets/js/admin/builder/fields/internal-information{$min}.js",
			[ 'wpforms-builder', 'wpforms-md5-hash' ],
			WPFORMS_VERSION,
			false
		);
	}

	/**
	 * Checks if the user is allowed to edit the field's content.
	 *
	 * @since 1.7.6
	 *
	 * @return bool
	 */
	private function is_editable(): bool {

		/**
		 * Allow changing a mode.
		 *
		 * @since 1.7.6
		 *
		 * @param bool $is_editable True if editable mode is allowed. Default: false.
		 */
		return (bool) apply_filters( 'wpforms_field_internal_information_is_editable', false );
	}

	/**
	 * Check if the field has type internal-information.
	 *
	 * @since 1.7.6
	 *
	 * @param array $field Field data.
	 *
	 * @return bool
	 */
	private function is_internal_information_field( $field ): bool {

		return isset( $field['type'] ) && $field['type'] === $this->type;
	}

	/**
	 * Render the result of the field_preview_option into a custom div.
	 *
	 * If the field has no value, do not echo anything.
	 *
	 * @since 1.7.6
	 *
	 * @param string $label Field label.
	 * @param array  $field Field settings and data.
	 * @param array  $args  Field arguments.
	 *
	 * @noinspection PhpSameParameterValueInspection
	 */
	private function render_preview( $label, $field, $args = [] ): void {

		$key = $label === 'heading' ? 'label' : $label;

		if ( empty( $field[ $key ] ) && ! $this->is_editable() ) {
			return;
		}

		$allowed_tags = $this->get_allowed_tags();

		printf(
			'<div class="wpforms-field-internal-information-row wpforms-field-internal-information-row-%s">%s</div>',
			esc_attr( $label ),
			wp_kses( $this->render_custom_preview( $label, $field, $args ), $allowed_tags )
		);
	}

	/**
	 * Replace `[] some text` with checkboxes.
	 *
	 * Additionally, generates the input name by hashing the line of text where the checkbox is.
	 *
	 * @since 1.7.6
	 *
	 * @param string $description Expanded description.
	 * @param array  $field       Field data and settings.
	 *
	 * @return string
	 * @noinspection HtmlUnknownAttribute
	 */
	private function replace_checkboxes( string $description, array $field ): string {

		if ( ! $this->form_id ) {
			return $description;
		}

		$lines     = explode( PHP_EOL, $description );
		$replaced  = [];
		$post_meta = get_post_meta( $this->form_id, self::CHECKBOX_META_KEY, true );
		$post_meta = ! empty( $post_meta ) ? (array) $post_meta : [];
		$field_id  = $field['id'] ?? 0;
		$needle    = '[] ';

		foreach ( $lines as $line_number => $line ) {
			$line = trim( $line );

			if ( strpos( $line, $needle ) !== 0 ) {
				$replaced[] = $line . PHP_EOL;

				continue;
			}

			$field_name = sprintf( 'iif-%d-%s-%d', $field_id, md5( $line ), $line_number );
			$checked    = (int) isset( $post_meta[ $field_name ] );
			$attributes = [
				'name'  => esc_attr( $field_name ),
				'value' => 1,
			];

			if ( $this->is_editable() ) {
				$attributes['disabled'] = 'disabled';
				$attributes['title']    = esc_html__( 'This field is disabled in the editor mode.', 'wpforms-lite' );
			}

			$html = sprintf(
				'<div class="wpforms-field-internal-information-checkbox-input"><input type="checkbox" %s %s /></div><div class="wpforms-field-internal-information-checkbox-label">',
				wpforms_html_attributes(
					'',
					[ 'wpforms-field-internal-information-checkbox' ],
					[],
					$attributes
				),
				! $this->is_editable() ? checked( $checked, 1, false ) : ''
			);

			$line = substr_replace( $line, $html, 0, strlen( $needle ) );

			$replaced[] = '<div class="wpforms-field-internal-information-checkbox-wrap">' . $line . '</div></div>';
		}

		return implode( '', $replaced );
	}

	/**
	 * Return allowed tags specific to internal information field content.
	 *
	 * @since 1.7.6
	 *
	 * @return array
	 */
	private function get_allowed_tags(): array {

		$allowed_tags = wpforms_builder_preview_get_allowed_tags();

		$allowed_tags['input'] = [
			'type'     => [],
			'name'     => [],
			'value'    => [],
			'class'    => [],
			'checked'  => [],
			'disabled' => [],
			'title'    => [],
		];

		return $allowed_tags;
	}

	/**
	 * Adds link parameters to all links in the provided content.
	 *
	 * @since 1.8.3
	 *
	 * @param string $content The content to modify.
	 *
	 * @return string The modified content with UTM parameters added to links.
	 */
	private function add_link_attributes( string $content ): string {

		if ( empty( $content ) || ! class_exists( 'DOMDocument' ) ) {
			return $content;
		}

		$dom           = new DOMDocument();
		$form_obj      = wpforms()->obj( 'form' );
		$form_data     = $form_obj ? $form_obj->get( $this->form_id, [ 'content_only' => true ] ) : [];
		$templates_obj = wpforms()->obj( 'builder_templates' );
		$template      = $form_data['meta']['template'] ?? '';
		$template_data = $templates_obj && $template ? $templates_obj->get_template( $template ) : [];
		$template_name = $template_data['name'] ?? '';

		$dom->loadHTML( htmlspecialchars_decode( htmlentities( $content ) ) );

		$links = $dom->getElementsByTagName( 'a' );

		foreach ( $links as $link ) {
			$href          = $link->getAttribute( 'href' );
			$text          = $link->textContent; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			$modified_href = wpforms_utm_link( $href, 'Form Template Information Note', $template_name, $text );

			$link->setAttribute( 'href', $modified_href );
			$link->setAttribute( 'target', '_blank' );
			$link->setAttribute( 'rel', 'noopener noreferrer' );
		}

		// Remove the wrapper elements.
		$body        = $dom->getElementsByTagName( 'body' )->item( 0 );
		$child_nodes = $body->childNodes ?? []; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		$inner_html  = '';

		foreach ( $child_nodes as $node ) {
			$inner_html .= $dom->saveHTML( $node );
		}

		return $inner_html;
	}

	/**
	 * Add UTM parameters to the CTA button link.
	 *
	 * @since 1.7.6
	 *
	 * @param array $field Field data.
	 *
	 * @return string
	 */
	private function add_url_utm( array $field ): string {

		$cta_link = (string) $field['cta-link'];

		if ( strpos( $cta_link, 'https://wpforms.com' ) === 0 ) {
			return wpforms_utm_link( $cta_link, 'Template Documentation' );
		}

		return $cta_link;
	}
}

new WPForms_Field_Internal_Information();
