<?php

namespace WPForms\SmartTags\SmartTag;

/**
 * Class AuthorEmail.
 *
 * @since 1.6.7
 */
class AuthorEmail extends SmartTag {

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
	public function get_value( $form_data, $fields = [], $entry_id = '' ): string {

		$author_email = $this->get_author_meta( $entry_id, 'user_email' );

		if ( empty( $author_email ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			$page_id      = isset( $_POST['page_id'] ) ? absint( $_POST['page_id'] ) : 0;
			$author_id    = $page_id ? (int) get_post_field( 'post_author', $page_id ) : get_current_user_id();
			$author_email = get_the_author_meta( 'user_email', $author_id );
		}

		$author_email = $this->has_cap() ? $author_email : '';

		return sanitize_email( $author_email );
	}
}
