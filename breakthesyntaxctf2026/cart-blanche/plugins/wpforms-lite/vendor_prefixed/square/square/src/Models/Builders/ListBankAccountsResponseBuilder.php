<?php

declare (strict_types=1);
namespace WPForms\Vendor\Square\Models\Builders;

use WPForms\Vendor\Core\Utils\CoreHelper;
use WPForms\Vendor\Square\Models\BankAccount;
use WPForms\Vendor\Square\Models\Error;
use WPForms\Vendor\Square\Models\ListBankAccountsResponse;
/**
 * Builder for model ListBankAccountsResponse
 *
 * @see ListBankAccountsResponse
 */
class ListBankAccountsResponseBuilder
{
    /**
     * @var ListBankAccountsResponse
     */
    private $instance;
    private function __construct(ListBankAccountsResponse $instance)
    {
        $this->instance = $instance;
    }
    /**
     * Initializes a new List Bank Accounts Response Builder object.
     */
    public static function init() : self
    {
        return new self(new ListBankAccountsResponse());
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
     * Sets bank accounts field.
     *
     * @param BankAccount[]|null $value
     */
    public function bankAccounts(?array $value) : self
    {
        $this->instance->setBankAccounts($value);
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
     * Initializes a new List Bank Accounts Response object.
     */
    public function build() : ListBankAccountsResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
