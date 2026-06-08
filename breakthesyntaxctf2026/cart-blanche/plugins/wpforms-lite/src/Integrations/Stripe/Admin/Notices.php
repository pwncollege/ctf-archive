<?php

namespace WPForms\Integrations\Stripe\Admin;

use WPForms\Integrations\Stripe\Helpers;
use WPForms\Integrations\Stripe\StripeAddonCompatibility;

/**
 * Stripe related admin notices.
 *
 * @since 1.8.2
 */
class Notices {

	/**
	 * Get a notice if a license is insufficient not to be charged a fee.
	 *
	 * @since 1.8.2
	 *
	 * @param string $classes Additional notice classes.
	 *
	 * @return string
	 */
	public static function get_fee_notice( $classes = '' ) {

		if ( ! Helpers::is_application_fee_supported() ) {
			return '';
		}

		$is_allowed_license = Helpers::is_allowed_license_type();
		$is_active_license  = Helpers::is_license_active();
		$notice             = '';

		if ( $is_allowed_license && $is_active_license ) {
			return $notice;
		}

		if ( ! $is_allowed_license ) {
			$notice = self::get_non_pro_license_level_notice();
		} elseif ( ! $is_active_license ) {
			$notice = self::get_non_active_license_notice();
		}

		if ( wpforms_is_admin_page( 'builder' ) ) {
			return sprintf( '<p class="wpforms-stripe-notice-info wpforms-alert wpforms-alert-info ' . wpforms_sanitize_classes( $classes ) . '">%s</p>', $notice );
		}

		return sprintf( '<div class="wpforms-stripe-notice-info ' . wpforms_sanitize_classes( $classes ) . '"><p>%s</p></div>', $notice );
	}

	/**
	 * Get a fee notice for a non-active license.
	 *
	 * If the license is NOT set/activated, show the notice to activate it.
	 * Otherwise, show the notice to renew it.
	 *
	 * @since 1.8.2
	 *
	 * @return string
	 */
	private static function get_non_active_license_notice() {

		$setting_page_url = add_query_arg(
			[
				'page' => 'wpforms-settings',
				'view' => 'general',
			],
			admin_url( 'admin.php' )
		);

		// The license is not set/activated at all.
		if ( empty( wpforms_get_license_key() ) ) {
			return sprintf(
				wp_kses( /* translators: %s - general admin settings page URL. */
					__( '<strong>Pay-as-you-go Pricing</strong><br>3%% fee per-transaction + Stripe fees. <a href="%s">Activate your license</a> to remove additional fees and unlock powerful features.', 'wpforms-lite' ),
					[
						'strong' => [],
						'br'     => [],
						'a'      => [
							'href'   => [],
							'target' => [],
						],
					]
				),
				esc_url( $setting_page_url )
			);
		}

		return sprintf(
			wp_kses( /* translators: %s - general admin settings page URL. */
				__( '<strong>Pay-as-you-go Pricing</strong><br> 3%% fee per-transaction + Stripe fees. <a href="%s">Renew your license</a> to remove additional fees and unlock powerful features.', 'wpforms-lite' ),
				[
					'strong' => [],
					'br'     => [],
					'a'      => [
						'href'   => [],
						'target' => [],
					],
				]
			),
			esc_url( $setting_page_url )
		);
	}

	/**
	 * Get a fee notice for license levels below the `pro`.
	 *
	 * Show the notice to upgrade to Pro.
	 *
	 * @since 1.8.2
	 *
	 * @return string
	 */
	private static function get_non_pro_license_level_notice() {

		$utm_content  = 'Stripe Pro - Remove Fees';
		$utm_medium   = wpforms_is_admin_page( 'builder' ) ? 'Payment Settings' : 'Settings - Payments';
		$upgrade_link = wpforms()->is_pro() ? wpforms_admin_upgrade_link( $utm_medium, $utm_content ) : wpforms_utm_link( 'https://wpforms.com/lite-upgrade/', $utm_medium, $utm_content );

		return sprintf(
			wp_kses( /* translators: %s - WPForms.com Upgrade page URL. */
				__( '<strong>Pay-as-you-go Pricing</strong><br> 3%% fee per-transaction + Stripe fees. <a href="%s" target="_blank">Upgrade to Pro</a> to remove additional fees and unlock powerful features.', 'wpforms-lite' ),
				[
					'strong' => [],
					'br'     => [],
					'a'      => [
						'href'   => [],
						'target' => [],
					],
				]
			),
			esc_url( $upgrade_link )
		);
	}

	/**
	 * Display alert about new interface.
	 *
	 * @since 1.8.4
	 */
	public static function prompt_new_interface() {

		$dismissed = get_user_meta( get_current_user_id(), 'wpforms_dismissed', true );

		// Check if not dismissed.
		if ( ! empty( $dismissed['edu-wpforms-stripe-legacy-interface'] ) ) {
			return;
		}

		$addon_compat = ( new StripeAddonCompatibility() )->init();

		if ( $addon_compat && ! $addon_compat->is_supported_modern_settings() ) {
			$message = __( 'A new and improved Stripe interface is available with new Stripe Pro addon.', 'wpforms-lite' );
		} else {
			$message = __( 'A new and improved Stripe interface is available when you create new forms.', 'wpforms-lite' );
		}

		?>
		<div id="wpforms-stripe-new-interface-alert" class="wpforms-alert wpforms-alert-warning wpforms-alert-dismissible wpforms-dismiss-container">
			<div class="wpforms-alert-message">
				<p>
					<?php echo esc_html( $message ); ?>
					<?php
					printf(
						'<a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a>',
						esc_url( wpforms_utm_link( 'https://wpforms.com/introducing-wpforms-1-8-4-new-stripe-payment-tools/#stripe-conditional-logic', 'Builder Settings', 'Stripe New Payments Interface' ) ),
						esc_html__( 'What\'s new?', 'wpforms-lite' )
					);
					?>
				</p>
			</div>
			<div class="wpforms-alert-buttons">
				<button type="button" class="wpforms-dismiss-button" title="<?php esc_attr_e( 'Dismiss this message.', 'wpforms-lite' ); ?>" data-section="wpforms-stripe-legacy-interface"></button>
			</div>
		</div>
		<?php
	}
}
