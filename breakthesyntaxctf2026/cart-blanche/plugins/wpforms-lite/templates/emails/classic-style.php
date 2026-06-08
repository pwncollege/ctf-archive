<?php
/**
 * Classic style template.
 *
 * This template can be overridden by copying it to yourtheme/wpforms/emails/classic-style.php.
 *
 * @since 1.8.5
 *
 * @var string $email_background_color  Background color for the email.
 * @var string $email_body_color        Background color for the email content body.
 * @var string $email_text_color        Text color for the email content.
 * @var string $email_links_color       Color for links in the email content.
 * @var string $email_typography        Preferred typography font-family for email content.
 * @var string $header_image_max_width  Maximum width for the header image.
 * @var string $header_image_max_height Maximum height for the header image.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require WPFORMS_PLUGIN_DIR . '/assets/css/emails/classic.min.css';

// Reuse border-color.
$border_color = wpforms_generate_contrasting_color( $email_text_color, 86, 72 );

?>

body, .body {
	background-color: <?php echo sanitize_hex_color( $email_background_color ); ?>;
}

.wrapper-inner {
	background-color: <?php echo sanitize_hex_color( $email_body_color ); ?>;
	border: 1px solid <?php echo sanitize_hex_color( wpforms_generate_contrasting_color( $email_text_color, 72, 63 ) ); ?>;
}

body, table.body, h1, h2, h3, h4, h5, h6, p, td, th, a {
	color: <?php echo sanitize_hex_color( $email_text_color ); ?>;
	font-family: <?php echo esc_attr( $email_typography ); ?>;
}

a, a:visited,
a:hover, a:active,
h1 a, h1 a:visited,
h2 a, h2 a:visited,
h3 a, h3 a:visited,
h4 a, h4 a:visited,
h5 a, h5 a:visited,
h6 a, h6 a:visited {
	color: <?php echo sanitize_hex_color( $email_links_color ); ?>;
}

.button-link {
	background-color: <?php echo sanitize_hex_color( $email_links_color ); ?>;
	border: 1px solid <?php echo sanitize_hex_color( $email_links_color ); ?>;
	color: <?php echo sanitize_hex_color( $email_body_color ); ?>;
}

.content .field-value {
	border-bottom: 1px solid <?php echo sanitize_hex_color( $border_color ); ?>;
}

.footer, .footer a {
	color: <?php echo sanitize_hex_color( wpforms_generate_contrasting_color( $email_text_color, 50, 45 ) ); ?>;
}

table.wpforms-order-summary-preview {
	border: 1px solid <?php echo sanitize_hex_color( $border_color ); ?>;
}

table.wpforms-order-summary-preview td {
	border-top: 1px solid <?php echo sanitize_hex_color( $border_color ); ?>;
}

<?php if ( ! empty( $header_image_max_width ) && ! empty( $header_image_max_height ) ) : ?>
.header-image {
	max-width: <?php echo esc_attr( $header_image_max_width ); ?>px;
}
.header-image img {
	max-height: <?php echo esc_attr( $header_image_max_height ); ?>px;
}
<?php endif; ?>
