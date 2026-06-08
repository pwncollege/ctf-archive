<?php
/**
 * Summary footer template (plain text).
 *
 * This template can be overridden by copying it to yourtheme/wpforms/emails/summary-footer-plain.php.
 *
 * @since 1.6.2.3
 *
 * @version 1.6.2.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

echo "\n---\n\n";
printf( /* translators: %s - link to the site. */
	esc_html__( 'This email was auto-generated and sent from %s.', 'wpforms-lite' ),
	esc_html( wp_specialchars_decode( get_bloginfo( 'name' ) ) )
);
echo "\n";
printf( /* translators: %1$s - link to Settings -> Misc tab in plugin, %2$s - link to the documentation. */
	esc_html__( 'If you want to disable these weekly emails, open %1$s, or read the guide %2$s.', 'wpforms-lite' ),
	esc_html( admin_url( 'admin.php?page=wpforms-settings&view=misc' ) ),
	'https://wpforms.com/docs/how-to-use-email-summaries/#disable-email-summaries'
);
