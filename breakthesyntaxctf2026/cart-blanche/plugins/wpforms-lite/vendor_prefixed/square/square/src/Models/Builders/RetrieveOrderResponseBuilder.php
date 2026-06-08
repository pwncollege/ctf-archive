<?php

declare (strict_types=1);
namespace WPForms\Vendor\Square\Models\Builders;

use WPForms\Vendor\Core\Utils\CoreHelper;
use WPForms\Vendor\Square\Models\Error;
use WPForms\Vendor\Square\Models\Order;
use WPForms\Vendor\Square\Models\RetrieveOrderResponse;
/**
 * Builder for model RetrieveOrderResponse
 *
 * @see RetrieveOrderResponse
 */
class RetrieveOrderResponseBuilder
{
    /**
     * @var RetrieveOrderResponse
     */
    private $instance;
    private function __construct(RetrieveOrderResponse $instance)
    {
        $this->instance = $instance;
    }
    /**
     * Initializes a new Retrieve Order Response Builder object.
     */
    public static function init() : self
    {
        return new self(new RetrieveOrderResponse());
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
     * Initializes a new Retrieve Order Response object.
     */
    public function build() : RetrieveOrderResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
