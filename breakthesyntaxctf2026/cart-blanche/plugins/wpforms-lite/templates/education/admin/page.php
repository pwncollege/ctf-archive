<?php
/**
 * General education template.
 *
 * @since 1.8.6
 *
 * @var string $action               Is plugin installed?
 * @var string $path                 Plugin file.
 * @var string $url                  URL download plugin download.
 * @var bool   $plugin_allow         Allow using plugin.
 * @var string $heading_title        Heading title.
 * @var string $badge                Badge.
 * @var string $heading_description  Heading description.
 * @var string $features_description Features description.
 * @var array  $features             List of features.
 * @var array  $images               List of images.
 * @var string $license_level        License level.
 * @var string $utm_medium           UTM medium.
 * @var string $utm_content          UTM content.
 * @var string $upgrade_link         Upgrade link.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wpforms-education-page">
	<div class="wpforms-education-page-heading">
		<?php if ( isset( $heading_title ) ) : ?>
			<h4>
				<?php echo esc_html( $heading_title ); ?>
				<?php if ( isset( $badge ) ) : ?>
					<span class="wpforms-badge wpforms-badge-sm wpforms-badge-inline wpforms-badge-titanium wpforms-badge-rounded"><?php echo esc_html( $badge ); ?></span>
				<?php endif; ?>
			</h4>
		<?php endif; ?>
		<?php
			if ( isset( $heading_description ) ) {
				echo wp_kses_post( $heading_description );
			}
		?>
	</div>

	<div class="wpforms-education-page-media">
		<div class="wpforms-education-page-images">
			<?php
			if ( isset( $images ) ) :
				foreach ( $images as $image ) :
					?>
				<figure>
					<div class="wpforms-education-page-images-image">
						<img src="<?php echo esc_url( $image['url'] ); ?>" alt="<?php echo esc_attr( $image['title'] ); ?>" />
						<a href="<?php echo esc_url( $image['url2x'] ); ?>" class="hover" data-lity data-lity-desc="<?php echo esc_attr( $image['title'] ); ?>"></a>
					</div>
					<figcaption><?php echo esc_html( $image['title'] ); ?></figcaption>
				</figure>
			<?php
				endforeach;
			endif;
			?>
		</div>
	</div>

	<div class="wpforms-education-page-caps">
		<?php if ( isset( $features_description ) ) : ?>
			<p><?php echo esc_html( $features_description ); ?></p>
		<?php endif; ?>
		<ul>
			<?php
			if ( isset( $features ) ) :
				foreach ( $features as $feature ) :
					?>
					<li>
						<i class="fa fa-solid fa-check"></i>
						<?php echo esc_html( $feature ); ?>
					</li>
				<?php
				endforeach;
			endif;
			?>
		</ul>
	</div>

	<div class="wpforms-education-page-button">
		<?php
		if ( isset( $action ) ) {
			wpforms_edu_get_button(
				$action,
				$plugin_allow,
				$path,
				$url,
				[
					'medium'  => $utm_medium,
					'content' => $utm_content,
				],
				$license_level
			);
		} else {
			printf(
				'<a href="%s" target="_blank" rel="noopener noreferrer" class="wpforms-upgrade-modal wpforms-btn wpforms-btn-lg wpforms-btn-orange">%s</a>',
				esc_url( wpforms_admin_upgrade_link( $utm_medium, $utm_content ) ),
				esc_html__( 'Upgrade to WPForms Pro', 'wpforms-lite' )
			);
		}
		?>
	</div>
</div>
