<?php
/**
 * Admin/Builder/LiteConnect Education modal template for Lite.
 *
 * @since 1.9.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<script type="text/html" id="tmpl-wpforms-builder-ai-lite-connect-modal-content">
	<div class="wpforms-settings-lite-connect-modal-content">
		<img src="<?php echo esc_url( WPFORMS_PLUGIN_URL . 'assets/images/sullie-alt.png' ); ?>" alt="<?php esc_attr_e( 'Sullie the WPForms mascot', 'wpforms-lite' ); ?>" class="wpforms-mascot">
		<h2><?php esc_html_e( 'Enable AI Features in WPForms', 'wpforms-lite' ); ?></h2>
		<p>
			<?php esc_html_e( 'Before you can proceed, we need your permission to record what you input in order to generate content with AI. You’ll also get...', 'wpforms-lite' ); ?>
		</p>
		<div class="wpforms-features">
			<section>
				<img src="<?php echo esc_url( WPFORMS_PLUGIN_URL . 'assets/images/lite-connect/lock-ai.svg' ); ?>" alt="<?php esc_attr_e( 'WPForms AI.', 'wpforms-lite' ); ?>">
				<aside>
					<h4><?php esc_html_e( 'WPForms AI', 'wpforms-lite' ); ?></h4>
					<p>
						<?php esc_html_e( 'Build your forms even faster with state-of-the-art generative AI built right into the form builder.', 'wpforms-lite' ); ?>
					</p>
				</aside>
			</section>
			<section>
				<img src="<?php echo esc_url( WPFORMS_PLUGIN_URL . 'assets/images/lite-connect/cloud.svg' ); ?>" alt="<?php esc_attr_e( 'Backup and Restore.', 'wpforms-lite' ); ?>">
				<aside>
					<h4><?php esc_html_e( 'Form Entry Backup & Restore', 'wpforms-lite' ); ?></h4>
					<p>
						<?php esc_html_e( 'When you upgrade to WPForms Pro, we\'ll automatically restore all of the entries that you collected in WPForms Lite.', 'wpforms-lite' ); ?>
					</p>
				</aside>
			</section>
			<section>
				<img src="<?php echo esc_url( WPFORMS_PLUGIN_URL . 'assets/images/lite-connect/lock-alt.svg' ); ?>" alt="<?php esc_attr_e( 'Security & Protection.', 'wpforms-lite' ); ?>">
				<aside>
					<h4><?php esc_html_e( 'Security & Protection', 'wpforms-lite' ); ?></h4>
					<p>
						<?php esc_html_e( 'Entries are stored securely and privately until you\'re ready to upgrade. Our team cannot view your forms or entries.', 'wpforms-lite' ); ?>
					</p>
				</aside>
			</section>
			<section>
				<img src="<?php echo esc_url( WPFORMS_PLUGIN_URL . 'assets/images/lite-connect/envelope.svg' ); ?>" alt="<?php esc_attr_e( 'WPForms Newsletter.', 'wpforms-lite' ); ?>">
				<aside>
					<h4><?php esc_html_e( 'WPForms Newsletter', 'wpforms-lite' ); ?></h4>
					<p>
						<?php esc_html_e( 'Ready to grow your website? Get the latest pro tips and updates from the WPForms team.', 'wpforms-lite' ); ?>
					</p>
				</aside>
			</section>
		</div>
		<footer>
			<?php
			printf(
				wp_kses( /* translators: %s - WPForms Terms of Service link. */
					__( 'By enabling Lite Connect you agree to our <a href="%s" target="_blank" rel="noopener noreferrer">Terms of Service</a> and to share your information with WPForms.', 'wpforms-lite' ),
					[
						'a' => [
							'href'   => [],
							'target' => [],
							'rel'    => [],
						],
					]
				),
				esc_url( wpforms_utm_link( 'https://wpforms.com/terms/', 'Lite Connect Modal', 'Terms of Service' ) )
			);
			?>
		</footer>
	</div>
</script>
