<?php

declare (strict_types=1);
namespace WPForms\Vendor\Square\Models\Builders;

use WPForms\Vendor\Core\Utils\CoreHelper;
use WPForms\Vendor\Square\Models\PaymentLink;
use WPForms\Vendor\Square\Models\UpdatePaymentLinkRequest;
/**
 * Builder for model UpdatePaymentLinkRequest
 *
 * @see UpdatePaymentLinkRequest
 */
class UpdatePaymentLinkRequestBuilder
{
    /**
     * @var UpdatePaymentLinkRequest
     */
    private $instance;
    private function __construct(UpdatePaymentLinkRequest $instance)
    {
        $this->instance = $instance;
    }
    /**
     * Initializes a new Update Payment Link Request Builder object.
     *
     * @param PaymentLink $paymentLink
     */
    public static function init(PaymentLink $paymentLink) : self
    {
        return new self(new UpdatePaymentLinkRequest($paymentLink));
    }
    /**
     * Initializes a new Update Payment Link Request object.
     */
    public function build() : UpdatePaymentLinkRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
