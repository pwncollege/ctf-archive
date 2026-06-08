<?php
/**
 * Payment related functions.
 *
 * @since 1.8.2
 */

/**
 * Get supported currencies.
 *
 * @since 1.2.4
 *
 * @return array
 */
function wpforms_get_currencies() {

	$currencies = [
		'USD' => [
			'name'                => esc_html__( 'U.S. Dollar', 'wpforms-lite' ),
			'symbol'              => '&#36;',
			'symbol_pos'          => 'left',
			'thousands_separator' => ',',
			'decimal_separator'   => '.',
			'decimals'            => 2,
		],
		'GBP' => [
			'name'                => esc_html__( 'Pound Sterling', 'wpforms-lite' ),
			'symbol'              => '&pound;',
			'symbol_pos'          => 'left',
			'thousands_separator' => ',',
			'decimal_separator'   => '.',
			'decimals'            => 2,
		],
		'EUR' => [
			'name'                => esc_html__( 'Euro', 'wpforms-lite' ),
			'symbol'              => '&euro;',
			'symbol_pos'          => 'right',
			'thousands_separator' => '.',
			'decimal_separator'   => ',',
			'decimals'            => 2,
		],
		'AUD' => [
			'name'                => esc_html__( 'Australian Dollar', 'wpforms-lite' ),
			'symbol'              => '&#36;',
			'symbol_pos'          => 'left',
			'thousands_separator' => ',',
			'decimal_separator'   => '.',
			'decimals'            => 2,
		],
		'BRL' => [
			'name'                => esc_html__( 'Brazilian Real', 'wpforms-lite' ),
			'symbol'              => 'R$',
			'symbol_pos'          => 'left',
			'thousands_separator' => '.',
			'decimal_separator'   => ',',
			'decimals'            => 2,
		],
		'BGN' => [
			'name'                => esc_html__( 'Bulgarian Lev', 'wpforms-lite' ),
			'symbol'              => 'лв',
			'symbol_pos'          => 'right',
			'thousands_separator' => ' ',
			'decimal_separator'   => ',',
			'decimals'            => 2,
		],
		'CAD' => [
			'name'                => esc_html__( 'Canadian Dollar', 'wpforms-lite' ),
			'symbol'              => '&#36;',
			'symbol_pos'          => 'left',
			'thousands_separator' => ',',
			'decimal_separator'   => '.',
			'decimals'            => 2,
		],
		'CRC' => [
			'name'                => esc_html__( 'Costa Rican Colón', 'wpforms-lite' ),
			'symbol'              => '&#8353;',
			'symbol_pos'          => 'left',
			'thousands_separator' => ',',
			'decimal_separator'   => '.',
			'decimals'            => 2,
		],
		'XAF' => [
			'name'                => esc_html__( 'Central African CFA Franc', 'wpforms-lite' ),
			'symbol'              => 'F.CFA ',
			'symbol_pos'          => 'left',
			'thousands_separator' => ',',
			'decimal_separator'   => '',
			'decimals'            => 0,
		],
		'CZK' => [
			'name'                => esc_html__( 'Czech Koruna', 'wpforms-lite' ),
			'symbol'              => '&#75;&#269;',
			'symbol_pos'          => 'right',
			'thousands_separator' => '.',
			'decimal_separator'   => ',',
			'decimals'            => 2,
		],
		'DKK' => [
			'name'                => esc_html__( 'Danish Krone', 'wpforms-lite' ),
			'symbol'              => 'kr.',
			'symbol_pos'          => 'right',
			'thousands_separator' => '.',
			'decimal_separator'   => ',',
			'decimals'            => 2,
		],
		'HKD' => [
			'name'                => esc_html__( 'Hong Kong Dollar', 'wpforms-lite' ),
			'symbol'              => '&#36;',
			'symbol_pos'          => 'left',
			'thousands_separator' => ',',
			'decimal_separator'   => '.',
			'decimals'            => 2,
		],
		'HUF' => [
			'name'                => esc_html__( 'Hungarian Forint', 'wpforms-lite' ),
			'symbol'              => 'Ft',
			'symbol_pos'          => 'right',
			'thousands_separator' => '.',
			'decimal_separator'   => ',',
			'decimals'            => 2,
		],
		'INR' => [
			'name'                => esc_html__( 'Indian Rupee', 'wpforms-lite' ),
			'symbol'              => '&#8377;',
			'symbol_pos'          => 'left',
			'thousands_separator' => ',',
			'decimal_separator'   => '.',
			'decimals'            => 2,
		],
		'ILS' => [
			'name'                => esc_html__( 'Israeli New Sheqel', 'wpforms-lite' ),
			'symbol'              => '&#8362;',
			'symbol_pos'          => 'left',
			'thousands_separator' => ',',
			'decimal_separator'   => '.',
			'decimals'            => 2,
		],
		'JPY' => [
			'name'                => esc_html__( 'Japanese Yen', 'wpforms-lite' ),
			'symbol'              => '&#165;',
			'symbol_pos'          => 'left',
			'thousands_separator' => ',',
			'decimal_separator'   => '',
			'decimals'            => 0,
		],
		'MYR' => [
			'name'                => esc_html__( 'Malaysian Ringgit', 'wpforms-lite' ),
			'symbol'              => '&#82;&#77;',
			'symbol_pos'          => 'left',
			'thousands_separator' => ',',
			'decimal_separator'   => '.',
			'decimals'            => 2,
		],
		'MXN' => [
			'name'                => esc_html__( 'Mexican Peso', 'wpforms-lite' ),
			'symbol'              => '&#36;',
			'symbol_pos'          => 'left',
			'thousands_separator' => ',',
			'decimal_separator'   => '.',
			'decimals'            => 2,
		],
		'NOK' => [
			'name'                => esc_html__( 'Norwegian Krone', 'wpforms-lite' ),
			'symbol'              => 'Kr',
			'symbol_pos'          => 'left',
			'thousands_separator' => '.',
			'decimal_separator'   => ',',
			'decimals'            => 2,
		],
		'NZD' => [
			'name'                => esc_html__( 'New Zealand Dollar', 'wpforms-lite' ),
			'symbol'              => '&#36;',
			'symbol_pos'          => 'left',
			'thousands_separator' => ',',
			'decimal_separator'   => '.',
			'decimals'            => 2,
		],
		'PHP' => [
			'name'                => esc_html__( 'Philippine Peso', 'wpforms-lite' ),
			'symbol'              => 'Php',
			'symbol_pos'          => 'left',
			'thousands_separator' => ',',
			'decimal_separator'   => '.',
			'decimals'            => 2,
		],
		'PLN' => [
			'name'                => esc_html__( 'Polish Zloty', 'wpforms-lite' ),
			'symbol'              => '&#122;&#322;',
			'symbol_pos'          => 'left',
			'thousands_separator' => '.',
			'decimal_separator'   => ',',
			'decimals'            => 2,
		],
		'RON' => [
			'name'                => esc_html__( 'Romanian Leu', 'wpforms-lite' ),
			'symbol'              => 'lei',
			'symbol_pos'          => 'right',
			'thousands_separator' => ',',
			'decimal_separator'   => '.',
			'decimals'            => 2,
		],
		'RUB' => [
			'name'                => esc_html__( 'Russian Ruble', 'wpforms-lite' ),
			'symbol'              => 'pyб',
			'symbol_pos'          => 'right',
			'thousands_separator' => ' ',
			'decimal_separator'   => '.',
			'decimals'            => 2,
		],
		'SAR' => [
			'name'                => esc_html__( 'Saudi Arabian Riyal', 'wpforms-lite' ),
			'symbol'              => '&#65020;',
			'symbol_pos'          => 'right',
			'thousands_separator' => ',',
			'decimal_separator'   => '.',
			'decimals'            => 2,
		],
		'SGD' => [
			'name'                => esc_html__( 'Singapore Dollar', 'wpforms-lite' ),
			'symbol'              => '&#36;',
			'symbol_pos'          => 'left',
			'thousands_separator' => ',',
			'decimal_separator'   => '.',
			'decimals'            => 2,
		],
		'RSD' => [
			'name'                => esc_html__( 'Serbian Dinar', 'wpforms-lite' ),
			'symbol'              => 'din. ',
			'symbol_pos'          => 'left',
			'thousands_separator' => ',',
			'decimal_separator'   => '.',
			'decimals'            => 2,
		],
		'ZAR' => [
			'name'                => esc_html__( 'South African Rand', 'wpforms-lite' ),
			'symbol'              => 'R',
			'symbol_pos'          => 'left',
			'thousands_separator' => ',',
			'decimal_separator'   => '.',
			'decimals'            => 2,
		],
		'KRW' => [
			'name'                => esc_html__( 'South Korean Won', 'wpforms-lite' ),
			'symbol'              => '&#8361;',
			'symbol_pos'          => 'left',
			'thousands_separator' => ',',
			'decimal_separator'   => '',
			'decimals'            => 0,
		],
		'LKR' => [
			'name'                => esc_html__( 'Sri Lankan Rupee', 'wpforms-lite' ),
			'symbol'              => 'Rs ',
			'symbol_pos'          => 'left',
			'thousands_separator' => ',',
			'decimal_separator'   => '.',
			'decimals'            => 2,
		],
		'SEK' => [
			'name'                => esc_html__( 'Swedish Krona', 'wpforms-lite' ),
			'symbol'              => 'Kr',
			'symbol_pos'          => 'right',
			'thousands_separator' => '.',
			'decimal_separator'   => ',',
			'decimals'            => 2,
		],
		'CHF' => [
			'name'                => esc_html__( 'Swiss Franc', 'wpforms-lite' ),
			'symbol'              => 'CHF',
			'symbol_pos'          => 'left',
			'thousands_separator' => ',',
			'decimal_separator'   => '.',
			'decimals'            => 2,
		],
		'TWD' => [
			'name'                => esc_html__( 'Taiwan New Dollar', 'wpforms-lite' ),
			'symbol'              => '&#36;',
			'symbol_pos'          => 'left',
			'thousands_separator' => ',',
			'decimal_separator'   => '.',
			'decimals'            => 2,
		],
		'THB' => [
			'name'                => esc_html__( 'Thai Baht', 'wpforms-lite' ),
			'symbol'              => '&#3647;',
			'symbol_pos'          => 'left',
			'thousands_separator' => ',',
			'decimal_separator'   => '.',
			'decimals'            => 2,
		],
		'TRY' => [
			'name'                => esc_html__( 'Turkish Lira', 'wpforms-lite' ),
			'symbol'              => '&#8378;',
			'symbol_pos'          => 'left',
			'thousands_separator' => ',',
			'decimal_separator'   => '.',
			'decimals'            => 2,
		],
		'AED' => [
			'name'                => esc_html__( 'United Arab Emirates Dirham', 'wpforms-lite' ),
			'symbol'              => '&#1583;.&#1573;',
			'symbol_pos'          => 'right',
			'thousands_separator' => ',',
			'decimal_separator'   => '.',
			'decimals'            => 2,
		],
	];

	/**
	 * Filter for currencies supported in WPForms payments.
	 *
	 * @since 1.2.4
	 *
	 * @param array $currencies List of currencies.
	 */
	return array_change_key_case( (array) apply_filters( 'wpforms_currencies', $currencies ), CASE_UPPER );
}

/**
 * Sanitize amount by stripping out thousands separators.
 *
 * @link https://github.com/easydigitaldownloads/easy-digital-downloads/blob/master/includes/formatting.php#L24
 *
 * @since 1.2.6
 *
 * @param string $amount   Price amount.
 * @param string $currency Currency ISO code (USD, EUR, etc).
 *
 * @return string $amount
 */
function wpforms_sanitize_amount( $amount, $currency = '' ) {

	if ( empty( $currency ) ) {
		$currency = wpforms_get_currency();
	}

	$raw_amount    = $amount;
	$currency      = strtoupper( $currency );
	$currencies    = wpforms_get_currencies();
	$thousands_sep = $currencies[ $currency ]['thousands_separator'] ?? ',';
	$decimal_sep   = $currencies[ $currency ]['decimal_separator'] ?? '.';

	/**
	 * Filter the raw price amount before sanitization.
	 *
	 * @since 1.8.9.4
	 *
	 * @param string $amount        Raw price amount.
	 * @param string $currency      Currency ISO code (USD, EUR, etc).
	 * @param string $thousands_sep Thousands separator.
	 * @param string $decimal_sep   Decimal separator.
	 *
	 * @return string
	 */
	$amount = (string) apply_filters( 'wpforms_sanitize_amount_before', $amount, $currency, $thousands_sep, $decimal_sep );

	// Sanitize the amount.
	if ( $decimal_sep === ',' && strpos( $amount, $decimal_sep ) !== false ) {
		if ( ( $thousands_sep === '.' || $thousands_sep === ' ' ) && strpos( $amount, $thousands_sep ) !== false ) {
			$amount = str_replace( $thousands_sep, '', $amount );
		} elseif ( empty( $thousands_sep ) && strpos( $amount, '.' ) !== false ) {
			$amount = str_replace( '.', '', $amount );
		}
		$amount = str_replace( $decimal_sep, '.', $amount );
	} elseif ( $thousands_sep === ',' && strpos( $amount, $thousands_sep ) !== false ) {
		$amount = str_replace( $thousands_sep, '', $amount );
	}

	// Remove current symbol.
	$amount = str_replace( $currencies[ $currency ]['symbol'], '', $amount );
	/**
	 * Remove any characters that are not a digit, a decimal point, or a minus sign.
	 *
	 * E is an exponent notation. Float number can be written in the form 2E-13, which means 2 * 10^-13.
	 * 0-9 is digits.
	 * . is decimal point.
	 * - is minus sign.
	 */
	$sanitized_amount = (string) preg_replace( '/[^E0-9.-]/', '', $amount );

	/**
	 * Set correct currency decimals.
	 *
	 * @since 1.6.6
	 *
	 * @param int     $decimals         Default number of decimals.
	 * @param string  $sanitized_amount Price amount.
	 */
	$decimals = (int) apply_filters(
		'wpforms_sanitize_amount_decimals',
		wpforms_get_currency_decimals( $currency ),
		$sanitized_amount
	);

	/**
	 * Filter the sanitized amount.
	 *
	 * @since 1.8.9.4
	 *
	 * @param string $sanitized_amount Sanitized price amount.
	 * @param string $raw_amount       Raw price amount.
	 * @param string $currency         Currency ISO code (USD, EUR, etc).
	 * @param int    $decimals         Number of decimals.
	 *
	 * @return string
	 */
	return (string) apply_filters(
		'wpforms_sanitize_amount',
		number_format( (float) $sanitized_amount, $decimals, '.', '' ),
		$raw_amount,
		$currency,
		$decimals
	);
}

/**
 * Return a nicely formatted amount.
 *
 * @since 1.2.6
 *
 * @param string $amount   Price amount.
 * @param bool   $symbol   Currency symbol ($, €).
 * @param string $currency Currency ISO code (USD, EUR, etc).
 *
 * @return string $amount Newly formatted amount or Price Not Available
 */
function wpforms_format_amount( $amount, $symbol = false, $currency = '' ) {

	if ( empty( $currency ) ) {
		$currency = wpforms_get_currency();
	}
	$currency      = strtoupper( $currency );
	$currencies    = wpforms_get_currencies();
	$thousands_sep = isset( $currencies[ $currency ]['thousands_separator'] ) ? $currencies[ $currency ]['thousands_separator'] : ',';
	$decimal_sep   = isset( $currencies[ $currency ]['decimal_separator'] ) ? $currencies[ $currency ]['decimal_separator'] : '.';
	$sep_found     = ! empty( $decimal_sep ) ? strpos( $amount, $decimal_sep ) : false;

	// Format the amount.
	if (
		$decimal_sep === ',' &&
		$sep_found !== false
	) {
		$whole  = substr( $amount, 0, $sep_found );
		$part   = substr( $amount, $sep_found + 1, ( strlen( $amount ) - 1 ) );
		$amount = $whole . '.' . $part;
	}

	// Strip "," (comma) from the amount (if set as the thousands' separator).
	if (
		$thousands_sep === ',' &&
		strpos( $amount, $thousands_sep ) !== false
	) {
		$amount = (float) str_replace( ',', '', $amount );
	}

	if ( empty( $amount ) ) {
		$amount = 0;
	}

	/** This filter is documented in wpforms_sanitize_amount function above. */
	$decimals = (int) apply_filters(
		'wpforms_sanitize_amount_decimals',
		wpforms_get_currency_decimals( $currency ),
		$amount
	);

	$number = number_format( (float) $amount, $decimals, $decimal_sep, $thousands_sep );

	// Display a symbol, if any.
	if ( $symbol && isset( $currencies[ $currency ]['symbol_pos'] ) ) {

		/**
		 * Filter for currency symbol padding.
		 *
		 * @since 1.2.6
		 *
		 * @param string $symbol_padding Currency symbol padding.
		 */
		$symbol_padding = apply_filters( 'wpforms_currency_symbol_padding', ' ' );

		if ( $currencies[ $currency ]['symbol_pos'] === 'right' ) {
			$number .= $symbol_padding . $currencies[ $currency ]['symbol'];
		} else {
			$number = $currencies[ $currency ]['symbol'] . $number;
		}
	}

	return $number;
}

/**
 * Get default number of decimals for a given currency.
 * If not provided inside the currency, default value is used, which is 2.
 *
 * @since 1.6.6
 *
 * @param array|string $currency Currency data we are getting decimals for.
 *
 * @return int
 */
function wpforms_get_currency_decimals( $currency ) {

	if ( is_string( $currency ) ) {
		$currencies    = wpforms_get_currencies();
		$currency_code = strtoupper( $currency );
		$currency      = isset( $currencies[ $currency_code ] ) ? $currencies[ $currency_code ] : [];
	}

	/**
	 * Get currency decimals.
	 *
	 * @since 1.6.6
	 *
	 * @param int          $decimals Default number of decimals.
	 * @param array|string $currency Currency data we are getting decimals for.
	 */
	return (int) apply_filters(
		'wpforms_get_currency_decimals',
		isset( $currency['decimals'] ) ? $currency['decimals'] : 2,
		$currency
	);
}

/**
 * Get payments currency.
 * If the currency not available anymore 'USD' used as default.
 *
 * @since 1.6.6
 *
 * @return string
 */
function wpforms_get_currency() {

	$currency   = wpforms_setting( 'currency' );
	$currencies = wpforms_get_currencies();

	/**
	 * Get payments currency.
	 *
	 * @since 1.6.6
	 *
	 * @param string $currency   Payments currency.
	 * @param array  $currencies Available currencies.
	 */
	return apply_filters(
		'wpforms_get_currency',
		isset( $currencies[ $currency ] ) ? $currency : 'USD',
		$currencies
	);
}

/**
 * Return recognized payment field types.
 *
 * @since 1.0.0
 *
 * @return array
 */
function wpforms_payment_fields() {

	/**
	 * Filters the recognized payment field types.
	 *
	 * @since 1.0.0
	 *
	 * @param array $fields Payment field types.
	 */
	return (array) apply_filters(
		'wpforms_payment_fields',
		[ 'payment-single', 'payment-multiple', 'payment-checkbox', 'payment-select' ]
	);
}

/**
 * Check if form or entry contains payment.
 *
 * @since 1.0.0
 *
 * @param string $type Either 'entry' or 'form'.
 * @param array  $data List of form fields.
 *
 * @return bool
 */
function wpforms_has_payment( $type = 'entry', $data = [] ) {

	$payment        = false;
	$payment_fields = wpforms_payment_fields();

	if ( ! empty( $data['fields'] ) ) {
		$data = $data['fields'];
	}

	if ( empty( $data ) ) {
		return false;
	}

	foreach ( $data as $field ) {
		if ( isset( $field['type'] ) && in_array( $field['type'], $payment_fields, true ) ) {

			// For entries, only return true if the payment field has an amount.
			if (
				$type === 'form' ||
				(
					$type === 'entry' &&
					! empty( $field['amount'] ) &&
					! empty( wpforms_sanitize_amount( $field['amount'] ) )
				)
			) {
				$payment = true;

				break;
			}
		}
	}

	return $payment;
}

/**
 * Check to see if a form has an active payment gateway configured.
 *
 * @since 1.4.5
 *
 * @param array $form_data Form data and settings.
 *
 * @return bool
 */
function wpforms_has_payment_gateway( $form_data ) {

	// PayPal Standard check.
	if ( ! empty( $form_data['payments']['paypal_standard']['enable'] ) ) {
		return true;
	}

	// Stripe Check.
	if ( ! empty( $form_data['payments']['stripe']['enable'] ) ) {
		return true;
	}

	/**
	 * Allow modifying whether a form has an active payment gateway.
	 *
	 * @since 1.4.5
	 *
	 * @param bool  $result    True if a form has an active payment gateway.
	 * @param array $form_data Form data and settings.
	 */
	return (bool) apply_filters( 'wpforms_has_payment_gateway', false, $form_data );
}

/**
 * Get payment total amount from entry.
 *
 * @since 1.0.0
 * @since 1.8.2.2 Added PHP max() function before returning a total.
 *
 * @param array $fields List of fields.
 *
 * @return string
 */
function wpforms_get_total_payment( $fields ) {

	$fields = wpforms_get_payment_items( $fields );
	$total  = 0;

	if ( empty( $fields ) ) {
		return false;
	}

	foreach ( $fields as $field ) {
		// Skip the field hidden by conditional logic.
		if ( isset( $field['visible'] ) && $field['visible'] === false ) {
			continue;
		}

		if ( empty( $field['amount'] ) ) {
			continue;
		}

		$amount = wpforms_sanitize_amount( $field['amount'] );

		if ( ! empty( $field['quantity'] ) ) {
			$amount *= (int) $field['quantity'];
		}

		$total += $amount;
	}

	$total = max( 0, $total );

	return wpforms_sanitize_amount( $total );
}

/**
 * Get payment fields in an entry.
 *
 * @since 1.0.0
 *
 * @param array $fields List of fields.
 *
 * @return array|bool False if no fields provided, otherwise array.
 */
function wpforms_get_payment_items( $fields = [] ) {

	if ( empty( $fields ) ) {
		return false;
	}

	$payment_fields = wpforms_payment_fields();

	foreach ( $fields as $id => $field ) {
		if (
			empty( $field['type'] ) ||
			empty( $field['amount'] ) ||
			! in_array( $field['type'], $payment_fields, true ) ||
			empty( wpforms_sanitize_amount( $field['amount'] ) ) ||
			( isset( $field['quantity'] ) && ! $field['quantity'] )
		) {
			// Remove all non-payment fields as well as payment fields with no amount or empty quantity.
			unset( $fields[ $id ] );
		}
	}

	return $fields;
}

/**
 * Determine if field has quantity enabled.
 *
 * @since 1.8.7
 *
 * @param array $field     Field data.
 * @param array $form_data Form data.
 *
 * @return bool
 */
function wpforms_payment_has_quantity( array $field, array $form_data ): bool {

	if ( ! isset( $field['id'] ) ) {
		return false;
	}

	if ( isset( $field['quantity'] ) ) {
		return true;
	}

	$field_settings = $form_data['fields'][ $field['id'] ] ?? [];

	if ( empty( $field_settings['enable_quantity'] ) ) {
		return false;
	}

	// Quantity is available only for `single` format of the Single payment field.
	if ( $field_settings['type'] === 'payment-single' && $field_settings['format'] !== 'single' ) {
		return false;
	}

	// Otherwise return true.
	// It covers the Dropdown Items field (and others where the quantity will be supported).
	return true;
}

/**
 * Formatted payment field value with quantity.
 *
 * @since 1.8.7
 *
 * @param array $field Field data.
 *
 * @return string
 */
function wpforms_payment_format_quantity( array $field ): string {

	if ( empty( $field['value'] ) ) {
		return '';
	}

	return sprintf( /* translators: %1$s - payment amount; %2$d - payment quantity. */
		esc_html__( '%1$s &times; %2$d', 'wpforms-lite' ),
		$field['value'],
		$field['quantity'] ?? 1
	);
}

/**
 * Get the multiplier for a given currency based on its decimal places.
 *
 * This function returns a scaling factor used to convert between
 * the smallest currency unit (e.g., cents for USD) and the standard
 * representation.
 * For example,
 * - USD (2 decimal places) → returns 100
 * - JPY (0 decimal places) → returns 1
 *
 * @since 1.9.5
 *
 * @param string $currency Currency.
 *
 * @return int
 */
function wpforms_get_currency_multiplier( string $currency = '' ): int {

	if ( ! $currency ) {
		$currency = wpforms_get_currency();
	}

	return (int) str_pad( 1, wpforms_get_currency_decimals( strtolower( $currency ) ) + 1, 0, STR_PAD_RIGHT );
}
