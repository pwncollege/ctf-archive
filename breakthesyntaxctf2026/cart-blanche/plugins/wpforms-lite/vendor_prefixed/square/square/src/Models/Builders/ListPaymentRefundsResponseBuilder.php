<?php

declare (strict_types=1);
namespace WPForms\Vendor\Square\Models\Builders;

use WPForms\Vendor\Core\Utils\CoreHelper;
use WPForms\Vendor\Square\Models\Error;
use WPForms\Vendor\Square\Models\ListPaymentRefundsResponse;
use WPForms\Vendor\Square\Models\PaymentRefund;
/**
 * Builder for model ListPaymentRefundsResponse
 *
 * @see ListPaymentRefundsResponse
 */
class ListPaymentRefundsResponseBuilder
{
    /**
     * @var ListPaymentRefundsResponse
     */
    private $instance;
    private function __construct(ListPaymentRefundsResponse $instance)
    {
        $this->instance = $instance;
    }
    /**
     * Initializes a new List Payment Refunds Response Builder object.
     */
    public static function init() : self
    {
        return new self(new ListPaymentRefundsResponse());
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
     * Sets refunds field.
     *
     * @param PaymentRefund[]|null $value
     */
    public function refunds(?array $value) : self
    {
        $this->instance->setRefunds($value);
        return $this;
    }
    /**
     * Sets cursor field.
     *
     * @param string|null $value
     */
    public function cursor(?string $value) : self
    {
        $this->instance->setCursor($value);
        return $this;
    }
    /**
     * Initializes a new List Payment Refunds Response object.
     */
    public function build() : ListPaymentRefundsResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
