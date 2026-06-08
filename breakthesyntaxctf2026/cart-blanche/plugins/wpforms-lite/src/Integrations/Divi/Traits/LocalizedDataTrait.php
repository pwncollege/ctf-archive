<?php

namespace WPForms\Integrations\Divi\Traits;

/**
 * Trait for managing localized data for Divi modules.
 *
 * @since 1.9.9
 */
trait LocalizedDataTrait {

	/**
	 * Localized data storage.
	 *
	 * @since 1.9.9
	 *
	 * @var array
	 */
	protected $localized_data = [];

	/**
	 * Get the localized data.
	 *
	 * @since 1.9.9
	 *
	 * @return array Localized data array.
	 */
	public function get_localized_data(): array {

		return $this->localized_data;
	}

	/**
	 * Set the localized data.
	 *
	 * @since 1.9.9
	 *
	 * @param array $script_data Localized data to set.
	 *
	 * @return self Returns the instance for method chaining.
	 */
	public function set_localized_data( array $script_data ): object {

		$this->localized_data = $script_data;

		return $this;
	}
}
