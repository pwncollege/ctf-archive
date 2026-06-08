<?php

declare (strict_types=1);
namespace WPForms\Vendor\Square\Models\Builders;

use WPForms\Vendor\Core\Utils\CoreHelper;
use WPForms\Vendor\Square\Models\Card;
use WPForms\Vendor\Square\Models\CreateCardResponse;
use WPForms\Vendor\Square\Models\Error;
/**
 * Builder for model CreateCardResponse
 *
 * @see CreateCardResponse
 */
class CreateCardResponseBuilder
{
    /**
     * @var CreateCardResponse
     */
    private $instance;
    private function __construct(CreateCardResponse $instance)
    {
        $this->instance = $instance;
    }
    /**
     * Initializes a new Create Card Response Builder object.
     */
    public static function init() : self
    {
        return new self(new CreateCardResponse());
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
     * Initializes a new Create Card Response object.
     */
    public function build() : CreateCardResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
