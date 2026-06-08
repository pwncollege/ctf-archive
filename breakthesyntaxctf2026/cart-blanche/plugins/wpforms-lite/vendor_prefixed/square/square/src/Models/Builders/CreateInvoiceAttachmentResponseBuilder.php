<?php

declare (strict_types=1);
namespace WPForms\Vendor\Square\Models\Builders;

use WPForms\Vendor\Core\Utils\CoreHelper;
use WPForms\Vendor\Square\Models\CreateInvoiceAttachmentResponse;
use WPForms\Vendor\Square\Models\Error;
use WPForms\Vendor\Square\Models\InvoiceAttachment;
/**
 * Builder for model CreateInvoiceAttachmentResponse
 *
 * @see CreateInvoiceAttachmentResponse
 */
class CreateInvoiceAttachmentResponseBuilder
{
    /**
     * @var CreateInvoiceAttachmentResponse
     */
    private $instance;
    private function __construct(CreateInvoiceAttachmentResponse $instance)
    {
        $this->instance = $instance;
    }
    /**
     * Initializes a new Create Invoice Attachment Response Builder object.
     */
    public static function init() : self
    {
        return new self(new CreateInvoiceAttachmentResponse());
    }
    /**
     * Sets attachment field.
     *
     * @param InvoiceAttachment|null $value
     */
    public function attachment(?InvoiceAttachment $value) : self
    {
        $this->instance->setAttachment($value);
        return $this;
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
     * Initializes a new Create Invoice Attachment Response object.
     */
    public function build() : CreateInvoiceAttachmentResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
