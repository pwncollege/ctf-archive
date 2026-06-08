<?php

namespace WPForms\Integrations\Square\Integrations;

/**
 * Interface defines required methods for integrations to work properly.
 *
 * @since 1.9.5
 */
interface IntegrationInterface {

	/**
	 * Indicate whether current integration is allowed to load.
	 *
	 * @since 1.9.5
	 *
	 * @return bool
	 */
	public function allow_load(): bool;

	/**
	 * Register hooks.
	 *
	 * @since 1.9.5
	 */
	public function hooks();

	/**
	 * Determine whether editor page is loaded.
	 *
	 * @since 1.9.5
	 *
	 * @return bool
	 */
	public function is_editor_page(): bool;
}
