<?php

declare (strict_types=1);
namespace WPForms\Vendor\Square\Models\Builders;

use WPForms\Vendor\Core\Utils\CoreHelper;
use WPForms\Vendor\Square\Models\DestinationDetails;
use WPForms\Vendor\Square\Models\DestinationDetailsCardRefundDetails;
use WPForms\Vendor\Square\Models\DestinationDetailsCashRefundDetails;
use WPForms\Vendor\Square\Models\DestinationDetailsExternalRefundDetails;
/**
 * Builder for model DestinationDetails
 *
 * @see DestinationDetails
 */
class DestinationDetailsBuilder
{
    /**
     * @var DestinationDetails
     */
    private $instance;
    private function __construct(DestinationDetails $instance)
    {
        $this->instance = $instance;
    }
    /**
     * Initializes a new Destination Details Builder object.
     */
    public static function init() : self
    {
        return new self(new DestinationDetails());
    }
    /**
     * Sets card details field.
     *
     * @param DestinationDetailsCardRefundDetails|null $value
     */
    public function cardDetails(?DestinationDetailsCardRefundDetails $value) : self
    {
        $this->instance->setCardDetails($value);
        return $this;
    }
    /**
     * Sets cash details field.
     *
     * @param DestinationDetailsCashRefundDetails|null $value
     */
    public function cashDetails(?DestinationDetailsCashRefundDetails $value) : self
    {
        $this->instance->setCashDetails($value);
        return $this;
    }
    /**
     * Sets external details field.
     *
     * @param DestinationDetailsExternalRefundDetails|null $value
     */
    public function externalDetails(?DestinationDetailsExternalRefundDetails $value) : self
    {
        $this->instance->setExternalDetails($value);
        return $this;
    }
    /**
     * Initializes a new Destination Details object.
     */
    public function build() : DestinationDetails
    {
        return CoreHelper::clone($this->instance);
    }
}
