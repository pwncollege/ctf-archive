<?php
/**
 * Helper logging and debug functions.
 *
 * @since 1.8.0
 */

use WPForms\Logger\Log;

/**
 * Check whether the plugin works in a debug mode.
 *
 * @since 1.2.3
 *
 * @return bool
 */
function wpforms_debug(): bool {

	$debug = false;

	if ( ( defined( 'WPFORMS_DEBUG' ) && true === WPFORMS_DEBUG ) && is_super_admin() ) {
		$debug = true;
	}

	/**
	 * Filters wpforms_debug status.
	 *
	 * @since 1.2.3
	 *
	 * @param bool $debug WPForms debug status.
	 */
	return (bool) apply_filters( 'wpforms_debug', $debug );
}

/**
 * Helper function to display debug data.
 *
 * @since 1.0.0
 *
 * @param mixed $data    What to dump - can be any type.
 * @param bool  $do_echo Whether to print or return. The default is to print.
 *
 * @return string|void
 */
function wpforms_debug_data( $data, bool $do_echo = true ) {

	if ( ! wpforms_debug() ) {
		return;
	}

	if ( is_array( $data ) || is_object( $data ) ) {
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
		$data = print_r( $data, true );
	}

	$output = sprintf(
		'<style>
			.wpforms-debug {
				line-height: 0;
			}
			.wpforms-debug textarea {
				background: #f6f7f7 !important;
				margin: 20px 0 0 0;
				width: 100%%;
				height: 500px;
				font-size: 12px;
				font-family: Consolas, Menlo, Monaco, monospace;
				direction: ltr;
				unicode-bidi: embed;
				line-height: 1.4;
				padding: 10px;
				border-radius: 0;
				border-color: #c3c4c7;
				box-sizing: border-box;
			}
			.postbox .wpforms-debug {
				padding: 6px;
			}
			.postbox .wpforms-debug:not(:first-of-type) {
				padding-top: 0;
			}
			.postbox .wpforms-debug textarea {
				margin-top: 0 !important;
			}
		</style>
		<div class="wpforms-debug">
			<textarea readonly>=================== WPFORMS DEBUG ===================%s</textarea>
		</div>',
		"\n\n" . esc_html( $data )
	);

	/**
	 * Allow developers to determine whether the debug data should be displayed.
	 * Works only in debug mode (`WPFORMS_DEBUG` constant is `true`).
	 *
	 * @since 1.6.8
	 *
	 * @param bool $allow_display True by default.
	 */
	$allow_display = apply_filters( 'wpforms_debug_data_allow_display', true );

	if ( $do_echo && $allow_display ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $output;
	} else {
		return $output;
	}
}

/**
 * Log helper.
 *
 * @since 1.0.0
 *
 * @param string $title   Title of a log message.
 * @param mixed  $message Content of a log message.
 * @param array  $args    Expected keys: type, form_id, meta, parent, force.
 */
function wpforms_log( $title = '', $message = '', $args = [] ) { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

	// Skip if logs disabled in Tools -> Logs.
	if ( empty( $args['force'] ) && ! wpforms_setting( 'logs-enable' ) ) {
		return;
	}

	// Require log title.
	if ( empty( $title ) ) {
		return;
	}

	/**
	 * Compare error levels to determine if we should log.
	 * Current supported levels:
	 * - Conditional Logic (conditional_logic)
	 * - Entries (entry)
	 * - Errors (error)
	 * - Payments (payment)
	 * - Providers (provider)
	 * - Security (security)
	 * - Spam (spam)
	 * - Log (log)
	 */
	$types = ! empty( $args['type'] ) ? (array) $args['type'] : [ 'error' ];

	// Skip invalid logs types.
	$log_types = Log::get_log_types();

	foreach ( $types as $key => $type ) {
		if ( ! isset( $log_types[ $type ] ) ) {
			unset( $types[ $key ] );
		}
	}

	if ( empty( $types ) ) {
		return;
	}

	/**
	 * Filter log message.
	 *
	 * @since 1.8.2
	 *
	 * @param mixed  $message Log message.
	 * @param string $title   Log title.
	 * @param array  $args    Log arguments.
	 */
	$message = apply_filters( 'wpforms_log_message', $message, $title, $args );

	// Make arrays and objects look nice.
	if ( is_array( $message ) || is_object( $message ) ) {
		$message = '<pre>' . esc_html( print_r( $message, true ) ) . '</pre>'; // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
	}

	// Filter logs types from Tools -> Logs page.
	$logs_types = wpforms_setting( 'logs-types' );

	if ( $logs_types && empty( array_intersect( $logs_types, $types ) ) ) {
		return;
	}

	// Filter user roles from Tools -> Logs page.
	$current_user       = function_exists( 'wp_get_current_user' ) ? wp_get_current_user() : null;
	$current_user_id    = $current_user->ID ?? 0;
	$current_user_roles = $current_user->roles ?? [];
	$logs_user_roles    = wpforms_setting( 'logs-user-roles' );

	if ( $logs_user_roles && empty( array_intersect( $logs_user_roles, $current_user_roles ) ) ) {
		return;
	}

	// Filter logs users from Tools -> Logs page.
	$logs_users = wpforms_setting( 'logs-users' );

	if ( $logs_users && ! in_array( $current_user_id, $logs_users, true ) ) {
		return;
	}

	$log = wpforms()->obj( 'log' );

	if ( ! $log || ! method_exists( $log, 'add' ) ) {
		return;
	}

	// Create log entry.
	$log->add(
		$title,
		$message,
		$types,
		isset( $args['form_id'] ) ? absint( $args['form_id'] ) : 0,
		isset( $args['parent'] ) ? absint( $args['parent'] ) : 0,
		$current_user_id
	);
}

/**
 * Wrapper for set_time_limit to see if it is enabled.
 *
 * @since 1.6.4
 *
 * @param int $limit Time limit.
 */
function wpforms_set_time_limit( $limit = 0 ) {

	if ( function_exists( 'set_time_limit' ) && false === strpos( ini_get( 'disable_functions' ), 'set_time_limit' ) ) {
		@set_time_limit( $limit ); // @codingStandardsIgnoreLine
	}
}
