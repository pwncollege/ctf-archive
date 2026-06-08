<?php

namespace WPForms\Admin\Forms;

use WP_Post;
use WPForms\Admin\Notice;
use WPForms\Pro\Tasks\Actions\PurgeTemplateEntryTask;

/**
 * User Templates class.
 *
 * @since 1.8.8
 */
class UserTemplates {

	/**
	 * Initialize class.
	 *
	 * @since 1.8.8
	 */
	public function init() {

		$this->hooks();
	}

	/**
	 * Hooks.
	 *
	 * @since 1.8.8
	 */
	public function hooks() {

		add_action( 'init', [ $this, 'register_post_type' ] );

		// Add template states.
		add_filter( 'display_post_states', [ $this, 'add_template_states' ], 10, 2 );

		// Modify get form args on the overview page.
		add_filter( 'wpforms_get_form_args', [ $this, 'add_template_post_type' ] );

		// Modify Show Templates user option.
		add_filter( 'get_user_option_wpforms_forms_overview_show_form_templates', [ $this, 'get_forms_overview_show_form_templates_option' ] );

		// Disable payment processing for user templates.
		add_filter( 'wpforms_process_before_form_data', [ $this, 'process_before_form_data' ] );

		// Add user templates to the form templates list.
		add_filter( 'wpforms_form_templates', [ $this, 'add_form_templates' ] );

		// AJAX handler for deleting user templates.
		add_action( 'wp_ajax_wpforms_user_template_remove', [ $this, 'ajax_remove_user_template' ] );

		// Disable Lite Connect integration for user templates on form submission.
		add_action( 'wpforms_process', [ $this, 'process_entry' ], 10, 3 );

		if ( wpforms()->is_pro() ) {
			// Add notices about entry(ies) being purged.
			add_action( 'admin_notices', [ $this, 'get_template_entries_notice' ] );
			add_action( 'admin_notices', [ $this, 'get_template_entry_notice' ] );

			// Add purge entry task.
			add_filter( 'wpforms_tasks_get_tasks', [ $this, 'add_purge_entry_task' ] );

			// Disable edit entry for templates.
			add_filter( 'wpforms_current_user_can', [ $this, 'disable_edit_entry' ], 10, 3 );
		}
	}

	/**
	 * Register the `wpforms-template` post type.
	 *
	 * @since 1.8.8
	 */
	public function register_post_type() {

		/**
		 * Filter the arguments for the `wpforms-template` post type.
		 *
		 * @since 1.8.8
		 *
		 * @param array $args Post type arguments.
		 */
		$args = apply_filters( // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName
			'wpforms_template_post_type_args',
			[
				'label'               => 'WPForms Template',
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

		register_post_type( 'wpforms-template', $args );
	}

	/**
	 * Add template states.
	 *
	 * @since 1.8.8
	 *
	 * @param array   $post_states Array of post states.
	 * @param WP_Post $post        Post object.
	 *
	 * @return array
	 */
	public function add_template_states( $post_states, $post ) {

		if ( ! ( wpforms_is_admin_page( 'overview' ) || wpforms_is_admin_page( 'entries' ) ) ) {
			return $post_states;
		}

		// No need to show template states on the templates page.
		if ( wpforms_is_admin_page( 'overview' ) && wpforms()->obj( 'forms_views' )->get_current_view() === 'templates' ) {
			return $post_states;
		}

		if ( $post->post_type === 'wpforms-template' ) {
			$post_states['wpforms_template'] = __( 'Template', 'wpforms-lite' );
		}

		return $post_states;
	}

	/**
	 * Disable edit entry for templates.
	 *
	 * @since 1.8.8
	 *
	 * @param bool   $user_can Whether the current user can perform the given capability.
	 * @param string $caps     Capability name.
	 * @param int    $id       The ID of the object to check against.
	 *
	 * @return bool Whether the current user can perform the given capability.
	 */
	public function disable_edit_entry( bool $user_can, $caps, $id ): bool {

		if ( $caps === 'edit_entries_form_single' && wpforms_is_form_template( $id ) ) {
			$user_can = false;
		}

		return $user_can;
	}

	/**
	 * Display admin notice for the entries page.
	 *
	 * @since 1.8.8
	 */
	public function get_template_entries_notice() {

		if ( ! wpforms_is_admin_page( 'entries', 'list' ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$form_id = ! empty( $_REQUEST['form_id'] ) ? absint( $_REQUEST['form_id'] ) : 0;

		// The notice should be displayed only for form templates.
		if ( ! wpforms_is_form_template( $form_id ) ) {
			return;
		}

		// If there are no entries, we don't need to display the notice on the empty state page.
		$entries = wpforms()->obj( 'entry' )->get_entries(
			[
				'form_id' => $form_id,
				'limit'   => 1,
			]
		);

		if ( empty( $entries ) ) {
			return;
		}

		/** This filter is documented in wpforms/src/Pro/Tasks/Actions/PurgeTemplateEntryTask.php */
		$delay = (int) apply_filters( 'wpforms_pro_tasks_actions_purge_template_entry_task_delay', DAY_IN_SECONDS ); // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName

		Notice::warning(
			sprintf(
			/* translators: %s - delay in formatted time. */
				esc_html__( 'Form template entries are for testing purposes and will be automatically deleted after %s.', 'wpforms-lite' ),
				// The `- 1` hack is to avoid the "1 day" message in favor of "24 hours".
				human_time_diff( time(), time() + $delay - 1 )
			)
		);
	}

	/**
	 * Display admin notice for the entry page.
	 *
	 * @since 1.8.8
	 */
	public function get_template_entry_notice() {

		if ( ! wpforms_is_admin_page( 'entries', 'details' ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$entry_id = ! empty( $_REQUEST['entry_id'] ) ? absint( $_REQUEST['entry_id'] ) : 0;

		$entry = wpforms()->obj( 'entry' )->get( $entry_id );

		// If entry does not exist, we don't need to display the notice on the empty state page.
		if ( empty( $entry ) ) {
			return;
		}

		// The notice should be displayed only for form template entry.
		if ( ! wpforms_is_form_template( $entry->form_id ) ) {
			return;
		}

		$meta = wpforms()->obj( 'entry_meta' )->get_meta(
			[
				'entry_id' => absint( $entry_id ),
				'type'     => 'purge_template_entry_task',
				'number'   => 1,
			]
		);

		if ( empty( $meta ) ) {
			return;
		}

		$task = wpforms_json_decode( $meta[0]->data,true );

		if ( empty( $task['timestamp'] ) ) {
			return;
		}

		Notice::warning(
			sprintf(
			/* translators: %s - delay in formatted time. */
				esc_html__( 'Form template entries are for testing purposes. This entry will be automatically deleted in %s.', 'wpforms-lite' ),
				human_time_diff( time(), $task['timestamp'] )
			)
		);
	}

	/**
	 * Get the Show Templates user option.
	 *
	 * If the user has not set the Show Templates screen option, it will default to showing templates.
	 * In this case, we want to show templates by default.
	 *
	 * @since 1.8.8
	 *
	 * @return bool Whether to show templates by default.
	 */
	public function get_forms_overview_show_form_templates_option(): bool {

		$screen_options = get_user_option( 'wpforms_forms_overview_options' );

		$result = $screen_options['wpforms_forms_overview_show_form_templates'] ?? true;

		return $result !== '0'; // phpcs:ignore WPForms.Formatting.EmptyLineBeforeReturn.RemoveEmptyLineBeforeReturnStatement
	}

	/**
	 * Add `wpforms-template` post type to the form args.
	 *
	 * @since 1.8.8
	 *
	 * @param array $args Form arguments.
	 *
	 * @return array
	 */
	public function add_template_post_type( array $args ): array {

		// Only add the post type to the form args on the overview page.
		if ( ! wpforms_is_admin_page( 'overview' ) ) {
			return $args;
		}

		// Only add the template post type if the Show Templates screen option is enabled
		// and `post_type` is not already set.
		if ( ! isset( $args['post_type'] ) && wpforms()->obj( 'forms_overview' )->overview_show_form_templates() ) {
			$args['post_type'] = wpforms()->obj( 'form' )::POST_TYPES;
		}

		return $args;
	}

	/**
	 * Add user templates to the form templates list.
	 *
	 * @since 1.8.8
	 *
	 * @param array $templates Form templates.
	 *
	 * @return array Form templates.
	 */
	public function add_form_templates( array $templates ): array {

		$user_templates = wpforms()->obj( 'form' )->get( '', [ 'post_type' => 'wpforms-template' ] );

		if ( empty( $user_templates ) ) {
			return $templates;
		}

		foreach ( $user_templates as $template ) {
			$template_data = wpforms_decode( $template->post_content );

			$edit_url = add_query_arg(
				[
					'page'    => 'wpforms-builder',
					'form_id' => $template->ID,
				],
				admin_url( 'admin.php' )
			);

			$create_url = add_query_arg(
				[
					'page'     => 'wpforms-builder',
					'form_id'  => $template->ID,
					'action'   => 'template_to_form',
					'_wpnonce' => wp_create_nonce( 'wpforms_template_to_form_form_nonce' ),
				],
				admin_url( 'admin.php' )
			);

			$templates[] = [
				'name'             => $template->post_title,
				'slug'             => 'wpforms-user-template-' . $template->ID,
				'action_text'      => wpforms_is_admin_page( 'builder' ) || wp_doing_ajax() ? esc_html__( 'Use Template', 'wpforms-lite' ) : esc_html__( 'Create Form', 'wpforms-lite' ),
				'edit_action_text' => esc_html__( 'Edit Template', 'wpforms-lite' ),
				'description'      => ! empty( $template_data['settings']['template_description'] ) ? $template_data['settings']['template_description'] : '',
				'source'           => 'wpforms-user-template',
				'create_url'       => $create_url,
				'edit_url'         => $edit_url,
				'categories'       => [ 'user' ],
				'has_access'       => true,
				'data'             => $template_data,
				'post_id'          => $template->ID,
			];
		}

		return $templates;
	}

	/**
	 * AJAX handler for removing user templates.
	 *
	 * @since 1.8.8
	 */
	public function ajax_remove_user_template(): void {

		// Run a security check.
		check_ajax_referer( 'wpforms-form-templates', 'nonce' );

		$template_id = isset( $_POST['template'] ) ? absint( $_POST['template'] ) : 0;

		if ( ! $template_id ) {
			wp_send_json_error();
		}

		// Check for permissions for the specific template.
		if ( ! wpforms_current_user_can( 'delete_form_single', $template_id ) ) {
			wp_send_json_error( esc_html__( 'You do not have permission to delete this template.', 'wpforms-lite' ) );
		}

		// Verify the post exists and is a template.
		$template = get_post( $template_id );

		if ( ! $template || $template->post_type !== 'wpforms-template' ) {
			wp_send_json_error( esc_html__( 'Template not found.', 'wpforms-lite' ) );
		}

		// Delete the template.
		$result = wp_delete_post( $template_id, true );

		if ( ! $result ) {
			wp_send_json_error( esc_html__( 'Failed to delete the template.', 'wpforms-lite' ) );
		}

		wp_send_json_success();
	}

	/**
	 * Add purge entry task.
	 *
	 * @since 1.8.8
	 *
	 * @param array $tasks Task class list.
	 */
	public function add_purge_entry_task( $tasks ) {

		$tasks[] = PurgeTemplateEntryTask::class;

		return $tasks;
	}

	/**
	 * Modify the form data before it is processed to disable payment processing.
	 *
	 * @since 1.8.8
	 *
	 * @param array $form_data Form data.
	 *
	 * @return array
	 */
	public function process_before_form_data( $form_data ) {

		if ( ! isset( $form_data['id'] ) ) {
			return $form_data;
		}

		if ( wpforms_is_form_template( $form_data['id'] ) ) {
			$form_data['payments'] = [];
		}

		return $form_data;
	}

	/**
	 * Disable Lite Connect integration for user templates while processing submission.
	 *
	 * @since 1.8.8
	 *
	 * @param array $fields    Form fields.
	 * @param array $entry     Form entry.
	 * @param array $form_data Form data.
	 */
	public function process_entry( array $fields, array $entry, array $form_data ) { // phpcs:ignore WPForms.PHP.HooksMethod.InvalidPlaceForAddingHooks

		if ( ! wpforms_is_form_template( $form_data['id'] ) ) {
			return;
		}

		add_filter( 'wpforms_integrations_lite_connect_is_allowed', '__return_false' );
	}
}
