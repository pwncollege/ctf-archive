<?php
/**
 * WPForms What's New modal template.
 *
 * @since 1.8.7
 *
 * @var array $header Header data.
 * @var array $footer Footer data.
 * @var array $blocks Blocks data.
 * @var array $license License type.
 * @var string $update_url Update URL.
 * @var bool $display_notice Whether to display the notice.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<script type="text/html" id="tmpl-wpforms-splash-modal-content">
	<div id="wpforms-splash-modal">
		<?php
		//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo wpforms_render( 'admin/splash/header', $header, true );
		?>
		<?php if ( $display_notice ) : ?>
			<div class="wpforms-splash-notice">
				<p>
					<?php
					printf(
						'<a href="%1$s">%2$s</a> — %3$s',
						esc_url( $update_url ),
						esc_html__( 'Update WPForms', 'wpforms-lite' ),
						esc_html__( 'Awesome new features are waiting for you!', 'wpforms-lite' )
					);
					?>
				</p>
			</div>
		<?php endif; ?>
		<main>
			<?php
				foreach ( $blocks as $section ) {
					//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo wpforms_render( 'admin/splash/section', $section, true );
				}
			?>
		</main>
		<?php
			if ( $license === 'lite' ) {
				//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo wpforms_render( 'admin/splash/footer', $footer, true );
			}
		?>
	</div>
</script>
