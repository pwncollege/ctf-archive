<?php

namespace WPForms\SmartTags\SmartTag;

/**
 * Class UrlReferer.
 *
 * @since 1.6.7
 */
class UrlReferer extends SmartTag {

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

		$referer = $this->get_meta( $entry_id, 'url_referer' );

		if ( ! empty( $referer ) ) {
			return $this->context === 'confirmation_redirect'
				? urldecode( $referer )
				: esc_url( urldecode( $referer ) );
		}

		$process = wpforms()->obj( 'process' );

		if ( $process && ! empty( $process->form_data['entry_meta']['url_referer'] ) ) {
			return esc_url( urldecode( $process->form_data['entry_meta']['url_referer'] ) );
		}

		if ( wp_doing_ajax() ) {
			return '';
		}

		$referer = urldecode( (string) wp_get_raw_referer() );

		return esc_url( $referer );
	}
}
