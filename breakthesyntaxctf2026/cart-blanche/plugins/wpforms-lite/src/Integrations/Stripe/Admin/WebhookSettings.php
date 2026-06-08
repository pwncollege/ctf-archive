<?php

namespace WPForms\Integrations\Stripe\Admin;

use WPForms\Integrations\Stripe\Helpers;
use WPForms\Integrations\Stripe\WebhooksHealthCheck;

/**
 * Stripe "Webhook Settings" section methods.
 *
 * @since 1.8.4
 */
class WebhookSettings {

	/**
	 * Initialization.
	 *
	 * @since 1.8.4
	 *
	 * @return WebhookSettings
	 */
	public function init() {

		return $this;
	}

	/**
	 * Register "Stripe webhooks" settings fields.
	 *
	 * @since 1.8.4
	 *
	 * @param array $settings Admin area settings list.
	 *
	 * @return array
	 */
	public function settings( $settings ) {

		$this->maybe_set_default_settings();

		// Do not display it as long as Stripe account is not connected.
		if ( ! Helpers::has_stripe_keys() ) {
			return $settings;
		}

		$settings['stripe-webhooks-enabled'] = [
			'id'      => 'stripe-webhooks-enabled',
			'name'    => esc_html__( 'Enable Webhooks', 'wpforms-lite' ),
			'type'    => 'toggle',
			'status'  => true,
			'default' => true,
			'desc'    => sprintf(
				wp_kses( /* translators: %s - WPForms.com URL for Stripe webhooks documentation. */
					__( 'Stripe uses webhooks to notify WPForms when an event has occurred in your Stripe account. Please see <a href="%s" target="_blank" rel="noopener noreferrer">our documentation on Stripe webhooks</a> for full details.', 'wpforms-lite' ),
					[
						'a' => [
							'href'   => [],
							'target' => [],
							'rel'    => [],
						],
					]
				),
				esc_url( wpforms_utm_link( 'https://wpforms.com/docs/setting-up-stripe-webhooks/', 'Stripe Settings', 'Enable webhooks' ) )
			),
		];

		// Bail out if $_GET parameter is not passed or webhooks is configured and active.
		if (
			! isset( $_GET['webhooks_settings'] ) && // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			Helpers::is_webhook_configured() &&
			( new WebhooksHealthCheck() )->is_webhooks_active()
		) {
			return $settings;
		}

		$settings['stripe-webhooks-communication'] = [
			'id'      => 'stripe-webhooks-communication',
			'name'    => esc_html__( 'Webhooks Method', 'wpforms-lite' ),
			'type'    => 'radio',
			'default' => wpforms_setting( 'stripe-webhooks-communication', 'rest' ),
			'options' => [
				'rest' => esc_html__( 'REST API (recommended)', 'wpforms-lite' ),
				'curl' => esc_html__( 'PHP listener', 'wpforms-lite' ),
			],
			'desc'    => sprintf(
				wp_kses( /* translators: %s - WPForms.com URL for Stripe webhooks documentation. */
					__( 'Choose the method of communication between Stripe and WPForms. If REST API support is disabled for WordPress, use PHP listener. <a href="%s" rel="nofollow noopener" target="_blank">Learn more</a>.', 'wpforms-lite' ),
					[
						'a' => [
							'href'   => [],
							'target' => [],
							'rel'    => [],
						],
					]
				),
				esc_url( wpforms_utm_link( 'https://wpforms.com/docs/setting-up-stripe-webhooks/', 'Stripe Settings', 'Webhook Listener' ) )
			),
			'class'   => $this->get_html_classes(),
		];

		$settings['stripe-webhooks-endpoint-set'] = [
			'id'    => 'stripe-webhooks-endpoint-set',
			'name'  => esc_html__( 'Webhooks Endpoint', 'wpforms-lite' ),
			'url'   => Helpers::get_webhook_url(),
			'type'  => 'webhook_endpoint',
			'desc'  => sprintf(
				wp_kses( /* translators: %s - Stripe Webhooks Settings url. */
					__( 'Ensure an endpoint with the above URL is present in the <a href="%s" target="_blank" rel="noopener noreferrer">Stripe webhook settings</a>.', 'wpforms-lite' ),
					[
						'a' => [
							'href'   => [],
							'target' => [],
							'rel'    => [],
						],
					]
				),
				Helpers::get_stripe_mode() === 'test' ? 'https://dashboard.stripe.com/test/workbench/webhooks/create' : 'https://dashboard.stripe.com/workbench/webhooks/create'
			),
			'class' => $this->get_html_classes(),
		];

		$settings['stripe-webhooks-id-test'] = [
			'id'    => 'stripe-webhooks-id-test',
			'name'  => esc_html__( 'Webhooks Test ID', 'wpforms-lite' ),
			'type'  => 'text',
			'desc'  => $this->get_webhooks_id_desc( 'test' ),
			'class' => $this->get_html_classes(),
		];

		$settings['stripe-webhooks-secret-test'] = [
			'id'    => 'stripe-webhooks-secret-test',
			'name'  => esc_html__( 'Webhooks Test Secret', 'wpforms-lite' ),
			'type'  => 'password',
			'desc'  => $this->get_webhooks_secret_desc( 'test' ),
			'class' => $this->get_html_classes(),
		];

		$settings['stripe-webhooks-id-live'] = [
			'id'    => 'stripe-webhooks-id-live',
			'name'  => esc_html__( 'Webhooks Live ID', 'wpforms-lite' ),
			'type'  => 'text',
			'desc'  => $this->get_webhooks_id_desc( 'live' ),
			'class' => $this->get_html_classes(),
		];

		$settings['stripe-webhooks-secret-live'] = [
			'id'    => 'stripe-webhooks-secret-live',
			'name'  => esc_html__( 'Webhooks Live Secret', 'wpforms-lite' ),
			'type'  => 'password',
			'desc'  => $this->get_webhooks_secret_desc( 'live' ),
			'class' => $this->get_html_classes(),
		];

		return $settings;
	}

	/**
	 * Show the link to the documentation about the Webhooks ID.
	 *
	 * @since 1.8.4
	 *
	 * @param string $mode Stripe mode (e.g. 'live' or 'test').
	 *
	 * @return string
	 */
	private function get_webhooks_id_desc( $mode ) {

		$modes = [
			'live' => __( 'Live Mode Endpoint ID', 'wpforms-lite' ),
			'test' => __( 'Test Mode Endpoint ID', 'wpforms-lite' ),
		];

		return sprintf(
			wp_kses( /* translators: %1$s - Live Mode Endpoint ID or Test Mode Endpoint ID. %2$s - WPForms.com Stripe documentation article URL. */
				__( 'Retrieve your %1$s from your <a href="%2$s" target="_blank" rel="noopener noreferrer">Stripe webhook settings</a>. Select the endpoint, then click Copy button.', 'wpforms-lite' ),
				[
					'a' => [
						'href'   => [],
						'target' => [],
						'rel'    => [],
					],
				]
			),
			$modes[ $mode ],
			$mode === 'test' ? 'https://dashboard.stripe.com/test/workbench/webhooks' : 'https://dashboard.stripe.com/workbench/webhooks'
		);
	}

	/**
	 * Show the link to the documentation about the Webhooks Secret.
	 *
	 * @since 1.8.4
	 *
	 * @param string $mode Stripe mode (e.g. 'live' or 'test').
	 *
	 * @return string
	 */
	private function get_webhooks_secret_desc( $mode ) {

		$modes = [
			'live' => __( 'Live Mode Signing Secret', 'wpforms-lite' ),
			'test' => __( 'Test Mode Signing Secret', 'wpforms-lite' ),
		];

		return sprintf(
			wp_kses( /* translators: %1$s - Live Mode Signing Secret or Test Mode Signing Secret. %2$s - WPForms.com Stripe documentation article URL. */
				__( 'Retrieve your %1$s from your <a href="%2$s" target="_blank" rel="noopener noreferrer">Stripe webhook settings</a>. Select the endpoint, then click Reveal.', 'wpforms-lite' ),
				[
					'a' => [
						'href'   => [],
						'target' => [],
						'rel'    => [],
					],
				]
			),
			$modes[ $mode ],
			$mode === 'test' ? 'https://dashboard.stripe.com/test/workbench/webhooks' : 'https://dashboard.stripe.com/workbench/webhooks'
		);
	}

	/**
	 * Get HTML classes for the Webhooks section.
	 *
	 * @since 1.8.4
	 *
	 * @return array
	 */
	private function get_html_classes() {

		$classes = [ 'wpforms-settings-stripe-webhooks' ];

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['webhooks_settings'] ) ) {
			return $classes;
		}

		if ( ! wpforms_setting( 'stripe-webhooks-enabled' ) ) {
			$classes[] = 'wpforms-hide';
		}

		return $classes;
	}

	/**
	 * Maybe set default settings.
	 *
	 * @since 1.8.4
	 */
	private function maybe_set_default_settings() {

		$settings   = (array) get_option( 'wpforms_settings', [] );
		$is_updated = false;

		// Enable Stripe webhooks by default if account is connected.
		// phpcs:ignore WPForms.PHP.BackSlash.UseShortSyntax
		if ( ! isset( $settings['stripe-webhooks-enabled'] ) && Helpers::has_stripe_keys() ) {
			$settings['stripe-webhooks-enabled'] = true;

			$is_updated = true;
		}

		// Set a default communication method.
		if ( ! isset( $settings['stripe-webhooks-communication'] ) ) {
			$settings['stripe-webhooks-communication'] = $this->is_rest_api_enabled() ? 'rest' : 'curl';

			$is_updated = true;
		}

		// Save settings only if something is changed.
		if ( $is_updated ) {
			update_option( 'wpforms_settings', $settings );
		}
	}

	/**
	 * Check if REST API is enabled.
	 *
	 * Testing configured webhook endpoint with non-authorised request.
	 * Based on UsageTracking::is_rest_api_enabled().
	 *
	 * @since 1.8.4
	 *
	 * @return bool
	 */
	private function is_rest_api_enabled() {

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
