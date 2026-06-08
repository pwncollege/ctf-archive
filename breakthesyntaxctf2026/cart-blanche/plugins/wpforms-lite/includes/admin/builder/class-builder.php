<?php

// phpcs:disable Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpIllegalPsrClassPathInspection */
/** @noinspection AutoloadingIssuesInspection */
// phpcs:enable Generic.Commenting.DocComment.MissingShort

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPForms\Integrations\AI\Helpers as AIHelpers;

/**
 * Form builder that contains magic.
 *
 * @since 1.0.0
 * @since 1.6.8 Form Builder Refresh.
 *                  - Added `deregister_common_wp_admin_styles()` method.
 *                  - Changed logic of enqueue styles.
 */
class WPForms_Builder {

	/**
	 * Abort. Bail on proceeding to process the page.
	 *
	 * @since 1.7.3
	 *
	 * @var bool
	 */
	public $abort = false;

	/**
	 * The human-readable error message.
	 *
	 * @since 1.7.3
	 *
	 * @var string
	 */
	private $abort_message;

	/**
	 * One is the loneliest number that you'll ever do.
	 *
	 * @since 1.4.4.1
	 *
	 * @var object
	 */
	private static $instance;

	/**
	 * Current view (panel).
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $view;

	/**
	 * Available panels.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	public $panels;

	/**
	 * Current form.
	 *
	 * @since 1.0.0
	 *
	 * @var WP_Post|null
	 */
	public $form;

	/**
	 * Form data and settings.
	 *
	 * @since 1.4.4.1
	 *
	 * @var array
	 */
	public $form_data;

	/**
	 * Current template information.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	public $template;

	/**
	 * Main Instance.
	 *
	 * @since 1.4.4.1
	 *
	 * @return WPForms_Builder
	 */
	public static function instance() { // phpcs:ignore WPForms.PHP.HooksMethod.InvalidPlaceForAddingHooks

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) {

			self::$instance = new self();

			self::$instance->instance_hooks();
		}

		return self::$instance;
	}

	/**
	 * Register instance hooks.
	 *
	 * @since 1.10.0
	 */
	public function instance_hooks(): void { // phpcs:ignore WPForms.PHP.HooksMethod.InvalidPlaceForAddingHooks

		// Only load if we are actually on the builder.
		if ( ! wpforms_is_admin_page( 'builder' ) ) {
			return;
		}

		add_action( 'init', [ $this, 'remove_gutenberg_scripts' ], 0 );
		add_action( 'admin_init', [ $this, 'init' ] );
		add_action( 'admin_init', [ $this, 'deregister_common_wp_admin_styles' ], PHP_INT_MAX );
		add_action( 'load-wpforms_page_wpforms-builder', [ $this, 'process_actions' ] );
	}

	/**
	 * Determine if the user is viewing the builder, if so, party on.
	 *
	 * @since 1.0.0
	 */
	public function init(): void { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		// Only load if we are actually on the builder.
		if ( ! wpforms_is_admin_page( 'builder' ) ) {
			return;
		}

		// Load form if found.
		$form_id = isset( $_GET['form_id'] ) ? absint( $_GET['form_id'] ) : false; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		// Abort early if form ID is set, but the value is empty, 0 or any non-numeric value.
		if ( $form_id === 0 ) {
			wp_die( esc_html__( 'It looks like the form you are trying to access is no longer available.', 'wpforms-lite' ), 403 );
		}

		if ( $form_id ) {
			// The default view for with an existing form is the fields panel.
			$this->view = isset( $_GET['view'] ) ? sanitize_key( $_GET['view'] ) : 'fields'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		} else {
			// The default view for the new form is the setup panel.
			$this->view = isset( $_GET['view'] ) ? sanitize_key( $_GET['view'] ) : 'setup'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		}

		if ( $this->view === 'setup' && ! wpforms_current_user_can( 'create_forms' ) ) {
			wp_die( esc_html__( 'Sorry, you are not allowed to create new forms.', 'wpforms-lite' ), 403 );
		}

		if ( $this->view === 'fields' && ! wpforms_current_user_can( 'edit_form_single', $form_id ) ) {
			wp_die( esc_html__( 'Sorry, you are not allowed to edit this form.', 'wpforms-lite' ), 403 );
		}

		// Fetch form.
		$form_obj   = wpforms()->obj( 'form' );
		$this->form = $form_obj ? $form_obj->get( $form_id ) : null;

		if ( ! empty( $form_id ) && empty( $this->form ) ) {
			$this->abort_message = esc_html__( 'It looks like the form you are trying to access is no longer available.', 'wpforms-lite' );
			$this->abort         = true;
		}

		if ( ! empty( $this->form->post_status ) && $this->form->post_status === 'trash' ) {
			$this->abort_message = esc_html__( 'You can\'t edit this form because it\'s in the trash.', 'wpforms-lite' );
			$this->abort         = true;
		}

		// Retrieve form data.
		$this->form_data = $this->form ? wpforms_decode( $this->form->post_content ) : false;

		/**
		 * Active form template data filter.
		 *
		 * Allows developers to modify fields' structure and form settings in the template of the current form.
		 *
		 * @since 1.6.8
		 *
		 * @param array         $template Template data.
		 * @param WP_Post|false $form_id  Form object.
		 */
		$this->template = (array) apply_filters( 'wpforms_builder_template_active', [], $this->form );

		// Load builder panels.
		$this->load_panels();

		$this->hooks();
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.10.0
	 */
	public function hooks(): void {

		// Modify the meta-viewport tag if the desktop view is forced.
		add_filter( 'admin_viewport_meta', [ $this, 'viewport_meta' ] );

		add_action( 'admin_head', [ $this, 'admin_head' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueues' ], PHP_INT_MAX );
		add_action( 'admin_print_footer_scripts', [ $this, 'footer_scripts' ] );
		add_action( 'wpforms_admin_page', [ $this, 'output' ] );

		// Display Abort Message screen.
		add_action( 'wpforms_admin_page', [ $this, 'display_abort_message' ] );

		add_filter( 'teeny_mce_plugins', [ $this, 'tinymce_buttons' ] );

		// Save the timestamp when the Builder has been opened for the first time.
		add_option( 'wpforms_builder_opened_date', time(), '', 'no' );

		/**
		 * Form Builder init action.
		 *
		 * Executes after all the form builder UI output.
		 * Intended to use in addons.
		 *
		 * @since 1.6.8
		 *
		 * @param string $view Current view.
		 */
		do_action( 'wpforms_builder_init', $this->view );
	}

	/**
	 * Clear common wp-admin styles, keep only allowed.
	 *
	 * @since 1.6.8
	 */
	public function deregister_common_wp_admin_styles(): void {

		/**
		 * Filter the allowed common wp-admin styles.
		 *
		 * @since 1.6.8
		 *
		 * @param array $allowed_styles Styles allowed in the Form Builder.
		 */
		$allowed_styles = (array) apply_filters( // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName
			'wpforms_admin_builder_allowed_common_wp_admin_styles',
			[
				'wp-editor',
				'wp-editor-font',
				'editor-buttons',
				'dashicons',
				'media-views',
				'imgareaselect',
				'wp-mediaelement',
				'mediaelement',
				'buttons',
				'admin-bar',
			]
		);

		wp_styles()->registered = array_intersect_key( wp_styles()->registered, array_flip( $allowed_styles ) );
	}

	/**
	 * Remove the Gutenberg scripts registration hook before WP_Scripts is instantiated.
	 *
	 * Must run on 'init' priority 0, before wp_scripts() is first called,
	 * otherwise wp_default_scripts has already fired and the remove_action has no effect.
	 *
	 * @since 1.10.0
	 */
	public function remove_gutenberg_scripts(): void { // phpcs:ignore WPForms.PHP.HooksMethod.InvalidPlaceForAddingHooks

		remove_action( 'wp_default_scripts', 'gutenberg_register_package_scripts' );
		remove_action( 'wp_default_scripts', 'gutenberg_register_vendor_scripts' );
		remove_action( 'wp_default_scripts', 'gutenberg_register_block_library_script_special_case', 11 );
		remove_action( 'wp_default_scripts', 'gutenberg_define_interactivity_modules_support' );
		remove_action( 'admin_enqueue_scripts', 'gutenberg_enqueue_core_abilities' );
	}

	/**
	 * Process form actions.
	 *
	 * @since 1.8.8
	 */
	public function process_actions(): void {

		$form_id = isset( $_GET['form_id'] ) ? (int) $_GET['form_id'] : 0;
		$action  = isset( $_REQUEST['action'] ) ? sanitize_key( $_REQUEST['action'] ) : false;
		$nonce   = isset( $_GET['_wpnonce'] ) ? sanitize_key( $_GET['_wpnonce'] ) : false;

		if ( ! $this->is_allowed_action( $form_id, $action ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $nonce, 'wpforms_' . $action . '_form_nonce' ) ) {
			return;
		}

		$this->process_action( $form_id, $action );
	}

	/**
	 * Check whether the form action is valid and allowed.
	 *
	 * @since 1.8.8
	 *
	 * @param int    $form_id Form ID.
	 * @param string $action  Action name.
	 *
	 * @return bool
	 */
	private function is_allowed_action( int $form_id, string $action ): bool {

		if ( empty( $form_id ) || empty( $action ) ) {
			return false;
		}

		return in_array( $action, [ 'save_as_template', 'template_to_form', 'duplicate' ], true );
	}

	/**
	 * Process a single form action.
	 *
	 * The action can be triggered via URL:
	 *   wp_nonce_url(
	 *       add_query_arg( [ 'action' => '<action>', 'form_id' => $form_id ] ),
	 *      'wpforms_save_as_template_form_nonce'
	 *   );
	 *
	 * @since 1.8.8
	 *
	 * @param int    $form_id Form ID.
	 * @param string $action  Action name.
	 */
	private function process_action( int $form_id, string $action ): void {

		$form_handler = wpforms()->obj( 'form' );

		if ( ! $form_handler ) {
			return;
		}

		$id = false;

		if ( $action === 'save_as_template' ) {
			$id = $form_handler->convert( $form_id, 'template' );
		}

		if ( $action === 'template_to_form' ) {
			$id = $form_handler->convert( $form_id, 'form' );
		}

		if ( $action === 'duplicate' ) {
			$ids = $form_handler->duplicate( $form_id );
			$id  = ! empty( $ids ) ? current( $ids ) : false;
		}

		// Reload the form builder with the target object.
		if ( ! empty( $id ) ) {
			wp_safe_redirect( $this->get_edit_url( $id, $this->view ) );

			exit;
		}
	}

	/**
	 * Get the form edit URL.
	 *
	 * @since 1.8.8
	 *
	 * @param string|int $form_id Form ID.
	 * @param string     $view    View name.
	 *
	 * @return string
	 */
	private function get_edit_url( $form_id, string $view = '' ): string {

		if ( empty( $view ) || ! in_array( $view, $this->panels, true ) ) {
			$view = 'fields';
		}

		return add_query_arg(
			[
				'view'    => $view,
				'form_id' => $form_id,
			],
			admin_url( 'admin.php?page=wpforms-builder' )
		);
	}

	/**
	 * Define TinyMCE buttons to use with our fancy editor instances.
	 *
	 * @since 1.0.3
	 *
	 * @param array $buttons List of default buttons.
	 *
	 * @return array
	 * @noinspection PhpMissingParamTypeInspection
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function tinymce_buttons( $buttons ): array { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found

		return [ 'colorpicker', 'lists', 'wordpress', 'wpeditimage', 'wplink' ];
	}

	/**
	 * Load panels.
	 *
	 * @since 1.0.0
	 */
	public function load_panels(): void {

		// Base class and functions.
		require_once WPFORMS_PLUGIN_DIR . 'includes/admin/builder/panels/class-base.php';

		/**
		 * Form Builder panels slugs array filter.
		 *
		 * Allows developers to disable loading of some builder panels.
		 *
		 * @since 1.0.0
		 *
		 * @param array $panels Panels slugs array.
		 */
		$this->panels = apply_filters(
			'wpforms_builder_panels',
			[
				'setup',
				'fields',
				'settings',
				'providers',
				'payments',
				'revisions',
			]
		);

		foreach ( $this->panels as $panel ) {
			$panel    = sanitize_file_name( $panel );
			$file     = WPFORMS_PLUGIN_DIR . "includes/admin/builder/panels/class-{$panel}.php";
			$file_pro = WPFORMS_PLUGIN_DIR . "pro/includes/admin/builder/panels/class-{$panel}.php";

			if ( file_exists( $file ) ) {
				require_once $file;
			} elseif ( file_exists( $file_pro ) ) {
				require_once $file_pro;
			}
		}
	}

	/**
	 * Admin head area inside the form builder.
	 *
	 * @since 1.4.6
	 */
	public function admin_head(): void {

		// Force hide an admin side menu.
		echo '<style>#adminmenumain { display: none !important }</style>';

		/**
		 * Form Builder admin head action.
		 *
		 * @param string $view Current view.
		 *
		 * @since 1.4.6
		 */
		do_action( 'wpforms_builder_admin_head', $this->view );
	}

	/**
	 * Enqueue assets for the builder.
	 *
	 * @since 1.0.0
	 * @since 1.6.8 All the panel's stylesheets restructured and moved here.
	 */
	public function enqueues(): void {

		$this->suppress_conflicts();

		/**
		 * Form Builder enqueues before action.
		 *
		 * @param string $view Current view.
		 *
		 * @since 1.0.0
		 */
		do_action( 'wpforms_builder_enqueues_before', $this->view );

		$min = wpforms_get_min_suffix();

		/*
		 * Builder CSS.
		 */
		$builder_styles = [
			'overlay',
			'basic',
			'third-party',
			'alerts',
			'ui-general',
			'panels',
			'subsystems',
			'fields',
			'fields-types',
		];

		foreach ( $builder_styles as $style ) {
			wp_enqueue_style(
				$style === 'basic' ? 'wpforms-builder' : 'wpforms-builder-' . $style,
				WPFORMS_PLUGIN_URL . "assets/css/builder/builder-{$style}{$min}.css",
				[],
				WPFORMS_VERSION
			);
		}

		/*
		 * Third-party CSS.
		 */
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
			'tooltipster',
			WPFORMS_PLUGIN_URL . 'assets/lib/jquery.tooltipster/jquery.tooltipster.min.css',
			null,
			'4.2.6'
		);

		// jQuery.Confirm Reloaded.
		wp_enqueue_style(
			'jquery-confirm',
			WPFORMS_PLUGIN_URL . 'assets/lib/jquery.confirm/jquery-confirm.min.css',
			null,
			'1.0.0'
		);

		wp_enqueue_style(
			'minicolors',
			WPFORMS_PLUGIN_URL . 'assets/lib/jquery.minicolors/jquery.minicolors.min.css',
			null,
			'2.3.6'
		);

		// Remove TinyMCE editor styles from third-party themes and plugins.
		remove_editor_styles();

		/*
		 * JavaScript.
		 */
		wp_enqueue_media();
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'jquery-ui-draggable' );
		wp_enqueue_script( 'wp-util' );

		wp_enqueue_script(
			'tooltipster',
			WPFORMS_PLUGIN_URL . 'assets/lib/jquery.tooltipster/jquery.tooltipster.min.js',
			[ 'jquery' ],
			'4.2.6',
			false
		);

		// jQuery.Confirm Reloaded.
		wp_enqueue_script(
			'jquery-confirm',
			WPFORMS_PLUGIN_URL . 'assets/lib/jquery.confirm/jquery-confirm.min.js',
			[ 'jquery' ],
			'1.0.0',
			false
		);

		wp_enqueue_script(
			'insert-at-caret',
			WPFORMS_PLUGIN_URL . 'assets/lib/jquery.insert-at-caret.min.js',
			[ 'jquery' ],
			'1.1.4',
			false
		);

		wp_enqueue_script(
			'minicolors',
			WPFORMS_PLUGIN_URL . 'assets/lib/jquery.minicolors/jquery.minicolors.min.js',
			[ 'jquery' ],
			'2.3.6',
			false
		);

		wp_enqueue_script(
			'conditions',
			WPFORMS_PLUGIN_URL . 'assets/lib/conditions.min.js',
			[ 'jquery' ],
			'1.1.0',
			false
		);

		wp_enqueue_script(
			'choicesjs',
			WPFORMS_PLUGIN_URL . 'assets/lib/choices.min.js',
			[],
			'10.2.0',
			false
		);

		wp_enqueue_script(
			'listjs',
			WPFORMS_PLUGIN_URL . 'assets/lib/list.min.js',
			[ 'jquery' ],
			'2.3.0',
			false
		);

		wp_enqueue_script(
			'dom-purify',
			WPFORMS_PLUGIN_URL . 'assets/lib/purify.min.js',
			[],
			'3.3.2',
			false
		);

		if ( wp_is_mobile() ) {
			wp_enqueue_script( 'jquery-touch-punch' );
		}

		wp_enqueue_script(
			'wpforms-utils',
			WPFORMS_PLUGIN_URL . "assets/js/admin/share/admin-utils{$min}.js",
			[ 'jquery', 'dom-purify' ],
			WPFORMS_VERSION,
			false
		);

		wp_enqueue_script(
			'wpforms-generic-utils',
			WPFORMS_PLUGIN_URL . "assets/js/share/utils{$min}.js",
			[ 'jquery' ],
			WPFORMS_VERSION,
			false
		);

		wp_enqueue_script(
			'wpforms-builder-choicesjs',
			WPFORMS_PLUGIN_URL . "assets/js/admin/builder/wpforms-choicesjs{$min}.js",
			[ 'jquery', 'choicesjs' ],
			WPFORMS_VERSION,
			false
		);

		wp_enqueue_script(
			'wpforms-admin-builder-dropdown-list',
			WPFORMS_PLUGIN_URL . "assets/js/admin/builder/dropdown-list{$min}.js",
			[ 'jquery', 'listjs' ],
			WPFORMS_VERSION,
			true
		);

		wp_enqueue_script(
			'wpforms-oops',
			WPFORMS_PLUGIN_URL . 'assets/lib/oops.min.js',
			[],
			'1.0.2',
			false
		);

		wp_enqueue_script(
			'wpforms-builder',
			WPFORMS_PLUGIN_URL . "assets/js/admin/builder/admin-builder{$min}.js",
			[
				'wpforms-utils',
				'wpforms-generic-utils',
				'wpforms-admin-builder-templates',
				'jquery-ui-sortable',
				'jquery-ui-draggable',
				'tooltipster',
				'jquery-confirm',
				'choicesjs',
				'wpforms-builder-choicesjs',
			],
			WPFORMS_VERSION,
			false
		);

		wp_enqueue_script(
			'wpforms-admin-builder-templates',
			WPFORMS_PLUGIN_URL . "assets/js/admin/builder/templates{$min}.js",
			[ 'wp-util' ],
			WPFORMS_VERSION,
			true
		);

		wp_enqueue_script(
			'wpforms-builder-smart-tags',
			WPFORMS_PLUGIN_URL . "assets/js/admin/builder/smart-tags{$min}.js",
			[ 'wpforms-builder' ],
			WPFORMS_VERSION,
			true
		);

		wp_enqueue_script(
			'wpforms-builder-field-map',
			WPFORMS_PLUGIN_URL . "assets/js/admin/builder/field-map{$min}.js",
			[ 'wpforms-builder' ],
			WPFORMS_VERSION,
			true
		);

		wp_register_script(
			'wpforms-builder-choices-list',
			WPFORMS_PLUGIN_URL . "assets/js/admin/builder/choices-list{$min}.js",
			[ 'jquery' ],
			WPFORMS_VERSION,
			true
		);

		wp_register_script(
			'wpforms-builder-chocolate-choices',
			WPFORMS_PLUGIN_URL . "assets/js/admin/builder/chocolate-choices{$min}.js",
			[ 'jquery' ],
			WPFORMS_VERSION,
			true
		);

		wp_localize_script(
			'wpforms-builder',
			'wpforms_builder',
			$this->get_localized_strings()
		);

		/**
		 * Form Builder enqueues action.
		 *
		 * Executes after all the form builder assets were enqueued.
		 * Intended to use in addons.
		 *
		 * @since 1.0.0
		 *
		 * @param string $view Current view.
		 */
		do_action( 'wpforms_builder_enqueues', $this->view );
	}

	/**
	 * Remove conflicting scripts and styles.
	 *
	 * @since 1.9.0
	 */
	private function suppress_conflicts(): void {

		// Remove conflicting styles (e.g., WP JobSearch plugin).
		wp_deregister_style( 'font-awesome' );

		// Remove conflicting scripts.
		wp_deregister_script( 'serialize-object' );
		wp_deregister_script( 'wpclef-ajax-settings' );
	}

	/**
	 * Get localized strings.
	 *
	 * @since 1.6.8
	 *
	 * @return array
	 * @noinspection HtmlUnknownTarget
	 */
	private function get_localized_strings(): array {

		/**
		 * It is a phpcs bug. This local variable is used below.
		 *
		 * @noinspection PhpUnusedLocalVariableInspection
		 */
		$min = wpforms_get_min_suffix();

		/**
		 * It is a phpcs bug. This local variable is used below.
		 *
		 * @noinspection PhpUnusedLocalVariableInspection
		 */
		$image_extensions = wpforms_chain( get_allowed_mime_types() )
			->map(
				static function ( $mime ) {

					return strpos( $mime, 'image/' ) === 0 ? $mime : '';
				}
			)
			->array_filter()
			->array_values()
			->value();

		$strings = [
			'and'                                     => esc_html__( 'And', 'wpforms-lite' ),
			'ajax_url'                                => admin_url( 'admin-ajax.php' ),
			'bulk_add_button'                         => esc_html__( 'Add New Choices', 'wpforms-lite' ),
			'bulk_add_show'                           => esc_html__( 'Bulk Add', 'wpforms-lite' ),
			'are_you_sure_to_close'                   => esc_html__( 'Are you sure you want to leave? You have unsaved changes', 'wpforms-lite' ),
			'bulk_add_hide'                           => esc_html__( 'Hide Bulk Add', 'wpforms-lite' ),
			'bulk_add_heading'                        => esc_html__( 'Add Choices (one per line)', 'wpforms-lite' ),
			'bulk_add_placeholder'                    => esc_html__( "Blue\nRed\nGreen", 'wpforms-lite' ),
			'bulk_add_presets_show'                   => esc_html__( 'Show presets', 'wpforms-lite' ),
			'bulk_add_presets_hide'                   => esc_html__( 'Hide presets', 'wpforms-lite' ),
			'date_select_day'                         => 'DD',
			'date_select_month'                       => 'MM',
			'date_select_year'                        => 'YYYY',
			'debug'                                   => wpforms_debug(),
			'version'                                 => WPFORMS_VERSION,
			'content_url'                             => content_url(), // Absolute URL to wp-content directory.
			'dynamic_choices'                         => [
				'limit_message' => sprintf( /* translators: %1$s - data source name (e.g., Categories, Posts), %2$s - data source type (e.g., post type, taxonomy), %3$s - display limit, %4$s - total number of items. */
					esc_html__( 'The %1$s %2$s contains over %3$s items (%4$s). This may make the field difficult for your visitors to use and/or cause the form to be slow.', 'wpforms-lite' ),
					'{source}',
					'{type}',
					'{limit}',
					'{total}'
				),
				'empty_message' => sprintf( /* translators: %1$s - data source name (e.g., Categories, Posts), %2$s - data source type (e.g., post type, taxonomy). */
					esc_html__( 'This field will not be displayed in your form since there are no %2$s belonging to %1$s.', 'wpforms-lite' ),
					'{source}',
					'{type}'
				),
				'entities'      => [
					'post_type' => esc_html__( 'posts', 'wpforms-lite' ),
					'taxonomy'  => esc_html__( 'terms', 'wpforms-lite' ),
				],
			],
			'cancel'                                  => esc_html__( 'Cancel', 'wpforms-lite' ),
			'ok'                                      => esc_html__( 'OK', 'wpforms-lite' ),
			'close'                                   => esc_html__( 'Close', 'wpforms-lite' ),
			'conditionals_change'                     => esc_html__( 'Due to form changes, conditional logic rules will be removed or updated:', 'wpforms-lite' ),
			'conditionals_disable'                    => esc_html__( 'Are you sure you want to disable conditional logic? This will remove the rules for this field or setting.', 'wpforms-lite' ),
			'field'                                   => esc_html__( 'Field', 'wpforms-lite' ),
			'field_locked'                            => esc_html__( 'Field Locked', 'wpforms-lite' ),
			'field_locked_msg'                        => esc_html__( 'This field cannot be deleted or duplicated.', 'wpforms-lite' ),
			'field_locked_no_delete_msg'              => esc_html__( 'This field cannot be deleted.', 'wpforms-lite' ),
			'field_locked_no_duplicate_msg'           => esc_html__( 'This field cannot be duplicated.', 'wpforms-lite' ),
			'fields_available'                        => esc_html__( 'Available Fields', 'wpforms-lite' ),
			'fields_unavailable'                      => esc_html__( 'No fields available', 'wpforms-lite' ),
			'heads_up'                                => esc_html__( 'Heads up!', 'wpforms-lite' ),
			'image_placeholder'                       => WPFORMS_PLUGIN_URL . 'assets/images/builder/placeholder-200x125.svg',
			'nonce'                                   => wp_create_nonce( 'wpforms-builder' ),
			'admin_nonce'                             => wp_create_nonce( 'wpforms-admin' ),
			'no_email_fields'                         => esc_html__( 'No email fields', 'wpforms-lite' ),
			'notification_delete'                     => esc_html__( 'Are you sure you want to delete this notification?', 'wpforms-lite' ),
			'notification_prompt'                     => esc_html__( 'Enter a notification name', 'wpforms-lite' ),
			'notification_ph'                         => esc_html__( 'Eg: User Confirmation', 'wpforms-lite' ),
			'notification_error'                      => esc_html__( 'You must provide a notification name', 'wpforms-lite' ),
			'notification_def_name'                   => esc_html__( 'Default Notification', 'wpforms-lite' ),
			'confirmation_delete'                     => esc_html__( 'Are you sure you want to delete this confirmation?', 'wpforms-lite' ),
			'confirmation_prompt'                     => esc_html__( 'Enter a confirmation name', 'wpforms-lite' ),
			'confirmation_ph'                         => esc_html__( 'Eg: Alternative Confirmation', 'wpforms-lite' ),
			'confirmation_error'                      => esc_html__( 'You must provide a confirmation name', 'wpforms-lite' ),
			'confirmation_def_name'                   => esc_html__( 'Default Confirmation', 'wpforms-lite' ),
			'save'                                    => esc_html__( 'Save', 'wpforms-lite' ),
			'saving'                                  => esc_html__( 'Saving', 'wpforms-lite' ),
			'saved'                                   => esc_html__( 'Saved!', 'wpforms-lite' ),
			'save_exit'                               => esc_html__( 'Save and Exit', 'wpforms-lite' ),
			'save_embed'                              => esc_html__( 'Save and Embed', 'wpforms-lite' ),
			'saved_state'                             => '',
			'layout_selector_show'                    => esc_html__( 'Show Layouts', 'wpforms-lite' ),
			'layout_selector_hide'                    => esc_html__( 'Hide Layouts', 'wpforms-lite' ),
			'layout_selector_layout'                  => esc_html__( 'Select your layout', 'wpforms-lite' ),
			'layout_selector_column'                  => esc_html__( 'Select your column', 'wpforms-lite' ),
			'loading'                                 => esc_html__( 'Loading', 'wpforms-lite' ),
			'template_name'                           => ! empty( $this->template['name'] ) ? $this->template['name'] : '',
			'template_slug'                           => ! empty( $this->template['slug'] ) ? $this->template['slug'] : '',
			'template_modal_title'                    => ! empty( $this->template['modal']['title'] ) ? $this->template['modal']['title'] : '',
			'template_modal_msg'                      => ! empty( $this->template['modal']['message'] ) ? $this->template['modal']['message'] : '',
			'template_modal_display'                  => ! empty( $this->template['modal_display'] ) ? $this->template['modal_display'] : '',
			'template_select'                         => esc_html__( 'Use Template', 'wpforms-lite' ),
			'template_confirm'                        => esc_html__( 'Changing the template on this form will delete existing fields, reset external connections, and unsaved changes will be lost. Are you sure you want to apply the new template?', 'wpforms-lite' ),
			'use_default_template'                    => esc_html__( 'Use Default Template', 'wpforms-lite' ),
			'embed'                                   => esc_html__( 'Embed', 'wpforms-lite' ),
			'exit'                                    => esc_html__( 'Exit', 'wpforms-lite' ),
			'exit_url'                                => wpforms_current_user_can( 'view_forms' ) ? admin_url( 'admin.php?page=wpforms-overview' ) : admin_url(),
			'exit_confirm'                            => esc_html__( 'Your form contains unsaved changes. Would you like to save your changes first.', 'wpforms-lite' ),
			'delete_confirm'                          => esc_html__( 'Are you sure you want to delete this field?', 'wpforms-lite' ),
			/* translators: %s - number of fields.*/
			'delete_confirm_multiple'                 => esc_html__( 'Are you sure you want to delete these %s fields?', 'wpforms-lite' ),
			'delete_confirm_multiple_title'           => esc_html__( 'Delete Fields', 'wpforms-lite' ),
			'delete_choice_confirm'                   => esc_html__( 'Are you sure you want to delete this choice?', 'wpforms-lite' ),
			'duplicate_confirm'                       => esc_html__( 'Are you sure you want to duplicate this field?', 'wpforms-lite' ),
			'duplicate_confirm_title'                 => esc_html__( 'Duplicate Field', 'wpforms-lite' ),
			/* translators: %s - number of fields. */
			'duplicate_confirm_multiple'              => esc_html__( 'Are you sure you want to duplicate these %s fields?', 'wpforms-lite' ),
			'duplicate_confirm_multiple_title'        => esc_html__( 'Duplicate Fields', 'wpforms-lite' ),
			'duplicate_copy'                          => esc_html__( '(copy)', 'wpforms-lite' ),
			'error_title'                             => esc_html__( 'Please enter a form name.', 'wpforms-lite' ),
			'error_choice'                            => esc_html__( 'This item must contain at least one choice.', 'wpforms-lite' ),
			'off'                                     => esc_html__( 'Off', 'wpforms-lite' ),
			'on'                                      => esc_html__( 'On', 'wpforms-lite' ),
			'or'                                      => esc_html__( 'or', 'wpforms-lite' ),
			'other'                                   => esc_html__( 'Other', 'wpforms-lite' ),
			'operator_is'                             => esc_html__( 'is', 'wpforms-lite' ),
			'operator_is_not'                         => esc_html__( 'is not', 'wpforms-lite' ),
			'operator_empty'                          => esc_html__( 'empty', 'wpforms-lite' ),
			'operator_not_empty'                      => esc_html__( 'not empty', 'wpforms-lite' ),
			'operator_contains'                       => esc_html__( 'contains', 'wpforms-lite' ),
			'operator_not_contains'                   => esc_html__( 'does not contain', 'wpforms-lite' ),
			'operator_starts'                         => esc_html__( 'starts with', 'wpforms-lite' ),
			'operator_ends'                           => esc_html__( 'ends with', 'wpforms-lite' ),
			'operator_greater_than'                   => esc_html__( 'greater than', 'wpforms-lite' ),
			'operator_less_than'                      => esc_html__( 'less than', 'wpforms-lite' ),
			'option_disabled'                         => esc_html__( 'Option Disabled', 'wpforms-lite' ),
			'payments_entries_off'                    => esc_html__( 'Entry storage is currently disabled, but is required to accept payments. Please enable in your form settings.', 'wpforms-lite' ),
			'payments_on_entries_off'                 => sprintf( /* translators: %s - marketing or gateway integration name. */
				esc_html__( 'Some third-party integrations require entry storage. If you’d like to continue, you’ll first need to disable %s.', 'wpforms-lite' ),
				'{integration}'
			),
			'entry_storage_required'                  => esc_html__( 'Entry Storage Required', 'wpforms-lite' ),
			'previous'                                => esc_html__( 'Previous', 'wpforms-lite' ),
			'provider_required_flds'                  => sprintf( /* translators: %s - marketing integration name. */
				esc_html__( 'In order to complete your form\'s %s integration, please check that all required (*) fields have been filled out.', 'wpforms-lite' ),
				'{provider}'
			),
			'rule_create'                             => esc_html__( 'Create new rule', 'wpforms-lite' ),
			'rule_create_group'                       => esc_html__( 'Add New Group', 'wpforms-lite' ),
			'rule_delete'                             => esc_html__( 'Delete rule', 'wpforms-lite' ),
			'smart_tags_dropdown_title'               => esc_html__( 'Smart Tags', 'wpforms-lite' ),
			'select_field'                            => esc_html__( '--- Select Field ---', 'wpforms-lite' ),
			'select_choice'                           => esc_html__( '--- Select Choice ---', 'wpforms-lite' ),
			'upload_image_title'                      => esc_html__( 'Upload or Choose Your Image', 'wpforms-lite' ),
			'upload_image_button'                     => esc_html__( 'Use Image', 'wpforms-lite' ),
			'upload_image_remove'                     => esc_html__( 'Remove Image', 'wpforms-lite' ),
			'upload_image_extensions'                 => $image_extensions,
			'upload_image_extensions_error'           => esc_html__( 'You tried uploading a file type that is not allowed. Please try again.', 'wpforms-lite' ),
			'provider_add_new_acc_btn'                => esc_html__( 'Add', 'wpforms-lite' ),
			'pro'                                     => wpforms()->is_pro(),
			'is_gutenberg'                            => ! is_plugin_active( 'classic-editor/classic-editor.php' ),
			'cl_fields_supported'                     => wpforms_get_conditional_logic_form_fields_supported(),
			'cl_incomplete_title'                     => esc_html__( 'Incomplete Condition', 'wpforms-lite' ),
			'cl_incomplete_message'                   => esc_html__( "You've enabled Conditional Logic but the rule is incomplete, which could affect form submission. Complete the condition or disable Conditional Logic to continue.", 'wpforms-lite' ),
			'redirect_url_field_error'                => esc_html__( 'You should enter a valid absolute address to the Confirmation Redirect URL field.', 'wpforms-lite' ),
			'add_custom_value_label'                  => esc_html__( 'Add Custom Value', 'wpforms-lite' ),
			'choice_empty_label_tpl'                  => sprintf( /* translators: %s - choice number. */
				esc_html__( 'Choice %s', 'wpforms-lite' ),
				'{number}'
			),
			'payment_choice_empty_label_tpl'          => sprintf( /* translators: %s - choice number. */
				esc_html__( 'Item %s', 'wpforms-lite' ),
				'{number}'
			),
			'error_save_form'                         => esc_html__( 'Something went wrong while saving the form. Please reload the page and try again.', 'wpforms-lite' ),
			'error_contact_support'                   => esc_html__( 'Please contact support if this behavior persists.', 'wpforms-lite' ),
			'error_select_template'                   => esc_html__( 'Something went wrong while applying the form template. Please try again. If the error persists, contact our support team.', 'wpforms-lite' ),
			'error_load_templates'                    => esc_html__( "Couldn't load the Setup panel.", 'wpforms-lite' ),
			'blank_form'                              => esc_html__( 'Blank Form', 'wpforms-lite' ),
			'something_went_wrong'                    => esc_html__( 'Something went wrong', 'wpforms-lite' ),
			'field_cannot_be_reordered'               => esc_html__( 'This field cannot be moved.', 'wpforms-lite' ),
			'empty_label'                             => esc_html__( 'Empty Label', 'wpforms-lite' ),
			'submit_text'                             => esc_html__( 'Submit', 'wpforms-lite' ),
			'name_field_formats'                      => [
				'full'   => esc_html__( 'Full', 'wpforms-lite' ),
				'first'  => esc_html__( 'First', 'wpforms-lite' ),
				'middle' => esc_html__( 'Middle', 'wpforms-lite' ),
				'last'   => esc_html__( 'Last', 'wpforms-lite' ),
			],
			'no_pages_found'                          => esc_html__( 'No results found', 'wpforms-lite' ),
			'no_results_found'                        => esc_html__( 'Sorry, no results found', 'wpforms-lite' ),
			'search'                                  => esc_html__( 'Search', 'wpforms-lite' ),
			'number_slider_error_valid_default_value' => sprintf( /* translators: %1$s - from value %2$s - to value. */
				esc_html__( 'Please enter a valid value or change the Increment. The nearest valid values are %1$s and %2$s.', 'wpforms-lite' ),
				'{from}',
				'{to}'
			),
			'form_meta'                               => $this->form_data['meta'] ?? [],
			'scrollbars_css_url'                      => WPFORMS_PLUGIN_URL . "assets/css/builder/builder-scrollbars$min.css",
			'is_ai_disabled'                          => AIHelpers::is_disabled(),
			'connection_label'                        => esc_html__( 'Connection', 'wpforms-lite' ),
			'cl_reference'                            => sprintf( /* translators: %s - Integration name. */
				esc_html__( '%s connection', 'wpforms-lite' ),
				'{integration}'
			),
		];

		$strings = $this->add_localized_currencies( $strings );

		$strings['disable_entries'] = sprintf(
			wp_kses( /* translators: %s - link to the WPForms.com doc article. */
				__( 'Disabling entry storage for this form will completely prevent any new submissions from getting saved to your site. If you still intend to keep a record of entries through notification emails, then please <a href="%s" target="_blank" rel="noopener noreferrer">test your form</a> to ensure emails are sent reliably.', 'wpforms-lite' ),
				[
					'a' => [
						'href'   => [],
						'rel'    => [],
						'target' => [],
					],
				]
			),
			esc_url(
				wpforms_utm_link(
					'https://wpforms.com/docs/how-to-properly-test-your-wordpress-forms-before-launching-checklist/',
					'Builder Notifications',
					'Testing A Form Documentation'
				)
			)
		);

		$strings['akismet_not_installed'] = sprintf(
			wp_kses( /* translators: %1$s - link to the plugin search page, %2$s - link to the WPForms.com doc article. */
				__( 'This feature cannot be used at this time because the Akismet plugin <a href="%1$s" target="_blank" rel="noopener noreferrer">has not been installed</a>. For information on how to use this feature, please <a href="%2$s" target="_blank" rel="noopener noreferrer">refer to our documentation</a>.', 'wpforms-lite' ),
				[
					'a' => [
						'href'   => [],
						'rel'    => [],
						'target' => [],
					],
				]
			),
			esc_url( admin_url( 'plugin-install.php' ) ),
			esc_url(
				wpforms_utm_link(
					'https://wpforms.com/docs/setting-up-akismet-anti-spam-protection/',
					'Builder Settings',
					'Akismet Documentation'
				)
			)
		);

		$strings['akismet_not_activated'] = sprintf(
			wp_kses( /* translators: %1$s - link to the plugin page, %2$s - link to the WPForms.com doc article. */
				__( 'This feature cannot be used at this time because the Akismet plugin <a href="%1$s" target="_blank" rel="noopener noreferrer">has not been activated</a>. For information on how to use this feature, please <a href="%2$s" target="_blank" rel="noopener noreferrer">refer to our documentation</a>.', 'wpforms-lite' ),
				[
					'a' => [
						'href'   => [],
						'rel'    => [],
						'target' => [],
					],
				]
			),
			esc_url( admin_url( 'plugins.php' ) ),
			esc_url(
				wpforms_utm_link(
					'https://wpforms.com/docs/setting-up-akismet-anti-spam-protection/',
					'Builder Settings',
					'Akismet Documentation'
				)
			)
		);

		$strings['akismet_no_api_key'] = sprintf(
			wp_kses( /* translators: %1$s - link to the Akismet settings page, %2$s - link to the WPForms.com doc article. */
				__( 'This feature cannot be used at this time because the Akismet plugin <a href="%1$s" target="_blank" rel="noopener noreferrer">has not been properly configured</a>. For information on how to use this feature, please <a href="%2$s" target="_blank" rel="noopener noreferrer">refer to our documentation</a>.', 'wpforms-lite' ),
				[
					'a' => [
						'href'   => [],
						'rel'    => [],
						'target' => [],
					],
				]
			),
			esc_url( admin_url( 'options-general.php?page=akismet-key-config&view=start' ) ),
			esc_url(
				wpforms_utm_link(
					'https://wpforms.com/docs/setting-up-akismet-anti-spam-protection/',
					'Builder Settings',
					'Akismet Documentation'
				)
			)
		);

		$strings['error_save_form_forbidden'] = sprintf(
			wp_kses( /* translators: %1$s - Documentation page URL. */
				__( 'The form cannot be saved due to a <a href="%1$s" target="_blank" rel="noopener noreferrer">403 error</a>.', 'wpforms-lite' ),
				[
					'a' => [
						'href'   => [],
						'target' => [],
						'rel'    => [],
					],
				]
			),
			wpforms_utm_link( 'https://wpforms.com/docs/troubleshooting-403-forbidden-errors/', 'Builder - Settings', '403 Form Errors' )
		);

		$strings['js_modules'] = $this->get_js_modules();

		/**
		 * Form Builder localized strings filter.
		 *
		 * @since 1.8.0
		 *
		 * @param array   $strings Localized strings.
		 * @param WP_Post $form    Form object.
		 */
		$strings = (array) apply_filters( 'wpforms_builder_strings', $strings, $this->form );

		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		if ( ! empty( $_GET['form_id'] ) ) {
			$form_id = (int) $_GET['form_id'];

			$strings['preview_url'] = esc_url( add_query_arg( 'new_window', 1, wpforms_get_form_preview_url( $form_id ) ) );
			$strings['entries_url'] = esc_url( admin_url( 'admin.php?page=wpforms-entries&view=list&form_id=' . $form_id ) );
		}
		// phpcs:enable

		return $strings;
	}

	/**
	 * Get JS modules.
	 *
	 * Modules become available in the browser context as `WPForms.Admin.Builder.{key}`,
	 * for example, `WPForms.Admin.Builder.CopyPaste`.
	 *
	 * @since 1.9.9
	 *
	 * @return array List of JS modules.
	 */
	public function get_js_modules(): array {

		$min = wpforms_get_min_suffix();

		$modules = [
			'UIGeneral'                         => "ui-general$min.js",
			'Panels'                            => "panels$min.js",
			'ReadOnlyField'                     => "read-only-field$min.js",
			'IconChoices'                       => "icon-choices$min.js",
			'DisabledFields'                    => "disabled-fields$min.js",
			'DropdownField'                     => "dropdown-field$min.js",
			'FieldHelpers'                      => "field-helpers$min.js",
			'FieldChoice'                       => "field-choices$min.js",
			'FieldsPanel'                       => "fields-panel$min.js",
			'NumberSliderField'                 => "number-slider-field$min.js",
			'PageBreakField'                    => "page-break-field$min.js",
			'EntryPreview'                      => "entry-preview$min.js",
			'LegacyLayout'                      => "legacy-layout$min.js",
			'RevisionsPanel'                    => "revisions-panel$min.js",
			'SettingsPanel'                     => "settings-panel$min.js",
			'SettingsConfirmations'             => "settings-confirmations$min.js",
			'SettingsNotifications'             => "settings-notifications$min.js",
			'BuilderProviders'                  => "builder-providers$min.js",
			'Captcha'                           => "captcha$min.js",
			'SaveExit'                          => "save-exit$min.js",
			'KeyboardShortcuts'                 => "keyboard-shortcuts$min.js",
			'DragFields'                        => "drag-fields$min.js",
			'DragFieldsMultiSelect'             => "drag-fields-multi-select$min.js",
			'UndoRedoHelpers'                   => "undo-redo/helpers$min.js",
			'UndoRedoHelpersFields'             => "undo-redo/helpers-fields$min.js",
			'UndoRedoInputCommandBase'          => "undo-redo/input-command-base$min.js",
			'UndoRedoActionCommandBase'         => "undo-redo/action-command-base$min.js",
			'UndoRedoInputSimple'               => "undo-redo/input-simple$min.js",
			'UndoRedoInputToggle'               => "undo-redo/input-toggle$min.js",
			'UndoRedoInputChoicesJS'            => "undo-redo/input-choicesjs$min.js",
			'UndoRedoInputProviderConnection'   => "undo-redo/input-provider-connection$min.js",
			'UndoRedoInputSmartTags'            => "undo-redo/input-smart-tags$min.js",
			'UndoRedoInputCodeMirror'           => "undo-redo/input-codemirror$min.js",
			'UndoRedoInputTinyMCE'              => "undo-redo/input-tinymce$min.js",
			'UndoRedoChoicesList'               => "undo-redo/choices-list$min.js",
			'UndoRedoFormThemes'                => "undo-redo/form-themes$min.js",
			'UndoRedoDateTimePickers'           => "undo-redo/date-time-pickers$min.js",
			'UndoRedoActionFieldAdd'            => "undo-redo/action-field-add$min.js",
			'UndoRedoActionFieldDelete'         => "undo-redo/action-field-delete$min.js",
			'UndoRedoActionFieldDuplicate'      => "undo-redo/action-field-duplicate$min.js",
			'UndoRedoActionFieldMove'           => "undo-redo/action-field-move$min.js",
			'UndoRedoActionMultiFieldDelete'    => "undo-redo/action-multi-field-delete$min.js",
			'UndoRedoActionMultiFieldDuplicate' => "undo-redo/action-multi-field-duplicate$min.js",
			'UndoRedoActionMultiFieldPaste'     => "undo-redo/action-multi-field-paste$min.js",
			'UndoRedoActionSettingsBlockAdd'    => "undo-redo/action-settings-block-add$min.js",
			'UndoRedoActionSettingsBlockDelete' => "undo-redo/action-settings-block-delete$min.js",
			'UndoRedoActionItemsAddRemove'      => "undo-redo/action-items-add-remove$min.js",
			'UndoRedoActionImageAddRemove'      => "undo-redo/action-image-add-remove$min.js",
			'UndoRedo'                          => "undo-redo$min.js",
			'MultiSelectActions'                => "multi-select/actions$min.js",
			'MultiSelect'                       => "multi-select/multi-select$min.js",
			'MultiSelectKeyboardShortcuts'      => "multi-select/keyboard-shortcuts$min.js",
			'CopyPaste'                         => "copy-paste$min.js",
			'Deprecated'                        => "deprecated$min.js",
		];

		/**
		 * Filters the list of Form Builder JS modules.
		 *
		 * Allows developers to add their own modules.
		 * The modules are loaded asynchronously.
		 * The custom module's path value should be absolute.
		 *
		 * @since 1.9.9
		 *
		 * @param array $modules List of JS modules.
		 */
		return apply_filters( 'wpforms_builder_js_modules', $modules );
	}

	/**
	 * Footer JavaScript.
	 *
	 * @since 1.3.7
	 */
	public function footer_scripts(): void {

		$countries        = wpforms_countries();
		$countries_postal = array_keys( $countries );
		$countries        = array_values( $countries );

		sort( $countries_postal );
		sort( $countries );

		$choices = [
			'countries'        => [
				'name'    => esc_html__( 'Countries', 'wpforms-lite' ),
				'choices' => $countries,
			],
			'countries_postal' => [
				'name'    => esc_html__( 'Countries Postal Code', 'wpforms-lite' ),
				'choices' => $countries_postal,
			],
			'states'           => [
				'name'    => esc_html__( 'States', 'wpforms-lite' ),
				'choices' => array_values( wpforms_us_states() ),
			],
			'states_postal'    => [
				'name'    => esc_html__( 'States Postal Code', 'wpforms-lite' ),
				'choices' => array_keys( wpforms_us_states() ),
			],
			'months'           => [
				'name'    => esc_html__( 'Months', 'wpforms-lite' ),
				'choices' => array_values( wpforms_months() ),
			],
			'days'             => [
				'name'    => esc_html__( 'Days', 'wpforms-lite' ),
				'choices' => array_values( wpforms_days() ),
			],
		];

		// phpcs:disable WPForms.Comments.ParamTagHooks.InvalidParamTagsQuantity
		/**
		 * Choice preset array filter.
		 *
		 * Allows developers to edit the choice preset used in all choice-based fields.
		 *
		 * @since 1.3.7
		 *
		 * @param array $choices {
		 *    Choices presets is the [ `slug` => `preset`, ... ] array.
		 *
		 *    @param array $preset {
		 *        Each preset data is the array with two elements:
		 *
		 *        @param string $name    Name of the preset
		 *        @param array  $choices Choices array.
		 *    }
		 *    ...
		 * }
		 */
		$choices = apply_filters( 'wpforms_builder_preset_choices', $choices );
		// phpcs:enable WPForms.Comments.ParamTagHooks.InvalidParamTagsQuantity

		echo '<script type="text/javascript">wpforms_preset_choices=' . wp_json_encode( $choices ) . '</script>';

		/**
		 * Form Builder footer scripts action.
		 *
		 * @since 1.3.8
		 */
		do_action( 'wpforms_builder_print_footer_scripts' );
	}

	/**
	 * Load the appropriate files to build the page.
	 *
	 * @since 1.0.0
	 *
	 * @noinspection OnlyWritesOnParameterInspection
	 */
	public function output(): void { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		if ( $this->abort ) {
			return;
		}

		/**
		 * Allow developers to disable Form Builder output.
		 *
		 * @since 1.5.8.2
		 *
		 * @param bool $is_enabled Is builder output enabled? Defaults to `true`.
		 */
		if ( ! apply_filters( 'wpforms_builder_output', true ) ) {
			return;
		}

		$form_id         = $this->form ? absint( $this->form->ID ) : '';
		$field_id        = ! empty( $this->form_data['field_id'] ) ? $this->form_data['field_id'] : '';
		$revisions_obj   = wpforms()->obj( 'revisions' );
		$revision        = $revisions_obj ? $revisions_obj->get_revision() : null;
		$preview_url     = wpforms_get_form_preview_url( $form_id, true );
		$allowed_caps    = [ 'edit_posts', 'edit_other_posts', 'edit_private_posts', 'edit_published_posts', 'edit_pages', 'edit_other_pages', 'edit_published_pages', 'edit_private_pages' ];
		$can_embed       = array_filter( $allowed_caps, 'current_user_can' );
		$preview_classes = [ 'wpforms-btn', 'wpforms-btn-toolbar', 'wpforms-btn-light-grey' ];
		$builder_classes = [ 'wpforms-admin-page' ];

		if ( ! $can_embed ) {
			$preview_classes[] = 'wpforms-alone';
		}

		$revision_id = null;

		if ( $revision ) {
			$revision_id       = $revision->ID;
			$builder_classes[] = 'wpforms-is-revision';
		}

		if ( $this->form && wp_revisions_enabled( $this->form ) ) {
			$builder_classes[] = 'wpforms-revisions-enabled';
		}

		/**
		 * Allow modifying builder container classes.
		 *
		 * @since 1.7.9
		 *
		 * @param array      $classes   List of classes.
		 * @param array|bool $form_data Form data and settings or false when the form isn't created.
		 */
		$builder_classes = (array) apply_filters( 'wpforms_builder_output_classes', $builder_classes, $this->form_data );

		/**
		 * Allow developers to add content before the top toolbar in the Form Builder.
		 *
		 * @since 1.7.4
		 *
		 * @param string $content Content before the toolbar. Defaults to empty string.
		 */
		$before_toolbar = (string) apply_filters( 'wpforms_builder_output_before_toolbar', '' );

		$args = compact(
			[
				'before_toolbar',
				'builder_classes',
				'can_embed',
				'field_id',
				'form_id',
				'preview_classes',
				'preview_url',
				'revision',
				'revision_id',
			]
		);

		$this->print_output( $args );
	}

	/**
	 * Display an abort message using empty state page.
	 *
	 * @since 1.7.3
	 */
	public function display_abort_message(): void {

		if ( ! $this->abort ) {
			return;
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo wpforms_render(
			'builder/fullscreen/abort-message',
			[
				'message' => $this->abort_message,
			],
			true
		);
	}

	/**
	 * Change the default admin meta-viewport tag upon request to force a scrollable desktop view on small screens.
	 *
	 * @since 1.7.8
	 *
	 * @param string|mixed $value Default meta viewport tag value.
	 *
	 * @return string
	 */
	public function viewport_meta( $value ): string {

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! empty( $_GET['force_desktop_view'] ) ) {
			return 'width=1024, initial-scale=1';
		}

		return (string) $value;
	}

	/**
	 * Get localized currency strings for the builder.
	 *
	 * @since 1.8.2
	 *
	 * @param array $strings Array of localized strings.
	 *
	 * @return array
	 */
	private function add_localized_currencies( array $strings ): array {

		$currency   = wpforms_get_currency();
		$currencies = wpforms_get_currencies();

		$strings['currency']            = sanitize_text_field( $currency );
		$strings['currency_name']       = isset( $currencies[ $currency ]['name'] ) ? sanitize_text_field( $currencies[ $currency ]['name'] ) : '';
		$strings['currency_decimals']   = wpforms_get_currency_decimals( $currencies[ $currency ] );
		$strings['currency_decimal']    = isset( $currencies[ $currency ]['decimal_separator'] ) ? sanitize_text_field( $currencies[ $currency ]['decimal_separator'] ) : '.';
		$strings['currency_thousands']  = isset( $currencies[ $currency ]['thousands_separator'] ) ? sanitize_text_field( $currencies[ $currency ]['thousands_separator'] ) : ',';
		$strings['currency_symbol']     = isset( $currencies[ $currency ]['symbol'] ) ? sanitize_text_field( $currencies[ $currency ]['symbol'] ) : '$';
		$strings['currency_symbol_pos'] = isset( $currencies[ $currency ]['symbol_pos'] ) ? sanitize_text_field( $currencies[ $currency ]['symbol_pos'] ) : 'left';

		return $strings;
	}

	/**
	 * Get context menu arguments, depending on the Lite/Pro version and form or form template type.
	 *
	 * @since 1.8.8
	 *
	 * @return array
	 */
	private function get_context_menu_args(): array {

		$payment_obj = wpforms()->obj( 'payment' );

		$args = [
			'form_id'          => $this->form->ID,
			'is_form_template' => $this->form->post_type === 'wpforms-template',
			'has_payments'     => $payment_obj && $payment_obj->get_by( 'form_id', $this->form->ID ),
		];

		if ( wpforms()->is_pro() ) {
			$entry_obj             = wpforms()->obj( 'entry' );
			$args['has_entries']   = $entry_obj && $entry_obj->get_entries( [ 'form_id' => $this->form->ID ], true );
			$args['can_duplicate'] = $this->can_duplicate();
		}

		return $args;
	}

	/**
	 * Check if the current user is allowed to duplicate the form.
	 *
	 * @since 1.8.8
	 *
	 * @return bool
	 */
	private function can_duplicate(): bool {

		if ( ! wpforms_current_user_can( 'create_forms' ) ) {
			return false;
		}

		if ( ! wpforms_current_user_can( 'view_form_single', $this->form->ID ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Print output.
	 *
	 * @since 1.10.0
	 *
	 * @param array $args Arguments.
	 *
	 * @return void
	 */
	private function print_output( array $args ): void {

		?>
		<div id="wpforms-builder" class="<?php echo wpforms_sanitize_classes( $args['builder_classes'], true ); ?>">
			<?php

			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo wpforms_render( 'builder/fullscreen/ie-notice' );

			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( empty( $_GET['force_desktop_view'] ) ) {
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo wpforms_render( 'builder/fullscreen/mobile-notice' );
			}

			?>
			<div id="wpforms-builder-overlay">
				<div class="wpforms-builder-overlay-content">
					<i class="spinner"></i>
					<i class="avatar"></i>
				</div>
			</div>

			<form
					name="wpforms-builder" id="wpforms-builder-form" method="post"
					data-id="<?php echo esc_attr( $args['form_id'] ); ?>"
					data-revision="<?php echo esc_attr( $args['revision_id'] ); ?>"
			>
				<input type="hidden" name="id" value="<?php echo esc_attr( $args['form_id'] ); ?>">
				<input type="hidden" value="<?php echo wpforms_validate_field_id( $args['field_id'] ); ?>" name="field_id" id="wpforms-field-id">

				<?php echo $args['before_toolbar']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

				<!-- Toolbar -->
				<div class="wpforms-toolbar">
					<div class="wpforms-left">
						<img src="<?php echo esc_url( WPFORMS_PLUGIN_URL . 'assets/images/sullie-alt.png' ); ?>" alt="<?php esc_attr_e( 'Sullie the WPForms mascot', 'wpforms-lite' ); ?>">
					</div>

					<div class="wpforms-center">
						<?php if ( $this->form ) : ?>
							<span class="wpforms-center-form-name-prefix">
								<?php esc_html_e( 'Now editing', 'wpforms-lite' ); ?>
							</span>

							<span class="wpforms-center-form-name wpforms-form-name">
								<?php echo esc_html( $this->form_data['settings']['form_title'] ?? $this->form->post_title ); ?>
							</span>

							<?php if ( $this->form->post_type === 'wpforms-template' ) : ?>
								<span class="wpforms-center-form-template-badge">
									<?php echo esc_html__( 'Template', 'wpforms-lite' ); ?>
								</span>
							<?php endif; ?>
						<?php endif; ?>
					</div>

					<div class="wpforms-right">
						<button id="wpforms-help"
							class="js-wpforms-help wpforms-btn wpforms-btn-toolbar wpforms-btn-light-grey"
							title="<?php esc_attr_e( 'Help Ctrl+H', 'wpforms-lite' ); ?>">
								<i class="fa fa-question-circle-o"></i>
								<span<?php echo $this->form ? ' class="screen-reader-text"' : ''; ?>>
									<?php esc_html_e( 'Help', 'wpforms-lite' ); ?>
								</span>
						</button>

						<?php if ( $this->form ) : ?>
							<div
									id="wpforms-context-menu-container"
									class="wpforms-btn wpforms-btn-toolbar wpforms-btn-light-grey">
								<svg width="16" height="16" fill="currentColor">
									<path d="M2 4a2 2 0 0 0 2-2 2 2 0 0 0-2-2 2 2 0 0 0-2 2c0 1.1.9 2 2 2Zm6 12a2 2 0 0 0 2-2 2 2 0 0 0-2-2 2 2 0 0 0-2 2c0 1.1.9 2 2 2Zm-6 0a2 2 0 0 0 2-2 2 2 0 0 0-2-2 2 2 0 0 0-2 2c0 1.1.9 2 2 2Zm0-6a2 2 0 0 0 2-2 2 2 0 0 0-2-2 2 2 0 0 0-2 2c0 1.1.9 2 2 2Zm6 0a2 2 0 0 0 2-2 2 2 0 0 0-2-2 2 2 0 0 0-2 2c0 1.1.9 2 2 2Zm4-8c0 1.1.9 2 2 2a2 2 0 0 0 2-2 2 2 0 0 0-2-2 2 2 0 0 0-2 2ZM8 4a2 2 0 0 0 2-2 2 2 0 0 0-2-2 2 2 0 0 0-2 2c0 1.1.9 2 2 2Zm6 6a2 2 0 0 0 2-2 2 2 0 0 0-2-2 2 2 0 0 0-2 2c0 1.1.9 2 2 2Zm0 6a2 2 0 0 0 2-2 2 2 0 0 0-2-2 2 2 0 0 0-2 2c0 1.1.9 2 2 2Z"/>
								</svg>
								<?php echo wpforms_render( 'builder/context-menu', $this->get_context_menu_args(), true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</div>

							<?php if ( ! $args['revision'] ) : ?>
								<a
										href="<?php echo esc_url( $args['preview_url'] ); ?>"
										id="wpforms-preview-btn"
										class="<?php echo wpforms_sanitize_classes( $args['preview_classes'], true ); ?>"
										title="<?php esc_attr_e( 'Preview Form Ctrl+P', 'wpforms-lite' ); ?>"
										target="_blank"
										rel="noopener noreferrer">
									<i class="fa fa-eye"></i>
									<span class="text"><?php esc_html_e( 'Preview', 'wpforms-lite' ); ?></span>
								</a>
							<?php endif; ?>

							<?php if ( $args['can_embed'] && ! $args['revision'] ) : ?>
								<?php if ( $this->form->post_type === 'wpforms-template' ) : ?>
									<button id="wpforms-embed"
											class="wpforms-btn wpforms-btn-toolbar wpforms-btn-light-grey wpforms-btn-light-grey-disabled"
											title="<?php esc_attr_e( 'You cannot embed a form template', 'wpforms-lite' ); ?>">
										<i class="fa fa-code"></i><span class="text"><?php esc_html_e( 'Embed', 'wpforms-lite' ); ?></span>
									</button>
								<?php else : ?>
									<button id="wpforms-embed"
											class="wpforms-btn wpforms-btn-toolbar wpforms-btn-light-grey"
											title="<?php esc_attr_e( 'Embed Form Ctrl+B', 'wpforms-lite' ); ?>">
										<i class="fa fa-code"></i><span class="text"><?php esc_html_e( 'Embed', 'wpforms-lite' ); ?></span>
									</button>
								<?php endif; ?>
							<?php endif; ?>

							<button id="wpforms-save"
									class="wpforms-btn wpforms-btn-toolbar wpforms-btn-orange"
									title="<?php esc_attr_e( 'Save Form Ctrl+S', 'wpforms-lite' ); ?>">
								<i class="fa fa-check"></i><i class="wpforms-loading-spinner wpforms-loading-white wpforms-loading-inline wpforms-hidden"></i>
								<span class="text"><?php esc_html_e( 'Save', 'wpforms-lite' ); ?></span>
							</button>
						<?php endif; ?>

						<button id="wpforms-exit" title="<?php esc_attr_e( 'Exit Ctrl+Q', 'wpforms-lite' ); ?>">
							<i class="fa fa-times"></i>
						</button>
					</div>
				</div>

				<!-- Panel toggle buttons. -->
				<div class="wpforms-panels-toggle" id="wpforms-panels-toggle">
					<?php

					/**
					 * Outputs the buttons to toggle between Form Builder panels.
					 *
					 * @since 1.0.0
					 *
					 * @param WP_Post $form The form object.
					 * @param string  $view Current view (panel) name.
					 */
					do_action( 'wpforms_builder_panel_buttons', $this->form, $this->view );

					?>
				</div>

				<div class="wpforms-panels">
					<?php

					/**
					 * Outputs the contents of Form Builder panels.
					 *
					 * @since 1.0.0
					 *
					 * @param WP_Post $form The form object.
					 * @param string  $view Current view (panel) name.
					 */
					do_action( 'wpforms_builder_panels', $this->form, $this->view );

					?>
				</div>
			</form>
		</div>
		<?php
	}
}

WPForms_Builder::instance();
