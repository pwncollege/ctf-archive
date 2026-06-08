<?php

declare (strict_types=1);
namespace WPForms\Vendor\Square\Models\Builders;

use WPForms\Vendor\Core\Utils\CoreHelper;
use WPForms\Vendor\Square\Models\GiftCardActivityImportReversal;
use WPForms\Vendor\Square\Models\Money;
/**
 * Builder for model GiftCardActivityImportReversal
 *
 * @see GiftCardActivityImportReversal
 */
class GiftCardActivityImportReversalBuilder
{
    /**
     * @var GiftCardActivityImportReversal
     */
    private $instance;
    private function __construct(GiftCardActivityImportReversal $instance)
    {
        $this->instance = $instance;
    }
    /**
     * Initializes a new Gift Card Activity Import Reversal Builder object.
     *
     * @param Money $amountMoney
     */
    public static function init(Money $amountMoney) : self
    {
        return new self(new GiftCardActivityImportReversal($amountMoney));
    }
    /**
     * Initializes a new Gift Card Activity Import Reversal object.
     */
    public function build() : GiftCardActivityImportReversal
    {
        return CoreHelper::clone($this->instance);
    }
}
