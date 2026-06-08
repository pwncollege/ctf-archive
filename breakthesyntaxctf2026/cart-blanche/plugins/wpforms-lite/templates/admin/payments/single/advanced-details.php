<?php
/**
 * Single Payment page - Advanced details template.
 *
 * @since 1.8.2
 *
 * @var array $details_list Details list.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div id="wpforms-payment-advanced-info" class="postbox">

	<div class="postbox-header">
		<h2 class="hndle">
			<span><?php echo esc_html__( 'Advanced Details', 'wpforms-lite' ); ?></span>
		</h2>
	</div>

	<div class="inside">

		<?php
		foreach ( $details_list as $item ) :
			?>

			<div class="wpforms-payment-advanced-item" >

				<p class="wpforms-payment-advanced-item-label">
					<?php echo esc_html( $item['label'] ); ?>
				</p>

				<div class="wpforms-payment-advanced-item-value">
					<?php if ( isset( $item['link'] ) ) : ?>
						<a href="<?php echo esc_url( $item['link'] ); ?>" target="_blank" rel="noopener noreferrer" class="wpforms-link">
					<?php endif; ?>
					<?php echo wp_kses_post( nl2br( make_clickable( $item['value'] ) ) ); ?>
					<?php if ( isset( $item['link'] ) ) : ?>
						</a>
					<?php endif; ?>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</div>
