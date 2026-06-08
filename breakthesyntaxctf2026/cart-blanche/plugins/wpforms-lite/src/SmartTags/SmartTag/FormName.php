<?php

namespace WPForms\SmartTags\SmartTag;

/**
 * Class FormName.
 *
 * @since 1.6.7
 */
class FormName extends SmartTag {

	/**
	 * Get smart tag value.
	 *
	 * @since 1.6.7
	 *
	 * @param array  $form_data Form data.
	 * @param array  $fields    List of fields.
	 * @param string $entry_id  Entry ID.
	 *
	 * @return string
	 */
	public function get_value( $form_data, $fields = [], $entry_id = '' ) {

		if ( ! isset( $form_data['settings']['form_title'] ) || $form_data['settings']['form_title'] === '' ) {
			return '';
		}

		return esc_html( wp_strip_all_tags( $form_data['settings']['form_title'] ) );
	}
}
