<?php
/**
 * Email Summary style template.
 *
 * This template can be overridden by copying it to yourtheme/wpforms/emails/summary-style.php.
 *
 * @since 1.5.4
 * @since 1.8.8 Removed `$header_image_max_width` parameter.
 *
 * @var string $email_background_color  Background color for the email.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require WPFORMS_PLUGIN_DIR . '/assets/css/emails/summary.min.css';

if ( ! empty( $email_background_color ) ) : ?>
	body, .body {
		background-color: <?php echo sanitize_hex_color( $email_background_color ); ?> !important;
	}
<?php endif; ?>
