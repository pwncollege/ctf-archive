<?php

namespace WPForms\SmartTags\SmartTag;

/**
 * Class QueryVar.
 *
 * @since 1.6.7
 */
class QueryVar extends SmartTag {

	/**
	 * Get smart tag value.
	 *
	 * @since 1.6.7
	 * @since 1.7.6 Added support for ajax submissions.
	 *
	 * @param array  $form_data Form data.
	 * @param array  $fields    List of fields.
	 * @param string $entry_id  Entry ID.
	 *
	 * @return string
	 */
	public function get_value( $form_data, $fields = [], $entry_id = '' ) {

		$attributes = $this->get_attributes();

		if ( empty( $attributes['key'] ) ) {
			return '';
		}

		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		if ( ! empty( $_GET[ $attributes['key'] ] ) ) {
			return esc_html( sanitize_text_field( wp_unslash( $_GET[ $attributes['key'] ] ) ) );
		}
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		$page_url = $this->get_page_url( $entry_id );

		if ( empty( $page_url ) ) {
			return '';
		}

		$query = wp_parse_url( esc_url_raw( wp_unslash( $page_url ) ), PHP_URL_QUERY );
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		if ( ! $query ) {
			return '';
		}

		parse_str( $query, $results );

		return ! empty( $results[ $attributes['key'] ] ) ? esc_html( sanitize_text_field( wp_unslash( $results[ $attributes['key'] ] ) ) ) : '';
	}

	/**
	 * Get page URL.
	 *
	 * @since 1.9.4
	 *
	 * @param string $entry_id Entry ID.
	 *
	 * @return string
	 */
	private function get_page_url( $entry_id ): string {

		// phpcs:disable WordPress.Security.NonceVerification.Missing
		if ( ! empty( $_POST['page_url'] ) ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			return (string) $_POST['page_url']; // It sanitized in the get_value method.
		}
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		return $this->get_meta( $entry_id, 'page_url' );
	}
}
