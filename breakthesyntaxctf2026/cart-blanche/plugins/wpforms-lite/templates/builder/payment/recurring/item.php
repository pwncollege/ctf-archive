<?php
/**
 * Payment Recurring Item HTML template.
 *
 * @since 1.8.4
 *
 * @var string $plan_id Plan ID.
 * @var string $content Recurring payment content.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="wpforms-panel-content-section-payment-plan" data-plan-id="<?php echo esc_attr( $plan_id ); ?>">
	<div class="wpforms-panel-content-section-payment-plan-head">
		<div class="wpforms-panel-content-section-payment-plan-head-title"></div>
		<div class="wpforms-panel-content-section-payment-plan-head-buttons">
			<i class="wpforms-panel-content-section-payment-plan-head-buttons-delete fa fa-trash-o"></i>
			<i class="wpforms-panel-content-section-payment-plan-head-buttons-toggle fa fa-chevron-circle-down"></i>
		</div>
	</div>
	<div class="wpforms-panel-content-section-payment-plan-body"><?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
</div>
