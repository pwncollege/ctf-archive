<?php
/**
 * Display Email settings heading.
 *
 * @since 1.8.5
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

?>
<h4>
	<?php esc_html_e( 'Email', 'wpforms-lite' ); ?>
</h4>
<p>
	<?php esc_html_e( 'Customize your email template and sending preferences.', 'wpforms-lite' ); ?>
</p>

<?php

// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
echo wpforms()->obj( 'education_smtp_notice' )->get_template();

/* Omit closing PHP tag at the end of PHP files to avoid "headers already sent" issues. */
