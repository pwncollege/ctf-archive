<?php

namespace WPForms\Helpers;

/**
 * Form helpers.
 *
 * @since 1.9.4
 */
class Form {

	/**
	 * Get form pro-fields array.
	 *
	 * @since 1.9.4
	 *
	 * @param array|mixed $form_data Form data.
	 *
	 * @return array Pro fields array.
	 */
	public static function get_form_pro_fields( $form_data ): array {

		$fields     = $form_data['fields'] ?? [];
		$pro_fields = [];

		foreach ( $fields as $field_data ) {

			/**
			 * Filter form pro fields array.
			 *
			 * @since 1.9.4
			 *
			 * @param array $pro_fields Pro-fields data.
			 * @param array $field_data Field data.
			 */
			$pro_fields = apply_filters( 'wpforms_helpers_form_pro_fields', $pro_fields, $field_data );
		}

		return $pro_fields;
	}

	/**
	 * Get form addons educational data.
	 *
	 * @since 1.9.4
	 *
	 * @param array|mixed $form_data Form data.
	 *
	 * @return array The form addons educational data.
	 */
	public static function get_form_addons_edu_data( $form_data ): array {

		$fields          = $form_data['fields'] ?? [];
		$addons_edu_data = [];

		foreach ( $fields as $field_data ) {
			/**
			 * Filter the form addons educational data.
			 *
			 * @since 1.9.4
			 *
			 * @param array $addons_edu_data The form addons educational data.
			 * @param array $field_data      Field data.
			 */
			$addons_edu_data = apply_filters( 'wpforms_helpers_form_addons_edu_data', $addons_edu_data, $field_data );
		}

		return $addons_edu_data;
	}
}
