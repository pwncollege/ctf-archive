<?php

namespace WPForms\Admin\Builder\Ajax;

/**
 * Save the form data.
 *
 * @since 1.9.4
 */
class SaveForm {

	/**
	 * The form fields processing while saving the form.
	 *
	 * @since 1.9.4
	 *
	 * @param array $fields    Form fields data.
	 * @param array $form_data Form data.
	 *
	 * @return array
	 */
	public function process_fields( array $fields, array $form_data ): array {

		$form_obj = wpforms()->obj( 'form' );

		if ( ! $form_obj || empty( $fields ) || empty( $form_data['id'] ) ) {
			return $fields;
		}

		$saved_form_data = $form_obj->get( $form_data['id'], [ 'content_only' => true ] );

		foreach ( $fields as $field_id => $field_data ) {
			if ( empty( $field_data['type'] ) ) {
				continue;
			}

			/**
			 * Filter field settings before saving the form.
			 *
			 * @since 1.9.4
			 *
			 * @param array $field_data      Field data.
			 * @param array $form_data       Forms data.
			 * @param array $saved_form_data Saved form data.
			 */
			$fields[ $field_id ] = apply_filters( "wpforms_admin_builder_ajax_save_form_field_{$field_data['type']}", $field_data, $form_data, $saved_form_data );
		}

		return $fields;
	}
}
