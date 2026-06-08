<?php

declare (strict_types=1);
namespace WPForms\Vendor\Square\Models\Builders;

use WPForms\Vendor\Core\Utils\CoreHelper;
use WPForms\Vendor\Square\Models\CreateVendorRequest;
use WPForms\Vendor\Square\Models\Vendor;
/**
 * Builder for model CreateVendorRequest
 *
 * @see CreateVendorRequest
 */
class CreateVendorRequestBuilder
{
    /**
     * @var CreateVendorRequest
     */
    private $instance;
    private function __construct(CreateVendorRequest $instance)
    {
        $this->instance = $instance;
    }
    /**
     * Initializes a new Create Vendor Request Builder object.
     *
     * @param string $idempotencyKey
     */
    public static function init(string $idempotencyKey) : self
    {
        return new self(new CreateVendorRequest($idempotencyKey));
    }
    /**
     * Sets vendor field.
     *
     * @param Vendor|null $value
     */
    public function vendor(?Vendor $value) : self
    {
        $this->instance->setVendor($value);
        return $this;
    }
    /**
     * Initializes a new Create Vendor Request object.
     */
    public function build() : CreateVendorRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
