<?php

namespace WPForms\Admin\Payments\Views\Overview\Traits;

use WPForms\Admin\Payments\Views\Overview\Coupon;
use WPForms\Admin\Payments\Views\Overview\Search;
use WPForms\Db\Payments\ValueValidator;

/**
 * This file is part of the Table class and contains methods responsible for
 * displaying notices on the Payments Overview page.
 *
 * @since 1.8.4
 */
trait ResetNotices {

	/**
	 * Show reset filter box.
	 *
	 * @since 1.8.4
	 */
	private function show_reset_filter() {

		$applied_filters = [
			$this->get_search_reset_filter(),
			$this->get_status_reset_filter(),
			$this->get_coupon_reset_filter(),
			$this->get_form_reset_filter(),
			$this->get_type_reset_filter(),
			$this->get_gateway_reset_filter(),
			$this->get_subscription_status_reset_filter(),
		];

		$applied_filters = array_filter( $applied_filters );

		// Let's not show the reset filter notice if there are no applied filters.
		if ( empty( $applied_filters ) ) {
			return;
		}

		// Output the reset filter notice.
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo wpforms_render(
			'admin/payments/reset-filter-notice',
			[
				'total'           => $this->get_valid_status_count_from_request(),
				'applied_filters' => $applied_filters,
			],
			true
		);
	}

	/**
	 * Show search reset filter.
	 *
	 * @since 1.8.4
	 *
	 * @return array
	 */
	private function get_search_reset_filter() {

		// Do not show the reset filter notice on the search results page.
		if ( ! Search::is_search() ) {
			return [];
		}

		$search_where = $this->get_search_where( $this->get_search_where_key() );
		$search_mode  = $this->get_search_mode( $this->get_search_mode_key() );

		return [
			'reset_url' => remove_query_arg( [ 's', 'search_where', 'search_mode', 'paged' ] ),
			'results'   => sprintf(
				' %s <em>%s</em> %s "<em>%s</em>"',
				__( 'where', 'wpforms-lite' ),
				esc_html( $search_where ),
				esc_html( $search_mode ),
				// It's important to escape the search term here for security.
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				esc_html( isset( $_GET['s'] ) ? wp_unslash( $_GET['s'] ) : '' )
			),
		];
	}

	/**
	 * Show status reset filter.
	 *
	 * @since 1.8.4
	 *
	 * @return array
	 */
	private function get_status_reset_filter() {

		// Do not show the reset filter notice on the status results page.
		// phpcs:disable WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		if ( empty( $this->get_valid_status_from_request() ) || $this->is_trash_view() ) {
			return [];
		}

		$statuses = ValueValidator::get_allowed_one_time_statuses();

		// Leave early if the status is not found.
		if ( ! isset( $statuses[ $this->get_valid_status_from_request() ] ) ) {
			return [];
		}

		return [
			'reset_url' => remove_query_arg( [ 'status' ] ),
			'results'   => sprintf(
				' %s "<em>%s</em>"',
				__( 'with the status', 'wpforms-lite' ),
				$statuses[ $this->get_valid_status_from_request() ]
			),
		];
	}

	/**
	 * Show coupon reset filter.
	 *
	 * @since 1.8.4
	 *
	 * @return array
	 */
	private function get_coupon_reset_filter() {

		// Do not show the reset filter notice on the coupon results page.
		if ( ! Coupon::is_coupon() ) {
			return [];
		}

		// Get the payment meta with the specified coupon ID.
		$payment_meta = wpforms()->obj( 'payment_meta' )->get_all_by_meta(
			'coupon_id',
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated, WordPress.Security.NonceVerification.Recommended
			absint( $_GET['coupon_id'] )
		);

		// If the coupon info is empty, exit the function.
		if ( empty( $payment_meta['coupon_info'] ) ) {
			return [];
		}

		return [
			'reset_url' => remove_query_arg( [ 'coupon_id', 'paged' ] ),
			'results'   => sprintf(
				' %s "<em>%s</em>"',
				__( 'with the coupon', 'wpforms-lite' ),
				$this->get_coupon_name_by_info( $payment_meta['coupon_info']->value )
			),
		];
	}

	/**
	 * Show form reset filter.
	 *
	 * @since 1.8.4
	 *
	 * @return array
	 */
	private function get_form_reset_filter() {

		// Do not show the reset filter notice on the form results page.
		// phpcs:disable WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		if ( empty( $_GET['form_id'] ) ) {
			return [];
		}

		// Retrieve the form with the specified ID.
		$form = wpforms()->obj( 'form' )->get( absint( $_GET['form_id'] ) );
		// phpcs:enable WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		// If the form is not found or not published, exit the function.
		if ( ! $form || $form->post_status !== 'publish' ) {
			return [];
		}

		return [
			'reset_url' => remove_query_arg( [ 'form_id', 'paged' ] ),
			'results'   => sprintf(
				' %s "<em>%s</em>"',
				__( 'with the form titled', 'wpforms-lite' ),
				! empty( $form->post_title ) ? $form->post_title : $form->post_name
			),
		];
	}

	/**
	 * Show type reset filter.
	 *
	 * @since 1.8.4
	 *
	 * @return array
	 */
	private function get_type_reset_filter() {

		// Do not show the reset filter notice on the type results page.
		// phpcs:disable WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		if ( empty( $_GET['type'] ) ) {
			return [];
		}

		$allowed_types = ValueValidator::get_allowed_types();
		$type          = explode( '|', sanitize_text_field( wp_unslash( $_GET['type'] ) ) );
		// phpcs:enable WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		return [
			'reset_url' => remove_query_arg( [ 'type', 'paged' ] ),
			'results'   => sprintf(
				' %s "<em>%s</em>"',
				_n( 'with the type', 'with the types', count( $type ), 'wpforms-lite' ),
				implode( ', ', array_intersect_key( $allowed_types, array_flip( $type ) ) )
			),
		];
	}

	/**
	 * Show gateway reset filter.
	 *
	 * @since 1.8.4
	 *
	 * @return array
	 */
	private function get_gateway_reset_filter() {

		// Do not show the reset filter notice on the gateway results page.
		// phpcs:disable WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		if ( empty( $_GET['gateway'] ) ) {
			return [];
		}

		$allowed_gateways = ValueValidator::get_allowed_gateways();
		$gateway          = explode( '|', sanitize_text_field( wp_unslash( $_GET['gateway'] ) ) );
		// phpcs:enable WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		return [
			'reset_url' => remove_query_arg( [ 'gateway', 'paged' ] ),
			'results'   => sprintf(
				' %s "<em>%s</em>"',
				_n( 'with the gateway', 'with the gateways', count( $gateway ), 'wpforms-lite' ),
				implode( ', ', array_intersect_key( $allowed_gateways, array_flip( $gateway ) ) )
			),
		];
	}

	/**
	 * Show subscription status reset filter.
	 *
	 * @since 1.8.4
	 *
	 * @return array
	 */
	private function get_subscription_status_reset_filter() {

		// Do not show the reset filter notice on the subscription status results page.
		// phpcs:disable WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		if ( empty( $_GET['subscription_status'] ) ) {
			return [];
		}

		$allowed_subscription_statuses = ValueValidator::get_allowed_subscription_statuses();
		$subscription_status           = explode( '|', sanitize_text_field( wp_unslash( $_GET['subscription_status'] ) ) );
		// phpcs:enable WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		return [
			'reset_url' => remove_query_arg( [ 'subscription_status', 'paged' ] ),
			'results'   => sprintf(
				' %s "<em>%s</em>"',
				_n( 'with the subscription status', 'with the subscription statuses', count( $subscription_status ), 'wpforms-lite' ),
				implode( ', ', array_intersect_key( $allowed_subscription_statuses, array_flip( $subscription_status ) ) )
			),
		];
	}
}
