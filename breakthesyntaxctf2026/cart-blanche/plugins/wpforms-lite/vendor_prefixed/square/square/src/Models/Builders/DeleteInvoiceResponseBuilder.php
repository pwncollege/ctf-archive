<?php

declare (strict_types=1);
namespace WPForms\Vendor\Square\Models\Builders;

use WPForms\Vendor\Core\Utils\CoreHelper;
use WPForms\Vendor\Square\Models\DeleteInvoiceResponse;
use WPForms\Vendor\Square\Models\Error;
/**
 * Builder for model DeleteInvoiceResponse
 *
 * @see DeleteInvoiceResponse
 */
class DeleteInvoiceResponseBuilder
{
    /**
     * @var DeleteInvoiceResponse
     */
    private $instance;
    private function __construct(DeleteInvoiceResponse $instance)
    {
        $this->instance = $instance;
    }
    /**
     * Initializes a new Delete Invoice Response Builder object.
     */
    public static function init() : self
    {
        return new self(new DeleteInvoiceResponse());
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
     * Initializes a new Delete Invoice Response object.
     */
    public function build() : DeleteInvoiceResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
