<?php

declare (strict_types=1);
namespace WPForms\Vendor\Square\Models\Builders;

use WPForms\Vendor\Core\Utils\CoreHelper;
use WPForms\Vendor\Square\Models\Customer;
use WPForms\Vendor\Square\Models\Error;
use WPForms\Vendor\Square\Models\RetrieveCustomerResponse;
/**
 * Builder for model RetrieveCustomerResponse
 *
 * @see RetrieveCustomerResponse
 */
class RetrieveCustomerResponseBuilder
{
    /**
     * @var RetrieveCustomerResponse
     */
    private $instance;
    private function __construct(RetrieveCustomerResponse $instance)
    {
        $this->instance = $instance;
    }
    /**
     * Initializes a new Retrieve Customer Response Builder object.
     */
    public static function init() : self
    {
        return new self(new RetrieveCustomerResponse());
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
     * Sets customer field.
     *
     * @param Customer|null $value
     */
    public function customer(?Customer $value) : self
    {
        $this->instance->setCustomer($value);
        return $this;
    }
    /**
     * Initializes a new Retrieve Customer Response object.
     */
    public function build() : RetrieveCustomerResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
