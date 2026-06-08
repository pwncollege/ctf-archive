<?php

namespace WPForms\Tasks\Actions;

use WPForms\Tasks\Task;
use WPForms\Integrations\Stripe\Api\DomainManager;
use WPForms\Integrations\Stripe\Helpers;

/**
 * Class DomainAutoRegistrationTask.
 *
 * @since 1.8.6
 */
class DomainAutoRegistrationTask extends Task {

	/**
	 * Action name.
	 *
	 * @since 1.8.6
	 */
	const ACTION = 'wpforms_process_domain_auto_registration';

	/**
	 * Status option name.
	 *
	 * @since 1.8.6
	 */
	const STATUS = 'wpforms_process_domain_auto_registration_status';

	/**
	 * Start status.
	 *
	 * @since 1.8.6
	 */
	const START = 'start';

	/**
	 * In progress status.
	 *
	 * @since 1.8.6
	 */
	const IN_PROGRESS = 'in_progress';

	/**
	 * Completed status.
	 *
	 * @since 1.8.6
	 */
	const COMPLETED = 'completed';

	/**
	 * Domain manager.
	 *
	 * @since 1.8.6
	 *
	 * @var DomainManager
	 */
	private $domain_manager;

	/**
	 * Log title.
	 *
	 * @since 1.9.1
	 *
	 * @var string
	 */
	protected $log_title = 'Migration';

	/**
	 * Constructor.
	 *
	 * @since 1.8.6
	 */
	public function __construct() {

		parent::__construct( self::ACTION );

		$this->domain_manager = new DomainManager();
	}

	/**
	 * Process the task.
	 *
	 * @since 1.8.6
	 */
	public function init() {

		// Get a task status.
		$status = get_option( self::STATUS );

		// This task is run in \WPForms\Migrations\Upgrade186::run(),
		// and started in \WPForms\Migrations\UpgradeBase::run_async().
		// Bail out if a task is not started or completed.
		if ( ! $status || $status === self::COMPLETED ) {
			return;
		}

		// Mark that the task is in progress.
		update_option( self::STATUS, self::IN_PROGRESS );

		// Register hooks.
		$this->hooks();

		$tasks = wpforms()->obj( 'tasks' );

		// Add new if none exists.
		if ( $tasks->is_scheduled( self::ACTION ) !== false ) {
			return;
		}

		$tasks->create( self::ACTION )->async()->register();
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.8.6
	 */
	private function hooks() {

		add_action( self::ACTION, [ $this, 'process' ] );
	}

	/**
	 * Process the task.
	 *
	 * @since 1.8.6
	 */
	public function process() {

		// If the Stripe account is connected, then try to register domain.
		if ( Helpers::has_stripe_keys() && $this->domain_manager->validate() ) {
			$this->log( 'Stripe Payments: Stripe domain auto registration during migration to WPForms 1.8.6.' );
		}

		// Mark that the task is completed.
		update_option( self::STATUS, self::COMPLETED );
	}
}
