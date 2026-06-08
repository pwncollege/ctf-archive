<?php
/**
 * No template payment HTML template.
 *
 * @since 1.8.2
 *
 * @var string $message An abort message to display.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="wpforms-admin-empty-state-container wpforms-admin-no-payments">

	<h2 class="waving-hand-emoji"><?php esc_html_e( 'Hi there!', 'wpforms-lite' ); ?></h2>

	<p><?php echo esc_html( $message ); ?></p>

	<img src="<?php echo esc_url( WPFORMS_PLUGIN_URL . 'assets/images/empty-states/payments/no-payments.svg' ); ?>" alt="" style="max-width: 314px;">

	<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpforms-payments' ) ); ?>" class="wpforms-btn wpforms-btn-lg wpforms-btn-orange">
		<?php esc_html_e( 'Back to All Payments', 'wpforms-lite' ); ?>
	</a>

</div>
