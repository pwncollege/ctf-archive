<?php

namespace WPForms\Vendor\Stripe\Exception\OAuth;

/**
 * Implements properties and methods common to all (non-SPL) Stripe OAuth
 * exceptions.
 */
abstract class OAuthErrorException extends \WPForms\Vendor\Stripe\Exception\ApiErrorException
{
    protected function constructErrorObject()
    {
        if (null === $this->jsonBody) {
            return null;
        }
        return \WPForms\Vendor\Stripe\OAuthErrorObject::constructFrom($this->jsonBody);
    }
}
