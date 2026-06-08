<?php

namespace WPForms\Forms;

/**
 * Class Anti-Spam v3.
 *
 * This class is used for modern Anti-Spam approach.
 *
 * @since 1.9.0
 */
class AntiSpam {

	/**
	 * Field ID to insert the honeypot field before.
	 *
	 * @since 1.9.0
	 *
	 * @var int
	 */
	private $insert_before_field_id = 1;

	/**
	 * Array with IDs of all honeypot fields on the current page grouped by form IDs ([form_id => field_id]).
	 *
	 * @since 1.9.0.3
	 *
	 * @var array
	 */
	private $forms_data = [];

	/**
	 * Initialise the actions for the modern Anti-Spam.
	 *
	 * @since 1.9.0
	 */
	public function init() {

		$this->hooks();
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.9.0
	 */
	private function hooks() {

		// Frontend hooks.
		add_filter( 'wpforms_frontend_strings', [ $this, 'add_frontend_strings' ] );
		add_filter( 'wpforms_frontend_fields_base_level', [ $this, 'get_random_field' ], 20 );
		add_action( 'wpforms_display_field_before', [ $this, 'maybe_insert_honeypot_field' ], 1, 2 );
		add_action( 'wpforms_display_fields_after', [ $this, 'maybe_insert_honeypot_init_js' ] );

		// Builder hooks.
		add_filter( 'wpforms_builder_panel_settings_init_form_data', [ $this, 'init_builder_settings_form_data' ] );
		add_filter( 'wpforms_admin_builder_templates_apply_to_new_form_modify_data', [ $this, 'update_template_form_data' ] );
		add_filter( 'wpforms_admin_builder_templates_apply_to_existing_form_modify_data', [ $this, 'update_template_form_data' ] );
		add_filter( 'wpforms_templates_class_base_template_modify_data', [ $this, 'update_template_form_data' ] );
		add_filter( 'wpforms_templates_class_base_template_replace_modify_data', [ $this, 'update_template_form_data' ] );
		add_filter( 'wpforms_form_handler_convert_form_data', [ $this, 'update_template_form_data' ] );
	}

	/**
	 * Store a random field id to insert a honeypot field later.
	 *
	 * @since 1.9.0
	 *
	 * @param array|mixed $fields_data Form fields data.
	 *
	 * @return array|mixed Form fields data.
	 */
	public function get_random_field( $fields_data ) {

		if ( ! is_array( $fields_data ) ) {
			return $fields_data;
		}

		$random_field_id = array_rand( $fields_data );

		if ( ! empty( $random_field_id ) ) {
			$this->insert_before_field_id = $random_field_id;
		}

		return $fields_data;
	}

	/**
	 * Insert honeypot field before a random field.
	 *
	 * @since 1.9.0
	 *
	 * @param array $field     Field.
	 * @param array $form_data Form data.
	 */
	public function maybe_insert_honeypot_field( array $field, array $form_data ) {

		if (
			$this->insert_before_field_id !== (int) $field['id'] ||
			! $this->is_honeypot_enabled( $form_data )
		) {
			return;
		}

		$honeypot_field_id            = $this->get_honeypot_field_id( $form_data );
		$form_id                      = (int) $form_data['id'];
		$label                        = $this->get_honeypot_label( $form_data );
		$id_attr                      = sprintf( 'wpforms-%1$s-field_%2$s', $form_id, $honeypot_field_id );
		$is_amp                       = wpforms_is_amp();
		$this->forms_data[ $form_id ] = $honeypot_field_id;

		if ( $is_amp ) {
			echo '<amp-layout layout="nodisplay">';
		}

		?>
		<div id="<?php echo esc_attr( $id_attr ); ?>-container"
			class="wpforms-field wpforms-field-text"
			data-field-type="text"
			data-field-id="<?php echo esc_attr( $honeypot_field_id ); ?>"
			>
			<label class="wpforms-field-label" for="<?php echo esc_attr( $id_attr ); ?>" ><?php echo esc_html( $label ); ?></label>
			<input type="text" id="<?php echo esc_attr( $id_attr ); ?>" class="wpforms-field-medium" name="wpforms[fields][<?php echo esc_attr( $honeypot_field_id ); ?>]" >
		</div>
		<?php

		if ( $is_amp ) {
			echo '</amp-layout>';
		}
	}

	/**
	 * Insert the inline styles.
	 *
	 * @since 1.9.0
	 *
	 * @param array $form_data Form data.
	 *
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function maybe_insert_honeypot_init_js( array $form_data ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found

		if (
			! $this->forms_data ||
			wpforms_is_amp()
		) {
			return;
		}

		$ids = [];

		foreach ( $this->forms_data as $form_id => $honeypot_field_id ) {
			$ids[] = sprintf(
				'#wpforms-%1$d-field_%2$d-container',
				$form_id,
				$honeypot_field_id
			);
		}

		if ( ! $ids ) {
			return;
		}

		$styles = sprintf(
			'%1$s { position: absolute !important; overflow: hidden !important; display: inline !important; height: 1px !important; width: 1px !important; z-index: -1000 !important; padding: 0 !important; } %1$s input { visibility: hidden; } #wpforms-conversational-form-page %1$s label { counter-increment: none; }',
			esc_attr( implode( ',', $ids ) )
		);

		// There must be no empty lines inside the script. Otherwise, wpautop adds <p> tags which break script execution.
		printf(
			"<script>
				( function() {
					const style = document.createElement( 'style' );
					style.appendChild( document.createTextNode( '%s' ) );
					document.head.appendChild( style );
					document.currentScript?.remove();
				} )();
			</script>",
			esc_js( $styles )
		);
	}

	/**
	 * Get honeypot field label.
	 *
	 * @since 1.9.0
	 *
	 * @param array $form_data Form data.
	 */
	private function get_honeypot_label( array $form_data ): string {

		$labels = [];

		foreach ( $form_data['fields'] ?? [] as $field ) {
			if ( ! empty( $field['label'] ) ) {
				$labels[] = $field['label'];
			}
		}

		$words       = explode( ' ', implode( ' ', $labels ) );
		$count_words = count( $words );
		$label_keys  = (array) array_rand( $words, min( $count_words, 3 ) );

		shuffle( $label_keys );

		$label_words = array_map(
			static function ( $key ) use ( $words ) {

				return $words[ $key ];
			},
			$label_keys
		);

		return implode( ' ', $label_words );
	}

	/**
	 * Add strings to the frontend.
	 *
	 * @since 1.9.0
	 *
	 * @param array|mixed $strings Frontend strings.
	 *
	 * @return array Frontend strings.
	 */
	public function add_frontend_strings( $strings ): array {

		$strings = (array) $strings;

		// Store the honeypot field ID for validation and adding inline styles.
		$strings['hn_data'] = $this->forms_data;

		return $strings;
	}

	/**
	 * Validate whether the modern Anti-Spam is enabled.
	 *
	 * @since 1.9.0
	 *
	 * @param array $form_data Form data.
	 * @param array $fields    Fields.
	 * @param array $entry     Form submission raw data ($_POST).
	 *
	 * @return bool True if the entry is valid, false otherwise.
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function validate( array $form_data, array $fields, array &$entry ): bool {

		// Bail out if the modern Anti-Spam is not enabled.
		if ( ! $this->is_honeypot_enabled( $form_data ) ) {
			return true;
		}

		$honeypot_fields = array_diff_key( $entry['fields'], $form_data['fields'] );
		$is_valid        = true;

		// Compatibility with the WPML plugin (WPFML addon).
		// In case the form contains an Entry Preview field, they add an extra field with ID 0 to the entry.
		if (
			isset( $entry['fields'][0] ) &&
			defined( 'WPML_WP_FORMS_VERSION' ) &&
			wpforms_has_field_type( 'entry-preview', $form_data )
		) {
			unset( $honeypot_fields[0] );
		}

		foreach ( $honeypot_fields as $key => $honeypot_field ) {
			// Remove the honeypot field from the entry.
			unset( $entry['fields'][ $key ] );

			// If the honeypot field is not empty, the entry is invalid.
			if ( ! empty( $honeypot_field ) ) {
				$is_valid = false;
			}
		}

		return $is_valid;
	}

	/**
	 * Check if the modern Anti-Spam is enabled.
	 *
	 * @since 1.9.0
	 *
	 * @param array $form_data Form data.
	 *
	 * @return bool True if the modern Anti-Spam is enabled, false otherwise.
	 */
	private function is_honeypot_enabled( array $form_data ): bool {

		static $is_enabled;

		if ( isset( $is_enabled ) ) {
			return $is_enabled;
		}

		/**
		 * Filters whether the modern Anti-Spam is enabled.
		 *
		 * @since 1.9.0
		 *
		 * @param bool $is_enabled True if the modern Anti-Spam is enabled, false otherwise.
		 */
		$is_enabled = (bool) apply_filters( 'wpforms_forms_anti_spam_v3_is_honeypot_enabled', ! empty( $form_data['settings']['antispam_v3'] ) );

		return $is_enabled;
	}

	/**
	 * Get the honeypot field ID.
	 *
	 * @since 1.9.0
	 *
	 * @param array $form_data Form data.
	 *
	 * @return int Honeypot field ID.
	 */
	private function get_honeypot_field_id( array $form_data ): int {

		$max_key = max( array_keys( $form_data['fields'] ) );

		// Find the first available field ID.
		for ( $i = 1; $i <= $max_key; $i++ ) {
			if ( ! isset( $form_data['fields'][ $i ] ) ) {
				return $i;
			}
		}

		// If no available field ID found, use the max ID + 1.
		return $max_key + 1;
	}

	/**
	 * Update the form data on the builder settings panel.
	 *
	 * @since 1.9.0
	 *
	 * @param array|bool $form_data Form data.
	 *
	 * @return array|bool
	 */
	public function init_builder_settings_form_data( $form_data ) {

		if ( ! $form_data ) {
			return $form_data;
		}

		// Update default time limit duration for the existing form.
		if ( empty( $form_data['settings']['anti_spam']['time_limit']['enable'] ) ) {
			$form_data['settings']['anti_spam']['time_limit']['duration'] = '2';
		}

		return $form_data;
	}

	/**
	 * Update the template form data. Set the modern Anti-Spam setting.
	 *
	 * @since 1.9.0
	 *
	 * @param array|mixed $form_data Form data.
	 *
	 * @return array
	 */
	public function update_template_form_data( $form_data ): array {

		$form_data = (array) $form_data;

		// Unset the old Anti-Spam setting.
		unset( $form_data['settings']['antispam'] );

		// Enable the modern Anti-Spam setting.
		$form_data['settings']['antispam_v3'] = $form_data['settings']['antispam_v3'] ?? '1';
		$form_data['settings']['anti_spam']   = $form_data['settings']['anti_spam'] ?? [];

		// Enable the time limit setting.
		$form_data['settings']['anti_spam']['time_limit'] = [
			'enable'   => '1',
			'duration' => '2',
		];

		return $form_data;
	}
}
