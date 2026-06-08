<?php

namespace WPForms\Migrations;

/**
 * Class upgrade for 1.9.7 release.
 *
 * @since 1.9.7
 */
class Upgrade1_9_7 extends UpgradeBase {

	/**
	 * Run upgrade.
	 *
	 * @since 1.9.7
	 */
	public function run(): bool {

		// Force update splash data cache.
		wpforms()->obj( 'splash_cache' )->update( true );

		return true;
	}
}
