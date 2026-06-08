<?php

namespace WPForms\Integrations\Square\Admin;

use WPForms\Vendor\Square\Environment;
use WPForms\Integrations\Square\Connection;
use WPForms\Integrations\Square\Helpers;

/**
 * Square addon settings.
 *
 * @since 1.9.5
 */
class Settings {

	/**
	 * Determine if Square account is connected.
	 *
	 * @since 1.9.5
	 *
	 * @var bool
	 */
	private $is_connected;

	/**
	 * Square Connect.
	 *
	 * @since 1.9.5
	 *
	 * @var Connect
	 */
	protected $connect;

	/**
	 * Square Webhook Settings.
	 *
	 * @since 1.9.5
	 *
	 * @var WebhookSettings
	 */
	protected $webhook_settings;

	/**
	 * Initialize.
	 *
	 * @since 1.9.5
	 *
	 * @return Settings
	 */
	public function init() {

		$this->connect          = ( new Connect() )->init();
		$this->webhook_settings = ( new WebhookSettings() )->init();

		$this->hooks();

		return $this;
	}

	/**
	 * Settings hooks.
	 *
	 * @since 1.9.5
	 */
	private function hooks() {

		add_action( 'wpforms_settings_enqueue',  [ $this, 'enqueue_assets' ] );
		add_filter( 'wpforms_admin_strings',     [ $this, 'javascript_strings' ] );

		add_filter( 'wpforms_settings_defaults', [ $this, 'register' ], 12 );
		add_action( 'wpforms_settings_updated',  [ $this, 'reset_transients' ] );
	}

	/**
	 * Enqueue Settings assets.
	 *
	 * @since 1.9.5
	 */
	public function enqueue_assets() {

		$min = wpforms_get_min_suffix();

		wp_enqueue_style(
			'wpforms-admin-settings-square',
			WPFORMS_PLUGIN_URL . "assets/css/integrations/square/admin-settings-square{$min}.css",
			[],
			WPFORMS_VERSION
		);

		wp_enqueue_script(
			'wpforms-admin-settings-square',
			WPFORMS_PLUGIN_URL . "assets/js/integrations/square/admin/settings-square{$min}.js",
			[ 'jquery', 'wpforms-admin-utils' ],
			WPFORMS_VERSION,
			true
		);
	}

	/**
	 * Localize needed strings.
	 *
	 * @since 1.9.5
	 *
	 * @param array $strings JS strings.
	 *
	 * @return array
	 */
	public function javascript_strings( $strings ): array {

		$strings = (array) $strings;

		$strings['square'] = [
			'mode_update'                => wp_kses(
				__(
					'<p>Switching sandbox/production modes requires Square account reconnection.</p><p>Press the <em>"Connect with Square"</em> button after saving the settings to reconnect.</p>',
					'wpforms-lite'
				),
				[
					'p'  => [],
					'em' => [],
				]
			),
			'refresh_error'              => esc_html__( 'Something went wrong while performing a refresh tokens request.', 'wpforms-lite' ),
			'webhook_create_title'       => esc_html__( 'Personal Access Token', 'wpforms-lite' ),
			'webhook_create_description' => sprintf(
				wp_kses( /* translators: %s - the Square developer dashboard URL. */
					__( '<p>To receive events, create a webhook route by providing your Personal Access Token, which you can find after registering an app on the <a href="%1$s" target="_blank">Square Developer Dashboard</a>. You can also set it up manually in the Advanced section.</p><p>See <a href="%2$s" target="_blank">our documentation</a> for details.</p>', 'wpforms-lite' ),
					[
						'a' => [
							'href'   => [],
							'target' => [],
						],
						'p' => [],
					]
				),
				esc_url( WebhookSettings::SQUARE_APPS_URL ),
				esc_url( wpforms_utm_link( 'https://wpforms.com/docs/setting-up-square-webhooks/', 'Settings - Payments', 'Square Webhooks Documentation Modal' ) )
			),

			'webhook_token_placeholder'  => esc_html__( 'Personal Access Token', 'wpforms-lite' ),
			'token_is_required'          => esc_html__( 'Personal Access Token is required to proceed.', 'wpforms-lite' ),
			'webhook_urls'               => [
				'rest' => Helpers::get_webhook_url_for_rest(),
				'curl' => Helpers::get_webhook_url_for_curl(),
			],
		];

		return $strings;
	}

	/**
	 * Register Settings fields.
	 *
	 * @since 1.9.5
	 *
	 * @param array $settings Array of current form settings.
	 *
	 * @return array
	 */
	public function register( $settings ): array {

		$settings = (array) $settings;

		$settings['payments']['square-heading'] = [
			'id'       => 'square-heading',
			'content'  => $this->get_heading_content(),
			'type'     => 'content',
			'no_label' => true,
			'class'    => [ 'section-heading' ],
		];

		foreach ( Helpers::get_available_modes() as $mode ) {
			$mode = sanitize_key( $mode );

			$settings['payments'][ 'square-connection-status-' . $mode ] = [
				'id'        => 'square-connection-status-' . $mode,
				'name'      => esc_html__( 'Connection Status', 'wpforms-lite' ),
				'content'   => $this->get_connection_status_content( $mode ),
				'type'      => 'content',
				'is_hidden' => Helpers::get_mode() !== $mode,
			];

			if ( $this->is_connected ) {

				$is_location_set = ! empty( Helpers::get_location_id( $mode ) );

				$settings['payments'][ 'square-location-id-' . $mode ] = [
					'id'        => 'square-location-id-' . $mode,
					'class'     => $is_location_set ? '' : 'location-error',
					'name'      => esc_html__( 'Business Location', 'wpforms-lite' ),
					'desc'      => esc_html__( 'Only active locations that support credit card processing in Square can be chosen.', 'wpforms-lite' ),
					'type'      => 'select',
					'choicesjs' => true,
					'options'   => $this->get_location_options( $mode ),
					'is_hidden' => Helpers::get_mode() !== $mode,
				];

				$settings['payments'][ 'square-location-status-' . $mode ] = [
					'id'        => 'square-location-status-' . $mode,
					'content'   => $is_location_set ? '' : $this->get_location_content_error(),
					'type'      => 'content',
					'is_hidden' => Helpers::get_mode() !== $mode,
				];
			}
		}

		$settings['payments']['square-sandbox-mode'] = [
			'id'     => 'square-sandbox-mode',
			'name'   => esc_html__( 'Test Mode', 'wpforms-lite' ),
			'desc'   => sprintf(
				wp_kses( /* translators: %s - WPForms.com URL for Square payment with more details. */
					__( 'Prevent Square from processing live transactions. <a href="%s" target="_blank" rel="noopener noreferrer" class="wpforms-learn-more">Learn More</a>', 'wpforms-lite' ),
					[
						'a' => [
							'href'   => [],
							'target' => [],
							'rel'    => [],
							'class'  => [],
						],
					]
				),
				esc_url( wpforms_utm_link( 'https://wpforms.com/docs/how-to-test-square-payments-on-your-site/', 'Settings - Payments', 'Square Test Documentation' ) )
			),
			'type'   => 'toggle',
			'status' => true,
		];

		$webhooks_settings = $this->webhook_settings->settings( $settings );

		return array_merge( $settings, $webhooks_settings );
	}

	/**
	 * Reset transients on settings save.
	 *
	 * @since 1.9.5
	 */
	public function reset_transients() {

		array_map( 'WPForms\Integrations\Square\Helpers::detete_transients', Helpers::get_available_modes() );
	}

	/**
	 * Retrieve a section header content.
	 *
	 * @since 1.9.5
	 *
	 * @return string
	 */
	private function get_heading_content(): string {

		return '<h4>' . esc_html__( 'Square', 'wpforms-lite' ) . '</h4><p>' .
			sprintf(
				wp_kses( /* translators: %s - WPForms.com Square documentation article URL. */
					__( 'Easily collect credit card payments with Square. For getting started and more information, see our <a href="%s" target="_blank" rel="noopener noreferrer">Square documentation</a>.', 'wpforms-lite' ),
					[
						'a' => [
							'href'   => [],
							'target' => [],
							'rel'    => [],
						],
					]
				),
				esc_url( wpforms_utm_link( 'https://wpforms.com/docs/how-to-install-and-use-the-square-addon-with-wpforms/', 'Settings - Payments', 'Square Documentation' ) )
			) .
			'</p>' .
			Notices::get_fee_notice();
	}

	/**
	 * Retrieve a Connection Status setting content.
	 *
	 * @since 1.9.5
	 *
	 * @param string $mode Square mode.
	 *
	 * @return string
	 */
	private function get_connection_status_content( string $mode ): string {

		$this->is_connected = false;
		$connection         = Connection::get( $mode );

		if ( ! $connection ) {
			return $this->get_disconnected_status_content( $mode );
		}

		$content = $this->get_disabled_status_content( $connection );

		if ( ! empty( $content ) ) {
			return $content;
		}

		$this->is_connected = true;

		return $this->get_enabled_status_content( $connection );
	}

	/**
	 * Retrieve a location content error.
	 *
	 * @since 1.9.5
	 *
	 * @return string
	 */
	private function get_location_content_error(): string {

		return '<div class="wpforms-notice notice-error"><p>' . $this->get_error_icon() . esc_html__( 'Business Location is required to process Square payments.', 'wpforms-lite' ) . '</p></div>';
	}

	/**
	 * Retrieve setting content when a connection is disabled.
	 *
	 * @since 1.9.5
	 *
	 * @param Connection $connection Connection data.
	 *
	 * @return string
	 */
	private function get_disabled_status_content( Connection $connection ): string {

		if ( ! $connection->is_configured() ) {
			return $this->get_missing_status_content( $connection->get_mode() );
		}

		if ( ! $connection->is_valid() ) {
			return $this->get_invalid_status_content( $connection->get_mode() );
		}

		return '';
	}

	/**
	 * Retrieve setting content when a connection is enabled.
	 *
	 * @since 1.9.5
	 *
	 * @param Connection $connection Connection data.
	 *
	 * @return string
	 */
	private function get_enabled_status_content( Connection $connection ): string {

		if ( $connection->is_expired() ) {
			return $this->get_expired_status_content( $connection->get_mode() );
		}

		if ( ! $connection->is_currency_matched() ) {
			return $this->get_currency_mismatch_status_content( $connection->get_mode() );
		}

		return '<div class="wpforms-square-connected"><span class="wpforms-success-icon"></span>' . $this->get_connected_status_content( $connection->get_mode() ) . $this->get_disconnect_button( $connection->get_mode() ) . '</div>';
	}

	/**
	 * Retrieve a Connected Status setting content.
	 *
	 * @since 1.9.5
	 *
	 * @param string $mode Square mode.
	 *
	 * @return string
	 */
	private function get_connected_status_content( string $mode ): string {

		$account_data = $this->connect->get_connected_account( $mode );
		$account_name = empty( $account_data['business_name'] ) ? '' : $account_data['business_name'];

		if ( empty( $account_name ) ) {
			return sprintf(
				wp_kses( /* translators: %s - Square mode. */
					__( 'Connected to Square in <strong>%s</strong> mode.', 'wpforms-lite' ),
					[
						'strong' => [],
					]
				),
				$mode === Environment::SANDBOX ? esc_html__( 'Sandbox', 'wpforms-lite' ) : esc_html__( 'Production', 'wpforms-lite' )
			);
		}

		return sprintf(
			wp_kses( /* translators: %1$s - Square connected account name; %2$s - Square mode. */
				__( 'Connected to Square as <em>%1$s</em> in <strong>%2$s</strong> mode.', 'wpforms-lite' ),
				[
					'strong' => [],
					'em'     => [],
				]
			),
			esc_html( $account_name ),
			$mode === Environment::SANDBOX ? esc_html__( 'Sandbox', 'wpforms-lite' ) : esc_html__( 'Production', 'wpforms-lite' )
		);
	}

	/**
	 * Retrieve a Disconnected Status setting content.
	 *
	 * @since 1.9.5
	 *
	 * @param string $mode Square mode.
	 *
	 * @return string
	 */
	private function get_disconnected_status_content( string $mode ): string {

		return $this->get_connect_button( $mode, false ) .
			'<p class="desc">' .
			sprintf(
				wp_kses( /* translators: %s - WPForms.com Square documentation article URL. */
					__( 'Securely connect to Square with just a few clicks to begin accepting payments! <a href="%s" target="_blank" rel="noopener noreferrer" class="wpforms-learn-more">Learn More</a>', 'wpforms-lite' ),
					[
						'a' => [
							'href'   => [],
							'target' => [],
							'rel'    => [],
							'class'  => [],
						],
					]
				),
				esc_url( wpforms_utm_link( 'https://wpforms.com/docs/how-to-install-and-use-the-square-addon-with-wpforms/#connect-square', 'Settings - Payments', 'Square Learn More' ) )
			) .
			'</p>';
	}

	/**
	 * Retrieve a connection is missing status content.
	 *
	 * @since 1.9.5
	 *
	 * @param string $mode Square mode.
	 *
	 * @return string
	 */
	private function get_missing_status_content( string $mode ): string {

		return '<div class="wpforms-square-connected">' . $this->get_error_icon() . esc_html__( 'Your connection is missing required data. You must reconnect your Square account.', 'wpforms-lite' ) . $this->get_disconnect_button( $mode ) . '</div>';
	}

	/**
	 * Retrieve a connection invalid status content.
	 *
	 * @since 1.9.5
	 *
	 * @param string $mode Square mode.
	 *
	 * @return string
	 */
	private function get_invalid_status_content( string $mode ): string {

		return '<div class="wpforms-square-connected">' . $this->get_error_icon() . $this->get_connected_status_content( $mode ) .
			'<p>' . esc_html__( 'It appears your connection may be invalid. You must refresh tokens or reconnect your account.', 'wpforms-lite' ) . '</p>' .
			'<p>' . $this->get_refresh_button( $mode ) . $this->get_disconnect_button( $mode, false ) . '</p></div>';
	}

	/**
	 * Retrieve a connection expired status content.
	 *
	 * @since 1.9.5
	 *
	 * @param string $mode Square mode.
	 *
	 * @return string
	 */
	private function get_expired_status_content( string $mode ): string {

		return '<div class="wpforms-square-connected">' . $this->get_error_icon() . $this->get_connected_status_content( $mode ) .
			'<p>' . esc_html__( 'Your connection is expired. You must refresh tokens or reconnect your account.', 'wpforms-lite' ) . '</p>' .
			'<p>' . $this->get_refresh_button( $mode ) . $this->get_disconnect_button( $mode, false ) . '</p></div>';
	}

	/**
	 * Retrieve a currency mismatch status content.
	 *
	 * @since 1.9.5
	 *
	 * @param string $mode Square mode.
	 *
	 * @return string
	 */
	private function get_currency_mismatch_status_content( string $mode ): string {

		return '<div class="wpforms-square-connected">' . $this->get_error_icon() . $this->get_connected_status_content( $mode ) .
			'<span class="wpforms-notice notice-error"><p>' . esc_html__( 'WPForms currency and Business Location currency are not matched.', 'wpforms-lite' ) . '</p></span></div>' .
			$this->get_disconnect_button( $mode );
	}

	/**
	 * Retrieve the Connect button.
	 *
	 * @since 1.9.5
	 *
	 * @param string $mode Square mode.
	 * @param bool   $wrap Optional. Wrap a button HTML element or not.
	 *
	 * @return string
	 */
	private function get_connect_button( string $mode, bool $wrap = true ): string {

		$button = sprintf(
			'<a class="wpforms-btn wpforms-btn-md wpforms-btn-light-grey" href="%1$s" title="%2$s">%3$s</a>',
			esc_url( $this->connect->get_connect_url( $mode ) ),
			esc_attr__( 'Connect Square account', 'wpforms-lite' ),
			esc_html__( 'Connect with Square', 'wpforms-lite' )
		);

		return $wrap ? '<p>' . $button . '</p>' : $button;
	}

	/**
	 * Retrieve the Disconnect button.
	 *
	 * @since 1.9.5
	 *
	 * @param string $mode Square mode.
	 * @param bool   $wrap Optional. Wrap a button HTML element or not.
	 *
	 * @return string
	 */
	private function get_disconnect_button( string $mode, bool $wrap = true ): string {

		$button = sprintf(
			'<a class="wpforms-btn wpforms-btn-md wpforms-btn-light-grey" href="%1$s" title="%2$s">%3$s</a>',
			esc_url( $this->connect->get_disconnect_url( $mode ) ),
			esc_attr__( 'Disconnect Square account', 'wpforms-lite' ),
			esc_html__( 'Disconnect', 'wpforms-lite' )
		);

		return $wrap ? '<p>' . $button . '</p>' : $button;
	}

	/**
	 * Retrieve the Refresh tokens button.
	 *
	 * @since 1.9.5
	 *
	 * @param string $mode Square mode.
	 *
	 * @return string
	 */
	private function get_refresh_button( string $mode ): string {

		return sprintf(
			'<button class="wpforms-btn wpforms-btn-md wpforms-btn-light-grey wpforms-square-refresh-btn" type="button" data-mode="%1$s" data-url="%2$s" title="%3$s">%4$s</button>',
			esc_attr( $mode ),
			esc_url( Helpers::get_settings_page_url() ),
			esc_attr__( 'Refresh connection tokens', 'wpforms-lite' ),
			esc_html__( 'Refresh tokens', 'wpforms-lite' )
		);
	}

	/**
	 * Retrieve the Error icon emoji.
	 *
	 * @since 1.9.5
	 *
	 * @return string
	 */
	private function get_error_icon(): string {

		return '<span class="wpforms-error-icon"></span>';
	}

	/**
	 * Retrieve Business Location options.
	 *
	 * @since 1.9.5
	 *
	 * @param string $mode Square mode.
	 *
	 * @return array
	 */
	private function get_location_options( string $mode ): array {

		$locations = $this->connect->get_connected_locations( $mode );

		return ! empty( $locations ) ? array_column( $locations, 'name', 'id' ) : [ '' => esc_html__( 'No locations were found', 'wpforms-lite' ) ];
	}
}
