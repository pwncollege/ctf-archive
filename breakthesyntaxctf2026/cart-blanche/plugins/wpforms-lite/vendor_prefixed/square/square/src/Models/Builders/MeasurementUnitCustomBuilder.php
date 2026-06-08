<?php

declare (strict_types=1);
namespace WPForms\Vendor\Square\Models\Builders;

use WPForms\Vendor\Core\Utils\CoreHelper;
use WPForms\Vendor\Square\Models\MeasurementUnitCustom;
/**
 * Builder for model MeasurementUnitCustom
 *
 * @see MeasurementUnitCustom
 */
class MeasurementUnitCustomBuilder
{
    /**
     * @var MeasurementUnitCustom
     */
    private $instance;
    private function __construct(MeasurementUnitCustom $instance)
    {
        $this->instance = $instance;
    }
    /**
     * Initializes a new Measurement Unit Custom Builder object.
     *
     * @param string $name
     * @param string $abbreviation
     */
    public static function init(string $name, string $abbreviation) : self
    {
        return new self(new MeasurementUnitCustom($name, $abbreviation));
    }
    /**
     * Initializes a new Measurement Unit Custom object.
     */
    public function build() : MeasurementUnitCustom
    {
        return CoreHelper::clone($this->instance);
    }
}
