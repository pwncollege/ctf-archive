<?php

declare (strict_types=1);
namespace WPForms\Vendor\Square\Models\Builders;

use WPForms\Vendor\Core\Utils\CoreHelper;
use WPForms\Vendor\Square\Models\DismissTerminalCheckoutResponse;
use WPForms\Vendor\Square\Models\Error;
use WPForms\Vendor\Square\Models\TerminalCheckout;
/**
 * Builder for model DismissTerminalCheckoutResponse
 *
 * @see DismissTerminalCheckoutResponse
 */
class DismissTerminalCheckoutResponseBuilder
{
    /**
     * @var DismissTerminalCheckoutResponse
     */
    private $instance;
    private function __construct(DismissTerminalCheckoutResponse $instance)
    {
        $this->instance = $instance;
    }
    /**
     * Initializes a new Dismiss Terminal Checkout Response Builder object.
     */
    public static function init() : self
    {
        return new self(new DismissTerminalCheckoutResponse());
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
     * Initializes a new Dismiss Terminal Checkout Response object.
     */
    public function build() : DismissTerminalCheckoutResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
