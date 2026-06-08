<?php
/**
 * Compact body template.
 *
 * This template can be overridden by copying it to yourtheme/wpforms/emails/compact-body.php.
 *
 * @since 1.8.5
 *
 * @var string $message Email message.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<table border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation">
	<tbody>
		<?php echo wp_kses_post( $message ); ?>
	</tbody>
</table>
