<?php

namespace WPForms\Integrations\Stripe;

/**
 * Compatibility with the Stripe addon.
 *
 * @since 1.8.2
 */
class StripeAddonCompatibility {

	/**
	 * Minimum compatible version of the Stripe addon.
	 *
	 * @since 1.8.2
	 *
	 * @var string
	 */
	const MIN_COMPAT_VERSION = '3.0.0';

	/**
	 * Minimum modern settings compatible version of the Stripe addon.
	 *
	 * @since 1.8.4
	 *
	 * @var string
	 */
	const MIN_MODERN_SETTINGS_VERSION = '3.1.0';

	/**
	 * Initialization.
	 *
	 * @since 1.8.4
	 *
	 * @return StripeAddonCompatibility|null
	 */
	public function init() {

		return Helpers::is_pro() ? $this : null;
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.8.2
	 */
	public function hooks() {

		// Warn the user about the fact that the not supported addon has been installed.
		add_action( 'admin_notices', [ $this, 'display_legacy_addon_notice' ] );
	}

	/**
	 * Check if the supported Stripe addon is active.
	 *
	 * @since 1.8.2
	 *
	 * @return bool
	 */
	public function is_supported_version() {

		return defined( 'WPFORMS_STRIPE_VERSION' )
			&& version_compare( WPFORMS_STRIPE_VERSION, self::MIN_COMPAT_VERSION, '>=' );
	}

	/**
	 * Check if the supported Stripe addon is active for modern builder settings.
	 *
	 * @since 1.8.4
	 *
	 * @return bool
	 */
	public function is_supported_modern_settings() {

		return defined( 'WPFORMS_STRIPE_VERSION' )
			&& version_compare( WPFORMS_STRIPE_VERSION, self::MIN_MODERN_SETTINGS_VERSION, '>=' );
	}

	/**
	 * Display wp-admin notification saying user first have to update addon to the latest version.
	 *
	 * @since 1.8.2
	 */
	public function display_legacy_addon_notice() {

		echo '<div class="notice notice-error"><p>';
			esc_html_e( 'The WPForms Stripe addon is out of date. To avoid payment processing issues, please upgrade the Stripe addon to the latest version.', 'wpforms-lite' );
		echo '</p></div>';
	}
}
