<?php

namespace WPForms\Integrations\Divi\Traits;

/**
 * Trait FormsResolverTrait.
 *
 * Provides implementation for resolving WPForms forms in Divi integration.
 *
 * @since 1.9.9
 */
trait FormsResolverTrait {

	/**
	 * Get all available forms.
	 *
	 * Retrieves all forms from the database ordered by descending ID.
	 *
	 * @since 1.9.9
	 *
	 * @return array Array of WP_Post objects representing forms, or empty array if a form object is unavailable.
	 */
	public function get_forms(): array {
		// Get all forms for the editor.
		$forms = wpforms()->obj( 'form' ) ? wpforms()->obj( 'form' )->get( '', [ 'order' => 'DESC' ] ) : [];

		// If $forms is false, return an empty array.
		return $forms ? (array) $forms : [];
	}

	/**
	 * Get form options for all available forms.
	 *
	 * Retrieves all forms and formats them as an option array by iterating
	 * through each form and adding it to the options using add_form_in_options().
	 *
	 * @since 1.9.9
	 *
	 * @return array Array of form options.
	 */
	public function get_form_options(): array {

		$forms   = $this->get_forms();
		$options = [];

		foreach ( $forms as $form ) {
			$options = $this->add_form_in_options( $options, $form );
		}

		return $options;
	}
}
