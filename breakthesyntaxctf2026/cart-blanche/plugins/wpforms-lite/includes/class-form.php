<?php

// phpcs:disable Generic.Commenting.DocComment.MissingShort
/** @noinspection AutoloadingIssuesInspection */
/** @noinspection PhpIllegalPsrClassPathInspection */
// phpcs:enable Generic.Commenting.DocComment.MissingShort

/**
 * All the form goodness and basics.
 *
 * Contains a bunch of helper methods as well.
 *
 * @since 1.0.0
 */
class WPForms_Form_Handler {

	/**
	 * Tags taxonomy.
	 *
	 * @since 1.7.5
	 */
	public const TAGS_TAXONOMY = 'wpforms_form_tag';

	/**
	 * Allowed post types.
	 *
	 * @since 1.8.8
	 */
	public const POST_TYPES = [
		'wpforms',
		'wpforms-template',
	];

	/**
	 * Is form data slashing enabled.
	 *
	 * @since 1.9.0
	 *
	 * @var bool
	 */
	private $is_form_data_slashing_enabled;

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		$this->is_form_data_slashing_enabled = wpforms_is_form_data_slashing_enabled();

		$this->hooks();
	}

	/**
	 * Hooks.
	 *
	 * @since 1.7.5
	 */
	private function hooks(): void {

		// Register wpforms custom post type and taxonomy.
		add_action( 'init', [ $this, 'register_taxonomy' ] );
		add_action( 'init', [ $this, 'register_cpt' ] );

		// Add wpforms to a new-content admin bar menu.
		add_action( 'admin_bar_menu', [ $this, 'admin_bar' ], 99 );
		add_action( 'wpforms_create_form', [ $this, 'track_first_form' ], 10, 3 );

		// @WPFormsBackCompat Support Zapier v1.5.0 and earlier.
		add_filter( 'wpforms_form_handler_add_notices', [ $this, '_zapier_disconnected_on_duplication' ], 10, 3 );
	}

	/**
	 * Register the custom post type to be used for forms.
	 *
	 * @since 1.0.0
	 */
	public function register_cpt(): void {

		// phpcs:disable WPForms.PHP.ValidateHooks.InvalidHookName

		/**
		 * Filters Custom Post Type arguments.
		 *
		 * @since 1.0.0
		 *
		 * @param array $args Arguments.
		 */
		$args = apply_filters(
			'wpforms_post_type_args',
			[
				'label'               => 'WPForms',
				'public'              => false,
				'exclude_from_search' => true,
				'show_ui'             => false,
				'show_in_admin_bar'   => false,
				'rewrite'             => false,
				'query_var'           => false,
				'can_export'          => false,
				'supports'            => [ 'title', 'author', 'revisions' ],
				'capability_type'     => 'wpforms_form', // Not using 'capability_type' anywhere. It just has to be custom for security reasons.
				'map_meta_cap'        => false, // Don't let WP to map meta caps to have a granular control over this process via 'map_meta_cap' filter.
			]
		);

		// phpcs:enable WPForms.PHP.ValidateHooks.InvalidHookName

		// Register the post type.
		register_post_type( 'wpforms', $args );
	}

	/**
	 * Register the new taxonomy for tags.
	 *
	 * @since 1.7.5
	 */
	public function register_taxonomy(): void {

		/**
		 * Filters Tags taxonomy arguments.
		 *
		 * @since 1.7.5
		 *
		 * @param array $args Arguments.
		 */
		$args = apply_filters(
			'wpforms_form_handler_register_taxonomy_args',
			[
				'hierarchical' => false,
				'rewrite'      => false,
				'public'       => false,
			]
		);

		register_taxonomy( self::TAGS_TAXONOMY, 'wpforms', $args );
	}

	/**
	 * Add "WPForms" item to new-content admin bar menu item.
	 *
	 * @since 1.1.7.2
	 *
	 * @param WP_Admin_Bar $wp_admin_bar WP_Admin_Bar instance, passed by reference.
	 */
	public function admin_bar( $wp_admin_bar ): void {

		if ( ! is_admin_bar_showing() || ! wpforms_current_user_can( 'create_forms' ) ) {
			return;
		}

		$args = [
			'id'     => 'wpforms',
			'title'  => esc_html__( 'WPForms', 'wpforms-lite' ),
			'href'   => admin_url( 'admin.php?page=wpforms-builder' ),
			'parent' => 'new-content',
		];

		$wp_admin_bar->add_node( $args );
	}

	/**
	 * Preserve the timestamp when the very first form has been created.
	 *
	 * @since 1.6.7.1
	 *
	 * @param int   $form_id Newly created form ID.
	 * @param array $form    Array past to create a new form in the wp_posts table.
	 * @param array $data    Additional form data.
	 *
	 * @noinspection PhpMissingParamTypeInspection
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function track_first_form( $form_id, $form, $data ): void { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed

		// Do we have the value already?
		$time = get_option( 'wpforms_forms_first_created' );

		// Check whether we have already saved this option - skip.
		if ( ! empty( $time ) ) {
			return;
		}

		// Check whether we have any forms other than the currently created one.
		$other_form = $this->get(
			'',
			[
				'posts_per_page'         => 1,
				'nopaging'               => false,
				'fields'                 => 'ids',
				'post__not_in'           => [ $form_id ], // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
				'cap'                    => false,
			]
		);

		// As we have other forms - we are not certain about the situation, skip.
		if ( ! empty( $other_form ) ) {
			return;
		}

		add_option( 'wpforms_forms_first_created', time(), '', 'no' );
	}

	/**
	 * Fetch forms.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $id   Form ID.
	 * @param array $args Additional arguments array.
	 *
	 * @return array|false|WP_Post
	 */
	public function get( $id = '', array $args = [] ) {

		if ( $id === false ) {
			return false;
		}

		// phpcs:disable WPForms.PHP.ValidateHooks.InvalidHookName

		/**
		 * Allow developers to filter the WPForms_Form_Handler::get() arguments.
		 *
		 * @since 1.0.0
		 *
		 * @param array $args Arguments array.
		 * @param mixed $id   Form ID.
		 */
		$args = (array) apply_filters( 'wpforms_get_form_args', $args, $id );

		// phpcs:enable WPForms.PHP.ValidateHooks.InvalidHookName

		// By default, we should return only published forms.
		$defaults = [
			'post_status' => 'publish',
		];

		$args = wp_parse_args( $args, $defaults );

		$forms = empty( $id ) ? $this->get_multiple( $args ) : $this->get_single( $id, $args );

		return ! empty( $forms ) ? $forms : false;
	}

	/**
	 * Fetch a single form.
	 *
	 * @since 1.5.8
	 *
	 * @param string|int $id   Form ID.
	 * @param array      $args Additional arguments array.
	 *
	 * @return array|false|WP_Post
	 */
	protected function get_single( $id = '', array $args = [] ) {

		// phpcs:disable WPForms.PHP.ValidateHooks.InvalidHookName

		/**
		 * Allow developers to filter the get_single() arguments.
		 *
		 * @since 1.5.8
		 *
		 * @param array      $args Arguments' array, same as for `get_post()` function.
		 * @param string|int $id   Form ID.
		 */
		$args = apply_filters( 'wpforms_get_single_form_args', $args, $id );

		// phpcs:enable WPForms.PHP.ValidateHooks.InvalidHookName

		$access_obj = wpforms()->obj( 'access' );

		if ( ! $access_obj ) {
			return false;
		}

		if ( ! isset( $args['cap'] ) && $access_obj->init_allowed() ) {
			$args['cap'] = 'view_form_single';
		}

		if ( ! empty( $args['cap'] ) && ! wpforms_current_user_can( $args['cap'], $id ) ) {
			return false;
		}

		// If no ID provided, we can't get a single form.
		if ( empty( $id ) ) {
			return false;
		}

		// If ID is provided, we get a single form.
		$form = get_post( absint( $id ) );

		// Check if the form exists.
		if ( empty( $form ) || ! $form instanceof WP_Post ) {
			return false;
		}

		// Check if the form is of the allowed post type.
		if ( ! in_array( $form->post_type, self::POST_TYPES, true ) ) {
			return false;
		}

		// Decode the form content.
		if ( ! empty( $args['content_only'] ) ) {
			$form = wpforms_decode( $form->post_content );
		}

		return $form;
	}

	/**
	 * Fetch multiple forms.
	 *
	 * @since 1.5.8
	 * @since 1.7.2 Added support for $args['search']['term'] - search form title or description by term.
	 *
	 * @param array $args Additional arguments array.
	 *
	 * @return array
	 */
	protected function get_multiple( array $args = [] ): array {

		// phpcs:disable WPForms.PHP.ValidateHooks.InvalidHookName

		/**
		 * Allow developers to filter the get_multiple() arguments.
		 *
		 * @since 1.5.8
		 *
		 * @param array $args Arguments' array. Almost the same as for the `get_posts ()` function.
		 *                    Additional element:
		 *                    ['search']['term'] - search the form title or description by term.
		 */
		$args = (array) apply_filters( 'wpforms_get_multiple_forms_args', $args );

		// phpcs:enable WPForms.PHP.ValidateHooks.InvalidHookName

		// No ID provided, get multiple forms.
		$defaults = [
			'orderby'          => 'id',
			'order'            => 'ASC',
			'no_found_rows'    => true,
			'nopaging'         => true,
			'suppress_filters' => false,
		];

		$args = wp_parse_args( $args, $defaults );

		$post_type = $args['post_type'] ?? [];

		// Post type should be one of the allowed post types.
		$post_type = array_intersect( (array) $post_type, self::POST_TYPES );

		// If no valid (allowed) post types are provided, use the default one.
		$args['post_type'] = ! empty( $post_type ) ? $post_type : 'wpforms';

		/**
		 * Allow developers to execute some code before get_posts() call inside \WPForms_Form_Handler::get_multiple().
		 *
		 * @since 1.7.2
		 *
		 * @param array $args Arguments of the `get_posts()`.
		 */
		do_action( 'wpforms_form_handler_get_multiple_before_get_posts', $args );

		$forms = get_posts( $args );

		/**
		 * Allow developers to execute some code right after the get_posts () call inside \WPForms_Form_Handler::get_multiple().
		 *
		 * @since 1.7.2
		 *
		 * @param array $args  Arguments of the `get_posts`.
		 * @param array $forms Forms data. Result of getting multiple forms.
		 */
		do_action( 'wpforms_form_handler_get_multiple_after_get_posts', $args, $forms );

		/**
		 * Allow developers to filter the result of get_multiple().
		 *
		 * @since 1.7.2
		 *
		 * @param array $forms Result of getting multiple forms.
		 */
		return apply_filters( 'wpforms_form_handler_get_multiple_forms_result', $forms );
	}

	/**
	 * Update the form status.
	 *
	 * @since 1.7.3
	 *
	 * @param int    $form_id Form ID.
	 * @param string $status  New status.
	 *
	 * @return bool
	 */
	public function update_status( $form_id, $status ): bool {

		// Status updates are used only in trash and restore actions,
		// which are actually part of the deletion operation.
		// Therefore, we should check the `delete_form_single` and not `edit_form_single` permission.
		if ( ! wpforms_current_user_can( 'delete_form_single', $form_id ) ) {
			return false;
		}

		$form_id = absint( $form_id );
		$status  = empty( $status ) ? 'publish' : sanitize_key( $status );

		/**
		 * Filters the allowed form statuses.
		 *
		 * @since 1.7.3
		 *
		 * @param array $allowed_statuses Array of allowed form statuses. Default: publish, trash.
		 */
		$allowed = (array) apply_filters( 'wpforms_form_handler_update_status_allowed', [ 'publish', 'trash' ] );

		if ( ! in_array( $status, $allowed, true ) ) {
			return false;
		}

		$result = wp_update_post(
			[
				'ID'          => $form_id,
				'post_status' => $status,
			]
		);

		/**
		 * Allow developers to execute some code after changing form status.
		 *
		 * @since 1.8.1
		 *
		 * @param string $form_id Form ID.
		 * @param string $status  New form status, `publish` or `trash`.
		 */
		do_action( 'wpforms_form_handler_update_status', $form_id, $status );

		return $result !== 0;
	}

	/**
	 * Delete all forms in the Trash.
	 *
	 * @since 1.7.3
	 *
	 * @return int|bool Number of deleted forms OR false.
	 */
	public function empty_trash() {

		$forms = $this->get_multiple(
			[
				'post_type'        => self::POST_TYPES,
				'post_status'      => 'trash',
				'fields'           => 'ids',
				'suppress_filters' => true, // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.SuppressFilters_suppress_filters
			]
		);

		if ( empty( $forms ) ) {
			return false;
		}

		return $this->delete( $forms ) ? count( $forms ) : false;
	}

	/**
	 * Delete forms.
	 *
	 * @since 1.0.0
	 *
	 * @param array $ids Form IDs.
	 *
	 * @return bool
	 */
	public function delete( $ids = [] ): bool {

		if ( ! is_array( $ids ) ) {
			$ids = [ $ids ];
		}

		$ids = array_map( 'absint', $ids );

		foreach ( $ids as $id ) {

			// Check for permissions.
			if ( ! wpforms_current_user_can( 'delete_form_single', $id ) ) {
				return false;
			}

			$entry_obj        = wpforms()->obj( 'entry' );
			$entry_meta_obj   = wpforms()->obj( 'entry_meta' );
			$entry_fields_obj = wpforms()->obj( 'entry_fields' );

			if ( $entry_obj && $entry_meta_obj && $entry_fields_obj ) {
				$entry_obj->delete_by( 'form_id', $id );
				$entry_meta_obj->delete_by( 'form_id', $id );
				$entry_fields_obj->delete_by( 'form_id', $id );
			}

			$form = wp_delete_post( $id, true );

			if ( ! $form ) {
				return false;
			}
		}

		/**
		 * Fires when forms are deleted.
		 *
		 * @since 1.5.1
		 *
		 * @param array $ids Array of form IDs.
		 */
		do_action( 'wpforms_delete_form', $ids ); // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName

		return true;
	}

	/**
	 * Add a new form.
	 *
	 * @since 1.0.0
	 *
	 * @param string $title Form title.
	 * @param array  $args  Additional arguments.
	 * @param array  $data  Form data.
	 *
	 * @return false|int|WP_Error
	 */
	public function add( $title = '', $args = [], $data = [] ) { // phpcs:ignore WPForms.PHP.HooksMethod.InvalidPlaceForAddingHooks, Generic.Metrics.CyclomaticComplexity.TooHigh

		// Must have a title.
		if ( empty( $title ) ) {
			return false;
		}

		// Check for permissions.
		if ( ! wpforms_current_user_can( 'create_forms' ) ) {
			return false;
		}

		$this->remove_form_content_filters();

		// phpcs:disable WPForms.PHP.ValidateHooks.InvalidHookName

		/**
		 * Filters form creation arguments.
		 *
		 * @since 1.0.0
		 *
		 * @param array $args Form creation arguments.
		 * @param array $data Additional data.
		 */
		$args = apply_filters( 'wpforms_create_form_args', $args, $data );

		// phpcs:enable WPForms.PHP.ValidateHooks.InvalidHookName

		$form_content = [
			'field_id' => '0',
			'settings' => [
				'form_title' => sanitize_text_field( $title ),
				'form_desc'  => '',
			],
		];

		if ( $this->is_form_data_slashing_enabled ) {
			$form_content = wp_slash( $form_content );
		}

		$args_form_data = isset( $args['post_content'] ) ? json_decode( wp_unslash( $args['post_content'] ), true ) : null;

		// Prevent $args['post_content'] from overwriting predefined $form_content.
		// Typically, it happens if the form was created with a form template and a user was not redirected to a form editing screen afterward.
		// This is only possible if a user has 'wpforms_create_forms' and no 'wpforms_edit_own_forms' capability.
		if ( is_array( $args_form_data ) ) {
			$args['post_content'] = wpforms_encode( array_replace_recursive( $form_content, $args_form_data ) );
		}

		// Merge args and create the form.
		$form = wp_parse_args(
			$args,
			[
				'post_title'   => esc_html( $title ),
				'post_status'  => 'publish',
				'post_type'    => 'wpforms',
				'post_content' => wpforms_encode( $form_content ),
			]
		);

		$form_id = wp_insert_post( $form );

		// Set form tags.
		if ( ! empty( $form_id ) && ! empty( $args_form_data['settings']['form_tags'] ) ) {
			wp_set_post_terms(
				$form_id,
				implode( ',', $args_form_data['settings']['form_tags'] ),
				self::TAGS_TAXONOMY
			);
		}

		// If a user has no editing permissions, the form considered to be created out of the WPForms form builder's context.
		if ( ! wpforms_current_user_can( 'edit_form_single', $form_id ) ) {
			$data['builder'] = false;
		}

		// If the form is created outside the context of the WPForms form
		// builder, then we define some additional default values.
		if ( ! empty( $form_id ) && isset( $data['builder'] ) && $data['builder'] === false ) {
			$form_data                                       = json_decode( wp_unslash( $form['post_content'] ), true );
			$form_data['id']                                 = $form_id;
			$form_data['settings']['submit_text']            = esc_html__( 'Submit', 'wpforms-lite' );
			$form_data['settings']['submit_text_processing'] = esc_html__( 'Sending...', 'wpforms-lite' );
			$form_data['settings']['notification_enable']    = '1';
			$form_data['settings']['notifications']          = [
				'1' => [
					'email'          => '{admin_email}',
					'subject'        => sprintf( /* translators: %s - form name. */
						esc_html__( 'New Entry: %s', 'wpforms-lite' ),
						esc_html( $title )
					),
					'sender_name'    => get_bloginfo( 'name' ),
					'sender_address' => '{admin_email}',
					'message'        => '{all_fields}',
				],
			];
			$form_data['settings']['confirmations']          = [
				'1' => [
					'type'           => 'message',
					'message'        => esc_html__( 'Thanks for contacting us! We will be in touch with you shortly.', 'wpforms-lite' ),
					'message_scroll' => '1',
				],
			];

			$this->update( $form_id, $form_data, [ 'cap' => 'create_forms' ] );
		}

		// phpcs:disable WPForms.PHP.ValidateHooks.InvalidHookName

		/**
		 * Fires after the form was created.
		 *
		 * @since 1.0.0
		 *
		 * @param int   $form_id Form ID.
		 * @param array $form    Form data.
		 * @param array $data    Additional data.
		 */
		do_action( 'wpforms_create_form', $form_id, $form, $data );

		// phpcs:enable WPForms.PHP.ValidateHooks.InvalidHookName

		return $form_id;
	}

	/**
	 * Update form.
	 *
	 * @since 1.0.0
	 *
	 * @param string|int $form_id Form ID.
	 * @param array      $data    Data retrieved from $_POST and processed.
	 * @param array      $args    Empty by default. May have custom data not intended to be saved.
	 *
	 * @return int|false
	 */
	public function update( $form_id = '', array $data = [], array $args = [] ) { // phpcs:ignore WPForms.PHP.HooksMethod.InvalidPlaceForAddingHooks, Generic.Metrics.CyclomaticComplexity.TooHigh

		if ( empty( $data ) ) {
			return false;
		}

		if ( empty( $form_id ) && isset( $data['id'] ) ) {
			$form_id = $data['id'];
		}

		if ( ! isset( $args['cap'] ) ) {
			$args['cap'] = 'edit_form_single';
		}

		if ( ! empty( $args['cap'] ) && ! wpforms_current_user_can( $args['cap'], $form_id ) ) {
			return false;
		}

		$this->remove_form_content_filters();

		if ( $this->is_form_data_slashing_enabled ) {
			// Even though we are not going to unslash some data,
			// columns-json and calculation_code fields must be unslashed.
			$data = $this->unslash_field_keys( $data, [ 'columns-json', 'calculation_code' ] );
		} else {
			$data = (array) wp_unslash( $data );
		}

		$title = empty( $data['settings']['form_title'] ) ? get_the_title( $form_id ) : $data['settings']['form_title'];
		$desc  = empty( $data['settings']['form_desc'] ) ? '' : $data['settings']['form_desc'];

		$data['field_id'] = ! empty( $data['field_id'] ) ? wpforms_validate_field_id( $data['field_id'] ) : '0';

		// Preserve the explicit "Do not store spam entries" state.
		$data['settings']['store_spam_entries'] = $data['settings']['store_spam_entries'] ?? '0';

		// Use the default 'submit' button text if not provided.
		$data['settings']['submit_text'] = ! empty( $data['settings']['submit_text'] ) ? $data['settings']['submit_text'] : esc_html__( 'Submit', 'wpforms-lite' );

		// Preserve form meta.
		$meta = $this->get_meta( $form_id );

		/**
		 * Filters the form meta before saving.
		 *
		 * @since 1.9.8
		 *
		 * @param array $meta    Form meta.
		 * @param int   $form_id Form ID.
		 * @param array $data    Form data.
		 */
		$meta = apply_filters( 'wpforms_form_handler_update_meta', $meta, $form_id, $data );

		if ( $meta ) {
			$data['meta'] = $meta;
		}

		// Update category and subcategory only if available.
		if ( ! empty( $args['category'] ) ) {
			$data['meta']['category'] = $args['category'];
		}

		if ( ! empty( $args['subcategory'] ) ) {
			$data['meta']['subcategory'] = $args['subcategory'];
		}

		// Preserve fields meta.
		if ( isset( $data['fields'] ) ) {
			$data['fields'] = $this->update__preserve_fields_meta( $data['fields'], $form_id );
		}

		// Sanitize - don't allow tags for users who do not have the appropriate cap.
		// If we don't do this, forms for these users can get corrupt due to conflicts with wp_kses().
		if ( ! current_user_can( 'unfiltered_html' ) ) {
			$data = map_deep( $data, 'wp_strip_all_tags' );
		}

		// Sanitize notifications names.
		if ( isset( $data['settings']['notifications'] ) ) {
			$data['settings']['notifications'] = $this->update__sanitize_notifications_names( $data['settings']['notifications'] );
		}

		unset( $notification );

		/**
		 * Allow changing post data before saving.
		 *
		 * @since 1.0.0
		 *
		 * @param array $post_data Post data.
		 * @param array $form_data Form data.
		 * @param array $args      Empty by default. May have custom data not intended to be saved.
		 */
		$form = apply_filters( // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName
			'wpforms_save_form_args',
			[
				'ID'           => $form_id,
				'post_title'   => esc_html( $title ),
				'post_excerpt' => $desc,
				'post_content' => wpforms_encode( $data ),
			],
			$data,
			$args
		);

		if ( ! empty( $args['skip_revision'] ) ) {
			remove_action( 'post_updated', 'wp_save_post_revision' );
		}

		$_form_id = wp_update_post( $form );

		if ( ! empty( $args['skip_revision'] ) ) {
			add_action( 'post_updated', 'wp_save_post_revision' );
		}

		if ( is_wp_error( $_form_id ) ) {
			return false;
		}

		/**
		 * Fires after saving the form.
		 *
		 * @since 1.0.0
		 *
		 * @param int   $_form_id Form ID.
		 * @param array $form     Form.
		 */
		do_action( 'wpforms_save_form', $_form_id, $form ); // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName

		return $_form_id;
	}

	/**
	 * Preserve fields meta in 'update' method.
	 *
	 * @since 1.5.8
	 *
	 * @param array      $fields  Form fields.
	 * @param string|int $form_id Form ID.
	 *
	 * @return array
	 */
	protected function update__preserve_fields_meta( $fields, $form_id ): array {

		foreach ( $fields as $i => $field_data ) {
			if ( isset( $field_data['id'] ) ) {
				$field_meta = $this->get_field_meta( $form_id, $field_data['id'] );

				if ( $field_meta ) {
					$fields[ $i ]['meta'] = $field_meta;
				}
			}
		}

		return $fields;
	}

	/**
	 * Sanitize notifications names meta in 'update' method.
	 *
	 * @since 1.5.8
	 *
	 * @param array $notifications Form notifications.
	 *
	 * @return array
	 */
	protected function update__sanitize_notifications_names( $notifications ): array {

		foreach ( $notifications as &$notification ) {
			if ( ! empty( $notification['notification_name'] ) ) {
				$notification['notification_name'] = sanitize_text_field( $notification['notification_name'] );
			}
		}

		return $notifications;
	}

	/**
	 * Duplicate forms.
	 *
	 * @since 1.1.4
	 * @since 1.8.8 Return array of new form IDs instead of true.
	 *
	 * @param array|string $ids Form IDs to duplicate.
	 *
	 * @return bool|array Array of new form IDs or false.
	 */
	public function duplicate( $ids ) { // phpcs:ignore WPForms.PHP.HooksMethod.InvalidPlaceForAddingHooks, Generic.Metrics.CyclomaticComplexity.TooHigh

		// Check for permissions.
		if ( ! wpforms_current_user_can( 'create_forms' ) ) {
			return false;
		}

		$this->remove_form_content_filters();

		if ( ! is_array( $ids ) ) {
			$ids = [ $ids ];
		}

		$ids = array_map( 'absint', $ids );

		$duplicate_ids = [];

		foreach ( $ids as $id ) {

			// Get the original entry.
			$form = get_post( $id );

			if ( ! wpforms_current_user_can( 'view_form_single', $id ) ) {
				return false;
			}

			// Confirm form exists.
			if ( empty( $form ) ) {
				return false;
			}

			// Get the form data.
			$new_form_data = (array) wpforms_decode( $form->post_content );

			if ( $this->is_form_data_slashing_enabled ) {
				$new_form_data = (array) wp_slash( $new_form_data );
			}

			// Remove form ID from the title if present.
			$new_form_data['settings']['form_title'] = str_replace( '(ID #' . absint( $id ) . ')', '', $new_form_data['settings']['form_title'] );

			// Remove '(copy)' from the form template title if present.
			$new_form_data['settings']['form_title'] = str_replace( __( '(copy)', 'wpforms-lite' ), '', $new_form_data['settings']['form_title'] );

			// Remove trailing spaces.
			$new_form_data['settings']['form_title'] = rtrim( $new_form_data['settings']['form_title'] );

			// Remove the `-template ` suffix and all after it from the post name.
			$post_name = preg_replace( '/-template(-\d+)?/', '', $form->post_name );

			// Add some notice messages before form preview area.
			$new_form_data = $this->add_notices( $new_form_data, (int) $id );

			// Create the duplicate form.
			$new_form    = [
				'post_content' => wpforms_encode( $new_form_data ),
				'post_excerpt' => $form->post_excerpt,
				'post_status'  => $form->post_status,
				'post_title'   => $new_form_data['settings']['form_title'],
				'post_type'    => $form->post_type,
				'post_name'    => wpforms_is_form_template( $id ) ? $post_name . '-template' : $post_name,
			];
			$new_form_id = wp_insert_post( $new_form );

			if ( ! $new_form_id || is_wp_error( $new_form_id ) ) {
				return false;
			}

			// Set a new form name.
			$new_form_data['settings']['form_title'] .= $form->post_type === 'wpforms-template' ?
				' ' . __( '(copy)', 'wpforms-lite' ) :
				' (ID #' . absint( $new_form_id ) . ')';

			// Set a new form ID.
			$new_form_data['id'] = absint( $new_form_id );

			// Update a new duplicate form.
			$new_form_id = $this->update( $new_form_id, $new_form_data, [ 'cap' => 'create_forms' ] );

			if ( ! $new_form_id ) {
				return false;
			}

			// Add tags to the new form.
			if ( ! empty( $new_form_data['settings']['form_tags'] ) ) {
				wp_set_post_terms(
					$new_form_id,
					implode( ',', (array) $new_form_data['settings']['form_tags'] ),
					self::TAGS_TAXONOMY
				);
			}

			/**
			 * Fires after the form was duplicated.
			 *
			 * @since 1.8.2.2
			 *
			 * @param int   $id            Original form ID.
			 * @param int   $new_form_id   New form ID.
			 * @param array $new_form_data New form data.
			 */
			do_action( 'wpforms_form_handler_duplicate_form', $id, $new_form_id, $new_form_data );

			$duplicate_ids[] = $new_form_id;
		}

		return $duplicate_ids;
	}

	/**
	 * Convert form to a template and vice versa.
	 *
	 * @since 1.8.8
	 *
	 * @param string|int $form_id    Form ID.
	 * @param string     $convert_to Convert to, `form` or `template`.
	 *
	 * @return false|int New object ID or false on failure.
	 */
	public function convert( $form_id, string $convert_to ) {

		if ( ! in_array( $convert_to, [ 'form', 'template' ], true ) ) {
			return false;
		}

		// Duplicate the form.
		$ids = $this->duplicate( $form_id );

		if ( empty( $ids ) ) {
			return false;
		}

		$new_form_id = current( $ids );
		$form        = get_post( $new_form_id );
		$form_data   = wpforms_decode( $form->post_content );

		if ( $this->is_form_data_slashing_enabled ) {
			$form_data = wp_slash( $form_data );
		}

		/**
		 * Filters the form data before converting it to a template or vice versa.
		 *
		 * @since 1.8.8
		 *
		 * @param array      $form_data   Form data.
		 * @param string|int $form_id     Form ID.
		 * @param string     $convert_to  Convert to, `form` or `template`.
		 */
		$form_data = apply_filters( 'wpforms_form_handler_convert_form_data', $form_data, $form_id, $convert_to );

		// Set default post type.
		$post_type = 'wpforms';

		// Remove the numeric suffix from the post name.
		// Duplication always adds `-{numeric}` suffix.
		$post_name = preg_replace( '/-\d+$/', '', $form->post_name );

		// Remove the `-template ` suffix and all after it from the post name.
		$post_name = preg_replace( '/-template(-\d+)?/', '', $post_name );

		// Remove (copy) from the form title, if present.
		$form_data['settings']['form_title'] = str_replace( __( '(copy)', 'wpforms-lite' ), '', $form_data['settings']['form_title'] );

		// Remove trailing spaces.
		$form_data['settings']['form_title'] = rtrim( $form_data['settings']['form_title'] );

		// Remove template description.
		unset( $form_data['settings']['template_description'] );

		if ( $convert_to === 'template' ) {
			$post_type = 'wpforms-template';

			// Remove (ID #<Form ID>) from the form title, if present.
			$form_data['settings']['form_title'] = preg_replace( '/\(ID #\d+\)/', '', $form_data['settings']['form_title'] );

			// Set an empty template description.
			$form_data['settings']['template_description'] = '';

			// Remove traces of any other template that may have been used to create the original form by setting itself as a template.
			$form_data['meta']['template'] = 'wpforms-user-template-' . $new_form_id;

			// Add `-template` suffix to the post name.
			$post_name .= '-template';
		}

		wp_update_post(
			[
				'ID'           => $new_form_id,
				'post_title'   => $form_data['settings']['form_title'],
				'post_type'    => $post_type,
				'post_content' => wpforms_encode( $form_data ),
				'post_name'    => $post_name,
			]
		);

		return $new_form_id;
	}

	/**
	 * Append notice(s) before form preview, if needed.
	 *
	 * @since 1.8.8
	 *
	 * @param array $new_form_data New form data.
	 * @param int   $form_id       Original form ID.
	 *
	 * @return array
	 */
	private function add_notices( array $new_form_data, int $form_id ): array { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		/**
		 * Add custom notices to be displayed in the preview area of the Form Builder
		 * after a form or a form template has been duplicated or converted.
		 *
		 * @since 1.8.8
		 *
		 * @param array $notices       Array of notices.
		 * @param array $new_form_data Form data of the newly duplicated form or form template.
		 * @param int   $form_id       Original form ID.
		 */
		$notices = apply_filters( 'wpforms_form_handler_add_notices', [], $new_form_data, $form_id );

		if ( empty( $notices ) ) {
			return $new_form_data;
		}

		$current_field_id = ! empty( $new_form_data['fields'] ) ? max( array_keys( $new_form_data['fields'] ) ) : 0;
		$code_fields      = array_column( $new_form_data['fields'], 'code' );
		$next_field_id    = $current_field_id;
		$warning          = [];

		foreach ( $notices as $notice ) {
			// Skip the duplicate notice if it already exists.
			if ( ! empty( $notice['code'] ) && in_array( $notice['code'], $code_fields, true ) ) {
				continue;
			}

			$next_field_id             = ++$current_field_id;
			$warning[ $next_field_id ] = [
				'id'          => $next_field_id,
				'type'        => 'internal-information',
				'code'        => ! empty( $notice['code'] ) ? esc_attr( $notice['code'] ) : '',
				'description' => '',
			];

			$warning[ $next_field_id ]['description'] .= ! empty( $notice['title'] ) ? '<strong>' . esc_html( $notice['title'] ) . '</strong>' : '';
			$warning[ $next_field_id ]['description'] .= ! empty( $notice['message'] ) ? '<p>' . wp_kses_post( $notice['message'] ) . '</p>' : '';

			// Do not add notice with empty body.
			if ( empty( $warning[ $next_field_id ]['description'] ) ) {
				unset( $warning[ $next_field_id ] );
				--$next_field_id; // Reset the next field ID to the previous value.
			}
		}

		if ( ! empty( $warning ) ) {
			$new_form_data['fields'] = $warning + $new_form_data['fields'];

			// Update the next field ID to be used for future created fields. Otherwise, the IIF field would be overwritten.
			$new_form_data['field_id'] = $next_field_id + 1;
		}

		return $new_form_data;
	}

	/**
	 * Add a notice about Zapier zaps disconnected after the form being duplicated or converted to/from the template.
	 *
	 * @WPFormsBackCompat Support Zapier v1.5.0 and earlier.
	 *
	 * @since             1.8.8
	 *
	 * @param array $notices       Array of notices.
	 * @param array $new_form_data Form data.
	 * @param int   $form_id       Original form ID.
	 *
	 * @return array
	 * @noinspection      HtmlUnknownTarget
	 * @noinspection      PhpUnusedParameterInspection
	 */
	public function _zapier_disconnected_on_duplication( $notices, array $new_form_data, int $form_id ): array { // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore

		// Check if the original form had any Zaps connected.
		$is_zapier_connected = get_post_meta( $form_id, 'wpforms_zapier', true );

		if ( ! $is_zapier_connected ) {
			return $notices;
		}

		$notices['zapier'] = [
			'title'   => esc_html__( 'Zaps Have Been Disabled', 'wpforms-lite' ),
			'code'    => 'disconnected_on_duplication',
			'message' => sprintf( /* translators: %s - URL the to list of Zaps. */
				__( 'Head over to the Zapier settings in the Marketing tab or visit your <a href="%s" target="_blank" rel="noopener noreferrer">Zapier account</a> to restore them.', 'wpforms-lite' ),
				esc_url( 'https://zapier.com/app/zaps' )
			),
		];

		return $notices;
	}

	/**
	 * Get the next available field ID and increment by one.
	 *
	 * @since 1.0.0
	 *
	 * @param string|int $form_id Form ID.
	 * @param array      $args    Additional arguments.
	 *
	 * @return mixed int or false
	 */
	public function next_field_id( $form_id, $args = [] ) { // phpcs:ignore WPForms.PHP.HooksMethod.InvalidPlaceForAddingHooks, Generic.Metrics.CyclomaticComplexity.TooHigh

		if ( empty( $form_id ) ) {
			return false;
		}

		$defaults = [
			'content_only' => true,
		];

		if ( isset( $args['cap'] ) ) {
			$defaults['cap'] = $args['cap'];
		}

		$form = $this->get( $form_id, $defaults );

		if ( $this->is_form_data_slashing_enabled ) {
			$form = wp_slash( $form );
		}

		if ( empty( $form ) ) {
			return false;
		}

		$field_id     = 0;
		$max_field_id = ! empty( $form['fields'] ) ? max( array_keys( $form['fields'] ) ) : 0;

		// We pass the `field_id` after duplicating the Layout field that contains a bunch of fields.
		// This is needed to avoid multiple AJAX calls after duplicating each field in the Layout.
		if ( isset( $args['field_id'] ) ) {
			$set_field_id = absint( $args['field_id'] ) - 1;
			$field_id     = $set_field_id > $max_field_id ? $set_field_id : $max_field_id + 1;
		} elseif ( ! empty( $form['field_id'] ) ) {
			$field_id = absint( $form['field_id'] );
			$field_id = $max_field_id > $field_id ? $max_field_id + 1 : $field_id;
		}

		$form['field_id'] = $field_id + 1;

		// Skip creating a revision for this action.
		remove_action( 'post_updated', 'wp_save_post_revision' );

		$this->update( $form_id, $form );

		// Restore the initial revisions state.
		add_action( 'post_updated', 'wp_save_post_revision' );

		return $field_id;
	}

	/**
	 * Get private meta information for a form.
	 *
	 * @since 1.0.0
	 *
	 * @param string|int $form_id Form ID.
	 * @param string     $field   Field.
	 * @param array      $args    Additional arguments.
	 *
	 * @return false|array
	 */
	public function get_meta( $form_id, $field = '', $args = [] ) {

		if ( empty( $form_id ) ) {
			return false;
		}

		$defaults = [
			'content_only' => true,
		];

		if ( isset( $args['cap'] ) ) {
			$defaults['cap'] = $args['cap'];
		}

		$data = $this->get( $form_id, $defaults );

		if ( ! isset( $data['meta'] ) ) {
			return false;
		}

		if ( empty( $field ) ) {
			return $data['meta'];
		}

		return $data['meta'][ $field ] ?? false;
	}

	/**
	 * Update or add form meta information to a form.
	 *
	 * @since 1.4.0
	 *
	 * @param string|int $form_id    Form ID.
	 * @param string     $meta_key   Meta key.
	 * @param mixed      $meta_value Meta value.
	 * @param array      $args       Additional arguments.
	 *
	 * @return false|int|WP_Error
	 */
	public function update_meta( $form_id, $meta_key, $meta_value, $args = [] ) { // phpcs:ignore WPForms.PHP.HooksMethod.InvalidPlaceForAddingHooks, Generic.Metrics.CyclomaticComplexity.TooHigh

		if ( empty( $form_id ) || empty( $meta_key ) ) {
			return false;
		}

		$this->remove_form_content_filters();

		if ( ! isset( $args['cap'] ) ) {
			$args['cap'] = 'edit_form_single';
		}

		$form = $this->get_single( absint( $form_id ), $args );

		if ( empty( $form ) ) {
			return false;
		}

		$data     = (array) wpforms_decode( $form->post_content );
		$meta_key = wpforms_sanitize_key( $meta_key );

		$data['meta'][ $meta_key ] = $meta_value;

		$form = [
			'ID'           => $form_id,
			'post_content' => wpforms_encode( $data ),
		];

		/**
		 * Allow changing form before updating form meta.
		 *
		 * @since 1.4.0
		 *
		 * @param array $form Form.
		 * @param array $data Form data.
		 */
		$form = (array) apply_filters( 'wpforms_update_form_meta_args', $form, $data ); // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName

		if ( ! empty( $args['skip_revision'] ) ) {
			remove_action( 'post_updated', 'wp_save_post_revision' );
		}

		$result = wp_update_post( $form );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		if ( ! empty( $args['skip_revision'] ) ) {
			add_action( 'post_updated', 'wp_save_post_revision' );
		}

		/**
		 * Fires when form meta is updated.
		 *
		 * @since 1.4.0
		 *
		 * @param string|int $form_id    Form ID.
		 * @param array      $form       Form.
		 * @param string     $meta_key   Meta key.
		 * @param mixed      $meta_value Meta value.
		 */
		do_action( 'wpforms_update_form_meta', $form_id, $form, $meta_key, $meta_value ); // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName

		return $form_id;
	}

	/**
	 * Delete form meta information from a form.
	 *
	 * @since 1.4.0
	 *
	 * @param string|int $form_id  Form ID.
	 * @param string     $meta_key Meta key.
	 * @param array      $args     Additional arguments.
	 *
	 * @return false|int|WP_Error
	 */
	public function delete_meta( $form_id, $meta_key, $args = [] ) { // phpcs:ignore WPForms.PHP.HooksMethod.InvalidPlaceForAddingHooks

		if ( empty( $form_id ) || empty( $meta_key ) ) {
			return false;
		}

		$this->remove_form_content_filters();

		if ( ! isset( $args['cap'] ) ) {
			$args['cap'] = 'edit_form_single';
		}

		$form = $this->get_single( absint( $form_id ), $args );

		if ( empty( $form ) ) {
			return false;
		}

		$data     = (array) wpforms_decode( $form->post_content );
		$meta_key = wpforms_sanitize_key( $meta_key );

		unset( $data['meta'][ $meta_key ] );

		$form = [
			'ID'           => $form_id,
			'post_content' => wpforms_encode( $data ),
		];

		/**
		 * Filters form which meta to be deleted.
		 *
		 * @since 1.4.0
		 *
		 * @param array $form Form.
		 * @param array $data Form data.
		 */
		$form   = (array) apply_filters( 'wpforms_delete_form_meta_args', $form, $data ); // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName
		$result = wp_update_post( $form );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		/**
		 * Fires when form meta is deleted.
		 *
		 * @since 1.4.0
		 *
		 * @param string|int $form_id  Form ID.
		 * @param array      $form     Form.
		 * @param string     $meta_key Meta key.
		 */
		do_action( 'wpforms_delete_form_meta', $form_id, $form, $meta_key ); // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName

		return $form_id;
	}

	/**
	 * Get private meta information for a form field.
	 *
	 * @since 1.0.0
	 *
	 * @param string|int $form_id  Form ID.
	 * @param string     $field_id Field ID.
	 * @param array      $args     Additional arguments.
	 *
	 * @return array|false
	 */
	public function get_field( $form_id, $field_id = '', $args = [] ) {

		if ( empty( $form_id ) ) {
			return false;
		}

		$defaults = [
			'content_only' => true,
		];

		if ( isset( $args['cap'] ) ) {
			$defaults['cap'] = $args['cap'];
		}

		$data = $this->get( $form_id, $defaults );

		return $data['fields'][ $field_id ] ?? false;
	}

	/**
	 * Get private meta information for a form field.
	 *
	 * @since 1.0.0
	 *
	 * @param string|int $form_id  Form ID.
	 * @param string     $field_id Field ID.
	 * @param array      $args     Additional arguments.
	 *
	 * @return array|false
	 */
	public function get_field_meta( $form_id, $field_id = '', $args = [] ) {

		$field = $this->get_field( $form_id, $field_id, $args );

		if ( ! $field ) {
			return false;
		}

		return $field['meta'] ?? false;
	}

	/**
	 * Checks if any forms are present on the site.
	 *
	 * @since 1.8.8
	 *
	 * @retun bool
	 */
	public function forms_exist(): bool {

		return (bool) $this->get(
			'',
			[
				'numberposts'            => 1,
				'fields'                 => 'ids',
				'no_found_rows'          => true,
				'suppress_filters'       => true, // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.SuppressFilters_suppress_filters
				'nopaging'               => false,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			]
		);
	}

	/**
	 * Get the forms' count per page.
	 *
	 * @since 1.8.8
	 *
	 * @return int
	 */
	public function get_count_per_page(): int {

		// phpcs:disable WPForms.PHP.ValidateHooks.InvalidHookName
		/**
		 * Allow developers to modify the number of forms per page.
		 *
		 * @since 1.8.8
		 *
		 * @param array $count Forms count per page.
		 */
		return (int) apply_filters( 'wpforms_forms_per_page', 20 );
		// phpcs:enable WPForms.PHP.ValidateHooks.InvalidHookName
	}

	/**
	 * Unslash field keys.
	 *
	 * @since 1.9.0
	 *
	 * @param array $data Form data.
	 * @param array $keys Field keys.
	 *
	 * @return array
	 * @noinspection PhpSameParameterValueInspection
	 */
	private function unslash_field_keys( array $data, array $keys ): array {

		if ( empty( $data['fields'] ) ) {
			return $data;
		}

		/**
		 * Filter field keys to be unslashed before saving.
		 *
		 * Works used with the filter wpforms_enable_form_data_slashing set to true.
		 *
		 * @since 1.9.0
		 *
		 * @param array $keys Field keys.
		 *
		 * @return array
		 */
		$keys = (array) apply_filters( 'wpforms_form_handler_unslash_field_keys', $keys );

		if ( empty( $keys ) ) {
			return $data;
		}

		foreach ( $data['fields'] as $id => $field ) {
			foreach ( $keys as $key ) {
				if ( isset( $field[ $key ] ) ) {
					$data['fields'][ $id ][ $key ] = wp_unslash( $field[ $key ] );
				}
			}
		}

		return $data;
	}

	/**
	 * Removes content filters that may break forms with HTML and adds a filter to prevent JSON damage.
	 *
	 * Specifically, it removes filters that sanitize content in a way that disrupts form functionality:
	 * - `balanceTags` to prevent unintended tag balancing.
	 * - `wp_filter_post_kses` to bypass permission-based sanitization (`unfiltered_html` capability).
	 *
	 * Additionally, adds a filter to clear `link rel` attributes to avoid unintended JSON issues.
	 *
	 * @since 1.9.8
	 */
	private function remove_form_content_filters(): void { // phpcs:ignore WPForms.PHP.HooksMethod.InvalidPlaceForAddingHooks
		// This filter breaks forms if they contain HTML.
		remove_filter( 'content_save_pre', 'balanceTags', 50 );
		// This filter breaks forms if the current user doesn't have "unfiltered_html" capability.
		remove_filter( 'content_save_pre', 'wp_filter_post_kses' );

		// Add a filter of the link rel attr to avoid JSON damage.
		add_filter( 'wp_targeted_link_rel', '__return_empty_string', 50, 1 );
	}
}
