<?php

namespace WPForms\Integrations\WPCode;

use WPForms\Integrations\IntegrationInterface;

/**
 * Route class for the API.
 *
 * @since 1.8.5
 */
class WPCode implements IntegrationInterface {

	/**
	 * WPCode lite download URL.
	 *
	 * @since 1.8.5
	 *
	 * @var string
	 */
	public $lite_download_url = 'https://downloads.wordpress.org/plugin/insert-headers-and-footers.zip';

	/**
	 * Lite plugin slug.
	 *
	 * @since 1.8.5
	 *
	 * @var string
	 */
	public $lite_plugin_slug = 'insert-headers-and-footers/ihaf.php';

	/**
	 * WPCode lite download URL.
	 *
	 * @since 1.8.5
	 *
	 * @var string
	 */
	public $pro_plugin_slug = 'wpcode-premium/wpcode.php';

	/**
	 * Determine if the class is allowed to load.
	 *
	 * @since 1.8.5
	 *
	 * @return bool
	 * @noinspection  PhpMissingReturnTypeInspection
	 * @noinspection  ReturnTypeCanBeDeclaredInspection
	 */
	public function allow_load() {

		return wpforms_is_admin_page( 'tools', 'wpcode' );
	}

	/**
	 * Load the class.
	 *
	 * @since 1.8.5
	 */
	public function load() {

		$this->hooks();
	}

	/**
	 * Hooks.
	 *
	 * @since 1.8.5
	 */
	private function hooks() {

		if ( ! is_admin() || wp_doing_ajax() || wp_doing_cron() ) {
			return;
		}

		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ], 20 );
	}

	/**
	 * Load the WPCode snippets for our desired username or return an empty array if not available.
	 *
	 * @since 1.8.5
	 *
	 * @return array The snippets.
	 */
	public function load_wpforms_snippets(): array {

		$snippets = $this->get_placeholder_snippets();

		if ( function_exists( 'wpcode_get_library_snippets_by_username' ) ) {
			$snippets = wpcode_get_library_snippets_by_username( 'wpforms' );
		}

		// Sort by installed.
		uasort(
			$snippets,
			function ( $a, $b ) {
			return ( $b['installed'] <=> $a['installed'] );
			}
		);

		return $snippets;
	}

	/**
	 * Checks if the plugin is installed, either the lite or premium version.
	 *
	 * @since 1.8.5
	 *
	 * @return bool True if the plugin is installed.
	 */
	public function is_plugin_installed(): bool {

		return $this->is_pro_installed() || $this->is_lite_installed();
	}

	/**
	 * Is the pro plugin installed.
	 *
	 * @since 1.8.5
	 *
	 * @return bool True if the pro plugin is installed.
	 */
	public function is_pro_installed(): bool {

		return array_key_exists( $this->pro_plugin_slug, get_plugins() );
	}

	/**
	 * Is the lite plugin installed.
	 *
	 * @since 1.8.5
	 *
	 * @return bool True if the lite plugin is installed.
	 */
	public function is_lite_installed(): bool {

		return array_key_exists( $this->lite_plugin_slug, get_plugins() );
	}

	/**
	 * Basic check if the plugin is active by looking for the main function.
	 *
	 * @since 1.8.5
	 *
	 * @return bool True if the plugin is active.
	 */
	public function is_plugin_active(): bool {

		return function_exists( 'wpcode' );
	}

	/**
	 * Get plugin version.
	 *
	 * @since 1.8.5
	 *
	 * @return string
	 */
	public function plugin_version(): string {

		if ( $this->is_pro_installed() ) {
			return get_plugins()[ $this->pro_plugin_slug ]['Version'];
		}

		if ( $this->is_lite_installed() ) {
			return get_plugins()[ $this->lite_plugin_slug ]['Version'];
		}

		return '';
	}

	/**
	 * Get placeholder snippets if the WPCode snippets are not available.
	 *
	 * @since 1.8.5
	 *
	 * @return array The placeholder snippets.
	 */
	private function get_placeholder_snippets(): array {

		$snippet_titles = [
			'Add Field Values for Dropdown, Checkboxes, and Multiple Choice',
			'Allow Date Range Selection in Date Picker',
			'Allow Multiple Dates Selection in Date Picker',
			'Change CSV Export Delimiter',
			'Change Position of v2 Invisible reCAPTCHA Badge',
			'Change Sublabels for the Email Field',
			'Create Additional Schemes for the Address Field',
			'Change Sublabels for the Stripe Credit Card Field',
			'Change the Submit Button Color',
			'Defer the reCAPTCHA Script',
			'Disable Enter Key in WPForms',
			'Disable the Email Address Suggestion',
		];

		$placeholder_snippets = [];

		foreach ( $snippet_titles as $snippet_title ) {

			// Add placeholder install link so we show a button.
			$placeholder_snippets[] = [
				'title'     => $snippet_title,
				'install'   => 'https://library.wpcode.com/',
				'installed' => false,
				'note'      => 'Placeholder code snippet short description text.',
			];
		}

		return $placeholder_snippets;
	}

	/**
	 * Enqueue assets.
	 *
	 * @since 1.8.5
	 */
	public function enqueue_scripts() {

		$min = wpforms_get_min_suffix();

		wp_enqueue_script(
			'listjs',
			WPFORMS_PLUGIN_URL . 'assets/lib/list.min.js',
			[ 'jquery' ],
			'1.5.0',
			false
		);

		wp_enqueue_script(
			'wpforms-wpcode',
			WPFORMS_PLUGIN_URL . "assets/js/integrations/wpcode/wpcode{$min}.js",
			[ 'jquery', 'listjs' ],
			WPFORMS_VERSION,
			true
		);

		wp_localize_script(
			'wpforms-wpcode',
			'wpformsWpcodeVars',
			[
				'installing_text' => __( 'Installing', 'wpforms-lite' ),
			]
		);
	}
}
