<?php

declare (strict_types=1);
namespace WPForms\Vendor\Square\Models\Builders;

use WPForms\Vendor\Core\Utils\CoreHelper;
use WPForms\Vendor\Square\Models\Subscription;
use WPForms\Vendor\Square\Models\UpdateSubscriptionRequest;
/**
 * Builder for model UpdateSubscriptionRequest
 *
 * @see UpdateSubscriptionRequest
 */
class UpdateSubscriptionRequestBuilder
{
    /**
     * @var UpdateSubscriptionRequest
     */
    private $instance;
    private function __construct(UpdateSubscriptionRequest $instance)
    {
        $this->instance = $instance;
    }
    /**
     * Initializes a new Update Subscription Request Builder object.
     */
    public static function init() : self
    {
        return new self(new UpdateSubscriptionRequest());
    }
    /**
     * Sets subscription field.
     *
     * @param Subscription|null $value
     */
    public function subscription(?Subscription $value) : self
    {
        $this->instance->setSubscription($value);
        return $this;
    }
    /**
     * Initializes a new Update Subscription Request object.
     */
    public function build() : UpdateSubscriptionRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
