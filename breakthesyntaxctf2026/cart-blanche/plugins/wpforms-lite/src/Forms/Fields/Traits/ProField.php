<?php

namespace WPForms\Forms\Fields\Traits;

use WPForms\Admin\Education\Helpers;

/**
 * Trait ProField.
 *
 * Mostly educational things for the Pro field in the Lite plugin.
 *
 * @since 1.9.4
 */
trait ProField {

	/**
	 * Is it the Pro plugin?
	 *
	 * @since 1.9.4
	 *
	 * @var boolean
	 */
	protected $is_pro = false;

	/**
	 * Whether the field is a Pro field.
	 *
	 * @since 1.9.4
	 *
	 * @var boolean
	 */
	protected $is_pro_field = true;

	/**
	 * Addon slug.
	 *
	 * @since 1.9.4
	 *
	 * @var string
	 */
	protected $addon_slug;

	/**
	 * Whether the Addon is initialized.
	 *
	 * @since 1.9.4
	 *
	 * @var boolean
	 */
	protected $is_addon_initialized = false;

	/**
	 * Whether the field is disabled.
	 *
	 * @since 1.9.4
	 *
	 * @var boolean
	 */
	protected $is_disabled_field = true;

	/**
	 * Addon educational data.
	 *
	 * @since 1.9.4
	 *
	 * @var array
	 */
	protected $addon_edu_data = [];

	/**
	 * Init Pro Field.
	 *
	 * @since 1.9.4
	 */
	private function init_pro_field(): void {

		$this->is_pro               = wpforms()->is_pro();
		$this->is_addon_initialized = ! empty( $this->addon_slug ) && wpforms_is_addon_initialized( $this->addon_slug );
		$this->is_disabled_field    = $this->is_disabled_field();

		// Add hooks.
		add_filter( 'admin_init', [ $this, 'admin_init_pro_field' ] );
		add_filter( 'wpforms_builder_field_option_class', [ $this, 'filter_field_option_class' ], 10, 2 );
		add_filter( "wpforms_admin_builder_ajax_save_form_field_$this->type", [ $this, 'filter_save_form_field_data' ], 10, 3 );
		add_filter( 'wpforms_field_data', [ $this, 'filter_frontend_field_data' ], PHP_INT_MAX, 2 );
		add_filter( 'wpforms_helpers_form_pro_fields', [ $this, 'filter_form_pro_fields' ], PHP_INT_MAX, 2 );
		add_filter( 'wpforms_helpers_form_addons_edu_data', [ $this, 'filter_form_addons_edu_data' ], PHP_INT_MAX, 2 );
		add_filter( 'wpforms_field_preview_display_duplicate_button', [ $this, 'filter_field_preview_display_duplicate_button' ], 10, 2 );
		add_filter( 'wpforms_field_preview_class', [ $this, 'filter_field_preview_class' ], 10, 2 );
		add_filter( 'wpforms_entry_save_data', [ $this, 'filter_entry_save_data' ], 10, 3 );
		add_filter( 'wpforms_pro_admin_entries_table_facades_columns_get_field_columns_forbidden_fields', [ $this, 'filter_field_columns_forbidden_fields' ], 10, 2 );
		add_filter( 'wpforms_pro_admin_entries_export_configuration', [ $this, 'filter_entries_export_configuration' ] );
		add_filter( "wpforms_pro_admin_entries_edit_is_field_displayable_$this->type", [ $this, 'filter_is_field_displayable' ], 10, 3 );
	}

	/**
	 * Init Pro field on the ` admin_init ` hook.
	 *
	 * @since 1.9.4
	 */
	public function admin_init_pro_field(): void {

		$this->addon_edu_data = $this->get_field_addon_edu_data();
	}

	/**
	 * Get the Pro field options tab CSS class.
	 *
	 * @since 1.9.4
	 *
	 * @param string|mixed $css_class CSS class.
	 * @param array        $field     Field data.
	 *
	 * @return string
	 * @noinspection PhpMissingParamTypeInspection
	 */
	public function filter_field_option_class( $css_class, $field ): string {

		$css_class = (string) $css_class;

		if ( $field['type'] !== $this->type ) {
			return $css_class;
		}

		$css_class .= empty( $this->is_disabled_field ) ? '' : ' wpforms-field-is-pro';

		return trim( $css_class );
	}

	/**
	 * Filter field data before saving the form.
	 *
	 * @since 1.9.4
	 *
	 * @param array $field_data      Field data.
	 * @param array $form_data       Forms data.
	 * @param array $saved_form_data Saved form data.
	 *
	 * @noinspection PhpMissingParamTypeInspection
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function filter_save_form_field_data( $field_data, $form_data, $saved_form_data ) {

		if ( empty( $this->is_disabled_field ) ) {
			return $field_data;
		}

		$field_id = $field_data['id'] ?? '';

		// Prevent changes in the field data if it's a Pro field in Lite.
		// The settings are disabled in the Form Builder, but users can still hijack the data.
		// Therefore, return the saved field data if it exists.
		return $saved_form_data['fields'][ $field_id ] ?? $field_data;
	}

	/**
	 * Filter form pro fields array.
	 *
	 * @since 1.9.4
	 *
	 * @param array|mixed $pro_fields Pro fields array.
	 * @param array       $field      Field data.
	 */
	public function filter_form_pro_fields( $pro_fields, array $field ): array {

		$pro_fields = is_array( $pro_fields ) ? $pro_fields : [];

		if ( isset( $field['type'] ) && $field['type'] === $this->type ) {
			$pro_fields[] = $field;
		}

		return $pro_fields;
	}

	/**
	 * Filter the form addons educational data array.
	 *
	 * @since 1.9.4
	 *
	 * @param array|mixed $addons_edu_data Addons educational data.
	 * @param array       $field           Field data.
	 */
	public function filter_form_addons_edu_data( $addons_edu_data, array $field ): array {

		$addons_edu_data = is_array( $addons_edu_data ) ? $addons_edu_data : [];

		if ( ! isset( $field['type'] ) || $field['type'] !== $this->type || empty( $this->addon_edu_data ) ) {
			return $addons_edu_data;
		}

		$addon                     = $this->addon_edu_data['slug'] ?? '';
		$addons_edu_data[ $addon ] = $this->addon_edu_data;

		return $addons_edu_data;
	}

	/**
	 * Get the Pro field options notice.
	 *
	 * @since 1.9.4
	 *
	 * @noinspection HtmlUnknownAttribute
	 */
	private function get_field_options_notice(): string {

		if ( empty( $this->is_disabled_field ) ) {
			return '';
		}

		[ $name, $title, $content, $button_label, $button_utm ] = $this->get_field_options_notice_texts();

		$action       = $this->addon_edu_data['action'] ?? 'upgrade';
		$button_class = 'education-action-button';
		$button_attr  = '';

		if ( $action !== 'upgrade' ) {
			$button_class = 'education-modal';
			$button_attr  = sprintf(
				'data-nonce="%1$s" data-path="%2$s" data-url="%3$s" data-message="%4$s" data-field-type="%5$s" data-name="%6$s"',
				esc_attr( wp_create_nonce( 'wpforms-admin' ) ),
				$this->addon_edu_data['path'] ?? '',
				$this->addon_edu_data['url'] ?? '',
				$action === 'incompatible' ? $this->addon_edu_data['message'] : '',
				esc_attr( $this->type ),
				esc_attr( $name )
			);
		}

		return sprintf(
			'<div class="wpforms-field-option-field-title-notice">
				<div class="wpforms-alert-info wpforms-alert wpforms-educational-alert">
					<h4>%1$s</h4>
					<p>%2$s</p>
					<button class="wpforms-btn wpforms-btn-sm wpforms-btn-blue %3$s" data-action="%4$s" %6$s data-license="%7$s" data-utm-content="%8$s">%5$s</button>
				</div>
			</div>',
			$title,
			esc_html( $content ),
			esc_attr( $button_class ),
			esc_attr( $action ),
			esc_html( $button_label ),
			$button_attr,
			esc_attr( $this->addon_edu_data['license_level'] ?? 'pro' ),
			esc_attr( $button_utm )
		);
	}

	/**
	 * Get the Pro field options notice texts.
	 *
	 * @since 1.9.4
	 */
	private function get_field_options_notice_texts(): array {

		$action     = $this->addon_edu_data['action'] ?? 'upgrade';
		$addon_name = $this->addon_edu_data['title'] ?? '';
		$name       = $this->name;

		$titles = [
			'upgrade'      => sprintf( /* translators: %1$s - Field name. */
				esc_html__( '%1$s is a Pro Feature', 'wpforms-lite' ),
				$name
			),
			'incompatible' => esc_html__( 'Incompatible Addon', 'wpforms-lite' ),
		];

		$contents = [
			'upgrade'      => sprintf( /* translators: %1$s - Field name. */
				esc_html__( 'Upgrade to gain access to the %1$s field and dozens of other powerful features to help you build smarter forms and grow your business.', 'wpforms-lite' ),
				$name
			),
			'install'      => sprintf( /* translators: %1$s - Addon name. */
				esc_html__( 'You have access to the %1$s, but it\'s not currently installed.', 'wpforms-lite' ),
				$addon_name
			),
			'activate'     => sprintf( /* translators: %1$s - Addon name. */
				esc_html__( 'You have access to the %1$s, but it\'s not currently activated.', 'wpforms-lite' ),
				$addon_name
			),
			'incompatible' => sprintf( /* translators: %1$s - Addon name. */
				esc_html__( 'The %1$s is not compatible with this version of WPForms and requires an update.', 'wpforms-lite' ),
				$addon_name
			),
		];

		$button_labels = [
			'upgrade'      => esc_html__( 'Upgrade to Pro', 'wpforms-lite' ),
			'install'      => esc_html__( 'Install Addon', 'wpforms-lite' ),
			'activate'     => esc_html__( 'Activate Addon', 'wpforms-lite' ),
			'incompatible' => esc_html__( 'Update Addon', 'wpforms-lite' ),
		];

		// If it's not an upgrade action, use the addon data.
		if ( $action !== 'upgrade' ) {
			$name     = $addon_name;
			$utm_name = $this->addon_edu_data['utm_content'];
		} else {
			$edu_fields = wpforms()->obj( 'education_fields' );
			$edu_field  = $edu_fields ? $edu_fields->get_field( $this->type ) : null;
			$utm_name   = $edu_field['name_en'] ?? $this->type; // Fallback to the field type.
		}

		$button_utm = sprintf(
			'AI Form - %1$s notice',
			esc_html( $utm_name )
		);

		return [
			$name,
			$titles[ $action ] ?? $titles['upgrade'],
			$contents[ $action ] ?? $contents['upgrade'],
			$button_labels[ $action ] ?? $button_labels['upgrade'],
			$button_utm,
		];
	}

	/**
	 * Determine if the field is disabled.
	 *
	 * @since 1.9.4
	 * @since 1.10.0 The method access modifier is changed from private to protected.
	 */
	protected function is_disabled_field(): bool {

		// It is a Pro field in Lite OR the addon is not initialized.
		return ! ( $this->is_pro && ( empty( $this->addon_slug ) || $this->is_addon_initialized ) );
	}

	/**
	 * Get a preview option.
	 *
	 * @since 1.9.4
	 *
	 * @param string $option  Option name.
	 * @param array  $field   Field data.
	 * @param array  $args    Additional arguments.
	 * @param bool   $do_echo Echo or return.
	 *
	 * @noinspection ReturnTypeCanBeDeclaredInspection
	 * @noinspection PhpMultipleClassDeclarationsInspection
	 */
	public function field_preview_option( $option, $field, $args = [], $do_echo = true ) {

		// Hide remaining elements, prevent incompatible addon field elements from being displayed.
		if ( $option === 'hide-remaining' && ! empty( $this->is_disabled_field ) ) {
			echo '<div class="wpforms-field-hide-remaining"></div>';

			return;
		}

		parent::field_preview_option( $option, $field, $args, $do_echo );
	}

	/**
	 * Get the Pro field preview badge.
	 *
	 * @since 1.9.4
	 */
	private function get_field_preview_badge(): string {

		if ( empty( $this->is_disabled_field ) ) {
			return '';
		}

		$action = $this->addon_edu_data['action'] ?? '';

		if ( $action === 'incompatible' ) {
			return Helpers::get_badge( esc_html__( 'Update required', 'wpforms-lite' ) , 'lg', 'inline', 'red' );
		}

		// If it's an addon field in Pro AND the addon is not initialized, show the ADDON badge.
		if ( in_array( $action, [ 'install' ,'activate' ], true ) ) {
			return Helpers::get_badge( 'Addon', 'lg', 'inline', 'orange' );
		}

		return Helpers::get_badge( 'Pro', 'lg', 'inline', 'green' );
	}

	/**
	 * Get the addon educational data of the field.
	 *
	 * @since 1.9.4
	 *
	 * @return array
	 */
	private function get_field_addon_edu_data(): array {

		if ( empty( $this->addon_slug ) || ! empty( $this->is_addon_initialized ) || ! is_admin() ) {
			return [];
		}

		$addons = Helpers::get_edu_addons();

		return $addons[ 'wpforms-' . $this->addon_slug ] ?? [];
	}

	/**
	 * Filter frontend field data to prevent rendering Pro fields in Lite.
	 *
	 * @since 1.9.4
	 *
	 * @param array|mixed $field     Field data.
	 * @param array       $form_data Form data.
	 *
	 * @return array
	 * @noinspection PhpMissingParamTypeInspection
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function filter_frontend_field_data( $field, $form_data ): array { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed

		$field = (array) $field;
		$type  = $field['type'] ?? '';

		// If it's not a Pro field or the field type doesn't match, return the field data as is.
		if ( empty( $this->is_pro_field ) || $type !== $this->type ) {
			return $field;
		}

		// If it's a Pro field in Lite OR,
		// the addon is not initialized,
		// return an empty array to prevent rendering.
		if ( ! empty( $this->is_disabled_field ) ) {
			return [];
		}

		return $field;
	}

	/**
	 * Disallow the field preview "Duplicate" button.
	 *
	 * @since 1.9.4
	 *
	 * @param bool|mixed $display Display switch.
	 * @param array      $field   Field settings.
	 *
	 * @return bool
	 * @noinspection PhpMissingParamTypeInspection
	 */
	public function filter_field_preview_display_duplicate_button( $display, $field ): bool {

		if ( $field['type'] !== $this->type || empty( $this->is_disabled_field ) ) {
			return (bool) $display;
		}

		return false;
	}

	/**
	 * Add a class to the field preview container.
	 *
	 * @since 1.9.4
	 *
	 * @param string|mixed $css_class CSS class.
	 * @param array        $field     Field settings.
	 *
	 * @return string
	 * @noinspection PhpMissingParamTypeInspection
	 */
	public function filter_field_preview_class( $css_class, $field ): string {

		$css_class = (string) $css_class;

		if ( $field['type'] !== $this->type || empty( $this->is_disabled_field ) ) {
			return $css_class;
		}

		return trim( $css_class . ' wpforms-field-is-pro' );
	}

	/**
	 * Filter entry save data.
	 *
	 * @since 1.9.5
	 *
	 * @param array|mixed $fields    Entry fields data.
	 * @param array       $entry     Entry data.
	 * @param array       $form_data Form data.
	 *
	 * @return array
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function filter_entry_save_data( $fields, array $entry, array $form_data ): array { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed

		$fields = (array) $fields;

		// If it's not a disabled Pro field, return the fields as is.
		if ( empty( $this->is_disabled_field ) ) {
			return $fields;
		}

		// Remove disabled Pro fields from the entry fields.
		foreach ( $fields as $field_id => $field ) {
			if ( isset( $field['type'] ) && $field['type'] === $this->type ) {
				unset( $fields[ $field_id ] );
			}
		}

		return $fields;
	}

	/**
	 * Filter forbidden columns on the Form Entries page.
	 *
	 * @since 1.9.5
	 *
	 * @param array|mixed $forbidden_fields Entry fields data.
	 * @param int|string  $form_id          Form ID.
	 *
	 * @return array
	 * @noinspection PhpUnusedParameterInspection
	 * @noinspection PhpUnusedLocalVariableInspection
	 */
	public function filter_field_columns_forbidden_fields( $forbidden_fields, $form_id ): array {

		$forbidden_fields = (array) $forbidden_fields;

		if ( empty( $this->is_disabled_field ) ) {
			return $forbidden_fields;
		}

		$form_data = $this->get_form_data( (int) $form_id );

		if ( ! $form_data ) {
			return $forbidden_fields;
		}

		$fields = $form_data['fields'] ?? [];

		foreach ( $fields as $field_id => $field ) {
			if ( isset( $field['type'] ) && $field['type'] === $this->type ) {
				$forbidden_fields[] = $field['type'];
			}
		}

		return $forbidden_fields;
	}

	/**
	 * Get form data by form ID and cache it.
	 *
	 * @since 1.9.5
	 *
	 * @param int $form_id Form ID.
	 *
	 * @return array
	 */
	private function get_form_data( int $form_id ): array {

		$form_obj = wpforms()->obj( 'form' );

		if ( ! $form_obj ) {
			return [];
		}

		// Cache the form data into static variable.
		static $cached_form_data = [];

		if ( isset( $cached_form_data[ $form_id ] ) ) {
			return $cached_form_data[ $form_id ];
		}

		$cached_form_data[ $form_id ] = (array) $form_obj->get( $form_id, [ 'content_only' => true ] );

		return $cached_form_data[ $form_id ];
	}

	/**
	 * Filter entries export configuration.
	 *
	 * @since 1.9.5
	 *
	 * @param array $config Export configuration.
	 *
	 * @return array
	 * @noinspection PhpMissingParamTypeInspection
	 */
	public function filter_entries_export_configuration( $config ): array {

		$config = (array) $config;

		// If it's not a disabled Pro field, return the config as is.
		if ( empty( $this->is_disabled_field ) ) {
			return $config;
		}

		if ( empty( $this->type ) ) {
			return $config;
		}

		$config['disallowed_fields'] = ! empty( $config['disallowed_fields'] ) ? (array) $config['disallowed_fields'] : [];

		// Add the disabled Pro field type to `disallowed_fields` if not already there.
		if ( ! in_array( $this->type, $config['disallowed_fields'], true ) ) {
			$config['disallowed_fields'][] = $this->type;
		}

		return $config;
	}

	/**
	 * Filter if the field is displayable in the Entry Edit page.
	 *
	 * @since 1.9.5
	 *
	 * @param bool|mixed $displayable Whether the field is displayable.
	 * @param array      $field       Field data.
	 * @param array      $form_data   Form data.
	 *
	 * @return bool
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function filter_is_field_displayable( $displayable, array $field, array $form_data ): bool { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed

		if ( ! $this->is_disabled_field ) {
			return (bool) $displayable;
		}

		return false;
	}
}
