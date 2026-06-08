<?php

declare (strict_types=1);
namespace WPForms\Vendor\Square\Models\Builders;

use WPForms\Vendor\Core\Utils\CoreHelper;
use WPForms\Vendor\Square\Models\BulkUpdateVendorsRequest;
use WPForms\Vendor\Square\Models\UpdateVendorRequest;
/**
 * Builder for model BulkUpdateVendorsRequest
 *
 * @see BulkUpdateVendorsRequest
 */
class BulkUpdateVendorsRequestBuilder
{
    /**
     * @var BulkUpdateVendorsRequest
     */
    private $instance;
    private function __construct(BulkUpdateVendorsRequest $instance)
    {
        $this->instance = $instance;
    }
    /**
     * Initializes a new Bulk Update Vendors Request Builder object.
     *
     * @param array<string,UpdateVendorRequest> $vendors
     */
    public static function init(array $vendors) : self
    {
        return new self(new BulkUpdateVendorsRequest($vendors));
    }
    /**
     * Initializes a new Bulk Update Vendors Request object.
     */
    public function build() : BulkUpdateVendorsRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
