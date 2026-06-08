<?php

namespace WPForms\Admin\Builder\Ajax;

/**
 * Form Builder Panel Loader AJAX actions.
 *
 * @since 1.8.6
 */
class PanelLoader {

	/**
	 * Determine if the class is allowed to load.
	 *
	 * @since 1.8.6
	 *
	 * @return bool
	 */
	private function allow_load(): bool {

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$action = isset( $_REQUEST['action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) : '';

		// Load only in the case of AJAX calls form the Form Builder.
		return wpforms_is_admin_ajax() && strpos( $action, 'wpforms_builder_' ) === 0;
	}

	/**
	 * Initialize class.
	 *
	 * @since 1.8.6
	 */
	public function init(): void {

		if ( ! $this->allow_load() ) {
			return;
		}

		$this->hooks();
	}

	/**
	 * Hooks.
	 *
	 * @since 1.8.6
	 */
	private function hooks(): void {

		add_action( 'wp_ajax_wpforms_builder_load_panel', [ $this, 'load_panel_content' ] );
	}

	/**
	 * Save tags.
	 *
	 * @since 1.8.6
	 */
	public function load_panel_content(): void {

		check_ajax_referer( 'wpforms-builder', 'nonce' );

		$form_id = absint( filter_input( INPUT_POST, 'form_id', FILTER_SANITIZE_NUMBER_INT ) );

		if ( ! wpforms_current_user_can( 'edit_forms', $form_id ) ) {
			wp_send_json_error( esc_html__( 'You do not have permission to perform this action.', 'wpforms-lite' ) );
		}

		$data        = $this->get_prepared_data( 'load_panel' );
		$panel       = $data['panel'] ?? '';
		$panel_class = '\WPForms_Builder_Panel_' . ucfirst( $panel );
		$panel_obj   = $this->get_panel_obj( $panel_class, $panel );

		ob_start();
		$panel_obj->panel_output( [], $panel );

		$panel_content = ob_get_clean();

		wp_send_json_success( $panel_content );
	}

	/**
	 * Get panel object.
	 *
	 * @since 1.9.4
	 *
	 * @param string $panel_class Panel class name.
	 * @param string $panel       Panel name.
	 *
	 * @return object
	 */
	private function get_panel_obj( string $panel_class, string $panel ) {

		if ( ! class_exists( $panel_class ) ) {
			// Load panel base class.
			require_once WPFORMS_PLUGIN_DIR . 'includes/admin/builder/panels/class-base.php';

			$file     = WPFORMS_PLUGIN_DIR . "includes/admin/builder/panels/class-{$panel}.php";
			$file_pro = WPFORMS_PLUGIN_DIR . "pro/includes/admin/builder/panels/class-{$panel}.php";

			if ( file_exists( $file_pro ) && wpforms()->is_pro() ) {
				require_once $file_pro;
			} elseif ( file_exists( $file ) ) {
				require_once $file;
			}
		}

		$panel_obj = $panel_class::instance();

		if ( ! method_exists( $panel_obj, 'panel_content' ) ) {
			wp_send_json_error( esc_html__( 'Invalid panel.', 'wpforms-lite' ) );
		}

		return $panel_obj;
	}

	/**
	 * Get prepared data before perform ajax action.
	 *
	 * @since 1.8.6
	 *
	 * @param string $action Action: `save` OR `delete`.
	 *
	 * @return array
	 * @noinspection PhpSameParameterValueInspection
	 */
	private function get_prepared_data( string $action ): array {

		// Run a security check.
		if ( ! check_ajax_referer( 'wpforms-builder', 'nonce', false ) ) {
			wp_send_json_error( esc_html__( 'Most likely, your session expired. Please reload the page.', 'wpforms-lite' ) );
		}

		// Check for permissions.
		if ( ! wpforms_current_user_can( 'edit_forms' ) ) {
			wp_send_json_error( esc_html__( 'You are not allowed to perform this action.', 'wpforms-lite' ) );
		}

		$data = [];

		if ( $action === 'load_panel' ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$data['panel'] = ! empty( $_POST['panel'] ) ? sanitize_key( $_POST['panel'] ) : '';
		}

		return $data;
	}
}
