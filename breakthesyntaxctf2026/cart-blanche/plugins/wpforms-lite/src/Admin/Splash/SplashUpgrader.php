<?php

namespace WPForms\Admin\Splash;

use WPForms\Migrations\Base as MigrationsBase;

/**
 * Splash upgrader.
 *
 * @since 1.8.7
 */
class SplashUpgrader {

	use SplashTrait;

	/**
	 * Initialize class.
	 *
	 * @since 1.8.7
	 */
	public function init(): void {

		$this->hooks();
	}

	/**
	 * Hooks.
	 *
	 * @since 1.8.7
	 */
	private function hooks(): void {

		// Update splash data after plugin update.
		add_action( 'wpforms_migrations_base_core_upgraded', [ $this, 'update_splash_data_on_migration' ], 10, 2 );
	}

	/**
	 * Update splash modal data on migration.
	 *
	 * @since 1.8.8
	 *
	 * @param string|mixed   $previous_version Previous plugin version.
	 * @param MigrationsBase $migrations_obj   Migrations object.
	 *
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function update_splash_data_on_migration( $previous_version, MigrationsBase $migrations_obj ): void { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed

		// Force update splash data cache.
		$splash_cache_obj = wpforms()->obj( 'splash_cache' );

		if ( ! $splash_cache_obj ) {
			return;
		}

		$splash_cache_obj->update( true );
	}
}
