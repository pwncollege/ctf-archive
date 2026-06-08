<?php

namespace WPForms\Integrations\PayPalCommerce;

/**
 * Compatibility with the PayPal addon.
 *
 * @since 1.10.0
 */
class AddonCompatibility {

	/**
	 * Minimum compatible version of the PayPal addon.
	 *
	 * @since 1.10.0
	 */
	private const MIN_COMPAT_VERSION = '2.0.0';

	/**
	 * Initialization.
	 *
	 * @since 1.10.0
	 *
	 * @return AddonCompatibility|null
	 */
	public function init(): ?AddonCompatibility {

		return Helpers::is_pro() ? $this : null;
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.10.0
	 */
	public function hooks(): void {

		// Warn the user about the fact that the not supported addon has been installed.
		add_action( 'admin_notices', [ $this, 'display_legacy_addon_notice' ] );
	}

	/**
	 * Check if the supported PayPal addon is active.
	 *
	 * @since 1.10.0
	 *
	 * @return bool
	 */
	public function is_supported_version(): bool {

		return (
			defined( 'WPFORMS_PAYPAL_COMMERCE_VERSION' ) &&
			version_compare( WPFORMS_PAYPAL_COMMERCE_VERSION, self::MIN_COMPAT_VERSION, '>=' )
		);
	}

	/**
	 * Display wp-admin notification saying user first have to update addon to the latest version.
	 *
	 * @since 1.10.0
	 */
	public function display_legacy_addon_notice(): void {

		echo '<div class="notice notice-error"><p>';
			esc_html_e( 'The WPForms PayPal Commerce addon is out of date. To avoid payment processing issues, please upgrade the WPForms PayPal Commerce addon to the latest version.', 'wpforms-lite' );
		echo '</p></div>';
	}
}
