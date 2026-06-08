<?php

namespace WPForms\Migrations\Tasks;

use RuntimeException;
use WPForms\Tasks\Task;
use WPForms\Tasks\Meta;

/**
 * Upgrade task base class.
 *
 * @since 1.9.5
 */
abstract class UpgradeBaseTask extends Task {

	/**
	 * Start status.
	 *
	 * @since 1.9.5
	 */
	private const START = 'start';

	/**
	 * In progress status.
	 *
	 * @since 1.9.5
	 */
	private const IN_PROGRESS = 'in progress';

	/**
	 * Completed status.
	 *
	 * @since 1.9.5
	 */
	private const COMPLETED = 'completed';

	/**
	 * Task action name.
	 *
	 * @since 1.9.5
	 *
	 * @var string
	 */
	private $action;

	/**
	 * Option name to store the task status.
	 *
	 * @since 1.9.5
	 *
	 * @var string
	 */
	private $status_option;

	/**
	 * Class constructor.
	 *
	 * @since 1.9.5
	 *
	 * @throws RuntimeException If class name doesn't contain a version.
	 */
	public function __construct() {

		$class_parts      = explode( '\\', static::class );
		$short_class_name = end( $class_parts );
		$short_class_name = strtolower( preg_replace( '/(?<!^)[A-Z]/', '_$0', $short_class_name ) );

		$this->action        = 'wpforms_process_migration_' . $short_class_name;
		$this->status_option = $this->action . '_status';

		parent::__construct( $this->action );
	}

	/**
	 * Get current task status.
	 *
	 * @since 1.9.5
	 *
	 * @return string
	 */
	private function get_status(): string {

		return (string) get_option( $this->status_option );
	}

	/**
	 * Update task status.
	 * Use the constants self::START, self::IN_PROGRESS, self::COMPLETED.
	 *
	 * @since 1.9.5
	 *
	 * @param string $status New status.
	 *
	 * @return void
	 */
	private function update_status( string $status ): void {

		update_option( $this->status_option, $status );
	}

	/**
	 * Initialize the task with all the proper checks.
	 *
	 * @since 1.9.5
	 */
	public function init(): void {

		$status = $this->get_status();

		if ( ! $status || $status === self::COMPLETED ) {
			return;
		}

		$this->set_task_properties();
		$this->hooks();

		if ( $status !== self::START ) {
			return;
		}

		$this->update_status( self::IN_PROGRESS );

		$this->init_migration();
	}

	/**
	 * Create a task.
	 *
	 * @param array $args Task arguments.
	 *
	 * @since 1.9.5
	 *
	 * @return void
	 */
	protected function create_task( array $args = [] ): void {

		$tasks = wpforms()->obj( 'tasks' );

		if ( ! $tasks ) {
			wpforms_log(
				'Migration error',
				[
					'error'  => "Object is not available: `null` returned by `wpforms()->obj( 'tasks' )`",
					'class'  => static::class,
					'method' => __METHOD__,
				],
				[
					'type'  => 'error',
					'force' => true,
				]
			);

			return;
		}

		$tasks
			->create( $this->action )
			->async()
			->params( ...$args )
			->register();
	}

	/**
	 * Set task properties.
	 *
	 * @since 1.9.5
	 *
	 * @return void
	 */
	abstract protected function set_task_properties(): void;

	/**
	 * Add hooks.
	 *
	 * @since 1.9.5
	 */
	protected function hooks(): void {

		add_action( $this->action, [ $this, 'migrate' ] );
		add_action( 'action_scheduler_after_process_queue', [ $this, 'after_process_queue' ] );
	}

	/**
	 * Migrate an entry.
	 *
	 * @since 1.9.5
	 *
	 * @param int $meta_id Action meta id.
	 *
	 * @noinspection PhpMissingParamTypeInspection
	 */
	public function migrate( $meta_id ): void {

		$params = ( new Meta() )->get( $meta_id );

		if ( ! $params || ! isset( $params->data ) ) {
			return;
		}

		$this->process_migration( (array) $params->data );
	}

	/**
	 * Execute an async migration task.
	 *
	 * @since 1.9.5
	 *
	 * @param array $data Migration data.
	 *
	 * @return void
	 */
	abstract protected function process_migration( array $data ): void;

	/**
	 * Set the status as completed after processing all queue action.
	 *
	 * @since 1.9.5
	 *
	 * @return void
	 */
	public function after_process_queue(): void {

		$tasks = wpforms()->obj( 'tasks' );

		if ( ! $tasks ) {
			wpforms_log(
				'Migration error',
				[
					'error'  => "Object is not available: `null` returned by `wpforms()->obj( 'tasks' )`",
					'class'  => static::class,
					'method' => __METHOD__,
				],
				[
					'type'  => 'error',
					'force' => true,
				]
			);

			return;
		}

		if ( $tasks->is_scheduled( $this->action ) ) {
			return;
		}

		$this->finish_migration();
	}

	/**
	 * Finish migration.
	 *
	 * @since 1.9.5
	 *
	 * @return void
	 */
	protected function finish_migration(): void {

		$this->update_status( self::COMPLETED );
	}

	/**
	 * Create migration tasks using the `create_task` method
	 * or `finish_migration` method to complete it.
	 *
	 * @since 1.9.5
	 *
	 * @return void
	 */
	abstract protected function init_migration(): void;

	/**
	 * Determine if the task is completed.
	 * Remove the status option to allow running the task again.
	 *
	 * @since 1.9.5
	 *
	 * @return bool True if a task is completed.
	 */
	public function is_completed(): bool {

		$status       = $this->get_status();
		$is_completed = $status === self::COMPLETED;

		if ( $is_completed ) {
			delete_option( $this->status_option );
		}

		return $is_completed;
	}

	/**
	 * Maybe start the task.
	 *
	 * @since 1.9.5
	 */
	public function maybe_start(): void {

		$status = $this->get_status();

		if ( ! $status ) {
			$this->update_status( self::START );
		}
	}
}
