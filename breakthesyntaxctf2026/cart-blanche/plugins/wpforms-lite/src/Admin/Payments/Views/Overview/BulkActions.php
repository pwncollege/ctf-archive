<?php

namespace WPForms\Admin\Payments\Views\Overview;

use WPForms\Admin\Notice;

/**
 * Bulk actions on the Payments Overview page.
 *
 * @since 1.8.2
 */
class BulkActions {

	/**
	 * Allowed actions.
	 *
	 * @since 1.8.2
	 *
	 * @const array
	 */
	const ALLOWED_ACTIONS = [
		'trash',
		'restore',
		'delete',
	];

	/**
	 * Payments ids.
	 *
	 * @since 1.8.2
	 *
	 * @var array
	 */
	private $ids;

	/**
	 * Current action.
	 *
	 * @since 1.8.2
	 *
	 * @var string
	 */
	private $action;

	/**
	 * Init.
	 *
	 * @since 1.8.2
	 */
	public function init() {

		$this->process();
	}

	/**
	 * Get the current action selected from the bulk actions dropdown.
	 *
	 * @since 1.8.2
	 *
	 * @return string|false The action name or False if no action was selected
	 */
	private function current_action() {

		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] !== '-1' ) {
			return sanitize_key( $_REQUEST['action'] );
		}

		if ( isset( $_REQUEST['action2'] ) && $_REQUEST['action2'] !== '-1' ) {
			return sanitize_key( $_REQUEST['action2'] );
		}
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		return false;
	}

	/**
	 * Process bulk actions.
	 *
	 * @since 1.8.2
	 */
	private function process() {

		if ( empty( $_GET['_wpnonce'] ) || empty( $_GET['payment_id'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ), 'bulk-wpforms_page_wpforms-payments' ) ) {
			wp_die( esc_html__( 'Your session expired. Please reload the page.', 'wpforms-lite' ) );
		}

		$this->ids    = array_map( 'absint', (array) $_GET['payment_id'] );
		$this->action = $this->current_action();

		if ( empty( $this->ids ) || ! $this->action || ! $this->is_allowed_action( $this->action ) ) {
			return;
		}

		$this->process_action();
	}

	/**
	 * Process a bulk action.
	 *
	 * @since 1.8.2
	 */
	private function process_action() {

		$method = "process_action_{$this->action}";

		// Check that we have a method for this action.
		if ( ! method_exists( $this, $method ) ) {
			return;
		}

		$processed = 0;

		foreach ( $this->ids as $id ) {
			$processed = $this->$method( $id ) ? $processed + 1 : $processed;
		}

		if ( ! $processed ) {
			return;
		}

		$this->display_bulk_action_message( $processed );
	}

	/**
	 * Trash the payment.
	 *
	 * @since 1.8.2
	 *
	 * @param int $id Payment ID to trash.
	 *
	 * @return bool
	 */
	private function process_action_trash( $id ) {

		return wpforms()->obj( 'payment' )->update( $id, [ 'is_published' => 0 ] );
	}

	/**
	 * Restore the payment.
	 *
	 * @since 1.8.2
	 *
	 * @param int $id Payment ID to restore from trash.
	 *
	 * @return bool
	 */
	private function process_action_restore( $id ) {

		return wpforms()->obj( 'payment' )->update( $id, [ 'is_published' => 1 ] );
	}

	/**
	 * Delete the payment.
	 *
	 * @since 1.8.2
	 *
	 * @param int $id Payment ID to delete.
	 *
	 * @return bool
	 */
	private function process_action_delete( $id ) {

		return wpforms()->obj( 'payment' )->delete( $id );
	}

	/**
	 * Display a bulk action message.
	 *
	 * @since 1.8.2
	 *
	 * @param int $count Count of processed payment IDs.
	 */
	private function display_bulk_action_message( $count ) {

		switch ( $this->action ) {
			case 'delete':
				/* translators: %d - number of deleted payments. */
				$message = sprintf( _n( '%d payment was successfully permanently deleted.', '%d payments were successfully permanently deleted.', $count, 'wpforms-lite' ), number_format_i18n( $count ) );
				break;

			case 'restore':
				/* translators: %d - number of restored payments. */
				$message = sprintf( _n( '%d payment was successfully restored.', '%d payments were successfully restored.', $count, 'wpforms-lite' ), number_format_i18n( $count ) );
				break;

			case 'trash':
				/* translators: %d - number of trashed payments. */
				$message = sprintf( _n( '%d payment was successfully moved to the Trash.', '%d payments were successfully moved to the Trash.', $count, 'wpforms-lite' ), number_format_i18n( $count ) );
				break;

			default:
				$message = '';
		}

		if ( empty( $message ) ) {
			return;
		}

		Notice::success( $message );
	}

	/**
	 * Determine whether the action is allowed.
	 *
	 * @since 1.8.2
	 *
	 * @param string $action Action name.
	 *
	 * @return bool
	 */
	private function is_allowed_action( $action ) {

		return in_array( $action, self::ALLOWED_ACTIONS, true );
	}
}
