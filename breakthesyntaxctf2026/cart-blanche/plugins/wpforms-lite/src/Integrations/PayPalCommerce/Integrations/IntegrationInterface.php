<?php

namespace WPForms\Integrations\PayPalCommerce\Integrations;

/**
 * Interface defines required methods for integrations to work properly.
 *
 * @since 1.10.0
 */
interface IntegrationInterface {

	/**
	 * Indicate if the current integration is allowed to load.
	 *
	 * @since 1.10.0
	 *
	 * @return bool
	 */
	public function allow_load(): bool;

	/**
	 * Register hooks.
	 *
	 * @since 1.10.0
	 */
	public function hooks(): void;

	/**
	 * Determine whether the integration page is loaded.
	 *
	 * @since 1.10.0
	 *
	 * @return bool
	 */
	public function is_integration_page_loaded(): bool;
}
