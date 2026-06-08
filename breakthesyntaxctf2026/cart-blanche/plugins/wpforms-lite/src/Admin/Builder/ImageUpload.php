<?php

namespace WPForms\Admin\Builder;

/**
 * Image Upload functionality for the Form Builder Settings.
 *
 * @since 1.9.7.3
 */
class ImageUpload {

	/**
	 * Initialize class.
	 *
	 * @since 1.9.7.3
	 */
	public function init(): void {

		$this->hooks();
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.9.7.3
	 */
	public function hooks(): void {

		add_action( 'wpforms_builder_enqueues', [ $this, 'enqueues' ] );
	}

	/**
	 * Enqueue assets for the Form Builder.
	 *
	 * @since 1.9.7.3
	 */
	public function enqueues(): void {

		$min = wpforms_get_min_suffix();

		wp_enqueue_script(
			'wpforms-builder-settings-image-upload',
			WPFORMS_PLUGIN_URL . "assets/js/admin/builder/image-upload{$min}.js",
			[ 'wp-util', 'wpforms-builder-settings' ],
			WPFORMS_VERSION,
			true
		);
	}
}
