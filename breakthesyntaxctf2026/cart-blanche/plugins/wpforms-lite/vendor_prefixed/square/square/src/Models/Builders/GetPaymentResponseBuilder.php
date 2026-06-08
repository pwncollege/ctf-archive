<?php

declare (strict_types=1);
namespace WPForms\Vendor\Square\Models\Builders;

use WPForms\Vendor\Core\Utils\CoreHelper;
use WPForms\Vendor\Square\Models\Error;
use WPForms\Vendor\Square\Models\GetPaymentResponse;
use WPForms\Vendor\Square\Models\Payment;
/**
 * Builder for model GetPaymentResponse
 *
 * @see GetPaymentResponse
 */
class GetPaymentResponseBuilder
{
    /**
     * @var GetPaymentResponse
     */
    private $instance;
    private function __construct(GetPaymentResponse $instance)
    {
        $this->instance = $instance;
    }
    /**
     * Initializes a new Get Payment Response Builder object.
     */
    public static function init() : self
    {
        return new self(new GetPaymentResponse());
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
     * Sets payment field.
     *
     * @param Payment|null $value
     */
    public function payment(?Payment $value) : self
    {
        $this->instance->setPayment($value);
        return $this;
    }
    /**
     * Initializes a new Get Payment Response object.
     */
    public function build() : GetPaymentResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
