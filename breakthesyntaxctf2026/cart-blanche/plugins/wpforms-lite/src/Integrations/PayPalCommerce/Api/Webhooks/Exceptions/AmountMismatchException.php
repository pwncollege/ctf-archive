<?php

namespace WPForms\Integrations\PayPalCommerce\Api\Webhooks\Exceptions;

use Exception;

/**
 * Class AmountMismatchException.
 *
 * @since 1.10.0
 */
class AmountMismatchException extends Exception {

	/**
	 * AmountMismatchException constructor.
	 *
	 * @since 1.10.0
	 *
	 * @param string $message Message.
	 */
	public function __construct( $message ) {

		parent::__construct( $message, 202 );
	}
}
