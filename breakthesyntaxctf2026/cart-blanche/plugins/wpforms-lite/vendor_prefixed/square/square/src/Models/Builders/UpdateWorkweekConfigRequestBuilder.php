<?php

declare (strict_types=1);
namespace WPForms\Vendor\Square\Models\Builders;

use WPForms\Vendor\Core\Utils\CoreHelper;
use WPForms\Vendor\Square\Models\UpdateWorkweekConfigRequest;
use WPForms\Vendor\Square\Models\WorkweekConfig;
/**
 * Builder for model UpdateWorkweekConfigRequest
 *
 * @see UpdateWorkweekConfigRequest
 */
class UpdateWorkweekConfigRequestBuilder
{
    /**
     * @var UpdateWorkweekConfigRequest
     */
    private $instance;
    private function __construct(UpdateWorkweekConfigRequest $instance)
    {
        $this->instance = $instance;
    }
    /**
     * Initializes a new Update Workweek Config Request Builder object.
     *
     * @param WorkweekConfig $workweekConfig
     */
    public static function init(WorkweekConfig $workweekConfig) : self
    {
        return new self(new UpdateWorkweekConfigRequest($workweekConfig));
    }
    /**
     * Initializes a new Update Workweek Config Request object.
     */
    public function build() : UpdateWorkweekConfigRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
