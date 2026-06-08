<?php

declare (strict_types=1);
namespace WPForms\Vendor\Square\Models\Builders;

use WPForms\Vendor\Core\Utils\CoreHelper;
use WPForms\Vendor\Square\Models\OrderCreated;
use WPForms\Vendor\Square\Models\OrderCreatedObject;
/**
 * Builder for model OrderCreatedObject
 *
 * @see OrderCreatedObject
 */
class OrderCreatedObjectBuilder
{
    /**
     * @var OrderCreatedObject
     */
    private $instance;
    private function __construct(OrderCreatedObject $instance)
    {
        $this->instance = $instance;
    }
    /**
     * Initializes a new Order Created Object Builder object.
     */
    public static function init() : self
    {
        return new self(new OrderCreatedObject());
    }
    /**
     * Sets order created field.
     *
     * @param OrderCreated|null $value
     */
    public function orderCreated(?OrderCreated $value) : self
    {
        $this->instance->setOrderCreated($value);
        return $this;
    }
    /**
     * Initializes a new Order Created Object object.
     */
    public function build() : OrderCreatedObject
    {
        return CoreHelper::clone($this->instance);
    }
}
