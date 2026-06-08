<?php

declare (strict_types=1);
namespace WPForms\Vendor\Square\Models\Builders;

use WPForms\Vendor\Core\Utils\CoreHelper;
use WPForms\Vendor\Square\Models\BookingCustomAttributeUpsertRequest;
use WPForms\Vendor\Square\Models\BulkUpsertBookingCustomAttributesRequest;
/**
 * Builder for model BulkUpsertBookingCustomAttributesRequest
 *
 * @see BulkUpsertBookingCustomAttributesRequest
 */
class BulkUpsertBookingCustomAttributesRequestBuilder
{
    /**
     * @var BulkUpsertBookingCustomAttributesRequest
     */
    private $instance;
    private function __construct(BulkUpsertBookingCustomAttributesRequest $instance)
    {
        $this->instance = $instance;
    }
    /**
     * Initializes a new Bulk Upsert Booking Custom Attributes Request Builder object.
     *
     * @param array<string,BookingCustomAttributeUpsertRequest> $values
     */
    public static function init(array $values) : self
    {
        return new self(new BulkUpsertBookingCustomAttributesRequest($values));
    }
    /**
     * Initializes a new Bulk Upsert Booking Custom Attributes Request object.
     */
    public function build() : BulkUpsertBookingCustomAttributesRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
