<?php

declare (strict_types=1);
namespace WPForms\Vendor\Square\Models;

class TenderSquareAccountDetailsStatus
{
    /**
     * The Square Account payment has been authorized but not yet captured.
     */
    public const AUTHORIZED = 'AUTHORIZED';
    /**
     * The Square Account payment was authorized and subsequently captured (i.e., completed).
     */
    public const CAPTURED = 'CAPTURED';
    /**
     * The Square Account payment was authorized and subsequently voided (i.e., canceled).
     */
    public const VOIDED = 'VOIDED';
    /**
     * The Square Account payment failed.
     */
    public const FAILED = 'FAILED';
}
