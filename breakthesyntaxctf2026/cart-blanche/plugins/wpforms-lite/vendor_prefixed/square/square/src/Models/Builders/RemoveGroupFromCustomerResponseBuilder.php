<?php

declare (strict_types=1);
namespace WPForms\Vendor\Square\Models\Builders;

use WPForms\Vendor\Core\Utils\CoreHelper;
use WPForms\Vendor\Square\Models\Error;
use WPForms\Vendor\Square\Models\RemoveGroupFromCustomerResponse;
/**
 * Builder for model RemoveGroupFromCustomerResponse
 *
 * @see RemoveGroupFromCustomerResponse
 */
class RemoveGroupFromCustomerResponseBuilder
{
    /**
     * @var RemoveGroupFromCustomerResponse
     */
    private $instance;
    private function __construct(RemoveGroupFromCustomerResponse $instance)
    {
        $this->instance = $instance;
    }
    /**
     * Initializes a new Remove Group From Customer Response Builder object.
     */
    public static function init() : self
    {
        return new self(new RemoveGroupFromCustomerResponse());
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
     * Initializes a new Remove Group From Customer Response object.
     */
    public function build() : RemoveGroupFromCustomerResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
