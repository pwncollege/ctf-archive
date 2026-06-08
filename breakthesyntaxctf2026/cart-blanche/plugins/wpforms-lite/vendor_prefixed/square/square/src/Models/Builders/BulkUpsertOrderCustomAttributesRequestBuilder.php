<?php

declare (strict_types=1);
namespace WPForms\Vendor\Square\Models\Builders;

use WPForms\Vendor\Core\Utils\CoreHelper;
use WPForms\Vendor\Square\Models\BulkUpsertOrderCustomAttributesRequest;
use WPForms\Vendor\Square\Models\BulkUpsertOrderCustomAttributesRequestUpsertCustomAttribute;
/**
 * Builder for model BulkUpsertOrderCustomAttributesRequest
 *
 * @see BulkUpsertOrderCustomAttributesRequest
 */
class BulkUpsertOrderCustomAttributesRequestBuilder
{
    /**
     * @var BulkUpsertOrderCustomAttributesRequest
     */
    private $instance;
    private function __construct(BulkUpsertOrderCustomAttributesRequest $instance)
    {
        $this->instance = $instance;
    }
    /**
     * Initializes a new Bulk Upsert Order Custom Attributes Request Builder object.
     *
     * @param array<string,BulkUpsertOrderCustomAttributesRequestUpsertCustomAttribute> $values
     */
    public static function init(array $values) : self
    {
        return new self(new BulkUpsertOrderCustomAttributesRequest($values));
    }
    /**
     * Initializes a new Bulk Upsert Order Custom Attributes Request object.
     */
    public function build() : BulkUpsertOrderCustomAttributesRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
