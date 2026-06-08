<?php

// phpcs:disable Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpExpressionResultUnusedInspection */
/** @noinspection PhpPropertyOnlyWrittenInspection */
/** @noinspection UnusedConstructorDependenciesInspection */
/** @noinspection PhpUnusedAliasInspection */
// phpcs:enable Generic.Commenting.DocComment.MissingShort

namespace WPForms\Migrations;

// phpcs:disable WPForms.PHP.UseStatement.UnusedUseStatement
use WPForms\Migrations\Migrations;
use WPForms\Migrations\Tasks\UpgradeBaseTask;
use WPForms\Pro\Migrations\Migrations as MigrationsPro;
// phpcs:enable WPForms.PHP.UseStatement.UnusedUseStatement

/**
 * Class UpgradeBase contains both Lite and Pro plugin upgrade methods.
 *
 * @since 1.7.5
 */
abstract class UpgradeBase {

	/**
	 * Migration class instance.
	 *
	 * @since 1.7.5
	 *
	 * @var Migrations|MigrationsPro
	 */
	protected $migrations;

	/**
	 * Primary class constructor.
	 *
	 * @since 1.7.5
	 *
	 * @param Migrations|MigrationsPro $migrations Instance of Migrations class.
	 */
	public function __construct( $migrations ) {

		$this->migrations = $migrations;
	}

	/**
	 * Run upgrade.
	 *
	 * @since 1.7.5
	 *
	 * @return bool|null Upgrade result:
	 *                   true  - the upgrade completed successfully,
	 *                   false - in the case of failure,
	 *                   null  - upgrade started but not yet finished (background task).
	 */
	abstract public function run();

	/**
	 * Run the async upgrade via an Action Scheduler (AS) task.
	 * The AS task has to support STATUS option with START, IN_PROGRESS, and COMPLETED values.
	 * Also, the AS task must have the init() method.
	 *
	 * @since 1.7.5
	 *
	 * @param string $classname Classname of an async AS task.
	 *
	 * @return bool|null Upgrade result:
	 *                   true  - the upgrade completed successfully,
	 *                   false - in the case of failure,
	 *                   null  - upgrade started but not yet finished (background task).
	 */
	protected function run_async( string $classname ) { // phpcs:ignore WPForms.PHP.HooksMethod.InvalidPlaceForAddingHooks

		$instance = new $classname();

		if ( $this->is_completed( $instance ) ) {
			return true;
		}

		$this->maybe_start( $instance );

		// Class Tasks does not exist at this point, so we have to add an action on init.
		add_action(
			'init',
			static function () use ( $instance ) {

				$instance->init();
			},
			PHP_INT_MAX
		);

		return null;
	}

	/**
	 * Determine if the async AS task is completed.
	 *
	 * @since 1.9.5
	 *
	 * @param object $instance Instance of an async AS task.
	 *
	 * @use UpgradeBaseTask::is_completed()
	 *
	 * @return bool
	 */
	private function is_completed( $instance ): bool {

		if ( method_exists( $instance, 'is_completed' ) ) {
			return $instance->is_completed();
		}

		// Legacy tasks.
		$status = get_option( $instance::STATUS );

		if ( $status === $instance::COMPLETED ) {
			delete_option( $instance::STATUS );

			return true;
		}

		return false;
	}

	/**
	 * Start the async AS task if it wasn't started yet.
	 *
	 * @since 1.9.5
	 *
	 * @param object $instance Instance of an async AS task.
	 *
	 * @use UpgradeBaseTask::maybe_start()
	 */
	private function maybe_start( $instance ): void {

		if ( method_exists( $instance, 'maybe_start' ) ) {
			$instance->maybe_start();

			return;
		}

		// Legacy tasks.
		$status = get_option( $instance::STATUS );

		if ( ! $status ) {
			update_option( $instance::STATUS, $instance::START );
		}
	}
}
