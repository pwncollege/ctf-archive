<?php

// phpcs:disable Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpUndefinedNamespaceInspection */
/** @noinspection PhpUndefinedClassInspection */
// phpcs:enable Generic.Commenting.DocComment.MissingShort

namespace WPForms\Integrations\Elementor;

use Elementor\Controls_Manager;
use Elementor\Plugin as ElementorPlugin;
use WPForms\Admin\Education\StringsTrait;
use WPForms\Frontend\CSSVars;
use WPForms\Integrations\IntegrationInterface;
use WPForms\Lite\Integrations\Elementor\ThemesData;

/**
 * Improve Elementor Compatibility.
 *
 * @since 1.6.0
 */
class Elementor implements IntegrationInterface {

	use StringsTrait;

	/**
	 * Rest API class instance.
	 *
	 * @since 1.9.6
	 *
	 * @var RestApi
	 */
	protected $rest_api_obj;

	/**
	 * ThemesData class instance.
	 *
	 * @since 1.9.6
	 *
	 * @var ThemesData
	 */
	protected $themes_data_obj;

	/**
	 * Indicates if the current integration is allowed to load.
	 *
	 * @since 1.6.0
	 *
	 * @return bool
	 */
	public function allow_load() {

		return (bool) did_action( 'elementor/loaded' );
	}

	/**
	 * Load an integration.
	 *
	 * @since 1.6.0
	 */
	public function load() {

		$this->themes_data_obj = new ThemesData();

		$this->hooks();
	}

	/**
	 * Integration hooks.
	 *
	 * @since 1.6.0
	 *
	 * @noinspection PhpUndefinedConstantInspection
	 */
	protected function hooks() {

		// Skip if Elementor is not available.
		if ( ! class_exists( '\Elementor\Plugin' ) ) {
			return;
		}

		add_action( 'elementor/preview/init', [ $this, 'init' ] );
		add_action( 'elementor/frontend/after_enqueue_scripts', [ $this, 'preview_assets' ] );
		add_action( 'elementor/frontend/after_enqueue_scripts', [ $this, 'frontend_assets' ] );
		add_action( 'elementor/editor/after_enqueue_styles', [ $this, 'editor_assets' ] );
		add_action( 'elementor/controls/register', [ $this, 'register_controls' ] );

		version_compare( ELEMENTOR_VERSION, '3.5.0', '>=' )
			? add_action( 'elementor/widgets/register', [ $this, 'register_widget' ] )
			: add_action( 'elementor/widgets/widgets_registered', [ $this, 'register_widget' ] );

		add_action( 'wp_ajax_wpforms_admin_get_form_selector_options', [ $this, 'ajax_get_form_selector_options' ] );
		add_filter( 'wpforms_integrations_gutenberg_form_selector_allow_render', [ $this, 'disable_gutenberg_block_render' ] );
		add_filter( 'wpforms_forms_anti_spam_v3_is_honeypot_enabled', [ $this, 'filter_is_honeypot_enabled' ] );

		add_action( 'rest_api_init', [ $this, 'init_rest' ] );
	}

	/**
	 * Initialize rest API.
	 *
	 * @since 1.9.6
	 */
	public function init_rest(): void {

		if ( ! $this->rest_api_obj ) {
			$this->rest_api_obj = new RestApi( $this, $this->themes_data_obj );
		}
	}

	/**
	 * Register Elementor controls.
	 *
	 * @since 1.9.6
	 *
	 * @param Controls_Manager $controls_manager Elementor controls manager.
	 */
	public function register_controls( Controls_Manager $controls_manager ): void {

		require_once WPFORMS_PLUGIN_DIR . 'src/Integrations/Elementor/Controls/WPFormsThemes.php';

		$controls_manager->register( new Controls\WPFormsThemes() );
	}

	/**
	 * Init the main logic.
	 *
	 * @since 1.6.0
	 */
	public function init(): void { // phpcs:ignore WPForms.PHP.HooksMethod.InvalidPlaceForAddingHooks

		/**
		 * Allow developers to determine whether the compatibility layer should be applied.
		 * We do this check here because we want this filter to be available for theme developers too.
		 *
		 * @since 1.6.0
		 *
		 * @param bool $use_compat Use compatibility.
		 */
		$use_compat = (bool) apply_filters( 'wpforms_apply_elementor_preview_compat', true ); // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName

		if ( $use_compat !== true ) {
			return;
		}

		// Load WPForms assets globally on the Elementor Preview panel only.
		add_filter( 'wpforms_global_assets', '__return_true' );

		// Hide CAPTCHA badge on Elementor Preview panel.
		add_filter( 'wpforms_frontend_recaptcha_disable', '__return_true' );
	}

	/**
	 * Load assets in the preview panel.
	 *
	 * @since 1.6.2
	 */
	public function preview_assets() {

		if ( ! ElementorPlugin::$instance->preview->is_preview_mode() ) {
			return;
		}

		$min = wpforms_get_min_suffix();

		// jQuery.Confirm Reloaded.
		wp_enqueue_style(
			'jquery-confirm',
			WPFORMS_PLUGIN_URL . 'assets/lib/jquery.confirm/jquery-confirm.min.css',
			null,
			'1.0.0'
		);

		wp_enqueue_script(
			'jquery-confirm',
			WPFORMS_PLUGIN_URL . 'assets/lib/jquery.confirm/jquery-confirm.min.js',
			[ 'jquery' ],
			'1.0.0',
			false
		);

		wp_enqueue_style(
			'wpforms-font-awesome',
			WPFORMS_PLUGIN_URL . 'assets/lib/font-awesome/css/all.min.css',
			null,
			'7.0.1'
		);

		// FontAwesome v4 compatibility shims.
		wp_enqueue_style(
			'wpforms-font-awesome-v4-shim',
			WPFORMS_PLUGIN_URL . 'assets/lib/font-awesome/css/v4-shims.min.css',
			null,
			'4.7.0'
		);

		wp_enqueue_style(
			'wpforms-integrations',
			WPFORMS_PLUGIN_URL . "assets/css/admin-integrations{$min}.css",
			null,
			WPFORMS_VERSION
		);

		wp_enqueue_script(
			'wpforms-elementor',
			WPFORMS_PLUGIN_URL . "assets/js/integrations/elementor/editor{$min}.js",
			[ 'elementor-frontend', 'jquery', 'wp-util', 'wpforms', 'jquery-confirm', 'wp-api-fetch' ],
			WPFORMS_VERSION,
			true
		);

		if ( $this->is_modern_widget() ) {

			wp_enqueue_script(
				'wpforms-generic-utils',
				WPFORMS_PLUGIN_URL . "assets/js/share/utils{$min}.js",
				[ 'jquery' ],
				WPFORMS_VERSION,
				true
			);

			wp_enqueue_script(
				'wpforms-elementor-modern',
				WPFORMS_PLUGIN_URL . "assets/js/integrations/elementor/editor-modern{$min}.js",
				[ 'wpforms-elementor', 'wpforms-generic-utils' ],
				WPFORMS_VERSION,
				true
			);
		}

		wp_enqueue_script(
			'wpforms-elementor-themes',
			WPFORMS_PLUGIN_URL . "assets/js/integrations/elementor/themes{$min}.js",
			[ 'wpforms-elementor-modern' ],
			WPFORMS_VERSION,
			true
		);

		// Define strings for JS.
		$strings = [
			'heads_up'                 => esc_html__( 'Heads up!', 'wpforms-lite' ),
			'cancel'                   => esc_html__( 'Cancel', 'wpforms-lite' ),
			'copy_paste_error'         => esc_html__( 'There was an error parsing your JSON code. Please check your code and try again.', 'wpforms-lite' ),
			'button_background'        => esc_html__( 'Button Background', 'wpforms-lite' ),
			'button_text'              => esc_html__( 'Button Text', 'wpforms-lite' ),
			'field_label'              => esc_html__( 'Field Label', 'wpforms-lite' ),
			'field_sublabel'           => esc_html__( 'Field Sublabel', 'wpforms-lite' ),
			'field_border'             => esc_html__( 'Field Border', 'wpforms-lite' ),
			'theme_delete_title'       => esc_html__( 'Delete Form Theme', 'wpforms-lite' ),
			// Translators: %1$s: Theme name.
			'theme_delete_confirm'     => esc_html__( 'Are you sure you want to delete the %1$s theme?', 'wpforms-lite' ),
			'theme_delete_cant_undone' => esc_html__( 'This cannot be undone.', 'wpforms-lite' ),
			'theme_delete_yes'         => esc_html__( 'Yes, Delete', 'wpforms-lite' ),
			'theme_copy'               => esc_html__( 'Copy', 'wpforms-lite' ),
			'theme_custom'             => esc_html__( 'Custom Theme', 'wpforms-lite' ),
			'theme_noname'             => esc_html__( 'Noname Theme', 'wpforms-lite' ),
			'form_themes'              => esc_html__( 'Themes', 'wpforms-lite' ),
			'themes_error'             => esc_html__( 'Error loading themes. Please try again later.', 'wpforms-lite' ),
			'upgrade_button'           => esc_html__( 'Upgrade to Pro', 'wpforms-lite' ),
			'license_message'          => esc_html__( 'To access the %name%, please enter and activate your WPForms license key in the plugin settings.', 'wpforms-lite' ),
			'license_button'           => esc_html__( 'Enter License Key', 'wpforms-lite' ),
			'license_url'              => esc_url( admin_url( 'admin.php?page=wpforms-settings' ) ),
			'pro_sections'             => [
				'background' => esc_html__( 'Background Styles', 'wpforms-lite' ),
				'container'  => esc_html__( 'Container Styles', 'wpforms-lite' ),
			],
		];

		/**
		 * Filter the strings passed to the Elementor editor script.
		 *
		 * @since 1.9.6
		 *
		 * @param array $strings Array of strings to be filtered.
		 */
		$strings = apply_filters( 'wpforms_integrations_elementor_editor_strings', $strings );

		/**
		 * Filter the variables passed to an Elementor editor script.
		 *
		 * @since 1.9.6
		 *
		 * @param array $vars Array of variables to be filtered.
		 */
		$vars = apply_filters(
			'wpforms_integrations_elementor_editor_vars',
			[
				'ajax_url'         => admin_url( 'admin-ajax.php' ),
				'nonce'            => wp_create_nonce( 'wpforms-elementor-integration' ),
				'edit_form_url'    => admin_url( 'admin.php?page=wpforms-builder&view=fields&form_id=' ),
				'add_form_url'     => admin_url( 'admin.php?page=wpforms-builder&view=setup' ),
				'css_url'          => WPFORMS_PLUGIN_URL . "assets/css/admin-integrations{$min}.css",
				'debug'            => wpforms_debug(),
				'isPro'            => wpforms()->is_pro(),
				'isAdmin'          => current_user_can( 'manage_options' ),
				'is_modern_markup' => wpforms_get_render_engine() === 'modern',
				'is_full_styling'  => (int) wpforms_setting( 'disable-css', '1' ) === 1,
				'route_namespace'  => RestApi::ROUTE_NAMESPACE,
				'strings'          => $strings,
				'sizes'            => [
					'field-size'            => CSSVars::FIELD_SIZE,
					'label-size'            => CSSVars::LABEL_SIZE,
					'button-size'           => CSSVars::BUTTON_SIZE,
					'container-shadow-size' => CSSVars::CONTAINER_SHADOW_SIZE,
				],
			]
		);

		wp_localize_script(
			'wpforms-elementor',
			'wpformsElementorVars',
			$vars
		);
	}

	/**
	 * Load an integration assets on the frontend.
	 *
	 * @since 1.6.2
	 */
	public function frontend_assets(): void {

		if ( ElementorPlugin::$instance->preview->is_preview_mode() ) {
			return;
		}

		$min = wpforms_get_min_suffix();

		wp_register_script(
			'wpforms-elementor',
			WPFORMS_PLUGIN_URL . "assets/js/integrations/elementor/frontend{$min}.js",
			[ 'elementor-frontend', 'jquery', 'wp-util', 'wpforms' ],
			WPFORMS_VERSION,
			true
		);

		wp_localize_script(
			'wpforms-elementor',
			'wpformsElementorVars',
			[
				'captcha_provider' => wpforms_setting( 'captcha-provider', 'recaptcha' ),
				'recaptcha_type'   => wpforms_setting( 'recaptcha-type', 'v2' ),
			]
		);
	}

	/**
	 * Load assets in the elementor document.
	 *
	 * @since 1.6.2
	 */
	public function editor_assets() {

		if ( empty( $_GET['action'] ) || $_GET['action'] !== 'elementor' ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}

		$min = wpforms_get_min_suffix();

		wp_enqueue_style(
			'wpforms-integrations',
			WPFORMS_PLUGIN_URL . "assets/css/admin-integrations{$min}.css",
			null,
			WPFORMS_VERSION
		);

		// Choices.js.
		wp_enqueue_script(
			'choicesjs',
			WPFORMS_PLUGIN_URL . 'assets/lib/choices.min.js',
			[],
			'10.2.0',
			false
		);

		wp_enqueue_script(
			'wpforms-admin-education-core',
			WPFORMS_PLUGIN_URL . "assets/js/admin/education/core{$min}.js",
			[ 'jquery' ],
			WPFORMS_VERSION,
			true
		);

		wp_enqueue_script(
			'wpforms-elementor-modern',
			WPFORMS_PLUGIN_URL . "assets/js/integrations/elementor/editor-context{$min}.js",
			[ 'jquery' ],
			WPFORMS_VERSION,
			true
		);

		wp_localize_script(
			'wpforms-admin-education-core',
			'wpforms_education',
			$this->get_js_strings()
		);
	}

	/**
	 * Register WPForms Widget.
	 *
	 * @since 1.6.2
	 * @since 1.7.6 Added support for the new registration method since 3.5.0.
	 * @since 1.8.3 Added a condition for selecting the required widget instance.
	 *
	 * @noinspection PhpUndefinedConstantInspection
	 */
	public function register_widget(): void {

		$widget_instance = $this->is_modern_widget() ? new WidgetModern() : new Widget();

		version_compare( ELEMENTOR_VERSION, '3.5.0', '>=' )
			? ElementorPlugin::instance()->widgets_manager->register( $widget_instance )
			: ElementorPlugin::instance()->widgets_manager->register_widget_type( $widget_instance );
	}

	/**
	 * Get form selector options.
	 *
	 * @since 1.6.2
	 */
	public function ajax_get_form_selector_options(): void {

		check_ajax_referer( 'wpforms-elementor-integration', 'nonce' );

		wp_send_json_success( ( new Widget() )->get_form_selector_options() );
	}

	/**
	 * Detect modern Widget.
	 *
	 * @since 1.8.3
	 */
	protected function is_modern_widget(): bool {

		return wpforms_get_render_engine() === 'modern' && (int) wpforms_setting( 'disable-css', '1' ) === 1;
	}

	/**
	 * Disable the block render for pages built in Elementor.
	 *
	 * @since 1.8.8
	 *
	 * @param bool|mixed $allow_render Whether to allow the block render.
	 *
	 * @return bool Whether to disable the block render.
	 */
	public function disable_gutenberg_block_render( $allow_render ): bool {

		$allow_render = (bool) $allow_render;

		$document = ElementorPlugin::$instance->documents->get( get_the_ID() );

		if ( $document && $document->is_built_with_elementor() ) {
			return false;
		}

		return $allow_render;
	}

	/**
	 * Disable honeypot on the preview panel.
	 *
	 * @since 1.9.0
	 *
	 * @param bool|mixed $is_enabled True if the honeypot is enabled, false otherwise.
	 *
	 * @return bool Whether to disable the honeypot.
	 */
	public function filter_is_honeypot_enabled( $is_enabled ): bool {

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$action = sanitize_key( $_REQUEST['action'] ?? '' );

		if (
			in_array( $action, [ 'elementor', 'elementor_ajax' ], true ) ||
			ElementorPlugin::$instance->preview->is_preview_mode()
		) {
			return false;
		}

		return (bool) $is_enabled;
	}
}
