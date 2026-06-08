<?php

declare (strict_types=1);
namespace WPForms\Vendor\Square\Models\Builders;

use WPForms\Vendor\Core\Utils\CoreHelper;
use WPForms\Vendor\Square\Models\Error;
use WPForms\Vendor\Square\Models\GiftCard;
use WPForms\Vendor\Square\Models\RetrieveGiftCardResponse;
/**
 * Builder for model RetrieveGiftCardResponse
 *
 * @see RetrieveGiftCardResponse
 */
class RetrieveGiftCardResponseBuilder
{
    /**
     * @var RetrieveGiftCardResponse
     */
    private $instance;
    private function __construct(RetrieveGiftCardResponse $instance)
    {
        $this->instance = $instance;
    }
    /**
     * Initializes a new Retrieve Gift Card Response Builder object.
     */
    public static function init() : self
    {
        return new self(new RetrieveGiftCardResponse());
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
     * Initializes a new Retrieve Gift Card Response object.
     */
    public function build() : RetrieveGiftCardResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
