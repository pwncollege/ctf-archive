<?php

// phpcs:disable Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpUndefinedNamespaceInspection */
/** @noinspection PhpUndefinedClassInspection */
/** @noinspection PhpUndefinedMethodInspection */
// phpcs:enable Generic.Commenting.DocComment.MissingShort

namespace WPForms\Integrations\Elementor;

use Elementor\Plugin;
use Elementor\Controls_Manager;
use Exception;
use WPForms\Frontend\CSSVars;

/**
 * WPForms modern widget for Elementor page builder.
 *
 * @since 1.8.3
 */
class WidgetModern extends Widget {

	/**
	 * Size options for widget settings.
	 *
	 * @since 1.8.3
	 *
	 * @var array
	 */
	protected $size_options;

	/**
	 * Border type options for widget settings.
	 *
	 * @since 1.9.6
	 *
	 * @var array
	 */
	private $border_options;

	/**
	 * Instance of CSSVars class.
	 *
	 * @since 1.8.3
	 *
	 * @var CSSVars
	 */
	protected $css_vars_obj;

	/**
	 * Widget constructor.
	 *
	 * @since 1.8.3
	 *
	 * @param array $data Widget data.
	 * @param array $args Widget arguments.
	 *
	 * @throws Exception If arguments are missing when initializing a full widget.
	 * @noinspection PhpMissingParamTypeInspection
	 */
	public function __construct( $data = [], $args = null ) {

		parent::__construct( $data, $args );

		$this->load();
	}

	/**
	 * Load widget.
	 *
	 * @since 1.8.3
	 */
	private function load(): void {

		$this->size_options = [
			'small'  => esc_html__( 'Small', 'wpforms-lite' ),
			'medium' => esc_html__( 'Medium', 'wpforms-lite' ),
			'large'  => esc_html__( 'Large', 'wpforms-lite' ),
		];

		$this->border_options = [
			'none'   => esc_html__( 'None', 'wpforms-lite' ),
			'solid'  => esc_html__( 'Solid', 'wpforms-lite' ),
			'dashed' => esc_html__( 'Dashed', 'wpforms-lite' ),
			'dotted' => esc_html__( 'Dotted', 'wpforms-lite' ),
		];

		$this->css_vars_obj = wpforms()->obj( 'css_vars' );
	}

	/**
	 * Register widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @since 1.8.3
	 */
	protected function register_controls() {

		$this->content_controls();
		$this->style_controls();
	}

	/**
	 * Register widget controls for the Style section.
	 *
	 * Adds different input fields into the "Style" section to allow the user to change and customize the widget style
	 * settings.
	 *
	 * @since 1.8.3
	 */
	private function style_controls(): void {

		$this->add_theme_style_controls();

		if ( $this->is_admin() ) {
			$this->add_field_style_controls();
			$this->add_label_style_controls();
			$this->add_button_style_controls();
			$this->add_container_style_controls();
			$this->add_background_style_controls();
			$this->add_other_style_controls();
		}

		$this->add_advanced_style_controls();
	}

	/**
	 * Register widget controls for the Theme Style section.
	 *
	 * @since 1.9.6
	 */
	private function add_theme_style_controls(): void {

		$this->start_controls_section(
			'themes',
			[
				'label' => esc_html__( 'Themes', 'wpforms-lite' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'lead_forms_notice',
			[
				'show_label'      => false,
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => sprintf(
					'<strong>%s</strong>%s',
					esc_html__( 'Form Styles are disabled because Lead Form Mode is turned on.', 'wpforms-lite' ),
					esc_html__( 'To change the styling for this form, open it in the form builder and edit the options in the Lead Forms settings.', 'wpforms-lite' )
				),
				'classes'         => 'wpforms-elementor-lead-forms-notice',
				'content_classes' => 'elementor-panel-alert elementor-panel-alert-warning',
			]
		);

		$this->add_control(
			'wpformsTheme',
			[
				'type'    => 'wpforms_themes',
				'default' => 'default',
			]
		);

		if ( $this->is_admin() ) {
			$this->add_control(
				'isCustomTheme',
				[
					'type' => Controls_Manager::HIDDEN,
				]
			);

			$this->add_control(
				'isMigrated',
				[
					'type'    => Controls_Manager::HIDDEN,
					'default' => 'false',
				]
			);

			$this->add_control(
				'customThemeName',
				[
					'type'      => Controls_Manager::TEXT,
					'label'     => esc_html__( 'Theme Name', 'wpforms-lite' ),
					'ai'        => [
						'active' => false,
					],
					'condition' => [
						'isCustomTheme!' => '',
					],
				]
			);

			$this->add_control(
				'deleteThemeButton',
				[
					'type'        => Controls_Manager::BUTTON,
					'event'       => 'WPFormsDeleteThemeButtonClick',
					'button_type' => 'danger',
					'text'        => esc_html__( 'DELETE THEME', 'wpforms-lite' ),
					'condition'   => [
						'isCustomTheme!' => '',
					],
				]
			);
		}

		$this->end_controls_section();
	}

	/**
	 * Register widget controls for the Field Style section.
	 *
	 * Adds controls to the "Field Styles" section of the Widget Style settings.
	 *
	 * @since 1.8.3
	 */
	protected function add_field_style_controls(): void {

		$this->start_controls_section(
			'field_styles',
			[
				'label' => esc_html__( 'Field Styles', 'wpforms-lite' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'fieldSize',
			[
				'label'   => esc_html__( 'Size', 'wpforms-lite' ),
				'type'    => Controls_Manager::SELECT,
				'options' => $this->size_options,
				'default' => 'medium',
			]
		);

		$this->add_control(
			'fieldBorderStyle',
			[
				'label'   => esc_html__( 'Border', 'wpforms-lite' ),
				'type'    => Controls_Manager::SELECT,
				'options' => $this->border_options,
				'default' => 'solid',
			]
		);

		$this->add_control(
			'fieldBorderSize',
			[
				'label'     => esc_html__( 'Border Size (px)', 'wpforms-lite' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => '1',
				'min'       => '0',
				'condition' => [
					'fieldBorderStyle!' => 'none',
				],
			]
		);

		$this->add_control(
			'fieldBorderRadius',
			[
				'label'   => esc_html__( 'Border Radius (px)', 'wpforms-lite' ),
				'type'    => Controls_Manager::NUMBER,
				'min'     => '0',
				'default' => '3',
			]
		);

		$this->add_control(
			'fieldBackgroundColor',
			[
				'label'   => esc_html__( 'Background', 'wpforms-lite' ),
				'type'    => Controls_Manager::COLOR,
				'default' => CSSVars::ROOT_VARS['field-background-color'],
			]
		);

		$this->add_control(
			'fieldBorderColor',
			[
				'label'   => esc_html__( 'Border', 'wpforms-lite' ),
				'type'    => Controls_Manager::COLOR,
				'alpha'   => true,
				'default' => CSSVars::ROOT_VARS['field-border-color'],
			]
		);

		$this->add_control(
			'fieldTextColor',
			[
				'label'   => esc_html__( 'Text', 'wpforms-lite' ),
				'type'    => Controls_Manager::COLOR,
				'alpha'   => true,
				'default' => CSSVars::ROOT_VARS['field-text-color'],
			]
		);

		$this->add_control(
			'fieldMenuColor',
			[
				'type'    => Controls_Manager::HIDDEN,
				'default' => CSSVars::ROOT_VARS['field-menu-color'],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Register widget controls for the Label Style section.
	 *
	 * Adds controls to the "Label Styles" section of the Widget Style settings.
	 *
	 * @since 1.8.3
	 */
	private function add_label_style_controls(): void {

		$this->start_controls_section(
			'label_styles',
			[
				'label' => esc_html__( 'Label Styles', 'wpforms-lite' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'labelSize',
			[
				'label'   => esc_html__( 'Size', 'wpforms-lite' ),
				'type'    => Controls_Manager::SELECT,
				'options' => $this->size_options,
				'default' => 'medium',
			]
		);

		$this->add_control(
			'labelColor',
			[
				'label'   => esc_html__( 'Label', 'wpforms-lite' ),
				'type'    => Controls_Manager::COLOR,
				'alpha'   => true,
				'default' => CSSVars::ROOT_VARS['label-color'],
			]
		);

		$this->add_control(
			'labelSublabelColor',
			[
				'label'   => esc_html__( 'Sublabel & Hint', 'wpforms-lite' ),
				'type'    => Controls_Manager::COLOR,
				'alpha'   => true,
				'default' => CSSVars::ROOT_VARS['label-sublabel-color'],
			]
		);

		$this->add_control(
			'labelErrorColor',
			[
				'label'   => esc_html__( 'Error', 'wpforms-lite' ),
				'type'    => Controls_Manager::COLOR,
				'default' => CSSVars::ROOT_VARS['label-error-color'],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Register widget controls for the "Button Style" section.
	 *
	 * Adds controls to the "Button Styles" section of the Widget Style settings.
	 *
	 * @since 1.8.3
	 */
	private function add_button_style_controls(): void {

		$this->start_controls_section(
			'button_styles',
			[
				'label' => esc_html__( 'Button Styles', 'wpforms-lite' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'buttonSize',
			[
				'label'   => esc_html__( 'Size', 'wpforms-lite' ),
				'type'    => Controls_Manager::SELECT,
				'options' => $this->size_options,
				'default' => 'medium',
			]
		);

		$this->add_control(
			'buttonBorderStyle',
			[
				'label'   => esc_html__( 'Border', 'wpforms-lite' ),
				'type'    => Controls_Manager::SELECT,
				'options' => $this->border_options,
				'default' => CSSVars::ROOT_VARS['button-border-style'],
			]
		);

		$this->add_control(
			'buttonBorderSize',
			[
				'label'     => esc_html__( 'Border Size (px)', 'wpforms-lite' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => CSSVars::ROOT_VARS['button-border-size'],
				'min'       => '0',
				'condition' => [
					'buttonBorderStyle!' => 'none',
				],
			]
		);

		$this->add_control(
			'buttonBorderRadius',
			[
				'label'   => esc_html__( 'Border Radius (px)', 'wpforms-lite' ),
				'type'    => Controls_Manager::NUMBER,
				'min'     => '0',
				'default' => '3',
			]
		);

		$this->add_control(
			'buttonBackgroundColor',
			[
				'label'   => esc_html__( 'Background', 'wpforms-lite' ),
				'type'    => Controls_Manager::COLOR,
				'default' => CSSVars::ROOT_VARS['button-background-color'],
			]
		);

		$this->add_control(
			'buttonBorderColor',
			[
				'label'     => esc_html__( 'Border', 'wpforms-lite' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => CSSVars::ROOT_VARS['button-border-color'],
				'condition' => [
					'buttonBorderStyle!' => 'none',
				],
			]
		);

		$this->add_control(
			'buttonTextColor',
			[
				'label'   => esc_html__( 'Text', 'wpforms-lite' ),
				'type'    => Controls_Manager::COLOR,
				'default' => CSSVars::ROOT_VARS['button-text-color'],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Register widget controls for the "Container Style" section.
	 *
	 * @since 1.9.6
	 */
	private function add_container_style_controls(): void {

		$this->start_controls_section(
			'container_styles',
			[
				'label' => esc_html__( 'Container Styles', 'wpforms-lite' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'containerPadding',
			[
				'label'   => esc_html__( 'Padding (px)', 'wpforms-lite' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => CSSVars::ROOT_VARS['container-padding'],
				'min'     => '0',
			]
		);

		$this->add_control(
			'containerBorderStyle',
			[
				'label'   => esc_html__( 'Border', 'wpforms-lite' ),
				'type'    => Controls_Manager::SELECT,
				'options' => $this->border_options,
				'default' => CSSVars::ROOT_VARS['container-border-style'],
			]
		);

		$this->add_control(
			'containerBorderWidth',
			[
				'label'     => esc_html__( 'Border Size (px)', 'wpforms-lite' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => CSSVars::ROOT_VARS['container-border-width'],
				'min'       => '0',
				'condition' => [
					'containerBorderStyle!' => 'none',
				],
			]
		);

		$this->add_control(
			'containerBorderRadius',
			[
				'label'   => esc_html__( 'Border Radius (px)', 'wpforms-lite' ),
				'type'    => Controls_Manager::NUMBER,
				'min'     => '0',
				'default' => '3',
			]
		);

		$this->add_control(
			'containerShadowSize',
			[
				'label'   => esc_html__( 'Shadow', 'wpforms-lite' ),
				'type'    => Controls_Manager::SELECT,
				'options' => [
					'none'   => esc_html__( 'None', 'wpforms-lite' ),
					'small'  => esc_html__( 'Small', 'wpforms-lite' ),
					'medium' => esc_html__( 'Medium', 'wpforms-lite' ),
					'large'  => esc_html__( 'Large', 'wpforms-lite' ),
				],
				'default' => CSSVars::CONTAINER_SHADOW_SIZE['none']['box-shadow'],
			]
		);

		$this->add_control(
			'containerBorderColor',
			[
				'label'     => esc_html__( 'Border', 'wpforms-lite' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => CSSVars::ROOT_VARS['container-border-color'],
				'condition' => [
					'containerBorderStyle!' => 'none',
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Register widget controls for the "Background Style" section.
	 *
	 * @since 1.9.6
	 */
	private function add_background_style_controls(): void {

		$this->start_controls_section(
			'background_styles',
			[
				'label' => esc_html__( 'Background Styles', 'wpforms-lite' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'backgroundImage',
			[
				'label'   => esc_html__( 'Image', 'wpforms-lite' ),
				'type'    => Controls_Manager::SELECT,
				'options' => [
					'none'    => esc_html__( 'None', 'wpforms-lite' ),
					'library' => esc_html__( 'Media Library', 'wpforms-lite' ),
					'stock'   => esc_html__( 'Stock Photo', 'wpforms-lite' ),
				],
				'default' => 'none',
			]
		);

		$this->add_control(
			'backgroundPosition',
			[
				'label'     => esc_html__( 'Position', 'wpforms-lite' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					'top left'      => esc_html__( 'Top Left', 'wpforms-lite' ),
					'top center'    => esc_html__( 'Top Center', 'wpforms-lite' ),
					'top right'     => esc_html__( 'Top Right', 'wpforms-lite' ),
					'center left'   => esc_html__( 'Center Left', 'wpforms-lite' ),
					'center center' => esc_html__( 'Center Center', 'wpforms-lite' ),
					'center right'  => esc_html__( 'Center Right', 'wpforms-lite' ),
					'bottom left'   => esc_html__( 'Bottom Left', 'wpforms-lite' ),
					'bottom center' => esc_html__( 'Bottom Center', 'wpforms-lite' ),
					'bottom right'  => esc_html__( 'Bottom Right', 'wpforms-lite' ),
				],
				'default'   => CSSVars::ROOT_VARS['background-position'],
				'condition' => [
					'backgroundImage!' => 'none',
				],
			]
		);

		$this->add_control(
			'backgroundRepeat',
			[
				'label'     => esc_html__( 'Repeat', 'wpforms-lite' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					'no-repeat' => esc_html__( 'No Repeat', 'wpforms-lite' ),
					'repeat'    => esc_html__( 'Tile', 'wpforms-lite' ),
					'repeat-x'  => esc_html__( 'Repeat X', 'wpforms-lite' ),
					'repeat-y'  => esc_html__( 'Repeat Y', 'wpforms-lite' ),
				],
				'default'   => CSSVars::ROOT_VARS['background-repeat'],
				'condition' => [
					'backgroundImage!' => 'none',
				],
			]
		);

		$this->add_control(
			'backgroundSize',
			[
				'label'     => esc_html__( 'Size', 'wpforms-lite' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					'dimensions' => esc_html__( 'Dimensions', 'wpforms-lite' ),
					'cover'      => esc_html__( 'Cover', 'wpforms-lite' ),
				],
				'default'   => CSSVars::ROOT_VARS['background-size'],
				'condition' => [
					'backgroundImage!' => 'none',
				],
			]
		);

		$this->add_control(
			'backgroundSizeMode',
			[
				'type'    => Controls_Manager::HIDDEN,
				'default' => CSSVars::ROOT_VARS['background-size'],
			]
		);

		$this->add_control(
			'backgroundWidth',
			[
				'label'     => esc_html__( 'Width (px)', 'wpforms-lite' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => '100',
				'min'       => '0',
				'condition' => [
					'backgroundImage!' => 'none',
					'backgroundSize'   => 'dimensions',
				],
			]
		);

		$this->add_control(
			'backgroundHeight',
			[
				'label'     => esc_html__( 'Height (px)', 'wpforms-lite' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => '100',
				'min'       => '0',
				'condition' => [
					'backgroundImage!' => 'none',
					'backgroundSize'   => 'dimensions',
				],
			]
		);

		$this->add_control(
			'backgroundUrl',
			[
				'label'     => esc_html__( 'Choose Image', 'wpforms-lite' ),
				'type'      => Controls_Manager::MEDIA,
				'default'   => [
					'url' => CSSVars::ROOT_VARS['background-url'],
				],
				'ai'        => [
					'active' => false,
				],
				'separator' => 'after',
				'condition' => [
					'backgroundImage!' => 'none',
				],
			]
		);

		$this->add_control(
			'backgroundColor',
			[
				'label'   => esc_html__( 'Background', 'wpforms-lite' ),
				'type'    => Controls_Manager::COLOR,
				'default' => CSSVars::ROOT_VARS['background-color'],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Register widget controls for the "Other Styles" section.
	 *
	 * @since 1.9.6
	 */
	private function add_other_style_controls(): void {

		$this->start_controls_section(
			'other_styles',
			[
				'label' => esc_html__( 'Other Styles', 'wpforms-lite' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'pageBreakColor',
			[
				'label'   => esc_html__( 'Page Break', 'wpforms-lite' ),
				'type'    => Controls_Manager::COLOR,
				'default' => CSSVars::ROOT_VARS['page-break-color'],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Register widget controls for the "Advanced" section.
	 *
	 * Adds controls to the "Button Styles" section of the Widget Style settings.
	 *
	 * @since 1.8.3
	 */
	private function add_advanced_style_controls(): void {

		$this->start_controls_section(
			'advanced',
			[
				'label' => esc_html__( 'Advanced', 'wpforms-lite' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'className',
			[
				'label'        => esc_html__( 'Additional Classes', 'wpforms-lite' ),
				'type'         => Controls_Manager::TEXT,
				'description'  => esc_html__( 'Separate multiple classes with spaces.', 'wpforms-lite' ),
				'ai'           => [
					'active' => false,
				],
				'prefix_class' => '', // Prevents re-rendering of the widget.
			]
		);
		if ( $this->is_admin() ) {
			$this->add_control(
				'ACDivider',
				[
					'type' => Controls_Manager::DIVIDER,
				]
			);

			$this->add_control(
				'copyPasteJsonValue',
				[
					'label'       => esc_html__( 'Copy / Paste Style Settings', 'wpforms-lite' ),
					'type'        => Controls_Manager::TEXTAREA,
					'description' => esc_html__( 'If you\'ve copied style settings from another form, you can paste them here to add the same styling to this form. Any current style settings will be overwritten.', 'wpforms-lite' ),
					'ai'          => [
						'active' => false,
					],
				]
			);

			$this->add_control(
				'CPDivider',
				[
					'type' => Controls_Manager::DIVIDER,
				]
			);
		}

		$this->end_controls_section();
	}

	/**
	 * Render widget output on the frontend.
	 *
	 * @since 1.8.3
	 */
	protected function render_frontend() {

		if ( empty( $this->css_vars_obj ) ) {
			return;
		}

		$widget_id      = $this->get_id();
		$attr           = $this->get_settings_for_display();
		$css_vars       = $this->css_vars_obj->get_customized_css_vars( $attr );
		$custom_classes = ! empty( $attr['className'] ) ? trim( $attr['className'] ) : '';

		if ( ! empty( $css_vars ) ) {

			$style_id = 'wpforms-css-vars-elementor-widget-' . $widget_id;

			/**
			 * Filter the CSS selector for output CSS variables for styling the form in Elementor widget.
			 *
			 * @since 1.8.3
			 *
			 * @param string $selector The CSS selector for output CSS variables for styling the Elementor Widget.
			 * @param array  $attr     Attributes passed by Elementor Widget.
			 * @param array  $css_vars CSS variables data.
			 */
			$vars_selector = apply_filters(
				'wpforms_integrations_elementor_widget_modern_output_css_vars_selector',
				".elementor-widget-wpforms.elementor-element-{$widget_id}",
				$attr,
				$css_vars
			);

			$this->css_vars_obj->output_selector_vars( $vars_selector, $css_vars, $style_id );
		}

		// Add custom classes.
		if ( $custom_classes ) {
			$this->add_render_attribute(
				'_wrapper',
				[
					'class' => [
						$custom_classes,
					],
				]
			);
		}

		// Render selected form.
		$this->render_form();
	}

	/**
	 * Get settings for display.
	 *
	 * @since 1.8.3
	 *
	 * @param string|null $setting_key Optional. The key of the requested setting. Default is null.
	 *
	 * @return mixed The settings.
	 */
	public function get_settings_for_display( $setting_key = null ) {

		$settings = parent::get_settings_for_display( $setting_key );

		if ( ! empty( $setting_key ) ) {
			return $settings;
		}

		$settings = $this->remove_empty_settings( $settings );
		$settings = $this->apply_dimension_settings( $settings );
		$settings = $this->apply_complex_settings( $settings );

		if ( isset( $settings['__globals__'] ) ) {
			$settings = $this->check_global_styles( $settings );
		}

		return $settings;
	}

	/**
	 * Remove empty settings.
	 *
	 * @since 1.9.6
	 *
	 * @param mixed $settings Widget settings.
	 *
	 * @return mixed Updated settings.
	 */
	private function remove_empty_settings( $settings ) {

		if ( ! is_array( $settings ) ) {
			return $settings;
		}

		return array_filter(
			$settings,
			static function ( $value ) {

				return ! empty( $value );
			}
		);
	}

	/**
	 * Apply complex settings values.
	 *
	 * @since 1.9.6
	 *
	 * @param mixed $settings Widget settings.
	 *
	 * @return mixed Updated settings.
	 */
	private function apply_complex_settings( $settings ) {

		if ( isset( $settings['backgroundUrl'] ) && is_array( $settings['backgroundUrl'] ) ) {
			$image_url                 = $settings['backgroundUrl']['url'] ?? '';
			$settings['backgroundUrl'] = 'url( ' . $image_url . ' )';
		}

		if ( isset( $settings['backgroundSize'] ) && $settings['backgroundSize'] === 'dimensions' ) {
			$bg_width  = $settings['backgroundWidth'] ?? CSSVars::ROOT_VARS['background-width'];
			$bg_height = $settings['backgroundHeight'] ?? CSSVars::ROOT_VARS['background-height'];

			$settings['backgroundSize'] = "{$bg_width} {$bg_height}";
		}

		return $settings;
	}

	/**
	 * Apply dimension settings with pixel units.
	 *
	 * @since 1.9.6
	 *
	 * @param mixed $settings Widget settings.
	 *
	 * @return mixed Updated settings with dimension values.
	 */
	private function apply_dimension_settings( $settings ) {

		$dimension_properties = [
			'fieldBorderRadius'     => 'field-border-radius',
			'fieldBorderSize'       => 'field-border-size',
			'buttonBorderRadius'    => 'button-border-radius',
			'buttonBorderSize'      => 'button-border-size',
			'containerPadding'      => 'container-padding',
			'containerBorderWidth'  => 'container-border-width',
			'containerBorderRadius' => 'container-border-radius',
			'backgroundWidth'       => 'background-width',
			'backgroundHeight'      => 'background-height',
		];

		foreach ( $dimension_properties as $property => $root_var ) {
			if ( ! isset( $settings[ $property ] ) ) {
				$settings[ $property ] = CSSVars::ROOT_VARS[ $root_var ];

				continue;
			}

			$value = (string) $settings[ $property ];

			if ( $value !== '' && substr( $value, -2 ) !== 'px' ) {
				$settings[ $property ] = $value . 'px';
			}
		}

		return $settings;
	}

	/**
	 * Check if global styles are used in colors controls and update its values with the real ones.
	 *
	 * @since 1.8.3
	 *
	 * @param mixed $settings Widget settings.
	 *
	 * @return mixed Updated settings.
	 */
	private function check_global_styles( $settings ) {

		$global_settings = $settings['__globals__'] ?? [];
		$kit             = Plugin::$instance->kits_manager->get_active_kit_for_frontend();
		$system_colors   = $kit->get_settings_for_display( 'system_colors' );
		$custom_colors   = $kit->get_settings_for_display( 'custom_colors' );
		$global_colors   = array_merge( $system_colors, $custom_colors );

		foreach ( $global_settings as $key => $value ) {
			if ( empty( $value ) ) {
				continue;
			}

			$color_id = str_replace( 'globals/colors?id=', '', $value );

			foreach ( $global_colors as $color ) {
				if ( $color['_id'] === $color_id ) {
					$settings[ $key ] = $color['color'];
				}
			}
		}

		return $settings;
	}

	/**
	 * Check if the user is an admin.
	 *
	 * @since 1.9.6
	 *
	 * @return bool True if the user is an admin, false otherwise.
	 */
	private function is_admin(): bool {

		return current_user_can( 'manage_options' );
	}
}
