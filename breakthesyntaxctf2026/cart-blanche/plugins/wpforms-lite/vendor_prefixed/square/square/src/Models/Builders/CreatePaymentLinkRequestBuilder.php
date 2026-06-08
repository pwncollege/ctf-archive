<?php

declare (strict_types=1);
namespace WPForms\Vendor\Square\Models\Builders;

use WPForms\Vendor\Core\Utils\CoreHelper;
use WPForms\Vendor\Square\Models\CheckoutOptions;
use WPForms\Vendor\Square\Models\CreatePaymentLinkRequest;
use WPForms\Vendor\Square\Models\Order;
use WPForms\Vendor\Square\Models\PrePopulatedData;
use WPForms\Vendor\Square\Models\QuickPay;
/**
 * Builder for model CreatePaymentLinkRequest
 *
 * @see CreatePaymentLinkRequest
 */
class CreatePaymentLinkRequestBuilder
{
    /**
     * @var CreatePaymentLinkRequest
     */
    private $instance;
    private function __construct(CreatePaymentLinkRequest $instance)
    {
        $this->instance = $instance;
    }
    /**
     * Initializes a new Create Payment Link Request Builder object.
     */
    public static function init() : self
    {
        return new self(new CreatePaymentLinkRequest());
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
     * Sets description field.
     *
     * @param string|null $value
     */
    public function description(?string $value) : self
    {
        $this->instance->setDescription($value);
        return $this;
    }
    /**
     * Sets quick pay field.
     *
     * @param QuickPay|null $value
     */
    public function quickPay(?QuickPay $value) : self
    {
        $this->instance->setQuickPay($value);
        return $this;
    }
    /**
     * Sets order field.
     *
     * @param Order|null $value
     */
    public function order(?Order $value) : self
    {
        $this->instance->setOrder($value);
        return $this;
    }
    /**
     * Sets checkout options field.
     *
     * @param CheckoutOptions|null $value
     */
    public function checkoutOptions(?CheckoutOptions $value) : self
    {
        $this->instance->setCheckoutOptions($value);
        return $this;
    }
    /**
     * Sets pre populated data field.
     *
     * @param PrePopulatedData|null $value
     */
    public function prePopulatedData(?PrePopulatedData $value) : self
    {
        $this->instance->setPrePopulatedData($value);
        return $this;
    }
    /**
     * Sets payment note field.
     *
     * @param string|null $value
     */
    public function paymentNote(?string $value) : self
    {
        $this->instance->setPaymentNote($value);
        return $this;
    }
    /**
     * Initializes a new Create Payment Link Request object.
     */
    public function build() : CreatePaymentLinkRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
