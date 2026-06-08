<?php

namespace WPForms\Integrations\Gutenberg;

use WP_Error;
use WP_REST_Request; // phpcs:ignore WPForms.PHP.UseStatement.UnusedUseStatement
use WP_REST_Response; // phpcs:ignore WPForms.PHP.UseStatement.UnusedUseStatement

/**
 * Rest API for Gutenberg block.
 *
 * @since 1.8.8
 */
class RestApi {

	/**
	 * Route prefix.
	 *
	 * @since 1.8.8
	 *
	 * @var string
	 */
	const ROUTE_NAMESPACE = '/wpforms/v1/';

	/**
	 * FormSelector class instance.
	 *
	 * @since 1.8.8
	 *
	 * @var FormSelector
	 */
	private $form_selector_obj;

	/**
	 * ThemesData class instance.
	 *
	 * @since 1.8.8
	 *
	 * @var ThemesData
	 */
	private $themes_data_obj;

	/**
	 * Initialize class.
	 *
	 * @since 1.8.8
	 *
	 * @param FormSelector|mixed $form_selector_obj FormSelector object.
	 * @param ThemesData|mixed   $themes_data_obj   ThemesData object.
	 */
	public function __construct( $form_selector_obj, $themes_data_obj ) {

		if ( ! $form_selector_obj || ! $themes_data_obj || ! wpforms_is_wpforms_rest() ) {
			return;
		}

		$this->form_selector_obj = $form_selector_obj;
		$this->themes_data_obj   = $themes_data_obj;

		$this->hooks();
	}

	/**
	 * Hooks.
	 *
	 * @since 1.8.8
	 */
	private function hooks() {

		add_action( 'rest_api_init', [ $this, 'register_api_routes' ], 20 );
	}

	/**
	 * Register API routes for Gutenberg block.
	 *
	 * @since 1.8.8
	 */
	public function register_api_routes() {

		/**
		 * Register routes with WordPress.
		 *
		 * @see https://developer.wordpress.org/reference/functions/register_rest_route/
		 */
		register_rest_route(
			self::ROUTE_NAMESPACE,
			'/forms/',
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'get_forms' ],
				'permission_callback' => [ $this, 'forms_permissions_check' ],
			]
		);

		register_rest_route(
			self::ROUTE_NAMESPACE,
			'/themes/',
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'get_themes' ],
				'permission_callback' => [ $this, 'permissions_check' ],
			]
		);

		register_rest_route(
			self::ROUTE_NAMESPACE,
			'/themes/custom/',
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'save_themes' ],
				'permission_callback' => [ $this, 'admin_permissions_check' ],
			]
		);
	}

	/**
	 * Check if a user has permission to access private data.
	 *
	 * @since 1.8.8
	 *
	 * @see https://developer.wordpress.org/rest-api/extending-the-rest-api/routes-and-endpoints/#permissions-callback
	 *
	 * @return true|WP_Error True if a user has permission.
	 */
	public function permissions_check() {

		// Restrict endpoint to only users who have the edit_posts capability.
		if ( ! current_user_can( 'edit_posts' ) ) {
			return new WP_Error( 'rest_forbidden', esc_html__( 'This route is private.', 'wpforms-lite' ), [ 'status' => 401 ] );
		}

		return true;
	}

	/**
	 * Check if a user has permission to access forms data.
	 *
	 * @since 1.9.9.4
	 *
	 * @return true|WP_Error True if a user has permission.
	 */
	public function forms_permissions_check() {

		// Restrict endpoint to only users who have WPForms capabilities.
		if ( ! wpforms_current_user_can() ) {
			return new WP_Error( 'rest_forbidden', esc_html__( 'This route is private.', 'wpforms-lite' ), [ 'status' => 401 ] );
		}

		return true;
	}

	/**
	 * Check if a user has admin permissions.
	 *
	 * @since 1.9.2.3
	 *
	 * @return true|WP_Error True if a user has permission.
	 */
	public function admin_permissions_check() {

		// Restrict endpoint to only users who have the manage_options capability.
		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error( 'rest_forbidden', esc_html__( 'This route is accessible only to administrators.', 'wpforms-lite' ), [ 'status' => 401 ] );
		}

		return true;
	}

	/**
	 * Return form list protected WP_REST_Response object.
	 *
	 * @since 1.8.8
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_forms() {

		return rest_ensure_response( $this->form_selector_obj->get_form_list() );
	}

	/**
	 * Return themes as protected WP_REST_Response object.
	 *
	 * @since 1.8.8
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_themes() {

		$custom_themes  = $this->themes_data_obj->get_custom_themes();
		$wpforms_themes = $this->themes_data_obj->get_wpforms_themes();

		return rest_ensure_response(
			[
				'custom'  => ! empty( $custom_themes ) ? $custom_themes : null,
				'wpforms' => ! empty( $wpforms_themes ) ? $wpforms_themes : null,
			]
		);
	}

	/**
	 * Save custom themes.
	 *
	 * @since 1.8.8
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function save_themes( WP_REST_Request $request ) {

		// Determine custom themes file path.
		$themes_file = $this->themes_data_obj->get_custom_themes_file_path();

		// In the case of error.
		if ( ! $themes_file ) {
			return rest_ensure_response(
				[
					'result' => false,
					'error'  => esc_html__( 'Can\'t create themes storage file.', 'wpforms-lite' ),
				]
			);
		}

		$custom_themes = (array) ( $request->get_param( 'customThemes' ) ?? [] );

		// Save custom themes data and return REST response.
		$result = $this->themes_data_obj->update_custom_themes_file( $custom_themes );

		if ( ! $result ) {
			return rest_ensure_response(
				[
					'result' => false,
					'error'  => esc_html__( 'Can\'t save theme data.', 'wpforms-lite' ),
				]
			);
		}

		return rest_ensure_response( [ 'result' => true ] );
	}
}
