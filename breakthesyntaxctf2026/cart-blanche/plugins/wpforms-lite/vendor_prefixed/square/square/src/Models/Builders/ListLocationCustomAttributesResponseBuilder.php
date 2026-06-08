<?php

declare (strict_types=1);
namespace WPForms\Vendor\Square\Models\Builders;

use WPForms\Vendor\Core\Utils\CoreHelper;
use WPForms\Vendor\Square\Models\CustomAttribute;
use WPForms\Vendor\Square\Models\Error;
use WPForms\Vendor\Square\Models\ListLocationCustomAttributesResponse;
/**
 * Builder for model ListLocationCustomAttributesResponse
 *
 * @see ListLocationCustomAttributesResponse
 */
class ListLocationCustomAttributesResponseBuilder
{
    /**
     * @var ListLocationCustomAttributesResponse
     */
    private $instance;
    private function __construct(ListLocationCustomAttributesResponse $instance)
    {
        $this->instance = $instance;
    }
    /**
     * Initializes a new List Location Custom Attributes Response Builder object.
     */
    public static function init() : self
    {
        return new self(new ListLocationCustomAttributesResponse());
    }
    /**
     * Sets custom attributes field.
     *
     * @param CustomAttribute[]|null $value
     */
    public function customAttributes(?array $value) : self
    {
        $this->instance->setCustomAttributes($value);
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
     * Initializes a new List Location Custom Attributes Response object.
     */
    public function build() : ListLocationCustomAttributesResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
