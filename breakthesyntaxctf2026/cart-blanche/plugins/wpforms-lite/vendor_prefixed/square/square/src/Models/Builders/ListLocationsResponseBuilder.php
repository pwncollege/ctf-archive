<?php

declare (strict_types=1);
namespace WPForms\Vendor\Square\Models\Builders;

use WPForms\Vendor\Core\Utils\CoreHelper;
use WPForms\Vendor\Square\Models\Error;
use WPForms\Vendor\Square\Models\ListLocationsResponse;
use WPForms\Vendor\Square\Models\Location;
/**
 * Builder for model ListLocationsResponse
 *
 * @see ListLocationsResponse
 */
class ListLocationsResponseBuilder
{
    /**
     * @var ListLocationsResponse
     */
    private $instance;
    private function __construct(ListLocationsResponse $instance)
    {
        $this->instance = $instance;
    }
    /**
     * Initializes a new List Locations Response Builder object.
     */
    public static function init() : self
    {
        return new self(new ListLocationsResponse());
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
     * Sets locations field.
     *
     * @param Location[]|null $value
     */
    public function locations(?array $value) : self
    {
        $this->instance->setLocations($value);
        return $this;
    }
    /**
     * Initializes a new List Locations Response object.
     */
    public function build() : ListLocationsResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
