<?php
/**
 * Helper functions to work with forms and form data.
 *
 * @since 1.8.0
 */

/**
 * Helper function to trigger displaying a form.
 *
 * @since 1.0.2
 *
 * @param mixed $form_id Form ID.
 * @param bool  $title   Form title.
 * @param bool  $desc    Form description.
 */
function wpforms_display( $form_id = false, $title = false, $desc = false ) {

	$frontend = wpforms()->obj( 'frontend' );

	if ( empty( $frontend ) ) {
		return;
	}

	$frontend->output( $form_id, $title, $desc );
}

/**
 * Return URL to form preview page.
 *
 * @since 1.5.1
 *
 * @param int  $form_id    Form ID.
 * @param bool $new_window New window flag.
 *
 * @return string
 */
function wpforms_get_form_preview_url( $form_id, $new_window = false ) {

	$url = add_query_arg(
		[
			'wpforms_form_preview' => absint( $form_id ),
		],
		home_url()
	);

	if ( $new_window ) {
		$url = add_query_arg(
			[
				'new_window' => 1,
			],
			$url
		);
	}

	return $url;
}

/**
 * Perform json_decode and unslash.
 *
 * IMPORTANT: This function decodes the result of wpforms_encode() properly only if
 * wp_insert_post() or wp_update_post() were used after the data is encoded.
 * Both wp_insert_post() and wp_update_post() remove excessive slashes added by wpforms_encode().
 *
 * Using wpforms_decode() on wpforms_encode() result directly
 * (without using wp_insert_post() or wp_update_post() first) always returns null or false.
 *
 * The json_decode failure returns an empty array.
 *
 * @since 1.0.0
 *
 * @param string $data Data to decode.
 *
 * @return array|false|null Empty array if json_decode fails, false if $data is empty, or decoded data.
 */
function wpforms_decode( $data ) {

	if ( empty( $data ) ) {
		return false;
	}

	$decoded_data = json_decode( $data, true );

	// If json_decode fails, return an empty array to prevent possible fatal errors due to type mismatch.
	if ( json_last_error() !== JSON_ERROR_NONE ) {
		return [];
	}

	return wp_unslash( $decoded_data );
}

/**
 * Perform json_encode and wp_slash.
 *
 * IMPORTANT: This function adds excessive slashes to prevent data damage
 * by wp_insert_post() or wp_update_post() that use wp_unslash() on all the incoming data.
 *
 * Decoding the result of this function by wpforms_decode() directly
 * (without using wp_insert_post() or wp_update_post() first) always returns null or false.
 *
 * @since 1.3.1.3
 *
 * @param mixed $data Data to encode.
 *
 * @return string|false
 */
function wpforms_encode( $data = false ) {

	if ( empty( $data ) ) {
		return false;
	}

	return wp_slash( wp_json_encode( $data ) );
}

/**
 * Decode json-encoded string if it is in JSON format.
 *
 * @since 1.7.5
 *
 * @param string $encoded_string A string.
 * @param bool   $associative    Decode to the associative array if true. Decode to object if false.
 *
 * @return array|string
 */
function wpforms_json_decode( $encoded_string, $associative = false ) {

	$encoded_string = html_entity_decode( $encoded_string );

	if ( ! wpforms_is_json( $encoded_string ) ) {
		return $encoded_string;
	}

	return json_decode( $encoded_string, $associative );
}

/**
 * Get the value of a specific WPForms setting.
 *
 * @since 1.0.0
 *
 * @param string $key           Setting name.
 * @param mixed  $default_value Default value to return if the setting is not available.
 * @param string $option        Option key, defaults to `wpforms_settings` in the `wp_options` table.
 *
 * @return mixed
 */
function wpforms_setting( $key, $default_value = false, $option = 'wpforms_settings' ) {

	$key     = wpforms_sanitize_key( $key );
	$options = get_option( $option, false );
	$value   = is_array( $options ) && ! empty( $options[ $key ] ) ? wp_unslash( $options[ $key ] ) : $default_value;

	/**
	 * Allows plugin setting to be modified.
	 *
	 * @since 1.7.8
	 *
	 * @param mixed  $value         Setting value.
	 * @param string $key           Setting key.
	 * @param mixed  $default_value Setting default value.
	 * @param string $option        Settings option name.
	 */
	return apply_filters( 'wpforms_setting', $value, $key, $default_value, $option );
}

/**
 * Update the plugin settings option and allow it to be filterable.
 *
 * The purpose of this function is to save settings when the "Save Settings" button is clicked.
 * If you are programmatically saving setting in the database in cases not triggered by user,
 * use update_option() instead.
 *
 * @since 1.6.6
 *
 * @param array $settings A plugin settings array that is saved into option table.
 *
 * @return bool
 */
function wpforms_update_settings( $settings ) {

	$old_settings = (array) get_option( 'wpforms_settings', [] );

	/**
	 * Allows plugin settings to be modified before persisting in the database.
	 *
	 * @since 1.6.6
	 *
	 * @param array $settings An array of plugin settings to modify.
	 */
	$settings = (array) apply_filters( 'wpforms_update_settings', $settings );

	$updated = update_option( 'wpforms_settings', $settings );

	/**
	 * Fires after the plugin settings were persisted in the database.
	 *
	 * The `$updated` parameter allows to check whether the update was actually successful.
	 *
	 * @since 1.6.1
	 * @since 1.8.4 The `$old_settings` parameter was added.
	 *
	 * @param array $settings     An array of plugin settings.
	 * @param bool  $updated      Whether an option was updated or not.
	 * @param array $old_settings An old array of plugin settings.
	 */
	do_action( 'wpforms_settings_updated', $settings, $updated, $old_settings );

	return $updated;
}

/**
 * Check an if form provided contains the specified field type.
 *
 * @since 1.0.5
 *
 * @param array|string $type     Field type or types.
 * @param array|object $form     Form data object.
 * @param bool         $multiple Whether to check multiple field types.
 *
 * @return bool
 */
function wpforms_has_field_type( $type, $form, $multiple = false ) {

	$form_data = '';
	$field     = false;
	$type      = (array) $type;

	if ( $multiple ) {
		foreach ( $form as $single_form ) {
			$field = wpforms_has_field_type( $type, $single_form );

			if ( $field ) {
				break;
			}
		}

		return $field;
	}

	if ( is_object( $form ) && ! empty( $form->post_content ) ) {
		$form_data = wpforms_decode( $form->post_content );
	} elseif ( is_array( $form ) ) {
		$form_data = $form;
	}

	if ( empty( $form_data['fields'] ) ) {
		return false;
	}

	foreach ( $form_data['fields'] as $single_field ) {
		if ( ! empty( $single_field['type'] ) && in_array( $single_field['type'], $type, true ) ) {
			$field = true;

			break;
		}
	}

	return $field;
}

/**
 * Check if the form provided contains a field which a specific setting.
 *
 * @since 1.4.5
 *
 * @param string       $setting  Setting key.
 * @param object|array $form     Form data.
 * @param bool         $multiple Whether to check multiple settings.
 *
 * @return bool
 */
function wpforms_has_field_setting( $setting, $form, $multiple = false ) {

	$form_data = '';
	$field     = false;

	if ( $multiple ) {
		foreach ( $form as $single_form ) {
			$field = wpforms_has_field_setting( $setting, $single_form );

			if ( $field ) {
				break;
			}
		}

		return $field;
	}

	if ( is_object( $form ) && ! empty( $form->post_content ) ) {
		$form_data = wpforms_decode( $form->post_content );
	} elseif ( is_array( $form ) ) {
		$form_data = $form;
	}

	if ( empty( $form_data['fields'] ) ) {
		return false;
	}

	foreach ( $form_data['fields'] as $single_field ) {

		if ( ! empty( $single_field[ $setting ] ) ) {
			$field = true;

			break;
		}
	}

	return $field;
}

/**
 * Retrieve actual fields from a form.
 *
 * Non-posting elements such as section divider, page break, and HTML are
 * automatically excluded. Optionally, a whitelist can be provided.
 *
 * @since 1.0.0
 *
 * @param mixed $form      Form data.
 * @param array $allowlist A list of allowed fields.
 *
 * @return mixed boolean false or array
 */
function wpforms_get_form_fields( $form = false, $allowlist = [] ) { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh, Generic.Metrics.NestingLevel.MaxExceeded

	// Accept form (post) object or form ID.
	if ( is_object( $form ) ) {
		$form = wpforms_decode( $form->post_content );
	} elseif ( is_numeric( $form ) ) {
		$form = wpforms()->obj( 'form' )->get(
			absint( $form ),
			[
				'content_only' => true,
			]
		);
	}

	$allowed_form_fields = [
		'address',
		'checkbox',
		'date-time',
		'email',
		'file-upload',
		'gdpr-checkbox',
		'hidden',
		'likert_scale',
		'name',
		'net_promoter_score',
		'number',
		'number-slider',
		'payment-checkbox',
		'payment-multiple',
		'payment-select',
		'payment-single',
		'payment-total',
		'phone',
		'radio',
		'rating',
		'richtext',
		'select',
		'signature',
		'text',
		'textarea',
		'url',
	];

	/**
	 * Filter the list of allowed form fields.
	 *
	 * @since 1.0.0
	 *
	 * @param array $allowed_form_fields List of allowed form fields.
	 */
	$allowed_form_fields = (array) apply_filters( 'wpforms_get_form_fields_allowed', $allowed_form_fields );

	if ( ! is_array( $form ) || empty( $form['fields'] ) ) {
		return false;
	}

	$allowlist = ! empty( $allowlist ) ? $allowlist : $allowed_form_fields;

	$form_fields = $form['fields'];

	foreach ( $form_fields as $id => $form_field ) {
		// Remove repeater field and its children.
		if ( $form_field['type'] === 'repeater' ) {
			foreach ( (array) $form_field['columns'] as $column ) {
				$column_fields = $column['fields'] ?? [];

				foreach ( $column_fields as $field_id ) {
					unset( $form_fields[ $field_id ] );
				}
			}
		}

		if ( ! in_array( $form_field['type'], $allowlist, true ) ) {
			unset( $form_fields[ $id ] );
		}
	}

	return $form_fields;
}

/**
 * Conditional logic form fields supported.
 *
 * @since 1.5.2
 *
 * @return array
 */
function wpforms_get_conditional_logic_form_fields_supported() {

	$fields_supported = [
		'checkbox',
		'email',
		'hidden',
		'net_promoter_score',
		'number',
		'number-slider',
		'payment-checkbox',
		'payment-multiple',
		'payment-select',
		'radio',
		'rating',
		'richtext',
		'select',
		'text',
		'textarea',
		'url',
	];

	/**
	 * Filter the list of form fields supported by conditional logic.
	 *
	 * @since 1.8.0
	 *
	 * @param array $fields_supported List of form fields supported by conditional logic.
	 */
	return apply_filters( 'wpforms_get_conditional_logic_form_fields_supported', $fields_supported );
}

/**
 * Get meta key value for a form field.
 *
 * @since 1.3.1
 * @since 1.5.0 More strict parameters. Always return an array.
 *
 * @param string $key       Meta key.
 * @param string $value     Meta value to check against.
 * @param array  $form_data Form data array.
 *
 * @return array Empty array, when no data is found.
 */
function wpforms_get_form_fields_by_meta( $key, $value, $form_data ) {

	$found = [];

	if ( empty( $key ) || empty( $value ) || empty( $form_data['fields'] ) ) {
		return $found;
	}

	foreach ( $form_data['fields'] as $id => $field ) {

		if ( ! empty( $field['meta'][ $key ] ) && $value === $field['meta'][ $key ] ) {
			$found[ $id ] = $field;
		}
	}

	return $found;
}

/**
 * Retrieve the full config for CAPTCHA.
 *
 * @since 1.6.4
 *
 * @return array
 */
function wpforms_get_captcha_settings() {

	$allowed_captcha_list = [ 'hcaptcha', 'recaptcha', 'turnstile' ];
	$captcha_provider     = wpforms_setting( 'captcha-provider', 'recaptcha' );

	if ( ! in_array( $captcha_provider, $allowed_captcha_list, true ) ) {
		return [
			'provider' => 'none',
		];
	}

	return [
		'provider'       => $captcha_provider,
		'site_key'       => sanitize_text_field( wpforms_setting( "{$captcha_provider}-site-key", '' ) ),
		'secret_key'     => sanitize_text_field( wpforms_setting( "{$captcha_provider}-secret-key", '' ) ),
		'recaptcha_type' => wpforms_setting( 'recaptcha-type', 'v2' ),
		'theme'          => sanitize_text_field( wpforms_setting( "{$captcha_provider}-theme", '' ) ),
	];
}

/**
 * Process smart tags.
 *
 * @since 1.7.1
 * @since 1.8.7   Added `$context` parameter.
 * @since 1.9.9.2 Added `$context_data` parameter.
 *
 * @param string $content      Content.
 * @param array  $form_data    Form data.
 * @param array  $fields       List of fields.
 * @param string $entry_id     Entry ID.
 * @param string $context      Context.
 * @param array  $context_data Context data.
 *
 * @return string|mixed
 */
function wpforms_process_smart_tags( $content, $form_data, $fields = [], $entry_id = '', $context = '', array $context_data = [] ) {

	// Skip it if variables have invalid format.
	if ( ! is_string( $content ) || ! is_array( $form_data ) || ! is_array( $fields ) ) {
		return $content;
	}

	/**
	 * Process smart tags.
	 *
	 * @since 1.4.0
	 * @since 1.8.7 Added $context parameter.
	 *
	 * @param string $content      Content.
	 * @param array  $form_data    Form data.
	 * @param array  $fields       List of fields.
	 * @param string $entry_id     Entry ID.
	 * @param string $context      Context.
	 * @param array  $context_data Context data.
	 *
	 * @return string
	 */
	return (string) apply_filters( 'wpforms_process_smart_tags', $content, $form_data, $fields, $entry_id, $context, $context_data );
}

/**
 * Get all smart tags in the content.
 * This function has been moved from the existing \WPForms\SmartTags\SmartTags::class.
 *
 * @since 1.10.0
 *
 * @param string $content Content.
 *
 * @return array
 */
function wpforms_get_all_smart_tags( $content ) {

	/**
	 * A smart tag should start and end with a curly brace.
	 * ([a-z0-9_]+) a smart tag name and also the first capturing group.
	 * Lowercase letters, digits, and an underscore.
	 * (|[ =][^\n}]*) - second capturing group:
	 * | no characters at all or the following:
	 * [ =][^\n}]* space or equal sign and any number of any characters except new line and closing curly brace.
	 */
	preg_match_all( '~{([a-z0-9_]+)(|[ =][^\n}]*)}~', $content, $smart_tags );

	return array_combine( $smart_tags[0], $smart_tags[1] );
}

/**
 * Check if form data slashing enabled.
 *
 * @since 1.9.0
 *
 * @return bool
 */
function wpforms_is_form_data_slashing_enabled() {

	static $enabled = null;

	if ( $enabled !== null ) {
		return $enabled;
	}

	/**
	 * Filter to enable form data slashing.
	 *
	 * @since 1.9.0
	 *
	 * @param bool $enabled Form data slashing enabled.
	 */
	$enabled = (bool) apply_filters( 'wpforms_enable_form_data_slashing', $enabled );
	$enabled = defined( 'WPFORMS_ENABLE_FORM_DATA_SLASHING' ) ? WPFORMS_ENABLE_FORM_DATA_SLASHING : $enabled;

	return $enabled;
}

/**
 * Check is frontend JS should be loaded in the header.
 *
 * @since 1.9.0
 *
 * @return bool
 */
function wpforms_is_frontend_js_header_force_load(): bool {

	/**
	 * Allow loading JS in header on various pages.
	 *
	 * @since 1.9.0
	 *
	 * @param bool $force_load Force loading JS in header, default `false`.
	 */
	return (bool) apply_filters( 'wpforms_frontend_js_header_force_load', false );
}
