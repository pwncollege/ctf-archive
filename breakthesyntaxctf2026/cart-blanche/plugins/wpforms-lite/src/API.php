<?php

namespace WPForms;

use WPForms\Admin\Tools\Views\Import;

/**
 * Class API.
 *
 * @since 1.8.6
 */
class API {

	/**
	 * Registry.
	 * Contains name of the class and method to be called.
	 * For non-static methods, should contain the id to operate via wpforms->get( 'class' ).
	 *
	 * @todo Add non-static methods processing.
	 *
	 * @since 1.8.6
	 *
	 * @var array[]
	 */
	private $registry = [
		'import_forms' => [
			'class'  => Import::class,
			'method' => 'import_forms',
		],
	];

	/**
	 * Magic method to call a method from registry.
	 *
	 * @since 1.8.6
	 *
	 * @param string $name Method name.
	 * @param array  $args Arguments.
	 *
	 * @return mixed|null
	 */
	public function __call( string $name, array $args ) {

		$callback = $this->registry[ $name ] ?? null;

		if ( $callback === null ) {
			return null;
		}

		return call_user_func( [ $callback['class'], $callback['method'] ], ...$args );
	}
}
