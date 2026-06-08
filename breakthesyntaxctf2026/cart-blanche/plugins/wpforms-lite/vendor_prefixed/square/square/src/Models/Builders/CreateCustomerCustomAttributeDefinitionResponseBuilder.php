<?php

declare (strict_types=1);
namespace WPForms\Vendor\Square\Models\Builders;

use WPForms\Vendor\Core\Utils\CoreHelper;
use WPForms\Vendor\Square\Models\CreateCustomerCustomAttributeDefinitionResponse;
use WPForms\Vendor\Square\Models\CustomAttributeDefinition;
use WPForms\Vendor\Square\Models\Error;
/**
 * Builder for model CreateCustomerCustomAttributeDefinitionResponse
 *
 * @see CreateCustomerCustomAttributeDefinitionResponse
 */
class CreateCustomerCustomAttributeDefinitionResponseBuilder
{
    /**
     * @var CreateCustomerCustomAttributeDefinitionResponse
     */
    private $instance;
    private function __construct(CreateCustomerCustomAttributeDefinitionResponse $instance)
    {
        $this->instance = $instance;
    }
    /**
     * Initializes a new Create Customer Custom Attribute Definition Response Builder object.
     */
    public static function init() : self
    {
        return new self(new CreateCustomerCustomAttributeDefinitionResponse());
    }
    /**
     * Sets custom attribute definition field.
     *
     * @param CustomAttributeDefinition|null $value
     */
    public function customAttributeDefinition(?CustomAttributeDefinition $value) : self
    {
        $this->instance->setCustomAttributeDefinition($value);
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
     * Initializes a new Create Customer Custom Attribute Definition Response object.
     */
    public function build() : CreateCustomerCustomAttributeDefinitionResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
