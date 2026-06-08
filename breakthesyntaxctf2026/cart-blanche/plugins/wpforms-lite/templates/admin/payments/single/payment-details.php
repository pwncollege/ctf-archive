<?php
/**
 * Single Payment page - Payment details template for single and subscription data.
 *
 * @since 1.8.2
 * @since 1.8.6 Added $class variable.
 *
 * @var string $id                  Block id.
 * @var string $class               Extra Class based on type of payment.
 * @var string $title               Block title.
 * @var string $payment_id          Payment id.
 * @var string $gateway_link        Link to gateway payment details.
 * @var string $gateway_text        Gateway link text.
 * @var string $gateway_name        Gateway name.
 * @var string $gateway_action_text Gateway action link text.
 * @var string $gateway_action_link Gateway action link.
 * @var string $gateway_action_slug Gateway action slug.
 * @var int    $payment_id_raw      Payment id raw.
 * @var string $status              Payment or Subscription status.
 * @var string $status_label        Payment or Subscription status label.
 * @var bool   $disabled            Is gateway action disabled.
 * @var array  $stat_cards          Stat cards to display.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div id="<?php echo esc_attr( $id ); ?>" class="postbox <?php echo esc_attr( $class ); ?>">

	<div class="postbox-header">
		<h2 class="hndle">
			<span><?php echo esc_html( $title ); ?></span>
			<?php if ( isset( $payment_id ) ) : ?>
				<span class="wpforms-payment-id"><?php echo esc_html( $payment_id ); ?></span>
			<?php endif; ?>
		</h2>
	</div>

	<div class="inside">
		<ul class="wpforms-payments-details-list">
			<?php foreach ( $stat_cards as $key => $stat_card ) : ?>
				<li class="wpforms-payments-details-stat-card">
					<button class="<?php echo wpforms_sanitize_classes( $stat_card['button_classes'], true ); ?>" >
						<span class="stat-card-label"><?php echo esc_html( $stat_card['label'] ); ?></span>
						<span class="stat-card-value">
							<?php echo ! empty( $stat_card['value'] ) ? esc_html( $stat_card['value'] ) : esc_html__( 'N/A', 'wpforms-lite' ); ?>
							<?php if ( ! empty( $stat_card['tooltip'] ) ) : ?>
								<i class="wpforms-single-payment-tooltip" data-tooltip-content="#wpforms-single-payment-tooltip-content-<?php echo esc_attr( $key ); ?>"></i>
								<span id="wpforms-single-payment-tooltip-content-<?php echo esc_attr( $key ); ?>" class="wpforms-single-payment-tooltip-content"><?php echo wp_kses_post( $stat_card['tooltip'] ); ?></span>
							<?php endif; ?>
						</span>
					</button>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>

	<div class="wpforms-payment-actions">
		<div class="status">
			<span class="wpforms-payment-action-status-label"><?php esc_html_e( 'Status:', 'wpforms-lite' ); ?></span>
			<span class="wpforms-payment-action-status-value <?php echo esc_attr( $status ); ?>"><?php echo wp_kses( $status_label, [ 'span' => [] ] ); ?></span>
		</div>
		<?php if ( $gateway_link ) : ?>
		<div class="actions">
			<a href="<?php echo esc_url( $gateway_link ); ?>" class="link" target="_blank" rel="noopener noreferrer">
				<?php echo esc_html( $gateway_text ); ?>
			</a>
			<?php if ( ! $disabled ) : ?>
				<a href="<?php echo esc_url( $gateway_action_link ); ?>"
					class="button wpforms-payments-single-action"
					target="_blank"
					rel="noopener noreferrer"
					id="wpforms-payments-single-<?php echo esc_attr( $gateway_action_slug ); ?>"
					data-action-id="<?php echo esc_attr( $payment_id_raw ); ?>"
					data-gateway="<?php echo esc_attr( $gateway_name ); ?>"
					data-action-type="<?php echo esc_attr( $gateway_action_slug ); ?>">
					<?php echo esc_html( $gateway_action_text ); ?>
				</a>
			<?php endif; ?>
		</div>
		<?php endif; ?>
		<div class="clear"></div>
	</div>
</div>
