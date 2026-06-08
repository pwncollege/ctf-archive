<?php

namespace WPForms\Integrations\DefaultThemes;

use WPForms\Integrations\IntegrationInterface;

/**
 * Class DefaultThemes.
 *
 * @since 1.6.6
 */
class DefaultThemes implements IntegrationInterface {

	/**
	 * Twenty Twenty theme name.
	 *
	 * @since 1.6.6
	 */
	const TT = 'twentytwenty';

	/**
	 * Twenty Twenty-One theme name.
	 *
	 * @since 1.6.6
	 */
	const TT1 = 'twentytwentyone';

	/**
	 * OceanWP theme name.
	 *
	 * @since 1.9.1
	 */
	const OCEANWP = 'oceanwp';

	/**
	 * Current theme name.
	 *
	 * @since 1.6.6
	 *
	 * @var string
	 */
	private $current_theme;

	/**
	 * Determine if WordPress default theme is used.
	 *
	 * @since 1.6.6
	 *
	 * @return string
	 */
	private function get_current_default_theme(): string {

		$allow_themes = [ self::TT, self::TT1, self::OCEANWP ];
		$theme_name   = get_template();

		return in_array( $theme_name, $allow_themes, true ) ? $theme_name : '';
	}

	/**
	 * Indicate if current integration is allowed to load.
	 *
	 * @since 1.6.6
	 *
	 * @return bool
	 */
	public function allow_load() {

		$this->current_theme = $this->get_current_default_theme();

		return ! empty( $this->current_theme );
	}

	/**
	 * Load an integration.
	 *
	 * @since 1.6.6
	 */
	public function load() {

		if ( $this->current_theme === self::TT ) {
			$this->tt_hooks();

			return;
		}

		if ( $this->current_theme === self::TT1 ) {
			$this->tt1_hooks();

			return;
		}

		if ( $this->current_theme === self::OCEANWP ) {
			$this->ocean_hooks();
		}
	}

	/**
	 * Hooks for the Twenty Twenty theme.
	 *
	 * @since 1.6.6
	 */
	private function tt_hooks() { // phpcs:ignore WPForms.PHP.HooksMethod.InvalidPlaceForAddingHooks

		add_action( 'wp_enqueue_scripts', [ $this, 'tt_iframe_fix' ], 11 );

		add_action( 'wpforms_frontend_css', [ $this, 'tt_dropdown_fix' ] );
	}

	/**
	 * Hooks for the Twenty Twenty-One theme.
	 *
	 * @since 1.6.6
	 */
	private function tt1_hooks() { // phpcs:ignore WPForms.PHP.HooksMethod.InvalidPlaceForAddingHooks

		if ( wpforms_get_render_engine() === 'modern' ) {
			return;
		}

		$form_styling = wpforms_setting( 'disable-css', '1' );

		if ( $form_styling === '1' ) {
			add_action( 'wp_enqueue_scripts', [ $this, 'tt1_multiple_fields_fix' ], 11 );
			add_action( 'wp_enqueue_scripts', [ $this, 'tt1_dropdown_fix' ], 11 );
		}

		if ( $form_styling === '2' ) {
			add_action( 'wp_enqueue_scripts', [ $this, 'tt1_base_style_fix' ], 11 );
		}
	}

	/**
	 * Hooks for the OceanWP theme.
	 *
	 * @since 1.9.1
	 */
	private function ocean_hooks() { // phpcs:ignore WPForms.PHP.HooksMethod.InvalidPlaceForAddingHooks

		add_action( 'wp_enqueue_scripts', [ $this, 'ocean_button_hover' ], 100 );
	}

	/**
	 * Apply button hover fix for OceanWP theme.
	 *
	 * @since 1.9.1
	 */
	public function ocean_button_hover() {

		// Only full styles are supported.
		if ( (int) wpforms_setting( 'disable-css', 1 ) !== 1 ) {
			return;
		}

		$styles = wpforms_get_render_engine() === 'modern' ?
			/** @lang CSS */
			'body div.wpforms-container-full .wpforms-form input[type=submit]:hover,
			body div.wpforms-container-full .wpforms-form input[type=submit]:active,
			body div.wpforms-container-full .wpforms-form button[type=submit]:hover,
			body div.wpforms-container-full .wpforms-form button[type=submit]:active,
			body div.wpforms-container-full .wpforms-form .wpforms-page-button:hover,
			body div.wpforms-container-full .wpforms-form .wpforms-page-button:active,
			body .wp-core-ui div.wpforms-container-full .wpforms-form input[type=submit]:hover,
			body .wp-core-ui div.wpforms-container-full .wpforms-form input[type=submit]:active,
			body .wp-core-ui div.wpforms-container-full .wpforms-form button[type=submit]:hover,
			body .wp-core-ui div.wpforms-container-full .wpforms-form button[type=submit]:active,
			body .wp-core-ui div.wpforms-container-full .wpforms-form .wpforms-page-button:hover,
			body .wp-core-ui div.wpforms-container-full .wpforms-form .wpforms-page-button:active {
					background: linear-gradient(0deg, rgba(0, 0, 0, 0.2), rgba(0, 0, 0, 0.2)), var(--wpforms-button-background-color-alt, var(--wpforms-button-background-color)) !important;
			}' :
			/** @lang CSS */
			'div.wpforms-container-full .wpforms-form input[type=submit]:hover,
			div.wpforms-container-full .wpforms-form input[type=submit]:focus,
			div.wpforms-container-full .wpforms-form input[type=submit]:active,
			div.wpforms-container-full .wpforms-form button[type=submit]:hover,
			div.wpforms-container-full .wpforms-form button[type=submit]:focus,
			div.wpforms-container-full .wpforms-form button[type=submit]:active,
			div.wpforms-container-full .wpforms-form .wpforms-page-button:hover,
			div.wpforms-container-full .wpforms-form .wpforms-page-button:active,
			div.wpforms-container-full .wpforms-form .wpforms-page-button:focus {
				border: none;
			}';

		wp_add_inline_style( 'oceanwp-style', $styles );
	}

	/**
	 * Apply fix for Checkboxes and Radio fields in the Twenty Twenty-One theme.
	 *
	 * @since 1.6.6
	 */
	public function tt1_multiple_fields_fix() {

		wp_add_inline_style(
			'twenty-twenty-one-style',
			/** @lang CSS */
			'@supports (-webkit-appearance: none) or (-moz-appearance: none) {
				div.wpforms-container-full .wpforms-form input[type=checkbox] {
					-webkit-appearance: checkbox;
					-moz-appearance: checkbox;
				}
				div.wpforms-container-full .wpforms-form input[type=radio] {
					-webkit-appearance: radio;
					-moz-appearance: radio;
				}
				div.wpforms-container-full .wpforms-form input[type=checkbox]:after,
				div.wpforms-container-full .wpforms-form input[type=radio]:after {
					content: none;
				}
			}'
		);
	}

	/**
	 * Apply fix for Dropdown field arrow, when it disappeared from select in the Twenty Twenty-One theme.
	 *
	 * @since 1.6.8
	 */
	public function tt1_dropdown_fix() {

		wp_add_inline_style(
			'twenty-twenty-one-style',
			/** @lang CSS */
			'div.wpforms-container-full form.wpforms-form select {
				background-image: url("data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'10\' height=\'10\' fill=\'%2328303d\'><polygon points=\'0,0 10,0 5,5\'/></svg>");
				background-repeat: no-repeat;
				background-position: right var(--form--spacing-unit) top 60%;
				padding-right: calc(var(--form--spacing-unit) * 2.5);
			}'
		);
	}

	/**
	 * Apply fix for Checkboxes and Radio fields width in the Twenty Twenty-One theme, when the user uses only base styles.
	 *
	 * @since 1.6.8
	 */
	public function tt1_base_style_fix() {

		wp_add_inline_style(
			'twenty-twenty-one-style',
			/** @lang CSS */
			'.wpforms-container .wpforms-field input[type=checkbox],
			.wpforms-container .wpforms-field input[type=radio] {
				width: 25px;
				height: 25px;
			}
			.wpforms-container .wpforms-field input[type=checkbox] + label,
			.wpforms-container .wpforms-field input[type=radio] + label {
				vertical-align: top;
			}'
		);
	}

	/**
	 * Apply resize fix for iframe HTML element, when the next page was clicked in the Twenty Twenty theme.
	 *
	 * @since 1.6.6
	 */
	public function tt_iframe_fix() {

		wp_add_inline_script(
			'twentytwenty-js',
			/** @lang JavaScript */
			'window.addEventListener( "load", function() {

				if ( typeof jQuery === "undefined" ) {
					return;
				}

				jQuery( document ).on( "wpformsPageChange wpformsShowConditionalsField", function() {

					if ( typeof twentytwenty === "undefined" || typeof twentytwenty.intrinsicRatioVideos === "undefined" || typeof twentytwenty.intrinsicRatioVideos.makeFit === "undefined" ) {
						return;
					}

					twentytwenty.intrinsicRatioVideos.makeFit();
				} );

				jQuery( document ).on( "wpformsRichTextEditorInit", function( e, editor ) {

					jQuery( editor.container ).find( "iframe" ).addClass( "intrinsic-ignore" );
				} );
			} );'
		);
	}

	/**
	 * Apply fix for the dropdown list in Twenty Twenty theme.
	 *
	 * @since 1.7.3
	 */
	public function tt_dropdown_fix() {

		static $fixed = false;

		if ( $fixed ) {
			return;
		}

		?>
		<style>
			#site-content {
				overflow: visible !important;
			}
		</style>
		<?php

		$fixed = true;
	}

}
