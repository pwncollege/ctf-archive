<?php

declare (strict_types=1);
namespace WPForms\Vendor\Square\Models\Builders;

use WPForms\Vendor\Core\Utils\CoreHelper;
use WPForms\Vendor\Square\Models\CustomAttributeDefinition;
use WPForms\Vendor\Square\Models\Error;
use WPForms\Vendor\Square\Models\ListCustomerCustomAttributeDefinitionsResponse;
/**
 * Builder for model ListCustomerCustomAttributeDefinitionsResponse
 *
 * @see ListCustomerCustomAttributeDefinitionsResponse
 */
class ListCustomerCustomAttributeDefinitionsResponseBuilder
{
    /**
     * @var ListCustomerCustomAttributeDefinitionsResponse
     */
    private $instance;
    private function __construct(ListCustomerCustomAttributeDefinitionsResponse $instance)
    {
        $this->instance = $instance;
    }
    /**
     * Initializes a new List Customer Custom Attribute Definitions Response Builder object.
     */
    public static function init() : self
    {
        return new self(new ListCustomerCustomAttributeDefinitionsResponse());
    }
    /**
     * Sets custom attribute definitions field.
     *
     * @param CustomAttributeDefinition[]|null $value
     */
    public function customAttributeDefinitions(?array $value) : self
    {
        $this->instance->setCustomAttributeDefinitions($value);
        return $this;
    }
    /**
     * Sets cursor field.
     *
     * @param string|null $value
     */
    public function cursor(?string $value) : self
    {
        $this->instance->setCursor($value);
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
     * Initializes a new List Customer Custom Attribute Definitions Response object.
     */
    public function build() : ListCustomerCustomAttributeDefinitionsResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
