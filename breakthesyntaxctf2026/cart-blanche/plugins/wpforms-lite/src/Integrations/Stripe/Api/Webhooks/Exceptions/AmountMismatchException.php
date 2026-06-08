<?php

namespace WPForms\Integrations\Stripe\Api\Webhooks\Exceptions;

use Exception;

/**
 * Class AmountMismatchException.
 *
 * @since 1.9.7
 */
class AmountMismatchException extends Exception {

	/**
	 * AmountMismatchException constructor.
	 *
	 * @since 1.9.7
	 *
	 * @param string $message Message.
	 */
	public function __construct( $message ) {

		parent::__construct( $message, 202 );
	}
}
