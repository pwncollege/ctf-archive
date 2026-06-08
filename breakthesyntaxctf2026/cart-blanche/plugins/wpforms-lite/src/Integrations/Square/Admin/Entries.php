<?php

namespace WPForms\Integrations\Square\Admin;

use WPForms\Integrations\Square\Helpers;

/**
 * Square admin entries.
 *
 * @since 1.9.5
 */
class Entries {

	/**
	 * Init the class.
	 *
	 * @since 1.9.5
	 */
	public function init() {

		$this->hooks();

		return $this;
	}

	/**
	 * Entries hooks.
	 *
	 * @since 1.9.5
	 */
	private function hooks() {

		add_filter( 'wpforms_has_payment_gateway', [ $this, 'has_payment_gateway' ], 10, 2 );
	}

	/**
	 * Make Square payment gateway work on the admin entries page.
	 *
	 * @since 1.9.5
	 *
	 * @param bool  $result    Initial value.
	 * @param array $form_data Form data and settings.
	 *
	 * @return bool
	 */
	public function has_payment_gateway( $result, array $form_data ): bool {

		if ( Helpers::is_payments_enabled( $form_data ) ) {
			return true;
		}

		return (bool) $result;
	}
}
