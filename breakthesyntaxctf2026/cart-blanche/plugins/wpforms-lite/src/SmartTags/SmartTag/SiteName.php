<?php

namespace WPForms\SmartTags\SmartTag;

/**
 * Class SiteName.
 *
 * @since 1.8.3
 */
class SiteName extends SmartTag {

	/**
	 * Get smart tag value.
	 *
	 * @since 1.8.3
	 *
	 * @param array  $form_data Form data.
	 * @param array  $fields    List of fields.
	 * @param string $entry_id  Entry ID.
	 *
	 * @return string
	 */
	public function get_value( $form_data, $fields = [], $entry_id = '' ) {

		return wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
	}
}
