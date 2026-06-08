<?php

declare (strict_types=1);
namespace WPForms\Vendor\Square\Models\Builders;

use WPForms\Vendor\Core\Utils\CoreHelper;
use WPForms\Vendor\Square\Models\Error;
use WPForms\Vendor\Square\Models\Job;
use WPForms\Vendor\Square\Models\RetrieveJobResponse;
/**
 * Builder for model RetrieveJobResponse
 *
 * @see RetrieveJobResponse
 */
class RetrieveJobResponseBuilder
{
    /**
     * @var RetrieveJobResponse
     */
    private $instance;
    private function __construct(RetrieveJobResponse $instance)
    {
        $this->instance = $instance;
    }
    /**
     * Initializes a new Retrieve Job Response Builder object.
     */
    public static function init() : self
    {
        return new self(new RetrieveJobResponse());
    }
    /**
     * Sets job field.
     *
     * @param Job|null $value
     */
    public function job(?Job $value) : self
    {
        $this->instance->setJob($value);
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
     * Initializes a new Retrieve Job Response object.
     */
    public function build() : RetrieveJobResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
