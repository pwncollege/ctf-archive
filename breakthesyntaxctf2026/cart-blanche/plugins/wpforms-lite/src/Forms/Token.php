<?php

namespace WPForms\Forms;

/**
 * Class Token.
 *
 * This token class generates tokens that are used in our Anti-Spam checking mechanism.
 *
 * @since 1.6.2
 */
class Token {

	/**
	 * Initialise the actions for the Anti-spam.
	 *
	 * @since 1.6.2
	 */
	public function init() {

		$this->hooks();
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.6.2
	 */
	public function hooks() {

		add_filter( 'wpforms_frontend_form_atts', [ $this, 'add_token_to_form_atts' ], 10, 2 );
		add_filter( 'wpforms_frontend_strings', [ $this, 'add_frontend_strings' ] );
		add_action( 'wp_ajax_nopriv_wpforms_get_token', [ $this, 'ajax_get_token' ] );
		add_action( 'wp_ajax_wpforms_get_token', [ $this, 'ajax_get_token' ] );
	}

	/**
	 * Return a valid token.
	 *
	 * @since 1.6.2
	 * @since 1.7.1 Added the $form_data argument.
	 *
	 * @param mixed $current   True to use current time, otherwise a timestamp string.
	 * @param array $form_data Form data and settings.
	 *
	 * @return string Token.
	 */
	public function get( $current = true, $form_data = [] ) {

		// If $current was not passed, or it is true, we use the current timestamp.
		// If $current was passed in as a string, we'll use that passed in timestamp.
		if ( $current !== true ) {
			$time = $current;
		} else {
			$time = time();
		}

		// Format the timestamp to be less exact, as we want to deal in days.
		// June 19th, 2020 would get formatted as: 1906202017125.
		// Day of the month, month number, year, day number of the year, week number of the year.
		$token_data = gmdate( 'dmYzW', $time );

		if ( ! empty( $form_data['id'] ) ) {
			$token_data .= "::{$form_data['id']}";
		}

		// Combine our token date and our token salt, and md5 it.
		return md5( $token_data . \WPForms\Helpers\Crypto::get_secret_key() );
	}

	/**
	 * Generate the array of valid tokens to check for. These include two days
	 * before the current date to account for long cache times.
	 *
	 * These two filters are available if a user wants to extend the times.
	 * 'wpforms_form_token_check_before_today'
	 * 'wpforms_form_token_check_after_today'
	 *
	 * @since 1.6.2
	 * @since 1.7.1 Added the $form_data argument.
	 *
	 * @param array $form_data Form data and settings.
	 *
	 * @return array Array of all valid tokens to check against.
	 */
	public function get_valid_tokens( $form_data = [] ) {

		$current_date = time();

		$valid_token_times_before = [];

		$days_in_5_years = 5 * 365;

		// Create an array of 5 years worth of days.
		for ( $i = 1; $i <= $days_in_5_years; $i++ ) {
			$valid_token_times_before[] = $i * DAY_IN_SECONDS;
		}

		// Create our array of times to check before today. A user with a longer
		// cache time can extend this. A user with a shorter cache time can remove times.
		$valid_token_times_before = apply_filters(
			'wpforms_form_token_check_before_today',
			$valid_token_times_before
		);

		// Mostly to catch edge cases like the form page loading and submitting on two different days.
		// This probably won't be filtered by users too much, but they could extend it.
		$valid_token_times_after = apply_filters(
			'wpforms_form_token_check_after_today',
			[
				( 45 * MINUTE_IN_SECONDS ), // Add in 45 minutes past today to catch some midnight edge cases.
			]
		);

		// Built up our valid tokens.
		$valid_tokens = [];

		// Add in all the previous times we check.
		foreach ( $valid_token_times_before as $time ) {
			$valid_tokens[] = $this->get( $current_date - $time, $form_data );
		}

		// Add in our current date.
		$valid_tokens[] = $this->get( $current_date, $form_data );

		// Add in the times after our check.
		foreach ( $valid_token_times_after as $time ) {
			$valid_tokens[] = $this->get( $current_date + $time, $form_data );
		}

		return $valid_tokens;
	}

	/**
	 * Check if the given token is valid or not.
	 *
	 * Tokens are valid for some period of time (see wpforms_token_validity_in_hours
	 * and wpforms_token_validity_in_days to extend the validation period).
	 * By default tokens are valid for day.
	 *
	 * @since 1.6.2
	 * @since 1.7.1 Added the $form_data argument.
	 *
	 * @param string $token     Token to validate.
	 * @param array  $form_data Form data and settings.
	 *
	 * @return bool Whether the token is valid or not.
	 */
	public function verify( string $token, array $form_data = [] ): bool {

		// Check to see if our token is inside the valid tokens.
		return in_array( $token, $this->get_valid_tokens( $form_data ), true );
	}

	/**
	 * Add the token to the form attributes.
	 *
	 * @since 1.6.2
	 * @since 1.7.1 Added the $form_data argument.
	 *
	 * @param array $attrs     Form attributes.
	 * @param array $form_data Form data and settings.
	 *
	 * @return array Form attributes.
	 */
	public function add_token_to_form_atts( array $attrs, array $form_data ) {

		$attrs['atts']['data-token']      = $this->get( true, $form_data );
		$attrs['atts']['data-token-time'] = time();

		return $attrs;
	}

	/**
	 * Validate Anti-spam if enabled.
	 *
	 * @since 1.6.2
	 *
	 * @param array $form_data Form data.
	 * @param array $fields    Fields.
	 * @param array $entry     Form entry.
	 *
	 * @return bool|string True or a string with the error.
	 */
	public function validate( array $form_data, array $fields, array $entry ) {

		// Bail out if we don't have the antispam setting.
		if ( empty( $form_data['settings']['antispam'] ) ) {
			return true;
		}

		// Bail out if the antispam setting isn't enabled.
		if ( $form_data['settings']['antispam'] !== '1' ) {
			return true;
		}

		$is_valid_token = isset( $entry['token'] ) && $this->verify( (string) $entry['token'], $form_data );

		if ( $this->process_antispam_filter_wrapper( $is_valid_token, $fields, $entry, $form_data ) ) {
			return true;
		}

		// Prepare the log data.
		$form_title = $form_data['settings']['form_title'] ?? '';
		$form_id    = $form_data['id'] ?? 'unknown';

		if ( $is_valid_token ) {
			// Token is OK, but antispam filter is not passed.
			$log_message   = 'Filter is not passed';
			$error_message = $this->get_antispam_filter_message();
		} else {
			// Invalid token.
			$log_message   = 'Token is invalid';
			$error_message = $this->get_invalid_token_message();
		}

		wpforms_log(
			'Antispam: ' . $log_message,
			[
				'message'    => $error_message,
				'referer'    => esc_url_raw( (string) wp_get_referer() ),
				'form'       => ! empty( $form_title ) ? $form_title . ' (ID: ' . $form_id . ')' : 'ID: ' . $form_id,
				'token'      => $entry['token'] ?? '',
				'user_ip'    => wpforms_get_ip(),
				'entry_data' => ! wpforms_setting( 'gdpr' ) ? $entry : 'Not logged',
			],
			[
				'type'    => [ 'spam', 'error' ],
				'form_id' => $form_data['id'],
				'force'   => true,
			]
		);

		return $error_message;
	}

	/**
	 * Helper to run our filter on all the responses for the antispam checks.
	 *
	 * @since 1.6.2
	 *
	 * @param bool  $is_valid_not_spam Is valid entry or not.
	 * @param array $fields            Form Fields.
	 * @param array $entry             Form entry.
	 * @param array $form_data         Form Data.
	 *
	 * @return bool Is valid or not.
	 */
	public function process_antispam_filter_wrapper( bool $is_valid_not_spam, array $fields, array $entry, array $form_data ): bool {

		/**
		 * Allows developers to filter the antispam check result.
		 *
		 * @since 1.6.2
		 *
		 * @param bool  $is_valid_not_spam True if entry valid, false otherwise.
		 * @param array $fields            Fields data.
		 * @param array $entry             Entry data.
		 * @param array $form_data         Form data.
		 */
		return (bool) apply_filters( 'wpforms_process_antispam', $is_valid_not_spam, $fields, $entry, $form_data ); // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName
	}

	/**
	 * Helper to get the invalid token message.
	 *
	 * @since 1.6.2.1
	 *
	 * @return string Invalid token message.
	 */
	private function get_invalid_token_message(): string {

		return $this->get_error_message( esc_html__( 'Antispam token is invalid.', 'wpforms-lite' ) );
	}

	/**
	 * Helper to get the antispam filter error message.
	 *
	 * @since 1.8.9
	 *
	 * @return string Missing token message.
	 */
	private function get_antispam_filter_message(): string {

		return $this->get_error_message( esc_html__( 'Antispam filter did not allow your data to pass through.', 'wpforms-lite' ) );
	}

	/**
	 * Get error message depends on user.
	 *
	 * @since 1.6.4.1
	 *
	 * @param string $text Message text.
	 *
	 * @return string
	 */
	private function get_error_message( string $text ): string {

		$text .= ' ' . esc_html__( 'Please reload the page and try submitting the form again.', 'wpforms-lite' );

		return wpforms_current_user_can() ? $text . $this->maybe_get_support_text() : $text;
	}

	/**
	 * If a user is a super admin, add a support link to the message.
	 *
	 * @since 1.6.2.1
	 *
	 * @return string Support text if super admin, empty string if not.
	 */
	private function maybe_get_support_text(): string {

		// If a user isn't a super admin, don't return any text.
		if ( ! is_super_admin() ) {
			return '';
		}

		// If the user is an admin, return text with a link to support.
		// We add a space here to separate the sentences, but outside the localized text to avoid it being removed.
		return ' ' . sprintf(
			/* translators: placeholders are links. */
			esc_html__( 'Please check out our %1$stroubleshooting guide%2$s for details on resolving this issue.', 'wpforms-lite' ),
			'<a href="https://wpforms.com/docs/getting-support-wpforms/">',
			'</a>'
		);
	}

	/**
	 * Add token related strings to the frontend.
	 *
	 * @since 1.8.8
	 *
	 * @param array|mixed $strings Frontend strings.
	 *
	 * @return array Frontend strings.
	 */
	public function add_frontend_strings( $strings ): array {

		$strings = (array) $strings;

		$strings['error_updating_token'] = esc_html__(
			'Error updating token. Please try again or contact support if the issue persists.',
			'wpforms-lite'
		);
		$strings['network_error']        = esc_html__(
			'Network error or server is unreachable. Check your connection or try again later.',
			'wpforms-lite'
		);

		// Default token lifetime is 24 hours in seconds.
		$token_lifetime = DAY_IN_SECONDS;

		/**
		 * Filter token cache lifetime in seconds.
		 *
		 * @since 1.8.8
		 *
		 * @param integer $token_lifetime Token lifetime in seconds.
		 */
		$strings['token_cache_lifetime'] = apply_filters( 'wpforms_forms_token_cache_lifetime', $token_lifetime );

		return $strings;
	}

	/**
	 * Update token via ajax handler.
	 *
	 * @since 1.8.8
	 */
	public function ajax_get_token() {

		$form_data       = [];
		$form_data['id'] = filter_input( INPUT_POST, 'formId', FILTER_VALIDATE_INT );

		$response = [
			'token' => $this->get( true, $form_data ),
		];

		wp_send_json_success( $response );
	}
}
