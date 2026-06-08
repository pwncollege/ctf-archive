<?php

declare (strict_types=1);
namespace WPForms\Vendor\Square\Models\Builders;

use WPForms\Vendor\Core\Utils\CoreHelper;
use WPForms\Vendor\Square\Models\BulkUpdateCustomerData;
use WPForms\Vendor\Square\Models\BulkUpdateCustomersRequest;
/**
 * Builder for model BulkUpdateCustomersRequest
 *
 * @see BulkUpdateCustomersRequest
 */
class BulkUpdateCustomersRequestBuilder
{
    /**
     * @var BulkUpdateCustomersRequest
     */
    private $instance;
    private function __construct(BulkUpdateCustomersRequest $instance)
    {
        $this->instance = $instance;
    }
    /**
     * Initializes a new Bulk Update Customers Request Builder object.
     *
     * @param array<string,BulkUpdateCustomerData> $customers
     */
    public static function init(array $customers) : self
    {
        return new self(new BulkUpdateCustomersRequest($customers));
    }
    /**
     * Initializes a new Bulk Update Customers Request object.
     */
    public function build() : BulkUpdateCustomersRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
