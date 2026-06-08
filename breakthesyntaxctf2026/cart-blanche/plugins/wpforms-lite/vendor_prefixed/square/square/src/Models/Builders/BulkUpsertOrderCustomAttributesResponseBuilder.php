<?php

declare (strict_types=1);
namespace WPForms\Vendor\Square\Models\Builders;

use WPForms\Vendor\Core\Utils\CoreHelper;
use WPForms\Vendor\Square\Models\BulkUpsertOrderCustomAttributesResponse;
use WPForms\Vendor\Square\Models\Error;
use WPForms\Vendor\Square\Models\UpsertOrderCustomAttributeResponse;
/**
 * Builder for model BulkUpsertOrderCustomAttributesResponse
 *
 * @see BulkUpsertOrderCustomAttributesResponse
 */
class BulkUpsertOrderCustomAttributesResponseBuilder
{
    /**
     * @var BulkUpsertOrderCustomAttributesResponse
     */
    private $instance;
    private function __construct(BulkUpsertOrderCustomAttributesResponse $instance)
    {
        $this->instance = $instance;
    }
    /**
     * Initializes a new Bulk Upsert Order Custom Attributes Response Builder object.
     *
     * @param array<string,UpsertOrderCustomAttributeResponse> $values
     */
    public static function init(array $values) : self
    {
        return new self(new BulkUpsertOrderCustomAttributesResponse($values));
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
     * Initializes a new Bulk Upsert Order Custom Attributes Response object.
     */
    public function build() : BulkUpsertOrderCustomAttributesResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
