<?php

declare (strict_types=1);
namespace WPForms\Vendor\Square\Models\Builders;

use WPForms\Vendor\Core\Utils\CoreHelper;
use WPForms\Vendor\Square\Models\DeleteLocationCustomAttributeDefinitionResponse;
use WPForms\Vendor\Square\Models\Error;
/**
 * Builder for model DeleteLocationCustomAttributeDefinitionResponse
 *
 * @see DeleteLocationCustomAttributeDefinitionResponse
 */
class DeleteLocationCustomAttributeDefinitionResponseBuilder
{
    /**
     * @var DeleteLocationCustomAttributeDefinitionResponse
     */
    private $instance;
    private function __construct(DeleteLocationCustomAttributeDefinitionResponse $instance)
    {
        $this->instance = $instance;
    }
    /**
     * Initializes a new Delete Location Custom Attribute Definition Response Builder object.
     */
    public static function init() : self
    {
        return new self(new DeleteLocationCustomAttributeDefinitionResponse());
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
     * Initializes a new Delete Location Custom Attribute Definition Response object.
     */
    public function build() : DeleteLocationCustomAttributeDefinitionResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
