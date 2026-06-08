<?php

namespace WPForms\SmartTags\SmartTag;

/**
 * Class AuthorId.
 *
 * @since 1.6.7
 */
class AuthorId extends SmartTag {

	/**
	 * Get smart tag value.
	 *
	 * @since 1.6.7
	 *
	 * @param array  $form_data Form data.
	 * @param array  $fields    List of fields.
	 * @param string $entry_id  Entry ID.
	 *
	 * @return int|string
	 */
	public function get_value( $form_data, $fields = [], $entry_id = '' ) {

		$author_id = $this->get_author_meta( $entry_id, 'ID' );

		if ( empty( $author_id ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			$page_id   = isset( $_POST['page_id'] ) ? absint( $_POST['page_id'] ) : 0;
			$author_id = $page_id ? (int) get_post_field( 'post_author', $page_id ) : get_current_user_id();
		}

		$author_id = $this->has_cap() ? $author_id : '';

		return $author_id ? absint( $author_id ) : '';
	}
}
