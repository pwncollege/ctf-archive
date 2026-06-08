<?php

namespace WPMailSMTP\Tasks;

use ActionScheduler_Action;
use ActionScheduler_DataController;
use ActionScheduler_DBStore;
use WPMailSMTP\Tasks\Queue\CleanupQueueTask;
use WPMailSMTP\Tasks\Queue\ProcessQueueTask;
use WPMailSMTP\Tasks\Queue\SendEnqueuedEmailTask;
use WPMailSMTP\Tasks\Reports\SummaryEmailTask;

/**
 * Class Tasks manages the tasks queue and provides API to work with it.
 *
 * @since 2.1.0
 */
class Tasks {

	/**
	 * Group that will be assigned to all actions.
	 *
	 * @since 2.1.0
	 */
	const GROUP = 'wp_mail_smtp';

	/**
	 * WP Mail SMTP pending or in-progress actions.
	 *
	 * @since 3.3.0
	 *
	 * @var array
	 */
	private static $active_actions = null;

	/**
	 * Perform certain things on class init.
	 *
	 * @since 2.1.0
	 */
	public function init() { // phpcs:ignore WPForms.PHP.HooksMethod.InvalidPlaceForAddingHooks

		// Hide the Action Scheduler admin menu item.
		add_action( 'admin_menu', [ $this, 'admin_hide_as_menu' ], PHP_INT_MAX );

		// Skip tasks registration if Action Scheduler is not usable yet.
		if ( ! self::is_usable() ) {
			return;
		}

		// Register tasks.
		foreach ( $this->get_tasks() as $task ) {
			if ( ! is_subclass_of( $task, '\WPMailSMTP\Tasks\Task' ) ) {
				continue;
			}

			$new_task = new $task();

			// Run the init method, if a task has one defined.
			if ( method_exists( $new_task, 'init' ) ) {
				$new_task->init();
			}
		}

		// Remove scheduled action meta after action execution.
		add_action( 'action_scheduler_after_execute', [ $this, 'clear_action_meta' ], PHP_INT_MAX, 2 );

		// Cancel tasks on plugin deactivation.
		register_deactivation_hook( WPMS_PLUGIN_FILE, [ $this, 'cancel_all' ] );
	}

	/**
	 * Get the list of default scheduled tasks.
	 * Tasks, that are fired under certain specific circumstances
	 * (like sending emails) are not listed here.
	 *
	 * @since 2.1.0
	 *
	 * @return Task[] List of tasks classes.
	 */
	public function get_tasks() {

		$tasks = [
			SummaryEmailTask::class,
			DebugEventsCleanupTask::class,
			ProcessQueueTask::class,
			CleanupQueueTask::class,
			SendEnqueuedEmailTask::class,
			NotificationsUpdateTask::class,
		];

		/**
		 * Filters list of tasks classes.
		 *
		 * @since 2.1.2
		 *
		 * @param Task[] $tasks List of tasks classes.
		 */
		return apply_filters( 'wp_mail_smtp_tasks_get_tasks', $tasks );
	}

	/**
	 * Hide Action Scheduler admin area when not in debug mode.
	 *
	 * @since 2.1.0
	 */
	public function admin_hide_as_menu() {

		$plugin_exceptions = [
			'woocommerce/woocommerce.php',
			'action-scheduler/action-scheduler.php',
		];

		/**
		 * Filters the list of plugins for which
		 * the Action Scheduler Tools ->Scheduled Actions menu item
		 * should remain visible.
		 *
		 * @since 4.3.0
		 *
		 * @param array $plugin_exceptions List of plugins exceptions.
		 */
		$plugin_exceptions = apply_filters( 'wp_mail_smtp_tasks_tasks_action_scheduler_tools_plugin_exceptions', $plugin_exceptions );
		$hide_as_menu      = empty( array_filter( $plugin_exceptions, 'is_plugin_active' ) );

		// Filter to redefine that WP Mail SMTP hides Tools > Action Scheduler menu item.
		if ( apply_filters( 'wp_mail_smtp_tasks_admin_hide_as_menu', $hide_as_menu ) ) {
			remove_submenu_page( 'tools.php', 'action-scheduler' );
		}
	}

	/**
	 * Create a new task.
	 * Used for "inline" tasks, that require additional information
	 * from the plugin runtime before they can be scheduled.
	 *
	 * Example:
	 *     wp_mail_smtp()->get( 'tasks' )
	 *              ->create( 'i_am_the_dude' )
	 *              ->async()
	 *              ->params( 'The Big Lebowski', 1998 )
	 *              ->register();
	 *
	 * This `i_am_the_dude` action will be later processed as:
	 *     add_action( 'i_am_the_dude', 'thats_what_you_call_me' );
	 *
	 * @since 2.1.0
	 *
	 * @param string $action Action that will be used as a hook.
	 *
	 * @return Task
	 */
	public function create( $action ) {

		return new Task( $action );
	}

	/**
	 * Cancel all the AS actions for a group.
	 *
	 * @since 2.1.0
	 *
	 * @param string $group Group to cancel all actions for.
	 */
	public function cancel_all( $group = '' ) {

		if ( empty( $group ) ) {
			$group = self::GROUP;
		} else {
			$group = sanitize_key( $group );
		}

		if ( class_exists( 'ActionScheduler_DBStore' ) ) {
			ActionScheduler_DBStore::instance()->cancel_actions_by_group( $group );
		}
	}

	/**
	 * Remove all the AS actions for a group and remove group.
	 *
	 * @since 3.7.0
	 *
	 * @param string $group Group to remove all actions for.
	 */
	public function remove_all( $group = '' ) {

		global $wpdb;

		if ( empty( $group ) ) {
			$group = self::GROUP;
		} else {
			$group = sanitize_key( $group );
		}

		if (
			class_exists( 'ActionScheduler_DBStore' ) &&
			isset( $wpdb->actionscheduler_actions ) &&
			isset( $wpdb->actionscheduler_groups )
		) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching
			$group_id = $wpdb->get_var(
				$wpdb->prepare( "SELECT group_id FROM {$wpdb->actionscheduler_groups} WHERE slug=%s", $group )
			);

			if ( ! empty( $group_id ) ) {
				// Delete actions.
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->delete( $wpdb->actionscheduler_actions, [ 'group_id' => (int) $group_id ], [ '%d' ] );

				// Delete group.
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->delete( $wpdb->actionscheduler_groups, [ 'slug' => $group ], [ '%s' ] );
			}
		}
	}

	/**
	 * Clear the meta after action complete.
	 * Fired before an action is marked as completed.
	 *
	 * @since 3.5.0
	 *
	 * @param integer                $action_id Action ID.
	 * @param ActionScheduler_Action $action    Action name.
	 */
	public function clear_action_meta( $action_id, $action ) {

		$action_schedule = $action->get_schedule();

		if (
			$action_schedule === null ||
			$action_schedule->is_recurring() ||
			$action->get_group() !== self::GROUP
		) {
			return;
		}

		$hook_args = $action->get_args();

		if ( ! is_numeric( $hook_args[0] ) ) {
			return;
		}

		$meta = new Meta();

		$meta->delete( $hook_args[0] );
	}

	/**
	 * Whether ActionScheduler thinks that it has migrated or not.
	 *
	 * @since 2.1.0
	 *
	 * @return bool
	 */
	public static function is_usable() {

		// No tasks if ActionScheduler wasn't loaded.
		if ( ! class_exists( 'ActionScheduler_DataController' ) ) {
			return false;
		}

		return ActionScheduler_DataController::is_migration_complete();
	}

	/**
	 * Whether task has been scheduled and is pending.
	 *
	 * @since 2.1.0
	 *
	 * @param string $hook Hook to check for.
	 *
	 * @return bool|null
	 */
	public static function is_scheduled( $hook ) {

		// If ActionScheduler wasn't loaded, then no tasks are scheduled.
		if ( ! function_exists( 'as_next_scheduled_action' ) ) {
			return null;
		}

		if ( is_null( self::$active_actions ) ) {
			self::$active_actions = self::get_active_actions();
		}

		if ( in_array( $hook, self::$active_actions, true ) ) {
			return true;
		}

		// Action is not in the array, so it is not scheduled or belongs to another group.
		if ( function_exists( 'as_has_scheduled_action' ) ) {
			// This function more performant than `as_next_scheduled_action`, but it is available only since AS 3.3.0.
			return as_has_scheduled_action( $hook );
		} else {
			return as_next_scheduled_action( $hook ) !== false;
		}
	}

	/**
	 * Get all WP Mail SMTP pending or in-progress actions.
	 *
	 * @since 3.3.0
	 */
	private static function get_active_actions() {

		global $wpdb;

		$group = self::GROUP;
		$sql   = "SELECT a.hook FROM {$wpdb->prefix}actionscheduler_actions a
					JOIN {$wpdb->prefix}actionscheduler_groups g ON g.group_id = a.group_id
					WHERE g.slug = '$group' AND a.status IN ('in-progress', 'pending')";

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.NoCaching
		// phpcs:disable WordPress.DB.PreparedSQL.NotPrepared
		$results = $wpdb->get_results( $sql, 'ARRAY_N' );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.NoCaching
		// phpcs:enable WordPress.DB.PreparedSQL.NotPrepared

		return $results ? array_merge( ...$results ) : [];
	}
}
