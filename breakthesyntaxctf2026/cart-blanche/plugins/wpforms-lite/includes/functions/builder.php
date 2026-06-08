<?php
/**
 * Helpers functions for builder.
 *
 * @since 1.9.6.1
 */

/**
 * Outputs a button element to display the connection status for a given connection.
 *
 * @since 1.9.6.1
 *
 * @param string $connection_id The unique identifier for the connection.
 * @param string $name          The name attribute value to be used for the status input field.
 * @param bool   $is_active     Connection status, where true represents active and false represents inactive.
 */
function wpforms_connection_status_button( string $connection_id, string $name, bool $is_active ) {

	$label = $is_active ? __( 'Active', 'wpforms-lite' ) : __( 'Inactive', 'wpforms-lite' );
	$title = $is_active ? __( 'Deactivate', 'wpforms-lite' ) : __( 'Activate', 'wpforms-lite' );

	printf(
		'<span class="wpforms-builder-settings-block-status wpforms-badge wpforms-badge-sm wpforms-badge-%1$s wpforms-status-button" title="%5$s" data-active="%2$s" data-connection-id="%6$s">%3$s<i class="wpforms-status-label">%4$s</i></span>',
		sanitize_html_class( $is_active ? 'green' : 'silver' ),
		esc_attr( $is_active ),
		$is_active ? '<i class="fa fa-check"></i>' : '<i class="fa fa-times"></i>',
		esc_html( $label ),
		esc_attr( $title ),
		esc_attr( $connection_id )
	);

	printf( '<input type="hidden" name="%1$s" id="wpforms-connection-status-%2$s" value="%3$d">', esc_attr( $name ), esc_attr( $connection_id ), absint( $is_active ) );
}
