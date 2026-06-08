<?php

declare (strict_types=1);
namespace WPForms\Vendor\Square\Models\Builders;

use WPForms\Vendor\Core\Utils\CoreHelper;
use WPForms\Vendor\Square\Models\CaptureTransactionResponse;
use WPForms\Vendor\Square\Models\Error;
/**
 * Builder for model CaptureTransactionResponse
 *
 * @see CaptureTransactionResponse
 */
class CaptureTransactionResponseBuilder
{
    /**
     * @var CaptureTransactionResponse
     */
    private $instance;
    private function __construct(CaptureTransactionResponse $instance)
    {
        $this->instance = $instance;
    }
    /**
     * Initializes a new Capture Transaction Response Builder object.
     */
    public static function init() : self
    {
        return new self(new CaptureTransactionResponse());
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
     * Initializes a new Capture Transaction Response object.
     */
    public function build() : CaptureTransactionResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
