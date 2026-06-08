<?php

namespace WPForms\Admin\Builder;

use WP_Query;

/**
 * Templates class.
 *
 * @since 1.6.8
 */
class Templates {

	/**
	 * Templates hash option.
	 *
	 * @since 1.8.6
	 *
	 * @var string
	 */
	const TEMPLATES_HASH_OPTION = 'wpforms_templates_hash';

	/**
	 * Favorite templates option.
	 *
	 * @since 1.7.7
	 *
	 * @var string
	 */
	const FAVORITE_TEMPLATES_OPTION = 'wpforms_favorite_templates';

	/**
	 * All templates data from API.
	 *
	 * @since 1.6.8
	 *
	 * @var array
	 */
	private $api_templates = [];

	/**
	 * Template categories data.
	 *
	 * @since 1.6.8
	 *
	 * @var array
	 */
	private $categories;

	/**
	 * Template subcategories data.
	 *
	 * @since 1.8.4
	 *
	 * @var array
	 */
	private $subcategories;

	/**
	 * License data.
	 *
	 * @since 1.6.8
	 *
	 * @var array
	 */
	private $license;

	/**
	 * All licenses list.
	 *
	 * @since 1.6.8
	 *
	 * @var array
	 */
	private $all_licenses;

	/**
	 * Favorite templates list.
	 *
	 * @since 1.8.6
	 *
	 * @var array
	 */
	private $favorites_list;

	/**
	 * Templates hash.
	 *
	 * @since 1.8.6
	 *
	 * @var string
	 */
	private $hash;

	/**
	 * Determine if the class is allowed to load.
	 *
	 * @since 1.6.8
	 *
	 * @return bool
	 */
	private function allow_load() {

		$has_permissions  = wpforms_current_user_can( [ 'create_forms', 'edit_forms' ] );
		$allowed_requests = wpforms_is_admin_ajax() || wpforms_is_admin_page( 'builder' ) || wpforms_is_admin_page( 'templates' );
		$allow            = $has_permissions && $allowed_requests;

		/**
		 * Whether to allow the form templates functionality to load.
		 *
		 * @since 1.7.2
		 *
		 * @param bool $allow True or false.
		 */
		return (bool) apply_filters( 'wpforms_admin_builder_templates_allow_load', $allow );
	}

	/**
	 * Initialize class.
	 *
	 * @since 1.6.8
	 */
	public function init() {

		if ( ! $this->allow_load() ) {
			return;
		}

		$this->init_license_data();
		$this->init_templates_data();
		$this->hooks();
	}

	/**
	 * Hooks.
	 *
	 * @since 1.6.8
	 */
	protected function hooks() {

		add_action( 'admin_init', [ $this, 'create_form_on_request' ], 100 );
		add_filter( 'wpforms_form_templates_core', [ $this, 'add_templates_to_setup_panel' ], 20 );
		add_filter( 'wpforms_create_form_args', [ $this, 'apply_to_new_form' ], 10, 2 );
		add_filter( 'wpforms_save_form_args', [ $this, 'apply_to_existing_form' ], 10, 3 );
        add_action( 'admin_print_scripts', [ $this, 'upgrade_banner_template' ] );
        add_action( 'admin_print_scripts', [ $this, 'upgrade_lite_banner_template' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueues' ] );
		add_action( 'wp_ajax_wpforms_templates_favorite', [ $this, 'ajax_save_favorites' ] );
		add_filter( 'wpforms_form_templates', [ $this, 'add_addons_templates' ] );
	}

	/**
	 * Enqueue assets for the Setup panel.
	 *
	 * @since 1.7.7
	 */
	public function enqueues() {

		$min = wpforms_get_min_suffix();

		wp_enqueue_script(
			'listjs',
			WPFORMS_PLUGIN_URL . 'assets/lib/list.min.js',
			[ 'jquery' ],
			'2.3.0',
			false
		);

		wp_enqueue_script(
			'wpforms-form-templates',
			WPFORMS_PLUGIN_URL . "assets/js/admin/builder/form-templates{$min}.js",
			[ 'underscore', 'wp-util', 'listjs' ],
			WPFORMS_VERSION,
			true
		);

		$strings = [
			'ajaxurl'                 => admin_url( 'admin-ajax.php' ),
			'admin_nonce'             => wp_create_nonce( 'wpforms-admin' ),
			'nonce'                   => wp_create_nonce( 'wpforms-form-templates' ),
			'can_install_addons'      => wpforms_can_install( 'addon' ),
			'activating'              => esc_html__( 'Activating', 'wpforms-lite' ),
			'cancel'                  => esc_html__( 'Cancel', 'wpforms-lite' ),
			'heads_up'                => esc_html__( 'Heads Up!', 'wpforms-lite' ),
			'install_confirm'         => esc_html__( 'Install and activate', 'wpforms-lite' ),
			'activate_confirm'        => esc_html__( 'Activate', 'wpforms-lite' ),
			'ok'                      => esc_html__( 'Ok', 'wpforms-lite' ),
			'template_addons_error'   => esc_html__( 'Could not install OR activate all the required addons. Please download from wpforms.com and install them manually. Would you like to use the template anyway?', 'wpforms-lite' ),
			'use_template'            => esc_html__( 'Yes, use template', 'wpforms-lite' ),
			'delete_template'         => esc_html__( 'Yes, Delete', 'wpforms-lite' ),
			'delete_template_title'   => esc_html__( 'Delete Form Template', 'wpforms-lite' ),
			'delete_template_content' => esc_html__( 'Are you sure you want to delete this form template? This cannot be undone.', 'wpforms-lite' ),
		];

		if ( $strings['can_install_addons'] ) {
			/* translators: %1$s - template name, %2$s - addon name(s). */
			$strings['template_addon_prompt'] = esc_html( sprintf( __( 'The %1$s template requires the %2$s. Would you like to install and activate it?', 'wpforms-lite' ), '%template%', '%addons%' ) );
			/* translators: %1$s - template name, %2$s - addon name(s). */
			$strings['template_addons_prompt'] = esc_html( sprintf( __( 'The %1$s template requires the %2$s. Would you like to install and activate all the required addons?', 'wpforms-lite' ), '%template%', '%addons%' ) );
			/* translators: %1$s - template name, %2$s - addon name(s). */
			$strings['template_addon_activate'] = esc_html( sprintf( __( 'The %1$s template requires the %2$s. Would you like to activate it?', 'wpforms-lite' ), '%template%', '%addons%' ) );
		} else {
			/* translators: %s - addon name(s). */
			$single_form                        = esc_html( sprintf( __( "To use all of the features in this template, you'll need the %s. Contact your site administrator to install it, then try opening this template again.", 'wpforms-lite' ), '%addons%' ) );
			$strings['template_addon_prompt']   = $single_form;
			$strings['template_addon_activate'] = $single_form;
			/* translators: %s - addon name(s). */
			$strings['template_addons_prompt'] = esc_html( sprintf( __( "To use all of the features in this template, you'll need the %s. Contact your site administrator to install them, then try opening this template again.", 'wpforms-lite' ), '%addons%' ) );
		}

		wp_localize_script(
			'wpforms-form-templates',
			'wpforms_form_templates',
			$strings
		);

		wp_localize_script(
			'wpforms-form-templates',
			'wpforms_addons',
			$this->get_localized_addons()
		);
	}

	/**
	 * Get localized addons.
	 *
	 * @since 1.8.2
	 *
	 * @return array
	 */
	private function get_localized_addons() {

		return wpforms_chain( wpforms()->obj( 'addons' )->get_available() )
			->map(
				static function( $addon ) {

					return [
						'title'  => $addon['title'],
						'action' => $addon['action'],
						'url'    => $addon['url'],
					];
				}
			)
			->value();
	}

	/**
	 * Init license data.
	 *
	 * @since 1.6.8
	 */
	private function init_license_data() {

		$this->all_licenses = [ 'lite', 'basic', 'plus', 'pro', 'elite', 'agency', 'ultimate' ];

		// User license data.
		$this->license['key']   = wpforms_get_license_key();
		$this->license['type']  = wpforms_get_license_type();
		$this->license['type']  = in_array( $this->license['type'], [ 'agency', 'ultimate' ], true ) ? 'elite' : $this->license['type'];
		$this->license['type']  = empty( $this->license['type'] ) ? 'lite' : $this->license['type'];
		$this->license['index'] = array_search( $this->license['type'], $this->all_licenses, true );
	}

	/**
	 * Init templates and categories data.
	 *
	 * @since 1.6.8
	 */
	private function init_templates_data() {

		// Get cached templates data.
		$cache_obj = wpforms()->obj( 'builder_templates_cache' );

		if ( ! $cache_obj ) {
			return;
		}

		$cache_data          = $cache_obj->get();
		$templates_all       = ! empty( $cache_data['templates'] ) ? $this->sort_templates_by_created_at( $cache_data['templates'] ) : [];
		$this->categories    = ! empty( $cache_data['categories'] ) ? $cache_data['categories'] : [];
		$this->subcategories = ! empty( $cache_data['subcategories'] ) ? $cache_data['subcategories'] : [];

		$this->init_api_templates( $templates_all );
	}

	/**
	 * Sort templates by their created_at value in ascending order.
	 *
	 * @since 1.8.4
	 *
	 * @param array $templates Templates to be sorted.
	 *
	 * @return array Sorted templates.
	 */
	private function sort_templates_by_created_at( array $templates ): array {

		uasort(
			$templates,
			static function ( $template_a, $template_b ) {

				if ( $template_a['created_at'] === $template_b['created_at'] ) {
					return 0;
				}

				return $template_a['created_at'] < $template_b['created_at'] ? -1 : 1;
			}
		);

		return $templates;
	}

	/**
	 * Determine if user's license level has access to the template.
	 *
	 * @since 1.6.8
	 *
	 * @param array $template Template data.
	 *
	 * @return bool
	 */
	private function has_access( $template ) {

		if ( ! empty( $template['has_access'] ) ) {
			return true;
		}

		$template_licenses = empty( $template['license'] ) ? [] : array_map( 'strtolower', (array) $template['license'] );
		$has_access        = true;

		foreach ( $template_licenses as $template_license ) {

			$has_access = $this->license['index'] >= array_search( $template_license, $this->all_licenses, true );

			if ( $has_access ) {
				break;
			}
		}

		return $has_access;
	}

	/**
	 * Get favorites templates list.
	 *
	 * @since 1.7.7
	 *
	 * @param bool $all Optional. True for getting all favorites lists. False by default.
	 *
	 * @return array
	 */
	public function get_favorites_list( $all = false ) {

		$favorites_list = (array) get_option( self::FAVORITE_TEMPLATES_OPTION, [] );

		if ( $all ) {
			return $favorites_list;
		}

		$user_id = get_current_user_id();

		return isset( $favorites_list[ $user_id ] ) ? $favorites_list[ $user_id ] : [];
	}

	/**
	 * Update favorites templates list.
	 *
	 * @since 1.8.6
	 */
	public function update_favorites_list() {

		$this->favorites_list = $this->get_favorites_list();
	}

	/**
	 * Determine if template is marked as favorite.
	 *
	 * @since 1.7.7
	 *
	 * @param string $template_slug Template slug.
	 *
	 * @return bool
	 */
	public function is_favorite( $template_slug ) {

		if ( $this->favorites_list === null ) {
			$this->update_favorites_list();
		}

		return isset( $this->favorites_list[ $template_slug ] );
	}

	/**
	 * Save favorites templates.
	 *
	 * @since 1.7.7
	 */
	public function ajax_save_favorites(): void {

		if ( ! $this->is_valid_ajax_request() ) {
			wp_send_json_error();
		}

		[ $template_slug, $favorite ] = $this->get_ajax_input();

		$favorites   = $this->get_favorites_list( true );
		$user_id     = get_current_user_id();
		$is_favorite = $favorite === 'true';
		$is_exists   = isset( $favorites[ $user_id ][ $template_slug ] );

		if ( $is_favorite && $is_exists ) {
			wp_send_json_success();
		}

		if ( $is_favorite ) {
			$favorites[ $user_id ][ $template_slug ] = true;
		} elseif ( $is_exists ) {
			unset( $favorites[ $user_id ][ $template_slug ] );
		}

		update_option( self::FAVORITE_TEMPLATES_OPTION, $favorites );

		// Update and save the template content cache.
		$templates_cache_obj = wpforms()->obj( 'builder_templates_cache' );

		if ( $templates_cache_obj ) {
			$templates_cache_obj->wipe_content_cache();
		}

		wp_send_json_success();
	}

	/**
	 * Get AJAX input.
	 *
	 * @since 1.9.6
	 *
	 * @return array
	 */
	protected function get_ajax_input(): array {

		// Nonce is checked in the is_valid_ajax_request() method.
		// phpcs:disable WordPress.Security.NonceVerification.Missing
		$template_slug = isset( $_POST['slug'] ) ? sanitize_text_field( wp_unslash( $_POST['slug'] ) ) : '';
		$favorite      = isset( $_POST['favorite'] ) ? sanitize_key( wp_unslash( $_POST['favorite'] ) ) : '';

		return [ $template_slug, $favorite ];
		// phpcs:enable WordPress.Security.NonceVerification.Missing
	}

	/**
	 * Determine if the AJAX request is valid.
	 *
	 * @since 1.9.5
	 *
	 * @return bool
	 */
	private function is_valid_ajax_request(): bool {

		return check_ajax_referer( 'wpforms-form-templates', 'nonce', false ) &&
			wpforms_current_user_can( 'create_forms' ) &&
			isset( $_POST['slug'], $_POST['favorite'] );
	}

	/**
	 * Determine if the template exists and the customer has access to it.
	 *
	 * @since 1.7.5.3
	 *
	 * @param string $slug Template slug or ID.
	 *
	 * @return bool
	 */
	public function is_valid_template( $slug ) {

		$template = $this->get_template_by_id( $slug );

		if ( ! $template ) {
			return ! empty( $this->get_template_by_slug( $slug ) );
		}

		$has_cache = wpforms()->obj( 'builder_template_single' )->instance( $template['id'], $this->license )->get();

		return $this->has_access( $template ) && $has_cache;
	}

	/**
	 * Determine license level of the template.
	 *
	 * @since 1.6.8
	 *
	 * @param array $template Template data.
	 *
	 * @return string
	 */
	private function get_license_level( $template ) {

		$licenses_pro      = [ 'basic', 'plus', 'pro' ];
		$licenses_template = (array) $template['license'];

		if (
			empty( $template['license'] ) ||
			in_array( 'lite', $licenses_template, true )
		) {
			return '';
		}

		foreach ( $licenses_pro as $license ) {
			if ( in_array( $license, $licenses_template, true ) ) {
				return 'pro';
			}
		}

		return 'elite';
	}

	/**
	 * Get categories data.
	 *
	 * @since 1.6.8
	 *
	 * @return array
	 */
	public function get_categories() {

		return $this->categories;
	}

	/**
	 * Get subcategories data.
	 *
	 * @since 1.8.4
	 *
	 * @return array
	 */
	public function get_subcategories() {

		return $this->subcategories;
	}

	/**
	 * Get templates data.
	 *
	 * @since 1.6.8
	 *
	 * @return array
	 */
	public function get_templates(): array {

		static $templates = [];

		if ( ! empty( $templates ) ) {
			return $templates;
		}

		// phpcs:disable WPForms.PHP.ValidateHooks.InvalidHookName

		/**
		 * Form templates available in the WPForms core plugin.
		 *
		 * @since 1.4.0
		 *
		 * @param array $templates Core templates data.
		 */
		$core_templates = (array) apply_filters( 'wpforms_form_templates_core', [] );

		/**
		 * Form templates available with the WPForms addons.
		 * Allows developers to provide additional templates with an addons.
		 *
		 * @since 1.4.0
		 *
		 * @param array $templates Addons templates data.
		 */
		$additional_templates = (array) apply_filters( 'wpforms_form_templates', [] );

		// phpcs:enable WPForms.PHP.ValidateHooks.InvalidHookName

		$templates = array_merge( $core_templates, $additional_templates );

		// Generate and store the templates' hash.
		$this->hash = wp_hash( wp_json_encode( $templates ) );

		return $templates;
	}

	/**
	 * Get templates' hash.
	 *
	 * @since 1.8.6
	 *
	 * @return string
	 */
	public function get_hash(): string {

		if ( ! $this->hash ) {
			$this->get_templates();
		}

		return $this->hash;
	}

	/**
	 * Get single template data.
	 *
	 * @since 1.6.8
	 *
	 * @param string $slug Template slug OR Id.
	 *
	 * @return array
	 */
	public function get_template( $slug ) {

		$template = $this->get_template_by_slug( $slug );

		if ( ! $template ) {
			$template = $this->get_template_by_id( $slug );
		}

		if ( empty( $template ) ) {
			return [];
		}

		if ( empty( $template['id'] ) ) {
			return $template;
		}

		// Attempt to get template with form data (if available).
		$full_template = wpforms()
			->obj( 'builder_template_single' )
			->instance( $template['id'], $this->license )
			->get();

		if ( ! empty( $full_template['data'] ) ) {
			return $full_template;
		}

		return $template;
	}

	/**
	 * Get template data by slug.
	 *
	 * @since 1.7.5.3
	 *
	 * @param string $slug Template slug.
	 *
	 * @return array
	 */
	private function get_template_by_slug( $slug ) {

		foreach ( $this->get_templates() as $template ) {
			if ( ! empty( $template['slug'] ) && $template['slug'] === $slug ) {
				return $template;
			}
		}

		return [];
	}

	/**
	 * Get template data by Id.
	 *
	 * @since 1.6.8
	 *
	 * @param string $id Template id.
	 *
	 * @return array
	 */
	private function get_template_by_id( $id ) {

		foreach ( $this->api_templates as $template ) {
			if ( ! empty( $template['id'] ) && $template['id'] === $id ) {
				return $template;
			}
		}

		return [];
	}

	/**
	 * Add templates to the list on the Setup panel.
	 *
	 * @since 1.6.8
	 *
	 * @param array $templates Templates list.
	 *
	 * @return array
	 */
	public function add_templates_to_setup_panel( $templates ) {

		return array_merge( $templates, $this->api_templates );
	}

	/**
	 * Add template data when form is created.
	 *
	 * @since 1.6.8
	 *
	 * @param array $args Create form arguments.
	 * @param array $data Template data.
	 *
	 * @return array
	 */
	public function apply_to_new_form( $args, $data ) {

		if ( empty( $data ) || empty( $data['template'] ) ) {
			return $args;
		}

		$template = $this->get_template( $data['template'] );

		if (
			empty( $template['data'] ) ||
			! $this->has_access( $template )
		) {
			return $args;
		}

		$template['data']['meta']['template']    = $template['id'] ?? $template['slug'];
		$template['data']['meta']['category']    = $data['category'] ?? 'all';
		$template['data']['meta']['subcategory'] = $data['subcategory'] ?? 'all';

		// Enable Notifications by default.
		$template['data']['settings']['notification_enable'] = isset( $template['data']['settings']['notification_enable'] )
			? $template['data']['settings']['notification_enable']
			: 1;

		// Unset settings that should be defined locally.
		unset(
			$template['data']['settings']['form_title'],
			$template['data']['settings']['conversational_forms_title'],
			$template['data']['settings']['form_pages_title']
		);

		// Unset certain values for each Notification, since:
		// - Email Subject Line field (subject) depends on the form name that is generated from the template name and form_id.
		// - From Name field (sender_name) depends on the blog name and can be replaced by WP Mail SMTP plugin.
		// - From Email field (sender_address) depends on the internal logic and can be replaced by WP Mail SMTP plugin.
		if ( ! empty( $template['data']['settings']['notifications'] ) ) {
			foreach ( (array) $template['data']['settings']['notifications'] as $key => $notification ) {
				unset(
					$template['data']['settings']['notifications'][ $key ]['subject'],
					$template['data']['settings']['notifications'][ $key ]['sender_name'],
					$template['data']['settings']['notifications'][ $key ]['sender_address']
				);
			}
		}

		/**
		 * Allow modifying form data when a template is applied to the new form.
		 *
		 * @since 1.9.0
		 *
		 * @param array $form_data New form data.
		 * @param array $template  Template data.
		 */
		$template['data'] = (array) apply_filters( 'wpforms_admin_builder_templates_apply_to_new_form_modify_data', $template['data'], $template );

		// Encode template data to post content.
		$args['post_content'] = wpforms_encode( $template['data'] );

		return $args;
	}

	/**
	 * Add template data when form is updated.
	 *
	 * @since 1.6.8
	 *
	 * @param array $form Form post data.
	 * @param array $data Form data.
	 * @param array $args Update form arguments.
	 *
	 * @return array
	 */
	public function apply_to_existing_form( $form, $data, $args ) {

		if ( empty( $args ) || empty( $args['template'] ) ) {
			return $form;
		}

		$template = $this->get_template( $args['template'] );

		if (
			empty( $template['data'] ) ||
			! $this->has_access( $template )
		) {
			return $form;
		}

		$form_data = wpforms_decode( wp_unslash( $form['post_content'] ) );

		// Something is wrong with the form data.
		if ( empty( $form_data ) ) {
			return $form;
		}

		// Compile the new form data preserving needed data from the existing form.
		$new             = $template['data'];
		$new['id']       = $form['ID'] ?? 0;
		$new['field_id'] = $form_data['field_id'] ?? 0;
		$new['settings'] = $form_data['settings'] ?? [];
		$new['payments'] = $form_data['payments'] ?? [];
		$new['meta']     = $form_data['meta'] ?? [];

		$template_id = $template['id'] ?? '';

		// Preserve template ID `wpforms-user-template-{$form_id}` when overwriting it with another template.
		if ( wpforms_is_form_template( $form['ID'] ) ) {
			$template_id = $form_data['meta']['template'] ?? '';
		}

		$new['meta']['template']    = $template_id;
		$new['meta']['category']    = ! empty( $args['category'] ) ? sanitize_text_field( $args['category'] ) : 'all';
		$new['meta']['subcategory'] = ! empty( $args['subcategory'] ) ? sanitize_text_field( $args['subcategory'] ) : 'all';

		/**
		 * Allow modifying form data when a new template is applied.
		 *
		 * @since 1.7.9
		 *
		 * @param array $new       Updated form data.
		 * @param array $form_data Current form data.
		 * @param array $template  Template data.
		 */
		$new = (array) apply_filters( 'wpforms_admin_builder_templates_apply_to_existing_form_modify_data', $new, $form_data, $template );

		// Update the form with new data.
		$form['post_content'] = wpforms_encode( $new );

		return $form;
	}

	/**
	 * Create a form on request.
	 *
	 * @since 1.6.8
	 */
	public function create_form_on_request() {

		$template = $this->get_template_on_request();

		// Just return if template not found OR user doesn't have access.
		if ( empty( $template['has_access'] ) ) {
			return;
		}

		// Check if the template requires some addons.
		if ( $this->check_template_required_addons( $template ) ) {
			return;
		}

		// Set form title equal to the template's name.
		$form_title   = ! empty( $template['name'] ) ? $template['name'] : esc_html__( 'New form', 'wpforms-lite' );
		$title_query  = new WP_Query(
			[
				'post_type'              => 'wpforms',
				'title'                  => $form_title,
				'posts_per_page'         => 1,
				'fields'                 => 'ids',
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
				'no_found_rows'          => true,
			]
		);
		$title_exists = $title_query->post_count > 0;
		$form_id      = wpforms()->obj( 'form' )->add(
			$form_title,
			[],
			[
				'template' => $template['id'],
			]
		);

		// Return if something wrong.
		if ( ! $form_id ) {
			return;
		}

		// Update form title if duplicated.
		if ( $title_exists ) {
			wpforms()->obj( 'form' )->update(
				$form_id,
				[
					'settings' => [
						'form_title' => $form_title . ' (ID #' . $form_id . ')',
					],
				]
			);
		}

		$this->create_form_on_request_redirect( $form_id );
	}

	/**
	 * Get template data before creating a new form on request.
	 *
	 * @since 1.6.8
	 *
	 * @return array|bool Template OR false.
	 */
	private function get_template_on_request() {

		if ( ! wpforms_is_admin_page( 'builder' ) || ! wpforms_is_admin_page( 'templates' ) ) {
			return false;
		}

		if ( ! wpforms_current_user_can( 'create_forms' ) ) {
			return false;
		}

		$form_id = isset( $_GET['form_id'] ) ? (int) $_GET['form_id'] : false; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( ! empty( $form_id ) ) {
			return false;
		}

		$view = isset( $_GET['view'] ) ? sanitize_key( $_GET['view'] ) : 'setup'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( $view !== 'setup' ) {
			return false;
		}

		$template_id = isset( $_GET['template_id'] ) ? sanitize_key( $_GET['template_id'] ) : false; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		// Attempt to get the template.
		$template = $this->get_template( $template_id );

		// Just return if template is not found.
		if ( empty( $template ) ) {
			return false;
		}

		return $template;
	}

	/**
	 * Redirect after creating the form.
	 *
	 * @since 1.6.8
	 *
	 * @param integer $form_id Form ID.
	 */
	private function create_form_on_request_redirect( $form_id ) {

		// Redirect to the builder if possible.
		if ( wpforms_current_user_can( 'edit_form_single', $form_id ) ) {
			wp_safe_redirect(
				add_query_arg(
					[
						'view'    => 'fields',
						'form_id' => $form_id,
						'newform' => '1',
					],
					admin_url( 'admin.php?page=wpforms-builder' )
				)
			);
			exit;
		}

		// Redirect to the forms overview admin page if possible.
		if ( wpforms_current_user_can( 'view_forms' ) ) {
			wp_safe_redirect(
				admin_url( 'admin.php?page=wpforms-overview' )
			);
			exit;
		}

		// Finally, redirect to the admin dashboard.
		wp_safe_redirect( admin_url() );
		exit;
	}

	/**
	 * Check if the template requires some addons and then redirect to the builder for further interaction if needed.
	 *
	 * @since 1.6.8
	 *
	 * @param array $template Template data.
	 *
	 * @return bool True if template requires some addons that are not yet installed and/or activated.
	 */
	private function check_template_required_addons( $template ) {

		// Return false if none addons required.
		if ( empty( $template['addons'] ) ) {
			return false;
		}

		$required_addons = wpforms()->obj( 'addons' )->get_by_slugs( $template['addons'] );

		foreach ( $required_addons as $i => $addon ) {
			if ( empty( $addon['action'] ) || ! in_array( $addon['action'], [ 'install', 'activate' ], true ) ) {
				unset( $required_addons[ $i ] );
			}
		}

		// Return false if not need to install or activate any addons.
		// We can proceed with creating the form directly in this process.
		if ( empty( $required_addons ) ) {
			return false;
		}

		// Otherwise return true.
		return true;
	}

	/**
	 * Render the upgrade banner template.
	 *
	 * This method generates the HTML template for the upgrade banner, which includes
	 * a title, description, and a button that links to the upgrade page.
	 *
	 * @param string $title       The title to be displayed in the banner.
	 * @param string $description The description to be displayed in the banner.
	 *
	 * @since 1.9.4
	 */
	private function render_upgrade_banner_template( string $title, string $description ): void {

		$medium = wpforms_is_admin_page( 'templates' ) ? 'Form Templates Subpage' : 'Builder Templates';

		?>
		<script type="text/html" id="tmpl-wpforms-templates-upgrade-banner">
			<div class="wpforms-template-upgrade-banner">
				<div class="wpforms-template-content">
					<h3>
						<?php echo esc_html( $title ); ?>
					</h3>

					<p>
						<?php echo esc_html( $description ); ?>
					</p>
				</div>
				<div class="wpforms-template-upgrade-button">
					<a href="<?php echo esc_url( wpforms_admin_upgrade_link( $medium, 'Upgrade to Pro' ) ); ?>" class="wpforms-btn wpforms-btn-orange wpforms-btn-md" target="_blank" rel="noopener noreferrer">
						<?php esc_html_e( 'Upgrade to Pro', 'wpforms-lite' ); ?>
					</a>
				</div>
			</div>
		</script>
		<?php
	}

	/**
	 * Render upgrade banner for basic and plus versions.
	 *
	 * @since 1.7.7
	 */
	public function upgrade_banner_template(): void {

		if ( in_array( wpforms_get_license_type(), [ 'pro', 'elite', 'agency', 'ultimate' ], true ) || ! wpforms()->is_pro() ) {
			return;
		}

		$title = sprintf(
			/* translators: %d - templates count. */
			esc_html__( 'Get Access to Our Complete Library of %d+ Form Templates', 'wpforms-lite' ),
			esc_html( floor( count( $this->get_templates() ) / 1000 ) * 1000 )
		);
		$description = esc_html__( 'Save time and reduce effort with our pre-built form templates covering popular use-cases in business operations, customer service, feedback, marketing, registrations, event planning, non-profit, healthcare, and education.', 'wpforms-lite' );

		$this->render_upgrade_banner_template( $title, $description );
	}

	/**
	 * Render upgrade banner for lite version.
	 *
	 * @since 1.9.4
	 */
	public function upgrade_lite_banner_template(): void {

		if ( wpforms()->is_pro() ) {
			return;
		}

		$title = sprintf(
			/* translators: %d - templates count. */
			esc_html__( 'Get Access to Our Library of %d+ Pre-Made Form Templates', 'wpforms-lite' ),
			esc_html( floor( count( $this->get_templates() ) / 1000 ) * 1000 )
		);
		$description = esc_html__( 'Never start from scratch again! While WPForms Lite allows you to create any type of form, you can save even more time with WPForms Pro. Upgrade to access hundreds more form templates and advanced form fields.', 'wpforms-lite' );

		$this->render_upgrade_banner_template( $title, $description );
	}

	/**
	 * Add additional addons templates.
	 *
	 * @since 1.8.9
	 *
	 * @param array $templates Templates list.
	 *
	 * @return array
	 */
	public function add_addons_templates( array $templates ): array {

		// Add User Registration templates only if the addon is not active.
		if ( ! wpforms()->obj( 'addons' )->is_active( 'user-registration' ) ) {
			$templates = $this->add_user_registration_templates( $templates );
		}

		// Add Post Submissions templates only if the addon is not active.
		if ( ! wpforms()->obj( 'addons' )->is_active( 'post-submissions' ) ) {
			$templates = $this->add_post_submissions_templates( $templates );
		}

		// Add Survey and Poll templates only if the addon is not active.
		if ( ! wpforms()->obj( 'addons' )->is_active( 'surveys-polls' ) ) {
			$templates = $this->add_surveys_polls_templates( $templates );
		}

		return $templates;
	}

	/**
	 * Add User Registration templates.
	 *
	 * @since 1.8.9
	 *
	 * @param array $templates Templates list.
	 *
	 * @return array
	 */
	private function add_user_registration_templates( array $templates ): array {

		$user_registration_templates = [
			[
				'name'        => esc_html__( 'User Registration Form', 'wpforms-lite' ),
				'slug'        => 'user_registration',
				'addons'      => [ 'user-registration' ],
				'license'     => $this->get_license_level( [ 'license' => [ 'pro' ] ] ),
				'has_access'  => $this->has_access( [ 'license' => [ 'pro' ] ] ),
				'source'      => 'wpforms-addon',
				'description' => esc_html__( 'Create customized WordPress user registration forms and add them anywhere on your website.', 'wpforms-lite' ),
			],
			[
				'name'        => esc_html__( 'User Login Form', 'wpforms-lite' ),
				'slug'        => 'user_login',
				'addons'      => [ 'user-registration' ],
				'license'     => $this->get_license_level( [ 'license' => [ 'pro' ] ] ),
				'has_access'  => $this->has_access( [ 'license' => [ 'pro' ] ] ),
				'source'      => 'wpforms-addon',
				'description' => esc_html__( 'Allow your users to easily log in to your site with their username and password.', 'wpforms-lite' ),
			],
			[
				'name'        => esc_html__( 'User Password Reset Form', 'wpforms-lite' ),
				'slug'        => 'user_reset',
				'addons'      => [ 'user-registration' ],
				'license'     => $this->get_license_level( [ 'license' => [ 'pro' ] ] ),
				'has_access'  => $this->has_access( [ 'license' => [ 'pro' ] ] ),
				'source'      => 'wpforms-addon',
				'description' => esc_html__( 'Allow your users to easily reset their password.', 'wpforms-lite' ),
			],
		];

		return array_merge( $templates, $user_registration_templates );
	}

	/**
	 * Add Post Submissions templates.
	 *
	 * @since 1.8.9
	 *
	 * @param array $templates Templates list.
	 *
	 * @return array
	 */
	private function add_post_submissions_templates( array $templates ): array {

		$post_submissions_templates = [
			[
				'name'        => esc_html__( 'Blog Post Submission Form', 'wpforms-lite' ),
				'slug'        => 'post_submission',
				'addons'      => [ 'post-submissions' ],
				'license'     => $this->get_license_level( [ 'license' => [ 'pro' ] ] ),
				'has_access'  => $this->has_access( [ 'license' => [ 'pro' ] ] ),
				'source'      => 'wpforms-addon',
				'description' => esc_html__( 'User-submitted content made easy. Allow your users to submit guest blog posts in WordPress. You can add and remove fields as needed.', 'wpforms-lite' ),
			],
		];

		return array_merge( $templates, $post_submissions_templates );
	}

	/**
	 * Add Surveys and Polls templates.
	 *
	 * @since 1.8.9
	 *
	 * @param array $templates Templates list.
	 *
	 * @return array
	 */
	private function add_surveys_polls_templates( array $templates ): array {

		$surveys_polls_templates = [
			[
				'name'        => esc_html__( 'Survey Form', 'wpforms-lite' ),
				'slug'        => 'survey',
				'addons'      => [ 'surveys-polls' ],
				'license'     => $this->get_license_level( [ 'license' => [ 'pro' ] ] ),
				'has_access'  => $this->has_access( [ 'license' => [ 'pro' ] ] ),
				'source'      => 'wpforms-addon',
				'description' => esc_html__( 'Collect customer feedback, then generate survey reports to determine satisfaction and spot trends.', 'wpforms-lite' ),
			],
			[
				'name'        => esc_html__( 'Poll Form', 'wpforms-lite' ),
				'slug'        => 'poll',
				'addons'      => [ 'surveys-polls' ],
				'license'     => $this->get_license_level( [ 'license' => [ 'pro' ] ] ),
				'has_access'  => $this->has_access( [ 'license' => [ 'pro' ] ] ),
				'source'      => 'wpforms-addon',
				'description' => esc_html__( 'Ask visitors a question and display the results after they provide an answer.', 'wpforms-lite' ),
			],
			[
				'name'        => esc_html__( 'NPS Survey Simple Form', 'wpforms-lite' ),
				'slug'        => 'nps-survey-simple',
				'addons'      => [ 'surveys-polls' ],
				'license'     => $this->get_license_level( [ 'license' => [ 'pro' ] ] ),
				'has_access'  => $this->has_access( [ 'license' => [ 'pro' ] ] ),
				'source'      => 'wpforms-addon',
				'description' => esc_html__( 'Find out if your clients or customers would recommend you to someone else with this basic Net Promoter Score survey template.', 'wpforms-lite' ),
			],
			[
				'name'        => esc_html__( 'NPS Survey Enhanced Form', 'wpforms-lite' ),
				'slug'        => 'nps-survey-enhanced',
				'addons'      => [ 'surveys-polls' ],
				'license'     => $this->get_license_level( [ 'license' => [ 'pro' ] ] ),
				'has_access'  => $this->has_access( [ 'license' => [ 'pro' ] ] ),
				'source'      => 'wpforms-addon',
				'description' => esc_html__( 'Measure customer loyalty and find out exactly what they are thinking with this enhanced Net Promoter Score survey template.', 'wpforms-lite' ),
			],
		];

		return array_merge( $templates, $surveys_polls_templates );
	}

	/**
	 * Init API templates.
	 *
	 * @since 1.9.1
	 *
	 * @param array $templates_all All templates.
	 *
	 * @return void
	 */
	private function init_api_templates( array $templates_all ) {

		// Higher priority templates slugs.
		// These remote templates are the replication of the default templates,
		// which were previously included with the WPForms plugin.
		$higher_templates_slugs = [
			'simple-contact-form-template',
			'request-a-quote-form-template',
			'donation-form-template',
			'billing-order-form-template',
			'newsletter-signup-form-template',
			'suggestion-form-template',
		];

		$templates_access_higher = [];
		$templates_access        = [];
		$templates_deny_higher   = [];
		$templates_deny          = [];

		/**
		 * The form template was moved to wpforms/includes/templates/class-simple-contact-form.php file.
		 *
		 * @since 1.7.5.3
		 */
		unset( $templates_all['simple-contact-form-template'] );

		foreach ( $templates_all as $i => $template ) {
			$template['has_access'] = $this->has_access( $template );
			$template['favorite']   = $this->is_favorite( $i );
			$template['license']    = $this->get_license_level( $template );
			$template['source']     = 'wpforms-api';
			$template['categories'] = ! empty( $template['categories'] ) ? array_keys( $template['categories'] ) : [];

			$is_higher = in_array( $i, $higher_templates_slugs, true );

			if ( $template['has_access'] ) {
				if ( $is_higher ) {
					$templates_access_higher[ $i ] = $template;
				} else {
					$templates_access[ $i ] = $template;
				}
			} elseif ( $is_higher ) {
				$templates_deny_higher[ $i ] = $template;
			} else {
				$templates_deny[ $i ] = $template;
			}
		}

		// Sort higher priority templates according to the slug order.
		$templates_access_higher = array_replace( array_flip( $higher_templates_slugs ), $templates_access_higher );
		$templates_access_higher = array_filter( $templates_access_higher, 'is_array' );

		// Finally, merge templates from API.
		$this->api_templates = array_merge(
			$templates_access_higher,
			$templates_access,
			$templates_deny_higher,
			$templates_deny
		);
	}
}
