<?php

namespace WPForms\Integrations\PayPalCommerce\Admin;

use WPForms\Integrations\PayPalCommerce\Helpers;

/**
 * PayPal Commerce admin entries.
 *
 * @since 1.10.0
 */
class Entries {

	/**
	 * Register hooks.
	 *
	 * @since 1.10.0
	 */
	public function hooks(): void {

		add_filter( 'wpforms_has_payment_gateway', [ $this, 'has_payment_gateway' ], 10, 2 );
	}

	/**
	 * Make PayPal Commerce payment gateway work on the Entries page.
	 *
	 * @since 1.10.0
	 *
	 * @param bool  $result    Initial value.
	 * @param array $form_data Form data and settings.
	 *
	 * @return bool
	 *
	 * @noinspection PhpMissingParamTypeInspection
	 */
	public function has_payment_gateway( $result, array $form_data ): bool {

		if ( $result ) {
			return (bool) $result;
		}

		return Helpers::is_paypal_commerce_enabled( $form_data );
	}
}
