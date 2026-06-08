<?php

namespace WPForms\Integrations\Square\Admin;

use WPForms\Admin\Notice;
use WPForms\Integrations\Square\Helpers;
use WPForms\Integrations\Square\Connection;
use WPForms\Integrations\Square\WebhooksHealthCheck;

/**
 * Square related admin notices.
 *
 * @since 1.9.5
 */
class Notices {

	/**
	 * Initialize.
	 *
	 * @since 1.9.5
	 *
	 * @return Notices
	 */
	public function init() {

		$this->hooks();

		return $this;
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.9.5
	 */
	private function hooks() {

		add_action( 'wpforms_settings_init', [ $this, 'display_notice' ] );
	}

	/**
	 * Display admin error notice if something wrong with the Square settings.
	 *
	 * @since 1.9.5
	 */
	public function display_notice() {

		$connection = Connection::get();

		if ( ! $connection ) {
			return;
		}

		$this->maybe_display_notice( $connection );
	}

	/**
	 * Maybe display admin notices in the settings area.
	 *
	 * @since 1.9.5
	 *
	 * @param Connection $connection Connection data.
	 */
	private function maybe_display_notice( Connection $connection ) {

		$all_notices = array_filter(
			[
				$this->maybe_get_notice( $connection ),
				$this->maybe_get_webhook_notice(),
			]
		);

		if ( empty( $all_notices ) ) {
			return;
		}

		// Notice header.
		$message = sprintf(
			'<strong>%s</strong>',
			esc_html__( 'There Are Some Problems With Your Square Connection', 'wpforms-lite' )
		);

		foreach ( $all_notices as $notice ) {
			$message .= '<br/>' . $notice;
		}

		// Display the notice.
		Notice::error( $message );
	}

	/**
	 * Maybe get admin error notice if a connection exists, but is not ready to use.
	 *
	 * @since 1.9.5
	 *
	 * @param Connection $connection Connection object.
	 *
	 * @return string Notice.
	 */
	private function maybe_get_notice( Connection $connection ): string {

		if ( ! $connection->is_configured() ) {
			return esc_html__( 'Square account connection is missing required data. You must reconnect your Square account.', 'wpforms-lite' );
		}

		if ( ! $connection->is_valid() ) {
			return esc_html__( 'Square account connection is invalid. You must reconnect your Square account.', 'wpforms-lite' );
		}

		if ( $connection->is_expired() ) {
			return esc_html__( 'Square account connection is expired. Tokens must be refreshed.', 'wpforms-lite' );
		}

		if ( empty( Helpers::get_location_id() ) ) {
			return esc_html__( 'Business Location is required to process Square payments.', 'wpforms-lite' );
		}

		if ( $connection->is_currency_matched() ) {
			return '';
		}

		return sprintf( /* translators: %1$s - Selected currency on the WPForms Settings admin page; %2$s - Currency of a business location. */
			esc_html__( 'The currency you have set (%1$s) does not match the currency of your Square business location (%2$s). Please choose a different business location or update your WPForms currency to %2$s.', 'wpforms-lite' ),
			esc_html( wpforms_get_currency() ),
			esc_html( $connection->get_currency() )
		);
	}

	/**
	 * Maybe get webhook notice if connection is not set.
	 *
	 * @since 1.9.5
	 *
	 * @return string
	 */
	private function maybe_get_webhook_notice(): string {

		// Bail out if webhooks are not enabled.
		if ( ! Helpers::is_webhook_enabled() ) {
			return '';
		}

		// Bail out if webhooks are configured and active.
		if ( Helpers::is_webhook_configured() ) {
			return '';
		}

		// If ENDPOINT_OPTION is set, it says that webhooks were configured previously. We have another notice for this case.
		if ( get_option( WebhooksHealthCheck::ENDPOINT_OPTION ) ) {
			return '';
		}

		return esc_html__( 'Webhooks are enabled, but not yet connected.', 'wpforms-lite' );
	}

	/**
	 * Get a notice if a license is insufficient not to be charged a fee.
	 *
	 * @since 1.9.5
	 *
	 * @param string $classes Additional notice classes.
	 *
	 * @return string
	 */
	public static function get_fee_notice( string $classes = '' ): string {

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
			return sprintf( '<p class="wpforms-square-notice-info wpforms-alert wpforms-alert-info ' . wpforms_sanitize_classes( $classes ) . '">%s</p>', $notice );
		}

		return sprintf( '<div class="wpforms-square-notice-info ' . wpforms_sanitize_classes( $classes ) . '"><p>%s</p></div>', $notice );
	}

	/**
	 * Get a fee notice for a non-active license.
	 *
	 * If the license is NOT set/activated, show the notice to activate it.
	 * Otherwise, show the notice to renew it.
	 *
	 * @since 1.9.5
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
					__( '<strong>Pay-as-you-go Pricing</strong><br>3%% fee per-transaction + Square fees. <a href="%s">Activate your license</a> to remove additional fees and unlock powerful features.', 'wpforms-lite' ),
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
				__( '<strong>Pay-as-you-go Pricing</strong><br> 3%% fee per-transaction + Square fees. <a href="%s">Renew your license</a> to remove additional fees and unlock powerful features.', 'wpforms-lite' ),
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
	 * @since 1.9.5
	 *
	 * @return string
	 */
	private static function get_non_pro_license_level_notice(): string {

		$utm_content  = 'Square Pro - Remove Fees';
		$utm_medium   = wpforms_is_admin_page( 'builder' ) ? 'Payment Settings' : 'Settings - Payments';
		$upgrade_link = wpforms()->is_pro() ? wpforms_admin_upgrade_link( $utm_medium, $utm_content ) : wpforms_utm_link( 'https://wpforms.com/lite-upgrade/', $utm_medium, $utm_content );

		return sprintf(
			wp_kses( /* translators: %s - WPForms.com Upgrade page URL. */
				__( '<strong>Pay-as-you-go Pricing</strong><br> 3%% fee per-transaction + Square fees. <a href="%s" target="_blank">Upgrade to Pro</a> to remove additional fees and unlock powerful features.', 'wpforms-lite' ),
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
