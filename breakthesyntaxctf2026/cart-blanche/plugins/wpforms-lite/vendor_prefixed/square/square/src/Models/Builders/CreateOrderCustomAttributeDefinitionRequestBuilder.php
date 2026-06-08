<?php

declare (strict_types=1);
namespace WPForms\Vendor\Square\Models\Builders;

use WPForms\Vendor\Core\Utils\CoreHelper;
use WPForms\Vendor\Square\Models\CreateOrderCustomAttributeDefinitionRequest;
use WPForms\Vendor\Square\Models\CustomAttributeDefinition;
/**
 * Builder for model CreateOrderCustomAttributeDefinitionRequest
 *
 * @see CreateOrderCustomAttributeDefinitionRequest
 */
class CreateOrderCustomAttributeDefinitionRequestBuilder
{
    /**
     * @var CreateOrderCustomAttributeDefinitionRequest
     */
    private $instance;
    private function __construct(CreateOrderCustomAttributeDefinitionRequest $instance)
    {
        $this->instance = $instance;
    }
    /**
     * Initializes a new Create Order Custom Attribute Definition Request Builder object.
     *
     * @param CustomAttributeDefinition $customAttributeDefinition
     */
    public static function init(CustomAttributeDefinition $customAttributeDefinition) : self
    {
        return new self(new CreateOrderCustomAttributeDefinitionRequest($customAttributeDefinition));
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
     * Initializes a new Create Order Custom Attribute Definition Request object.
     */
    public function build() : CreateOrderCustomAttributeDefinitionRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
