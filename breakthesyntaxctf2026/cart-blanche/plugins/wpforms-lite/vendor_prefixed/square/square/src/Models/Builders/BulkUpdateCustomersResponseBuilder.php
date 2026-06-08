<?php

declare (strict_types=1);
namespace WPForms\Vendor\Square\Models\Builders;

use WPForms\Vendor\Core\Utils\CoreHelper;
use WPForms\Vendor\Square\Models\BulkUpdateCustomersResponse;
use WPForms\Vendor\Square\Models\Error;
use WPForms\Vendor\Square\Models\UpdateCustomerResponse;
/**
 * Builder for model BulkUpdateCustomersResponse
 *
 * @see BulkUpdateCustomersResponse
 */
class BulkUpdateCustomersResponseBuilder
{
    /**
     * @var BulkUpdateCustomersResponse
     */
    private $instance;
    private function __construct(BulkUpdateCustomersResponse $instance)
    {
        $this->instance = $instance;
    }
    /**
     * Initializes a new Bulk Update Customers Response Builder object.
     */
    public static function init() : self
    {
        return new self(new BulkUpdateCustomersResponse());
    }
    /**
     * Sets responses field.
     *
     * @param array<string,UpdateCustomerResponse>|null $value
     */
    public function responses(?array $value) : self
    {
        $this->instance->setResponses($value);
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
     * Initializes a new Bulk Update Customers Response object.
     */
    public function build() : BulkUpdateCustomersResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
