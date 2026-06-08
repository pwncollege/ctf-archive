<?php

declare (strict_types=1);
namespace WPForms\Vendor\Square\Models\Builders;

use WPForms\Vendor\Core\Utils\CoreHelper;
use WPForms\Vendor\Square\Models\Error;
use WPForms\Vendor\Square\Models\Location;
use WPForms\Vendor\Square\Models\UpdateLocationResponse;
/**
 * Builder for model UpdateLocationResponse
 *
 * @see UpdateLocationResponse
 */
class UpdateLocationResponseBuilder
{
    /**
     * @var UpdateLocationResponse
     */
    private $instance;
    private function __construct(UpdateLocationResponse $instance)
    {
        $this->instance = $instance;
    }
    /**
     * Initializes a new Update Location Response Builder object.
     */
    public static function init() : self
    {
        return new self(new UpdateLocationResponse());
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
     * Sets location field.
     *
     * @param Location|null $value
     */
    public function location(?Location $value) : self
    {
        $this->instance->setLocation($value);
        return $this;
    }
    /**
     * Initializes a new Update Location Response object.
     */
    public function build() : UpdateLocationResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
