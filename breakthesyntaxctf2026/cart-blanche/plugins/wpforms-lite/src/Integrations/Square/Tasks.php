<?php

namespace WPForms\Integrations\Square;

use WPForms\Tasks\Actions\SquareSubscriptionTransactionIDTask;

/**
 * Register tasks.
 *
 * @since 1.9.5
 */
class Tasks {

	/**
	 * Initialize.
	 *
	 * @since 1.9.5
	 */
	public function init() {

		$this->hooks();
	}

	/**
	 * Frontend hooks.
	 *
	 * @since 1.9.5
	 */
	private function hooks() {

		add_filter( 'wpforms_tasks_get_tasks', [ $this, 'register' ] );
	}

	/**
	 * Add class to registered tasks array.
	 *
	 * @since 1.9.5
	 *
	 * @param array $tasks Array of tasks.
	 *
	 * @return array
	 */
	public function register( $tasks ): array {

		$tasks = (array) $tasks;

		$tasks[] = SquareSubscriptionTransactionIDTask::class;

		return $tasks;
	}
}
