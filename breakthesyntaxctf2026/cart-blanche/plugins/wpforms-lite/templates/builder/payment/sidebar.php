<?php
/**
 * Payments sidebar button in the builder.
 *
 * @since 1.8.2
 *
 * @var string $configured  Whether payment is configured.
 * @var string $slug        Slug of the payment integration.
 * @var string $icon        Icon of the payment integration.
 * @var string $name        Name of the payment integration.
 * @var bool   $recommended Whether payment is recommended.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<a href="#" class="wpforms-panel-sidebar-section icon <?php echo esc_attr( $configured ); ?> wpforms-panel-sidebar-section-<?php echo esc_attr( $slug ); ?>" data-section="<?php echo esc_attr( $slug ); ?>">

	<img src="<?php echo esc_url( $icon ); ?>" alt="<?php echo esc_attr( $name ); ?>">

	<div class="wpforms-panel-sidebar-info">
		<span class="wpforms-panel-sidebar-name"><?php echo esc_html( $name ); ?></span>

		<?php if ( ! empty( $recommended ) ) : ?>
		<span class="wpforms-panel-sidebar-recommended">
			<i class="fa fa-star" aria-hidden="true"></i>&nbsp;
			<?php esc_html_e( 'Recommended', 'wpforms-lite' ); ?>
		</span>
		<?php endif; ?>
	</div>

	<div class="wpforms-panel-sidebar-controls">
		<i class="fa fa-angle-right wpforms-toggle-arrow"></i>
		<i class="fa fa-check-circle-o <?php echo empty( $configured ) ? 'wpforms-hidden' : ''; ?>"></i>
	</div>
</a>
