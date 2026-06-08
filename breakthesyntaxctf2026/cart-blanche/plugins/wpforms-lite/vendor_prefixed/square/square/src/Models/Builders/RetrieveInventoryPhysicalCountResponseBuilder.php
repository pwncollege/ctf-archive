<?php

declare (strict_types=1);
namespace WPForms\Vendor\Square\Models\Builders;

use WPForms\Vendor\Core\Utils\CoreHelper;
use WPForms\Vendor\Square\Models\Error;
use WPForms\Vendor\Square\Models\InventoryPhysicalCount;
use WPForms\Vendor\Square\Models\RetrieveInventoryPhysicalCountResponse;
/**
 * Builder for model RetrieveInventoryPhysicalCountResponse
 *
 * @see RetrieveInventoryPhysicalCountResponse
 */
class RetrieveInventoryPhysicalCountResponseBuilder
{
    /**
     * @var RetrieveInventoryPhysicalCountResponse
     */
    private $instance;
    private function __construct(RetrieveInventoryPhysicalCountResponse $instance)
    {
        $this->instance = $instance;
    }
    /**
     * Initializes a new Retrieve Inventory Physical Count Response Builder object.
     */
    public static function init() : self
    {
        return new self(new RetrieveInventoryPhysicalCountResponse());
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
     * Sets count field.
     *
     * @param InventoryPhysicalCount|null $value
     */
    public function count(?InventoryPhysicalCount $value) : self
    {
        $this->instance->setCount($value);
        return $this;
    }
    /**
     * Initializes a new Retrieve Inventory Physical Count Response object.
     */
    public function build() : RetrieveInventoryPhysicalCountResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
