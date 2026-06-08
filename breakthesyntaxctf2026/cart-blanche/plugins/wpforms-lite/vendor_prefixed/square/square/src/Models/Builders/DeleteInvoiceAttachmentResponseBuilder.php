<?php

declare (strict_types=1);
namespace WPForms\Vendor\Square\Models\Builders;

use WPForms\Vendor\Core\Utils\CoreHelper;
use WPForms\Vendor\Square\Models\DeleteInvoiceAttachmentResponse;
use WPForms\Vendor\Square\Models\Error;
/**
 * Builder for model DeleteInvoiceAttachmentResponse
 *
 * @see DeleteInvoiceAttachmentResponse
 */
class DeleteInvoiceAttachmentResponseBuilder
{
    /**
     * @var DeleteInvoiceAttachmentResponse
     */
    private $instance;
    private function __construct(DeleteInvoiceAttachmentResponse $instance)
    {
        $this->instance = $instance;
    }
    /**
     * Initializes a new Delete Invoice Attachment Response Builder object.
     */
    public static function init() : self
    {
        return new self(new DeleteInvoiceAttachmentResponse());
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
     * Initializes a new Delete Invoice Attachment Response object.
     */
    public function build() : DeleteInvoiceAttachmentResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
