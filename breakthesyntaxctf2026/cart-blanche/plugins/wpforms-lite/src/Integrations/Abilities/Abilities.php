<?php

namespace WPForms\Integrations\Abilities;

use WP_Error;
use WP_Post;
use WPForms\Integrations\IntegrationInterface;

/**
 * WordPress Abilities API Integration for WPForms.
 *
 * Provides a standardized interface for AI assistants and automation tools
 * to discover and interact with WPForms functionality.
 *
 * @since 1.9.9
 */
abstract class Abilities implements IntegrationInterface {

	/**
	 * Ability namespace for WPForms abilities.
	 *
	 * @since 1.9.9
	 *
	 * @var string
	 */
	protected const ABILITY_NAMESPACE = 'wpforms';

	/**
	 * Category slug for WPForms abilities.
	 *
	 * @since 1.9.9
	 *
	 * @var string
	 */
	protected const CATEGORY_SLUG = 'wpforms-forms';

	/**
	 * Indicate if the current integration is allowed to load.
	 *
	 * @since 1.9.9
	 *
	 * @return bool
	 */
	public function allow_load(): bool {

		// Only load if the Abilities API is available (WordPress 6.9+).
		return function_exists( 'wp_register_ability' );
	}

	/**
	 * Load the integration.
	 *
	 * @since 1.9.9
	 */
	public function load(): void {

		$this->hooks();
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.9.9
	 */
	protected function hooks(): void {

		add_action( 'wp_abilities_api_categories_init', [ $this, 'register_category' ] );
		add_action( 'wp_abilities_api_init', [ $this, 'register_abilities' ] );
	}

	/**
	 * Register the WPForms ability category.
	 *
	 * @since 1.9.9
	 */
	public function register_category(): void {

		wp_register_ability_category(
			self::CATEGORY_SLUG,
			[
				'label'       => __( 'WPForms', 'wpforms-lite' ),
				'description' => __( 'Abilities for interacting with WPForms forms and entries.', 'wpforms-lite' ),
			]
		);
	}

	/**
	 * Register WPForms abilities.
	 *
	 * @since 1.9.9
	 */
	abstract public function register_abilities();

	/**
	 * Register common abilities shared between Lite and Pro.
	 *
	 * @since 1.9.9
	 */
	protected function register_common_abilities(): void {

		$this->register_list_forms_ability();
		$this->register_get_form_ability();
	}

	/**
	 * Register the list_forms ability.
	 *
	 * @since 1.9.9
	 */
	protected function register_list_forms_ability(): void {

		wp_register_ability(
			self::ABILITY_NAMESPACE . '/list-forms',
			[
				'label'               => __( 'List Forms', 'wpforms-lite' ),
				'description'         => __( 'List all available WPForms forms with their metadata.', 'wpforms-lite' ),
				'category'            => self::CATEGORY_SLUG,
				'execute_callback'    => [ $this, 'ability_list_forms' ],
				'permission_callback' => [ $this, 'check_view_forms_permission' ],
				'input_schema'        => [
					'type'       => 'object',
					'properties' => [
						'status' => [
							'description' => __( 'Filter forms by status.', 'wpforms-lite' ),
							'type'        => 'string',
							'enum'        => [ 'publish', 'draft', 'trash' ],
							'default'     => 'publish',
						],
						'limit'  => [
							'description' => __( 'Maximum number of forms to return.', 'wpforms-lite' ),
							'type'        => 'integer',
							'minimum'     => 1,
							'maximum'     => 100,
							'default'     => 20,
						],
						'offset' => [
							'description' => __( 'Number of forms to skip.', 'wpforms-lite' ),
							'type'        => 'integer',
							'minimum'     => 0,
							'default'     => 0,
						],
					],
				],
				'output_schema'       => [
					'type'       => 'object',
					'properties' => [
						'forms' => [
							'type'        => 'array',
							'description' => __( 'Array of form objects.', 'wpforms-lite' ),
							'items'       => [
								'type'       => 'object',
								'properties' => [
									'id'       => [ 'type' => 'integer' ],
									'title'    => [ 'type' => 'string' ],
									'status'   => [ 'type' => 'string' ],
									'created'  => [ 'type' => 'string' ],
									'modified' => [ 'type' => 'string' ],
								],
							],
						],
						'total' => [
							'type'        => 'integer',
							'description' => __( 'Total number of forms returned.', 'wpforms-lite' ),
						],
					],
				],
				'meta'                => [
					'annotations'  => [
						'readonly'    => true,
						'destructive' => false,
						'idempotent'  => true,
					],
					'show_in_rest' => true,
					'mcp'          => [
						'public' => true,
					],
				],
			]
		);
	}

	/**
	 * Register the get_form ability.
	 *
	 * @since 1.9.9
	 */
	protected function register_get_form_ability(): void {

		wp_register_ability(
			self::ABILITY_NAMESPACE . '/get-form',
			[
				'label'               => __( 'Get Form', 'wpforms-lite' ),
				'description'         => __( 'Get detailed information about a specific WPForms form including its fields.', 'wpforms-lite' ),
				'category'            => self::CATEGORY_SLUG,
				'execute_callback'    => [ $this, 'ability_get_form' ],
				'permission_callback' => [ $this, 'check_view_single_form_permission' ],
				'input_schema'        => [
					'type'       => 'object',
					'properties' => [
						'form_id'        => [
							'description' => __( 'The ID of the form to retrieve.', 'wpforms-lite' ),
							'type'        => 'integer',
							'minimum'     => 1,
						],
						'include_fields' => [
							'description' => __( 'Whether to include field configuration.', 'wpforms-lite' ),
							'type'        => 'boolean',
							'default'     => true,
						],
					],
					'required'   => [ 'form_id' ],
				],
				'output_schema'       => [
					'type'       => 'object',
					'properties' => [
						'id'       => [ 'type' => 'integer' ],
						'title'    => [ 'type' => 'string' ],
						'status'   => [ 'type' => 'string' ],
						'settings' => [ 'type' => 'object' ],
						'fields'   => [ 'type' => 'array' ],
					],
				],
				'meta'                => [
					'annotations'  => [
						'readonly'    => true,
						'destructive' => false,
						'idempotent'  => true,
					],
					'show_in_rest' => true,
					'mcp'          => [
						'public' => true,
					],
				],
			]
		);
	}

	/**
	 * Permission callback: Check if the user can view forms.
	 *
	 * @since 1.9.9
	 *
	 * @return bool|WP_Error
	 */
	public function check_view_forms_permission() {

		if ( ! wpforms_current_user_can( 'view_forms' ) ) {
			return new WP_Error(
				'wpforms_forbidden',
				__( 'You do not have permission to view forms.', 'wpforms-lite' ),
				[ 'status' => 403 ]
			);
		}

		return true;
	}

	/**
	 * Permission callback: Check if the user can view a specific form.
	 *
	 * @since 1.9.9
	 *
	 * @param mixed $input Input data containing form_id.
	 *
	 * @return bool|WP_Error
	 */
	public function check_view_single_form_permission( $input = null ) {

		$input   = $this->normalize_input( $input );
		$form_id = absint( $input['form_id'] ?? 0 );

		if ( ! $form_id || ! wpforms_current_user_can( 'view_form_single', $form_id ) ) {
			return new WP_Error(
				'wpforms_forbidden',
				__( 'You do not have permission to view this form.', 'wpforms-lite' ),
				[ 'status' => 403 ]
			);
		}

		return true;
	}

	/**
	 * Ability callback: List forms.
	 *
	 * @since 1.9.9
	 *
	 * @param mixed $input Input data.
	 *
	 * @return array
	 */
	public function ability_list_forms( $input = null ): array {

		$args = $this->normalize_input( $input );

		$form_handler = $this->get_form_handler();

		if ( is_wp_error( $form_handler ) ) {
			return [
				'forms' => [],
				'total' => 0,
			];
		}

		$limit  = absint( $args['limit'] ?? 20 );
		$offset = absint( $args['offset'] ?? 0 );
		$status = sanitize_text_field( $args['status'] ?? 'publish' );

		// Get total count efficiently using the cached WordPress function.
		$counts = wp_count_posts( 'wpforms' );
		$total  = $counts->{$status} ?? 0;

		// Get paginated forms with proper WordPress pagination.
		$query_args = [
			'post_status'    => $status,
			'posts_per_page' => $limit,
			'offset'         => $offset,
			'nopaging'       => false, // Override default to enable pagination.
			'order'          => 'DESC',
			'orderby'        => 'date',
		];

		$forms = $form_handler->get( '', $query_args );

		if ( empty( $forms ) ) {
			return [
				'forms' => [],
				'total' => $total,
			];
		}

		$result = [];

		foreach ( $forms as $form ) {
			$result[] = $this->format_form_summary( $form );
		}

		return [
			'forms' => $result,
			'total' => $total,
		];
	}

	/**
	 * Ability callback: Get single form.
	 *
	 * @since 1.9.9
	 *
	 * @param mixed $input Input data.
	 *
	 * @return array|WP_Error
	 */
	public function ability_get_form( $input = null ) {

		$args    = $this->normalize_input( $input );
		$form_id = absint( $args['form_id'] ?? 0 );

		if ( empty( $form_id ) ) {
			return new WP_Error(
				'wpforms_invalid_form_id',
				__( 'Invalid form ID.', 'wpforms-lite' ),
				[ 'status' => 400 ]
			);
		}

		$form_handler = $this->get_form_handler();

		if ( is_wp_error( $form_handler ) ) {
			return $form_handler;
		}

		$form = $form_handler->get( $form_id );

		if ( empty( $form ) ) {
			return new WP_Error(
				'wpforms_form_not_found',
				__( 'Form not found.', 'wpforms-lite' ),
				[ 'status' => 404 ]
			);
		}

		$include_fields = wp_validate_boolean( $args['include_fields'] ?? true );

		return $this->format_form_detail( $form, $include_fields );
	}

	/**
	 * Normalize input data to array format.
	 *
	 * @since 1.9.9
	 *
	 * @param mixed $input Input data (can be the array, object, or null).
	 *
	 * @return array
	 */
	protected function normalize_input( $input ): array {

		if ( is_array( $input ) ) {
			return $input;
		}

		if ( is_object( $input ) ) {
			return (array) $input;
		}

		return [];
	}

	/**
	 * Get the form handler and validate it.
	 *
	 * @since 1.9.9
	 *
	 * @return object|WP_Error Form handler object or WP_Error on failure.
	 */
	protected function get_form_handler() {

		$form_handler = wpforms()->obj( 'form' );

		if ( ! $form_handler ) {
			return new WP_Error(
				'wpforms_form_handler_error',
				__( 'Form handler not available.', 'wpforms-lite' ),
				[ 'status' => 500 ]
			);
		}

		return $form_handler;
	}

	/**
	 * Format form data for summary listing.
	 *
	 * @since 1.9.9
	 *
	 * @param WP_Post $form Form the `post` object.
	 *
	 * @return array
	 */
	protected function format_form_summary( WP_Post $form ): array {

		return [
			'id'       => $form->ID,
			'title'    => $form->post_title,
			'status'   => $form->post_status,
			'created'  => $form->post_date,
			'modified' => $form->post_modified,
			'author'   => absint( $form->post_author ),
		];
	}

	/**
	 * Format form data for the detailed view.
	 *
	 * @since 1.9.9
	 *
	 * @param WP_Post $form           Form `post` object.
	 * @param bool    $include_fields Whether to include fields.
	 *
	 * @return array
	 */
	protected function format_form_detail( WP_Post $form, bool $include_fields = true ): array {

		$form_handler = $this->get_form_handler();
		$form_data    = ! is_wp_error( $form_handler ) ? $form_handler->get( $form->ID, [ 'content_only' => true ] ) : [];

		// Ensure form_data is an array.
		if ( ! is_array( $form_data ) ) {
			$form_data = [];
		}

		$result = [
			'id'       => $form->ID,
			'title'    => $form->post_title,
			'status'   => $form->post_status,
			'created'  => $form->post_date,
			'modified' => $form->post_modified,
			'author'   => absint( $form->post_author ),
			'settings' => $this->get_safe_settings( $form_data ),
		];

		if ( $include_fields && ! empty( $form_data['fields'] ) ) {
			$result['fields'] = $this->format_fields( $form_data['fields'] );
		}

		return $result;
	}

	/**
	 * Get safe settings (without sensitive data).
	 *
	 * @since 1.9.9
	 *
	 * @param array $form_data Form data.
	 *
	 * @return array
	 */
	protected function get_safe_settings( array $form_data ): array {

		$settings = $form_data['settings'] ?? [];

		// Return only safe, non-sensitive settings.
		return [
			'form_title'  => $settings['form_title'] ?? '',
			'form_desc'   => $settings['form_desc'] ?? '',
			'submit_text' => $settings['submit_text'] ?? __( 'Submit', 'wpforms-lite' ),
			'ajax_submit' => ! empty( $settings['ajax_submit'] ),
			'honeypot'    => ! empty( $settings['honeypot'] ),
			'antispam'    => ! empty( $settings['antispam'] ),
		];
	}

	/**
	 * Format fields for output.
	 *
	 * @since 1.9.9
	 *
	 * @param array $fields Form fields.
	 *
	 * @return array
	 */
	protected function format_fields( array $fields ): array {

		$result = [];

		foreach ( $fields as $field_id => $field ) {
			$result[] = [
				'id'          => absint( $field_id ),
				'type'        => sanitize_text_field( $field['type'] ?? '' ),
				'label'       => sanitize_text_field( $field['label'] ?? '' ),
				'description' => sanitize_text_field( $field['description'] ?? '' ),
				'required'    => ! empty( $field['required'] ),
				'size'        => sanitize_text_field( $field['size'] ?? 'medium' ),
			];
		}

		return $result;
	}
}
