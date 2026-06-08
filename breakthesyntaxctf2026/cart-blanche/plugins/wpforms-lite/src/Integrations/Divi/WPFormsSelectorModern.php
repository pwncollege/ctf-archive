<?php

// phpcs:disable Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpUndefinedNamespaceInspection */
/** @noinspection PhpUndefinedClassInspection */
// phpcs:enable Generic.Commenting.DocComment.MissingShort

namespace WPForms\Integrations\Divi;

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Framework\Utility\HTMLUtility;
use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\Module\Options\Css\CssStyle;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;
use ET\Builder\VisualBuilder\Assets\PackageBuildManager;
use WP_Post;
use WPForms\Integrations\Divi\Interfaces\FormsResolverInterface;
use WPForms\Integrations\Divi\Interfaces\LocalizedDataInterface;
use WPForms\Integrations\Divi\Traits\FormsResolverTrait;
use WPForms\Integrations\Divi\Traits\LocalizedDataTrait;
use WP_Block_Type_Registry;

/**
 * WPForms Divi 5 Module.
 *
 * @since 1.9.9
 */
class WPFormsSelectorModern implements DependencyInterface, LocalizedDataInterface, FormsResolverInterface {

	use LocalizedDataTrait;
	use FormsResolverTrait;

	/**
	 * Defines the module type identifier for the WPForms Divi form selector.
	 *
	 * @since 1.9.9
	 */
	private const MODULE_TYPE = 'wpforms/divi-form-selector';

	/**
	 * This function registers and initiates all the logic the class implements.
	 *
	 * @since 1.9.9
	 */
	public function load(): void {

		$this->hooks();
	}

	/**
	 * Registers a hook to enqueue visual builder assets before the Divi visual builder loads scripts.
	 *
	 * @since 1.9.9
	 */
	private function hooks(): void {

		add_action( 'init', [ $this, 'register_module' ] );
		add_action( 'divi_visual_builder_assets_before_enqueue_scripts', [ $this, 'enqueue_visual_builder_assets' ] );
	}

	/**
	 * Register module.
	 *
	 * @since 1.9.9
	 */
	public static function register_module(): void {
		// Path to module metadata that is shared between Frontend and Visual Builder.
		$module_json_folder_path = __DIR__;

		ModuleRegistration::register_module(
			$module_json_folder_path,
			[
				'render_callback' => [ __CLASS__, 'render_callback' ],
			]
		);
	}

	/**
	 * Adds a form into the provided options array with its ID as the key and label derived from the form's title.
	 *
	 * @since 1.9.9
	 *
	 * @param array   $options The option array to be updated.
	 * @param WP_Post $form    The form post object containing the ID and title.
	 *
	 * @return array The updated options array including the new form entry.
	 */
	public function add_form_in_options( array $options, WP_Post $form ): array {

		$options[ $form->ID ] = [
			'label' => htmlspecialchars_decode( $form->post_title, ENT_QUOTES ),
		];

		return $options;
	}

	/**
	 * Render callback for the module.
	 *
	 * @since 1.9.9
	 *
	 * @param array  $attrs    Module attributes.
	 * @param string $content  Module content.
	 * @param object $block    Block object.
	 * @param object $elements Elements helper object.
	 *
	 * @return string Rendered module HTML.
	 * @noinspection PhpUnusedParameterInspection
	 */
	public static function render_callback( array $attrs, string $content, object $block, object $elements ): string {

		$new_attrs = $block->parsed_block['attrs'];

		// Get attribute values through $attrs (proper way).
		$form_id    = absint( $new_attrs['formId']['desktop']['value'] ?? 0 );
		$show_title = isset( $new_attrs['showTitle']['desktop']['value'] ) && $new_attrs['showTitle']['desktop']['value'] === 'on';
		$show_desc  = isset( $new_attrs['showDescription']['desktop']['value'] ) && $new_attrs['showDescription']['desktop']['value'] === 'on';

		/**
		 * Filter whether to display the form title for the Divi module.
		 *
		 * @since 1.6.3
		 *
		 * @param bool $show_title Whether to show the form title.
		 * @param int  $form_id    Form ID.
		 */
		$show_title = (bool) apply_filters( 'wpforms_divi_builder_form_title', $show_title, $form_id ); // phpcs:disable WPForms.PHP.ValidateHooks.InvalidHookName

		/**
		 * Filter whether to display the form description for the Divi module.
		 *
		 * @since 1.6.3
		 *
		 * @param bool $show_desc Whether to show the form description.
		 * @param int  $form_id   Form ID.
		 */
		$show_desc = (bool) apply_filters( 'wpforms_divi_builder_form_desc', $show_desc, $form_id ); // phpcs:disable WPForms.PHP.ValidateHooks.InvalidHookName

		// If no form selected, return the empty string.
		if ( empty( $form_id ) ) {
			return '';
		}

		// Generate the form shortcode output.
		$form_output = do_shortcode(
			sprintf(
				'[wpforms id="%1$s" title="%2$s" description="%3$s"]',
				$form_id,
				$show_title,
				$show_desc
			)
		);

		// Wrap content in module_inner.
		$module_inner = HTMLUtility::render(
			[
				'tag'               => 'div',
				'attributes'        => [
					'class' => 'et_pb_module_inner',
				],
				'childrenSanitizer' => 'et_core_esc_previously',
				'children'          => $form_output,
			]
		);

		// Get style components. TODO: check if this is needed.
		$module_elements = $elements->style_components(
			[
				'attrName' => 'module',
			]
		);

		// Combine all components.
		$module_container_children = $module_elements . $module_inner;

		// Render the final module through Module::render().
		return Module::render(
			[
				'orderIndex'          => $block->parsed_block['orderIndex'],
				'storeInstance'       => $block->parsed_block['storeInstance'],
				'attrs'               => $new_attrs,
				'elements'            => $elements,
				'id'                  => $block->parsed_block['id'],
				'moduleClassName'     => 'wpforms_divi_form_selector',
				'name'                => $block->block_type->name,
				'classnamesFunction'  => [ __CLASS__, 'module_classnames' ],
				'moduleCategory'      => $block->block_type->category,
				'stylesComponent'     => [ __CLASS__, 'module_styles' ],
				'scriptDataComponent' => [ __CLASS__, 'module_script_data' ], // TODO: check if this is needed.
				'children'            => $module_container_children,
			]
		);
	}

	/**
	 * Module classnames function.
	 *
	 * @since 1.9.9
	 *
	 * @param array $args Arguments for generating classnames.
	 *
	 * @return array CSS classes.
	 * @noinspection PhpUnusedParameterInspection
	 */
	public static function module_classnames( array $args ): array {

		return [];
	}

	/**
	 * Module styles function.
	 *
	 * @since 1.9.9
	 *
	 * @param array $args Arguments for generating styles.
	 */
	public static function module_styles( array $args ): void {

		$attrs    = $args['attrs'] ?? [];
		$elements = $args['elements'];
		$settings = $args['settings'] ?? [];

		$default_printed_style_attrs = $args['defaultPrintedStyleAttrs'] ?? [];

		Style::add(
			[
				'id'            => $args['id'],
				'name'          => $args['name'],
				'orderIndex'    => $args['orderIndex'],
				'storeInstance' => $args['storeInstance'],
				'styles'        => [
					$elements->style(
						[
							'attrName'   => 'module',
							'styleProps' => [
								'defaultPrintedStyleAttrs' => $default_printed_style_attrs['module']['decoration'] ?? [],
								'disabledOn'               => [
									'disabledModuleVisibility' => $settings['disabledModuleVisibility'] ?? null,
								],
							],
						]
					),

					CssStyle::style(
                        [
	                        'selector'  => $args['orderClass'],
							'attr'      => $attrs['css'] ?? [],
							'cssFields' => self::get_custom_css(),
                        ]
                    ),
				],
			]
		);
	}

	/**
	 * Retrieves the custom CSS associated with the registered block type.
	 *
	 * @since 1.9.9
	 *
	 * @return array Custom CSS fields.
	 */
	private static function get_custom_css(): array {

		$instance = WP_Block_Type_Registry::get_instance();

		if ( ! $instance ) {
			return [];
		}

		$block_type = $instance->get_registered( self::MODULE_TYPE );

		// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		if ( ! $block_type || ! isset( $block_type->customCssFields ) ) {
			return [];
		}

		return $block_type->customCssFields;
		// phpcs:enable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
	}

	/**
	 * Module script data function.
	 *
	 * @since 1.9.9
	 *
	 * @param array $args Arguments for generating script data.
	 *
	 * @return array Script data.
	 * @noinspection PhpUnusedParameterInspection
	 */
	public static function module_script_data( array $args ): array {

		return [
			'data' => [],
		];
	}

	/**
	 * Retrieves and merges localized data with additional configuration options.
	 *
	 * @since 1.9.9
	 *
	 * @return array Merged an array of localized data and additional configurations.
	 */
	public function get_localized_data(): array {

		return array_merge(
			$this->localized_data,
			[
				'forms'         => $this->get_form_options(),
				'settingsGroup' => [
					'label' => esc_html__( 'Form Settings', 'wpforms-lite' ),
				],
				'formId'        => [
					'placeholder' => esc_html__( 'Select form', 'wpforms-lite' ),
					'label'       => esc_html__( 'Form', 'wpforms-lite' ),
				],
				'showTitle'     => [
					'label' => esc_html__( 'Show Title', 'wpforms-lite' ),
				],
				'showDesc'      => [
					'label' => esc_html__( 'Show Description', 'wpforms-lite' ),
				],
            ]
		);
	}

	/**
	 * Enqueues the Visual Builder assets for the D5 Tutorial Simple Quick Module
	 * if the Front Builder and D5 Builder are enabled.
	 *
	 * @since 1.9.9
	 *
	 * @noinspection PhpUndefinedFunctionInspection
	 */
	public function enqueue_visual_builder_assets(): void {

		if ( ! et_core_is_fb_enabled() || ! et_builder_d5_enabled() ) {
			return;
		}

		$min = wpforms_get_min_suffix();

		PackageBuildManager::register_package_build(
			[
				'name'    => 'wpforms-divi',
				'version' => WPFORMS_VERSION,
				'script'  => [
					'src'                => WPFORMS_PLUGIN_URL . "assets/js/integrations/divi/modern/formselector.es5{$min}.js",
					'deps'               => [
						'react',
						'jquery',
						'divi-module-library',
						'wp-hooks',
						'divi-rest',
					],
					'enqueue_top_window' => false,
					'enqueue_app_window' => true,
					'data_app_window'    => $this->get_localized_data(),
				],
			]
		);
	}
}
