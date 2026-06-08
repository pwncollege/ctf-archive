<?php

namespace WPForms\Integrations\PayPalCommerce\Admin;

use WPForms\Admin\Notice;
use WPForms\Integrations\PayPalCommerce\Helpers;
use WPForms\Integrations\PayPalCommerce\Connection;

/**
 * PayPal Commerce admin notices.
 *
 * @since 1.10.0
 */
class Notices {

	/**
	 * Initialize.
	 *
	 * @since 1.10.0
	 *
	 * @return Notices
	 */
	public function init(): Notices {

		$this->hooks();

		return $this;
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.10.0
	 */
	private function hooks(): void {

		add_action( 'wpforms_settings_init', [ $this, 'display_notice' ] );
	}

	/**
	 * Display admin error notice if something wrong with the PayPalCommerce settings.
	 *
	 * @since 1.10.0
	 */
	public function display_notice(): void {

		$connection = Connection::get();

		if ( ! $connection ) {
			return;
		}

		// Try to refresh tokens for valid connections.
		if ( $connection->is_valid() ) {
			$connection->refresh_expired_tokens();
		}

		$this->maybe_display_connection_notice( $connection );
	}

	/**
	 * Display an admin error notice if a connection is not ready to use.
	 *
	 * @since 1.10.0
	 *
	 * @param Connection|\WPFormsPaypalCommerce\Connection $connection Connection data.
	 */
	private function maybe_display_connection_notice( $connection ): void {

		if ( isset( $_GET['paypal_commerce_disconnect'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			Notice::error( esc_html__( 'Heads up! An error occurred while disconnecting your PayPal account. Please try again.', 'wpforms-lite' ) );

			return;
		}

		if ( ! $connection->is_configured() ) {
			Notice::error( esc_html__( 'Heads up! Your connection to PayPal Commerce is not complete. Please reconnect your PayPal Commerce account.', 'wpforms-lite' ) );

			return;
		}

		if ( ! $connection->is_valid() ) {
			Notice::error( esc_html__( 'Heads up! Your connection to PayPal Commerce is not valid. Please reconnect your PayPal Commerce account.', 'wpforms-lite' ) );

			return;
		}

		if ( Helpers::is_currency_supported() ) {
			return;
		}

		Notice::error(
			sprintf(
				wp_kses( /* translators: %1$s - Selected currency on the WPForms Settings admin page. */
					__( '<strong>Payments Cannot Be Processed</strong><br>The currency you have set (%1$s) is not supported by PayPal Commerce. Please choose a different currency, or consider switching your payment gateway to Stripe.', 'wpforms-lite' ),
					[
						'strong' => [],
						'br'     => [],
					]
				),
				esc_html( wpforms_get_currency() )
			)
		);
	}

	/**
	 * Get fee notice if a license is not set/activated or is below the `pro` level.
	 *
	 * @since 1.10.0
	 *
	 * @param string $classes Additional notice classes.
	 *
	 * @return string
	 */
	public static function get_fee_notice( string $classes = '' ): string {

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
			return sprintf( '<p class="wpforms-paypal-commerce-notice-info wpforms-alert wpforms-alert-info ' . wpforms_sanitize_classes( $classes ) . '">%s</p>', $notice );
		}

		return sprintf( '<div class="wpforms-paypal-commerce-notice-info ' . wpforms_sanitize_classes( $classes ) . '"><p>%s</p></div>', $notice );
	}

	/**
	 * Get a fee notice for a non-active license.
	 *
	 * If the license is NOT set/activated, show the notice to activate it.
	 * Otherwise, show the notice to renew it.
	 *
	 * @since 1.10.0
	 *
	 * @return string
	 */
	private static function get_non_active_license_notice(): string {

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
					__( '<strong>Pay-as-you-go Pricing</strong><br>3%% fee per-transaction + PayPal Commerce fees. <a href="%s">Activate your license</a> to remove additional fees and unlock powerful features.', 'wpforms-lite' ),
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
				__( '<strong>Pay-as-you-go Pricing</strong><br> 3%% fee per-transaction + PayPal Commerce fees. <a href="%s">Renew your license</a> to remove additional fees and unlock powerful features.', 'wpforms-lite' ),
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
	 * @since 1.10.0
	 *
	 * @return string
	 */
	private static function get_non_pro_license_level_notice(): string {

		$utm_content  = 'PayPal Commerce Pro - Remove Fees';
		$utm_medium   = wpforms_is_admin_page( 'builder' ) ? 'Payment Settings' : 'Settings - Payments';
		$upgrade_link = wpforms()->is_pro() ? wpforms_admin_upgrade_link( $utm_medium, $utm_content ) : wpforms_utm_link( 'https://wpforms.com/lite-upgrade/', $utm_medium, $utm_content );

		return sprintf(
			wp_kses( /* translators: %s - WPForms.com Upgrade page URL. */
				__( '<strong>Pay-as-you-go Pricing</strong><br> 3%% fee per-transaction + PayPal Commerce fees. <a href="%s" target="_blank">Upgrade to Pro</a> to remove additional fees and unlock powerful features.', 'wpforms-lite' ),
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
}
