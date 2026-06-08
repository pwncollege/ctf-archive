<?php

declare (strict_types=1);
namespace WPForms\Vendor\Square\Models\Builders;

use WPForms\Vendor\Core\Utils\CoreHelper;
use WPForms\Vendor\Square\Models\Employee;
use WPForms\Vendor\Square\Models\Error;
use WPForms\Vendor\Square\Models\ListEmployeesResponse;
/**
 * Builder for model ListEmployeesResponse
 *
 * @see ListEmployeesResponse
 */
class ListEmployeesResponseBuilder
{
    /**
     * @var ListEmployeesResponse
     */
    private $instance;
    private function __construct(ListEmployeesResponse $instance)
    {
        $this->instance = $instance;
    }
    /**
     * Initializes a new List Employees Response Builder object.
     */
    public static function init() : self
    {
        return new self(new ListEmployeesResponse());
    }
    /**
     * Sets employees field.
     *
     * @param Employee[]|null $value
     */
    public function employees(?array $value) : self
    {
        $this->instance->setEmployees($value);
        return $this;
    }
    /**
     * Sets cursor field.
     *
     * @param string|null $value
     */
    public function cursor(?string $value) : self
    {
        $this->instance->setCursor($value);
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
     * Initializes a new List Employees Response object.
     */
    public function build() : ListEmployeesResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
