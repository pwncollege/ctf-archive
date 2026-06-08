<?php
/**
 * Settings API.
 *
 * @since 1.3.7
 */

use WPForms\Admin\Education\Helpers as EducationHelpers;

/**
 * Settings output wrapper.
 *
 * @since 1.3.9
 *
 * @param array $args Arguments.
 *
 * @return string
 */
function wpforms_settings_output_field( array $args ): string {

	// Define default callback for this field type.
	$callback = ! empty( $args['type'] ) && function_exists( 'wpforms_settings_' . $args['type'] . '_callback' ) ? 'wpforms_settings_' . $args['type'] . '_callback' : 'wpforms_settings_missing_callback';

	// Allow custom callback to be provided via arg.
	if ( ! empty( $args['callback'] ) && function_exists( $args['callback'] ) ) {
		$callback = $args['callback'];
	}

	// Store returned markup from callback.
	$field = $callback( $args );

	// Allow arg to bypass standard field wrap for custom display.
	if ( ! empty( $args['wrap'] ) ) {
		return $field;
	}

	// Default class names.
	$class = [
		'wpforms-setting-row',
		"wpforms-setting-row-{$args['type']}",
		'wpforms-clear',
	];

	// Row attributes.
	$wrapper_attributes = wpforms_html_attributes(
		'wpforms-setting-row-' . wpforms_sanitize_key( $args['id'] ),
		! empty( $args['class'] ) ? array_merge( $class, (array) $args['class'] ) : $class,
		! empty( $args['data_attributes'] ) && is_array( $args['data_attributes'] ) ? $args['data_attributes'] : [],
		! empty( $args['is_hidden'] ) ? [ 'style' => 'display:none;' ] : []
	);

	// Build standard field markup and return.
	$output = "<div {$wrapper_attributes}>";

	if ( ! empty( $args['name'] ) && empty( $args['no_label'] ) ) {
		$output .= '<span class="wpforms-setting-label">';
		$output .= '<label for="wpforms-setting-' . wpforms_sanitize_key( $args['id'] ) . '">' . esc_html( $args['name'] );

		// Add education badge, if needed.
		// The badge should be added after the label text, but before the label closing tag.
		if ( ! empty( $args['education_badge'] ) ) {
			$output .= wp_kses( $args['education_badge'], [ 'span' => [ 'class' => [] ] ] );
		}

		$output .= '</label>';
		$output .= '</span>';
	}

	$output .= '<span class="wpforms-setting-field">';
	$output .= $field;

	if ( ! empty( $args['desc_after'] ) ) {
		$output .= '<div class="wpforms-clear">' . $args['desc_after'] . '</div>';
	}

	$output .= '</span>';
	$output .= '</div>';

	return $output;
}

/**
 * Missing Callback.
 *
 * If a function is missing for settings, callbacks alert the user.
 *
 * @since 1.3.9
 *
 * @param array $args Arguments passed by the setting.
 *
 * @return string
 */
function wpforms_settings_missing_callback( array $args ): string {

	return sprintf(
		/* translators: %s - ID of a setting. */
		esc_html__( 'The callback function used for the %s setting is missing.', 'wpforms-lite' ),
		'<strong>' . wpforms_sanitize_key( $args['id'] ) . '</strong>'
	);
}

/**
 * Settings content field callback.
 *
 * @since 1.3.9
 *
 * @param array $args Arguments.
 *
 * @return string
 */
function wpforms_settings_content_callback( array $args ): string {

	return ! empty( $args['content'] ) ? $args['content'] : '';
}

/**
 * Settings license field callback.
 *
 * @since 1.3.9
 *
 * @param array $args Settings arguments.
 *
 * @return string
 * @noinspection HtmlUnknownTarget
 * @noinspection PhpUnusedParameterInspection
 */
function wpforms_settings_license_callback( array $args ): string { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found

	$output  = '<p>' . esc_html__( 'You\'re using WPForms Lite - no license needed. Enjoy!', 'wpforms-lite' ) . ' 🙂</p>';
	$output .=
		'<p>' .
		sprintf(
			wp_kses( /* translators: %s - WPForms.com upgrade URL. */
				__( 'To unlock more features consider <strong><a href="%s" target="_blank" rel="noopener noreferrer" class="wpforms-upgrade-modal">upgrading to PRO</a></strong>.', 'wpforms-lite' ),
				[
					'a'      => [
						'href'   => [],
						'class'  => [],
						'target' => [],
						'rel'    => [],
					],
					'strong' => [],
				]
			),
			esc_url( wpforms_admin_upgrade_link( 'settings-license', 'Upgrade to WPForms Pro text Link' ) )
		) .
		'</p>';
	$output .=
		'<p class="discount-note">' .
			wp_kses(
				__( 'As a valued WPForms Lite user you receive <strong>50% off</strong>, automatically applied at checkout!', 'wpforms-lite' ),
				[
					'strong' => [],
				]
			) .
		'</p>';

	$output .= '<hr><p>' . esc_html__( 'Already purchased? Simply enter your license key below to enable WPForms PRO!', 'wpforms-lite' ) . '</p>';
	$output .= '<p>';
	$output .= '<input type="password" spellcheck="false" id="wpforms-settings-upgrade-license-key" placeholder="' . esc_attr__( 'Paste license key here', 'wpforms-lite' ) . '" value="">';
	$output .= '<button class="wpforms-btn wpforms-btn-md wpforms-btn-blue" id="wpforms-settings-connect-btn">' . esc_html__( 'Verify Key', 'wpforms-lite' ) . '</button>';
	$output .= '</p>';

	/**
	 * Filter license settings HTML output.
	 *
	 * @since 1.7.9
	 *
	 * @param string $output HTML markup to be rendered in place of license settings.
	 */
	return (string) apply_filters( 'wpforms_settings_license_output', $output );
}

/**
 * Settings text input field callback.
 *
 * @since 1.3.9
 * @since 1.10.0 Adds the ability to make text input readonly.
 *
 * @param array $args Settings arguments.
 *
 * @return string
 */
function wpforms_settings_text_callback( array $args ): string {

	if ( ! in_array( $args['type'], [ 'text', 'password' ], true ) ) {
		$args['type'] = 'text';
	}

	$default  = isset( $args['default'] ) ? esc_html( $args['default'] ) : '';
	$value    = wpforms_setting( $args['id'], $default );
	$id       = wpforms_sanitize_key( $args['id'] );
	$readonly = ! empty( $args['readonly'] ) ? ' readonly' : '';

	$output = '<input type="' . esc_attr( $args['type'] ) . '" id="wpforms-setting-' . $id . '" name="' . $id . '" value="' . esc_attr( $value ) . ' " ' . $readonly . ' />';

	if ( ! empty( $args['desc'] ) ) {
		$output .= '<p class="desc">' . wp_kses_post( $args['desc'] ) . '</p>';
	}

	return $output;
}

/**
 * Settings password input field callback.
 *
 * @since 1.8.4
 *
 * @param array $args Setting field arguments.
 *
 * @return string
 */
function wpforms_settings_password_callback( array $args ): string {

	return wpforms_settings_text_callback( $args );
}

/**
 * Settings number input field callback.
 *
 * @since 1.5.3
 *
 * @param array $args Setting field arguments.
 *
 * @return string
 * @noinspection HtmlUnknownAttribute
 */
function wpforms_settings_number_callback( array $args ): string {

	$default = isset( $args['default'] ) ? esc_html( $args['default'] ) : '';
	$id      = 'wpforms-setting-' . wpforms_sanitize_key( $args['id'] );
	$attr    = [
		'value' => wpforms_setting( $args['id'], $default ),
		'name'  => wpforms_sanitize_key( $args['id'] ),
	];
	$data    = ! empty( $args['data'] ) ? $args['data'] : [];

	if ( ! empty( $args['attr'] ) ) {
		$attr = array_merge( $attr, $args['attr'] );
	}

	$output = sprintf(
		'<input type="number" %s>',
		wpforms_html_attributes( $id, [], $data, $attr )
	);

	if ( ! empty( $args['desc'] ) ) {
		$output .= '<p class="desc">' . wp_kses_post( $args['desc'] ) . '</p>';
	}

	return $output;
}

/**
 * Settings select field callback.
 *
 * @since 1.3.9
 *
 * @param array $args Arguments.
 *
 * @return string
 */
function wpforms_settings_select_callback( array $args ): string { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

	$default     = isset( $args['default'] ) ? esc_html( $args['default'] ) : '';
	$value       = wpforms_setting( $args['id'], $default );
	$id          = wpforms_sanitize_key( $args['id'] );
	$select_name = $id;
	$class       = ! empty( $args['choicesjs'] ) ? 'choicesjs-select' : '';
	$choices     = ! empty( $args['choicesjs'] );
	$data        = isset( $args['data'] ) ? (array) $args['data'] : [];
	$attr        = isset( $args['attr'] ) ? (array) $args['attr'] : [];

	if ( $choices && ! empty( $args['search'] ) ) {
		$data['search'] = 'true';
	}

	if ( ! empty( $args['placeholder'] ) ) {
		$data['placeholder'] = $args['placeholder'];
	}

	$size_attr = '';

	if ( $choices && ! empty( $args['multiple'] ) ) {
		$attr[]      = 'multiple';
		$select_name = $id . '[]';
		$size_attr   = ' size="1"';
	}

	foreach ( $data as $name => $val ) {
		$data[ $name ] = 'data-' . sanitize_html_class( $name ) . '="' . esc_attr( $val ) . '"';
	}

	$data = implode( ' ', $data );
	$attr = implode( ' ', array_map( 'sanitize_html_class', $attr ) );

	$output  = $choices ? '<span class="choicesjs-select-wrap">' : '';
	$output .= '<select id="wpforms-setting-' . $id . '" name="' . $select_name . '" class="' . $class . '"' . $data . $attr . $size_attr . '>';

	foreach ( $args['options'] as $option => $name ) {
		if ( empty( $args['selected'] ) ) {
			$selected = selected( $value, $option, false );
		} else {
			$selected = is_array( $args['selected'] ) && in_array( $option, $args['selected'], true ) ? 'selected' : '';
		}
		$output .= '<option value="' . esc_attr( $option ) . '" ' . $selected . '>' . esc_html( $name ) . '</option>';
	}

	$output .= '</select>';
	$output .= $choices ? '</span>' : '';

	if ( ! empty( $args['desc'] ) ) {
		$output .= '<p class="desc">' . wp_kses_post( $args['desc'] ) . '</p>';
	}

	return $output;
}

/**
 * Settings checkbox field callback.
 *
 * @since 1.3.9
 *
 * @param array $args Arguments.
 *
 * @return string
 */
function wpforms_settings_checkbox_callback( array $args ): string {

	$value    = wpforms_setting( $args['id'] );
	$id       = wpforms_sanitize_key( $args['id'] );
	$checked  = ! empty( $value ) ? checked( 1, $value, false ) : '';
	$disabled = ! empty( $args['disabled'] ) ? ' disabled' : '';

	$output = '<input type="checkbox" id="wpforms-setting-' . $id . '" name="' . $id . '" ' . $checked . $disabled . '>';

	if ( ! empty( $args['desc'] ) ) {
		$output .= '<p class="desc">' . wp_kses_post( $args['desc'] ) . '</p>';
	}

	if ( ! empty( $args['disabled_desc'] ) ) {
		$output .= '<p class="disabled-desc">' . wp_kses_post( $args['disabled_desc'] ) . '</p>';
	}

	return $output;
}

/**
 * Settings radio field callback.
 *
 * @since 1.3.9
 *
 * @param array $args Arguments.
 *
 * @return string
 */
function wpforms_settings_radio_callback( array $args ): string {

	$default = isset( $args['default'] ) ? esc_html( $args['default'] ) : '';
	$value   = wpforms_setting( $args['id'], $default );
	$id      = wpforms_sanitize_key( $args['id'] );
	$output  = '';
	$x       = 1;

	foreach ( $args['options'] as $option => $name ) {

		$checked = checked( $value, $option, false );
		$output .= '<span class="wpforms-settings-field-radio-wrapper">';
		$output .= '<input type="radio" id="wpforms-setting-' . $id . '[' . $x . ']" name="' . $id . '" value="' . esc_attr( $option ) . '" ' . $checked . '>';
		$output .= '<label for="wpforms-setting-' . $id . '[' . $x . ']" class="option-' . sanitize_html_class( $option ) . '">';
		$output .= esc_html( $name );
		$output .= '</label>';
		$output .= '</span>';

		++$x;
	}

	if ( ! empty( $args['desc'] ) ) {
		$output .= '<p class="desc">' . wp_kses_post( $args['desc'] ) . '</p>';
	}

	return $output;
}

/**
 * Email template endpoint field callback.
 *
 * @since 1.8.5
 *
 * @param array $args Field arguments.
 *
 * @return string
 */
function wpforms_settings_email_template_callback( array $args ): string {

	$id             = wpforms_sanitize_key( $args['id'] );
	$is_pro         = wpforms()->is_pro();
	$output         = '';
	$x              = 1;
	$education_args = [
		'name'   => esc_html__( 'Email Templates', 'wpforms-lite' ),
		'plural' => '1',
		'action' => 'upgrade',
	];

	foreach ( $args['options'] as $option => $attrs ) {
		$checked       = checked( $args['value'], $option, false );
		$has_education = ! $is_pro && isset( $attrs['is_pro'] ) && $attrs['is_pro'];
		$class         = [ 'wpforms-settings-field-radio-wrapper', 'wpforms-card-image' ];
		$data          = [];

		// Add class and data attributes for education modal, if needed.
		if ( $has_education ) {
			$class[] = 'education-modal'; // This class is used for JS.
			$data    = $education_args; // This data is used for JS.
		}

		$output .= '<span ' . wpforms_html_attributes( '', $class, $data ) . '>';
		$output .= '<input type="radio" id="wpforms-setting-' . $id . '[' . $x . ']" name="' . $id . '" value="' . esc_attr( $option ) . '" ' . $checked . '>';
		$output .= '<label for="wpforms-setting-' . $id . '[' . $x . ']" class="option-' . sanitize_html_class( $option ) . '">';
		$output .= esc_html( $attrs['name'] );

		// Add class and data attributes for education modal, if needed.
		if ( $has_education ) {
			$output .= EducationHelpers::get_badge( 'Pro' );
		}

		$output .= '<span class="wpforms-card-image-overlay">';
		$output .= '<span class="wpforms-btn-choose wpforms-btn wpforms-btn-md wpforms-btn-orange">';
		$output .= esc_html__( 'Choose', 'wpforms-lite' ) . '</span>';

		// Only add the preview action button if provided.
		if ( ! empty( $attrs['preview'] ) ) {
			$output .= '<a href="' . esc_url( $attrs['preview'] ) . '" class="wpforms-btn-preview wpforms-btn wpforms-btn-md wpforms-btn-light-grey" target="_blank">';
			$output .= esc_html__( 'Preview', 'wpforms-lite' );
			$output .= '</a>';
		}

		$output .= '</span>';
		$output .= '</label>';
		$output .= '</span>';

		++$x;
	}

	if ( ! empty( $args['desc'] ) ) {
		$output .= '<p class="desc">' . wp_kses_post( $args['desc'] ) . '</p>';
	}

	return $output;
}

/**
 * Settings toggle field callback.
 *
 * @since 1.7.4
 *
 * @param array $args Arguments.
 *
 * @return string
 */
function wpforms_settings_toggle_callback( array $args ): string { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

	$value      = ! empty( $args['value'] ) ? $args['value'] : wpforms_setting( $args['id'] );
	$id         = wpforms_sanitize_key( $args['id'] );
	$class      = ! empty( $args['control-class'] ) ? $args['control-class'] : '';
	$class     .= ! empty( $args['is-important'] ) ? ' wpforms-important' : '';
	$input_attr = ! empty( $args['input-attr'] ) ? $args['input-attr'] : '';

	$default_args = [
		'control-class' => $class,
	];

	$args = wp_parse_args( $args, $default_args );

	$output = wpforms_panel_field_toggle_control(
		$args,
		'wpforms-setting-' . $id,
		$id,
		! empty( $args['label'] ) ? $args['label'] : '',
		$value,
		$input_attr
	);

	$desc_on  = ! empty( $args['desc'] ) ? $args['desc'] : '';
	$desc_on  = ! empty( $args['desc-on'] ) ? $args['desc-on'] : $desc_on;
	$desc_off = ! empty( $args['desc-off'] ) ? $args['desc-off'] : '';

	$output .= sprintf(
		'<p class="desc desc-on wpforms-toggle-desc%1$s">%2$s</p>',
		empty( $value ) && ! empty( $desc_off ) ? ' wpforms-hidden' : '',
		wp_kses_post( $desc_on )
	);

	if ( ! empty( $desc_off ) ) {
		$output .= sprintf(
			'<p class="desc desc-off wpforms-toggle-desc%1$s">%2$s</p>',
			empty( $value ) ? '' : ' wpforms-hidden',
			wp_kses_post( $desc_off )
		);
	}

	if ( ! empty( $args['disabled_desc'] ) ) {
		$output .= '<p class="disabled-desc">' . wp_kses_post( $args['disabled_desc'] ) . '</p>';
	}

	return $output;
}

/**
 * Settings image uploads field callback.
 *
 * @since 1.3.9
 *
 * @param array $args Arguments.
 *
 * @return string
 */
function wpforms_settings_image_callback( array $args ): string {

	$default = isset( $args['default'] ) ? esc_html( $args['default'] ) : '';
	$value   = wpforms_setting( $args['id'], $default );
	$id      = wpforms_sanitize_key( $args['id'] );
	$output  = '';

	if ( ! empty( $value ) ) {
		$output .= '<img src="' . esc_url_raw( $value ) . '">';
	}

	$output .= '<input type="text" id="wpforms-setting-' . $id . '" name="' . $id . '" value="' . esc_url_raw( $value ) . '">';

	// Show the remove button if specified.
	if ( isset( $args['show_remove'] ) && $args['show_remove'] ) {
		$output .= '<button class="wpforms-btn wpforms-btn-md wpforms-setting-remove-image">' . esc_html__( 'Remove Image', 'wpforms-lite' ) . '</button>';
	}

	$output .= '<button class="wpforms-btn wpforms-btn-md wpforms-btn-light-grey wpforms-setting-upload-image">' . esc_html__( 'Upload Image', 'wpforms-lite' ) . '</button>';

	if ( ! empty( $args['desc'] ) ) {
		$output .= '<p class="desc">' . wp_kses_post( $args['desc'] ) . '</p>';
	}

	return $output;
}

/**
 * Settings color picker field callback.
 *
 * @since 1.3.9
 *
 * @param array $args Arguments.
 *
 * @return string
 */
function wpforms_settings_color_callback( array $args ): string {

	$default = isset( $args['default'] ) ? esc_html( $args['default'] ) : '';
	$value   = wpforms_setting( $args['id'], $default );
	$id      = wpforms_sanitize_key( $args['id'] );
	$data    = isset( $args['data'] ) ? (array) $args['data'] : [];

	foreach ( $data as $name => $val ) {
		$data[ $name ] = 'data-' . sanitize_html_class( $name ) . '="' . esc_attr( $val ) . '"';
	}

	$data = implode( ' ', $data );

	$output = '<input type="text" id="wpforms-setting-' . $id . '" class="wpforms-color-picker" name="' . $id . '" value="' . esc_attr( $value ) . '" ' . $data . '>';

	if ( ! empty( $args['desc'] ) ) {
		$output .= '<p class="desc">' . wp_kses_post( $args['desc'] ) . '</p>';
	}

	return $output;
}

/**
 * Color scheme endpoint fieldset callback.
 * This function will output a fieldset with color picker inputs.
 *
 * @since 1.8.5
 *
 * @param array $args Field arguments.
 *
 * @return string
 */
function wpforms_settings_color_scheme_callback( array $args ): string {

	$id     = wpforms_sanitize_key( $args['id'] );
	$value  = wpforms_setting( $args['id'], [] );
	$output = '';

	foreach ( $args['colors'] as $color => $attrs ) {
		$data             = isset( $attrs['data'] ) ? (array) $attrs['data'] : [];
		$default_value    = isset( $data['fallback-color'] ) ? wpforms_sanitize_hex_color( $data['fallback-color'] ) : '';
		$field_id         = "{$id}-{$color}";
		$field_value      = isset( $value[ $color ] ) ? wpforms_sanitize_hex_color( $value[ $color ] ) : $default_value;
		$input_attributes = wpforms_html_attributes(
			"wpforms-setting-{$field_id}",
			[ 'wpforms-color-picker' ],
			$data,
			[
				'type'  => 'text',
				'name'  => "{$id}[{$color}]",
				'value' => esc_attr( $field_value ),
			]
		);

		$output .= "<input {$input_attributes}>";
		$output .= '<label for="wpforms-setting-' . $field_id . '">';
		$output .= esc_html( $attrs['name'] );
		$output .= '</label>';
	}

	if ( ! empty( $args['desc'] ) ) {
		$output .= '<p class="desc">' . wp_kses_post( $args['desc'] ) . '</p>';
	}

	return $output;
}

/**
 * Settings providers field callback - this is for the Integrations tab.
 *
 * @since 1.3.9
 *
 * @param array $args Arguments.
 *
 * @return string
 * @noinspection PhpUnusedParameterInspection
 */
function wpforms_settings_providers_callback( array $args ): string { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found

	$active    = wpforms_get_providers_available();
	$providers = wpforms_get_providers_options();

	$output = '<div id="wpforms-settings-providers">';

	ob_start();

	/**
	 * Output settings providers.
	 *
	 * @since 1.3.9.1
	 *
	 * @param array $active    Active providers.
	 * @param array $providers Providers options.
	 */
	do_action( 'wpforms_settings_providers', $active, $providers );

	$output .= ob_get_clean();
	$output .= '</div>';

	return $output;
}

/**
 * Webhooks' endpoint field callback.
 *
 * @since 1.8.4
 *
 * @param array $args Field arguments.
 *
 * @return string
 */
function wpforms_settings_webhook_endpoint_callback( array $args ): string {

	if ( empty( $args['url'] ) ) {
		return ''; // Early return if no URL is provided.
	}

	$provider    = $args['provider'] ?? 'stripe';
	$input_id    = "wpforms-{$provider}-webhook-endpoint-url";
	$copy_btn    = '<a class="button button-secondary wpforms-copy-to-clipboard" data-clipboard-target="#' . esc_attr( $input_id ) . '" href="#" aria-label="' . esc_attr__( 'Copy webhook URL', 'wpforms-lite' ) . '"><span class="dashicons dashicons-admin-page"></span></a>';
	$input_field = '<input type="text" disabled id="' . esc_attr( $input_id ) . '" value="' . esc_url( $args['url'] ) . '" />';

	$output = sprintf(
		'<div class="%1$s">%2$s %3$s</div>',
		esc_attr( "wpforms-{$provider}-webhook-endpoint-url" ),
		$input_field,
		$copy_btn
	);

	if ( ! empty( $args['desc'] ) ) {
		$output .= sprintf( '<p class="desc">%s</p>', wp_kses_post( $args['desc'] ) );
	}

	return $output;
}

/**
 * Settings field columns callback.
 *
 * @since 1.5.8
 *
 * @param array $args Arguments passed by the setting.
 *
 * @return string
 */
function wpforms_settings_columns_callback( array $args ): string {

	if ( empty( $args['columns'] ) || ! is_array( $args['columns'] ) ) {
		return '';
	}

	$output = '<div class="wpforms-setting-columns">';

	foreach ( $args['columns'] as $column ) {

		// Define default callback for this field type.
		$callback = ! empty( $column['type'] ) ? 'wpforms_settings_' . $column['type'] . '_callback' : '';

		// Allow custom callback to be provided via arg.
		if ( ! empty( $column['callback'] ) ) {
			$callback = $column['callback'];
		}

		$output .= '<div class="wpforms-setting-column">';

		if ( ! empty( $column['name'] ) ) {
			$output .= '<label><b>' . wp_kses_post( $column['name'] ) . '</b></label>';
		}

		if ( function_exists( $callback ) ) {
			$output .= $callback( $column );
		}

		$output .= '</div>';
	}

	$output .= '</div>';

	return $output;
}
