<?php

declare (strict_types=1);
namespace WPForms\Vendor\Square\Models\Builders;

use WPForms\Vendor\Core\Utils\CoreHelper;
use WPForms\Vendor\Square\Models\EmployeeWage;
use WPForms\Vendor\Square\Models\Error;
use WPForms\Vendor\Square\Models\GetEmployeeWageResponse;
/**
 * Builder for model GetEmployeeWageResponse
 *
 * @see GetEmployeeWageResponse
 */
class GetEmployeeWageResponseBuilder
{
    /**
     * @var GetEmployeeWageResponse
     */
    private $instance;
    private function __construct(GetEmployeeWageResponse $instance)
    {
        $this->instance = $instance;
    }
    /**
     * Initializes a new Get Employee Wage Response Builder object.
     */
    public static function init() : self
    {
        return new self(new GetEmployeeWageResponse());
    }
    /**
     * Sets employee wage field.
     *
     * @param EmployeeWage|null $value
     */
    public function employeeWage(?EmployeeWage $value) : self
    {
        $this->instance->setEmployeeWage($value);
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
     * Initializes a new Get Employee Wage Response object.
     */
    public function build() : GetEmployeeWageResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
