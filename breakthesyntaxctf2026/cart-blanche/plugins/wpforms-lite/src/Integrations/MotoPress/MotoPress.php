<?php

namespace WPForms\Integrations\MotoPress;

use WPForms\Integrations\IntegrationInterface;

/**
 * Improve MotoPress compatibility.
 *
 * @since 1.9.9
 */
class MotoPress implements IntegrationInterface {

	/**
	 * Indicate if the current integration is allowed to load.
	 *
	 * @since 1.9.9
	 *
	 * @return bool
	 */
	public function allow_load(): bool {

		return $this->is_motopress_active() && $this->is_motopress_editor();
	}

	/**
	 * Load an integration.
	 *
	 * @since 1.9.9
	 */
	public function load() {

		$this->hooks();
	}

	/**
	 * Hooks.
	 *
	 * @since 1.9.9
	 */
	private function hooks(): void {

		// Disable Anti Spam v3 honeypot.
		add_filter( 'wpforms_forms_anti_spam_v3_is_honeypot_enabled', '__return_false' );
	}

	/**
	 * Determine if a current page is opened in the MotorPress editor.
	 *
	 * @since 1.9.9
	 *
	 * @return bool
	 */
	private function is_motopress_editor(): bool {

		return ! empty( $_GET['motopress-ce'] ) || ! empty( $_GET['mpce-edit'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}

	/**
	 * Determine if the MotoPress plugin is active.
	 *
	 * @since 1.9.9
	 *
	 * @return bool
	 */
	private function is_motopress_active(): bool {

		return function_exists( 'mpceSettings' );
	}
}
