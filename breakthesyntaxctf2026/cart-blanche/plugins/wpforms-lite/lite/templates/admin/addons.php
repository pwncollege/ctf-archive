<?php
/**
 * Admin > Addons page template.
 *
 * @since 1.6.7
 *
 * @var string $upgrade_link_base Upgrade link base.
 * @var array  $addons            Addons data.
 */

use WPForms\Admin\Education\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div id="wpforms-admin-addons" class="wrap wpforms-admin-wrap wpforms-addons">
	<h1 class="wpforms-addons-header">
		<span class="wpforms-addons-header-title">
			<?php esc_html_e( 'WPForms Addons', 'wpforms-lite' ); ?>
		</span>

		<span class="wpforms-addons-header-search">
			<input type="search" placeholder="<?php esc_attr_e( 'Search Addons', 'wpforms-lite' ); ?>" id="wpforms-addons-search">
		</span>
	</h1>
	<div class="wpforms-admin-content">
		<div id="wpforms-addons-list-section-all">
			<div class="list wpforms-addons-list">
				<?php
				foreach ( $addons as $addon ) :
					$addon['icon']    = ! empty( $addon['icon'] ) ? $addon['icon'] : '';
					$addon['title']   = ! empty( $addon['title'] ) ? $addon['title'] : __( 'Unknown Addon', 'wpforms-lite' );
					$addon['title']   = str_replace( ' Addon', '', $addon['title'] );
					$addon['excerpt'] = ! empty( $addon['excerpt'] ) ? $addon['excerpt'] : '';
					$upgrade_link     = add_query_arg(
						[
							'utm_content' => $addon['title'],
						],
						$upgrade_link_base
					);

					$licenses                 = [ 'basic', 'plus', 'pro', 'elite', 'agency', 'ultimate' ];
					$addon_licenses           = $addon['license'];
					$common_licenses          = array_intersect( $licenses, $addon_licenses );
					$minimum_required_license = reset( $common_licenses );
					$image_alt                = sprintf( /* translators: %s - addon title. */
						__( '%s logo', 'wpforms-lite' ),
						$addon['title']
					);

					$badge = Helpers::get_addon_badge( $addon );

					$item_classes = [
						'wpforms-addons-list-item',
						'addon-item',
						! empty( $badge ) ? 'has-badge' : '',
					];
				?>
					<div class="<?php echo wpforms_sanitize_classes( $item_classes, true ); ?>">
						<div class="wpforms-addons-list-item-header">
							<img src="<?php echo esc_url( WPFORMS_PLUGIN_URL . 'assets/images/' . $addon['icon'] ); ?>" alt="<?php echo esc_attr( $image_alt ); ?>">

							<div class="wpforms-addons-list-item-header-meta">
								<div class="wpforms-addons-list-item-header-meta-title">
									<?php
									printf(
										'<a href="%1$s" title="%2$s" target="_blank" rel="noopener noreferrer" class="addon-link">%3$s</a>',
										esc_url( $upgrade_link ),
										esc_attr__( 'Learn more', 'wpforms-lite' ),
										esc_html( $addon['title'] )
									);
									?>

									<?php echo $badge; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								</div>

								<div class="wpforms-addons-list-item-header-meta-excerpt">
									<?php echo esc_html( $addon['excerpt'] ); ?>
								</div>
							</div>
						</div>

						<div class="wpforms-addons-list-item-footer">
							<?php Helpers::print_badge( $minimum_required_license, 'lg' ); ?>

							<a href="<?php echo esc_url( $upgrade_link ); ?>" target="_blank" rel="noopener noreferrer" class="button button-secondary wpforms-upgrade-modal">
								<?php esc_html_e( 'Upgrade Now', 'wpforms-lite' ); ?>
							</a>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<div id="wpforms-addons-no-results"><?php esc_html_e( 'Sorry, we didn\'t find any addons that match your criteria.', 'wpforms-lite' ); ?></div>
	</div>
</div>
