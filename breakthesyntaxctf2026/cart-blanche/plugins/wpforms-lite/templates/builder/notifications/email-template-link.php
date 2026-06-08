<?php
/**
 * Email Template modal link.
 *
 * This template will display a link to select a template for a notification.
 * It is used in the context of both WPForms Lite and Pro.
 *
 * @since 1.8.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<p class="note">
	<?php
	printf(
		/* translators: %1$s - Opening anchor tag, %2$s - Closing anchor tag. */
		esc_html__( 'Select a template to use for this notification or %1$sview templates%2$s.', 'wpforms-lite' ),
		'<a href="#" class="wpforms-all-email-template-modal">',
		'</a>'
	);
	?>
</p>

<?php
/* Omit closing PHP tag at the end of PHP files to avoid "headers already sent" issues. */
