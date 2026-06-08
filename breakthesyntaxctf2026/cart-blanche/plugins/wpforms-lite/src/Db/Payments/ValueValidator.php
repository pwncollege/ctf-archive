<?php

namespace WPForms\Db\Payments;

/**
 * ValueValidator class.
 *
 * This class is used to validate values for the Payments DB table.
 *
 * @since 1.8.2
 */
class ValueValidator {

	/**
	 * Check if value is valid for the given column.
	 *
	 * @since 1.8.2
	 *
	 * @param string $value  Value to check if is valid.
	 * @param string $column Database column name.
	 *
	 * @return bool
	 */
	public static function is_valid( $value, $column ) {

		$method = 'get_allowed_' . self::get_plural_column_name( $column );

		if ( ! method_exists( __CLASS__, $method ) ) {
			return false;
		}

		return isset( self::$method()[ $value ] );
	}

	/**
	 * Get allowed modes.
	 *
	 * @since 1.8.2
	 *
	 * @return array
	 */
	private static function get_allowed_modes() {

		return [
			'live' => esc_html__( 'Live', 'wpforms-lite' ),
			'test' => esc_html__( 'Test', 'wpforms-lite' ),
		];
	}

	/**
	 * Get allowed gateways.
	 *
	 * @since 1.8.2
	 *
	 * @return array
	 */
	public static function get_allowed_gateways() {

		/**
		 * Filter allowed gateways.
		 *
		 * @since 1.8.2
		 *
		 * @param array $gateways Array of allowed gateways.
		 */
		return (array) apply_filters(
			'wpforms_db_payments_value_validator_get_allowed_gateways',
			[
				'paypal_standard' => esc_html__( 'PayPal Standard', 'wpforms-lite' ),
				'paypal_commerce' => esc_html__( 'PayPal Commerce', 'wpforms-lite' ),
				'stripe'          => esc_html__( 'Stripe', 'wpforms-lite' ),
				'square'          => esc_html__( 'Square', 'wpforms-lite' ),
				'authorize_net'   => esc_html__( 'Authorize.net', 'wpforms-lite' ),
			]
		);
	}

	/**
	 * Get allowed statuses.
	 *
	 * @since 1.8.2
	 *
	 * @return array
	 */
	public static function get_allowed_statuses() {

		return array_merge(
			self::get_allowed_one_time_statuses(),
			self::get_allowed_subscription_statuses()
		);
	}

	/**
	 * Get allowed one-time payment statuses.
	 *
	 * @since 1.8.4
	 *
	 * @return array
	 */
	public static function get_allowed_one_time_statuses() {

		return [
			'processed'  => __( 'Processed', 'wpforms-lite' ),
			'completed'  => __( 'Completed', 'wpforms-lite' ),
			'pending'    => __( 'Pending', 'wpforms-lite' ),
			'failed'     => __( 'Failed', 'wpforms-lite' ),
			'refunded'   => __( 'Refunded', 'wpforms-lite' ),
			'partrefund' => __( 'Partially Refunded', 'wpforms-lite' ),
		];
	}

	/**
	 * Get allowed subscription statuses.
	 *
	 * @since 1.8.2
	 *
	 * @return array
	 */
	public static function get_allowed_subscription_statuses() {

		return [
			'active'     => __( 'Active', 'wpforms-lite' ),
			'cancelled'  => __( 'Cancelled', 'wpforms-lite' ),
			'not-synced' => __( 'Not Synced', 'wpforms-lite' ),
			'failed'     => __( 'Failed', 'wpforms-lite' ),
			'pending'    => __( 'Pending', 'wpforms-lite' ),
			'completed'  => __( 'Completed', 'wpforms-lite' ),
		];
	}

	/**
	 * Get allowed types.
	 *
	 * @since 1.8.2
	 *
	 * @return array
	 */
	public static function get_allowed_types() {

		return array_merge(
			[
				'one-time' => __( 'One-Time', 'wpforms-lite' ),
			],
			self::get_allowed_subscription_types()
		);
	}

	/**
	 * Get allowed subscription types.
	 *
	 * @since 1.8.2
	 *
	 * @return array
	 */
	public static function get_allowed_subscription_types() {

		return [
			'subscription' => __( 'Subscription', 'wpforms-lite' ),
			'renewal'      => __( 'Renewal', 'wpforms-lite' ),
		];
	}

	/**
	 * Get allowed subscription intervals.
	 * The measurement of time between billing occurrences for an automated recurring billing subscription.
	 *
	 * @since 1.8.2
	 *
	 * @return array
	 */
	public static function get_allowed_subscription_intervals() {

		return [
			'daily'      => esc_html__( 'day', 'wpforms-lite' ),
			'weekly'     => esc_html__( 'week', 'wpforms-lite' ),
			'monthly'    => esc_html__( 'month', 'wpforms-lite' ),
			'quarterly'  => esc_html__( 'quarter', 'wpforms-lite' ),
			'semiyearly' => esc_html__( 'semi-year', 'wpforms-lite' ),
			'yearly'     => esc_html__( 'year', 'wpforms-lite' ),
		];
	}

	/**
	 * Map singular to plural column names.
	 *
	 * @since 1.8.2
	 *
	 * @param string $column Column name.
	 *
	 * @return string
	 */
	private static function get_plural_column_name( $column ) {

		$map = [
			'mode'                => 'modes',
			'gateway'             => 'gateways',
			'status'              => 'statuses',
			'type'                => 'types',
			'subscription_type'   => 'subscription_types',
			'subscription_status' => 'subscription_statuses',
		];

		return isset( $map[ $column ] ) ? $map[ $column ] : $column;
	}
}
