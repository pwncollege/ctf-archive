<?php

declare (strict_types=1);
namespace WPForms\Vendor\Square\Models\Builders;

use WPForms\Vendor\Core\Utils\CoreHelper;
use WPForms\Vendor\Square\Models\BulkDeleteMerchantCustomAttributesRequest;
use WPForms\Vendor\Square\Models\BulkDeleteMerchantCustomAttributesRequestMerchantCustomAttributeDeleteRequest;
/**
 * Builder for model BulkDeleteMerchantCustomAttributesRequest
 *
 * @see BulkDeleteMerchantCustomAttributesRequest
 */
class BulkDeleteMerchantCustomAttributesRequestBuilder
{
    /**
     * @var BulkDeleteMerchantCustomAttributesRequest
     */
    private $instance;
    private function __construct(BulkDeleteMerchantCustomAttributesRequest $instance)
    {
        $this->instance = $instance;
    }
    /**
     * Initializes a new Bulk Delete Merchant Custom Attributes Request Builder object.
     *
     * @param array<string,BulkDeleteMerchantCustomAttributesRequestMerchantCustomAttributeDeleteRequest> $values
     */
    public static function init(array $values) : self
    {
        return new self(new BulkDeleteMerchantCustomAttributesRequest($values));
    }
    /**
     * Initializes a new Bulk Delete Merchant Custom Attributes Request object.
     */
    public function build() : BulkDeleteMerchantCustomAttributesRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
