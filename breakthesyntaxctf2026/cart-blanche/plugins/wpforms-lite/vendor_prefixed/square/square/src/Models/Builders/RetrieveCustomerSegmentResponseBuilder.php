<?php

declare (strict_types=1);
namespace WPForms\Vendor\Square\Models\Builders;

use WPForms\Vendor\Core\Utils\CoreHelper;
use WPForms\Vendor\Square\Models\CustomerSegment;
use WPForms\Vendor\Square\Models\Error;
use WPForms\Vendor\Square\Models\RetrieveCustomerSegmentResponse;
/**
 * Builder for model RetrieveCustomerSegmentResponse
 *
 * @see RetrieveCustomerSegmentResponse
 */
class RetrieveCustomerSegmentResponseBuilder
{
    /**
     * @var RetrieveCustomerSegmentResponse
     */
    private $instance;
    private function __construct(RetrieveCustomerSegmentResponse $instance)
    {
        $this->instance = $instance;
    }
    /**
     * Initializes a new Retrieve Customer Segment Response Builder object.
     */
    public static function init() : self
    {
        return new self(new RetrieveCustomerSegmentResponse());
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
     * Sets segment field.
     *
     * @param CustomerSegment|null $value
     */
    public function segment(?CustomerSegment $value) : self
    {
        $this->instance->setSegment($value);
        return $this;
    }
    /**
     * Initializes a new Retrieve Customer Segment Response object.
     */
    public function build() : RetrieveCustomerSegmentResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
