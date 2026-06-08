<?php

namespace WPForms\Integrations\Square;

/**
 * Compatibility with cURL extension.
 * Square SDK requires cURL to be enabled to work correctly.
 *
 * @since 1.9.5
 */
class CurlCompatibility {

	/**
	 * Initialization.
	 *
	 * @since 1.9.5
	 *
	 * @return CurlCompatibility|null
	 */
	public function init(): ?CurlCompatibility {

		return ! $this->is_curl_loaded() ? $this : null;
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.9.5
	 */
	public function hooks() {

		// Warn the user about the fact that cURL is not loaded.
		add_action( 'admin_notices', [ $this, 'display_curl_missing_notice' ] );
	}

	/**
	 * Check if cURL is loaded.
	 *
	 * @since 1.9.5
	 *
	 * @return bool
	 */
	private function is_curl_loaded(): bool {

		return extension_loaded( 'curl' );
	}

	/**
	 * Display wp-admin notification saying user to enable cURL extension for the Square payments.
	 *
	 * @since 1.9.5
	 */
	public function display_curl_missing_notice() {

		echo '<div class="notice notice-error"><p>';
			esc_html_e( 'The WPForms Square payments require cURL to be enabled to work correctly.', 'wpforms-lite' );
		echo '</p></div>';
	}
}
