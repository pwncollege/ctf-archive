<?php

declare (strict_types=1);
namespace WPForms\Vendor\Square\Models\Builders;

use WPForms\Vendor\Core\Utils\CoreHelper;
use WPForms\Vendor\Square\Models\RedeemLoyaltyRewardRequest;
/**
 * Builder for model RedeemLoyaltyRewardRequest
 *
 * @see RedeemLoyaltyRewardRequest
 */
class RedeemLoyaltyRewardRequestBuilder
{
    /**
     * @var RedeemLoyaltyRewardRequest
     */
    private $instance;
    private function __construct(RedeemLoyaltyRewardRequest $instance)
    {
        $this->instance = $instance;
    }
    /**
     * Initializes a new Redeem Loyalty Reward Request Builder object.
     *
     * @param string $idempotencyKey
     * @param string $locationId
     */
    public static function init(string $idempotencyKey, string $locationId) : self
    {
        return new self(new RedeemLoyaltyRewardRequest($idempotencyKey, $locationId));
    }
    /**
     * Initializes a new Redeem Loyalty Reward Request object.
     */
    public function build() : RedeemLoyaltyRewardRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
