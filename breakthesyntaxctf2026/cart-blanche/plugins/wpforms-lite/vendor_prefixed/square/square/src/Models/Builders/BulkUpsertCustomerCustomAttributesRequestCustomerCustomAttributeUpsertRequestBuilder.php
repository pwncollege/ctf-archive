<?php

declare (strict_types=1);
namespace WPForms\Vendor\Square\Models\Builders;

use WPForms\Vendor\Core\Utils\CoreHelper;
use WPForms\Vendor\Square\Models\BulkUpsertCustomerCustomAttributesRequestCustomerCustomAttributeUpsertRequest;
use WPForms\Vendor\Square\Models\CustomAttribute;
/**
 * Builder for model BulkUpsertCustomerCustomAttributesRequestCustomerCustomAttributeUpsertRequest
 *
 * @see BulkUpsertCustomerCustomAttributesRequestCustomerCustomAttributeUpsertRequest
 */
class BulkUpsertCustomerCustomAttributesRequestCustomerCustomAttributeUpsertRequestBuilder
{
    /**
     * @var BulkUpsertCustomerCustomAttributesRequestCustomerCustomAttributeUpsertRequest
     */
    private $instance;
    private function __construct(BulkUpsertCustomerCustomAttributesRequestCustomerCustomAttributeUpsertRequest $instance)
    {
        $this->instance = $instance;
    }
    /**
     * Initializes a new Bulk Upsert Customer Custom Attributes Request Customer Custom Attribute Upsert
     * Request Builder object.
     *
     * @param string $customerId
     * @param CustomAttribute $customAttribute
     */
    public static function init(string $customerId, CustomAttribute $customAttribute) : self
    {
        return new self(new BulkUpsertCustomerCustomAttributesRequestCustomerCustomAttributeUpsertRequest($customerId, $customAttribute));
    }
    /**
     * Sets idempotency key field.
     *
     * @param string|null $value
     */
    public function idempotencyKey(?string $value) : self
    {
        $this->instance->setIdempotencyKey($value);
        return $this;
    }
    /**
     * Unsets idempotency key field.
     */
    public function unsetIdempotencyKey() : self
    {
        $this->instance->unsetIdempotencyKey();
        return $this;
    }
    /**
     * Initializes a new Bulk Upsert Customer Custom Attributes Request Customer Custom Attribute Upsert
     * Request object.
     */
    public function build() : BulkUpsertCustomerCustomAttributesRequestCustomerCustomAttributeUpsertRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
