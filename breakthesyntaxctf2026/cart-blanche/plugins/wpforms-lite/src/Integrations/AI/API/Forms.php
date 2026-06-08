<?php

namespace WPForms\Integrations\AI\API;

use WPForms\Integrations\AI\Admin\Ajax\Forms as FormsAjax;
use WPForms\Integrations\AI\Helpers;
use WPForms\Forms\Fields\Pagebreak\Field as PagebreakField;

/**
 * Form API class.
 *
 * @since 1.9.2
 */
class Forms extends API {

	/**
	 * API endpoint.
	 *
	 * @since 1.9.2
	 */
	private const ENDPOINT = '/ai-forms';

	/**
	 * Get form from the API.
	 *
	 * @since 1.9.2
	 *
	 * @param string $prompt     Prompt to get the form.
	 * @param string $session_id Session ID.
	 *
	 * @return array
	 * @noinspection PhpUndefinedConstantInspection
	 */
	public function form( string $prompt, string $session_id = '' ): array {

		$args = [
			'userPrompt' => $this->prepare_prompt( $prompt ),
			'limit'      => $this->get_limit(),
		];

		if ( ! empty( $session_id ) ) {
			$args['sessionId'] = $session_id;
		}

		// Flag requests from Lite plugin.
		$args['lite'] = ! wpforms()->is_pro();

		// Add available addons to the request arguments.
		$args['addons'] = $this->get_available_addons();

		// Add GDPR setting to the request arguments.
		$args['gdpr'] = wpforms_setting( 'gdpr' );

		// Add a Page break field support.
		$args['pagebreak'] = true;

		// Add prompt debug info support.
		$args['debug'] = defined( 'WPFORMS_AI_DEBUG' ) && WPFORMS_AI_DEBUG;

		$response = $this->request->post( self::ENDPOINT, $args );

		if ( $response->has_errors() ) {
			$error_data = $response->get_error_data();

			Helpers::log_error( $response->get_log_message( $error_data ), self::ENDPOINT, $args );

			return $error_data;
		}

		return $this->normalize_form_data( $response->get_body() );
	}

	/**
     * Get available addons.
     *
     * @since 1.9.2
     *
     * @return array
     */
    private function get_available_addons(): array {

		// Since starting from 1.9.4, we display unavailable addon fields in Lite and all Pro licenses,
	    // we need to return all required addons to let AI generate addon fields.
	    return FormsAjax::FORM_GENERATOR_REQUIRED_ADDONS;
    }

	/**
	 * Normalize form data.
	 *
	 * @since 1.9.2
	 *
	 * @param array $form_data Form data.
	 *
	 * @return array
	 */
	private function normalize_form_data( array $form_data ): array {

		// Recursively normalize form data.
		$form_data = $this->normalize_form_data_recursive( $form_data );

		// Fix fields data.
		$form_data = $this->fix_fields_data( $form_data );

		// Notifications and confirmations arrays should be indexed from 1.
		if ( ! empty( $form_data['settings']['notifications'] ) ) {
			$form_data['settings']['notifications'] = array_combine(
				range( 1, count( $form_data['settings']['notifications'] ) ),
				array_values( $form_data['settings']['notifications'] )
			);
		}

		if ( ! empty( $form_data['settings']['confirmations'] ) ) {
			$form_data['settings']['confirmations'] = array_combine(
				range( 1, count( $form_data['settings']['confirmations'] ) ),
				array_values( $form_data['settings']['confirmations'] )
			);
		}

		$form_data['form_title']             = empty( $form_data['form_title'] ) ? esc_html__( 'Untitled Form', 'wpforms-lite' ) : $form_data['form_title'];
		$form_data['settings']['form_title'] = $form_data['form_title'];

		return $form_data;
	}

	/**
	 * Normalize form data recursive.
	 *
	 * @since 1.9.2
	 *
	 * @param array $form_data Form data.
	 *
	 * @return array
	 */
	private function normalize_form_data_recursive( array $form_data ): array {

		foreach ( $form_data as $key => $value ) {
			if ( is_array( $value ) ) {
				$form_data[ $key ] = $this->normalize_form_data_recursive( $value );
			}

			// Convert `false` and `true` values to '0' and '1'.
			$form_data[ $key ] = $form_data[ $key ] === false ? '0' : $form_data[ $key ];
			$form_data[ $key ] = $form_data[ $key ] === true ? '1' : $form_data[ $key ];

			// Remove null values.
			if ( $form_data[ $key ] === null ) {
				unset( $form_data[ $key ] );
			}
		}

		return $form_data;
	}

	/**
	 * Fix fields' data.
	 *
	 * @since 1.9.2
	 *
	 * @param array $form_data Form data.
	 *
	 * @return array
	 */
	private function fix_fields_data( array $form_data ): array {

		$updated_fields_data = [];
		$page_breaks         = [];

		// Fix array keys. The key should be identical to `id`.
		foreach ( $form_data['fields'] as $field_data ) {
			$updated_fields_data[ (string) $field_data['id'] ] = $field_data;
		}

		$form_data['fields'] = $updated_fields_data;

		// Fix choice values and choices array indexes.
		foreach ( $form_data['fields'] as $id => $field_data ) {
			$field_data                 = $this->fix_field_defaults( $field_data );
			$form_data['fields'][ $id ] = $this->fix_choices( $field_data );
		}

		// Fix conditional logic rules and detect page breaks.
		foreach ( $form_data['fields'] as $id => $field_data ) {
			$form_data['fields'][ $id ] = $this->fix_field_cl( $field_data, $form_data );

			if ( $field_data['type'] === 'pagebreak' ) {
				$page_breaks[] = $id;
			}
		}

		// Fix page breaks.
		if ( ! empty( $page_breaks ) ) {
			$form_data = $this->fix_page_breaks( $form_data, $page_breaks );
		}

		return $form_data;
	}

	/**
	 * Fix field's conditional logic rules.
	 *
	 * @since 1.9.2
	 *
	 * @param array $field     Field data.
	 * @param array $form_data Form data.
	 *
	 * @return array
	 */
	private function fix_field_cl( array $field, array $form_data ): array {

		if ( empty( $field['conditionals'] ) || empty( $field['conditional_logic'] ) ) {
			return $field;
		}

		// Loop groups.
		foreach ( $field['conditionals'] as $group_key => $group ) {

			// Loop rules.
			foreach ( $group as $rule_key => $rule ) {
				$choices = $form_data['fields'][ $rule['field'] ]['choices'] ?? [];

				// We only need to update rules for choice-based fields.
				if ( empty( $choices ) ) {
					continue;
				}

				// AI uses choice value, but we should use the index of the choice in the `choices` array.
				$field['conditionals'][ $group_key ][ $rule_key ]['value'] = $this->get_choice_index( $choices, $rule['value'] );

				// Continue if the operator is supported by the choice-based field.
				if ( in_array( $rule['operator'], [ '==', '!=', 'e', '!e' ], true ) ) {
					continue;
				}

				// Fix `operator` value for choice-based fields.
				$rule['operator'] = in_array( $rule['operator'], [ 'c', '^', '>', '<' ], true ) ? '==' : $rule['operator'];
				$rule['operator'] = in_array( $rule['operator'], [ '!c', '~' ], true ) ? '!=' : $rule['operator'];

				$field['conditionals'][ $group_key ][ $rule_key ]['operator'] = $rule['operator'];
			}
		}

		return $field;
	}

	/**
	 * Find choice index in the `choices` array.
	 *
	 * @since 1.9.2
	 *
	 * @param array  $choices Choices data.
	 * @param string $value   Value to find in choices.
	 *
	 * @return string|null
	 */
	private function get_choice_index( array $choices, string $value ) {

		$index = array_search( $value, array_column( $choices, 'value' ), true );

		if ( $index === false ) {
			$index = array_search( $value, array_column( $choices, 'label' ), true );
		}

		$choices_keys = array_keys( $choices );

		return $index === false ? null : $choices_keys[ $index ];
	}

	/**
	 * Add missing default attributes to the field.
	 *
	 * @since 1.9.4
	 *
	 * @param array $field Field data.
	 *
	 * @return array
	 */
	private function fix_field_defaults( array $field ): array {

		/**
		 * Allow the default field settings to be filtered.
		 *
		 * @since 1.0.8
		 *
		 * @param array $field Default field settings.
		 */
		$field = (array) apply_filters( 'wpforms_field_new_default', $field ); // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName

		// Set the defaults for certain fields.
		if ( $field['type'] === 'content' ) {
			$field['label'] = empty( $field['label'] ) ? esc_html__( 'Content', 'wpforms-lite' ) : $field['label'];
		}

		if ( $field['type'] === 'richtext' ) {
			$field['default_value'] = '';
		}

		return $field;
	}

	/**
	 * Fix choices.
	 *
	 * Remove unnecessary values from choices.
	 *
	 * @since 1.9.2
	 *
	 * @param array $field Field data.
	 *
	 * @return array
	 */
	private function fix_choices( array $field ): array {

		if ( empty( $field['choices'] ) ) {
			return $field;
		}

		// Remove values from choices for non-payment fields.
		if ( ! in_array( $field['type'], [ 'payment-multiple', 'payment-checkbox', 'payment-select' ], true ) ) {
			// Remove values from choices.
			foreach ( $field['choices'] as $i => $choice ) {
				$field['choices'][ $i ]['value'] = '';
			}
		}

		$updated_choices = [];

		// Update array keys to start from 1.
		foreach ( $field['choices'] as $i => $choice ) {
			$updated_choices[ $i + 1 ] = $choice;
		}

		$field['choices'] = $updated_choices;

		return $field;
	}

	/**
	 * Fix page breaks.
	 *
	 * Add top and bottom page breaks to the form, set `nav_align` for all page breaks.
	 *
	 * @since 1.9.3
	 *
	 * @param array $form_data   Form data.
	 * @param array $page_breaks Page break IDs.
	 *
	 * @return array
	 */
	private function fix_page_breaks( array $form_data, array $page_breaks ): array { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		reset( $form_data['fields'] );

		$max_field_id = max( array_keys( $form_data['fields'] ) );

		// Update or add the top page break.
		$first_field_id = key( $form_data['fields'] );

		// If the first field is a page break, use its ID, otherwise create a new one.
		if ( $form_data['fields'][ $first_field_id ]['type'] === 'pagebreak_top' ) {
			$top_id = $first_field_id;
		} else {
			$top_id              = '0';
			$form_data['fields'] = [ $top_id => [] ] + $form_data['fields'];
		}

		$form_data['fields'][ $top_id ] = [
			'type'            => 'pagebreak',
			'id'              => $top_id,
			'position'        => 'top',
			'indicator'       => 'progress',
			'indicator_color' => PagebreakField::get_default_indicator_color(),
			'title'           => $form_data['fields'][ $top_id ]['title'] ?? '',
			'nav_align'       => $form_data['fields'][ $top_id ]['nav_align'] ?? 'left',
		];

		// Remove the Previous button from the first normal pagebreak.
		$form_data['fields'][ $page_breaks[0] ]['prev']        = '';
		$form_data['fields'][ $page_breaks[0] ]['prev_toggle'] = '';

		end( $form_data['fields'] );

		// Update or add the bottom page break.
		// If the last field is a page break, use its ID, otherwise create a new one.
		$last_field_id                     = key( $form_data['fields'] );
		$last_field                        = $form_data['fields'][ key( $form_data['fields'] ) ];
		$bottom_id                         = $last_field['type'] === 'pagebreak_bottom' ? $last_field_id : (string) ++$max_field_id;
		$form_data['fields'][ $bottom_id ] = [
			'type'        => 'pagebreak',
			'id'          => $bottom_id,
			'position'    => 'bottom',
			'title'       => '',
			'prev'        => $form_data['fields'][ $bottom_id ]['prev'] ?? '',
			'prev_toggle' => $form_data['fields'][ $bottom_id ]['prev_toggle'] ?? '',
		];

		// Remove the Previous button from the bottom pagebreak.
		if ( empty( $form_data['fields'][ $bottom_id ]['prev_toggle'] ) ) {
			unset( $form_data['fields'][ $bottom_id ]['prev'], $form_data['fields'][ $bottom_id ]['prev_toggle'] );
		}

		// Prevent wrong pagebreaks.
		foreach ( $form_data['fields'] as $d => $field ) {
			$field['type'] = $field['type'] === 'pagebreak_top' ? 'pagebreak' : $field['type'];
			$field['type'] = $field['type'] === 'pagebreak_bottom' ? 'pagebreak' : $field['type'];

			$form_data['fields'][ $d ] = $field;
		}

		return $form_data;
	}
}
