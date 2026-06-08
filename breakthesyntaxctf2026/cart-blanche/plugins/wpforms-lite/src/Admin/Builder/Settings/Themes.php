<?php

namespace WPForms\Admin\Builder\Settings;

use WPForms\Frontend\CSSVars;
use WPForms\Integrations\Gutenberg\RestApi;
use WPForms\Integrations\Gutenberg\ThemesData;
use WPForms_Builder_Panel_Settings;

/**
 * Themes panel.
 *
 * @since 1.8.8
 */
class Themes {

	/**
	 * Form data.
	 *
	 * @since 1.9.7
	 *
	 * @var array
	 */
	public $form_data;

	/**
	 * CSS vars class instance.
	 *
	 * @since 1.9.7
	 *
	 * @var CSSVars
	 */
	protected $css_vars_obj;

	/**
	 * Rest API class instance.
	 *
	 * @since 1.9.7
	 *
	 * @var ThemesData
	 */
	protected $themes_data_obj;

	/**
	 * Size options for themes settings.
	 *
	 * @since 1.9.7
	 *
	 * @var array
	 */
	protected $size_options;

	/**
	 * Border type options for themes settings.
	 *
	 * @since 1.9.7
	 *
	 * @var array
	 */
	private $border_options;

	/**
	 * Is admin.
	 *
	 * @since 1.9.7
	 *
	 * @var bool
	 */
	private $is_admin;

	/**
	 * Whether a modern engine is enabled.
	 *
	 * @since 1.9.7
	 *
	 * @var bool
	 */
	private $is_modern;

	/**
	 * Whether full style is used.
	 *
	 * @since 1.9.7
	 *
	 * @var bool
	 */
	private $is_full_styles;

	/**
	 * Init class.
	 *
	 * @since 1.8.8
	 */
	public function init(): void {

		$this->css_vars_obj   = wpforms()->obj( 'css_vars' );
		$this->is_admin       = current_user_can( 'manage_options' );
		$this->is_modern      = wpforms_get_render_engine() === 'modern';
		$this->is_full_styles = (int) wpforms_setting( 'disable-css', '1' ) === 1;

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

		$this->hooks();
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.8.8
	 */
	protected function hooks(): void {

		// If the current user can't add posts, he can't save themes either. Enqueue no-access assets.
		if ( ! current_user_can( 'edit_posts' ) ) {
			add_action( 'admin_enqueue_scripts', [ $this, 'enqueues_no_access' ] );
			add_filter( 'wpforms_builder_panel_sidebar_section_classes', [ $this, 'add_pro_class' ], 10, 3 );

			return;
		}

		add_action( 'wpforms_form_settings_panel_content', [ $this, 'panel_content' ] );
		add_action( 'wpforms_builder_panel_sidebar_after', [ $this, 'sidebar_content' ], 10, 2 );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueues' ] );
	}

	/**
	 * Enqueue assets for the builder themes.
	 *
	 * @since 1.9.7
	 */
	public function enqueues(): void {

		$min = wpforms_get_min_suffix();

		wp_enqueue_script(
			'wpforms-builder-themes',
			WPFORMS_PLUGIN_URL . "assets/js/admin/builder/themes/builder-themes{$min}.js",
			[ 'wpforms-builder', 'wp-api-fetch' ],
			WPFORMS_VERSION,
			true
		);

		wp_enqueue_style(
			'wpforms-full',
			WPFORMS_PLUGIN_URL . "assets/css/frontend/modern/wpforms-full{$min}.css",
			[],
			WPFORMS_VERSION
		);

		wp_localize_script(
			'wpforms-builder-themes',
			'wpforms_builder_themes',
			$this->get_localize_data()
		);

		wp_add_inline_style( 'wpforms-full', $this->css_vars_obj->get_root_vars_css() );
	}

	/**
	 * Enqueue assets for the builder themes for the users who don't have access to the theme settings.
	 *
	 * @since 1.9.8
	 */
	public function enqueues_no_access(): void {

		$min = wpforms_get_min_suffix();

		wp_enqueue_script(
			'wpforms-builder-themes-no-access',
			WPFORMS_PLUGIN_URL . "assets/js/admin/builder/themes/builder-themes-no-access{$min}.js",
			[ 'wpforms-builder', 'wp-api-fetch' ],
			WPFORMS_VERSION,
			true
		);

		wp_localize_script(
			'wpforms-builder-themes-no-access',
			'wpforms_builder_themes_no_access',
			$this->get_localize_data()
		);
	}

	/**
	 * Add a class to the themes section if the user doesn't have access to it.
	 *
	 * @since 1.9.8
	 *
	 * @param array  $classes Sidebar section classes.
	 * @param string $name    Sidebar section name.
	 * @param string $slug    Sidebar section slug.
	 *
	 * @return array
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function add_pro_class( array $classes, string $name, string $slug ): array {

		if ( $slug !== 'themes' ) {
			return $classes;
		}

		return array_merge( $classes, [ 'wpforms-panel-sidebar-section-no-access' ] );
	}

	/**
	 * Get localize data.
	 *
	 * @since 1.9.7
	 *
	 * @return array
	 */
	protected function get_localize_data(): array {

		return [
			'modules'         => $this->get_modules(),
			'sizes'           => [
				'field-size'            => CSSVars::FIELD_SIZE,
				'label-size'            => CSSVars::LABEL_SIZE,
				'button-size'           => CSSVars::BUTTON_SIZE,
				'container-shadow-size' => CSSVars::CONTAINER_SHADOW_SIZE,
			],
			'strings'         => [
				'heads_up'                 => esc_html__( 'Heads Up!', 'wpforms-lite' ),
				'themes_error'             => esc_html__( 'Error loading themes. Please try again later.', 'wpforms-lite' ),
				'button_background'        => esc_html__( 'Button Background', 'wpforms-lite' ),
				'button_text'              => esc_html__( 'Button Text', 'wpforms-lite' ),
				'copy_paste_error'         => esc_html__( 'There was an error parsing your JSON code. Please check your code and try again.', 'wpforms-lite' ),
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
				'pro_sections'             => [
					'background' => esc_html__( 'Background Styles', 'wpforms-lite' ),
					'container'  => esc_html__( 'Container Styles', 'wpforms-lite' ),
					'themes'     => esc_html__( 'Themes', 'wpforms-lite' ),
				],
				'permission_modal'         => [
					'title'   => esc_html__( 'Insufficient Permissions', 'wpforms-lite' ),
					'content' => esc_html__( "Sorry, your user role doesn't have permission to access this feature.", 'wpforms-lite' ),
					'confirm' => esc_html__( 'OK', 'wpforms-lite' ),
				],
			],
			'isAdmin'         => $this->is_admin,
			'isPro'           => wpforms()->is_pro(),
			'isModern'        => $this->is_modern,
			'isFullStyles'    => $this->is_full_styles,
			'route_namespace' => RestApi::ROUTE_NAMESPACE,
		];
	}

	/**
	 * Get Form Builder themes modules.
	 *
	 * @since 1.9.7
	 *
	 * @return array Modules list.
	 */
	public function get_modules(): array {

		$min = wpforms_get_min_suffix();

		return [
			[
				'name' => 'common',
				'path' => "./modules/common{$min}.js",
			],
			[
				'name' => 'themes',
				'path' => "./modules/themes{$min}.js",
			],
			[
				'name' => 'stockPhotos',
				'path' => "./modules/stock-photos{$min}.js",
			],
			[
				'name' => 'background',
				'path' => "./modules/background{$min}.js",
			],
			[
				'name' => 'advancedSettings',
				'path' => "./modules/advanced-settings{$min}.js",
			],
		];
	}

	/**
	 * Add a content for `Themes` panel.
	 *
	 * @since 1.8.8
	 *
	 * @param WPForms_Builder_Panel_Settings $instance Settings panel instance.
	 *
	 * @noinspection HtmlUnknownTarget
	 */
	public function panel_content( WPForms_Builder_Panel_Settings $instance ): void {

		$this->form_data = $instance->form_data;
		$url             = wpforms_utm_link( 'https://wpforms.com/docs/styling-your-forms/', 'Builder Themes', 'Description Link' );

		?>
		<div class="wpforms-panel-content-section wpforms-panel-content-section-themes">
			<div class="wpforms-panel-content-section-themes-inner">
				<div class="wpforms-panel-content-section-themes-top">
					<div class="wpforms-panel-content-section-title">
						<?php esc_html_e( 'Form Themes', 'wpforms-lite' ); ?>
					</div>

					<div class="wpforms-panel-content-section-themes-preview">
						<p class="wpforms-panel-content-section-themes-preview-description">
							<?php
								echo wp_kses_post(
									sprintf(
										/* translators: %s - URL to the documentation. */
										__( 'Customize the look and feel of your form with premade themes or simple style settings that allow you to use your own colors to match your brand. Themes and style settings are also available in the Block Editor and Elementor, where you can see a realtime preview. <a href="%s" target="_blank">Learn more about styling your forms</a>.', 'wpforms-lite' ),
										$url
									)
								);
							?>
						</p>

						<div class="wpforms-alert wpforms-alert-warning wpforms-alert-warning-wide wpforms-builder-themes-preview-notice">
							<h4>
								<?php esc_html_e( 'Preview only', 'wpforms-lite' ); ?>
							</h4>
							<p>
								<?php esc_html_e( 'The fields shown below are for demo purposes and do not reflect the fields in your actual form.', 'wpforms-lite' ); ?>
							</p>
						</div>

						<?php
						echo wpforms_render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							'builder/themes/notices',
							[
								'is_modern'      => $this->is_modern,
								'is_full_styles' => $this->is_full_styles,
							],
							true
						);

							// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							echo wpforms_render( 'builder/themes/preview' );
						?>
					</div>

				</div> <!-- .wpforms-panel-content-section-themes-top -->
			</div> <!-- .wpforms-panel-content-section-themes-inner -->
		</div> <!-- .wpforms-panel-content-section-themes -->
		<?php
	}

	/**
	 * Add content for the Themes Sidebar.
	 *
	 * @param object $form Current form object.
	 * @param string $slug Current panel slug.
	 *
	 * @since 1.9.7
	 */
	public function sidebar_content( $form, $slug ): void {

		if ( $slug !== 'settings' ) {
			return;
		}

		$form_obj = wpforms()->obj( 'form' );

		if ( ! $form_obj || ! isset( $form->ID ) ) {
			return;
		}

		$form_data = $form_obj->get( $form->ID, [ 'content_only' => true ] );

		$this->form_data = $form_data;

		$this->show_sidebar_html();
	}

	/**
	 * Show sidebar HTML.
	 *
	 * @since 1.9.7
	 */
	private function show_sidebar_html(): void {
		?>
		<div id="wpforms-builder-themes-sidebar" class="wpforms-hidden">
			<div class="wpforms-builder-themes-sidebar-head">
				<button id="wpforms-builder-themes-back"> <?php esc_html_e( 'Back to Settings', 'wpforms-lite' ); ?></button>
			</div>
			<div id="wpforms-builder-themes-sidebar-tabs">
				<a href="#" class="active"><?php esc_html_e( 'General', 'wpforms-lite' ); ?></a>
				<?php if ( $this->is_admin ) : ?>
					<a href="#"><?php esc_html_e( 'Advanced', 'wpforms-lite' ); ?></a>
				<?php endif; ?>
			</div>
			<div class="wpforms-builder-themes-sidebar-content">

				<div class="wpforms-builder-themes-sidebar-general wpforms-builder-themes-sidebar-tab-content">
					<?php $this->show_sidebar_themes(); ?>
					<div class="wpforms-builder-themes-restricted <?php echo esc_attr( ! $this->is_admin ? 'wpforms-hidden' : '' ); ?>">
						<?php $this->show_sidebar_field_styles(); ?>
						<?php $this->show_sidebar_label_styles(); ?>
						<?php $this->show_sidebar_button_styles(); ?>
						<?php $this->show_sidebar_container_styles(); ?>
						<?php $this->show_sidebar_background_styles(); ?>
						<?php $this->show_sidebar_other_styles(); ?>
					</div>
				</div>
				<div class="wpforms-builder-themes-sidebar-advanced wpforms-builder-themes-sidebar-tab-content wpforms-hidden">
					<?php $this->show_sidebar_advanced(); ?>
				</div>

			</div>
		</div>
		<?php
	}

	/**
	 * Show sidebar themes.
	 *
	 * @since 1.9.7
	 *
	 * @return void
	 */
	private function show_sidebar_themes(): void {

		?>
		<div class="wpforms-add-fields-group">
			<a href="#" class="wpforms-add-fields-heading" data-group="themes">
				<span><?php esc_html_e( 'Themes', 'wpforms-lite' ); ?></span>
				<i class="fa fa-angle-down"></i>
			</a>
			<div class="wpforms-add-fields-buttons">
				<?php

				wpforms_panel_field(
					'text',
					'themes',
					'wpformsTheme',
					$this->form_data,
					esc_html__( 'Theme', 'wpforms-lite' ),
					[
						'parent' => 'settings',
						'type'   => 'hidden',
						'value'  => $this->form_data['settings']['themes']['wpformsTheme'] ?? 'default',
						'class'  => 'wpforms-hidden',
					]
				);

				wpforms_panel_field(
					'text',
					'themes',
					'isCustomTheme',
					$this->form_data,
					false,
					[
						'parent' => 'settings',
						'type'   => 'hidden',
						'value'  => $this->form_data['settings']['themes']['isCustomTheme'] ?? '',
						'class'  => 'wpforms-hidden',
					]
				);

				?>

				<div class="wpforms-builder-themes-control"></div>

				<?php

				wpforms_panel_field(
					'text',
					'themes',
					'themeName',
					$this->form_data,
					esc_html__( 'Theme Name', 'wpforms-lite' ),
					[
						'parent' => 'settings',
						'type'   => 'text',
						'value'  => $this->form_data['settings']['themes']['themeName'] ?? '',
						'class'  => 'wpforms-hidden',
					]
				);

				?>

				<button id="wpforms-builder-themer-remove-theme" class="wpforms-hidden"><?php esc_html_e( 'Delete Theme', 'wpforms-lite' ); ?></button>
			</div>
		</div>
		<?php
	}

	/**
	 * Show sidebar field styles.
	 *
	 * @since 1.9.7
	 *
	 * @return void
	 */
	private function show_sidebar_field_styles(): void {

		?>
		<div class="wpforms-add-fields-group">
			<a href="#" class="wpforms-add-fields-heading" data-group="field_styles">
				<span><?php esc_html_e( 'Field Styles', 'wpforms-lite' ); ?></span>
				<i class="fa fa-angle-down"></i>
			</a>
			<div class="wpforms-add-fields-buttons">
				<div class="wpforms-builder-themes-fields-row">
					<?php

					wpforms_panel_field(
						'select',
						'themes',
						'fieldSize',
						$this->form_data,
						esc_html__( 'Size', 'wpforms-lite' ),
						[
							'parent'  => 'settings',
							'options' => $this->size_options,
							'value'   => $this->form_data['settings']['themes']['fieldSize'] ?? 'medium',
						]
					);

					wpforms_panel_field(
						'select',
						'themes',
						'fieldBorderStyle',
						$this->form_data,
						esc_html__( 'Border', 'wpforms-lite' ),
						[
							'parent'  => 'settings',
							'options' => $this->border_options,
							'value'   => $this->form_data['settings']['themes']['fieldBorderStyle'] ?? 'solid',
						]
					);
					?>
				</div>

				<div class="wpforms-builder-themes-fields-row">
					<?php

					wpforms_panel_field(
						'text',
						'themes',
						'fieldBorderSize',
						$this->form_data,
						esc_html__( 'Border Size', 'wpforms-lite' ),
						[
							'parent'      => 'settings',
							'type'        => 'number',
							'min'         => 0,
							'value'       => $this->form_data['settings']['themes']['fieldBorderSize'] ?? '1',
							'input_class' => 'wpforms-builder-themes-number-input',
							'class'       => 'wpforms-builder-themes-number-input-wrapper',
						]
					);

					wpforms_panel_field(
						'text',
						'themes',
						'fieldBorderRadius',
						$this->form_data,
						esc_html__( 'Border Radius', 'wpforms-lite' ),
						[
							'parent'      => 'settings',
							'type'        => 'number',
							'min'         => 0,
							'value'       => $this->form_data['settings']['themes']['fieldBorderRadius'] ?? '3',
							'input_class' => 'wpforms-builder-themes-number-input',
							'class'       => 'wpforms-builder-themes-number-input-wrapper',
						]
					);

					?>
				</div>

				<div class="wpforms-builder-themes-fields-row">
					<?php

					wpforms_panel_field(
						'color',
						'themes',
						'fieldBackgroundColor',
						$this->form_data,
						esc_html__( 'Background', 'wpforms-lite' ),
						[
							'parent' => 'settings',
							'value'  => $this->form_data['settings']['themes']['fieldBackgroundColor'] ?? CSSVars::ROOT_VARS['field-background-color'],
						]
					);

					wpforms_panel_field(
						'color',
						'themes',
						'fieldBorderColor',
						$this->form_data,
						esc_html__( 'Border', 'wpforms-lite' ),
						[
							'parent' => 'settings',
							'value'  => $this->form_data['settings']['themes']['fieldBorderColor'] ?? CSSVars::ROOT_VARS['field-border-color'],
						]
					);

					wpforms_panel_field(
						'color',
						'themes',
						'fieldTextColor',
						$this->form_data,
						esc_html__( 'Text', 'wpforms-lite' ),
						[
							'parent' => 'settings',
							'value'  => $this->form_data['settings']['themes']['fieldTextColor'] ?? CSSVars::ROOT_VARS['field-text-color'],
						]
					);

					?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Show sidebar label styles.
	 *
	 * @since 1.9.7
	 *
	 * @return void
	 */
	private function show_sidebar_label_styles(): void {

		?>
		<div class="wpforms-add-fields-group">
			<a href="#" class="wpforms-add-fields-heading" data-group="label_styles">
				<span><?php esc_html_e( 'Label Styles', 'wpforms-lite' ); ?></span>
				<i class="fa fa-angle-down"></i>
			</a>
			<div class="wpforms-add-fields-buttons">
				<div class="wpforms-builder-themes-fields-row">
					<?php

					wpforms_panel_field(
						'select',
						'themes',
						'labelSize',
						$this->form_data,
						esc_html__( 'Size', 'wpforms-lite' ),
						[
							'parent'  => 'settings',
							'options' => $this->size_options,
							'value'   => $this->form_data['settings']['themes']['labelSize'] ?? 'medium',
						]
					);

					?>
				</div>

				<div class="wpforms-builder-themes-fields-row">
					<?php

					wpforms_panel_field(
						'color',
						'themes',
						'labelColor',
						$this->form_data,
						esc_html__( 'Label', 'wpforms-lite' ),
						[
							'parent' => 'settings',
							'value'  => $this->form_data['settings']['themes']['labelColor'] ?? CSSVars::ROOT_VARS['label-color'],
						]
					);

					wpforms_panel_field(
						'color',
						'themes',
						'labelSublabelColor',
						$this->form_data,
						esc_html__( 'Sublabel', 'wpforms-lite' ),
						[
							'parent' => 'settings',
							'value'  => $this->form_data['settings']['themes']['labelSublabelColor'] ?? CSSVars::ROOT_VARS['label-sublabel-color'],
						]
					);

					wpforms_panel_field(
						'color',
						'themes',
						'labelErrorColor',
						$this->form_data,
						esc_html__( 'Error', 'wpforms-lite' ),
						[
							'parent' => 'settings',
							'value'  => $this->form_data['settings']['themes']['labelErrorColor'] ?? CSSVars::ROOT_VARS['label-error-color'],
						]
					);

					?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Show sidebar button styles.
	 *
	 * @since 1.9.7
	 *
	 * @return void
	 */
	private function show_sidebar_button_styles(): void {

		?>
		<div class="wpforms-add-fields-group">
			<a href="#" class="wpforms-add-fields-heading" data-group="button_styles">
				<span><?php esc_html_e( 'Button Styles', 'wpforms-lite' ); ?></span>
				<i class="fa fa-angle-down"></i>
			</a>
			<div class="wpforms-add-fields-buttons">
				<div class="wpforms-builder-themes-fields-row">
					<?php

					wpforms_panel_field(
						'select',
						'themes',
						'buttonSize',
						$this->form_data,
						esc_html__( 'Size', 'wpforms-lite' ),
						[
							'parent'  => 'settings',
							'options' => $this->size_options,
							'value'   => $this->form_data['settings']['themes']['buttonSize'] ?? 'medium',
						]
					);

					wpforms_panel_field(
						'select',
						'themes',
						'buttonBorderStyle',
						$this->form_data,
						esc_html__( 'Border', 'wpforms-lite' ),
						[
							'parent'  => 'settings',
							'options' => $this->border_options,
							'value'   => $this->form_data['settings']['themes']['buttonBorderStyle'] ?? CSSVars::ROOT_VARS['button-border-style'],
						]
					);

					?>
				</div>

				<div class="wpforms-builder-themes-fields-row">
					<?php

					wpforms_panel_field(
						'text',
						'themes',
						'buttonBorderSize',
						$this->form_data,
						esc_html__( 'Border Size', 'wpforms-lite' ),
						[
							'parent'      => 'settings',
							'type'        => 'number',
							'min'         => 0,
							'value'       => $this->form_data['settings']['themes']['buttonBorderSize'] ?? '1',
							'input_class' => 'wpforms-builder-themes-number-input',
							'class'       => 'wpforms-builder-themes-number-input-wrapper',
						]
					);

					wpforms_panel_field(
						'text',
						'themes',
						'buttonBorderRadius',
						$this->form_data,
						esc_html__( 'Border Radius', 'wpforms-lite' ),
						[
							'parent'      => 'settings',
							'type'        => 'number',
							'min'         => 0,
							'value'       => $this->form_data['settings']['themes']['buttonBorderRadius'] ?? '3',
							'input_class' => 'wpforms-builder-themes-number-input',
							'class'       => 'wpforms-builder-themes-number-input-wrapper',
						]
					);

					?>
				</div>

				<div class="wpforms-builder-themes-fields-row">
					<?php

					wpforms_panel_field(
						'color',
						'themes',
						'buttonBackgroundColor',
						$this->form_data,
						esc_html__( 'Background', 'wpforms-lite' ),
						[
							'parent' => 'settings',
							'value'  => $this->form_data['settings']['themes']['buttonBackgroundColor'] ?? CSSVars::ROOT_VARS['button-background-color'],
						]
					);

					wpforms_panel_field(
						'color',
						'themes',
						'buttonBorderColor',
						$this->form_data,
						esc_html__( 'Border', 'wpforms-lite' ),
						[
							'parent' => 'settings',
							'value'  => $this->form_data['settings']['themes']['buttonBorderColor'] ?? CSSVars::ROOT_VARS['button-border-color'],
						]
					);

					wpforms_panel_field(
						'color',
						'themes',
						'buttonTextColor',
						$this->form_data,
						esc_html__( 'Text', 'wpforms-lite' ),
						[
							'parent' => 'settings',
							'value'  => $this->form_data['settings']['themes']['buttonTextColor'] ?? CSSVars::ROOT_VARS['button-text-color'],
						]
					);

					?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Show sidebar container styles.
	 *
	 * @since 1.9.7
	 *
	 * @return void
	 */
	private function show_sidebar_container_styles(): void {

		?>
		<div class="wpforms-add-fields-group">
			<a href="#" class="wpforms-add-fields-heading" data-group="container_styles">
				<span><?php esc_html_e( 'Container Styles', 'wpforms-lite' ); ?></span>
				<i class="fa fa-angle-down"></i>
			</a>
			<div class="wpforms-add-fields-buttons wpforms-builder-themes-pro-section">
				<div class="wpforms-builder-themes-fields-row">
					<?php

					wpforms_panel_field(
						'text',
						'themes',
						'containerPadding',
						$this->form_data,
						esc_html__( 'Padding', 'wpforms-lite' ),
						[
							'parent'      => 'settings',
							'type'        => 'number',
							'min'         => 0,
							'value'       => $this->form_data['settings']['themes']['containerPadding'] ?? '0',
							'input_class' => 'wpforms-builder-themes-number-input',
							'class'       => 'wpforms-builder-themes-number-input-wrapper',
						]
					);

					wpforms_panel_field(
						'select',
						'themes',
						'containerBorderStyle',
						$this->form_data,
						esc_html__( 'Border', 'wpforms-lite' ),
						[
							'parent'  => 'settings',
							'options' => $this->border_options,
							'value'   => $this->form_data['settings']['themes']['containerBorderStyle'] ?? CSSVars::ROOT_VARS['container-border-style'],
						]
					);

					?>
				</div>
				<div class="wpforms-builder-themes-fields-row">
					<?php

					wpforms_panel_field(
						'text',
						'themes',
						'containerBorderWidth',
						$this->form_data,
						esc_html__( 'Border Size', 'wpforms-lite' ),
						[
							'parent'      => 'settings',
							'type'        => 'number',
							'min'         => 0,
							'value'       => $this->form_data['settings']['themes']['containerBorderWidth'] ?? '1',
							'input_class' => 'wpforms-builder-themes-number-input',
							'class'       => 'wpforms-builder-themes-number-input-wrapper',
						]
					);

					wpforms_panel_field(
						'text',
						'themes',
						'containerBorderRadius',
						$this->form_data,
						esc_html__( 'Border Radius', 'wpforms-lite' ),
						[
							'parent'      => 'settings',
							'type'        => 'number',
							'min'         => 0,
							'value'       => $this->form_data['settings']['themes']['containerBorderRadius'] ?? '3',
							'input_class' => 'wpforms-builder-themes-number-input',
							'class'       => 'wpforms-builder-themes-number-input-wrapper',
						]
					);

					?>
				</div>

				<div class="wpforms-builder-themes-fields-row">
					<?php

					wpforms_panel_field(
						'color',
						'themes',
						'containerBorderColor',
						$this->form_data,
						esc_html__( 'Border', 'wpforms-lite' ),
						[
							'parent' => 'settings',
							'value'  => $this->form_data['settings']['themes']['containerBorderColor'] ?? CSSVars::ROOT_VARS['container-border-color'],
						]
					);

					wpforms_panel_field(
						'select',
						'themes',
						'containerShadowSize',
						$this->form_data,
						esc_html__( 'Shadow', 'wpforms-lite' ),
						[
							'parent'  => 'settings',
							'options' => [
								'none'   => esc_html__( 'None', 'wpforms-lite' ),
								'small'  => esc_html__( 'Small', 'wpforms-lite' ),
								'medium' => esc_html__( 'Medium', 'wpforms-lite' ),
								'large'  => esc_html__( 'Large', 'wpforms-lite' ),
							],
							'value'   => $this->form_data['settings']['themes']['containerShadowSize'] ?? CSSVars::CONTAINER_SHADOW_SIZE['none']['box-shadow'],
						]
					);

					?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Show sidebar background styles.
	 *
	 * @since 1.9.7
	 *
	 * @return void
	 */
	private function show_sidebar_background_styles(): void {

		?>
		<div class="wpforms-add-fields-group">
			<a href="#" class="wpforms-add-fields-heading" data-group="background_styles"><span><?php esc_html_e( 'Background Styles', 'wpforms-lite' ); ?></span><i class="fa fa-angle-down"></i></a>
			<div class="wpforms-add-fields-buttons wpforms-builder-themes-pro-section">
				<div class="wpforms-builder-themes-fields-row">
					<?php
					wpforms_panel_field(
						'color',
						'themes',
						'backgroundColor',
						$this->form_data,
						esc_html__( 'Color', 'wpforms-lite' ),
						[
							'parent' => 'settings',
							'value'  => $this->form_data['settings']['themes']['backgroundColor'] ?? CSSVars::ROOT_VARS['background-color'],
						]
					);
					?>
				</div>

				<div class="wpforms-builder-themes-fields-row">
					<?php

					wpforms_panel_field(
						'select',
						'themes',
						'backgroundImage',
						$this->form_data,
						esc_html__( 'Image', 'wpforms-lite' ),
						[
							'parent'  => 'settings',
							'options' => [
								'none'    => esc_html__( 'None', 'wpforms-lite' ),
								'library' => esc_html__( 'Media Library', 'wpforms-lite' ),
								'stock'   => esc_html__( 'Stock Photo', 'wpforms-lite' ),
							],
							'value'   => $this->form_data['settings']['themes']['backgroundImage'] ?? 'none',
						]
					);

					wpforms_panel_field(
						'select',
						'themes',
						'backgroundPosition',
						$this->form_data,
						esc_html__( 'Position', 'wpforms-lite' ),
						[
							'parent'  => 'settings',
							'options' => [
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
							'value'   => $this->form_data['settings']['themes']['backgroundPosition'] ?? CSSVars::ROOT_VARS['background-position'],
						]
					);
					?>

				</div>
				<div class="wpforms-builder-themes-fields-row wpforms-builder-themes-conditional-hide">
					<?php

					wpforms_panel_field(
						'select',
						'themes',
						'backgroundRepeat',
						$this->form_data,
						esc_html__( 'Repeat', 'wpforms-lite' ),
						[
							'parent'  => 'settings',
							'options' => [
								'no-repeat' => esc_html__( 'No Repeat', 'wpforms-lite' ),
								'repeat'    => esc_html__( 'Tile', 'wpforms-lite' ),
								'repeat-x'  => esc_html__( 'Repeat X', 'wpforms-lite' ),
								'repeat-y'  => esc_html__( 'Repeat Y', 'wpforms-lite' ),
							],
							'value'   => $this->form_data['settings']['themes']['backgroundRepeat'] ?? CSSVars::ROOT_VARS['background-repeat'],
						]
					);

					wpforms_panel_field(
						'select',
						'themes',
						'backgroundSizeMode',
						$this->form_data,
						esc_html__( 'Size', 'wpforms-lite' ),
						[
							'parent'  => 'settings',
							'options' => [
								'dimensions' => esc_html__( 'Dimensions', 'wpforms-lite' ),
								'cover'      => esc_html__( 'Cover', 'wpforms-lite' ),
							],
							'value'   => $this->form_data['settings']['themes']['backgroundSizeMode'] ?? CSSVars::ROOT_VARS['background-size'],
						]
					);

					wpforms_panel_field(
						'text',
						'themes',
						'backgroundSize',
						$this->form_data,
						false,
						[
							'parent' => 'settings',
							'type'   => 'hidden',
							'value'  => $this->form_data['settings']['themes']['backgroundSize'] ?? CSSVars::ROOT_VARS['background-size'],
							'class'  => 'wpforms-hidden',
						]
					);
					?>

				</div>
				<div class="wpforms-builder-themes-fields-row wpforms-builder-themes-conditional-hide">
					<?php
					wpforms_panel_field(
						'text',
						'themes',
						'backgroundWidth',
						$this->form_data,
						esc_html__( 'Width', 'wpforms-lite' ),
						[
							'parent'      => 'settings',
							'type'        => 'number',
							'min'         => 0,
							'value'       => $this->form_data['settings']['themes']['backgroundWidth'] ?? '100',
							'input_class' => 'wpforms-builder-themes-number-input',
							'class'       => 'wpforms-builder-themes-number-input-wrapper',
						]
					);

					wpforms_panel_field(
						'text',
						'themes',
						'backgroundHeight',
						$this->form_data,
						esc_html__( 'Height', 'wpforms-lite' ),
						[
							'parent'      => 'settings',
							'type'        => 'number',
							'min'         => 0,
							'value'       => $this->form_data['settings']['themes']['backgroundHeight'] ?? '100',
							'input_class' => 'wpforms-builder-themes-number-input',
							'class'       => 'wpforms-builder-themes-number-input-wrapper',
						]
					);
					?>

				</div>

				<div class="wpforms-builder-themes-background-selector wpforms-hidden">
					<?php
					wpforms_panel_field(
						'text',
						'themes',
						'backgroundUrl',
						$this->form_data,
						false,
						[
							'parent' => 'settings',
							'type'   => 'hidden',
							'value'  => $this->form_data['settings']['themes']['backgroundUrl'] ?? CSSVars::ROOT_VARS['background-url'],
							'class'  => 'wpforms-hidden',
						]
					);

					?>
					<button class="wpforms-builder-themes-bg-image-choose wpforms-hidden"><?php esc_html_e( 'Choose Image', 'wpforms-lite' ); ?></button>
					<div class="wpforms-builder-themes-bg-image-preview wpforms-hidden"></div>
					<button class="wpforms-builder-themes-bg-image-remove wpforms-hidden"><?php esc_html_e( 'Remove Image', 'wpforms-lite' ); ?></button>
				</div>

			</div>
		</div>
		<?php
	}

	/**
	 * Show sidebar background styles.
	 *
	 * @since 1.9.7
	 *
	 * @return void
	 */
	private function show_sidebar_other_styles(): void {

		?>
		<div class="wpforms-add-fields-group wpforms-hidden">
			<a href="#" class="wpforms-add-fields-heading" data-group="other_styles"><span><?php esc_html_e( 'Other Styles', 'wpforms-lite' ); ?></span><i class="fa fa-angle-down"></i></a>
			<div class="wpforms-add-fields-buttons">
				<?php
				wpforms_panel_field(
					'color',
					'themes',
					'fieldMenuColor',
					$this->form_data,
					false,
					[
						'parent' => 'settings',
						'value'  => $this->form_data['settings']['themes']['fieldMenuColor'] ?? CSSVars::ROOT_VARS['field-menu-color'],
					]
				);

				wpforms_panel_field(
					'color',
					'themes',
					'pageBreakColor',
					$this->form_data,
					false,
					[
						'parent' => 'settings',
						'value'  => $this->form_data['settings']['themes']['pageBreakColor'] ?? CSSVars::ROOT_VARS['page-break-color'],
					]
				);
				?>
			</div>
		</div>
		<?php
	}

	/**
	 * Show sidebar background styles.
	 *
	 * @since 1.9.7
	 *
	 * @return void
	 */
	private function show_sidebar_advanced(): void {

		if ( ! $this->is_admin ) {
			return;
		}

		?>
		<?php
		wpforms_panel_field(
			'textarea',
			'themes',
			'customCss',
			$this->form_data,
			esc_html__( 'Custom CSS', 'wpforms-lite' ),
			[
				'parent' => 'settings',
				'value'  => $this->form_data['settings']['themes']['customCss'] ?? '',
				'after'  => sprintf( '<span class="wpforms-panel-field-after">%s</span>', __( 'Further customize the look of this form without having to edit theme files.', 'wpforms-lite' ) ),
			]
		);

		wpforms_panel_field(
			'textarea',
			'themes',
			'copyPasteJsonValue',
			$this->form_data,
			esc_html__( 'Copy / Paste Style Settings', 'wpforms-lite' ),
			[
				'parent' => 'settings',
				'value'  => $this->form_data['settings']['themes']['copyPasteJsonValue'] ?? '',
				'after'  => sprintf( '<span class="wpforms-panel-field-after">%s</span>', __( 'If you\'ve copied style settings from another form, you can paste them here to add the same styling to this form. Any current style settings will be overwritten.', 'wpforms-lite' ) ),
			]
		);
		?>
		<?php
	}
}
