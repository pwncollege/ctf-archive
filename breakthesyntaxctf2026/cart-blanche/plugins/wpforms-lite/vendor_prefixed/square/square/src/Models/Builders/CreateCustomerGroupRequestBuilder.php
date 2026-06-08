<?php

declare (strict_types=1);
namespace WPForms\Vendor\Square\Models\Builders;

use WPForms\Vendor\Core\Utils\CoreHelper;
use WPForms\Vendor\Square\Models\CreateCustomerGroupRequest;
use WPForms\Vendor\Square\Models\CustomerGroup;
/**
 * Builder for model CreateCustomerGroupRequest
 *
 * @see CreateCustomerGroupRequest
 */
class CreateCustomerGroupRequestBuilder
{
    /**
     * @var CreateCustomerGroupRequest
     */
    private $instance;
    private function __construct(CreateCustomerGroupRequest $instance)
    {
        $this->instance = $instance;
    }
    /**
     * Initializes a new Create Customer Group Request Builder object.
     *
     * @param CustomerGroup $group
     */
    public static function init(CustomerGroup $group) : self
    {
        return new self(new CreateCustomerGroupRequest($group));
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
     * Initializes a new Create Customer Group Request object.
     */
    public function build() : CreateCustomerGroupRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
