<?php

namespace WPForms\Integrations\Gutenberg;

use WPForms\Frontend\CSSVars;
use WPForms\Integrations\IntegrationInterface;
use WPForms\Admin\Education\StringsTrait;

/**
 * Form Selector Gutenberg block with a live preview.
 *
 * @since 1.4.8
 */
abstract class FormSelector implements IntegrationInterface {

	use StringsTrait;

	/**
	 * Default attributes.
	 *
	 * @since 1.8.1
	 *
	 * @var array
	 */
	private const DEFAULT_ATTRIBUTES = [
		'formId'                => '',
		'displayTitle'          => false,
		'displayDesc'           => false,
		'theme'                 => '',
		'themeName'             => '',
		'fieldSize'             => 'medium',
		'backgroundImage'       => CSSVars::ROOT_VARS['background-image'],
		'backgroundPosition'    => CSSVars::ROOT_VARS['background-position'],
		'backgroundRepeat'      => CSSVars::ROOT_VARS['background-repeat'],
		'backgroundSizeMode'    => CSSVars::ROOT_VARS['background-size'],
		'backgroundSize'        => CSSVars::ROOT_VARS['background-size'],
		'backgroundWidth'       => CSSVars::ROOT_VARS['background-width'],
		'backgroundHeight'      => CSSVars::ROOT_VARS['background-height'],
		'backgroundUrl'         => CSSVars::ROOT_VARS['background-url'],
		'backgroundColor'       => CSSVars::ROOT_VARS['background-color'],
		'fieldBorderRadius'     => CSSVars::ROOT_VARS['field-border-radius'],
		'fieldBorderStyle'      => CSSVars::ROOT_VARS['field-border-style'],
		'fieldBorderSize'       => CSSVars::ROOT_VARS['field-border-size'],
		'fieldBackgroundColor'  => CSSVars::ROOT_VARS['field-background-color'],
		'fieldBorderColor'      => CSSVars::ROOT_VARS['field-border-color'],
		'fieldTextColor'        => CSSVars::ROOT_VARS['field-text-color'],
		'fieldMenuColor'        => CSSVars::ROOT_VARS['field-menu-color'],
		'labelSize'             => 'medium',
		'labelColor'            => CSSVars::ROOT_VARS['label-color'],
		'labelSublabelColor'    => CSSVars::ROOT_VARS['label-sublabel-color'],
		'labelErrorColor'       => CSSVars::ROOT_VARS['label-error-color'],
		'buttonSize'            => 'medium',
		'buttonBorderStyle'     => CSSVars::ROOT_VARS['button-border-style'],
		'buttonBorderSize'      => CSSVars::ROOT_VARS['button-border-size'],
		'buttonBorderRadius'    => CSSVars::ROOT_VARS['button-border-radius'],
		'buttonBackgroundColor' => CSSVars::ROOT_VARS['button-background-color'],
		'buttonTextColor'       => CSSVars::ROOT_VARS['button-text-color'],
		'buttonBorderColor'     => CSSVars::ROOT_VARS['button-border-color'],
		'pageBreakColor'        => CSSVars::ROOT_VARS['page-break-color'],
		'containerPadding'      => CSSVars::ROOT_VARS['container-padding'],
		'containerBorderStyle'  => CSSVars::ROOT_VARS['container-border-style'],
		'containerBorderWidth'  => CSSVars::ROOT_VARS['container-border-width'],
		'containerBorderColor'  => CSSVars::ROOT_VARS['container-border-color'],
		'containerBorderRadius' => CSSVars::ROOT_VARS['container-border-radius'],
		'containerShadowSize'   => CSSVars::CONTAINER_SHADOW_SIZE['none']['box-shadow'],
		'customCss'             => '',
		'copyPasteJsonValue'    => '',
	];

	/**
	 * Rest API class instance.
	 *
	 * @since 1.8.8
	 *
	 * @var RestApi
	 */
	protected $rest_api_obj;

	/**
	 * Rest API class instance.
	 *
	 * @since 1.8.8
	 *
	 * @var ThemesData
	 */
	protected $themes_data_obj;

	/**
	 * Render engine.
	 *
	 * @since 1.8.1
	 *
	 * @var string
	 */
	protected $render_engine;

	/**
	 * Disabled CSS setting.
	 *
	 * @since 1.8.1
	 *
	 * @var integer
	 */
	protected $disable_css_setting;

	/**
	 * Instance of CSSVars class.
	 *
	 * @since 1.8.1
	 *
	 * @var CSSVars
	 */
	private $css_vars_obj;

	/**
	 * Callbacks registered for wpforms_frontend_container_class filter.
	 *
	 * @since 1.7.5
	 *
	 * @var array
	 */
	private $callbacks = [];

	/**
	 * Currently displayed form ID.
	 *
	 * @since 1.8.8
	 *
	 * @var string|int
	 */
	private $current_form_id = 0;

	/**
	 * Indicate if the current integration is allowed to load.
	 *
	 * @since 1.4.8
	 *
	 * @return bool
	 */
	public function allow_load(): bool {

		return function_exists( 'register_block_type' );
	}

	/**
	 * Load an integration.
	 *
	 * @since 1.4.8
	 */
	public function load() {

		$this->render_engine       = wpforms_get_render_engine();
		$this->disable_css_setting = (int) wpforms_setting( 'disable-css', '1' );
		$this->css_vars_obj        = wpforms()->obj( 'css_vars' );

		wpforms()->register_instance( 'formselector_themes_data', $this->themes_data_obj );

		$this->hooks();
	}

	/**
	 * Integration hooks.
	 *
	 * @since 1.4.8
	 */
	protected function hooks() {

		add_action( 'init', [ $this, 'register_block' ] );
		add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_block_editor_assets' ] );
		add_action( 'wpforms_frontend_output_container_after', [ $this, 'replace_wpforms_frontend_container_class_filter' ] );
		add_filter( 'wpforms_frontend_form_action', [ $this, 'form_action_filter' ], 10, 2 );
		add_filter( 'wpforms_forms_anti_spam_v3_is_honeypot_enabled', [ $this, 'filter_is_honeypot_enabled' ] );
		add_filter( 'wpforms_field_richtext_display_editor_is_media_enabled', [ $this, 'disable_richtext_media' ], 10, 2 );
	}

	/**
	 * Disable honeypot in Gutenberg/Block editor.
	 *
	 * @since 1.9.0
	 *
	 * @param bool|mixed $is_enabled True if the honeypot is enabled, false otherwise.
	 *
	 * @return bool Whether to disable the honeypot.
	 */
	public function filter_is_honeypot_enabled( $is_enabled ): bool {

		if ( wpforms_is_wpforms_rest() ) {
			return false;
		}

		return (bool) $is_enabled;
	}

	/**
	 * Replace the filter registered for wpforms_frontend_container_class.
	 *
	 * @since 1.7.5
	 *
	 * @param array $form_data Form data.
	 *
	 * @return void
	 */
	public function replace_wpforms_frontend_container_class_filter( array $form_data ): void { // phpcs:ignore WPForms.PHP.HooksMethod.InvalidPlaceForAddingHooks

		if ( empty( $this->callbacks[ $form_data['id'] ] ) ) {
			return;
		}

		$callback = array_shift( $this->callbacks[ $form_data['id'] ] );

		remove_filter( 'wpforms_frontend_container_class', $callback );

		if ( ! empty( $this->callbacks[ $form_data['id'] ] ) ) {
			add_filter( 'wpforms_frontend_container_class', reset( $this->callbacks[ $form_data['id'] ] ), 10, 2 );
		}
	}

	/**
	 * Register WPForms Gutenberg block on the backend.
	 *
	 * @since 1.4.8
	 */
	public function register_block(): void {

		$type_string  = [ 'type' => 'string' ];
		$type_boolean = [ 'type' => 'boolean' ];

		$attributes = [
			'clientId'              => $type_string,
			'formId'                => $type_string,
			'displayTitle'          => $type_boolean,
			'displayDesc'           => $type_boolean,
			'className'             => $type_string,
			'theme'                 => $type_string,
			'themeName'             => $type_string,
			'fieldSize'             => $type_string,
			'fieldBorderRadius'     => $type_string,
			'fieldBorderStyle'      => $type_string,
			'fieldBorderSize'       => $type_string,
			'fieldBackgroundColor'  => $type_string,
			'fieldBorderColor'      => $type_string,
			'fieldTextColor'        => $type_string,
			'fieldMenuColor'        => $type_string,
			'labelSize'             => $type_string,
			'labelColor'            => $type_string,
			'labelSublabelColor'    => $type_string,
			'labelErrorColor'       => $type_string,
			'buttonSize'            => $type_string,
			'buttonBorderStyle'     => $type_string,
			'buttonBorderSize'      => $type_string,
			'buttonBorderRadius'    => $type_string,
			'buttonBackgroundColor' => $type_string,
			'buttonBorderColor'     => $type_string,
			'buttonTextColor'       => $type_string,
			'pageBreakColor'        => $type_string,
			'backgroundImage'       => $type_string,
			'backgroundPosition'    => $type_string,
			'backgroundRepeat'      => $type_string,
			'backgroundSizeMode'    => $type_string,
			'backgroundSize'        => $type_string,
			'backgroundWidth'       => $type_string,
			'backgroundHeight'      => $type_string,
			'backgroundUrl'         => $type_string,
			'backgroundColor'       => $type_string,
			'containerPadding'      => $type_string,
			'containerBorderStyle'  => $type_string,
			'containerBorderWidth'  => $type_string,
			'containerBorderColor'  => $type_string,
			'containerBorderRadius' => $type_string,
			'containerShadowSize'   => $type_string,
			'customCss'             => $type_string,
			'copyPasteJsonValue'    => $type_string,
		];

		$this->register_styles();

		/**
		 * Modify WPForms block attributes.
		 *
		 * @since 1.5.8.2
		 *
		 * @param array $attributes Attributes.
		 */
		$attributes = apply_filters( 'wpforms_gutenberg_form_selector_attributes', $attributes ); // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName

		register_block_type(
			'wpforms/form-selector',
			[
				'api_version'     => $this->get_block_api_version(),
				'attributes'      => $attributes,
				'style'           => 'wpforms-gutenberg-form-selector',
				'editor_style'    => 'wpforms-integrations',
				'render_callback' => [ $this, 'get_form_html' ],
			]
		);
	}

	/**
	 * Register WPForms Gutenberg block styles.
	 *
	 * @since 1.7.4.2
	 */
	protected function register_styles() {

		if ( ! is_admin() ) {
			return;
		}

		$min = wpforms_get_min_suffix();

		wp_register_style(
			'wpforms-integrations',
			WPFORMS_PLUGIN_URL . "assets/css/admin-integrations{$min}.css",
			[ 'dashicons' ],
			WPFORMS_VERSION
		);

		if ( $this->disable_css_setting === 3 ) {
			return;
		}

		$css_file = $this->disable_css_setting === 2 ? 'base' : 'full';
		$handle   = 'wpforms-gutenberg-form-selector';

		wp_register_style(
			$handle,
			WPFORMS_PLUGIN_URL . "assets/css/frontend/{$this->render_engine}/wpforms-{$css_file}{$min}.css",
			[ 'wp-edit-blocks', 'wpforms-integrations' ],
			WPFORMS_VERSION
		);

		// Add root CSS variables for the Modern Markup mode for full styles.
		if ( empty( $this->css_vars_obj ) || $this->render_engine !== 'modern' || $css_file !== 'full' ) {
			return;
		}

		wp_add_inline_style( $handle, $this->css_vars_obj->get_root_vars_css() );
	}

	/**
	 * Load WPForms Gutenberg block scripts.
	 *
	 * @since 1.4.8
	 */
	public function enqueue_block_editor_assets() {

		$min = wpforms_get_min_suffix();

		wp_enqueue_style( 'wpforms-integrations' );

		wp_set_script_translations( 'wpforms-gutenberg-form-selector', 'wpforms-lite' );

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

		// Support for the legacy form selector.
		// It is located in the common namespace.
		if ( $this->is_legacy_block() ) {
			wp_enqueue_script(
				'wpforms-gutenberg-form-selector',
				WPFORMS_PLUGIN_URL . "assets/js/integrations/gutenberg/formselector-legacy.es5{$min}.js",
				[ 'wp-blocks', 'wp-i18n', 'wp-element', 'jquery' ],
				WPFORMS_VERSION,
				true
			);

			return;
		}

		if ( $this->render_engine === 'modern' ) {
			wp_enqueue_script(
				'wpforms-modern',
				WPFORMS_PLUGIN_URL . "assets/js/frontend/wpforms-modern{$min}.js",
				[ 'wpforms-gutenberg-form-selector' ],
				WPFORMS_VERSION,
				true
			);
		}

		wp_enqueue_script(
			'wpforms-admin-education-core',
			WPFORMS_PLUGIN_URL . "assets/js/admin/education/core{$min}.js",
			[ 'jquery', 'jquery-confirm' ],
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
	 * Whether the block is legacy.
	 *
	 * @since 1.8.8
	 */
	protected function is_legacy_block() {

		return version_compare( $GLOBALS['wp_version'], '6.0', '<' );
	}

	/**
	 * Get localize data.
	 *
	 * @since 1.8.1
	 *
	 * @return array
	 */
	public function get_localize_data(): array {

		$strings = [
			'title'                        => esc_html__( 'WPForms', 'wpforms-lite' ),
			'description'                  => esc_html__( 'Select and display one of your forms.', 'wpforms-lite' ),
			'form_keywords'                => [
				esc_html__( 'form', 'wpforms-lite' ),
				esc_html__( 'contact', 'wpforms-lite' ),
				esc_html__( 'survey', 'wpforms-lite' ),
			],
			'form_select'                  => esc_html__( 'Select a Form', 'wpforms-lite' ),
			'form_settings'                => esc_html__( 'Form Settings', 'wpforms-lite' ),
			'form_edit'                    => esc_html__( 'Edit Form', 'wpforms-lite' ),
			'form_entries'                 => esc_html__( 'View Entries', 'wpforms-lite' ),
			'themes'                       => esc_html__( 'Themes', 'wpforms-lite' ),
			'theme_name'                   => esc_html__( 'Theme Name', 'wpforms-lite' ),
			'theme_delete'                 => esc_html__( 'Delete Theme', 'wpforms-lite' ),
			'theme_delete_title'           => esc_html__( 'Delete Form Theme', 'wpforms-lite' ),
			// Translators: %1$s: Theme name.
			'theme_delete_confirm'         => esc_html__( 'Are you sure you want to delete the %1$s theme?', 'wpforms-lite' ),
			'theme_delete_cant_undone'     => esc_html__( 'This cannot be undone.', 'wpforms-lite' ),
			'theme_delete_yes'             => esc_html__( 'Yes, Delete', 'wpforms-lite' ),
			'theme_copy'                   => esc_html__( 'Copy', 'wpforms-lite' ),
			'theme_custom'                 => esc_html__( 'Custom Theme', 'wpforms-lite' ),
			'theme_noname'                 => esc_html__( 'Noname Theme', 'wpforms-lite' ),
			'field_styles'                 => esc_html__( 'Field Styles', 'wpforms-lite' ),
			'field_label'                  => esc_html__( 'Field Label', 'wpforms-lite' ),
			'field_sublabel'               => esc_html__( 'Field Sublabel', 'wpforms-lite' ),
			'field_border'                 => esc_html__( 'Field Border', 'wpforms-lite' ),
			'label_styles'                 => esc_html__( 'Label Styles', 'wpforms-lite' ),
			'button_background'            => esc_html__( 'Button Background', 'wpforms-lite' ),
			'button_text'                  => esc_html__( 'Button Text', 'wpforms-lite' ),
			'button_styles'                => esc_html__( 'Button Styles', 'wpforms-lite' ),
			'container_styles'             => esc_html__( 'Container Styles', 'wpforms-lite' ),
			'background_styles'            => esc_html__( 'Background Styles', 'wpforms-lite' ),
			'remove_image'                 => esc_html__( 'Remove Image', 'wpforms-lite' ),
			'position'                     => esc_html__( 'Position', 'wpforms-lite' ),
			'top_left'                     => esc_html__( 'Top Left', 'wpforms-lite' ),
			'top_center'                   => esc_html__( 'Top Center', 'wpforms-lite' ),
			'top_right'                    => esc_html__( 'Top Right', 'wpforms-lite' ),
			'center_left'                  => esc_html__( 'Center Left', 'wpforms-lite' ),
			'center_center'                => esc_html__( 'Center Center', 'wpforms-lite' ),
			'center_right'                 => esc_html__( 'Center Right', 'wpforms-lite' ),
			'bottom_left'                  => esc_html__( 'Bottom Left', 'wpforms-lite' ),
			'bottom_center'                => esc_html__( 'Bottom Center', 'wpforms-lite' ),
			'bottom_right'                 => esc_html__( 'Bottom Right', 'wpforms-lite' ),
			'repeat'                       => esc_html__( 'Repeat', 'wpforms-lite' ),
			'no_repeat'                    => esc_html__( 'No Repeat', 'wpforms-lite' ),
			'repeat_x'                     => esc_html__( 'Repeat Horizontal', 'wpforms-lite' ),
			'repeat_y'                     => esc_html__( 'Repeat Vertical', 'wpforms-lite' ),
			'tile'                         => esc_html__( 'Tile', 'wpforms-lite' ),
			'cover'                        => esc_html__( 'Cover', 'wpforms-lite' ),
			'dimensions'                   => esc_html__( 'Dimensions', 'wpforms-lite' ),
			'width'                        => esc_html__( 'Width', 'wpforms-lite' ),
			'height'                       => esc_html__( 'Height', 'wpforms-lite' ),
			'button_color_notice'          => esc_html__( 'Also used for other fields like Multiple Choice, Checkboxes, Rating, and NPS Survey.', 'wpforms-lite' ),
			'advanced'                     => esc_html__( 'Advanced', 'wpforms-lite' ),
			'additional_css_classes'       => esc_html__( 'Additional CSS Classes', 'wpforms-lite' ),
			'form_selected'                => esc_html__( 'Form', 'wpforms-lite' ),
			'show_title'                   => esc_html__( 'Show Title', 'wpforms-lite' ),
			'show_description'             => esc_html__( 'Show Description', 'wpforms-lite' ),
			'panel_notice_head'            => esc_html__( 'Heads up!', 'wpforms-lite' ),
			'panel_notice_text'            => esc_html__( 'Do not forget to test your form.', 'wpforms-lite' ),
			'panel_notice_link'            => esc_url( wpforms_utm_link( 'https://wpforms.com/docs/how-to-properly-test-your-wordpress-forms-before-launching-checklist/', 'gutenberg' ) ),
			'panel_notice_link_text'       => esc_html__( 'Check out our complete guide!', 'wpforms-lite' ),
			'update_wp_notice_head'        => esc_html__( 'Want to customize your form styles without editing CSS?', 'wpforms-lite' ),
			'update_wp_notice_text'        => esc_html__( 'Update WordPress to the latest version to use our modern markup and unlock the controls below.', 'wpforms-lite' ),
			'update_wp_notice_link'        => esc_url( wpforms_utm_link( 'https://wpforms.com/docs/styling-your-forms/', 'Block Settings', 'Form Styles Documentation' ) ),
			'learn_more'                   => esc_html__( 'Learn more', 'wpforms-lite' ),
			'use_modern_notice_head'       => esc_html__( 'Want to customize your form styles without editing CSS?', 'wpforms-lite' ),
			'use_modern_notice_text'       => esc_html__( 'Enable modern markup in your WPForms settings to unlock the controls below.', 'wpforms-lite' ),
			'use_modern_notice_link'       => esc_url( wpforms_utm_link( 'https://wpforms.com/docs/styling-your-forms/', 'Block Settings', 'Form Styles Documentation' ) ),
			'lead_forms_panel_notice_head' => esc_html__( 'Form Styles are disabled because Lead Form Mode is turned on.', 'wpforms-lite' ),
			'lead_forms_panel_notice_text' => esc_html__( 'To change the styling for this form, open it in the form builder and edit the options in the Lead Forms settings.', 'wpforms-lite' ),
			'size'                         => esc_html__( 'Size', 'wpforms-lite' ),
			'padding'                      => esc_html__( 'Padding', 'wpforms-lite' ),
			'background'                   => esc_html__( 'Background', 'wpforms-lite' ),
			'border'                       => esc_html__( 'Border', 'wpforms-lite' ),
			'text'                         => esc_html__( 'Text', 'wpforms-lite' ),
			'menu'                         => esc_html__( 'Menu', 'wpforms-lite' ),
			'image'                        => esc_html__( 'Image', 'wpforms-lite' ),
			'media_library'                => esc_html__( 'Media Library', 'wpforms-lite' ),
			'choose_image'                 => esc_html__( 'Choose Image', 'wpforms-lite' ),
			'stock_photo'                  => esc_html__( 'Stock Photo', 'wpforms-lite' ),
			'border_radius'                => esc_html__( 'Border Radius', 'wpforms-lite' ),
			'border_size'                  => esc_html__( 'Border Size', 'wpforms-lite' ),
			'border_style'                 => esc_html__( 'Border Style', 'wpforms-lite' ),
			'none'                         => esc_html__( 'None', 'wpforms-lite' ),
			'solid'                        => esc_html__( 'Solid', 'wpforms-lite' ),
			'dashed'                       => esc_html__( 'Dashed', 'wpforms-lite' ),
			'dotted'                       => esc_html__( 'Dotted', 'wpforms-lite' ),
			'double'                       => esc_html__( 'Double', 'wpforms-lite' ),
			'shadow_size'                  => esc_html__( 'Shadow', 'wpforms-lite' ),
			'border_width'                 => esc_html__( 'Border Size', 'wpforms-lite' ),
			'border_color'                 => esc_html__( 'Border', 'wpforms-lite' ),
			'colors'                       => esc_html__( 'Colors', 'wpforms-lite' ),
			'label'                        => esc_html__( 'Label', 'wpforms-lite' ),
			'sublabel_hints'               => esc_html__( 'Sublabel & Hint', 'wpforms-lite' ),
			'error_message'                => esc_html__( 'Error Message', 'wpforms-lite' ),
			'small'                        => esc_html__( 'Small', 'wpforms-lite' ),
			'medium'                       => esc_html__( 'Medium', 'wpforms-lite' ),
			'large'                        => esc_html__( 'Large', 'wpforms-lite' ),
			'btn_yes'                      => esc_html__( 'Yes', 'wpforms-lite' ),
			'btn_no'                       => esc_html__( 'No', 'wpforms-lite' ),
			'copy_paste_settings'          => esc_html__( 'Copy / Paste Style Settings', 'wpforms-lite' ),
			'copy_paste_error'             => esc_html__( 'There was an error parsing your JSON code. Please check your code and try again.', 'wpforms-lite' ),
			'copy_paste_notice'            => esc_html__( 'If you\'ve copied style settings from another form, you can paste them here to add the same styling to this form. Any current style settings will be overwritten.', 'wpforms-lite' ),
			'custom_css'                   => esc_html__( 'Custom CSS', 'wpforms-lite' ),
			'custom_css_notice'            => esc_html__( 'Further customize the look of this form without having to edit theme files.', 'wpforms-lite' ),
			// Translators: %1$s: Opening strong tag, %2$s: Closing strong tag.
			'wpforms_empty_info'           => sprintf( esc_html__( 'You can use %1$sWPForms%2$s to build contact forms, surveys, payment forms, and more with just a few clicks.', 'wpforms-lite' ), '<strong>','</strong>' ),
			// Translators: %1$s: Opening anchor tag, %2$s: Closing anchor tag.
			'wpforms_empty_help'           => sprintf( esc_html__( 'Need some help? Check out our %1$scomprehensive guide.%2$s', 'wpforms-lite' ), '<a target="_blank" href="' . esc_url( wpforms_utm_link( 'https://wpforms.com/docs/creating-first-form/', 'gutenberg', 'Create Your First Form Documentation' ) ) . '">','</a>' ),
			'other_styles'                 => esc_html__( 'Other Styles', 'wpforms-lite' ),
			'page_break'                   => esc_html__( 'Page Break', 'wpforms-lite' ),
			'rating'                       => esc_html__( 'Rating', 'wpforms-lite' ),
			'heads_up'                     => esc_html__( 'Heads Up!', 'wpforms-lite' ),
			'form_not_available_message'   => esc_html__( 'It looks like the form you had selected is in the Trash or has been permanently deleted.', 'wpforms-lite' ),
		];

		return [
			'logo_url'          => WPFORMS_PLUGIN_URL . 'assets/images/wpforms-logo.svg',
			'block_preview_url' => WPFORMS_PLUGIN_URL . 'assets/images/integrations/gutenberg/block-preview.png',
			'block_empty_url'   => WPFORMS_PLUGIN_URL . 'assets/images/empty-states/no-forms.svg',
			'route_namespace'   => RestApi::ROUTE_NAMESPACE,
			'wpnonce'           => wp_create_nonce( 'wpforms-gutenberg-form-selector' ),
			'urls'              => [
				'form_url'    => admin_url( 'admin.php?page=wpforms-builder&view=fields&form_id={ID}' ),
				'entries_url' => admin_url( 'admin.php?view=list&page=wpforms-entries&form_id={ID}' ),
			],
			'forms'             => $this->get_form_list(),
			'strings'           => $strings,
			'isAdmin'           => current_user_can( 'manage_options' ),
			'isPro'             => wpforms()->is_pro(),
			'defaults'          => self::DEFAULT_ATTRIBUTES,
			'is_modern_markup'  => $this->render_engine === 'modern',
			'is_full_styling'   => $this->disable_css_setting === 1,
			'wpforms_guide'     => esc_url( wpforms_utm_link( 'https://wpforms.com/docs/creating-first-form/', 'gutenberg', 'Create Your First Form Documentation' ) ),
			'get_started_url'   => esc_url( admin_url( 'admin.php?page=wpforms-builder' ) ),
			'sizes'             => [
				'field-size'            => CSSVars::FIELD_SIZE,
				'label-size'            => CSSVars::LABEL_SIZE,
				'button-size'           => CSSVars::BUTTON_SIZE,
				'container-shadow-size' => CSSVars::CONTAINER_SHADOW_SIZE,
			],
		];
	}

	/**
	 * Get the form list.
	 *
	 * @since 1.8.8
	 *
	 * @return array
	 * @noinspection NullPointerExceptionInspection
	 */
	public function get_form_list(): array {

		$forms = wpforms()->obj( 'form' )->get( '', [ 'order' => 'DESC' ] );

		if ( empty( $forms ) ) {
			return [];
		}

		return array_map(
			static function ( $form ) {
				$form->post_title = htmlspecialchars_decode( $form->post_title, ENT_QUOTES );
				$max_length       = 47;
				$form->post_title = trim( mb_substr( trim( $form->post_title ), 0, $max_length ) );
				$form->post_title = mb_strlen( $form->post_title ) === $max_length ? $form->post_title . '…' : $form->post_title;

				return $form;
			},
			$forms
		);
	}

	/**
	 * Filter form action.
	 *
	 * @since 1.8.8
	 *
	 * @param string|mixed $action    Form action.
	 * @param array|mixed  $form_data Form data.
	 *
	 * @return string
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function form_action_filter( $action, $form_data ): string {

		if ( $this->is_gb_editor() ) {

			// Remove inappropriate form action URL that contains all the block attributes.
			$action = '';
		}

		return (string) $action;
	}

	/**
	 * Get form HTML to display in a WPForms Gutenberg block.
	 *
	 * @since 1.4.8
	 *
	 * @param array|mixed $attr Attributes passed by WPForms Gutenberg block.
	 *
	 * @return string
	 */
	public function get_form_html( $attr ): string {

		$attr = (array) $attr;

		$id = ! empty( $attr['formId'] ) ? absint( $attr['formId'] ) : 0;

		$this->current_form_id = $id;

		if ( empty( $id ) ) {
			return '';
		}

		if ( $this->is_gb_editor() ) {
			$this->disable_fields_in_gb_editor();
		}

		$title = ! empty( $attr['displayTitle'] );
		$desc  = ! empty( $attr['displayDesc'] );

		$this->add_class_callback( $id, $attr );

		// Maybe override block attributes with the theme settings.
		$attr = $this->maybe_override_block_attributes( $attr );

		// Get block content.
		$content = $this->get_content( $id, $title, $desc, $attr );

		// phpcs:disable WPForms.PHP.ValidateHooks.InvalidHookName

		/**
		 * Filter Gutenberg block content.
		 *
		 * @since 1.5.8.2
		 *
		 * @param string $content Block content.
		 * @param int    $id      Form id.
		 */
		return (string) apply_filters( 'wpforms_gutenberg_block_form_content', $content, $id );

		// phpcs:enable WPForms.PHP.ValidateHooks.InvalidHookName
	}

	/**
	 * Maybe override block attributes.
	 *
	 * This method is used to override block attributes with the theme settings.
	 *
	 * @since 1.8.8
	 *
	 * @param array $attr Attributes passed by WPForms Gutenberg block.
	 *
	 * @return array
	 */
	private function maybe_override_block_attributes( array $attr ): array {

		$theme_slug = (string) ( $attr['theme'] ?? '' );

		// Previously added blocks (FS 1.0) don't have the themeName attribute.
		// To preserve existing styling of such old blocks, we shouldn't override attributes.
		if ( ! isset( $attr['themeName'] ) || ( empty( $attr['themeName'] ) && $theme_slug === 'default' ) ) {
			return $attr;
		}

		if ( $theme_slug === '' ) {
			$theme_slug = $this->get_theme_slug( $attr );
		}

		$theme_data = $this->themes_data_obj->get_theme( $theme_slug );

		// Theme doesn't exist, let's return.
		if ( ! $theme_data ) {
			return $attr;
		}

		// Override block attributes with the theme settings.
		return array_merge( $attr, $theme_data['settings'] );
	}

	/**
	 * Get the theme slug.
	 *
	 * @since 1.9.7
	 *
	 * @param array $attr Attributes passed by WPForms Gutenberg block.
	 *
	 * @return string
	 */
	private function get_theme_slug( array $attr ): string {

		$form_handler = wpforms()->obj( 'form' );

		if ( ! $form_handler ) {
			return 'default';
		}

		$form_id   = (int) $attr['formId'];
		$form_data = $form_handler->get( $form_id, [ 'content_only' => true ] );

		if ( empty( $form_data['settings']['themes']['wpformsTheme'] ) ) {
			return 'default';
		}

		return $form_data['settings']['themes']['wpformsTheme'];
	}

	/**
	 * Add class callback.
	 *
	 * @since 1.8.1
	 *
	 * @param int   $id   Form id.
	 * @param array $attr Form attributes.
	 *
	 * @return void
	 */
	private function add_class_callback( int $id, array $attr ): void { // phpcs:ignore WPForms.PHP.HooksMethod.InvalidPlaceForAddingHooks

		$class_callback = static function ( $classes, $form_data ) use ( $id, $attr ) {

			if ( (int) $form_data['id'] !== $id ) {
				return $classes;
			}

			$cls = [];

			// Add custom class to form container.
			if ( ! empty( $attr['className'] ) ) {
				$cls = array_map( 'esc_attr', explode( ' ', $attr['className'] ) );
			}

			// Add classes to identify that the form displays inside the block.
			$cls[] = 'wpforms-block';

			if ( ! empty( $attr['clientId'] ) ) {
				$cls[] = 'wpforms-block-' . $attr['clientId'];
			}

			return array_unique( array_merge( $classes, $cls ) );
		};

		if ( empty( $this->callbacks[ $id ] ) ) {
			add_filter( 'wpforms_frontend_container_class', $class_callback, 10, 2 );
		}

		$this->callbacks[ $id ][] = $class_callback;
	}

	/**
	 * Get content.
	 *
	 * @since 1.8.1
	 *
	 * @param int   $id    Form id.
	 * @param bool  $title Form title is not empty.
	 * @param bool  $desc  Form desc is not empty.
	 * @param array $attr  Form attributes.
	 *
	 * @return string
	 * @noinspection JSUnresolvedReference
	 */
	private function get_content( int $id, bool $title, bool $desc, array $attr ): string {

		/**
		 * Filter allow render block content flag.
		 *
		 * @since 1.8.8
		 *
		 * @param bool $allow_render Allow render flag. Defaults to `true`.
		 */
		$allow_render = (bool) apply_filters( 'wpforms_integrations_gutenberg_form_selector_allow_render', true );

		if ( ! $allow_render ) {
			return '';
		}

		ob_start();

		// phpcs:disable WPForms.PHP.ValidateHooks.InvalidHookName

		/**
		 * Fires before Gutenberg block output.
		 *
		 * @since 1.5.8.2
		 */
		do_action( 'wpforms_gutenberg_block_before' );

		/**
		 * Filter block title display flag.
		 *
		 * @since 1.5.8.2
		 *
		 * @param bool $title Title display flag.
		 * @param int  $id    Form id.
		 */
		$title = (bool) apply_filters( 'wpforms_gutenberg_block_form_title', $title, $id );

		/**
		 * Filter block description display flag.
		 *
		 * @since 1.5.8.2
		 *
		 * @param bool $desc Description display flag.
		 * @param int  $id   Form id.
		 */
		$desc = (bool) apply_filters( 'wpforms_gutenberg_block_form_desc', $desc, $id );

		$this->output_css_vars( $attr );
		$this->output_custom_css( $attr );

		wpforms_display( $id, $title, $desc );

		/**
		 * Fires after Gutenberg block output.
		 *
		 * @since 1.5.8.2
		 */
		do_action( 'wpforms_gutenberg_block_after' );

		// phpcs:enable WPForms.PHP.ValidateHooks.InvalidHookName

		$content = (string) ob_get_clean();

		if ( ! $this->is_gb_editor() ) {
			return $content;
		}

		if ( empty( $content ) ) {
			return '<div class="components-placeholder"><div class="components-placeholder__label"></div>' .
						'<div class="components-placeholder__fieldset">' .
						esc_html__( 'The form cannot be displayed.', 'wpforms-lite' ) .
						'</div></div>';
		}

		/**
		 * Unfortunately, the inline 'script' tag cannot be executed in the GB editor.
		 * This is the hacky way to trigger custom event on form loaded in the Block Editor / GB / FSE.
		 */

		// phpcs:disable WordPress.PHP.DevelopmentFunctions.error_log_var_export
		$content .= sprintf(
			// language=JavaScript
			'<img src="data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==" onLoad="
				window.top.dispatchEvent(
					new CustomEvent(
						\'wpformsFormSelectorFormLoaded\',
						{
							detail: {
								formId: %1$s,
								title: %2$s,
								desc: %3$s,
								block: this.closest( \'.wp-block\' )
							}
						}
					)
				);
			" class="wpforms-pix-trigger" alt="">',
			absint( $id ),
			var_export( $title, true ),
			var_export( $desc, true )
		);

		// phpcs:enable WordPress.PHP.DevelopmentFunctions.error_log_var_export

		return $content;
	}

	/**
	 * Checking if is Gutenberg REST API call.
	 *
	 * @since 1.5.7
	 *
	 * @return bool True if is Gutenberg REST API call.
	 */
	public function is_gb_editor(): bool {

		// TODO: Find a better way to check if is GB editor API call.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return defined( 'REST_REQUEST' ) && REST_REQUEST && ! empty( $_REQUEST['context'] ) && $_REQUEST['context'] === 'edit';
	}

	/**
	 * Disable form fields if called from the Gutenberg editor.
	 *
	 * @since 1.7.5
	 *
	 * @return void
	 */
	private function disable_fields_in_gb_editor(): void { // phpcs:ignore WPForms.PHP.HooksMethod.InvalidPlaceForAddingHooks

		add_filter(
			'wpforms_frontend_container_class',
			static function ( $classes ) {

				$classes[] = 'wpforms-gutenberg-form-selector';

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
			},
			30
		);
	}

	/**
	 * Output CSS variables for the particular form.
	 *
	 * @since 1.8.1
	 *
	 * @param array $attr Attributes passed by WPForms Gutenberg block.
	 */
	private function output_css_vars( array $attr ): void {

		if ( empty( $this->css_vars_obj ) || ! method_exists( $this->css_vars_obj, 'get_vars' ) ) {
			return;
		}

		if ( $this->render_engine === 'classic' || $this->disable_css_setting !== 1 ) {
			return;
		}

		$css_vars = $this->css_vars_obj->get_customized_css_vars( $attr );

		if ( empty( $css_vars ) ) {
			return;
		}

		$style_id = "#wpforms-css-vars-{$attr['formId']}-block-{$attr['clientId']}";

		/**
		 * Filter the CSS selector for output CSS variables for styling the GB block form.
		 *
		 * @since 1.8.1
		 *
		 * @param string $selector The CSS selector for output CSS variables for styling the GB block form.
		 * @param array  $attr     Attributes passed by WPForms Gutenberg block.
		 * @param array  $css_vars CSS variables data.
		 */
		$vars_selector = apply_filters(
			'wpforms_integrations_gutenberg_form_selector_output_css_vars_selector',
			"#wpforms-{$attr['formId']}.wpforms-block-{$attr['clientId']}",
			$attr,
			$css_vars
		);

		$style_id      = rtrim( $style_id, '-' );
		$vars_selector = rtrim( $vars_selector, '-' );

		$this->css_vars_obj->output_selector_vars( $vars_selector, $css_vars, $style_id, $this->current_form_id );
	}

	/**
	 * Output custom CSS styles.
	 *
	 * @since 1.8.8
	 *
	 * @param array $attr Attributes passed by WPForms Gutenberg block.
	 */
	private function output_custom_css( array $attr ): void {

		if ( wpforms_get_render_engine() === 'classic' ) {
			return;
		}

		$custom_css = trim( $attr['customCss'] ?? '' );

		if ( empty( $custom_css ) ) {
			return;
		}

		$style_id = "#wpforms-custom-css-{$attr['formId']}-block-{$attr['clientId']}";

		printf(
			'<style id="%1$s">
				%2$s
			</style>',
			sanitize_key( $style_id ),
			wp_strip_all_tags( $custom_css ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		);
	}

	/**
	 * Disable loading media for the richtext editor for edit action to prevent script conflicts.
	 *
	 * @since 1.9.1
	 *
	 * @param bool|mixed $media_enabled Whether to enable media.
	 * @param array      $field         Field data.
	 *
	 * @return bool
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function disable_richtext_media( $media_enabled, array $field ): bool {

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! empty( $_REQUEST['action'] ) && $_REQUEST['action'] === 'edit' && is_admin() ) {
			return false;
		}

		return (bool) $media_enabled;
	}

	/**
	 * Get block API version based on WP core version.
	 *
	 * @since 1.9.3
	 *
	 * @return int Block API version.
	 */
	private function get_block_api_version(): int {

		if ( $this->is_legacy_block() ) {
			return 1;
		}

		return version_compare( $GLOBALS['wp_version'], '6.3', '<' ) ? 2 : 3;
	}
}
