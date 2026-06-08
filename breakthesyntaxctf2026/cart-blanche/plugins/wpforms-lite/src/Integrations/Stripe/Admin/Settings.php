<?php

namespace WPForms\Integrations\Stripe\Admin;

use WPForms\Integrations\Stripe\Api\PaymentIntents;
use WPForms\Integrations\Stripe\Helpers;
use WPForms\Admin\Notice;

/**
 * Stripe "Settings" section methods.
 *
 * @since 1.8.2
 */
class Settings {

	/**
	 * Stripe Connect.
	 *
	 * @since 1.8.2
	 *
	 * @var Connect
	 */
	protected $connect;

	/**
	 * Stripe Webhook Settings.
	 *
	 * @since 1.8.4
	 *
	 * @var WebhookSettings
	 */
	protected $webhook_settings;

	/**
	 * Initialize class.
	 *
	 * @since 1.8.2
	 */
	public function init() {

		$this->connect          = ( new Connect() )->init();
		$this->webhook_settings = ( new WebhookSettings() )->init();

		$this->hooks();
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.8.2
	 */
	private function hooks() {

		add_action( 'wpforms_settings_init', [ $this, 'connection_is_missing_notice' ] );
		add_action( 'wpforms_settings_init', [ $this, 'not_supported_currency_notice' ] );
		add_action( 'wpforms_settings_enqueue', [ $this, 'enqueue_assets' ] );
		add_filter( 'wpforms_settings_defaults', [ $this, 'register_settings_fields' ], 6 );
	}

	/**
	 * Stripe is not connected for the current payment mode notice.
	 *
	 * @since 1.8.2
	 */
	public function connection_is_missing_notice() {

		if ( ! Helpers::is_pro() || Helpers::has_stripe_keys() ) {
			return;
		}

		$account = $this->connect->get_connected_account();

		if ( ! empty( $account->id ) ) {
			return;
		}

		Notice::warning(
			esc_html__( 'Stripe is not connected for your current payment mode. Please press the "Connect with Stripe" button to complete this setup.', 'wpforms-lite' )
		);
	}

	/**
	 * Selected currency is not supported for connected account.
	 *
	 * @since 1.8.2
	 */
	public function not_supported_currency_notice() {

		if ( ! Helpers::has_stripe_keys() ) {
			return;
		}

		$account = $this->connect->get_connected_account();

		if ( is_null( $account ) ) {
			return;
		}

		$selected_currency = strtolower( wpforms_get_currency() );

		if ( $selected_currency === $account->default_currency ) {
			return;
		}

		$country_specs = ( new PaymentIntents() )->get_country_specs( $account->country );

		if ( ! $country_specs || in_array( $selected_currency, $country_specs->supported_payment_currencies, true ) ) {
			return;
		}

		Notice::error(
			sprintf(
				wp_kses( /* translators: %1$s - Selected currency on the WPForms Settings admin page. */
					__( '<strong>Payments Cannot Be Processed</strong><br>The currency you have set (%1$s) is not supported by Stripe. Please choose a different currency.', 'wpforms-lite' ),
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
	 * Enqueue "Settings" scripts and styles.
	 *
	 * @since 1.8.2
	 */
	public function enqueue_assets() {

		$min = wpforms_get_min_suffix();

		wp_enqueue_style(
			'wpforms-admin-settings-stripe',
			WPFORMS_PLUGIN_URL . "assets/css/integrations/stripe/admin-settings-stripe{$min}.css",
			[],
			WPFORMS_VERSION
		);

		wp_enqueue_script(
			'wpforms-admin-settings-stripe',
			WPFORMS_PLUGIN_URL . "assets/js/integrations/stripe/admin-settings-stripe{$min}.js",
			[ 'jquery', 'wpforms-admin-utils' ],
			WPFORMS_VERSION,
			true
		);

		$admin_settings_stripe_l10n = [
			'mode_update'  => wp_kses(
				__( '<p>Switching test/live modes requires Stripe account reconnection.</p><p>Press the <em>"Connect with Stripe"</em> button after saving the settings to reconnect.</p>', 'wpforms-lite' ),
				[
					'p'  => [],
					'em' => [],
				]
			),
			'webhook_urls' => [
				'rest' => Helpers::get_webhook_url_for_rest(),
				'curl' => Helpers::get_webhook_url_for_curl(),
			],
		];

		wp_localize_script(
			'wpforms-admin-settings-stripe',
			'wpforms_admin_settings_stripe',
			$admin_settings_stripe_l10n
		);
	}

	/**
	 * Register "Stripe" settings fields.
	 *
	 * @since 1.8.2
	 *
	 * @param array $settings Admin area settings list.
	 *
	 * @return array
	 */
	public function register_settings_fields( $settings ) {

		// Bail early, in case "Payments" settings is not registered.
		if ( ! isset( $settings['payments'] ) ) {
			return $settings;
		}

		$stripe_settings = [
			'stripe-heading'           => [
				'id'       => 'stripe-heading',
				'content'  => $this->get_heading_content(),
				'type'     => 'content',
				'no_label' => true,
				'class'    => [ 'section-heading' ],
			],
			'stripe-connection-status' => [
				'id'      => 'stripe-connection-status',
				'name'    => esc_html__( 'Connection Status', 'wpforms-lite' ),
				'content' => $this->get_connection_status_content(),
				'type'    => 'content',
			],
			'stripe-test-mode'         => [
				'id'     => 'stripe-test-mode',
				'name'   => esc_html__( 'Test Mode', 'wpforms-lite' ),
				'type'   => 'toggle',
				'status' => true,
				'desc'   => sprintf(
					wp_kses( /* translators: %s - WPForms.com URL for Stripe payments with more details. */
						__( 'Prevent Stripe from processing live transactions. Please see <a href="%s" target="_blank" rel="noopener noreferrer">our documentation on Stripe test payments</a> for full details.', 'wpforms-lite' ),
						[
							'a' => [
								'href'   => [],
								'target' => [],
								'rel'    => [],
								'class'  => [],
							],
						]
					),
					esc_url( wpforms_utm_link( 'https://wpforms.com/docs/how-to-test-stripe-payments-on-your-site/', 'Settings - Payments', 'Stripe Test Payments Documentation' ) )
				),
			],
		];

		$stripe_settings = $this->webhook_settings->settings( $stripe_settings );

		$this->maybe_set_card_mode();

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! empty( $_GET['stripe_card_mode'] ) || ! Helpers::is_payment_element_enabled() ) {
			$stripe_settings['stripe-card-mode'] = [
				'id'         => 'stripe-card-mode',
				'name'       => esc_html__( 'Credit Card Field Mode', 'wpforms-lite' ),
				'type'       => 'radio',
				'default'    => 'payment',
				'desc_after' => $this->get_credit_card_field_desc_after(),
				'options'    => [
					'card'    => esc_html__( 'Card Element', 'wpforms-lite' ),
					'payment' => esc_html__( 'Payment Element', 'wpforms-lite' ),
				],
			];
		}

		$settings['payments'] = array_merge( $settings['payments'], $stripe_settings );

		return $settings;
	}

	/**
	 * Maybe set card mode setting.
	 *
	 * @since 1.8.2
	 */
	private function maybe_set_card_mode() {

		// Bail out if a card mode is already set.
		if ( wpforms_setting( 'stripe-card-mode' ) ) {
			return;
		}

		$settings                     = (array) get_option( 'wpforms_settings', [] );
		$settings['stripe-card-mode'] = Helpers::has_stripe_keys() ? 'card' : 'payment';

		update_option( 'wpforms_settings', $settings );
	}

	/**
	 * Section header content.
	 *
	 * @since 1.8.2
	 *
	 * @return string
	 */
	private function get_heading_content() {

		return '<h4>' . esc_html__( 'Stripe', 'wpforms-lite' ) . '</h4>' .
			'<p>' .
			sprintf(
				wp_kses( /* translators: %s - WPForms.com Stripe documentation article URL. */
					__( 'Easily collect credit card payments with Stripe. For getting started and more information, see our <a href="%s" target="_blank" rel="noopener noreferrer">Stripe documentation</a>.', 'wpforms-lite' ),
					[
						'a' => [
							'href'   => [],
							'target' => [],
							'rel'    => [],
						],
					]
				),
				esc_url( wpforms_utm_link( 'https://wpforms.com/docs/how-to-install-and-use-the-stripe-addon-with-wpforms/', 'Settings - Payments', 'Stripe Documentation' ) )
			) .
			'</p>' .
			Notices::get_fee_notice();
	}

	/**
	 * Connection Status setting content.
	 *
	 * @since 1.8.2
	 *
	 * @return string
	 */
	protected function get_connection_status_content() {

		$output       = '';
		$current_mode = Helpers::get_stripe_mode();

		foreach ( Helpers::CONNECTION_MODES as $mode ) {

			$class_names = [
				'wpforms-stripe-connection-status',
				"wpforms-stripe-connection-status-{$mode}",
				$current_mode !== $mode ? 'wpforms-hide' : '',
			];
			$account     = $this->connect->get_connected_account( $mode );
			$output     .= sprintf( '<div %s>', wpforms_html_attributes( '', $class_names ) );

			if ( empty( $account->id ) ) {
				$output .= $this->get_disconnected_status_content( $mode );
			} else {
				$output .= $this->get_connected_status_content( $mode );
			}

			$output .= '</div>';
		}

		return $output;
	}

	/**
	 * Connected Status setting content.
	 *
	 * @since 1.8.2
	 *
	 * @param string $mode Stripe mode (e.g. 'live' or 'test').
	 *
	 * @return string
	 */
	private function get_connected_status_content( string $mode = '' ): string {

		$output         = '';
		$account_name   = $this->connect->get_connected_account_name( $mode );
		$connect_url    = $this->connect->get_connect_with_stripe_url( $mode );
		$disconnect_url = $this->connect->get_disconnect_stripe_url( $mode );

		$connected_status = sprintf(
			wp_kses( /* translators: %1$s - Stripe account name connected, %2$s - Stripe mode connected (live or test). */
				__( 'Connected to Stripe as <em>%1$s</em> in <strong>%2$s Mode</strong>.', 'wpforms-lite' ),
				[
					'strong' => [],
					'em'     => [],
				]
			),
			esc_html( $account_name ),
			ucwords( $mode ? $mode : Helpers::get_stripe_mode() )
		);

		$output .= sprintf( '<div class="wpforms-connected"><p>%s</p></div>', $connected_status );
		$output .= '<p>' . sprintf(
			wp_kses( /* translators: %s - Stripe connect URL. */
				__( '<a href="%s" class="wpforms-btn wpforms-btn-md wpforms-btn-light-grey" style="margin-right: 10px;">Switch Accounts</a>', 'wpforms-lite' ),
				[
					'a' => [
						'href'  => [],
						'class' => [],
						'style' => [],
					],
				]
			),
			esc_url( $connect_url )
		);
		$output .= sprintf(
			wp_kses( /* translators: %s - Stripe disconnect URL. */
				__( '<a href="%s" class="wpforms-btn wpforms-btn-md wpforms-btn-light-grey">Disconnect</a>', 'wpforms-lite' ),
				[
					'a' => [
						'href'  => [],
						'class' => [],
					],
				]
			),
			esc_url( $disconnect_url )
		) . '</p>';

		return $output;
	}

	/**
	 * Disconnected Status setting content.
	 *
	 * @since 1.8.2
	 *
	 * @param string $mode Stripe mode (e.g. 'live' or 'test').
	 *
	 * @return string
	 */
	protected function get_disconnected_status_content( $mode = '' ) {

		$connect_url    = $this->connect->get_connect_with_stripe_url( $mode );
		$connect_button = sprintf(
			wp_kses( /* translators: %s - WPForms.com Stripe documentation article URL. */
				__( 'Securely connect to Stripe with just a few clicks to begin accepting payments! <a href="%s" target="_blank" rel="noopener noreferrer" class="wpforms-learn-more">Learn More</a>', 'wpforms-lite' ),
				[
					'a' => [
						'href'   => [],
						'target' => [],
						'rel'    => [],
						'class'  => [],
					],
				]
			),
			esc_url( wpforms_utm_link( 'https://wpforms.com/docs/how-to-install-and-use-the-stripe-addon-with-wpforms/#connect-stripe', 'Settings - Payments', 'Stripe Documentation' ) )
		);

		return sprintf(
			'<div class="wpforms-connect"><a href="%s" class="wpforms-stripe-connect-button" title="%s"></a><p>%s</p></div>',
			esc_url( $connect_url ),
			esc_attr__( 'Connect with Stripe', 'wpforms-lite' ),
			$connect_button
		);
	}

	/**
	 * Credit Card mode description.
	 *
	 * @since 1.8.2
	 *
	 * @return string
	 */
	private function get_credit_card_field_desc_after() {

		return '<p class="desc">' . sprintf(
			wp_kses( /* translators: %s - WPForms.com Stripe documentation article URL. */
				__( 'Please see <a href="%s" target="_blank" rel="noopener noreferrer">our documentation on Stripe Credit Card field modes for full details</a>.', 'wpforms-lite' ),
				[
					'a' => [
						'href'   => [],
						'target' => [],
						'rel'    => [],
					],
				]
			),
			esc_url( wpforms_utm_link( 'https://wpforms.com/docs/how-to-install-and-use-the-stripe-addon-with-wpforms/#field-modes', 'Settings - Payments', 'Stripe Field Modes' ) )
		) . '</p>';
	}
}
