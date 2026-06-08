<?php

namespace WPForms\Admin\Forms;

use WPForms\Admin\Notice;

/**
 * Bulk actions on All Forms page.
 *
 * @since 1.7.3
 */
class BulkActions {

	/**
	 * Allowed actions.
	 *
	 * @since 1.7.3
	 *
	 * @const array
	 */
	const ALLOWED_ACTIONS = [
		'trash',
		'restore',
		'delete',
		'duplicate',
		'empty_trash',
	];

	/**
	 * Forms ids.
	 *
	 * @since 1.7.3
	 *
	 * @var array
	 */
	private $ids;

	/**
	 * Current action.
	 *
	 * @since 1.7.3
	 *
	 * @var string
	 */
	private $action;

	/**
	 * Current view.
	 *
	 * @since 1.7.3
	 *
	 * @var string
	 */
	private $view;

	/**
	 * Determine if the class is allowed to load.
	 *
	 * @since 1.7.3
	 *
	 * @return bool
	 */
	private function allow_load() {

		// Load only on the `All Forms` admin page.
		return wpforms_is_admin_page( 'overview' );
	}

	/**
	 * Initialize class.
	 *
	 * @since 1.7.3
	 */
	public function init() {

		if ( ! $this->allow_load() ) {
			return;
		}

		$this->view = wpforms()->obj( 'forms_views' )->get_current_view();

		$this->hooks();
	}

	/**
	 * Hooks.
	 *
	 * @since 1.7.3
	 */
	private function hooks() {

		add_action( 'load-toplevel_page_wpforms-overview', [ $this, 'notices' ] );
		add_action( 'load-toplevel_page_wpforms-overview', [ $this, 'process' ] );
		add_filter( 'removable_query_args', [ $this, 'removable_query_args' ] );
	}

	/**
	 * Process the bulk actions.
	 *
	 * @since 1.7.3
	 */
	public function process() {

		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		$this->ids    = isset( $_GET['form_id'] ) ? array_map( 'absint', (array) $_GET['form_id'] ) : [];
		$this->action = isset( $_REQUEST['action'] ) ? sanitize_key( $_REQUEST['action'] ) : false;

		if ( $this->action === '-1' ) {
			$this->action = ! empty( $_REQUEST['action2'] ) ? sanitize_key( $_REQUEST['action2'] ) : false;
		}
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		if ( empty( $this->ids ) || empty( $this->action ) ) {
			return;
		}

		// Check exact action values.
		if ( ! in_array( $this->action, self::ALLOWED_ACTIONS, true ) ) {
			return;
		}

		if ( empty( $_GET['_wpnonce'] ) ) {
			return;
		}

		// Check the nonce.
		if (
			! wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ), 'bulk-forms' ) &&
			! wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ), 'wpforms_' . $this->action . '_form_nonce' )
		) {
			return;
		}

		// Finally, we can process the action.
		$this->process_action();
	}

	/**
	 * Process action.
	 *
	 * @since 1.7.3
	 *
	 * @uses process_action_trash
	 * @uses process_action_restore
	 * @uses process_action_delete
	 * @uses process_action_duplicate
	 * @uses process_action_empty_trash
	 */
	private function process_action() {

		$method = "process_action_{$this->action}";

		// Check that we have a method for this action.
		if ( ! method_exists( $this, $method ) ) {
			return;
		}

		if ( empty( $this->ids ) || ! is_array( $this->ids ) ) {
			return;
		}

		$query_args = [];

		if ( count( $this->ids ) === 1 ) {
			$query_args['type'] = wpforms_is_form_template( $this->ids[0] ) ? 'template' : 'form';
		}

		$result = [];

		foreach ( $this->ids as $id ) {
			$result[ $id ] = $this->$method( $id );
		}

		$count_result = count( array_keys( array_filter( $result ) ) );

		// Empty trash action returns count of deleted forms.
		if ( $method === 'process_action_empty_trash' ) {
			$count_result = $result[1] ?? 0;
		}

		$query_args[ rtrim( $this->action, 'e' ) . 'ed' ] = $count_result;

		// Unset get vars and perform redirect to avoid action reuse.
		wp_safe_redirect(
			add_query_arg(
				$query_args,
				remove_query_arg( [ 'action', 'action2', '_wpnonce', 'form_id', 'paged', '_wp_http_referer' ] )
			)
		);
		exit;
	}

	/**
	 * Trash the form.
	 *
	 * @since 1.7.3
	 *
	 * @param int $id Form ID to trash.
	 *
	 * @return bool
	 */
	private function process_action_trash( $id ) {

		return wpforms()->obj( 'form' )->update_status( $id, 'trash' );
	}

	/**
	 * Restore the form.
	 *
	 * @since 1.7.3
	 *
	 * @param int $id Form ID to restore from trash.
	 *
	 * @return bool
	 */
	private function process_action_restore( $id ) {

		return wpforms()->obj( 'form' )->update_status( $id, 'publish' );
	}

	/**
	 * Delete the form.
	 *
	 * @since 1.7.3
	 *
	 * @param int $id Form ID to delete.
	 *
	 * @return bool
	 */
	private function process_action_delete( $id ) {

		return wpforms()->obj( 'form' )->delete( $id );
	}

	/**
	 * Duplicate the form.
	 *
	 * @since 1.7.3
	 *
	 * @param int $id Form ID to duplicate.
	 *
	 * @return bool
	 */
	private function process_action_duplicate( $id ) {

		if ( ! wpforms_current_user_can( 'create_forms' ) ) {
			return false;
		}

		if ( ! wpforms_current_user_can( 'view_form_single', $id ) ) {
			return false;
		}

		return wpforms()->obj( 'form' )->duplicate( $id );
	}

	/**
	 * Empty trash.
	 *
	 * @since 1.7.3
	 *
	 * @param int $id Form ID. This parameter is not used in this method,
	 *                but we need to keep it here because all the `process_action_*` methods
	 *                should be called with the $id parameter.
	 *
	 * @return bool
	 */
	private function process_action_empty_trash( $id ) {

		// Empty trash is actually the "delete all forms in trash" action.
		// So, after the execution we should display the same notice as for the `delete` action.
		$this->action = 'delete';

		return wpforms()->obj( 'form' )->empty_trash();
	}

	/**
	 * Define bulk actions available for forms overview table.
	 *
	 * @since 1.7.3
	 *
	 * @return array
	 */
	public function get_dropdown_items() {

		$items = [];

		if ( wpforms_current_user_can( 'delete_forms' ) ) {
			if ( $this->view === 'trash' ) {
				$items = [
					'restore' => esc_html__( 'Restore', 'wpforms-lite' ),
					'delete'  => esc_html__( 'Delete Permanently', 'wpforms-lite' ),
				];
			} else {
				$items = [
					'trash' => esc_html__( 'Move to Trash', 'wpforms-lite' ),
				];
			}
		}

		// phpcs:disable WPForms.Comments.ParamTagHooks.InvalidParamTagsQuantity

		/**
		 * Filters the Bulk Actions dropdown items.
		 *
		 * @since 1.7.5
		 *
		 * @param array $items Dropdown items.
		 */
		$items = apply_filters( 'wpforms_admin_forms_bulk_actions_get_dropdown_items', $items );

		// phpcs:enable WPForms.Comments.ParamTagHooks.InvalidParamTagsQuantity

		if ( empty( $items ) ) {
			// We should have dummy item, otherwise, WP will hide the Bulk Actions Dropdown,
			// which is not good from a design point of view.
			return [
				'' => '&mdash;',
			];
		}

		return $items;
	}

	/**
	 * Admin notices.
	 *
	 * @since 1.7.3
	 */
	public function notices() {

		// phpcs:disable WordPress.Security.NonceVerification
		$results = [
			'trashed'    => ! empty( $_REQUEST['trashed'] ) ? sanitize_key( $_REQUEST['trashed'] ) : false,
			'restored'   => ! empty( $_REQUEST['restored'] ) ? sanitize_key( $_REQUEST['restored'] ) : false,
			'deleted'    => ! empty( $_REQUEST['deleted'] ) ? sanitize_key( $_REQUEST['deleted'] ) : false,
			'duplicated' => ! empty( $_REQUEST['duplicated'] ) ? sanitize_key( $_REQUEST['duplicated'] ) : false,
			'type'       => ! empty( $_REQUEST['type'] ) ? sanitize_key( $_REQUEST['type'] ) : 'form',
		];
		// phpcs:enable WordPress.Security.NonceVerification

		// Display notice in case of error.
		if ( in_array( 'error', $results, true ) ) {
			Notice::add(
				esc_html__( 'Security check failed. Please try again.', 'wpforms-lite' ),
				'error'
			);

			return;
		}

		$this->notices_success( $results );
	}

	/**
	 * Admin success notices.
	 *
	 * @since 1.7.3
	 *
	 * @param array $results Action results data.
	 */
	private function notices_success( array $results ) {

		$type = $results['type'] ?? '';

		if ( ! in_array( $type, [ 'form', 'template' ], true ) ) {
			return;
		}

		$method  = "get_notice_success_for_{$type}";
		$actions = [ 'trashed', 'restored', 'deleted', 'duplicated' ];

		foreach ( $actions as $action ) {
			$count = (int) $results[ $action ];

			if ( ! $count ) {
				continue;
			}

			$notice = $this->$method( $action, $count );

			if ( ! $notice ) {
				continue;
			}

			Notice::add( $notice, 'info' );
		}
	}

	/**
	 * Remove certain arguments from a query string that WordPress should always hide for users.
	 *
	 * @since 1.7.3
	 *
	 * @param array $removable_query_args An array of parameters to remove from the URL.
	 *
	 * @return array Extended/filtered array of parameters to remove from the URL.
	 */
	public function removable_query_args( $removable_query_args ) {

		$removable_query_args[] = 'trashed';
		$removable_query_args[] = 'restored';
		$removable_query_args[] = 'deleted';
		$removable_query_args[] = 'duplicated';

		return $removable_query_args;
	}

	/**
	 * Get notice success message for form.
	 *
	 * @since 1.9.2.3
	 *
	 * @param string $action Action type.
	 * @param int    $count  Count of forms.
	 *
	 * @return string
	 * @noinspection PhpUnusedPrivateMethodInspection
	 */
	private function get_notice_success_for_form( string $action, int $count ): string {

		switch ( $action ) {
			case 'restored':
				/* translators: %1$d - restored forms count. */
				$notice = _n( '%1$d form was successfully restored.', '%1$d forms were successfully restored.', $count, 'wpforms-lite' );
				break;

			case 'deleted':
				/* translators: %1$d - deleted forms count. */
				$notice = _n( '%1$d form was successfully permanently deleted.', '%1$d forms were successfully permanently deleted.', $count, 'wpforms-lite' );
				break;

			case 'duplicated':
				/* translators: %1$d - duplicated forms count. */
				$notice = _n( '%1$d form was successfully duplicated.', '%1$d forms were successfully duplicated.', $count, 'wpforms-lite' );
				break;

			case 'trashed':
				/* translators: %1$d - trashed forms count. */
				$notice = _n( '%1$d form was successfully moved to Trash.', '%1$d forms were successfully moved to Trash.', $count, 'wpforms-lite' );
				break;

			default:
				// phpcs:ignore WPForms.Formatting.EmptyLineBeforeReturn.AddEmptyLineBeforeReturnStatement
				return '';
		}

		return sprintf( $notice, $count );
	}

	/**
	 * Get notice success message for template.
	 *
	 * @since 1.9.2.3
	 *
	 * @param string $action Action type.
	 * @param int    $count  Count of forms.
	 *
	 * @return string
	 * @noinspection PhpUnusedPrivateMethodInspection
	 */
	private function get_notice_success_for_template( string $action, int $count ): string {

		switch ( $action ) {
			case 'restored':
				/* translators: %1$d - restored templates count. */
				$notice = _n( '%1$d template was successfully restored.', '%1$d templates were successfully restored.', $count, 'wpforms-lite' );
				break;

			case 'deleted':
				/* translators: %1$d - deleted templates count. */
				$notice = _n( '%1$d template was successfully permanently deleted.', '%1$d templates were successfully permanently deleted.', $count, 'wpforms-lite' );
				break;

			case 'duplicated':
				/* translators: %1$d - duplicated templates count. */
				$notice = _n( '%1$d template was successfully duplicated.', '%1$d templates were successfully duplicated.', $count, 'wpforms-lite' );
				break;

			case 'trashed':
				/* translators: %1$d - trashed templates count. */
				$notice = _n( '%1$d template was successfully moved to Trash.', '%1$d templates were successfully moved to Trash.', $count, 'wpforms-lite' );
				break;

			default:
				// phpcs:ignore WPForms.Formatting.EmptyLineBeforeReturn.AddEmptyLineBeforeReturnStatement
				return '';
		}

		return sprintf( $notice, $count );
	}
}
