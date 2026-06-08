<?php

declare (strict_types=1);
namespace WPForms\Vendor\Square\Models\Builders;

use WPForms\Vendor\Core\Utils\CoreHelper;
use WPForms\Vendor\Square\Models\TerminalActionQuerySort;
/**
 * Builder for model TerminalActionQuerySort
 *
 * @see TerminalActionQuerySort
 */
class TerminalActionQuerySortBuilder
{
    /**
     * @var TerminalActionQuerySort
     */
    private $instance;
    private function __construct(TerminalActionQuerySort $instance)
    {
        $this->instance = $instance;
    }
    /**
     * Initializes a new Terminal Action Query Sort Builder object.
     */
    public static function init() : self
    {
        return new self(new TerminalActionQuerySort());
    }
    /**
     * Sets sort order field.
     *
     * @param string|null $value
     */
    public function sortOrder(?string $value) : self
    {
        $this->instance->setSortOrder($value);
        return $this;
    }
    /**
     * Initializes a new Terminal Action Query Sort object.
     */
    public function build() : TerminalActionQuerySort
    {
        return CoreHelper::clone($this->instance);
    }
}
