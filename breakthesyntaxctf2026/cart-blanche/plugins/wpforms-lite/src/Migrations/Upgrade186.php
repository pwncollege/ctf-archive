<?php

namespace WPForms\Migrations;

use WPForms\Tasks\Actions\DomainAutoRegistrationTask;

/**
 * Class upgrade for 1.8.6 release.
 *
 * @since 1.8.6
 *
 * @noinspection PhpUnused
 */
class Upgrade186 extends UpgradeBase {

	/**
	 * Run upgrade.
	 *
	 * @since 1.8.6
	 *
	 * @return bool|null
	 */
	public function run() {

		return $this->run_async( DomainAutoRegistrationTask::class );
	}
}
