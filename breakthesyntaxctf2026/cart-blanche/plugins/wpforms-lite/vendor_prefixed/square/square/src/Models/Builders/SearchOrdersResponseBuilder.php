<?php

declare (strict_types=1);
namespace WPForms\Vendor\Square\Models\Builders;

use WPForms\Vendor\Core\Utils\CoreHelper;
use WPForms\Vendor\Square\Models\Error;
use WPForms\Vendor\Square\Models\Order;
use WPForms\Vendor\Square\Models\OrderEntry;
use WPForms\Vendor\Square\Models\SearchOrdersResponse;
/**
 * Builder for model SearchOrdersResponse
 *
 * @see SearchOrdersResponse
 */
class SearchOrdersResponseBuilder
{
    /**
     * @var SearchOrdersResponse
     */
    private $instance;
    private function __construct(SearchOrdersResponse $instance)
    {
        $this->instance = $instance;
    }
    /**
     * Initializes a new Search Orders Response Builder object.
     */
    public static function init() : self
    {
        return new self(new SearchOrdersResponse());
    }
    /**
     * Sets order entries field.
     *
     * @param OrderEntry[]|null $value
     */
    public function orderEntries(?array $value) : self
    {
        $this->instance->setOrderEntries($value);
        return $this;
    }
    /**
     * Sets orders field.
     *
     * @param Order[]|null $value
     */
    public function orders(?array $value) : self
    {
        $this->instance->setOrders($value);
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
     * Initializes a new Search Orders Response object.
     */
    public function build() : SearchOrdersResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
