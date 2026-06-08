<?php

namespace WPForms\Integrations\Square;

use WPForms\Integrations\IntegrationInterface;

/**
 * Integration of the Square payment gateway.
 *
 * @since 1.9.5
 */
final class Square implements IntegrationInterface {

	/**
	 * Square application name.
	 *
	 * @since 1.9.5
	 */
	public const APP_NAME = 'WPForms';

	/**
	 * Determine if the integration is allowed to load.
	 *
	 * @since 1.9.5
	 *
	 * @return bool
	 */
	public function allow_load(): bool {

		// Determine whether the Square addon version is compatible with the WPForms plugin version.
		$addon_compat = ( new AddonCompatibility() )->init();

		// Do not load integration if unsupported version of the Square addon is active.
		if ( $addon_compat && ! $addon_compat->is_supported_version() ) {
			$addon_compat->hooks();

			return false;
		}

		// Determine whether the cURL extension is enabled.
		$curl_compat = ( new CurlCompatibility() )->init();

		// Do not load integration if curl is not enabled.
		if ( $curl_compat ) {
			$curl_compat->hooks();

			return false;
		}

		/**
		 * Whether the integration is allowed to load.
		 *
		 * @since 1.9.5
		 *
		 * @param bool $is_allowed Integration loading state.
		 */
		return (bool) apply_filters( 'wpforms_integrations_square_allow_load', true );
	}

	/**
	 * Load the integration.
	 *
	 * @since 1.9.5
	 */
	public function load() {

		$this->load_admin_entries();
		$this->load_settings();
		$this->load_connect();
		$this->load_field();
		$this->load_frontend();
		$this->load_integrations();
		$this->load_payments_actions();
		$this->load_builder();
		$this->load_webhooks();
		$this->load_tasks();

		// Bail early for paid users with active Square addon.
		if ( Helpers::is_pro() ) {
			return;
		}

		$this->load_builder_settings();
		$this->load_processing();
	}

	/**
	 * Load admin entries functionality.
	 *
	 * @since 1.9.5
	 */
	private function load_admin_entries() {

		if ( wpforms_is_admin_page( 'entries' ) ) {
			( new Admin\Entries() )->init();
		}
	}

	/**
	 * Load Square settings.
	 *
	 * @since 1.9.5
	 */
	private function load_settings() {

		if ( wpforms_is_admin_page( 'settings', 'payments' ) ) {
			( new Admin\Settings() )->init();
			( new Admin\Notices() )->init();
		}
	}

	/**
	 * Load connect handler.
	 *
	 * @since 1.9.5
	 */
	private function load_connect() {

		( new Admin\Connect() )->init();
	}

	/**
	 * Load Square field.
	 *
	 * @since 1.9.5
	 */
	private function load_field() {

		// phpcs:disable WordPress.Security.NonceVerification
		$is_elementor =
			( ! empty( $_POST['action'] ) && $_POST['action'] === 'elementor_ajax' ) ||
			( ! empty( $_GET['action'] ) && $_GET['action'] === 'elementor' );
		// phpcs:enable WordPress.Security.NonceVerification

		if ( $is_elementor || ! is_admin() || wp_doing_ajax() || wpforms_is_admin_page( 'builder' ) ) {
			( new Fields\Square() )->init();
		}
	}

	/**
	 * Load builder functionality.
	 *
	 * @since 1.9.5
	 */
	private function load_builder() {

		if ( wpforms_is_admin_page( 'builder' ) ) {
			( new Admin\Builder\Enqueues() )->init();
		}
	}

	/**
	 * Load builder settings functionality.
	 *
	 * @since 1.9.5
	 */
	private function load_builder_settings() {

		if ( wpforms_is_admin_page( 'builder' ) ) {
			( new Admin\Builder\Settings() )->init();
			( new Admin\Builder\Notifications() )->init();
		}
	}

	/**
	 * Load payments actions.
	 *
	 * @since 1.9.5
	 */
	private function load_payments_actions() {

		if ( ! Connection::get() ) {
			return;
		}

		( new Admin\Payments\SingleActionsHandler() )->init();
	}

	/**
	 * Load frontend functionality.
	 *
	 * @since 1.9.5
	 */
	private function load_frontend() {

		if ( ! is_admin() ) {
			( new Frontend() )->init();
		}
	}

	/**
	 * Load payment form processing.
	 *
	 * @since 1.9.5
	 */
	private function load_processing() {

		if ( ! is_admin() || wpforms_is_frontend_ajax() ) {
			( new Process() )->init();
		}
	}

	/**
	 * Load integrations.
	 *
	 * @since 1.9.5
	 */
	private function load_integrations() {

		( new Integrations\Loader() )->init();
	}

	/**
	 * Load webhooks.
	 *
	 * @since 1.9.5
	 */
	private function load_webhooks() {

		( new Api\WebhookRoute() )->init();
		( new WebhooksHealthCheck() )->init();
	}

	/**
	 * Load tasks.
	 *
	 * @since 1.9.5
	 */
	private function load_tasks() {

		if ( ! Connection::get() ) {
			return;
		}

		( new Tasks() )->init();
	}
}
