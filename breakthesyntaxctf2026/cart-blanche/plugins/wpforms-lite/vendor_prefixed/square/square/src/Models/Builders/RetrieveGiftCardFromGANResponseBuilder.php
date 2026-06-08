<?php

declare (strict_types=1);
namespace WPForms\Vendor\Square\Models\Builders;

use WPForms\Vendor\Core\Utils\CoreHelper;
use WPForms\Vendor\Square\Models\Error;
use WPForms\Vendor\Square\Models\GiftCard;
use WPForms\Vendor\Square\Models\RetrieveGiftCardFromGANResponse;
/**
 * Builder for model RetrieveGiftCardFromGANResponse
 *
 * @see RetrieveGiftCardFromGANResponse
 */
class RetrieveGiftCardFromGANResponseBuilder
{
    /**
     * @var RetrieveGiftCardFromGANResponse
     */
    private $instance;
    private function __construct(RetrieveGiftCardFromGANResponse $instance)
    {
        $this->instance = $instance;
    }
    /**
     * Initializes a new Retrieve Gift Card From GAN Response Builder object.
     */
    public static function init() : self
    {
        return new self(new RetrieveGiftCardFromGANResponse());
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
     * Sets gift card field.
     *
     * @param GiftCard|null $value
     */
    public function giftCard(?GiftCard $value) : self
    {
        $this->instance->setGiftCard($value);
        return $this;
    }
    /**
     * Initializes a new Retrieve Gift Card From GAN Response object.
     */
    public function build() : RetrieveGiftCardFromGANResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
