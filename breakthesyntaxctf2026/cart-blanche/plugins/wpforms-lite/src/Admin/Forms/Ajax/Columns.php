<?php

namespace WPForms\Admin\Forms\Ajax;

use WPForms\Admin\Forms\Table\Facades;

/**
 * Columns AJAX actions on Forms Overview list page.
 *
 * @since 1.8.6
 */
class Columns {

	/**
	 * Determine if the class is allowed to load.
	 *
	 * @since 1.8.6
	 *
	 * @return bool
	 */
	private function allow_load(): bool {

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$action = isset( $_REQUEST['action'] ) ? sanitize_key( wp_unslash( $_REQUEST['action'] ) ) : '';

		// Load only in the case of AJAX calls on Forms Overview page.
		return wpforms_is_admin_ajax() && strpos( $action, 'wpforms_admin_forms_overview_' ) === 0;
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

		add_action( 'wp_ajax_wpforms_admin_forms_overview_save_columns_order', [ $this, 'save_order' ] );
	}

	/**
	 * Save columns' order.
	 *
	 * @since 1.8.6
	 */
	public function save_order(): void {

		check_ajax_referer( 'wpforms-admin', 'nonce' );

		if ( ! wpforms_current_user_can( 'view_forms' ) ) {
			wp_send_json_error( esc_html__( 'You do not have permission to perform this action.', 'wpforms-lite' ) );
		}

		$data = $this->get_prepared_data();

		// Prepare the new columns' order.
		$columns = [];

		foreach ( $data['columns'] as $column ) {
			$columns[] = str_replace( '-foot', '', $column );
		}

		$result = Facades\Columns::sanitize_and_save_columns( $columns );

		if ( $result === false ) {
			wp_send_json_error( esc_html__( 'Cannot save columns order.', 'wpforms-lite' ) );
		}

		wp_send_json_success();
	}

	/**
	 * Get prepared data before perform ajax action.
	 *
	 * @since 1.8.6
	 *
	 * @return array
	 */
	private function get_prepared_data(): array {

		// Run a security check.
		if ( ! check_ajax_referer( 'wpforms-admin', 'nonce', false ) ) {
			wp_send_json_error( esc_html__( 'Most likely, your session expired. Please reload the page.', 'wpforms-lite' ) );
		}

		return [
			'columns' => ! empty( $_POST['columns'] ) ? map_deep( (array) wp_unslash( $_POST['columns'] ), 'sanitize_key' ) : [],
		];
	}
}
