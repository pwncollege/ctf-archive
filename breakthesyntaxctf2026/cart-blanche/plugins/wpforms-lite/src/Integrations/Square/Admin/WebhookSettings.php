<?php

namespace WPForms\Integrations\Square\Admin;

use WPForms\Integrations\Square\Helpers;

/**
 * Square "Webhook Settings" section methods.
 *
 * @since 1.9.5
 */
class WebhookSettings {

	/**
	 * Square Apps URL.
	 *
	 * @since 1.9.5
	 */
	public const SQUARE_APPS_URL = 'https://developer.squareup.com/apps';

	/**
	 * Initialization.
	 *
	 * @since 1.9.5
	 *
	 * @return WebhookSettings
	 */
	public function init(): WebhookSettings {

		return $this;
	}

	/**
	 * Register "Square webhooks" settings fields.
	 *
	 * @since 1.9.5
	 *
	 * @param array $settings Admin area settings list.
	 *
	 * @return array
	 */
	public function settings( $settings ): array {

		$settings = (array) $settings;

		$this->maybe_set_default_settings();

		// Do not display it as long as a Square account is not connected.
		if ( ! Helpers::is_square_configured() ) {
			return $settings;
		}

		$settings['payments']['square-webhooks-enabled'] = [
			'id'      => 'square-webhooks-enabled',
			'name'    => esc_html__( 'Enable Webhooks', 'wpforms-lite' ),
			'type'    => 'toggle',
			'status'  => true,
			'default' => true,
			'desc'    => sprintf(
				wp_kses( /* translators: %s - WPForms.com URL for Square webhooks documentation. */
					__( 'Square uses webhooks to notify WPForms when an event has occurred in your Square account. Please see <a href="%s" target="_blank" rel="noopener noreferrer">our documentation on Square webhooks</a> for full details.', 'wpforms-lite' ),
					[
						'a' => [
							'href'   => [],
							'target' => [],
							'rel'    => [],
						],
					]
				),
				esc_url( wpforms_utm_link( 'https://wpforms.com/docs/setting-up-square-webhooks/', 'Settings - Payments', 'Square Webhooks Documentation' ) )
			),
		];

		$mode = Helpers::get_mode();

		$settings['payments'][ 'square-webhooks-connect-status-' . $mode ] = [
			'id'      => 'square-webhooks-connect-status-' . $mode,
			'name'    => '',
			'content' => $this->get_webhook_connection_notice(),
			'type'    => 'content',
			'class'   => Helpers::is_webhook_enabled() && ! Helpers::is_webhook_configured() ? '' : 'wpforms-hide',
		];

		$settings['payments']['square-webhooks-connect'] = [
			'id'      => 'square-webhooks-connect',
			'name'    => '',
			'content' => $this->get_connect_button(),
			'type'    => 'content',
			'class'   => ! Helpers::is_webhook_enabled() ? 'wpforms-hide' : '',
		];

		// Bail out if $_GET parameter is not passed.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! isset( $_GET['webhooks_settings'] ) ) {
			return $settings;
		}

		$settings['payments']['square-webhooks-communication'] = [
			'id'      => 'square-webhooks-communication',
			'name'    => esc_html__( 'Webhooks Method', 'wpforms-lite' ),
			'type'    => 'radio',
			'default' => wpforms_setting( 'square-webhooks-communication', 'rest' ),
			'options' => [
				'rest' => esc_html__( 'REST API (recommended)', 'wpforms-lite' ),
				'curl' => esc_html__( 'PHP listener', 'wpforms-lite' ),
			],
			'desc'    => sprintf(
				wp_kses( /* translators: %s - WPForms.com URL for Square webhooks documentation. */
					__( 'Choose the method of communication between Square and WPForms. If REST API support is disabled for WordPress, use PHP listener. <a href="%s" rel="nofollow noopener" target="_blank">Learn more</a>.', 'wpforms-lite' ),
					[
						'a' => [
							'href'   => [],
							'target' => [],
							'rel'    => [],
						],
					]
				),
				esc_url( wpforms_utm_link( 'https://wpforms.com/docs/setting-up-square-webhooks/', 'Settings - Payments', 'Square Webhooks Documentation' ) )
			),
			'class'   => $this->get_html_classes(),
		];

		$settings['payments']['square-webhooks-communication-status'] = [
			'id'      => 'square-webhooks-communication-status',
			'name'    => '',
			'content' => $this->display_webhooks_communication_notice(),
			'type'    => 'content',
			'class'   => 'wpforms-hide',
		];

		$settings['payments']['square-webhooks-endpoint-set'] = [
			'id'       => 'square-webhooks-endpoint-set',
			'name'     => esc_html__( 'Webhooks Endpoint', 'wpforms-lite' ),
			'url'      => Helpers::get_webhook_url(),
			'type'     => 'webhook_endpoint',
			'provider' => 'square',
			'desc'     => sprintf(
				wp_kses( /* translators: %s - Square Dashboard Webhooks Settings URL. */
					__( 'Ensure an endpoint with the above URL is present in the <a href="%s" target="_blank" rel="noopener noreferrer">Square webhook settings</a>.', 'wpforms-lite' ),
					[
						'a' => [
							'href'   => [],
							'target' => [],
							'rel'    => [],
						],
					]
				),
				esc_url( self::SQUARE_APPS_URL )
			),
			'class'    => $this->get_html_classes(),
		];

		$settings['payments']['square-webhooks-id-sandbox'] = [
			'id'    => 'square-webhooks-id-sandbox',
			'name'  => esc_html__( 'Webhooks Test ID', 'wpforms-lite' ),
			'type'  => 'text',
			'desc'  => $this->get_webhooks_id_desc( 'sandbox' ),
			'class' => $this->get_html_classes(),
		];

		$settings['payments']['square-webhooks-secret-sandbox'] = [
			'id'    => 'square-webhooks-secret-sandbox',
			'name'  => esc_html__( 'Webhooks Test Signature Key', 'wpforms-lite' ),
			'type'  => 'password',
			'desc'  => $this->get_webhooks_secret_desc( 'sandbox' ),
			'class' => $this->get_html_classes(),
		];

		$settings['payments']['square-webhooks-id-live'] = [
			'id'    => 'square-webhooks-id-live',
			'name'  => esc_html__( 'Webhooks Live ID', 'wpforms-lite' ),
			'type'  => 'text',
			'desc'  => $this->get_webhooks_id_desc( 'live' ),
			'class' => $this->get_html_classes(),
		];

		$settings['payments']['square-webhooks-secret-live'] = [
			'id'    => 'square-webhooks-secret-live',
			'name'  => esc_html__( 'Webhooks Live Signature Key', 'wpforms-lite' ),
			'type'  => 'password',
			'desc'  => $this->get_webhooks_secret_desc( 'live' ),
			'class' => $this->get_html_classes(),
		];

		return $settings;
	}

	/**
	 * Get the Webhook connection notice.
	 *
	 * @since 1.9.5
	 *
	 * @return string
	 */
	private function get_webhook_connection_notice(): string {

		if ( ! Helpers::is_webhook_enabled() ) {
			return '';
		}

		if ( Helpers::is_webhook_configured() ) {
			return '';
		}

		return sprintf(
			'<div class="wpforms-notice notice-error"><p><span class="wpforms-error-icon"></span>%1$s</p></div>',
			esc_html__( 'Webhooks are enabled, but not yet connected.', 'wpforms-lite' )
		);
	}

	/**
	 * Display the Webhooks communication notice. The notice is displayed when a user tries to change communication method manually
	 * and needs to be informed that the Webhook URL should be updated after that accordingly.
	 *
	 * @since 1.9.5
	 *
	 * @return string
	 */
	private function display_webhooks_communication_notice(): string {

		return sprintf(
			'<div class="wpforms-notice notice-warning"><p>%1$s</p></div>',
			esc_html__( 'Make sure that Webhooks Endpoint is updated inside the Square app after Webhooks Method switch.', 'wpforms-lite' )
		);
	}

	/**
	 * Show the link to the documentation about the Webhooks ID.
	 *
	 * @since 1.9.5
	 *
	 * @param string $mode Square mode (e.g. 'live' or 'sandbox').
	 *
	 * @return string
	 */
	private function get_webhooks_id_desc( string $mode ): string {

		$modes = [
			'live'    => __( 'Live Mode Endpoint Subscription ID', 'wpforms-lite' ),
			'sandbox' => __( 'Test Mode Endpoint Subscription ID', 'wpforms-lite' ),
		];

		return sprintf(
			wp_kses( /* translators: %1$s - Live Mode Endpoint ID or Test Mode Endpoint ID. %2$s - Square Dashboard Webhooks Settings URL. */
				__( 'Retrieve your %1$s from your <a href="%2$s" target="_blank" rel="noopener noreferrer">Square webhook settings</a>. Select the endpoint, then click Copy button.', 'wpforms-lite' ),
				[
					'a' => [
						'href'   => [],
						'target' => [],
						'rel'    => [],
					],
				]
			),
			$modes[ $mode ],
			esc_url( self::SQUARE_APPS_URL )
		);
	}

	/**
	 * Show the link to the documentation about the Webhook Signature Key.
	 *
	 * @since 1.9.5
	 *
	 * @param string $mode Square mode (e.g. 'live' or 'sandbox').
	 *
	 * @return string
	 */
	private function get_webhooks_secret_desc( string $mode ): string {

		$modes = [
			'live'    => __( 'Live Mode Signature Key', 'wpforms-lite' ),
			'sandbox' => __( 'Test Mode Signature Key', 'wpforms-lite' ),
		];

		return sprintf(
			wp_kses( /* translators: %1$s - Live Mode Signing Secret or Test Mode Signing Secret. %2$s - Square Dashboard Webhooks Settings URL. */
				__( 'Retrieve your %1$s from your <a href="%2$s" target="_blank" rel="noopener noreferrer">Square webhook settings</a>. Select the endpoint, then click Reveal.', 'wpforms-lite' ),
				[
					'a' => [
						'href'   => [],
						'target' => [],
						'rel'    => [],
					],
				]
			),
			$modes[ $mode ],
			esc_url( self::SQUARE_APPS_URL )
		);
	}

	/**
	 * Get HTML classes for the Webhooks section.
	 *
	 * @since 1.9.5
	 *
	 * @return array
	 */
	private function get_html_classes(): array {

		$classes = [ 'wpforms-settings-square-webhooks' ];

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['webhooks_settings'] ) ) {
			return $classes;
		}

		if ( ! wpforms_setting( 'square-webhooks-enabled' ) ) {
			$classes[] = 'wpforms-hide';
		}

		return $classes;
	}

	/**
	 * Maybe set default settings.
	 *
	 * @since 1.9.5
	 */
	private function maybe_set_default_settings() {

		$settings   = (array) get_option( 'wpforms_settings', [] );
		$is_updated = false;

		// Enable Square webhooks by default if an account is connected.
		if ( ! isset( $settings['square-webhooks-enabled'] ) && Helpers::is_square_configured() ) {
			$settings['square-webhooks-enabled'] = true;

			$is_updated = true;
		}

		// Set a default communication method.
		if ( ! isset( $settings['square-webhooks-communication'] ) ) {
			$settings['square-webhooks-communication'] = $this->is_rest_api_enabled() ? 'rest' : 'curl';

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
	 * @since 1.9.5
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

	/**
	 * Get the "Connect Webhooks" button.
	 *
	 * @since 1.9.5
	 *
	 * @return string
	 */
	private function get_connect_button(): string {

		$is_connected = Helpers::is_webhook_enabled() && Helpers::is_webhook_configured();

		if ( $is_connected ) {
			return sprintf(
				'<div><span class="%s"></span>%s</div>',
				esc_attr( 'wpforms-success-icon' ),
				esc_html__( 'Webhooks are connected and active.', 'wpforms-lite' )
			);
		}

		$button = sprintf(
			'<button class="wpforms-btn wpforms-btn-md wpforms-btn-blue" type="button" id="wpforms-setting-square-webhooks-connect" title="%1$s">%2$s</button>',
			esc_attr__( 'Press here to see the further instructions.', 'wpforms-lite' ),
			esc_html__( 'Connect Webhooks', 'wpforms-lite' )
		);

		$description = sprintf(
			'<p class="desc">%s</p>',
			wp_kses(
			/* translators: %s - WPForms.com URL for Square webhooks documentation. */
				__( 'To start using webhooks, please register a webhook route inside our application. You can do this by pressing the button above or setting the credentials manually. Please see <a href="%1$s" target="_blank">our documentation on Square webhooks</a> for full details.', 'wpforms-lite' ),
				[
					'a' => [
						'href'   => [],
						'target' => [],
					],
				]
			)
		);

		$description = sprintf( $description, esc_url( wpforms_utm_link( 'https://wpforms.com/docs/setting-up-square-webhooks/', 'Settings - Payments', 'Square Webhooks Documentation' ) ) );

		return $button . $description;
	}
}
