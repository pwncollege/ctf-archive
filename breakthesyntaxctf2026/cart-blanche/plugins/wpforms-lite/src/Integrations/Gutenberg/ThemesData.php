<?php

namespace WPForms\Integrations\Gutenberg;

use WPForms\Helpers\File;

/**
 * Rest API for Gutenberg block.
 *
 * @since 1.8.8
 */
abstract class ThemesData {

	/**
	 * Custom themes JSON file path.
	 *
	 * Relative to `wp-content/uploads/wpforms` directory.
	 *
	 * @since 1.8.8
	 *
	 * @var string
	 */
	const THEMES_CUSTOM_JSON_PATH = 'themes/themes-custom.json';

	/**
	 * WPForms themes JSON file path for lite version.
	 *
	 * Relative to WPForms plugin directory.
	 *
	 * @since 1.8.8
	 *
	 * @var string
	 */
	const THEMES_WPFORMS_JSON_PATH_LITE = 'assets/lite/js/integrations/gutenberg/themes.json';

	/**
	 * Custom themes file path.
	 *
	 * @since 1.8.8
	 *
	 * @var string
	 */
	private $custom_themes_file_path;

	/**
	 * WPForms themes data.
	 *
	 * @since 1.8.8
	 *
	 * @var array
	 */
	protected $wpforms_themes;

	/**
	 * Custom themes data.
	 *
	 * @since 1.8.8
	 *
	 * @var array
	 */
	private $custom_themes;

	/**
	 * Return WPForms themes.
	 *
	 * @since 1.8.8
	 *
	 * @return array
	 */
	public function get_wpforms_themes(): array {

		if ( $this->wpforms_themes !== null ) {
			return $this->wpforms_themes;
		}

		$themes_json = File::get_contents( WPFORMS_PLUGIN_DIR . static::THEMES_WPFORMS_JSON_PATH ) ?? '{}';
		$themes      = json_decode( $themes_json, true );

		$this->wpforms_themes = ! empty( $themes ) ? $themes : [];

		return $this->wpforms_themes;
	}

	/**
	 * Return custom themes.
	 *
	 * @since 1.8.8
	 *
	 * @return array
	 */
	public function get_custom_themes(): array {

		if ( $this->custom_themes !== null ) {
			return $this->custom_themes;
		}

		$themes_json = File::get_contents( $this->get_custom_themes_file_path() ) ?? '{}';
		$themes      = json_decode( $themes_json, true );

		$this->custom_themes = ! empty( $themes ) ? $themes : [];

		return $this->custom_themes;
	}

	/**
	 * Return theme data.
	 *
	 * @since 1.8.8
	 *
	 * @param string $slug Theme slug.
	 *
	 * @return array|null
	 */
	public function get_theme( string $slug ) {

		$wpforms = $this->get_wpforms_themes();

		if ( ! empty( $wpforms[ $slug ] ) ) {
			return $wpforms[ $slug ];
		}

		$custom = $this->get_custom_themes();

		if ( ! empty( $custom[ $slug ] ) ) {
			return $custom[ $slug ];
		}

		return null;
	}

	/**
	 * Get custom themes json file path.
	 *
	 * @since 1.8.8
	 *
	 * @return string|bool File path OR false in the case of permissions error.
	 */
	public function get_custom_themes_file_path() {

		// Caching the file path in the class property.
		if ( $this->custom_themes_file_path !== null ) {
			return $this->custom_themes_file_path;
		}

		// Determine custom themes file path.
		$upload_dir  = wpforms_upload_dir();
		$upload_path = ! empty( $upload_dir['path'] ) ? $upload_dir['path'] : WP_CONTENT_DIR . 'uploads/wpforms/';
		$upload_path = trailingslashit( wp_normalize_path( $upload_path ) );
		$file_path   = $upload_path . self::THEMES_CUSTOM_JSON_PATH;
		$dirname     = dirname( $file_path );

		// If the directory doesn't exist, create it. Also, check for permissions.
		if ( ! wp_mkdir_p( $dirname ) ) {
			$file_path = false;
		}

		$this->custom_themes_file_path = $file_path;

		return $file_path;
	}

	/**
	 * Sanitize custom themes data.
	 *
	 * @since 1.8.8
	 *
	 * @param array $custom_themes Custom themes data.
	 *
	 * @return array
	 */
	private function sanitize_custom_themes_data( array $custom_themes ): array {

		$wpforms          = $this->get_wpforms_themes();
		$sanitized_themes = [];

		// Get the default theme settings.
		// If there are no default settings, use an empty array. This should never happen, but just in case.
		$default_theme             = $wpforms['default'] ?? [];
		$default_theme['settings'] = $default_theme['settings'] ?? [];

		foreach ( $custom_themes as $slug => $theme ) {
			$slug                              = sanitize_key( $slug );
			$sanitized_themes[ $slug ]['name'] = sanitize_text_field( $theme['name'] ?? 'Copy of ' . $default_theme['name'] );

			// Fill in missed settings keys with default values.
			$settings = wp_parse_args( $theme['settings'] ?? [], $default_theme['settings'] );

			// Make sure we will save only settings that are present in the default theme.
			$settings = array_intersect_key( $settings, $default_theme['settings'] );

			// Sanitize settings.
			$sanitized_themes[ $slug ]['settings'] = array_map( 'sanitize_text_field', $settings );
		}

		return $sanitized_themes;
	}

	/**
	 * Update custom themes data.
	 *
	 * @since 1.8.8
	 *
	 * @param array $custom_themes Custom themes data.
	 *
	 * @return bool
	 */
	public function update_custom_themes_file( array $custom_themes ): bool {

		// Sanitize custom themes data to be saved.
		$sanitized_themes = $this->sanitize_custom_themes_data( $custom_themes );

		// Determine custom themes file path.
		$themes_file = $this->get_custom_themes_file_path();
		$json_data   = ! empty( $sanitized_themes ) ? wp_json_encode( $sanitized_themes ) : '{}';

		// Save custom themes data and return the result.
		return File::put_contents( $themes_file, $json_data );
	}
}
