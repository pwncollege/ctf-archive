<?php

namespace WPForms\Integrations\PayPalCommerce\Integrations;

/**
 * Main loader.
 *
 * @since 1.10.0
 */
class Loader {

	/**
	 * Loaded integrations.
	 *
	 * @since 1.10.0
	 *
	 * @var IntegrationInterface[]
	 */
	private $integrations = [];

	/**
	 * Classes to register.
	 *
	 * @since 1.10.0
	 */
	private const CLASSES = [
		'Divi',
		'Elementor',
		'BlockEditor',
	];

	/**
	 * Init class constructor.
	 *
	 * @since 1.10.0
	 */
	public function __construct() {

		$this->init();
	}

	/**
	 * Init.
	 *
	 * @since 1.10.0
	 */
	private function init(): void {

		foreach ( self::CLASSES as $class_name ) {
			$this->load_integration( $class_name );
		}
	}

	/**
	 * Load an integration.
	 *
	 * @since 1.10.0
	 *
	 * @param string $class_name Class name to register.
	 */
	private function load_integration( string $class_name ): void {

		if ( isset( $this->integrations[ $class_name ] ) ) {
			return;
		}

		$full_class_name = 'WPForms\Integrations\PayPalCommerce\Integrations\\' . sanitize_text_field( $class_name );

		$integration = class_exists( $full_class_name ) ? new $full_class_name() : null;

		if ( $integration === null || ! $integration->allow_load() ) {
			return;
		}

		$integration->hooks();

		$this->integrations[ $class_name ] = $integration;
	}

	/**
	 * Indicate if an integration page is loaded.
	 *
	 * @since 1.10.0
	 *
	 * @return bool
	 */
	public function is_integration_page_loaded(): bool {

		static $loaded;

		if ( $loaded !== null ) {
			return $loaded;
		}

		$loaded = false;

		foreach ( $this->integrations as $integration ) {

			if ( $integration->is_integration_page_loaded() ) {
				$loaded = true;

				break;
			}
		}

		return $loaded;
	}
}
