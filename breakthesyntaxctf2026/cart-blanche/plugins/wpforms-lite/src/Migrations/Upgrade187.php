<?php

namespace WPForms\Migrations;

use WPForms\Admin\Builder\TemplatesCache;
use WPForms\Tasks\Actions\StripeLinkSubscriptionsTask;

/**
 * Class upgrade for 1.8.7 release.
 *
 * @since 1.8.7
 *
 * @noinspection PhpUnused
 */
class Upgrade187 extends UpgradeBase {

	/**
	 * Run upgrade.
	 *
	 * @since 1.8.7
	 *
	 * @return bool|null
	 */
	public function run() {

		$sync_result  = $this->update_templates_cache() && $this->maybe_create_logs_table();
		$async_result = $this->run_async( StripeLinkSubscriptionsTask::class );

		return $async_result === null ? null : $sync_result && $async_result;
	}

	/**
	 * Update templates' cache.
	 *
	 * @since 1.8.7
	 *
	 * @return bool
	 */
	private function update_templates_cache(): bool {

		$templates_cache = new TemplatesCache();

		$templates_cache->init();
		$templates_cache->update();

		return true;
	}

	/**
	 * Maybe create logs' table.
	 * Previously, logs' table was created dynamically on the first access to the Tools->Logs admin page.
	 * As from 1.8.7, we create it only once during the activation of the plugin.
	 * So, the table may not exist, and we must maybe create it during migration to 1.8.7.
	 *
	 * @since 1.8.7
	 *
	 * @return bool
	 */
	private function maybe_create_logs_table(): bool {

		$log = wpforms()->obj( 'log' );

		if ( ! $log ) {
			return false;
		}

		$log->create_table();

		return true;
	}
}
