<?php

declare (strict_types=1);
namespace WPForms\Vendor\Square\Models\Builders;

use WPForms\Vendor\Core\Utils\CoreHelper;
use WPForms\Vendor\Square\Models\Card;
use WPForms\Vendor\Square\Models\CreateCustomerCardResponse;
use WPForms\Vendor\Square\Models\Error;
/**
 * Builder for model CreateCustomerCardResponse
 *
 * @see CreateCustomerCardResponse
 */
class CreateCustomerCardResponseBuilder
{
    /**
     * @var CreateCustomerCardResponse
     */
    private $instance;
    private function __construct(CreateCustomerCardResponse $instance)
    {
        $this->instance = $instance;
    }
    /**
     * Initializes a new Create Customer Card Response Builder object.
     */
    public static function init() : self
    {
        return new self(new CreateCustomerCardResponse());
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
     * Sets card field.
     *
     * @param Card|null $value
     */
    public function card(?Card $value) : self
    {
        $this->instance->setCard($value);
        return $this;
    }
    /**
     * Initializes a new Create Customer Card Response object.
     */
    public function build() : CreateCustomerCardResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
