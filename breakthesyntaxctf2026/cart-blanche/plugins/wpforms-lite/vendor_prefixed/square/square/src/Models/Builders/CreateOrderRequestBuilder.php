<?php

declare (strict_types=1);
namespace WPForms\Vendor\Square\Models\Builders;

use WPForms\Vendor\Core\Utils\CoreHelper;
use WPForms\Vendor\Square\Models\CreateOrderRequest;
use WPForms\Vendor\Square\Models\Order;
/**
 * Builder for model CreateOrderRequest
 *
 * @see CreateOrderRequest
 */
class CreateOrderRequestBuilder
{
    /**
     * @var CreateOrderRequest
     */
    private $instance;
    private function __construct(CreateOrderRequest $instance)
    {
        $this->instance = $instance;
    }
    /**
     * Initializes a new Create Order Request Builder object.
     */
    public static function init() : self
    {
        return new self(new CreateOrderRequest());
    }
    /**
     * Sets order field.
     *
     * @param Order|null $value
     */
    public function order(?Order $value) : self
    {
        $this->instance->setOrder($value);
        return $this;
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
     * Initializes a new Create Order Request object.
     */
    public function build() : CreateOrderRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
