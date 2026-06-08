<?php

namespace WPForms\Tasks\Actions;

use WPForms\Tasks\Task;
use WPForms\Integrations\Stripe\Api\WebhooksManager;
use WPForms\Integrations\Stripe\Helpers;

/**
 * Class WebhooksAutoConfigurationTask.
 *
 * @since 1.8.4
 */
class WebhooksAutoConfigurationTask extends Task {

	/**
	 * Action name.
	 *
	 * @since 1.8.4
	 */
	const ACTION = 'wpforms_process_webhooks_auto_configuration';

	/**
	 * Status option name.
	 *
	 * @since 1.8.4
	 */
	const STATUS = 'wpforms_process_webhooks_auto_configuration_status';

	/**
	 * Start status.
	 *
	 * @since 1.8.4
	 */
	const START = 'start';

	/**
	 * In progress status.
	 *
	 * @since 1.8.4
	 */
	const IN_PROGRESS = 'in_progress';

	/**
	 * Completed status.
	 *
	 * @since 1.8.4
	 */
	const COMPLETED = 'completed';

	/**
	 * Webhooks manager.
	 *
	 * @since 1.8.4
	 *
	 * @var WebhooksManager
	 */
	private $webhooks_manager;

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
	 * @since 1.8.4
	 */
	public function __construct() {

		parent::__construct( self::ACTION );

		$this->webhooks_manager = new WebhooksManager();
	}

	/**
	 * Process the task.
	 *
	 * @since 1.8.4
	 */
	public function init() {

		// Get a task status.
		$status = get_option( self::STATUS );

		// This task is run in \WPForms\Migrations\Upgrade184::run(),
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
	 * @since 1.8.4
	 */
	private function hooks() {

		add_action( self::ACTION, [ $this, 'process' ] );
	}

	/**
	 * Process the task.
	 *
	 * @since 1.8.4
	 */
	public function process() {

		// If the Stripe account is connected, then try to configure webhooks.
		if ( Helpers::has_stripe_keys() && $this->webhooks_manager->connect() ) {
			$this->log( 'Stripe Payments: Webhooks configured during migration to WPForms 1.8.4.' );
		}

		// Mark that the task is completed.
		update_option( self::STATUS, self::COMPLETED );
	}
}
