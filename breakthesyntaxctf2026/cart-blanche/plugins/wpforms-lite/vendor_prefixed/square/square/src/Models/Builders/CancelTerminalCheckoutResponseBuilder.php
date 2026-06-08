<?php

declare (strict_types=1);
namespace WPForms\Vendor\Square\Models\Builders;

use WPForms\Vendor\Core\Utils\CoreHelper;
use WPForms\Vendor\Square\Models\CancelTerminalCheckoutResponse;
use WPForms\Vendor\Square\Models\Error;
use WPForms\Vendor\Square\Models\TerminalCheckout;
/**
 * Builder for model CancelTerminalCheckoutResponse
 *
 * @see CancelTerminalCheckoutResponse
 */
class CancelTerminalCheckoutResponseBuilder
{
    /**
     * @var CancelTerminalCheckoutResponse
     */
    private $instance;
    private function __construct(CancelTerminalCheckoutResponse $instance)
    {
        $this->instance = $instance;
    }
    /**
     * Initializes a new Cancel Terminal Checkout Response Builder object.
     */
    public static function init() : self
    {
        return new self(new CancelTerminalCheckoutResponse());
    }
    /**
     * Sets errors field.
     *
     * @param Error[]|null $value
     */
    public function errors(?array $value) : self
    {
        $this->instance->setErrors($value);
        return $this;
    }
    /**
     * Sets checkout field.
     *
     * @param TerminalCheckout|null $value
     */
    public function checkout(?TerminalCheckout $value) : self
    {
        $this->instance->setCheckout($value);
        return $this;
    }
    /**
     * Initializes a new Cancel Terminal Checkout Response object.
     */
    public function build() : CancelTerminalCheckoutResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
