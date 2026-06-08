<?php
/**
 * No Payments HTML template.
 *
 * @since 1.8.2
 *
 * @var string $cta_url URL for the "Go To All Forms" CTA button.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="wpforms-admin-empty-state-container wpforms-admin-no-payments">
	<h2 class="waving-hand-emoji"><?php esc_html_e( 'Hi there!', 'wpforms-lite' ); ?></h2>
	<h4><?php esc_html_e( "It looks like you haven't received any payments yet.", 'wpforms-lite' ); ?></h4>
	<p><?php esc_html_e( "Your payment gateway has been configured and you're ready to go.", 'wpforms-lite' ); ?></p>
	<img src="<?php echo esc_url( WPFORMS_PLUGIN_URL . 'assets/images/empty-states/payments/no-payments.svg' ); ?>" alt="" style="max-width: 314px;">

	<?php if ( wpforms_current_user_can( 'view_forms' ) ) : ?>
	<a href="<?php echo esc_url( $cta_url ); ?>" class="wpforms-btn wpforms-btn-lg wpforms-btn-orange">
		<?php esc_html_e( 'Go To All Forms', 'wpforms-lite' ); ?>
	</a>
	<?php endif; ?>

	<p class="wpforms-admin-no-forms-footer">
	<?php
		printf(
			wp_kses( /* translators: %s - URL to the comprehensive guide. */
				__( 'Need some help? Check out our <a href="%s" rel="noopener noreferrer" target="_blank">comprehensive guide.</a>', 'wpforms-lite' ),
				[
					'a' => [
						'href'   => [],
						'rel'    => [],
						'target' => [],
					],
				]
			),
			esc_url(
				wpforms_utm_link(
					'https://wpforms.com/docs/using-stripe-with-wpforms-lite/',
					'Payments Dashboard',
					'Activated - Manage Payments Documentation'
				)
			)
		);
	?>
	</p>
</div>
