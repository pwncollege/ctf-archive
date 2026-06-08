<?php

declare (strict_types=1);
namespace WPForms\Vendor\Square\Models\Builders;

use WPForms\Vendor\Core\Utils\CoreHelper;
use WPForms\Vendor\Square\Models\Error;
use WPForms\Vendor\Square\Models\SquareAccountDetails;
/**
 * Builder for model SquareAccountDetails
 *
 * @see SquareAccountDetails
 */
class SquareAccountDetailsBuilder
{
    /**
     * @var SquareAccountDetails
     */
    private $instance;
    private function __construct(SquareAccountDetails $instance)
    {
        $this->instance = $instance;
    }
    /**
     * Initializes a new Square Account Details Builder object.
     */
    public static function init() : self
    {
        return new self(new SquareAccountDetails());
    }
    /**
     * Sets payment source token field.
     *
     * @param string|null $value
     */
    public function paymentSourceToken(?string $value) : self
    {
        $this->instance->setPaymentSourceToken($value);
        return $this;
    }
    /**
     * Unsets payment source token field.
     */
    public function unsetPaymentSourceToken() : self
    {
        $this->instance->unsetPaymentSourceToken();
        return $this;
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
     * Unsets errors field.
     */
    public function unsetErrors() : self
    {
        $this->instance->unsetErrors();
        return $this;
    }
    /**
     * Initializes a new Square Account Details object.
     */
    public function build() : SquareAccountDetails
    {
        return CoreHelper::clone($this->instance);
    }
}
