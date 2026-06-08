<?php

declare (strict_types=1);
namespace WPForms\Vendor\Square\Models\Builders;

use WPForms\Vendor\Core\Utils\CoreHelper;
use WPForms\Vendor\Square\Models\CustomerGroup;
use WPForms\Vendor\Square\Models\Error;
use WPForms\Vendor\Square\Models\UpdateCustomerGroupResponse;
/**
 * Builder for model UpdateCustomerGroupResponse
 *
 * @see UpdateCustomerGroupResponse
 */
class UpdateCustomerGroupResponseBuilder
{
    /**
     * @var UpdateCustomerGroupResponse
     */
    private $instance;
    private function __construct(UpdateCustomerGroupResponse $instance)
    {
        $this->instance = $instance;
    }
    /**
     * Initializes a new Update Customer Group Response Builder object.
     */
    public static function init() : self
    {
        return new self(new UpdateCustomerGroupResponse());
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
     * Sets group field.
     *
     * @param CustomerGroup|null $value
     */
    public function group(?CustomerGroup $value) : self
    {
        $this->instance->setGroup($value);
        return $this;
    }
    /**
     * Initializes a new Update Customer Group Response object.
     */
    public function build() : UpdateCustomerGroupResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
