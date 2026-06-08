<?php

namespace WPForms\Integrations\PayPalCommerce\Admin;

use WPForms\Integrations\PayPalCommerce\Helpers;
use WPForms\Integrations\PayPalCommerce\Connection;
use WPForms\Integrations\PayPalCommerce\WebhooksHealthCheck;

/**
 * PayPal Commerce "Webhook Settings" section methods.
 *
 * @since 1.10.0
 */
class WebhookSettings {

	/**
	 * Register "PayPal webhooks" settings fields.
	 *
	 * @since 1.10.0
	 *
	 * @param array $settings Admin area settings list.
	 *
	 * @return array
	 */
	public function settings( array $settings ): array {

		$this->maybe_set_default_settings();

		// Do not display if the PayPal Commerce account is not connected.
		if ( ! Connection::get() ) {
			return $settings;
		}

		$settings['paypal-commerce-webhooks-enabled'] = [
			'id'      => 'paypal-commerce-webhooks-enabled',
			'name'    => esc_html__( 'Enable Webhooks', 'wpforms-lite' ),
			'type'    => 'toggle',
			'status'  => true,
			'default' => true,
			'desc'    => sprintf(
				wp_kses(
				/* translators: %s - WPForms.com URL for PayPal Commerce webhooks documentation. */
					__( 'PayPal uses webhooks to notify WPForms when events occur in your PayPal account. Please see <a href="%s" target="_blank" rel="noopener noreferrer">our documentation on PayPal webhooks</a> for full details.', 'wpforms-lite' ),
					[
						'a' => [
							'href'   => [],
							'target' => [],
							'rel'    => [],
						],
					]
				),
				esc_url( wpforms_utm_link( 'https://wpforms.com/docs/setting-up-paypal-commerce-webhooks/', 'PayPal Settings', 'Enable webhooks' ) )
			),
		];

		// Bail out if the $_GET parameter is not passed or webhooks configured and active.
		// Do not show for the new connections.
		if (
			( ! isset( $_GET['webhooks_settings'] ) || // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				! Helpers::is_legacy()
			) &&
			Helpers::is_webhook_configured() &&
			( new WebhooksHealthCheck() )->is_webhooks_active()
		) {
			return $settings;
		}

		$settings['paypal-commerce-webhooks-communication'] = [
			'id'      => 'paypal-commerce-webhooks-communication',
			'name'    => esc_html__( 'Webhooks Method', 'wpforms-lite' ),
			'type'    => 'radio',
			'default' => wpforms_setting( 'paypal-commerce-webhooks-communication', 'rest' ),
			'options' => [
				'rest' => esc_html__( 'REST API (recommended)', 'wpforms-lite' ),
				'curl' => esc_html__( 'PHP listener', 'wpforms-lite' ),
			],
			'desc'    => sprintf(
				wp_kses( /* translators: %s - WPForms.com URL for PayPal Commerce webhooks documentation. */
					__( 'Choose how PayPal delivers events to WPForms. If REST API support is disabled for WordPress, use the PHP listener fallback. <a href="%s" rel="nofollow noopener" target="_blank">Learn more</a>.', 'wpforms-lite' ),
					[
						'a' => [
							'href'   => [],
							'target' => [],
							'rel'    => [],
						],
					]
				),
				esc_url( wpforms_utm_link( 'https://wpforms.com/docs/setting-up-paypal-commerce-webhooks/', 'PayPal Settings', 'Webhook Listener' ) )
			),
			'class'   => $this->get_html_classes(),
		];

		$settings['paypal-commerce-webhooks-endpoint-set'] = [
			'id'       => 'paypal-commerce-webhooks-endpoint-set',
			'name'     => esc_html__( 'Webhooks Endpoint', 'wpforms-lite' ),
			'url'      => Helpers::get_webhook_url(),
			'type'     => 'webhook_endpoint',
			'provider' => 'paypal-commerce',
			'desc'     => sprintf(
				wp_kses(
				/* translators: %s - WPForms.com PayPal Commerce webhooks documentation url. */
					__( 'This is the endpoint WPForms expects PayPal to deliver events to. See <a href="%s" target="_blank" rel="noopener noreferrer">our PayPal webhooks guide</a> for details.', 'wpforms-lite' ),
					[
						'a' => [
							'href'   => [],
							'target' => [],
							'rel'    => [],
						],
					]
				),
				esc_url( wpforms_utm_link( 'https://wpforms.com/docs/setting-up-paypal-commerce-webhooks/', 'Settings - Payments', 'PayPal Commerce Webhooks Documentation' ) )
			),
			'class'    => $this->get_html_classes(),
		];

		$settings['paypal-commerce-webhooks-id-sandbox'] = [
			'id'       => 'paypal-commerce-webhooks-id-sandbox',
			'name'     => esc_html__( 'Webhooks Sandbox ID', 'wpforms-lite' ),
			'type'     => 'text',
			'desc'     => esc_html__( 'PayPal Commerce webhook ID created for the app. Created and configured automatically.', 'wpforms-lite' ),
			'class'    => $this->get_html_classes(),
			'readonly' => true,
		];

		$settings['paypal-commerce-webhooks-id-live'] = [
			'id'       => 'paypal-commerce-webhooks-id-live',
			'name'     => esc_html__( 'Webhooks Live ID', 'wpforms-lite' ),
			'type'     => 'text',
			'desc'     => esc_html__( 'PayPal Commerce webhook ID created for the app. Created and configured automatically.', 'wpforms-lite' ),
			'class'    => $this->get_html_classes(),
			'readonly' => true,
		];

		return $settings;
	}

	/**
	 * Get HTML classes for the Webhooks section.
	 *
	 * @since 1.10.0
	 *
	 * @return array
	 */
	private function get_html_classes(): array {

		$classes = [ 'wpforms-settings-paypal-commerce-webhooks' ];

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['webhooks_settings'] ) ) {
			return $classes;
		}

		if ( ! wpforms_setting( 'paypal-commerce-webhooks-enabled' ) ) {
			$classes[] = 'wpforms-hide';
		}

		return $classes;
	}

	/**
	 * Maybe set default settings.
	 *
	 * @since 1.10.0
	 */
	private function maybe_set_default_settings(): void {

		$settings   = (array) get_option( 'wpforms_settings', [] );
		$is_updated = false;

		// Enable PayPal webhooks by default if an account is connected.
		// phpcs:ignore WPForms.PHP.BackSlash.UseShortSyntax
		if ( ! isset( $settings['paypal-commerce-webhooks-enabled'] ) && Connection::get() ) {
			$settings['paypal-commerce-webhooks-enabled'] = true;

			$is_updated = true;
		}

		// Set a default communication method.
		if ( ! isset( $settings['paypal-commerce-webhooks-communication'] ) ) {
			$settings['paypal-commerce-webhooks-communication'] = $this->is_rest_api_enabled() ? 'rest' : 'curl';

			$is_updated = true;
		}

		// Save settings only if something is changed.
		if ( $is_updated ) {
			update_option( 'wpforms_settings', $settings );
		}
	}

	/**
	 * Check if the REST API is enabled.
	 *
	 * Test configured webhook endpoint with a non-authorised request.
	 * Based on UsageTracking::is_rest_api_enabled().
	 *
	 * @since 1.10.0
	 *
	 * @return bool
	 */
	private function is_rest_api_enabled(): bool {

		// phpcs:disable WPForms.PHP.ValidateHooks.InvalidHookName
		/** This filter is documented in wp-includes/class-wp-http-streams.php */
		$sslverify = apply_filters( 'https_local_ssl_verify', false );
		// phpcs:enable WPForms.PHP.ValidateHooks.InvalidHookName

		$url      = add_query_arg( [ 'verify' => 1 ], Helpers::get_webhook_url_for_rest() );
		$response = wp_remote_get(
			$url,
			[
				'timeout'   => 10,
				'cookies'   => [],
				'sslverify' => $sslverify,
				'headers'   => [
					'Cache-Control' => 'no-cache',
				],
			]
		);

		// When testing the REST API, an error was encountered, leave early.
		if ( is_wp_error( $response ) ) {
			return false;
		}

		// When testing the REST API, an unexpected result was returned, leave early.
		if ( wp_remote_retrieve_response_code( $response ) !== 200 ) {
			return false;
		}

		// The REST API did not behave correctly, leave early.
		if ( ! wpforms_is_json( wp_remote_retrieve_body( $response ) ) ) {
			return false;
		}

		// We are all set. Confirm the connection.
		return true;
	}
}
