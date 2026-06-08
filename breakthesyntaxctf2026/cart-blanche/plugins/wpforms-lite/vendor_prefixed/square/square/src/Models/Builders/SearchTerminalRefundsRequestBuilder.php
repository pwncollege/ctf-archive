<?php

declare (strict_types=1);
namespace WPForms\Vendor\Square\Models\Builders;

use WPForms\Vendor\Core\Utils\CoreHelper;
use WPForms\Vendor\Square\Models\SearchTerminalRefundsRequest;
use WPForms\Vendor\Square\Models\TerminalRefundQuery;
/**
 * Builder for model SearchTerminalRefundsRequest
 *
 * @see SearchTerminalRefundsRequest
 */
class SearchTerminalRefundsRequestBuilder
{
    /**
     * @var SearchTerminalRefundsRequest
     */
    private $instance;
    private function __construct(SearchTerminalRefundsRequest $instance)
    {
        $this->instance = $instance;
    }
    /**
     * Initializes a new Search Terminal Refunds Request Builder object.
     */
    public static function init() : self
    {
        return new self(new SearchTerminalRefundsRequest());
    }
    /**
     * Sets query field.
     *
     * @param TerminalRefundQuery|null $value
     */
    public function query(?TerminalRefundQuery $value) : self
    {
        $this->instance->setQuery($value);
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
     * Sets limit field.
     *
     * @param int|null $value
     */
    public function limit(?int $value) : self
    {
        $this->instance->setLimit($value);
        return $this;
    }
    /**
     * Initializes a new Search Terminal Refunds Request object.
     */
    public function build() : SearchTerminalRefundsRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
