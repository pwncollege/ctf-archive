<?php
/**
 * User Templates Empty State Template.
 *
 * @since 1.8.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wpforms-admin-empty-state-container wpforms-admin-no-user-templates">

	<h2 class="waving-hand-emoji">
		<?php esc_html_e( 'Hi there!', 'wpforms-lite' ); ?>
	</h2>

	<h4><?php esc_html_e( 'Did you know you can save your forms as reusable templates?', 'wpforms-lite' ); ?></h4>
	<p><?php esc_html_e( 'Save your custom forms to the templates library for quick and easy use.', 'wpforms-lite' ); ?></p>

	<img src="<?php echo esc_url( WPFORMS_PLUGIN_URL . 'assets/images/empty-states/no-user-templates.png' ); ?>" alt="">

	<p class="wpforms-admin-no-forms-footer">
		<?php
		printf(
			wp_kses( /* translators: %s - URL to the documentation article. */
				__( 'Need some help? Check out our <a href="%s" rel="noopener noreferrer" target="_blank">documentation</a>.', 'wpforms-lite' ),
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
					'https://wpforms.com/docs/how-to-create-a-custom-form-template/',
					wpforms_is_admin_page( 'builder' ) ? 'builder-templates' : 'Form Templates Subpage',
					'User Templates Documentation'
				)
			)
		);
		?>
	</p>

</div>
