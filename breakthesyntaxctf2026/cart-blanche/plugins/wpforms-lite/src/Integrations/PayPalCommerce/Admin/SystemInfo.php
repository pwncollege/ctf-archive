<?php

namespace WPForms\Integrations\PayPalCommerce\Admin;

use WPForms\Integrations\PayPalCommerce\Connection;
use WPForms\Integrations\PayPalCommerce\Helpers;

/**
 * PayPal Commerce System Information.
 *
 * @since 1.10.0
 */
class SystemInfo {

	/**
	 * Init class.
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

		add_filter( 'wpforms_admin_tools_views_system_wpforms_info', [ $this, 'add_paypal_commerce_info' ] );
	}

	/**
	 * Add PayPal Commerce connection information to system info.
	 *
	 * @since 1.10.0
	 *
	 * @param array $values Associative array of WPForms information key-value pairs.
	 *
	 * @return array
	 *
	 * @noinspection PhpMissingParamTypeInspection
	 */
	public function add_paypal_commerce_info( $values ): array {

		$values          = (array) $values;
		$connection_type = Helpers::is_legacy() ? 'first_party' : Connection::TYPE_THIRD_PARTY;

		$values['PayPal Commerce Connection'] = $connection_type;

		return $values;
	}
}
