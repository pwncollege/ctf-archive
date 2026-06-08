<?php

namespace WPForms\Integrations\PayPalCommerce\Admin\Builder;

use WPForms\Integrations\PayPalCommerce\Helpers;
use WPForms\Integrations\PayPalCommerce\PayPalCommerce;

/**
 * PayPalCommerce Form Builder notifications-related functionality.
 *
 * @since 1.10.0
 */
class Notifications {

	/**
	 * Initialize.
	 *
	 * @since 1.10.0
	 */
	public function init(): void {

		$this->hooks();
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.10.0
	 */
	private function hooks(): void {

		if ( wpforms()->is_pro() ) {
			add_action( 'wpforms_form_settings_notifications_single_after', [ $this, 'notification_settings' ], 5, 2 );

			return;
		}

		add_action( 'wpforms_lite_form_settings_notifications_block_content_after', [ $this, 'notification_settings' ], 5, 2 );
	}

	/**
	 * Add checkbox to form notification settings.
	 *
	 * @since 1.10.0
	 *
	 * @param object|mixed $settings Current confirmation settings.
	 * @param int          $id       Subsection ID.
	 */
	public function notification_settings( $settings, int $id ): void {

		if ( empty( $settings->form_data ) ) {
			return;
		}

		wpforms_panel_field(
			'toggle',
			'notifications',
			PayPalCommerce::SLUG,
			$settings->form_data,
			esc_html__( 'Enable for PayPalCommerce completed payments', 'wpforms-lite' ),
			$this->get_notification_settings_data( $settings->form_data, $id )
		);
	}

	/**
	 * Get notification settings data based on the license type.
	 *
	 * @since 1.10.0
	 *
	 * @param array $form_data Form settings data.
	 * @param int   $id        Subsection ID.
	 *
	 * @return array
	 */
	private function get_notification_settings_data( array $form_data, int $id ): array {

		return [
			'parent'      => 'settings',
			'class'       => ! Helpers::is_paypal_commerce_enabled( $form_data ) ? 'wpforms-hidden' : '',
			'subsection'  => $id,
			'value'       => 0,
			'input_class' => 'education-modal',
			'pro_badge'   => ! Helpers::is_allowed_license_type(),
			'data'        => $this->get_notification_section_data(),
			'attrs'       => [ 'disabled' => 'disabled' ],
		];
	}

	/**
	 * Get notification section data.
	 *
	 * @since 1.10.0
	 *
	 * @return array
	 */
	private function get_notification_section_data(): array {

		$addon = wpforms()->obj( 'addons' )->get_addon( 'paypal-commerce' );

		if (
			empty( $addon ) ||
			empty( $addon['action'] ) ||
			empty( $addon['status'] ) || (
				$addon['status'] === 'active' &&
				$addon['action'] !== 'upgrade'
			)
		) {
			return [];
		}

		if ( $addon['plugin_allow'] && $addon['action'] === 'install' ) {
			return [
				'action'  => 'install',
				'message' => esc_html__( 'The PayPal Commerce Pro addon is required to enable notification for completed payments. Would you like to install and activate it?', 'wpforms-lite' ),
				'url'     => $addon['url'],
				'nonce'   => wp_create_nonce( 'wpforms-admin' ),
				'license' => 'pro',
			];
		}

		if ( $addon['plugin_allow'] && $addon['action'] === 'activate' ) {
			return [
				'action'  => 'activate',
				'message' => esc_html__( 'The PayPal Commerce Pro addon is required to enable notification for completed payments. Would you like to activate it?', 'wpforms-lite' ),
				'path'    => $addon['path'],
				'nonce'   => wp_create_nonce( 'wpforms-admin' ),
			];
		}

		return [
			'action'      => 'upgrade',
			'name'        => esc_html__( 'Notification for PayPal Commerce Completed Payments', 'wpforms-lite' ),
			'utm-content' => 'Builder PayPal Commerce Completed Payments',
			'license'     => 'pro',
		];
	}
}
