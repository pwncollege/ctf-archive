<?php

namespace WPForms\Integrations\PayPalCommerce\Admin;

use WPForms\Integrations\PayPalCommerce\Connection;
use WPForms\Integrations\PayPalCommerce\Helpers;
use WPForms\Integrations\PayPalCommerce\PayPalCommerce;

/**
 * PayPal Commerce Settings.
 *
 * @since 1.10.0
 */
class Settings {

	/**
	 * PayPalCommerce Webhook Settings.
	 *
	 * @since 1.10.0
	 *
	 * @var WebhookSettings
	 */
	protected $webhook_settings;

	/**
	 * Init class.
	 *
	 * @since 1.10.0
	 */
	public function init(): void {

		$this->webhook_settings = new WebhookSettings();

		$this->hooks();
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.10.0
	 */
	private function hooks(): void {

		add_action( 'wpforms_settings_enqueue',  [ $this, 'enqueue_assets' ] );
		add_filter( 'wpforms_admin_strings',     [ $this, 'javascript_strings' ] );

		add_filter( 'wpforms_settings_defaults', [ $this, 'register' ], 10 );
	}

	/**
	 * Enqueue Settings assets.
	 *
	 * @since 1.10.0
	 */
	public function enqueue_assets(): void {

		$min = wpforms_get_min_suffix();

		wp_enqueue_style(
			'wpforms-admin-settings-paypal-commerce',
			WPFORMS_PLUGIN_URL . "assets/css/integrations/paypal-commerce/admin-settings-paypal-commerce{$min}.css",
			[],
			WPFORMS_VERSION
		);

		wp_enqueue_script(
			'wpforms-admin-settings-paypal-commerce',
			WPFORMS_PLUGIN_URL . "assets/js/integrations/paypal-commerce/settings-paypal-commerce{$min}.js",
			[ 'jquery' ],
			WPFORMS_VERSION,
			true
		);

		wp_enqueue_script(
			'wpforms-paypal-commerce-partner-js',
			'https://www.paypal.com/webapps/merchantboarding/js/lib/lightbox/partner.js',
			[],
			WPFORMS_VERSION,
			true
		);
	}

	/**
	 * Localize needed strings.
	 *
	 * @since 1.10.0
	 *
	 * @param array $strings JS strings.
	 *
	 * @return array
	 *
	 * @noinspection PhpMissingParamTypeInspection
	 */
	public function javascript_strings( $strings ): array {

		$strings = (array) $strings;

		$strings[ PayPalCommerce::SLUG ] = [
			'mode_update'      => wp_kses(
				__(
					'<p>Switching sandbox/live modes requires PayPal Commerce account reconnection.</p><p>Press the <em>"Connect with PayPal Commerce"</em> button after saving the settings to reconnect.</p>',
					'wpforms-lite'
				),
				[
					'p'  => [],
					'em' => [],
				]
			),
			'connection_error' => esc_html__( 'Something went wrong while performing the authorization request.', 'wpforms-lite' ),
			'webhook_urls'     => [
				'rest' => Helpers::get_webhook_url_for_rest(),
				'curl' => Helpers::get_webhook_url_for_curl(),
			],
		];

		return $strings;
	}

	/**
	 * Register Settings fields.
	 *
	 * @since 1.10.0
	 *
	 * @param array $settings Array of current form settings.
	 *
	 * @return array
	 *
	 * @noinspection PhpMissingParamTypeInspection
	 */
	public function register( $settings ): array {

		$settings = (array) $settings;

		// Ensure payments section exists.
		if ( ! isset( $settings['payments'] ) ) {
			return $settings;
		}

		$ppc_settings = [
			'paypal-commerce-heading' => [
				'id'       => 'paypal-commerce-heading',
				'content'  => $this->get_heading_content(),
				'type'     => 'content',
				'no_label' => true,
				'class'    => [ 'section-heading' ],
			],
		];

		foreach ( Helpers::get_available_modes() as $mode ) {

			$ppc_settings[ 'paypal-commerce-connection-status-' . $mode ] = [
				'id'        => 'paypal-commerce-connection-status-' . $mode,
				'name'      => esc_html__( 'Connection Status', 'wpforms-lite' ),
				'content'   => $this->get_connection_status_content( $mode ),
				'type'      => 'content',
				'is_hidden' => Helpers::get_mode() !== $mode,
			];

			$is_merchant_info_visible = Helpers::get_mode() === $mode && Connection::get( $mode );

			$ppc_settings[ 'paypal-commerce-connection-merchant-email-' . $mode ] = [
				'id'        => 'paypal-commerce-connection-merchant-email-' . $mode,
				'name'      => esc_html__( 'Primary Email', 'wpforms-lite' ),
				'content'   => $this->get_connected_merchant_email( $mode ),
				'type'      => 'content',
				'is_hidden' => ! $is_merchant_info_visible,
			];

			$ppc_settings[ 'paypal-commerce-connection-merchant-id-' . $mode ] = [
				'id'        => 'paypal-commerce-connection-merchant-id-' . $mode,
				'name'      => esc_html__( 'Account ID', 'wpforms-lite' ),
				'content'   => $this->get_connected_merchant_id( $mode ),
				'type'      => 'content',
				'is_hidden' => ! $is_merchant_info_visible,
			];

			$ppc_settings[ 'paypal-commerce-connection-merchant-granted-scopes-' . $mode ] = [
				'id'        => 'paypal-commerce-connection-merchant-granted-scopes-' . $mode,
				'name'      => esc_html__( 'Scopes Granted', 'wpforms-lite' ),
				'content'   => $this->get_connected_merchant_granted_scopes( $mode ),
				'type'      => 'content',
				'is_hidden' => ! $is_merchant_info_visible,
			];
		}

		$ppc_settings['paypal-commerce-sandbox-mode'] = [
			'id'     => 'paypal-commerce-sandbox-mode',
			'name'   => esc_html__( 'Test Mode', 'wpforms-lite' ),
			'desc'   => sprintf(
				wp_kses( /* translators: %s - WPForms.com URL for PayPal Commerce payment with more details. */
					__( 'Prevent PayPal Commerce from processing live transactions. <a href="%s" target="_blank" rel="noopener noreferrer" class="wpforms-learn-more">Learn More</a>', 'wpforms-lite' ),
					[
						'a' => [
							'href'   => [],
							'target' => [],
							'rel'    => [],
							'class'  => [],
						],
					]
				),
				wpforms_utm_link( 'https://wpforms.com/docs/testing-payments-with-the-paypal-commerce-addon/', 'Payment Settings', 'PayPal Commerce Test Mode' )
			),
			'type'   => 'toggle',
			'status' => true,
		];

		$ppc_settings = $this->webhook_settings->settings( $ppc_settings );

		$settings['payments'] = array_merge( $settings['payments'], $ppc_settings );

		return $settings;
	}

	/**
	 * Retrieve a section header content.
	 *
	 * @since 1.10.0
	 *
	 * @return string
	 */
	private function get_heading_content(): string {

		return '<h4>' . esc_html__( 'PayPal Commerce', 'wpforms-lite' ) . '</h4><p>' .
			sprintf(
				wp_kses( /* translators: %s - WPForms.com PayPal Commerce documentation article URL. */
					__( 'Easily collect PayPal Checkout and credit card payments with PayPal Commerce. To get started, see our <a href="%s" target="_blank" rel="noopener noreferrer">PayPal Commerce documentation</a>.', 'wpforms-lite' ),
					[
						'a' => [
							'href'   => [],
							'target' => [],
							'rel'    => [],
						],
					]
				),
				wpforms_utm_link( 'https://wpforms.com/docs/paypal-commerce-addon/#install', 'Payment Settings', 'PayPal Commerce Documentation' )
			) .
			'</p>' .
			Notices::get_fee_notice();
	}

	/**
	 * Retrieve a Connection Status setting content.
	 *
	 * @since 1.10.0
	 *
	 * @param string $mode PayPal Commerce mode.
	 *
	 * @return string
	 */
	private function get_connection_status_content( string $mode ): string {

		$connection = Connection::get( $mode );

		if ( ! $connection ) {
			return $this->get_disconnected_status_content( $mode );
		}

		$content = $this->get_disabled_status_content( $connection );

		if ( ! empty( $content ) ) {
			return $content;
		}

		return $this->get_enabled_status_content( $mode );
	}

	/**
	 * Retrieve setting content when a connection is disabled.
	 *
	 * @since 1.10.0
	 *
	 * @param Connection|\WPFormsPaypalCommerce\Connection $connection Connection data.
	 *
	 * @return string
	 */
	private function get_disabled_status_content( $connection ): string {

		if ( ! $connection->get_access_token() ) {
			return $this->get_missing_access_token_content( $connection->get_mode() );
		}

		if ( ! $connection->get_client_token() ) {
			return $this->get_missing_client_token_content( $connection->get_mode() );
		}

		if ( ! $connection->is_configured() ) {
			return $this->get_missing_status_content( $connection->get_mode() );
		}

		// First party connection is not allowed to be used without Pro addon.
		if ( Helpers::is_legacy() && ! Helpers::is_pro() ) {
			$connection->set_status( Connection::STATUS_INVALID )->save();

			return $this->get_invalid_status_content( $connection );
		}

		if ( ! $connection->is_valid() ) {
			// Try to validate the connection.
			$merchant_info = $this->get_merchant_info( $connection->get_mode() );
			$status        = $connection->validate_permissions( $merchant_info );

			if ( $status === 'valid' ) {
				$connection->set_status( $status )->save();

				return '';
			}

			return $this->get_invalid_status_content( $connection );
		}

		return '';
	}

	/**
	 * Retrieve setting content when a connection is enabled.
	 *
	 * @since 1.10.0
	 *
	 * @param string $mode Connection mode.
	 *
	 * @return string
	 */
	private function get_enabled_status_content( string $mode ): string {

		return '<span class="wpforms-paypal-commerce-connected">' . $this->get_connected_status_content( $mode, 'wpforms-success-icon' ) . $this->get_disconnect_button( $mode ) . '</span>';
	}

	/**
	 * Get Merchant info.
	 *
	 * @since 1.10.0
	 *
	 * @param string $mode Current mode.
	 *
	 * @return array
	 */
	private function get_merchant_info( string $mode ): array {

		$connection = Connection::get( $mode );

		if ( ! $connection ) {
			return [];
		}

		$api           = PayPalCommerce::get_api( $connection );
		$merchant_info = $api->get_merchant_info();

		if ( empty( $merchant_info ) ) {
			return [];
		}

		return $merchant_info;
	}

	/**
	 * Retrieve Connected status setting content.
	 *
	 * @since 1.10.0
	 *
	 * @param string $mode       PayPal Commerce mode.
	 * @param string $icon_class Status icon class.
	 *
	 * @return string
	 */
	private function get_connected_status_content( string $mode, string $icon_class ): string {

		$content  = $this->get_connected_status( $mode, $icon_class );
		$content .= $this->get_connected_merchant_vaulting_status( $mode );

		return $content;
	}

	/**
	 * Get connected merchant vaulting status if set.
	 *
	 * @since 1.10.0
	 *
	 * @param string $mode PayPal Commerce mode.
	 *
	 * @return string
	 */
	private function get_connected_merchant_vaulting_status( string $mode ): string {

		$merchant_info = $this->get_merchant_info( $mode );

		if ( ! isset( $merchant_info['oauth_integrations'][0] ) ) {
			return '';
		}

		$oauth_integration = $merchant_info['oauth_integrations'][0];

		if ( ! isset( $oauth_integration['integration_type'] ) || $oauth_integration['integration_type'] !== 'OAUTH_THIRD_PARTY' || ! is_array( $merchant_info['products'] ) ) {
			return '';
		}

		$statuses           = array_column( $merchant_info['products'], 'vetting_status', 'name' );
		$vaulting_status    = $statuses['ADVANCED_VAULTING'] ?? 'DENIED';
		$vaulting_status_ok = $vaulting_status === 'SUBSCRIBED';
		$vaulting_icon      = $vaulting_status_ok ? 'wpforms-success-icon' : 'wpforms-warning-icon';

		$content  = '<span class="wpforms-paypal-commerce-vaulting-status">';
		$content .= '<span>';
		$content .= '<span class="' . $vaulting_icon . '"></span>';
		$content .= sprintf(
			wp_kses( /* translators: %s - PayPal Commerce Connected Merchant Vaulting Status. */
				__( 'Vaulting Status: <strong>%s</strong>', 'wpforms-lite' ),
				[
					'strong' => [],
				]
			),
			esc_html( $vaulting_status )
		);
		$content .= '</span>';

		if ( ! $vaulting_status_ok ) {

			switch ( $vaulting_status ) {
				case 'NEED_MORE_DATA':
					$vaulting_description = __( 'PayPal needs more info please go to account settings and complete the requested details to finish your vaulting application.', 'wpforms-lite' );
					break;

				case 'IN_REVIEW':
					$vaulting_description = __( 'Your Vaulting application is currently under review by PayPal, we will notify you once vaulting is approved or additional information is needed.', 'wpforms-lite' );
					break;

				case 'DENIED':
					$vaulting_description = __( 'Your account is currently not approved for vaulting, please review your account settings.', 'wpforms-lite' );
					break;

				default:
					$vaulting_description = '';
					break;
			}

			$content .= '<p class="desc">';
			$content .= $vaulting_description;
			$content .= '</p>';
		}

		$content .= '</span>';

		return $content;
	}

	/**
	 * Retrieve Connected status setting content.
	 *
	 * @since 1.10.0
	 *
	 * @param string $mode       PayPal Commerce mode.
	 * @param string $icon_class Status icon class.
	 *
	 * @return string
	 */
	private function get_connected_status( string $mode, string $icon_class = '' ): string {

		$content  = '<span>';
		$content .= '<span class="' . esc_attr( $icon_class ) . '"></span>';
		$content .= sprintf(
			wp_kses( /* translators: %s - PayPal Commerce Mode. */
				__( 'Connected to PayPal in <strong>%s</strong> mode.', 'wpforms-lite' ),
				[
					'strong' => [],
				]
			),
			$mode === Helpers::SANDBOX ? esc_html__( 'Sandbox', 'wpforms-lite' ) : esc_html__( 'Production', 'wpforms-lite' )
		);

		$content .= '</span>';

		return $content;
	}

	/**
	 * Get connected merchant email if set.
	 *
	 * @since 1.10.0
	 *
	 * @param string $mode Current mode.
	 *
	 * @return string
	 */
	private function get_connected_merchant_email( string $mode ): string {

		$merchant_info = $this->get_merchant_info( $mode );
		$email         = ! empty( $merchant_info['primary_email'] ) ? sanitize_email( $merchant_info['primary_email'] ) : esc_html__( 'Not available', 'wpforms-lite' );

		$content  = '<span class="wpforms-paypal-commerce-merchant-info"><span>';
		$content .= $email;
		$content .= '</span></span>';

		return $content;
	}

	/**
	 * Get connected merchant ID if set.
	 *
	 * @since 1.10.0
	 *
	 * @param string $mode PayPal Commerce mode.
	 *
	 * @return string
	 */
	private function get_connected_merchant_id( string $mode ): string {

		$merchant_info = $this->get_merchant_info( $mode );
		$merchant_id   = ! empty( $merchant_info['merchant_id'] ) ? esc_html( $merchant_info['merchant_id'] ) : esc_html__( 'Not available', 'wpforms-lite' );

		$content  = '<span class="wpforms-paypal-commerce-merchant-info"><span>';
		$content .= $merchant_id;
		$content .= '</span></span>';

		return $content;
	}

	/**
	 * Get connected merchant granted scopes list.
	 *
	 * @since 1.10.0
	 *
	 * @param string $mode PayPal Commerce mode.
	 *
	 * @return string
	 */
	private function get_connected_merchant_granted_scopes( string $mode ): string { // phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded

		$merchant_info = $this->get_merchant_info( $mode );
		$content       = '<span class="wpforms-paypal-commerce-merchant-info">';

		if ( isset( $merchant_info['oauth_integrations'][0] ) ) {
			$oauth_integration = $merchant_info['oauth_integrations'][0];

			if ( isset( $oauth_integration['integration_type'] ) && $oauth_integration['integration_type'] === 'OAUTH_THIRD_PARTY' && ! empty( $oauth_integration['oauth_third_party'][0]['scopes'] ) ) {

				$scopes_map = [
					'https://uri.paypal.com/services/payments/realtimepayment' => __( 'Payments – Create / Capture', 'wpforms-lite' ),
					'https://uri.paypal.com/services/payments/payment/authcapture' => __( 'Payments – Authorize & Capture', 'wpforms-lite' ),
					'https://uri.paypal.com/services/payments/refund' => __( 'Payments – Refunds', 'wpforms-lite' ),
					'https://uri.paypal.com/services/payments/partnerfee' => __( 'Partner Fees', 'wpforms-lite' ),
					'https://uri.paypal.com/services/billing-agreements' => __( 'Billing Agreements', 'wpforms-lite' ),
					'https://uri.paypal.com/services/vault/payment-tokens/read' => __( 'Vault – Read payment tokens', 'wpforms-lite' ),
					'https://uri.paypal.com/services/vault/payment-tokens/readwrite' => __( 'Vault – Manage payment tokens', 'wpforms-lite' ),
					'Braintree:Vault' => __( 'Braintree – Vault access', 'wpforms-lite' ),
				];

				foreach ( $oauth_integration['oauth_third_party'][0]['scopes'] as $scope ) {
					if ( ! array_key_exists( $scope, $scopes_map ) ) {
						continue;
					}

					$content .= '<span>';
					$content .= esc_html( $scopes_map[ $scope ] );
					$content .= '</span>';
				}
			}
		} else {
			$content .= '<span>';
			$content .= esc_html__( 'Not available', 'wpforms-lite' );
			$content .= '</span>';
		}

		$content .= '</span>';

		return $content;
	}

	/**
	 * Retrieve a Disconnected Status setting content.
	 *
	 * @since 1.10.0
	 *
	 * @param string $mode PayPal Commerce mode.
	 *
	 * @return string
	 */
	private function get_disconnected_status_content( string $mode ): string {

		$connect_url = ( new Connect() )->get_connect_url( $mode );

		if ( empty( $connect_url ) ) {
			return '<div class="wpforms-paypal-commerce-connected"><span>' . $this->get_warning_icon() . sprintf(
				wp_kses( /* translators: %s - WPForms Payments page URL. */
					__( 'There’s a temporary problem with the connection to PayPal Commerce. Please click <a href="%s" rel="noopener noreferrer">here</a> to try again.', 'wpforms-lite' ),
					[
						'a' => [
							'href' => [],
							'rel'  => [],
						],
					]
				),
				esc_url(
					add_query_arg(
						[
							'paypal_commerce_refresh_signup' => true,
						],
						Helpers::get_settings_page_url()
					)
				)
			) . '</span></div>';
		}

		return $this->get_connect_button( $connect_url ) .
			'<p class="desc">' .
			sprintf(
				wp_kses( /* translators: %s - WPForms.com PayPal Commerce documentation article URL. */
					__( 'Securely connect to PayPal Commerce with just a few clicks to begin accepting payments! <a href="%s" target="_blank" rel="noopener noreferrer" class="wpforms-learn-more">Learn More</a>', 'wpforms-lite' ),
					[
						'a' => [
							'href'   => [],
							'target' => [],
							'rel'    => [],
							'class'  => [],
						],
					]
				),
				wpforms_utm_link( 'https://wpforms.com/docs/paypal-commerce-addon/#connect', 'Payment Settings', 'PayPal Commerce Learn More' )
			) .
			'</p>';
	}

	/**
	 * Retrieve a connection is missing status content.
	 *
	 * @since 1.10.0
	 *
	 * @param string $mode PayPal Commerce mode.
	 *
	 * @return string
	 */
	private function get_missing_status_content( string $mode ): string {

		return '<div class="wpforms-paypal-commerce-connected">' . $this->get_warning_icon() . esc_html__( 'Your connection to PayPal Commerce is not complete. Please reconnect your PayPal Commerce account.', 'wpforms-lite' ) . $this->get_disconnect_button( $mode ) . '</div>';
	}

	/**
	 * Retrieve a connection is missing access token content.
	 *
	 * @since 1.10.0
	 *
	 * @param string $mode PayPal Commerce mode.
	 *
	 * @return string
	 */
	private function get_missing_access_token_content( string $mode ): string {

		return '<div class="wpforms-paypal-commerce-connected">' . $this->get_warning_icon() . esc_html__( 'Your PayPal Commerce access token is not valid. Please reconnect your PayPal Commerce account.', 'wpforms-lite' ) . $this->get_disconnect_button( $mode ) . '</div>';
	}

	/**
	 * Retrieve a connection is missing client token content.
	 *
	 * @since 1.10.0
	 *
	 * @param string $mode PayPal Commerce mode.
	 *
	 * @return string
	 */
	private function get_missing_client_token_content( string $mode ): string {

		return '<div class="wpforms-paypal-commerce-connected">' . $this->get_warning_icon() . esc_html__( 'Your PayPal Commerce client token is not valid. Please reconnect your PayPal Commerce account.', 'wpforms-lite' ) . $this->get_disconnect_button( $mode ) . '</div>';
	}

	/**
	 * Retrieve a connection invalid status content.
	 *
	 * @since 1.10.0
	 *
	 * @param Connection|\WPFormsPaypalCommerce\Connection $connection Connection data.
	 *
	 * @return string
	 */
	private function get_invalid_status_content( $connection ): string {

		$api = PayPalCommerce::get_api( $connection );

		if ( is_null( $api ) ) {
			return '';
		}

		$mode = $connection->get_mode();

		$content  = '<div class="wpforms-paypal-commerce-connected">';
		$content .= $this->get_connected_status_content( $mode, 'wpforms-warning-icon' );

		$permissions = $this->get_merchant_info( $mode );

		// Add permission-related warnings only if permissions exist.
		if ( ! empty( $permissions ) ) {
			$content .= $this->get_permission_warnings( $permissions );
		}

		// General invalid status.
		$content .= '<p class="desc">' . esc_html__( 'Your PayPal Commerce connection is not valid. Please reconnect your PayPal Commerce account.', 'wpforms-lite' ) . '</p>';
		$content .= '<p class="desc">' . $this->get_disconnect_button( $mode, false ) . '</p>';

		$content .= '</div>';

		return $content;
	}

	/**
	 * Generate permission-related warning messages for an invalid PayPal Commerce connection.
	 *
	 * Builds the list of warnings related to merchant permissions, such as disabled
	 * payment capabilities, unconfirmed email, or unsupported PPCP products. This
	 * method is called only when a merchant permission array is available.
	 *
	 * @since 1.10.0
	 *
	 * @param array $permissions Merchant permission data returned by the API.
	 *
	 * @return string HTML markup for permission-related warnings
	 */
	private function get_permission_warnings( array $permissions ): string {

		$output = '';

		if ( empty( $permissions['payments_receivable'] ) ) {
			$output .= '<p class="desc">' . $this->get_warning_icon() . __( 'Payments are disabled.', 'wpforms-lite' ) . '</p>';
		}

		if ( empty( $permissions['primary_email_confirmed'] ) ) {
			$output .= '<p class="desc">' . $this->get_warning_icon() . __( 'Primary email unconfirmed.', 'wpforms-lite' ) . '</p>';
		}

		if ( ! empty( $permissions['products'] ) && is_array( $permissions['products'] ) ) {
			foreach ( $permissions['products'] as $product ) {

				if ( isset( $product['vetting_status'] ) && $product['vetting_status'] === 'SUBSCRIBED' ) {
					continue;
				}

				if ( $product['name'] === 'PPCP_STANDARD' ) {
					$output .= '<p class="desc">' . $this->get_warning_icon() . __( 'Credit Card field support is disabled.', 'wpforms-lite' ) . '</p>';
				}

				if ( $product['name'] === 'PPCP_CUSTOM' ) {
					$output .= '<p class="desc">' . $this->get_warning_icon() . __( 'PayPal Checkout support is disabled.', 'wpforms-lite' ) . '</p>';
				}
			}
		}

		return $output;
	}

	/**
	 * Retrieve the Connect button.
	 *
	 * @since 1.10.0
	 *
	 * @param string $connect_url Connect URL.
	 *
	 * @return string
	 */
	private function get_connect_button( string $connect_url ): string {

		$button = sprintf(
			'<a target="_blank" class="wpforms-btn wpforms-btn-md wpforms-btn-light-grey" href="%1$s" title="%2$s" data-paypal-onboard-complete="wpformsPaypalOnboardCompleted" data-paypal-button="true">%3$s</a>',
			esc_url( $connect_url ),
			esc_attr__( 'Connect PayPal Commerce account', 'wpforms-lite' ),
			esc_html__( 'Connect with PayPal Commerce', 'wpforms-lite' )
		);

		return '<p>' . $button . '</p>';
	}

	/**
	 * Retrieve the Disconnect button.
	 *
	 * @since 1.10.0
	 *
	 * @param string $mode PayPal Commerce mode.
	 * @param bool   $wrap Optional. Wrap a button HTML element or not.
	 *
	 * @return string
	 */
	private function get_disconnect_button( string $mode, bool $wrap = true ): string {

		$button = sprintf(
			'<a id="wpforms-paypal-commerce-disconnect-' . esc_attr( $mode ) . '" class="wpforms-btn wpforms-btn-md wpforms-btn-light-grey" href="%1$s" title="%2$s">%3$s</a>',
			esc_url( $this->get_disconnect_url( $mode ) ),
			esc_attr__( 'Disconnect PayPal Commerce account', 'wpforms-lite' ),
			esc_html__( 'Disconnect', 'wpforms-lite' )
		);

		return $wrap ? '<p>' . $button . '</p>' : $button;
	}

	/**
	 * Retrieve the disconnect URL.
	 *
	 * @since 1.10.0
	 *
	 * @param string $mode Connection mode.
	 *
	 * @return string
	 */
	private function get_disconnect_url( string $mode ): string {

		$mode = Helpers::validate_mode( $mode );
		$url  = add_query_arg(
			[
				'action'    => Connect::DISCONNECT_ACTION_NONCE,
				'live_mode' => absint( $mode === Helpers::PRODUCTION ),
			],
			Helpers::get_settings_page_url()
		);

		return wp_nonce_url( $url, Connect::DISCONNECT_ACTION_NONCE );
	}

	/**
	 * Retrieve the Warning icon emoji.
	 *
	 * @since 1.10.0
	 *
	 * @return string
	 */
	private function get_warning_icon(): string {

		return '<span class="wpforms-warning-icon"></span>';
	}
}
