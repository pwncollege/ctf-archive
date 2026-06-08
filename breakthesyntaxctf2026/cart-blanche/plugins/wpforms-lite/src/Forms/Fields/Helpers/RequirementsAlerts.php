<?php

namespace WPForms\Forms\Fields\Helpers;

/**
 * Helpers for Requirements Alerts.
 *
 * Can be used for notifying about new features that addons are not supported.
 *
 * @since 1.8.7
 */
class RequirementsAlerts {

	/**
	 * Determine if the Product Quantities feature is allowed to use.
	 *
	 * @since 1.8.7
	 *
	 * @return bool
	 */
	public static function is_product_quantities_allowed(): bool {

		return empty( self::get_addons_require_for_product_quantities() );
	}

	/**
	 * Determine if the Order Summary feature is allowed to use.
	 *
	 * @since 1.8.7
	 *
	 * @return bool
	 */
	public static function is_order_summary_allowed(): bool {

		return ! self::is_pro() || ! defined( 'WPFORMS_COUPONS_VERSION' ) || version_compare( WPFORMS_COUPONS_VERSION, '1.2.0', '>=' );
	}

	/**
	 * Product Quantities feature: get an update required alert HTML.
	 *
	 * @since 1.8.7
	 *
	 * @return string
	 */
	public static function get_product_quantities_alert(): string {

		$addons_require_update = self::get_addons_require_for_product_quantities();

		// Generate update link when only one addon needs to be updated.
		if ( count( $addons_require_update ) === 1 ) {
			$update_url = self::get_addon_update_url( key( $addons_require_update ) );
		} else {
			// Redirect to the Plugins admin page if multiple addons require an update.
			$update_url = admin_url( 'plugins.php?plugin_status=upgrade' );
		}

		return self::get_update_alert(
			sprintf( /* translators: %1$s - addons list. */
				__( 'The following addons require an update to support product quantities: %1$s', 'wpforms-lite' ),
				implode( ', ', $addons_require_update )
			),
			$update_url
		);
	}

	/**
	 * Order Summary feature: get an update required alert HTML.
	 *
	 * @since 1.8.7
	 *
	 * @return string
	 */
	public static function get_order_summary_alert(): string {

		return self::get_update_alert(
			__( 'You\'re using an older version of the Coupons addon that does not support order summary.', 'wpforms-lite' ),
			self::get_addon_update_url( 'wpforms-coupons' )
		);
	}

	/**
	 * Repeater field: determine if addon is allowed to use inside the repeater field.
	 *
	 * @since 1.8.9
	 *
	 * @param string $addon_slug Addon slug.
	 *
	 * @return bool
	 */
	public static function is_inside_repeater_allowed( string $addon_slug ): bool {

		$requirements = [
			'wpforms-geolocation'      => '2.10.0',
			'wpforms-signatures'       => '1.11.0',
			'wpforms-form-abandonment' => '1.12.0',
			'wpforms-save-resume'      => '1.11.0',
			'wpforms-lead-forms'       => '1000', // @todo: We should adjust this value when the Lead Forms get the Repeater field support.
			'wpforms-google-sheets'    => '2.1.0',
		];

		if ( ! isset( $requirements[ $addon_slug ] ) ) {
			return true;
		}

		$version_constant = strtoupper( str_replace( '-', '_', $addon_slug ) ) . '_VERSION';

		return self::is_pro() &&
			defined( $version_constant ) &&
			version_compare( constant( $version_constant ), $requirements[ $addon_slug ], '>=' );
	}

	/**
	 * Repeater field: get an update required alert HTML.
	 *
	 * @since 1.8.9
	 *
	 * @param string $addon_name Addon name.
	 * @param string $addon_slug Addon slug.
	 *
	 * @return string
	 */
	public static function get_repeater_alert( string $addon_name, string $addon_slug ): string {

		return self::get_update_alert(
			self::get_repeater_alert_text( $addon_name ),
			self::get_addon_update_url( $addon_slug )
		);
	}

	/**
	 * Repeater field: get alert text.
	 *
	 * @since 1.8.9
	 *
	 * @param string $addon_name Addon name.
	 *
	 * @return string
	 */
	public static function get_repeater_alert_text( string $addon_name ): string {

		return sprintf(
			/* translators: %1$s - addon name. */
			__( 'You\'re using an older version of the %1$s addon that does not support the Repeater field.', 'wpforms-lite' ),
			$addon_name
		);
	}

	/**
	 * Retrieve a list of addons that require updating to support the Product Quantities feature.
	 *
	 * @since 1.8.7
	 *
	 * @return array
	 */
	private static function get_addons_require_for_product_quantities(): array {

		static $addons;

		if ( ! is_null( $addons ) ) {
			return $addons;
		}

		$addons = [];

		// All addons require Pro and Top level licenses.
		if ( ! self::is_pro() ) {
			return $addons;
		}

		if ( defined( 'WPFORMS_COUPONS_VERSION' ) && version_compare( WPFORMS_COUPONS_VERSION, '1.2.0', '<' ) ) {
			$addons['wpforms-coupons'] = __( 'Coupons', 'wpforms-lite' );
		}

		if ( defined( 'WPFORMS_PAYPAL_COMMERCE_VERSION' ) && version_compare( WPFORMS_PAYPAL_COMMERCE_VERSION, '1.9.0', '<' ) ) {
			$addons['wpforms-paypal-commerce'] = __( 'PayPal Commerce', 'wpforms-lite' );
		}

		if ( defined( 'WPFORMS_PAYPAL_STANDARD_VERSION' ) && version_compare( WPFORMS_PAYPAL_STANDARD_VERSION, '1.10.0', '<' ) ) {
			$addons['wpforms-paypal-standard'] = __( 'PayPal Standard', 'wpforms-lite' );
		}

		if ( defined( 'WPFORMS_SQUARE_VERSION' ) && version_compare( WPFORMS_SQUARE_VERSION, '1.9.0', '<' ) ) {
			$addons['wpforms-square'] = __( 'Square', 'wpforms-lite' );
		}

		if ( defined( 'WPFORMS_SAVE_RESUME_VERSION' ) && version_compare( WPFORMS_SAVE_RESUME_VERSION, '1.9.0', '<' ) ) {
			$addons['wpforms-save-resume'] = __( 'Save and Resume', 'wpforms-lite' );
		}

		return $addons;
	}

	/**
	 * Get an update alert HTML.
	 *
	 * @since 1.8.7
	 *
	 * @param string $message    Alert message.
	 * @param string $update_url Update button URL.
	 *
	 * @return string
	 */
	private static function get_update_alert( string $message, string $update_url ): string {

		$alert = sprintf(
			'<div class="wpforms-alert-message">
				<h4>%1$s</h4>
				<p>%2$s</p>
			</div>
			<div class="wpforms-alert-buttons">
				<a href="%3$s" target="_blank" rel="noopener noreferrer" class="wpforms-btn wpforms-btn-sm wpforms-btn-blue">%4$s</a>
			</div>',
			esc_html__( 'Update Required', 'wpforms-lite' ),
			esc_html( $message ),
			esc_url( $update_url ),
			esc_html__( 'Update Now', 'wpforms-lite' )
		);

		return sprintf(
			'<div class="wpforms-alert wpforms-alert-danger wpforms-alert-field-requirements">%1$s</div>',
			$alert // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		);
	}

	/**
	 * Get addon update URL.
	 *
	 * @since 1.8.7
	 *
	 * @param string $addon_slug Addon slug.
	 *
	 * @return string
	 */
	private static function get_addon_update_url( string $addon_slug ): string {

		$addon_path = sprintf( '%1$s/%1$s.php', $addon_slug );

		return wp_nonce_url(
			self_admin_url( 'update.php?action=upgrade-plugin&plugin=' . $addon_path ),
			'upgrade-plugin_' . $addon_path
		);
	}

	/**
	 * Determine if Pro or Top level license is used.
	 *
	 * @since 1.8.7
	 *
	 * @return bool
	 */
	private static function is_pro(): bool {

		return in_array( wpforms_get_license_type(), [ 'pro', 'elite', 'agency', 'ultimate' ], true );
	}
}
