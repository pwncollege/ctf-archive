<?php

namespace WPForms\Admin\Builder;

/**
 * Context Menu class.
 *
 * @since 1.8.6
 */
class ContextMenu {

	/**
	 * Init class.
	 *
	 * @since 1.8.6
	 */
	public function init() {

		$this->hooks();
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.8.6
	 */
	protected function hooks() {

		add_action( 'wpforms_builder_enqueues', [ $this, 'enqueues' ] );
		add_action( 'wpforms_admin_page', [ $this, 'output' ], 20 );
	}

	/**
	 * Enqueue assets.
	 *
	 * @since 1.8.6
	 */
	public function enqueues() {

		$min = wpforms_get_min_suffix();

		wp_enqueue_script(
			'wpforms-builder-context-menu',
			WPFORMS_PLUGIN_URL . "assets/js/admin/builder/context-menu{$min}.js",
			[ 'wpforms-builder' ],
			WPFORMS_VERSION,
			true
		);
	}

	/**
	 * Output context menu markup.
	 *
	 * @since 1.8.6
	 */
	public function output() {

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo wpforms_render( 'builder/field-context-menu' );
	}
}
