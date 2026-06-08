<?php

namespace WPForms\Integrations\Elementor;

use WP_Error;
use WP_REST_Request;
use WP_REST_Response; // phpcs:ignore WPForms.PHP.UseStatement.UnusedUseStatement
use WPForms\Frontend\CSSVars;

/**
 * Rest API for Elementor Modern widget.
 *
 * @since 1.9.6
 */
class RestApi {

	/**
	 * Route prefix.
	 *
	 * @since 1.9.6
	 *
	 * @var string
	 */
	public const ROUTE_NAMESPACE = '/wpforms/v1/';

	/**
	 * ThemesData class instance.
	 *
	 * @since 1.9.6
	 *
	 * @var CSSVars
	 */
	private $themes_data;

	/**
	 * Initialize class.
	 *
	 * @since 1.9.6
	 *
	 * @param Widget|mixed     $widget_obj  Widget object.
	 * @param ThemesData|mixed $themes_data ThemesData object.
	 */
	public function __construct( $widget_obj, $themes_data ) {

		if ( ! $widget_obj || ! $themes_data || ! wpforms_is_wpforms_rest() ) {
			return;
		}

		$this->themes_data = $themes_data;

		$this->hooks();
	}

	/**
	 * Hooks.
	 *
	 * @since 1.9.6
	 */
	private function hooks(): void {

		add_action( 'rest_api_init', [ $this, 'register_api_routes' ], 20 );
	}

	/**
	 * Register API routes for Elementor Modern widget.
	 *
	 * @since 1.9.6
	 */
	public function register_api_routes() {

		/**
		 * Register routes with WordPress.
		 *
		 * @see https://developer.wordpress.org/reference/functions/register_rest_route/
		 */
		register_rest_route(
			self::ROUTE_NAMESPACE,
			'/elementor/themes/',
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'get_themes' ],
				'permission_callback' => [ $this, 'permissions_check' ],
			]
		);

		register_rest_route(
			self::ROUTE_NAMESPACE,
			'/elementor/themes/custom/',
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
	 * @since 1.9.6
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
	 * Check if a user has admin permissions.
	 *
	 * @since 1.9.6
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
	 * Return themes as a protected WP_REST_Response object.
	 *
	 * @since 1.9.6
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_themes() {

		$custom_themes  = $this->themes_data->get_custom_themes();
		$wpforms_themes = $this->themes_data->get_wpforms_themes();

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
	 * @since 1.9.6
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function save_themes( WP_REST_Request $request ) {

		$custom_themes = (array) ( $request->get_param( 'customThemes' ) ?? [] );

		// Save custom themes data and return REST response.
		$result = $this->themes_data->update_custom_themes_file( $custom_themes );

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
