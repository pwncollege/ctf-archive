<?php

declare (strict_types=1);
namespace WPForms\Vendor\Square\Models\Builders;

use WPForms\Vendor\Core\Utils\CoreHelper;
use WPForms\Vendor\Square\Models\Error;
use WPForms\Vendor\Square\Models\GetPayoutResponse;
use WPForms\Vendor\Square\Models\Payout;
/**
 * Builder for model GetPayoutResponse
 *
 * @see GetPayoutResponse
 */
class GetPayoutResponseBuilder
{
    /**
     * @var GetPayoutResponse
     */
    private $instance;
    private function __construct(GetPayoutResponse $instance)
    {
        $this->instance = $instance;
    }
    /**
     * Initializes a new Get Payout Response Builder object.
     */
    public static function init() : self
    {
        return new self(new GetPayoutResponse());
    }
    /**
     * Sets payout field.
     *
     * @param Payout|null $value
     */
    public function payout(?Payout $value) : self
    {
        $this->instance->setPayout($value);
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
     * Initializes a new Get Payout Response object.
     */
    public function build() : GetPayoutResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
