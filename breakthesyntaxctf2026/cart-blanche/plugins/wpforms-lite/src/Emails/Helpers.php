<?php

namespace WPForms\Emails;

/**
 * Helper class for the email templates.
 *
 * @since 1.8.5
 */
class Helpers {

	/**
	 * Get Email template choices.
	 *
	 * @since 1.8.5
	 *
	 * @param bool $include_legacy Whether to include a Legacy template into the list.
	 *
	 * @return array
	 */
	public static function get_email_template_choices( $include_legacy = true ) {

		$choices   = [];
		$templates = Notifications::get_all_templates();

		// If there are no templates, return empty choices.
		if ( empty( $templates ) || ! is_array( $templates ) ) {
			return $choices;
		}

		// Add legacy template to the choices as the first option.
		if ( $include_legacy && self::is_legacy_html_template() ) {
			$choices['default'] = [
				'name' => esc_html__( 'Legacy', 'wpforms-lite' ),
			];
		}

		// Iterate through templates and build $choices array.
		foreach ( $templates as $template_key => $template ) {
			// Skip if the template name is empty.
			if ( empty( $template['name'] ) ) {
				continue;
			}

			$choices[ $template_key ] = $template;
		}

		return $choices;
	}

	/**
	 * Retrieves the current email template name.
	 * If the current template is not found, the default template will be returned.
	 *
	 * This method respects backward compatibility and will return the old "Legacy" template if it is set.
	 * If a template name is provided, the function will attempt to validate and return it. If validation fails,
	 * it will default to the email template name "Classic."
	 *
	 * @since 1.8.5
	 *
	 * @param string $template_name Optional. The name of the email template to evaluate.
	 *
	 * @return string
	 */
	public static function get_current_template_name( $template_name = '' ) {

		// If a template name is provided, sanitize it. Otherwise, use the default template name from settings.
		$settings_template = wpforms_setting( 'email-template', Notifications::DEFAULT_TEMPLATE );
		$template          = ! empty( $template_name ) ? trim( sanitize_text_field( $template_name ) ) : $settings_template;

		// If the user has set the legacy template, return it.
		if ( $template === Notifications::LEGACY_TEMPLATE && self::is_legacy_html_template() ) {
			return Notifications::LEGACY_TEMPLATE;
		}

		// In case the user has changed the general settings template,
		// but the form submitted still uses the “Legacy” template,
		// we need to revert to the general settings template.
		if ( $template === Notifications::LEGACY_TEMPLATE && ! self::is_legacy_html_template() ) {
			$template = wpforms_setting( 'email-template', Notifications::DEFAULT_TEMPLATE );
		}

		// Check if the given template name is valid by looking into available templates.
		$current_template = Notifications::get_available_templates( $template );

		// If the current template is not found or its corresponding class does not exist, return the default template.
		if ( ! isset( $current_template['path'] ) || ! class_exists( $current_template['path'] ) ) {

			// Last resort, check if the template defined in the settings can be used.
			// This would be helpful when user downgrades from Pro to Lite version and the template is not available anymore.
			if ( isset( $current_template[ $settings_template ] ) ) {
				return $settings_template;
			}

			return Notifications::DEFAULT_TEMPLATE;
		}

		// The provided template is valid, so return it.
		return $template;
	}

	/**
	 * Get the current email template class path.
	 *
	 * @since 1.8.5
	 *
	 * @param string $template_name  Optional. The name of the email template to evaluate.
	 * @param string $fallback_class Optional. The class to use if the template is not found.
	 *                               This argument most likely will be used for backward compatibility and supporting the "Legacy" template.
	 *
	 * @return string
	 */
	public static function get_current_template_class( $template_name = '', $fallback_class = '' ) {

		$template_name = self::get_current_template_name( $template_name );

		// If the user has set the legacy template, return the "General" template.
		if ( $template_name === Notifications::LEGACY_TEMPLATE ) {
			return ! empty( $fallback_class ) && class_exists( $fallback_class ) ? $fallback_class : __NAMESPACE__ . '\Templates\General';
		}

		// Check if the given template name is valid by looking into available templates.
		$current_template = Notifications::get_available_templates( $template_name );

		// If the current template is not found or its corresponding class does not exist, return the "Classic" template.
		if ( ! isset( $current_template['path'] ) || ! class_exists( $current_template['path'] ) ) {
			return Notifications::get_available_templates( Notifications::DEFAULT_TEMPLATE )['path'];
		}

		// The provided template is valid, so return it.
		return $current_template['path'];
	}

	/**
	 * Get the style overrides for the current email template.
	 *
	 * This function retrieves the style overrides for the email template, including background color,
	 * body color, text color, link color, and typography. It provides default values and handles
	 * different settings for both the free and Pro versions of the plugin.
	 *
	 * @since 1.8.5
	 *
	 * @return array
	 */
	public static function get_current_template_style_overrides() {

		// Get the header image size for the current template.
		list( $header_image_size, $header_image_size_dark ) = self::get_template_header_image_size();

		// Get the typography for the current template.
		list( $email_typography, $email_typography_dark ) = self::get_template_typography();

		// Default style overrides.
		$defaults = [
			'email_background_color'       => '#e9eaec',
			'email_body_color'             => '#ffffff',
			'email_text_color'             => '#333333',
			'email_links_color'            => '#e27730',
			'email_background_color_dark'  => '#2d2f31',
			'email_body_color_dark'        => '#1f1f1f',
			'email_text_color_dark'        => '#dddddd',
			'email_links_color_dark'       => '#e27730',
			'email_typography'             => $email_typography,
			'email_typography_dark'        => $email_typography_dark,
			'header_image_max_width'       => $header_image_size['width'],
			'header_image_max_height'      => $header_image_size['height'],
			'header_image_max_width_dark'  => $header_image_size_dark['width'],
			'header_image_max_height_dark' => $header_image_size_dark['height'],
		];

		// Retrieve old background colors setting from the Lite version.
		$lite_background_color      = wpforms_setting( 'email-background-color', $defaults['email_background_color'] );
		$lite_background_color_dark = wpforms_setting( 'email-background-color-dark', $defaults['email_background_color_dark'] );

		// Leave early if the user has the Lite version.
		if ( ! wpforms()->is_pro() ) {
			// Override the background colors with the old setting.
			$defaults['email_background_color']      = $lite_background_color;
			$defaults['email_background_color_dark'] = $lite_background_color_dark;

			/**
			 * Filter the style overrides for the current email template.
			 *
			 * @since 1.8.6
			 *
			 * @param array $overrides The current email template style overrides.
			 */
			return (array) apply_filters( 'wpforms_emails_helpers_style_overrides_args', $defaults );
		}

		// Get the color scheme from the settings.
		$color_scheme = wpforms_setting( 'email-color-scheme', [] );

		// If the user has the Pro version, but the light mode background color is the old setting, override it.
		if ( empty( $color_scheme['email_background_color'] ) && ! empty( $lite_background_color ) ) {
			$color_scheme['email_background_color'] = $lite_background_color;
		}

		// Get the dark mode color scheme from the settings.
		$color_scheme_dark = wpforms_setting( 'email-color-scheme-dark', [] );

		// If the user has the Pro version, but the dark mode background color is the old setting, override it.
		if ( empty( $color_scheme_dark['email_background_color_dark'] ) && ! empty( $lite_background_color_dark ) ) {
			$color_scheme_dark['email_background_color_dark'] = $lite_background_color_dark;
		}

		// Merge the color schemes with the defaults.
		$overrides = wp_parse_args( $color_scheme + $color_scheme_dark, $defaults );

		/**
		 * Filter the style overrides for the current email template.
		 *
		 * @since 1.8.6
		 *
		 * @param array $overrides The current email template style overrides.
		 */
		return (array) apply_filters( 'wpforms_emails_helpers_style_overrides_args', $overrides );
	}

	/**
	 * Check if the current email template is plain text.
	 *
	 * @since 1.8.5
	 *
	 * @param string $template_name Optional. The name of the email template to compare.
	 *
	 * @return bool
	 */
	public static function is_plain_text_template( $template_name = '' ) {

		// Leave early in case the given template name is not empty, and we can resolve it early.
		if ( ! empty( $template_name ) ) {
			return $template_name === Notifications::PLAIN_TEMPLATE;
		}

		return wpforms_setting( 'email-template', Notifications::DEFAULT_TEMPLATE ) === Notifications::PLAIN_TEMPLATE;
	}

	/**
	 * Check if the current template is legacy.
	 * Legacy template is the one that its value is 'default'.
	 *
	 * @since 1.8.5
	 *
	 * @return bool
	 */
	public static function is_legacy_html_template() {

		return wpforms_setting( 'email-template', Notifications::DEFAULT_TEMPLATE ) === Notifications::LEGACY_TEMPLATE;
	}

	/**
	 * Get the current template's typography.
	 *
	 * This function retrieves the typography setting for email templates and returns the corresponding font family.
	 *
	 * If the user has the Pro version, the font-family is determined based on the current template.
	 * For free users, the font-family defaults to "Sans Serif" because the available templates
	 * ("Classic" and "Compact") use this font-family in their design.
	 *
	 * @since 1.8.5
	 * @since 1.8.6 Added $typography argument.
	 *
	 * @param string $typography Optional. The typography setting to evaluate.
	 *
	 * @return array|string
	 */
	public static function get_template_typography( $typography = '' ) {

		// Predefined font families for light and dark modes.
		$font_families = [
			'sans_serif' => '-apple-system, BlinkMacSystemFont, avenir next, avenir, segoe ui, helvetica neue, helvetica, Cantarell, Ubuntu, roboto, noto, arial, sans-serif',
			'serif'      => 'Iowan Old Style, Apple Garamond, Baskerville, Times New Roman, Droid Serif, Times, Source Serif Pro, serif, Apple Color Emoji, Segoe UI Emoji, Segoe UI Symbol',
		];

		// If the user is not using the Pro version, return "Sans Serif" font-family.
		if ( ! wpforms()->is_pro() ) {
			return [ $font_families['sans_serif'], $font_families['sans_serif'] ];
		}

		// Leave early if a specific typography is requested.
		if ( ! empty( $typography ) ) {
			// Validate the input and return the corresponding font family.
			return $font_families[ $typography ] ?? $font_families['sans_serif'];
		}

		// Get typography settings from email settings.
		$setting_typography = [
			// Light mode.
			wpforms_setting( 'email-typography', 'sans-serif' ),
			// Dark mode.
			wpforms_setting( 'email-typography-dark', 'sans-serif' ),
		];

		// Map setting values to predefined font families, default to 'sans_serif' if not found.
		return array_map(
			static function ( $item ) use ( $font_families ) {

				return $font_families[ $item ] ?? $font_families['sans_serif'];
			},
			$setting_typography
		);
	}


	/**
	 * Get the header image size based on the specified size or 'medium' by default.
	 *
	 * Note that when given a size input, this function will only validate the input and return the corresponding size.
	 * Otherwise, it will return the header image size for the current template in both light and dark modes.
	 *
	 * @since 1.8.5
	 * @since 1.8.6 Added $size argument.
	 *
	 * @param string $size Optional. The desired image size ('small', 'medium', or 'large').
	 *
	 * @return array
	 */
	public static function get_template_header_image_size( $size = '' ) {

		// Predefined image sizes.
		$sizes = [
			'small'  => [
				'width'  => '240',
				'height' => '120',
			],
			'medium' => [
				'width'  => '350',
				'height' => '180',
			],
			'large'  => [
				'width'  => '500',
				'height' => '240',
			],
		];

		// Leave early if a specific size is requested.
		if ( ! empty( $size ) ) {
			// Validate the input and return the corresponding size.
			return $sizes[ $size ] ?? $sizes['medium'];
		}

		// Get header image sizes from settings.
		$setting_size = [
			// Light mode.
			wpforms_setting( 'email-header-image-size', 'medium' ),
			// Dark mode.
			wpforms_setting( 'email-header-image-size-dark', 'medium' ),
		];

		// Map setting values to predefined sizes, default to 'medium' if not found.
		return array_map(
			static function ( $item ) use ( $sizes ) {

				return $sizes[ $item ] ?? $sizes['medium'];
			},
			$setting_size
		);
	}
}
