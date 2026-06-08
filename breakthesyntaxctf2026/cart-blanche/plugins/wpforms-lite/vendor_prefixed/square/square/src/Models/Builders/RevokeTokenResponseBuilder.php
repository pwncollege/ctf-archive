<?php

declare (strict_types=1);
namespace WPForms\Vendor\Square\Models\Builders;

use WPForms\Vendor\Core\Utils\CoreHelper;
use WPForms\Vendor\Square\Models\Error;
use WPForms\Vendor\Square\Models\RevokeTokenResponse;
/**
 * Builder for model RevokeTokenResponse
 *
 * @see RevokeTokenResponse
 */
class RevokeTokenResponseBuilder
{
    /**
     * @var RevokeTokenResponse
     */
    private $instance;
    private function __construct(RevokeTokenResponse $instance)
    {
        $this->instance = $instance;
    }
    /**
     * Initializes a new Revoke Token Response Builder object.
     */
    public static function init() : self
    {
        return new self(new RevokeTokenResponse());
    }
    /**
     * Sets success field.
     *
     * @param bool|null $value
     */
    public function success(?bool $value) : self
    {
        $this->instance->setSuccess($value);
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
     * Initializes a new Revoke Token Response object.
     */
    public function build() : RevokeTokenResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
