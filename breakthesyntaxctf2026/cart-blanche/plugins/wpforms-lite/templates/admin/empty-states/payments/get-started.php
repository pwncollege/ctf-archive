<?php
/**
 * Get Started HTML template.
 *
 * @since 1.8.2
 *
 * @var string $message An abort message to display.
 * @var string $version Determine whether is pro or lite version.
 * @var string $cta_url URL for the "Get Started" CTA button.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$allowed_html = [
	'a'      => [
		'href'   => [],
		'rel'    => [],
		'target' => [],
	],
	'strong' => [],
];

?>
<div class="wpforms-admin-empty-state-container wpforms-admin-no-payments">
	<h2 class="waving-hand-emoji"><?php esc_html_e( 'Hi there!', 'wpforms-lite' ); ?></h2>
	<h4><?php esc_html_e( 'Ready to start collecting payments from your customers?', 'wpforms-lite' ); ?></h4>
	<p><?php echo wp_kses( $message, $allowed_html ); ?></p>
	<img src="<?php echo esc_url( WPFORMS_PLUGIN_URL . "assets/images/empty-states/payments/get-started-$version.svg" ); ?>" alt="">

	<?php if ( wpforms_current_user_can( 'create_forms' ) ) : ?>
	<a href="<?php echo esc_url( $cta_url ); ?>" class="wpforms-btn wpforms-btn-lg wpforms-btn-orange">
		<?php esc_html_e( 'Get Started', 'wpforms-lite' ); ?>
	</a>
	<?php endif; ?>

	<p class="wpforms-admin-no-forms-footer">
	<?php
		printf(
			wp_kses( /* translators: %s - URL to the comprehensive guide. */
				__( 'Need some help? Check out our <a href="%s" rel="noopener noreferrer" target="_blank">comprehensive guide.</a>', 'wpforms-lite' ),
				$allowed_html
			),
			esc_url(
				wpforms_utm_link(
					'https://wpforms.com/docs/using-stripe-with-wpforms-lite/',
					'Payments Dashboard',
					'Splash - Manage Payments Documentation'
				)
			)
		);
	?>
	</p>
</div>
