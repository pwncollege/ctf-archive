<?php

declare (strict_types=1);
namespace WPForms\Vendor\Square\Models\Builders;

use WPForms\Vendor\Core\Utils\CoreHelper;
use WPForms\Vendor\Square\Models\CustomAttributeDefinition;
use WPForms\Vendor\Square\Models\Error;
use WPForms\Vendor\Square\Models\RetrieveBookingCustomAttributeDefinitionResponse;
/**
 * Builder for model RetrieveBookingCustomAttributeDefinitionResponse
 *
 * @see RetrieveBookingCustomAttributeDefinitionResponse
 */
class RetrieveBookingCustomAttributeDefinitionResponseBuilder
{
    /**
     * @var RetrieveBookingCustomAttributeDefinitionResponse
     */
    private $instance;
    private function __construct(RetrieveBookingCustomAttributeDefinitionResponse $instance)
    {
        $this->instance = $instance;
    }
    /**
     * Initializes a new Retrieve Booking Custom Attribute Definition Response Builder object.
     */
    public static function init() : self
    {
        return new self(new RetrieveBookingCustomAttributeDefinitionResponse());
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
     * Initializes a new Retrieve Booking Custom Attribute Definition Response object.
     */
    public function build() : RetrieveBookingCustomAttributeDefinitionResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
