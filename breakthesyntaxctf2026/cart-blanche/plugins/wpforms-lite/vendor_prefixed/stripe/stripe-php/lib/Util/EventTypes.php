<?php

namespace WPForms\Vendor\Stripe\Util;

class EventTypes
{
    const thinEventMapping = [
        // The beginning of the section generated from our OpenAPI spec
        \WPForms\Vendor\Stripe\Events\V1BillingMeterErrorReportTriggeredEvent::LOOKUP_TYPE => \WPForms\Vendor\Stripe\Events\V1BillingMeterErrorReportTriggeredEvent::class,
        \WPForms\Vendor\Stripe\Events\V1BillingMeterNoMeterFoundEvent::LOOKUP_TYPE => \WPForms\Vendor\Stripe\Events\V1BillingMeterNoMeterFoundEvent::class,
    ];
}
