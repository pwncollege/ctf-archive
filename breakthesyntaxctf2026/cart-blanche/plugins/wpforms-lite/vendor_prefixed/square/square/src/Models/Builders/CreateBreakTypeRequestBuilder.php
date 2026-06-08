<?php

declare (strict_types=1);
namespace WPForms\Vendor\Square\Models\Builders;

use WPForms\Vendor\Core\Utils\CoreHelper;
use WPForms\Vendor\Square\Models\BreakType;
use WPForms\Vendor\Square\Models\CreateBreakTypeRequest;
/**
 * Builder for model CreateBreakTypeRequest
 *
 * @see CreateBreakTypeRequest
 */
class CreateBreakTypeRequestBuilder
{
    /**
     * @var CreateBreakTypeRequest
     */
    private $instance;
    private function __construct(CreateBreakTypeRequest $instance)
    {
        $this->instance = $instance;
    }
    /**
     * Initializes a new Create Break Type Request Builder object.
     *
     * @param BreakType $breakType
     */
    public static function init(BreakType $breakType) : self
    {
        return new self(new CreateBreakTypeRequest($breakType));
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
     * Initializes a new Create Break Type Request object.
     */
    public function build() : CreateBreakTypeRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
