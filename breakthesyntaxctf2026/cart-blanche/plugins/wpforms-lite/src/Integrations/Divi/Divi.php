<?php

namespace WPForms\Integrations\Divi;

use WPForms\Integrations\Divi\Interfaces\LocalizedDataInterface;
use WPForms_Field_Select;
use WPForms\Integrations\IntegrationInterface;

/**
 * Class Divi.
 *
 * @since 1.6.3
 */
class Divi implements IntegrationInterface {

	/**
	 * Instance of the legacy module.
	 *
	 * @since 1.9.9
	 *
	 * @var WPFormsSelector|null
	 */
	private $legacy_module;

	/**
	 * Instance of the modern module.
	 *
	 * @since 1.9.9
	 *
	 * @var WPFormsSelectorModern|null
	 * @noinspection PhpPrivateFieldCanBeLocalVariableInspection
	 */
	private $modern_module;

	/**
	 * Indicate if the current integration is allowed to load.
	 *
	 * @since 1.6.3
	 *
	 * @return bool
	 */
	public function allow_load(): bool {

		return wpforms_is_divi_active();
	}

	/**
	 * Load an integration.
	 *
	 * @since 1.6.3
	 */
	public function load() {

		$this->hooks();
	}

	/**
	 * Hooks.
	 *
	 * @since 1.6.3
	 */
	public function hooks(): void {

		add_action( 'et_builder_ready', [ $this, 'register_module' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'frontend_styles' ], 12 );

		// Register module.
		add_action( 'divi_module_library_modules_dependency_tree', [ $this, 'register_modern_selector' ] );

		if ( wp_doing_ajax() ) {
			add_action( 'wp_ajax_wpforms_divi_preview', [ $this, 'preview' ] );
		}

		if ( $this->is_divi_builder() ) {
			add_action( 'wp_enqueue_scripts', [ $this, 'builder_styles' ], 12 );
			add_action( 'wp_enqueue_scripts', [ $this, 'builder_scripts' ] );

			add_filter( 'wpforms_global_assets', '__return_true' );
			add_filter( 'wpforms_frontend_missing_assets_error_js_disable', '__return_true', PHP_INT_MAX );

			// Hide CAPTCHA badge in Divi Builder.
			add_filter( 'wpforms_frontend_recaptcha_disable', '__return_true' );
		}
	}

	/**
	 * Register modern selector dependency.
	 *
	 * @since 1.9.9
	 *
	 * @param object $dependency_tree Dependency tree object.
	 */
	public function register_modern_selector( object $dependency_tree ): void {

		if ( ! $this->is_divi_5_or_higher() ) {
			return;
		}

		$this->modern_module = new WPFormsSelectorModern();

		if ( $this->is_divi_builder() ) {
			$this->insert( $this->modern_module );
		}

		$dependency_tree->add_dependency( $this->modern_module );
	}

	/**
	 * Check if Divi 5 or higher is active.
	 *
	 * @since 1.9.9
	 *
	 * @return bool
	 * @noinspection PhpUndefinedConstantInspection
	 */
	protected function is_divi_5_or_higher(): bool {

		if ( ! defined( 'ET_BUILDER_VERSION' ) ) {
			return false;
		}

		return version_compare( ET_BUILDER_VERSION, '5.0.0', '>=' );
	}

	/**
	 * Determine if a current page is opened in the Divi Builder.
	 *
	 * @since 1.6.3
	 *
	 * @return bool
	 */
	private function is_divi_builder(): bool {

		return ! empty( $_GET['et_fb'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}

	/**
	 * Get the current style name.
	 *
	 * Overwrite styles for the Divi Builder.
	 *
	 * @since 1.6.3
	 *
	 * @return string
	 */
	public function get_current_styles_name(): string {

		$disable_css = absint( wpforms_setting( 'disable-css', 1 ) );

		if ( $disable_css === 3 ) {
			return '';
		}

		$styles_name = wpforms_get_render_engine() . '-';

		$styles_name .= $disable_css === 1 ? 'full' : 'base';

		return $styles_name;
	}

	/**
	 * Determine if the Divi Builder plugin is loaded.
	 *
	 * @since 1.6.3
	 *
	 * @return bool
	 */
	protected function is_divi_plugin_loaded(): bool {

		return self::is_divi_loaded();
	}

	/**
	 * Helper method to check if the Divi plugin is loaded.
	 *
	 * @since 1.8.5
	 *
	 * @return bool
	 */
	public static function is_divi_loaded(): bool {

		if ( ! is_singular() ) {
			return false;
		}

		return defined( 'ET_BUILDER_PLUGIN_ACTIVE' ) || defined( 'ET_BUILDER_THEME' );
	}

	/**
	 * WPForms frontend styles special for Divi.
	 *
	 * @since 1.8.1
	 */
	protected function divi_frontend_styles() {

		$min = wpforms_get_min_suffix();

		$styles_name = $this->get_current_styles_name();

		wp_enqueue_style(
			'wpforms-choicesjs',
			WPFORMS_PLUGIN_URL . "assets/css/integrations/divi/choices{$min}.css",
			[],
			WPForms_Field_Select::CHOICES_VERSION
		);

		if ( empty( $styles_name ) ) {
			return;
		}

		// Load CSS per global setting.
		wp_register_style(
			"wpforms-divi-{$styles_name}",
			WPFORMS_PLUGIN_URL . "assets/css/integrations/divi/wpforms-{$styles_name}{$min}.css",
			[],
			WPFORMS_VERSION
		);
	}

	/**
	 * Register frontend styles.
	 * Required for the plugin version of builder only.
	 *
	 * @since 1.6.3
	 */
	public function frontend_styles() {

		if ( ! $this->is_divi_plugin_loaded() ) {
			return;
		}

		if ( $this->allow_frontend_styles() ) {
			$this->divi_frontend_styles();
		}
	}

	/**
	 * Load styles.
	 *
	 * @since 1.6.3
	 */
	public function builder_styles() {

		$min = wpforms_get_min_suffix();

		wp_enqueue_style(
			'wpforms-integrations',
			WPFORMS_PLUGIN_URL . "assets/css/admin-integrations{$min}.css",
			null,
			WPFORMS_VERSION
		);

		$this->divi_frontend_styles();
	}

	/**
	 * Load scripts.
	 *
	 * @since 1.6.3
	 */
	public function builder_scripts(): void {

		if ( ! $this->legacy_module ) {
			return;
		}

		$min = wpforms_get_min_suffix();

		wp_enqueue_script(
			'wpforms-divi',
			WPFORMS_PLUGIN_URL . "assets/js/integrations/divi/formselector.es5{$min}.js",
			[ 'react', 'react-dom' ],
			WPFORMS_VERSION,
			true
		);

		$this->insert( $this->legacy_module );

		wp_localize_script(
			'wpforms-divi',
			'wpforms_divi_builder',
			$this->legacy_module->get_localized_data()
		);
	}

	/**
	 * Injects localized data into the provided form selector to be used in the frontend.
	 *
	 * @since 1.9.9
	 *
	 * @param LocalizedDataInterface $form_selector Interface instance to set localized data for forms.
	 */
	private function insert( LocalizedDataInterface $form_selector ): void {

		$form_selector->set_localized_data(
			[
				'ajax_url'         => admin_url( 'admin-ajax.php' ),
				'nonce'            => wp_create_nonce( 'wpforms_divi_builder' ),
				'placeholder'      => WPFORMS_PLUGIN_URL . 'assets/images/wpforms-logo.svg',
				'block_empty_url'  => WPFORMS_PLUGIN_URL . 'assets/images/empty-states/no-forms.svg',
				'block_empty_text' => wp_kses(
					__( 'You can use <b>WPForms</b> to build contact forms, surveys, payment forms, and more with just a few clicks.', 'wpforms-lite' ),
					[
						'b' => [],
					]
				),
				'get_started_url'  => esc_url( admin_url( 'admin.php?page=wpforms-builder' ) ),
				'get_started_text' => esc_html__( 'Get Started', 'wpforms-lite' ),
				'guide_url'        => esc_url( wpforms_utm_link( 'https://wpforms.com/docs/creating-first-form/', 'Divi', 'Create Your First Form Documentation' ) ),
				'guide_text'       => esc_html__( 'comprehensive guide', 'wpforms-lite' ),
				'help_text'        => esc_html__( 'Need some help? Check out our', 'wpforms-lite' ),
			]
		);
	}

	/**
	 * Register module.
	 *
	 * @since        1.6.3
	 */
	public function register_module(): void {

		if ( ! class_exists( 'ET_Builder_Module' ) ||
			( $this->is_divi_5_or_higher() && $this->is_divi_builder() )
		) {
			return;
		}

		$this->legacy_module = new WPFormsSelector();
	}

	/**
	 * Ajax handler for the form preview.
	 *
	 * @since 1.6.3
	 */
	public function preview(): void { // phpcs:ignore WPForms.PHP.HooksMethod.InvalidPlaceForAddingHooks

		check_ajax_referer( 'wpforms_divi_builder', 'nonce' );

		$form_id = absint( filter_input( INPUT_POST, 'form_id', FILTER_SANITIZE_NUMBER_INT ) );

		if ( $form_id ) {
			$form_obj = wpforms()->obj( 'form' );
			$form     = $form_obj ? $form_obj->get( $form_id ) : null;
			$author   = $form ? (int) $form->post_author : 0;
			$cap      = $author === get_current_user_id() ? 'wpforms_view_own_forms' : 'wpforms_view_others_forms';

			$has_permission = wpforms_current_user_can( $cap, $form_id );
		} else {
			$has_permission = wpforms_current_user_can( [ 'wpforms_view_own_forms', 'wpforms_view_others_forms' ] );
		}

		if ( ! $has_permission ) {
			wp_send_json_error(
				esc_html__( 'You do not have permission to preview form.', 'wpforms-lite' )
			);
		}

		// Disable Anti Spam v3 honeypot.
		add_filter( 'wpforms_forms_anti_spam_v3_is_honeypot_enabled', '__return_false' );

		add_filter(
			'wpforms_frontend_container_class',
			static function ( $classes ) {

				$classes[] = 'wpforms-gutenberg-form-selector';
				$classes[] = 'wpforms-container-full';

				return $classes;
			}
		);

		add_action(
			'wpforms_frontend_output',
			static function () {

				echo '<fieldset disabled>';
			},
			3
		);

		add_action(
			'wpforms_frontend_output',
			static function () {

				echo '</fieldset>';

				// This empty image is needed to execute JS code that triggers the custom event.
				// Unfortunately, the < script > tag doesn't work in the Divi Builder.
				echo '<img
					src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg=="
					alt="Empty"
					height="0"
					width="0"
					onLoad="jQuery( document ).trigger( \'wpformsDiviModuleDisplay\' );"
				/>';
			},
			30
		);

		/**
		 * Allows showing/hiding form title and description.
		 *
		 * @since 1.6.3.1
		 *
		 * @param bool $show_title Show form title.
		 * @param int  $form_id    Form ID.
		 */
		$show_title = (bool) apply_filters( // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName
			'wpforms_divi_builder_form_title',
			'on' === filter_input( INPUT_POST, 'show_title', FILTER_SANITIZE_FULL_SPECIAL_CHARS ),
			$form_id
		);

		/**
		 * Allows showing/hiding form description.
		 *
		 * @since 1.6.3.1
		 *
		 * @param bool $show_desc Show form description.
		 * @param int  $form_id   Form ID.
		 */
		$show_desc = (bool) apply_filters( // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName
			'wpforms_divi_builder_form_desc',
			'on' === filter_input( INPUT_POST, 'show_desc', FILTER_SANITIZE_FULL_SPECIAL_CHARS ),
			$form_id
		);

		wp_send_json_success(
			do_shortcode(
				sprintf(
					'[wpforms id="%1$d" title="%2$s" description="%3$s"]',
					$form_id,
					$show_title,
					$show_desc
				)
			)
		);
	}

	/**
	 * Allow frontend styles.
	 *
	 * @since 1.9.8.6
	 *
	 * @return bool
	 */
	protected function allow_frontend_styles(): bool {

		$frontend_obj = wpforms()->obj( 'frontend' );

		if ( ! $frontend_obj ) {
			return false;
		}

		global $post;

		$content = $post->post_content ?? '';

		return (
			$frontend_obj->assets_global() ||
			has_shortcode( $content, 'wpforms' ) ||
			has_shortcode( $content, 'wpforms_selector' ) ||
			( function_exists( 'has_block' ) && has_block( 'wpforms/form-selector' ) )
		);
	}
}
