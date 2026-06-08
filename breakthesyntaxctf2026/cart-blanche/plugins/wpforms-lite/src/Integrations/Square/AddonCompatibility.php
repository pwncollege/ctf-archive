<?php

namespace WPForms\Integrations\Square;

/**
 * Compatibility with the Square addon.
 *
 * @since 1.9.5
 */
class AddonCompatibility {

	/**
	 * Minimum compatible version of the Square addon.
	 *
	 * @since 1.9.5
	 *
	 * @var string
	 */
	private const MIN_COMPAT_VERSION = '2.0.0';

	/**
	 * Initialization.
	 *
	 * @since 1.9.5
	 *
	 * @return AddonCompatibility|null
	 */
	public function init() {

		return Helpers::is_pro() ? $this : null;
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.9.5
	 */
	public function hooks() {

		// Warn the user about the fact that the not supported addon has been installed.
		add_action( 'admin_notices', [ $this, 'display_legacy_addon_notice' ] );
	}

	/**
	 * Check if the supported Square addon is active.
	 *
	 * @since 1.9.5
	 *
	 * @return bool
	 */
	public function is_supported_version(): bool {

		return defined( 'WPFORMS_SQUARE_VERSION' ) && version_compare( WPFORMS_SQUARE_VERSION, self::MIN_COMPAT_VERSION, '>=' );
	}

	/**
	 * Display wp-admin notification saying user first have to update addon to the latest version.
	 *
	 * @since 1.9.5
	 */
	public function display_legacy_addon_notice() {

		echo '<div class="notice notice-error"><p>';
			esc_html_e( 'The WPForms Square addon is out of date. To avoid payment processing issues, please upgrade the Square addon to the latest version.', 'wpforms-lite' );
		echo '</p></div>';
	}
}
