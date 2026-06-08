<?php

namespace WPForms\Integrations\AI;

/**
 * AI features related helper methods.
 *
 * @since 1.9.1
 */
class Helpers {

	/**
	 * Key for a state whether integration is disabled on the Settings > Misc admin page.
	 *
	 * @since 1.9.1
	 */
	public const DISABLE_KEY = 'ai-feature-disabled';

	/**
	 * Key for a state whether integration is used (or has been used).
	 * There is no UI/UX for it, and it's used for internal purposes.
	 *
	 * @since 1.9.1
	 */
	private const USE_KEY = 'ai-feature-used';

	/**
	 * Determine whether integration is disabled.
	 *
	 * @since 1.9.1
	 *
	 * @return bool
	 */
	public static function is_disabled(): bool {

		return self::is_disabled_by_rule() || wpforms_setting( self::DISABLE_KEY );
	}

	/**
	 * Determine whether integration is used.
	 *
	 * @since 1.9.1
	 *
	 * @return bool
	 */
	public static function is_used(): bool {

		return (bool) wpforms_setting( self::USE_KEY );
	}

	/**
	 * Mark integration as used.
	 *
	 * @since 1.9.1
	 */
	public static function set_ai_used() {

		if ( self::is_used() ) {
			return;
		}

		$settings = (array) get_option( 'wpforms_settings', [] );

		$settings[ self::USE_KEY ] = true;

		update_option( 'wpforms_settings', $settings );
	}

	/**
	 * Determine whether integration is disabled through constant or filter.
	 *
	 * @since 1.9.1
	 *
	 * @return bool
	 * @noinspection PhpUndefinedConstantInspection
	 */
	public static function is_disabled_by_rule(): bool {

		$is_disabled = defined( 'WPFORMS_DISABLE_AI_FEATURES' ) && WPFORMS_DISABLE_AI_FEATURES;

		/**
		 * Allow modifying whether AI integration is disabled in WPForms.
		 *
		 * @since 1.9.1
		 *
		 * @param bool $is_disabled True if AI integration is disabled. Default is false.
		 */
		return (bool) apply_filters( 'wpforms_disable_ai_features', $is_disabled ); // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName
	}

	/**
	 * Log an error record.
	 *
	 * @since 1.9.1
	 *
	 * @param string $message  Error message.
	 * @param string $endpoint Endpoint.
	 * @param array  $args     Arguments.
	 */
	public static function log_error( string $message, string $endpoint, array $args ) {

		wpforms_log(
			'AI Integration Error',
			[
				'error'    => $message,
				'endpoint' => $endpoint,
				'args'     => $args,
			],
			[
				'type' => [ 'ai', 'error' ],
			]
		);
	}

	/**
	 * Get the license type.
	 *
	 * @since 1.9.4
	 *
	 * @return string
	 */
	public static function get_license_type(): string {

		$license = (array) get_option( 'wpforms_license', [] );

		return $license['type'] ?? '';
	}

	/**
	 * Determine whether a license key is active.
	 *
	 * @since 1.9.4
	 *
	 * @return bool
	 */
	public static function is_license_active(): bool {

		$license = (array) get_option( 'wpforms_license', [] );

		return ! empty( wpforms_get_license_key() ) &&
			empty( $license['is_expired'] ) &&
			empty( $license['is_disabled'] ) &&
			empty( $license['is_invalid'] );
	}
}
