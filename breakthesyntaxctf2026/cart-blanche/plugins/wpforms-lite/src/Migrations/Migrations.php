<?php

// phpcs:disable Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpIllegalPsrClassPathInspection */
/** @noinspection AutoloadingIssuesInspection */
// phpcs:enable Generic.Commenting.DocComment.MissingShort

namespace WPForms\Migrations;

/**
 * Class Migrations handles Lite plugin upgrade routines.
 *
 * @since 1.7.5
 */
class Migrations extends Base {

	/**
	 * WP option name to store the migration version.
	 *
	 * @since 1.5.9
	 */
	public const MIGRATED_OPTION_NAME = 'wpforms_versions_lite';

	/**
	 * Name of the core plugin used in log messages.
	 *
	 * @since 1.7.5
	 */
	protected const PLUGIN_NAME = 'WPForms';

	/**
	 * Upgrade classes.
	 *
	 * @since 1.7.5
	 */
	public const UPGRADE_CLASSES = [
		'Upgrade159',
		'Upgrade1672',
		'Upgrade168',
		'Upgrade175',
		'Upgrade1751',
		'Upgrade177',
		'Upgrade182',
		'Upgrade183',
		'Upgrade184',
		'Upgrade186',
		'Upgrade187',
		'Upgrade1_9_1',
		'Upgrade1_9_2',
		'Upgrade1_9_7',
		'Upgrade1_9_8_6',
	];
}
