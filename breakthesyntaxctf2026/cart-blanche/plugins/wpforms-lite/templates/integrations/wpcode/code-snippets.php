<?php
/**
 * WPCode integration code snippets page.
 *
 * @since 1.8.5
 *
 * @var array  $snippets        WPCode snippets list.
 * @var bool   $action_required Indicate that user should install or activate WPCode.
 * @var string $action          Popup button action.
 * @var string $plugin          WPCode Lite download URL | WPCode Lite plugin slug.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<?php
$container_class   = $action_required ? 'wpforms-wpcode-blur' : '';
$popup_title       = esc_html__( 'Please Install WPCode to Use the WPForms Snippet Library', 'wpforms-lite' );
$popup_button_text = esc_html__( 'Install + Activate WPCode', 'wpforms-lite' );

if ( $action === 'update' ) {
	$popup_title       = esc_html__( 'Please Update WPCode to Use the WPForms Snippet Library', 'wpforms-lite' );
	$popup_button_text = esc_html__( 'Update + Activate WPCode', 'wpforms-lite' );
}

if ( $action === 'activate' ) {
	$popup_title       = esc_html__( 'Please Activate WPCode to Use the WPForms Snippet Library', 'wpforms-lite' );
	$popup_button_text = esc_html__( 'Activate WPCode', 'wpforms-lite' );
}
?>

<div class="wpforms-wpcode">
	<?php if ( $action_required ) : ?>
		<div class="wpforms-wpcode-popup">
			<div class="wpforms-wpcode-popup-title"><?php echo esc_html( $popup_title ); ?></div>
			<div class="wpforms-wpcode-popup-description">
				<?php esc_html_e( 'Using WPCode, you can install WPForms code snippets with 1 click right from this page or the WPCode Library in the WordPress admin.', 'wpforms-lite' ); ?>
			</div>
			<div data-action="<?php echo esc_attr( $action ); ?>" data-plugin="<?php echo esc_attr( $plugin ); ?>" class="wpforms-wpcode-popup-button wpforms-btn wpforms-btn-lg wpforms-btn-orange"><?php echo esc_html( $popup_button_text ); ?></div>
			<a
					href="https://wordpress.org/plugins/insert-headers-and-footers/?utm_source=wpformsplugin&utm_medium=WPCode+WordPress+Repo&utm_campaign=plugin&utm_content=WPCode"
					class="wpforms-wpcode-popup-link">
				<?php esc_html_e( 'Learn more about WPCode', 'wpforms-lite' ); ?>
			</a>
		</div>
	<?php endif; ?>

	<div class="wpforms-wpcode-container <?php echo sanitize_html_class( $container_class ); ?>">
		<div class="wpforms-setting-row tools wpforms-wpcode-header">
			<div class="wpforms-wpcode-header-meta">
				<h4><?php esc_html_e( 'Code Snippets', 'wpforms-lite' ); ?></h4>
				<p>
					<?php
					printf(
						wp_kses( /* translators: %s - WPCode library website URL. */
							__( 'Using WPCode, you can install WPForms code snippets with 1 click directly from this page or the <a href="%s" target="_blank" rel="noopener noreferrer">WPCode library</a>.', 'wpforms-lite' ),
							[
								'a' => [
									'href'   => [],
									'rel'    => [],
									'target' => [],
								],
							]
						),
						esc_url( admin_url( 'admin.php?page=wpcode-library' ) )
					);
					?>
				</p>
			</div>
			<div class="wpforms-wpcode-header-search">
				<label for="wpforms-wpcode-snippet-search"></label>
				<input
						type="search" placeholder="<?php esc_attr_e( 'Search Snippets', 'wpforms-lite' ); ?>"
				        id="wpforms-wpcode-snippet-search">
			</div>
		</div>

		<div id="wpforms-wpcode-snippets-list">
			<div class="list">
				<?php
				foreach ( $snippets as $snippet ) :
					$button_text       = $snippet['installed'] ? __( 'Edit Snippet', 'wpforms-lite' ) : __( 'Install Snippet', 'wpforms-lite' );
					$button_type_class = $snippet['installed'] ? 'button-primary' : 'button-secondary';
					$button_action     = $snippet['installed'] ? 'edit' : 'install';
					$badge_text        = $snippet['installed'] ? __( 'Installed', 'wpforms-lite' ) : '';
					?>
					<div class="wpforms-wpcode-snippet">
						<div class="wpforms-wpcode-snippet-header">
							<h3 class="wpforms-wpcode-snippet-title"><?php echo esc_html( $snippet['title'] ); ?></h3>
							<div class="wpforms-wpcode-snippet-note"><?php echo esc_html( $snippet['note'] ); ?></div>
						</div>
						<div class="wpforms-wpcode-snippet-footer">
							<div class="wpforms-wpcode-snippet-badge"><?php echo esc_html( $badge_text ); ?></div>
							<a
								href="<?php echo esc_url( $snippet['install'] ); ?>"
								class="button wpforms-wpcode-snippet-button <?php echo sanitize_html_class( $button_type_class ); ?>"
								data-action="<?php echo esc_attr( $button_action ); ?>"><?php echo esc_html( $button_text ); ?> </a>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
			<div id="wpforms-wpcode-no-results"><?php esc_html_e( "Sorry, we didn't find any snippets that match your criteria.", 'wpforms-lite' ); ?></div>
		</div>
	</div>
</div>
