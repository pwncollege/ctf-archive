<?php
/**
 * Single Payment page - Log metabox.
 *
 * @since 1.8.2
 *
 * @var array $logs Logs.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div id="wpforms-payment-logs" class="postbox">

	<div class="postbox-header">
		<h2 class="hndle">
			<span><?php esc_html_e( 'Log', 'wpforms-lite' ); ?></span>
		</h2>
	</div>

	<div class="inside">

		<?php if ( empty( $logs ) ) : ?>
			<span class="wpforms-payment-no-logs"><?php esc_html_e( 'No Logs', 'wpforms-lite' ); ?></span>
		<?php endif; ?>

		<?php
		foreach ( $logs as $log ) :

			$item      = json_decode( $log['value'], false );
			$date_time = sprintf( /* translators: %1$s - date, %2$s - time when item was created, e.g. "Oct 22, 2022 at 11:11 am". */
				__( '%1$s at %2$s', 'wpforms-lite' ),
				wpforms_date_format( $item->date, 'M j, Y', true ),
				wpforms_time_format( $item->date, '', true )
			);

			if ( empty( $item->value ) ) {
				continue;
			}
			?>

			<div class="wpforms-payment-log-item" >

				<span class="wpforms-payment-log-item-value">
					<?php echo esc_html( $item->value ); ?>
				</span>

				<span class="wpforms-payment-log-item-date">
					<?php echo esc_html( $date_time ); ?>
				</span>
			</div>
		<?php endforeach; ?>
	</div>
</div>
