<?php

declare (strict_types=1);
namespace WPForms\Vendor\Square\Models\Builders;

use WPForms\Vendor\Core\Utils\CoreHelper;
use WPForms\Vendor\Square\Models\CreateDisputeEvidenceTextResponse;
use WPForms\Vendor\Square\Models\DisputeEvidence;
use WPForms\Vendor\Square\Models\Error;
/**
 * Builder for model CreateDisputeEvidenceTextResponse
 *
 * @see CreateDisputeEvidenceTextResponse
 */
class CreateDisputeEvidenceTextResponseBuilder
{
    /**
     * @var CreateDisputeEvidenceTextResponse
     */
    private $instance;
    private function __construct(CreateDisputeEvidenceTextResponse $instance)
    {
        $this->instance = $instance;
    }
    /**
     * Initializes a new Create Dispute Evidence Text Response Builder object.
     */
    public static function init() : self
    {
        return new self(new CreateDisputeEvidenceTextResponse());
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
     * Sets evidence field.
     *
     * @param DisputeEvidence|null $value
     */
    public function evidence(?DisputeEvidence $value) : self
    {
        $this->instance->setEvidence($value);
        return $this;
    }
    /**
     * Initializes a new Create Dispute Evidence Text Response object.
     */
    public function build() : CreateDisputeEvidenceTextResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
