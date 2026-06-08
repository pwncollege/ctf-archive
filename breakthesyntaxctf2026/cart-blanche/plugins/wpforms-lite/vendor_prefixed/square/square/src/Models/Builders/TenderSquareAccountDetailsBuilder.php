<?php

declare (strict_types=1);
namespace WPForms\Vendor\Square\Models\Builders;

use WPForms\Vendor\Core\Utils\CoreHelper;
use WPForms\Vendor\Square\Models\TenderSquareAccountDetails;
/**
 * Builder for model TenderSquareAccountDetails
 *
 * @see TenderSquareAccountDetails
 */
class TenderSquareAccountDetailsBuilder
{
    /**
     * @var TenderSquareAccountDetails
     */
    private $instance;
    private function __construct(TenderSquareAccountDetails $instance)
    {
        $this->instance = $instance;
    }
    /**
     * Initializes a new Tender Square Account Details Builder object.
     */
    public static function init() : self
    {
        return new self(new TenderSquareAccountDetails());
    }
    /**
     * Sets status field.
     *
     * @param string|null $value
     */
    public function status(?string $value) : self
    {
        $this->instance->setStatus($value);
        return $this;
    }
    /**
     * Initializes a new Tender Square Account Details object.
     */
    public function build() : TenderSquareAccountDetails
    {
        return CoreHelper::clone($this->instance);
    }
}
