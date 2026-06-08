<?php
/**
 * Single Payment page - Display a table outlining the subscription payment history.
 *
 * @since 1.8.4
 *
 * @var string $title               Table heading.
 * @var array  $renewals            Renewal payments data.
 * @var array  $types               Payment types.
 * @var array  $statuses            Payment statuses.
 * @var string $placeholder_na_text Placeholder text. Display "N\A" if empty.
 * @var string $single_url          Single payment URL. Note that payment ID will be appended to this URL.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="postbox">
	<div class="postbox-header">
		<h2 class="hndle">
			<span><?php echo esc_html( $title ); ?></span>
		</h2>
	</div>
	<table class="wpforms-subscription-payment-history" role="table" aria-label="<?php esc_attr_e( 'Subscription Renewal History Table', 'wpforms-lite' ); ?>">
		<thead>
			<tr>
				<th scope="col"><?php esc_html_e( 'Payment ID', 'wpforms-lite' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Date', 'wpforms-lite' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Type', 'wpforms-lite' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Total', 'wpforms-lite' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Status', 'wpforms-lite' ); ?></th>
		</thead>
		<tbody>
			<?php
			foreach ( $renewals as $renewal ) :
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				$is_current = isset( $_GET['payment_id'] ) && $renewal->id === wp_unslash( $_GET['payment_id'] );
			?>
				<tr class="<?php echo $is_current ? sanitize_html_class( 'current' ) : ''; ?>">
					<td data-title="<?php esc_attr_e( 'Payment ID', 'wpforms-lite' ); ?>">
						<a href="<?php echo esc_url( add_query_arg( [ 'payment_id' => $renewal->id ], $single_url ) ); ?>">
							<?php echo esc_html( $renewal->id ); ?>
						</a>
					</td>
					<td data-title="<?php esc_attr_e( 'Date', 'wpforms-lite' ); ?>">
						<?php echo wpforms_datetime_format( $renewal->date_created_gmt, 'M j, Y', true ); ?>
					</td>
					<td data-title="<?php esc_attr_e( 'Type', 'wpforms-lite' ); ?>">
						<?php echo esc_html( isset( $types[ $renewal->type ] ) ? $renewal->type : $placeholder_na_text ); ?>
					</td>
					<td data-title="<?php esc_attr_e( 'Total', 'wpforms-lite' ); ?>">
						<?php echo wpforms_format_amount( wpforms_sanitize_amount( $renewal->total_amount, $renewal->currency ), true, $renewal->currency ); ?>
					</td>
					<td data-title="<?php esc_attr_e( 'Status', 'wpforms-lite' ); ?>">
						<?php echo esc_html( $statuses[ $renewal->status ] ); ?>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>

<?php
/* Omit closing PHP tag at the end of PHP files to avoid "headers already sent" issues. */
