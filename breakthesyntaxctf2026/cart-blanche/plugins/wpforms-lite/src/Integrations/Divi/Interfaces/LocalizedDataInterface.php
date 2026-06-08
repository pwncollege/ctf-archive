<?php

namespace WPForms\Integrations\Divi\Interfaces;

/**
 * Interface for managing localized data for Divi modules.
 *
 * @since 1.9.9
 */
interface LocalizedDataInterface {

	/**
	 * Get the localized data.
	 *
	 * @since 1.9.9
	 *
	 * @return array Localized data array.
	 */
	public function get_localized_data(): array;

	/**
	 * Set the localized data.
	 *
	 * @since 1.9.9
	 *
	 * @param array $script_data Localized data to set.
	 *
	 * @return self Returns the instance for method chaining.
	 */
	public function set_localized_data( array $script_data ): object;
}
