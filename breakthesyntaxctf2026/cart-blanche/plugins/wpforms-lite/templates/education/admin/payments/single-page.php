<?php
/**
 * Payment single page education notice.
 *
 * @since 1.8.2
 */

use WPForms\Integrations\Square\Helpers as SquareHelpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wpforms-payment-single-education-notice postbox wpforms-dismiss-container">
	<div class="wpforms-payment-single-education-notice-title">
		<?php esc_html_e( 'Get More Out of Payments', 'wpforms-lite' ); ?>
	</div>
	<div class="wpforms-payment-single-education-notice-description">
		<?php
		if ( SquareHelpers::is_application_fee_supported() ) {
			esc_html_e( 'Unlock conditional logic, coupons, lower Stripe, PayPal and Square fees, and more.', 'wpforms-lite' );
		} else {
			esc_html_e( 'Unlock conditional logic, coupons, lower Stripe and PayPal fees, and more.', 'wpforms-lite' );
		}
		echo '&nbsp;';
		printf(
			wp_kses( /* translators: %s - WPForms.com Upgrade page URL. */
				__( '<a href="%s" target="_blank" rel="noopener noreferrer">Upgrade to Pro!</a>', 'wpforms-lite' ),
				[
					'a' => [
						'href'   => [],
						'rel'    => [],
						'target' => [],
					],
				]
			),
			esc_url(
				wpforms_admin_upgrade_link(
					'Single Payment Page',
					'Stripe Pro - Remove Fees'
				)
			)
		);
		?>
	</div>
	<div
		class="wpforms-payment-single-education-notice-dismiss-button wpforms-dismiss-button"
		data-section="single-payment"
		aria-label="<?php esc_html_e( 'Dismiss this notice', 'wpforms-lite' ); ?>">
		<span class="dashicons dashicons-no-alt"></span>
	</div>
</div>
