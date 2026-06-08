<?php

declare (strict_types=1);
namespace WPForms\Vendor\Square\Models\Builders;

use WPForms\Vendor\Core\Utils\CoreHelper;
use WPForms\Vendor\Square\Models\AccumulateLoyaltyPointsResponse;
use WPForms\Vendor\Square\Models\Error;
use WPForms\Vendor\Square\Models\LoyaltyEvent;
/**
 * Builder for model AccumulateLoyaltyPointsResponse
 *
 * @see AccumulateLoyaltyPointsResponse
 */
class AccumulateLoyaltyPointsResponseBuilder
{
    /**
     * @var AccumulateLoyaltyPointsResponse
     */
    private $instance;
    private function __construct(AccumulateLoyaltyPointsResponse $instance)
    {
        $this->instance = $instance;
    }
    /**
     * Initializes a new Accumulate Loyalty Points Response Builder object.
     */
    public static function init() : self
    {
        return new self(new AccumulateLoyaltyPointsResponse());
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
     * Sets event field.
     *
     * @param LoyaltyEvent|null $value
     */
    public function event(?LoyaltyEvent $value) : self
    {
        $this->instance->setEvent($value);
        return $this;
    }
    /**
     * Sets events field.
     *
     * @param LoyaltyEvent[]|null $value
     */
    public function events(?array $value) : self
    {
        $this->instance->setEvents($value);
        return $this;
    }
    /**
     * Initializes a new Accumulate Loyalty Points Response object.
     */
    public function build() : AccumulateLoyaltyPointsResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
