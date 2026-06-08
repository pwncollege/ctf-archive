<?php
/**
 * Ajax actions used in by admin.
 *
 * @since 1.0.0
 */

use WPForms\Helpers\DB;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Save a form.
 *
 * @since 1.0.0
 */
function wpforms_save_form() {

	// Run a security check.
	if ( ! check_ajax_referer( 'wpforms-builder', 'nonce', false ) ) {
		wp_send_json_error( esc_html__( 'Your session expired. Please reload the builder.', 'wpforms-lite' ) );
	}

	// Check for permissions.
	if ( ! wpforms_current_user_can( 'edit_forms' ) ) {
		wp_send_json_error( esc_html__( 'You are not allowed to perform this action.', 'wpforms-lite' ) );
	}

	// Check for form data.
	if ( empty( $_POST['data'] ) ) {
		wp_send_json_error( esc_html__( 'Something went wrong while performing this action.', 'wpforms-lite' ) );
	}

	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$form_post = json_decode( wp_unslash( $_POST['data'] ), false );

	$data = wpforms_prepare_form_data( $form_post );
	$data = wpforms_sanitize_form_data( $data );

	// Process fields data.
	$data['fields'] = wpforms()->obj( 'builder_save_form' )->process_fields( $data['fields'], $data );

	// Get form tags.
	$form_tags = isset( $data['settings']['form_tags_json'] ) ? json_decode( wp_unslash( $data['settings']['form_tags_json'] ), true ) : [];

	// Clear unnecessary data.
	unset( $data['settings']['form_tags_json'] );

	// Store tags labels in the form settings.
	$data['settings']['form_tags'] = wp_list_pluck( $form_tags, 'label' );

	// Update form data.
	$form_id = (int) wpforms()->obj( 'form' )->update( $data['id'], $data, [ 'context' => 'save_form' ] );

	/**
	 * Fires after updating form data.
	 *
	 * @since 1.4.0
	 *
	 * @param int   $form_id Form ID.
	 * @param array $data    Form data.
	 */
	do_action( 'wpforms_builder_save_form', $form_id, $data );

	if ( ! $form_id ) {
		wp_send_json_error( esc_html__( 'Something went wrong while saving the form.', 'wpforms-lite' ) );
	}

	// Update form tags.
	wp_set_post_terms(
		$form_id,
		wpforms()->obj( 'forms_tags_ajax' )->get_processed_tags( $form_tags ),
		WPForms_Form_Handler::TAGS_TAXONOMY
	);

	$response_data = [
		'form_name' => esc_html( $data['settings']['form_title'] ),
		'form_desc' => $data['settings']['form_desc'],
		'redirect'  => admin_url( 'admin.php?page=wpforms-overview' ),
	];

	/**
	 * Allows filtering ajax response data after the form was saved.
	 *
	 * @since 1.5.1
	 *
	 * @param array $response_data The data to be sent in the response.
	 * @param int   $form_id       Form ID.
	 * @param array $data          Form data.
	 */
	$response_data = apply_filters(
		'wpforms_builder_save_form_response_data',
		$response_data,
		$form_id,
		$data
	);

	wp_send_json_success( $response_data );
}

add_action( 'wp_ajax_wpforms_save_form', 'wpforms_save_form' );

/**
 * Prepare form data.
 *
 * @since 1.9.4
 *
 * @param object $form_post Form data received from $_POST.
 *
 * @return array
 */
function wpforms_prepare_form_data( $form_post ): array {

	$data = [
		'fields' => [],
	];

	if ( ! $form_post ) {
		return $data;
	}

	$new_post_data_accum = [];

	foreach ( $form_post as $post_input_data ) {
		// For input names that are arrays (e.g. `menu-item-db-id[3][4][5]`),
		// derive the array path keys via regex and set the value in $_POST.
		preg_match( '#([^\[]*)(\[(.+)])?#', $post_input_data->name, $matches );

		$array_bits = [ $matches[1] ];

		if ( isset( $matches[3] ) ) {
			/**
			 * This array_merge is not slow because it is new for each loop iteration.
			 *
			 * @noinspection SlowArrayOperationsInLoopInspection
			 */
			$array_bits = array_merge( $array_bits, explode( '][', $matches[3] ) );
		}

		$new_post_data = [];

		// Build the new array value from leaf to trunk.
		for ( $i = count( $array_bits ) - 1; $i >= 0; $i-- ) {
			if ( $i === count( $array_bits ) - 1 ) {
				$new_post_data[ $array_bits[ $i ] ] = wp_slash( $post_input_data->value );
			} else {
				$new_post_data = [
					$array_bits[ $i ] => $new_post_data,
				];
			}
		}

		$new_post_data_accum[] = $new_post_data;
	}

	return array_replace_recursive( $data, ...$new_post_data_accum );
}

/**
 * Create a new form.
 *
 * @since 1.0.0
 */
function wpforms_new_form() { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

	check_ajax_referer( 'wpforms-builder', 'nonce' );

	// Prevent the second form creating if a user has no license set.
	// Redirect will lead to the warning page.
	if ( wpforms()->is_pro() && empty( wpforms_get_license_type() ) && wp_count_posts( 'wpforms' )->publish >= 1 ) {
		wp_send_json_success( [ 'redirect' => admin_url( 'admin.php?page=wpforms-builder&view=setup' ) ] );
	}

	if ( empty( $_POST['title'] ) ) {
		wp_send_json_error(
			[
				'error_type' => 'missing_form_title',
				'message'    => esc_html__( 'No Form Name Provided', 'wpforms-lite' ),
			]
		);
	}

	$form_title    = sanitize_text_field( wp_unslash( $_POST['title'] ) );
	$form_template = empty( $_POST['template'] ) ? 'blank' : sanitize_text_field( wp_unslash( $_POST['template'] ) );
	$category      = empty( $_POST['category'] ) ? 'all' : sanitize_text_field( wp_unslash( $_POST['category'] ) );
	$subcategory   = empty( $_POST['subcategory'] ) ? 'all' : sanitize_text_field( wp_unslash( $_POST['subcategory'] ) );

	if ( ! wpforms()->obj( 'builder_templates' )->is_valid_template( $form_template ) ) {
		wp_send_json_error(
			[
				'error_type' => 'invalid_template',
				'message'    => esc_html__( 'The template you selected is currently not available, but you can try again later. If you continue to have trouble, please reach out to support.', 'wpforms-lite' ),
			]
		);
	}

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
			'template'    => $form_template,
			'category'    => $category,
			'subcategory' => $subcategory,
		]
	);

	if ( $title_exists ) {

		// Skip creating a revision for this action.
		remove_action( 'post_updated', 'wp_save_post_revision' );

		wp_update_post(
			[
				'ID'         => $form_id,
				'post_title' => $form_title . ' (ID #' . $form_id . ')',
			]
		);

		// Restore the initial revisions state.
		add_action( 'post_updated', 'wp_save_post_revision' );
	}

	if ( ! $form_id ) {
		wp_send_json_error(
			[
				'error_type' => 'cant_create_form',
				'message'    => esc_html__( 'Error Creating Form', 'wpforms-lite' ),
			]
		);
	}

	if ( wpforms_current_user_can( 'edit_form_single', $form_id ) ) {
		wp_send_json_success(
			[
				'id'       => $form_id,
				'redirect' => add_query_arg(
					[
						'view'    => 'fields',
						'form_id' => $form_id,
						'newform' => '1',
					],
					admin_url( 'admin.php?page=wpforms-builder' )
				),
			]
		);
	}

	if ( wpforms_current_user_can( 'view_forms' ) ) {
		wp_send_json_success( [ 'redirect' => admin_url( 'admin.php?page=wpforms-overview' ) ] );
	}

	wp_send_json_success( [ 'redirect' => admin_url() ] );
}

add_action( 'wp_ajax_wpforms_new_form', 'wpforms_new_form' );

/**
 * Update form template.
 *
 * @since 1.0.0
 */
function wpforms_update_form_template() { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.MaxExceeded

	// Run a security check.
	check_ajax_referer( 'wpforms-builder', 'nonce' );

	// Check for permissions.
	if ( ! wpforms_current_user_can( 'edit_forms' ) ) {
		wp_send_json_error(
			[
				'error_type' => 'permissions_denied',
				'message'    => esc_html__( 'You are not allowed to perform this action.', 'wpforms-lite' ),
			]
		);
	}

	// Check for form ID.
	if ( empty( $_POST['form_id'] ) ) {
		wp_send_json_error(
			[
				'error_type' => 'invalid_form_id',
				'message'    => esc_html__( 'No Form ID Provided', 'wpforms-lite' ),
			]
		);
	}

	// Set initial variables.
	$form_id       = absint( $_POST['form_id'] );
	$form_template = empty( $_POST['template'] ) ? 'blank' : sanitize_text_field( wp_unslash( $_POST['template'] ) );
	$category      = empty( $_POST['category'] ) ? 'all' : sanitize_text_field( wp_unslash( $_POST['category'] ) );
	$subcategory   = empty( $_POST['subcategory'] ) ? 'all' : sanitize_text_field( wp_unslash( $_POST['subcategory'] ) );

	// Check for valid template.
	if ( ! wpforms()->obj( 'builder_templates' )->is_valid_template( $form_template ) ) {
		wp_send_json_error(
			[
				'error_type' => 'invalid_template',
				'message'    => esc_html__( 'The template you selected is currently not available, but you can try again later. If you continue to have trouble, please reach out to support.', 'wpforms-lite' ),
			]
		);
	}

	// Get current form data.
	$data = wpforms()->obj( 'form' )->get(
		$form_id,
		[
			'content_only' => true,
		]
	);

	// Get the cached data from the form template JSON.
	$template_data = wpforms()->obj( 'builder_templates' )->get_template( $form_template );

	// If the template title is set, use it. Otherwise, clear the form title.
	$template_title = ! empty( $template_data['name'] ) ? $template_data['name'] : '';

	// If the form title is set, use it. Otherwise, use the template title.
	$form_title = ! empty( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : $template_title;

	// Check if the current form title is equal to the previous template name.
	// If so, set the form title equal to the new template name.
	$prev_template_slug = $data['meta']['template'] ?? '';
	$prev_template      = wpforms()->obj( 'builder_templates' )->get_template( $prev_template_slug );
	$form_title         = isset( $prev_template['name'] ) && $prev_template['name'] === $form_title ? $template_title : $form_title;

	// If these template titles are empty, use the form title.
	$form_pages_title          = $template_title ? $template_title : $form_title;
	$form_conversational_title = ! empty( $template_data['data']['settings']['conversational_forms_title'] ) ? $template_data['data']['settings']['conversational_forms_title'] : $form_title;

	// If these template slugs are empty, use the form title.
	$form_conversational_slug = ! empty( $template_data['data']['settings']['conversational_forms_page_slug'] ) ? $template_data['data']['settings']['conversational_forms_page_slug'] : $form_title;
	$form_pages_slug          = ! empty( $template_data['data']['settings']['form_pages_page_slug'] ) ? $template_data['data']['settings']['form_pages_page_slug'] : $form_title;

	// Loop over notifications.
	$notifications = $template_data['data']['settings']['notifications'] ?? [];

	foreach ( $notifications as $key => $notification ) {
		// If the subject is empty, set it to an empty string.
		$notification_subject = ! empty( $notification['subject'] ) ? sanitize_text_field( $notification['subject'] ) : '';

		$data['settings']['notifications'][ $key ]['subject'] = $notification_subject;
	}

	// Loop over confirmations.
	$confirmations = $template_data['data']['settings']['confirmations'] ?? [];

	foreach ( $confirmations as $key => $confirmation ) {

		// If the message is empty, set it to an empty string.
		$confirmation_message = ! empty( $confirmation['message'] ) ? wp_kses_post( $confirmation['message'] ) : '';

		$data['settings']['confirmations'][ $key ]['message'] = $confirmation_message;
	}

	// Set updated form titles.
	$data['settings']['form_title']                 = sanitize_text_field( $form_title );
	$data['settings']['form_pages_title']           = sanitize_text_field( $form_pages_title );
	$data['settings']['conversational_forms_title'] = sanitize_text_field( $form_conversational_title );

	// Set updated form slugs.
	$data['settings']['form_pages_page_slug']           = sanitize_title( $form_pages_slug );
	$data['settings']['conversational_forms_page_slug'] = sanitize_title( $form_conversational_slug );

	// Try to update the form.
	$updated = (bool) wpforms()->obj( 'form' )->update(
		$form_id,
		$data,
		[
			'template'    => $form_template,
			'category'    => $category,
			'subcategory' => $subcategory,
		]
	);

	// If the form was updated, return the form ID and redirect to the form builder.
	if ( $updated ) {
		wp_send_json_success(
			[
				'id'       => $form_id,
				'redirect' => add_query_arg(
					[
						'view'    => 'fields',
						'form_id' => $form_id,
					],
					admin_url( 'admin.php?page=wpforms-builder' )
				),
			]
		);
	}

	// Otherwise, return an error.
	wp_send_json_error(
		[
			'error_type' => 'cant_update',
			'message'    => esc_html__( 'Error Updating Template', 'wpforms-lite' ),
		]
	);
}

add_action( 'wp_ajax_wpforms_update_form_template', 'wpforms_update_form_template' );

/**
 * Form Builder update next field ID.
 *
 * @since 1.2.9
 */
function wpforms_builder_increase_next_field_id() {

	// Run a security check.
	check_ajax_referer( 'wpforms-builder', 'nonce' );

	// Check for permissions.
	if ( ! wpforms_current_user_can( 'edit_forms' ) ) {
		wp_send_json_error();
	}

	// Check for required items.
	if ( empty( $_POST['form_id'] ) ) {
		wp_send_json_error();
	}

	$args = [];

	// In the case of duplicating the Layout field that contains a bunch of fields,
	// we need to set the next `field_id` to the desired value which is passed via POST argument.
	if ( ! empty( $_POST['field_id'] ) ) {
		$args['field_id'] = sanitize_text_field( wp_unslash( $_POST['field_id'] ) );
	}

	wpforms()->obj( 'form' )->next_field_id( absint( $_POST['form_id'] ), $args );

	wp_send_json_success();
}

add_action( 'wp_ajax_wpforms_builder_increase_next_field_id', 'wpforms_builder_increase_next_field_id' );

/**
 * Form Builder Dynamic Choices option toggle.
 *
 * This can be triggered with select/radio/checkbox fields.
 *
 * @since 1.2.8
 */
function wpforms_builder_dynamic_choices() {

	// Run a security check.
	check_ajax_referer( 'wpforms-builder', 'nonce' );

	// Check for permissions.
	if ( ! wpforms_current_user_can( 'edit_forms' ) ) {
		wp_send_json_error();
	}

	// Check for valid/required items.
	if ( ! isset( $_POST['field_id'] ) || empty( $_POST['type'] ) || ! in_array( $_POST['type'], [ 'post_type', 'taxonomy' ], true ) ) {
		wp_send_json_error();
	}

	$type = sanitize_key( $_POST['type'] );
	$id   = sanitize_text_field( wp_unslash( $_POST['field_id'] ) );

	// Fetch the option row HTML to be returned to the builder.
	$field      = new WPForms_Field_Select( false );
	$field_args = [
		'id'              => $id,
		'dynamic_choices' => $type,
	];
	$option_row = $field->field_option( 'dynamic_choices_source', $field_args, [], false );

	wp_send_json_success(
		[
			'markup' => $option_row,
		]
	);
}

add_action( 'wp_ajax_wpforms_builder_dynamic_choices', 'wpforms_builder_dynamic_choices' );

/**
 * Form Builder Dynamic Choices Source option toggles.
 *
 * This can be triggered with select/radio/checkbox fields.
 *
 * @since 1.2.8
 */
function wpforms_builder_dynamic_source() {

	// Run a security check.
	check_ajax_referer( 'wpforms-builder', 'nonce' );

	// Check for permissions.
	if ( ! wpforms_current_user_can( 'edit_forms' ) ) {
		wp_send_json_error();
	}

	// Check for required items.
	if ( ! isset( $_POST['field_id'] ) || empty( $_POST['form_id'] ) || empty( $_POST['type'] ) || empty( $_POST['source'] ) ) {
		wp_send_json_error();
	}

	$type        = sanitize_key( $_POST['type'] );
	$source      = sanitize_key( $_POST['source'] );
	$id          = sanitize_text_field( wp_unslash( $_POST['field_id'] ) );
	$form_id     = absint( $_POST['form_id'] );
	$items       = [];
	$total       = 0;
	$source_name = '';
	$type_name   = '';

	if ( $type === 'post_type' ) {
		$type_name = esc_html__( 'post type', 'wpforms-lite' );
		$args      = [
			'post_type'      => $source,
			'posts_per_page' => 20,
			'orderby'        => 'title',
			'order'          => 'ASC',
		];

		/**
		 * Filters the arguments used to query the post type for dynamic choices.
		 *
		 * @since 1.4.1
		 *
		 * @param array $args    Arguments used to query the post's type for dynamic choices.
		 * @param array $field   Field.
		 * @param int   $form_id Form ID.
		 */
		$args  = (array) apply_filters( 'wpforms_dynamic_choice_post_type_args', $args, [ 'id' => $id ], $form_id );
		$posts = wpforms_get_hierarchical_object( $args, true );
		$total = wp_count_posts( $source );
		$total = $total->publish;
		$pt    = get_post_type_object( $source );

		if ( $pt !== null ) {
			$source_name = $pt->labels->name;
		}

		foreach ( $posts as $post ) {
			$items[] = esc_html( wpforms_get_post_title( $post ) );
		}
	} elseif ( $type === 'taxonomy' ) {
		$type_name = esc_html__( 'taxonomy', 'wpforms-lite' );
		$args      = [
			'taxonomy'   => $source,
			'hide_empty' => false,
			'number'     => 20,
		];

		/**
		 * Filters the arguments used to query the taxonomy for dynamic choices.
		 *
		 * @since 1.4.1
		 *
		 * @param array $args    Arguments used to query the post's type for dynamic choices.
		 * @param array $field   Field.
		 * @param int   $form_id Form ID.
		 */
		$args        = apply_filters( 'wpforms_dynamic_choice_taxonomy_args', $args, [ 'id' => $id ], $form_id );
		$terms       = wpforms_get_hierarchical_object( $args, true );
		$total       = wp_count_terms( $source );
		$tax         = get_taxonomy( $source );
		$source_name = $tax->labels->name;

		foreach ( $terms as $term ) {
			$items[] = esc_html( wpforms_get_term_name( $term ) );
		}
	}

	if ( empty( $items ) ) {
		$items = [];
	}

	wp_send_json_success(
		[
			'items'       => $items,
			'source'      => $source,
			'source_name' => $source_name,
			'total'       => $total,
			'type'        => $type,
			'type_name'   => $type_name,
		]
	);
}

add_action( 'wp_ajax_wpforms_builder_dynamic_source', 'wpforms_builder_dynamic_source' );

/**
 * Perform a test connection to verify that the current web host can successfully
 * make outbound SSL connections.
 *
 * @since 1.4.5
 */
function wpforms_verify_ssl() {

	// Run a security check.
	check_ajax_referer( 'wpforms-admin', 'nonce' );

	// Check for permissions.
	if ( ! wpforms_current_user_can() ) {
		wp_send_json_error(
			[
				'msg' => esc_html__( 'You do not have permission to perform this operation.', 'wpforms-lite' ),
			]
		);
	}

	$response = wp_remote_post( 'https://wpforms.com/connection-test.php' );

	if ( 200 === wp_remote_retrieve_response_code( $response ) ) {
		wp_send_json_success(
			[
				'msg' => esc_html__( 'Success! Your server can make SSL connections.', 'wpforms-lite' ),
			]
		);
	}

	wp_send_json_error(
		[
			'msg'   => esc_html__( 'There was an error and the connection failed. Please contact your web host with the technical details below.', 'wpforms-lite' ),
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
			'debug' => '<pre>' . print_r( map_deep( $response, 'wp_strip_all_tags' ), true ) . '</pre>',
		]
	);
}
add_action( 'wp_ajax_wpforms_verify_ssl', 'wpforms_verify_ssl' );

/**
 * Recreate custom database tables.
 *
 * @since 1.9.0
 */
function wpforms_recreate_tables() {

	// Run a security check.
	check_ajax_referer( 'wpforms-admin', 'nonce' );

	// Check for permissions.
	if ( ! wpforms_current_user_can() ) {
		wp_send_json_error(
			[
				'msg' => esc_html__( 'You do not have permission to perform this operation.', 'wpforms-lite' ),
			]
		);
	}

	DB::create_custom_tables( true );

	if ( DB::custom_tables_exist() ) {
		wp_send_json_success(
			[
				'msg' => esc_html__( 'WPForms custom database tables are recreated.', 'wpforms-lite' ),
			]
		);
	}

	wp_send_json_error(
		[
			'msg' => esc_html__( 'Error recreating WPForms custom database tables.', 'wpforms-lite' ),
		]
	);
}

add_action( 'wp_ajax_wpforms_recreate_tables', 'wpforms_recreate_tables' );

/**
 * Deactivate addon.
 *
 * @since 1.0.0
 * @since 1.6.2.3 Updated the permissions checking.
 */
function wpforms_deactivate_addon() {

	// Run a security check.
	check_ajax_referer( 'wpforms-admin', 'nonce' );

	// Check for permissions.
	if ( ! current_user_can( 'deactivate_plugins' ) ) {
		wp_send_json_error( esc_html__( 'Plugin deactivation is disabled for you on this site.', 'wpforms-lite' ) );
	}

	$type = empty( $_POST['type'] ) ? 'addon' : sanitize_key( $_POST['type'] );

	if ( isset( $_POST['plugin'] ) ) {
		$plugin = sanitize_text_field( wp_unslash( $_POST['plugin'] ) );

		deactivate_plugins( $plugin );

		/**
		 * Fire after the plugin deactivating via the WPForms installer.
		 *
		 * @since 1.6.3
		 *
		 * @param string $plugin Plugin deactivated.
		 */
		do_action( 'wpforms_plugin_deactivated', $plugin );

		if ( $type === 'plugin' ) {
			wp_send_json_success( esc_html__( 'Plugin deactivated.', 'wpforms-lite' ) );
		} else {
			wp_send_json_success( esc_html__( 'Addon deactivated.', 'wpforms-lite' ) );
		}
	}

	wp_send_json_error( esc_html__( 'Could not deactivate the addon. Please deactivate from the Plugins page.', 'wpforms-lite' ) );
}
add_action( 'wp_ajax_wpforms_deactivate_addon', 'wpforms_deactivate_addon' );

/**
 * Activate addon.
 *
 * @since 1.0.0
 * @since 1.6.2.3 Updated the permissions checking.
 */
function wpforms_activate_addon() {

	// Run a security check.
	check_ajax_referer( 'wpforms-admin', 'nonce' );

	// Check for permissions.
	if ( ! current_user_can( 'activate_plugins' ) ) {
		wp_send_json_error( esc_html__( 'Plugin activation is disabled for you on this site.', 'wpforms-lite' ) );
	}

	$success_messages = [
		'plugin' => __( 'Plugin activated.', 'wpforms-lite' ),
		'addon'  => __( 'Addon activated.', 'wpforms-lite' ),
	];
	$error_messages   = [
		'plugin' => __( 'Could not activate the plugin. Please activate it on the Plugins page.', 'wpforms-lite' ),
		'addon'  => __( 'Could not activate the addon. Please activate it on the Plugins page.', 'wpforms-lite' ),
	];

	$type            = ! empty( $_POST['type'] ) ? sanitize_key( $_POST['type'] ) : 'addon';
	$success_message = $success_messages[ $type ];
	$error_message   = $error_messages[ $type ];

	if ( isset( $_POST['plugin'] ) ) {
		$plugin   = sanitize_text_field( wp_unslash( $_POST['plugin'] ) );
		$activate = wpforms_activate_plugin( $plugin );

		/**
		 * Fire after the plugin activating via the WPForms installer.
		 *
		 * @since 1.6.3.1
		 *
		 * @param string $plugin Path to the plugin file relative to the plugins' directory.
		 */
		do_action( 'wpforms_plugin_activated', $plugin );

		if ( $activate === null ) {
			wp_send_json_success( wp_kses_post( $success_message ) );
		}

		$error_message = $activate->get_error_message();
	}

	wp_send_json_error( wp_kses_post( $error_message ) );
}

add_action( 'wp_ajax_wpforms_activate_addon', 'wpforms_activate_addon' );

/**
 * Install addon.
 *
 * @since 1.0.0
 * @since 1.6.2.3 Updated the permissions checking.
 *
 * @noinspection HtmlUnknownTarget
 */
function wpforms_install_addon() { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

	// Run a security check.
	check_ajax_referer( 'wpforms-admin', 'nonce' );

	$generic_error = esc_html__( 'There was an error while performing your request.', 'wpforms-lite' );
	$type          = ! empty( $_POST['type'] ) ? sanitize_key( $_POST['type'] ) : 'addon';

	// Check if new installations are allowed.
	if ( ! wpforms_can_install( $type ) ) {
		wp_send_json_error( $generic_error );
	}

	$error = $type === 'plugin'
		? esc_html__( 'Could not install the plugin. Please download and install it manually.', 'wpforms-lite' )
		: sprintf(
			wp_kses( /* translators: %1$s - addon download URL, %2$s - link to a manual installation guide, %3$s - link to contact support. */
				__( 'Could not install the addon. Please <a href="%1$s" target="_blank" rel="noopener noreferrer">download it from wpforms.com</a> and <a href="%2$s" target="_blank" rel="noopener noreferrer">install it manually</a>, or <a href="%3$s" target="_blank" rel="noopener noreferrer">contact support</a> for assistance.', 'wpforms-lite' ),
				[
					'a' => [
						'href'   => true,
						'target' => true,
						'rel'    => true,
					],
				]
			),
			esc_url( wpforms_utm_link( 'https://wpforms.com/account/licenses/', 'Licenses', 'Addons Error' ) ),
			esc_url( wpforms_utm_link( 'https://wpforms.com/docs/how-to-manually-install-addons-in-wpforms/', 'Addons Doc', 'Addons Error' ) ),
			esc_url( wpforms_utm_link( 'https://wpforms.com/contact/', 'Contact', 'Addons Error' ) )
		);

	$plugin_url = ! empty( $_POST['plugin'] ) ? esc_url_raw( wp_unslash( $_POST['plugin'] ) ) : '';

	if ( empty( $plugin_url ) ) {
		wp_send_json_error( $error );
	}

	$args_str = ! empty( $_POST['args'] ) ? sanitize_text_field( wp_unslash( $_POST['args'] ) ) : '';
	$args     = json_decode( $args_str, true ) ?? [];

	// Set the current screen to avoid undefined notices.
	set_current_screen( 'wpforms_page_wpforms-settings' );

	// Prepare variables.
	$url = esc_url_raw(
		add_query_arg(
			[
				'page' => 'wpforms-addons',
			],
			admin_url( 'admin.php' )
		)
	);

	ob_start();
	$creds = request_filesystem_credentials( $url, '', false, false );

	// Hide the filesystem credentials form.
	ob_end_clean();

	// Check for file system permissions.
	if ( $creds === false ) {
		wp_send_json_error( $error );
	}

	if ( ! WP_Filesystem( $creds ) ) {
		wp_send_json_error( $error );
	}

	/*
	 * We do not need any extra credentials if we have gotten this far, so let's install the plugin.
	 */

	// Do not allow WordPress to search/download translations, as this will break JS output.
	remove_action( 'upgrader_process_complete', [ 'Language_Pack_Upgrader', 'async_upgrade' ], 20 );

	// Create the plugin upgrader with our custom skin.
	$installer = new WPForms\Helpers\PluginSilentUpgrader( new WP_Ajax_Upgrader_Skin() );

	// Error check.
	if ( ! method_exists( $installer, 'install' ) ) {
		wp_send_json_error( $error );
	}

	$installer->install( $plugin_url, $args );

	// Flush the cache and return the newly installed plugin basename.
	wp_cache_flush();

	$plugin_basename = $installer->plugin_info();

	if ( empty( $plugin_basename ) ) {
		wp_send_json_error( $error );
	}

	$result = [
		'msg'          => $generic_error,
		'is_activated' => false,
		'basename'     => $plugin_basename,
	];

	// Check for permissions.
	if ( ! current_user_can( 'activate_plugins' ) ) {
		$result['msg'] = $type === 'plugin' ? esc_html__( 'Plugin installed.', 'wpforms-lite' ) : esc_html__( 'Addon installed.', 'wpforms-lite' );

		wp_send_json_success( $result );
	}

	// Activate the plugin silently.
	$activated = activate_plugin( $plugin_basename );

	if ( ! is_wp_error( $activated ) ) {

		/**
		 * Fire after the plugin activating via the WPForms installer.
		 *
		 * @since 1.7.0
		 *
		 * @param string $plugin_basename Path to the plugin file relative to the plugins' directory.
		 */
		do_action( 'wpforms_plugin_activated', $plugin_basename );

		$result['is_activated'] = true;
		$result['msg']          = $type === 'plugin' ? esc_html__( 'Plugin installed & activated.', 'wpforms-lite' ) : esc_html__( 'Addon installed & activated.', 'wpforms-lite' );

		wp_send_json_success( $result );
	}

	// Fallback error just in case.
	wp_send_json_error( $result );
}
add_action( 'wp_ajax_wpforms_install_addon', 'wpforms_install_addon' );

/**
 * Search pages for dropdown.
 *
 * @since 1.7.9
 */
function wpforms_ajax_search_pages_for_dropdown() {

	// Run a security check.
	if ( ! check_ajax_referer( 'wpforms-builder', 'nonce', false ) ) {
		wp_send_json_error( esc_html__( 'Your session expired. Please reload the builder.', 'wpforms-lite' ) );
	}

	if ( ! array_key_exists( 'search', $_GET ) ) {
		wp_send_json_error( esc_html__( 'Incorrect usage of this operation.', 'wpforms-lite' ) );
	}

	$search              = sanitize_text_field( wp_unslash( $_GET['search'] ) );
	$result_pages        = [];
	$previous_page_label = esc_html__( 'Back to Previous Page (Referrer) ', 'wpforms-lite' );

	if ( stripos( strtolower( $previous_page_label ), $search ) !== false ) {
		$result_pages[] = [
			'value' => 'previous_page',
			'label' => $previous_page_label,
		];
	}

	$result_pages += wpforms_search_pages_for_dropdown( $search );

	if ( empty( $result_pages ) ) {
		wp_send_json_success( [] );
	}

	wp_send_json_success( $result_pages );
}
add_action( 'wp_ajax_wpforms_ajax_search_pages_for_dropdown', 'wpforms_ajax_search_pages_for_dropdown' );
