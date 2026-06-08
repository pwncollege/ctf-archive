<?php

namespace WPForms\Integrations\Divi\Interfaces;

use WP_Post;

/**
 * Interface FormsResolverInterface.
 *
 * Defines methods for resolving and managing WPForms forms in Divi integration.
 *
 * @since 1.9.9
 */
interface FormsResolverInterface {

	/**
	 * Get all available forms.
	 *
	 * @since 1.9.9
	 *
	 * @return array Array of WP_Post objects representing forms.
	 */
	public function get_forms(): array;

	/**
	 * Add a form to the option array.
	 *
	 * @since 1.9.9
	 *
	 * @param array   $options Existing options array.
	 * @param WP_Post $form    Form WP_Post object to add.
	 *
	 * @return array Updated options array with the form added.
	 */
	public function add_form_in_options( array $options, WP_Post $form ): array;

	/**
	 * Get form options for all available forms.
	 *
	 * Retrieves all forms and formats them as an option array.
	 *
	 * @since 1.9.9
	 *
	 * @return array Array of form options.
	 */
	public function get_form_options(): array;
}
