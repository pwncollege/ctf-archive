<?php

declare (strict_types=1);
namespace WPForms\Vendor\Square\Models\Builders;

use WPForms\Vendor\Core\Utils\CoreHelper;
use WPForms\Vendor\Square\Models\CreateTerminalCheckoutResponse;
use WPForms\Vendor\Square\Models\Error;
use WPForms\Vendor\Square\Models\TerminalCheckout;
/**
 * Builder for model CreateTerminalCheckoutResponse
 *
 * @see CreateTerminalCheckoutResponse
 */
class CreateTerminalCheckoutResponseBuilder
{
    /**
     * @var CreateTerminalCheckoutResponse
     */
    private $instance;
    private function __construct(CreateTerminalCheckoutResponse $instance)
    {
        $this->instance = $instance;
    }
    /**
     * Initializes a new Create Terminal Checkout Response Builder object.
     */
    public static function init() : self
    {
        return new self(new CreateTerminalCheckoutResponse());
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
     * Initializes a new Create Terminal Checkout Response object.
     */
    public function build() : CreateTerminalCheckoutResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
