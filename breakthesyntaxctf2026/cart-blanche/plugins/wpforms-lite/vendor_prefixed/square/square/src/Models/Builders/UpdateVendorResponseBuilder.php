<?php

declare (strict_types=1);
namespace WPForms\Vendor\Square\Models\Builders;

use WPForms\Vendor\Core\Utils\CoreHelper;
use WPForms\Vendor\Square\Models\Error;
use WPForms\Vendor\Square\Models\UpdateVendorResponse;
use WPForms\Vendor\Square\Models\Vendor;
/**
 * Builder for model UpdateVendorResponse
 *
 * @see UpdateVendorResponse
 */
class UpdateVendorResponseBuilder
{
    /**
     * @var UpdateVendorResponse
     */
    private $instance;
    private function __construct(UpdateVendorResponse $instance)
    {
        $this->instance = $instance;
    }
    /**
     * Initializes a new Update Vendor Response Builder object.
     */
    public static function init() : self
    {
        return new self(new UpdateVendorResponse());
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
     * Initializes a new Update Vendor Response object.
     */
    public function build() : UpdateVendorResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
