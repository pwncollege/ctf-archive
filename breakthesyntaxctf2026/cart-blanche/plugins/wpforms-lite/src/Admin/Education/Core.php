<?php

namespace WPForms\Admin\Education;

/**
 * Education core.
 *
 * @since 1.6.6
 */
class Core {

	use StringsTrait;

	/**
	 * Indicate if Education core is allowed to load.
	 *
	 * @since 1.6.6
	 *
	 * @return bool
	 */
	public function allow_load(): bool {

		return wp_doing_ajax() || wpforms_is_admin_page() || wpforms_is_admin_page( 'builder' );
	}

	/**
	 * Init.
	 *
	 * @since 1.6.6
	 */
	public function init() {

		// Only proceed if allowed.
		if ( ! $this->allow_load() ) {
			return;
		}

		$this->hooks();
	}

	/**
	 * Hooks.
	 *
	 * @since 1.6.6
	 */
	protected function hooks() {

		if ( wp_doing_ajax() ) {
			add_action( 'wp_ajax_wpforms_education_dismiss', [ $this, 'ajax_dismiss' ] );

			return;
		}

		add_action( 'admin_enqueue_scripts', [ $this, 'enqueues' ] );
	}

	/**
	 * Load enqueues.
	 *
	 * @since 1.6.6
	 */
	public function enqueues() {

		$min = wpforms_get_min_suffix();

		wp_enqueue_script(
			'wpforms-admin-education-core',
			WPFORMS_PLUGIN_URL . "assets/js/admin/education/core{$min}.js",
			[ 'jquery', 'jquery-confirm' ],
			WPFORMS_VERSION,
			true
		);

		wp_localize_script(
			'wpforms-admin-education-core',
			'wpforms_education',
			$this->get_js_strings()
		);
	}

	/**
	 * Ajax handler for the education dismisses buttons.
	 *
	 * @since 1.6.6
	 */
	public function ajax_dismiss() {

		// Run a security check.
		check_ajax_referer( 'wpforms-education', 'nonce' );

		// Section is the identifier of the education feature.
		// For example, in Builder/DidYouKnow feature used 'builder-did-you-know-notifications'
		// and 'builder-did-you-know-confirmations'.
		$section = ! empty( $_POST['section'] ) ? sanitize_key( $_POST['section'] ) : '';

		if ( empty( $section ) ) {
			wp_send_json_error(
				[ 'error' => esc_html__( 'Please specify a section.', 'wpforms-lite' ) ]
			);
		}

		// Check for permissions.
		if ( ! $this->current_user_can() ) {
			wp_send_json_error(
				[ 'error' => esc_html__( 'You do not have permission to perform this action.', 'wpforms-lite' ) ]
			);
		}

		$user_id   = get_current_user_id();
		$dismissed = get_user_meta( $user_id, 'wpforms_dismissed', true );

		if ( empty( $dismissed ) ) {
			$dismissed = [];
		}

		$dismissed[ 'edu-' . $section ] = time();

		update_user_meta( $user_id, 'wpforms_dismissed', $dismissed );
		wp_send_json_success();
	}

	/**
	 * Whether the current user can perform an action.
	 *
	 * @since 1.8.0
	 *
	 * @return bool
	 */
	private function current_user_can(): bool {

		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$page = ! empty( $_POST['page'] ) ? sanitize_key( $_POST['page'] ) : '';

		// key is the same as $current_screen->id and the JS global 'pagenow', value - capability name(s).
		$caps = [
			'toplevel_page_wpforms-overview' => [ 'view_forms' ],
			'wpforms_page_wpforms-builder'   => [ 'edit_forms' ],
			'wpforms_page_wpforms-entries'   => [ 'view_entries' ],
		];

		return isset( $caps[ $page ] ) ? wpforms_current_user_can( $caps[ $page ] ) : wpforms_current_user_can();
	}
}
