<?php

namespace WPForms\Integrations\Square\Integrations;

/**
 * Integrations loader.
 *
 * @since 1.9.5
 */
class Loader {

	/**
	 * Loaded integrations.
	 *
	 * @since 1.9.5
	 *
	 * @var array
	 */
	private $integrations = [];

	/**
	 * Classes to register.
	 *
	 * @since 1.9.5
	 */
	private const CLASSES = [
		'Divi',
		'Elementor',
		'BlockEditor',
	];

	/**
	 * Init.
	 *
	 * @since 1.9.5
	 */
	public function init() {

		foreach ( self::CLASSES as $class_name ) {
			$this->load_integration( $class_name );
		}
	}

	/**
	 * Load an integration.
	 *
	 * @since 1.9.5
	 *
	 * @param string $class_name Class name to register.
	 */
	private function load_integration( string $class_name ) {

		if ( isset( $this->integrations[ $class_name ] ) ) {
			return;
		}

		$full_class_name = 'WPForms\Integrations\Square\Integrations\\' . sanitize_text_field( $class_name );

		$integration = class_exists( $full_class_name ) ? new $full_class_name() : null;

		if ( $integration === null || ! $integration->allow_load() ) {
			return;
		}

		$integration->hooks();

		$this->integrations[ $class_name ] = $integration;
	}
}
