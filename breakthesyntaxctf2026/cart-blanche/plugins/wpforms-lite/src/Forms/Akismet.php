<?php

namespace WPForms\Forms;

use Akismet as AkismetPlugin;

/**
 * Class Akismet.
 *
 * @since 1.7.6
 */
class Akismet {

	/**
	 * Is the Akismet plugin installed?
	 *
	 * @since 1.7.6
	 *
	 * @return bool
	 */
	public static function is_installed(): bool {

		return file_exists( WP_PLUGIN_DIR . '/akismet/akismet.php' );
	}

	/**
	 * Is the Akismet plugin activated?
	 *
	 * @since 1.7.6
	 *
	 * @return bool
	 */
	public static function is_activated(): bool {

		return is_callable( [ 'Akismet', 'get_api_key' ] ) && is_callable( [ 'Akismet', 'http_post' ] );
	}

	/**
	 * Has the Akismet plugin been configured wih a valid API key?
	 *
	 * @since 1.7.6
	 *
	 * @return bool
	 */
	public static function is_configured(): bool {

		// Akismet will only allow an API key to be saved if it is a valid key.
		// We can assume that if there is an API key saved, it is valid.
		return self::is_activated() && ! empty( AkismetPlugin::get_api_key() );
	}

	/**
	 * Get the list of field types that are allowed to be sent to Akismet.
	 *
	 * @since 1.7.6
	 *
	 * @return array List of field types that are allowed to be sent to Akismet
	 */
	private function get_field_type_allowlist(): array {

		$field_type_allowlist = [
			'text',
			'textarea',
			'name',
			'email',
			'phone',
			'address',
			'url',
			'richtext',
		];

		/**
		 * Filters the field types that are allowed to be sent to Akismet.
		 *
		 * @since 1.7.6
		 *
		 * @param array $field_type_allowlist Field types allowed to be sent to Akismet.
		 */
		return (array) apply_filters( 'wpforms_forms_akismet_get_field_type_allowlist', $field_type_allowlist );
	}

	/**
	 * Get the entry data to be sent to Akismet.
	 *
	 * @since 1.7.6
	 *
	 * @param array $fields Field data for the current form.
	 * @param array $entry  Entry data.
	 *
	 * @return array $entry_data Entry data to be sent to Akismet.
	 */
	private function get_entry_data( array $fields, array $entry ): array {

		$field_type_allowlist = $this->get_field_type_allowlist();
		$entry_data           = [];
		$entry_content        = [];

		foreach ( $fields as $field_id => $field ) {
			$field_type = $field['type'];

			if ( ! in_array( $field_type, $field_type_allowlist, true ) ) {
				continue;
			}

			$field_content = $this->get_field_content( $field, $entry, $field_id );

			if ( ! isset( $entry_data[ $field_type ] ) && in_array( $field_type, [ 'name', 'email', 'url' ], true ) ) {
				$entry_data[ $field_type ] = $field_content;

				continue;
			}

			$entry_content[] = $field_content;
		}

		$entry_data['content'] = implode( ' ', $entry_content );

		return $entry_data;
	}

	/**
	 * Get field content.
	 *
	 * @since 1.8.5
	 * @since 1.8.9.3 Changed $field_id type from string to int|string.
	 *
	 * @param array      $field    Field data.
	 * @param array      $entry    Entry data.
	 * @param int|string $field_id Field ID.
	 *
	 * @return string
	 */
	private function get_field_content( array $field, array $entry, $field_id ): string {

		if ( ! isset( $entry['fields'][ $field_id ] ) ) {
			return '';
		}

		if ( ! is_array( $entry['fields'][ $field_id ] ) ) {
			return (string) $entry['fields'][ $field_id ];
		}

		if ( ! empty( $field['type'] ) && $field['type'] === 'email' && ! empty( $entry['fields'][ $field_id ]['primary'] ) ) {
			return (string) $entry['fields'][ $field_id ]['primary'];
		}

		return implode( ' ', $entry['fields'][ $field_id ] );
	}

	/**
	 * Is the entry marked as spam by Akismet?
	 *
	 * @since 1.7.6
	 *
	 * @param array $form_data Form data for the current form.
	 * @param array $entry     Entry data for the current entry.
	 *
	 * @return bool
	 */
	private function entry_is_spam( array $form_data, array $entry ): bool {

		$request = $this->get_request_args( $form_data, $entry );

		// Tell Akismet to not use the submission for training if we're on the Preview page and the user is
		// an administrator. Checking for both the preview page and the administrator role prevents
		// abuse by simply adding a GET parameter. This check happens in the ajax request,
		// where `\WPForms\Forms\Preview::is_preview_page()` does not work, so we
		// need to check for the GET parameter directly.
		if (
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			isset( $_REQUEST['page_url'] ) && strpos( wp_unslash( $_REQUEST['page_url'] ), 'wpforms_form_preview' ) !== false &&
			current_user_can( 'manage_options' )
		) {
			$request['is_test'] = true;
		}

		$response = $this->http_post( $request, 'comment-check' );

		return ! empty( $response ) && isset( $response[1] ) && 'true' === trim( $response[1] );
	}

	/**
	 * Mark the entry as not spam in Akismet.
	 *
	 * @since 1.8.8
	 *
	 * @param array $form_data Form data for the current form.
	 * @param array $entry     Entry data for the current entry.
	 *
	 * @return bool
	 */
	public function set_entry_not_spam( array $form_data, array $entry ) {

		if ( ! self::is_configured() ) {
			return false;
		}

		$request = $this->get_request_args( $form_data, $entry );

		$response = $this->http_post( $request, 'submit-ham' );

		// Yes, Akismet returns "Thanks for making the web a better place." as the response.
		return ! empty( $response ) && isset( $response[1] ) && 'Thanks for making the web a better place.' === trim( $response[1] );
	}

	/**
	 * Mark the entry as spam in Akismet.
	 *
	 * @since 1.8.9
	 *
	 * @param array $form_data Form data for the current form.
	 * @param array $entry     Entry data for the current entry.
	 *
	 * @return bool
	 */
	public function submit_missed_spam( array $form_data, array $entry ) {

		if ( ! self::is_configured() ) {
			return false;
		}

		$request = $this->get_request_args( $form_data, $entry );

		$response = $this->http_post( $request, 'submit-spam' );

		// Yes, Akismet returns "Thanks for making the web a better place." as the response.
		return ! empty( $response ) && isset( $response[1] ) && 'Thanks for making the web a better place.' === trim( $response[1] );
	}

	/**
	 * Get the request arguments to be sent to Akismet.
	 *
	 * @since 1.8.8
	 *
	 * @param array $form_data Form data for the current form.
	 * @param array $entry     Entry data for the current entry.
	 *
	 * @return array $request_args Request arguments to be sent to Akismet.
	 */
	private function get_request_args( $form_data, $entry ) {

		$entry_data = $this->get_entry_data( $form_data['fields'], $entry );

		$entry_id = $entry['entry_id'] ?? null;

		// We can't use certain real-time functions when the entry is marked as not spam.
		// In this case, we need to use the smart tag value.
		if ( ! empty( $entry_id ) ) {
			$page_url    = wpforms_process_smart_tags( '{page_url}', $form_data, [], $entry_id, 'akismet-request-args' );
			$url_referer = wpforms_process_smart_tags( '{url_referer}', $form_data, [], $entry_id, 'akismet-request-args' );
			$user_id     = wpforms_process_smart_tags( '{user_id}', $form_data, [], $entry_id, 'akismet-request-args' );
			$user_ip     = wpforms_process_smart_tags( '{user_ip}', $form_data, [], $entry_id, 'akismet-request-args' );
			$user_agent  = '';
		} else {
			$page_url    = wpforms_current_url();
			$url_referer = wp_get_referer();
			$user_id     = get_current_user_id();
			$user_ip     = wpforms_get_ip();
			$user_agent  = isset( $_SERVER['HTTP_USER_AGENT'] ) ? wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		}

		return [
			'blog'                 => get_option( 'home' ),
			'blog_lang'            => get_locale(),
			'blog_charset'         => get_bloginfo( 'charset' ),
			'permalink'            => $page_url,
			'user_ip'              => wpforms_is_collecting_ip_allowed( $form_data ) ? $user_ip : '',
			'user_id'              => $user_id,
			'user_role'            => AkismetPlugin::get_user_roles( $user_id ),
			'user_agent'           => $user_agent,
			'referrer'             => $url_referer ? $url_referer : '',
			'comment_type'         => 'contact-form',
			'comment_author'       => $entry_data['name'] ?? '',
			'comment_author_email' => $entry_data['email'] ?? '',
			'comment_author_url'   => $entry_data['url'] ?? '',
			'comment_content'      => $entry_data['content'] ?? '',
			'honeypot_field_name'  => 'wpforms[hp]',
		];
	}

	/**
	 * Send a POST request to the Akismet API.
	 *
	 * @since 1.8.8
	 *
	 * @param array  $request Request arguments to be sent to Akismet.
	 * @param string $path    API path.
	 *
	 * @return array
	 */
	private function http_post( $request, $path ) {

		// build_query() does not urlencode the values, but API explicitly requires it.
		$request = array_map( 'urlencode', $request );

		return AkismetPlugin::http_post( build_query( $request ), $path );
	}

	/**
	 * Validate entry.
	 *
	 * @since 1.7.6
	 *
	 * @param array $form_data Form data for the current form.
	 * @param array $entry     Entry data for the current entry.
	 *
	 * @return string|bool
	 */
	public function validate( array $form_data, array $entry ) {

		// If Akismet is turned on in form settings, is activated, is configured and the entry is spam.
		if (
			! empty( $form_data['settings']['akismet'] ) &&
			self::is_configured() &&
			$this->entry_is_spam( $form_data, $entry )
		) {
			// This string is being logged not printed, so it does not need to be translatable.
			return esc_html__( 'Anti-spam verification failed, please try again later.', 'wpforms-lite' );
		}

		return false;
	}
}
